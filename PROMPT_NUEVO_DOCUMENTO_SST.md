

```
Necesito crear un nuevo tipo de documento SST para el aplicativo EnterpriseSST.

ANTES DE ESCRIBIR CUALQUIER CODIGO, haz lo siguiente:

### FASE 1: Leer documentacion del proyecto

Lee estos archivos en orden para entender la arquitectura completa:

1. C:\xampp\htdocs\enterprisesst\proyecto_documentacion_sst_parte8.md (arquitectura del generador de documentos, constantes TIPOS_DOCUMENTO, flujo Phase-Locking, secciones, IA, almacenamiento, rutas, exportacion PDF/Word, versionado)
2. C:\xampp\htdocs\enterprisesst\proyecto_documentacion_sst_parte9.md (seccion 16: guia paso a paso para crear nuevo documento, diagnostico de componentes genericos vs hardcodeados, checklist de verificacion)

Despues de leerlos, confirma que entendiste:
- La constante TIPOS_DOCUMENTO y CODIGOS_DOCUMENTO
- El flujo generarConIA() -> generarSeccionIA() -> generarConIAReal() / generarContenidoSeccion()
- Los prompts en getPromptBaseParaSeccion()
- La vista previa programaCapacitacion() y su vista programa_capacitacion.php
- La tabla de carpeta.php con sus 6 botones de accion
- El mapeo plantilla-carpeta en tbl_doc_plantilla_carpeta

### FASE 2: Preguntarme sobre el nuevo documento

Hazme estas preguntas UNA POR UNA (no todas juntas), esperando mi respuesta antes de continuar:

**Pregunta 1 - Identidad del documento:**
- ¿Como se llama el documento? (ej: "Programa de Mantenimiento Preventivo")
- ¿Cual es el tipo_documento en snake_case? (ej: "programa_mantenimiento")
- ¿Que codigo le asignamos? Tipo y Tema (ej: PRG-MNT). Muestrame los codigos existentes en CODIGOS_DOCUMENTO para que yo elija.

**Pregunta 2 - Secciones del documento:**
- Propon una lista de secciones basandote en documentos SST similares de la Resolucion 0312/2019 y normativa colombiana. Cada seccion necesita: numero, nombre, key.
- Preguntame si quiero agregar, quitar o modificar alguna seccion.
- Preguntame cuales secciones se alimentan de tablas existentes (como cronograma, PTA, indicadores) vs generadas por IA/plantilla.

**Pregunta 3 - Ubicacion en carpetas:**
- Lee la estructura actual de carpetas del cliente ejecutando un query a tbl_doc_carpetas para ver los estandares disponibles.
- Preguntame en cual estandar de la Resolucion 0312 se ubica este documento.
- Muestra las opciones de carpetas tipo 'estandar' disponibles.

**Pregunta 4 - Dependencias:**
- ¿Este documento requiere que se complete alguna fase previa? (cronograma, PTA, indicadores, u otro documento)
- ¿O es independiente y se puede generar directamente?

**Pregunta 5 - Vista previa:**
- ¿La estructura visual del documento es similar al Programa de Capacitacion (encabezado formal + secciones numeradas + firmas + control de cambios)?
- ¿O necesita una estructura diferente? Si es asi, describir.

### FASE 3: Implementar los 12 pasos

Una vez que tenga todas las respuestas, ejecutar los pasos en este orden EXACTO. Despues de cada paso, confirmar que se completo sin errores antes de continuar:

**Paso 1:** Agregar entrada en TIPOS_DOCUMENTO con las secciones definidas
**Paso 2:** Agregar entrada en CODIGOS_DOCUMENTO (si no existe)
**Paso 3:** Refactorizar generarConIAReal() para que reciba $tipo en vez de tener hardcodeado 'programa_capacitacion' (SOLO LA PRIMERA VEZ, luego ya esta generico)
**Paso 4:** Agregar prompts IA para cada seccion del nuevo tipo en getPromptBaseParaSeccion()
**Paso 5:** Agregar plantillas estaticas (fallback sin IA) en generarContenidoSeccion()
**Paso 6:** Crear metodo de vista previa (o generalizar programaCapacitacion())
**Paso 7:** Crear vista de renderizado en app/Views/documentos_sst/
**Paso 8:** Agregar ruta de vista previa en Routes.php
**Paso 9:** Hacer dinamicos los links en carpeta.php (botones Ver/Editar por tipo_documento)
**Paso 10:** Insertar mapeo en tbl_doc_plantilla_carpeta (LOCAL y PRODUCCION)
**Paso 11:** Verificar que tipoCarpetaFases detecte el nuevo tipo en DocumentacionController.php
**Paso 12:** Ejecutar el CHECKLIST DE VERIFICACION (seccion 16 de parte 9)

### FASE 4: Verificacion

Despues de implementar, verificar manualmente:
1. Ir a la carpeta del estandar correspondiente y ver que aparezca el boton para generar
2. Generar al menos 2 secciones con IA
3. Guardar y aprobar secciones
4. Ver vista previa
5. Exportar PDF
6. Publicar en reportList
7. Verificar que el documento aparece en la tabla de carpeta.php con todos los botones funcionales

### REGLAS IMPORTANTES:

- NO crear controladores nuevos. Todo va en DocumentosSSTController.php
- NO crear modelos nuevos a menos que se necesite una tabla nueva
- Reutilizar las vistas PDF/Word si la estructura es similar
- Los prompts de IA deben ser especificos para normativa colombiana SST
- Siempre ejecutar cambios de BD en LOCAL y PRODUCCION
- Para BD PRODUCCION usar el patron de app/SQL/ejecutar_migracion.php con las credenciales que el usuario proporcionara
- Cada seccion de prompt debe ajustarse por estandares (7, 21, 60) siguiendo el mismo patron del programa de capacitacion

### DOCUMENTO QUE QUIERO CREAR:

[ESCRIBIR AQUI EL NOMBRE DEL DOCUMENTO]
```

---

## EJEMPLO DE USO

Para crear un "Programa de Mantenimiento Preventivo", el usuario pegaria el prompt anterior y al final escribiria:

```
DOCUMENTO QUE QUIERO CREAR: Programa de Mantenimiento Preventivo y Correctivo
```

Claude leeria las partes 8 y 9, haria las 5 preguntas, y ejecutaria los 12 pasos.

---

## NOTAS TECNICAS

- Este prompt asume que la refactorizacion de generarConIAReal() (Paso 3) se hace UNA SOLA VEZ. La segunda vez que se cree un documento, ese paso ya estara hecho.
- Lo mismo aplica para el Paso 9 (links dinamicos en carpeta.php). Una vez hechos dinamicos, aplica para todos los tipos futuros.
- Los pasos que se repiten por cada nuevo documento son: 1, 2, 4, 5, 6, 7, 8, 10, 11.

## ARCHIVOS CLAVE QUE CLAUDE DEBE LEER

| Archivo | Para que |
|---------|----------|
| `proyecto_documentacion_sst_parte8.md` | Arquitectura completa del generador |
| `proyecto_documentacion_sst_parte9.md` | Guia de creacion (seccion 16) |
| `app/Controllers/DocumentosSSTController.php` | Constantes, metodos de generacion, prompts |
| `app/Controllers/DocumentacionController.php` | Metodo carpeta(), tipoCarpetaFases |
| `app/Config/Routes.php` | Rutas existentes |
| `app/Views/documentacion/carpeta.php` | Tabla con botones de accion |
| `app/Views/documentos_sst/programa_capacitacion.php` | Vista de referencia para el nuevo tipo |
| `app/Views/documentos_sst/pdf_template.php` | Template PDF de referencia |
