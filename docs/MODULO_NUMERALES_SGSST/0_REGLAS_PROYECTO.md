# Reglas y Patrones del Proyecto - Enterprise SST

Este documento recoge las reglas criticas, patrones de arquitectura y lecciones aprendidas del proyecto. Debe ser consultado por cualquier desarrollador o IA antes de hacer cambios en el modulo de documentos SST.

---

## Arquitectura 3-Partes (Modulos SST)

Cada documento SST se alimenta de 3 fuentes de datos:

| Parte | Tabla BD | Campo clave | Descripcion |
|-------|----------|-------------|-------------|
| **Parte 1** | `tbl_pta_cliente` | `tipo_servicio` | Actividades del Plan de Trabajo Anual |
| **Parte 2** | `tbl_indicadores_sst` | `categoria` | Indicadores del SG-SST |
| **Parte 3** | `tbl_documentos_sst` | `tipo_documento` | El documento formal generado |

Las 3 fuentes + contexto del cliente (`tbl_cliente_contexto_sst`) alimentan la IA.

**Documentacion detallada:**
- Parte 1: `ZZ_80_PARTE1.md`
- Parte 2: `ZZ_81_PARTE2.md`
- Parte 3: `ZZ_95_PARTE3.md`
- Preparacion: `ZZ_77_PREPARACION.md`

---

## Reglas Criticas de URLs

- URLs de documentos SIEMPRE en **snake_case**: `/documentos/generar/programa_capacitacion/18`
- NUNCA kebab-case: ~~`programa-capacitacion`~~ (no matchea el Factory)
- El `tipo_documento` de la URL debe coincidir **exactamente** con:
  - La key del `DocumentoSSTFactory`
  - El valor `tipo_documento` en la BD
  - El return de `getTipoDocumento()` en la clase

**Si no coinciden, el documento no se genera y no hay error claro.**

---

## Patron getContextoBase()

- Cada clase en `app/Libraries/DocumentosSSTTypes/` sobrescribe `getContextoBase()` para consultar PTA + Indicadores
- **NO usar** `tbl_cronog_capacitacion` como fuente principal, siempre `tbl_pta_cliente`
- Ejemplo correcto: `ProgramaPromocionPrevencionSalud.php`, `ProgramaCapacitacion.php`

---

## SweetAlert de Verificacion de Datos

Control critico que muestra al usuario que datos alimentaran la IA antes de generar.

- **Endpoint:** `GET /documentos/previsualizar-datos/{tipo}/{id_cliente}`
- **Controller:** `DocumentosSSTController::previsualizarDatos()`
- Muestra actividades PTA + indicadores + contexto antes de generar
- Cache en variable JS `datosPreviewCache` para no consultar cada clic
- Mapeo de filtros por tipo: `getFiltroServicioPTA()` y `getCategoriaIndicador()`
- Flag `verificacionConfirmada` = se muestra UNA vez por sesion, luego se omite (evita loop)
- **Doc completa:** `ZZ_90_PARTESWEETALERT.md`

### Estilos dentro del SweetAlert

SweetAlert2 usa su propio DOM. Las clases de Bootstrap **NO funcionan** dentro del HTML del SweetAlert. Se deben usar **estilos inline**:

```javascript
// MAL - no funciona en SweetAlert
html += '<div class="text-start mb-3">';

// BIEN - funciona en SweetAlert
html += '<div style="text-align: left; margin-bottom: 12px;">';
```

---

## Sistema de Toast Notifications (Stack Dinamico)

- Toasts se crean **dinamicamente** en `#toastStack` (no hay elemento estatico)
- Cada toast tiene ID unico (`toast-{timestamp}-{random}`), se auto-elimina del DOM al cerrarse
- 8 tipos: `success`, `error`, `warning`, `info`, `ia`, `save`, `database`, **`progress`**
- `progress`: spinner animado, autohide=false, se cierra programaticamente con `cerrarToast(ref)`
- Errores de generacion incluyen boton **Reintentar** via parametro `reintentarCallback`
- `modoBatch=true` suprime toasts individuales durante "Generar Todo"
- Timestamp muestra hora real (HH:MM:SS), no "Ahora" estatico
- **Doc completa:** `ZZ_91_MENSAJESTOAST.md`

---

## Discrepancias de Nombres de Campos en BD

Los nombres en la tabla `tbl_cliente_contexto_sst` NO siempre coinciden con los que usa el codigo:

| Codigo busca | Tabla tiene | Solucion |
|--------------|-------------|----------|
| `nivel_riesgo` | `nivel_riesgo_arl` | Usar nombre real de BD |
| `numero_trabajadores` | `total_trabajadores` | Usar nombre real de BD |
| `actividad_economica` | `actividad_economica_principal` | Usar con fallback chain |

### Fallback chain para actividad economica

```php
$contexto['actividad_economica_principal']
    ?? $contexto['sector_economico']
    ?? $cliente['codigo_actividad_economica']
    ?? 'No especificada'
```

---

## Checklist para Nuevo Tipo de Documento

Al agregar un nuevo tipo de documento SST al sistema:

1. [ ] Crear clase en `app/Libraries/DocumentosSSTTypes/NuevoDocumento.php`
2. [ ] Registrar en `DocumentoSSTFactory.php` con key en **snake_case**
3. [ ] Crear vista en `app/Views/documentos_sst/nuevo_documento.php`
4. [ ] Agregar ruta en `app/Config/Routes.php`
5. [ ] Sobrescribir `getContextoBase()` con las fuentes de datos correctas
6. [ ] Agregar entrada en `getFiltroServicioPTA()` (filtros de Plan de Trabajo)
7. [ ] Agregar entrada en `getCategoriaIndicador()` (categoria de indicadores)
8. [ ] Verificar que el SweetAlert muestre datos para este tipo
9. [ ] Agregar SQL de insercion en `app/SQL/`
10. [ ] Verificar que la URL use **snake_case** y coincida con Factory key

---

## Convenciones de Documentacion

- **Prefijo `ZZ_`:** Instructivos del flujo de generacion (se leen en orden numerico)
- **Prefijo `AA_`:** Documentacion de componentes especificos
- **Prefijo numerico (`1_`, `2_`, etc.):** Agrupacion por area
- El equipo prefiere **documentacion-primero**: escribir el .md, luego implementar codigo
- Si surgen nuevos aprendizajes durante la implementacion, agregarlos a los .md correspondientes

---

## Indice de Documentacion

| Archivo | Contenido |
|---------|-----------|
| `ZZ_77_PREPARACION.md` | Configuracion de contexto del cliente |
| `ZZ_80_PARTE1.md` | Parte 1: Plan de Trabajo (tbl_pta_cliente) |
| `ZZ_81_PARTE2.md` | Parte 2: Indicadores (tbl_indicadores_sst) |
| `ZZ_90_PARTESWEETALERT.md` | SweetAlert de verificacion antes de generar |
| `ZZ_91_MENSAJESTOAST.md` | Sistema de toast notifications |
| `ZZ_95_PARTE3.md` | Parte 3: Documento formal y clases |
| `ZZ_96_PARTE4.md` | Parte 4 |
| `ZZ_97_PARTE5.md` | Parte 5 |
| `ZZ_11_PROMPTNUEVO.md` | Prompt para crear documentos nuevos |
| `ZZ_22_PROMPTREPARACIONES.md` | Prompt para reparaciones |
