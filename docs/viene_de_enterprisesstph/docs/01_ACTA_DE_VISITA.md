# Acta de Visita y Seguimiento al Sistema

**Código SGSST:** FT-SST-007
**Versión:** 001
**Prioridad:** ALTA (primera inspección a implementar)
**Origen:** Migración desde AppSheet

---

## 1. Descripción General

El Acta de Visita es el documento principal que se genera en cada visita del consultor a un cliente. Registra:
- Quiénes participaron en la reunión
- Los temas tratados
- El estado de pendientes, mantenimientos y hallazgos del cliente
- Observaciones y compromisos
- Firmas de los participantes

**Flujo actual (AppSheet - se elimina):**
```
BD MySQL (DigitalOcean)
    │ Google Apps Script + JDBC (sincroniza cada X minutos)
    ▼
Google Sheets (puentes: tbl_clientes, tbl_pendientes, tbl_vencimientos_mantenimientos, TBL_HALLAZGOS_LOCATIVOS)
    │ Data source
    ▼
AppSheet (formularios + columnas calculadas: resumen_pendientes_pdf, resumen_mantenimiento, etc.)
    │ Genera PDF manual
    ▼
Consultor sube PDF manualmente al sistema
```

**Flujo nuevo (PWA - directo):**
```
BD MySQL (DigitalOcean)
    │ CI4 Models (consulta directa, sin intermediarios)
    ▼
PWA → Formulario → Firma → PDF automático (DOMPDF) → Vinculado al cliente
```

**Lo que desaparece:**
- Google Apps Script con JDBC paginado (chunks de 500 filas)
- Google Sheets como puente de datos
- Columnas calculadas de AppSheet (`resumen_pendientes_pdf`, `resumen_mantenimiento`, `resumen_hallazgos_abiertos`, `NombreCliente_Puente`, etc.)
- La conexión JDBC desde Google
- Subida manual de PDFs

---

## 2. Mapeo AppSheet → Nueva Estructura

### Campos del formulario

| Campo AppSheet           | Campo Nuevo              | Tipo              | Notas                                                    |
|--------------------------|--------------------------|-------------------|----------------------------------------------------------|
| ID                       | `id`                     | INT AUTO_INCREMENT| AppSheet usa UUID, nosotros usamos INT                   |
| FECHA DE REGISTRO        | `created_at`             | DATETIME          | Automático                                               |
| FECHA                    | `fecha_visita`           | DATE              | Fecha de la visita                                       |
| HORA                     | `hora_visita`            | TIME              | Hora de inicio                                           |
| AÑO DE VISITA            | _(derivado)_             | —                 | Se calcula de `fecha_visita`, no se almacena             |
| MES DE VISITA            | _(derivado)_             | —                 | Se calcula de `fecha_visita`, no se almacena             |
| CLIENTE                  | `id_cliente`             | INT FK             | Referencia a `tbl_clientes.id_cliente`                  |
| UBICACION                | `ubicacion_gps`          | VARCHAR(50)       | Coordenadas GPS capturadas por Geolocation API           |
| MOTIVO                   | `motivo`                 | VARCHAR(255)      | Motivo de la visita                                      |
| INTEGRANTE 1-4           | tabla `tbl_acta_visita_integrantes` | —      | Tabla relacionada, sin límite de 4                       |
| ROL 1-4                  | tabla `tbl_acta_visita_integrantes` | —      | Junto con el integrante                                  |
| TEMA 1-5                 | tabla `tbl_acta_visita_temas`       | —      | Tabla relacionada, sin límite de 5                       |
| CARTERA                  | `cartera`                | TEXT              | Estado de cartera                                        |
| OBSERVACIONES            | `observaciones`          | TEXT              | Observaciones generales                                  |
| PROXIMA REUNIÓN          | `proxima_reunion_fecha`  | DATE              | Nullable                                                 |
| HORA PRÓXIMA REUNIÓN     | `proxima_reunion_hora`   | TIME              | Nullable                                                 |
| MODALIDAD                | `modalidad`              | VARCHAR(50)       | Presencial/Virtual/Mixta                                 |
| FIRMA DEL ADMINISTRADOR  | `firma_administrador`    | VARCHAR(255)      | Ruta al PNG de la firma                                  |
| FIRMA DEL VIGIA          | `firma_vigia`            | VARCHAR(255)      | Ruta al PNG de la firma (nullable)                       |
| FIRMA DEL CONSULTOR      | `firma_consultor`        | VARCHAR(255)      | Ruta al PNG de la firma                                  |
| SOPORTES                 | `soportes`               | TEXT/JSON         | Rutas a archivos adjuntos                                |
| SOPORTE LAVADO TANQUES   | `soporte_lavado_tanques` | VARCHAR(255)      | Ruta a archivo                                           |
| SOPORTE PLAGAS           | `soporte_plagas`         | VARCHAR(255)      | Ruta a archivo                                           |
| FOTO 1-3 SEG SOC         | tabla `tbl_acta_visita_fotos`      | —       | Tabla relacionada, sin límite de 3                       |
| CLIENTESSTPH             | _(derivado)_             | —                 | Se obtiene de `tbl_clientes.nombre_cliente`              |
| ESTADO                   | `estado`                 | ENUM              | `borrador`, `completo`                                   |
| AGENDA_ID                | `agenda_id`              | VARCHAR(50)       | Nullable, para vincular con agenda                       |
| _(nuevo)_                | `id_consultor`           | INT FK             | Quién creó el acta                                      |
| _(nuevo)_                | `ruta_pdf`               | VARCHAR(255)      | Ruta al PDF generado                                     |
| _(nuevo)_                | `updated_at`             | DATETIME          | Automático                                               |

### Mejoras sobre AppSheet

1. **Integrantes dinámicos:** En AppSheet hay máximo 4. En el nuevo sistema no hay límite.
2. **Temas dinámicos:** En AppSheet hay máximo 5. En el nuevo sistema no hay límite.
3. **Fotos dinámicas:** En AppSheet hay máximo 3. En el nuevo sistema no hay límite.
4. **Datos automáticos:** El acta jala automáticamente pendientes, mantenimientos y hallazgos del cliente desde las tablas existentes. En AppSheet esto no existe.
5. **PDF automático:** Se genera con un click, no hay que subirlo manualmente.

---

## 3. Flujo de Usuario (Mobile)

### 3.1 Crear nueva Acta de Visita

```
[Dashboard Inspecciones]
        │
        ▼
[Seleccionar Cliente]  ←  Select2 con búsqueda, muestra solo clientes del consultor
        │
        ▼
[Formulario Acta de Visita]
   ┌────────────────────────────────────────┐
   │  📍 Ubicación: (auto GPS)             │
   │                                        │
   │  Fecha: [19/02/2026]  Hora: [17:54]   │
   │  Motivo: [___________________________] │
   │                                        │
   │  ── INTEGRANTES ──                     │
   │  + Agregar integrante                  │
   │  [Nombre] [Rol ▼]                     │
   │  [Nombre] [Rol ▼]                     │
   │                                        │
   │  ── TEMAS ──                           │
   │  + Agregar tema                        │
   │  [Tema 1: ________________________]   │
   │  [Tema 2: ________________________]   │
   │                                        │
   │  ── TEMAS ABIERTOS (auto) ──          │
   │  ⚠️ Hallazgos Locativos: 0            │
   │  ⚠️ Mantenimientos x vencer: 2         │
   │  ⚠️ Pendientes abiertos: 3             │
   │   • Remitir pieza gráfica — 12/02     │
   │   • Actualizar matriz — 15/02         │
   │   • Enviar cotización — 18/02         │
   │                                        │
   │  ── OBSERVACIONES ──                   │
   │  [___________________________]         │
   │                                        │
   │  ── CARTERA ──                         │
   │  [Ok / Pendiente / ...]               │
   │                                        │
   │  ── COMPROMISOS ──                     │
   │  + Agregar compromiso                  │
   │  [Actividad] [Fecha cierre] [Resp.]   │
   │                                        │
   │  ── PRÓXIMA REUNIÓN ──                 │
   │  Fecha: [__/__/____]  Hora: [__:__]   │
   │  Modalidad: [Presencial ▼]            │
   │                                        │
   │  ── SOPORTES ──                        │
   │  📷 Tomar foto / Adjuntar archivo      │
   │                                        │
   │  [💾 Guardar borrador]                 │
   │  [✍️ Ir a firmas]                      │
   └────────────────────────────────────────┘
```

### 3.2 Proceso de Firmas

```
[Pantalla de Firmas]
   ┌────────────────────────────────────────┐
   │  Firma del Administrador               │
   │  ┌──────────────────────────────────┐  │
   │  │                                  │  │
   │  │         (canvas firma)           │  │
   │  │                                  │  │
   │  └──────────────────────────────────┘  │
   │  [Limpiar]                             │
   │                                        │
   │  Firma del Vigía (opcional)            │
   │  ┌──────────────────────────────────┐  │
   │  │         (canvas firma)           │  │
   │  └──────────────────────────────────┘  │
   │  [Limpiar]  [No aplica ☐]            │
   │                                        │
   │  Firma del Consultor                   │
   │  ┌──────────────────────────────────┐  │
   │  │         (canvas firma)           │  │
   │  └──────────────────────────────────┘  │
   │  [Limpiar]                             │
   │                                        │
   │  [⬅️ Volver al acta]                   │
   │  [✅ Finalizar y generar PDF]          │
   └────────────────────────────────────────┘
```

### 3.3 Post-firma

```
[Confirmación]
   ┌────────────────────────────────────────┐
   │                                        │
   │  ✅ Acta generada exitosamente         │
   │                                        │
   │  📄 Ver PDF                            │
   │  📤 Compartir (WhatsApp/Email)         │
   │  📋 Nueva acta                         │
   │  🏠 Volver al inicio                   │
   │                                        │
   └────────────────────────────────────────┘
```

---

## 4. Sección "Temas Abiertos y Vencidos" (Automática)

Esta es la **gran ventaja sobre AppSheet**. En AppSheet estas secciones se calculaban con fórmulas como `IF(ISBLANK(SELECT(...)))` sobre Google Sheets puente. Ahora se consultan directamente a MySQL.

### 4.1 Pendientes Abiertos
**Tabla:** `tbl_pendientes` (PK: `id_pendientes`)
**Modelo existente:** `PendientesModel`
**Campo estado:** `estado` = `'ABIERTA'` / `'CERRADA'` (UPPERCASE)

```sql
SELECT tarea_actividad, fecha_asignacion, responsable, conteo_dias
FROM tbl_pendientes
WHERE id_cliente = ? AND estado = 'ABIERTA'
ORDER BY fecha_asignacion DESC
```
- Si hay 0 → "Sin pendientes abiertos" (check verde)
- Si hay N → lista con tarea, responsable y días abierta

### 4.2 Mantenimientos por Vencer
**Tabla:** `tbl_vencimientos_mantenimientos` (PK: `id_vencimientos_mmttos`)
**Modelo existente:** `VencimientosMantenimientoModel` (ya tiene `getUpcomingVencimientos()`)

```sql
SELECT vm.*, m.descripcion_mantenimiento
FROM tbl_vencimientos_mantenimientos vm
LEFT JOIN tbl_mantenimientos m ON vm.id_mantenimiento = m.id_mantenimiento
WHERE vm.id_cliente = ?
AND vm.estado_actividad = 'sin ejecutar'
AND vm.fecha_vencimiento <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
ORDER BY vm.fecha_vencimiento ASC
```
- Si hay 0 → "Sin mantenimientos por vencer (próx. 30 días)" (check verde)
- Si hay N → lista con descripción y fecha de vencimiento (incluye vencidos)

### 4.3 Hallazgos Locativos
**IMPORTANTE:** `tbl_hallazgos` **NO existe actualmente en la BD MySQL.**
En AppSheet existía como `TBL_HALLAZGOS_LOCATIVOS` (fuente: Google Sheets).

**Opciones:**
- **Opción A (recomendada):** Crear `tbl_hallazgos_locativos` en MySQL como parte de este módulo (será una inspección futura del roadmap)
- **Opción B:** Omitir esta sección del acta hasta que se implemente la inspección de hallazgos locativos

**Decisión:** Por ahora se omite. Cuando se implemente la inspección de "Locativas" (item #3 del roadmap), se crea la tabla y se agrega la sección al acta automáticamente.

### Resumen de tablas consultadas (solo lectura)

| Tabla existente | Modelo CI4 | Campo estado | Valores |
|-----------------|------------|--------------|---------|
| `tbl_pendientes` | `PendientesModel` | `estado` | `ABIERTA` / `CERRADA` |
| `tbl_vencimientos_mantenimientos` | `VencimientosMantenimientoModel` | `estado_actividad` | `sin ejecutar` / (ejecutado) |
| `tbl_clientes` | `ClientModel` | Activo = tiene contrato activo en `tbl_contratos` | `estado = 'activo'` en contratos |
| `tbl_consultor` | `ConsultantModel` | — | — |
| `tbl_hallazgos_locativos` | **NO EXISTE AÚN** | — | Se crea en fase de inspecciones locativas |

---

## 5. Estructura del PDF Generado

El PDF mantiene el mismo formato profesional del actual (FT-SST-007) pero generado con DOMPDF:

```
┌─────────────────────────────────────────────────────────┐
│ [LOGO CLIENTE]  SISTEMA DE GESTIÓN DE    Código: FT-SST-007 │
│                 SEGURIDAD Y SALUD EN     Versión: 001        │
│                 EL TRABAJO                                    │
│                 ACTA DE REUNIÓN          Fecha: 1/04/2024    │
├─────────────────────────────────────────────────────────┤
│                                                         │
│          ACTA DE VISITA Y SEGUIMIENTO AL SISTEMA        │
│                                                         │
│  MOTIVO: [motivo]              HORARIO: [hora]          │
│  CLIENTE: [nombre] - [fecha]   FECHA: [fecha]           │
│                                                         │
├─── 1. INTEGRANTES ──────────────────────────────────────┤
│  NOMBRE           │ ROL                │ FIRMA          │
│  [integrante 1]   │ [rol 1]           │ [img firma]    │
│  [integrante 2]   │ [rol 2]           │ [img firma]    │
│  ...              │ ...               │ ...            │
│                                                         │
├─── Temas Abiertos y Vencidos ───────────────────────────┤
│  HALLAZGOS LOCATIVOS:                                   │
│  ✅ Sin hallazgos / ⚠️ Lista de hallazgos               │
│                                                         │
│  MANTENIMIENTOS:                                        │
│  ✅ Sin mantenimientos / ⚠️ Lista de mantenimientos      │
│                                                         │
│  PENDIENTES:                                            │
│  ✅ Sin pendientes / • pendiente 1 — fecha              │
│                                                         │
├─── 2. TEMAS ────────────────────────────────────────────┤
│  TEMA 1: [descripción]                                  │
│  TEMA 2: [descripción]                                  │
│  ...                                                    │
│                                                         │
├─── 4. OBSERVACIONES ────────────────────────────────────┤
│  [texto observaciones]                                  │
│                                                         │
├─── 5. CARTERA ──────────────────────────────────────────┤
│  [estado cartera]                                       │
│                                                         │
├─── 6. COMPROMISOS ──────────────────────────────────────┤
│  ACTIVIDAD        │ FECHA DE CIERRE │ RESPONSABLE       │
│  [actividad 1]    │ [fecha]         │ [responsable]     │
│  ...              │ ...             │ ...               │
├─── 7. REGISTRO FOTOGRAFICO ────────────────────────────┤
│  [foto1]  [foto2]  [foto3]                              │
│   desc.    desc.    desc.                               │
│  (3 columnas, solo si hay fotos adjuntas)               │
├─────────────────────────────────────────────────────────┤
│  FIRMAS                                                 │
│  _______________  _______________  _______________       │
│  ADMINISTRADOR    VIGIA SST        CONSULTOR            │
└─────────────────────────────────────────────────────────┘
```

---

## 6. Validaciones

### En el formulario (frontend)
- **Cliente:** Obligatorio
- **Fecha:** Obligatorio, default hoy
- **Hora:** Obligatorio, default ahora
- **Motivo:** Obligatorio, mínimo 5 caracteres
- **Al menos 1 integrante:** Obligatorio
- **Al menos 1 tema:** Obligatorio
- **Cartera:** Obligatorio

### Al finalizar (backend)
- Firma del consultor: Obligatoria siempre
- Firma del administrador: Obligatoria si hay integrante con rol ADMINISTRADOR
- Firma del vigía: Obligatoria si hay integrante con rol VIGÍA SST
- Validación de firma (>100 píxeles oscuros) para evitar firmas accidentales

### Estados y Flujo de Finalizacion

La **firma es el candado** — ningun documento incompleto puede llegar al sistema de reportes.

```
BORRADOR ──guardar (N veces)──> BORRADOR
    │
    │  "Ir a firmas" (solo si validacion minima OK)
    ▼
PENDIENTE_FIRMA ──firman todos──> todas las firmas recolectadas
    │
    │  "Finalizar y generar PDF" + SweetAlert confirmacion
    ▼
COMPLETO ──── PDF generado + cargado a tbl_reporte (BLOQUEADO)
```

| Estado | Descripcion | Editable | PDF | En tbl_reporte |
|--------|-------------|----------|-----|----------------|
| `borrador` | Datos guardados, trabajo en progreso | Si | No | No |
| `pendiente_firma` | Datos completos, esperando firmas | Solo firmas | No | No |
| `completo` | Firmado, PDF generado, cargado al sistema | No (bloqueado) | Si | Si |

**5 capas de proteccion contra documentos incompletos:**

| Capa | Mecanismo | Que previene |
|------|-----------|--------------|
| 1 | Estado `borrador` | Guardar NUNCA genera PDF ni carga a reportes |
| 2 | Validacion minima para firmas | No se puede ir a firmas sin datos basicos (fecha, cliente, 1 tema) |
| 3 | Firmas como candado | No se puede generar PDF sin las firmas obligatorias |
| 4 | SweetAlert confirmacion explicita | "Confirmo que el acta esta completa" con preview |
| 5 | Bloqueo post-finalizacion | Una vez `completo`, no se puede editar (admin puede reabrir) |

**Escenario real multi-inspeccion:**
```
Consultor en campo:
  → Abre extintores, llena 5 de 20, GUARDA (estado: borrador)
  → Va al ala opuesta, abre botiquin, llena todo, GUARDA (borrador)
  → Vuelve a extintores, continua, GUARDA (borrador)
  → Termina extintores → Ir a firmas → Firma → Finalizar
  → Solo AHORA se genera PDF y se carga a reportes
```

**ENUM en BD:**
```sql
estado ENUM('borrador', 'pendiente_firma', 'completo') NOT NULL DEFAULT 'borrador'
```

### Vista de Documentos Pendientes (Dashboard)

Los borradores y documentos pendientes de firma se muestran en **3 lugares**:

| Vista | Ubicacion | Que muestra | Acciones |
|-------|-----------|-------------|----------|
| Dashboard consultor (PC) | `/dashboardconsultant` — nueva seccion debajo de acceso rapido | Borradores y pendientes de firma **del consultor** | Editar, Ir a firmas, Eliminar |
| Dashboard admin (PC) | `/admindashboard` — nueva seccion debajo de acceso rapido | Borradores y pendientes de firma **de TODOS los consultores** | Ver, Editar, Reasignar |
| Dashboard PWA (movil) | `/inspecciones` — seccion superior del dashboard | Borradores y pendientes de firma **del consultor** | Continuar, Firmar |

**Mockup tabla PC (consultor y admin):**

```
┌──────────────────────────────────────────────────────────────────────────────────────┐
│  Documentos Pendientes de Inspeccion                                    [Ver todos]  │
├───────┬──────────────────┬───────────────┬──────────────┬────────────┬───────────────┤
│ Tipo  │ Cliente          │ Fecha         │ Estado       │ Consultor  │ Acciones      │
├───────┼──────────────────┼───────────────┼──────────────┼────────────┼───────────────┤
│ Acta  │ Los Tucanes      │ 22/02/2026    │ 📝 Borrador  │ E. Cuervo  │ ✏️ 🗑️        │
│ Acta  │ El Zorzal        │ 21/02/2026    │ ✍️ Pend.Firma│ E. Cuervo  │ ✍️ 👁️        │
│ Extin.│ Jacaranda        │ 20/02/2026    │ 📝 Borrador  │ E. Cuervo  │ ✏️ 🗑️        │
└───────┴──────────────────┴───────────────┴──────────────┴────────────┴───────────────┘
```

**Nota:** La columna "Consultor" solo aparece en el dashboard admin (superadmin ve todos). En el dashboard del consultor se omite esa columna.

---

## 7. Integraciones

### 7.1 Con el Panel Admin (PC)
- Las actas generadas aparecen en el panel del cliente bajo una nueva sección "Actas de Visita"
- El admin/consultor puede ver y descargar los PDFs desde el PC
- Posibilidad de vincular acta con actividad del Plan de Trabajo (`tbl_pta_cliente`)

### 7.2 Con el Sistema de Pendientes (CRITICO)

En AppSheet, los compromisos del acta **SON** directamente entradas en `tbl_pendientes`.
El template usa `<<Start: [Related tbl_pendientes]>>` para listar los compromisos en el PDF.
Columna 42 del AppSheet: `Related tbl_pendientes` (List, fórmula `REF_ROWS("tbl_pend...")`)

**Diseno nuevo:** Igual que AppSheet. Los compromisos se crean directamente en `tbl_pendientes` con referencia al acta.

- Se agrega columna `id_acta_visita` a `tbl_pendientes` (FK nullable, solo para los que vienen de un acta)
- NO se crea tabla separada `tbl_acta_visita_compromisos` — es redundante
- Al llenar la seccion "Compromisos" del acta, se insertan en `tbl_pendientes` con `id_acta_visita` = id del acta
- El PDF los lista con la misma query: `WHERE id_acta_visita = ?`
- Estos pendientes aparecen tambien en el modulo de pendientes existente (doble visibilidad)

### 7.3 Compartir
- Botón "Compartir por WhatsApp" (usa Web Share API o link directo `wa.me`)
- Botón "Enviar por Email" (usa SendGrid, mismo patrón existente)
- El PDF se comparte como link público temporal o se adjunta

---

## 8. Archivos a Crear/Modificar

### Nuevos archivos
| Archivo | Descripción |
|---------|-------------|
| `app/Controllers/Inspecciones/InspeccionesController.php` | Dashboard PWA |
| `app/Controllers/Inspecciones/ActaVisitaController.php` | CRUD acta de visita |
| `app/Models/ActaVisitaModel.php` | Modelo principal |
| `app/Models/ActaVisitaIntegranteModel.php` | Integrantes del acta |
| `app/Models/ActaVisitaTemaModel.php` | Temas del acta |
| `app/Models/ActaVisitaFotoModel.php` | Fotos adjuntas |
| `app/Views/inspecciones/layout_pwa.php` | Layout mobile |
| `app/Views/inspecciones/dashboard.php` | Dashboard inspecciones |
| `app/Views/inspecciones/acta_visita/create.php` | Formulario crear |
| `app/Views/inspecciones/acta_visita/edit.php` | Formulario editar |
| `app/Views/inspecciones/acta_visita/list.php` | Listado |
| `app/Views/inspecciones/acta_visita/view.php` | Vista previa |
| `app/Views/inspecciones/acta_visita/firma.php` | Pantalla firmas |
| `app/Views/inspecciones/acta_visita/pdf.php` | Template PDF |
| `app/Views/inspecciones/acta_visita/confirmacion.php` | Post-firma |
| `public/manifest_inspecciones.json` | PWA manifest |
| `public/sw_inspecciones.js` | Service Worker |
| `app/SQL/migrate_inspecciones.php` | Script migración BD |

### Archivos a modificar
| Archivo | Cambio |
|---------|--------|
| `app/Config/Routes.php` | Agregar grupo `/inspecciones/*` |
| `app/Config/Filters.php` | Agregar rutas inspecciones al filtro `auth` |

---

## 9. Template AppSheet Original (Referencia)

Este era el template de generación de PDF en AppSheet. Sirve como referencia para replicar el formato exacto en DOMPDF:

```
<<[LOGO_CLIENTE]>>            | SISTEMA DE GESTIÓN DE SEGURIDAD Y SALUD EN EL TRABAJO | Código: FT-SST-007
                              | ACTA DE REUNIÓN                                        | Versión: 001
                              |                                                        | Fecha: <<[FECHA_SGSST]>>

              ACTA DE VISITA Y SEGUIMIENTO AL SISTEMA

MOTIVO: <<[MOTIVO]>>                    HORARIO: <<[HORA]>>
CLIENTE: <<[NOMBRE_VISIBLE]>>           FECHA: <<[FECHA]>>

1. INTEGRANTES
NOMBRE                    | ROL                  | FIRMA
<<[INTEGRANTE 1]>>        | <<[ROL 1]>>          | <<[FIRMA DEL ADMINISTRADOR]>>
<<[INTEGRANTE 2]>>        | <<[ROL 2]>>          | <<[FIRMA DEL CONSULTOR]>>
<<[INTEGRANTE 3].[NOMBRE COMPLETO]>> | <<[ROL 3]>> | <<[FIRMA DEL VIGIA]>>

Temas Abiertos y Vencidos:
  HALLAZGOS LOCATIVOS:    <<[resumen_hallazgos_abiertos]>>
  MANTENIMIENTOS:         <<[resumen_mantenimientos_vencidos]>>
  PENDIENTES:             <<[resumen_pendientes_pdf]>>

2. TEMAS:
  <<IF: ISNOTBLANK([TEMA 1])>> TEMA 1: <<[TEMA 1]>> <<ENDIF>>
  <<IF: ISNOTBLANK([TEMA 2])>> TEMA 2: <<[TEMA 2]>> <<ENDIF>>
  ... (hasta TEMA 5)

4. OBSERVACIONES:         <<[OBSERVACIONES]>>
5. CARTERA:               <<[CARTERA]>>

6. COMPROMISOS
ACTIVIDAD              | FECHA DE CIERRE    | RESPONSABLE
<<Start: [Related tbl_pendientes]>>
<<[tarea_actividad]>>  | <<[fecha_cierre]>> | <<[responsable]>>
<<End>>
```

**Notas clave del template:**

- `INTEGRANTE 3` es tipo Ref (referencia a tabla VIGIA SST): `<<[INTEGRANTE 3].[NOMBRE COMPLETO]>>`
- Las firmas están fijas por posición: Admin=1, Consultor=2, Vigía=3
- `LOGO_CLIENTE` y `FECHA_SGSST` son columnas virtuales calculadas desde `tbl_clientes`
- `NOMBRE_VISIBLE` = `CONCATENATE(ANY(...))` — nombre completo del cliente con formato
- `resumen_*` = columnas calculadas con `IF(ISBLANK(SELECT(...)))` sobre Google Sheets puente
- **COMPROMISOS = Related tbl_pendientes** — los compromisos se crean directamente como pendientes

### Columnas AppSheet completas (54 columnas)

| # | Columna | Tipo | Editable | Notas |
|---|---------|------|----------|-------|
| 1 | _RowNumber | Number | No | Auto |
| 2 | ID | Text (KEY) | Si | UUID de AppSheet |
| 3 | FECHA DE REGISTRO | DateTime | Si | Auto timestamp |
| 4 | FECHA | Date (LABEL) | Si | Fecha de la visita |
| 5 | HORA | Time | Si | Hora de la visita |
| 6 | AÑO DE VISITA | Number | No | Derivado |
| 7 | MES DE VISITA | Number | No | Derivado |
| 8 | CLIENTE | Ref | Si | FK a tbl_clientes |
| 9 | UBICACION | LatLong | Si | GPS |
| 10 | MOTIVO | Text | Si | Motivo visita |
| 11-12 | INTEGRANTE 1, ROL 1 | Ref, Enum | Si | Administrador |
| 13-14 | INTEGRANTE 2, ROL 2 | Ref, Text | Si | Consultor |
| 15-16 | INTEGRANTE 3, ROL 3 | Ref, Text | Si | Vigía (IF show) |
| 17-18 | INTEGRANTE 4, ROL 4 | Text, Text | Si | Opcional (IF show) |
| 19-23 | TEMA 1-5 | Text | Si | Temas (2-5 con IF show) |
| 24 | CARTERA | Text | Si | Estado cartera |
| 25 | OBSERVACIONES | Text | Si | Observaciones |
| 26 | PROXIMA REUNIÓN | Date | Si | Nullable |
| 27 | HORA PRÓXIMA REUNIÓN | Time | Si | Nullable |
| 28 | MODALIDAD | Enum | Si | Presencial/Virtual |
| 29 | FIRMA DEL ADMINISTRADOR | Signature | Si | Canvas firma |
| 30 | FIRMA DEL VIGIA | Signature | Si | Canvas firma |
| 31 | FIRMA DEL CONSULTOR | Signature | Si | Canvas firma |
| 32 | GENERAR INFORME | Text | Si | Action button |
| 33 | SOPORTES | Enum | Si | Tipo soporte |
| 34 | SOPORTE LAVADO DE TANQUES | Drawing | Si | Adjunto (IF show) |
| 35 | SOPORTE PLAGAS | Drawing | Si | Adjunto (IF show) |
| 36-38 | FOTO 1-3 SEG SOC | Drawing | Si | Fotos (IF show) |
| 39 | CLIENTESSTPH | Text | No | `LOOKUP([CLIENTE], ...)` |
| 40 | ESTADO | Text | No | COMPLETO/PENDIENTE |
| 41 | AGENDA_ID | Text | Si | Link a agenda |
| 42 | Related tbl_pendientes | List | No | `REF_ROWS("tbl_pend...")` |
| 43 | LOGO_CLIENTE | Image | No | `ANY(SELECT(CLIEN...))` |
| 44 | FECHA_SGSST | Date | No | `ANY(SELECT(CLIEN...))` |
| 45 | NOMBRE_VISIBLE | LongText | No | `CONCATENATE(ANY(...))` |
| 46 | NombreCliente_Puente | Text | No | `ANY(SELECT(tbl_c...))` |
| 47 | pendientes_abiertos_cliente | LongText | No | `IF(ISBLANK([pend...]))` |
| 48 | resumen_pendientes_pdf | LongText | No | `IF(ISBLANK(SELECT(...)))` |
| 49 | resumen_mantenimiento | LongText | No | `IF(ISBLANK(SELECT(...)))` |
| 50 | resumen_hallazgos_abiertos | LongText | No | `IF(ISBLANK(SELECT(...)))` |
| 51 | prueba_comparacion | Text | No | Debug column |
| 52 | COLUMNA TEMPORAL | Text | No | Concatenación temporal |
| 53 | NombreCliente_Texto | LongText | No | `LOOKUP([CLIENTE], ...)` |
| 54 | BOTON_AGENDA | Show | No | HTML link a agenda |

---

## 10. Migración de Datos AppSheet (Opcional)

Si se quiere migrar los datos históricos de AppSheet:
1. Exportar tabla AppSheet como CSV
2. Script PHP de importación que mapea los campos
3. Las firmas (imágenes) se descargan y reubican

**Recomendación:** Migrar solo si hay menos de 100 registros y las firmas están accesibles. Si no, arrancar desde cero y dejar AppSheet como archivo histórico.
