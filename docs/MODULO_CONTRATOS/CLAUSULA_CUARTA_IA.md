# Cláusula Cuarta - Generación con IA

## Descripción General
La Cláusula Cuarta (Duración y Plazo de Ejecución) de los contratos de prestación de servicios SST se genera usando la API de OpenAI a través de `IADocumentacionService::generarContenido()`.

## Flujo UX

### Paso 1: Botón "Generar con IA"
- Ubicado junto al textarea `clausula_cuarta_duracion`
- Disponible en: `create.php` y `edit_contract_data.php`

### Paso 2: SweetAlert con Acuerdos Contractuales
Campos capturados:
| Campo | Ejemplo | Uso en Prompt |
|-------|---------|---------------|
| Plazo de ejecución | 30 días calendario | Plazo operativo para ejecutar actividades |
| Duración del contrato | 10 meses (auto-calculado) | Periodo total del contrato |
| Fecha de inicio | 2025-03-01 (del formulario) | Fecha real para la cláusula |
| Fecha de fin | 2025-12-31 (del formulario) | Fecha real para la cláusula |
| Porcentaje de anticipo | 50% | Monto del anticipo |
| Condiciones de pago | 50% anticipo, 50% contra entrega | Estructura de pagos |
| Terminación anticipada | Solo honorarios causados | Condiciones de terminación |
| Obligaciones especiales | Diseño documental, MinTrabajo | Obligaciones adicionales |
| Contexto adicional | (libre) | Info complementaria |

### Paso 3: Texto generado en textarea
- Editable libremente antes de guardar
- Toolbar post-generación: Regenerar, Refinar, Limpiar

### Paso 4: Refinamiento
- "Refinar con contexto": instrucciones adicionales sobre el texto existente
- "Regenerar todo": SweetAlert pre-llenado con acuerdos anteriores

## Arquitectura Técnica

### Ruta
```
POST /contracts/generar-clausula-ia → ContractController::generarClausulaIA()
```

### Archivos Involucrados
- **Controller:** `app/Controllers/ContractController.php` → `generarClausulaIA()`
- **Servicio IA:** `app/Services/IADocumentacionService.php` → `generarContenido(prompt, 1500)`
- **Vista crear:** `app/Views/contracts/create.php`
- **Vista editar:** `app/Views/contracts/edit_contract_data.php`

### Payload JSON enviado al backend
```json
{
  "id_cliente": 19,
  "plazo_ejecucion": "30 días calendario",
  "duracion_contrato": "10 meses",
  "fecha_inicio": "2025-03-01",
  "fecha_fin": "2025-12-31",
  "porcentaje_anticipo": "50%",
  "condiciones_pago": "50% anticipo, 50% contra entrega",
  "terminacion_anticipada": "Solo honorarios causados",
  "obligaciones_especiales": "Diseño documental SST",
  "contexto_adicional": "",
  "texto_actual": "",
  "modo_refinamiento": false
}
```

## Reglas del Prompt

### Reglas Obligatorias
1. **Fechas reales**: Usar las fechas `fecha_inicio` y `fecha_fin` del formulario. NUNCA placeholders como `[FECHA]`
2. **Partes en mayúsculas**: Siempre `EL CONTRATANTE` y `EL CONTRATISTA` en mayúsculas sostenidas
3. **Contexto SST**: Mencionar explícitamente "Seguridad y Salud en el Trabajo" y servicios de "diseño e implementación del SG-SST"
4. **Duración consistente**: La duración debe coincidir con la diferencia de las fechas proporcionadas
5. **Sin explicaciones**: Solo texto de la cláusula, sin comentarios ni encabezados de IA

### Estructura Esperada del Texto Generado
```
CLÁUSULA CUARTA – DURACIÓN Y PLAZO DE EJECUCIÓN:

CUARTA-PLAZO DE EJECUCIÓN: EL CONTRATISTA se obliga a ejecutar las actividades
objeto del presente contrato en un plazo de [X] días calendario...

CUARTA-DURACIÓN: El presente contrato tendrá una duración de [X] meses,
contados a partir del [fecha_inicio formateada] hasta el [fecha_fin formateada]...

PARÁGRAFO PRIMERO: En caso de terminación anticipada...
PARÁGRAFO SEGUNDO: El presente contrato NO admite prórroga automática...
[Parágrafos adicionales según obligaciones especiales]
```

### Formato de Fechas
- En el texto legal: `1 de marzo de 2025` (formato largo español)
- Nunca: `2025-03-01` ni `01/03/2025` en el texto generado

## Historial de Refinamiento

### v1.0 - Implementación Inicial (2025-02-13)
- Prompt básico sin fechas del formulario
- **Problemas identificados:**
  - Fechas placeholder `[FECHA DE INICIO]` en vez de reales
  - Duración incorrecta (365 días vs 10 meses reales)
  - Nomenclatura inconsistente (minúsculas para partes)
  - Sin mención de anticipo en la cláusula
  - Sin referencia a SST

### v1.1 - Corrección de Prompt (2025-02-13)
- Fechas `fecha_inicio` y `fecha_fin` enviadas desde JS al backend
- Prompt mejorado con reglas estrictas:
  - Fechas reales en formato largo español
  - Partes siempre en mayúsculas sostenidas
  - Contexto SST obligatorio
  - Anticipo y condiciones de pago integrados
  - Duración calculada correctamente

## Mejoras Futuras (Ideas)
- [ ] Incluir nombre del representante legal en el prompt (de la BD del cliente)
- [ ] Agregar opción de tono: formal estándar vs. ultra-formal
- [ ] Template de cláusula cuarta como base (IA refina, no genera desde cero)
- [ ] Historial de generaciones por contrato
- [ ] Preview lado a lado: texto actual vs. texto generado
- [ ] Integrar con las otras cláusulas del contrato para coherencia global
