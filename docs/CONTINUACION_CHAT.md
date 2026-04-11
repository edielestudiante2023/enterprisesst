# Continuacion Chat - Modulo Investigacion Accidentes + Fix ReportList

> Fecha: 2026-04-11 | Estado: EN PROGRESO

## PRIORIDAD 1: Fix uploadToReportes en investigacion de accidentes

Al finalizar una investigacion de accidente/incidente, el PDF NO se esta subiendo a reportlist (`tbl_reporte`). Esto afecta tanto al controller del consultor como al del miembro COPASST.

### Como funciona uploadToReportes (patron correcto - locativas del consultor)

Archivo de referencia: `app/Controllers/Inspecciones/InspeccionLocativaController.php` lineas 527-574

```php
private function uploadToReportes(array $inspeccion, string $pdfPath): bool
{
    $reporteModel = new ReporteModel();  // tabla: tbl_reporte
    $clientModel = new ClientModel();
    $cliente = $clientModel->find($inspeccion['id_cliente']);
    $nitCliente = $cliente['nit_cliente'];

    // Buscar si ya existe (para update en vez de duplicar)
    $existente = $reporteModel
        ->where('id_cliente', $inspeccion['id_cliente'])
        ->where('id_report_type', 6)        // Tipo: Inspecciones
        ->where('id_detailreport', 10)       // Detalle: Inspeccion Locativa
        ->like('observaciones', 'insp_locativa_id:' . $inspeccion['id'])
        ->first();

    // Copiar PDF a uploads/{nit_cliente}/
    $destDir = ROOTPATH . 'public/uploads/' . $nitCliente;
    mkdir($destDir, 0755, true);  // si no existe
    $fileName = 'inspeccion_locativa_' . $inspeccion['id'] . '_' . date('Ymd_His') . '.pdf';
    copy(FCPATH . $pdfPath, $destDir . '/' . $fileName);

    $data = [
        'titulo_reporte'  => 'INSPECCION LOCATIVA - ' . $cliente['nombre_cliente'] . ' - ' . $inspeccion['fecha_inspeccion'],
        'id_detailreport' => 10,
        'id_report_type'  => 6,
        'id_cliente'      => $inspeccion['id_cliente'],
        'estado'          => 'CERRADO',
        'observaciones'   => 'Generado automaticamente. insp_locativa_id:' . $inspeccion['id'],
        'enlace'          => base_url('uploads/' . $nitCliente . '/' . $fileName),
        'updated_at'      => date('Y-m-d H:i:s'),
    ];

    if ($existente) {
        return $reporteModel->update($existente['id_reporte'], $data);
    }
    $data['created_at'] = date('Y-m-d H:i:s');
    return $reporteModel->save($data);
}
```

### Campos clave de tbl_reporte (ReporteModel)

```php
protected $allowedFields = [
    'titulo_reporte', 'id_detailreport', 'enlace', 'estado',
    'observaciones', 'id_cliente', 'created_at', 'updated_at', 'id_report_type'
];
```

### IDs de referencia para cada modulo

| Modulo | id_report_type | id_detailreport | tag en observaciones |
|--------|---------------|-----------------|----------------------|
| Inspeccion Locativa | 6 | 10 | insp_locativa_id:{id} |
| Pausas Activas | 6 | 10 | pausas_activas_id:{id} |
| Investigacion AT/IT | 7 | 38 | investigacion_accidente_id:{id} |

El `id_report_type=7` y `id_detailreport=38` ya fueron creados en BD por el script `app/SQL/crear_modulo_investigacion_accidente.php`.

### Archivos que tienen uploadToReportes y hay que verificar

1. `app/Controllers/Inspecciones/InvestigacionAccidenteController.php` - consultor
2. `app/Controllers/MiembroInvestigacionAccidenteController.php` - miembro COPASST
3. `app/Controllers/Inspecciones/InspeccionPausasActivasController.php` - consultor pausas
4. `app/Controllers/MiembroPausasActivasController.php` - miembro pausas

Verificar que:
- El metodo `uploadToReportes` existe y se llama en `finalizar()`
- Los `id_report_type` y `id_detailreport` son correctos
- El `copy()` del PDF usa la ruta correcta: `FCPATH . $pdfPath` → `ROOTPATH . 'public/uploads/' . $nitCliente`
- El `enlace` usa `base_url('uploads/' . $nitCliente . '/' . $fileName)`

## PRIORIDAD 2: Lo que ya se hizo (modulo investigacion accidentes)

### Base de datos (LOCAL + PROD OK)

- Script: `app/SQL/crear_modulo_investigacion_accidente.php`
- `tbl_investigacion_accidente` (tabla principal)
- `tbl_investigacion_testigos`
- `tbl_investigacion_evidencia`
- `tbl_investigacion_medidas` (plan accion)
- detail_report id=38, report_type id=7

### Modelos creados

- `app/Models/InvestigacionAccidenteModel.php`
- `app/Models/InvestigacionTestigoModel.php`
- `app/Models/InvestigacionEvidenciaModel.php`
- `app/Models/InvestigacionMedidaModel.php`

### Controllers creados

- `app/Controllers/Inspecciones/InvestigacionAccidenteController.php` (consultor) - CRUD + firmas canvas + WhatsApp token + PDF + reportlist + email
- `app/Controllers/MiembroInvestigacionAccidenteController.php` (miembro COPASST) - CRUD + PDF + reportlist + notificarConsultor

### Vistas creadas

Consultor:
- `app/Views/inspecciones/investigacion_accidente/list.php`
- `app/Views/inspecciones/investigacion_accidente/form.php` (9 secciones accordion)
- `app/Views/inspecciones/investigacion_accidente/view.php`
- `app/Views/inspecciones/investigacion_accidente/firma.php` (canvas + WhatsApp)
- `app/Views/inspecciones/investigacion_accidente/firma_remota.php` (publica)
- `app/Views/inspecciones/investigacion_accidente/pdf.php` (DOMPDF condicional)

Miembro:
- `app/Views/inspecciones/miembro/investigacion_list.php`
- `app/Views/inspecciones/miembro/investigacion_form.php`
- `app/Views/inspecciones/miembro/investigacion_view.php`

### Rutas agregadas en Routes.php

- Publicas: firma remota investigacion (sin auth)
- Grupo miembro: CRUD + finalizar + PDF
- Grupo inspecciones (consultor): CRUD + firmas + token + PDF + email + regenerar

### Dashboard miembro actualizado

Card "Investigacion AT/IT" ya agregada en `app/Views/actas/miembro_auth/dashboard.php`

## PRIORIDAD 3: Que falta

1. **Fix uploadToReportes** en los 4 controllers listados arriba
2. **Testing funcional**: probar flujos completos
3. **Deploy**: git add . → commit → checkout main → merge cycloid → push → checkout cycloid

## Reglas del proyecto

- NO hardcodear textos autopromocionales en PDFs
- Solo boton galeria, NO boton camara en formularios
- Textos genericos (organizacion, instalaciones), no propiedad horizontal
- EnterpriseSST en vez de Cycloid Talent en todo
- BD: scripts PHP CLI, primero LOCAL, luego --prod
- Deploy: git add . → commit → checkout main → merge cycloid → push → checkout cycloid
