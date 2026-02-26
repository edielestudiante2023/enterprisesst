# 08 - Estrategia de Autoguardado (localStorage)

## Problema

El consultor llena el Acta de Visita desde el celular durante la visita al cliente. Si la sesion de CI4 expira (por inactividad o timeout del servidor), al intentar guardar se pierde **todo** lo que habia escrito. Esto es critico porque el formulario tiene multiples secciones con datos que no se pueden reconstruir facilmente.

---

## Solucion: localStorage como borrador local

Se implementa un sistema de autoguardado que persiste los datos del formulario en `localStorage` del navegador. No depende de la sesion del servidor ni de conexion a internet.

### Diferencia con la estrategia offline (doc 05)

| Aspecto | Autoguardado (este doc) | Offline (doc 05) |
|---------|------------------------|-------------------|
| Tecnologia | `localStorage` | IndexedDB + Service Worker |
| Proposito | Recuperar datos si se pierde sesion o se cierra el navegador | Funcionar sin internet y sincronizar despues |
| Complejidad | Baja (ya implementado) | Alta (pendiente de implementacion) |
| Datos | Solo el formulario actual | Cache de assets + datos para sincronizar |

---

## Implementacion

### Clave de almacenamiento

```
acta_draft_new        → formulario de creacion (/acta-visita/create)
acta_draft_{id}       → formulario de edicion (/acta-visita/edit/123)
```

### Datos que se guardan

La funcion `collectFormData()` serializa todos los campos del formulario:

```javascript
{
    id_cliente: "45",
    fecha_visita: "2026-02-22",
    hora_visita: "10:30",
    motivo: "Visita de seguimiento mensual",
    modalidad: "presencial",
    observaciones: "Se revisaron extintores...",
    integrantes: [
        { nombre: "Edison Cuervo", cargo: "CONSULTOR CYCLOID", rol: "CONSULTOR CYCLOID" },
        { nombre: "Ana Garcia", cargo: "Jefe RRHH", rol: "ADMINISTRADOR" }
    ],
    temas: [
        { tema: "Revision de extintores", detalle: "Todos vigentes" },
        { tema: "Capacitacion primeros auxilios", detalle: "Programada para marzo" }
    ],
    compromisos: [
        { compromiso: "Renovar extintor piso 3", responsable: "Ana Garcia", fecha: "2026-03-15" }
    ],
    timestamp: 1740230400000
}
```

### Frecuencia de guardado

1. **Periodico:** cada 30 segundos (`setInterval`)
2. **Por cambio:** 2 segundos despues de cualquier input (`debounce`)
3. **Indicador visual:** muestra "Guardado HH:MM:SS" en esquina inferior

```javascript
// Guardado periodico
setInterval(saveToLocal, 30000);

// Guardado por cambio (debounce 2s)
var debounceTimer;
document.getElementById('actaForm').addEventListener('input', function() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(saveToLocal, 2000);
});
```

### Restauracion del borrador

Al cargar la pagina del formulario:

1. Busca la clave correspondiente en `localStorage`
2. Verifica que el timestamp sea menor a 24 horas
3. Muestra SweetAlert2 preguntando si desea restaurar
4. Si acepta: reconstruye todo el formulario (campos simples + filas dinamicas)
5. Si rechaza: limpia el borrador de localStorage

```
┌─────────────────────────────────────┐
│  Se encontro un borrador guardado   │
│  hace 2 horas.                      │
│                                     │
│  Desea restaurar los datos?         │
│                                     │
│  [Si, restaurar]  [No, empezar de 0]│
└─────────────────────────────────────┘
```

### Restauracion del cliente (Select2)

El campo de cliente usa Select2 que carga opciones via AJAX. Como las opciones no estan disponibles inmediatamente al cargar la pagina, se usa un patron de "restauracion pendiente":

```javascript
// En restoreFromLocal():
if (data.id_cliente) {
    window._pendingClientRestore = data.id_cliente;
}

// En el callback de exito de Select2 AJAX:
if (window._pendingClientRestore) {
    $('#selectCliente').val(window._pendingClientRestore).trigger('change');
    window._pendingClientRestore = null;
}
```

### Limpieza

- **Al enviar el formulario:** se elimina el borrador de localStorage
- **Expiracion automatica:** borradores con mas de 24 horas se ignoran y se eliminan
- **No hay limpieza periodica en background** (no es necesario, localStorage tiene ~5MB)

---

## Flujo completo

```
Consultor abre /acta-visita/create
        │
        ▼
¿Existe borrador en localStorage?
        │
   Si ──┤── No
   │         │
   ▼         ▼
¿< 24h?    Formulario vacio
   │
   Si ──┤── No
   │         │
   ▼         ▼
SweetAlert  Borrar borrador viejo
"Restaurar?"
   │
   Si ──┤── No
   │         │
   ▼         ▼
Restaurar  Formulario vacio
datos
        │
        ▼
Consultor llena el formulario
        │
        ▼
Autoguardado cada 30s + 2s post-input
        │
        ▼
Consultor presiona "Guardar"
        │
        ▼
POST a /acta-visita/store
        │
   OK ──┤── Error (sesion expirada, etc.)
   │         │
   ▼         ▼
Borrar      Datos siguen en localStorage
borrador    → Consultor hace login
             → Vuelve a /acta-visita/create
             → Restaura borrador
```

---

## Archivos involucrados

| Archivo | Rol |
|---------|-----|
| `app/Views/inspecciones/acta_visita/form.php` | Formulario compartido por create y edit. Contiene todo el JS de autoguardado |

---

## Limitaciones conocidas

1. **localStorage es por dominio + navegador.** Si el consultor empieza en Chrome y termina en Safari, no se restaura.
2. **No sincroniza entre dispositivos.** Es solo local al navegador.
3. **Fotos/soportes no se guardan.** Solo datos de texto del formulario. Las fotos tendrian que volver a adjuntarse.
4. **Un solo borrador por tipo.** Si abre dos actas nuevas en pestanas diferentes, se sobreescriben. Esto es aceptable porque el flujo normal es una acta a la vez.

---

## Mejoras futuras

- Guardar fotos como base64 en localStorage (atencion al limite de 5MB)
- Migrar a IndexedDB cuando se implemente la estrategia offline completa (doc 05)
- Indicador visual mas prominente cuando hay borrador sin guardar
