# Guia Paso a Paso: Crear Documento Tipo C (Data-Driven, sin IA)

> **Uso:** Crear un documento PDF cuyo contenido proviene de **datos existentes en BD**
> (proceso electoral, miembros del comite, formularios capturados por el usuario), NO de
> generacion con IA.
>
> **Validada contra:** `acta_constitucion_copasst` (linea base) y patron a aplicar para
> `socializacion_miembros_*`, `socializacion_cronograma_*`.
>
> **Diferencias con Tipo A y Tipo B:**
> - Tipo A (`secciones_ia`): IA escribe el contenido a partir del contexto del cliente.
> - Tipo B (`programa_con_pta`): IA + datos de PTA + Indicadores.
> - **Tipo C (data-driven):** **NO usa IA**. El contenido se compone de datos ya capturados
>   en el sistema (ej: candidatos electos, fechas de reuniones) o entrada manual.

---

## Cuando Usar Tipo C

**Aplica para:**
- Documentos generados a partir de procesos del aplicativo (elecciones, asistencia, inspecciones).
- Documentos que SOLO requieren un formulario corto del usuario para capturar datos faltantes.
- Documentos donde NO hay nada que "escribir": el formato es fijo y los datos van en plantilla.

**Ejemplos existentes:**
- `acta_constitucion_copasst` / `cocolab` / `brigada` / `vigia`
- `acta_recomposicion_*`
- (Plan futuro: `socializacion_miembros_*`, `socializacion_cronograma_*`)

**NO aplica para:**
- Politicas, manuales, procedimientos, reglamentos. Esos son Tipo A.
- Programas con PTA + Indicadores. Esos son Tipo B.

---

## PASO 1: Disenar (.md PRIMERO)

**Que hacer:** Crear un `.md` con todos los datos del nuevo documento antes de tocar codigo.

| Campo | Que es | Ejemplo |
|-------|--------|---------|
| `tipo_documento` | snake_case unico | `socializacion_miembros_copasst` |
| Nombre completo | Como aparece en encabezados | "Socializacion de miembros COPASST" |
| Codigo BASE | FT-SST-XXX | `FT-SST-201` |
| Fuente de datos | De donde se sacan los datos | `tbl_candidatos` (electos) + `tbl_clientes.logo` |
| Form de entrada | Que captura el usuario | "Mensaje del comite" + "Periodo" |
| Vista preview | Path en `app/Views/...` | `comites_elecciones/socializacion_miembros_preview` |
| Controller que genera | Quien dispara la generacion | `ComitesEleccionesController::generarSocializacion` |
| Que se guarda en `tbl_documentos_sst` | Snapshot JSON del contenido | Ver paso 5 |
| Aparece en reportlist | si/no | si — bajo categoria comites |
| Firmantes requeridos | array de firmantes | `[]` (no se firma, solo se socializa) |

**Output esperado del paso:** un `.md` que cualquier dev pueda leer y saber el alcance.

---

## PASO 2: Crear la Clase PHP

**Donde:** `app/Libraries/DocumentosSSTTypes/{ClasePascalCase}.php`

**Patron — extender una abstracta:**

```php
<?php

namespace App\Libraries\DocumentosSSTTypes;

class SocializacionMiembrosCopasst extends AbstractDocumentoSST
{
    public function getTipoDocumento(): string
    {
        return 'socializacion_miembros_copasst';
    }

    public function getNombre(): string
    {
        return 'Socializacion de miembros COPASST';
    }

    public function getDescripcion(): string
    {
        return 'Documento de socializacion del Comite Paritario de Seguridad y Salud
                en el Trabajo, distribuido a los colaboradores via email.';
    }

    public function getEstandar(): ?string
    {
        return '1.1.6'; // mismo numeral que conformacion COPASST
    }

    public function getSecciones(): array
    {
        // Documentos data-driven suelen tener 1 sola seccion logica.
        return [
            ['numero' => 1, 'nombre' => 'Documento Completo', 'key' => 'documento_completo']
        ];
    }

    public function getFirmantesRequeridos(int $estandares): array
    {
        return []; // No se firma, solo se distribuye
    }

    public function getContextoBase(array $cliente, ?array $contexto): string
    {
        return ''; // No usa IA, no necesita contexto
    }

    public function getContenidoEstatico(string $seccionKey, array $cliente, ?array $contexto, int $estandares, int $anio): string
    {
        return 'Documento generado a partir de los datos del proceso electoral. No admite edicion manual.';
    }

    public function validarSeccion(string $seccionKey, string $contenido): bool
    {
        return true;
    }

    public function getCodigoBase(): string
    {
        return 'FT-SST-201';
    }

    public function getVistaPath(): string
    {
        return 'comites_elecciones/socializacion_miembros_preview';
    }

    /**
     * Genera el snapshot JSON que se guarda en tbl_documentos_sst.contenido.
     * Este JSON es la fuente de verdad para reconstruir el PDF en cualquier momento.
     */
    public function buildContenidoSnapshot(array $datos): string
    {
        $snapshot = [
            'flujo' => 'comite_electoral_socializacion',
            'tipo' => 'miembros',
            'tipo_comite' => 'COPASST',
            'fecha_generacion' => date('Y-m-d H:i:s'),
            'periodo' => [
                'inicio' => $datos['periodo_inicio'] ?? null,
                'fin' => $datos['periodo_fin'] ?? null,
            ],
            'mensaje_comite' => $datos['mensaje_comite'] ?? '',
            'miembros_principales' => $datos['principales'] ?? [],
            'miembros_suplentes' => $datos['suplentes'] ?? [],
            'destinatarios' => $datos['destinatarios'] ?? [],
            'totales' => [
                'enviados_ok' => (int)($datos['enviados_ok'] ?? 0),
                'fallidos' => (int)($datos['fallidos'] ?? 0),
            ],
        ];
        return json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
```

**Si hay 3+ tipos similares** (ej: COPASST, COCOLAB, BRIGADA): crear una **abstracta intermedia**
parametrizada (ver `AbstractActaConstitucion.php`). Cada subclase concreta solo cambia el codigo,
nombre y tipo de comite.

---

## PASO 3: Registrar en el Factory

**Donde:** `app/Libraries/DocumentosSSTTypes/DocumentoSSTFactory.php`

```php
private static array $tiposRegistrados = [
    // ... otros tipos
    'socializacion_miembros_copasst' => SocializacionMiembrosCopasst::class,
    'socializacion_miembros_cocolab' => SocializacionMiembrosCocolab::class,
    // ...
];
```

**Por que:** El Factory es el unico punto desde donde el resto del aplicativo crea instancias
del tipo. Sin esto, llamadas como `DocumentoSSTFactory::crear('socializacion_miembros_copasst')`
fallan con InvalidArgumentException.

---

## PASO 4: Decidir si Registrar en `tbl_doc_tipo_configuracion`

**Hay 2 caminos** segun como quieras que el documento aparezca en el aplicativo:

### Camino 4A — SIN registro en `tbl_doc_tipo_configuracion`

**Cuando:** El documento es transversal a un proceso (como acta_constitucion). NO aparece
en el grid generico de "Documentos SST" de cliente, sino en su propio modulo
(comites-elecciones).

**Que NO hay que hacer:** Insertar en `tbl_doc_tipo_configuracion` /
`tbl_doc_secciones_config` / `tbl_doc_firmantes_config`.

**Que SI hay que hacer:** Asegurar que `tbl_doc_plantillas` tenga el `codigo_sugerido`
para que `getCodigoBase()` funcione (ver paso 5).

### Camino 4B — CON registro completo

**Cuando:** El documento debe aparecer en el grid generico de "Documentos SST" del cliente,
junto a politicas/programas/etc.

**Como:** Crear un script CLI en `scripts/{tipo_documento}_config.php` siguiendo el patron
de `scripts/matriz_epp_config.php`. Inserta en:

- `tbl_doc_tipo_configuracion` (1 registro)
- `tbl_doc_secciones_config` (n registros, 1 por seccion)
- `tbl_doc_firmantes_config` (n registros, 1 por firmante)

**Ejecutar SIEMPRE local primero, luego prod:**

```bash
php scripts/{tipo_documento}_config.php --force         # local
php scripts/{tipo_documento}_config.php --prod --force  # prod
```

> **Para socializacion:** Camino 4A es suficiente. Aparecen bajo el modulo de comites,
> no en el grid de documentos SST del cliente.

---

## PASO 5: Reservar el Codigo en `tbl_doc_plantillas`

**Por que:** `AbstractDocumentoSST::getCodigoBase()` consulta esta tabla para devolver
el `FT-SST-XXX`. Si no esta, devuelve `'DOC-GEN'` (fallback poco util).

**Como:** Script CLI similar a `scripts/perfil_cargo_reservar_codigo_ft.php`:

```php
$pdo->prepare("INSERT INTO tbl_doc_plantillas (codigo_sugerido, tipo_documento, activo)
              VALUES (?, ?, 1)
              ON DUPLICATE KEY UPDATE codigo_sugerido = VALUES(codigo_sugerido)")
    ->execute(['FT-SST-201', 'socializacion_miembros_copasst']);
```

> **Asegurate** de elegir un codigo NO usado. Convencion sugerida:
> - 100-199: docs IA Tipo A
> - 200-299: socializaciones / comunicaciones
> - Verifica con: `SELECT codigo_sugerido FROM tbl_doc_plantillas ORDER BY codigo_sugerido`

---

## PASO 6: Crear la Vista (Preview/PDF)

**Donde:** `app/Views/{vistaPath_definido_en_paso_2}.php`

**Patron:** HTML + estilos inline (Dompdf no soporta CSS externo bien).

**Estructura recomendada:**

```php
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Helvetica', Arial, sans-serif; }
        .header { display:flex; justify-content:space-between; }
        .miembro-card { display:inline-block; width:30%; margin:5px; }
        .miembro-card img { width:100%; height:120px; object-fit:cover; border-radius:6px; }
        /* ... mas estilos */
    </style>
</head>
<body>
    <div class="header">
        <img src="<?= $logoBase64 ?>" style="height:50px;">
        <h1>Miembros del <?= esc($tipoComite) ?></h1>
    </div>
    <p class="periodo"><?= esc($periodoInicio) ?> a <?= esc($periodoFin) ?></p>

    <h2>Representantes del Empleador</h2>
    <?php foreach ($empleadorPrincipales as $m): ?>
        <div class="miembro-card">
            <?php if (!empty($m['foto'])): ?>
                <img src="<?= esc($m['foto_base64']) ?>">
            <?php endif; ?>
            <p><?= esc($m['nombre']) ?></p>
            <p class="cargo"><?= esc($m['rol_comite']) ?></p>
        </div>
    <?php endforeach; ?>

    <!-- ... mismo para trabajadores -->

    <div class="mensaje">
        <?= nl2br(esc($mensajeComite)) ?>
    </div>
</body>
</html>
```

**Tips para PDF con Dompdf:**
- Imagenes del cliente y miembros: convertir a **base64** antes de pasar a la vista
  (Dompdf no resuelve URLs externas en produccion bajo SSL).
- Fuentes: `Helvetica` o `Arial`. Evitar fonts custom (requiere registrarlas en Dompdf).
- Layout: `display:flex` funciona parcialmente. Si fallan layouts complejos, usar `<table>`.
- Tamanos: `letter` o `A4` portrait/landscape. Definir en el controller con
  `$dompdf->setPaper('letter', 'portrait');`

---

## PASO 7: Controller que Dispara la Generacion

**Donde:** Un controller existente del modulo (ej: `ComitesEleccionesController`) o uno nuevo.

**Pattern minimo:**

```php
public function generarSocializacionMiembros(int $idProceso)
{
    $proceso = $this->procesoModel->find($idProceso);
    if (!$proceso) return redirect()->back()->with('error', 'Proceso no encontrado');

    // 1) Recopilar datos
    $candidatos    = $this->candidatoModel->getElectos($idProceso);
    $cliente       = $this->clienteModel->find($proceso['id_cliente']);
    $logoBase64    = $this->imgToBase64(FCPATH . 'uploads/' . $cliente['logo']);
    foreach ($candidatos as &$c) {
        $c['foto_base64'] = $this->imgToBase64(FCPATH . $c['foto']);
    }

    // 2) Capturar form (mensaje del comite, periodo)
    $mensaje       = $this->request->getPost('mensaje_comite');
    $periodoInicio = $this->request->getPost('periodo_inicio');
    $periodoFin    = $this->request->getPost('periodo_fin');

    // 3) Crear instancia del tipo
    $tipo = DocumentoSSTFactory::crear('socializacion_miembros_copasst');

    // 4) Renderizar HTML -> PDF
    $html = view($tipo->getVistaPath(), [
        'cliente' => $cliente,
        'logoBase64' => $logoBase64,
        'tipoComite' => 'COPASST',
        'periodoInicio' => $periodoInicio,
        'periodoFin' => $periodoFin,
        'mensajeComite' => $mensaje,
        'empleadorPrincipales' => array_filter($candidatos, fn($c) => $c['representacion'] === 'empleador'),
        'trabajadoresPrincipales' => array_filter($candidatos, fn($c) => $c['representacion'] === 'trabajador'),
    ]);

    $dompdf = new \Dompdf\Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('letter', 'portrait');
    $dompdf->render();
    $pdfContent = $dompdf->output();

    // 5) Guardar archivo en disco
    $rutaRel = "uploads/comites/{$proceso['id_cliente']}/socializacion_miembros_copasst_" . date('Ymd_His') . ".pdf";
    file_put_contents(FCPATH . $rutaRel, $pdfContent);

    // 6) Snapshot JSON + INSERT en tbl_documentos_sst
    $snapshot = $tipo->buildContenidoSnapshot([
        'periodo_inicio' => $periodoInicio,
        'periodo_fin' => $periodoFin,
        'mensaje_comite' => $mensaje,
        'principales' => array_map(fn($c) => ['nombre' => $c['nombres'].' '.$c['apellidos'], 'rol' => $c['rol_comite']], $candidatos),
    ]);

    $codigoCompleto = $tipo->getCodigoBase() . '-' . $this->generarConsecutivo($proceso['id_cliente'], $tipo->getTipoDocumento());

    $idDoc = $this->documentoModel->insert([
        'id_cliente'      => $proceso['id_cliente'],
        'tipo_documento'  => $tipo->getTipoDocumento(),
        'codigo'          => $codigoCompleto,
        'titulo'          => $tipo->getNombre() . ' - ' . $cliente['nombre_cliente'],
        'contenido'       => $snapshot,         // JSON con todos los datos
        'archivo_pdf'     => $rutaRel,
        'estado'          => 'generado',
        'created_by'      => session()->get('id_usuario'),
    ], true);

    return redirect()->to("/comites-elecciones/{$proceso['id_cliente']}/proceso/{$idProceso}")
        ->with('success', "Documento generado: {$codigoCompleto}");
}
```

---

## PASO 8: Visibilidad en el Reportlist

**Donde:** `app/Controllers/DocumentacionController.php`

**Que hacer:** Si quieres que el documento aparezca en la carpeta de docs del cliente
(la que se ve en `/client/{id}/documentacion-fases`), agregar el `tipo_documento` al
mapeo de `tipoCarpetaFases`:

```php
// DocumentacionController.php (alrededor de la linea 320)
'1.1.6' => 'conformacion_copasst', // ya existia para acta_constitucion_copasst
```

Si tu nuevo doc va a la misma carpeta que el acta de conformacion, no hace falta tocar
nada. Si va en su propia carpeta, agregar el mapeo.

---

## PASO 9: Verificar

**Checklist de verificacion:**

- [ ] La clase aparece en `DocumentoSSTFactory::$tiposRegistrados`.
- [ ] `php spark routes | grep {tipo_documento}` muestra la ruta del controller que genera.
- [ ] `SELECT codigo_sugerido FROM tbl_doc_plantillas WHERE tipo_documento='{tipo}'` devuelve fila.
- [ ] Generar el documento de prueba con un cliente real (idealmente el cliente de validacion
      en local) y abrir el PDF resultante.
- [ ] Verificar que la fila aparezca en `tbl_documentos_sst`.
- [ ] Verificar que el reportlist muestre el documento (si decidiste path 8).
- [ ] Repetir contra produccion DESPUES de validar local.

---

## Errores Frecuentes

| Sintoma | Causa probable | Fix |
|---------|----------------|-----|
| `InvalidArgumentException: Tipo {x} no existe` | Olvidaste registrar en Factory | Anadir en `$tiposRegistrados` |
| `getCodigoBase()` devuelve `'DOC-GEN'` | No reservaste el codigo en `tbl_doc_plantillas` | Ejecutar paso 5 |
| Imagenes no aparecen en el PDF | Dompdf no resuelve URLs externas | Convertir a base64 antes de pasar a la vista |
| El documento no aparece en el reportlist | Falta mapeo en `DocumentacionController` | Anadir en el switch de `tipoCarpetaFases` |
| Layout roto en PDF pero OK en navegador | Dompdf interpreta CSS distinto | Simplificar layout, usar `<table>` para grid |
| `tipo_documento` distinto entre BD y URL | Mezclaste snake_case con kebab-case | Reglas: en BD/Factory snake_case, en URLs internas snake_case, en URLs publicas kebab-case |

---

## Diferencias clave Tipo A / B / C

| Aspecto | Tipo A | Tipo B | Tipo C |
|---------|--------|--------|--------|
| Flujo | `secciones_ia` | `programa_con_pta` | `comite_electoral` u otro |
| Usa IA | si | si | **no** |
| `tbl_doc_secciones_config` | 5-12 secciones con prompts | varias con prompts | 0 o 1 (no hay prompts) |
| `tbl_doc_firmantes_config` | 2-3 firmantes | 2-3 firmantes | a veces 0 |
| Datos del documento | IA genera desde contexto | IA + PTA + Indicadores | Datos ya existentes en BD + form corto |
| Vista | `documentos_sst/{tipo}.php` | `documentos_sst/{tipo}.php` | path custom (ej `comites_elecciones/...`) |
| Generador | `DocumentosController::generar` | `DocumentosController::generar/parte/N` | controller especifico del modulo |
| Editable post-generacion | si (regenerar secciones) | si | usualmente no |
| Aparece en grid generico de docs | si | si | depende (camino 4A o 4B) |

---

## Referencias

- Linea base concreta: `app/Libraries/DocumentosSSTTypes/AbstractActaConstitucion.php`
- Factory: `app/Libraries/DocumentosSSTTypes/DocumentoSSTFactory.php`
- Reportlist: `app/Controllers/DocumentacionController.php` (mapeo `tipoCarpetaFases`)
- Generacion data-driven: `app/Controllers/ComitesEleccionesController.php` (busca `acta_constitucion`)
- Ejemplo registro BD completo: `scripts/matriz_epp_config.php`
- Ejemplo reservar codigo: `scripts/perfil_cargo_reservar_codigo_ft.php`
