# ESTADO FINAL: Auditoria y nivelacion de paginas de firma electronica

**Fecha:** 2026-02-20
**Estado:** COMPLETADO

---

## Resumen de lo realizado

Se auditaron las 6 paginas de firma electronica del sistema y se nivelaron para que todas tengan los botones estandar.

### Pagina de referencia: `firma/estado.php`
Botones estandar por firmante pendiente: `[Copiar enlace] [Reenviar] [Email alt.] [Cancelar] [Audit Log]`

### Resultado por pagina

| # | Pagina | Copiar | Reenviar | Email alt. | Cancelar | Audit Log | Cambios |
|---|--------|--------|----------|------------|----------|-----------|---------|
| 1 | `firma/solicitar.php` | ✅ NUEVO | ✅ NUEVO | ✅ | ✅ NUEVO | ✅ NUEVO | +4 botones (condicional: solo si solicitud existe) |
| 2 | `firma/estado.php` | ✅ | ✅ | ✅ | ✅ | ✅ | REFERENCIA |
| 3 | `actas/estado_firmas.php` | ✅ | ✅ | ✅ | ✅ NUEVO | - | +Cancelar |
| 4 | `comites_elecciones/estado_firmas_acta.php` | ✅ | ✅ | ✅ | ✅ | ✅ NUEVO | +Audit Log |
| 5 | `contracts/estado_firma.php` | ✅ | ✅ | ✅ | ✅ NUEVO | - | +Cancelar |
| 6 | `presupuesto_estado_firmas.php` | ✅ | ✅ | ✅ | ✅ NUEVO | - | +Cancelar |

### Nota sobre Audit Log
Solo COPASST (#4) y Documentos SST (#1, #2) tienen Audit Log porque usan `tbl_doc_firmas_solicitudes`. Actas, Contratos y Presupuesto tienen tablas propias sin infraestructura de audit log.

## Archivos modificados

### Vistas (frontend)
- `app/Views/firma/solicitar.php` — Agregados 5 botones condicionales por firmante (si solicitud existe: Copiar/Reenviar/Email alt./Cancelar/Audit Log; si no: solo Email alt.) + SweetAlert2 + firma-helpers.js
- `app/Views/comites_elecciones/estado_firmas_acta.php` — Agregado boton Audit Log en estados firmado y pendiente/esperando
- `app/Views/actas/estado_firmas.php` — Agregado boton Cancelar firma en btn-group
- `app/Views/contracts/estado_firma.php` — Agregado boton Cancelar + funcion JS `cancelarFirmaContrato()`
- `app/Views/documentos_sst/presupuesto_estado_firmas.php` — Agregado boton Cancelar + funcion JS `cancelarFirmaPresupuesto()`

### Controllers (backend)
- `app/Controllers/FirmaElectronicaController.php` — `cancelar()` ahora soporta AJAX (JSON response)
- `app/Controllers/ActasController.php` — Nuevo metodo `cancelarFirmaAsistente($idActa, $idAsistente)`
- `app/Controllers/ContractController.php` — Nuevo metodo `cancelarFirmaContrato()`
- `app/Controllers/PzpresupuestoSstController.php` — Nuevo metodo `cancelarFirmaPresupuesto()`

### Rutas
- `app/Config/Routes.php` — 3 rutas nuevas:
  - `POST /actas/comite/(:num)/acta/(:num)/cancelar-firma/(:num)`
  - `POST /contracts/cancelar-firma-contrato`
  - `POST /documentos-sst/presupuesto/cancelar-firma`

## Flujo Git
```
git add .
git commit -m "feat: nivelar botones firma electronica en todas las paginas"
git checkout main
git merge cycloid
git push origin main
git checkout cycloid
```
