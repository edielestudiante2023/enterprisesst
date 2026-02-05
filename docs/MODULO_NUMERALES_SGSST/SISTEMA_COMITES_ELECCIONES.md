# Sistema de Conformación de Comités SST

## Fecha: 2026-02-03
## Versión: 1.0

---

## Resumen Ejecutivo

Sistema integral para la conformación de comités de Seguridad y Salud en el Trabajo:
- **COPASST** (Comité Paritario de Seguridad y Salud en el Trabajo)
- **COCOLAB** (Comité de Convivencia Laboral)
- **Brigada de Emergencias** (integral)
- **Vigía SST** (empresas de 1-9 trabajadores)

El sistema gestiona todo el ciclo de vida: inscripción de candidatos, votación electrónica, escrutinio automático, generación de documentos y firmas electrónicas.

---

## Marco Normativo

### COPASST
| Normativa | Descripción |
|-----------|-------------|
| Resolución 2013/1986 | Conformación de comités paritarios |
| Decreto 1072/2015 | Decreto Único Reglamentario del Sector Trabajo |
| Resolución 0312/2019 | Estándares Mínimos del SG-SST |

### COCOLAB
| Normativa | Descripción |
|-----------|-------------|
| Resolución 652/2012 | Conformación del Comité de Convivencia |
| Resolución 1356/2012 | Modificaciones a la 652 |
| Resolución 3461/2025 | Actualización de conformación por tamaño |

### Brigadas
| Normativa | Descripción |
|-----------|-------------|
| Decreto 1072/2015 | Plan de emergencias y brigadas |
| Resolución 0312/2019 | Estándar de conformación de brigadas |

---

## Conformación por Tamaño de Empresa

### COPASST

| # Trabajadores | Empleador | Trabajadores | Total Principales | Observación |
|----------------|-----------|--------------|-------------------|-------------|
| 1 - 9 | - | - | 1 (Vigía) | No hay comité, se designa Vigía |
| 10 - 49 | 1 | 1 | 2 | + suplentes |
| 50 - 499 | 2 | 2 | 4 | + suplentes |
| 500 - 999 | 3 o 4 | 3 o 4 | 6 u 8 | Recomendado 4+4 |
| 1000+ | 4 | 4 | 8 | + suplentes |

> **Nota:** Siempre paridad (igual número empleador/trabajador). Suplentes en igual cantidad que principales.

### COCOLAB (Resolución 3461/2025)

| # Trabajadores | Empleador | Trabajadores | Total Principales |
|----------------|-----------|--------------|-------------------|
| < 5 | 1 | 1 | 2 |
| 5 a < 20 | 1 | 1 | 2 |
| >= 20 | 2 | 2 | 4 |

### Brigada de Emergencias

No tiene conformación paritaria. Se conforma por:
- Voluntariado de trabajadores
- Designación del empleador
- Una sola brigada integral (primeros auxilios, evacuación, incendios)

---

## Flujos de Conformación

### Flujo COPASST / COCOLAB (con votación)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                        PROCESO DE CONFORMACIÓN                               │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│  ┌──────────────┐    ┌──────────────┐    ┌──────────────┐    ┌────────────┐ │
│  │ INSCRIPCIÓN  │───>│  VOTACIÓN    │───>│  ESCRUTINIO  │───>│   FIRMAS   │ │
│  │              │    │              │    │              │    │            │ │
│  │ - Candidatos │    │ - Enlace 24h │    │ - Conteo     │    │ - Miembros │ │
│  │ - Jurados    │    │ - Cédula     │    │ - Resultados │    │ - Delegado │ │
│  │              │    │ - 1 voto     │    │ - Ganadores  │    │ - Rep Legal│ │
│  └──────────────┘    └──────────────┘    └──────────────┘    └────────────┘ │
│         │                   │                   │                   │       │
│         ▼                   ▼                   ▼                   ▼       │
│    Doc: Acta de       Doc: Registro       Doc: Acta de        Doc: Acta de │
│    Apertura           de Votantes         Cierre/Resultados   Constitución │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Flujo Brigada / Vigía (sin votación)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                     PROCESO DE DESIGNACIÓN                                   │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│  ┌──────────────────┐    ┌──────────────────┐    ┌──────────────────┐       │
│  │  VOLUNTARIADO    │───>│   DESIGNACIÓN    │───>│     FIRMAS       │       │
│  │                  │    │                  │    │                  │       │
│  │ - Registro       │    │ - Rep Legal      │    │ - Designados     │       │
│  │ - Voluntarios    │    │   selecciona     │    │ - Rep Legal      │       │
│  │                  │    │ - Asigna roles   │    │ - Delegado SST   │       │
│  └──────────────────┘    └──────────────────┘    └──────────────────┘       │
│          │                       │                       │                  │
│          ▼                       ▼                       ▼                  │
│     Listado de             Doc: Acta de            Documento con            │
│     Voluntarios            Designación             firmas completas         │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## Estados del Proceso Electoral

| Estado | Descripción | Acciones Disponibles |
|--------|-------------|---------------------|
| `configuracion` | Consultor configura parámetros del proceso | Definir fechas, número de plazas |
| `inscripcion` | Período de inscripción de candidatos y jurados | Registrar candidatos, registrar jurados |
| `votacion` | Votación abierta (enlace activo 24h) | Votar (trabajadores) |
| `escrutinio` | Conteo y publicación de resultados | Ver resultados, resolver empates |
| `designacion_empleador` | Rep Legal designa sus representantes | Registrar rep. empleador |
| `firmas` | Proceso de firmas electrónicas | Firmar documentos |
| `completado` | Comité conformado y documentos firmados | Descargar documentos |

---

## Documentos Generados

### COPASST / COCOLAB

| # | Documento | Momento | Firmantes |
|---|-----------|---------|-----------|
| 1 | Acta de Apertura de Elecciones | Al abrir inscripción | Jurados |
| 2 | Registro de Votantes | Durante votación | Auto-generado |
| 3 | Acta de Cierre de Votaciones | Al cerrar votación | Jurados |
| 4 | Resultados de Votación | Post-escrutinio | Jurados |
| 5 | Acta de Constitución del Comité | Final | Todos los miembros + Delegado SST + Rep Legal |

### Brigada de Emergencias

| # | Documento | Momento | Firmantes |
|---|-----------|---------|-----------|
| 1 | Acta de Designación de Brigada | Al designar | Brigadistas + Rep Legal + Delegado SST |

### Vigía SST

| # | Documento | Momento | Firmantes |
|---|-----------|---------|-----------|
| 1 | Acta de Designación de Vigía | Al designar | Vigía + Rep Legal + Delegado SST |

---

## Arquitectura de Base de Datos

### Tablas Existentes (reutilizar)

```
tbl_comites              → Almacena los comités con período de vigencia
tbl_comite_miembros      → Miembros del comité (principales/suplentes)
tbl_tipos_comite         → Tipos: COPASST, COCOLAB, VIGIA, BRIGADA
tbl_doc_firma_solicitudes → Sistema de firmas electrónicas
tbl_doc_firma_evidencias  → Evidencias de firmas
```

### Nuevas Tablas (YA CREADAS EN LOCAL Y PRODUCCIÓN)

> **Nota:** Estas tablas fueron creadas el 2026-02-03 tanto en la base de datos local como en producción (DigitalOcean).

#### `tbl_procesos_electorales`

Almacena cada proceso de conformación de comité.

```sql
CREATE TABLE tbl_procesos_electorales (
    id_proceso INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_comite INT NULL,
    tipo_comite ENUM('COPASST', 'COCOLAB', 'BRIGADA', 'VIGIA') NOT NULL,
    anio INT NOT NULL,
    estado ENUM('configuracion', 'inscripcion', 'votacion', 'escrutinio',
                'designacion_empleador', 'firmas', 'completado', 'cancelado')
                DEFAULT 'configuracion',
    plazas_principales INT NOT NULL DEFAULT 2,
    plazas_suplentes INT NOT NULL DEFAULT 2,
    fecha_inicio_inscripcion DATETIME NULL,
    fecha_fin_inscripcion DATETIME NULL,
    fecha_inicio_votacion DATETIME NULL,
    fecha_fin_votacion DATETIME NULL,
    fecha_escrutinio DATETIME NULL,
    fecha_completado DATETIME NULL,
    token_votacion VARCHAR(64) UNIQUE NULL,
    fecha_inicio_periodo DATE NULL,
    fecha_fin_periodo DATE NULL,
    id_consultor INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### `tbl_participantes_comite` (TABLA UNIFICADA)

Almacena tanto candidatos de trabajadores (votación) como representantes del empleador (designación) en una sola tabla.

```sql
CREATE TABLE tbl_participantes_comite (
    id_participante INT AUTO_INCREMENT PRIMARY KEY,
    id_proceso INT NOT NULL,

    -- Datos personales (comunes a todos)
    nombre_completo VARCHAR(200) NOT NULL,
    tipo_documento VARCHAR(10) DEFAULT 'CC',
    numero_documento VARCHAR(20) NOT NULL,
    cargo VARCHAR(100) NOT NULL,
    area_dependencia VARCHAR(100) NULL,
    email VARCHAR(150) NOT NULL,
    telefono VARCHAR(20) NULL,
    foto_url VARCHAR(500) NOT NULL,              -- Foto obligatoria

    -- Certificación 50 horas (obligatorio por ley para COPASST)
    certificado_50_horas_url VARCHAR(500) NULL,
    certificado_50_horas_fecha DATE NULL,
    certificado_50_horas_institucion VARCHAR(200) NULL,

    -- Tipo de participación
    representacion ENUM('empleador', 'trabajador') NOT NULL,
    origen ENUM('votacion', 'designacion') NOT NULL,

    -- Campos para votación (NULL si es designación)
    votos_obtenidos INT DEFAULT 0,
    es_ganador TINYINT(1) DEFAULT 0,
    orden_resultado INT NULL,

    -- Asignación final
    tipo_miembro ENUM('principal', 'suplente', 'reserva', 'no_electo', 'pendiente') DEFAULT 'pendiente',
    rol_comite ENUM('presidente', 'secretario', 'miembro') DEFAULT 'miembro',

    -- Transferencia a comité
    transferido_a_comite TINYINT(1) DEFAULT 0,
    id_miembro_comite INT NULL,
    fecha_transferencia DATETIME NULL,

    -- Estado
    estado ENUM('inscrito', 'activo', 'retirado') DEFAULT 'inscrito',
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY unique_participante_proceso (id_proceso, numero_documento)
);
```

**Ventajas de la tabla unificada:**
- Consulta simple para todos los participantes de un proceso
- Campo `certificado_50_horas_url` disponible para todos (empleador y trabajadores)
- Verificación de paridad con un solo `GROUP BY representacion`
- Evita duplicación de lógica de negocio

#### `tbl_jurados_eleccion`

Jurados de votación (mínimo 2, sin máximo).

```sql
CREATE TABLE tbl_jurados_eleccion (
    id_jurado INT AUTO_INCREMENT PRIMARY KEY,
    id_proceso INT NOT NULL,

    -- Datos del jurado
    nombre_completo VARCHAR(200) NOT NULL,
    tipo_documento VARCHAR(10) DEFAULT 'CC',
    numero_documento VARCHAR(20) NOT NULL,
    cargo VARCHAR(100) NOT NULL,
    area_dependencia VARCHAR(100) NULL,
    email VARCHAR(150) NOT NULL,

    -- Rol en el proceso
    rol ENUM('presidente_mesa', 'secretario_mesa', 'testigo') DEFAULT 'testigo',

    -- Firma
    ha_firmado TINYINT(1) DEFAULT 0,
    fecha_firma DATETIME NULL,

    -- Estado
    estado ENUM('activo', 'retirado') DEFAULT 'activo',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_proceso) REFERENCES tbl_procesos_electorales(id_proceso),
    UNIQUE KEY unique_jurado_proceso (id_proceso, numero_documento)
);
```

#### `tbl_votos_eleccion`

Registro de votos (anónimo pero validable).

```sql
CREATE TABLE tbl_votos_eleccion (
    id_voto INT AUTO_INCREMENT PRIMARY KEY,
    id_proceso INT NOT NULL,
    id_candidato INT NOT NULL,

    -- Validación del votante (hash para evitar duplicados sin revelar identidad)
    hash_votante VARCHAR(64) NOT NULL,           -- SHA256(cedula + id_proceso + salt)

    -- Auditoría
    ip_votante VARCHAR(45) NULL,
    user_agent TEXT NULL,
    fecha_voto DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_proceso) REFERENCES tbl_procesos_electorales(id_proceso),
    FOREIGN KEY (id_candidato) REFERENCES tbl_candidatos_eleccion(id_candidato),
    UNIQUE KEY unique_votante_proceso (id_proceso, hash_votante)
);
```

#### `tbl_representantes_empleador`

Representantes designados por el empleador (no votan).

```sql
CREATE TABLE tbl_representantes_empleador (
    id_representante INT AUTO_INCREMENT PRIMARY KEY,
    id_proceso INT NOT NULL,

    -- Datos
    nombre_completo VARCHAR(200) NOT NULL,
    tipo_documento VARCHAR(10) DEFAULT 'CC',
    numero_documento VARCHAR(20) NOT NULL,
    cargo VARCHAR(100) NOT NULL,
    area_dependencia VARCHAR(100) NULL,
    email VARCHAR(150) NOT NULL,

    -- Tipo
    tipo_miembro ENUM('principal', 'suplente') NOT NULL,

    -- Estado
    estado ENUM('activo', 'retirado') DEFAULT 'activo',
    fecha_designacion DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_proceso) REFERENCES tbl_procesos_electorales(id_proceso),
    UNIQUE KEY unique_rep_proceso (id_proceso, numero_documento)
);
```

#### `tbl_voluntarios_brigada`

Voluntarios para brigada de emergencias.

```sql
CREATE TABLE tbl_voluntarios_brigada (
    id_voluntario INT AUTO_INCREMENT PRIMARY KEY,
    id_proceso INT NOT NULL,

    -- Datos
    nombre_completo VARCHAR(200) NOT NULL,
    tipo_documento VARCHAR(10) DEFAULT 'CC',
    numero_documento VARCHAR(20) NOT NULL,
    cargo VARCHAR(100) NOT NULL,
    area_dependencia VARCHAR(100) NULL,
    email VARCHAR(150) NOT NULL,
    foto_url VARCHAR(500) NULL,

    -- Designación
    fue_designado TINYINT(1) DEFAULT 0,
    rol_brigada VARCHAR(100) NULL,               -- Ej: "Coordinador", "Primeros auxilios"
    fecha_designacion DATETIME NULL,

    -- Estado
    estado ENUM('voluntario', 'designado', 'retirado') DEFAULT 'voluntario',
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_proceso) REFERENCES tbl_procesos_electorales(id_proceso)
);
```

#### `tbl_documentos_proceso_electoral`

Documentos generados en cada proceso.

```sql
CREATE TABLE tbl_documentos_proceso_electoral (
    id_documento_proceso INT AUTO_INCREMENT PRIMARY KEY,
    id_proceso INT NOT NULL,

    -- Tipo de documento
    tipo_documento ENUM(
        'acta_apertura',
        'registro_votantes',
        'acta_cierre',
        'resultados_votacion',
        'acta_constitucion',
        'acta_designacion_brigada',
        'acta_designacion_vigia'
    ) NOT NULL,

    -- Archivo
    archivo_pdf VARCHAR(500) NULL,
    archivo_word VARCHAR(500) NULL,

    -- Estado de firmas
    estado_firmas ENUM('pendiente', 'en_proceso', 'completado') DEFAULT 'pendiente',
    id_solicitud_firma INT NULL,                 -- FK a tbl_doc_firma_solicitudes

    -- Timestamps
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (id_proceso) REFERENCES tbl_procesos_electorales(id_proceso),
    FOREIGN KEY (id_solicitud_firma) REFERENCES tbl_doc_firma_solicitudes(id_solicitud)
);
```

### Diagrama de Relaciones

```
tbl_clientes (1)
       │
       ├────────────────────────────> (N) tbl_procesos_electorales
       │                                          │
       │                                          ├──> (N) tbl_candidatos_eleccion
       │                                          │           │
       │                                          │           └──> (N) tbl_votos_eleccion
       │                                          │
       │                                          ├──> (N) tbl_jurados_eleccion
       │                                          │
       │                                          ├──> (N) tbl_representantes_empleador
       │                                          │
       │                                          ├──> (N) tbl_voluntarios_brigada
       │                                          │
       │                                          ├──> (N) tbl_documentos_proceso_electoral
       │                                          │           │
       │                                          │           └──> tbl_doc_firma_solicitudes
       │                                          │
       │                                          └──> (1) tbl_comites
       │                                                      │
       └────────────────────────────────────────────> (N) tbl_comite_miembros
```

---

## Reglas de Negocio

### Votación

1. **Un voto por trabajador:** Validado por hash de cédula
2. **Un candidato por voto:** No se puede votar por múltiples
3. **Sin quórum mínimo:** No hay mínimo de participación requerido
4. **Enlace temporal:** 24 horas de vigencia, definido por consultor
5. **Acceso universal:** Cualquier dispositivo, cualquier ubicación

### Resultados

1. **Ganadores por votos:** Los N candidatos con más votos son principales
2. **Suplentes automáticos:** Los siguientes en votos son suplentes
3. **Desempate por concertación:** Los empatados deciden entre ellos (queda en acta)
4. **Lista de reserva:** Candidatos no electos quedan como elegibles para reemplazo

**Texto legal para acta de desempate:**
> "En caso de empate entre candidatos a representantes de los trabajadores que exceda el número de plazas disponibles, los candidatos empatados deliberarán y concertarán entre ellos quién ocupará la(s) plaza(s) correspondiente(s). Los demás candidatos empatados quedarán registrados como elegibles y podrán ocupar la vacante en caso de retiro, desvinculación laboral, incapacidad prolongada o cualquier situación que genere descomposición del comité durante el período."

### Firmas

**Orden de firma para Acta de Constitución:**
1. Miembros principales (en orden alfabético)
2. Miembros suplentes (en orden alfabético)
3. Delegado SST
4. Representante Legal

**Orden de firma para documentos de jurados:**
1. Jurados (en orden de rol: presidente, secretario, testigos)

---

## Interfaz de Usuario

### Dashboard del Proceso (Consultor)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ 🗳️ COPASST 2026-2028 - Empresa XYZ                                         │
│ Estado: INSCRIPCIÓN                                                ⚙️ Config │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐        │
│  │ 📝          │  │ 🗳️          │  │ 📊          │  │ ✍️          │        │
│  │ Inscripción │  │ Votación    │  │ Escrutinio  │  │ Firmas      │        │
│  │ ✅ Activo   │  │ ⏳ Pendiente │  │ ⏳ Pendiente │  │ ⏳ Pendiente │        │
│  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘        │
│                                                                              │
├─────────────────────────────────────────────────────────────────────────────┤
│ 📋 Candidatos Inscritos (5)                        [+ Nuevo Candidato]      │
├─────────────────────────────────────────────────────────────────────────────┤
│ │ Foto │ Nombre          │ Cédula      │ Cargo        │ Área      │ Estado │
│ ├──────┼─────────────────┼─────────────┼──────────────┼───────────┼────────┤
│ │ 👤   │ Juan Pérez      │ 1234567890  │ Operario     │ Producción│ ✅     │
│ │ 👤   │ María García    │ 0987654321  │ Asistente    │ RRHH      │ ✅     │
│ │ ...                                                                       │
├─────────────────────────────────────────────────────────────────────────────┤
│ 👥 Jurados (2)                                      [+ Nuevo Jurado]        │
├─────────────────────────────────────────────────────────────────────────────┤
│ │ Nombre          │ Cédula      │ Rol              │ Email                  │
│ ├─────────────────┼─────────────┼──────────────────┼────────────────────────│
│ │ Carlos López    │ 1122334455  │ Presidente Mesa  │ carlos@empresa.com     │
│ │ Ana Rodríguez   │ 5544332211  │ Secretario Mesa  │ ana@empresa.com        │
│                                                                              │
├─────────────────────────────────────────────────────────────────────────────┤
│                    [📤 Abrir Votación]  [❌ Cancelar Proceso]               │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Pantalla de Votación (Trabajador - Mobile First)

```
┌─────────────────────────────────┐
│     🗳️ VOTACIÓN COPASST 2026    │
│          Empresa XYZ            │
├─────────────────────────────────┤
│                                 │
│  Ingrese su cédula:             │
│  ┌─────────────────────────────┐│
│  │ 1234567890                  ││
│  └─────────────────────────────┘│
│                                 │
│  [      Continuar       ]       │
│                                 │
└─────────────────────────────────┘

          ↓ (tras validar)

┌─────────────────────────────────┐
│     🗳️ VOTACIÓN COPASST 2026    │
│     Seleccione UN candidato     │
├─────────────────────────────────┤
│                                 │
│  ┌─────────────────────────────┐│
│  │  👤 Juan Pérez              ││
│  │  Operario - Producción      ││
│  │  [    Votar por Juan   ]    ││
│  └─────────────────────────────┘│
│                                 │
│  ┌─────────────────────────────┐│
│  │  👤 María García            ││
│  │  Asistente - RRHH           ││
│  │  [   Votar por María   ]    ││
│  └─────────────────────────────┘│
│                                 │
│  ┌─────────────────────────────┐│
│  │  👤 Pedro Sánchez           ││
│  │  Técnico - Mantenimiento    ││
│  │  [   Votar por Pedro   ]    ││
│  └─────────────────────────────┘│
│                                 │
└─────────────────────────────────┘

          ↓ (tras votar)

┌─────────────────────────────────┐
│                                 │
│         ✅ ¡VOTO REGISTRADO!    │
│                                 │
│   Gracias por participar en    │
│   la elección del COPASST.     │
│                                 │
│   Su voto ha sido registrado   │
│   de forma anónima y segura.   │
│                                 │
└─────────────────────────────────┘
```

### Pantalla de Resultados

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ 📊 RESULTADOS VOTACIÓN COPASST 2026                                         │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│  Total votantes: 45 de 120 trabajadores (37.5%)                             │
│                                                                              │
│  ┌─────────────────────────────────────────────────────────────────────────┐│
│  │ # │ Candidato     │ Votos │ %     │ Gráfica                │ Resultado ││
│  ├───┼───────────────┼───────┼───────┼────────────────────────┼───────────┤│
│  │ 1 │ Juan Pérez    │  15   │ 33.3% │ ██████████████         │ PRINCIPAL ││
│  │ 2 │ María García  │  12   │ 26.7% │ ███████████            │ PRINCIPAL ││
│  │ 3 │ Pedro Sánchez │   9   │ 20.0% │ ████████               │ SUPLENTE  ││
│  │ 4 │ Ana López     │   5   │ 11.1% │ ████                   │ SUPLENTE  ││
│  │ 5 │ Luis Gómez    │   4   │  8.9% │ ███                    │ RESERVA   ││
│  └─────────────────────────────────────────────────────────────────────────┘│
│                                                                              │
│  ☑️ Juan Pérez      ☑️ María García      [Transferir a Comité]              │
│  ☑️ Pedro Sánchez   ☑️ Ana López                                            │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## Integración con Sistema de Firmas

### Tipo de Firma para Acta de Constitución

El Acta de Constitución requiere firma tipo **F** o **G** (múltiples firmantes con Delegado SST).

Según `SISTEMA_FIRMAS_DOCUMENTOS.md`, el flujo es:

```php
// Crear solicitud de firma para Acta de Constitución
$solicitudFirma = [
    'id_documento' => $idDocumento,
    'tipo_documento' => 'acta_constitucion_copasst',
    'tipo_firma' => 'F',  // Múltiples firmantes
    'requiere_delegado_sst' => 1,
    'estado' => 'pendiente'
];
```

**Orden de firma (secuencial):**
1. Miembros del comité (principales y suplentes) - `orden_firma: 1, 2, 3...`
2. Delegado SST - `orden_firma: N+1, estado: 'esperando'`
3. Representante Legal - `orden_firma: N+2, estado: 'esperando'`

### Integración con `tbl_doc_firma_solicitudes`

```sql
-- Al crear solicitud de firma para Acta de Constitución
INSERT INTO tbl_doc_firma_solicitudes (
    id_documento,
    tipo_documento,
    tipo_firma,
    requiere_delegado_sst,
    estado
) VALUES (
    :id_documento,
    'acta_constitucion_copasst',
    'F',
    1,
    'pendiente'
);

-- Agregar firmantes
INSERT INTO tbl_doc_firma_evidencias (
    id_solicitud,
    nombre_firmante,
    email_firmante,
    cedula_firmante,
    cargo_firmante,
    orden_firma,
    estado
) VALUES
    (:id_solicitud, 'Juan Pérez', 'juan@...', '123...', 'Miembro Principal', 1, 'pendiente'),
    (:id_solicitud, 'María García', 'maria@...', '098...', 'Miembro Principal', 2, 'pendiente'),
    -- ... más miembros
    (:id_solicitud, 'Delegado SST', 'delegado@...', '...', 'Delegado SST', 9, 'esperando'),
    (:id_solicitud, 'Rep Legal', 'rep@...', '...', 'Representante Legal', 10, 'esperando');
```

---

## Controladores y Rutas

### Nuevo Controlador: `ComitesEleccionesController.php`

```php
class ComitesEleccionesController extends BaseController
{
    // Dashboard del proceso
    public function dashboard($idCliente, $idProceso = null);

    // Gestión de procesos
    public function nuevoProceso($idCliente);
    public function guardarProceso();
    public function configurarProceso($idProceso);
    public function cancelarProceso($idProceso);

    // Candidatos
    public function nuevoCandidato($idProceso);
    public function guardarCandidato();
    public function retirarCandidato($idCandidato);

    // Jurados
    public function nuevoJurado($idProceso);
    public function guardarJurado();

    // Votación
    public function abrirVotacion($idProceso);
    public function cerrarVotacion($idProceso);
    public function pantallaVotacion($token);           // Pública
    public function validarVotante($token);             // AJAX
    public function registrarVoto($token);              // AJAX

    // Escrutinio
    public function verResultados($idProceso);
    public function resolverEmpate($idProceso);
    public function transferirAComite($idProceso);      // Mover ganadores a tbl_comite_miembros

    // Representantes empleador
    public function designarRepresentanteEmpleador($idProceso);
    public function guardarRepresentanteEmpleador();

    // Documentos
    public function generarDocumento($idProceso, $tipo);
    public function descargarDocumento($idDocumentoProceso);

    // Firmas
    public function iniciarProcesoFirmas($idDocumentoProceso);
    public function estadoFirmas($idProceso);
}
```

### Nuevo Controlador: `BrigadasController.php`

```php
class BrigadasController extends BaseController
{
    // Dashboard
    public function dashboard($idCliente, $idProceso = null);

    // Voluntarios
    public function registrarVoluntario($idProceso);
    public function guardarVoluntario();

    // Designación
    public function designarBrigadistas($idProceso);
    public function guardarDesignacion();

    // Documento
    public function generarActaDesignacion($idProceso);
}
```

### Rutas Propuestas

```php
// Comités con elección (COPASST, COCOLAB)
$routes->group('comites-elecciones', ['filter' => 'auth'], function($routes) {
    $routes->get('(:num)', 'ComitesEleccionesController::dashboard/$1');
    $routes->get('(:num)/proceso/(:num)', 'ComitesEleccionesController::dashboard/$1/$2');
    $routes->get('(:num)/nuevo', 'ComitesEleccionesController::nuevoProceso/$1');
    $routes->post('proceso/guardar', 'ComitesEleccionesController::guardarProceso');

    // Candidatos
    $routes->get('proceso/(:num)/candidato/nuevo', 'ComitesEleccionesController::nuevoCandidato/$1');
    $routes->post('candidato/guardar', 'ComitesEleccionesController::guardarCandidato');

    // Jurados
    $routes->get('proceso/(:num)/jurado/nuevo', 'ComitesEleccionesController::nuevoJurado/$1');
    $routes->post('jurado/guardar', 'ComitesEleccionesController::guardarJurado');

    // Control de votación
    $routes->post('proceso/(:num)/abrir-votacion', 'ComitesEleccionesController::abrirVotacion/$1');
    $routes->post('proceso/(:num)/cerrar-votacion', 'ComitesEleccionesController::cerrarVotacion/$1');

    // Resultados y transferencia
    $routes->get('proceso/(:num)/resultados', 'ComitesEleccionesController::verResultados/$1');
    $routes->post('proceso/(:num)/transferir-comite', 'ComitesEleccionesController::transferirAComite/$1');

    // Representantes empleador
    $routes->get('proceso/(:num)/rep-empleador/nuevo', 'ComitesEleccionesController::designarRepresentanteEmpleador/$1');
    $routes->post('rep-empleador/guardar', 'ComitesEleccionesController::guardarRepresentanteEmpleador');

    // Documentos
    $routes->get('proceso/(:num)/documento/(:segment)', 'ComitesEleccionesController::generarDocumento/$1/$2');
});

// Votación pública (sin auth, con token)
$routes->get('votar/(:alphanum)', 'ComitesEleccionesController::pantallaVotacion/$1');
$routes->post('votar/(:alphanum)/validar', 'ComitesEleccionesController::validarVotante/$1');
$routes->post('votar/(:alphanum)/registrar', 'ComitesEleccionesController::registrarVoto/$1');

// Brigadas
$routes->group('brigadas', ['filter' => 'auth'], function($routes) {
    $routes->get('(:num)', 'BrigadasController::dashboard/$1');
    $routes->get('(:num)/proceso/(:num)', 'BrigadasController::dashboard/$1/$2');
    $routes->post('voluntario/registrar', 'BrigadasController::guardarVoluntario');
    $routes->post('proceso/(:num)/designar', 'BrigadasController::guardarDesignacion');
    $routes->get('proceso/(:num)/acta', 'BrigadasController::generarActaDesignacion/$1');
});

// Vigía SST (similar a brigadas)
$routes->group('vigia-sst', ['filter' => 'auth'], function($routes) {
    $routes->get('(:num)', 'VigiaSSTController::dashboard/$1');
    $routes->post('designar', 'VigiaSSTController::guardarDesignacion');
    $routes->get('proceso/(:num)/acta', 'VigiaSSTController::generarActaDesignacion/$1');
});
```

---

## Seguridad de Votación

### Validación de Votante

```php
public function validarVotante($token)
{
    $cedula = $this->request->getPost('cedula');
    $proceso = $this->getProcesoByToken($token);

    // Verificar que el proceso esté en estado 'votacion'
    if ($proceso['estado'] !== 'votacion') {
        return $this->response->setJSON(['error' => 'Votación no disponible']);
    }

    // Verificar que no haya votado antes
    $hashVotante = hash('sha256', $cedula . $proceso['id_proceso'] . VOTE_SALT);
    $yaVoto = $this->db->table('tbl_votos_eleccion')
        ->where('id_proceso', $proceso['id_proceso'])
        ->where('hash_votante', $hashVotante)
        ->countAllResults();

    if ($yaVoto > 0) {
        return $this->response->setJSON(['error' => 'Ya registró su voto']);
    }

    // Retornar candidatos
    $candidatos = $this->getCandidatosActivos($proceso['id_proceso']);
    return $this->response->setJSON(['success' => true, 'candidatos' => $candidatos]);
}
```

### Registro de Voto (Anónimo)

```php
public function registrarVoto($token)
{
    $cedula = $this->request->getPost('cedula');
    $idCandidato = $this->request->getPost('id_candidato');
    $proceso = $this->getProcesoByToken($token);

    // Crear hash anónimo
    $hashVotante = hash('sha256', $cedula . $proceso['id_proceso'] . VOTE_SALT);

    // Registrar voto
    $this->db->table('tbl_votos_eleccion')->insert([
        'id_proceso' => $proceso['id_proceso'],
        'id_candidato' => $idCandidato,
        'hash_votante' => $hashVotante,
        'ip_votante' => $this->request->getIPAddress(),
        'user_agent' => $this->request->getUserAgent()->getAgentString()
    ]);

    // Incrementar contador del candidato
    $this->db->table('tbl_candidatos_eleccion')
        ->where('id_candidato', $idCandidato)
        ->set('votos_obtenidos', 'votos_obtenidos + 1', false)
        ->update();

    return $this->response->setJSON(['success' => true]);
}
```

---

## Vistas Principales

```
app/Views/comites_elecciones/
├── dashboard.php              // Panel principal del proceso
├── nuevo_proceso.php          // Crear nuevo proceso electoral
├── configurar_proceso.php     // Configurar fechas y plazas
├── candidatos/
│   ├── lista.php              // Tabla de candidatos
│   ├── nuevo.php              // Formulario nuevo candidato
│   └── tarjeta.php            // Card de candidato (componente)
├── jurados/
│   ├── lista.php              // Tabla de jurados
│   └── nuevo.php              // Formulario nuevo jurado
├── votacion/
│   ├── publica.php            // Pantalla de votación (pública)
│   ├── validar_cedula.php     // Modal/pantalla validación
│   └── confirmacion.php       // Voto registrado
├── resultados/
│   ├── ver.php                // Tabla de resultados con gráficas
│   ├── empate.php             // Resolver empates
│   └── transferir.php         // Confirmar transferencia a comité
├── empleador/
│   ├── designar.php           // Formulario rep. empleador
│   └── lista.php              // Lista rep. designados
├── documentos/
│   ├── acta_apertura.php      // Preview acta apertura
│   ├── acta_cierre.php        // Preview acta cierre
│   ├── resultados_pdf.php     // Resultados con gráficas
│   └── acta_constitucion.php  // Acta final
└── firmas/
    └── estado.php             // Estado de firmas del proceso

app/Views/brigadas/
├── dashboard.php              // Panel de brigadas
├── voluntarios/
│   ├── lista.php              // Lista de voluntarios
│   └── registrar.php          // Formulario registro
├── designacion.php            // Designar brigadistas
└── acta_designacion.php       // Preview acta

app/Views/vigia_sst/
├── dashboard.php              // Panel vigía
├── designar.php               // Designar vigía
└── acta_designacion.php       // Preview acta
```

---

## Acceso desde Carpeta 1.1.6

La carpeta 1.1.6 en el módulo de documentación mostrará:

1. **Enlace al Sistema de Comités** (nuevo módulo)
2. **Documentos subidos** (el consultor descarga PDFs firmados y los sube aquí)

```php
// Vista _tipos/conformacion_copasst.php
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h4 class="mb-1">
            <i class="bi bi-folder-fill text-warning me-2"></i>
            1.1.6 Conformación COPASST/Vigía
        </h4>
    </div>
</div>

<!-- Acceso al módulo de elecciones -->
<div class="alert alert-primary">
    <i class="bi bi-box-arrow-up-right me-2"></i>
    <strong>Sistema de Comités:</strong>
    <a href="<?= base_url('comites-elecciones/' . $cliente['id_cliente']) ?>"
       class="alert-link" target="_blank">
        Abrir módulo de conformación de comités
    </a>
</div>

<!-- Documentos subidos manualmente -->
<?= view('documentacion/_components/tabla_documentos', [
    'documentos' => $documentosSSTAprobados ?? []
]) ?>
```

---

## Checklist de Implementación

### Fase 1: Base de Datos
- [ ] Crear tabla `tbl_procesos_electorales`
- [ ] Crear tabla `tbl_candidatos_eleccion`
- [ ] Crear tabla `tbl_jurados_eleccion`
- [ ] Crear tabla `tbl_votos_eleccion`
- [ ] Crear tabla `tbl_representantes_empleador`
- [ ] Crear tabla `tbl_voluntarios_brigada`
- [ ] Crear tabla `tbl_documentos_proceso_electoral`

### Fase 2: Controladores
- [ ] Crear `ComitesEleccionesController`
- [ ] Crear `BrigadasController`
- [ ] Crear `VigiaSSTController`

### Fase 3: Vistas - COPASST/COCOLAB
- [ ] Dashboard del proceso
- [ ] CRUD Candidatos
- [ ] CRUD Jurados
- [ ] Pantalla de votación pública (mobile first)
- [ ] Pantalla de resultados
- [ ] Designación rep. empleador
- [ ] Transferencia a comité

### Fase 4: Vistas - Brigadas/Vigía
- [ ] Dashboard brigadas
- [ ] Registro voluntarios
- [ ] Designación brigadistas
- [ ] Dashboard vigía
- [ ] Designación vigía

### Fase 5: Documentos PDF
- [ ] Template Acta de Apertura
- [ ] Template Registro de Votantes
- [ ] Template Acta de Cierre
- [ ] Template Resultados (con gráficas)
- [ ] Template Acta de Constitución
- [ ] Template Acta Designación Brigada
- [ ] Template Acta Designación Vigía

### Fase 6: Integración
- [ ] Integrar con sistema de firmas existente
- [ ] Integrar transferencia a `tbl_comite_miembros`
- [ ] Crear vista en carpeta 1.1.6
- [ ] Pruebas de flujo completo

---

## Notas Finales

1. **Período bienal:** El sistema debe alertar cuando un comité está próximo a vencer (2 años)
2. **Historial:** Se mantiene historial de todos los procesos electorales
3. **Auditoría:** Todos los votos son anónimos pero auditables (hash + IP + timestamp)
4. **Mobile first:** La pantalla de votación debe funcionar perfectamente en celular
5. **Sin quórum:** No hay mínimo de participación, la elección es válida con cualquier número de votantes

---

Fecha de creación: 2026-02-03
Autor: Claude Code
Revisión: v1.0
