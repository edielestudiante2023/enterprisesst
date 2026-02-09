# PROMPT PARA INICIAR NUEVO MÓDULO DE DOCUMENTO SST

## Instrucción para Claude

Copia y pega este prompt en una nueva conversación con Claude para iniciar la creación de un nuevo módulo de documento del sistema Enterprise SST.

---

## PROMPT DE INICIO

```
Necesito crear un nuevo módulo de documento SST para el sistema Enterprise SST (CodeIgniter 4 + PHP 8).

ANTES DE EMPEZAR, debes leer la documentación completa del sistema. Lee estos archivos EN ORDEN:

1. **PARTE 1 - Generación de Actividades (Plan de Trabajo)**
   Ruta: C:\xampp\htdocs\enterprisesst\docs\MODULO_NUMERALES_SGSST\ZZ_88_PARTE1.md
   - Arquitectura del generador de actividades
   - Servicio de actividades
   - Controlador y rutas
   - Vista con modal de preview y selección

2. **PARTE 2 - Generación de Indicadores**
   Ruta: C:\xampp\htdocs\enterprisesst\docs\MODULO_NUMERALES_SGSST\ZZ_89_PARTE2.md
   - Servicio de indicadores
   - Tipos de indicadores (estructura, proceso, resultado)
   - Límites según estándares
   - IMPORTANTE: Registro de categorías en IndicadorSSTModel

3. **PARTE 3 - Generación del Documento Formal**
   Ruta: C:\xampp\htdocs\enterprisesst\docs\MODULO_NUMERALES_SGSST\ZZ_90_PARTE3.md
   - Factory pattern para tipos de documento
   - Método getContextoBase() para consumir Parte 1 y 2
   - Constantes TIPO_SERVICIO y CATEGORIA
   - DocumentoVersionService

4. **PARTE 4 - Implementación Paso a Paso**
   Ruta: C:\xampp\htdocs\enterprisesst\docs\MODULO_NUMERALES_SGSST\ZZ_91_PARTE4.md
   - Plantilla completa de clase de documento
   - Script SQL para configuración en BD
   - Registro en Factory
   - Checklist de implementación

5. **PARTE 5 - UX/UI y Experiencia de Usuario**
   Ruta: C:\xampp\htdocs\enterprisesst\docs\MODULO_NUMERALES_SGSST\ZZ_92_PARTE5.md
   - Componentes UI reutilizables (Toast, Modal, Cards)
   - Patrones de interacción (checkbox, edición inline)
   - Feedback visual y colores
   - Funciones JavaScript reutilizables
   - Troubleshooting de errores comunes

---

## TIPOLOGÍAS DE MÓDULOS

Antes de iniciar, identifica qué tipo de módulo vas a crear:

### TIPO A: Módulo Simple (Solo Parte 3)
- **NO necesita** actividades del Plan de Trabajo (Parte 1)
- **NO necesita** indicadores (Parte 2)
- **SOLO genera** el documento formal con IA (Parte 3)
- **Ejemplos**: Políticas, Manuales, Procedimientos simples, Mecanismos de Comunicación
- **Documentación a leer**: Solo PARTE 3, PARTE 4 y PARTE 5
- **Constantes**: TIPO_SERVICIO = null, CATEGORIA = null

### TIPO B: Módulo Completo (3 Partes)
- **SÍ necesita** actividades del Plan de Trabajo (Parte 1)
- **SÍ necesita** indicadores (Parte 2)
- **Genera** documento que consume datos de Partes 1 y 2 (Parte 3)
- **Ejemplos**: Programas (Capacitación, Promoción), Planes con metas y objetivos
- **Documentación a leer**: TODAS las partes (1, 2, 3, 4, 5)
- **Constantes**: TIPO_SERVICIO y CATEGORIA deben coincidir en las 3 partes

### ¿Cómo saber qué tipo usar?
Pregúntate:
1. ¿El documento necesita listar actividades específicas que se agregarán al Plan de Trabajo Anual?
2. ¿El documento necesita definir indicadores de gestión (estructura, proceso, resultado)?

- Si respondiste **NO a ambas** → **TIPO A**
- Si respondiste **SÍ a alguna** → **TIPO B**

---

DESPUÉS DE LEER TODA LA DOCUMENTACIÓN, confirma que entiendes:

1. La arquitectura de 3 partes (Actividades → Indicadores → Documento)
2. Los campos de vinculación: tipo_servicio, categoria, tipo_documento
3. Cómo sobrescribir getContextoBase() para consumir datos
4. Los límites según estándares (7, 21, 60)
5. El patrón de Factory y registro de tipos
6. Los componentes UX/UI estándar

---

EL DOCUMENTO QUE VOY A CREAR ES:

[COMPLETAR CON LA INFORMACIÓN DEL NUEVO DOCUMENTO]

- Nombre del documento:
- Estándar de la Resolución 0312/2019:
- tipo_documento (snake_case):
- **TIPOLOGÍA: (TIPO A / TIPO B)** ← IMPORTANTE
- Secciones que debe tener:
- ¿Necesita actividades del Plan de Trabajo? (Sí/No) → Si es "Sí" = TIPO B
- ¿Necesita indicadores? (Sí/No) → Si es "Sí" = TIPO B
- ¿Quiénes firman el documento?

**NOTA**: Si ambas respuestas son "No" → es TIPO A. Si alguna es "Sí" → es TIPO B.

---

Una vez que hayas leído toda la documentación y tengas clara la información del documento, procede con la implementación según el tipo:

### IMPLEMENTACIÓN TIPO A (Solo Parte 3)

1. Crear la clase del tipo de documento (con TIPO_SERVICIO = null, CATEGORIA = null)
2. Registrar en Factory
3. Crear vista del tipo en `_tipos/`
4. Crear script SQL y **EJECUTARLO**
5. **Actualizar componente acciones_documento.php** (agregar rutas de ver/editar)
6. **Verificar botón siempre visible** (no ocultar cuando hay documento aprobado)
7. **Crear ruta y método para Vista Web** (ver sección VISTA WEB DEL DOCUMENTO)
8. Probar la implementación

### IMPLEMENTACIÓN TIPO B (3 Partes completas)

1. Crear el servicio de actividades (Parte 1)
2. Agregar 4 métodos en GeneradorIAController para actividades
3. Agregar 4 rutas para actividades
4. Crear vista de actividades en `generador_ia/`
5. Crear el servicio de indicadores (Parte 2)
6. Registrar categoría en IndicadorSSTModel::CATEGORIAS
7. Agregar 4 métodos en GeneradorIAController para indicadores
8. Agregar 4 rutas para indicadores
9. Crear vista de indicadores en `generador_ia/`
10. Crear la clase del tipo de documento (con getContextoBase() que consume Partes 1 y 2)
11. Registrar en Factory
12. Crear vista del tipo en `_tipos/`
13. Crear script SQL y **EJECUTARLO**
14. **Actualizar componente acciones_documento.php** (agregar rutas de ver/editar)
15. **Verificar botón siempre visible** (no ocultar cuando hay documento aprobado)
16. **Crear ruta y método para Vista Web** (ver sección VISTA WEB DEL DOCUMENTO)
17. Probar la implementación completa (Parte 1 → Parte 2 → Parte 3)
```

---

## EJEMPLO DE USO

```
Necesito crear un nuevo módulo de documento SST para el sistema Enterprise SST (CodeIgniter 4 + PHP 8).

[... resto del prompt anterior ...]

EL DOCUMENTO QUE VOY A CREAR ES:

- Nombre del documento: Plan de Prevención y Preparación ante Emergencias
- Estándar de la Resolución 0312/2019: 5.1.1
- tipo_documento (snake_case): plan_prevencion_emergencias
- Secciones que debe tener:
  1. Objetivo
  2. Alcance
  3. Marco Legal
  4. Identificación de Amenazas
  5. Análisis de Vulnerabilidad
  6. Recursos para Emergencias
  7. Plan de Evacuación
  8. Brigadas de Emergencia
  9. Procedimientos Operativos
  10. Simulacros
- ¿Necesita actividades del Plan de Trabajo? Sí (actividades de simulacros, capacitaciones de brigada)
- ¿Necesita indicadores? Sí (cobertura de capacitación, tiempo de evacuación, etc.)
- ¿Quiénes firman el documento? Responsable SST, Representante Legal, Coordinador de Emergencias
```

---

## CHECKLIST DE ARCHIVOS DE DOCUMENTACIÓN

Antes de iniciar, verificar que existan estos archivos:

- [ ] `C:\xampp\htdocs\enterprisesst\docs\MODULO_NUMERALES_SGSST\ZZ_88_PARTE1.md` - Actividades
- [ ] `C:\xampp\htdocs\enterprisesst\docs\MODULO_NUMERALES_SGSST\ZZ_89_PARTE2.md` - Indicadores
- [ ] `C:\xampp\htdocs\enterprisesst\docs\MODULO_NUMERALES_SGSST\ZZ_90_PARTE3.md` - Documento Formal
- [ ] `C:\xampp\htdocs\enterprisesst\docs\MODULO_NUMERALES_SGSST\ZZ_91_PARTE4.md` - Implementación
- [ ] `C:\xampp\htdocs\enterprisesst\docs\MODULO_NUMERALES_SGSST\ZZ_92_PARTE5.md` - UX/UI

---

## MAPA DE DOCUMENTACIÓN

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                    DOCUMENTACIÓN DEL MÓDULO DE 3 PARTES                     │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  ┌──────────────┐   ┌──────────────┐   ┌──────────────┐                    │
│  │ ZZ_88_PARTE1 │──▶│ ZZ_89_PARTE2 │──▶│ ZZ_90_PARTE3 │                    │
│  │ Actividades  │   │ Indicadores  │   │ Documento    │                    │
│  │ (PTA)        │   │ (Medición)   │   │ (Formal)     │                    │
│  └──────────────┘   └──────────────┘   └──────────────┘                    │
│         │                  │                  │                             │
│         │                  │                  │                             │
│         └──────────────────┼──────────────────┘                             │
│                            │                                                │
│                            ▼                                                │
│                   ┌──────────────┐                                          │
│                   │ ZZ_91_PARTE4 │                                          │
│                   │ Implementa-  │                                          │
│                   │ ción paso a  │                                          │
│                   │ paso         │                                          │
│                   └──────────────┘                                          │
│                            │                                                │
│                            ▼                                                │
│                   ┌──────────────┐                                          │
│                   │ ZZ_92_PARTE5 │                                          │
│                   │ UX/UI        │                                          │
│                   │ Componentes  │                                          │
│                   │ JavaScript   │                                          │
│                   └──────────────┘                                          │
│                                                                             │
│  FLUJO DE LECTURA: PARTE1 → PARTE2 → PARTE3 → PARTE4 → PARTE5              │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## ARCHIVOS QUE SE CREARÁN (SEGÚN TIPOLOGÍA)

### TIPO A - Módulo Simple (Solo Parte 3)

**Archivos a CREAR:**
- `app/Libraries/DocumentosSSTTypes/[NombreClase].php`
- `app/Views/documentacion/_tipos/[tipo].php`
- `app/SQL/agregar_[tipo_documento].php`

**Archivos a MODIFICAR:**
- `app/Libraries/DocumentosSSTTypes/DocumentoSSTFactory.php` - Registrar tipo
- `app/Views/documentacion/_components/acciones_documento.php`:
  - Agregar entrada en `$mapaRutas`
  - Agregar elseif para `$urlEditar`

---

### TIPO B - Módulo Completo (3 Partes)

**Parte 1 - Actividades:**
- `app/Services/[NombreDocumento]Service.php`
- `app/Views/generador_ia/[nombre_documento].php`
- 4 métodos en `GeneradorIAController.php`
- 4 rutas en `Routes.php`

**Parte 2 - Indicadores:**
- `app/Services/[NombreDocumento]IndicadoresService.php`
- `app/Views/generador_ia/indicadores_[nombre].php`
- 4 métodos adicionales en `GeneradorIAController.php`
- 4 rutas adicionales en `Routes.php`
- Nueva categoría en `IndicadorSSTModel.php`

**Parte 3 - Documento:**
- `app/Libraries/DocumentosSSTTypes/[NombreClase].php`
- `app/Views/documentacion/_tipos/[tipo].php`
- `app/SQL/agregar_[tipo_documento].php`

**Archivos a MODIFICAR:**
- `app/Libraries/DocumentosSSTTypes/DocumentoSSTFactory.php` - Registrar tipo
- `app/Views/documentacion/_components/acciones_documento.php`:
  - Agregar entrada en `$mapaRutas`
  - Agregar elseif para `$urlEditar`

---

## VISTA WEB DEL DOCUMENTO (CRÍTICO)

**⚠️ Sin esto, el botón "Vista Previa" / "Ver" dará error 404.**

Para que el documento se pueda ver en la web (no solo exportar a PDF/Word), se necesita:

### 1. Agregar ruta en `app/Config/Routes.php`

```php
// [Estándar] [Nombre del documento]
$routes->get('/documentos-sst/(:num)/[nombre-kebab-case]/(:num)', 'DocumentosSSTController::[nombreMetodo]/$1/$2');
```

**Ejemplo:**
```php
// 2.8.1 Mecanismos de Comunicación, Auto Reporte en SG-SST
$routes->get('/documentos-sst/(:num)/mecanismos-comunicacion/(:num)', 'DocumentosSSTController::mecanismosComunicacion/$1/$2');
```

### 2. Agregar método en `app/Controllers/DocumentosSSTController.php`

El método usa la vista genérica `documento_generico`. Copiar estructura de otro método similar:

```php
/**
 * [Estándar] [Nombre del Documento]
 */
public function [nombreMetodo](int $idCliente, int $anio)
{
    $cliente = $this->clienteModel->find($idCliente);
    if (!$cliente) {
        return redirect()->back()->with('error', 'Cliente no encontrado');
    }

    $documento = $this->db->table('tbl_documentos_sst')
        ->where('id_cliente', $idCliente)
        ->where('tipo_documento', '[tipo_documento_snake_case]')
        ->where('anio', $anio)
        ->get()
        ->getRowArray();

    if (!$documento) {
        return redirect()->to(base_url('documentos/generar/[tipo_documento]/' . $idCliente))
            ->with('error', 'Documento no encontrado. Genere primero el documento.');
    }

    $contenido = json_decode($documento['contenido'], true);

    if (!empty($contenido['secciones'])) {
        $contenido['secciones'] = $this->normalizarSecciones($contenido['secciones'], '[tipo_documento]');
    }

    // ... cargar versiones, responsables, contexto, consultor, firmas electrónicas ...
    // (copiar de otro método existente como manualConvivenciaLaboral)

    $data = [
        'titulo' => '[Nombre del Documento] - ' . $cliente['nombre_cliente'],
        'cliente' => $cliente,
        'documento' => $documento,
        'contenido' => $contenido,
        'anio' => $anio,
        // ... resto de datos ...
        'tipoDocumento' => '[tipo_documento_snake_case]'
    ];

    return view('documentos_sst/documento_generico', $data);
}
```

### 3. Verificar consistencia de nombres

| Ubicación | Formato | Ejemplo |
|-----------|---------|---------|
| `$mapaRutas` en acciones_documento.php | `'tipo_documento' => 'nombre-kebab/anio'` | `'mecanismos_comunicacion_sgsst' => 'mecanismos-comunicacion/' . $docSST['anio']` |
| Ruta en Routes.php | `/documentos-sst/(:num)/nombre-kebab/(:num)` | `/documentos-sst/(:num)/mecanismos-comunicacion/(:num)` |
| Método en controlador | `nombreCamelCase()` | `mecanismosComunicacion()` |

**⚠️ El nombre en la ruta DEBE coincidir con el valor en `$mapaRutas`.**

### Documentación de referencia para estilos WEB

Consultar: `C:\xampp\htdocs\enterprisesst\docs\MODULO_NUMERALES_SGSST\2_AA_ WEB.md`

---

## CONTENIDO INICIAL DINÁMICO (OBLIGATORIO)

**⚠️ NUNCA hardcodear contenido inicial en controladores.**

Cuando un controlador necesita crear un documento nuevo con secciones iniciales, usar `DocumentoConfigService`:

### Patrón Correcto

```php
// ✅ CORRECTO: Contenido dinámico desde BD
$contenidoInicial = $this->configService->crearContenidoInicial('tipo_documento');

$this->db->table('tbl_documentos_sst')->insert([
    'id_cliente' => $idCliente,
    'tipo_documento' => 'tipo_documento',
    'contenido' => json_encode($contenidoInicial),
    // ...
]);
```

### Patrón Incorrecto (EVITAR)

```php
// ❌ INCORRECTO: Hardcodeo que causa inconsistencias
$contenidoInicial = [
    'secciones' => [
        ['titulo' => '1. OBJETIVO', 'contenido' => '...', 'orden' => 1],
        ['titulo' => '2. ALCANCE', 'contenido' => '...', 'orden' => 2],
    ]
];
```

### Por qué es importante

1. **Consistencia**: Las secciones vienen de `tbl_doc_secciones_config`
2. **Keys correctos**: El contenido usa keys (`objetivo`, `alcance`) que coinciden con BD
3. **normalizarSecciones()**: Puede hacer match correcto entre Vista Web y Vista Edición
4. **Mantenibilidad**: Cambiar secciones solo requiere modificar BD, no código PHP

### Métodos disponibles en DocumentoConfigService

| Método | Uso |
|--------|-----|
| `crearContenidoInicial($tipo)` | Estructura vacía con keys de BD |
| `crearContenidoConContexto($tipo, $idCliente, $extra)` | Incluye datos dinámicos del cliente |
| `obtenerSecciones($tipo)` | Lista de secciones con metadatos |
| `obtenerMapeoSecciones($tipo)` | Mapeo `['key' => 'Nombre']` |

---

## NOTAS IMPORTANTES

1. **Orden de lectura según tipología**:
   - **TIPO A**: Leer PARTE 3 → PARTE 4 → PARTE 5 (saltarse PARTE 1 y 2)
   - **TIPO B**: Leer PARTE 1 → PARTE 2 → PARTE 3 → PARTE 4 → PARTE 5 (todas en orden)

2. **No saltar documentación**: Si la IA no lee toda la documentación, puede crear código inconsistente con el sistema.

3. **Categorías de indicadores**: SIEMPRE verificar que la categoría exista en `IndicadorSSTModel::CATEGORIAS` antes de crear el servicio de indicadores.

4. **Constantes de vinculación**: Los valores de `TIPO_SERVICIO` y `CATEGORIA` deben ser idénticos en las 3 partes.

5. **Límites según estándares**:
   - Actividades: 7 est. = 3, 21 est. = 5, 60 est. = 8
   - Indicadores: 7 est. = 2, 21 est. = 4, 60 est. = 6

6. **UX consistente**: Usar los componentes documentados en PARTE5 para mantener coherencia visual.

7. **Componente acciones_documento.php**: SIEMPRE agregar el tipo_documento a:
   - `$mapaRutas` para el botón "Ver"
   - Los elseif de `$urlEditar` para el botón "Editar"
   Sin esto, los botones de acción en la tabla de documentos no funcionan.

8. **Botón de generación siempre visible**: La vista del tipo debe mostrar el botón SIEMPRE:
   - Si NO hay documento: "Crear con IA [año]" (btn-success)
   - Si YA hay documento: "Nueva versión [año]" (btn-outline-success)
   Esto permite regenerar documentos por cambios normativos, cambio de sede, etc.

9. **Script SQL debe ejecutarse**: No basta con crear el archivo `app/SQL/agregar_[tipo].php`, hay que ejecutarlo con `php app/SQL/agregar_[tipo].php` para que la generación con IA funcione.

10. **Vista Web del Documento (Ruta + Método)**: Sin la ruta en `Routes.php` y el método en `DocumentosSSTController.php`, el botón "Ver" / "Vista Previa" dará **error 404**. Ver sección "VISTA WEB DEL DOCUMENTO" arriba. Documentación de estilos en `2_AA_ WEB.md`.

---

## CONTACTO

Documentación creada para Enterprise SST
Última actualización: Febrero 2026
