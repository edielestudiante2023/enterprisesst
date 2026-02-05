# Guía del Módulo Matriz Legal SST

## Descripción General

El módulo **Matriz Legal** permite gestionar toda la normativa aplicable en Seguridad y Salud en el Trabajo (SST) de manera centralizada. Cumple con los requisitos del Decreto 1072 de 2015 y la Resolución 0312 de 2019 que exigen identificar y mantener actualizada la normativa legal vigente.

---

## Acceso al Módulo

1. Ingresar al sistema como **Consultor** o **Administrador**
2. En el Dashboard, ubicar la sección de documentación
3. Clic en el botón **"Matriz Legal"**
4. URL directa: `/matriz-legal`

---

## Funcionalidades Principales

### 1. Vista Principal (Listado)

La tabla principal muestra todas las normas con:

| Columna | Descripción |
|---------|-------------|
| **+** | Expandir para ver detalles completos |
| **Sector** | Industria a la que aplica (General, Salud, Construcción, etc.) |
| **Tipo** | Ley, Decreto, Resolución, Circular, etc. |
| **Norma** | Número identificador de la norma |
| **Año** | Año de expedición |
| **Tema** | Tema principal y subtema |
| **Autoridad** | Entidad que expide la norma |
| **Estado** | Activa, Derogada o Modificada |
| **Acciones** | Editar / Eliminar |

---

## Paso a Paso: Agregar Normas

### Opción A: Agregar Manualmente

1. Clic en **"Nueva Norma"** (botón dorado)
2. Completar el formulario:
   - **Sector**: Seleccionar el sector económico (por defecto "General")
   - **Tema**: Ej. "Sistema General de Riesgos Laborales"
   - **Subtema**: Ej. "Accidente de Trabajo" (opcional)
   - **Tipo de Norma**: Ley, Decreto, Resolución, etc.
   - **No. Norma**: El número (ej. "0312", "1072")
   - **Año**: Año de expedición
   - **Descripción**: Resumen de qué establece la norma
   - **Autoridad**: Quién la expide (ej. "Ministerio del Trabajo")
   - **Referentes**: Marcar si aplica nacional/internacional
   - **Artículos Aplicables**: Artículos específicos relevantes
   - **Parámetros**: Requisitos y detalles de cumplimiento
   - **Notas de Vigencia**: Modificaciones, derogatorias, observaciones
3. Clic en **"Guardar"**

### Opción B: Buscar con Inteligencia Artificial (Recomendado)

Esta es la forma más rápida y precisa de agregar normas:

1. Clic en **"Buscar con IA"** (botón dorado en navbar)
2. En el campo de búsqueda, escribir el nombre de la norma:
   - Ejemplos válidos:
     - "Resolución 0312 de 2019"
     - "Decreto 1072 de 2015"
     - "Ley 1562 de 2012"
     - "Circular 0017 de 2024"
3. Clic en **"Buscar"**
4. La IA buscará en internet información actualizada sobre la norma
5. Revisar los datos encontrados (todos los campos se llenan automáticamente)
6. Editar si es necesario
7. Clic en **"Guardar en Matriz Legal"**

**Ventajas de la búsqueda con IA:**
- Encuentra información de normas recientes (usa búsqueda web en tiempo real)
- Completa automáticamente todos los campos
- Identifica artículos aplicables a SST
- Incluye notas de vigencia actualizadas

**Si la IA no encuentra la norma:**
- Se mostrará un formulario manual
- Los datos básicos de la búsqueda se pre-llenan
- Completar manualmente los campos faltantes

### Opción C: Importar desde CSV (Masivo)

Para importar múltiples normas de una vez:

1. Clic en **"Importar CSV"** en la barra de navegación
2. Descargar el **archivo de muestra** para ver el formato correcto
3. Preparar el archivo CSV con las columnas:
   ```
   TEMA; SUBTEMA; TIPO DE NORMA; ID NORMA LEGAL; AÑO; DESCRIPCIÓN;
   AUTORIDAD; REFERENTE NACIONAL; REFERENTE INTERNACIONAL;
   ARTÍCULOS APLICABLES; PARÁMETROS; NOTAS VIGENCIAS
   ```
4. Seleccionar el **Sector por defecto** para las normas importadas
5. Arrastrar el archivo o clic para seleccionar
6. Revisar la **vista previa** (primeras 10 filas)
7. Clic en **"Importar"**

**Formatos soportados:**
- Delimitadores: `;` (punto y coma), `,` (coma), `TAB`, `|`
- Codificación: UTF-8, ISO-8859-1, Windows-1252

---

## Uso de Filtros

### Filtros en la Tabla

Cada columna tiene un filtro en la segunda fila del encabezado:

| Filtro | Tipo | Uso |
|--------|------|-----|
| **Sector** | Desplegable | Seleccionar un sector específico |
| **Tipo** | Desplegable | Filtrar por tipo de norma |
| **Norma** | Texto | Buscar por número de norma |
| **Año** | Desplegable | Filtrar por año específico |
| **Tema** | Texto | Buscar términos en el tema |
| **Autoridad** | Texto | Buscar por entidad emisora |
| **Estado** | Desplegable | Activa, Derogada, Modificada |

### Combinar Filtros

Los filtros se pueden combinar. Ejemplo:
- Sector: "Construcción" + Estado: "Activa" = Solo normas activas de construcción

### Limpiar Filtros

- Cuando hay filtros activos, aparece el botón **"Limpiar Filtros"**
- También muestra un badge con la cantidad de filtros activos

### Búsqueda Global

- El campo de búsqueda de DataTables busca en todas las columnas a la vez
- Útil para búsquedas rápidas sin filtros específicos

---

## Ver Detalles de una Norma

1. Clic en el ícono **"+"** al inicio de la fila
2. Se expande una sección con:
   - Descripción completa
   - Artículos aplicables
   - Parámetros y requisitos
   - Notas de vigencia

---

## Editar una Norma

1. Clic en el botón de **editar** (ícono lápiz)
2. Modificar los campos necesarios
3. Clic en **"Guardar"**

---

## Eliminar una Norma

1. Clic en el botón de **eliminar** (ícono papelera)
2. Confirmar la acción en el diálogo
3. La norma se elimina permanentemente

---

## Exportar la Matriz Legal

1. Clic en **"Exportar"** en la barra de navegación
2. Se descarga un archivo CSV con todas las normas
3. Compatible con Excel (incluye BOM UTF-8)

---

## Ventajas Comerciales

### Para Consultores SST

| Ventaja | Beneficio |
|---------|-----------|
| **Ahorro de tiempo** | La IA completa automáticamente los datos de las normas |
| **Información actualizada** | Búsqueda web en tiempo real para normas recientes |
| **Profesionalismo** | Matriz legal completa y bien estructurada para entregar a clientes |
| **Cumplimiento normativo** | Demuestra cumplimiento del requisito legal de identificar normativa |
| **Diferenciador** | Servicio de valor agregado con tecnología de IA |

### Para Empresas/Clientes

| Ventaja | Beneficio |
|---------|-----------|
| **Centralización** | Toda la normativa en un solo lugar |
| **Trazabilidad** | Historial de cambios y estados |
| **Auditorías** | Lista para mostrar a auditores y ARL |
| **Actualización** | Fácil agregar nuevas normas cuando se expiden |
| **Filtrado por sector** | Solo ver normas que aplican a su industria |

---

## Ventajas Técnicas

### Arquitectura

- **Server-side processing**: Maneja miles de normas sin afectar rendimiento
- **Filtros en tiempo real**: Consultas optimizadas a la base de datos
- **API RESTful**: Fácil integración con otros sistemas

### Inteligencia Artificial

- **OpenAI GPT-4o con Web Search**: Busca información actualizada en internet
- **Endpoint de Responses**: Usa la última tecnología de OpenAI
- **Fallback inteligente**: Si la IA falla, ofrece entrada manual con datos pre-llenados

### Importación CSV

- **Detección automática de delimitador**: Compatible con diferentes formatos
- **Mapeo flexible de columnas**: Acepta variaciones en nombres de columnas
- **Validación de datos**: Verifica campos obligatorios antes de importar

### Base de Datos

- **Índices optimizados**: Búsquedas rápidas por sector, tipo, año
- **Soporte multiempresa**: Preparado para escalar
- **Sincronización**: Funciona en local y producción

---

## Casos de Uso Recomendados

### 1. Construcción Inicial de la Matriz

```
Paso 1: Importar CSV con normas base (Decreto 1072, Res. 0312, etc.)
Paso 2: Usar IA para agregar normas específicas del sector
Paso 3: Revisar y completar información faltante
```

### 2. Actualización por Nueva Normativa

```
Paso 1: Enterarse de nueva norma (ej. "Resolución 40595 de 2024")
Paso 2: Ir a "Buscar con IA"
Paso 3: Escribir el nombre de la norma
Paso 4: Revisar y guardar
```

### 3. Preparación para Auditoría

```
Paso 1: Filtrar por sector del cliente
Paso 2: Filtrar por estado "Activa"
Paso 3: Exportar a CSV
Paso 4: Entregar al auditor o incluir en informe
```

### 4. Revisión de Vigencia

```
Paso 1: Filtrar por estado "Derogada" o "Modificada"
Paso 2: Verificar si hay normas que reemplazaron
Paso 3: Agregar nuevas normas vigentes
Paso 4: Actualizar notas de vigencia
```

---

## Normas Base Recomendadas

Al iniciar, se recomienda tener al menos estas normas:

| Norma | Descripción |
|-------|-------------|
| Decreto 1072 de 2015 | Decreto Único Reglamentario del Sector Trabajo |
| Resolución 0312 de 2019 | Estándares Mínimos del SG-SST |
| Ley 1562 de 2012 | Sistema General de Riesgos Laborales |
| Resolución 2400 de 1979 | Estatuto de Seguridad Industrial |
| Resolución 2013 de 1986 | COPASST |
| Resolución 1401 de 2007 | Investigación de accidentes |
| Resolución 2346 de 2007 | Evaluaciones médicas ocupacionales |
| Decreto 1295 de 1994 | Organización del SGRL |

---

## Soporte

Para dudas o sugerencias sobre el módulo:
- Contactar al administrador del sistema
- Revisar la documentación técnica en `/docs`

---

*Documento generado para EnterpriseSST - Módulo Matriz Legal v1.0*
