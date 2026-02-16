# PROMPT PARA REPARAR MÓDULOS DE DOCUMENTO SST EXISTENTES

## Instrucción para Claude

Copia y pega este prompt en una nueva conversación con Claude para reparar un módulo de documento del sistema Enterprise SST que está mal estructurado.

---

## PROMPT DE REPARACIÓN

```
Necesito reparar un módulo de documento SST existente en el sistema Enterprise SST (CodeIgniter 4 + PHP 8).

El módulo actualmente funciona pero NO sigue la arquitectura estándar del sistema. Necesito reestructurarlo para que sea mantenible y consistente con los demás módulos.

---

## PASO 1: LEER LA DOCUMENTACIÓN

ANTES DE HACER CUALQUIER CAMBIO, debes leer la documentación del sistema para entender la arquitectura correcta:

1. **PARTE 3 - Documento Formal (OBLIGATORIO)**
   Ruta: C:\xampp\htdocs\enterprisesst\docs\MODULO_NUMERALES_SGSST\ZZ_90_PARTE3.md
   - Factory pattern para tipos de documento
   - Método getContextoBase()
   - Constantes TIPO_SERVICIO y CATEGORIA
   - DocumentoVersionService

2. **PARTE 4 - Implementación Paso a Paso (OBLIGATORIO)**
   Ruta: C:\xampp\htdocs\enterprisesst\docs\MODULO_NUMERALES_SGSST\ZZ_91_PARTE4.md
   - Plantilla completa de clase de documento
   - Los 8 métodos que debe implementar
   - Registro en Factory
   - Script SQL

3. **PARTE 5 - UX/UI (RECOMENDADO)**
   Ruta: C:\xampp\htdocs\enterprisesst\docs\MODULO_NUMERALES_SGSST\ZZ_92_PARTE5.md
   - Componentes UI estándar
   - Patrones de interacción
   - Troubleshooting

SOLO SI el módulo necesita actividades o indicadores:

4. **PARTE 1 - Actividades** (si aplica)
   Ruta: C:\xampp\htdocs\enterprisesst\docs\MODULO_NUMERALES_SGSST\ZZ_88_PARTE1.md

5. **PARTE 2 - Indicadores** (si aplica)
   Ruta: C:\xampp\htdocs\enterprisesst\docs\MODULO_NUMERALES_SGSST\ZZ_89_PARTE2.md

---

## PASO 2: IDENTIFICAR EL TIPO DE MÓDULO

Los módulos pueden ser:

### TIPO A: Módulo Simple (Solo Documento - Parte 3)
- **NO necesita** actividades del Plan de Trabajo (Parte 1)
- **NO necesita** indicadores (Parte 2)
- **Solo genera** el documento formal con IA (Parte 3)
- **Ejemplos**: Políticas, Manuales de convivencia, Procedimientos simples, Mecanismos de Comunicación
- **Constantes en la clase**: `TIPO_SERVICIO = null`, `CATEGORIA = null`
- **Documentación requerida**: PARTE 3, PARTE 4, PARTE 5

### TIPO B: Módulo Completo (3 Partes)
- **SÍ necesita** actividades del Plan de Trabajo (Parte 1)
- **SÍ necesita** indicadores (Parte 2)
- **Genera** documento que consume datos de las partes anteriores (Parte 3)
- **Ejemplos**: Programas (Capacitación, Promoción), Planes con objetivos y metas
- **Constantes en la clase**: `TIPO_SERVICIO` y `CATEGORIA` deben coincidir con servicios de Parte 1 y 2
- **Documentación requerida**: TODAS (PARTE 1, 2, 3, 4, 5)

### ¿Cómo identificar el tipo del módulo existente?
1. Buscar si existe servicio de actividades en `app/Services/[Nombre]Service.php`
2. Buscar si existe servicio de indicadores en `app/Services/[Nombre]IndicadoresService.php`
3. Revisar si la clase del documento tiene `TIPO_SERVICIO` o `CATEGORIA` diferentes de null

- Si **NO tiene servicios** y constantes son null → **TIPO A**
- Si **tiene servicios** o constantes tienen valor → **TIPO B** (aunque esté incompleto)

---

## PASO 3: DIAGNÓSTICO DE PROBLEMAS

Revisar estos archivos para diagnosticar el estado actual del módulo:

### 3.1 Verificar la clase del tipo de documento
Buscar en: `app/Libraries/DocumentosSSTTypes/[NombreClase].php`

PROBLEMAS COMUNES:
- [ ] No existe la clase (todo está hardcodeado en controlador)
- [ ] La clase no extiende AbstractDocumentoSST
- [ ] La clase no implementa DocumentoSSTInterface
- [ ] Faltan métodos obligatorios
- [ ] El método getContextoBase() no consume datos de Parte 1/2
- [ ] Las constantes TIPO_SERVICIO/CATEGORIA no coinciden con otros servicios

### 3.2 Verificar el registro en Factory
Buscar en: `app/Libraries/DocumentosSSTTypes/DocumentoSSTFactory.php`

PROBLEMAS COMUNES:
- [ ] El tipo_documento no está registrado
- [ ] El mapeo apunta a una clase incorrecta

### 3.3 Verificar la vista del tipo
Buscar en: `app/Views/documentacion/_tipos/[tipo_documento].php`

PROBLEMAS COMUNES:
- [ ] La vista está inline en otra vista general
- [ ] No usa los componentes estándar
- [ ] El modal de IA está mal estructurado
- [ ] No hay panel colapsable para "Mejorar con IA"

### 3.4 Verificar los servicios (si es Tipo B)
Buscar en: `app/Services/`

PROBLEMAS COMUNES:
- [ ] El servicio de actividades no existe
- [ ] El servicio de indicadores no existe
- [ ] Los servicios usan constantes diferentes
- [ ] No respetan los límites según estándares (7→3, 21→5, 60→8)

### 3.5 Verificar la configuración en BD (CRÍTICO)

**Tabla principal:** `tbl_doc_tipo_configuracion`

El controlador `DocumentosSSTController::generarConIA()` usa `DocumentoConfigService` que busca en esta tabla. Si no existe el registro, la generación con IA NO FUNCIONA aunque la clase exista.

VERIFICAR:
```sql
SELECT * FROM tbl_doc_tipo_configuracion WHERE tipo_documento = '[tipo_documento]';
SELECT * FROM tbl_doc_secciones_config WHERE id_tipo_config = [id];
SELECT * FROM tbl_doc_firmantes_config WHERE id_tipo_config = [id];
```

PROBLEMAS COMUNES:
- [ ] **Script SQL no ejecutado** (archivo existe pero no se corrió)
- [ ] Falta el registro en `tbl_doc_tipo_configuracion`
- [ ] Faltan las secciones en `tbl_doc_secciones_config`
- [ ] Faltan los firmantes en `tbl_doc_firmantes_config`
- [ ] Los campos `tipo_servicio` o `categoria` no coinciden

**SOLUCIÓN:** Ejecutar el script SQL:
```bash
php app/SQL/agregar_[tipo_documento].php
```

### 3.6 Verificar componente de acciones (CRÍTICO)

**Archivo:** `app/Views/documentacion/_components/acciones_documento.php`

Este componente maneja los botones de acción (PDF, ver, editar) en la tabla de documentos. Si el tipo_documento NO está registrado aquí, el botón de editar no aparece.

VERIFICAR en el archivo:
1. Buscar en `$mapaRutas` - debe existir entrada para el tipo_documento
2. Buscar en los `elseif ($tipoDoc === '...')` - debe existir entrada para `$urlEditar`

PROBLEMAS COMUNES:
- [ ] Falta entrada en `$mapaRutas` (botón "Ver" no funciona)
- [ ] Falta elseif para `$urlEditar` (botón "Editar" no aparece)

**SOLUCIÓN:** Agregar las entradas necesarias:
```php
// En $mapaRutas (línea ~10):
'[tipo_documento]' => '[ruta-slug]/' . $docSST['anio'],

// En los elseif de $urlEditar (línea ~49):
} elseif ($tipoDoc === '[tipo_documento]') {
    $urlEditar = base_url('documentos/generar/[tipo_documento]/' . $cliente['id_cliente'] . '?anio=' . $docSST['anio']);
}
```

### 3.7 Verificar botón de generación en vista del tipo

**Archivo:** `app/Views/documentacion/_tipos/[tipo_documento].php`

PROBLEMA COMÚN: El botón "Crear con IA" desaparece cuando ya existe un documento aprobado.

**Esto es INCORRECTO** porque:
- Cambios normativos pueden requerir nueva versión
- La empresa puede cambiar de sede
- Se necesita regenerar el documento N veces

VERIFICAR:
- [ ] El botón está condicionado con `if (!$hayAprobadoAnioActual)`
- [ ] No hay opción para crear nueva versión

**SOLUCIÓN:** Cambiar la lógica para mostrar siempre el botón:
```php
<?php if ($hayAprobadoAnioActual): ?>
    <a href="..." class="btn btn-outline-success">
        <i class="bi bi-arrow-repeat me-1"></i>Nueva versión <?= date('Y') ?>
    </a>
<?php else: ?>
    <a href="..." class="btn btn-success">
        <i class="bi bi-magic me-1"></i>Crear con IA <?= date('Y') ?>
    </a>
<?php endif; ?>
```

### 3.8 Verificar Vista Web del Documento (CRÍTICO)

**Sin esto, el botón "Vista Previa" / "Ver" dará ERROR 404.**

El sistema necesita:
1. **Ruta** en `app/Config/Routes.php`
2. **Método** en `app/Controllers/DocumentosSSTController.php`

VERIFICAR:
```bash
# Buscar si existe ruta para el tipo de documento
grep -n "[nombre-kebab]" app/Config/Routes.php

# Buscar si existe método en controlador
grep -n "function [nombreMetodo]" app/Controllers/DocumentosSSTController.php
```

PROBLEMAS COMUNES:
- [ ] Falta la ruta en Routes.php
- [ ] Falta el método en DocumentosSSTController.php
- [ ] El nombre en la ruta NO coincide con el valor en `$mapaRutas` de acciones_documento.php

**SOLUCIÓN:**

1. Agregar ruta en `app/Config/Routes.php`:
```php
// [Estándar] [Nombre del documento]
$routes->get('/documentos-sst/(:num)/[nombre-kebab]/(:num)', 'DocumentosSSTController::[nombreMetodo]/$1/$2');
```

2. Agregar método en `app/Controllers/DocumentosSSTController.php`:
```php
public function [nombreMetodo](int $idCliente, int $anio)
{
    // Copiar estructura de otro método similar como manualConvivenciaLaboral()
    // Usar tipo_documento correcto en las consultas
    // Retornar view('documentos_sst/documento_generico', $data);
}
```

3. Verificar consistencia de nombres:

| Ubicación | Valor |
|-----------|-------|
| `$mapaRutas` en acciones_documento.php | `'[tipo_documento]' => '[nombre-kebab]/' . $docSST['anio']` |
| Ruta en Routes.php | `/documentos-sst/(:num)/[nombre-kebab]/(:num)` |
| Método en controlador | `[nombreCamelCase]()` |

**Referencia de estilos WEB:** `C:\xampp\htdocs\enterprisesst\docs\MODULO_NUMERALES_SGSST\2_AA_ WEB.md`

---

## PASO 4: INFORMACIÓN DEL MÓDULO A REPARAR

[COMPLETAR CON LA INFORMACIÓN DEL MÓDULO]

- URL de la carpeta:
- Nombre del documento:
- Estándar de la Resolución 0312/2019:
- tipo_documento actual (snake_case):
- ¿Es Tipo A o Tipo B?:
- Si es Tipo B, ¿qué partes tiene implementadas?:
  - [ ] Parte 1 (Actividades)
  - [ ] Parte 2 (Indicadores)
  - [ ] Parte 3 (Documento)

PROBLEMAS IDENTIFICADOS:
[Lista los problemas específicos que detectaste]

---

## PASO 5: PLAN DE REPARACIÓN

Una vez diagnosticado, procede con las reparaciones EN ESTE ORDEN:

### Para Módulos TIPO A (Solo Documento):

1. **Crear/Corregir la clase del tipo**
   - Usar plantilla de C:\xampp\htdocs\enterprisesst\docs\MODULO_NUMERALES_SGSST\ZZ_91_PARTE4.md
   - Implementar los 8 métodos obligatorios
   - Configurar CATEGORIA = null (no usa indicadores)
   - Configurar TIPO_SERVICIO = null (no usa actividades)

2. **Registrar en Factory**
   - Agregar mapeo en DocumentoSSTFactory.php

3. **Crear/Corregir la vista del tipo**
   - Extraer a app/Views/documentacion/_tipos/
   - Usar componentes estándar de C:\xampp\htdocs\enterprisesst\docs\MODULO_NUMERALES_SGSST\ZZ_92_PARTE5.md

4. **Verificar/Crear script SQL**
   - Crear en app/SQL/agregar_[tipo].php

### Para Módulos TIPO B (3 Partes):

1. **Primero reparar/crear Parte 1** (si aplica)
   - Servicio de actividades con TIPO_SERVICIO
   - Vista con modal de preview
   - Métodos en GeneradorIAController
   - Rutas

2. **Luego reparar/crear Parte 2** (si aplica)
   - Servicio de indicadores con CATEGORIA
   - Registrar categoría en IndicadorSSTModel::CATEGORIAS
   - Vista con modal de preview
   - Métodos en GeneradorIAController
   - Rutas

3. **Finalmente reparar Parte 3**
   - Clase con getContextoBase() que consume Partes 1 y 2
   - TIPO_SERVICIO = mismo valor que Parte 1
   - CATEGORIA = mismo valor que Parte 2
   - Registrar en Factory
   - Vista del tipo con paneles colapsables

---

## REGLAS DE REPARACIÓN

1. **NO romper funcionalidad existente**: Si algo funciona, no lo elimines hasta tener el reemplazo funcionando.

2. **Mantener datos existentes**: Las reparaciones deben ser compatibles con datos ya guardados en BD.

3. **Constantes consistentes**: Si Parte 1 usa `TIPO_SERVICIO = 'programa_xyz'`, Partes 2 y 3 deben usar el mismo valor.

4. **Respetar límites**:
   - 7 estándares: 3 actividades, 2 indicadores
   - 21 estándares: 5 actividades, 4 indicadores
   - 60 estándares: 8 actividades, 6 indicadores

5. **Extraer, no duplicar**: Si hay código repetido, extraer a la clase/servicio correspondiente.

6. **Usar Factory**: Nunca instanciar clases de documento directamente en controladores.

---

## CHECKLIST FINAL DE REPARACIÓN

### Módulo Tipo A (Solo Documento)
- [ ] Clase existe en app/Libraries/DocumentosSSTTypes/
- [ ] Clase extiende AbstractDocumentoSST
- [ ] Clase implementa DocumentoSSTInterface
- [ ] Los 8 métodos están implementados
- [ ] Registrado en DocumentoSSTFactory.php
- [ ] Vista existe en app/Views/documentacion/_tipos/
- [ ] Vista usa componentes estándar
- [ ] Script SQL existe en app/SQL/
- [ ] Tipo registrado en tbl_tipos_documentos_sst
- [ ] **Ruta existe en Routes.php** (para Vista Web)
- [ ] **Método existe en DocumentosSSTController.php** (para Vista Web)
- [ ] **Entrada en $mapaRutas de acciones_documento.php coincide con ruta**

### Módulo Tipo B (3 Partes)
Todo lo anterior MÁS:
- [ ] Servicio de actividades existe y usa TIPO_SERVICIO correcto
- [ ] Servicio de indicadores existe y usa CATEGORIA correcta
- [ ] CATEGORIA registrada en IndicadorSSTModel::CATEGORIAS
- [ ] getContextoBase() consume actividades filtradas
- [ ] getContextoBase() consume indicadores filtrados
- [ ] Vista de actividades en app/Views/generador_ia/
- [ ] Vista de indicadores en app/Views/generador_ia/
- [ ] Rutas de actividades en Routes.php
- [ ] Rutas de indicadores en Routes.php
- [ ] 4 métodos de actividades en GeneradorIAController
- [ ] 4 métodos de indicadores en GeneradorIAController
```

---

## EJEMPLO DE USO - Módulo Tipo A

```
Necesito reparar un módulo de documento SST existente...

[... resto del prompt anterior ...]

## PASO 4: INFORMACIÓN DEL MÓDULO A REPARAR

- URL de la carpeta: /documentacion/carpeta/334
- Nombre del documento: Mecanismos de Comunicación del SG-SST
- Estándar de la Resolución 0312/2019: 1.2.2
- tipo_documento actual: mecanismos_comunicacion_sgsst
- ¿Es Tipo A o Tipo B?: TIPO A (Solo documento, no necesita actividades ni indicadores propios)

PROBLEMAS IDENTIFICADOS:
1. No existe clase en DocumentosSSTTypes/
2. La lógica está dispersa en el controlador
3. La vista no usa componentes estándar
4. No está registrado en Factory
```

---

## EJEMPLO DE USO - Módulo Tipo B

```
Necesito reparar un módulo de documento SST existente...

[... resto del prompt anterior ...]

## PASO 4: INFORMACIÓN DEL MÓDULO A REPARAR

- URL de la carpeta: /documentacion/carpeta/XXX
- Nombre del documento: Programa de Promoción y Prevención en Salud
- Estándar de la Resolución 0312/2019: 3.1.7
- tipo_documento actual: programa_promocion_prevencion_salud
- ¿Es Tipo A o Tipo B?: TIPO B (Necesita actividades e indicadores)
- Partes implementadas:
  - [X] Parte 1 (Actividades) - Existe pero con estructura incorrecta
  - [ ] Parte 2 (Indicadores) - No existe
  - [X] Parte 3 (Documento) - Existe pero no consume Partes 1 y 2

PROBLEMAS IDENTIFICADOS:
1. La clase existe pero getContextoBase() no consume actividades
2. Parte 2 (indicadores) nunca fue implementada
3. Las constantes TIPO_SERVICIO son inconsistentes
4. El servicio de actividades no respeta límites
```

---

## DIAGNÓSTICO RÁPIDO

### Comando para verificar estructura de un módulo

```bash
# Verificar si existe la clase
ls app/Libraries/DocumentosSSTTypes/ | grep -i "[nombre]"

# Verificar si está en Factory
grep -n "tipo_documento" app/Libraries/DocumentosSSTTypes/DocumentoSSTFactory.php

# Verificar si existe la vista del tipo
ls app/Views/documentacion/_tipos/ | grep -i "[nombre]"

# Verificar servicios relacionados
ls app/Services/ | grep -i "[nombre]"

# Verificar rutas
grep -n "[nombre]" app/Config/Routes.php
```

---

## DIFERENCIAS ENTRE CREAR Y REPARAR

| Aspecto | Crear Nuevo | Reparar Existente |
|---------|-------------|-------------------|
| Clase | Crear desde cero | Puede existir parcialmente |
| Factory | Solo agregar | Verificar mapeo existente |
| BD | INSERT nuevo | UPDATE o verificar existente |
| Datos | No hay datos | Preservar datos existentes |
| Vistas | Crear nuevas | Migrar código existente |
| Pruebas | Solo probar nuevo | Verificar no romper nada |

---

## ERRORES COMUNES AL REPARAR

### 1. Cambiar tipo_documento
**Problema**: Cambiar el valor de `tipo_documento` rompe documentos existentes
**Solución**: Mantener el mismo `tipo_documento`, solo mejorar la estructura interna

### 2. Cambiar CATEGORIA
**Problema**: Cambiar CATEGORIA desvincula indicadores existentes
**Solución**: Usar la misma CATEGORIA que ya tienen los indicadores en BD

### 3. Eliminar código sin verificar
**Problema**: Eliminar método usado por otras partes del sistema
**Solución**: Buscar referencias antes de eliminar (`grep -r "nombreMetodo"`)

### 4. No migrar datos
**Problema**: La nueva estructura espera campos que no existen
**Solución**: Crear script de migración si hay cambios en estructura de datos

---

## SOLUCIÓN ARQUITECTÓNICA: CONTENIDO DINÁMICO DESDE BD

### El Problema del Hardcodeo

En versiones anteriores, los controladores creaban contenido inicial hardcodeado:

```php
// ❌ INCORRECTO: Hardcodeo que causa inconsistencias
$contenidoInicial = [
    'secciones' => [
        ['titulo' => '1. OBJETIVO', 'contenido' => '...', 'orden' => 1],
        ['titulo' => '2. ALCANCE', 'contenido' => '...', 'orden' => 2],
        // Solo 5 secciones, sin keys
    ]
];
```

**Problemas causados:**
- Inconsistencia entre Vista Edición (controlador) y Vista Web (que usa BD)
- El método `normalizarSecciones()` no puede hacer match correcto
- Las secciones de BD tienen keys (`objetivo`, `alcance`) pero el hardcodeo no
- Cambiar secciones requiere modificar código PHP

### La Solución: DocumentoConfigService::crearContenidoInicial()

El servicio `DocumentoConfigService` ahora tiene un método que crea contenido dinámico desde la BD:

```php
// ✅ CORRECTO: Contenido dinámico desde BD
$contenidoInicial = $this->configService->crearContenidoInicial('plan_objetivos_metas');
```

**Este método:**
1. Lee las secciones de `tbl_doc_secciones_config`
2. Crea estructura con keys que coinciden con la BD
3. Incluye metadatos útiles (`_meta` con nombre, prompt_ia, etc.)
4. Garantiza consistencia entre todas las vistas

### Cómo Verificar si un Módulo tiene Hardcodeo

```bash
# Buscar patrones de hardcodeo en controladores
grep -n "contenidoInicial.*\[" app/Controllers/DocumentosSSTController.php
grep -n "'secciones'.*=>" app/Controllers/DocumentosSSTController.php
```

Si encuentras bloques como este, necesitan ser reemplazados:
```php
$contenidoInicial = [
    'secciones' => [
        ['titulo' => '...', 'contenido' => '...', 'orden' => 1],
        // ...
    ]
];
```

### Cómo Reparar un Módulo con Hardcodeo

1. **Verificar que exista configuración en BD:**
```sql
SELECT * FROM tbl_doc_tipo_configuracion WHERE tipo_documento = '[tipo]';
SELECT * FROM tbl_doc_secciones_config WHERE id_tipo_config = [id];
```

2. **Si no existe, ejecutar script SQL:**
```bash
php app/SQL/agregar_[tipo_documento].php
```

3. **Reemplazar hardcodeo por llamada dinámica:**
```php
// ANTES (hardcodeado):
$contenidoInicial = [
    'secciones' => [
        ['titulo' => '1. OBJETIVO', 'contenido' => '...', 'orden' => 1],
        // ...
    ]
];

// DESPUÉS (dinámico):
$contenidoInicial = $this->configService->crearContenidoInicial('tipo_documento');
```

4. **Para contenido con contexto del cliente:**
```php
$contenidoInicial = $this->configService->crearContenidoConContexto(
    'tipo_documento',
    $idCliente,
    ['datos_adicionales' => $datos]
);
```

### Métodos Disponibles en DocumentoConfigService

| Método | Descripción |
|--------|-------------|
| `crearContenidoInicial($tipo)` | Crea estructura vacía con keys de BD |
| `crearContenidoConContexto($tipo, $idCliente, $extra)` | Incluye datos dinámicos del cliente |
| `obtenerMapeoSecciones($tipo)` | Retorna `['key' => 'Nombre']` para validaciones |
| `obtenerSecciones($tipo)` | Lista completa de secciones con metadatos |

### Checklist de Verificación de Hardcodeo

- [ ] El método del controlador NO tiene `$contenidoInicial = [...]` hardcodeado
- [ ] Usa `$this->configService->crearContenidoInicial('tipo_documento')`
- [ ] El script SQL fue ejecutado y la configuración existe en BD
- [ ] Las secciones en BD tienen `seccion_key` definido
- [ ] Vista Web y Vista Edición muestran el mismo contenido

---

## NOTAS IMPORTANTES

1. **Prioridad de lectura**: PARTE3 y PARTE4 son obligatorias. PARTE1 y PARTE2 solo si el módulo las necesita.

2. **Backup mental**: Antes de modificar, entender completamente qué hace el código actual.

3. **Pruebas incrementales**: Reparar una cosa a la vez y probar antes de continuar.

4. **Compatibilidad hacia atrás**: Los documentos ya generados deben seguir funcionando.

5. **Registro en Factory**: Es el punto crítico - sin esto, el sistema no encuentra la clase.

---

## CONTACTO

Documentación creada para Enterprise SST
Última actualización: Febrero 2026
