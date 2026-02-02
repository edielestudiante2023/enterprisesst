# Prompt para Crear Nuevo Documento SST

---

## ⛔ PROHIBICIONES ABSOLUTAS - LEER PRIMERO ⛔

### REGLA #1: NUNCA CREAR CONTROLADORES Pz* o Hz*

```
❌ PROHIBIDO: Crear app/Controllers/PznuevoDocumentoController.php
❌ PROHIBIDO: Crear app/Controllers/HznuevoDocumentoController.php
❌ PROHIBIDO: Crear cualquier archivo que empiece con Pz o Hz
```

**Si necesitas crear un nuevo documento, SIEMPRE usa DocumentosSSTController.php**

### REGLA #2: NUNCA MODIFICAR CONTROLADORES Pz* o Hz* EXISTENTES

```
❌ PROHIBIDO: Editar app/Controllers/PzasignacionresponsableSstController.php
❌ PROHIBIDO: Editar app/Controllers/PzresponsabilidadesRepLegalController.php
❌ PROHIBIDO: Editar app/Controllers/HzauditoriaController.php
❌ PROHIBIDO: Editar CUALQUIER archivo Pz*.php o Hz*.php existente
```

**Los controladores Pz*/Hz* son LEGACY. NO SE TOCAN. Siguen funcionando pero estan congelados.**

### REGLA #3: SI NECESITAS FUNCIONALIDAD DE UN Pz*/Hz*, DUPLICALA

```
✅ CORRECTO: Leer el codigo de PzasignacionresponsableSstController.php
✅ CORRECTO: Copiar la logica que necesitas
✅ CORRECTO: Implementarla EN DocumentosSSTController.php
✅ CORRECTO: Crear nuevas rutas que apunten a DocumentosSSTController

❌ INCORRECTO: Modificar PzasignacionresponsableSstController.php
❌ INCORRECTO: Agregar metodos a controladores Pz*/Hz*
❌ INCORRECTO: Cambiar rutas existentes de Pz*/Hz*
```

### REGLA #4: COEXISTENCIA SIN CONFLICTO

Los controladores Pz*/Hz* y DocumentosSSTController pueden coexistir:

```
/pz/asignacion-responsable/{id}     -> PzasignacionresponsableSstController (LEGACY, no tocar)
/documentos-sst/asignacion/{id}     -> DocumentosSSTController (NUEVO, usar este)
```

**NUNCA sobrescribir rutas existentes. Crear rutas NUEVAS que apunten a DocumentosSSTController.**

### RESUMEN EJECUTIVO

| Accion | Permitido |
|--------|-----------|
| Crear controlador Pz* nuevo | ❌ **NUNCA** |
| Crear controlador Hz* nuevo | ❌ **NUNCA** |
| Editar controlador Pz* existente | ❌ **NUNCA** |
| Editar controlador Hz* existente | ❌ **NUNCA** |
| Leer codigo de Pz*/Hz* para entender logica | ✅ SI |
| Copiar logica de Pz*/Hz* a DocumentosSSTController | ✅ SI |
| Crear metodos nuevos en DocumentosSSTController | ✅ SI |
| Crear rutas nuevas para DocumentosSSTController | ✅ SI |

---

## ARQUITECTURA DE PERMISOS: CONSULTOR vs CLIENTE

### IMPORTANTE - Dos Entornos Completamente Separados

El sistema tiene **DOS entornos con permisos diferentes**:

| Aspecto | CONSULTOR | CLIENTE |
|---------|-----------|---------|
| **URL Base** | `/documentacion/carpeta/{id}` | `/client/mis-documentos-sst/carpeta/{id}` |
| **Controlador** | `DocumentacionController` | `ClienteDocumentosSstController` |
| **Crear documentos** | SI | NO |
| **Editar documentos** | SI | NO |
| **Eliminar documentos** | SI | NO |
| **Aprobar/Firmar** | SI | NO |
| **Ver documentos** | SI (todos los estados) | SI (solo aprobados/firmados) |
| **Descargar PDF** | SI | SI |
| **Gestionar presupuesto** | SI | NO |

### Reglas de Implementacion

1. **El cliente NUNCA puede modificar datos** - Solo visualiza y descarga PDFs de documentos ya aprobados/firmados.

2. **Filtro de estados para cliente:**
   ```php
   // En ClienteDocumentosSstController - SOLO documentos finalizados
   ->whereIn('estado', ['aprobado', 'firmado'])
   ```

3. **Sin botones de accion para cliente:**
   - No hay botones de "Editar", "Eliminar", "Aprobar"
   - Solo boton "Descargar PDF"

4. **Mapeo de plantillas obligatorio:**
   - Cada nuevo documento debe agregarse a `tbl_doc_plantilla_carpeta`
   - Y al metodo `mapearPlantillaATipoDocumento()` en `ClienteDocumentosSstController`

### Archivos del Entorno Cliente (Solo Lectura)

| Archivo | Funcion |
|---------|---------|
| `app/Controllers/ClienteDocumentosSstController.php` | Lista documentos aprobados del cliente |
| `app/Views/client/documentos_sst/index.php` | Vista arbol de carpetas |
| `app/Views/client/documentos_sst/carpeta.php` | Vista de documentos en carpeta |

**NUNCA agregar funcionalidad de edicion/eliminacion a estos archivos del cliente.**

### Documentos con Firma Fisica vs Firma Electronica

| Tipo de Firma | Ejemplo | Flujo Cliente |
|---------------|---------|---------------|
| **Firma Electronica** | Responsabilidades Rep. Legal | Cliente ve PDF generado con firmas digitales |
| **Firma Fisica** | Responsabilidades Trabajadores | Cliente ve PDF escaneado (archivo adjuntado) |

**Flujo de firma fisica:**
1. Consultor genera PDF imprimible
2. Trabajadores firman en papel durante induccion
3. Consultor escanea y adjunta el documento firmado (boton "Adjuntar")
4. Sistema guarda enlace en `tbl_doc_versiones_sst.archivo_pdf`
5. Cliente ve boton verde "Firmado" que descarga el escaneado

**Codigo en ClienteDocumentosSstController:**
```php
// Obtener enlace del archivo firmado si existe
$doc['archivo_firmado'] = $this->obtenerArchivoFirmado($doc['id_documento']);

// obtenerArchivoFirmado() busca en tbl_doc_versiones_sst.archivo_pdf
```

**Vista del cliente (carpeta.php):**
```php
<?php if (!empty($doc['archivo_firmado'])): ?>
    <a href="<?= esc($doc['archivo_firmado']) ?>" class="btn btn-success">Firmado</a>
<?php else: ?>
    <a href="<?= base_url('documentos-sst/exportar-pdf/' . $doc['id_documento']) ?>" class="btn btn-pdf">PDF</a>
<?php endif; ?>
```

---

## INSTRUCCIONES PARA CLAUDE

```
Necesito crear un nuevo tipo de documento SST para el aplicativo EnterpriseSST.

ANTES DE ESCRIBIR CUALQUIER CODIGO, haz lo siguiente:

### FASE 1: Determinar el tipo de documento

Preguntame primero:
- ¿El documento requiere generacion con IA y edicion de secciones? (como Programa de Capacitacion)
- ¿O es un documento simple que se genera automaticamente desde el contexto del cliente? (como Asignacion de Responsable)

**ARQUITECTURA UNIFICADA - Todo en DocumentosSSTController:**

Todos los documentos se manejan desde `DocumentosSSTController.php`, diferenciados por su `flujo`:

| Flujo | Descripcion | Ejemplo |
|-------|-------------|---------|
| `secciones_ia` | Editor de secciones con IA | Programa Capacitacion, Plan Emergencias |
| `auto_contexto` | Generacion automatica desde contexto | Asignacion Responsable, Responsabilidades |
| `formulario` | Formulario interactivo | Presupuesto SST |

**NO CREAR controladores Pz* o Hz* nuevos.** Los existentes son LEGACY y seran migrados.

### FASE 2: Leer archivos de referencia

**SIEMPRE leer estos archivos:**
1. app/Controllers/DocumentosSSTController.php - especialmente:
   - Constantes TIPOS_DOCUMENTO y CODIGOS_DOCUMENTO
   - Metodo generarConIA() para documentos con IA
   - Metodo generarAutoContexto() para documentos simples
   - Metodos de vista previa (programaCapacitacion(), asignacionResponsable())
2. app/Views/documentos_sst/generar_con_ia.php (editor de secciones)
3. app/Views/documentos_sst/pdf_template.php (template PDF unificado)
4. app/Views/documentos_sst/word_template.php (template Word unificado)

### FASE 3: Preguntarme sobre el nuevo documento

Hazme estas preguntas UNA POR UNA:

**Pregunta 1 - Identidad del documento:**
- ¿Como se llama el documento? (ej: "Programa de Vigilancia Epidemiologica")
- ¿Cual es el codigo? Muestrame los existentes en CODIGOS_DOCUMENTO para elegir formato (ej: PRG-VEP)

**Pregunta 2 - Secciones del documento:**
- Propon una lista de secciones basandote en la Resolucion 0312/2019 y normativa colombiana
- Cada seccion necesita: numero, nombre, key
- Preguntame cuales se alimentan de tablas existentes vs generadas por IA/plantilla

**Pregunta 3 - Ubicacion en carpetas:**
- Preguntame en cual estandar de la Resolucion 0312 se ubica este documento
- Consultar tbl_doc_carpetas para ver opciones disponibles

**Pregunta 4 - Dependencias:**
- ¿Requiere fases previas? (cronograma, PTA, indicadores, responsables)
- ¿O es independiente?

**Pregunta 5 - Vista previa:**
- ¿Similar a Programa de Capacitacion? (encabezado + secciones + firmas + control cambios)
- ¿O estructura diferente?

### FASE 4: Implementar EN BLOQUE

IMPORTANTE: Implementar TODO de una vez, en este orden:

---

#### BLOQUE 1: ENTORNO CONSULTOR (crear/editar/aprobar/firmar)

**ARQUITECTURA UNIFICADA - Todo en DocumentosSSTController + Vistas por Componentes:**

| Paso | Archivo | Accion |
|------|---------|--------|
| 1 | DocumentosSSTController.php | Agregar en TIPOS_DOCUMENTO con flujo correcto |
| 2 | ⛔ CODIGOS_DOCUMENTO | **OBSOLETO** - Los códigos van en tbl_doc_plantillas |
| 3 | DocumentosSSTController.php | Si flujo=secciones_ia: agregar prompts en getPromptBaseParaSeccion() |
| 4 | DocumentosSSTController.php | Si flujo=auto_contexto: agregar logica en generarAutoContexto() |
| 5 | DocumentosSSTController.php | Crear metodo vista previa (ej: nuevodocumento()) |
| 6 | app/Views/documentos_sst/[nuevo].php | Crear vista previa del documento |
| 7 | Routes.php | Agregar rutas del documento |
| 8 | DocumentacionController.php | Agregar código en determinarTipo() |
| 9 | app/Views/documentacion/_tipos/[tipo].php | **CREAR vista de tipo** con botón/dropdown |
| 10 | FasesDocumentoService.php | Agregar fase si tiene dependencias |

**⚠️ NUEVA ARQUITECTURA:** Los botones/dropdowns van en `_tipos/mi_tipo.php`, NO en carpeta.php

**Estructura de TIPOS_DOCUMENTO:**

```php
public const TIPOS_DOCUMENTO = [
    'nuevo_documento' => [
        'nombre' => 'Nombre del Documento',
        'descripcion' => 'Descripcion breve',
        'flujo' => 'auto_contexto',  // o 'secciones_ia' o 'formulario'
        'estandar' => '1.1.1',       // Codigo del estandar 0312
        'firmantes' => ['representante_legal', 'consultor'],  // Quienes firman
        'secciones' => [             // Solo si flujo=secciones_ia
            ['numero' => 1, 'nombre' => 'Introduccion', 'key' => 'introduccion'],
            // ...
        ]
    ]
];
```

**NO CREAR controladores Pz* o Hz* nuevos.**

---

#### BLOQUE 2: ENTORNO CLIENTE (solo lectura - ver/descargar PDF)

| Paso | Archivo | Accion |
|------|---------|--------|
| 1 | ClienteDocumentosSstController.php | Agregar mapeo en mapearPlantillaATipoDocumento() |

```php
// En ClienteDocumentosSstController::mapearPlantillaATipoDocumento()
$mapa = [
    'PRG-CAP' => 'programa_capacitacion',
    'ASG-RES' => 'asignacion_responsable_sgsst',
    'NUEVO-COD' => 'nuevo_tipo_documento',  // <-- Agregar aqui
];
```

**NOTA:** El cliente accede via `/client/mis-documentos-sst` y solo ve documentos con estado `aprobado` o `firmado`.

---

#### BLOQUE 3: BASE DE DATOS (LOCAL + PRODUCCION)

| Paso | Tabla | Accion |
|------|-------|--------|
| 1 | tbl_doc_plantilla_carpeta | INSERT mapeo codigo_plantilla -> codigo_carpeta |
| 2 | tbl_doc_plantillas | INSERT plantilla si no existe |
| 3 | tbl_doc_tipos | INSERT tipo si no existe |

Crear script en `app/SQL/ejecutar_[nombre_documento].php` y ejecutar:
```bash
php app/SQL/ejecutar_[nombre_documento].php
```

---

### FASE 5: Verificacion

**Verificar entorno CONSULTOR:**
1. Ir a la carpeta del estandar y ver que aparezca el boton para generar
2. Generar el documento (con IA o automatico segun patron)
3. Ver vista previa
4. Exportar PDF
5. Solicitar firmas
6. Publicar en reportList

**Verificar entorno CLIENTE:**
1. Acceder a /client/mis-documentos-sst
2. Navegar a la carpeta del estandar
3. Ver el documento en la tabla (solo si esta aprobado/firmado)
4. Descargar PDF

### REGLAS IMPORTANTES:

- **TODO en DocumentosSSTController.php** - No crear controladores Pz* o Hz* nuevos
- **Vistas de carpeta usan arquitectura _tipos/_components** - Ver `docs/ARQUITECTURA_VISTAS_COMPONENTES.md`
- Usar el campo `flujo` para diferenciar tipos de documento
- Reutilizar vistas PDF/Word unificadas (pdf_template.php, word_template.php)
- Los prompts de IA deben ser especificos para normativa colombiana SST
- Siempre ejecutar cambios de BD en LOCAL y PRODUCCION
- Cada documento debe ajustarse por estandares (7, 21, 60)
- SIEMPRE agregar mapeo en ClienteDocumentosSstController para vista cliente
- Los controladores Pz*/Hz* existentes son **LEGACY** y seran migrados gradualmente
- **Botones/dropdowns van en `_tipos/mi_tipo.php`**, NO editar carpeta.php directamente

### DOCUMENTO QUE QUIERO CREAR:

[ESCRIBIR AQUI EL NOMBRE DEL DOCUMENTO]
```

---

## EJEMPLO 1: Documento con IA (flujo: secciones_ia)

```
DOCUMENTO QUE QUIERO CREAR: Programa de Vigilancia Epidemiologica

Flujo: secciones_ia
Estandar: 3.1.4
Firmantes: representante_legal, consultor
```

Claude agregara en TIPOS_DOCUMENTO con flujo='secciones_ia', creara prompts y metodo de vista previa.

---

## EJEMPLO 2: Documento auto-generado (flujo: auto_contexto)

```
DOCUMENTO QUE QUIERO CREAR: Politica de Seguridad y Salud en el Trabajo

Flujo: auto_contexto
Estandar: 2.1.1
Firmantes: representante_legal
```

Claude agregara en TIPOS_DOCUMENTO con flujo='auto_contexto', creara logica en generarAutoContexto() y vista previa.

---

## ARCHIVOS DE REFERENCIA

### Controlador Principal (TODOS los documentos):

| Archivo | Funcion |
|---------|---------|
| `app/Controllers/DocumentosSSTController.php` | Controlador UNICO para todos los documentos |
| `app/Views/documentos_sst/generar_con_ia.php` | Editor de secciones (flujo: secciones_ia) |
| `app/Views/documentos_sst/pdf_template.php` | Template PDF unificado |
| `app/Views/documentos_sst/word_template.php` | Template Word unificado |
| `app/Services/FasesDocumentoService.php` | Definicion de fases y dependencias |
| `app/Controllers/DocumentacionController.php` | Logica de carpeta.php |

### ⛔ Controladores LEGACY (PROHIBIDO CREAR O MODIFICAR):

| Archivo | Estado | Accion Permitida |
|---------|--------|------------------|
| `app/Controllers/Pz*.php` | **CONGELADO** | Solo LEER para entender logica |
| `app/Controllers/Hz*.php` | **CONGELADO** | Solo LEER para entender logica |

**⛔ NUNCA crear controladores Pz* o Hz* nuevos**
**⛔ NUNCA modificar controladores Pz* o Hz* existentes**
**✅ SI necesitas su funcionalidad, copiala a DocumentosSSTController**

### Vista del Cliente (solo lectura):

| Archivo | Funcion |
|---------|---------|
| `app/Controllers/ClienteDocumentosSstController.php` | Controlador para vista del cliente |
| `app/Views/client/documentos_sst/index.php` | Arbol de carpetas PHVA |
| `app/Views/client/documentos_sst/carpeta.php` | Tabla de documentos con boton PDF |

---

## NOTAS TECNICAS

### Arquitectura Unificada
- **UN SOLO controlador** (`DocumentosSSTController`) para todos los documentos
- El campo `flujo` en TIPOS_DOCUMENTO define el comportamiento:
  - `secciones_ia`: Editor de secciones con generacion IA
  - `auto_contexto`: Generacion automatica desde contexto del cliente
  - `formulario`: Formulario interactivo (ej: Presupuesto)
- Infraestructura compartida: versionamiento, firmas, exportacion PDF/Word
- El cliente accede via `/client/mis-documentos-sst` - solo ve documentos aprobados/firmados

### 🏗️ ARQUITECTURA DE VISTAS POR COMPONENTES Y TIPOS (NUCLEAR)

**Esta es la arquitectura actual para vistas que manejan múltiples tipos de contenido desde una URL.**

**Ver documentación completa en:** `docs/ARQUITECTURA_VISTAS_COMPONENTES.md`

#### Estructura de Archivos

```
app/Views/{modulo}/
├── {vista_principal}.php          # Layout base (~80 líneas)
├── _components/                   # Componentes reutilizables
│   ├── styles.php                 # CSS del módulo
│   ├── header.php                 # Navbar + breadcrumb
│   ├── alertas.php                # Mensajes flash
│   ├── panel_fases.php            # Panel de fases del documento
│   ├── tabla_documentos.php       # Tabla de documentos
│   └── scripts.php                # JavaScript del módulo
└── _tipos/                        # Vistas por tipo de carpeta
    ├── generica.php               # Tipo por defecto
    ├── responsables_sst.php       # Vista para código 1.1.1
    ├── responsabilidades_sgsst.php # Vista para código 1.1.2
    ├── presupuesto_sst.php        # Vista para código 1.1.3
    └── capacitacion_sst.php       # Vista para código 1.2.1
```

#### Flujo de Datos

```
1. URL: /documentacion/carpeta/{id}
                │
                ▼
2. CONTROLADOR: determinarTipo($carpeta) → retorna 'responsables_sst'
                │
                ▼
3. LAYOUT BASE: view('documentacion/carpeta', ['vistaContenido' => 'documentacion/_tipos/responsables_sst'])
                │
                ▼
4. LAYOUT carga: view($vistaContenido) → Renderiza _tipos/responsables_sst.php
                │
                ▼
5. VISTA DE TIPO usa componentes: view('_components/panel_fases'), view('_components/tabla_documentos')
```

#### Cómo Agregar un Nuevo Tipo de Carpeta

**PASO 1: Crear vista del tipo**

```bash
# Crear archivo en _tipos/
touch app/Views/documentacion/_tipos/nuevo_tipo.php
```

```php
<?php
/**
 * Vista de Tipo: Nuevo Tipo
 * Código: X.X.X
 * Descripción de qué muestra esta carpeta
 */
?>

<!-- Card específica con botón de acción -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h4><?= esc($carpeta['nombre']) ?></h4>
        <form action="<?= base_url('documentos-sst/' . $cliente['id_cliente'] . '/crear-nuevo') ?>" method="post">
            <button type="submit" class="btn btn-success">Generar Documento</button>
        </form>
    </div>
</div>

<!-- Componentes reutilizables -->
<?= view('documentacion/_components/panel_fases', ['fasesInfo' => $fasesInfo ?? null]) ?>
<?= view('documentacion/_components/tabla_documentos', ['documentosSSTAprobados' => $documentosSSTAprobados ?? []]) ?>
```

**PASO 2: Agregar detección en DocumentacionController**

```php
// En determinarTipo() - línea ~150
protected function determinarTipo(array $carpeta): ?string
{
    $codigo = strtolower($carpeta['codigo'] ?? '');

    // AGREGAR: Detectar nuevo tipo por código
    if ($codigo === 'x.x.x') return 'nuevo_tipo';

    // ... otros tipos ...
    return null;
}
```

**PASO 3: Agregar al array de tipos con documentos SST (si aplica)**

```php
// En carpeta() - buscar la línea con in_array para tipos SST
$tiposConDocumentos = ['capacitacion_sst', 'responsables_sst', 'responsabilidades_sgsst', 'nuevo_tipo'];
```

**IMPORTANTE:** Ya NO se edita `carpeta.php` directamente. Los botones y dropdowns van en `_tipos/nuevo_tipo.php`.

---

### ⛔ Códigos de Documento - CENTRALIZADOS EN BD (NO HARDCODEAR)

**Los códigos de documentos se almacenan en `tbl_doc_plantillas`:**

| Columna | Descripcion | Ejemplo |
|---------|-------------|---------|
| `codigo_sugerido` | Código base del documento | `PRG-CAP` |
| `tipo_documento` | Tipo usado en PHP (TIPOS_DOCUMENTO) | `programa_capacitacion` |

**Cómo funciona:**

```php
// El controlador obtiene el código desde BD:
$codigoBase = $this->obtenerCodigoPlantilla('programa_capacitacion');
// Retorna: 'PRG-CAP' (desde tbl_doc_plantillas.codigo_sugerido)

// Genera código con consecutivo:
$codigo = $this->generarCodigoDocumento($idCliente, 'programa_capacitacion');
// Retorna: 'PRG-CAP-001'
```

**Reglas:**

| Accion | Permitido |
|--------|-----------|
| Agregar código en PHP (CODIGOS_DOCUMENTO) | ❌ **PROHIBIDO** - Constante OBSOLETA |
| Agregar código en tbl_doc_plantillas | ✅ **CORRECTO** |
| Hardcodear códigos en cualquier archivo PHP | ❌ **PROHIBIDO** |

**Para agregar un nuevo tipo de documento:**

```sql
-- 1. PRIMERO en BD (sin esto el documento NO funciona)
INSERT INTO tbl_doc_plantillas (
    id_tipo, nombre, codigo_sugerido, tipo_documento, version, activo
) VALUES (
    3, 'Mi Nuevo Documento', 'MND-DOC', 'mi_nuevo_documento', '001', 1
);

-- 2. Mapear a carpeta
INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta)
VALUES ('MND-DOC', '1.1.1');
```

```php
// 3. DESPUÉS en DocumentosSSTController (tipo_documento debe coincidir)
public const TIPOS_DOCUMENTO = [
    'mi_nuevo_documento' => [  // <-- DEBE coincidir con BD
        'nombre' => 'Mi Nuevo Documento',
        'flujo' => 'auto_contexto',
        // ...
    ]
];
```

### ⛔ Controladores Legacy (Pz*/Hz*) - CONGELADOS

**ESTADO: 42 controladores Pz* + 10 controladores Hz* = TODOS CONGELADOS**

| Accion | Permitido |
|--------|-----------|
| Crear Pz* o Hz* nuevo | ❌ **PROHIBIDO** |
| Modificar Pz* o Hz* existente | ❌ **PROHIBIDO** |
| Agregar metodos a Pz* o Hz* | ❌ **PROHIBIDO** |
| Leer codigo de Pz* o Hz* | ✅ Permitido |
| Copiar logica a DocumentosSSTController | ✅ Permitido |

**Los controladores Pz*/Hz* siguen funcionando pero estan CONGELADOS. No se tocan.**

### Como "Migrar" Funcionalidad de un Pz*/Hz*

**IMPORTANTE: Migrar NO significa modificar el Pz*/Hz*. Significa DUPLICAR su logica en DocumentosSSTController.**

Pasos correctos:
1. **LEER** el controlador Pz*/Hz* para entender su logica (ej: `construirContenido()`)
2. **COPIAR** esa logica a un nuevo metodo en `DocumentosSSTController.php`
3. **AGREGAR** entrada en TIPOS_DOCUMENTO con flujo='auto_contexto'
4. **CREAR** nuevas rutas en Routes.php (NO modificar las existentes)
5. **PROBAR** que la nueva implementacion funciona
6. **DEJAR** el controlador Pz*/Hz* intacto - sigue funcionando para documentos antiguos

```
⛔ INCORRECTO:
   - Editar PzasignacionresponsableSstController.php
   - Agregar @deprecated al archivo
   - Eliminar el controlador

✅ CORRECTO:
   - Leer PzasignacionresponsableSstController.php
   - Crear asignacionResponsable() en DocumentosSSTController.php
   - Crear ruta /documentos-sst/asignacion/{id}
   - Ambos coexisten sin conflicto
```

---

## CREDENCIALES Y PATRON PARA CAMBIOS EN BASE DE DATOS

### Instruccion para Claude

Cuando necesites ejecutar cambios de base de datos (INSERT, UPDATE, ALTER, CREATE), **SIEMPRE** debes ejecutarlos en **ambos entornos**: LOCAL y PRODUCCION.

### Credenciales

```php
// LOCAL (XAMPP)
$localConfig = [
    'host' => 'localhost',
    'port' => 3306,
    'database' => 'empresas_sst',
    'username' => 'root',
    'password' => ''
];

// PRODUCCION (DigitalOcean)
$prodConfig = [
    'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
    'port' => 25060,
    'database' => 'empresas_sst',
    'username' => 'cycloid_userdb',
    'password' => 'AVNS_iDypWizlpMRwHIORJGG',
    'ssl' => true  // IMPORTANTE: Requiere SSL
];
```

### Patron de Codigo PHP para Migraciones

Crear archivo en `app/SQL/ejecutar_{nombre}.php`:

```php
<?php
/**
 * Script de migracion: {descripcion}
 * Ejecutar con: php app/SQL/ejecutar_{nombre}.php
 */

// Configuracion de entornos
$environments = [
    'LOCAL' => [
        'host' => 'localhost',
        'port' => 3306,
        'dbname' => 'empresas_sst',
        'user' => 'root',
        'pass' => '',
        'ssl' => false
    ],
    'PRODUCTION' => [
        'host' => 'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
        'port' => 25060,
        'dbname' => 'empresas_sst',
        'user' => 'cycloid_userdb',
        'pass' => 'AVNS_iDypWizlpMRwHIORJGG',
        'ssl' => true
    ]
];

// SQL a ejecutar
$sqlStatements = [
    "INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta)
     SELECT 'NUEVO-COD', '1.1.1'
     FROM DUAL
     WHERE NOT EXISTS (
         SELECT 1 FROM tbl_doc_plantilla_carpeta
         WHERE codigo_plantilla = 'NUEVO-COD' AND codigo_carpeta = '1.1.1'
     )",

    // Agregar mas statements si es necesario
];

foreach ($environments as $envName => $config) {
    echo "\n========== {$envName} ==========\n";

    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset=utf8mb4";
        $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

        if ($config['ssl']) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = true;
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        }

        $pdo = new PDO($dsn, $config['user'], $config['pass'], $options);
        echo "Conectado a {$envName}\n";

        foreach ($sqlStatements as $sql) {
            try {
                $pdo->exec($sql);
                echo "OK: " . substr($sql, 0, 60) . "...\n";
            } catch (PDOException $e) {
                // Ignorar errores de duplicado (ya existe)
                if (strpos($e->getMessage(), 'Duplicate') !== false) {
                    echo "SKIP (ya existe): " . substr($sql, 0, 40) . "...\n";
                } else {
                    echo "ERROR: " . $e->getMessage() . "\n";
                }
            }
        }

    } catch (PDOException $e) {
        echo "ERROR conexion {$envName}: " . $e->getMessage() . "\n";
    }
}

echo "\n========== COMPLETADO ==========\n";
```

### Ejecucion

```bash
# Desde la raiz del proyecto
php app/SQL/ejecutar_{nombre}.php
```

### Reglas para Migraciones

1. **NUNCA** ejecutar solo en LOCAL o solo en PRODUCCION
2. **SIEMPRE** usar el patron de doble conexion
3. **SIEMPRE** manejar errores de duplicado (ya existe)
4. **SIEMPRE** mostrar resultado por consola
5. Para PRODUCCION, SSL es **OBLIGATORIO**
6. El archivo debe quedar en `app/SQL/` para referencia futura
7. Nombrar archivos descriptivamente: `ejecutar_agregar_plantilla_[documento].php`

---

## CHECKLIST RAPIDO PARA NUEVO DOCUMENTO

**⚠️ ACTUALIZADO: Usa la nueva arquitectura de vistas por componentes**

**Ver:** `docs/ARQUITECTURA_VISTAS_COMPONENTES.md`

```
[ ] BLOQUE BASE DE DATOS (PRIMERO - SIN ESTO NO FUNCIONA)
    [ ] INSERT en tbl_doc_plantillas con codigo_sugerido Y tipo_documento
    [ ] INSERT en tbl_doc_plantilla_carpeta (codigo -> estandar)
    [ ] Script de migracion creado y ejecutado en LOCAL y PRODUCCION

[ ] BLOQUE DocumentosSSTController (UNICO CONTROLADOR)
    [ ] Agregar en TIPOS_DOCUMENTO con flujo correcto
    [ ] ⛔ NO agregar en CODIGOS_DOCUMENTO (OBSOLETO - usar BD)
    [ ] Si flujo=secciones_ia: agregar prompts en getPromptBaseParaSeccion()
    [ ] Si flujo=auto_contexto: agregar logica en generarAutoContexto()
    [ ] Crear metodo vista previa (ej: nuevoDocumento())
    [ ] Crear vista en app/Views/documentos_sst/[nuevo].php
    [ ] Agregar rutas en Routes.php

[ ] BLOQUE ACCESO EN CARPETA (NUEVA ARQUITECTURA _tipos/_components)
    [ ] Crear app/Views/documentacion/_tipos/mi_tipo.php (con botón/dropdown)
    [ ] Agregar detección en determinarTipo() de DocumentacionController
    [ ] Agregar tipo al array $tiposConDocumentos en carpeta()
    [ ] Si múltiples docs: dropdown va EN _tipos/mi_tipo.php (no en carpeta.php)
    [ ] Configurar mapa de URLs en _components/tabla_documentos.php

[ ] BLOQUE CLIENTE
    [ ] Agregar mapeo en mapearPlantillaATipoDocumento()

[ ] VERIFICACION
    [ ] Consultor puede crear documento
    [ ] Consultor puede ver vista previa
    [ ] Consultor puede exportar PDF
    [ ] Consultor puede exportar Word
    [ ] Consultor puede solicitar firmas
    [ ] Cliente ve documento aprobado/firmado
    [ ] Cliente puede descargar PDF
```

---

## IMPLEMENTACION DE FLUJO auto_contexto

Para documentos simples que se generan automaticamente desde el contexto del cliente:

### PASO 1: Agregar en Base de Datos (OBLIGATORIO)

```sql
-- Primero crear la plantilla con tipo_documento
INSERT INTO tbl_doc_plantillas (
    id_tipo, nombre, descripcion, codigo_sugerido, tipo_documento, version, activo
) VALUES (
    3,  -- Tipo: Programa (ver tbl_doc_tipos)
    'Nuevo Documento Simple',
    'Descripcion del documento',
    'NUE-DOC',           -- codigo_sugerido: genera NUE-DOC-001, NUE-DOC-002, etc.
    'nuevo_documento_simple',  -- tipo_documento: DEBE coincidir con TIPOS_DOCUMENTO
    '001',
    1
);

-- Mapear a la carpeta del estandar
INSERT INTO tbl_doc_plantilla_carpeta (codigo_plantilla, codigo_carpeta, descripcion)
VALUES ('NUE-DOC', '1.1.1', 'Nuevo Documento Simple');
```

### PASO 2: En DocumentosSSTController.php:

```php
// 1. Agregar en TIPOS_DOCUMENTO (el tipo_documento DEBE coincidir con la BD)
public const TIPOS_DOCUMENTO = [
    'nuevo_documento_simple' => [
        'nombre' => 'Nuevo Documento Simple',
        'descripcion' => 'Descripcion del documento',
        'flujo' => 'auto_contexto',
        'estandar' => '1.1.1',
        'firmantes' => ['representante_legal', 'consultor']
    ]
];

// ⛔ NO agregar en CODIGOS_DOCUMENTO - está OBSOLETO
// Los códigos se obtienen automáticamente de tbl_doc_plantillas.codigo_sugerido

// 3. Crear metodo para generar contenido automatico
protected function generarContenidoAutoContexto(string $tipo, array $cliente, array $contexto): array
{
    switch ($tipo) {
        case 'nuevo_documento_simple':
            return $this->construirContenidoNuevoDocumento($cliente, $contexto);
        // ... otros casos
    }
}

// 4. Crear metodo especifico del documento
protected function construirContenidoNuevoDocumento(array $cliente, array $contexto): array
{
    return [
        'titulo' => 'Titulo del Documento',
        'secciones' => [
            [
                'numero' => 1,
                'nombre' => 'Seccion 1',
                'contenido' => 'Contenido generado desde contexto...'
            ]
        ],
        'firmantes' => [
            'representante_legal' => [
                'nombre' => $contexto['representante_legal_nombre'],
                'cedula' => $contexto['representante_legal_cedula'],
                'cargo' => 'Representante Legal'
            ]
        ]
    ];
}

// 5. Crear metodo de vista previa
public function nuevoDocumentoSimple(int $idCliente, int $anio = null)
{
    // Similar a asignacionResponsable() pero para el nuevo documento
}
```

---

## LECCIONES APRENDIDAS (ERRORES COMUNES)

### 1. Collation de Base de Datos

**Problema:** Error `Illegal mix of collations` al usar LIKE o comparar strings entre tablas.

**Solucion:** La base de datos debe tener collation unificado `utf8mb4_general_ci`. Si hay errores de collation:

```bash
# Ejecutar script de unificacion
php app/SQL/ejecutar_unificar_collation.php
```

**Regla:** NUNCA agregar `COLLATE` manualmente en queries de CodeIgniter. Unificar la BD es la solucion permanente.

---

### 2. HTML en Exportacion PDF/Word (Negritas no se renderizan)

**Problema:** Los tags `<strong>` aparecen como texto literal en PDF/Word: `<strong>Nombre</strong>` en vez de **Nombre**.

**Causa:** Las funciones `convertirMarkdownAHtml()` y `convertirMarkdownAHtmlPdf()` en los templates escapan TODO el HTML con `htmlspecialchars()`.

**Solucion:** Los templates ya estan corregidos para preservar tags HTML existentes. Si creas contenido con HTML:

```php
// CORRECTO - El contenido puede tener HTML
$texto = "<strong>{$nombre}</strong> con cedula <strong>{$cedula}</strong>";

// Los templates PDF/Word preservaran los tags <strong>, <b>, <em>, <i>
```

**Archivos corregidos:**
- `app/Views/documentos_sst/pdf_template.php` - funcion `convertirMarkdownAHtmlPdf()`
- `app/Views/documentos_sst/word_template.php` - funcion `convertirMarkdownAHtml()`

---

### 3. Nombres de Campos en Tablas

**Tabla `tbl_cliente_contexto_sst` (contexto):**
```php
$contexto['representante_legal_nombre']  // Nombre del rep. legal
$contexto['representante_legal_cedula']  // Cedula del rep. legal
$contexto['id_consultor_responsable']    // ID del consultor asignado
$contexto['estandares_aplicables']       // 7, 21 o 60
$contexto['delegado_sst_nombre']         // Si aplica
$contexto['delegado_sst_cargo']          // Si aplica
```

**Tabla `tbl_consultor` (consultor):**
```php
$consultor['nombre_consultor']   // Nombre completo
$consultor['cedula_consultor']   // Documento de identidad
$consultor['numero_licencia']    // Licencia SST (incluye fecha, ej: "4241 de 19/08/2022")
$consultor['firma_consultor']    // Ruta imagen de firma
```

**ERROR COMUN:** Usar `licencia_sst` en vez de `numero_licencia`.

---

### 4. Manejo de Stored Procedures en CodeIgniter 4

**Problema:** Error `Call to undefined method Result::close()` al llamar SP.

**Solucion:** CodeIgniter 4 no tiene metodo `close()`. Usar este patron:

```php
protected function generarCodigo(int $idCliente): string
{
    $query = $this->db->query(
        "CALL sp_generar_codigo_documento(?, ?, ?, @codigo)",
        [$idCliente, self::CODIGO_TIPO, self::CODIGO_TEMA]
    );
    $query->getResult();

    // Liberar resultados del SP para evitar "commands out of sync"
    if (method_exists($query, 'freeResult')) {
        $query->freeResult();
    }
    while ($this->db->connID->next_result()) {
        $this->db->connID->store_result();
    }

    $result = $this->db->query("SELECT @codigo as codigo")->getRow();
    return $result->codigo ?? (self::CODIGO_TIPO . '-' . self::CODIGO_TEMA . '-001');
}
```

---

### 5. Titulo del Documento en Templates PDF/Word

**Problema:** El PDF/Word muestra "DOCUMENTO SST" en vez del titulo real.

**Causa:** El template usa `$contenido['titulo']` pero no todos los documentos lo incluyen en el JSON.

**Solucion:** Siempre usar fallback chain:

```php
// En pdf_template.php y word_template.php
<?= esc(strtoupper($contenido['titulo'] ?? $documento['titulo'] ?? 'DOCUMENTO SST')) ?>
```

**Regla:** Si el documento PATRON B no incluye 'titulo' en el JSON de contenido, el template usara `$documento['titulo']` de la tabla.

---

### 6. Busqueda de Consultor (Fallback Chain)

**Problema:** El documento no muestra datos del consultor.

**Solucion:** Buscar en multiples fuentes con fallback:

```php
// Obtener datos del consultor asignado (responsable SST)
$consultor = null;
$consultorModel = new ConsultantModel();

// 1. Primero: id_consultor_responsable del contexto
$idConsultor = $contexto['id_consultor_responsable'] ?? null;
if ($idConsultor) {
    $consultor = $consultorModel->find($idConsultor);
}

// 2. Fallback: buscar consultor asignado al cliente en tbl_consultor
if (!$consultor) {
    $consultor = $consultorModel->where('id_cliente', $idCliente)->first();
}

// 3. Fallback: id_consultor del cliente
if (!$consultor && !empty($cliente['id_consultor'])) {
    $consultor = $consultorModel->find($cliente['id_consultor']);
}
```

---

### 7. URLs de Navegacion (Boton Volver)

**Problema:** El boton "Volver" lleva a error 404.

**Solucion:** Usar URLs simples y probadas:

```php
// CORRECTO - URL simple
<a href="<?= base_url('documentacion/' . $documento['id_cliente']) ?>">
    Volver a Documentacion
</a>

// INCORRECTO - URL compleja que puede fallar
<a href="<?= base_url('documentacion/carpeta/' . $documento['id_carpeta']) ?>">
```

---

### 8. Vista Web vs Exportacion (Escapado de HTML)

**En la vista web (.php):** NO escapar contenido que ya tiene HTML:
```php
<!-- CORRECTO - Renderiza HTML -->
<?= $seccion['contenido'] ?>

<!-- INCORRECTO - Escapa los tags -->
<?= esc($seccion['contenido']) ?>
```

**En PDF/Word:** Los templates ya manejan esto automaticamente.

---

### 9. Testing de Exportaciones

**SIEMPRE probar AMBOS formatos despues de crear un documento:**

1. **PDF:** Verificar que negritas, tablas y formato se renderizan
2. **Word:** Verificar lo mismo (Word usa template diferente)
3. **Titulo:** Verificar que aparece el nombre correcto en el encabezado
4. **Firmas:** Verificar que la seccion de firmas tiene los datos correctos

---

### 10. Importacion de Modelos (Case Sensitivity)

**Problema:** Error `Class not found` por nombre incorrecto del modelo.

**Regla:** El nombre del `use` debe coincidir EXACTAMENTE con el archivo:

```php
// Si el archivo es ClienteContextoSstModel.php
use App\Models\ClienteContextoSstModel;  // CORRECTO

// NO usar:
use App\Models\ClienteContextoSSTModel;  // INCORRECTO (SST vs Sst)
```

---

### 11. Regeneracion de Documentos con Edicion Directa

**Problema:** El usuario edita datos en un formulario pero el documento regenerado sigue mostrando los datos antiguos.

**Causa:** El modal de regeneracion solo MOSTRABA los datos pero NO los editaba. El usuario creia que estaba cambiando datos, pero la fuente de datos (tbl_cliente_contexto_sst) no se actualizaba.

**Solucion:** Implementar edicion directa en el modal de regeneracion:

```php
// En el controlador regenerar(), ANTES de reconstruir contenido:

// 1. Recibir datos editados del formulario
$nuevoRepLegalNombre = $this->request->getPost('representante_legal_nombre');
$nuevoRepLegalCedula = $this->request->getPost('representante_legal_cedula');
$nuevoIdConsultor = $this->request->getPost('id_consultor_responsable');

// 2. Actualizar tbl_cliente_contexto_sst si hay cambios
$datosActualizar = [];
if ($nuevoRepLegalNombre && $nuevoRepLegalNombre !== ($contexto['representante_legal_nombre'] ?? '')) {
    $datosActualizar['representante_legal_nombre'] = $nuevoRepLegalNombre;
}
if (!empty($datosActualizar)) {
    $this->db->table('tbl_cliente_contexto_sst')
        ->where('id_cliente', $idCliente)
        ->update($datosActualizar);

    // 3. Recargar contexto actualizado
    $contexto = $contextoModel->getByCliente($idCliente);
}

// 4. AHORA construir contenido con datos frescos
$nuevoContenido = $this->construirContenido($cliente, $contexto, $consultor, $anio);
```

**Regla:** Siempre que el usuario pueda "actualizar" un documento, asegurar que:
1. El formulario tenga campos EDITABLES (no solo informativos)
2. El controlador ACTUALICE la fuente de datos antes de regenerar
3. Se RECARGUE el contexto despues de actualizar

---

### 12. Campos ENUM en MySQL - Fallo Silencioso

**Problema:** Al intentar actualizar un campo ENUM con un valor que no existe en la definicion, MySQL falla SILENCIOSAMENTE (retorna 0 filas afectadas) sin lanzar error.

**Ejemplo del error:**
```php
// El campo 'estado' tiene ENUM('vigente', 'obsoleto')
$pdo->exec("UPDATE tbl_doc_versiones_sst SET estado = 'historico' WHERE id_version = 9");
// Resultado: 0 filas afectadas, pero NO hay error
// El campo queda VACIO en vez de actualizarse
```

**Solucion:** SIEMPRE verificar que los valores ENUM incluyan todos los estados necesarios ANTES de usarlos:

```sql
-- Verificar estructura actual
SHOW COLUMNS FROM tbl_doc_versiones_sst WHERE Field = 'estado';

-- Si falta un valor, alterar la tabla
ALTER TABLE tbl_doc_versiones_sst
MODIFY COLUMN estado ENUM('vigente', 'obsoleto', 'historico', 'pendiente_firma')
NOT NULL DEFAULT 'vigente';
```

**Regla:** Cuando agregues nuevos estados a un flujo de trabajo:
1. Verificar que el ENUM de la columna los incluya
2. Si no existen, ejecutar ALTER TABLE antes de usar los nuevos valores
3. MySQL NO lanza error al intentar insertar valor invalido en ENUM - falla silenciosamente

---

### 13. Estados de Versiones de Documentos

**Flujo correcto de estados en `tbl_doc_versiones_sst`:**

| Estado | Descripcion | Cuando se usa |
|--------|-------------|---------------|
| `pendiente_firma` | Version recien creada/regenerada | Al crear nueva version, antes de firmar |
| `vigente` | Version activa actual | Despues de firmar o aprobar |
| `historico` | Version anterior reemplazada | Al crear nueva version, la anterior pasa a historico |
| `obsoleto` | Version descartada/invalida | Cuando se invalida manualmente |

**Codigo para regenerar documento con estados correctos:**

```php
// 1. Marcar version anterior como historico
$this->db->table('tbl_doc_versiones_sst')
    ->where('id_documento', $documento['id_documento'])
    ->where('estado', 'vigente')
    ->update(['estado' => 'historico']);

// 2. Crear nueva version en pendiente_firma
$this->db->table('tbl_doc_versiones_sst')->insert([
    'id_documento' => $documento['id_documento'],
    'version_texto' => $nuevaVersion,
    'estado' => 'pendiente_firma',  // NO 'vigente' directamente
    // ... otros campos
]);

// 3. Actualizar documento principal
$this->db->table('tbl_documentos_sst')
    ->where('id_documento', $documento['id_documento'])
    ->update([
        'estado' => 'pendiente_firma',
        'version' => $nuevaVersion
    ]);
```

**Regla:** Un documento regenerado NUNCA debe quedar en estado 'vigente' inmediatamente. Debe pasar por 'pendiente_firma' hasta que se firme.

---

### 14. Manejo de Estados Vacios en Vistas

**Problema:** La columna Estado en tablas de historial muestra valores vacios cuando el campo no tiene valor o tiene NULL.

**Solucion en el controlador:** Asignar valores por defecto al cargar versiones:

```php
// Al obtener versiones del documento
$versiones = $db->table('tbl_doc_versiones_sst')
    ->select('id_version, version_texto, estado, ...')
    ->where('id_documento', $idDocumento)
    ->orderBy('id_version', 'DESC')
    ->get()->getResultArray();

// Asignar estado por defecto a versiones que no lo tengan
foreach ($versiones as $idx => &$ver) {
    if (empty($ver['estado'])) {
        // La version mas reciente (primera en array) es vigente, las demas historicas
        $ver['estado'] = ($idx === 0) ? 'vigente' : 'historico';
    }
}
unset($ver);
```

**Solucion en la vista:** Usar operador null coalescing y match para mostrar valores legibles:

```php
<?php
$estadoVer = $ver['estado'] ?? 'historico';
$estadoVerBadge = match($estadoVer) {
    'vigente' => 'bg-success',
    'pendiente_firma' => 'bg-info',
    'historico' => 'bg-secondary',
    default => 'bg-secondary'
};
$estadoVerTexto = match($estadoVer) {
    'vigente' => 'Vigente',
    'pendiente_firma' => 'Pendiente firma',
    'historico' => 'Historico',
    default => ucfirst(str_replace('_', ' ', $estadoVer))
};
?>
<span class="badge <?= $estadoVerBadge ?>"><?= $estadoVerTexto ?></span>
```

**Regla:** NUNCA confiar en que un campo tendra valor. Siempre usar fallbacks tanto en controlador como en vista

---

### 15. Case Sensitivity en Linux (Produccion) vs Windows (Local)

**Problema:** Error `Class not found` solo en produccion pero funciona en local.

**Causa:** Windows es case-insensitive, Linux es case-sensitive. El archivo `ClienteContextoSstModel.php` funcionaba con import `ClienteContextoSSTModel` en Windows pero fallaba en Linux.

**Solucion:** El nombre del `use` debe coincidir EXACTAMENTE con el nombre del archivo:

```php
// Archivo: ClienteContextoSstModel.php

// CORRECTO
use App\Models\ClienteContextoSstModel;

// INCORRECTO - Falla en Linux
use App\Models\ClienteContextoSSTModel;  // SST vs Sst
```

**Regla:** SIEMPRE verificar que el case del import coincida con el nombre del archivo. Buscar con:
```bash
# Buscar archivos con nombre similar
ls -la app/Models/ | grep -i contexto
```

**Archivos afectados tipicamente:**
- Controladores que importan modelos
- Servicios que importan modelos
- Cualquier clase con acronimos (SST, SG, PDF, etc.)

---

### 16. Error de Doble COLLATE en Queries

**Problema:** Error SQL `syntax error near 'COLLATE ... COLLATE'`.

**Causa:** Poner COLLATE dos veces en la misma expresion:

```php
// INCORRECTO - Doble COLLATE
->where("campo COLLATE utf8mb4_general_ci LIKE 'valor%' COLLATE utf8mb4_general_ci")

// CORRECTO - Solo un COLLATE
->where("campo LIKE 'valor%' COLLATE utf8mb4_general_ci", null, false)
```

**Regla:** COLLATE solo va UNA vez en la expresion, idealmente despues del valor de comparacion.

---

### 17. Flujo Correcto de Documentos: Generar → Firmar → Aprobar

**Problema:** El flujo anterior era Generar → Aprobar → Firmar, lo cual no tenia sentido porque si el cliente no aprobaba al firmar, la version ya estaba creada.

**Solucion - Flujo correcto:**

```
1. Generar documento (secciones con IA o automatico)
2. Aprobar secciones (marcar cada seccion como lista)
3. Enviar a Firmas (el cliente revisa y firma)
4. Aprobacion automatica (al completarse todas las firmas, se crea la version)
```

**Implementacion:**

En `generar_con_ia.php`, cambiar el boton segun estado:
```php
<?php if ($estadoDoc === 'firmado'): ?>
    <!-- Documento firmado y aprobado -->
    <a href="firma/estado/<?= $idDocumento ?>">Ver Firmas</a>
<?php elseif ($estadoDoc === 'pendiente_firma'): ?>
    <!-- Esperando firmas -->
    <a href="firma/estado/<?= $idDocumento ?>">Estado Firmas</a>
<?php elseif ($todasSeccionesListas && $idDocumento): ?>
    <!-- Listo para firmas -->
    <a href="firma/solicitar/<?= $idDocumento ?>">Enviar a Firmas</a>
<?php endif; ?>
```

En `FirmaElectronicaController.php`, al completar todas las firmas:
```php
if ($this->firmaModel->firmasCompletas($idDocumento)) {
    // Cambiar estado a firmado
    $this->db->table('tbl_documentos_sst')
        ->where('id_documento', $idDocumento)
        ->update(['estado' => 'firmado']);

    // Crear version automaticamente
    $this->aprobarDocumentoAutomatico($idDocumento);
}
```

**Regla:** El cliente SIEMPRE debe revisar y firmar ANTES de que el documento quede oficialmente aprobado.

---

### 18. Tamano de Imagenes de Firma en PDF

**Problema:** Las firmas se ven muy pequenas en el PDF.

**Solucion:** Aumentar el tamano maximo de las imagenes de firma:

```php
// ANTES (muy pequeno)
style="max-height: 40px; max-width: 120px;"

// DESPUES (+40%)
style="max-height: 56px; max-width: 168px;"
```

**Archivos a modificar:**
- `app/Views/documentos_sst/pdf_template.php`
- `app/Views/documentos_sst/programa_capacitacion.php`
- `app/Views/documentos_sst/asignacion_responsable.php`

**Tamaños recomendados:**
| Firmantes | Altura | Ancho |
|-----------|--------|-------|
| 2 firmantes | 56px | 168px |
| 3 firmantes | 49px | 140px |

---

### 19. Calidad de Firmas Electronicas (Canvas)

**Problema:** La firma dibujada por el cliente se ve pixelada o de baja calidad en el PDF.

**Causas:**
1. El canvas no usa `devicePixelRatio` para pantallas de alta densidad (retina)
2. La firma se exporta con todo el espacio vacio alrededor
3. El trazo es muy delgado

**Solucion en `firmar.php`:**

```javascript
// 1. Usar alta resolucion
let dpr = window.devicePixelRatio || 1;
canvas.width = rect.width * dpr;
canvas.height = 200 * dpr;
ctx.scale(dpr, dpr);

// 2. Trazo mas grueso
ctx.lineWidth = 3;  // Era 2

// 3. Al exportar, recortar y optimizar
function exportarFirmaOptimizada() {
    // Encontrar bounding box del dibujo
    // Recortar solo el area con contenido
    // Escalar a tamaño fijo (150px altura)
    return tempCanvas.toDataURL('image/png');
}

// 4. Para imagenes subidas, redimensionar
function optimizarImagenFirma(dataUrl, callback) {
    // Redimensionar a max 150px altura, 400px ancho
    // Mantener proporcion
}
```

**Regla:** Las firmas nuevas deben procesarse con alta resolucion. Las firmas existentes de baja calidad requieren que el cliente vuelva a firmar.

---

### 20. Navegacion - Boton Volver

**Problema:** El boton "Volver" lleva a una pagina incorrecta o genera error.

**Solucion:** Definir claramente la jerarquia de navegacion:

```php
// Desde responsables-sst/{id} -> volver a contexto/{id}
<a href="<?= base_url('contexto/' . $cliente['id_cliente']) ?>">Volver</a>

// Desde documento -> volver a documentacion/{id}
<a href="<?= base_url('documentacion/' . $documento['id_cliente']) ?>">Volver</a>
```

**Jerarquia recomendada:**
```
contexto/{id} (ficha del cliente)
├── responsables-sst/{id}
├── documentacion/{id}
│   ├── documentos/generar/programa_capacitacion/{id}
│   └── documentos-sst/{id}/programa-capacitacion/{anio}
```

**Regla:** El boton "Volver" debe llevar al nivel inmediatamente superior en la jerarquia, NO a una pagina aleatoria.

---

### 21. Botones de Acciones en Vista del Documento

**Problema:** Confusion sobre cuando mostrar cada boton (Editar, Firmar, Aprobar, Ver PDF).

**Matriz de botones segun estado:**

| Estado | Editar | Enviar a Firmas | Estado Firmas | Ver PDF |
|--------|--------|-----------------|---------------|---------|
| borrador | Si | No | No | Si (preview) |
| en_revision | Si | No | No | Si |
| secciones_listas | No | **Si** | No | Si |
| pendiente_firma | No | No | **Si** | Si |
| firmado | No | No | Ver Firmas | Si (oficial) |
| aprobado | No | No | - | Si (oficial) |

**Codigo en vista:**
```php
<?php
$estado = $documento['estado'] ?? 'borrador';
$seccionesListas = $todasSeccionesListas ?? false;
?>

<?php if ($estado === 'firmado'): ?>
    <span class="badge bg-success">Firmado y Aprobado</span>
    <a href="firma/estado/<?= $id ?>">Ver Firmas</a>
<?php elseif ($estado === 'pendiente_firma'): ?>
    <span class="badge bg-warning">Pendiente Firma</span>
    <a href="firma/estado/<?= $id ?>">Estado Firmas</a>
<?php elseif ($seccionesListas): ?>
    <a href="firma/solicitar/<?= $id ?>" class="btn btn-success">Enviar a Firmas</a>
<?php else: ?>
    <span class="badge bg-secondary">En edicion</span>
<?php endif; ?>
```

---

### 22. Configurar Acceso en carpeta.php para Nuevos Documentos

**⚠️ ACTUALIZADO: Nueva arquitectura de vistas por componentes**

**Ver:** `docs/ARQUITECTURA_VISTAS_COMPONENTES.md` para documentación completa.

**Problema:** Creaste el controlador, la vista y las rutas, pero al entrar a la carpeta no aparece el botón para crear el documento.

**Causa:** El sistema usa `determinarTipo()` para detectar el tipo de carpeta y cargar la vista correspondiente de `_tipos/`.

**Solución - 3 pasos con nueva arquitectura:**

**Paso 1: Crear vista del tipo en `_tipos/`**

```bash
touch app/Views/documentacion/_tipos/mi_nuevo_tipo.php
```

```php
<?php
/**
 * Vista de Tipo: Mi Nuevo Tipo
 * Código: X.X.X
 */
?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h4><?= esc($carpeta['nombre']) ?></h4>
        <!-- Tu botón aquí -->
        <form action="<?= base_url('documentos-sst/' . $cliente['id_cliente'] . '/crear-mi-documento') ?>" method="post">
            <button type="submit" class="btn btn-success">Generar Documento</button>
        </form>
    </div>
</div>

<?= view('documentacion/_components/panel_fases', ['fasesInfo' => $fasesInfo ?? null]) ?>
<?= view('documentacion/_components/tabla_documentos', ['documentosSSTAprobados' => $documentosSSTAprobados ?? []]) ?>
```

**Paso 2: Agregar detección en DocumentacionController.php**

```php
// En determinarTipo() - NO en determinarTipoCarpetaFases (OBSOLETO)
protected function determinarTipo(array $carpeta): ?string
{
    $codigo = strtolower($carpeta['codigo'] ?? '');

    // AGREGAR: Detectar tu nueva carpeta por código
    if ($codigo === 'x.x.x') return 'mi_nuevo_tipo';

    return null;
}
```

**Paso 3: Agregar tipo al array de documentos SST (si requiere documentos)**

```php
// En carpeta() de DocumentacionController.php
$tiposConDocumentos = ['capacitacion_sst', 'responsables_sst', 'mi_nuevo_tipo'];
```

**⚠️ IMPORTANTE:** Ya NO se edita `carpeta.php` directamente. Los botones van en la vista de tipo `_tipos/mi_nuevo_tipo.php`.

**Regla:** Sin estos 3 pasos, el documento NO aparecerá en la interfaz aunque el controlador y rutas existan.

---

### 23. Múltiples Documentos en Una Carpeta (Patrón Dropdown)

**⚠️ ACTUALIZADO: Nueva arquitectura de vistas por componentes**

**Problema:** Una carpeta contiene varios documentos relacionados (ej: 1.1.2 tiene 4 documentos de responsabilidades).

**Solución con nueva arquitectura:** El dropdown va en la vista de tipo `_tipos/responsabilidades_sgsst.php`, NO en carpeta.php.

**Paso 1: Crear vista de tipo con dropdown**

```php
<?php
// app/Views/documentacion/_tipos/responsabilidades_sgsst.php

/**
 * Vista de Tipo: Responsabilidades SG-SST
 * Código: 1.1.2
 * Contiene 4 documentos de responsabilidades
 */

$nivelEstandares = $contextoCliente['estandares_aplicables'] ?? 60;
$docsExistentesTipos = [];
if (!empty($documentosSSTAprobados)) {
    foreach ($documentosSSTAprobados as $d) {
        if ($d['anio'] == date('Y')) {
            $docsExistentesTipos[$d['tipo_documento']] = true;
        }
    }
}
?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h4><?= esc($carpeta['nombre']) ?></h4>
            </div>
            <div class="col-md-4 text-end">
                <div class="dropdown">
                    <button class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-plus-lg me-1"></i>Nuevo Documento
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <?php if (!isset($docsExistentesTipos['responsabilidades_rep_legal_sgsst'])): ?>
                        <li>
                            <form action="<?= base_url('documentos-sst/' . $cliente['id_cliente'] . '/crear-resp-rep-legal') ?>" method="post">
                                <button type="submit" class="dropdown-item">Resp. Representante Legal</button>
                            </form>
                        </li>
                        <?php endif; ?>
                        <!-- Más opciones... -->
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?= view('documentacion/_components/tabla_documentos', ['documentosSSTAprobados' => $documentosSSTAprobados ?? []]) ?>
```

**Paso 2: En DocumentacionController - pasar contextoCliente y obtener todos los tipos**

```php
// En carpeta() - configurar tipos de documentos
if ($tipo === 'responsabilidades_sgsst') {
    $queryDocs->whereIn('tipo_documento', [
        'responsabilidades_rep_legal_sgsst',
        'responsabilidades_responsable_sgsst',
        'responsabilidades_trabajadores_sgsst',
        'responsabilidades_vigia_sgsst'
    ]);

    // Pasar contexto para filtros por estándares
    $data['contextoCliente'] = $db->table('tbl_cliente_contexto_sst')
        ->where('id_cliente', $cliente['id_cliente'])
        ->get()->getRowArray();
}
```

**Paso 3: Configurar mapa de URLs en el componente `_components/tabla_documentos.php`**

```php
<?php
// En _components/tabla_documentos.php
$mapaRutas = [
    'responsabilidades_rep_legal_sgsst' => 'responsabilidades-rep-legal/',
    'responsabilidades_responsable_sgsst' => 'responsabilidades-responsable-sst/',
    // ... agregar todos los tipos
];
?>
```

**Regla:** Con la nueva arquitectura:
1. Los dropdowns van en `_tipos/nombre_tipo.php`, NO en carpeta.php
2. La lógica de filtrado por estándares va en la vista de tipo
3. Los componentes reutilizables van en `_components/`
4. carpeta.php es solo el layout base

---

### 24. Checklist Ampliado para Documentos con Acceso en Carpeta

**⚠️ ACTUALIZADO: Nueva arquitectura de vistas por componentes**

```
[ ] BLOQUE CONSULTOR
    [ ] Controlador creado/actualizado en DocumentosSSTController.php
    [ ] Vista previa creada en app/Views/documentos_sst/
    [ ] Rutas agregadas en Routes.php

[ ] BLOQUE ACCESO EN CARPETA (NUEVA ARQUITECTURA)
    [ ] Crear vista de tipo: app/Views/documentacion/_tipos/mi_tipo.php
    [ ] determinarTipo() en DocumentacionController - agregar detección del código
    [ ] Agregar tipo al array $tiposConDocumentos en carpeta()
    [ ] Si múltiples docs: crear dropdown EN la vista de tipo (no en carpeta.php)
    [ ] Si filtro por estándares: pasar contextoCliente desde controlador
    [ ] Configurar mapaRutas en _components/tabla_documentos.php

[ ] BLOQUE CLIENTE
    [ ] Mapeo en mapearPlantillaATipoDocumento()

[ ] BLOQUE BASE DE DATOS
    [ ] Script de migracion creado
    [ ] Ejecutado en LOCAL
    [ ] Ejecutado en PRODUCCION
```

---

### 25. Publicar Documento en ReportList (PDF)

**Funcionalidad:** El botón "Publicar en Reportes" (icono nube con flecha) genera un PDF del documento y lo publica en `tbl_reporte` para que sea consultable desde `/reportList`.

**Cómo funciona:**

1. El botón en `carpeta.php` llama a: `documentos-sst/publicar-pdf/{id_documento}`
2. El controlador `DocumentosSSTController::publicarPDF()`:
   - Genera PDF usando la vista `documentos_sst/pdf_template`
   - Guarda el archivo en `uploads/{nit}/`
   - Crea/actualiza registro en `tbl_reporte`
   - Asocia al `detail_report` "Documento SG-SST"

**Código del botón en carpeta.php:**

```php
<a href="<?= base_url('documentos-sst/publicar-pdf/' . $docSST['id_documento']) ?>"
   class="btn btn-outline-dark" title="Publicar en Reportes"
   onclick="return confirm('¿Publicar este documento en Reportes?')">
    <i class="bi bi-cloud-upload"></i>
</a>
```

**Ruta requerida en Routes.php:**

```php
$routes->get('/documentos-sst/publicar-pdf/(:num)', 'DocumentosSSTController::publicarPDF/$1');
```

**Posibles errores:**

| Error | Causa | Solución |
|-------|-------|----------|
| "Documento no encontrado" | ID incorrecto | Verificar que `$docSST['id_documento']` existe |
| PDF vacío o mal formado | Vista faltante | Crear `documentos_sst/pdf_template.php` |
| No aparece en reportList | Falta detail_report | Verificar que existe "Documento SG-SST" en `detail_report` |

**IMPORTANTE sobre mapaRutas:**

Cuando configures URLs en `carpeta.php`, usa el parámetro correcto según la ruta:

```php
// Si la ruta usa (:num) para ANIO:
$routes->get('/documentos-sst/(:num)/mi-documento/(:num)', 'Controller::ver/$1/$2');
//                         idCliente              anio

// Entonces en mapaRutas usa $docSST['anio']:
'mi_tipo_documento' => 'mi-documento/' . $docSST['anio'],

// NO uses id_documento si la ruta espera anio - causará "Documento no encontrado"
```

---

### 26. HTML Sin Renderizar en PDF/Word (Tags Visibles como Texto)

**Problema:** Al exportar a PDF o Word, el contenido muestra tags HTML literales como `<p>`, `<ol>`, `<li>` en lugar de renderizar las listas y párrafos correctamente.

**Causa:** Las funciones `convertirMarkdownAHtmlPdf()` y `convertirMarkdownAHtml()` en los templates usan `htmlspecialchars()` para escapar HTML, pero esto también escapa el HTML válido que viene del controlador.

**Ejemplo del error:**
```
3. RESPONSABILIDADES DEL REPRESENTANTE LEGAL
<p>El Representante Legal tiene las siguientes responsabilidades:</p>
<ol style="line-height: 1.8;"><li style="margin-bottom: 8px;">Definir la política...</li></ol>
```

**Solución:** Agregar detección temprana de HTML existente en los templates.

**En `pdf_template.php` y `word_template.php`:**

```php
function convertirMarkdownAHtmlPdf($texto) {
    if (empty($texto)) return '';

    // Si el contenido ya tiene tags HTML de estructura, devolverlo directamente
    // El contenido ya viene formateado con estilos desde el controlador
    if (preg_match('/<(p|ol|ul|li|div|table|br)\b[^>]*>/i', $texto)) {
        return $texto;
    }

    // ... resto del código para procesar Markdown ...
}
```

**¿Por qué ocurre?**

El controlador genera HTML con `formatearResponsabilidades()`:
```php
protected function formatearResponsabilidades(array $responsabilidades, string $intro = ''): string
{
    $html = '<p>' . $intro . '</p>';
    $html .= '<ol style="line-height: 1.8;">';
    foreach ($responsabilidades as $resp) {
        $html .= '<li style="margin-bottom: 8px;">' . $resp . '</li>';
    }
    $html .= '</ol>';
    return $html;
}
```

Este HTML válido luego pasa por `htmlspecialchars()` que lo escapa:
- `<p>` → `&lt;p&gt;` (se muestra como texto literal)

**Regla:** Si el contenido ya tiene tags HTML estructurados, NO procesarlo con `htmlspecialchars()`.

---

### 27. Logo con Fondo Negro en Word (Transparencia PNG)

**Problema:** Al exportar a Word, el logo PNG con transparencia aparece con fondo negro.

**Causa:** MS Word no maneja bien la transparencia PNG en imágenes base64.

**Solución:** Agregar fondo blanco explícito al contenedor e imagen.

**En `word_template.php`:**

```php
<!-- ANTES (fondo negro) -->
<td width="80" rowspan="2" align="center" valign="middle" style="border:1px solid #333; padding:5px;">
    <img src="<?= $logoBase64 ?>" width="70" height="45" alt="Logo">

<!-- DESPUÉS (fondo blanco) -->
<td width="80" rowspan="2" align="center" valign="middle" style="border:1px solid #333; padding:5px; background-color: #ffffff;">
    <img src="<?= $logoBase64 ?>" width="70" height="45" alt="Logo" style="background-color: #ffffff;">
```

**Regla:** Siempre agregar `background-color: #ffffff` a contenedores de imágenes PNG en exports Word.

---

### 28. Formato de Firmas Incorrecto en PDF/Word (Electrónica vs Física)

**Problema:** Algunos documentos SST exportan con el formato de firma incorrecto:
- "Responsabilidades del Responsable del SG-SST" mostraba 3 firmantes (Consultor + Vigía + Rep Legal) cuando solo debe llevar 1 (Consultor)
- "Responsabilidades de Trabajadores y Contratistas" mostraba firmas electrónicas (2-3 personas) cuando debe mostrar tabla de firmas físicas (múltiples trabajadores)

**Causa:** Los templates PDF/Word usaban el mismo bloque de firmas para todos los documentos sin detectar el tipo específico.

**Solución:** Detectar el tipo de documento y mostrar el bloque de firmas apropiado.

**Tipos de firma:**

| Tipo | Documentos que lo usan | Formato |
|------|------------------------|---------|
| `solo_firma_consultor` | Responsabilidades Responsable SG-SST | 1 firmante: Consultor/Responsable |
| `tipo_firma = 'fisica'` | Responsabilidades Trabajadores | Tabla con múltiples filas para trabajadores |
| (estándar) | Todos los demás | 2-3 firmantes según estándares |

**En `pdf_template.php` y `word_template.php`:**

```php
<?php
// Detectar tipo de documento
$tipoDoc = $documento['tipo_documento'] ?? '';

// Documento con firma física (tabla para múltiples trabajadores)
$esFirmaFisica = !empty($contenido['tipo_firma']) && $contenido['tipo_firma'] === 'fisica'
    || $tipoDoc === 'responsabilidades_trabajadores_sgsst';

// Documento con solo firma del consultor
$soloFirmaConsultor = !empty($contenido['solo_firma_consultor'])
    || $tipoDoc === 'responsabilidades_responsable_sgsst';
?>

<?php if ($esFirmaFisica): ?>
    <!-- Tabla de firmas físicas para trabajadores -->
    <table>
        <tr>
            <th>No.</th><th>Fecha</th><th>Nombre Completo</th>
            <th>Cédula</th><th>Cargo/Área</th><th>Firma</th>
        </tr>
        <?php for ($i = 1; $i <= ($contenido['filas_firma'] ?? 15); $i++): ?>
        <tr>
            <td><?= $i ?></td>
            <td></td><td></td><td></td><td></td><td></td>
        </tr>
        <?php endfor; ?>
    </table>
<?php elseif ($soloFirmaConsultor): ?>
    <!-- Solo firma del consultor -->
    <table>
        <tr><th>RESPONSABLE DEL SG-SST</th></tr>
        <tr><td>Nombre: <?= $consultorNombre ?></td></tr>
        <tr><td>Documento: <?= $consultorCedula ?></td></tr>
        <tr><td>Firma: _______________</td></tr>
    </table>
<?php else: ?>
    <!-- Firmas estándar (2-3 firmantes) -->
    ...
<?php endif; ?>
```

**En el controlador (PzresponsabilidadesTrabajadoresController.php):**

```php
// Indicar tipo de firma física
'tipo_firma' => 'fisica',
'filas_firma' => 15  // Número de filas para trabajadores
```

**En el controlador (PzresponsabilidadesResponsableSstController.php):**

```php
// Indicar solo firma del consultor
'solo_firma_consultor' => true
```

**Regla:**
1. Siempre agregar `tipo_documento` al guardar en `tbl_documentos_sst` para detección por tipo
2. Para documentos con firmantes especiales, usar flags en `$contenido`
3. Los templates deben detectar ambos: flag explícito O tipo_documento (backwards compatibility)

---

### 29. Documento con Firma Única (Solo Consultor)

**Cuándo aplica:** Documentos donde el consultor es el único responsable, no requiere aprobación de terceros.

**Ejemplo:** "Responsabilidades del Responsable del SG-SST" - el consultor firma aceptando sus propias responsabilidades.

**Implementación:**

1. En el controlador agregar flag:
```php
$contenido = [
    'titulo' => '...',
    'solo_firma_consultor' => true,  // <-- Flag para firma única
    'secciones' => [...]
];
```

2. Los templates detectan y muestran:
- Título: "FIRMA DE ACEPTACIÓN" (no "FIRMAS DE APROBACIÓN")
- Una sola columna centrada con datos del consultor
- Cargo mostrado como "Consultor SST / Responsable del SG-SST"

---

### 30. Adjuntar Documento Firmado Escaneado (Firmas Físicas)

**Caso de uso:** Documentos como "Responsabilidades de Trabajadores y Contratistas" requieren que múltiples personas firmen físicamente en papel. Después de la inducción, el documento firmado se escanea y se debe publicar en reportList.

**Flujo:**
1. Generar documento con tabla de firmas vacías (PDF/Word)
2. Imprimir y hacer firmar a los trabajadores durante la inducción
3. Escanear el documento firmado (PDF o imagen)
4. Subir el escaneado desde la carpeta del documento
5. El archivo queda publicado en reportList y visible en la carpeta

**Implementación en carpeta.php:**

```php
<?php if ($tipoDoc === 'responsabilidades_trabajadores_sgsst'): ?>
<!-- Documento de firma física: botón para adjuntar escaneado -->
<button type="button" class="btn btn-outline-info" title="Adjuntar documento firmado (escaneado)"
   data-bs-toggle="modal" data-bs-target="#modalAdjuntarFirmado"
   data-id-documento="<?= $docSST['id_documento'] ?>"
   data-titulo="<?= esc($docSST['titulo']) ?>">
    <i class="bi bi-paperclip"></i>
</button>
<?php else: ?>
<!-- Documento estándar: publicar PDF generado -->
<a href="<?= base_url('documentos-sst/publicar-pdf/' . $docSST['id_documento']) ?>" ...>
    <i class="bi bi-cloud-upload"></i>
</a>
<?php endif; ?>
```

**Controlador (DocumentosSSTController::adjuntarFirmado):**

```php
public function adjuntarFirmado()
{
    $idDocumento = $this->request->getPost('id_documento');
    $archivo = $this->request->getFile('archivo_firmado');

    // Validar archivo (PDF, JPG, PNG - máx 10MB)
    // Guardar en uploads/{nit}/firmado_{tipo}_{fecha}.{ext}
    // Insertar/actualizar en tbl_reporte
    // Actualizar tbl_documentos_sst con enlace al archivo
}
```

**Ruta requerida:**

```php
$routes->post('/documentos-sst/adjuntar-firmado', 'DocumentosSSTController::adjuntarFirmado');
```

**Regla:** Usar el botón de paperclip (adjuntar) para documentos de firma física, y el botón de cloud-upload (publicar) para documentos de firma electrónica.

---

## RESUMEN RAPIDO DE ERRORES COMUNES

| Error | Causa | Solucion Rapida |
|-------|-------|-----------------|
| Class not found en produccion | Case sensitivity | Verificar nombre exacto del archivo |
| Syntax error COLLATE | Doble COLLATE | Usar solo un COLLATE |
| Firma muy pequeña | max-height bajo | Aumentar a 56px/168px |
| Firma pixelada | Sin devicePixelRatio | Agregar dpr al canvas |
| Boton volver error | URL incorrecta | Usar base_url + id_cliente |
| Aprobado sin firmas | Flujo incorrecto | Firmar ANTES de aprobar |
| **Botón no aparece en carpeta** | **Falta vista en _tipos/ o detección en determinarTipo()** | **Crear _tipos/mi_tipo.php + agregar código en determinarTipo()** |
| **Dropdown vacío** | **Falta whereIn para múltiples tipos** | **Agregar todos los tipo_documento al whereIn** |
| **Documento Vigía aparece siempre** | **Falta filtro por estándares** | **Pasar contextoCliente y verificar nivelEstandares <= 7** |
| **HTML visible en PDF/Word** | **htmlspecialchars escapa HTML válido** | **Detectar HTML existente y retornar sin procesar** |
| **Logo fondo negro en Word** | **PNG transparencia no soportada** | **Agregar background-color: #ffffff al contenedor** |
| **PDF/Word con firmas incorrectas** | **Template no detecta tipo de documento** | **Agregar detección $esFirmaFisica y $soloFirmaConsultor** |
| **Trabajadores con firma electrónica** | **Falta tipo_firma = 'fisica'** | **Agregar flag en controlador y bloque en templates** |
| **Rep Legal muestra Vigía en vez de Delegado** | **Condición solo verifica estándares, no requiere_delegado** | **Usar $esDelegado en lugar de solo $estandares >= 21** |
| **Vista no carga (tipo nuevo)** | **Falta archivo en _tipos/** | **Crear app/Views/documentacion/_tipos/mi_tipo.php** |

---

## 31. Responsabilidades Rep Legal: Vigía vs Delegado SST

**Problema:** El documento "Responsabilidades del Representante Legal" mostraba "Vigía SST" en el texto y las firmas, incluso cuando el cliente tiene `requiere_delegado_sst = true` configurado.

**Causa:** En `PzresponsabilidadesRepLegalController.php`, la condición para agregar responsabilidades solo verificaba `$estandares >= 21`, pero no consideraba `$requiereDelegado`:

```php
// ANTES (incorrecto):
if ($estandares >= 21) {
    $responsabilidadesRepLegal[] = 'Garantizar el funcionamiento del COPASST...';
    $responsabilidadesRepLegal[] = 'Garantizar el funcionamiento del Comite de Convivencia...';
} else {
    $responsabilidadesRepLegal[] = 'Garantizar el funcionamiento del Vigia de SST.'; // ← Siempre Vigía para < 21
}
```

**Solución:** Usar la variable `$esDelegado` que ya considera ambas condiciones:

```php
// DESPUÉS (correcto):
if ($estandares >= 21) {
    // 21 o 60 estándares: COPASST y Comité de Convivencia
    $responsabilidadesRepLegal[] = 'Garantizar el funcionamiento del COPASST...';
    $responsabilidadesRepLegal[] = 'Garantizar el funcionamiento del Comite de Convivencia...';
} elseif ($esDelegado) {
    // Menos de 21 estándares pero con Delegado SST configurado
    $responsabilidadesRepLegal[] = 'Garantizar el funcionamiento del Delegado de SST.';
} else {
    // 7 estándares sin Delegado: Vigía SST
    $responsabilidadesRepLegal[] = 'Garantizar el funcionamiento del Vigia de SST.';
}
```

**Variable clave:** `$esDelegado = $requiereDelegado || $estandares >= 21;` (línea 233)

**Archivo modificado:** `app/Controllers/PzresponsabilidadesRepLegalController.php` líneas 255-265

**Regenerar documento:** Después de aplicar el fix, el usuario debe usar el botón "Actualizar Datos" en la vista del documento para regenerar el contenido con la lógica corregida.

---

### 32. QRCode Library - outputType Requiere Strings (No Constantes)

**Problema:** Error `TypeError: Cannot assign int to property chillerlan\QRCode\QROptions::$outputType of type string` al generar códigos QR.

**Causa:** La librería `chillerlan/php-qrcode` actualizó su API y ahora el parámetro `outputType` requiere un **string** en lugar de constantes enteras.

**Código anterior (fallaba):**
```php
$options = new \chillerlan\QRCode\QROptions([
    'outputType' => \chillerlan\QRCode\Output\QROutputInterface::GDIMAGE_PNG,  // ❌ Constante entera
    'eccLevel' => \chillerlan\QRCode\Common\EccLevel::L,  // ❌ Constante entera
]);
```

**Código corregido:**
```php
$options = new \chillerlan\QRCode\QROptions([
    'outputType' => 'png',       // ✅ String
    'eccLevel' => 'L',           // ✅ String
    'scale' => 5,
    'outputBase64' => true,
]);
$qrcode = new \chillerlan\QRCode\QRCode($options);
return $qrcode->render($url);
```

**Archivo modificado:** `app/Controllers/FirmaElectronicaController.php` método `generarQR()`

**Regla:** Al usar `chillerlan/php-qrcode`, siempre usar **strings** para `outputType` y `eccLevel`:
- `'outputType' => 'png'` (no `QROutputInterface::GDIMAGE_PNG`)
- `'eccLevel' => 'L'` (no `EccLevel::L`)

---

### 33. Código de Verificación - Consistencia en Ordenamiento de Tokens

**Problema:** El código de verificación mostrado en el PDF era diferente al generado al momento de verificar, causando "Verificación No Válida".

**Causa:** El código de verificación se genera combinando los tokens de todas las solicitudes firmadas y aplicando un hash SHA-256. Si el orden de las solicitudes cambia entre generación y verificación, el hash será diferente.

**Ejemplo del problema:**
```
- Al firmar: tokens ordenados por orden_firma → hash = "42852F2EDDF5"
- Al verificar: tokens ordenados por created_at → hash = "7FFB7D000471"
```

**Solución:** Ordenar SIEMPRE por `id_solicitud ASC` en todos los lugares donde se genera el código.

**En `DocFirmaModel.php` (generarCodigoVerificacion):**
```php
public function generarCodigoVerificacion(int $idDocumento): string
{
    $solicitudes = $this->where('id_documento', $idDocumento)
                       ->where('estado', 'firmado')
                       ->orderBy('id_solicitud', 'ASC')  // ← CRÍTICO: ordenamiento consistente
                       ->findAll();

    if (empty($solicitudes)) {
        return '';
    }

    $tokens = array_column($solicitudes, 'token');
    $hash = hash('sha256', implode('|', $tokens) . '|' . $idDocumento);
    return strtoupper(substr($hash, 0, 12));
}
```

**En la vista `estado.php` (verificación):**
```php
// Ordenar firmados por id_solicitud para consistencia con el código de verificación
$firmados = array_filter($solicitudes, fn($s) => $s['estado'] === 'firmado');
usort($firmados, fn($a, $b) => $a['id_solicitud'] <=> $b['id_solicitud']);
$tokens = array_column($firmados, 'token');
$hash = hash('sha256', implode('|', $tokens) . '|' . $idDocumento);
$codigoVerificacion = strtoupper(substr($hash, 0, 12));
```

**Archivos modificados:**
- `app/Models/DocFirmaModel.php` línea 349
- `app/Views/documentos_sst/firma/estado.php` líneas 158-165

**Regla:** NUNCA usar `orden_firma` o `created_at` para ordenar tokens de verificación. Usar SIEMPRE `id_solicitud ASC` que es inmutable.

---

### 34. Bloque de Firmas para Responsabilidades Rep Legal (Sin Consultor)

**Problema:** El documento "Responsabilidades del Representante Legal del SG-SST" mostraba la firma del consultor como "Elaboró" en PDF/Word, pero en la vista web no aparecía.

**Causa:** Este documento específico solo requiere dos firmantes:
1. **Representante Legal** - quien acepta sus responsabilidades
2. **Vigía SST / Delegado SST** - quien valida

El consultor NO debe aparecer como firmante en este documento.

**Solución:** Agregar detección específica para este tipo de documento en los templates.

**En `pdf_template.php` y `word_template.php`:**

```php
<?php
// Detección de tipo de documento
$tipoDoc = $documento['tipo_documento'] ?? '';

// Documento de Responsabilidades Rep Legal: solo Rep. Legal + Vigía/Delegado (sin consultor)
$esDocResponsabilidadesRepLegal = $tipoDoc === 'responsabilidades_rep_legal_sgsst';
$tieneSegundoFirmante = !empty($contenido['tiene_segundo_firmante'])
                        || !empty($contenido['segundo_firmante']['nombre']);

// Condición para usar el bloque de 2 firmantes sin consultor
$firmasRepLegalYSegundo = $esDocResponsabilidadesRepLegal
                          && $tieneSegundoFirmante
                          && !$soloFirmaRepLegal;
?>

<?php if ($firmasRepLegalYSegundo): ?>
    <!-- Bloque especial: Rep. Legal + Vigía/Delegado (sin Consultor) -->
    <table width="100%" cellspacing="0" cellpadding="8" style="margin-top:30px; border-collapse: collapse;">
        <tr>
            <th width="50%" style="border:1px solid #333; background:#f5f5f5;">REPRESENTANTE LEGAL</th>
            <th width="50%" style="border:1px solid #333; background:#f5f5f5;">
                <?= ($estandares >= 21) ? 'VIGÍA SST' : 'DELEGADO SST' ?>
            </th>
        </tr>
        <tr>
            <td style="border:1px solid #333; height:60px; text-align:center;">
                <!-- Firma Rep. Legal -->
                <?php if (!empty($firmasMap['representante_legal'])): ?>
                    <img src="<?= $firmasMap['representante_legal']['firma_imagen'] ?>"
                         style="max-height:50px; max-width:150px;">
                <?php endif; ?>
            </td>
            <td style="border:1px solid #333; height:60px; text-align:center;">
                <!-- Firma Vigía/Delegado -->
                <?php if (!empty($firmasMap['delegado_sst'])): ?>
                    <img src="<?= $firmasMap['delegado_sst']['firma_imagen'] ?>"
                         style="max-height:50px; max-width:150px;">
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td style="border:1px solid #333; padding:8px;">
                <strong><?= esc($repLegalNombre) ?></strong><br>
                C.C. <?= esc($repLegalCedula) ?><br>
                Representante Legal
            </td>
            <td style="border:1px solid #333; padding:8px;">
                <strong><?= esc($segundoFirmante['nombre'] ?? '') ?></strong><br>
                C.C. <?= esc($segundoFirmante['cedula'] ?? '') ?><br>
                <?= esc($segundoFirmante['cargo'] ?? 'Vigía SST') ?>
            </td>
        </tr>
    </table>
<?php elseif ($esFirmaFisica): ?>
    <!-- Firma física... -->
<?php elseif ($soloFirmaConsultor): ?>
    <!-- Solo consultor... -->
<?php else: ?>
    <!-- Firmas estándar (2-3 firmantes)... -->
<?php endif; ?>
```

**En el controlador (PzresponsabilidadesRepLegalController.php):**

```php
// Agregar datos del segundo firmante (Vigía o Delegado) al contenido
$contenido['tiene_segundo_firmante'] = true;
$contenido['segundo_firmante'] = [
    'nombre' => $segundoFirmante['nombre'] ?? '',
    'cedula' => $segundoFirmante['cedula'] ?? '',
    'cargo' => $esDelegado ? 'Delegado SST' : 'Vigía SST'
];
```

**Archivos modificados:**
- `app/Views/documentos_sst/pdf_template.php` líneas 441-450, 596-653
- `app/Views/documentos_sst/word_template.php` líneas 397-442

**Regla:** Cada tipo de documento puede tener su propia configuración de firmas. Usar flags en `$contenido` y detección por `tipo_documento` para determinar qué bloque de firmas mostrar.

---

### 35. Logo PNG Transparencia en Word - Atributo bgcolor (Ampliación)

**Problema:** Al exportar a Word, los logos PNG con transparencia aparecen con fondo negro.

**Causa:** Microsoft Word no interpreta correctamente la transparencia de imágenes PNG en base64. El canal alfa se pierde y el fondo se vuelve negro.

**Solución completa:** Agregar fondo blanco explícito usando AMBOS métodos:

1. **Atributo HTML `bgcolor`** (Word lo entiende mejor que CSS):
```php
<td bgcolor="#FFFFFF" style="background-color: #ffffff;">
```

2. **CSS en la imagen también**:
```php
<img src="<?= $logoBase64 ?>" style="background-color: #ffffff;">
```

**En `word_template.php` (encabezado):**
```php
<!-- CORRECTO: bgcolor + style -->
<td width="80" rowspan="2" align="center" valign="middle"
    bgcolor="#FFFFFF"
    style="border:1px solid #333; padding:5px; background-color:#ffffff;">
    <?php if (!empty($logoBase64)): ?>
    <img src="<?= $logoBase64 ?>" width="70" height="45" alt="Logo"
         style="background-color:#ffffff;">
    <?php endif; ?>
</td>
```

**En `pdf_template.php` (CSS):**
```php
.encabezado-logo {
    width: 120px;
    padding: 8px;
    text-align: center;
    background-color: #ffffff;  /* Fondo blanco explícito */
}
.encabezado-logo img {
    max-width: 100px;
    max-height: 60px;
    background-color: #ffffff;  /* Fondo blanco en la imagen */
}
```

**¿Por qué usar ambos métodos?**
- `bgcolor="#FFFFFF"` es un atributo HTML legacy que Word interpreta correctamente
- `style="background-color:#ffffff"` es el método CSS moderno
- Usar ambos garantiza compatibilidad con diferentes versiones de Word

**Archivos modificados:**
- `app/Views/documentos_sst/word_template.php` líneas 207-209
- `app/Views/documentos_sst/pdf_template.php` líneas 171-182

**Regla:** Para cualquier contenedor de imágenes PNG en exports Word, SIEMPRE agregar tanto `bgcolor="#FFFFFF"` como `style="background-color:#ffffff"`.
