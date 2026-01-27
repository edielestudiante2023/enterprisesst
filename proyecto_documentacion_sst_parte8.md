# Proyecto de Documentacion SST - Parte 8

## Sistema de Generacion de Documentos con IA - Implementacion Real

---

## CAMBIOS RESPECTO A DOCUMENTACION ANTERIOR

Esta parte documenta el estado **REAL** de la implementacion del generador de documentos SST con IA, corrigiendo y actualizando la informacion de las partes anteriores.

### Cambios Clave

| Aspecto | Documentacion Anterior | Implementacion Real |
|---------|------------------------|---------------------|
| Estructura documentos | Librerias PHP en `app/Libraries/` | Constante en `DocumentosSSTController.php` |
| Stored Procedures | 7 SPs documentados | NO se usan actualmente |
| Flujo de generacion | Independiente | Phase-Locking (Cronograma -> PTA -> Indicadores -> Documento) |
| Secciones por documento | 13 predefinidas | 13 con nombres/orden diferentes |
| Contexto IA | Sidebar con links | Input por seccion |
| Almacenamiento | `tbl_doc_documentos` + `tbl_doc_secciones` | `tbl_documentos_sst` (JSON) |

---

## 1. FLUJO COMPLETO DEL SISTEMA (Phase-Locking)

### 1.1 Diagrama del Flujo

```
+-------------------------------------------------------------------+
|                    MODULO GENERADOR IA                             |
|                  /generador-ia/{id_cliente}                        |
+-------------------------------------------------------------------+
         |
         | Usuario hace clic en "Generar Documentacion"
         v
+-------------------------------------------------------------------+
|  FASE 1: CRONOGRAMA DE CAPACITACIONES                              |
|  Requisitos: Contexto SST del cliente configurado                  |
|  Genera: Capacitaciones mensuales segun peligros y estandares      |
|  Almacena: tbl_cronogcapacitacion                                  |
|  Estado: DEBE completarse antes de continuar                       |
+-------------------------------------------------------------------+
         |
         | Al generar cronograma, se habilita siguiente fase
         v
+-------------------------------------------------------------------+
|  FASE 2: PLAN DE TRABAJO ANUAL (PTA)                               |
|  Requisitos: Cronograma generado                                   |
|  Genera: Actividades PHVA (Planear-Hacer-Verificar-Actuar)         |
|  Almacena: tbl_pta_actividades                                     |
|  Estado: DEBE completarse antes de continuar                       |
+-------------------------------------------------------------------+
         |
         | Al generar PTA, se habilita siguiente fase
         v
+-------------------------------------------------------------------+
|  FASE 3: INDICADORES DE GESTION SST                                |
|  Requisitos: PTA generado                                          |
|  Genera: Indicadores de Estructura, Proceso, Resultado             |
|  Almacena: tbl_indicadores_sst                                     |
|  Estado: DEBE completarse antes de continuar                       |
+-------------------------------------------------------------------+
         |
         | Al generar indicadores, se habilita documento
         v
+-------------------------------------------------------------------+
|  FASE 4: PROGRAMA DE CAPACITACION                                  |
|  Requisitos: Cronograma + PTA + Indicadores                        |
|  Genera: Documento completo por 13 secciones                       |
|  Almacena: tbl_documentos_sst (JSON)                               |
|  Usa: OpenAI GPT-4o-mini cuando hay contexto adicional             |
+-------------------------------------------------------------------+
         |
         v
+-------------------------------------------------------------------+
|  VISTA PREVIA Y EXPORTACION                                        |
|  /documentos-sst/{id_cliente}/programa-capacitacion/{anio}         |
|  Opciones: Imprimir, Descargar PDF                                 |
+-------------------------------------------------------------------+
```

### 1.2 Justificacion del Phase-Locking

El sistema **requiere** completar cada fase antes de la siguiente porque:

1. **El Cronograma** alimenta la seccion "9. Cronograma de Capacitaciones" del documento
2. **El PTA** alimenta la seccion "10. Plan de Trabajo Anual" del documento
3. **Los Indicadores** alimentan la seccion "11. Indicadores" del documento

Si se genera el documento sin estas fases, esas secciones estarian vacias o con datos genericos.

---

## 2. ESTRUCTURA DEL PROGRAMA DE CAPACITACION

### 2.1 Definicion en Controlador

**Archivo:** `app/Controllers/DocumentosSSTController.php`

```php
public const TIPOS_DOCUMENTO = [
    'programa_capacitacion' => [
        'nombre' => 'Programa de Capacitacion',
        'descripcion' => 'Documento formal del programa de capacitacion en SST',
        'secciones' => [
            ['numero' => 1,  'nombre' => 'Introduccion',              'key' => 'introduccion'],
            ['numero' => 2,  'nombre' => 'Objetivo General',          'key' => 'objetivo_general'],
            ['numero' => 3,  'nombre' => 'Objetivos Especificos',     'key' => 'objetivos_especificos'],
            ['numero' => 4,  'nombre' => 'Alcance',                   'key' => 'alcance'],
            ['numero' => 5,  'nombre' => 'Marco Legal',               'key' => 'marco_legal'],
            ['numero' => 6,  'nombre' => 'Definiciones',              'key' => 'definiciones'],
            ['numero' => 7,  'nombre' => 'Responsabilidades',         'key' => 'responsabilidades'],
            ['numero' => 8,  'nombre' => 'Metodologia',               'key' => 'metodologia'],
            ['numero' => 9,  'nombre' => 'Cronograma de Capacitaciones', 'key' => 'cronograma'],
            ['numero' => 10, 'nombre' => 'Plan de Trabajo Anual',     'key' => 'plan_trabajo'],
            ['numero' => 11, 'nombre' => 'Indicadores',               'key' => 'indicadores'],
            ['numero' => 12, 'nombre' => 'Recursos',                  'key' => 'recursos'],
            ['numero' => 13, 'nombre' => 'Evaluacion y Seguimiento',  'key' => 'evaluacion'],
        ]
    ]
];
```

### 2.2 Descripcion de Cada Seccion

| # | Seccion | Tipo | Fuente de Datos |
|---|---------|------|-----------------|
| 1 | Introduccion | IA/Plantilla | Contexto cliente |
| 2 | Objetivo General | IA/Plantilla | Contexto cliente |
| 3 | Objetivos Especificos | IA/Plantilla | Estandares aplicables |
| 4 | Alcance | IA/Plantilla | Trabajadores, sedes |
| 5 | Marco Legal | IA/Plantilla | Normativa colombiana |
| 6 | Definiciones | IA/Plantilla | Glosario tecnico |
| 7 | Responsabilidades | IA/Plantilla | Estandares (7=Vigia, 21+=COPASST) |
| 8 | Metodologia | IA/Plantilla | Metodos de capacitacion |
| 9 | Cronograma | **Tabla** | `tbl_cronogcapacitacion` (Fase 1) |
| 10 | Plan de Trabajo | **Tabla** | `tbl_pta_actividades` (Fase 2) |
| 11 | Indicadores | **Tabla** | `tbl_indicadores_sst` (Fase 3) |
| 12 | Recursos | IA/Plantilla | Recursos RRHH, fisicos, financieros |
| 13 | Evaluacion | IA/Plantilla | Seguimiento y mejora |

### 2.3 Logica de Ajuste por Estandares

El sistema ajusta el contenido segun el nivel de estandares (7, 21 o 60):

```php
// Ejemplo en getPromptBaseParaSeccion()
'responsabilidades' => "Define los roles y responsabilidades para el programa.
ROLES SEGUN ESTANDARES:
- 7 estandares: SOLO 3-4 roles (Representante Legal, Responsable SST, VIGIA SST, Trabajadores)
- 21 estandares: 5-6 roles (incluye COPASST)
- 60 estandares: Todos los roles necesarios
ADVERTENCIA: Si son 7 estandares, NUNCA mencionar COPASST, usar 'Vigia de SST'"
```

---

## 3. GENERACION CON IA

### 3.1 Flujo de Generacion por Seccion

```
Usuario escribe contexto adicional
         |
         v
+-----------------------------------+
| Clic en "Generar con IA"          |
+-----------------------------------+
         |
         | AJAX POST /documentos/generar-seccion
         v
+-----------------------------------+
| DocumentosSSTController           |
| ::generarSeccionIA()              |
+-----------------------------------+
         |
         | if (contextoAdicional)
         |    -> generarConIAReal()
         | else
         |    -> generarContenidoSeccion()
         v
+-----------------------------------+
| IADocumentacionService            |
| ::generarSeccion()                |
| - Construye prompt con contexto   |
| - Llama OpenAI GPT-4o-mini        |
| - Retorna contenido               |
+-----------------------------------+
         |
         v
+-----------------------------------+
| Respuesta JSON                    |
| { success: true,                  |
|   contenido: "texto generado" }   |
+-----------------------------------+
         |
         v
+-----------------------------------+
| JavaScript actualiza textarea     |
| Muestra toast de exito            |
+-----------------------------------+
```

### 3.2 Servicio de IA

**Archivo:** `app/Services/IADocumentacionService.php`

```php
class IADocumentacionService
{
    // Modelo: gpt-4o-mini
    // Temperatura: 0.3 (consistente y formal)
    // Max tokens: 2000 por seccion

    public function generarSeccion(array $datos): array
    {
        // $datos contiene:
        // - seccion (numero, nombre)
        // - documento (tipo, nombre)
        // - cliente (datos empresa)
        // - contexto (contexto SST)
        // - prompt_base (prompt especifico de la seccion)
        // - contexto_adicional (input del usuario)
    }
}
```

### 3.3 Prompts por Seccion

Cada seccion tiene un prompt especifico definido en `getPromptBaseParaSeccion()`:

```php
$prompts = [
    'introduccion' => "Genera una introduccion para el Programa de Capacitacion...",
    'objetivo_general' => "Genera el objetivo general del Programa...",
    'objetivos_especificos' => "Genera los objetivos especificos...",
    'alcance' => "Define el alcance del programa...",
    'marco_legal' => "Lista el marco normativo aplicable...",
    'definiciones' => "Genera un glosario de terminos tecnicos...",
    'responsabilidades' => "Define los roles y responsabilidades...",
    'metodologia' => "Describe la metodologia de capacitacion...",
    'cronograma' => "Genera el cronograma de capacitaciones...",
    'plan_trabajo' => "Resume las actividades del Plan de Trabajo Anual...",
    'indicadores' => "Define los indicadores de gestion...",
    'recursos' => "Identifica los recursos necesarios...",
    'evaluacion' => "Define el mecanismo de seguimiento y evaluacion..."
];
```

---

## 4. ALMACENAMIENTO EN BASE DE DATOS

### 4.1 Tabla Principal

**Tabla:** `tbl_documentos_sst`

```sql
CREATE TABLE tbl_documentos_sst (
    id_documento INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    tipo_documento VARCHAR(50) NOT NULL,  -- 'programa_capacitacion'
    titulo VARCHAR(255),
    anio INT NOT NULL,
    contenido LONGTEXT,                    -- JSON con todas las secciones
    version INT DEFAULT 1,
    estado ENUM('borrador', 'en_revision', 'aprobado', 'obsoleto'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### 4.2 Estructura del JSON `contenido`

```json
{
    "secciones": [
        {
            "key": "introduccion",
            "titulo": "1. INTRODUCCION",
            "contenido": "El presente Programa de Capacitacion...",
            "aprobado": false
        },
        {
            "key": "objetivo_general",
            "titulo": "2. OBJETIVO GENERAL",
            "contenido": "Establecer los lineamientos...",
            "aprobado": true
        },
        // ... mas secciones
    ],
    "empresa": {
        "nombre": "Empresa Demo S.A.S.",
        "nit": "900123456-7"
    },
    "vigencia": 2026,
    "estandares_aplicables": 7,
    "firma": {
        "responsable": "Juan Perez",
        "cargo": "Responsable SG-SST",
        "licencia": "SST-12345"
    }
}
```

### 4.3 Normalizacion de Secciones

El sistema incluye una funcion `normalizarSecciones()` que:

1. Elimina secciones duplicadas
2. Asigna `key` a secciones que solo tienen `titulo`
3. Mantiene compatibilidad con formato antiguo (`"4. ALCANCE"`) y nuevo (`key: "alcance"`)

```php
private function normalizarSecciones(array $secciones, string $tipo): array
{
    // Elimina duplicados basandose en key o titulo
    // Mantiene el contenido mas reciente
    // Asegura que todas las secciones tengan key para futuras busquedas
}
```

---

## 5. INTERFAZ DE USUARIO

### 5.1 Vista de Generacion

**Archivo:** `app/Views/documentos_sst/generar_con_ia.php`

**Caracteristicas:**

- Sidebar con navegacion por secciones
- Cards colapsables para cada seccion
- Input de contexto adicional por seccion
- Botones: Generar IA, Guardar, Aprobar
- Barra de progreso global
- Acciones masivas: Generar Todo, Guardar Todo, Aprobar Todo

### 5.2 Sistema de Toasts

Notificaciones visuales para cada accion:

| Tipo | Color | Icono | Uso |
|------|-------|-------|-----|
| success | Verde | check-circle-fill | Accion completada |
| error | Rojo | x-circle-fill | Error en operacion |
| warning | Amarillo | exclamation-triangle-fill | Advertencia |
| info | Azul | info-circle-fill | Informacion |
| ia | Morado | robot | Contenido generado con IA |
| save | Verde | save-fill | Seccion guardada |

### 5.3 Vista Previa

**Archivo:** `app/Views/documentos_sst/programa_capacitacion.php`

**Caracteristicas:**

- Barra de herramientas: Volver, Imprimir, Descargar PDF
- Encabezado con logo y datos empresa
- Secciones renderizadas con formato profesional
- Tablas estilizadas para Cronograma/PTA/Indicadores
- Bloque de firma al final
- Estilos optimizados para impresion (@media print)

---

## 6. RUTAS DEL SISTEMA

### 6.1 Rutas de Generacion IA

**Archivo:** `app/Config/Routes.php`

```php
// Generador IA (flujo principal)
$routes->get('/generador-ia/(:num)', 'GeneradorIAController::index/$1');
$routes->post('/generador-ia/(:num)/generar-cronograma', 'GeneradorIAController::generarCronograma/$1');
$routes->post('/generador-ia/(:num)/generar-pta-completo', 'GeneradorIAController::generarPTACompleto/$1');
$routes->post('/generador-ia/(:num)/generar-indicadores', 'GeneradorIAController::generarIndicadores/$1');
$routes->get('/generador-ia/(:num)/resumen', 'GeneradorIAController::resumen/$1');

// Documentos SST
$routes->get('/documentos-sst/(:num)/programa-capacitacion/(:num)', 'DocumentosSSTController::programaCapacitacion/$1/$2');

// Generador por secciones
$routes->get('/documentos/generar/(:segment)/(:num)', 'DocumentosSSTController::generarConIA/$1/$2');
$routes->post('/documentos/generar-seccion', 'DocumentosSSTController::generarSeccionIA');
$routes->post('/documentos/guardar-seccion', 'DocumentosSSTController::guardarSeccion');
$routes->post('/documentos/aprobar-seccion', 'DocumentosSSTController::aprobarSeccion');
$routes->get('/documentos/pdf/(:num)', 'DocumentosSSTController::generarPDF/$1');
```

---

## 7. CONTROLADORES PRINCIPALES

### 7.1 DocumentosSSTController

**Archivo:** `app/Controllers/DocumentosSSTController.php`

| Metodo | Tipo | Descripcion |
|--------|------|-------------|
| `generarConIA($tipo, $idCliente)` | GET | Vista principal de generacion |
| `generarSeccionIA()` | POST/AJAX | Genera contenido de una seccion |
| `guardarSeccion()` | POST/AJAX | Guarda contenido editado |
| `aprobarSeccion()` | POST/AJAX | Marca seccion como aprobada |
| `programaCapacitacion($idCliente, $anio)` | GET | Vista previa del documento |
| `generarPDF($idDocumento)` | GET | Genera PDF del documento |

### 7.2 GeneradorIAController

**Archivo:** `app/Controllers/GeneradorIAController.php`

| Metodo | Tipo | Descripcion |
|--------|------|-------------|
| `index($idCliente)` | GET | Dashboard del generador IA |
| `generarCronograma($idCliente)` | POST | Genera cronograma (Fase 1) |
| `generarPTACompleto($idCliente)` | POST | Genera PTA (Fase 2) |
| `generarIndicadores($idCliente)` | POST | Genera indicadores (Fase 3) |
| `resumen($idCliente)` | GET | Resumen de lo generado |

---

## 8. STORED PROCEDURES INTEGRADOS

### 8.1 SP Implementado: sp_generar_codigo_documento

**Estado:** INTEGRADO Y FUNCIONANDO

Este SP genera codigos unicos para documentos SST con formato profesional.

**Formato del codigo:** `TIPO-TEMA-XXX`

Ejemplos:
- `PRG-CAP-001` - Programa de Capacitacion #1
- `PRG-CAP-002` - Programa de Capacitacion #2
- `POL-SST-001` - Politica SST #1

**Mapeo de tipos de documento a codigos:**

```php
public const CODIGOS_DOCUMENTO = [
    'programa_capacitacion' => ['tipo' => 'PRG', 'tema' => 'CAP'],
    'politica_sst' => ['tipo' => 'POL', 'tema' => 'SST'],
    'plan_emergencias' => ['tipo' => 'PLA', 'tema' => 'EME'],
    'programa_emo' => ['tipo' => 'PRG', 'tema' => 'EMO'],
    'matriz_peligros' => ['tipo' => 'MTZ', 'tema' => 'PEL'],
    'procedimiento_investigacion' => ['tipo' => 'PRO', 'tema' => 'INV'],
    'programa_inspecciones' => ['tipo' => 'PRG', 'tema' => 'INS'],
    'programa_epp' => ['tipo' => 'PRG', 'tema' => 'EPP'],
];
```

**Integracion en el controlador:**

```php
protected function generarCodigoDocumento(int $idCliente, string $tipoDocumento): string
{
    // Llamar al Stored Procedure
    $this->db->query("CALL sp_generar_codigo_documento(?, ?, ?, @codigo)", [
        $idCliente,
        $codigos['tipo'],
        $codigos['tema']
    ]);

    $result = $this->db->query("SELECT @codigo as codigo")->getRow();
    return $result->codigo;  // Ej: PRG-CAP-001
}
```

**Donde se muestra el codigo:**

1. **Editor de secciones:** Badge amarillo en el header
2. **Vista previa:** En info-documento y encabezado del documento
3. **Base de datos:** Campo `codigo` en `tbl_documentos_sst`

### 8.2 Migracion de Base de Datos

**Archivo:** `app/SQL/migracion_agregar_codigo_documento.sql`

Agrega el campo `codigo VARCHAR(50)` a `tbl_documentos_sst` y actualiza documentos existentes.

```sql
ALTER TABLE tbl_documentos_sst
ADD COLUMN codigo VARCHAR(50) NULL COMMENT 'Codigo del documento (PRG-CAP-001)';
```

### 8.3 Estado de Otros SPs

| SP | Estado | Uso |
|----|--------|-----|
| `sp_generar_codigo_documento` | **INTEGRADO** | Genera codigos unicos PRG-CAP-001 |
| `sp_generar_carpetas_cliente` | Pendiente | Para estructura PHVA |
| `sp_calcular_cumplimiento` | Pendiente | Para % cumplimiento en documentos |
| `sp_calcular_nivel_estandares` | Deprecado | Campo manual en contexto |
| `sp_inicializar_estandares_cliente` | Parcial | Via EstandaresClienteController |

### 8.4 Proximos SPs a Integrar

1. **sp_calcular_cumplimiento** - Mostrar % cumplimiento en el documento generado
2. **sp_generar_carpetas_cliente** - Organizar documentos en carpetas PHVA

---

## 9. EXPORTACION DE DOCUMENTOS (PDF y Word)

### 9.1 Vista Principal

**Archivo:** `app/Views/documentos_sst/programa_capacitacion.php`

- Encabezado formal: Logo | Titulo SG-SST | Codigo/Version/Fecha
- Logo obtenido de `tbl_clientes.logo`
- Botones de descarga: PDF y Word

### 9.2 Exportacion PDF

**Archivo:** `app/Views/documentos_sst/pdf_template.php`

- Tecnologia: Dompdf
- Encabezado formal con logo embebido en base64
- Interlineado compacto (`line-height: 1.15-1.2`)
- Control de Cambios y Firmas incluidos

### 9.3 Exportacion Word

**Archivo:** `app/Views/documentos_sst/word_template.php`

- Formato HTML compatible con Microsoft Word (.doc)
- Interlineado compacto (`mso-line-height-rule: exactly`)
- Headers HTTP para descarga automatica

### 9.4 Rutas de Exportacion

```php
$routes->get('/documentos-sst/exportar-pdf/(:num)', 'DocumentosSSTController::exportarPDF/$1');
$routes->get('/documentos-sst/exportar-word/(:num)', 'DocumentosSSTController::exportarWord/$1');
```

### 9.5 Conversion Markdown a HTML

| Markdown | HTML |
|----------|------|
| `**texto**` | `<strong>texto</strong>` |
| `*texto*` | `<em>texto</em>` |
| `- item` | `<li>item</li>` |

---

## 10. SISTEMA DE FIRMAS Y VERSIONADO

### 10.1 Control de Cambios (Versionado)

- Tabla visual con historial de versiones del documento
- Cada version muestra: numero (badge azul), descripcion del cambio, fecha
- Si no hay versiones: "1.0 - Elaboracion inicial del documento"

### 10.2 Logica de Firmas Condicional

La seccion de firmas se adapta segun la configuracion del contexto del cliente:

| Condicion | Firmantes |
|-----------|-----------|
| `requiere_delegado_sst = true` | 3 columnas: Consultor SST + Delegado SST + Rep. Legal |
| 7 estandares (<=10 trabajadores) SIN delegado | 2 columnas: Consultor SST + Rep. Legal |
| 21+ estandares SIN delegado | 3 columnas: Consultor SST + Vigia/COPASST + Rep. Legal |

### 10.3 Datos de Firmantes

| Firmante | Fuente de Datos |
|----------|-----------------|
| Consultor SST | `tbl_consultor` (nombre, cargo, licencia, firma digital) |
| Delegado SST | `tbl_cliente_contexto_sst` (delegado_sst_nombre, delegado_sst_cargo) |
| Representante Legal | Contexto SST o `tbl_clientes` |
| Vigia/COPASST | Segun `estandares_aplicables` (<=21 = Vigia, >21 = COPASST) |

### 10.4 Firma Digital del Consultor

- Imagen de firma desde `uploads/` + `firma_consultor`
- En PDF: Convertida a base64 para embeber
- Dimensiones fijas para consistencia visual

### 10.5 Mejoras Visuales

- Tildes corregidas: "Elaboro", "Aprobo", "Reviso"
- Lineas de firma alineadas al mismo nivel
- Vista web: `position: absolute; bottom: 15px`
- PDF: Fila separada para firmas (compatibilidad Dompdf)
- Altura fija de celdas (180px para 2 firmantes, 160px para 3)

### 10.6 Flujo del Toggle Delegado SST

```
Usuario activa "Requiere Delegado SST" en Contexto
         |
         v
Se guarda requiere_delegado_sst = 1 en tbl_cliente_contexto_sst
         |
         v
Al generar/visualizar documento:
  - Si activo -> Columna Delegado SST con sus datos
  - Si inactivo -> Columna Vigia/COPASST segun estandares
```

### 10.7 Estructura de Firmas en Documento

```
+------------------------+------------------------+------------------------+
|       ELABORO          |        REVISO          |        APROBO          |
+------------------------+------------------------+------------------------+
|                        |                        |                        |
|    [Firma Digital]     |    ____________        |    ____________        |
|                        |                        |                        |
+------------------------+------------------------+------------------------+
| Nombre Consultor       | Nombre Vigia/Delegado  | Nombre Rep. Legal      |
| Responsable SG-SST     | Vigia SST / Delegado   | Representante Legal    |
| Lic. SST: XXXXX        |                        | CC: XXXXXXXXX          |
+------------------------+------------------------+------------------------+
```

### 10.8 Flujo para Crear Nueva Version

Desde la Vista Previa del documento aprobado:

**Opcion 1: Editar directamente**

```
Click en "Editar"
    |
    v
Pantalla de generacion
    |
    v
Modifica secciones (IA o manual)
    |
    v
Guarda y aprueba secciones
    |
    v
Vista Previa -> "Aprobar Documento"
    |
    v
Ingresa motivo -> Crea v1.1 o v2.0
```

**Opcion 2: Nueva Version (recomendada)**

```
Click en "Nueva Version"
    |
    v
Selecciona tipo de cambio:
  - Menor (v1.x) - correcciones, ajustes
  - Mayor (v2.0) - cambios significativos
    |
    v
Ingresa motivo del cambio
    |
    v
Click "Continuar a Edicion"
    |
    v
Documento pasa a estado "borrador"
    |
    v
Redirige a pantalla de edicion (alerta informativa)
    |
    v
Modifica secciones -> Guarda -> Aprueba
    |
    v
Vista Previa -> Aprobar -> Nueva version creada
```

### 10.9 Tabla de Versiones

**Tabla:** `tbl_doc_versiones_sst`

| Campo | Descripcion |
|-------|-------------|
| `id_cliente` | Para consultas rapidas |
| `codigo` | Codigo del documento (PRG-CAP-001) |
| `titulo` | Titulo del documento |
| `anio` | Ano de vigencia |
| `version_texto` | "1.0", "1.1", "2.0", etc. |
| `descripcion_cambio` | Motivo que aparece en Control de Cambios |
| `contenido_snapshot` | Copia del contenido al momento de aprobar |
| `created_at` | Fecha de creacion de la version |

### 10.10 Archivos del Sistema de Versionado

| Archivo | Funcion |
|---------|---------|
| `programa_capacitacion.php` | Botones Editar/Nueva Version, modal |
| `generar_con_ia.php` | Alerta de version pendiente |
| `DocumentosSSTController.php` | Endpoint `iniciarNuevaVersion` |
| `Routes.php` | Ruta `/documentos-sst/iniciar-nueva-version` |

---

## 11. DIFERENCIAS CON PARTES ANTERIORES

### 11.1 Parte 1 - Arquitectura

| Documentado | Implementado |
|-------------|--------------|
| Librerias PHP definen estructura | Constante `TIPOS_DOCUMENTO` en controlador |
| Aprobacion por seccion + global | Solo aprobacion por seccion |
| Flujo firma tipo DocuSeal | Firmas condicionales implementadas |

### 11.2 Parte 2 - Generacion IA

| Documentado | Implementado |
|-------------|--------------|
| Temperatura 0.3 | Correcto |
| Max 2000 tokens | Correcto |
| Contexto en sidebar | Contexto por seccion (input) |

### 11.3 Parte 7 - Tareas

| Tarea | Estado |
|-------|--------|
| Crear clase base DocumentoBase.php | NO (usar constante) |
| Implementar DocumentoGeneradorService.php | PARCIAL (IADocumentacionService) |
| Vista de generacion/edicion | SI (generar_con_ia.php) |
| Edicion inline de secciones | SI |
| Campo de contexto adicional | SI |
| Generacion PDF | SI (Dompdf integrado) |
| Generacion Word | SI (HTML compatible) |

---

## 12. ARCHIVOS CLAVE DEL SISTEMA

### 12.1 Controladores

```
app/Controllers/
├── DocumentosSSTController.php      [PRINCIPAL - Generacion documentos]
├── GeneradorIAController.php        [Flujo Phase-Locking]
├── ContextoClienteController.php    [Contexto SST cliente]
├── EstandaresClienteController.php  [Cumplimiento PHVA]
```

### 12.2 Servicios

```
app/Services/
├── IADocumentacionService.php       [Integracion OpenAI]
├── CronogramaIAService.php          [Generacion cronograma]
├── PTAGeneratorService.php          [Generacion PTA]
├── OpenAIService.php                [Servicio base OpenAI]
```

### 12.3 Vistas

```
app/Views/documentos_sst/
├── generar_con_ia.php               [Editor por secciones]
├── programa_capacitacion.php        [Vista previa documento]
├── pdf_template.php                 [Template para PDF]
├── word_template.php                [Template para Word]

app/Views/generador_ia/
├── index.php                        [Dashboard generador]
├── cronograma.php                   [Vista cronograma]
├── pta.php                          [Vista PTA]
├── indicadores.php                  [Vista indicadores]
```

### 12.4 Modelos

```
app/Models/
├── ClientModel.php                  [Datos cliente]
├── ClienteContextoSSTModel.php      [Contexto SST]
├── CronogcapacitacionModel.php      [Cronograma capacitaciones]
├── IndicadorSSTModel.php            [Indicadores SST]
├── ResponsableSSTModel.php          [Responsables]
```

---

## 13. MEJORAS RECIENTES

### 13.1 Conversion de Tablas Markdown a HTML en Exports

**Archivos modificados:**
- `word_template.php`
- `pdf_template.php`

**Funciones agregadas:**
- `convertirMarkdownAHtml()` - Para Word
- `convertirMarkdownAHtmlPdf()` - Para PDF

**Capacidades:**
- Detectan automaticamente inicio/fin de tablas Markdown (`| col | col |`)
- Convierten negritas (`**texto**`), cursivas (`*texto*`) y listas (`- item`)
- Cada tabla se renderiza como HTML con estilos (encabezados azules, bordes)
- Manejan contenido mixto (texto + multiples tablas)

### 13.2 Flujo de Documentos SST Rediseñado

**Carpeta (`carpeta.php`):**
- Nueva tabla "Documentos SST Aprobados"
- Columnas: Codigo, Nombre, Año, Version, Estado
- Botones [Ver] y [Editar] para cada documento aprobado
- Boton "Crear con IA" solo aparece si NO hay documento aprobado del año actual

**Vista Previa (`programa_capacitacion.php`):**
- Eliminados botones de edicion
- Solo permite: Ver, Historial, Exportar PDF/Word
- Funcion `convertirTablaMarkdownSST()` para mostrar tablas correctamente

**Pantalla de Edicion (`generar_con_ia.php`):**
- Modal "Aprobar Documento y Crear Version"
- Permite seleccionar tipo de cambio:
  - Menor: v1.0 → v1.1
  - Mayor: v1.x → v2.0
- Requiere descripcion del cambio

### 13.3 Controladores Actualizados

**DocumentacionController.php:**
- Query para obtener documentos SST aprobados del cliente
- Pasa `$documentosSSTAprobados` a la vista carpeta

**DocumentosSSTController.php:**
- Funcion `normalizarSecciones()` corregida
- Ordena secciones segun estructura definida (1-13)
- Elimina duplicados y mantiene contenido mas reciente
- Version 1.0 para primera aprobacion

### 13.4 Correcciones de Datos

- Limpieza de versiones corruptas (v4.0 repetido)
- Reset de documento a version 1, estado borrador
- Orden correcto de secciones en BD

---

## 14. PROXIMOS PASOS RECOMENDADOS

### 14.1 Corto Plazo

1. [x] ~~Implementar generacion real de PDF con Dompdf~~ COMPLETADO
2. [ ] Agregar marca de agua "BORRADOR" en documentos no aprobados
3. [x] ~~Implementar exportacion a Word (.doc)~~ COMPLETADO
4. [x] ~~Conversion tablas Markdown en exports~~ COMPLETADO

### 14.2 Mediano Plazo

1. [ ] Migrar estructura de documentos a Librerias PHP
2. [x] ~~Implementar versionamiento de documentos~~ COMPLETADO (Control de Cambios)
3. [x] ~~Integrar firmas en documentos~~ COMPLETADO (Firmas condicionales)
4. [x] ~~Flujo documentos desde carpeta~~ COMPLETADO

### 14.3 Largo Plazo

1. [ ] Crear mas tipos de documentos (Plan Emergencias, PVE, etc.)
2. [ ] Dashboard de cumplimiento integrado con documentos
3. [ ] Reportes de generacion y uso de IA
4. [ ] Firma electronica real (DocuSeal o similar)

---

*Documento actualizado: Enero 2026*
*Proyecto: EnterpriseSST - Modulo de Documentacion*
*Parte 8 de 8*
