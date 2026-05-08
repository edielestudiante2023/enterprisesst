# Vincular Reportes del reportList a una Carpeta

> Permite traer un reporte existente del `/reportList` (`tbl_reporte`) como
> **referencia visual** dentro de una carpeta de documentacion, **SIN** duplicar
> el archivo PDF.

---

## El Problema que Resuelve

Hasta ahora, si el consultor tenia un PDF en el reportList del cliente y queria
mostrarlo dentro de una carpeta del modulo de documentacion (`/documentacion/{id}`),
la unica opcion era **subir el archivo de nuevo** como soporte. Esto:

- Duplicaba el archivo en disco (`uploads/{nit}/...` y `uploads/{nit}/...`).
- Generaba dos versiones que podian desincronizarse.
- Inflaba el reportList con copias.
- Confundia al cliente que veia el mismo PDF dos veces con codigos diferentes.

**Solucion:** un sistema de **vinculos logicos** carpeta ↔ reporte. La carpeta
muestra el reporte como referencia, apuntando al `enlace` original del reporte.

---

## Modelo de Datos

```
tbl_carpeta_reporte_vinculo
  ├─ id_vinculo  (PK)
  ├─ id_carpeta  -> tbl_doc_carpetas
  ├─ id_reporte  -> tbl_reporte
  ├─ id_cliente  (denorm para validar consistencia y queries rapidas)
  ├─ observacion (opcional, por que se vinculo)
  ├─ created_by, created_at
  └─ UNIQUE (id_carpeta, id_reporte)
```

Relacion N:M: un reporte puede vincularse a varias carpetas; una carpeta puede
vincular varios reportes.

**No tocamos** `tbl_reporte` ni `tbl_documentos_sst`. Si el reporte se borra del
reportList, los vinculos quedan huerfanos — el JOIN INNER en `getByCarpeta()` los
filtra automaticamente.

Script DDL: `scripts/crear_tbl_carpeta_reporte_vinculo.php` (idempotente, dry-run/apply).

---

## Componentes

### Backend

- **`App\Models\CarpetaReporteVinculoModel`**
  - `getByCarpeta(int $idCarpeta): array` — vinculados con detalle del reporte (JOIN
    a `tbl_reporte`, `report_type_table`, `detail_report`).
  - `existeVinculo(int $idCarpeta, int $idReporte): bool` — anti-duplicado.

- **`App\Controllers\VinculoReporteController`**
  - `GET /documentacion/vinculo/reportes-disponibles/{idCliente}` — JSON para Select2 AJAX
    con busqueda. Soporta `?q=texto` y `?id_carpeta=N` (este ultimo marca los ya
    vinculados con `ya_vinculado: true` para deshabilitarlos en el dropdown).
  - `POST /documentacion/vinculo/agregar` — crea vinculo. **Valida que el reporte
    pertenezca al mismo cliente que la carpeta** (defensa contra cross-cliente).
  - `POST /documentacion/vinculo/{idVinculo}/quitar` — borra el vinculo. NO toca
    el reporte ni el archivo.

### Frontend

- **`app/Views/documentacion/_components/vincular_reportes.php`** — componente
  reusable que se incluye en `documentacion/carpeta.php`. Aplica a TODAS las
  carpetas tipo (no a `raiz` ni `phva`).
  - Card "Documentos vinculados desde el reportList".
  - Boton "+ Vincular documento existente" abre modal con Select2 AJAX.
  - Tabla con vinculos actuales, badges por tipo, y boton "Quitar vinculo".

### Asset loading

El componente carga **Select2** desde CDN una sola vez por pagina (gate
`VINCULAR_REPORTES_ASSETS_LOADED`). jQuery ya lo carga `_components/scripts.php`.

---

## Como Funciona Visualmente

1. Consultor entra a una carpeta del cliente (ej. `/documentacion/carpeta/409`).
2. Al final de la vista (despues del contenido tipo) aparece la card gris
   "Documentos vinculados desde el reportList".
3. Click en "+ Vincular documento existente" → modal con Select2.
4. El consultor escribe en el buscador → AJAX trae reportes del cliente
   (titulo, tipo, fecha). Los ya vinculados se marcan `YA VINCULADO`.
5. Selecciona uno, opcionalmente anade una observacion, click "Vincular".
6. La pagina recarga y el reporte aparece en la tabla con boton "Ver" (abre el
   `enlace` original en nueva pestana) y "Quitar".

**No se copia ningun archivo.** El reporte sigue siendo el mismo del reportList.

---

## Diferencias clave con "Subir Soporte"

| Aspecto | Subir Soporte (actual) | Vincular Reporte (nuevo) |
|---------|------------------------|--------------------------|
| Crea archivo nuevo | Si (subido por usuario) | **No** |
| Tabla destino | `tbl_documentos_sst` (interno) | `tbl_carpeta_reporte_vinculo` (puente) |
| Aparece en `/reportList` del cliente | No (solo en la carpeta) | Si (era ya parte del reportList) |
| Quitar borra archivo | Si | **No, solo quita el vinculo** |
| Util cuando | Soporte propio de la carpeta (acta firmada, evidencia subida) | El doc ya existe en el reportList y solo quiere mostrarse aqui |

---

## Como Replicar el Patron en Otros Modulos

Si en el futuro quieres que OTRO listado de archivos del cliente pueda vincularse
a otra entidad sin duplicar, sigue este patron:

### 1. Tabla puente
```sql
CREATE TABLE tbl_{entidad_destino}_{fuente}_vinculo (
    id_vinculo INT AUTO_INCREMENT PRIMARY KEY,
    id_{destino} INT NOT NULL,
    id_{fuente}  INT NOT NULL,
    id_cliente   INT NOT NULL,
    -- otros campos opcionales
    UNIQUE KEY (id_{destino}, id_{fuente}),
    INDEX idx_destino (id_{destino})
);
```

### 2. Modelo con `getBy{Destino}()` que hace INNER JOIN al recurso fuente

### 3. Controller con 3 endpoints
- `GET /modulo/vinculo/disponibles/{idCliente}` — JSON Select2 con busqueda.
- `POST /modulo/vinculo/agregar` — valida ownership por id_cliente, anti-duplicado.
- `POST /modulo/vinculo/{id}/quitar` — solo borra el vinculo.

### 4. Componente UI
- Modal con Select2 AJAX (parametros `?q=` y `?id_destino=` para marcar ya-vinculados).
- Tabla de vinculados con accion "Ver" (abre el enlace original) y "Quitar".

### 5. NUNCA copiar archivos

El criterio de "vincular vs subir":
- Si el recurso es **inmutable y compartible** (un PDF generado, una evidencia ya subida,
  un documento aprobado) → **vincular**.
- Si el recurso es **especifico de esa carpeta** (una version firmada para esa carpeta
  particular) → **subir copia**.

---

## Referencias

- DDL: `scripts/crear_tbl_carpeta_reporte_vinculo.php`
- Modelo: `app/Models/CarpetaReporteVinculoModel.php`
- Controller: `app/Controllers/VinculoReporteController.php`
- Componente UI: `app/Views/documentacion/_components/vincular_reportes.php`
- Integracion: `app/Views/documentacion/carpeta.php` y
  `app/Controllers/DocumentacionController::carpeta` (variable `reportesVinculados`)
