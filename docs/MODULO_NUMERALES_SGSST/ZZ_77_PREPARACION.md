# PREPARACIÓN: Requisitos de UI para Módulos Generadores IA

## ⚠️ LEER ANTES DE CREAR CUALQUIER MÓDULO

Este documento establece los **requisitos obligatorios de interfaz de usuario** que TODOS los módulos generadores de IA deben cumplir. Léelo ANTES de revisar ZZ_88_PARTE1.md, ZZ_89_PARTE2.md o ZZ_90_PARTE3.md.

---

## REQUISITO #1: Consistencia de UX entre Parte 1 y Parte 2

**REGLA FUNDAMENTAL**: La Parte 1 (Actividades/Capacitaciones) y la Parte 2 (Indicadores) DEBEN tener **UX idéntico**.

### Anti-patrón vs Patrón Correcto

```
┌─────────────────────────────────────────────────────────────────────────────┐
│  ❌ ANTI-PATRÓN: Parte 2 redirige      ✅ CORRECTO: Parte 2 usa modal      │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  ┌─────────────────────┐                ┌─────────────────────┐            │
│  │ 2 Indicadores       │                │ 2 Indicadores       │            │
│  │                     │                │                     │            │
│  │ Configurados: 0     │                │ Configurados: 0     │            │
│  │ Mínimo: 2           │                │ Mínimo: 2           │            │
│  │                     │                │                     │            │
│  │ ┌─────────────────┐ │                │ ┌─────────────────┐ │            │
│  │ │ Ir a Indicadores│─┼──▶ REDIRIGE   │ │ Ver Preview     │─┼──▶ MODAL  │
│  │ └─────────────────┘ │     ❌         │ └─────────────────┘ │     ✅     │
│  │                     │                │ ┌─────────────────┐ │            │
│  │                     │                │ │ Generar Indic.  │─┼──▶ MODAL  │
│  │                     │                │ └─────────────────┘ │     ✅     │
│  └─────────────────────┘                └─────────────────────┘            │
│                                                                             │
│  PROBLEMA:                              BENEFICIO:                          │
│  • Rompe consistencia con Parte 1       • UX idéntico a Parte 1            │
│  • Usuario pierde contexto              • Usuario no pierde contexto       │
│  • Experiencia confusa                  • Flujo intuitivo                  │
└─────────────────────────────────────────────────────────────────────────────┘
```

### Elementos que DEBEN ser idénticos entre Parte 1 y Parte 2

| Elemento | Parte 1 (Actividades) | Parte 2 (Indicadores) |
|----------|----------------------|----------------------|
| Botón Preview | "Ver Preview" | "Ver Preview" |
| Botón Generar | "Generar [Items]" | "Generar Indicadores" |
| **Comportamiento** | **Modal en MISMA vista** | **Modal en MISMA vista** |
| Modal | Modal XL con cards | Modal XL con cards |
| Selección | Checkbox + Sel. Todos | Checkbox + Sel. Todos |
| Edición | Inline en cada card | Inline en cada card |
| Mejorar con IA | Panel por cada ítem | Panel por cada ítem |

---

## REQUISITO #2: Edición Individual por Ítem

**CADA ÍTEM generado por IA DEBE permitir:**

1. ✅ **Edición inline de todos los campos** - El usuario puede modificar textos directamente
2. ✅ **Panel "Mejorar con IA" propio** - Cada ítem tiene su contexto IA para regenerarse individualmente
3. ✅ **Selección independiente** - Checkbox para incluir/excluir del envío final

### Comparación de Patrones

```
┌─────────────────────────────────────────────────────────────────────────┐
│                     COMPARACIÓN DE PATRONES                              │
├───────────────────────────────────┬─────────────────────────────────────┤
│  ❌ INCORRECTO (Anti-patrón)      │  ✅ CORRECTO (Patrón obligatorio)   │
├───────────────────────────────────┼─────────────────────────────────────┤
│                                   │                                     │
│  ☐ Capacitación 1                │  ☐ [___Capacitación 1________]     │
│    Objetivo: texto estático       │    [___Objetivo editable_______]   │
│                                   │    🤖 Mejorar con IA ▼              │
│                                   │    └─ [instrucciones propias]       │
│                                   │                                     │
│  ☐ Capacitación 2                │  ☐ [___Capacitación 2________]     │
│    Objetivo: texto estático       │    [___Objetivo editable_______]   │
│                                   │    🤖 Mejorar con IA ▼              │
│                                   │                                     │
├───────────────────────────────────┼─────────────────────────────────────┤
│  PROBLEMAS:                       │  BENEFICIOS:                        │
│  • No puede ajustar textos        │  • Control total sobre cada ítem    │
│  • Si IA genera algo mal,         │  • Puede regenerar SOLO lo malo     │
│    debe regenerar TODO            │  • Personalización granular         │
│  • Frustrante para el usuario     │  • UX profesional                   │
└───────────────────────────────────┴─────────────────────────────────────┘
```

---

## Estructura Visual de un Ítem Editable

Cada ítem en el modal de preview DEBE verse así:

```
┌────────────────────────────────────────────────────────────────────────┐
│ ☑ [Nombre del ítem editable_________________________________]  [Badge]│
│                                                                        │
│   [Descripción u objetivo del ítem en textarea editable               │
│    que permite modificar el contenido directamente______________]     │
│                                                                        │
│   Campo1: [Select ▼]    Campo2: [Input]    Campo3: [Select ▼]         │
│                                                                        │
│   ─────────────────────────────────────────────────────────────────   │
│   🤖 Mejorar con IA  ▼                                                │
│   ┌────────────────────────────────────────────────────────────────┐  │
│   │ [Instrucciones específicas para este ítem: hazlo más          │  │
│   │  específico, enfoca en X, agrega detalle Y________________]   │  │
│   │                                                                │  │
│   │        [🔮 Regenerar este ítem]                               │  │
│   └────────────────────────────────────────────────────────────────┘  │
└────────────────────────────────────────────────────────────────────────┘
```

### Componentes Obligatorios por Ítem

| # | Componente | Descripción | Obligatorio |
|---|------------|-------------|:-----------:|
| 1 | Checkbox | Para seleccionar/deseleccionar el ítem | ✅ |
| 2 | Input título | Campo editable para el nombre/título | ✅ |
| 3 | Textarea descripción | Campo editable para descripción/objetivo | ✅ |
| 4 | Campos adicionales | Inputs/selects según el tipo de ítem | ✅ |
| 5 | Panel "Mejorar con IA" | Colapsable con textarea de instrucciones | ✅ |
| 6 | Botón "Regenerar" | Llama endpoint para regenerar solo este ítem | ✅ |

---

## Código HTML del Card de Ítem

```html
<div class="card mb-2 item-card" data-idx="${idx}">
    <div class="card-body py-2 px-3">
        <div class="d-flex align-items-start">

            <!-- 1. CHECKBOX -->
            <div class="form-check me-2 pt-1">
                <input type="checkbox" class="form-check-input item-check"
                       data-idx="${idx}" checked onchange="actualizarContador()">
            </div>

            <div class="flex-grow-1">

                <!-- 2. TÍTULO EDITABLE + BADGE -->
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <input type="text" class="form-control form-control-sm fw-bold item-titulo"
                           data-idx="${idx}" value="${item.nombre}" style="flex:1; margin-right:8px;">
                    <span class="badge bg-secondary">${item.tipo}</span>
                </div>

                <!-- 3. DESCRIPCIÓN EDITABLE -->
                <div class="mb-2">
                    <textarea class="form-control form-control-sm item-descripcion"
                              data-idx="${idx}" rows="2">${item.descripcion}</textarea>
                </div>

                <!-- 4. CAMPOS ADICIONALES -->
                <div class="row g-2 mb-2">
                    <div class="col-md-4">
                        <select class="form-select form-select-sm item-campo1" data-idx="${idx}">
                            <!-- opciones -->
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control form-control-sm item-campo2"
                               data-idx="${idx}" value="${item.campo2}">
                    </div>
                    <div class="col-md-4">
                        <input type="number" class="form-control form-control-sm item-campo3"
                               data-idx="${idx}" value="${item.campo3}">
                    </div>
                </div>

                <!-- 5. PANEL MEJORAR CON IA -->
                <div class="border-top pt-2">
                    <button type="button" class="btn btn-sm btn-link text-decoration-none p-0"
                            onclick="toggleIAPanel(${idx})">
                        <i class="bi bi-robot me-1"></i>
                        <small>Mejorar con IA</small>
                        <i class="bi bi-chevron-down ms-1" id="iaChevron${idx}"></i>
                    </button>

                    <div class="collapse mt-2" id="iaPanelItem${idx}">
                        <div class="card card-body bg-light border-0 p-2">
                            <textarea class="form-control form-control-sm instrucciones-ia-item mb-2"
                                      data-idx="${idx}" rows="2"
                                      placeholder="Instrucciones para mejorar este ítem..."></textarea>

                            <!-- 6. BOTÓN REGENERAR -->
                            <button type="button" class="btn btn-sm w-100"
                                    style="border-color:#9c27b0; color:#9c27b0;"
                                    onclick="regenerarItemConIA(${idx})">
                                <i class="bi bi-magic me-1"></i>Regenerar este ítem
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
```

---

## JavaScript Requerido

### 1. Toggle del Panel IA

```javascript
function toggleIAPanel(idx) {
    const panel = document.getElementById(`iaPanelItem${idx}`);
    const chevron = document.getElementById(`iaChevron${idx}`);

    if (panel.classList.contains('show')) {
        panel.classList.remove('show');
        chevron.classList.remove('bi-chevron-up');
        chevron.classList.add('bi-chevron-down');
    } else {
        panel.classList.add('show');
        chevron.classList.remove('bi-chevron-down');
        chevron.classList.add('bi-chevron-up');
    }
}
```

### 2. Obtener Datos Editados

```javascript
function getItemData(idx) {
    return {
        nombre: document.querySelector(`.item-titulo[data-idx="${idx}"]`).value,
        descripcion: document.querySelector(`.item-descripcion[data-idx="${idx}"]`).value,
        campo1: document.querySelector(`.item-campo1[data-idx="${idx}"]`).value,
        campo2: document.querySelector(`.item-campo2[data-idx="${idx}"]`).value,
        campo3: document.querySelector(`.item-campo3[data-idx="${idx}"]`).value,
        // ... adaptar según el tipo de ítem
    };
}
```

### 3. Regenerar Ítem con IA

```javascript
function regenerarItemConIA(idx) {
    const instrucciones = document.querySelector(`.instrucciones-ia-item[data-idx="${idx}"]`).value;
    const itemActual = getItemData(idx);

    if (!instrucciones.trim()) {
        showToast('info', 'Instrucciones', 'Escriba instrucciones para la IA');
        return;
    }

    const btn = event.target;
    const btnOriginal = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Regenerando...';

    fetch(`${baseUrl}/regenerar-item`, {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            item: itemActual,
            instrucciones: instrucciones,
            contexto_general: getInstruccionesIA()
        })
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = btnOriginal;

        if (data.success && data.data) {
            const nuevo = data.data;
            document.querySelector(`.item-titulo[data-idx="${idx}"]`).value = nuevo.nombre || '';
            document.querySelector(`.item-descripcion[data-idx="${idx}"]`).value = nuevo.descripcion || '';
            // ... actualizar otros campos

            showToast('success', 'Regenerado', 'Ítem mejorado por la IA');
            document.querySelector(`.instrucciones-ia-item[data-idx="${idx}"]`).value = '';
        } else {
            showToast('error', 'Error', data.message || 'No se pudo regenerar');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = btnOriginal;
        showToast('error', 'Error', 'Error de conexión');
    });
}
```

---

## Backend Requerido (por cada tipo de ítem)

### 1. Ruta para Preview

```php
$routes->get('generador-ia/(:num)/preview-[items]', 'GeneradorIAController::preview[Items]/$1');
```

### 2. Ruta para Generar

```php
$routes->post('generador-ia/(:num)/generar-[items]', 'GeneradorIAController::generar[Items]/$1');
```

### 3. Ruta para Regenerar Individual

```php
$routes->post('generador-ia/(:num)/regenerar-[item]', 'GeneradorIAController::regenerar[Item]/$1');
```

### Método Ejemplo para Regenerar

```php
public function regenerarItem(int $idCliente)
{
    $datos = $this->request->getJSON(true);
    $itemActual = $datos['item'] ?? [];
    $instrucciones = $datos['instrucciones'] ?? '';

    // Obtener contexto del cliente
    $contexto = $this->obtenerContextoCliente($idCliente);

    // Llamar a OpenAI para mejorar
    $itemMejorado = $this->regenerarConIA($itemActual, $contexto, $instrucciones);

    return $this->response->setJSON([
        'success' => true,
        'data' => $itemMejorado
    ]);
}
```

---

## Checklist de Verificación

### Por cada módulo generador, verificar:

| Requisito | Pregunta de Verificación | ✓ |
|-----------|--------------------------|---|
| **UX Consistente** | ¿Parte 2 usa modal igual que Parte 1? | ☐ |
| **No redirige** | ¿Parte 2 NO redirige a otra página? | ☐ |
| Campos editables | ¿El usuario puede editar TODOS los campos? | ☐ |
| Panel IA individual | ¿CADA ítem tiene su propio panel "Mejorar con IA"? | ☐ |
| Textarea instrucciones | ¿El panel tiene textarea para instrucciones? | ☐ |
| Botón regenerar | ¿Existe botón "Regenerar este ítem"? | ☐ |
| Endpoint regenerar | ¿Existe ruta POST para regenerar individual? | ☐ |
| Actualización UI | ¿Tras regenerar, los campos se actualizan? | ☐ |

---

## Estado de Módulos

| Módulo | Parte 1 Modal | Parte 2 Modal | Edición | Panel IA | Estado |
|--------|:-------------:|:-------------:|:-------:|:--------:|--------|
| capacitacion_sst | ✅ | ✅ | ✅ | ✅ | **COMPLETO** ✅ |
| objetivos_sgsst | ✅ | ✅ | ✅ | ✅ | **COMPLETO** ✅ |
| indicadores_objetivos | - | ✅ | ✅ | ✅ | **COMPLETO** ✅ |
| pyp_salud | ✅ | ✅ | ✅ | ✅ | **COMPLETO** ✅ |

---

## Implementación Realizada: capacitacion_sst (Parte 2 - Indicadores)

### Archivos Creados

1. **app/Services/IndicadoresCapacitacionService.php**
   - Servicio dedicado para indicadores de capacitación (categoría: 'capacitacion')
   - Constantes: `INDICADORES_BASE` con 5 indicadores sugeridos
   - Métodos: `previewIndicadores()`, `generarIndicadores()`, `getIndicadoresCliente()`
   - Validación: Verifica que existan capacitaciones (Parte 1) antes de generar indicadores

### Archivos Modificados

1. **app/Controllers/GeneradorIAController.php**
   - Agregados métodos:
     - `previewIndicadoresCapacitacion()` - Preview con indicadores sugeridos
     - `generarIndicadoresCapacitacion()` - Guardar indicadores seleccionados
     - `regenerarIndicadorCapacitacion()` - Regenerar indicador individual con IA
     - `regenerarIndicadorConIA()` - Lógica de llamada a OpenAI

2. **app/Config/Routes.php**
   - Agregadas rutas:
     ```
     GET  /generador-ia/(:num)/preview-indicadores-capacitacion
     POST /generador-ia/(:num)/generar-indicadores-capacitacion
     POST /generador-ia/(:num)/regenerar-indicador-capacitacion
     ```

3. **app/Views/generador_ia/capacitacion_sst.php**
   - Sección Indicadores: Cambiado "Ir a Indicadores" por "Ver Preview" + "Generar Indicadores"
   - Agregado modal `modalPreviewIndicadores` con cards editables
   - Agregadas funciones JavaScript:
     - `previewIndicadores()` - Cargar preview en modal
     - `renderIndicadoresCards()` - Renderizar cards con edición inline
     - `getIndicadorData()` - Obtener datos editados
     - `regenerarIndicadorConIA()` - Regenerar con IA
     - `generarIndicadoresSeleccionados()` - Guardar seleccionados

### Indicadores Base Implementados

| # | Indicador | Tipo | Meta |
|---|-----------|------|------|
| 1 | Cumplimiento del Cronograma de Capacitación | Proceso | 100% |
| 2 | Cobertura de Capacitación en SST | Proceso | 100% |
| 3 | Evaluación de Eficacia de Capacitaciones | Resultado | 80% |
| 4 | Oportunidad en la Ejecución de Capacitaciones | Proceso | 90% |
| 5 | Horas de Capacitación por Trabajador | Resultado | 20h |

---

## Flujo de Usuario Estándar (IDÉNTICO en Parte 1 y Parte 2)

```
┌──────────────────────────────────────────────────────────────────────────┐
│                    FLUJO DE USUARIO ESTÁNDAR                              │
├──────────────────────────────────────────────────────────────────────────┤
│                                                                          │
│  1. Usuario ve el ESTADO ACTUAL en la tarjeta                            │
│     - Cuántos elementos existen                                          │
│     - Cuál es el mínimo requerido                                       │
│     - Si la fase está completa                                          │
│                                                                          │
│  2. Usuario hace clic en "Ver Preview" o "Generar [Items]"              │
│     - Ambos botones abren el MISMO MODAL                                │
│     - El modal carga items desde el servidor                            │
│                                                                          │
│  3. MODAL se abre con cards editables:                                   │
│     ┌────────────────────────────────────────────────────────────────┐  │
│     │ Total: X items sugeridos (límite: Y)                           │  │
│     │ [Seleccionar Todos] [Deseleccionar]                           │  │
│     │                                                                │  │
│     │ ┌────────────────────────────────────────────────────────────┐│  │
│     │ │ ☑ [Nombre editable_______________________]     [Badge]    ││  │
│     │ │   [Descripción editable___________________________]       ││  │
│     │ │   Campo: [___] Campo2: [___]                              ││  │
│     │ │   ▼ Mejorar con IA                                        ││  │
│     │ └────────────────────────────────────────────────────────────┘│  │
│     │                                                                │  │
│     │ [X items seleccionados]          [Cancelar] [Generar X Items] │  │
│     └────────────────────────────────────────────────────────────────┘  │
│                                                                          │
│  4. Usuario SELECCIONA con checkbox cuáles incluir                      │
│  5. Usuario EDITA inline los campos que necesite                        │
│  6. Usuario puede "Mejorar con IA" cualquier ítem individual            │
│  7. Usuario hace clic en "Generar X Items"                              │
│     - Sistema guarda SOLO los seleccionados                             │
│     - Toast de confirmación                                             │
│     - Página se recarga                                                 │
│                                                                          │
└──────────────────────────────────────────────────────────────────────────┘
```

---

## Próximos Pasos

Después de leer este documento, continuar con:

1. **ZZ_88_PARTE1.md** - Generador de Actividades/Capacitaciones
2. **ZZ_89_PARTE2.md** - Generador de Indicadores
3. **ZZ_90_PARTE3.md** - Generador de Documento Formal

Cada documento ASUME que has leído y entendido estos requisitos.

---

*Documento actualizado: 2026-02-06*
*Versión: 2.0 - Agregado requisito de consistencia Parte 1/Parte 2*
