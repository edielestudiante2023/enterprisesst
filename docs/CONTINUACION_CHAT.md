# PROMPT: Copiar Enlace + Email Alternativo — Transversal a 5 Sistemas de Firma

**Fecha:** 2026-02-19
**Estado:** COMPLETADO - 5 sistemas de firma actualizados

---

## Que se hizo

### Tarea: Implementar "Copiar Enlace" + "Email Alternativo" en los 5 sistemas de firma

**Problema de negocio:** Cliente Ardurra Colombia tiene TI corporativo que bloquea emails de la plataforma. Los firmantes no reciben las solicitudes de firma.

**Solucion:** Dos funcionalidades transversales:
1. **Copiar enlace** - copia URL de firma al portapapeles para compartir por WhatsApp/chat
2. **Email alternativo** - envia la notificacion a un correo personal via SweetAlert2 + AJAX

### Archivo JS compartido (CREADO):
- `public/js/firma-helpers.js` - Funciones: `copiarEnlaceFirma()`, `modalEmailAlternativo()`, `enviarEmailAlternativo()`, `validarEmailFirma()`, `mostrarToastFirma()`, `copiarEnlaceFallback()`

### Sistema 1 - Documentos SST (FirmaElectronicaController):
- `app/Views/firma/estado.php` - Agregados botones copiar enlace + WhatsApp + email alt en cada firmante del timeline
- `app/Controllers/FirmaElectronicaController.php` - Ya tenia `reenviarFirma()`, se verifico soporte `email_alternativo`

### Sistema 2 - Presupuesto SST (PzpresupuestoSstController):
- `app/Views/documentos_sst/presupuesto_estado_firmas.php` - **VISTA NUEVA** timeline estado firmas
- `app/Controllers/PzpresupuestoSstController.php` - **2 metodos nuevos**: `estadoFirmas()` + `reenviarFirmaPresupuesto()`
- `app/Config/Routes.php` - 2 rutas nuevas:
  - `GET /documentos-sst/presupuesto/estado-firmas/(:num)/(:num)`
  - `POST /documentos-sst/presupuesto/reenviar-firma`

### Sistema 3 - Actas Comite (ActasController):
- `app/Views/actas/estado_firmas.php` - Agregados botones copiar enlace + WhatsApp + email alt
- Controller ya tenia soporte `email_alternativo`

### Sistema 4 - COPASST (ComitesEleccionesController):
- `app/Views/comites_elecciones/estado_firmas_acta.php` - Agregados botones copiar enlace + WhatsApp + email alt
- Controller ya tenia soporte `email_alternativo`

### Sistema 5 - Contratos (ContractController):
- `app/Views/contracts/estado_firma.php` - **VISTA NUEVA** timeline estado firma contrato
- `app/Controllers/ContractController.php` - `estadoFirma()` modificado: GET → HTML view, AJAX → JSON
- `app/Views/contracts/view.php` - Ya tenia botones embebidos desde sesion anterior

---

## Archivos modificados/creados

| Archivo | Accion |
|---------|--------|
| `public/js/firma-helpers.js` | CREADO - JS compartido |
| `app/Views/firma/estado.php` | MODIFICADO - botones copiar/email alt |
| `app/Views/actas/estado_firmas.php` | MODIFICADO - botones copiar/email alt |
| `app/Views/comites_elecciones/estado_firmas_acta.php` | MODIFICADO - botones copiar/email alt |
| `app/Views/contracts/estado_firma.php` | CREADO - vista timeline |
| `app/Views/contracts/view.php` | Ya tenia botones (sesion anterior) |
| `app/Views/documentos_sst/presupuesto_estado_firmas.php` | CREADO - vista timeline |
| `app/Controllers/ContractController.php` | MODIFICADO - estadoFirma() dual mode |
| `app/Controllers/PzpresupuestoSstController.php` | MODIFICADO - 2 metodos nuevos |
| `app/Config/Routes.php` | MODIFICADO - 2 rutas presupuesto |

---

## Patron de botones en cada sistema

```html
<!-- Copiar enlace -->
<button onclick="copiarEnlaceFirma(url, nombre)">Copiar enlace</button>
<!-- WhatsApp -->
<a href="https://wa.me/?text=..." target="_blank">WhatsApp</a>
<!-- Reenviar (email original) -->
<button onclick="reenviarFirma{Sistema}()">Reenviar</button>
<!-- Email alternativo -->
<button onclick="modalEmailAlternativo(urlReenvio, nombre, email)">Email alt.</button>
```

---

## Pendiente para verificar

1. Probar copiar enlace en cada sistema (clipboard API + fallback)
2. Probar email alternativo con SweetAlert modal
3. Verificar reenvio normal sigue funcionando
4. Probar en movil (responsive)
5. Verificar tokens expirados muestran advertencia
6. **Opcional:** Agregar boton "Ver Estado Firmas" en toolbar de `presupuesto_preview.php`

---

## Tarea completada anteriormente (mismo dia)

### Nivelar UX Contexto IA en 14 vistas generador_ia
- 7 vistas actividades: 2-col → 3-col
- 7 vistas indicadores: card contexto nuevo colapsado
- Backend: Fix hardcodeo en 15 services PHP
