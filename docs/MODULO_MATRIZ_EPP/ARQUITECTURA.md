# Módulo: Matriz de EPP y Dotación por Cliente

## 1. Objetivo

Generar, para cada cliente, su **Matriz de Elementos de Protección Personal y Dotación** (formato `SST-MT-G-003`) a partir de un **catálogo maestro global** de elementos, mediante un flujo de **asignación masiva** (checkboxes + modal con Select2 de cliente). Incluye **auto-rellenado con IA + web search** de los campos técnicos y **carga manual de fotos** con dimensiones guiadas.

Referencia funcional: `docs/SST-MT-G-003 Matriz de EPP.xlsx`.

## 2. Alcance

- **Catálogo maestro global** (no por cliente): universo de EPP y prendas de dotación reutilizables por todos los clientes.
- **Matriz del cliente**: subconjunto del maestro asignado + datos específicos (pueden sobrescribirse por cliente: ej. frecuencia, observación).
- **IA + web search**: al crear un ítem nuevo en el maestro, basta escribir el nombre; la IA completa norma, mantenimiento, frecuencia, motivos de cambio y momentos de uso.
- **Foto 100% manual**: la sube el usuario. El formulario debe mostrar en el encabezado el tamaño recomendado (ver §7.3).
- Documento formal Tipo A (flujo `secciones_ia`) registrado en el motor SGSST para reutilizar generación, firmas y versionamiento.
- Acceso desde el **dashboard del consultor** (no menú cliente).

## 3. Modelo de datos

### 3.1 `tbl_epp_categoria` (categorías GLOBAL, editable por consultor)

| columna | tipo | descripción |
|---|---|---|
| id_categoria | INT PK AUTO_INCREMENT | |
| nombre | VARCHAR(100) NOT NULL | ej: "Protección para pies" |
| tipo | VARCHAR(20) NOT NULL | 'EPP' o 'DOTACION' (sin ENUM, validación aplicación) |
| orden | INT DEFAULT 0 | orden de presentación |
| activo | TINYINT(1) DEFAULT 1 | |
| created_at / updated_at | TIMESTAMP | |

Seed inicial: Protección pies, manos, auditiva, respiratoria, visual, cabeza, caídas, Dotación cuerpo, Otros. CRUD desde catálogo maestro.

### 3.2 `tbl_epp_maestro` (catálogo GLOBAL, sin `id_cliente`)

| columna | tipo | descripción |
|---|---|---|
| id_epp | INT PK AUTO_INCREMENT | |
| id_categoria | INT FK → tbl_epp_categoria | NOT NULL |
| elemento | VARCHAR(200) NOT NULL | ej: "Botas de seguridad dieléctricas" |
| norma | TEXT NULL | normas técnicas aplicables |
| mantenimiento | TEXT NULL | |
| frecuencia_cambio | VARCHAR(200) NULL | |
| motivos_cambio | TEXT NULL | condiciones que obligan cambio anticipado |
| momentos_uso | TEXT NULL | cuándo debe usarse |
| foto_path | VARCHAR(255) NULL | ruta relativa (`public/uploads/epp_maestro/{id}.jpg`) |
| ia_generado | TINYINT(1) DEFAULT 0 | 1 si campos vinieron de IA sin edición humana (auditoría) |
| activo | TINYINT(1) DEFAULT 1 | |
| created_at / updated_at | TIMESTAMP | |

Índices: `idx_categoria`, `idx_activo`. Único lógico (no restrictivo): `(id_categoria, elemento)`.

### 3.3 `tbl_epp_cliente` (matriz asignada — SNAPSHOT editable del maestro)

| columna | tipo | descripción |
|---|---|---|
| id | INT PK | |
| id_cliente | INT FK → tbl_clientes | ON DELETE CASCADE |
| id_epp | INT FK → tbl_epp_maestro | ON DELETE RESTRICT |
| orden | INT DEFAULT 0 | orden de presentación en el documento |
| elemento | VARCHAR(200) NOT NULL | snapshot del maestro al asignar (editable inline) |
| norma | TEXT NULL | snapshot (editable inline) |
| mantenimiento | TEXT NULL | snapshot (editable inline) |
| frecuencia_cambio | VARCHAR(200) NULL | snapshot (editable inline) |
| motivos_cambio | TEXT NULL | snapshot (editable inline) |
| momentos_uso | TEXT NULL | snapshot (editable inline) |
| observacion_cliente | TEXT NULL | notas propias del cliente |
| sincronizado_maestro | TINYINT(1) DEFAULT 1 | 1 si los campos siguen iguales al maestro; 0 si el consultor editó algo inline |
| fecha_ultima_sync | TIMESTAMP NULL | cuándo fue la última resincronización desde maestro |
| activo | TINYINT(1) DEFAULT 1 | |
| created_at / updated_at | TIMESTAMP | |

Índice único: `(id_cliente, id_epp)`. Índice: `idx_cliente`.

**Política snapshot → editing inline**:
- Al asignar un ítem del maestro al cliente, los 5 campos técnicos + `elemento` se **copian como snapshot** en `tbl_epp_cliente`.
- El consultor edita esos campos **inline** en la matriz del cliente (click en celda → input/textarea → guarda con AJAX → marca `sincronizado_maestro=0`).
- **La edición inline del cliente NUNCA afecta al maestro**. Flujo estrictamente unidireccional: maestro → cliente.
- La **foto siempre se lee del maestro** (`JOIN` con `tbl_epp_maestro.foto_path`) — no se copia ni se edita por cliente.
- Si el maestro cambia después de la asignación, el cliente NO se actualiza automáticamente. Ver §8 (Resincronización).

## 4. Registro en el motor de documentos

### 4.1 `tbl_doc_tipo_configuracion`
```
tipo_documento = 'matriz_epp'
nombre         = 'Matriz de Elementos de Protección Personal y Dotación'
flujo          = 'secciones_ia'
categoria      = 'talento_humano'   (o la que aplique a dotación/EPP)
estandar       = NULL
```

### 4.2 `tbl_doc_secciones_config`

| # | seccion_key | nombre | tipo_contenido | IA |
|---|---|---|---|---|
| 1 | objetivo | Objetivo | texto | sí |
| 2 | alcance | Alcance | texto | sí |
| 3 | marco_legal | Marco legal y normativo | texto | sí |
| 4 | responsabilidades | Responsabilidades (empleador/trabajador) | texto | sí |
| 5 | matriz_epp | Matriz de EPP (por categoría, con foto) | tabla_dinamica | no |
| 6 | matriz_dotacion | Matriz de Dotación | tabla_dinamica | no |
| 7 | entrega_reposicion | Criterios de entrega y reposición | texto | sí |

Secciones 5 y 6 se alimentan desde `getContextoBase()` de la clase PHP.

### 4.3 `tbl_doc_firmantes_config`
1. Representante Legal · 2. Responsable SST · 3. Consultor SST.

## 5. Clase PHP

`app/Libraries/DocumentosSSTTypes/MatrizEpp.php` extiende `DocumentoSSTBase`.
- `getTipoDocumento()` → `'matriz_epp'`.
- Sobrescribe `getContextoBase($idCliente)` para inyectar, haciendo `tbl_epp_cliente ec JOIN tbl_epp_maestro em ON ec.id_epp = em.id_epp JOIN tbl_epp_categoria cat ON em.id_categoria = cat.id_categoria`:
  - `epps_por_categoria`: array agrupado por `cat.nombre`, filtrado `cat.tipo = 'EPP'`.
  - `dotacion_por_categoria`: idem con `cat.tipo = 'DOTACION'`.
  - Para cada ítem: `ec.elemento, ec.norma, ec.mantenimiento, ec.frecuencia_cambio, ec.motivos_cambio, ec.momentos_uso, ec.observacion_cliente, em.foto_path` (los campos vienen del **snapshot del cliente**, la foto del maestro).
- Registro en `DocumentoSSTFactory`.

## 6. Auto-rellenado con IA + Web Search

### 6.1 Endpoint
`POST /matrizEpp/maestro/autocompletar`
- Payload: `{ elemento: "Botas de seguridad dieléctricas", categoria: "PROTECCION_PIES" }`
- Respuesta:
  ```json
  {
    "norma": "...",
    "mantenimiento": "...",
    "frecuencia_cambio": "...",
    "motivos_cambio": "...",
    "momentos_uso": "..."
  }
  ```

### 6.2 Flujo IA
- Modelo: `gpt-4o-mini` (alineado con política Otto), usando la config de OpenAI ya presente en `.env`.
- **Sin web search externo** — se apoya únicamente en el conocimiento del modelo. El usuario SIEMPRE revisa antes de guardar.
- Prompt del sistema: rol "asistente SST experto en EPP y dotación en Colombia"; debe devolver JSON estricto con los 5 campos (`norma`, `mantenimiento`, `frecuencia_cambio`, `motivos_cambio`, `momentos_uso`); citar normas NTC/ANSI/OSHA cuando aplique; lenguaje profesional, sin adornos.
- `response_format: { type: "json_object" }` para forzar JSON válido.
- Frontend: botón "Autocompletar con IA" junto al campo `elemento`; loader mientras consulta; al volver, rellena los 5 textareas permitiendo edición antes de guardar.
- Log via `log_message('info', ...)` con input/output para auditoría.

### 6.3 Bandera `ia_generado`
- Se marca `1` automáticamente si el usuario guarda tras autocompletar sin editar los campos; se marca `0` si edita.
- Permite filtrar "pendientes de validación humana" en el listado del maestro.

## 7. UI

### 7.1 Acceso
- Link "Matriz de EPP — Catálogo Maestro" en el dashboard del consultor.
- Link "Matriz de EPP" dentro del cliente (lleva a la matriz asignada del cliente).

### 7.2 Pantallas

1. **Catálogo maestro** `/matrizEpp/maestro`
   - Tabla agrupada por `categoria` con columnas: foto (thumb), elemento, norma (truncada), activo, acciones (editar, desactivar).
   - Filtros: categoría, tipo (EPP/DOTACION), `ia_generado` sí/no, texto libre.
   - **Checkbox por fila + checkbox "seleccionar todo por categoría"**.
   - Botones de cabecera:
     - `+ Nuevo elemento` → form de alta con autocompletar IA.
     - **`Asignar a cliente`** (activo solo si hay ≥1 selección).
   - Contador sticky "N elementos seleccionados".

2. **Modal "Asignar a cliente"**
   - Select2 de `tbl_clientes` (search server-side).
   - Resumen de los N elementos seleccionados (lista plegable por categoría).
   - Checkbox "Sobrescribir si ya existe en la matriz del cliente" (por defecto NO: si ya existe, se omite y se reporta).
   - Botón `Confirmar asignación` → SweetAlert de confirmación.
   - Al confirmar: `POST /matrizEpp/asignarACliente` con `{id_cliente, ids_epp: [...], sobrescribir: bool}`.
   - Respuesta: `{insertados: N, omitidos: M, errores: []}` + Toast + opción de navegar a la matriz del cliente.

3. **Form nuevo/editar elemento maestro** `/matrizEpp/maestro/nuevo` · `/matrizEpp/maestro/{id}/editar`
   - Campos: categoría (select), tipo (select EPP/DOTACION), elemento, norma, mantenimiento, frecuencia_cambio, motivos_cambio, momentos_uso.
   - Botón "Autocompletar con IA" (§6).
   - **Zona de foto**: input file + preview. Encabezado con texto obligatorio:
     > **Foto recomendada**: JPG, proporción **1:1 (cuadrada)**, **400×400 px**, peso < 100 KB, fondo blanco o neutro, elemento centrado. Así la matriz se ve uniforme y el PDF/Word pesan poco.
   - **Backend siempre normaliza**: acepta JPG/PNG de entrada, redimensiona a 400×400 px (cover/crop centrado), recomprime a JPG calidad 75, guarda como `.jpg`. Archivos >2 MB o con dimensiones <200 px se rechazan con error claro.
   - Almacenamiento: `public/uploads/epp_maestro/{id_epp}.jpg` (siempre `.jpg`, se sobrescribe al reemplazar).

4. **Matriz del cliente** `/matrizEpp/cliente/{id_cliente}`
   - Tabla agrupada por `categoria` con todos los EPP/dotación asignados.
   - Columnas: foto, elemento, norma (maestro), mantenimiento (maestro), frecuencia (override editable inline), motivos (maestro), momentos (maestro), observación cliente (editable inline), acciones (quitar de matriz).
   - Botón "Agregar más del maestro" → redirige a §7.2.1 con filtro del cliente en contexto.
   - Botón "Generar documento con IA" → `/documentos/generar/matriz_epp/{id_cliente}` (flujo Tipo A estándar).

### 7.3 Especificación visual de foto (resumen que se muestra al usuario)

- Formato entrada: JPG o PNG
- Formato salida: JPG (normalizado por backend)
- Proporción: 1:1
- Resolución final: 400×400 px (el backend redimensiona)
- Peso final: < 100 KB (calidad 75)
- Fondo: blanco/neutro · elemento centrado

## 8. Resincronización desde maestro

Cuando el consultor corrige o enriquece un ítem del maestro después de haberlo asignado a clientes, puede propagar esos cambios bajo control.

### 8.1 Alcance
- Solo afecta campos técnicos: `elemento, norma, mantenimiento, frecuencia_cambio, motivos_cambio, momentos_uso`.
- NUNCA toca `observacion_cliente` (es del cliente).
- NUNCA toca la foto (siempre se lee del maestro en tiempo real).

### 8.2 UI
- En la matriz del cliente, cada fila muestra un indicador:
  - ✅ **Sincronizado** (`sincronizado_maestro=1`)
  - ✏️ **Editado localmente** (`sincronizado_maestro=0`, tooltip: "El consultor ajustó este ítem para el cliente").
- Botón por fila: **"Resincronizar desde maestro"** (icono de refresh).
- Botón global en cabecera: **"Resincronizar todos"**.
- Ambos abren SweetAlert con **diff lado a lado** (valor actual del cliente vs. valor del maestro, campo por campo), resaltando diferencias.
- Confirmación destructiva explícita: _"Esto sobrescribe tus ajustes inline con los valores del maestro. ¿Continuar?"_
- Tras confirmar: actualiza los 6 campos, marca `sincronizado_maestro=1`, `fecha_ultima_sync = NOW()`.

### 8.3 Endpoints
- `GET  /matrizEpp/cliente/{id_cliente}/item/{id_epp}/diffMaestro` → `{cliente: {...}, maestro: {...}, cambios: ['norma','mantenimiento']}`
- `POST /matrizEpp/cliente/{id_cliente}/item/{id_epp}/resincronizar` → confirma sobrescritura de una fila.
- `POST /matrizEpp/cliente/{id_cliente}/resincronizarTodos` → body `{ids_epp: [...]}` (el usuario elige cuáles tras ver el diff global).

### 8.4 Regla crítica
**La edición inline del cliente jamás toca `tbl_epp_maestro`.** Flujo estrictamente unidireccional: maestro → cliente. Si el consultor quiere cambiar el maestro, lo hace en la pantalla del catálogo maestro (§7.2.1).

## 9. Endpoints (resumen)

| método | ruta | acción |
|---|---|---|
| GET  | `/matrizEpp/categorias` | CRUD categorías (vista) |
| POST | `/matrizEpp/categorias/guardar` | crear/editar categoría |
| POST | `/matrizEpp/categorias/{id}/desactivar` | soft delete categoría |
| GET  | `/matrizEpp/maestro` | listado maestro |
| GET  | `/matrizEpp/maestro/nuevo` | form alta |
| POST | `/matrizEpp/maestro/guardar` | crear/editar ítem maestro |
| POST | `/matrizEpp/maestro/autocompletar` | IA (gpt-4o-mini, JSON estricto) |
| POST | `/matrizEpp/maestro/{id}/foto` | subir foto (backend normaliza a 400×400 JPG) |
| POST | `/matrizEpp/maestro/{id}/desactivar` | soft delete ítem maestro |
| POST | `/matrizEpp/asignarACliente` | asignación masiva (body: `id_cliente, ids_epp[], sobrescribir`) |
| GET  | `/matrizEpp/cliente/{id_cliente}` | matriz del cliente |
| POST | `/matrizEpp/cliente/{id_cliente}/item/{id_epp}/editarInline` | AJAX edición inline de snapshot |
| POST | `/matrizEpp/cliente/{id_cliente}/item/{id_epp}/quitar` | remover de matriz |
| GET  | `/matrizEpp/cliente/{id_cliente}/item/{id_epp}/diffMaestro` | diff cliente vs. maestro |
| POST | `/matrizEpp/cliente/{id_cliente}/item/{id_epp}/resincronizar` | resincronizar una fila |
| POST | `/matrizEpp/cliente/{id_cliente}/resincronizarTodos` | resincronizar varias filas |

## 10. Checklist de implementación

- [ ] **BD (local)**: `scripts/matriz_epp_schema.php` — crea `tbl_epp_categoria`, `tbl_epp_maestro`, `tbl_epp_cliente` (idempotente `CREATE TABLE IF NOT EXISTS`).
- [ ] **BD (local)**: `scripts/matriz_epp_config.php` — inserta en `tbl_doc_tipo_configuracion` + `tbl_doc_secciones_config` + `tbl_doc_firmantes_config`.
- [ ] **BD (local)**: `scripts/matriz_epp_seed.php` — seed de las 9 categorías iniciales + ítems del Excel de referencia (botas dieléctricas, guantes dieléctricos/mecánicos/caucho, protector auditivo inserción, tapabocas N95, gafas ANSI Z87.1, camisa ML, pantalón jean).
- [ ] **Modelos**: `EppCategoriaModel`, `EppMaestroModel`, `EppClienteModel`.
- [ ] **Controlador**: `MatrizEppController` con todas las rutas de §9.
- [ ] **Servicio IA**: `MatrizEppIaService` (llamada a OpenAI `gpt-4o-mini` con `response_format=json_object`, parseo y validación).
- [ ] **Servicio foto**: helper que redimensiona (GD/Imagick) a 400×400 JPG calidad 75.
- [ ] **Clase PHP doc**: `MatrizEpp.php` extendiendo `DocumentoSSTBase` + registro en `DocumentoSSTFactory`.
- [ ] **Vistas**: CRUD categorías, listado maestro (con checkboxes + modal asignación), form elemento (con botón IA y zona foto con encabezado de dimensiones), matriz cliente (con editing inline + indicadores sync + modal diff).
- [ ] **Rutas** en `Config/Routes.php`.
- [ ] **Dashboard consultor**: enlace "Catálogo Maestro EPP".
- [ ] **Verificación local**: crear categoría, crear ítem con IA, subir foto, asignar a cliente de prueba, editar inline, resincronizar, generar documento y validar secciones 5/6.
- [ ] **Producción**: ejecutar los 3 scripts BD en orden (schema → config → seed).
