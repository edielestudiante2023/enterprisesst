# Numerales que Requieren PROGRAMAS con Objetivos, Actividades e Indicadores

## Fundamento Normativo

Basado en la **Resolución 0312 de 2019** y el **Decreto 1072 de 2015**, solo ciertos numerales del SG-SST requieren gestión programática. La norma NO exige que todos los numerales se gestionen mediante programas, objetivos e indicadores.

Un numeral requiere PROGRAMA únicamente cuando:
- Implica intervención preventiva o de control
- Contiene actividades periódicas o recurrentes
- Requiere planificación (cronograma)
- Exige seguimiento y evaluación
- Se orienta a la mejora continua

---

## Los 9 Numerales que SÍ llevan PROGRAMA

| Numeral | Nombre del Programa | tipo_documento en BD |
|---------|---------------------|----------------------|
| 1.2.1 | Programa de Capacitación en PYP | `programa_capacitacion` |
| 1.2.2 | Programa de Inducción y Reinducción | `programa_induccion_reinduccion` |
| 3.1.2 | Programa de Promoción y Prevención en Salud | `programa_promocion_prevencion_salud` |
| 3.1.7 | Programa de Estilos de Vida Saludables | `programa_estilos_vida_saludables` |
| 4.2.3 | Programa de Seguridad | `programa_seguridad` |
| 4.2.4 | Programa de Inspecciones | `programa_inspecciones` |
| 4.2.5 | Programa de Mantenimiento | `programa_mantenimiento` |
| 5.1.1 | Plan de Emergencias | `plan_emergencias` |
| 5.1.2 | Programa de Brigada de Emergencias | `programa_brigada` |

---

## Estructura de un PROGRAMA

Cada programa debe contener las siguientes secciones:

| # | Sección | Descripción | Sincroniza con BD |
|---|---------|-------------|-------------------|
| 1 | Introducción | Contexto y justificación del programa | No |
| 2 | Objetivo General | Propósito principal del programa | No |
| 3 | Objetivos Específicos | Metas puntuales a alcanzar | No |
| 4 | Alcance | A quién aplica el programa | No |
| 5 | Marco Legal | Normatividad aplicable | No |
| 6 | Definiciones | Términos clave | No |
| 7 | Responsabilidades | Roles y funciones | No |
| 8 | Metodología | Cómo se ejecuta el programa | No |
| 9 | Cronograma/Actividades | **Actividades planificadas** | **SÍ → tbl_pta_cliente** |
| 10 | Indicadores | **Métricas de seguimiento** | **SÍ → tbl_indicadores_sst** |
| 11 | Recursos | Humanos, físicos, financieros | No |
| 12 | Evaluación y Seguimiento | Cómo se evalúa el programa | No |

---

## Flujo de Generación y Aprobación

```
┌─────────────────────────────────────────────────────────────────┐
│                    FLUJO PARA PROGRAMAS                         │
└─────────────────────────────────────────────────────────────────┘

1. GENERAR DOCUMENTO CON IA
   └─► El sistema genera todas las secciones
   └─► Sección "Actividades": IA idea actividades según el programa
   └─► Sección "Indicadores": IA idea indicadores según el programa
                    ↓
2. USUARIO REVISA Y EDITA
   └─► Puede modificar cualquier sección
   └─► Aprueba cada sección individualmente
                    ↓
3. USUARIO APRUEBA DOCUMENTO COMPLETO
                    ↓
4. SISTEMA DETECTA QUE ES UN PROGRAMA (está en lista de 9)
                    ↓
5. SISTEMA PARSEA SECCIONES ESPECIALES:
   ┌────────────────────────────────────┐
   │ Sección "Actividades"              │
   │ └─► Extrae cada actividad          │
   │ └─► INSERT en tbl_pta_cliente      │
   │     - id_cliente                   │
   │     - tipo_servicio = programa     │
   │     - numeral_plandetrabajo        │
   │     - actividad_plandetrabajo      │
   │     - fecha_propuesta              │
   │     - estado_actividad = 'ABIERTA' │
   └────────────────────────────────────┘
   ┌────────────────────────────────────┐
   │ Sección "Indicadores"              │
   │ └─► Extrae cada indicador          │
   │ └─► INSERT en tbl_indicadores_sst  │
   │     - id_cliente                   │
   │     - nombre_indicador             │
   │     - tipo_indicador               │
   │     - categoria = programa         │
   │     - formula                      │
   │     - meta                         │
   │     - periodicidad                 │
   └────────────────────────────────────┘
                    ↓
6. DOCUMENTO APROBADO + DATOS EN BD
   └─► Las actividades aparecen en el PTA del cliente
   └─► Los indicadores aparecen en el módulo de KPI
```

---

## Configuración en Base de Datos

### 1. Marcar tipo de documento como PROGRAMA

En `tbl_doc_tipo_configuracion`, agregar columna o usar `categoria = 'programas'`:

```sql
-- Verificar qué tipos son PROGRAMAS
SELECT tipo_documento, nombre, categoria, estandar
FROM tbl_doc_tipo_configuracion
WHERE categoria = 'programas'
ORDER BY estandar;
```

### 2. Marcar secciones que sincronizan con BD

En `tbl_doc_secciones_config`, usar columna `sincronizar_bd`:

```sql
-- Ejemplo: marcar sección de actividades para sincronizar
UPDATE tbl_doc_secciones_config
SET sincronizar_bd = 'pta_cliente'
WHERE seccion_key = 'cronograma' OR seccion_key = 'actividades';

-- Ejemplo: marcar sección de indicadores para sincronizar
UPDATE tbl_doc_secciones_config
SET sincronizar_bd = 'indicadores_sst'
WHERE seccion_key = 'indicadores';
```

### 3. Consulta para saber si un documento es PROGRAMA

```sql
SELECT
    tc.tipo_documento,
    tc.nombre,
    tc.estandar,
    CASE
        WHEN tc.categoria = 'programas' THEN 'SÍ genera actividades e indicadores'
        ELSE 'NO genera actividades e indicadores'
    END as comportamiento
FROM tbl_doc_tipo_configuracion tc
WHERE tc.tipo_documento = :tipo_documento;
```

---

## Numerales que NO llevan PROGRAMA

Todos los demás numerales del SG-SST NO requieren programas ni indicadores. Su cumplimiento se demuestra mediante:

| Tipo de Evidencia | Ejemplos |
|-------------------|----------|
| Políticas | Política SST (1.1.1) |
| Procedimientos | Control documental (2.5.1), Investigación incidentes (4.2.1) |
| Matrices | Requisitos legales (1.2.1), Identificación peligros (2.4.1) |
| Registros | Afiliaciones SGRL, Exámenes médicos |
| Actas | Reuniones COPASST, Revisión por la dirección |
| Designaciones | Responsable SG-SST, Vigía SST |
| Reportes | Investigaciones de accidentes |

Para estos numerales:
- Se genera documento con IA (si aplica)
- NO tienen secciones de actividades/indicadores
- Al aprobar → solo se aprueba el documento

---

## Implementación en Código

### En `DocumentosSSTController::aprobarDocumento()`

```php
// Después de aprobar el documento, verificar si es un PROGRAMA
$tipoDocumento = $documento['tipo_documento'];

// Obtener configuración del tipo
$tipoConfig = $this->configService->obtenerTipoDocumento($tipoDocumento);

// Si es categoría "programas", sincronizar con BD
if ($tipoConfig['categoria'] === 'programas') {
    $this->sincronizarActividadesIndicadores($documento);
}
```

### Método `sincronizarActividadesIndicadores()`

```php
protected function sincronizarActividadesIndicadores(array $documento): array
{
    $resultado = ['actividades' => 0, 'indicadores' => 0];
    $contenido = json_decode($documento['contenido'], true);

    foreach ($contenido['secciones'] as $seccion) {
        $key = $seccion['key'] ?? '';

        // Sincronizar actividades
        if (in_array($key, ['cronograma', 'actividades', 'plan_trabajo'])) {
            $actividades = $this->parsearActividades($seccion['contenido']);
            foreach ($actividades as $act) {
                $this->insertarActividadPTA($documento, $act);
                $resultado['actividades']++;
            }
        }

        // Sincronizar indicadores
        if ($key === 'indicadores') {
            $indicadores = $this->parsearIndicadores($seccion['contenido']);
            foreach ($indicadores as $ind) {
                $this->insertarIndicador($documento, $ind);
                $resultado['indicadores']++;
            }
        }
    }

    return $resultado;
}
```

---

## Notas Importantes

### 1. Programa de Capacitación (1.2.1) - Caso Especial

Este programa YA tiene un flujo diferente implementado:
- Usa el módulo **Generador IA** para crear cronograma, PTA e indicadores ANTES
- El documento LEE esos datos de la BD

**Decisión**: Mantener este flujo especial o migrarlo al flujo estándar de programas.

### 2. Formato de Secciones para Parseo

Para que el sistema pueda parsear las actividades e indicadores, deben estar en formato estructurado:

**Actividades (formato tabla markdown):**
```markdown
| Actividad | Responsable | Fecha | PHVA |
|-----------|-------------|-------|------|
| Capacitación X | Resp. SST | Marzo 2026 | HACER |
| Inspección Y | COPASST | Mensual | VERIFICAR |
```

**Indicadores (formato lista):**
```markdown
**Nombre del Indicador**
- Fórmula: (Ejecutadas / Programadas) x 100
- Meta: 90%
- Periodicidad: Trimestral
```

### 3. Evitar Duplicados

Al sincronizar, verificar si la actividad/indicador ya existe para no duplicar:

```php
// Verificar antes de insertar
$existe = $this->db->table('tbl_pta_cliente')
    ->where('id_cliente', $idCliente)
    ->where('id_documento_origen', $idDocumento)
    ->where('actividad_plandetrabajo', $nombreActividad)
    ->countAllResults();

if ($existe === 0) {
    // INSERT
}
```

---

## Referencias

- [PROMPT_NUEVO_DOCUMENTO_SST.md](../../PROMPT_NUEVO_DOCUMENTO_SST.md) - Guía para crear documentos
- [TROUBLESHOOTING_GENERACION_IA.md](./TROUBLESHOOTING_GENERACION_IA.md) - Solución de problemas
- Resolución 0312 de 2019
- Decreto 1072 de 2015

---

**Última actualización:** Febrero 2026
