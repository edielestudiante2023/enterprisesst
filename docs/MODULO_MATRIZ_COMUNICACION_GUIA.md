# Modulo: Matriz de Comunicacion SST

## Descripcion

Modulo gemelo de Matriz Legal, adaptado para gestionar protocolos de comunicacion interna y externa del SG-SST. Cada empresa (cliente) tiene su propia matriz personalizada segun sector, nivel de riesgo y estructura organizacional.

## Diferencias con Matriz Legal

| Aspecto | Matriz Legal | Matriz Comunicacion |
|---------|-------------|-------------------|
| Tabla BD | `matriz_legal` (sin id_cliente) | `matriz_comunicacion` (con id_cliente) |
| Alcance | Global | Por cliente |
| IA | Busca normas existentes | Genera protocolos de comunicacion |
| Modos IA | 1 (busqueda individual) | 2 (masivo + individual) |

## Acceso

- URL: `/matriz-comunicacion`
- Generador IA: `/matriz-comunicacion/generar-ia`
- Importar CSV: `/matriz-comunicacion/importar`

## Estructura de la Tabla BD

```
matriz_comunicacion
- id (PK)
- id_cliente (FK)
- categoria (Accidentes, Incidentes, Emergencias, Convivencia Laboral, etc.)
- situacion_evento (descripcion del evento)
- que_comunicar (informacion a transmitir)
- quien_comunica (emisor)
- a_quien_comunicar (destinatario)
- mecanismo_canal (medio de comunicacion)
- frecuencia_plazo (plazo o periodicidad)
- registro_evidencia (como se evidencia)
- norma_aplicable (norma legal)
- tipo (interna/externa/ambas)
- estado (activo/inactivo)
- generado_por_ia (0/1)
```

## 11 Categorias Obligatorias

1. Accidentes de Trabajo
2. Incidentes
3. Enfermedades Laborales
4. Emergencias
5. Convivencia Laboral (acoso sexual, acoso laboral)
6. Peligros y Riesgos
7. Resultados de Auditoria
8. Cambios Normativos
9. Capacitaciones
10. COPASST / Comite de Convivencia
11. Comunicacion Externa (ARL, EPS, MinTrabajo)

## Generacion con IA

### Modo 1: Matriz Completa
- Genera 25-35 protocolos cubriendo las 11 categorias
- Usa contexto del cliente (sector, riesgo, trabajadores, peligros)
- Preview en DataTable editable con checkboxes
- Guardar seleccionados en bulk

### Modo 2: Protocolo Especifico
- Usuario describe situacion (ej: "Acoso sexual")
- IA genera 1-4 filas con norma aplicable
- Preview card editable
- Guardar individualmente

### API: gpt-4o via /v1/responses con web_search_preview, temp 0.3

## Documento Tipo A Asociado

- tipo_documento: `procedimiento_matriz_comunicacion`
- estandar: 2.8.1
- flujo: secciones_ia
- 8 secciones: Objetivo, Alcance, Definiciones, Responsabilidades, Estructura, Actualizacion, Canales, Indicadores
- 2 firmantes: responsable_sst + representante_legal
- codigo: PRC-MCO

## Archivos del Modulo

| Archivo | Descripcion |
|---------|-------------|
| `app/SQL/crear_tabla_matriz_comunicacion.php` | Crear tabla BD |
| `app/SQL/agregar_procedimiento_matriz_comunicacion.php` | Config documento Tipo A |
| `app/Models/MatrizComunicacionModel.php` | Model con filtros por id_cliente |
| `app/Controllers/MatrizComunicacionController.php` | Controller CRUD + IA + CSV |
| `app/Views/matriz_comunicacion/index.php` | Vista principal DataTables |
| `app/Views/matriz_comunicacion/generar_ia.php` | Vista IA 2 tabs |
| `app/Views/matriz_comunicacion/importar_csv.php` | Importar CSV |
| `app/Libraries/DocumentosSSTTypes/ProcedimientoMatrizComunicacion.php` | Clase documento |
| `app/Config/Routes.php` | 14 rutas /matriz-comunicacion/* |

## Rutas

```
GET  /matriz-comunicacion                    → index
GET  /matriz-comunicacion/datatable          → datatable (server-side)
GET  /matriz-comunicacion/ver/{id}           → ver detalle
POST /matriz-comunicacion/guardar            → crear/actualizar
POST /matriz-comunicacion/eliminar/{id}      → eliminar
GET  /matriz-comunicacion/generar-ia         → vista IA
POST /matriz-comunicacion/procesar-generacion-ia → IA individual
POST /matriz-comunicacion/generar-bulk-ia    → IA masiva
POST /matriz-comunicacion/guardar-desde-ia   → guardar desde IA
GET  /matriz-comunicacion/importar           → vista CSV
POST /matriz-comunicacion/preview-csv        → preview CSV
POST /matriz-comunicacion/procesar-csv       → importar CSV
GET  /matriz-comunicacion/exportar           → exportar CSV
GET  /matriz-comunicacion/descargar-muestra  → CSV muestra
```
