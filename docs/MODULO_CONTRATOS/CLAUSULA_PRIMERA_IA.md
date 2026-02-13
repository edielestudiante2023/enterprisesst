# Cláusula Primera - Generación con IA

## Descripción General
La Cláusula Primera (Objeto del Contrato) de los contratos de prestación de servicios SST se genera usando la API de OpenAI a través de `IADocumentacionService::generarContenido()`. A diferencia de la Cláusula Cuarta (duración/plazos), esta cláusula define **qué servicios se prestan** y **quién es el coordinador técnico SST**.

## Motivación
Solicitud por email: cuando el consultor es **externo**, la cláusula debe incluir:
- Nombramiento del profesional SST como responsable técnico y coordinador
- Cláusula de delegación de visitas presenciales
- Descripción detallada de los servicios SST
- Mención de la plataforma EnterpriseSST

## Flujo UX

### Paso 1: Botón "Generar con IA"
- Ubicado junto al textarea `clausula_primera_objeto`
- Disponible en: `create.php` y `edit_contract_data.php`

### Paso 2: SweetAlert con Datos del Servicio
Campos capturados:
| Campo | Ejemplo | Uso en Prompt |
|-------|---------|---------------|
| Descripción del servicio | Diseño e implementación del SG-SST | Define el objeto del contrato |
| Tipo de consultor | Externo (radio) | Si externo → incluye delegación de visitas |
| Nombre coordinador SST | Edison Pérez García | Nombrado como responsable técnico |
| Cédula coordinador | 1234567890 | Identificación en la cláusula |
| Licencia SST | 12345-2024 | Licencia ocupacional referenciada |
| Contexto adicional | (libre) | Info complementaria |

**Pre-llenado automático** (solo en edit): Los campos del coordinador se pre-llenan desde los hidden inputs `nombre_responsable_sgsst`, `cedula_responsable_sgsst`, `licencia_responsable_sgsst`.

### Paso 3: Texto generado en textarea
- Editable libremente antes de guardar
- Toolbar post-generación: Regenerar, Refinar, Limpiar

### Paso 4: Refinamiento
- "Refinar con contexto": instrucciones adicionales sobre el texto existente
- "Regenerar todo": SweetAlert pre-llenado con datos anteriores

## Arquitectura Técnica

### Ruta
```
POST /contracts/generar-clausula1-ia → ContractController::generarClausula1IA()
```

### Archivos Involucrados
- **Controller:** `app/Controllers/ContractController.php` → `generarClausula1IA()`
- **Servicio IA:** `app/Services/IADocumentacionService.php` → `generarContenido(prompt, 1500)`
- **Vista crear:** `app/Views/contracts/create.php`
- **Vista editar:** `app/Views/contracts/edit_contract_data.php`
- **PDF Generator:** `app/Libraries/ContractPDFGenerator.php` → `buildClausulaObjeto()`
- **Modelo:** `app/Models/ContractModel.php` → campo `clausula_primera_objeto`

### Campo en BD
```sql
ALTER TABLE tbl_contratos ADD COLUMN clausula_primera_objeto TEXT NULL AFTER clausula_cuarta_duracion;
```

### Payload JSON enviado al backend
```json
{
  "id_cliente": 19,
  "descripcion_servicio": "Diseño e implementación del SG-SST",
  "tipo_consultor": "externo",
  "nombre_coordinador": "Edison Pérez García",
  "cedula_coordinador": "1234567890",
  "licencia_coordinador": "12345-2024",
  "contexto_adicional": "",
  "texto_actual": "",
  "modo_refinamiento": false
}
```

## Reglas del Prompt

### Reglas Obligatorias
1. **Partes en mayúsculas**: Siempre `EL CONTRATANTE` y `EL CONTRATISTA` en mayúsculas sostenidas
2. **Coordinador SST**: Nombrar al profesional como "responsable técnico y coordinador del servicio" con nombre, cédula y licencia reales
3. **Plataforma EnterpriseSST**: Mencionarla como herramienta principal de gestión documental y seguimiento
4. **Resolución 0312 de 2019**: Referenciar estándares mínimos del SG-SST
5. **Sin placeholders**: NUNCA `[NOMBRE]` o `[FECHA]` — usar datos reales
6. **Sin explicaciones**: Solo texto de la cláusula, sin comentarios ni encabezados de IA

### Lógica Interno vs. Externo
- **Interno**: El coordinador SST es empleado directo del contratista. No se incluye cláusula de delegación.
- **Externo**: El coordinador SST es externo. Se agrega:
  - Párrafo de **delegación de visitas**: las visitas presenciales podrán ser realizadas por otros profesionales del equipo, bajo coordinación del profesional SST asignado
  - Garantía de que los profesionales delegados cuentan con formación y licencia SST

### Estructura Esperada del Texto Generado
```
PRIMERA-OBJETO DEL CONTRATO: EL CONTRATISTA se compromete a proporcionar
servicios de consultoría para [descripción_servicio] a favor de EL CONTRATANTE
mediante la plataforma EnterpriseSST...

[Descripción de la plataforma y su rol en la gestión]

Se asignará al profesional en SG-SST [NOMBRE REAL], identificado con cédula
de ciudadanía No. [CÉDULA REAL] y licencia SST No. [LICENCIA REAL], como
responsable técnico y coordinador del servicio...

[Detalle de servicios: supervisión, capacitación, documentación, etc.]

[Si externo:] No obstante, las visitas presenciales a las instalaciones de
EL CONTRATANTE podrán ser realizadas por otros profesionales del equipo de
EL CONTRATISTA, siempre bajo la coordinación y responsabilidad del profesional
SST asignado...
```

## PDF Generator - Fallback
En `ContractPDFGenerator::buildClausulaObjeto()`:
1. Si `$data['clausula_primera_objeto']` tiene contenido → se usa con `nl2br()`
2. Si está vacío → se usa el texto hardcodeado genérico (2 oraciones)

Mismo patrón que `buildClausulaDuracion()` con `clausula_cuarta_duracion`.

## Historial de Refinamiento

### v1.0 - Implementación Inicial (2026-02-13)
- Texto hardcodeado genérico de 2 oraciones en `buildClausulaObjeto()`
- No distinguía interno/externo
- Sin mención de delegación de visitas

### v1.1 - Generación con IA (2026-02-13)
- Campo `clausula_primera_objeto` en BD
- SweetAlert con tipo consultor (interno/externo)
- Prompt con delegación de visitas para externos
- Coordinador SST nombrado como responsable técnico
- Mención de EnterpriseSST y Resolución 0312
- Toolbar: Regenerar, Refinar, Limpiar

## Mejoras Futuras (Ideas)
- [ ] Auto-detectar tipo consultor (interno/externo) desde BD del consultor
- [ ] Incluir actividades específicas del PTA del cliente en la descripción de servicios
- [ ] Template base de cláusula primera que la IA refine (no genere desde cero)
- [ ] Preview lado a lado: texto actual vs. texto generado
