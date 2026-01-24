# Proyecto Documentacion SST - Parte 7
## Sistema de Generacion de Documentos con IA por Secciones

---

## CONTEXTO DEL PROYECTO

Este es un sistema de gestion SST (Seguridad y Salud en el Trabajo) para Colombia, basado en la Resolucion 0312 de 2019. El sistema permite a consultores SST generar documentacion para sus clientes empresariales.

### Stack Tecnologico
- **Backend**: PHP 8 + CodeIgniter 4
- **Frontend**: Bootstrap 5 + JavaScript vanilla
- **Base de datos**: MySQL 8 (local XAMPP + produccion DigitalOcean)
- **IA**: OpenAI API (gpt-4o-mini)
- **PDF**: Dompdf

### Ubicacion del Proyecto
```
c:\xampp\htdocs\enterprisesst\
```

---

## OBJETIVO DE ESTA PARTE

Implementar el sistema de generacion de documentos SST con las siguientes caracteristicas:

1. **Libreria PHP** que define la estructura de cada tipo de documento
2. **Generacion por secciones** usando IA (cada seccion tiene su prompt)
3. **Edicion/regeneracion individual** de secciones con contexto adicional
4. **Almacenamiento en BD** del documento y sus secciones
5. **Versionamiento** de documentos
6. **Generacion de PDF** con encabezado corporativo

---

## ARQUITECTURA PROPUESTA

### 1. Librerias de Documentos (Estructura)

Ubicacion: `app/Libraries/DocumentosSST/`

```php
// app/Libraries/DocumentosSST/DocumentoBase.php
abstract class DocumentoBase
{
    public string $codigo;           // Ej: 'PRG-EMO'
    public string $nombre;           // Ej: 'Programa de Evaluaciones Medicas'
    public string $estandar;         // Ej: '3.1.4'
    public string $carpeta;          // Ej: '2.1'
    public array $secciones = [];    // Definicion de cada seccion

    abstract public function getSecciones(): array;
    abstract public function getVariablesRequeridas(): array;
}
```

```php
// app/Libraries/DocumentosSST/ProgramaExamenesMedicos.php
class ProgramaExamenesMedicos extends DocumentoBase
{
    public string $codigo = 'PRG-EMO';
    public string $nombre = 'Programa de Evaluaciones Medicas Ocupacionales';
    public string $estandar = '3.1.4';
    public string $carpeta = '2.1';

    public function getSecciones(): array
    {
        return [
            [
                'key' => 'objetivo',
                'titulo' => 'OBJETIVO',
                'orden' => 1,
                'tipo' => 'ia',  // 'ia' | 'fijo' | 'tabla'
                'prompt' => 'Genera el objetivo del programa de evaluaciones medicas ocupacionales para {empresa}, una empresa de {actividad_economica} con {total_trabajadores} trabajadores y nivel de riesgo {nivel_riesgo}. Maximo 3 parrafos.',
                'variables' => ['empresa', 'actividad_economica', 'total_trabajadores', 'nivel_riesgo'],
                'longitud_max' => 300
            ],
            [
                'key' => 'alcance',
                'titulo' => 'ALCANCE',
                'orden' => 2,
                'tipo' => 'ia',
                'prompt' => 'Define el alcance del programa de evaluaciones medicas para {empresa}. Indica a quienes aplica segun el contexto de la empresa.',
                'variables' => ['empresa', 'sedes', 'total_trabajadores'],
                'longitud_max' => 200
            ],
            [
                'key' => 'marco_legal',
                'titulo' => 'MARCO LEGAL',
                'orden' => 3,
                'tipo' => 'fijo',
                'contenido' => [
                    'Resolucion 2346 de 2007 - Evaluaciones medicas ocupacionales',
                    'Resolucion 1918 de 2009 - Modifica articulos Res. 2346',
                    'Decreto 1072 de 2015 - Decreto Unico Reglamentario del Sector Trabajo',
                    'Resolucion 0312 de 2019 - Estandares Minimos del SG-SST'
                ]
            ],
            [
                'key' => 'definiciones',
                'titulo' => 'DEFINICIONES',
                'orden' => 4,
                'tipo' => 'fijo',
                'contenido' => [
                    'Examen medico de ingreso: Evaluacion realizada antes de iniciar labores.',
                    'Examen medico periodico: Evaluacion realizada durante la vigencia del contrato.',
                    'Examen medico de retiro: Evaluacion realizada al terminar el contrato.',
                    'Examen post-incapacidad: Evaluacion despues de incapacidad prolongada.',
                    'Profesiograma: Matriz que relaciona cargos con examenes requeridos.'
                ]
            ],
            [
                'key' => 'tipos_examenes',
                'titulo' => 'TIPOS DE EVALUACIONES MEDICAS',
                'orden' => 5,
                'tipo' => 'ia',
                'prompt' => 'Describe los tipos de examenes medicos ocupacionales para {empresa} considerando nivel de riesgo {nivel_riesgo} y peligros: {peligros}. Incluye: ingreso, periodicos (frecuencia), retiro, post-incapacidad, cambio de cargo.',
                'variables' => ['empresa', 'nivel_riesgo', 'peligros'],
                'longitud_max' => 600
            ],
            [
                'key' => 'procedimiento',
                'titulo' => 'PROCEDIMIENTO',
                'orden' => 6,
                'tipo' => 'ia',
                'prompt' => 'Genera el procedimiento paso a paso para realizar examenes medicos en {empresa}. Desde solicitud hasta comunicacion de resultados. Formato lista numerada.',
                'variables' => ['empresa'],
                'longitud_max' => 500,
                'formato' => 'lista_numerada'
            ],
            [
                'key' => 'responsabilidades',
                'titulo' => 'RESPONSABILIDADES',
                'orden' => 7,
                'tipo' => 'ia',
                'prompt' => 'Define las responsabilidades de: Empleador, Trabajador, IPS contratada, Responsable SST. Para el programa de evaluaciones medicas de {empresa}.',
                'variables' => ['empresa'],
                'longitud_max' => 400
            ],
            [
                'key' => 'indicadores',
                'titulo' => 'INDICADORES',
                'orden' => 8,
                'tipo' => 'ia',
                'prompt' => 'Propone 3-4 indicadores de gestion para el programa de evaluaciones medicas. Incluir formula, meta y frecuencia de medicion.',
                'variables' => [],
                'longitud_max' => 300
            ]
        ];
    }

    public function getVariablesRequeridas(): array
    {
        return [
            'empresa',
            'nit',
            'actividad_economica',
            'nivel_riesgo',
            'total_trabajadores',
            'peligros',
            'sedes'
        ];
    }
}
```

### 2. Estructura de Base de Datos

```sql
-- Tabla principal de documentos generados
CREATE TABLE tbl_doc_generados (
    id_documento INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    codigo_documento VARCHAR(20) NOT NULL,      -- PRG-EMO-001
    tipo_documento VARCHAR(50) NOT NULL,        -- ProgramaExamenesMedicos (nombre clase)
    nombre_documento VARCHAR(255) NOT NULL,
    version INT DEFAULT 1,
    estado ENUM('borrador', 'revision', 'aprobado', 'firmado', 'obsoleto') DEFAULT 'borrador',
    id_carpeta INT NULL,                        -- FK a tbl_doc_carpetas
    fecha_aprobacion DATE NULL,
    aprobado_por INT NULL,
    observaciones TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,

    INDEX idx_cliente (id_cliente),
    INDEX idx_estado (estado),
    INDEX idx_tipo (tipo_documento),
    FOREIGN KEY (id_cliente) REFERENCES tbl_clientes(id_cliente) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de secciones de cada documento
CREATE TABLE tbl_doc_secciones (
    id_seccion INT AUTO_INCREMENT PRIMARY KEY,
    id_documento INT NOT NULL,
    seccion_key VARCHAR(50) NOT NULL,           -- 'objetivo', 'alcance', etc.
    titulo VARCHAR(100) NOT NULL,
    orden INT DEFAULT 0,
    tipo ENUM('ia', 'fijo', 'tabla', 'manual') DEFAULT 'ia',

    -- Contenidos
    contenido_generado LONGTEXT NULL,           -- Lo que genero la IA originalmente
    contexto_adicional TEXT NULL,               -- Contexto que agrego el usuario para regenerar
    contenido_editado LONGTEXT NULL,            -- Si el usuario edito manualmente
    contenido_final LONGTEXT NULL,              -- El contenido definitivo para el PDF

    -- Metadata
    prompt_usado TEXT NULL,                     -- El prompt con variables reemplazadas
    modelo_ia VARCHAR(50) NULL,                 -- gpt-4o-mini
    tokens_usados INT NULL,
    regeneraciones INT DEFAULT 0,

    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_documento (id_documento),
    UNIQUE KEY uk_documento_seccion (id_documento, seccion_key),
    FOREIGN KEY (id_documento) REFERENCES tbl_doc_generados(id_documento) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Historial de versiones de documentos
CREATE TABLE tbl_doc_versiones (
    id_version INT AUTO_INCREMENT PRIMARY KEY,
    id_documento INT NOT NULL,
    version INT NOT NULL,
    contenido_completo LONGTEXT NOT NULL,       -- JSON con todas las secciones
    motivo_cambio VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_by INT NULL,

    INDEX idx_documento (id_documento),
    FOREIGN KEY (id_documento) REFERENCES tbl_doc_generados(id_documento) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 3. Servicio de Generacion

```php
// app/Services/DocumentoGeneradorService.php
class DocumentoGeneradorService
{
    protected $openaiService;
    protected $db;

    /**
     * Genera un documento completo (todas las secciones)
     */
    public function generarDocumentoCompleto(string $tipoDocumento, int $idCliente): array
    {
        // 1. Cargar la libreria del documento
        $libreria = $this->cargarLibreria($tipoDocumento);

        // 2. Obtener contexto del cliente
        $contexto = $this->obtenerContextoCliente($idCliente);

        // 3. Crear registro del documento
        $idDocumento = $this->crearDocumento($libreria, $idCliente);

        // 4. Generar cada seccion
        $secciones = [];
        foreach ($libreria->getSecciones() as $seccion) {
            $contenido = $this->generarSeccion($seccion, $contexto);
            $this->guardarSeccion($idDocumento, $seccion, $contenido);
            $secciones[] = $contenido;
        }

        return [
            'id_documento' => $idDocumento,
            'secciones' => $secciones
        ];
    }

    /**
     * Regenera una seccion especifica con contexto adicional
     */
    public function regenerarSeccion(int $idDocumento, string $seccionKey, string $contextoAdicional): array
    {
        // 1. Obtener documento y su tipo
        $documento = $this->obtenerDocumento($idDocumento);
        $libreria = $this->cargarLibreria($documento['tipo_documento']);

        // 2. Obtener definicion de la seccion
        $seccionDef = $this->obtenerSeccionDefinicion($libreria, $seccionKey);

        // 3. Obtener contexto del cliente
        $contexto = $this->obtenerContextoCliente($documento['id_cliente']);

        // 4. Agregar contexto adicional al prompt
        $promptModificado = $seccionDef['prompt'] . "\n\nContexto adicional del consultor: " . $contextoAdicional;

        // 5. Regenerar con IA
        $contenido = $this->llamarIA($promptModificado, $contexto);

        // 6. Actualizar seccion en BD
        $this->actualizarSeccion($idDocumento, $seccionKey, $contenido, $contextoAdicional);

        return [
            'seccion_key' => $seccionKey,
            'contenido' => $contenido
        ];
    }
}
```

### 4. Controlador

```php
// app/Controllers/DocumentoGeneradorController.php
class DocumentoGeneradorController extends BaseController
{
    /**
     * Vista para generar/editar documento
     * GET /documentos/generar/{tipo}/{idCliente}
     */
    public function generar($tipo, $idCliente)
    {
        // Mostrar interfaz de generacion
    }

    /**
     * Generar documento completo via AJAX
     * POST /documentos/generar-completo
     */
    public function generarCompleto()
    {
        $tipo = $this->request->getPost('tipo');
        $idCliente = $this->request->getPost('id_cliente');

        $servicio = new DocumentoGeneradorService();
        $resultado = $servicio->generarDocumentoCompleto($tipo, $idCliente);

        return $this->response->setJSON($resultado);
    }

    /**
     * Regenerar una seccion con contexto adicional
     * POST /documentos/regenerar-seccion
     */
    public function regenerarSeccion()
    {
        $idDocumento = $this->request->getPost('id_documento');
        $seccionKey = $this->request->getPost('seccion_key');
        $contexto = $this->request->getPost('contexto_adicional');

        $servicio = new DocumentoGeneradorService();
        $resultado = $servicio->regenerarSeccion($idDocumento, $seccionKey, $contexto);

        return $this->response->setJSON($resultado);
    }

    /**
     * Guardar edicion manual de seccion
     * POST /documentos/guardar-seccion
     */
    public function guardarSeccion()
    {
        // Guardar contenido editado manualmente
    }

    /**
     * Generar PDF del documento
     * GET /documentos/pdf/{idDocumento}
     */
    public function generarPDF($idDocumento)
    {
        // Generar PDF con Dompdf
    }
}
```

### 5. Vista (Interfaz de Usuario)

```php
<!-- app/Views/documentos/generar.php -->
<!--
Interfaz con:
- Lista de secciones del documento
- Cada seccion tiene:
  - Titulo
  - Contenido (editable)
  - Boton "Regenerar"
  - Campo "Agregar contexto adicional"
- Boton global "Generar Todo"
- Boton "Vista Previa PDF"
- Boton "Guardar Borrador"
- Boton "Aprobar Documento"
-->
```

---

## TAREAS A IMPLEMENTAR

### Fase 1: Base de Datos
- [ ] Crear tabla `tbl_doc_generados`
- [ ] Crear tabla `tbl_doc_secciones`
- [ ] Crear tabla `tbl_doc_versiones`
- [ ] Ejecutar en local y produccion

### Fase 2: Librerias de Documentos
- [ ] Crear clase base `DocumentoBase.php`
- [ ] Crear `AsignacionResponsable.php` (documento simple para probar)
- [ ] Crear `ProgramaExamenesMedicos.php` (documento complejo)
- [ ] Crear registro de librerias disponibles

### Fase 3: Servicio de Generacion
- [ ] Implementar `DocumentoGeneradorService.php`
- [ ] Integrar con OpenAI API existente
- [ ] Implementar generacion completa
- [ ] Implementar regeneracion por seccion
- [ ] Implementar guardado de ediciones manuales

### Fase 4: Controlador y Rutas
- [ ] Crear `DocumentoGeneradorController.php`
- [ ] Definir rutas en `Routes.php`
- [ ] Implementar endpoints AJAX

### Fase 5: Interfaz de Usuario
- [ ] Crear vista de generacion/edicion
- [ ] Implementar JS para interaccion AJAX
- [ ] Implementar edicion inline de secciones
- [ ] Implementar campo de contexto adicional

### Fase 6: Generacion PDF
- [ ] Crear plantilla PDF con encabezado corporativo
- [ ] Integrar con Dompdf
- [ ] Implementar descarga/preview

### Fase 7: Integracion
- [ ] Conectar con modulo de documentacion existente
- [ ] Conectar con carpetas del cliente
- [ ] Conectar con estandares (para marcar cumplimiento)

---

## ARCHIVOS EXISTENTES RELEVANTES

- `app/Services/OpenAIService.php` - Servicio de IA existente
- `app/Controllers/DocumentacionController.php` - Controlador actual de documentacion
- `app/Views/documentacion/dashboard.php` - Vista actual
- `app/Libraries/ContractPDFGenerator.php` - Ejemplo de generador PDF existente
- `app/Models/DocDocumentoModel.php` - Modelo de documentos existente

---

## CREDENCIALES Y CONFIGURACION

Ver archivo: `SYNC_LOCAL_PRODUCCION.md` para credenciales de BD produccion.

Variables de entorno (`.env`):
- `OPENAI_API_KEY` - API key de OpenAI
- `OPENAI_MODEL` - Modelo a usar (gpt-4o-mini)
