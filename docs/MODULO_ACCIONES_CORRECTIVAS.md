# Modulo de Acciones Correctivas (CAPA)

## Descripcion General

El modulo de Acciones Correctivas implementa un sistema completo de gestion CAPA (Corrective and Preventive Actions) para el cumplimiento de los numerales 7.1.1, 7.1.2, 7.1.3 y 7.1.4 de la Resolucion 0312 de 2019 del Sistema de Gestion de Seguridad y Salud en el Trabajo (SG-SST) en Colombia.

### Numerales Cubiertos

| Numeral | Descripcion | Tipos de Origen |
|---------|-------------|-----------------|
| 7.1.1 | Acciones resultados SG-SST | Auditoria interna, Revision por direccion, Autoevaluacion |
| 7.1.2 | Efectividad medidas de prevencion | Indicador AT, Indicador EL, Indicador ATEL |
| 7.1.3 | Investigacion ATEL | Investigacion accidente, Investigacion incidente, Investigacion enfermedad |
| 7.1.4 | Requerimientos ARL/Autoridades | Requerimiento ARL, Requerimiento Min. Trabajo, Visita SST |

---

## Arquitectura del Sistema

### Patron MVC + Service Layer

```
┌─────────────────────────────────────────────────────────────────┐
│                         RUTAS                                    │
│                  app/Config/Routes.php                          │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                      CONTROLADOR                                 │
│           AccionesCorrectivasController.php                      │
│  - Maneja peticiones HTTP                                        │
│  - Valida permisos de acceso                                     │
│  - Delega logica de negocio al Service                          │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                       SERVICIO                                   │
│            AccionesCorrectivasService.php                        │
│  - Logica de negocio centralizada                               │
│  - Calculo de KPIs y estadisticas                               │
│  - Orquestacion entre modelos                                    │
│  - Integracion con IA (5 Porques)                               │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                        MODELOS                                   │
│  ┌─────────────────┐  ┌─────────────────┐                       │
│  │ AccHallazgosModel│  │ AccAccionesModel │                      │
│  │ tbl_acc_hallazgos│  │ tbl_acc_acciones │                      │
│  └─────────────────┘  └─────────────────┘                       │
│  ┌─────────────────┐  ┌─────────────────┐                       │
│  │AccVerificaciones│  │AccSeguimientos  │                       │
│  │     Model       │  │     Model       │                       │
│  │tbl_acc_verific..│  │tbl_acc_seguim.. │                       │
│  └─────────────────┘  └─────────────────┘                       │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                         VISTAS                                   │
│              app/Views/acciones_correctivas/                     │
│  ├── dashboard.php        (Vista general por cliente)           │
│  ├── index.php            (Lista de hallazgos/acciones)         │
│  ├── por_numeral.php      (Filtrado por numeral)                │
│  ├── hallazgos/                                                  │
│  │   ├── index.php        (Lista de hallazgos)                  │
│  │   ├── crear.php        (Formulario nuevo hallazgo)           │
│  │   └── ver.php          (Detalle de hallazgo)                 │
│  ├── acciones/                                                   │
│  │   ├── crear.php        (Formulario nueva accion)             │
│  │   ├── ver.php          (Detalle + seguimiento)               │
│  │   └── analisis_causa_raiz.php (Chat IA 5 Porques)            │
│  └── reportes/                                                   │
│      ├── pdf.php          (Reporte PDF auditoria)               │
│      └── excel.php        (Exportacion Excel)                   │
└─────────────────────────────────────────────────────────────────┘
```

---

## Estructura de Base de Datos

### Diagrama ER

```
┌─────────────────────┐       ┌─────────────────────┐
│   tbl_clientes      │       │    tbl_usuarios     │
│   (id_cliente)      │       │    (id_usuario)     │
└─────────┬───────────┘       └──────────┬──────────┘
          │                              │
          │ 1:N                          │ 1:N (responsable)
          ▼                              │
┌─────────────────────────────────────────────────────┐
│              tbl_acc_hallazgos                       │
├─────────────────────────────────────────────────────┤
│ id_hallazgo (PK)                                     │
│ id_cliente (FK)                                      │
│ titulo                                               │
│ descripcion                                          │
│ numeral_asociado (7.1.1, 7.1.2, 7.1.3, 7.1.4)       │
│ tipo_origen (auditoria_interna, indicador_at, etc.) │
│ severidad (critico, mayor, menor, observacion)      │
│ estado (abierto, en_tratamiento, cerrado)           │
│ fecha_deteccion                                      │
│ evidencia_inicial (ruta archivo)                    │
│ reportado_por (FK usuarios)                         │
│ created_at, updated_at                              │
└─────────────────────┬───────────────────────────────┘
                      │
                      │ 1:N
                      ▼
┌─────────────────────────────────────────────────────┐
│              tbl_acc_acciones                        │
├─────────────────────────────────────────────────────┤
│ id_accion (PK)                                       │
│ id_hallazgo (FK)                                     │
│ tipo_accion (correctiva, preventiva, mejora)        │
│ descripcion_accion                                   │
│ causa_raiz                                           │
│ analisis_causa_raiz (JSON - historial IA)           │
│ responsable_id (FK usuarios)                        │
│ responsable_nombre                                   │
│ estado (borrador, asignada, en_ejecucion,           │
│         en_revision, en_verificacion,               │
│         cerrada_efectiva, cerrada_no_efectiva,      │
│         cancelada, reabierta)                       │
│ fecha_asignacion                                     │
│ fecha_compromiso                                     │
│ fecha_cierre_real                                    │
│ created_at, updated_at                              │
└──────────┬──────────────────────────┬───────────────┘
           │                          │
           │ 1:N                      │ 1:N
           ▼                          ▼
┌─────────────────────┐    ┌─────────────────────────┐
│tbl_acc_seguimientos │    │ tbl_acc_verificaciones  │
├─────────────────────┤    ├─────────────────────────┤
│ id_seguimiento (PK) │    │ id_verificacion (PK)    │
│ id_accion (FK)      │    │ id_accion (FK)          │
│ tipo_seguimiento:   │    │ fecha_verificacion      │
│  - avance           │    │ es_efectiva (bool)      │
│  - evidencia        │    │ observaciones           │
│  - comentario       │    │ criterios_evaluados     │
│  - cambio_estado    │    │ evidencia_verificacion  │
│  - recordatorio     │    │ verificado_por (FK)     │
│  - solicitud_prorr. │    │ created_at              │
│ descripcion         │    └─────────────────────────┘
│ porcentaje_avance   │
│ archivo_adjunto     │
│ nombre_archivo      │
│ tipo_archivo        │
│ registrado_por      │
│ created_at          │
└─────────────────────┘
```

---

## Flujo de Estados

### Estados de Hallazgos

```
┌─────────┐     Crear      ┌──────────────┐    Asignar    ┌─────────────────┐
│ (nuevo) │ ──────────────▶│   ABIERTO    │──────────────▶│ EN_TRATAMIENTO  │
└─────────┘                └──────────────┘               └────────┬────────┘
                                                                   │
                                                    Cerrar todas   │
                                                    las acciones   │
                                                                   ▼
                                                          ┌─────────────┐
                                                          │   CERRADO   │
                                                          └─────────────┘
```

### Estados de Acciones (Ciclo Completo)

```
┌──────────┐
│ BORRADOR │ ◄───────────────────────────────────────────┐
└────┬─────┘                                             │
     │ Asignar responsable                               │
     ▼                                                   │
┌──────────┐                                             │
│ ASIGNADA │                                             │
└────┬─────┘                                             │
     │ Iniciar ejecucion                                 │
     ▼                                                   │
┌─────────────┐                                          │
│ EN_EJECUCION│ ◄──────────────────────────────┐        │
└────┬────────┘                                │        │
     │ Completar (100%)                        │        │
     ▼                                         │        │
┌─────────────┐    Requiere mas trabajo        │        │
│ EN_REVISION │ ───────────────────────────────┘        │
└────┬────────┘                                         │
     │ Aprobar para verificacion                        │
     ▼                                                  │
┌────────────────┐                                      │
│EN_VERIFICACION │                                      │
└────┬───────────┘                                      │
     │                                                  │
     ├──── Verificar EFECTIVA ────▶ ┌─────────────────┐│
     │                              │ CERRADA_EFECTIVA ││
     │                              └─────────────────┘│
     │                                                  │
     └─── Verificar NO EFECTIVA ──▶ ┌───────────────────┐
                                    │CERRADA_NO_EFECTIVA│
                                    └────────┬──────────┘
                                             │ Reabrir
                                             ▼
                                    ┌──────────┐
                                    │ REABIERTA│──▶ (vuelve a EN_EJECUCION)
                                    └──────────┘

                    Cancelar (cualquier estado)
                              │
                              ▼
                    ┌──────────┐
                    │CANCELADA │
                    └──────────┘
```

---

## Rutas del Modulo

### Dashboard y Listados

| Metodo | Ruta | Controlador | Descripcion |
|--------|------|-------------|-------------|
| GET | `/acciones-correctivas` | dashboard() | Dashboard general (todos los clientes) |
| GET | `/acciones-correctivas/{idCliente}` | index() | Vista principal del cliente |
| GET | `/acciones-correctivas/{idCliente}/numeral/{numeral}` | porNumeral() | Filtrar por numeral |
| GET | `/acciones-correctivas/{idCliente}/hallazgos` | hallazgos() | Lista de hallazgos |

### Hallazgos

| Metodo | Ruta | Controlador | Descripcion |
|--------|------|-------------|-------------|
| GET | `/acciones-correctivas/{idCliente}/hallazgo/crear` | crearHallazgo() | Form crear hallazgo |
| GET | `/acciones-correctivas/{idCliente}/hallazgo/crear/{numeral}` | crearHallazgo() | Form con numeral preseleccionado |
| POST | `/acciones-correctivas/{idCliente}/hallazgo/guardar` | guardarHallazgo() | Guardar hallazgo |
| GET | `/acciones-correctivas/{idCliente}/hallazgo/{idHallazgo}` | verHallazgo() | Ver detalle hallazgo |

### Acciones

| Metodo | Ruta | Controlador | Descripcion |
|--------|------|-------------|-------------|
| GET | `/acciones-correctivas/{idCliente}/hallazgo/{idHallazgo}/accion/crear` | crearAccion() | Form crear accion |
| POST | `/acciones-correctivas/{idCliente}/hallazgo/{idHallazgo}/accion/guardar` | guardarAccion() | Guardar accion |
| GET | `/acciones-correctivas/{idCliente}/accion/{idAccion}` | verAccion() | Ver detalle accion |
| POST | `/acciones-correctivas/{idCliente}/accion/{idAccion}/cambiar-estado` | cambiarEstadoAccion() | Cambiar estado |

### Seguimiento y Evidencias

| Metodo | Ruta | Controlador | Descripcion |
|--------|------|-------------|-------------|
| POST | `/acciones-correctivas/{idCliente}/accion/{idAccion}/avance` | registrarAvance() | Registrar % avance |
| POST | `/acciones-correctivas/{idCliente}/accion/{idAccion}/evidencia` | subirEvidencia() | Subir archivo o enlace |
| POST | `/acciones-correctivas/{idCliente}/accion/{idAccion}/comentario` | registrarComentario() | Agregar comentario |
| GET | `/acciones-correctivas/evidencia/{idSeguimiento}/descargar` | descargarEvidencia() | Descargar archivo |

### Verificacion de Efectividad

| Metodo | Ruta | Controlador | Descripcion |
|--------|------|-------------|-------------|
| POST | `/acciones-correctivas/{idCliente}/accion/{idAccion}/verificacion` | registrarVerificacion() | Verificar efectividad |

### Analisis de Causa Raiz (IA)

| Metodo | Ruta | Controlador | Descripcion |
|--------|------|-------------|-------------|
| GET | `/acciones-correctivas/{idCliente}/accion/{idAccion}/analisis-causa-raiz` | analisisCausaRaiz() | Vista chat IA |
| POST | `/acciones-correctivas/{idCliente}/accion/{idAccion}/analisis-ia` | procesarAnalisisIA() | Procesar respuesta IA |
| POST | `/acciones-correctivas/{idCliente}/accion/{idAccion}/causa-raiz` | guardarCausaRaiz() | Guardar causa manual |

### Reportes

| Metodo | Ruta | Controlador | Descripcion |
|--------|------|-------------|-------------|
| GET | `/acciones-correctivas/{idCliente}/reporte/pdf` | reportePDF() | Generar PDF |
| GET | `/acciones-correctivas/{idCliente}/reporte/excel` | exportarExcel() | Exportar Excel |

### API (AJAX)

| Metodo | Ruta | Controlador | Descripcion |
|--------|------|-------------|-------------|
| GET | `/acciones-correctivas/{idCliente}/api/estadisticas` | apiEstadisticas() | JSON estadisticas |
| GET | `/acciones-correctivas/{idCliente}/api/hallazgos` | apiHallazgos() | JSON hallazgos |
| GET | `/acciones-correctivas/{idCliente}/api/acciones` | apiAcciones() | JSON acciones |

---

## Modelos y Metodos Principales

### AccHallazgosModel

```php
// Consultas principales
getByCliente(int $idCliente, ?string $numeral = null): array
getById(int $idHallazgo): ?array
getEstadisticas(int $idCliente, ?int $anio = null): array

// Estados
actualizarEstado(int $idHallazgo, string $nuevoEstado): bool
```

### AccAccionesModel

```php
// Consultas principales
getByCliente(int $idCliente, ?string $estado = null, ?string $tipoAccion = null): array
getByHallazgo(int $idHallazgo): array
getByNumeral(int $idCliente, string $numeral): array
getById(int $idAccion): ?array

// Estadisticas
getEstadisticas(int $idCliente, ?int $anio = null): array
getParaNotificacion(int $diasAntes = 3): array

// Actualizaciones
actualizarEstado(int $idAccion, string $nuevoEstado): bool
actualizarCausaRaiz(int $idAccion, string $causaRaiz, ?array $analisis = null): bool
```

### AccSeguimientosModel

```php
// Registro de seguimientos
registrarAvance(int $idAccion, string $descripcion, int $porcentaje, int $userId, ?string $userName): int|false
registrarEvidencia(int $idAccion, string $descripcion, array $archivo, int $userId, ?string $userName): int|false
registrarComentario(int $idAccion, string $comentario, int $userId, ?string $userName): int|false
registrarCambioEstado(int $idAccion, string $estadoAnterior, string $estadoNuevo, int $userId, ?string $notas): int|false
registrarSolicitudProrroga(int $idAccion, string $nuevaFecha, string $justificacion, int $userId, ?string $userName): int|false

// Consultas
getByAccion(int $idAccion): array
getTimeline(int $idAccion): array
getEvidencias(int $idAccion): array
getUltimoPorcentaje(int $idAccion): int
contarEvidencias(int $idAccion): int
```

### AccVerificacionesModel

```php
// Verificacion de efectividad
registrarVerificacion(int $idAccion, bool $esEfectiva, string $observaciones, int $verificadorId, ?string $evidencia): int|false
getByAccion(int $idAccion): array
getEstadisticasEfectividad(int $idCliente, ?int $anio = null): array
```

---

## Servicio Principal

### AccionesCorrectivasService

El servicio centraliza toda la logica de negocio:

```php
// Datos por numeral
getDatosPorNumeral(int $idCliente, string $numeral): array

// KPIs y metricas
calcularKPIs(int $idCliente, ?int $anio = null): array
// Retorna: cierre_a_tiempo, efectividad, dias_promedio, reincidencia

// Reportes
generarReporteAuditoria(int $idCliente, ?int $anio = null): array
// Retorna datos completos para PDF/Excel

// Dashboard
getResumenWidget(int $idCliente): array

// Validacion
validarAccesoCliente(int $idCliente, int $idUsuario, string $rolUsuario): bool

// IA - Analisis 5 Porques
procesarAnalisisIA(int $idAccion, string $respuestaUsuario): array
```

---

## Integracion con IA (5 Porques)

El modulo incluye un sistema de chat con IA para realizar el analisis de causa raiz usando la metodologia de los "5 Porques".

### Flujo del Analisis

1. Usuario inicia el analisis desde la vista de la accion
2. La IA hace la primera pregunta basada en el hallazgo
3. Usuario responde
4. IA analiza y hace siguiente pregunta (hasta 7 iteraciones max)
5. Cuando identifica la causa raiz, la guarda automaticamente
6. Todo el historial se almacena en JSON en `analisis_causa_raiz`

### Estructura del Historial

```json
{
  "dialogo": [
    {"rol": "ia", "mensaje": "Pregunta inicial...", "timestamp": "2024-01-15 10:30:00"},
    {"rol": "usuario", "mensaje": "Respuesta...", "timestamp": "2024-01-15 10:31:00"},
    {"rol": "ia", "mensaje": "Segunda pregunta...", "timestamp": "2024-01-15 10:31:05"}
  ],
  "causa_identificada": true,
  "causa_raiz": "Texto de la causa raiz identificada"
}
```

---

## KPIs Calculados

| KPI | Descripcion | Meta | Formula |
|-----|-------------|------|---------|
| Cierre a Tiempo | % de acciones cerradas antes de fecha compromiso | 85% | (Total - Vencidas) / Total * 100 |
| Efectividad | % de acciones verificadas como efectivas | 80% | Efectivas / Total Verificadas * 100 |
| Dias Promedio | Tiempo promedio de cierre de acciones | 30 dias | AVG(fecha_cierre - fecha_asignacion) |
| Reincidencia | % de acciones que fueron reabiertas | <10% | Reabiertas / Cerradas * 100 |

---

## Permisos y Acceso

| Rol | Acceso |
|-----|--------|
| admin / superadmin | Todos los clientes |
| consultant | Solo clientes asignados (id_consultor) |
| client | Solo su propio cliente |

---

## Archivos y Evidencias

Las evidencias se almacenan en:
```
public/uploads/{nit_cliente}/acciones_correctivas/
```

Tipos soportados:
- Archivos: PDF, Word, Excel, imagenes
- Enlaces externos (hipervinculo)

---

## Proximas Mejoras Sugeridas

1. **Notificaciones por email** - Alertas de vencimiento de acciones
2. **Dashboard grafico** - Charts con Chart.js para KPIs
3. **Firma electronica** - Integrar con modulo de firmas existente
4. **Workflow configurable** - Estados personalizables por cliente
5. **Integracion calendario** - Sincronizar fechas compromiso
6. **App movil** - Registro de evidencias desde campo
7. **Auditoria de cambios** - Log detallado de modificaciones

---

## Referencias

- Resolucion 0312 de 2019 - Estandares Minimos SG-SST
- ISO 45001:2018 - Sistemas de Gestion SST
- Metodologia 5 Porques (5 Whys) - Toyota Production System
