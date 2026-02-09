# INSTRUCTIVO: Sistema de Mensajes Toast en Generacion de Documentos

## Que Son

Los toast son notificaciones no-intrusivas que aparecen en la esquina superior derecha de la pantalla. Informan al usuario sobre el resultado de cada accion sin interrumpir el flujo de trabajo (a diferencia de los SweetAlert que requieren confirmacion).

**Archivo:** `app/Views/documentos_sst/generar_con_ia.php`

---

## Arquitectura: Stack Dinamico

Los toasts se crean **dinamicamente** en el DOM. No existe un toast estatico fijo — cada llamada a `mostrarToast()` crea un nuevo elemento que se apila verticalmente. Esto permite que **multiples toasts sean visibles simultaneamente** sin pisarse entre si.

### Estructura HTML

```html
<!-- Contenedor vacio - los toasts se crean dentro dinamicamente -->
<div class="toast-container position-fixed top-0 end-0 p-3" id="toastStack"></div>
```

Cada toast generado tiene un ID unico (`toast-{timestamp}-{random}`) y se **auto-elimina del DOM** al cerrarse.

### CSS Requerido

```css
.toast-container {
    z-index: 9999;
}
.toast {
    min-width: 300px;
    box-shadow: 0 4px 12px rgba(0,0,0,.15);
    margin-bottom: 8px;
}
.toast-retry-btn {
    background: none;
    border: 1px solid #dc3545;
    color: #dc3545;
    border-radius: 4px;
    padding: 2px 10px;
    font-size: 0.8rem;
    cursor: pointer;
}
.toast-retry-btn:hover {
    background: #dc3545;
    color: white;
}
```

---

## Funcion Principal: mostrarToast()

```javascript
function mostrarToast(tipo, titulo, mensaje, reintentarCallback) {
    // tipo: 'success' | 'error' | 'warning' | 'info' | 'ia' | 'save' | 'database' | 'progress'
    // titulo: Texto del encabezado
    // mensaje: Texto del cuerpo (acepta HTML)
    // reintentarCallback: (opcional) Funcion a ejecutar al hacer clic en "Reintentar"
    //
    // Retorna: { id, element, instance } para cierre programatico
}
```

### Caracteristicas clave

1. **Stack dinamico:** Cada llamada crea un nuevo toast que se apila, nunca sobreescribe
2. **Timestamp real:** Muestra la hora actual (HH:MM:SS) en vez de "Ahora"
3. **Boton Reintentar:** Si se pasa `reintentarCallback`, agrega un boton rojo en el cuerpo del toast
4. **Auto-limpieza:** El elemento se remueve del DOM al cerrarse (evento `hidden.bs.toast`)
5. **Cierre programatico:** Retorna referencia para cerrar con `cerrarToast(ref)`

---

## Tipos de Toast Disponibles

| Tipo | Color Header | Icono | Duracion | Auto-hide | Uso |
|------|-------------|-------|----------|-----------|-----|
| `success` | Verde (`bg-success`) | `bi-check-circle-fill` | 6s | Si | Operacion exitosa |
| `error` | Rojo (`bg-danger`) | `bi-x-circle-fill` | 8s | Si | Error (incluye boton Reintentar) |
| `warning` | Amarillo (`bg-warning`) | `bi-exclamation-triangle-fill` | 6s | Si | Resultado parcial o advertencia |
| `info` | Celeste (`bg-info`) | `bi-info-circle-fill` | 5s | Si | Informacion general |
| `ia` | Azul (`bg-primary`) | `bi-robot` | 5s | Si | Generacion IA completada |
| `save` | Verde (`bg-success`) | `bi-save-fill` | 5s | Si | Seccion guardada en BD |
| `database` | Celeste (`bg-info`) | `bi-database-check` | 15s | Si | Metadata de tablas consultadas |
| `progress` | Azul (`bg-primary`) | Spinner animado | 60s | **No** | Operacion en curso (se cierra manual) |

### Duraciones por Tipo

```javascript
const duraciones = {
    'database': 15000,  // 15s - contiene mucha informacion
    'error':     8000,  // 8s  - el usuario necesita leer el error
    'success':   6000,  // 6s
    'warning':   6000,  // 6s
    'save':      5000,  // 5s
    'progress': 60000,  // 60s - timeout de seguridad (se cierra manualmente)
    // default:  5000   // 5s para cualquier otro tipo
};
```

---

## Funciones Auxiliares

### cerrarToast(ref)

Cierra un toast programaticamente. Util para cerrar toasts de tipo `progress` cuando la operacion termina.

```javascript
function cerrarToast(ref) {
    if (ref && ref.instance) ref.instance.hide();
}

// Uso:
const progreso = mostrarToast('progress', 'Generando...', 'Seccion X...');
// ... operacion async ...
cerrarToast(progreso); // Cierra el progress toast
```

### mostrarToastBD(metadata)

Toast especial que muestra que tablas de la BD fueron consultadas por la IA. Llama internamente a `mostrarToast('database', ...)`.

---

## Variable: modoBatch

```javascript
let modoBatch = false;
```

Cuando `modoBatch = true`, la funcion `generarSeccion()` **suprime** los toasts individuales (`ia`, `database`, `progress`). Esto evita que se apilen N toasts durante "Generar Todo" (donde ya hay un modal de progreso).

Los toasts de error **NO se suprimen** en batch — aparecen siempre.

---

## Inventario Completo de Toasts por Accion

### 1. Generacion IA (seccion individual)

| Resultado | Tipo | Titulo | Mensaje | Reintentar |
|-----------|------|--------|---------|------------|
| En curso | `progress` | "Generando..." | `Seccion "[nombre]" siendo redactada por la IA...` | No |
| Exito | `ia` | "Contenido Generado" / "...con IA (OpenAI)" | `Seccion "[nombre]" generada correctamente.` | No |
| Exito + metadata | `database` | "Bases de Datos Consultadas" | Lista HTML de tablas con conteo | No |
| Error backend | `error` | "Error al Generar" | Mensaje del backend o generico | **Si** |
| Error conexion | `error` | "Error de Conexion" | `No se pudo conectar: [error]` | **Si** |

**Flujo:** progress (aparece) → [espera IA] → progress (se cierra) → ia + database (aparecen)

**Nota:** En modo batch (`modoBatch = true`), solo aparecen los toasts de error. Los de progress, ia y database se suprimen porque el modal de progreso ya informa al usuario.

### 2. Guardar Seccion

| Resultado | Tipo | Titulo | Mensaje |
|-----------|------|--------|---------|
| Exito | `save` | "Seccion Guardada" | `"[nombre]" guardada en la base de datos.` |
| Error backend | `error` | "Error al Guardar" | Mensaje del backend o generico |
| Error conexion | `error` | "Error de Conexion" | `No se pudo conectar: [error]` |

### 3. Aprobar Seccion

| Resultado | Tipo | Titulo | Mensaje |
|-----------|------|--------|---------|
| Exito | `success` | "Seccion Aprobada" | `"[nombre]" aprobada y lista para el documento final.` |
| Error backend | `error` | "Error al Aprobar" | Mensaje del backend o generico |
| Error conexion | `error` | "Error de Conexion" | `No se pudo conectar: [error]` |

### 4. Generar Todo (masivo)

| Resultado | Tipo | Titulo | Mensaje |
|-----------|------|--------|---------|
| Todo exitoso | `success` | "Generacion Completa" | `Las [N] secciones fueron generadas exitosamente.` |
| Parcial | `warning` | "Generacion Parcial" | `[N] secciones generadas, [M] con errores.` + **nombres de secciones fallidas** |

**Mejora:** El toast parcial ahora incluye `Fallidas: Cronograma, Indicadores` para que el usuario sepa exactamente cuales revisar.

### 5. Guardar Todo (masivo)

| Resultado | Tipo | Titulo | Mensaje |
|-----------|------|--------|---------|
| Todo exitoso | `save` | "Guardado Completo" | `Las [N] secciones fueron guardadas en la base de datos.` |
| Parcial | `warning` | "Guardado Parcial" | `[N] de [total] secciones guardadas. Algunas no pudieron guardarse.` |

### 6. Aprobar Todo (masivo)

| Resultado | Tipo | Titulo | Mensaje |
|-----------|------|--------|---------|
| Sin secciones | `warning` | "Sin Secciones" | `No hay secciones con contenido para aprobar.` |
| Todo aprobado | `success` | "Aprobacion Completa" | `Las [N] secciones fueron aprobadas exitosamente.` |
| Parcial | `warning` | "Aprobacion Parcial" | `[N] de [total] secciones aprobadas.` |

### 7. Documento Creado (primera vez que se guarda)

| Resultado | Tipo | Titulo | Mensaje |
|-----------|------|--------|---------|
| Creado | `info` | "Documento Creado" | `El documento fue guardado. Ahora puedes enviarlo a firmas.` |

### 8. Documento Listo para Vista Previa

| Resultado | Tipo | Titulo | Mensaje |
|-----------|------|--------|---------|
| Listo | `success` | "Documento Listo" | `Todas las secciones guardadas y aprobadas. Ya puedes ver la Vista Previa.` |

### 9. Aprobar Documento (crear version)

| Resultado | Tipo | Titulo | Mensaje |
|-----------|------|--------|---------|
| Exito | `success` | "Documento Aprobado" | `Version [vX.X] creada correctamente.` |
| Error | `alert()` | -- | Usa alert() nativo, no toast |

---

## Toast Especial: Metadata de BD (mostrarToastBD)

Este toast muestra al usuario que tablas de la base de datos fueron consultadas durante la generacion IA. Refuerza la confianza de que la IA uso datos reales.

### Estructura de metadata esperada

```javascript
// data.metadata_bd (viene del backend)
{
    resumen: "Datos consultados para esta seccion",
    tablas_consultadas: [
        {
            descripcion: "Plan de Trabajo",
            icono: "bi-calendar-check",
            registros: 29,
            datos: ["Induccion SST", "Capacitacion alturas", "Reinduccion"]
        },
        {
            descripcion: "Indicadores SST",
            icono: "bi-graph-up",
            registros: 3,
            datos: ["Cobertura", "Cumplimiento", "Eficacia"]
        }
    ]
}
```

### Logica de presentacion

```javascript
function mostrarToastBD(metadata) {
    // - Muestra cada tabla con icono y conteo de registros
    // - Si hay <= 3 registros: muestra los nombres
    // - Si hay > 3 registros: muestra los 2 primeros + "(+N mas)"
    // - Registros > 0 se muestran en verde (text-success)
    // - Registros = 0 se muestran en amarillo (text-warning)
    // - Duracion: 15 segundos (la mas larga de los tipos auto-hide)
}
```

---

## Flujo Visual de Toasts por Accion del Usuario

```
Click "Generar con IA"
    |
    v
[Toast progress] "Generando..."       <-- aparece inmediatamente (spinner)
    |
    ... espera IA (10-30s) ...
    |
    v
[Toast progress] se cierra             <-- cerrarToast(ref)
    |
    v
[Toast ia] "Contenido Generado"        <-- se apila en el stack
    |
    +-- 500ms delay -->
    |
[Toast database] "BD Consultadas"      <-- se apila (ambos visibles)
    |
    v
Click "Guardar"
    |
    v
[Toast save] "Seccion Guardada"
    |
    v
Click "Aprobar"
    |
    v
[Toast success] "Seccion Aprobada"
    |
    v (si es la ultima seccion)
[Toast success] "Documento Listo"      <-- habilita Vista Previa
```

### Flujo "Generar Todo" (batch)

```
Click "Generar Todo"
    |
    v
modoBatch = true
    |
    v
[Modal Progreso] "Redactando seccion 1: ..."    <-- el modal da feedback
[Modal Progreso] "Redactando seccion 2: ..."
[Modal Progreso] "Redactando seccion N: ..."
    |
    v
modoBatch = false
    |
    v
[Toast success] "Generacion Completa"            <-- resumen final
   O
[Toast warning] "Generacion Parcial"             <-- incluye nombres fallidas
    + "Fallidas: Cronograma, Indicadores"
```

---

## Boton "Reintentar" en Toasts de Error

Los toasts de error en la generacion IA incluyen un boton **Reintentar** que ejecuta `generarSeccion(seccionKey)` directamente, sin pasar por el SweetAlert de verificacion (ya fue confirmado).

```
+------------------------------------------+
| [bg-danger] Error al Generar     14:32:05|
+------------------------------------------+
| No se pudo generar "Cronograma".         |
|                                          |
| [Reintentar]  <-- boton rojo outline     |
+------------------------------------------+
```

El boton cierra el toast actual y ejecuta el callback. El nuevo intento muestra su propio toast de progreso.

---

## Relacion con Otros Documentos

| Documento | Relacion |
|-----------|----------|
| `ZZ_90_PARTESWEETALERT.md` | SweetAlert de verificacion (antes de generar). Los toast aparecen DESPUES de generar. |
| `ZZ_95_PARTE3.md` | Flujo general de la Parte 3 donde los toast dan feedback de cada paso |
