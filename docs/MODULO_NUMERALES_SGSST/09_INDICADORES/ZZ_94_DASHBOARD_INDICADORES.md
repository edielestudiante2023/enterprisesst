# ZZ_94 - Dashboard Jerárquico de Indicadores SST

## 1. FUNDAMENTO NORMATIVO

### Decreto 1072 de 2015 (Arts. 2.2.4.6.19 a 2.2.4.6.22)

El empleador **debe** definir indicadores para evaluar 3 dimensiones del SG-SST:

| Tipo | Artículo | PHVA | Pregunta clave | Mide |
|------|----------|------|----------------|------|
| **ESTRUCTURA** | 2.2.4.6.20 | Planear | ¿Existen los recursos, políticas y organización? | Disponibilidad (10 aspectos) |
| **PROCESO** | 2.2.4.6.21 | Hacer | ¿Se está ejecutando según lo planeado? | Desarrollo e implementación (9 aspectos) |
| **RESULTADO** | 2.2.4.6.22 | Verificar/Actuar | ¿Se lograron los cambios esperados? | Impacto y cumplimiento (10 aspectos) |

### Resolución 0312 de 2019 (Art. 30) — 6 Indicadores Mínimos Obligatorios

| # | Indicador | Fórmula | Periodicidad | Constante |
|---|-----------|---------|--------------|-----------|
| 1 | Frecuencia de Accidentalidad | (N° AT mes / N° trabajadores mes) × 100 | Mensual | 100 |
| 2 | Severidad de Accidentalidad | (Días incapacidad + días cargados / N° trabajadores) × 100 | Mensual | 100 |
| 3 | Proporción AT Mortales | (AT mortales año / Total AT año) × 100 | Anual | 100 |
| 4 | Prevalencia Enfermedad Laboral | (Casos nuevos + existentes / Promedio trabajadores) × 100.000 | Anual | 100.000 |
| 5 | Incidencia Enfermedad Laboral | (Casos nuevos / Promedio trabajadores) × 100.000 | Anual | 100.000 |
| 6 | Ausentismo por Causa Médica | (Días ausencia / Días programados) × 100 | Mensual | 100 |

### Cadena Causal Legal

```
ESTRUCTURA ──────> PROCESO ──────> RESULTADO
(¿Existe?)         (¿Se ejecuta?)   (¿Funcionó?)
   │                   │                 │
 PLANEAR            HACER          VERIFICAR/ACTUAR
   │                   │                 │
 Políticas,       Tasas de          Tasas de
 recursos,        ejecución,        accidentalidad,
 planes           cobertura         enfermedad,
 definidos        de actividades    cumplimiento
```

> **Dato clave del Decreto:** "En el ejercicio del establecimiento de indicadores se podrán encontrar algunos que son comunes tanto en Estructura, como Proceso y Resultado, como es el caso de los indicadores de los **objetivos de SST**."

---

## 2. JERARQUÍA DE 4 NIVELES (Arquitectura del Dashboard)

```
┌─────────────────────────────────────────────────────────┐
│  NIVEL 1: GAUGE MAESTRO                                 │
│  "Cumplimiento Global del SG-SST"                       │
│  ┌─────────┐                                            │
│  │  78%    │  ← Promedio ponderado de los 3 tipos       │
│  └─────────┘                                            │
├─────────────────────────────────────────────────────────┤
│  NIVEL 2: GAUGES POR TIPO LEGAL (Decreto 1072)         │
│  ┌────────┐  ┌────────┐  ┌────────┐                    │
│  │ESTRUCT.│  │PROCESO │  │RESULT. │                     │
│  │  90%   │  │  72%   │  │  65%   │                     │
│  │Planear │  │ Hacer  │  │Verif.  │                     │
│  └────────┘  └────────┘  └────────┘                     │
├─────────────────────────────────────────────────────────┤
│  NIVEL 3: GAUGES POR CATEGORÍA                          │
│  ┌──────┐┌──────┐┌──────┐┌──────┐┌──────┐┌──────┐     │
│  │Accid.││Capa. ││PTA   ││PVE   ││PyP   ││Obj.  │     │
│  │ 85%  ││ 60%  ││ 80%  ││ 70%  ││ 75%  ││ 50%  │     │
│  └──────┘└──────┘└──────┘└──────┘└──────┘└──────┘     │
│  Cada gauge muestra breakdown E/P/R interno             │
├─────────────────────────────────────────────────────────┤
│  NIVEL 4: INDICADORES INDIVIDUALES                      │
│  ┌─────────────────────────────────────────────┐        │
│  │ Ficha: Frecuencia AT  │ Meta: 1.0 │ Real: 0│        │
│  │ ████████████████░░░░ 100% (cumple)          │        │
│  │ Tendencia: ↗ ──── ────                      │        │
│  └─────────────────────────────────────────────┘        │
└─────────────────────────────────────────────────────────┘
```

### 2.1 Nivel 1 — Gauge Maestro: Cumplimiento Global SG-SST

**Fuente:** Promedio ponderado de los 3 tipos.
**Ponderación sugerida (alineada con Res. 0312):**

| Tipo | Peso | Justificación |
|------|------|---------------|
| Estructura | 25% | PLANEAR — precondiciones del sistema |
| Proceso | 35% | HACER — ejecución del plan |
| Resultado | 40% | VERIFICAR/ACTUAR — impacto real |

> Los pesos reflejan que la Resolución 0312 asigna más valor a la verificación del funcionamiento (VERIFICAR = 25%, ACTUAR = 10% del total de estándares).

**Fórmula:**
```
Global = (Estructura × 0.25) + (Proceso × 0.35) + (Resultado × 0.40)
```

**Visual:** Gauge semicircular grande con:
- Arco de color degradado (rojo → amarillo → verde)
- Número central en grande (ej: "78%")
- Etiqueta "Cumplimiento Global SG-SST"
- Semáforo: < 60% rojo, 60-85% amarillo, > 85% verde
- Tooltip con desglose E/P/R

### 2.2 Nivel 2 — 3 Gauges por Tipo Legal

Cada gauge representa un tipo del Decreto 1072:

**ESTRUCTURA (Art. 2.2.4.6.20)**
- Color dominante: `#3498db` (azul — planificación)
- Ícono: `bi-building-gear`
- Fórmula: `(indicadores_estructura_que_cumplen / total_estructura) × 100`
- Aspecto clave: Binary check — ¿el recurso/política/plan EXISTE?

**PROCESO (Art. 2.2.4.6.21)**
- Color dominante: `#f39c12` (naranja — ejecución)
- Ícono: `bi-gear-wide-connected`
- Fórmula: `(indicadores_proceso_que_cumplen / total_proceso) × 100`
- Aspecto clave: Tasas de ejecución — ¿SE ESTÁ HACIENDO?

**RESULTADO (Art. 2.2.4.6.22)**
- Color dominante: `#27ae60` (verde — logro)
- Ícono: `bi-trophy`
- Fórmula: `(indicadores_resultado_que_cumplen / total_resultado) × 100`
- Aspecto clave: Impacto medible — ¿SE LOGRÓ EL OBJETIVO?

**Visual:** 3 gauges semicirculares en fila, cada uno con:
- Porcentaje central
- Barra inferior mostrando: `X cumplen / Y total (Z sin medir)`
- Click para drill-down al Nivel 3 filtrado por ese tipo

### 2.3 Nivel 3 — Gauges por Categoría

Cada categoría existente en `CATEGORIAS` se muestra como un gauge compacto.
Dentro de cada gauge, un **mini stacked bar** horizontal muestra la proporción E/P/R.

**Mapeo Categoría → Marco Normativo:**

| Categoría (BD) | Estándar Res. 0312 | Decreto 1072 Art. | Contiene 6 mín.? |
|-----------------|--------------------|--------------------|-------------------|
| `accidentalidad` | Art. 30 | 2.2.4.6.22 #6,7,8 | **SÍ** (IF, IS, PATM) |
| `ausentismo` | Art. 30 | 2.2.4.6.22 #9 | **SÍ** (ACM) |
| `capacitacion` | 1.2.1 | 2.2.4.6.21 #3 | No |
| `pta` | 2.1.1 | 2.2.4.6.21 #2 | No |
| `objetivos_sgsst` | 2.2.1 | 2.2.4.6.22 #2 | No |
| `vigilancia` | 4.2.3 | 2.2.4.6.21 #4 | **SÍ** (PEL, IEL) |
| `riesgos` | 4.2.1 | 2.2.4.6.21 #4 | No |
| `pyp_salud` | 3.1.2 | 2.2.4.6.21 #5 | No |
| `inspecciones` | 4.2.4 | 2.2.4.6.21 #6 | No |
| `emergencias` | 5.1.1 | 2.2.4.6.20 #9 | No |
| `induccion` | 1.1.4 | 2.2.4.6.20 #10 | No |
| `estilos_vida_saludable` | 3.1.7 | 2.2.4.6.21 #5 | No |
| `evaluaciones_medicas` | 3.1.4 | 2.2.4.6.21 #5 | No |
| `pve_biomecanico` | 4.2.3 | 2.2.4.6.21 #4 | No |
| `pve_psicosocial` | 4.2.3 | 2.2.4.6.21 #4 | No |
| `mantenimiento_periodico` | 4.2.5 | 2.2.4.6.21 #8 | No |

**Visual:** Grid de mini-gauges (3×N) con:
- Gauge circular pequeño con porcentaje
- Nombre de categoría
- Badge "Res. 0312" si contiene indicadores mínimos obligatorios
- Indicador de "completitud de ficha técnica" (¿tiene los 7 campos?)
- Click para expandir Nivel 4

### 2.4 Nivel 4 — Indicadores Individuales (Drill-down)

Al hacer click en un gauge de Nivel 3, se expande un panel con:

**Para cada indicador:**
- **Barra de progreso** con valor_resultado vs meta
- **Semáforo** (cumple/no cumple/sin medir)
- **Sparkline** de tendencia histórica (últimas 4-6 mediciones de `tbl_indicadores_sst_mediciones`)
- **Badge de tipo** (E/P/R)
- **Badge PHVA** (Planear/Hacer/Verificar/Actuar)
- **Fecha última medición**
- **Botón "Medir"** para registrar nueva medición inline

---

## 3. PANEL DE INDICADORES MÍNIMOS OBLIGATORIOS (Res. 0312)

Un panel especial **siempre visible** (posición fija superior o sidebar) que muestra los 6 indicadores mínimos:

```
┌────────────────────────────────────────────────────────────────┐
│  INDICADORES MÍNIMOS - Resolución 0312 de 2019, Art. 30       │
│                                                                │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐                      │
│  │ IF: 0.0  │ │ IS: 0.0  │ │PATM: 0%  │                      │
│  │ Meta: 1  │ │ Meta: 6  │ │ Meta: 0% │                      │
│  │ ✅ CUMPLE │ │ ✅ CUMPLE │ │ ✅ CUMPLE │                      │
│  └──────────┘ └──────────┘ └──────────┘                      │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐                      │
│  │PEL: 1887 │ │IEL: 0    │ │ACM: 0%   │                      │
│  │Meta: 2000│ │Meta: 2000│ │Meta: 15% │                      │
│  │ ✅ CUMPLE │ │ ✅ CUMPLE │ │ ✅ CUMPLE │                      │
│  └──────────┘ └──────────┘ └──────────┘                      │
│                                                                │
│  Cumplimiento Mínimos: 6/6 (100%) ████████████████████ ✅     │
└────────────────────────────────────────────────────────────────┘
```

**Lógica especial:**
- Estos 6 indicadores se identifican por `nombre_indicador` o por una nueva columna `es_minimo_obligatorio`
- Su fórmula y constante son fijas por ley (no editables)
- Semáforo INVERSO para accidentalidad: menor valor = mejor resultado
- IF y IS: "Meta" es un TECHO (valor máximo permitido), no un piso

---

## 4. CONSOLIDADOR: Cumplimiento de Objetivos del SG-SST

El indicador "Cumplimiento de Objetivos" es el **indicador madre** que cruza los 3 tipos:

```
CUMPLIMIENTO DE OBJETIVOS DEL SG-SST
│
├── ESTRUCTURA: ¿Están definidos los objetivos?
│   Fórmula: (Objetivos con ficha técnica / Total objetivos) × 100
│   Meta: 100%
│
├── PROCESO: ¿Se ejecutan las actividades vinculadas?
│   Fórmula: (Actividades PTA ejecutadas / Actividades programadas) × 100
│   Meta: ≥ 90%
│
└── RESULTADO: ¿Se cumplieron los objetivos?
    Fórmula: (Objetivos cumplidos / Total objetivos) × 100
    Meta: ≥ 80%

CONSOLIDADO = Promedio de cumplimiento de indicadores E + P + R
```

**Visual:** Un gauge especial tipo "rosca" con 3 anillos concéntricos:
- Anillo exterior: Estructura (azul)
- Anillo medio: Proceso (naranja)
- Anillo interior: Resultado (verde)
- Centro: % consolidado

---

## 5. ARQUITECTURA TÉCNICA

### 5.1 Decisión: Integrado vs. Universo Aparte

**Recomendación: INTEGRADO** al módulo existente como una nueva vista del mismo controller.

**Razón:**
- Ya existe `IndicadoresSSTController` con `apiObtener()` y `apiVerificar()`
- Ya existe `IndicadorSSTModel` con `getByClienteAgrupados()` y `verificarCumplimiento()`
- Los datos ya están en `tbl_indicadores_sst` y `tbl_indicadores_sst_mediciones`
- Solo falta una capa de presentación dashboard con gauges

### 5.2 Nuevas Rutas

```php
// Dashboard jerárquico de indicadores
$routes->get('indicadores-sst/(:num)/dashboard', 'IndicadoresSSTController::dashboard/$1');

// API para datos del dashboard (JSON)
$routes->get('indicadores-sst/(:num)/api/dashboard', 'IndicadoresSSTController::apiDashboard/$1');

// API para datos de consolidación por tipo
$routes->get('indicadores-sst/(:num)/api/consolidacion', 'IndicadoresSSTController::apiConsolidacion/$1');
```

### 5.3 Nuevo Método en Controller

```
dashboard($idCliente)
├── Verificar permisos
├── Cargar datos del cliente
├── Llamar apiDashboard() internamente para datos iniciales
├── Renderizar vista indicadores_sst/dashboard.php
│
apiDashboard($idCliente)  → JSON
├── nivel1: { global: 78, semaforo: 'warning' }
├── nivel2: {
│     estructura: { valor: 90, total: 10, cumplen: 9, sin_medir: 0 },
│     proceso:    { valor: 72, total: 18, cumplen: 13, sin_medir: 2 },
│     resultado:  { valor: 65, total: 12, cumplen: 8, sin_medir: 1 }
│   }
├── nivel3: {
│     accidentalidad: { valor: 85, estructura: 100, proceso: 80, resultado: 75, es_minimo: true },
│     capacitacion:   { valor: 60, estructura: 80, proceso: 50, resultado: 50, es_minimo: false },
│     ...
│   }
├── minimos_obligatorios: [
│     { nombre: 'IF', valor_real: 0, meta: 1, cumple: true, tendencia: 'estable' },
│     ...
│   ]
└── periodo_actual: '2026'
```

### 5.4 Nuevos Métodos en Modelo (IndicadorSSTModel)

```php
/**
 * Obtener datos consolidados para el dashboard jerárquico
 */
public function getDashboardData(int $idCliente): array
{
    // Nivel 2: Agrupación por tipo_indicador
    // Nivel 3: Agrupación por tipo + categoría
    // Mínimos: Filtro especial por es_minimo_obligatorio = 1
}

/**
 * Calcular consolidación global con ponderación legal
 */
public function getConsolidacionGlobal(int $idCliente): array
{
    // Estructura × 0.25 + Proceso × 0.35 + Resultado × 0.40
}

/**
 * Obtener indicadores mínimos obligatorios (Res. 0312)
 */
public function getMinimosObligatorios(int $idCliente): array
{
    // WHERE es_minimo_obligatorio = 1
    // O por nombres canónicos: IF, IS, PATM, PEL, IEL, ACM
}
```

### 5.5 Cambio en BD (Opcional pero Recomendado)

```sql
-- Agregar campo para identificar indicadores mínimos Res. 0312
ALTER TABLE tbl_indicadores_sst
ADD COLUMN es_minimo_obligatorio TINYINT(1) DEFAULT 0
AFTER numeral_resolucion;

-- Agregar campo para peso/ponderación personalizada
ALTER TABLE tbl_indicadores_sst
ADD COLUMN peso_ponderacion DECIMAL(5,2) DEFAULT NULL
AFTER es_minimo_obligatorio;

-- Índice para consulta rápida de mínimos
CREATE INDEX idx_minimo ON tbl_indicadores_sst(es_minimo_obligatorio, id_cliente);
```

### 5.6 Vista: `indicadores_sst/dashboard.php`

**Stack tecnológico (consistente con el proyecto):**
- Bootstrap 5 (layout, cards, badges, progress bars)
- Bootstrap Icons (íconos)
- Chart.js 4.x (gauges como doughnut semi-circular + sparklines)
- CSS custom (animaciones de gauge, degradados)

**Estructura del layout:**

```
┌──────────────────────────────────────────────────────────────┐
│ NAVBAR: Indicadores SST — [Cliente] — [Volver] [CRUD Index] │
├──────────────────────────────────────────────────────────────┤
│                                                              │
│  ┌─────────── HEADER ───────────┐  ┌─── MÍNIMOS RES.0312 ──┐│
│  │  GAUGE MAESTRO (Nivel 1)     │  │  6 mini-cards          ││
│  │  [████ 78% ████]             │  │  IF IS PATM            ││
│  │  Cumplimiento Global SG-SST  │  │  PEL IEL ACM           ││
│  └──────────────────────────────┘  └─────────────────────────┘│
│                                                              │
│  ┌──────────── NIVEL 2: TIPOS LEGALES ──────────────────────┐│
│  │  [ESTRUCTURA 90%]  [PROCESO 72%]  [RESULTADO 65%]        ││
│  │   10 indic.         18 indic.      12 indic.             ││
│  │   9 cumplen         13 cumplen     8 cumplen             ││
│  └──────────────────────────────────────────────────────────┘│
│                                                              │
│  ┌──────────── NIVEL 3: CATEGORÍAS ─────────────────────────┐│
│  │  [Accid. 85%] [Capac. 60%] [PTA 80%] [PVE 70%]         ││
│  │  [PyP 75%] [Obj.SST 50%] [Inducción 90%] [...]         ││
│  │                                                          ││
│  │  ▼ DRILL-DOWN (Nivel 4) — Categoría seleccionada        ││
│  │  ┌───────────────────────────────────────────────────┐   ││
│  │  │ Indicador 1: IF ████████░░ 0/1 ✅ 📈             │   ││
│  │  │ Indicador 2: IS ████████░░ 0/6 ✅ 📈             │   ││
│  │  │ Indicador 3: PATM ████░░░░ 0/0 ✅ 📊             │   ││
│  │  └───────────────────────────────────────────────────┘   ││
│  └──────────────────────────────────────────────────────────┘│
│                                                              │
│  ┌──────────── CONSOLIDADOR (Nivel Especial) ───────────────┐│
│  │  ROSCA 3 ANILLOS: Cumplimiento de Objetivos SG-SST      ││
│  │  [E: 100%] [P: 72%] [R: 65%] → Consolidado: 76%        ││
│  └──────────────────────────────────────────────────────────┘│
└──────────────────────────────────────────────────────────────┘
```

---

## 6. GAUGE TÉCNICO: Chart.js como Semicircular Gauge

Chart.js no tiene un tipo "gauge" nativo, pero se simula con doughnut:

```javascript
// Plugin para texto central
const centerTextPlugin = {
    id: 'centerText',
    afterDraw(chart) {
        const { ctx, width, height } = chart;
        const text = chart.config.options.plugins.centerText?.text || '';
        const subtext = chart.config.options.plugins.centerText?.subtext || '';
        ctx.save();
        // Texto principal (porcentaje)
        ctx.font = 'bold 2rem Segoe UI';
        ctx.fillStyle = chart.config.options.plugins.centerText?.color || '#333';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(text, width / 2, height / 2 - 10);
        // Subtexto (label)
        ctx.font = '0.75rem Segoe UI';
        ctx.fillStyle = '#6c757d';
        ctx.fillText(subtext, width / 2, height / 2 + 15);
        ctx.restore();
    }
};

// Gauge semicircular
function crearGauge(canvasId, valor, meta, label, color) {
    const cumple = valor >= meta;
    return new Chart(document.getElementById(canvasId), {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [valor, Math.max(0, 100 - valor)],
                backgroundColor: [color, '#e9ecef'],
                borderWidth: 0
            }]
        },
        options: {
            rotation: -90,          // Empezar desde abajo-izquierda
            circumference: 180,     // Solo media circunferencia
            cutout: '75%',          // Grosor del arco
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false },
                tooltip: { enabled: false },
                centerText: {
                    text: valor + '%',
                    subtext: label,
                    color: cumple ? '#27ae60' : (valor >= 50 ? '#f39c12' : '#e74c3c')
                }
            }
        },
        plugins: [centerTextPlugin]
    });
}
```

### Sparklines para tendencia (Chart.js line mini)

```javascript
function crearSparkline(canvasId, datos, meta) {
    return new Chart(document.getElementById(canvasId), {
        type: 'line',
        data: {
            labels: datos.map(d => d.periodo),
            datasets: [
                {
                    data: datos.map(d => d.valor_resultado),
                    borderColor: '#3498db',
                    borderWidth: 2,
                    pointRadius: 2,
                    fill: false,
                    tension: 0.3
                },
                {
                    data: Array(datos.length).fill(meta),
                    borderColor: '#e74c3c',
                    borderWidth: 1,
                    borderDash: [5, 5],
                    pointRadius: 0,
                    fill: false
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { enabled: true } },
            scales: { x: { display: false }, y: { display: false } }
        }
    });
}
```

---

## 7. LÓGICA DE SEMÁFORO

### Indicadores Porcentuales (mayoría — "más es mejor")

| Rango | Color | Estado |
|-------|-------|--------|
| ≥ 85% | `#27ae60` verde | Cumple satisfactoriamente |
| 60% - 84% | `#f39c12` amarillo | Cumplimiento parcial — requiere atención |
| < 60% | `#e74c3c` rojo | No cumple — acción correctiva urgente |

### Indicadores de Accidentalidad (inversos — "menos es mejor")

| Condición | Color | Estado |
|-----------|-------|--------|
| Valor ≤ Meta | `#27ae60` verde | Cumple (accidentalidad controlada) |
| Valor > Meta y ≤ Meta×1.5 | `#f39c12` amarillo | Alerta (por encima de meta) |
| Valor > Meta×1.5 | `#e74c3c` rojo | Crítico (muy por encima de meta) |

### Sin Medir

| Condición | Color | Estado |
|-----------|-------|--------|
| Sin medición registrada | `#6c757d` gris | Pendiente de medición |

---

## 8. FLUJO DE NAVEGACIÓN

```
[Documentación Cliente]
        │
        ▼
[indicadores-sst/{id}]          ← Vista actual: CRUD de indicadores
        │
        ├── [/dashboard]         ← NUEVA vista: Dashboard jerárquico
        │       │
        │       ├── Click Gauge Nivel 2 → Filtra Nivel 3 por tipo
        │       ├── Click Gauge Nivel 3 → Expande Nivel 4 con indicadores
        │       ├── Click indicador Nivel 4 → Modal/redirect a editar
        │       └── Click "Medir" → Modal de medición inline
        │
        ├── [/crear]             ← Formulario crear indicador
        └── [/editar/{id}]       ← Formulario editar indicador
```

**Navegación bidireccional:**
- Desde el dashboard, botón "Ver lista completa" → va al index actual
- Desde el index actual, botón "Ver Dashboard" → va al dashboard
- Ambas vistas coexisten: CRUD (index) para gestión, Dashboard para análisis

---

## 9. DATOS DE EJEMPLO (Basados en los screenshots)

Del análisis de las fichas técnicas compartidas, este cliente tiene ~20 indicadores:

| Indicador | Tipo | Categoría | Meta | Valor Real | Cumple |
|-----------|------|-----------|------|------------|--------|
| Plan de Trabajo Anual | P+R | pta | 80% | 24% | ❌ |
| Cumplimiento Matriz IPVR | P+R | riesgos | 90% | 0% | ❌ |
| Programas Riesgo Prioritario | P+R | vigilancia | 80% | 100% | ✅ |
| Programas Vigilancia Epidemiológica | P+R | vigilancia | 80% | 0% | ❌ |
| Evaluación Inicial SG-SST | P | pta | 90% | 49% | ❌ |
| Acciones Preventivas/Correctivas | P+R | objetivos_sgsst | — | 100% | ✅ |
| Cumplimiento Objetivos SG-SST | R | objetivos_sgsst | 100% | 24% | ❌ |
| Requisitos Legales Aplicables | R | objetivos_sgsst | 100% | 0% | ❌ |
| Plan de Capacitación | P | capacitacion | 80% | 63% | ❌ |
| Estructura del SG-SST | E | objetivos_sgsst | 100% | 0% | ❌ |
| Reporte/Investigación Accidentes | P+R | accidentalidad | 90% | 100% | ✅ |
| **Frecuencia AT (IF)** | **P+R** | **accidentalidad** | **1.0** | **0.0** | **✅** |
| **Severidad AT (IS)** | **P+R** | **accidentalidad** | **6** | **0.0** | **✅** |
| **Proporción AT Mortales** | **P+R** | **accidentalidad** | **0%** | **0%** | **✅** |
| **Prevalencia Enfermedad Laboral** | **P+R** | **vigilancia** | **2000** | **1887** | **✅** |
| **Incidencia Enfermedad Laboral** | **P+R** | **vigilancia** | **2000** | **0** | **✅** |
| **Ausentismo** | **P+R** | **ausentismo** | **15%** | **0%** | **✅** |
| Rehabilitación | R | vigilancia | 100% | 0% | ❌ |

**Negrilla** = Indicadores mínimos Res. 0312

---

## 10. RESUMEN DE ARCHIVOS A CREAR/MODIFICAR

### Nuevos

| Archivo | Descripción |
|---------|-------------|
| `app/Views/indicadores_sst/dashboard.php` | Vista principal del dashboard con gauges |
| `app/SQL/agregar_campos_dashboard_indicadores.sql` | ALTER TABLE para `es_minimo_obligatorio` y `peso_ponderacion` |

### Modificados

| Archivo | Cambio |
|---------|--------|
| `app/Controllers/IndicadoresSSTController.php` | Agregar `dashboard()`, `apiDashboard()`, `apiConsolidacion()` |
| `app/Models/IndicadorSSTModel.php` | Agregar `getDashboardData()`, `getConsolidacionGlobal()`, `getMinimosObligatorios()` |
| `app/Config/Routes.php` | Agregar 3 nuevas rutas |
| `app/Views/indicadores_sst/index.php` | Agregar botón "Ver Dashboard" en el header |

### No Modificados

- Services existentes (no se tocan — el dashboard consume lo que ya generan)
- DocumentosSSTTypes (no afectados — flujo de generación de documentos intacto)
- Vistas de Generador IA (no afectadas)

---

## 11. ORDEN DE IMPLEMENTACIÓN

1. **SQL**: Agregar campos `es_minimo_obligatorio` y `peso_ponderacion`
2. **Model**: Agregar métodos `getDashboardData()`, `getConsolidacionGlobal()`, `getMinimosObligatorios()`
3. **Controller**: Agregar `dashboard()` y `apiDashboard()`
4. **Routes**: Agregar rutas
5. **Vista**: Crear `dashboard.php` con gauges Chart.js
6. **Enlace**: Agregar botón "Dashboard" en el index existente
7. **Pruebas**: Verificar con datos reales del cliente
