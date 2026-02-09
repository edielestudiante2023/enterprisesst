# MÓDULO 2.10.1 - EVALUACIÓN Y SELECCIÓN DE PROVEEDORES Y CONTRATISTAS

> **Fecha de diseño:** 2026-02-06
> **Estado:** Propuesta arquitectónica
> **Estándar Resolución 0312/2019:** 2.10.1

---

## 1. OBJETIVO DEL MÓDULO

Implementar un sistema completo para la gestión, evaluación y seguimiento de proveedores y contratistas en materia de Seguridad y Salud en el Trabajo, cumpliendo con los requisitos del estándar 2.10.1 de la Resolución 0312 de 2019.

---

## 2. ANÁLISIS DE TIPOS DE USUARIO

### Tipos actuales en el sistema
```
ENUM tipo_usuario: admin, consultant, client, miembro
```

### Propuesta: Agregar nuevo tipo
```sql
ALTER TABLE tbl_usuarios
MODIFY COLUMN tipo_usuario ENUM('admin','consultant','client','miembro','proveedor');
```

El tipo `proveedor` permitirá que los proveedores/contratistas tengan acceso a un portal para:
- Subir documentos SST
- Ver estado de evaluaciones
- Recibir notificaciones
- Firmar compromisos digitalmente

---

## 3. ARQUITECTURA DE BASE DE DATOS

### 3.1 Tabla Principal: Proveedores/Contratistas

```sql
CREATE TABLE tbl_proveedores (
    id_proveedor INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,                    -- Cliente que lo registra

    -- Datos básicos
    tipo ENUM('proveedor','contratista','ambos') NOT NULL,
    razon_social VARCHAR(255) NOT NULL,
    nit VARCHAR(20) NOT NULL,
    direccion VARCHAR(255),
    ciudad VARCHAR(100),
    telefono VARCHAR(50),
    email VARCHAR(150),
    sitio_web VARCHAR(255),

    -- Representante
    representante_legal VARCHAR(150),
    contacto_sst VARCHAR(150),
    contacto_sst_email VARCHAR(150),
    contacto_sst_telefono VARCHAR(50),

    -- Clasificación
    categoria ENUM('critico','importante','normal') DEFAULT 'normal',
    servicios_productos TEXT,                   -- JSON de servicios/productos
    nivel_riesgo ENUM('I','II','III','IV','V'),

    -- Estado del ciclo
    estado ENUM('pendiente','preseleccionado','aprobado','rechazado','suspendido','inactivo') DEFAULT 'pendiente',
    fecha_registro DATE,
    fecha_ultima_evaluacion DATE,
    fecha_proxima_evaluacion DATE,

    -- Puntaje actual
    puntaje_actual DECIMAL(5,2) DEFAULT 0,
    clasificacion_actual ENUM('A','B','C','D') DEFAULT NULL,

    -- Control
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (id_cliente) REFERENCES tbl_clientes(id_cliente),
    UNIQUE KEY uk_proveedor_cliente (nit, id_cliente)
);
```

### 3.2 Tabla: Criterios de Evaluación (Configurables por cliente)

```sql
CREATE TABLE tbl_criterios_evaluacion_proveedor (
    id_criterio INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,

    nombre_criterio VARCHAR(150) NOT NULL,
    descripcion TEXT,
    categoria ENUM('documentacion_sst','cumplimiento_legal','desempeno','calidad','precio') NOT NULL,
    peso_porcentaje DECIMAL(5,2) NOT NULL,      -- Peso en la evaluación total
    es_eliminatorio BOOLEAN DEFAULT FALSE,      -- Si no cumple, rechaza automáticamente
    aplica_a ENUM('proveedor','contratista','ambos') DEFAULT 'ambos',

    orden INT DEFAULT 0,
    activo BOOLEAN DEFAULT TRUE,

    FOREIGN KEY (id_cliente) REFERENCES tbl_clientes(id_cliente)
);
```

### 3.3 Tabla: Evaluaciones (Registro histórico)

```sql
CREATE TABLE tbl_evaluaciones_proveedor (
    id_evaluacion INT AUTO_INCREMENT PRIMARY KEY,
    id_proveedor INT NOT NULL,
    id_cliente INT NOT NULL,

    tipo_evaluacion ENUM('inicial','periodica','incidente','reevaluacion') NOT NULL,
    fecha_evaluacion DATE NOT NULL,
    evaluador_id INT,                           -- Usuario que evalúa
    evaluador_nombre VARCHAR(150),

    -- Resultados
    puntaje_total DECIMAL(5,2),
    clasificacion ENUM('A','B','C','D'),        -- A: Excelente, B: Bueno, C: Regular, D: Deficiente
    decision ENUM('aprobado','aprobado_condicional','rechazado','requiere_mejora'),

    -- Observaciones
    observaciones TEXT,
    compromisos_mejora TEXT,                    -- JSON con compromisos
    fecha_seguimiento DATE,

    -- Control
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_proveedor) REFERENCES tbl_proveedores(id_proveedor),
    FOREIGN KEY (id_cliente) REFERENCES tbl_clientes(id_cliente)
);
```

### 3.4 Tabla: Detalle de Evaluación (Puntajes por criterio)

```sql
CREATE TABLE tbl_evaluacion_detalle (
    id_detalle INT AUTO_INCREMENT PRIMARY KEY,
    id_evaluacion INT NOT NULL,
    id_criterio INT NOT NULL,

    puntaje INT NOT NULL,                       -- 0-100
    cumple BOOLEAN,
    observacion TEXT,
    evidencia_url VARCHAR(500),                 -- Link a documento/evidencia

    FOREIGN KEY (id_evaluacion) REFERENCES tbl_evaluaciones_proveedor(id_evaluacion),
    FOREIGN KEY (id_criterio) REFERENCES tbl_criterios_evaluacion_proveedor(id_criterio)
);
```

### 3.5 Tabla: Documentos SST del Proveedor

```sql
CREATE TABLE tbl_documentos_proveedor (
    id_documento INT AUTO_INCREMENT PRIMARY KEY,
    id_proveedor INT NOT NULL,

    tipo_documento ENUM(
        'afiliacion_arl',
        'afiliacion_eps',
        'afiliacion_pension',
        'certificado_aptitud_medica',
        'matriz_peligros',
        'plan_trabajo_sst',
        'reglamento_higiene',
        'politica_sst',
        'constancia_capacitacion',
        'licencia_sst',
        'certificacion_iso',
        'otros'
    ) NOT NULL,

    nombre_documento VARCHAR(255),
    archivo_url VARCHAR(500),
    fecha_emision DATE,
    fecha_vencimiento DATE,
    estado ENUM('vigente','por_vencer','vencido','no_aplica') DEFAULT 'vigente',
    verificado BOOLEAN DEFAULT FALSE,
    verificado_por INT,
    fecha_verificacion DATETIME,
    observaciones TEXT,

    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_proveedor) REFERENCES tbl_proveedores(id_proveedor)
);
```

### 3.6 Tabla: Contratos/Órdenes Activas

```sql
CREATE TABLE tbl_contratos_proveedor (
    id_contrato INT AUTO_INCREMENT PRIMARY KEY,
    id_proveedor INT NOT NULL,
    id_cliente INT NOT NULL,

    numero_contrato VARCHAR(50),
    descripcion_servicio TEXT,
    fecha_inicio DATE,
    fecha_fin DATE,
    valor DECIMAL(15,2),
    estado ENUM('vigente','finalizado','suspendido','cancelado') DEFAULT 'vigente',

    -- Control SST durante ejecución
    requiere_ingreso_instalaciones BOOLEAN DEFAULT FALSE,
    requiere_capacitacion_sst BOOLEAN DEFAULT FALSE,
    capacitacion_realizada BOOLEAN DEFAULT FALSE,
    fecha_capacitacion DATE,

    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_proveedor) REFERENCES tbl_proveedores(id_proveedor),
    FOREIGN KEY (id_cliente) REFERENCES tbl_clientes(id_cliente)
);
```

### 3.7 Tabla: Incidentes/No Conformidades

```sql
CREATE TABLE tbl_incidentes_proveedor (
    id_incidente INT AUTO_INCREMENT PRIMARY KEY,
    id_proveedor INT NOT NULL,
    id_cliente INT NOT NULL,
    id_contrato INT,

    tipo ENUM('incidente_sst','incumplimiento','queja','no_conformidad') NOT NULL,
    fecha_incidente DATE NOT NULL,
    descripcion TEXT NOT NULL,
    gravedad ENUM('leve','moderada','grave','muy_grave') NOT NULL,

    -- Gestión
    accion_tomada TEXT,
    responsable_seguimiento VARCHAR(150),
    fecha_cierre DATE,
    estado ENUM('abierto','en_proceso','cerrado') DEFAULT 'abierto',

    -- Impacto en evaluación
    afecta_evaluacion BOOLEAN DEFAULT TRUE,
    puntos_descuento INT DEFAULT 0,

    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_proveedor) REFERENCES tbl_proveedores(id_proveedor),
    FOREIGN KEY (id_cliente) REFERENCES tbl_clientes(id_cliente)
);
```

---

## 4. CRITERIOS DE EVALUACIÓN BASE

Criterios predeterminados según Resolución 0312/2019:

### Documentación SST (40%)
| Criterio | Peso | Eliminatorio |
|----------|------|--------------|
| Afiliación ARL vigente | 10% | ✅ Sí |
| Afiliación EPS trabajadores | 5% | ✅ Sí |
| Afiliación Pensión trabajadores | 5% | ✅ Sí |
| Certificados aptitud médica ocupacional | 5% | No |
| Matriz de peligros actualizada | 5% | No |
| Política SST firmada | 5% | No |
| Plan de trabajo SST | 5% | No |

### Cumplimiento Legal (25%)
| Criterio | Peso | Eliminatorio |
|----------|------|--------------|
| RUT actualizado | 5% | ✅ Sí |
| Cámara de comercio vigente | 5% | ✅ Sí |
| Antecedentes disciplinarios limpios | 5% | No |
| Licencia SST (si aplica) | 5% | No |
| Certificaciones ISO/OHSAS | 5% | No |

### Desempeño Histórico (20%)
| Criterio | Peso | Eliminatorio |
|----------|------|--------------|
| Historial de incidentes SST | 10% | No |
| Cumplimiento de entregas/servicios | 10% | No |

### Calidad y Precio (15%)
| Criterio | Peso | Eliminatorio |
|----------|------|--------------|
| Calidad del producto/servicio | 10% | No |
| Competitividad en precio | 5% | No |

---

## 5. FLUJO DE ESTADOS

```
                    ┌──────────────┐
                    │  PENDIENTE   │
                    │  (Registro)  │
                    └──────┬───────┘
                           │
                    ┌──────▼───────┐
                    │PRESELECCIONADO│
                    │ (En evaluación)│
                    └──────┬───────┘
                           │
              ┌────────────┼────────────┐
              │            │            │
       ┌──────▼─────┐ ┌────▼────┐ ┌────▼────┐
       │ APROBADO   │ │RECHAZADO│ │REQUIERE │
       │            │ │         │ │ MEJORA  │
       └──────┬─────┘ └─────────┘ └────┬────┘
              │                        │
              │    ┌───────────────────┘
              │    │
       ┌──────▼────▼──┐
       │  SUSPENDIDO  │ (Por incidentes o vencimientos)
       └──────────────┘
```

---

## 6. CLASIFICACIÓN POR PUNTAJE

| Clasificación | Puntaje | Significado | Frecuencia Evaluación |
|--------------|---------|-------------|----------------------|
| **A** | 90-100 | Excelente - Preferencial | Anual |
| **B** | 70-89 | Bueno - Estándar | Semestral |
| **C** | 50-69 | Regular - Con seguimiento | Trimestral |
| **D** | <50 | Deficiente - Suspender/Rechazar | N/A |

---

## 7. ARQUITECTURA DE ARCHIVOS

```
app/
├── Controllers/
│   └── ProveedoresController.php          # CRUD + Evaluaciones
│
├── Models/
│   ├── ProveedorModel.php                 # Modelo principal
│   ├── CriterioEvaluacionModel.php        # Criterios configurables
│   ├── EvaluacionProveedorModel.php       # Evaluaciones
│   ├── DocumentoProveedorModel.php        # Documentos SST
│   ├── ContratoProveedorModel.php         # Contratos activos
│   └── IncidenteProveedorModel.php        # Incidentes/NC
│
├── Services/
│   ├── ProveedorEvaluacionService.php     # Lógica de evaluación
│   ├── ProveedorAlertasService.php        # Alertas de vencimientos
│   └── ProveedorReportesService.php       # Reportes y dashboards
│
├── Views/
│   └── proveedores/
│       ├── index.php                      # Listado con filtros
│       ├── crear.php                      # Registro nuevo
│       ├── ver.php                        # Ficha completa
│       ├── evaluar.php                    # Formulario evaluación
│       ├── documentos.php                 # Gestión documentos
│       ├── historial.php                  # Historial evaluaciones
│       ├── dashboard.php                  # Dashboard resumen
│       └── _components/
│           ├── card_proveedor.php
│           ├── form_evaluacion.php
│           └── tabla_documentos.php
│
└── Libraries/
    └── DocumentosSSTTypes/
        └── ProcedimientoProveedores.php   # Documento formal 2.10.1
```

---

## 8. FUNCIONALIDADES POR ROL

### 8.1 Portal del Proveedor (tipo_usuario = 'proveedor')

- **Autenticación:** Login independiente con credenciales propias
- **Mi Perfil:** Ver y actualizar datos de la empresa
- **Documentos SST:**
  - Subir documentos requeridos
  - Ver estado de cada documento (vigente/por vencer/vencido)
  - Recibir alertas de vencimientos
- **Evaluaciones:**
  - Ver resultados de evaluaciones
  - Ver compromisos de mejora
  - Responder a observaciones
- **Contratos:** Ver contratos activos con cada cliente
- **Notificaciones:** Alertas por email de documentos por vencer

### 8.2 Panel del Cliente (tipo_usuario = 'client')

- **Gestión de Proveedores:**
  - Registrar nuevos proveedores/contratistas
  - Ver listado con filtros (estado, clasificación, tipo)
  - Buscar por nombre, NIT, servicio
- **Configuración de Criterios:**
  - Personalizar criterios de evaluación
  - Definir pesos porcentuales
  - Marcar criterios eliminatorios
- **Evaluaciones:**
  - Realizar evaluación inicial
  - Programar evaluaciones periódicas
  - Registrar reevaluaciones por incidentes
- **Documentos:**
  - Verificar documentos subidos
  - Solicitar documentos faltantes
  - Ver alertas de vencimientos
- **Incidentes:**
  - Registrar incidentes/no conformidades
  - Dar seguimiento a acciones correctivas
- **Reportes:**
  - Dashboard de proveedores
  - Exportar listados a Excel
  - Generar certificados de proveedor aprobado

### 8.3 Panel del Consultor (tipo_usuario = 'consultant')

- Acceso a todos los clientes asignados
- Vista consolidada de proveedores por cliente
- Generación de informes para auditorías
- Configuración de criterios base (plantillas)

---

## 9. DASHBOARD DE PROVEEDORES

```
┌─────────────────────────────────────────────────────────────────┐
│  📊 RESUMEN DE PROVEEDORES Y CONTRATISTAS                       │
├─────────────┬─────────────┬─────────────┬──────────────┬───────┤
│ Total: 25   │ Aprobados:18│ Pendientes:4│ Suspendidos:3│ Rech:0│
├─────────────┴─────────────┴─────────────┴──────────────┴───────┤
│                                                                 │
│  ⚠️ ALERTAS ACTIVAS                                             │
│  ┌───────────────────────────────────────────────────────────┐ │
│  │ 🔴 3 documentos vencidos                                   │ │
│  │ 🟡 5 documentos por vencer en 30 días                     │ │
│  │ 🔵 2 evaluaciones periódicas pendientes                   │ │
│  │ 🟠 1 contratista con incidente abierto                    │ │
│  └───────────────────────────────────────────────────────────┘ │
│                                                                 │
├─────────────────────────────────────────────────────────────────┤
│  📈 DISTRIBUCIÓN POR CLASIFICACIÓN                             │
│                                                                 │
│  A (Excelente) [████████████████████░░░░░░░░░░] 40% (10)       │
│  B (Bueno)     [█████████████████████████░░░░░] 50% (12)       │
│  C (Regular)   [████░░░░░░░░░░░░░░░░░░░░░░░░░░] 8%  (2)        │
│  D (Deficiente)[██░░░░░░░░░░░░░░░░░░░░░░░░░░░░] 2%  (1)        │
│                                                                 │
├─────────────────────────────────────────────────────────────────┤
│  📋 PRÓXIMAS EVALUACIONES                                       │
│  ┌─────────────────────────┬────────────┬─────────────────────┐│
│  │ Proveedor               │ Fecha      │ Tipo                ││
│  ├─────────────────────────┼────────────┼─────────────────────┤│
│  │ Ferretería El Tornillo  │ 15/02/2026 │ Periódica (Semest.) ││
│  │ Servicios Técnicos ABC  │ 20/02/2026 │ Reevaluación        ││
│  │ Transporte Seguro SAS   │ 28/02/2026 │ Periódica (Anual)   ││
│  └─────────────────────────┴────────────┴─────────────────────┘│
└─────────────────────────────────────────────────────────────────┘
```

---

## 10. INTEGRACIÓN CON MÓDULOS EXISTENTES

### 10.1 Módulo de Capacitaciones SST (1.2.1)
- Registrar capacitaciones SST impartidas a contratistas
- Verificar capacitación antes de ingreso a instalaciones
- Incluir contratistas en el cronograma de capacitación

### 10.2 Plan de Trabajo Anual (1.4)
- Actividades de evaluación de proveedores en el PTA
- Seguimiento a compromisos de mejora
- Verificación de documentos como actividad programada

### 10.3 Indicadores SST
- **Indicador de cobertura:** % proveedores evaluados / total proveedores
- **Indicador de cumplimiento:** % proveedores clasificación A y B
- **Indicador de documentación:** % documentos vigentes

### 10.4 Módulo de Documentación SST
- Generar Procedimiento de Selección y Evaluación de Proveedores con IA
- El documento consume los criterios y proveedores configurados
- Secciones: Objetivo, Alcance, Definiciones, Responsabilidades, Criterios, Procedimiento, Anexos

---

## 11. RUTAS PROPUESTAS

```php
// Gestión de proveedores (Cliente/Consultor)
$routes->group('proveedores', ['filter' => 'auth'], function($routes) {
    $routes->get('/', 'ProveedoresController::index');
    $routes->get('crear', 'ProveedoresController::crear');
    $routes->post('guardar', 'ProveedoresController::guardar');
    $routes->get('(:num)', 'ProveedoresController::ver/$1');
    $routes->get('(:num)/editar', 'ProveedoresController::editar/$1');
    $routes->post('(:num)/actualizar', 'ProveedoresController::actualizar/$1');
    $routes->get('(:num)/evaluar', 'ProveedoresController::evaluar/$1');
    $routes->post('(:num)/guardar-evaluacion', 'ProveedoresController::guardarEvaluacion/$1');
    $routes->get('(:num)/documentos', 'ProveedoresController::documentos/$1');
    $routes->get('(:num)/historial', 'ProveedoresController::historial/$1');
    $routes->get('(:num)/incidentes', 'ProveedoresController::incidentes/$1');
    $routes->post('(:num)/registrar-incidente', 'ProveedoresController::registrarIncidente/$1');

    // Configuración de criterios
    $routes->get('criterios', 'ProveedoresController::criterios');
    $routes->post('criterios/guardar', 'ProveedoresController::guardarCriterios');

    // Dashboard y reportes
    $routes->get('dashboard', 'ProveedoresController::dashboard');
    $routes->get('exportar', 'ProveedoresController::exportar');
    $routes->get('alertas', 'ProveedoresController::alertas');
});

// Portal del proveedor (Login separado)
$routes->group('portal-proveedor', function($routes) {
    $routes->get('login', 'PortalProveedorController::login');
    $routes->post('auth', 'PortalProveedorController::auth');
    $routes->get('dashboard', 'PortalProveedorController::dashboard');
    $routes->get('documentos', 'PortalProveedorController::documentos');
    $routes->post('subir-documento', 'PortalProveedorController::subirDocumento');
    $routes->get('evaluaciones', 'PortalProveedorController::evaluaciones');
    $routes->get('contratos', 'PortalProveedorController::contratos');
});

// API para AJAX
$routes->group('api/proveedores', ['filter' => 'auth'], function($routes) {
    $routes->get('buscar', 'ProveedoresController::buscarAjax');
    $routes->get('(:num)/resumen', 'ProveedoresController::resumenAjax/$1');
    $routes->post('(:num)/verificar-documento', 'ProveedoresController::verificarDocumentoAjax/$1');
});
```

---

## 12. CRONOGRAMA DE IMPLEMENTACIÓN SUGERIDO

### Fase 1: Base de Datos y Modelos (Semana 1)
- [ ] Crear tablas en BD
- [ ] Crear modelos CodeIgniter
- [ ] Crear seeders con datos de prueba
- [ ] Crear criterios base predeterminados

### Fase 2: CRUD Básico (Semana 2)
- [ ] ProveedoresController con CRUD
- [ ] Vistas de listado, crear, ver, editar
- [ ] Integración con sistema de rutas

### Fase 3: Sistema de Evaluación (Semana 3)
- [ ] Formulario de evaluación dinámico
- [ ] Cálculo automático de puntajes
- [ ] Clasificación automática
- [ ] Historial de evaluaciones

### Fase 4: Gestión de Documentos (Semana 4)
- [ ] Upload de documentos
- [ ] Sistema de vencimientos
- [ ] Alertas automáticas
- [ ] Verificación por cliente

### Fase 5: Portal del Proveedor (Semana 5)
- [ ] Login separado
- [ ] Dashboard del proveedor
- [ ] Subida de documentos
- [ ] Visualización de evaluaciones

### Fase 6: Dashboard y Reportes (Semana 6)
- [ ] Dashboard con indicadores
- [ ] Exportación a Excel
- [ ] Generación de certificados
- [ ] Integración con documentación SST

---

## 13. CONSIDERACIONES TÉCNICAS

### Seguridad
- Validar que el cliente solo vea sus propios proveedores
- Validar que el proveedor solo vea su propia información
- Sanitizar uploads de documentos
- Implementar rate limiting en uploads

### Performance
- Índices en campos de búsqueda frecuente (nit, estado, clasificacion)
- Paginación en listados
- Cache de criterios de evaluación

### UX
- Wizard paso a paso para registro de proveedores
- Formulario de evaluación con guardado automático
- Notificaciones toast para acciones
- Confirmación antes de cambios de estado

---

## 14. ANEXOS

### A. Documentos SST Requeridos por Tipo

| Documento | Proveedor | Contratista |
|-----------|:---------:|:-----------:|
| Afiliación ARL | ✅ | ✅ |
| Afiliación EPS | ✅ | ✅ |
| Afiliación Pensión | ✅ | ✅ |
| Certificados médicos | Opcional | ✅ |
| Matriz de peligros | Opcional | ✅ |
| Plan de trabajo SST | Opcional | ✅ |
| Política SST | Opcional | ✅ |
| Licencia SST | Si aplica | Si aplica |

### B. Frecuencia de Evaluación Sugerida

| Tipo de Proveedor | Categoría | Frecuencia |
|-------------------|-----------|------------|
| Contratista crítico | Crítico | Trimestral |
| Contratista permanente | Importante | Semestral |
| Proveedor recurrente | Normal | Anual |
| Proveedor ocasional | Normal | Por contrato |

---

*Documento generado como guía de implementación. Ajustar según necesidades específicas del proyecto.*
