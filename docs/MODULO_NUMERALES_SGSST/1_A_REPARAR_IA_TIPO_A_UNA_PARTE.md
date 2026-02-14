# Como Reparar IA Tipo A (Documento de 1 Parte)

> Guia para diagnosticar y corregir problemas en la generacion IA de documentos
> que usan el flujo `secciones_ia` (directos, de 1 parte).

---

## Que es un Documento Tipo A (1 Parte)

Un documento Tipo A usa el flujo `secciones_ia`. Se genera **directamente** con la IA usando
**unicamente el contexto del cliente**. NO usa Plan de Trabajo (PTA) ni Indicadores.

### Fuentes de datos:
```
┌─────────────────────────────────────┐
│ Documento Tipo A (secciones_ia)     │
│                                     │
│   Fuente UNICA:                     │
│   └── Contexto del cliente          │
│       (tbl_cliente_contexto_sst)    │
│                                     │
│   NO usa:                           │
│   ├── ✗ PTA (tbl_pta_cliente)       │
│   └── ✗ Indicadores                 │
│         (tbl_indicadores_sst)       │
└─────────────────────────────────────┘
```

### Diferencia con Tipo B (3 Partes):
```
┌─────────────────────────────────────┐
│ Documento Tipo B (programa_con_pta) │
│                                     │
│   Fuentes (3 partes):               │
│   ├── Parte 1: PTA (tipo_servicio)  │
│   ├── Parte 2: Indicadores (cat.)   │
│   └── Parte 3: Contexto cliente     │
└─────────────────────────────────────┘
```

**Regla clave:** Si el `flujo` en `tbl_doc_tipo_configuracion` es `secciones_ia`, es Tipo A.

---

## Problema 1: `[Seccion no definida]` y SweetAlert Colgado

### Sintoma
- Las secciones muestran `[Seccion no definida]` en la interfaz
- El SweetAlert "Consultando datos..." se queda girando indefinidamente
- No se puede generar ninguna seccion

### Causa raiz
Falta la clase PHP en `app/Libraries/DocumentosSSTTypes/` y/o no esta registrada en el Factory.

El Factory no puede crear el handler → `previsualizarDatos()` retorna error → SweetAlert se cuelga.
Las secciones no se pueden resolver porque `getSecciones()` no existe.

### Solucion

**Paso 1:** Crear la clase PHP (ejemplo: `IdentificacionAltoRiesgo.php`)
```
app/Libraries/DocumentosSSTTypes/IdentificacionAltoRiesgo.php
```

La clase debe:
- Extender `AbstractDocumentoSST`
- Implementar `getTipoDocumento()` retornando el snake_case exacto
- Implementar `getSecciones()` con fallback
- Implementar `getPromptParaSeccion()` con fallback
- Leer configuracion desde BD via `DocumentoConfigService`

**Paso 2:** Registrar en Factory
```php
// app/Libraries/DocumentosSSTTypes/DocumentoSSTFactory.php
'identificacion_alto_riesgo' => IdentificacionAltoRiesgo::class,
```

**Paso 3:** Verificar
- URL: `/documentos/generar/identificacion_alto_riesgo/{id_cliente}`
- No debe aparecer `[Seccion no definida]`
- SweetAlert debe completar la carga

### Caso real
`identificacion_alto_riesgo` (2026-02-13): Se creo el SQL y la vista pero se olvido la clase PHP.
Resultado: documento completamente roto.

---

## Problema 2: SweetAlert Muestra PTA e Indicadores No Relacionados

### Sintoma
- El SweetAlert de verificacion carga correctamente
- PERO muestra actividades del Plan de Trabajo (ej: 19 actividades)
- Y muestra indicadores (ej: 17 indicadores)
- Estas actividades e indicadores son de OTROS modulos, no de este documento
- El documento Tipo A no deberia usar PTA ni indicadores

### Causa raiz
El endpoint `previsualizarDatos()` en `DocumentosSSTController` consultaba PTA e indicadores
para TODOS los documentos sin importar el flujo.

Cuando `getFiltroServicioPTA()` no tiene filtro para el tipo de documento, retorna `[]`,
y la query se ejecuta SIN filtro → devuelve TODAS las actividades del cliente.

Lo mismo con `getCategoriaIndicador()` → devuelve TODOS los indicadores.

```
getFiltroServicioPTA('identificacion_alto_riesgo')
→ return $filtros[$tipoDocumento] ?? [];  // [] = sin filtro
→ query SIN WHERE tipo_servicio
→ retorna las 19 actividades de TODOS los modulos
```

### Solucion

**Backend** (`DocumentosSSTController::previsualizarDatos()`):

1. Detectar el flujo del documento consultando `DocumentoConfigService`
2. Si `flujo === 'secciones_ia'`, NO consultar PTA ni indicadores (retornar arrays vacios)
3. Incluir el campo `flujo` en la respuesta JSON

```php
// Detectar flujo
$configService = new DocumentoConfigService();
$configDoc = $configService->obtenerTipoDocumento($tipoDocumento);
$flujo = $configDoc['flujo'] ?? 'secciones_ia';

// Solo consultar PTA si NO es documento directo
$actividades = [];
if ($flujo !== 'secciones_ia') {
    // ... query tbl_pta_cliente con filtros ...
}

// Solo consultar indicadores si NO es documento directo
$indicadores = [];
if ($flujo !== 'secciones_ia') {
    // ... query tbl_indicadores_sst con filtros ...
}

// Incluir flujo en la respuesta
return $this->response->setJSON([
    'ok' => true,
    'flujo' => $flujo,  // <-- NUEVO
    'actividades' => $actividades,
    'indicadores' => $indicadores,
    'contexto' => [ ... ]
]);
```

**Frontend** (`generar_con_ia.php` - SweetAlert JS):

1. Leer `data.flujo` de la respuesta
2. Si `flujo === 'secciones_ia'`, no renderizar secciones de PTA ni indicadores
3. Mostrar nota informativa: "Documento directo: se genera usando contexto de la empresa"

```javascript
const esDocDirecto = (data.flujo === 'secciones_ia');

if (!esDocDirecto) {
    // Renderizar PTA + Indicadores (como antes)
} else {
    // Nota: "Documento directo, solo usa contexto"
}

// Contexto del cliente: siempre se muestra
```

---

## Archivos Involucrados

| Archivo | Que hace |
|---------|----------|
| `app/Controllers/DocumentosSSTController.php` | `previsualizarDatos()` - endpoint AJAX del SweetAlert |
| `app/Views/documentos_sst/generar_con_ia.php` | JS del SweetAlert - renderiza la verificacion |
| `app/Libraries/DocumentosSSTTypes/DocumentoSSTFactory.php` | Factory que crea handlers por tipo |
| `app/Libraries/DocumentosSSTTypes/*.php` | Clases de cada tipo de documento |
| `app/Services/DocumentoConfigService.php` | Lee config (flujo, secciones, firmantes) desde BD |
| `tbl_doc_tipo_configuracion` | Tabla BD con campo `flujo` (`secciones_ia` o `programa_con_pta`) |

---

## Checklist: Nuevo Documento Tipo A (1 Parte)

Al crear un nuevo documento de 1 parte, verificar:

- [ ] Clase PHP creada en `DocumentosSSTTypes/`
- [ ] Registrada en `DocumentoSSTFactory::$tiposRegistrados`
- [ ] SQL ejecutado con `flujo = 'secciones_ia'` en `tbl_doc_tipo_configuracion`
- [ ] `getTipoDocumento()` retorna el snake_case exacto
- [ ] `getSecciones()` tiene fallback si BD vacia
- [ ] `getPromptParaSeccion()` tiene prompts de fallback
- [ ] **NO necesita** entrada en `getFiltroServicioPTA()` (no usa PTA)
- [ ] **NO necesita** entrada en `getCategoriaIndicador()` (no usa indicadores)
- [ ] SweetAlert muestra SOLO contexto del cliente (no PTA ni indicadores)
- [ ] URL en snake_case: `/documentos/generar/{tipo_snake_case}/{id_cliente}`

---

## Diagrama de Decision: El SweetAlert Muestra Datos Incorrectos?

```
SweetAlert muestra datos raros?
│
├── Muestra PTA + Indicadores de otros modulos?
│   ├── Es documento Tipo A (secciones_ia)?
│   │   └── SI: El backend no esta filtrando por flujo
│   │       → Verificar que previsualizarDatos() chequea flujo
│   │
│   └── Es documento Tipo B (programa_con_pta)?
│       └── Falta filtro en getFiltroServicioPTA() o getCategoriaIndicador()
│           → Agregar los filtros correspondientes
│
├── Se queda colgado en "Consultando datos..."?
│   └── Falta la clase PHP o no esta en Factory
│       → Ver Problema 1
│
└── Muestra "No se pudieron obtener los datos"?
    └── Error en el endpoint o cliente no encontrado
        → Revisar logs del servidor
```

---

## Referencia: Documentos Tipo A Existentes

| tipo_documento | Estandar | Clase PHP |
|---------------|----------|-----------|
| `politica_sst_general` | 2.1.1 | PoliticaSstGeneral |
| `politica_alcohol_drogas` | 2.1.1 | PoliticaAlcoholDrogas |
| `politica_acoso_laboral` | 2.1.1 | PoliticaAcosoLaboral |
| `politica_violencias_genero` | 2.1.1 | PoliticaViolenciasGenero |
| `politica_discriminacion` | 2.1.1 | PoliticaDiscriminacion |
| `politica_prevencion_emergencias` | 2.1.1 | PoliticaPrevencionEmergencias |
| `manual_convivencia_laboral` | 1.1.8 | ManualConvivenciaLaboral |
| `procedimiento_control_documental` | 2.8.1 | ProcedimientoControlDocumental |
| `procedimiento_matriz_legal` | 2.7.1 | ProcedimientoMatrizLegal |
| `mecanismos_comunicacion_sgsst` | 2.8.1 | MecanismosComunicacionSgsst |
| `procedimiento_evaluaciones_medicas` | 3.1.1 | ProcedimientoEvaluacionesMedicas |
| `procedimiento_adquisiciones` | 2.9.1 | ProcedimientoAdquisiciones |
| `procedimiento_evaluacion_proveedores` | 2.10.1 | ProcedimientoEvaluacionProveedores |
| `procedimiento_gestion_cambio` | 2.11.1 | ProcedimientoGestionCambio |
| `procedimiento_investigacion_accidentes` | 3.2.1 | ProcedimientoInvestigacionAccidentes |
| `procedimiento_investigacion_incidentes` | 3.2.2 | ProcedimientoInvestigacionIncidentes |
| `metodologia_identificacion_peligros` | 4.1.1 | MetodologiaIdentificacionPeligros |
| `identificacion_sustancias_cancerigenas` | 4.1.3 | IdentificacionSustanciasCancerigenas |
| `identificacion_alto_riesgo` | 1.1.5 | IdentificacionAltoRiesgo |

---

*Documentacion creada: 2026-02-13*
*Caso origen: identificacion_alto_riesgo mostraba 19 actividades y 17 indicadores no relacionados*
