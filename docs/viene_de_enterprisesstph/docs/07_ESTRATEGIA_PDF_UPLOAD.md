# Estrategia de Cargue Automatico de PDF al Sistema de Reportes

---

## 1. Problema: El Pipeline Actual (n8n + Gmail + Google Sheets)

Actualmente, los PDFs generados en AppSheet llegan al sistema mediante una cadena fragil:

```
AppSheet genera PDF
    |
    v
Envia email a Gmail (from: noreply@appsheet.com)
    |
    v
n8n (robot) detecta email nuevo
    |
    v
Parsea el subject: "DOCUMENTO__CLIENTE___FECHA"
    |
    v
Consulta Google Sheets para obtener id_report_type e id_detailreport
    |
    v
Descarga el PDF adjunto
    |
    v
POST /addReportPost (multipart/form-data)
    |
    v
PDF queda en uploads/{nit_cliente}/ y registrado en tbl_reporte
```

**Problemas de este pipeline:**
- 5 servicios externos encadenados (AppSheet, Gmail, n8n, Google Sheets, API)
- Si falla cualquier eslabon, el PDF no llega al sistema
- Delay de 1-5 minutos entre generacion y cargue
- Costo de n8n, mantenimiento de credenciales Gmail/Google Sheets
- Formato fragil del subject (`__` y `___` como separadores)

---

## 2. Solucion: Insercion Directa desde el Controlador

Con el nuevo modulo de inspecciones, el PDF se genera **dentro del mismo servidor**. No hay necesidad de salir a servicios externos:

```
Consultor finaliza el acta y firma
    |
    v
ActaVisitaController::generatePdf($id)
    |
    v
DOMPDF genera el PDF en memoria
    |
    v
Se guarda en uploads/{nit_cliente}/acta_visita_{id}_{fecha}.pdf
    |
    v
Se inserta directamente en tbl_reporte (misma transaccion)
    |
    v
Listo. El PDF aparece en "Reportes" del cliente inmediatamente
```

**Ventajas:**
- 0 servicios externos
- Instantaneo (misma peticion HTTP)
- Sin posibilidad de perdida
- Sin costos adicionales

---

## 3. Endpoint Existente: addReportPost

El sistema ya tiene el endpoint `ReportController::addReportPost()` que guarda reportes. Sin embargo, **NO vamos a llamar al endpoint via HTTP** — vamos a reutilizar la misma logica internamente.

### Estructura de `tbl_reporte`

```
tbl_reporte
├── id_reporte (PK, AUTO_INCREMENT)
├── titulo_reporte (VARCHAR)
├── id_detailreport (INT, FK)
├── id_report_type (INT, FK)
├── id_cliente (INT, FK)
├── estado (VARCHAR)
├── observaciones (TEXT)
├── enlace (VARCHAR) ← URL completa al archivo PDF
├── created_at (DATETIME)
└── updated_at (DATETIME)
```

### Como guarda addReportPost actualmente

```php
// Archivo: uploads/{nit_cliente}/{nombre_random}
$uploadPath = ROOTPATH . 'public/uploads/' . $nitCliente;
$fileName = $file->getRandomName();
$file->move($uploadPath, $fileName);

// Registro en BD
$data = [
    'titulo_reporte' => $titulo,
    'id_detailreport' => $idDetailReport,
    'id_report_type' => $idReportType,
    'id_cliente' => $idCliente,
    'estado' => $estado,
    'observaciones' => $observaciones,
    'enlace' => base_url('uploads/' . $nitCliente . '/' . $fileName),
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s'),
];
$reporteModel->save($data);
```

---

## 4. Implementacion: Auto-Upload en ActaVisitaController

### 4.1 Metodo uploadToReportes() (privado)

```php
/**
 * Registra el PDF del acta en tbl_reporte para que aparezca
 * en el modulo de reportes del cliente.
 */
private function uploadToReportes(array $acta, string $pdfPath): bool
{
    $reporteModel = new \App\Models\ReporteModel();
    $clientModel = new \App\Models\ClientModel();

    $cliente = $clientModel->find($acta['id_cliente']);
    if (!$cliente) return false;

    $nitCliente = $cliente['nit_cliente'];

    // Copiar PDF a uploads/{nit_cliente}/ (donde el sistema de reportes lo espera)
    $destDir = ROOTPATH . 'public/uploads/' . $nitCliente;
    if (!is_dir($destDir)) {
        mkdir($destDir, 0755, true);
    }

    $fileName = 'acta_visita_' . $acta['id'] . '_' . date('Ymd_His') . '.pdf';
    $destPath = $destDir . '/' . $fileName;

    // Copiar desde uploads/inspecciones/pdfs/ a uploads/{nit}/
    copy(FCPATH . $pdfPath, $destPath);

    // Insertar en tbl_reporte
    $data = [
        'titulo_reporte' => 'ACTA DE VISITA - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $acta['fecha_visita'],
        'id_detailreport' => 9,      // ACTA DE VISITA
        'id_report_type'  => 6,      // GESTIÓN SST
        'id_cliente'      => $acta['id_cliente'],
        'estado'          => 'Activo',
        'observaciones'   => 'Generado automaticamente desde modulo de inspecciones',
        'enlace'          => base_url('uploads/' . $nitCliente . '/' . $fileName),
        'created_at'      => date('Y-m-d H:i:s'),
        'updated_at'      => date('Y-m-d H:i:s'),
    ];

    return $reporteModel->save($data);
}
```

### 4.2 Integracion en generatePdf()

```php
public function generatePdf($id)
{
    $acta = $this->actaModel->find($id);
    if (!$acta) return redirect()->back();

    // ... cargar datos, integrantes, temas, firmas, etc ...

    // Generar PDF con DOMPDF
    $html = view('inspecciones/acta_visita/pdf', $data);
    $dompdf = new \Dompdf\Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('letter', 'portrait');
    $dompdf->render();

    // Guardar PDF en disco
    $pdfDir = 'uploads/inspecciones/pdfs/';
    if (!is_dir(FCPATH . $pdfDir)) {
        mkdir(FCPATH . $pdfDir, 0755, true);
    }
    $pdfFileName = 'acta_visita_' . $id . '_' . date('Ymd') . '.pdf';
    $pdfPath = $pdfDir . $pdfFileName;
    file_put_contents(FCPATH . $pdfPath, $dompdf->output());

    // Guardar ruta en el acta
    $this->actaModel->update($id, [
        'ruta_pdf' => $pdfPath,
        'estado'   => 'completo'
    ]);

    // AUTO-UPLOAD: registrar en tbl_reporte
    $this->uploadToReportes($acta, $pdfPath);

    // Retornar PDF para descarga/vista
    return $this->response
        ->setHeader('Content-Type', 'application/pdf')
        ->setHeader('Content-Disposition', 'inline; filename="' . $pdfFileName . '"')
        ->setBody($dompdf->output());
}
```

### 4.3 Flujo completo paso a paso

```
1. Consultor toca "Finalizar y generar PDF"
    |
2. ActaVisitaController::generatePdf($id)
    |
3. Carga datos: acta + integrantes + temas + firmas + pendientes + mantenimientos
    |
4. Renderiza vista 'inspecciones/acta_visita/pdf' con DOMPDF
    |
5. Guarda PDF en uploads/inspecciones/pdfs/acta_visita_{id}_{fecha}.pdf
    |
6. Actualiza tbl_acta_visita: ruta_pdf = '...', estado = 'completo'
    |
7. uploadToReportes():
    |   a. Busca nit_cliente del cliente
    |   b. Copia PDF a uploads/{nit_cliente}/acta_visita_{id}_{fecha}.pdf
    |   c. INSERT INTO tbl_reporte (id_report_type=6, id_detailreport=9)
    |
8. PDF aparece inmediatamente en el modulo de Reportes del panel admin
    |
9. Retorna el PDF al navegador para vista previa
```

---

## 5. Mapeo de Tipos de Documento

Esta tabla mapea los documentos que antes se enviaban via n8n/AppSheet a sus IDs en el sistema. Solo la fila de **ACTA DE VISITA** aplica ahora, pero se documenta la tabla completa para futuras inspecciones.

### Documentos relevantes para el modulo de inspecciones

| Documento | id_report_type | id_detailreport | Estado |
|-----------|---------------|-----------------|--------|
| **ACTA DE VISITA** | **6** | **9** | FUNCIONAL |
| **INSPECCION LOCATIVAS** | **6** | **10** | FUNCIONAL |
| **INSPECCION SENALIZACION** | **6** | **11** | FUNCIONAL |
| **INSPECCION EXTINTORES** | **6** | **12** | FUNCIONAL |
| **INSPECCION BOTIQUINES** | **6** | **13** | FUNCIONAL |
| INSPECCION GABINETES CONTRAINCENDIO | 6 | — | Pendiente (roadmap #6) |
| COMUNICACIONES | 6 | — | Pendiente (roadmap #7) |

### Tabla completa de tipos de reporte (referencia)

| id_report_type | Descripcion |
|---------------|-------------|
| 1 | INFORMES |
| 2 | CAPACITACIONES |
| 3 | ESTANDARES MINIMOS |
| 4 | LEGAL SST |
| 5 | PLAN DE TRABAJO |
| 6 | GESTION SST |
| 7 | SISTEMA GESTION |

---

## 6. Prevencion de Duplicados

Si el consultor regenera el PDF (por ejemplo, despues de corregir algo), se debe evitar duplicar el reporte.

```php
private function uploadToReportes(array $acta, string $pdfPath): bool
{
    $reporteModel = new \App\Models\ReporteModel();
    $clientModel = new \App\Models\ClientModel();

    $cliente = $clientModel->find($acta['id_cliente']);
    if (!$cliente) return false;

    $nitCliente = $cliente['nit_cliente'];

    // Verificar si ya existe un reporte para esta acta
    $existente = $reporteModel
        ->where('id_cliente', $acta['id_cliente'])
        ->where('id_report_type', 6)
        ->where('id_detailreport', 9)
        ->like('titulo_reporte', 'ACTA DE VISITA')
        ->like('observaciones', 'acta_id:' . $acta['id'])
        ->first();

    // Preparar archivo
    $destDir = ROOTPATH . 'public/uploads/' . $nitCliente;
    if (!is_dir($destDir)) {
        mkdir($destDir, 0755, true);
    }

    $fileName = 'acta_visita_' . $acta['id'] . '_' . date('Ymd_His') . '.pdf';
    $destPath = $destDir . '/' . $fileName;
    copy(FCPATH . $pdfPath, $destPath);

    $data = [
        'titulo_reporte' => 'ACTA DE VISITA - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $acta['fecha_visita'],
        'id_detailreport' => 9,
        'id_report_type'  => 6,
        'id_cliente'      => $acta['id_cliente'],
        'estado'          => 'Activo',
        'observaciones'   => 'Generado automaticamente desde modulo de inspecciones. acta_id:' . $acta['id'],
        'enlace'          => base_url('uploads/' . $nitCliente . '/' . $fileName),
        'updated_at'      => date('Y-m-d H:i:s'),
    ];

    if ($existente) {
        // Actualizar reporte existente (nuevo PDF reemplaza al anterior)
        $data['created_at'] = $existente['created_at']; // mantener fecha original
        return $reporteModel->update($existente['id_reporte'], $data);
    } else {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $reporteModel->save($data);
    }
}
```

**Clave:** Se usa `acta_id:{id}` en observaciones como marcador para detectar duplicados.

---

## 7. Almacenamiento de Archivos

El PDF se guarda en **dos ubicaciones** con propositos distintos:

| Ubicacion | Proposito | Quien lo usa |
|-----------|-----------|--------------|
| `uploads/inspecciones/pdfs/acta_visita_{id}_{fecha}.pdf` | Acceso rapido desde la PWA y referencia interna del acta | Modulo de inspecciones |
| `uploads/{nit_cliente}/acta_visita_{id}_{fecha}.pdf` | Registro oficial en el sistema de reportes | Panel admin (Reportes del cliente) |

```
uploads/
├── inspecciones/
│   └── pdfs/
│       ├── acta_visita_1_20260222.pdf    ← referencia del acta
│       └── acta_visita_2_20260222.pdf
├── 800123456/                            ← NIT del cliente
│   ├── acta_visita_1_20260222.pdf        ← reporte del cliente
│   ├── otros_documentos.pdf
│   └── ...
└── 900654321/
    └── ...
```

---

## 8. Comparacion: Antes vs Despues

| Aspecto | Antes (n8n + AppSheet) | Despues (Directo) |
|---------|----------------------|-------------------|
| **Servicios involucrados** | AppSheet → Gmail → n8n → Google Sheets → API | Solo CI4 (1 servidor) |
| **Tiempo de cargue** | 1-5 minutos (depende de n8n) | Instantaneo (misma request) |
| **Punto de fallo** | 5 posibles | 0 externos |
| **Costo adicional** | n8n hosting + Gmail API | $0 |
| **Formato titulo** | `DOC__CLIENTE___FECHA` (fragil) | Construido en PHP (robusto) |
| **Mapeo doc → tipo** | Google Sheets manual | Hardcoded en el controller |
| **Duplicados** | Posibles (si n8n reintenta) | Controlados con `acta_id:` |
| **Trazabilidad** | Logs dispersos (n8n, Gmail) | Log CI4 centralizado |

---

## 9. Consideraciones de Produccion

### Permisos de carpetas

```bash
# En servidor de produccion
mkdir -p /www/wwwroot/phorizontal/enterprisesstph/uploads/inspecciones/pdfs
chown -R www:www /www/wwwroot/phorizontal/enterprisesstph/uploads/inspecciones
chmod -R 775 /www/wwwroot/phorizontal/enterprisesstph/uploads/inspecciones

# Las carpetas uploads/{nit} ya existen y tienen permisos correctos
```

### Limpieza de PDFs antiguos

Si se regenera un PDF, el anterior queda huerfano en disco. Opciones:
1. **Borrar el anterior** al regenerar (recomendado para inspecciones/pdfs/)
2. **Dejar acumular** y limpiar con cron mensual (para uploads/{nit}/)

```php
// Al regenerar, borrar PDF anterior
if (!empty($acta['ruta_pdf']) && file_exists(FCPATH . $acta['ruta_pdf'])) {
    unlink(FCPATH . $acta['ruta_pdf']);
}
```

### Retiro del pipeline n8n

Una vez que el modulo de Acta de Visita este en produccion:

1. **No apagar n8n inmediatamente** — puede que otros documentos aun lo necesiten
2. Remover solo el trigger de "ACTA DE VISITA" del n8n workflow
3. Cuando todas las inspecciones esten migradas, se puede retirar n8n completamente
4. Mantener el robot activo para documentos que no son inspecciones (INFORME MENSUAL, etc.)

---

## 10. Reutilizacion para Futuras Inspecciones

> **Nota (2026-02-24):** La clase `ReporteAutoUpload` descrita abajo NO se implemento como libreria compartida. En su lugar, cada controlador de inspeccion tiene su propio metodo privado `uploadToReportes()` con la misma logica pero `id_detailreport` especifico (9-13). Funciona bien — extraer a libreria queda como refactor opcional.

El metodo `uploadToReportes()` se podria extraer a un **Trait o Helper** reutilizable:

```php
// app/Libraries/ReporteAutoUpload.php
namespace App\Libraries;

class ReporteAutoUpload
{
    /**
     * Registra un PDF de inspeccion en tbl_reporte
     *
     * @param int    $idCliente
     * @param string $pdfPath       Ruta relativa del PDF (uploads/inspecciones/pdfs/...)
     * @param string $tipoDocumento Ej: 'ACTA DE VISITA', 'INSPECCION EXTINTORES'
     * @param int    $idOrigen      ID del registro origen (acta, inspeccion, etc.)
     * @param int    $idReportType  Default 6 (GESTION SST)
     * @param int    $idDetailReport Default 9
     */
    public static function upload(
        int $idCliente,
        string $pdfPath,
        string $tipoDocumento,
        int $idOrigen,
        int $idReportType = 6,
        int $idDetailReport = 9
    ): bool {
        $reporteModel = new \App\Models\ReporteModel();
        $clientModel = new \App\Models\ClientModel();

        $cliente = $clientModel->find($idCliente);
        if (!$cliente) return false;

        $nitCliente = $cliente['nit_cliente'];

        // Buscar duplicado
        $existente = $reporteModel
            ->where('id_cliente', $idCliente)
            ->where('id_report_type', $idReportType)
            ->where('id_detailreport', $idDetailReport)
            ->like('observaciones', 'origen_id:' . $idOrigen)
            ->like('observaciones', 'tipo:' . $tipoDocumento)
            ->first();

        // Copiar a uploads/{nit}/
        $destDir = ROOTPATH . 'public/uploads/' . $nitCliente;
        if (!is_dir($destDir)) mkdir($destDir, 0755, true);

        $safeNombre = strtolower(str_replace(' ', '_', $tipoDocumento));
        $fileName = $safeNombre . '_' . $idOrigen . '_' . date('Ymd_His') . '.pdf';
        copy(FCPATH . $pdfPath, $destDir . '/' . $fileName);

        $fechaVisita = date('Y-m-d'); // se puede pasar como parametro
        $data = [
            'titulo_reporte'  => $tipoDocumento . ' - ' . ($cliente['nombre_cliente'] ?? '') . ' - ' . $fechaVisita,
            'id_detailreport' => $idDetailReport,
            'id_report_type'  => $idReportType,
            'id_cliente'      => $idCliente,
            'estado'          => 'Activo',
            'observaciones'   => "Generado automaticamente. tipo:{$tipoDocumento} origen_id:{$idOrigen}",
            'enlace'          => base_url('uploads/' . $nitCliente . '/' . $fileName),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        if ($existente) {
            return $reporteModel->update($existente['id_reporte'], $data);
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        return $reporteModel->save($data);
    }
}
```

**Uso desde cualquier controlador de inspeccion:**

```php
use App\Libraries\ReporteAutoUpload;

// En ActaVisitaController
ReporteAutoUpload::upload($acta['id_cliente'], $pdfPath, 'ACTA DE VISITA', $acta['id']);

// En futuro ExtintoresController
ReporteAutoUpload::upload($inspeccion['id_cliente'], $pdfPath, 'INSPECCION EXTINTORES', $inspeccion['id']);

// En futuro BotiquinController
ReporteAutoUpload::upload($inspeccion['id_cliente'], $pdfPath, 'INSPECCION BOTIQUINES', $inspeccion['id']);
```

---

## 11. Resumen

| Aspecto | Decision | Razon |
|---------|----------|-------|
| Metodo de cargue | Insercion directa en tbl_reporte desde el controller | Elimina 5 servicios externos, instantaneo |
| IDs para Acta de Visita | id_report_type=6, id_detailreport=9 | Mismo mapeo que usaba n8n/Google Sheets |
| Almacenamiento | Doble: inspecciones/pdfs/ + uploads/{nit}/ | PWA + sistema de reportes |
| Duplicados | Marcador `origen_id:{id}` en observaciones | Evita registros duplicados al regenerar |
| Reutilizacion | Clase `ReporteAutoUpload` en Libraries | Todas las inspecciones futuras la usan |
| Pipeline n8n | Se retira gradualmente por tipo de documento | Sin riesgo de perder otros documentos |
