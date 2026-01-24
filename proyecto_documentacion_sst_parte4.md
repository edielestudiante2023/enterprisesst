# Proyecto de Documentacion SST - Parte 4

## Resumen

Esta parte documenta las mejoras y simplificaciones implementadas en el modulo de Contexto SST y los selectores de cliente del sistema.

---

## 1. Mejoras en Selectores de Cliente

### 1.1 Solucion Implementada

Se implemento **Select2** con busqueda en todos los modulos:

- Busqueda por nombre del cliente
- Busqueda por NIT
- Interfaz limpia y consistente

### 1.2 Archivos Modificados

| Archivo | Modulo |
|---------|--------|
| app/Views/contexto/seleccionar_cliente.php | Contexto SST |
| app/Views/documentacion/seleccionar_cliente.php | Documentacion |
| app/Views/estandares/seleccionar_cliente.php | Estandares PHVA |

---

## 2. Filtrado de Clientes Activos

### 2.1 Cambio Realizado

Todos los controladores ahora muestran **solo clientes activos** sin filtrar por consultor.

### 2.2 Justificacion

> "Un consultor puede ver todos los clientes. Eso complica mucho si alguien se incapacita, renuncia o falta al trabajo."

### 2.3 Codigo Actualizado

```php
// Obtener todos los clientes activos (sin filtrar por consultor)
$clientes = $this->clienteModel
    ->where('estado', 'activo')
    ->orderBy('nombre_cliente', 'ASC')
    ->findAll();
```

### 2.4 Controladores Modificados

- ContextoClienteController.php
- DocumentacionController.php
- EstandaresClienteController.php

---

## 3. Niveles de Riesgo ARL Multiples

### 3.1 Cambio Realizado

El campo "Nivel de Riesgo ARL" cambio de select unico a **checkboxes multiples**.

**Ejemplo - Afiancol:**

- Administrativos: Riesgo I
- Servicios generales: Riesgo II
- Comerciales: Riesgo III
- Escoltas: Riesgo V

### 3.2 Almacenamiento en BD

- **Columna:** `niveles_riesgo_arl` (JSON)
- **Formato:** `["I", "III", "V"]`

---

## 4. Estandares Aplicables - MANUAL

### 4.1 Cambio Critico

El campo "Estandares Aplicables" ahora es **MANUAL**, definido por el consultor.

### 4.2 Justificacion

> "Los estandares se definen por el oficio de la empresa... debe ponerlo manualmente el consultor."

### 4.3 Opciones Disponibles

| Valor | Descripcion |
|-------|-------------|
| 7 | Nivel basico |
| 21 | Nivel intermedio |
| 60 | Nivel completo |

**El sistema NO calcula automaticamente el nivel.** El consultor lo define basado en:

- Oficio/actividad de la empresa
- Criterio profesional
- Proteccion legal ante accidentes

---

## 5. Campo Eliminado: Clase de Riesgo Cotizacion

### 5.1 Campo Removido

"Clase de Riesgo Cotizacion" fue eliminado del formulario por no ser relevante para el modulo de documentacion SST.

### 5.2 Diferencia con Nivel de Riesgo

| Campo | Uso |
|-------|-----|
| **Nivel de Riesgo ARL** | Contexto para documentos SST |
| **Clase de Riesgo Cotizacion** | Calcular aportes a seguridad social (Nomina) |

---

## 6. Responsable SG-SST como Selector de Consultores

### 6.1 Cambio Realizado

El campo "Responsable del SG-SST" cambio de multiples campos de texto a un **selector de consultores**.

### 6.2 Campos Anteriores (Eliminados)

- Nombre del responsable
- Cargo del responsable
- Cedula del responsable
- Numero de licencia SST
- Vigencia de licencia

### 6.3 Nuevo Campo

**Selector:** `id_consultor_responsable` (FK a tbl_consultor)

Al seleccionar, se muestran automaticamente:

- Cedula del consultor
- Numero de licencia SST

---

## 7. Estructura Actual del Formulario Contexto SST

### Seccion 1: Datos de la Empresa

- Razon Social (readonly)
- NIT (readonly)
- Ciudad (readonly)
- Representante Legal (readonly)
- Cedula Rep. Legal (readonly)
- Actividad Economica (readonly)

### Seccion 2: Clasificacion Empresarial

- Sector Economico (select)
- Codigo CIIU Secundario (text)
- **Niveles de Riesgo ARL** (checkboxes multiples)
- **Estandares Aplicables** (select manual: 7/21/60)
- ARL Actual (select)

### Seccion 3: Tamano y Estructura

- Total Trabajadores (number, requerido)
- Trabajadores Directos (number)
- Trabajadores Temporales (number)
- Contratistas Permanentes (number)
- Numero de Sedes (number)
- Turnos de Trabajo (checkboxes)

### Seccion 4: Informacion SST

- **Responsable del SG-SST** (selector de consultores)
- Organos de Participacion (switches):
  - COPASST
  - Vigia SST
  - Comite Convivencia
  - Brigada Emergencias

### Seccion 5: Peligros Identificados

- Accordion con 7 categorias de peligros (checkboxes)

### Seccion 6: Firmantes de Documentos

- Toggle: Requiere Delegado SST
- Datos Delegado SST (condicional)
- Datos Representante Legal

---

## 8. Archivos Modificados en Esta Fase

### 8.1 Controladores

| Archivo | Cambios |
|---------|---------|
| ContextoClienteController.php | + ConsultantModel, filtro activos, niveles multiples |
| DocumentacionController.php | Filtro solo activos |
| EstandaresClienteController.php | Filtro solo activos |

### 8.2 Modelos

| Archivo | Cambios |
|---------|---------|
| ClienteContextoSstModel.php | + id_consultor_responsable, - campos responsable antiguos |

### 8.3 Vistas

| Archivo | Cambios |
|---------|---------|
| contexto/formulario.php | Checkboxes riesgo, selector consultor |
| contexto/seleccionar_cliente.php | Select2 con busqueda |
| documentacion/seleccionar_cliente.php | Select2 con busqueda |
| estandares/seleccionar_cliente.php | Select2 con busqueda |

---

*Documento actualizado: Enero 2026*
*Proyecto: EnterpriseSST - Modulo de Documentacion*
*Parte 4 de 7*
