# ZZ_99 - Fichas Técnicas de Indicadores SST

## 1. Resumen

Las **Fichas Técnicas** son documentos formales que presentan cada indicador del SG-SST con su definición, fórmula, mediciones periódicas, gráfica de tendencia, análisis y plan de acción. Son requeridas para auditorías de la Resolución 0312/2019.

Adicionalmente, la **Matriz de Objetivos y Metas** consolida todos los indicadores en una tabla resumen.

---

## 2. Estructura de la Ficha Técnica (5 Secciones)

### Sección 1: Información del Indicador

| Campo | Fuente BD | Columna |
|-------|-----------|---------|
| Nombre del Indicador | `tbl_indicadores_sst` | `nombre_indicador` |
| Definición | `tbl_indicadores_sst` | `definicion` **NUEVA** |
| Interpretación | `tbl_indicadores_sst` | `interpretacion` **NUEVA** |
| Meta | `tbl_indicadores_sst` | `meta` + `unidad_medida` |
| Fórmula | `tbl_indicadores_sst` | `formula` |
| Frecuencia de Medición | `tbl_indicadores_sst` | `periodicidad` |
| Origen de los Datos | `tbl_indicadores_sst` | `origen_datos` **NUEVA** |
| Responsable de la Medición | `tbl_indicadores_sst` | `cargo_responsable` **NUEVA** |
| Cargos que conocen el resultado | `tbl_indicadores_sst` | `cargos_conocer_resultado` **NUEVA** |
| Tipo de Indicador | `tbl_indicadores_sst` | `tipo_indicador` |
| Numeral / Base Legal | `tbl_indicadores_sst` | `numeral_resolucion` |
| Ciclo PHVA | `tbl_indicadores_sst` | `phva` |

### Sección 2: Tabla de Medición (Dinámica por Periodicidad)

La estructura de columnas cambia según la `periodicidad` del indicador:

#### Mensual (12 columnas + ACUM)
```
| Componente | Ene | Feb | Mar | Abr | May | Jun | Jul | Ago | Sep | Oct | Nov | Dic | ACUM |
|------------|-----|-----|-----|-----|-----|-----|-----|-----|-----|-----|-----|-----|------|
| Numerador  |     |     |     |     |     |     |     |     |     |     |     |     |      |
| Denominador|     |     |     |     |     |     |     |     |     |     |     |     |      |
| Resultado  |     |     |     |     |     |     |     |     |     |     |     |     |      |
| Meta       |     |     |     |     |     |     |     |     |     |     |     |     |      |
```

#### Trimestral (4 columnas + ACUM)
```
| Componente | Trim I | Trim II | Trim III | Trim IV | ACUM |
|------------|--------|---------|----------|---------|------|
| Numerador  |        |         |          |         |      |
| Denominador|        |         |          |         |      |
| Resultado  |        |         |          |         |      |
| Meta       |        |         |          |         |      |
```

#### Semestral (2 columnas + ACUM)
```
| Componente | Sem I | Sem II | ACUM |
|------------|-------|--------|------|
| Numerador  |       |        |      |
| Denominador|       |        |      |
| Resultado  |       |        |      |
| Meta       |       |        |      |
```

#### Anual (1 columna + ACUM)
```
| Componente | Anual | ACUM |
|------------|-------|------|
| Numerador  |       |      |
| Denominador|       |      |
| Resultado  |       |      |
| Meta       |       |      |
```

**Filas fijas (4):**
1. **Numerador**: `valor_numerador` de cada periodo
2. **Denominador**: `valor_denominador` de cada periodo
3. **Resultado**: `valor_resultado` de cada periodo (calculado)
4. **Meta**: valor fijo de `meta` (repetido en cada columna)

**Fuente de datos:** `tbl_indicadores_sst_mediciones` filtrada por `id_indicador` y `periodo` del año.

**Formato de periodo en BD:**
- Mensual: `YYYY-01`, `YYYY-02`, ... `YYYY-12`
- Trimestral: `YYYY-Q1`, `YYYY-Q2`, `YYYY-Q3`, `YYYY-Q4`
- Semestral: `YYYY-S1`, `YYYY-S2`
- Anual: `YYYY`

**Columna ACUM (Acumulado):** Suma o promedio de todos los periodos medidos.

### Sección 3: Gráfica de Tendencia

Gráfico de líneas que compara:
- **Línea azul**: Resultado real por periodo
- **Línea roja punteada**: Meta (línea horizontal constante)
- **Línea gris**: Tendencia (línea de regresión)

Tecnología:
- **Web**: Chart.js (canvas) - ya usado en dashboard
- **PDF**: Imagen base64 generada por Chart.js antes de exportar (via canvas.toDataURL)
- **Word**: Imagen base64 (mismo mecanismo)

### Sección 4: Análisis de Datos

Texto libre de interpretación de resultados. Puede ser:
- **Manual**: Editado por el consultor en campo `analisis_datos` **NUEVO** en `tbl_indicadores_sst`
- **IA-asistido** (futuro): Generado por IA con contexto del indicador y mediciones

### Sección 5: Seguimiento / Plan de Acción

| Campo | Fuente BD |
|-------|-----------|
| ¿Requiere plan de acción? (SI/NO) | `requiere_plan_accion` **NUEVO** TINYINT |
| Número de acción | `numero_accion` **NUEVO** VARCHAR(50) |
| Descripción de la acción | `acciones_mejora` (existente) |
| Observaciones | `observaciones` (existente) |

---

## 3. Columnas Nuevas Requeridas en `tbl_indicadores_sst`

```sql
ALTER TABLE tbl_indicadores_sst
  ADD COLUMN `definicion` TEXT NULL COMMENT 'Definición del indicador para ficha técnica' AFTER `nombre_indicador`,
  ADD COLUMN `interpretacion` TEXT NULL COMMENT 'Cómo interpretar el resultado' AFTER `definicion`,
  ADD COLUMN `origen_datos` VARCHAR(255) NULL COMMENT 'Fuente de los datos (ej: registro de accidentes)' AFTER `interpretacion`,
  ADD COLUMN `cargo_responsable` VARCHAR(255) NULL COMMENT 'Cargo responsable de medir' AFTER `origen_datos`,
  ADD COLUMN `cargos_conocer_resultado` VARCHAR(500) NULL COMMENT 'Cargos que deben conocer el resultado' AFTER `cargo_responsable`,
  ADD COLUMN `analisis_datos` TEXT NULL COMMENT 'Análisis/interpretación textual de la sección 4' AFTER `acciones_mejora`,
  ADD COLUMN `requiere_plan_accion` TINYINT(1) NULL DEFAULT NULL COMMENT '1=SI, 0=NO, NULL=No evaluado' AFTER `analisis_datos`,
  ADD COLUMN `numero_accion` VARCHAR(50) NULL COMMENT 'Código del plan de acción' AFTER `requiere_plan_accion`;
```

**Total: 7 columnas nuevas.**

---

## 4. Nomenclatura y Versionamiento

### Código del Documento
Formato: `FT-IND-{NNN}`
- `FT` = Ficha Técnica
- `IND` = Indicador
- `{NNN}` = Consecutivo con 3 dígitos (001, 002, ...)

Ejemplo: `FT-IND-001` (primer indicador), `FT-IND-018` (indicador 18)

### Nomenclatura alternativa según tipo
- Indicadores de Estructura: `FT-EST-{NNN}`
- Indicadores de Proceso: `FT-PRO-{NNN}`
- Indicadores de Resultado: `FT-RES-{NNN}`

**Decisión:** Usar `FT-IND-{NNN}` unificado (más simple, un solo consecutivo).

### Versión
Formato: `001` (3 dígitos, str_pad con ceros)
- Versión inicial: `001`
- No se usa DocumentoVersionService para fichas técnicas (son reportes periódicos, no documentos versionados)
- La versión se incrementa manualmente si el consultor modifica la ficha

### Vigencia
- Fecha de generación/impresión
- Formato: `dd/mm/YYYY`

---

## 5. Arquitectura de Implementación

### NO es un Documento SST del Factory

Las Fichas Técnicas **NO** se registran en `DocumentoSSTFactory` ni se almacenan en `tbl_documentos_sst`. Razón:
- No son documentos generados por IA con secciones
- Son **reportes formateados** de datos existentes en `tbl_indicadores_sst` + `tbl_indicadores_sst_mediciones`
- El contenido proviene de la BD, no de generación de texto

### Controlador: `IndicadoresSSTController`

Agregar métodos al controlador existente:

```
GET /indicadores-sst/{idCliente}/ficha-tecnica/{idIndicador}
    → fichaTecnica()             # Vista web

GET /indicadores-sst/{idCliente}/ficha-tecnica/{idIndicador}/pdf
    → fichaTecnicaPDF()          # Exportar PDF

GET /indicadores-sst/{idCliente}/ficha-tecnica/{idIndicador}/word
    → fichaTecnicaWord()         # Exportar Word

GET /indicadores-sst/{idCliente}/matriz-objetivos-metas
    → matrizObjetivosMetas()     # Vista web de la matriz

GET /indicadores-sst/{idCliente}/matriz-objetivos-metas/pdf
    → matrizObjetivosMetasPDF()  # Exportar PDF
```

### Vistas Nuevas

```
app/Views/indicadores_sst/
├── ficha_tecnica.php           # Vista web de una ficha técnica
├── ficha_tecnica_pdf.php       # Template PDF (DomPDF)
├── ficha_tecnica_word.php      # Template Word (HTML/DOC)
├── matriz_objetivos_metas.php  # Vista web de la matriz
└── matriz_objetivos_metas_pdf.php  # Template PDF de la matriz
```

### Datos para la Vista (Controller → View)

```php
$data = [
    'indicador'   => [...],           // Datos del indicador
    'mediciones'  => [...],           // Array de mediciones del año
    'cliente'     => [...],           // Datos del cliente
    'contexto'    => [...],           // Contexto SST del cliente
    'consultor'   => [...],           // Datos del consultor
    'anio'        => 2026,            // Año de mediciones
    'periodos'    => [...],           // Periodos según periodicidad
    'chartBase64' => '...',           // Gráfica pre-renderizada (solo PDF/Word)
    'logoBase64'  => '...',           // Logo del cliente
    'consecutivo' => 1,               // Número de ficha
];
```

---

## 6. Renderizado Web (Bootstrap 5)

### Layout General

```
┌─────────────────────────────────────────────────────┐
│ [Toolbar: ← Volver | Exportar PDF | Exportar Word]  │
├─────────────────────────────────────────────────────┤
│ [ENCABEZADO FORMAL]                                  │
│ Logo | SG-SST | FT-IND-001 | Version: 001 | Fecha   │
├─────────────────────────────────────────────────────┤
│ SECCIÓN 1: INFORMACIÓN DEL INDICADOR                 │
│ ┌─────────────────┬────────────────────────────────┐ │
│ │ Nombre          │ Índice de Frecuencia           │ │
│ │ Definición      │ Mide la relación entre...      │ │
│ │ Interpretación  │ A menor valor, mejor gestión   │ │
│ │ Meta            │ ≤ 5.0 por 240,000 HHT         │ │
│ │ Fórmula         │ (Acc/HHT) × 240,000           │ │
│ │ Periodicidad    │ Mensual                        │ │
│ │ Origen Datos    │ Registro de accidentes          │ │
│ │ Responsable     │ Responsable SG-SST             │ │
│ │ Conocen Result. │ Gerencia, COPASST, trabajadores │ │
│ │ Tipo            │ Resultado                      │ │
│ │ Base Legal      │ Art. 30 Res. 0312/2019         │ │
│ │ PHVA            │ Verificar                      │ │
│ └─────────────────┴────────────────────────────────┘ │
├─────────────────────────────────────────────────────┤
│ SECCIÓN 2: MEDICIÓN                                  │
│ [Tabla dinámica según periodicidad - ver Sección 2]  │
├─────────────────────────────────────────────────────┤
│ SECCIÓN 3: GRÁFICA                                   │
│ [Chart.js: Resultado vs Meta vs Tendencia]           │
├─────────────────────────────────────────────────────┤
│ SECCIÓN 4: ANÁLISIS DE DATOS                         │
│ [Texto libre del análisis]                           │
├─────────────────────────────────────────────────────┤
│ SECCIÓN 5: SEGUIMIENTO / PLAN DE ACCIÓN              │
│ ¿Requiere? [SI] [NO]  |  Acción #: ___              │
│ Descripción: ___________                             │
├─────────────────────────────────────────────────────┤
│ [FIRMAS DE APROBACIÓN - 2 firmantes]                 │
│ Elaboró (Consultor) | Aprobó (Rep. Legal)            │
├─────────────────────────────────────────────────────┤
│ [CONTROL DE CAMBIOS]                                 │
│ Version | Descripción | Fecha                        │
└─────────────────────────────────────────────────────┘
```

### Estilos Web (Coherencia con documentos existentes)

| Elemento | Estilo |
|----------|--------|
| Encabezado formal | Tabla 3 columnas (logo, título, info) con bordes Bootstrap |
| Títulos de sección | `bg-primary text-white` con gradiente (como documentos existentes) |
| Tabla Sección 1 | Tabla 2 columnas, TH `bg-light` a la izquierda, TD a la derecha |
| Tabla Sección 2 | `.table .table-bordered .table-sm`, TH `bg-primary text-white` |
| Gráfica | Canvas Chart.js, responsive |
| Sección 4 | Panel con borde, texto justificado |
| Sección 5 | Checkboxes Bootstrap + inputs |
| Firmas | Patrón existente 2 firmantes (Elaboró + Aprobó) |
| Control Cambios | Patrón existente (Versión, Descripción, Fecha) |

---

## 7. Renderizado PDF (DomPDF)

Mismos estilos que los documentos SST existentes:

| Elemento | Valor |
|----------|-------|
| Fuente | DejaVu Sans |
| Body font-size | 10pt |
| Títulos sección | 11pt bold #0d6efd, border-bottom #e9ecef |
| Tablas | 9pt, TH #0d6efd white, TD border #999, padding 5px 8px |
| Firmas | Barra verde #198754, 2 firmantes (Consultor + Rep. Legal) |
| Control Cambios | Barra azul #0d6efd, TH #e9ecef |
| @page | letter, margin 2cm 1.5cm |

### Tabla de Medición PDF (Sección 2)

Para periodicidad **mensual** (caso más ancho):
- Font-size: **8pt** (reducido para caber 14 columnas en letter)
- Padding: 3px 4px
- Ancho columna Componente: 90px fijo
- Ancho columnas periodo: auto (flexible)
- Ancho ACUM: auto con bold

Para periodicidad **trimestral/semestral/anual**:
- Font-size: 9pt normal
- Más espacio por columna

### Gráfica PDF
- Imagen base64 generada en JS antes de enviar a backend
- `<img src="data:image/png;base64,..." style="max-width: 100%; height: auto;">`
- Alternativa: librería PHP para Chart (PhpSpreadsheet Chart, etc.)

---

## 8. Renderizado Word (HTML/DOC)

Mismos estilos que Word template existente:

| Elemento | Valor |
|----------|-------|
| Fuente | Arial, sans-serif |
| Body font-size | 10pt |
| line-height | 1.0, mso-line-height-rule: exactly |
| Títulos sección | 11pt bold #0d6efd, border-bottom #ccc |
| Tablas | 9pt, border #999, padding 3px 5px |
| Firmas | 2 firmantes, padding 5px, height 45px |
| Directivas MSO | w:View Print, w:Zoom 100 |

---

## 9. Matriz de Objetivos y Metas

### Estructura

Tabla resumen de TODOS los indicadores del cliente en un año:

```
| N° | Política | Objetivo | Indicador | Tipo | Meta | Periodicidad | Q1 | Q2 | Q3 | Q4 | Cumple |
|----|----------|----------|-----------|------|------|--------------|----|----|----|-----|--------|
| 1  | SST      | Reducir  | IF        | Res  | ≤5   | Mensual      | 3.2| 4.1| 2.8| 3.5 | SI     |
| 2  | SST      | Reducir  | IS        | Res  | ≤50  | Mensual      | 45 | 38 | 42 | 35  | SI     |
```

### Columnas de la Matriz

| Columna | Fuente | Ancho PDF |
|---------|--------|-----------|
| N° | Consecutivo | 30px |
| Política SST | Texto fijo: "Política SST" | 80px |
| Objetivo | `nombre_indicador` (abreviado) | flexible |
| Indicador | `formula` (abreviado) | flexible |
| Tipo | `tipo_indicador` (E/P/R) | 40px |
| Meta | `meta` + `unidad_medida` | 60px |
| Periodicidad | `periodicidad` | 60px |
| Periodos (Q1-Q4 o M1-M12) | `tbl_indicadores_sst_mediciones` | variable |
| Cumple | `cumple_meta` (SI/NO) | 50px |

### Nomenclatura Matriz
- Código: `MA-SST-OBJ`
- Nombre: "Matriz de Objetivos y Metas del SG-SST"
- Versión: `001`

---

## 10. Auto-poblado de Campos Ficha Técnica

Los 18 indicadores legales del `INDICADORES_LEGALES` constant pueden incluir valores por defecto para las columnas nuevas:

| Indicador | definicion | interpretacion | origen_datos | cargo_responsable |
|-----------|-----------|---------------|-------------|------------------|
| IF | Mide frecuencia de accidentes por HHT | A menor valor, menor accidentalidad | FURAT, registro de accidentes | Responsable SG-SST |
| IS | Mide severidad por días perdidos/HHT | A menor valor, menor severidad | FURAT, incapacidades | Responsable SG-SST |
| PATM | Proporción de accidentes mortales | Debe ser 0, cualquier valor > 0 es crítico | FURAT, ARL | Responsable SG-SST |
| PEL | Casos de enfermedad laboral / expuestos | A menor valor, mejor prevención | Diagnósticos médicos, ARL | Responsable SG-SST |
| IEL | Nuevos casos enfermedad laboral / expuestos | A menor valor, mejor control | Diagnósticos médicos, ARL | Responsable SG-SST |
| ACM | Días ausencia causa médica / HHT | A menor valor, menor ausentismo | Incapacidades, RRHH | Responsable SG-SST |

---

## 11. Flujo de Usuario

### Generar Ficha Técnica Individual

1. Usuario va a **Indicadores SST** → Lista de indicadores
2. En cada indicador, nuevo botón: **📄 Ficha Técnica**
3. Se abre vista web con las 5 secciones
4. Si faltan campos (definición, interpretación), muestra alerta para completar
5. Botones superiores: **Exportar PDF** | **Exportar Word**

### Generar Fichas Masivamente

1. En el **Dashboard de Indicadores**, botón: **📋 Generar Todas las Fichas**
2. Genera un PDF/ZIP con todas las fichas técnicas del año
3. Alternativa: PDF multi-página con salto de página entre fichas

### Ver Matriz de Objetivos y Metas

1. En el **Dashboard de Indicadores**, botón: **📊 Matriz Objetivos y Metas**
2. Vista web con tabla resumen
3. Exportar PDF / Word

---

## 12. Tabla de Mediciones - Mapeo de Periodos

### Función para generar columnas dinámicas

```php
function getPeriodosParaPeriodicidad(string $periodicidad, int $anio): array
{
    switch ($periodicidad) {
        case 'mensual':
            return [
                ['periodo' => "{$anio}-01", 'label' => 'Ene'],
                ['periodo' => "{$anio}-02", 'label' => 'Feb'],
                ['periodo' => "{$anio}-03", 'label' => 'Mar'],
                ['periodo' => "{$anio}-04", 'label' => 'Abr'],
                ['periodo' => "{$anio}-05", 'label' => 'May'],
                ['periodo' => "{$anio}-06", 'label' => 'Jun'],
                ['periodo' => "{$anio}-07", 'label' => 'Jul'],
                ['periodo' => "{$anio}-08", 'label' => 'Ago'],
                ['periodo' => "{$anio}-09", 'label' => 'Sep'],
                ['periodo' => "{$anio}-10", 'label' => 'Oct'],
                ['periodo' => "{$anio}-11", 'label' => 'Nov'],
                ['periodo' => "{$anio}-12", 'label' => 'Dic'],
            ];
        case 'trimestral':
            return [
                ['periodo' => "{$anio}-Q1", 'label' => 'Trim I'],
                ['periodo' => "{$anio}-Q2", 'label' => 'Trim II'],
                ['periodo' => "{$anio}-Q3", 'label' => 'Trim III'],
                ['periodo' => "{$anio}-Q4", 'label' => 'Trim IV'],
            ];
        case 'semestral':
            return [
                ['periodo' => "{$anio}-S1", 'label' => 'Sem I'],
                ['periodo' => "{$anio}-S2", 'label' => 'Sem II'],
            ];
        case 'anual':
            return [
                ['periodo' => "{$anio}", 'label' => 'Anual'],
            ];
    }
}
```

### Mapeo mediciones a periodos

```php
// Indexar mediciones por periodo
$medicionesPorPeriodo = [];
foreach ($mediciones as $m) {
    $medicionesPorPeriodo[$m['periodo']] = $m;
}

// Rellenar tabla
foreach ($periodos as $p) {
    $m = $medicionesPorPeriodo[$p['periodo']] ?? null;
    // $m puede ser null (periodo sin medición)
}
```

---

## 13. Semáforo de Cumplimiento en Tabla de Medición

| Condición | Color | Badge |
|-----------|-------|-------|
| Resultado cumple meta | Verde `#198754` | Cumple |
| Resultado no cumple meta | Rojo `#dc3545` | No Cumple |
| Sin medición | Gris `#6c757d` | — |

### Lógica de cumplimiento

```php
// Para indicadores donde menor es mejor (IF, IS, ACM, etc.)
$cumple = ($resultado !== null && $resultado <= $meta);

// Para indicadores donde mayor es mejor (% cumplimiento PTA, etc.)
$cumple = ($resultado !== null && $resultado >= $meta);
```

**Nota:** El campo `cumple_meta` en BD ya almacena esta evaluación. El sistema debe respetar lo que almacenó el registro de medición.

---

## 14. Archivos a Crear/Modificar

### Nuevos

| Archivo | Propósito |
|---------|-----------|
| `app/Views/indicadores_sst/ficha_tecnica.php` | Vista web |
| `app/Views/indicadores_sst/ficha_tecnica_pdf.php` | Template PDF |
| `app/Views/indicadores_sst/ficha_tecnica_word.php` | Template Word |
| `app/Views/indicadores_sst/matriz_objetivos_metas.php` | Vista web matriz |
| `app/Views/indicadores_sst/matriz_objetivos_metas_pdf.php` | Template PDF matriz |
| `app/SQL/agregar_columnas_ficha_tecnica.php` | Migración BD |

### Modificar

| Archivo | Cambio |
|---------|--------|
| `app/Controllers/IndicadoresSSTController.php` | Agregar 5 métodos nuevos |
| `app/Models/IndicadorSSTModel.php` | Agregar columnas a `$allowedFields`, método `getMedicionesAnio()` |
| `app/Config/Routes.php` | Agregar 5 rutas nuevas |
| `app/Views/indicadores_sst/index.php` | Agregar botón "Ficha Técnica" en cada indicador |
| `app/Views/indicadores_sst/dashboard.php` | Agregar botones "Fichas" y "Matriz" |
| `app/Views/indicadores_sst/formulario.php` | Agregar campos nuevos (definicion, interpretacion, etc.) |

---

## 15. Rutas Nuevas

```php
// Ficha Técnica individual
$routes->get('indicadores-sst/(:num)/ficha-tecnica/(:num)', 'IndicadoresSSTController::fichaTecnica/$1/$2');
$routes->get('indicadores-sst/(:num)/ficha-tecnica/(:num)/pdf', 'IndicadoresSSTController::fichaTecnicaPDF/$1/$2');
$routes->get('indicadores-sst/(:num)/ficha-tecnica/(:num)/word', 'IndicadoresSSTController::fichaTecnicaWord/$1/$2');

// Matriz de Objetivos y Metas
$routes->get('indicadores-sst/(:num)/matriz-objetivos-metas', 'IndicadoresSSTController::matrizObjetivosMetas/$1');
$routes->get('indicadores-sst/(:num)/matriz-objetivos-metas/pdf', 'IndicadoresSSTController::matrizObjetivosMetasPDF/$1');
```

---

## 16. Orden de Implementación

1. **SQL**: Migración de columnas nuevas (LOCAL + PROD)
2. **Model**: Actualizar `$allowedFields` + método `getMedicionesAnio()`
3. **Formulario**: Agregar campos nuevos en formulario de indicadores
4. **INDICADORES_LEGALES**: Actualizar constante con valores por defecto para campos nuevos
5. **Vista Web Ficha**: `ficha_tecnica.php` con Chart.js
6. **Vista PDF Ficha**: `ficha_tecnica_pdf.php` con DomPDF
7. **Vista Word Ficha**: `ficha_tecnica_word.php`
8. **Controller**: Métodos fichaTecnica/PDF/Word
9. **Routes**: Registrar rutas
10. **UI**: Botones en index.php y dashboard.php
11. **Matriz**: Vista web + PDF
12. **Pruebas**: Generar fichas con datos reales

---

## 17. Consideraciones de Tamaño para PDF

### Periodicidad Mensual (Caso Crítico)

Tabla de 14 columnas (1 label + 12 meses + 1 ACUM) en tamaño carta:
- Ancho útil: ~18cm (letter con margins 1.5cm cada lado)
- Columna label: ~3cm
- 13 columnas restantes: ~1.15cm cada una
- **Font-size: 7-8pt** obligatorio para caber

### Solución: Orientación Horizontal

Para fichas mensuales, usar `@page { size: letter landscape; }` o dividir la tabla en 2 semestres.

**Opción recomendada:** Orientación landscape para periodicidad mensual.

```php
// En el template PDF
$orientacion = ($indicador['periodicidad'] === 'mensual') ? 'landscape' : 'portrait';
```

---

## 18. Coherencia con Sistema de Documentos

Aunque las fichas técnicas NO usan el Factory de documentos SST, SÍ deben mantener coherencia visual:

| Aspecto | Ficha Técnica | Documentos SST |
|---------|--------------|----------------|
| Encabezado | Mismo formato (Logo + Título + Info) | ✓ Idéntico |
| Fuente PDF | DejaVu Sans | ✓ Idéntico |
| Fuente Word | Arial | ✓ Idéntico |
| Colores | Misma paleta Bootstrap | ✓ Idéntico |
| Firmas | 2 firmantes (Consultor + Rep. Legal) | ✓ Compatible |
| Control Cambios | Solo Version 1.0 (es reporte) | ✓ Simplificado |
| Nomenclatura | FT-IND-NNN | Diferente código pero misma estructura |
