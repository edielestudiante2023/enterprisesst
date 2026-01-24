# Proyecto de Documentacion SST - Parte 7

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

```text
c:\xampp\htdocs\enterprisesst\
```

---

## OBJETIVO DE ESTA PARTE

Implementar el sistema de generacion de documentos SST con las siguientes caracteristicas:

1. **Librerias PHP** que definen la estructura de cada tipo de documento
2. **Generacion por secciones** usando IA (cada seccion tiene su prompt)
3. **Edicion/regeneracion individual** de secciones con contexto adicional
4. **Almacenamiento en BD** del documento y sus secciones
5. **Versionamiento** de documentos
6. **Generacion de PDF** con encabezado corporativo

---

## ARQUITECTURA: LIBRERIAS PHP

### Ubicacion

```text
app/Libraries/DocumentosSST/
```

### Clase Base

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

### Ejemplo: Programa de Examenes Medicos

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
                'prompt' => 'Genera el objetivo del programa de evaluaciones medicas...',
                'variables' => ['empresa', 'actividad_economica', 'total_trabajadores'],
                'longitud_max' => 300
            ],
            [
                'key' => 'alcance',
                'titulo' => 'ALCANCE',
                'orden' => 2,
                'tipo' => 'ia',
                'prompt' => 'Define el alcance del programa...',
                'variables' => ['empresa', 'sedes', 'total_trabajadores'],
                'longitud_max' => 200
            ],
            [
                'key' => 'marco_legal',
                'titulo' => 'MARCO LEGAL',
                'orden' => 3,
                'tipo' => 'fijo',
                'contenido' => [
                    'Resolucion 2346 de 2007',
                    'Resolucion 1918 de 2009',
                    'Decreto 1072 de 2015',
                    'Resolucion 0312 de 2019'
                ]
            ],
            // ... mas secciones
        ];
    }

    public function getVariablesRequeridas(): array
    {
        return [
            'empresa', 'nit', 'actividad_economica',
            'nivel_riesgo', 'total_trabajadores', 'peligros', 'sedes'
        ];
    }
}
```

---

## TABLAS DE BASE DE DATOS

### Tabla: tbl_doc_documentos (YA EXISTE)

Usar la tabla existente para almacenar documentos generados.

### Tabla: tbl_doc_secciones (YA EXISTE)

Campos importantes para el sistema de generacion:

```sql
tbl_doc_secciones {
    id_seccion INT PRIMARY KEY
    id_documento INT
    seccion_key VARCHAR(50)           -- 'objetivo', 'alcance', etc.
    titulo VARCHAR(100)
    orden INT
    tipo ENUM('ia', 'fijo', 'tabla', 'manual')

    -- Contenidos
    contenido_generado LONGTEXT       -- Lo que genero la IA originalmente
    contexto_adicional TEXT           -- Contexto que agrego el usuario
    contenido_editado LONGTEXT        -- Si el usuario edito manualmente
    contenido_final LONGTEXT          -- El contenido definitivo para el PDF

    -- Metadata
    prompt_usado TEXT                 -- El prompt con variables reemplazadas
    modelo_ia VARCHAR(50)             -- gpt-4o-mini
    tokens_usados INT
    regeneraciones INT DEFAULT 0
}
```

---

## FLUJO DE GENERACION

### Paso 1: Usuario selecciona tipo de documento

```text
1. Clic en "Nuevo Documento"
2. Selecciona tipo (Programa, Procedimiento, Plan, etc.)
3. Selecciona plantilla especifica (Ej: "Programa de Examenes Medicos")
4. Sistema carga la Libreria PHP correspondiente
```

### Paso 2: Sistema genera todas las secciones

```text
1. Obtiene contexto del cliente desde BD
2. Para cada seccion tipo 'ia':
   - Construye prompt con variables del cliente
   - Llama a OpenAI GPT-4o-mini
   - Guarda resultado en contenido_generado
3. Para secciones tipo 'fijo':
   - Copia contenido predefinido
4. Muestra editor con todas las secciones
```

### Paso 3: Usuario revisa y ajusta

```text
+-----------------------------------------------------------+
| SECCION 1: Objetivo                              [Aprobar] |
| +-------------------------------------------------------+ |
| | Texto generado por IA...                              | |
| +-------------------------------------------------------+ |
| Contexto adicional: [________________________________]    |
| [Regenerar con contexto]                                  |
+-----------------------------------------------------------+
```

### Paso 4: Regeneracion con contexto

```text
Usuario agrega: "el cliente paga poco, solo empleados directos"

Sistema:
1. Toma prompt original
2. Agrega: "\n\nContexto adicional del consultor: {contexto}"
3. Regenera solo esa seccion
4. Incrementa contador de regeneraciones
5. Guarda contexto_adicional en BD
```

### Paso 5: Aprobacion y PDF

```text
1. Usuario aprueba cada seccion (opcional)
2. Usuario aprueba documento completo
3. Sistema genera version
4. Sistema genera PDF con encabezado corporativo
5. Envia a flujo de firmas
```

---

## SERVICIO DE GENERACION

### Archivo

```text
app/Services/DocumentoGeneradorService.php
```

### Metodos principales

```php
class DocumentoGeneradorService
{
    /**
     * Genera un documento completo (todas las secciones)
     */
    public function generarDocumentoCompleto(string $tipoDocumento, int $idCliente): array

    /**
     * Regenera una seccion especifica con contexto adicional
     */
    public function regenerarSeccion(int $idDocumento, string $seccionKey, string $contextoAdicional): array

    /**
     * Guarda edicion manual de una seccion
     */
    public function guardarEdicionManual(int $idDocumento, string $seccionKey, string $contenido): bool

    /**
     * Aprueba una seccion
     */
    public function aprobarSeccion(int $idDocumento, string $seccionKey): bool

    /**
     * Finaliza documento (cambia estado a revision)
     */
    public function finalizarDocumento(int $idDocumento): bool
}
```

---

## CONTROLADOR

### Archivo

```text
app/Controllers/DocumentoGeneradorController.php
```

### Rutas

| Metodo | Ruta | Funcion |
|--------|------|---------|
| GET | /documentos/generar/{tipo}/{idCliente} | Vista para generar documento |
| POST | /documentos/generar-completo | AJAX: Genera todas las secciones |
| POST | /documentos/regenerar-seccion | AJAX: Regenera una seccion |
| POST | /documentos/guardar-seccion | AJAX: Guarda edicion manual |
| POST | /documentos/aprobar-seccion | AJAX: Aprueba seccion |
| GET | /documentos/pdf/{idDocumento} | Genera PDF |

---

## TAREAS DE IMPLEMENTACION

### Fase 1: Librerias de Documentos

- [ ] Crear clase base DocumentoBase.php
- [ ] Crear AsignacionResponsable.php (documento simple)
- [ ] Crear ProgramaExamenesMedicos.php (documento complejo)
- [ ] Crear registro de librerias disponibles

### Fase 2: Servicio de Generacion

- [ ] Implementar DocumentoGeneradorService.php
- [ ] Integrar con OpenAI API existente
- [ ] Implementar generacion completa
- [ ] Implementar regeneracion por seccion
- [ ] Implementar guardado de ediciones manuales

### Fase 3: Controlador y Rutas

- [ ] Crear DocumentoGeneradorController.php
- [ ] Definir rutas en Routes.php
- [ ] Implementar endpoints AJAX

### Fase 4: Interfaz de Usuario

- [ ] Crear vista de generacion/edicion
- [ ] Implementar JS para interaccion AJAX
- [ ] Implementar edicion inline de secciones
- [ ] Implementar campo de contexto adicional

### Fase 5: Generacion PDF

- [ ] Crear plantilla PDF con encabezado corporativo
- [ ] Integrar con Dompdf
- [ ] Implementar descarga/preview

---

## ARCHIVOS EXISTENTES RELEVANTES

- `app/Services/OpenAIService.php` - Servicio de IA existente
- `app/Services/IADocumentacionService.php` - Servicio de documentacion IA
- `app/Controllers/DocumentacionController.php` - Controlador actual
- `app/Libraries/ContractPDFGenerator.php` - Ejemplo de generador PDF

---

## CREDENCIALES Y SINCRONIZACION

Ver archivo: `SYNC_LOCAL_PRODUCCION.md` para:

- Credenciales de BD local y produccion
- Scripts para ejecutar SQL en ambos entornos
- Metodos de sincronizacion

Variables de entorno (`.env`):

- `OPENAI_API_KEY` - API key de OpenAI
- `OPENAI_MODEL` - Modelo a usar (gpt-4o-mini)

---

*Documento actualizado: Enero 2026*
*Proyecto: EnterpriseSST - Modulo de Documentacion*
*Parte 7 de 7*
