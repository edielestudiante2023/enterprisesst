# Módulo de Recomposición de Comités SST

## Fecha: 2026-02-04
## Versión: 1.0

---

## Resumen Ejecutivo

El módulo de **Recomposición de Comités** permite gestionar el reemplazo de miembros de los comités de SST (COPASST, COCOLAB, Brigada, Vigía) cuando alguno de sus integrantes debe ser retirado durante la vigencia del período.

**Problema que resuelve:** Cuando un miembro del comité renuncia, es despedido, o debe salir por cualquier causal, el comité debe mantener su conformación. Este módulo automatiza el proceso de reemplazo sin necesidad de realizar un nuevo proceso electoral completo.

---

## Marco Normativo

### Causales de Retiro de Miembros

Según la normatividad vigente, un miembro puede ser retirado por:

| Causal | Descripción | Aplica a |
|--------|-------------|----------|
| `terminacion_contrato` | Terminación del contrato de trabajo | Todos |
| `renuncia_voluntaria` | Renuncia voluntaria al comité | Todos |
| `sancion_disciplinaria` | Sanción disciplinaria por falta grave | Todos |
| `violacion_confidencialidad` | Violación del deber de confidencialidad | COCOLAB |
| `inasistencia_reiterada` | Inasistencia a más de 3 reuniones consecutivas sin justificación | Todos |
| `incumplimiento_funciones` | Incumplimiento reiterado de obligaciones | Todos |
| `fallecimiento` | Fallecimiento del miembro | Todos |
| `otro` | Otro motivo documentado | Todos |

### Mecanismos de Reemplazo

| Tipo de Representante | Mecanismo de Reemplazo |
|----------------------|------------------------|
| **Trabajador** | Ingresa el siguiente candidato con mayor votación que no fue elegido en el proceso electoral original |
| **Empleador** | El empleador designa directamente al nuevo representante |
| **Sin candidatos disponibles** | Se debe realizar asamblea extraordinaria de trabajadores |

---

## Arquitectura del Módulo

### Estructura de Base de Datos

#### Tabla: `tbl_recomposiciones_comite`

```sql
CREATE TABLE `tbl_recomposiciones_comite` (
    `id_recomposicion` INT AUTO_INCREMENT PRIMARY KEY,
    `id_proceso` INT NOT NULL,              -- FK a tbl_procesos_electorales
    `id_cliente` INT NOT NULL,              -- FK a tbl_cliente
    `fecha_recomposicion` DATE NOT NULL,
    `numero_recomposicion` INT DEFAULT 1,   -- Secuencial por proceso
    `id_candidato_saliente` INT NOT NULL,   -- FK a tbl_candidatos_comite
    `motivo_salida` ENUM(...) NOT NULL,
    `motivo_detalle` TEXT,
    `fecha_efectiva_salida` DATE NOT NULL,
    `id_candidato_entrante` INT DEFAULT NULL,
    `tipo_ingreso` ENUM('siguiente_votacion', 'designacion_empleador', 'asamblea_extraordinaria'),
    -- Campos para nuevos miembros (empleador o asamblea)
    `entrante_nombres` VARCHAR(100),
    `entrante_apellidos` VARCHAR(100),
    `entrante_documento` VARCHAR(20),
    `entrante_cargo` VARCHAR(100),
    `entrante_email` VARCHAR(150),
    `entrante_telefono` VARCHAR(20),
    `estado` ENUM('borrador', 'pendiente_firmas', 'firmado', 'cancelado'),
    `id_documento` INT DEFAULT NULL,
    `observaciones` TEXT,
    `justificacion_legal` TEXT,
    `created_by` INT,
    `created_at` DATETIME,
    `updated_at` DATETIME
);
```

#### Columnas Agregadas a `tbl_candidatos_comite`

```sql
ALTER TABLE `tbl_candidatos_comite`
    ADD COLUMN `estado_miembro` ENUM('activo', 'retirado', 'reemplazado') DEFAULT 'activo',
    ADD COLUMN `fecha_ingreso_comite` DATE,
    ADD COLUMN `fecha_retiro_comite` DATE,
    ADD COLUMN `es_recomposicion` TINYINT(1) DEFAULT 0,
    ADD COLUMN `id_recomposicion_ingreso` INT,
    ADD COLUMN `posicion_votacion` INT;
```

### Estructura de Archivos

```
app/
├── Controllers/
│   └── ComitesEleccionesController.php    # Métodos de recomposición (líneas 3225+)
│
├── Views/comites_elecciones/
│   ├── recomposicion/
│   │   ├── nueva.php          # Formulario para nueva recomposición
│   │   ├── listar.php         # Lista de recomposiciones del proceso
│   │   ├── ver.php            # Detalle de una recomposición
│   │   └── acta_pdf.php       # Plantilla PDF del acta
│   └── ver_proceso.php        # Botón de acceso (línea ~745)
│
├── SQL/
│   ├── crear_modulo_recomposicion.sql     # SQL para crear tablas
│   └── ejecutar_modulo_recomposicion.php  # Script PHP para ejecutar SQL
│
└── Config/
    └── Routes.php              # Rutas del módulo (líneas 980-985)
```

---

## Rutas del Módulo

| Ruta | Método | Controlador | Descripción |
|------|--------|-------------|-------------|
| `/comites-elecciones/proceso/{id}/recomposiciones` | GET | `listarRecomposiciones` | Lista todas las recomposiciones |
| `/comites-elecciones/proceso/{id}/recomposicion/nueva` | GET | `nuevaRecomposicion` | Formulario nueva recomposición |
| `/comites-elecciones/proceso/guardar-recomposicion` | POST | `guardarRecomposicion` | Guardar recomposición |
| `/comites-elecciones/proceso/{id}/recomposicion/{idR}` | GET | `verRecomposicion` | Ver detalle |
| `/comites-elecciones/proceso/{id}/recomposicion/{idR}/acta-pdf` | GET | `generarActaRecomposicionPdf` | Generar PDF |
| `/comites-elecciones/proceso/{id}/siguiente-votacion` | GET | `getSiguienteEnVotacion` | API: siguiente candidato |

---

## Flujo de Uso

### 1. Acceso al Módulo

Desde la vista de un **proceso completado** (`ver_proceso.php`):

1. El consultor ve el estado "Proceso Completado"
2. Debajo aparece la pregunta: "¿Necesita reemplazar algún miembro del comité?"
3. Clic en **"Recomposición del Comité"**

### 2. Listar Recomposiciones

Vista: `/comites-elecciones/proceso/{id}/recomposiciones`

- Muestra todas las recomposiciones del proceso
- Estadísticas: total, firmadas, pendientes, cambios efectivos
- Cards con información del cambio (saliente → entrante)
- Acceso a ver detalle y PDF de cada una

### 3. Nueva Recomposición

Vista: `/comites-elecciones/proceso/{id}/recomposicion/nueva`

**Paso 1: Seleccionar miembro saliente**
- Se muestran todos los miembros activos del comité
- Separados por representación (Trabajadores / Empleador)
- Al seleccionar, se detecta automáticamente el tipo de representación

**Paso 2: Motivo de salida**
- Seleccionar causal de retiro
- Opcional: detalle adicional
- Fechas: salida efectiva y recomposición

**Paso 3: Seleccionar reemplazo**
- **Si es trabajador:** Se muestra lista de candidatos no elegidos ordenados por votos
- **Si es empleador:** Se muestra formulario para ingresar datos del nuevo miembro
- **Si no hay candidatos:** Se habilita opción de asamblea extraordinaria

**Paso 4: Observaciones y guardar**
- Observaciones adicionales
- Justificación legal (pre-llenada según tipo de comité)
- Guardar recomposición

### 4. Ver Detalle

Vista: `/comites-elecciones/proceso/{id}/recomposicion/{idR}`

- Información completa del cambio
- Cuadro visual: Saliente → Entrante
- Motivo y fechas
- Conformación actual del comité con marcas (A)/(B)
- Estado del acta
- Acciones: Ver PDF, solicitar firmas

### 5. Generar Acta PDF

Vista: `/comites-elecciones/proceso/{id}/recomposicion/{idR}/acta-pdf`

El acta incluye:
1. Introducción y fecha de recomposición
2. Funciones del comité (según tipo)
3. Causales de retiro
4. Información del miembro saliente
5. Información del miembro entrante
6. Conformación actual con marcas (A) Continuante / (B) Nuevo
7. Fundamento legal
8. Firmas

---

## Lógica de Negocio

### Obtener Siguiente Candidato en Votación

```php
public function getSiguienteEnVotacion(int $idProceso)
{
    $siguiente = $this->db->table('tbl_candidatos_comite')
        ->where('id_proceso', $idProceso)
        ->where('representacion', 'trabajador')
        ->where('estado', 'no_elegido')
        ->where('estado_miembro', 'activo')
        ->orderBy('posicion_votacion', 'ASC')
        ->limit(1)
        ->get()
        ->getRowArray();

    return $this->response->setJSON(['success' => true, 'siguiente' => $siguiente]);
}
```

### Guardar Recomposición

El proceso de guardado:

1. **Validar proceso:** Solo procesos `completado` o `firmas`
2. **Calcular número secuencial:** Contar recomposiciones anteriores + 1
3. **Registrar miembro saliente:**
   - Cambiar `estado_miembro` a `'retirado'`
   - Registrar `fecha_retiro_comite`
4. **Registrar miembro entrante:**
   - Si es candidato existente: actualizar a `'elegido'`, marcar `es_recomposicion = 1`
   - Si es nuevo: crear registro en `tbl_candidatos_comite`
5. **Crear recomposición:** Insertar en `tbl_recomposiciones_comite`

### Obtener Miembros Actuales con Marcas (A)/(B)

```php
private function obtenerMiembrosComiteActual(int $idProceso, array $recomposicion): array
{
    $miembros = $this->db->table('tbl_candidatos_comite')
        ->where('id_proceso', $idProceso)
        ->whereIn('estado', ['elegido', 'designado'])
        ->where('estado_miembro', 'activo')
        ->orderBy('representacion', 'ASC')
        ->orderBy('tipo_plaza', 'ASC')
        ->get()
        ->getResultArray();

    // Marcar como nuevo si ingresó por esta recomposición
    foreach ($miembros as &$m) {
        $m['es_nuevo'] = ($m['es_recomposicion'] == 1 &&
                         $m['id_recomposicion_ingreso'] == $recomposicion['id_recomposicion']);
    }

    return $miembros;
}
```

---

## Instalación

### Paso 1: Ejecutar SQL

Acceder a: `/ejecutar-sql-recomposicion`

O ejecutar directamente el archivo:
```
app/SQL/ejecutar_modulo_recomposicion.php
```

Esto crea:
- La tabla `tbl_recomposiciones_comite`
- Las columnas adicionales en `tbl_candidatos_comite`
- Los índices necesarios
- Actualiza `posicion_votacion` para candidatos existentes

### Paso 2: Verificar Rutas

Las rutas ya están agregadas en `app/Config/Routes.php` (líneas ~980-985).

### Paso 3: Acceder

1. Ir a cualquier proceso electoral **completado**
2. Buscar el botón "Recomposición del Comité"
3. Comenzar a registrar cambios

---

## Consideraciones Especiales

### Múltiples Recomposiciones

Un mismo proceso puede tener múltiples recomposiciones:
- Se numeran secuencialmente: #1, #2, #3...
- Cada una genera su propia acta
- El historial se mantiene completo

### Candidatos Agotados

Si no hay más candidatos no elegidos:
- El sistema muestra advertencia
- Se habilita la opción "Asamblea extraordinaria"
- El consultor debe ingresar los datos manualmente

### Vigencia

La recomposición aplica solo durante el período del comité:
- Si el período ya venció, se recomienda crear nuevo proceso electoral
- El sistema no valida fechas de vigencia automáticamente

### Integración con Firmas

El módulo está preparado para integrar firmas electrónicas:
- Estado `borrador` → `pendiente_firmas` → `firmado`
- Campo `id_documento` para vincular con sistema de firmas

---

## Anexos

### Códigos de Documento

| Tipo Comité | Código Recomposición |
|-------------|---------------------|
| COCOLAB | FT-SST-155 |
| COPASST | FT-SST-156 |
| BRIGADA | FT-SST-157 |
| VIGIA | FT-SST-158 |

### Estados de Recomposición

| Estado | Descripción |
|--------|-------------|
| `borrador` | Recién creada, en edición |
| `pendiente_firmas` | Enviada para firmas electrónicas |
| `firmado` | Acta firmada completamente |
| `cancelado` | Recomposición anulada |

### Estados de Miembro

| Estado | Descripción |
|--------|-------------|
| `activo` | Miembro activo del comité |
| `retirado` | Salió por recomposición o fin de período |
| `reemplazado` | Fue reemplazado por otro miembro |

---

## Troubleshooting

### Error: "Solo se puede recomponer comités completados"

**Causa:** El proceso no está en estado `completado` o `firmas`.

**Solución:** Completar el proceso electoral primero.

### Error: "No hay candidatos disponibles"

**Causa:** Todos los candidatos no elegidos ya fueron usados en recomposiciones anteriores.

**Solución:** Usar la opción "Asamblea extraordinaria" e ingresar datos manualmente.

### No aparece botón de recomposición

**Causa:** El proceso no está completado o se está viendo en modo histórico.

**Solución:** Asegurarse de que el proceso esté en estado `completado` y no se esté usando el parámetro `?fase=` en la URL.

---

## Changelog

### v1.0 (2026-02-04)
- Versión inicial del módulo
- Soporte para COPASST, COCOLAB, Brigada, Vigía
- Generación de acta PDF
- Tres tipos de ingreso: votación, designación, asamblea
