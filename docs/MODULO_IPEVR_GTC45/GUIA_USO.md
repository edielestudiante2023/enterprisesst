# Guía de uso — Módulo IPEVR GTC 45

## Acceso

- **Lista de matrices por cliente:** `/ipevr/cliente/{id_cliente}`
- **Editor PC:** `/ipevr/matriz/{id_matriz}/editar`
- **Editor PWA móvil:** `/ipevr/matriz/{id_matriz}/pwa`
- **Maestros del cliente:** `/maestros-cliente/{id_cliente}`
- **Catálogo GTC 45 (JSON):** `/ipevr/catalogo`

## Flujo del consultor

### 1. Antes de crear la matriz — cargar maestros

Ve a `/maestros-cliente/{id_cliente}` y registra:
- **Procesos** (estratégico / misional / apoyo / evaluación).
- **Cargos** (opcionalmente asociados a un proceso).
- **Tareas** (rutinarias / no rutinarias).
- **Zonas** (asociadas a sedes del cliente).

Los maestros son opcionales — la matriz permite también texto libre — pero al cargarlos, los dropdowns del editor IPEVR se autocompletan y puedes reutilizarlos en otros módulos.

### 2. Crear una matriz

Desde `/ipevr/cliente/{id}`, botón **Nueva matriz IPEVR** → modal pide nombre y versión (001 por defecto). La matriz arranca en estado `borrador`.

### 3. Llenar filas

**Opción A — IA:** botón **Sugerir con IA** → pide cantidad (5-50) → llama `gpt-4o-mini` con el contexto del cliente + peligros identificados + maestros → inserta filas pre-diligenciadas marcadas como `origen_fila='ia'`.

**Opción B — Manual:** botón **+ Agregar fila** → modal con 6 pestañas:
1. Proceso / Zona / Actividad / Tarea / Rutinaria / Cargos / Nº expuestos.
2. Peligro (catálogo agrupado por clasificación) / Descripción / Efectos posibles.
3. Controles existentes (fuente / medio / individuo).
4. **Evaluación:** ND + NE + NC → NP y NR se calculan en vivo con color semáforo según el catálogo GTC 45.
5. Peor consecuencia / Requisito legal.
6. Medidas de intervención (eliminación / sustitución / ingeniería / administrativo / EPP).

### 4. Versionamiento

Menú `⋮` en el editor:
- **Enviar a revisión** (`borrador` → `revision`).
- **Aprobar** (`revision` → `vigente` + snapshot JSON + registro en control de cambios).
- **Nueva versión** (desde `vigente`: la actual pasa a `historica`, se crea una copia en `borrador` con versión siguiente y las filas copiadas como `origen_fila='importada'`).

### 5. Exportar

- **Excel (XLSX):** réplica de `FT-SST-035` con 3 hojas (Matriz, Tablas de evaluación, Instructivo). Incluye colores de nivel de riesgo.
- **PDF:** orientación landscape A3 con las columnas principales y badge coloreado de nivel.

## PWA móvil

Al abrir `/ipevr/matriz/{id}/pwa` en un dispositivo móvil/tablet:
- Lista de filas como cards con nivel de riesgo visible.
- FAB "+" → wizard multi-paso (6 pasos) adaptado a pantalla táctil.
- Autosave online; si no hay conexión, las filas se encolan en `localStorage` y se sincronizan al volver online.
- Indicador de estado online/offline en la barra inferior.
- Instalable como app independiente (manifest PWA).

## Cálculo GTC 45 (referencia rápida)

| Parámetro | Valores |
|-----------|---------|
| ND (Nivel de Deficiencia) | MA=10, A=6, M=2, B=0 |
| NE (Nivel de Exposición) | EC=4, EF=3, EO=2, EE=1 |
| NP (Nivel de Probabilidad) | `ND × NE` — rangos MA:40-24, A:20-10, M:8-6, B:4-2 |
| NC (Nivel de Consecuencia) | M=100, MG=60, G=25, L=10 |
| NR (Nivel de Riesgo) | `NP × NC` — I:4000-600, II:500-150, III:120-40, IV:≤20 |

**Aceptabilidad:**
- Nivel I → No Aceptable
- Nivel II → No Aceptable o Aceptable con control específico
- Nivel III → Aceptable
- Nivel IV → Aceptable
