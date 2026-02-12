# INSTRUCTIVO: Arboles de Decision — Rutas de Generacion de Documentos

## Resumen

El sistema tiene **2 rutas principales** para documentos, con **3 variantes** de interaccion:

| Variante | Ruta entrada | Accion principal | Base de datos | Usa IA |
|----------|-------------|------------------|---------------|--------|
| **A: Soporte/Adjunto** | `/documentacion/carpeta/{id}` | Adjuntar archivo o enlace | `tbl_documentos_sst` (adjunto) | NO |
| **B: Documento con IA** | `/documentos/generar/{tipo}/{id}` | Generar secciones con IA | `tbl_documentos_sst` (JSON secciones) | SI |
| **C: Documento Normativo** | `/documentos/generar/{tipo}/{id}` | Editar contenido estatico pre-cargado | `tbl_documentos_sst` (JSON secciones) | NO |

---

## Arbol de Decision General

```
Usuario navega a /documentacion/carpeta/{idCarpeta}
         |
         v
DocumentacionController::carpeta()
         |
         v
determinarTipoCarpetaFases($carpeta)
         |
         +-- return null ──────────────> Vista: _tipos/generica.php
         |                                Boton: "Nuevo Documento" (upload manual)
         |                                BD: tbl_doc_documentos (legacy)
         |                                *** VARIANTE LEGACY - fuera del sistema SST ***
         |
         +-- return 'tipo_carpeta' ───> Vista: _tipos/{tipo_carpeta}.php
                     |
                     v
              La vista decide el boton segun el tipo:
                     |
                     +── Tipo SOPORTE ──────────> Boton: "Adjuntar Soporte"
                     |   (agua_servicios,          Modal: subir archivo/enlace
                     |    entrega_epp,              POST a endpoint especifico
                     |    brigada_emergencias,      BD: tbl_documentos_sst (archivo)
                     |    etc.)                     *** VARIANTE A ***
                     |
                     +── Tipo FORMULARIO ───────> Boton: "Abrir [nombre]"
                     |   (presupuesto_sst)         Redirige a formulario propio
                     |                              BD: tbl_documentos_sst
                     |                              *** VARIANTE A (sub-tipo) ***
                     |
                     +── Tipo DOCUMENTO IA ─────> Boton: "Crear con IA"
                         (capacitacion_sst,        href: /documentos/generar/{tipo}/{idCliente}
                          promocion_prevencion,     *** VARIANTE B o C ***
                          politicas_2_1_1,                    |
                          plan_objetivos_metas,               v
                          mecanismos_comunicacion,   DocumentosSSTController::generarConIA()
                          manual_convivencia,                 |
                          matriz_legal,                       v
                          induccion_reinduccion)     DocumentoSSTFactory::crear($tipo)
                                                              |
                                                              v
                                                     handler->requiereGeneracionIA()
                                                              |
                                                     +── true ──> VARIANTE B (con IA)
                                                     |            Boton: "Generar Todo con IA"
                                                     |            SweetAlert verificacion
                                                     |            Toast progress/ia/database
                                                     |
                                                     +── false ─> VARIANTE C (normativo)
                                                                  Badge: "Contenido Normativo"
                                                                  Pre-carga contenido estatico
                                                                  Editor sin boton IA
```

---

## Variante A: Soporte/Adjunto (Sin IA)

### Cuando Aplica

Carpetas que requieren **evidencia documental** pero NO generan contenido con IA. El usuario sube un archivo (PDF, imagen, Excel) o pega un enlace externo.

### Ejemplos de Numerales

| Codigo | Numeral | tipoCarpetaFases |
|--------|---------|------------------|
| 3.1.8 | Agua potable, servicios sanitarios | `agua_servicios_sanitarios` |
| 3.1.9 | Eliminacion de residuos | `eliminacion_residuos` |
| 4.1.4 | Mediciones ambientales | `mediciones_ambientales` |
| 4.2.1 | Medidas de prevencion y control | `medidas_prevencion_control` |
| 4.2.2 | Verificacion medidas prevencion | `verificacion_medidas_prevencion` |
| 4.2.6 | Entrega de EPP | `entrega_epp` |
| 5.1.1 | Plan de emergencias | `plan_emergencias` |
| 5.1.2 | Brigada de emergencias | `brigada_emergencias` |
| 6.1.3 | Revision por la direccion | `revision_direccion` |
| 6.1.4 | Planificacion auditorias COPASST | `planificacion_auditorias_copasst` |
| 3.1.3 | Informacion al medico perfiles | `informacion_medico_perfiles` |
| 3.1.4 | Evaluaciones medicas ocupacionales | `evaluaciones_medicas` |
| 3.1.5 | Custodia historias clinicas | `custodia_historias_clinicas` |
| 1.2.3 | Responsables curso 50 horas | `responsables_curso_50h` |
| 2.3.1 | Evaluacion de prioridades | `evaluacion_prioridades` |
| 2.6.1 | Rendicion sobre el desempeno | `rendicion_desempeno` |
| 1.1.3 | Presupuesto SST | `presupuesto_sst` (formulario propio) |
| 1.1.4 | Afiliacion al SRL | `afiliacion_srl` |
| 2.5.1 | Archivo o retencion documental del SG-SST | `archivo_documental` (listado maestro — muestra TODOS los documentos sin filtro, con enlace a Variante C `procedimiento_control_documental`) |

### Flujo Tecnico

```
GET /documentacion/carpeta/316
         |
         v
DocumentacionController::carpeta(316)
         |
         v
$tipoCarpetaFases = 'agua_servicios_sanitarios'
         |
         v
Carga documentosSSTAprobados filtrado por tipo_documento = 'soporte_agua_servicios'
         |
         v
Vista: _tipos/agua_servicios_sanitarios.php
         |
         v
Botones disponibles:
  [Adjuntar Soporte] → Modal con formulario de carga
         |
         v
POST /documentos-sst/adjuntar-soporte-agua
  - archivo_soporte (file) O url_externa (link)
  - descripcion, anio, observaciones
         |
         v
Inserta en tbl_documentos_sst con:
  tipo_documento = 'soporte_agua_servicios'
  archivo_pdf = ruta del archivo O url_externa = enlace
```

### Caracteristicas

- **NO hay editor de secciones** — el usuario solo sube/enlaza archivos
- **NO hay Factory** — la vista maneja directamente el formulario
- **NO hay SweetAlert de verificacion** — no se consulta contexto del cliente
- **NO hay toasts de generacion** — solo respuesta directa del POST
- Cada soporte se muestra en una tabla simple con: codigo, descripcion, ano, tipo (archivo/enlace), acciones (ver/descargar)

---

## Variante B: Documento con IA

### Cuando Aplica

Documentos formales del SG-SST cuyo contenido es **generado por IA** alimentada con datos reales del cliente (3-Partes: PTA + Indicadores + Contexto).

### Ejemplos de Numerales

| Codigo | Numeral | tipo_documento (snake_case) | Clase Handler |
|--------|---------|----------------------------|---------------|
| 1.2.1 | Programa de Capacitacion | `programa_capacitacion` | `ProgramaCapacitacion` |
| 3.1.2 | Programa Promocion y Prevencion | `programa_promocion_prevencion_salud` | `ProgramaPromocionPrevencionSalud` |
| 2.1.1 | Politica SST General | `politica_sst_general` | `PoliticaSstGeneral` |
| 2.1.1 | Politica Alcohol y Drogas | `politica_alcohol_drogas` | `PoliticaAlcoholDrogas` |
| 2.1.1 | Politica Acoso Laboral | `politica_acoso_laboral` | `PoliticaAcosoLaboral` |
| 2.1.1 | Politica Violencias Genero | `politica_violencias_genero` | `PoliticaViolenciasGenero` |
| 2.1.1 | Politica Discriminacion | `politica_discriminacion` | `PoliticaDiscriminacion` |
| 2.1.1 | Politica Prevencion Emergencias | `politica_prevencion_emergencias` | `PoliticaPrevencionEmergencias` |
| 1.1.8 | Manual Convivencia Laboral | `manual_convivencia_laboral` | `ManualConvivenciaLaboral` |
| 2.2.1 | Plan Objetivos y Metas | `plan_objetivos_metas` | `PlanObjetivosMetas` |
| 2.8.1 | Mecanismos de Comunicacion | `mecanismos_comunicacion_sgsst` | `MecanismosComunicacionSgsst` |
| 1.2.2 | Induccion y Reinduccion | `programa_induccion_reinduccion` | `ProgramaInduccionReinduccion` |
| 2.7.1 | Procedimiento Matriz Legal | `procedimiento_matriz_legal` | `ProcedimientoMatrizLegal` |

### Flujo Tecnico

```
GET /documentacion/carpeta/327
         |
         v
DocumentacionController::carpeta(327)
         |
         v
$tipoCarpetaFases = 'capacitacion_sst'
         |
         v
Vista: _tipos/capacitacion_sst.php
  - Verifica si hay documento aprobado para el ano actual
  - Verifica si las fases previas estan completas (fasesInfo)
         |
         +── Fases incompletas ──> Boton "Crear con IA" [DISABLED, candado]
         |
         +── Fases completas, no hay doc actual ──> Boton "Crear con IA" [ACTIVO]
         |
         +── Ya hay doc aprobado ──> NO muestra boton (ya existe)
         |
         v
Click en "Crear con IA"
         |
         v
GET /documentos/generar/programa_capacitacion/18
         |
         v
DocumentosSSTController::generarConIA('programa_capacitacion', 18)
         |
         v
1. DocumentoConfigService::obtenerTipoDocumento('programa_capacitacion')
   → Obtiene secciones, nombre, configuracion de tbl_doc_tipo_configuracion
         |
         v
2. DocumentoSSTFactory::crear('programa_capacitacion')
   → Instancia ProgramaCapacitacion (handler)
         |
         v
3. handler->requiereGeneracionIA() → true
   → $usaIA = true
         |
         v
4. Busca documento existente en tbl_documentos_sst
   → Si existe: carga JSON de secciones con contenido
   → Si no existe: secciones vacias
         |
         v
5. Calcula progreso:
   - $seccionesGuardadas / $totalSecciones
   - $seccionesAprobadas / $totalSecciones
   - $todasSeccionesListas = todo guardado Y aprobado
         |
         v
Vista: documentos_sst/generar_con_ia.php con $usaIA = true
         |
         v
Botones visibles:
  [Generar Todo con IA]  ← Genera todas las secciones
  [Generar con IA] por seccion ← Genera seccion individual
  [Guardar] / [Aprobar] por seccion
  [Vista Previa] ← Solo cuando todasSeccionesListas = true
```

### Flujo de Generacion IA (seccion individual)

```
Click "Generar con IA" en una seccion
         |
         v
verificacionConfirmada?
         |
         +── false (primera vez) ──> SweetAlert de verificacion
         |       GET /documentos/previsualizar-datos/{tipo}/{idCliente}
         |       Muestra: PTA + Indicadores + Contexto
         |       Click "Generar con IA" ──> verificacionConfirmada = true
         |
         +── true (ya confirmo) ──> Ejecuta directamente
         |
         v
Toast progress: "Generando... Seccion X"
         |
         v
POST /documentos/generar-seccion
  Body: { tipo, seccion, id_cliente, anio, contexto_adicional }
         |
         v
Backend: Handler->getContextoBase() consulta:
  - tbl_pta_cliente (filtrado por tipo_servicio)
  - tbl_indicadores_sst (filtrado por categoria)
  - tbl_cliente_contexto_sst (contexto general)
         |
         v
Envia prompt a OpenAI/Anthropic con datos reales
         |
         v
Toast progress se cierra
Toast ia: "Contenido Generado"
Toast database: "Bases de Datos Consultadas" (metadata de tablas)
```

### Caracteristicas

- **Editor por secciones** con contenido editable (textarea/editor)
- **Factory pattern** para instanciar el handler correcto
- **SweetAlert de verificacion** muestra datos ANTES de generar (una sola vez por sesion)
- **Sistema de toasts** para feedback: progress, ia, database, error con reintentar
- **modoBatch** para generacion masiva ("Generar Todo")
- **Versionamiento** con tbl_doc_versiones_sst
- **Vista Previa** solo habilitada cuando TODAS las secciones estan guardadas Y aprobadas

---

## Variante C: Documento Normativo (Sin IA, mismo editor)

### Cuando Aplica

Documentos que usan el **mismo editor por secciones** que la Variante B, pero cuyo contenido es **estatico/normativo** (texto legal predefinido). NO consulta la IA — el handler pre-carga el contenido con `getContenidoEstatico()`.

### Como se Distingue

El handler del documento implementa `requiereGeneracionIA()` retornando `false`:

```php
class ProcedimientoControlDocumental extends AbstractDocumentoSST
{
    public function requiereGeneracionIA(): bool
    {
        return false; // Contenido normativo, no necesita IA
    }

    public function getContenidoEstatico(string $key, ...): string
    {
        // Retorna texto legal predefinido segun la seccion
    }
}
```

### Flujo Tecnico

```
GET /documentos/generar/procedimiento_control_documental/18
         |
         v
DocumentosSSTController::generarConIA('procedimiento_control_documental', 18)
         |
         v
1. DocumentoSSTFactory::crear('procedimiento_control_documental')
   → Instancia ProcedimientoControlDocumental
         |
         v
2. handler->requiereGeneracionIA() → false
   → $usaIA = false
         |
         v
3. Si NO existe documento en BD:
   → Llama handler->getContenidoEstatico() para cada seccion
   → Pre-carga el contenido normativo
   → $seccionesGuardadas = $totalSecciones (ya tienen contenido)
         |
         v
Vista: documentos_sst/generar_con_ia.php con $usaIA = false
         |
         v
Diferencias visuales:
  - Titulo: "Editar [nombre]" (no "Generar con IA")
  - Badge: "Contenido Normativo" (en vez de boton "Generar Todo con IA")
  - NO hay botones "Generar con IA" por seccion
  - SIN SweetAlert de verificacion
  - SIN toasts de generacion IA
  - SI tiene: Guardar, Aprobar, Vista Previa
```

### Caracteristicas

- **Mismo layout** que Variante B (editor por secciones)
- Contenido **pre-cargado** por el handler (texto normativo/legal)
- El usuario puede **editar** el contenido pre-cargado antes de guardar
- **NO hay** botones de generacion IA ni SweetAlert
- **SI hay** funcionalidad de Guardar, Aprobar, Vista Previa, Versionamiento
- Util para documentos como: Procedimiento de Control Documental, donde el texto es estandar pero se puede personalizar

---

## Punto de Decision Clave: determinarTipoCarpetaFases()

Esta funcion en `DocumentacionController` (lineas 555-780) es el **primer punto de decision**. Mapea la carpeta a un tipo basandose en el codigo o nombre:

```php
protected function determinarTipoCarpetaFases(array $carpeta): ?string
{
    $nombre = strtolower($carpeta['nombre'] ?? '');
    $codigo = strtolower($carpeta['codigo'] ?? '');

    if ($codigo === '1.2.1') return 'capacitacion_sst';
    if ($codigo === '3.1.8') return 'agua_servicios_sanitarios';
    // ... mas de 30 mapeos
    return null; // Carpeta generica (sin tipificacion)
}
```

**Regla:** Si retorna `null`, la carpeta usa la vista generica (legacy). Si retorna un string, busca la vista especifica en `_tipos/{tipo}.php`.

---

## Punto de Decision Clave: La Vista _tipos/{tipo}.php

Cada vista decide **que boton mostrar**. Esta es la logica que determina si el usuario ira a la Variante A o B:

### Patron: Vista con boton "Crear con IA" → Variante B

```php
// _tipos/capacitacion_sst.php
<a href="<?= base_url('documentos/generar/programa_capacitacion/' . $cliente['id_cliente']) ?>"
   class="btn btn-success">
    <i class="bi bi-magic me-1"></i>Crear con IA
</a>
```

### Patron: Vista con boton "Adjuntar Soporte" → Variante A

```php
// _tipos/agua_servicios_sanitarios.php
<button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#modalAdjuntar">
    <i class="bi bi-cloud-upload me-1"></i>Adjuntar Soporte
</button>
```

### Patron: Vista con dropdown de multiples docs IA → Variante B (multiples)

```php
// _tipos/politicas_2_1_1.php
<div class="dropdown">
    <button class="btn btn-primary dropdown-toggle">Nueva Politica</button>
    <ul class="dropdown-menu">
        <li><a href="/documentos/generar/politica_sst_general/{id}">Politica SST General</a></li>
        <li><a href="/documentos/generar/politica_alcohol_drogas/{id}">Politica Alcohol y SPA</a></li>
        <!-- 6 politicas en total -->
    </ul>
</div>
```

---

## Punto de Decision Clave: requiereGeneracionIA()

**Dentro** de la Variante B, el handler puede declarar que NO usa IA:

```php
// DocumentosSSTController::generarConIA(), lineas 246-276
$documentoHandler = DocumentoSSTFactory::crear($tipo);

$usaIA = true; // Por defecto SI usa IA

if (method_exists($documentoHandler, 'requiereGeneracionIA')) {
    $usaIA = $documentoHandler->requiereGeneracionIA();
}

if (!$usaIA && !$documentoExistente) {
    // Pre-cargar contenido estatico
    foreach ($secciones as &$seccion) {
        $seccion['contenido'] = $documentoHandler->getContenidoEstatico(...);
    }
}
```

Esto bifurca entre:
- `$usaIA = true` → Variante B (botones IA, SweetAlert, toasts)
- `$usaIA = false` → Variante C (editor sin IA, contenido pre-cargado)

---

## Mapa de Carpetas por Variante

### CARPETAS DE CARGA DE SOPORTES (Variante A)

Estas carpetas NO generan contenido — el usuario sube evidencia:

| Codigo | Numeral | Vista | Boton |
|--------|---------|-------|-------|
| 1.1.3 | Presupuesto SST | presupuesto_sst | "Abrir Presupuesto SST" |
| 1.1.4 | Afiliacion SRL | afiliacion_srl | "Adjuntar" |
| 1.2.3 | Curso 50 horas | responsables_curso_50h | "Adjuntar" |
| 2.3.1 | Evaluacion prioridades | evaluacion_prioridades | "Adjuntar" |
| 2.5.1 | Archivo documental | archivo_documental | (lista todos los docs) |
| 2.6.1 | Rendicion desempeno | rendicion_desempeno | "Adjuntar" |
| 3.1.3 | Perfiles medico | informacion_medico_perfiles | "Adjuntar" |
| 3.1.4 | Evaluaciones medicas | evaluaciones_medicas | "Adjuntar" |
| 3.1.5 | Custodia HC | custodia_historias_clinicas | "Adjuntar" |
| 3.1.8 | Agua y sanitarios | agua_servicios_sanitarios | "Adjuntar Soporte" |
| 3.1.9 | Eliminacion residuos | eliminacion_residuos | "Adjuntar" |
| 4.1.4 | Mediciones ambientales | mediciones_ambientales | "Adjuntar" |
| 4.2.1 | Medidas prevencion | medidas_prevencion_control | "Adjuntar" |
| 4.2.2 | Verificacion medidas | verificacion_medidas_prevencion | "Adjuntar" |
| 4.2.6 | Entrega EPP | entrega_epp | "Adjuntar" |
| 5.1.1 | Plan emergencias | plan_emergencias | "Adjuntar" |
| 5.1.2 | Brigada emergencias | brigada_emergencias | "Adjuntar" |
| 6.1.3 | Revision direccion | revision_direccion | "Adjuntar" |
| 6.1.4 | Auditorias COPASST | planificacion_auditorias_copasst | "Adjuntar" |

### CARPETAS CON GENERACION IA (Variante B)

Estas carpetas tienen boton "Crear con IA" que lleva a `generar_con_ia.php`:

| Codigo | Numeral | tipo_documento | Handler |
|--------|---------|----------------|---------|
| 1.2.1 | Programa Capacitacion | programa_capacitacion | ProgramaCapacitacion |
| 3.1.2 | Promocion y Prevencion | programa_promocion_prevencion_salud | ProgramaPromocionPrevencionSalud |
| 2.1.1 | 6 Politicas SST | politica_sst_general, politica_alcohol_drogas, etc. | 6 clases de politicas |
| 1.1.8 | Manual Convivencia | manual_convivencia_laboral | ManualConvivenciaLaboral |
| 2.2.1 | Plan Objetivos y Metas | plan_objetivos_metas | PlanObjetivosMetas |
| 2.8.1 | Mecanismos Comunicacion | mecanismos_comunicacion_sgsst | MecanismosComunicacionSgsst |
| 1.2.2 | Induccion Reinduccion | programa_induccion_reinduccion | ProgramaInduccionReinduccion |
| 2.7.1 | Matriz Legal | procedimiento_matriz_legal | ProcedimientoMatrizLegal |

### CARPETAS CON EDITOR SIN IA (Variante C)

Usan el mismo editor por secciones pero con contenido normativo pre-cargado:

| tipo_documento | Handler | Contenido |
|----------------|---------|-----------|
| procedimiento_control_documental | ProcedimientoControlDocumental | Texto legal estandar de control documental |

### CARPETAS HIBRIDAS (Variante A + B)

Combinan **carga de soportes** (Variante A) con **generacion de documento formal con IA** (Variante B) en la misma vista. El usuario puede tanto adjuntar evidencia como generar un documento formal.

| Codigo | Numeral                          | tipoCarpetaFases                | tipo_documento IA                    | Handler                          | Soportes                    |
|--------|----------------------------------|---------------------------------|--------------------------------------|----------------------------------|-----------------------------|
| 3.1.1  | Diagnostico condiciones de salud | `diagnostico_condiciones_salud` | `procedimiento_evaluaciones_medicas` | ProcedimientoEvaluacionesMedicas | `soporte_diagnostico_salud` |

**Caracteristicas:**

- La vista `_tipos/{tipoCarpetaFases}.php` tiene DOS secciones: boton "Crear con IA" (arriba) y seccion "Soportes Adicionales" (abajo)
- El boton "Crear con IA" apunta a `/documentos/generar/{tipo_documento_IA}/{idCliente}`
- Los soportes se adjuntan via modal con POST a endpoint especifico
- `DocumentacionController::carpeta()` filtra `documentosSSTAprobados` por AMBOS tipos: el tipo_documento IA y el tipo de soporte
- Los soportes se consultan por separado en `$soportesAdicionales`

---

## Diagrama Resumen de Controladores

```
                    /documentacion/carpeta/{id}
                              |
                    DocumentacionController
                              |
               determinarTipoCarpetaFases()
                    /         |         \
                   /          |          \
                null     'soporte_X'   'documento_Y'
                  |           |              |
            generica.php  tipo_soporte.php  tipo_documento.php
            (legacy)      (Variante A)      (tiene link a /documentos/generar/...)
                                                     |
                                           DocumentosSSTController
                                                     |
                                             generarConIA()
                                                     |
                                              Factory::crear()
                                                     |
                                          requiereGeneracionIA()?
                                              /            \
                                           true           false
                                            |               |
                                      VARIANTE B       VARIANTE C
                                      (con IA)         (normativo)
```

---

## Checklist: Como Saber en Que Variante Cae un Numeral Nuevo

1. **El numeral requiere que la empresa suba evidencia (certificados, planillas, actas)?**
   → Variante A (Soporte). Crear vista `_tipos/{tipo}.php` con boton "Adjuntar Soporte".

2. **El numeral requiere un documento formal cuyo contenido se personaliza por empresa?**
   → Variante B (IA). Crear clase Handler en `DocumentosSSTTypes/`, registrar en Factory, crear vista `_tipos/{tipo}.php` con boton "Crear con IA".

3. **El numeral requiere un documento formal pero el texto es estandar/legal y NO cambia por empresa?**
   → Variante C (Normativo). Crear clase Handler con `requiereGeneracionIA() → false` y `getContenidoEstatico()`.

---

## Relacion con Otros Documentos

| Documento | Relacion |
|-----------|----------|
| `0_REGLAS_PROYECTO.md` | Reglas generales, checklist de nuevo documento, patrones |
| `ZZ_77_PREPARACION.md` | Contexto del cliente que alimenta Variante B |
| `ZZ_88_PARTE1.md` | Plan de Trabajo (fuente de datos para Variante B) |
| `ZZ_89_PARTE2.md` | Indicadores (fuente de datos para Variante B) |
| `ZZ_90_PARTESWEETALERT.md` | SweetAlert de verificacion (solo Variante B) |
| `ZZ_91_MENSAJESTOAST.md` | Toasts de feedback (solo Variante B) |
| `ZZ_95_PARTE3.md` | Flujo completo de generacion del documento (Variante B) |
