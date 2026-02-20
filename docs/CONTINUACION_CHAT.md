# PROMPT: Diagnosticar y corregir "Solicitar Firmas" — Email Alternativo en produccion

**Fecha:** 2026-02-20
**Estado:** EN PROGRESO — funcionalidad implementada pero con problema en produccion

---

## Contexto del problema

Cliente **Ardurra Colombia** tiene TI corporativo que bloquea emails de la plataforma (`@ardurra.com`). Los firmantes no reciben solicitudes de firma. Se implemento "email alternativo" en la pagina **Solicitar Firmas** (`firma/solicitar/{id}`) para poder enviar a un correo personal en vez del corporativo.

## Que ya se hizo (TODO COMMITEADO Y PUSHEADO A MAIN)

### 1. Email alternativo en "Solicitar Firmas" (commit c023645)
- **`app/Views/firma/solicitar.php`** — Se agregaron 2 campos opcionales "Usar email alternativo" (uno por firmante: Delegado SST y Representante Legal). Cada uno es un enlace que despliega un `<input type="email">` con `name="email_alt_delegado"` y `name="email_alt_representante"`.
- **`app/Controllers/FirmaElectronicaController.php`** — Se modifico `crearSolicitud()` para leer `email_alt_delegado` y `email_alt_representante` del POST. Si estan llenos, usa ese email en vez del configurado en contexto.

### 2. Copiar enlace + Email alt en Estado de Firmas (commits anteriores, ya en produccion)
- **`public/js/firma-helpers.js`** — JS compartido con `copiarEnlaceFirma()`, `modalEmailAlternativo()`, `enviarEmailAlternativo()`
- **`app/Views/firma/estado.php`** — Botones: Copiar enlace, WhatsApp, Reenviar, Email alt, Cancelar, Audit Log
- Mismos botones en: `actas/estado_firmas.php`, `comites_elecciones/estado_firmas_acta.php`, `contracts/estado_firma.php` (nueva), `documentos_sst/presupuesto_estado_firmas.php` (nueva)

### 3. Boton "Firma Presupuesto" en toolbar (commit bb8683c)
- **`app/Views/documentos_sst/presupuesto_preview.php`** — Boton visible cuando presupuesto en `pendiente_firma/aprobado/cerrado`

## PROBLEMA ACTUAL

La pagina `firma/solicitar/26` en produccion (`dashboard.cycloidtalent.com`) muestra "Usar email alternativo" visualmente (deploy exitoso), pero el usuario reporta que **"sigue igual"**. Posibles causas:

1. **El campo `<input>` esta FUERA del `<form>`** — Revisar que los inputs `email_alt_delegado` y `email_alt_representante` esten DENTRO del tag `<form action="firma/crear-solicitud">` (linea 276 aprox). Si estan fuera, el POST no los envia.
2. **El controller no los recibe** — Verificar que `$this->request->getPost('email_alt_delegado')` funciona en produccion.
3. **Despues de enviar, la pagina "Estado Firmas" no muestra los botones copiar/reenviar** — Verificar que `firma/estado.php` tiene los botones (commit ceafc0b, anterior).

### Documento de prueba
- **MAN-CVL-001** (Manual de Convivencia Laboral, id_documento=26, cliente Ardurra Colombia)
- Firmantes: Henry Pulido Sosa (`hpulido@ardurra.com`, Delegado SST) + Juan Ricardo Baraya Lievano (`jbaraya@ardurra.com`, Rep Legal)
- Firma secuencial: Delegado primero, luego Rep Legal

## Diagnostico a realizar

1. Leer `app/Views/firma/solicitar.php` y verificar que los inputs estan DENTRO del `<form>` tag
2. Leer `app/Controllers/FirmaElectronicaController.php` funcion `crearSolicitud()` y verificar que lee los campos
3. Si los inputs estan fuera del form, moverlos adentro
4. Verificar que `firma/estado.php` tiene los botones copiar enlace / email alt (commit ceafc0b)
5. Commit + push + probar en produccion

## Archivos clave

| Archivo | Que revisar |
|---------|-------------|
| `app/Views/firma/solicitar.php` | Inputs dentro del `<form>` tag |
| `app/Controllers/FirmaElectronicaController.php` | `crearSolicitud()` lee email_alt_* del POST |
| `app/Views/firma/estado.php` | Botones copiar/reenviar/email alt existen |
| `public/js/firma-helpers.js` | Funciones compartidas |

## Flujo Git

```
git add .
git commit -m "fix: ..."
git checkout main
git merge cycloid
git push origin main
git checkout cycloid
```
