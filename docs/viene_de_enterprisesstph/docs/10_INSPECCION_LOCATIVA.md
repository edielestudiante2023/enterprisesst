# 10 - Inspección Locativa

## Estado: FUNCIONAL (2026-02-24)

Módulo de inspección locativa para la PWA de inspecciones SST. Migración desde AppSheet. NO requiere firmas.

---

## Origen en AppSheet

En AppSheet se manejaban 2 tablas porque no soporta `foreach` dentro de un registro:

- **TABLA_MAESTRA_INSPECCIONES** (27 registros): ID, fecha, cliente, consultor, observaciones. También referenciaba PLAN DE EMERGENCIA.
- **TBL_HALLAZGOS_LOCATIVOS** (100+ registros): ID_HALLAZGO, ID_INSPECCION (FK), descripción, imagen (Drawing), fecha, imagen corrección, fecha corrección, estado (Enum: ABIERTA), observaciones.

En PHP+DOMPDF un simple `foreach` resuelve la iteración. Mantenemos 2 tablas SQL por diseño relacional pero es **una sola experiencia** en el formulario.

### Código formato AppSheet
- **Código**: FT-SST-216
- **Versión**: 001

---

## Arquitectura

### Tablas SQL

```sql
tbl_inspeccion_locativa
├── id (INT, PK, AI)
├── id_cliente (INT, FK → tbl_clientes)
├── id_consultor (INT, FK → tbl_consultor)
├── fecha_inspeccion (DATE)
├── observaciones (TEXT)
├── ruta_pdf (VARCHAR 255)
├── estado (ENUM: borrador, completo)
├── created_at, updated_at

tbl_hallazgo_locativo
├── id (INT, PK, AI)
├── id_inspeccion (INT, FK → tbl_inspeccion_locativa, CASCADE)
├── descripcion (TEXT)
├── imagen (VARCHAR 255) — foto del hallazgo
├── imagen_correccion (VARCHAR 255) — foto corrección (opcional)
├── fecha_hallazgo (DATE)
├── fecha_correccion (DATE)
├── estado (VARCHAR 50) — ABIERTO, CERRADO, TIEMPO EXCEDIDO SIN RESPUESTA
├── observaciones (TEXT)
├── orden (TINYINT)
├── created_at
```

### Flujo (sin firmas)

```
borrador
├── Editar (CRUD)
├── Eliminar
└── Finalizar → genera PDF + upload reportes → completo

completo
├── Ver (read-only)
├── Ver PDF
└── No editar/eliminar
```

### Archivos

| Archivo | Rol |
|---------|-----|
| `app/SQL/migrate_inspeccion_locativa.php` | Migración 2 tablas |
| `app/Models/InspeccionLocativaModel.php` | Modelo maestro |
| `app/Models/HallazgoLocativoModel.php` | Modelo hallazgos |
| `app/Controllers/Inspecciones/InspeccionLocativaController.php` | CRUD + PDF + reportes |
| `app/Views/inspecciones/inspeccion_locativa/list.php` | Listado cards |
| `app/Views/inspecciones/inspeccion_locativa/form.php` | Formulario create/edit |
| `app/Views/inspecciones/inspeccion_locativa/view.php` | Vista read-only + galería fotos |
| `app/Views/inspecciones/inspeccion_locativa/pdf.php` | Template DOMPDF |
| `uploads/inspecciones/locativas/hallazgos/` | Fotos hallazgos |
| `uploads/inspecciones/locativas/pdfs/` | PDFs generados |

### Integración reportes

- `id_report_type = 6`, `id_detailreport = 10` (locativas; acta_visita=9, señalización=11, extintores=12, botiquín=13)
- Mismo patrón `uploadToReportes()` de ActaVisitaController

---

## PDF — Diseño

```
┌─────────────────────────────────────────────────────────┐
│  [LOGO]  │  SISTEMA DE GESTION DE SEGURIDAD  │ Codigo  │
│          │  Y SALUD EN EL TRABAJO             │ FT-SST  │
│          │  FORMATO DE INSPECCIÓN LOCATIVA    │ -216    │
│          │                                     │ Version │
│          │                                     │ 001     │
├─────────────────────────────────────────────────────────┤
│                                                         │
│     INSPECCIÓN DE CONDICIONES LOCATIVAS                 │
│                                                         │
├─────────────────────────────────────────────────────────┤
│  FECHA: dd/mm/yyyy    │  CLIENTE: xxx                   │
│  CONSULTOR: xxx       │                                 │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  [Texto introductorio estático - importancia de las     │
│   inspecciones locativas en propiedad horizontal]       │
│                                                         │
├─────────────────────────────────────────────────────────┤
│  TABLA DE HALLAZGOS                                     │
│  ┌──────────────────┬──────────────┬──────────┐         │
│  │ HALLAZGO         │ IMAGEN       │ FECHA    │         │
│  ├──────────────────┼──────────────┼──────────┤         │
│  │ foreach hallazgo │ [foto base64]│ dd/mm/yy │         │
│  └──────────────────┴──────────────┴──────────┘         │
├─────────────────────────────────────────────────────────┤
│  OBSERVACIONES: xxx                                     │
└─────────────────────────────────────────────────────────┘
```

El texto introductorio es contenido estático (hardcoded en el template) extraído del formato AppSheet original, sobre importancia de las inspecciones locativas en SST.

---

## Roadmap hacia Plan de Emergencia

Cada inspección es un módulo independiente. El Plan de Emergencia (fase 10) consolida todas jalando la última de cada tipo por cliente.

```
Fase 1:  ✅ Acta de Visita (funcional v2)
Fase 2:  ✅ Inspección Locativa (funcional)
Fase 3:  ✅ Señalización (37 ítems fijos, calificación)
Fase 4:  ✅ Extintores (N dinámico, 12 criterios, PDF landscape)
Fase 5:  ✅ Botiquín (32 elementos fijos, cantidades)
Fase 6:     Gabinetes contra incendio (condicional: solo si tiene)
Fase 7:     Equipos de Comunicación
Fase 8:     Recursos de Seguridad
Fase 9:     Matriz Vulnerabilidad + Probabilidad Peligros
Fase 10:    PLAN DE EMERGENCIA (documento maestro ~50 pág, 118 campos)
```

### Relación con PLAN DE EMERGENCIA (AppSheet)

La tabla PLAN DE EMERGENCIA (118 columnas) es el documento maestro que:
- Tiene datos fijos del conjunto (estructura, parqueaderos, fotos, teléfonos emergencia)
- Tiene texto legal boilerplate extenso (legislación, definiciones, PONs)
- Jala la ÚLTIMA inspección de cada tipo via fórmulas `SELECT()`:
  - `HALLAZGOS_DE_LA_ULTIMA_INSP_LOCATIVA` → hallazgos locativos
  - `ULTIMA_MATRIZ_LISTA` → evaluación vulnerabilidad (25 ítems con calificación)
  - `ULTIMA_PROBABILIDAD_LISTA` → probabilidad por tipo de peligro
  - `ULTIMO_INVENTARIO_EXTINCION_LISTA` → inventario extintores (3 inspecciones detalladas)
  - `ULTIMA_INSP_BOTIQUIN` → inventario botiquín (~50 elementos con cantidad/estado)
  - `ULTIMO_RECURSOS_SEGURIDAD` → lámparas, antideslizantes, cámaras, etc.
  - `ULTIMO_EQUIP_COMUNICACION` → teléfonos, radios, CCTV, megafonía
  - `ULTIMO_GABINETES` → gabinetes contra incendio (condicional)

Cuando se implemente fase 10, el controlador PlanEmergenciaController consultará todas las tablas de inspecciones (`tbl_inspeccion_locativa`, `tbl_inspeccion_extintores`, etc.) para generar el PDF consolidado.
