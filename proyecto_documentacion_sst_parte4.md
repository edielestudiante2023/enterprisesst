# Proyecto de Documentación SST - Parte 4

## Resumen

Esta parte documenta las mejoras y simplificaciones implementadas en el módulo de Contexto SST y los selectores de cliente del sistema.

---

## 1. Mejoras en Selectores de Cliente

### 1.1 Problema Original

Los selectores de cliente usaban tarjetas con iconos (cards), lo cual era poco práctico para empresas con 100+ clientes.

### 1.2 Solución Implementada

Se implementó **Select2** con búsqueda en todos los módulos:
- Búsqueda por nombre del cliente
- Búsqueda por NIT
- Interfaz limpia y consistente

### 1.3 Archivos Modificados

| Archivo | Módulo |
|---------|--------|
| `app/Views/contexto/seleccionar_cliente.php` | Contexto SST |
| `app/Views/documentacion/seleccionar_cliente.php` | Documentación |
| `app/Views/estandares/seleccionar_cliente.php` | Estándares PHVA |

### 1.4 Código del Selector

```php
<select id="selectCliente" class="form-select form-select-lg" style="width: 100%;">
    <option value="">Buscar cliente por nombre o NIT...</option>
    <?php foreach ($clientes as $cliente): ?>
        <option value="<?= $cliente['id_cliente'] ?>"
                data-nit="<?= esc($cliente['nit_cliente'] ?? '') ?>">
            <?= esc($cliente['nombre_cliente']) ?>
            <?php if (!empty($cliente['nit_cliente'])): ?>
                - NIT: <?= esc($cliente['nit_cliente']) ?>
            <?php endif; ?>
        </option>
    <?php endforeach; ?>
</select>
```

---

## 2. Filtrado de Clientes Activos

### 2.1 Cambio Realizado

Todos los controladores ahora muestran **solo clientes activos** sin filtrar por consultor.

### 2.2 Justificación

> "Un consultor puede ver todos los clientes. Eso complica mucho si alguien se incapacita, renuncia o falta al trabajo."

### 2.3 Código Actualizado

```php
// Obtener todos los clientes activos (sin filtrar por consultor)
$clientes = $this->clienteModel
    ->where('estado', 'activo')
    ->orderBy('nombre_cliente', 'ASC')
    ->findAll();
```

### 2.4 Controladores Modificados

- `ContextoClienteController.php`
- `DocumentacionController.php`
- `EstandaresClienteController.php`

---

## 3. Niveles de Riesgo ARL Múltiples

### 3.1 Problema Original

El campo "Nivel de Riesgo ARL" era un select único. Pero una empresa puede tener múltiples niveles de riesgo.

**Ejemplo - Afiancol:**
- Administrativos: Riesgo I
- Servicios generales: Riesgo II
- Comerciales: Riesgo III
- Escoltas: Riesgo V

### 3.2 Solución Implementada

Cambio de select único a **checkboxes múltiples** (como "Turnos de Trabajo").

### 3.3 Código del Formulario

```php
<div class="col-md-6">
    <label class="form-label fw-bold text-danger">Niveles de Riesgo ARL *</label>
    <div class="d-flex flex-wrap gap-3">
        <?php
        $nivelesRiesgoGuardados = json_decode($contexto['niveles_riesgo_arl'] ?? '[]', true) ?: [];
        $nivelesDisponibles = [
            'I' => ['color' => 'success', 'desc' => 'Minimo'],
            'II' => ['color' => 'success', 'desc' => 'Bajo'],
            'III' => ['color' => 'warning', 'desc' => 'Medio'],
            'IV' => ['color' => 'danger', 'desc' => 'Alto'],
            'V' => ['color' => 'danger', 'desc' => 'Maximo']
        ];
        foreach ($nivelesDisponibles as $nivel => $info):
        ?>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="niveles_riesgo_arl[]"
                       value="<?= $nivel ?>" id="riesgo<?= $nivel ?>"
                       <?= in_array($nivel, $nivelesRiesgoGuardados) ? 'checked' : '' ?>>
                <label class="form-check-label" for="riesgo<?= $nivel ?>">
                    <span class="badge bg-<?= $info['color'] ?>">Riesgo <?= $nivel ?></span>
                </label>
            </div>
        <?php endforeach; ?>
    </div>
</div>
```

### 3.4 Almacenamiento en BD

- **Nueva columna:** `niveles_riesgo_arl` (JSON)
- **Columna existente:** `nivel_riesgo_arl` (VARCHAR) - conservada para compatibilidad, guarda el riesgo máximo

---

## 4. Estándares Aplicables Manual

### 4.1 Cambio Realizado

El campo "Estándares Aplicables" ahora es **manual**, definido por el consultor.

### 4.2 Justificación

> "Los estándares se definen por el oficio de la empresa... debe ponerlo manualmente el consultor."

### 4.3 Opciones Disponibles

| Valor | Descripción |
|-------|-------------|
| 7 | Nivel básico (≤10 trabajadores, Riesgo I-III) |
| 21 | Nivel intermedio (11-50 trabajadores, Riesgo I-III) |
| 60 | Nivel completo (>50 trabajadores o Riesgo IV-V) |

---

## 5. Campo Eliminado: Clase de Riesgo Cotización

### 5.1 Campo Removido

"Clase de Riesgo Cotización" fue eliminado del formulario por no ser relevante para el módulo de documentación SST.

### 5.2 Diferencia con Nivel de Riesgo

| Campo | Uso |
|-------|-----|
| **Nivel de Riesgo ARL** | Determinar estándares aplicables (7, 21, 60) |
| **Clase de Riesgo Cotización** | Calcular aportes a seguridad social (Nómina) |

---

## 6. Responsable SG-SST como Selector de Consultores

### 6.1 Cambio Realizado

El campo "Responsable del SG-SST" cambió de múltiples campos de texto a un **selector de consultores**.

### 6.2 Campos Anteriores (Eliminados)

- Nombre del responsable
- Cargo del responsable
- Cédula del responsable
- Número de licencia SST
- Vigencia de licencia

### 6.3 Nuevo Campo

```php
<select name="id_consultor_responsable" class="form-select" required>
    <option value="">Seleccione consultor...</option>
    <?php foreach ($consultores as $consultor): ?>
        <option value="<?= $consultor['id_consultor'] ?>"
                data-cedula="<?= esc($consultor['cedula_consultor'] ?? '') ?>"
                data-licencia="<?= esc($consultor['numero_licencia'] ?? '') ?>">
            <?= esc($consultor['nombre_consultor']) ?>
            - Lic: <?= esc($consultor['numero_licencia']) ?>
        </option>
    <?php endforeach; ?>
</select>
```

### 6.4 Datos Mostrados al Seleccionar

- Cédula del consultor
- Número de licencia SST

Los datos provienen directamente de `tbl_consultor`, evitando duplicación.

---

## 7. SQL de Actualización

### 7.1 Archivo

```
sql_actualizacion_contexto_sst.sql
```

### 7.2 Contenido

```sql
-- 1. Agregar columna para múltiples niveles de riesgo (JSON)
ALTER TABLE tbl_cliente_contexto_sst
ADD COLUMN niveles_riesgo_arl JSON NULL
AFTER nivel_riesgo_arl;

-- 2. Migrar datos existentes
UPDATE tbl_cliente_contexto_sst
SET niveles_riesgo_arl = JSON_ARRAY(nivel_riesgo_arl)
WHERE niveles_riesgo_arl IS NULL
  AND nivel_riesgo_arl IS NOT NULL;

-- 3. Agregar columna para consultor responsable
ALTER TABLE tbl_cliente_contexto_sst
ADD COLUMN id_consultor_responsable INT NULL
AFTER turnos_trabajo;

-- 4. Foreign key
ALTER TABLE tbl_cliente_contexto_sst
ADD CONSTRAINT fk_contexto_consultor
FOREIGN KEY (id_consultor_responsable) REFERENCES tbl_consultor(id_consultor)
ON DELETE SET NULL ON UPDATE CASCADE;
```

---

## 8. Archivos Modificados en Esta Fase

### 8.1 Controladores

| Archivo | Cambios |
|---------|---------|
| `ContextoClienteController.php` | + ConsultantModel, filtro activos, niveles múltiples, id_consultor_responsable |
| `DocumentacionController.php` | Filtro solo activos, sin restricción consultor |
| `EstandaresClienteController.php` | Filtro solo activos, sin restricción consultor |

### 8.2 Modelos

| Archivo | Cambios |
|---------|---------|
| `ClienteContextoSstModel.php` | + id_consultor_responsable, - campos responsable antiguos |

### 8.3 Vistas

| Archivo | Cambios |
|---------|---------|
| `contexto/formulario.php` | Checkboxes riesgo, selector consultor, - clase cotización |
| `contexto/seleccionar_cliente.php` | Select2 con búsqueda |
| `documentacion/seleccionar_cliente.php` | Select2 con búsqueda |
| `estandares/seleccionar_cliente.php` | Select2 con búsqueda |

### 8.4 SQL

| Archivo | Propósito |
|---------|-----------|
| `sql_actualizacion_contexto_sst.sql` | Sincronizar producción con desarrollo |

---

## 9. Estructura Actual del Formulario Contexto SST

### Sección 1: Datos de la Empresa
- Razón Social (readonly)
- NIT (readonly)
- Ciudad (readonly)
- Representante Legal (readonly)
- Cédula Rep. Legal (readonly)
- Actividad Económica (readonly)

### Sección 2: Clasificación Empresarial
- Sector Económico (select)
- Código CIIU Secundario (text)
- **Niveles de Riesgo ARL** (checkboxes múltiples) ⭐
- **Estándares Aplicables** (select manual: 7/21/60) ⭐
- ARL Actual (select)

### Sección 3: Tamaño y Estructura
- Total Trabajadores (number, requerido)
- Trabajadores Directos (number)
- Trabajadores Temporales (number)
- Contratistas Permanentes (number)
- Número de Sedes (number)
- Turnos de Trabajo (checkboxes)

### Sección 4: Información SST
- **Responsable del SG-SST** (selector de consultores) ⭐
- Órganos de Participación (switches):
  - COPASST
  - Vigía SST
  - Comité Convivencia
  - Brigada Emergencias

### Sección 5: Peligros Identificados
- Accordion con 7 categorías de peligros (checkboxes)

### Sección 6: Firmantes de Documentos
- Toggle: Requiere Delegado SST
- Datos Delegado SST (condicional)
- Datos Representante Legal

---

## 10. Próximos Pasos

- [ ] Implementar dashboard de documentación por cliente
- [ ] Sistema de generación de documentos con IA
- [ ] Módulo de firma electrónica
- [ ] Exportación PDF/Word
- [ ] Reportes de cumplimiento

---

## 11. Archivos del Proyecto

```
proyecto_documentacion_sst_parte1.md  -- Conceptos, alcance, estructura general
proyecto_documentacion_sst_parte2.md  -- Prompts IA, wireframes, flujo firmas
proyecto_documentacion_sst_parte3.md  -- BD implementada, stored procedures
proyecto_documentacion_sst_parte4.md  -- (Este archivo) Mejoras contexto SST

sql_actualizacion_contexto_sst.sql    -- SQL para sincronizar producción
```

---

*Documento generado: Enero 2026*
*Proyecto: EnterpriseSST - Módulo de Documentación*
*Parte 4 de 4*
