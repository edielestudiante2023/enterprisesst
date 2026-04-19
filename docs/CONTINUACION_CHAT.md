# Continuacion de Chat - Estado de Sesion

**Fecha:** 2026-04-19
**Rama:** cycloid → merge a main (deployado)

## Que se hizo en esta sesion

### Cambio de regla de firmantes en documentos SST

**Problema detectado:**
En el documento "Asignacion Responsable SG-SST" de EMPRESA OMEGA aparecia firma REVISO/COPASST, pero el cliente tiene `requiere_delegado_sst = 0`. El usuario determino que cuando el delegado SST esta deshabilitado, el unico firmante del lado cliente debe ser el Representante Legal.

**Causa raiz:**
La logica `$esSoloDosFirmantes = ($estandares <= 10) && !$requiereDelegado;` hacia que con `estandares > 10` (EMPRESA OMEGA tiene 60) siempre se mostrara un 3er firmante (Vigia SST o COPASST segun numero de estandares), aun sin delegado.

**Solucion aplicada (opcion global):**
Se cambio la regla a `$esSoloDosFirmantes = !$requiereDelegado;` en **10 archivos** de vista/templates. La condicion de estandares queda como codigo muerto mientras el delegado este off.

### Archivos modificados (10)

| Archivo | Linea |
|---------|-------|
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

En cada uno se preservo la linea anterior como comentario para poder revivirla.

### Documentacion creada

- `docs/MODULO_NUMERALES_SGSST/04_FIRMAS_ELECTRONICAS/1_A_CAMBIO_REGLA_FIRMANTES_2026.md`
  - Motivacion, regla antigua vs nueva
  - Lista de archivos afectados
  - Guia paso a paso para revertir el cambio

### Memoria del agente actualizada

- `memory/firmas-sistema.md` ← arbol de decision actualizado con la nueva regla

## Estado al final de la sesion

- Deploy hecho a `main`
- Rama activa: `cycloid`

## Que validar despues del deploy

1. Recargar documento `asignacion_responsable_sst` de EMPRESA OMEGA en navegador → debe mostrar solo ELABORO (Consultor) + APROBO (Rep.Legal).
2. Probar un cliente con `requiere_delegado_sst = 1` → debe seguir mostrando 3 firmantes (Consultor + Delegado + Rep.Legal).
3. PDF y Word del documento deben reflejar lo mismo que la vista web.
4. Documentos ya generados NO cambian hasta que se regeneren con "Actualizar Datos".

## Como revertir si se necesita

Ver guia detallada en:
`docs/MODULO_NUMERALES_SGSST/04_FIRMAS_ELECTRONICAS/1_A_CAMBIO_REGLA_FIRMANTES_2026.md`

Comando rapido para localizar los 10 puntos de cambio:
```bash
grep -rn "Regla negocio 2026-04-19" app/Views/documentos_sst/
```

## Otros cambios incluidos en el commit (no relacionados con esta sesion)

Cambios en working tree preexistentes que entraron en el mismo push:

- `app/Models/AccAccionesModel.php` (M)
- `app/Models/AccVerificacionesModel.php` (M)
- `app/Models/CompetenciaNivelClienteModel.php` (M)
- `app/Models/DocFirmaModel.php` (M)
- `app/Models/HistorialEstandaresModel.php` (M)
- `app/Models/HistorialPlanTrabajoModel.php` (M)
- `app/Models/PtaTransicionesModel.php` (M)
- `scripts/multitenant_05_diagnostico.php` (nuevo)
- `scripts/multitenant_06_limpiar_trait.php` (nuevo)
