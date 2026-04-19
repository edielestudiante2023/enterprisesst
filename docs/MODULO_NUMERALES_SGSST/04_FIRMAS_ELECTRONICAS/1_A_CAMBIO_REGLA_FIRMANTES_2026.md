# Cambio de Regla de Firmantes - 2026-04-19

## Resumen ejecutivo

Se modifico la logica que decide **cuantos firmantes aparecen en los documentos SST**. A partir de esta fecha, cuando el cliente tiene el **Delegado SST deshabilitado**, los documentos muestran **solo 2 firmantes** (Consultor + Representante Legal), independientemente del numero de estandares aplicables (7, 21 o 60).

## Motivacion

Antes del cambio, si un cliente tenia `requiere_delegado_sst = 0` pero `estandares_aplicables > 10`, el sistema agregaba una tercera firma de **Vigia SST** (21 estandares) o **COPASST** (60 estandares) por parte del cliente.

El usuario final reporto que esto es incorrecto para su operacion: **cuando el delegado esta deshabilitado, el unico firmante del lado del cliente debe ser el Representante Legal**. La figura de Vigia/COPASST no siempre existe formalmente en el cliente, y forzar su firma generaba documentos que no se podian firmar en la practica.

## Regla antigua vs. regla nueva

### Regla antigua (arbol de decision original)

```
1. requiere_delegado_sst = true                 → 3 firmantes (Consultor + Delegado + Rep.Legal)
2. firmantesDefinidos tiene valores             → usar array definido por tipo doc
3. estandares <= 10 && !requiereDelegado        → 2 firmantes (Consultor + Rep.Legal)
4. DEFAULT (estandares > 10)                    → 3 firmantes (Consultor + Vigia/COPASST + Rep.Legal)
```

### Regla nueva (2026-04-19)

```
1. requiere_delegado_sst = true                 → 3 firmantes (Consultor + Delegado + Rep.Legal)
2. firmantesDefinidos tiene valores (<=2) && !delegado → 2 firmantes por definicion
3. !requiereDelegado                            → 2 firmantes (Consultor + Rep.Legal)  ← CAMBIO
4. [rama muerta — estandares > 10 sin delegado NO aplica]
```

**Efecto practico:** la rama que mostraba "REVISO / VIGIA SST" o "REVISO / COPASST" queda inalcanzable mientras `requiere_delegado_sst = 0`. Solo se entra a 3 firmantes cuando el delegado esta explicitamente habilitado.

## Archivos modificados (10)

Todos los cambios se marcaron con comentario inline referenciando este documento, y **la regla anterior se dejo como comentario para poder revivirla**.

| Archivo | Linea aprox. |
|---------|--------------|
| `app/Views/documentos_sst/asignacion_responsable.php` | 337 |
| `app/Views/documentos_sst/programa_capacitacion.php` | 477 |
| `app/Views/documentos_sst/programa_induccion_reinduccion.php` | 365 |
| `app/Views/documentos_sst/programa_promocion_prevencion_salud.php` | 473 |
| `app/Views/documentos_sst/plan_objetivos_metas.php` | 475 |
| `app/Views/documentos_sst/procedimiento_control_documental.php` | 658 |
| `app/Views/documentos_sst/procedimiento_matriz_legal.php` | 360 |
| `app/Views/documentos_sst/documento_generico.php` | 498 |
| `app/Views/documentos_sst/pdf_template.php` | 560 |
| `app/Views/documentos_sst/word_template.php` | 350 |

### Archivos con patron simple (7)
Cambio de:
```php
$esSoloDosFirmantes = ($estandares <= 10) && !$requiereDelegado;
```
A:
```php
$esSoloDosFirmantes = !$requiereDelegado;
```

### Archivos con matices

**`documento_generico.php`**
```php
// Antes
$esSoloDosFirmantes = $esDosFirmantesPorDefinicion || (($estandares <= 10) && !$requiereDelegado);
// Despues
$esSoloDosFirmantes = $esDosFirmantesPorDefinicion || !$requiereDelegado;
```

**`pdf_template.php` y `word_template.php`** (mantienen la exclusion de `responsabilidades_rep_legal_sgsst`)
```php
// Antes
$esSoloDosFirmantes = !$esDocResponsabilidadesRepLegal && ($estandares <= 10) && !$requiereDelegado;
// Despues
$esSoloDosFirmantes = !$esDocResponsabilidadesRepLegal && !$requiereDelegado;
```

## Como revertir (volver a la regla antigua)

Cada archivo modificado tiene un comentario de la forma:

```php
// Regla negocio 2026-04-19: si delegado SST esta deshabilitado, solo 2 firmantes (Consultor + Rep.Legal)
// Ver docs/MODULO_NUMERALES_SGSST/04_FIRMAS_ELECTRONICAS/1_A_CAMBIO_REGLA_FIRMANTES_2026.md
// Regla anterior (deshabilitada): $esSoloDosFirmantes = ($estandares <= 10) && !$requiereDelegado;
$esSoloDosFirmantes = !$requiereDelegado;
```

Para revertir:

1. `grep -rn "Regla negocio 2026-04-19" app/Views/documentos_sst/` para localizar los 10 puntos.
2. En cada archivo, reemplazar la linea activa (`$esSoloDosFirmantes = !$requiereDelegado;` o la variante con `!$esDocResponsabilidadesRepLegal` / `$esDosFirmantesPorDefinicion`) por la linea de la regla anterior que esta comentada justo arriba.
3. Borrar los 3 comentarios de bloque (regla 2026-04-19, referencia a este doc, regla anterior).
4. Actualizar `firmas-sistema.md` si aun existe en `docs/`, o en memoria del agente, para restaurar el arbol de decision original.

## Tablas / campos relevantes

- **Tabla:** `tbl_cliente_contexto_sst`
- **Campo que dispara la logica:** `requiere_delegado_sst` (0/1)
- **Campo asociado (ahora irrelevante para esta regla):** `estandares_aplicables` (7/21/60)
- **Controlador admin:** `app/Controllers/ContextoClienteController.php` (formulario edicion contexto cliente)

## Impacto colateral

- **Documentos ya generados:** mantienen su layout original hasta que se regeneren. La nueva regla aplica solo a documentos nuevos o regenerados (boton "Actualizar Datos").
- **Firma electronica:** el sistema de solicitudes (`tbl_doc_firma_solicitudes`) sigue usando el mismo flujo. Como ahora no hay firmante Vigia/COPASST, solo se crearan 2 solicitudes (Consultor + Rep.Legal) cuando delegado este off.
- **Auditoria:** se preserva la regla maestra "todo documento tecnico incluye firma de Consultor SST". Esa regla no cambio.
- **Documento `responsabilidades_rep_legal_sgsst`:** sigue excluido del modo 2-firmantes en `pdf_template.php` / `word_template.php`.

## Archivos NO modificados (verificados)

Estos archivos fueron inspeccionados y **no** requerian cambios (no contenian la condicion):

- `app/Views/documentos_sst/presupuesto_sst.php` — solo texto "Capacitacion COPASST/Vigia"
- `app/Views/documentos_sst/generar_con_ia.php` — solo referencias JS a `tiene_copasst`
