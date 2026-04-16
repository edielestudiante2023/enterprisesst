# Modulo Profesiograma - Arquitectura

> Documento vivo, sin versionamiento ni firmas.
> Un profesiograma por cliente+cargo (universos independientes).
> Modulo independiente — NO integrado con documentos SST.

## Concepto

El profesiograma define **que examenes medicos ocupacionales** debe realizarse a cada cargo
de la empresa, cruzando los **peligros identificados en la Matriz IPEVR GTC 45**.

Normativa: Resolucion 2346/2007, Resolucion 1918/2009, Decreto 1072/2015 Art 2.2.4.6.24.

## Flujo de negocio

```
IPEVR (existente)                       PROFESIOGRAMA (este modulo)
+-----------------------+              +-------------------------------+
| Cargo: Auxiliar       |              | Cargo: Auxiliar de Bodega     |
| Peligros:             |  --cruza-->  | Examenes ingreso:             |
|  - Biomecanico        |              |   * Osteomuscular             |
|  - Quimico            |              |   * Visiometria               |
|  - Locativo           |              | Examenes periodicos:          |
|                       |              |   * Espirometria (anual)      |
| NC: Grave             |              | Examenes retiro:              |
| NR: 300 (II)          |              |   * Osteomuscular             |
+-----------------------+              | Restricciones:                |
                                       |   No manipular >25kg          |
                                       +-------------------------------+
```

## Regla critica: IPEVR obligatorio

Si el cliente NO tiene matriz IPEVR con estado `vigente` o `aprobada`,
el modulo muestra mensaje: **"No es posible generar el profesiograma.
Primero debe completar y aprobar la Matriz IPEVR GTC 45."**

## Modelo de datos

### Tabla catalogo global: `tbl_profesiograma_examenes_catalogo`

| Campo | Tipo | Descripcion |
|-------|------|-------------|
| id | INT PK AI | |
| nombre | VARCHAR(200) | Nombre del examen |
| tipo_examen | ENUM | laboratorio, imagenologia, funcional, psicologico, especialista |
| descripcion | TEXT | Descripcion del examen |
| clasificaciones_aplica | JSON | Array de codigos GTC45: ["biomecanico","quimico"] |
| normativa_referencia | VARCHAR(200) | Ej: "Res 2346/2007 Art 5" |
| activo | TINYINT(1) | 1=activo |
| orden | INT | Orden de presentacion |

Seed: ~20 examenes tipicos colombianos (visiometria, audiometria, espirometria, etc).

### Tabla operacional: `tbl_profesiograma_cliente`

| Campo | Tipo | Descripcion |
|-------|------|-------------|
| id | INT PK AI | |
| id_cliente | INT FK | Cliente |
| id_cargo | INT FK NULL | FK a tbl_cargos_cliente |
| cargo_texto | VARCHAR(200) | Fallback texto libre si no hay maestro |
| id_examen | INT FK | FK a catalogo |
| momento | ENUM | ingreso, periodico, retiro, cambio_cargo |
| frecuencia | VARCHAR(50) | anual, semestral, cada_2_anios, unica_vez |
| obligatorio | TINYINT(1) | 1=obligatorio, 0=recomendado |
| observaciones | TEXT | Notas adicionales |
| origen | ENUM | manual, ia, ipevr |
| activo | TINYINT(1) | 1=activo |
| created_at | TIMESTAMP | |
| updated_at | TIMESTAMP | |

Indices: idx_cliente, idx_cargo, idx_examen.
FKs: id_cliente -> tbl_clientes, id_cargo -> tbl_cargos_cliente, id_examen -> catalogo.

### Dashboard item

Card en dashboard del Consultor:
- Detalle: "Profesiograma"
- URL: `/profesiograma/cliente/{id_cliente}`
- Categoria: Operacion por Cliente
- Icono: `fas fa-stethoscope`
- Color: `#bd9751,#d4af37` (dorado corporativo)

## Rutas

```
GET  /profesiograma/cliente/(:num)                -> index (lista cargos)
GET  /profesiograma/cliente/(:num)/cargo/(:num)   -> editorCargo (examenes del cargo)
POST /profesiograma/asignar                       -> asignarExamen (upsert)
POST /profesiograma/quitar/(:num)                 -> quitarExamen (soft-delete)
POST /profesiograma/generar-ipevr/(:num)          -> generarDesdeIpevr (cruce automatico)
GET  /profesiograma/catalogo-json                 -> catalogoJson (examenes disponibles)
```

## Controller: ProfesiogramaController

### index($idCliente)
1. Validar que existe IPEVR vigente/aprobada del cliente
2. Si no existe: vista con mensaje de bloqueo
3. Si existe: cargar cargos del cliente + conteo examenes por cargo
4. Vista tabla cruzada: filas=cargos, columnas=momentos, celdas=conteo examenes

### editorCargo($idCliente, $idCargo)
1. Cargar examenes asignados al cargo (tbl_profesiograma_cliente)
2. Cargar catalogo completo de examenes
3. Vista con checkboxes/toggle por examen y momento
4. Guardar via AJAX (POST /profesiograma/asignar)

### generarDesdeIpevr($idCliente)
1. Obtener IPEVR vigente del cliente
2. Leer tbl_ipevr_fila: extraer pares (cargos_expuestos JSON, id_clasificacion)
3. Para cada cargo unico:
   a. Obtener clasificaciones de peligro del cargo
   b. Cruzar contra catalogo.clasificaciones_aplica
   c. INSERT examenes que aplican (momento=ingreso+periodico+retiro segun tipo)
4. Marcar origen='ipevr'
5. Retornar conteo de examenes generados

### asignarExamen() - POST AJAX
```json
{
  "id": 0,
  "id_cliente": 18,
  "id_cargo": 42,
  "id_examen": 5,
  "momento": "periodico",
  "frecuencia": "anual",
  "obligatorio": 1,
  "observaciones": ""
}
```
Patron upsert: id=0 insert, id>0 update.

## Vista principal (tabla cruzada)

```
+-------------------+--------+-----------+-----------+-------+
| Cargo             | #Total | Ingreso   | Periodico | Retiro|
+-------------------+--------+-----------+-----------+-------+
| Aux Bodega        | 8      | 4 exam    | 3 exam    | 1 exam| [Editar]
| Conductor         | 10     | 5 exam    | 4 exam    | 1 exam| [Editar]
| Administrativo    | 6      | 3 exam    | 2 exam    | 1 exam| [Editar]
+-------------------+--------+-----------+-----------+-------+
                                          [Generar desde IPEVR]
```

## Generacion automatica desde IPEVR

Algoritmo de cruce:

```
Para cada fila IPEVR del cliente:
  cargos = JSON_DECODE(cargos_expuestos)     // ["Auxiliar","Conductor"]
  clasificacion = codigo de id_clasificacion  // "biomecanico"

  Para cada cargo:
    examenes = SELECT FROM catalogo WHERE JSON_CONTAINS(clasificaciones_aplica, clasificacion)
    Para cada examen:
      INSERT IGNORE en tbl_profesiograma_cliente (cargo, examen, momento=ingreso)
      INSERT IGNORE en tbl_profesiograma_cliente (cargo, examen, momento=periodico)
      // retiro solo para examenes marcados
```

## Seed del catalogo (examenes tipicos Colombia)

| # | Examen | Tipo | Clasificaciones que aplica |
|---|--------|------|---------------------------|
| 1 | Visiometria | funcional | biomecanico, fisico |
| 2 | Audiometria | funcional | fisico |
| 3 | Espirometria | funcional | quimico, biologico |
| 4 | Valoracion osteomuscular | funcional | biomecanico |
| 5 | Optometria | funcional | biomecanico |
| 6 | Examen medico ocupacional | especialista | todos |
| 7 | Perfil lipidico | laboratorio | psicosocial |
| 8 | Glicemia | laboratorio | general |
| 9 | Cuadro hematico completo | laboratorio | biologico, quimico |
| 10 | Parcial de orina | laboratorio | general |
| 11 | Prueba psicologica | psicologico | psicosocial |
| 12 | Bateria riesgo psicosocial | psicologico | psicosocial |
| 13 | Rx columna lumbosacra | imagenologia | biomecanico |
| 14 | Rx torax | imagenologia | quimico |
| 15 | Electrocardiograma | funcional | condiciones_seguridad |
| 16 | Prueba de equilibrio / vestibular | funcional | condiciones_seguridad |
| 17 | Psicosensometrico | funcional | condiciones_seguridad |
| 18 | Colinesterasa | laboratorio | quimico |
| 19 | Prueba de embarazo | laboratorio | quimico, biologico |
| 20 | Dermatologico | especialista | quimico, biologico |

## Convenciones

- Scripts BD: `scripts/profesiograma_fase1.php`
- Models: `App\Models\ProfesiogramaCatalogoModel`, `App\Models\ProfesiogramaClienteModel`
- Controller: `App\Controllers\ProfesiogramaController`
- Vistas: `app/Views/profesiograma/`
- Estilo dorado (#bd9751), Bootstrap 5, DataTables, SweetAlert2
