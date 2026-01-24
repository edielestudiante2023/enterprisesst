# Proyecto de Documentacion SST - Parte 6

## Resumen

Esta parte documenta las mejoras implementadas en el modulo de **Cumplimiento PHVA** (Estandares Minimos Res. 0312/2019), incluyendo:

- Campo `criterio` (pregunta de auditoria) en estandares
- Calificacion automatica segun la Resolucion 0312/2019
- Actualizacion en tiempo real de cards de resumen
- Inicializacion manual con doble confirmacion

---

## 1. Campo Criterio de Verificacion

### 1.1 Descripcion

Cada uno de los 60 estandares minimos ahora tiene un campo `criterio` que contiene la **pregunta de auditoria** exacta segun el formato de autoevaluacion de la Res. 0312/2019.

### 1.2 Cambio en Base de Datos

```sql
ALTER TABLE tbl_estandares_minimos
ADD COLUMN criterio TEXT NULL AFTER nombre;
```

### 1.3 Modelo Actualizado

**Archivo:** `app/Models/EstandarMinimoModel.php`

```php
protected $allowedFields = [
    'ciclo_phva', 'categoria', 'categoria_nombre', 'item', 'nombre',
    'criterio',  // Campo agregado
    'peso_porcentual', 'aplica_7', 'aplica_21', 'aplica_60',
    'documentos_sugeridos', 'activo'
];
```

---

## 2. Calificacion Automatica (Res. 0312/2019)

### 2.1 Regla de Negocio

Segun la Resolucion 0312/2019, la calificacion de cada estandar es **automatica** y depende del estado:

| Estado | Calificacion |
|--------|--------------|
| **Cumple** | 100% del peso del estandar |
| **No Aplica** | 100% del peso (cuando esta justificado) |
| **No Cumple** | 0% |
| **En Proceso** | 0% |
| **Pendiente** | 0% |

### 2.2 Implementacion en Vista (detalle.php)

```php
<?php
$estadoActual = $clienteEstandar['estado'] ?? 'pendiente';
$pesoEstandar = (float)($estandar['peso_porcentual'] ?? 0);
// Calificacion automatica segun estado (Res. 0312/2019)
$calificacionAuto = ($estadoActual === 'cumple' || $estadoActual === 'no_aplica')
                    ? $pesoEstandar
                    : 0;
?>
```

### 2.3 Implementacion en Controlador

**Archivo:** `app/Controllers/EstandaresClienteController.php`

```php
public function actualizarEstado()
{
    // Si no se envia calificacion, calcularla automaticamente
    if ($calificacionPost === null || $calificacionPost === '') {
        $estandar = $this->estandarModel->find($idEstandar);
        $pesoEstandar = (float) ($estandar['peso_porcentual'] ?? 0);
        // Cumple o No Aplica = 100% del peso, otros = 0
        $calificacion = ($estado === 'cumple' || $estado === 'no_aplica')
                        ? $pesoEstandar : 0;
    }
}
```

### 2.4 Actualizacion en Tiempo Real (JavaScript)

```javascript
function calcularCalificacion(estado) {
    // Segun Res. 0312/2019:
    // Cumple = 100% del peso del estandar
    // No Aplica = 100% del peso (cuando esta justificado)
    // No Cumple / Pendiente / En Proceso = 0%
    return (estado === 'cumple' || estado === 'no_aplica') ? pesoMax : 0;
}
```

---

## 3. Dashboard con Actualizacion en Tiempo Real

### 3.1 Cards de Resumen con IDs

Los cards ahora tienen IDs para actualizacion dinamica:

```html
<h4 id="contadorCumple">0</h4>
<h4 id="contadorEnProceso">0</h4>
<h4 id="contadorNoCumple">0</h4>
<h4 id="contadorPendiente">0</h4>
<h4 id="contadorNoAplica">0</h4>
<h2 id="porcentajeCumplimiento">0%</h2>
<circle id="progressCircle" ... />
```

### 3.2 Funcion de Actualizacion AJAX

```javascript
function actualizarResumen(resumen) {
    if (!resumen) return;

    // Actualizar contadores
    document.getElementById('contadorCumple').textContent = resumen.cumple || 0;
    document.getElementById('contadorEnProceso').textContent = resumen.en_proceso || 0;
    document.getElementById('contadorNoCumple').textContent = resumen.no_cumple || 0;
    document.getElementById('contadorPendiente').textContent = resumen.pendiente || 0;
    document.getElementById('contadorNoAplica').textContent = resumen.no_aplica || 0;

    // Actualizar porcentaje de cumplimiento
    const porcentaje = resumen.porcentaje_cumplimiento || 0;
    document.getElementById('porcentajeCumplimiento').textContent =
        porcentaje.toFixed(1) + '%';

    // Actualizar el circulo de progreso
    const circle = document.getElementById('progressCircle');
    if (circle) {
        const offset = 440 - (440 * porcentaje / 100);
        circle.style.strokeDashoffset = offset;
    }
}
```

### 3.3 Flujo de Actualizacion

1. Usuario cambia estado de un estandar en el select
2. Se muestra indicador de carga (select deshabilitado)
3. AJAX envia `id_cliente`, `id_estandar`, `estado`
4. Controlador calcula calificacion automatica y guarda
5. Controlador retorna `resumen` con contadores actualizados
6. JavaScript actualiza todos los cards sin recargar pagina
7. Select muestra feedback verde de exito

---

## 4. Proceso de Inicializacion Manual

### 4.1 Cambio de Comportamiento

Anteriormente los estandares se inicializaban automaticamente. Ahora requiere:

1. Clic en boton "Inicializar Estandares del Cliente"
2. Primera confirmacion (muestra nivel de estandares)
3. Segunda confirmacion (confirmacion final)

### 4.2 Codigo del Dashboard

```javascript
function confirmarInicializacion() {
    const nivelEstandares = <?= $contexto['estandares_aplicables'] ?? 60 ?>;
    const primerConfirm = confirm(
        'Esta seguro de inicializar los estandares?\n\n' +
        'Nivel configurado: ' + nivelEstandares + ' estandares aplicables'
    );

    if (primerConfirm) {
        const segundoConfirm = confirm('CONFIRMACION FINAL\n\nConfirma?');
        if (segundoConfirm) {
            window.location.href = baseUrl + 'estandares/inicializar/' + idCliente;
        }
    }
}
```

### 4.3 Cuando Usar la Inicializacion

- Una vez al anio para la autoevaluacion del SG-SST
- Cuando ingresa un cliente nuevo al sistema

---

## 5. Vista de Detalle del Estandar

### 5.1 Archivo

**Ubicacion:** `app/Views/estandares/detalle.php`

### 5.2 Secciones de la Vista

| Seccion | Descripcion |
|---------|-------------|
| Encabezado | Numero, ciclo PHVA, peso porcentual, nombre |
| Criterio de Verificacion | Pregunta de auditoria segun Res. 0312/2019 |
| Evaluacion del Estandar | Estado, calificacion (automatica), fecha, observaciones |
| Documentos Sugeridos | Lista de documentos recomendados como evidencia |
| Documentos Vinculados | Documentos del cliente relacionados al estandar |
| Sidebar | Calificacion actual, informacion del estandar, acciones |

### 5.3 Ruta de Acceso

```text
/estandares/detalle/{id_cliente}/{id_estandar}
```

Ejemplo: `/estandares/detalle/11/1` para ver el estandar 1.1.1 del cliente 11.

---

## 6. Estructura de Datos del Estandar

### 6.1 Tabla tbl_estandares_minimos

```sql
id_estandar INT PRIMARY KEY
ciclo_phva ENUM('PLANEAR','HACER','VERIFICAR','ACTUAR')
categoria VARCHAR(10)
categoria_nombre VARCHAR(100)
item VARCHAR(10)           -- Ej: '1.1.1', '2.1.1'
nombre TEXT                -- Nombre del estandar
criterio TEXT              -- Pregunta de auditoria (AGREGADO)
peso_porcentual DECIMAL(5,2)
aplica_7 TINYINT(1)
aplica_21 TINYINT(1)
aplica_60 TINYINT(1)
documentos_sugeridos TEXT
activo TINYINT(1)
```

### 6.2 Tabla tbl_cliente_estandares

```sql
id_cliente_estandar INT PRIMARY KEY
id_cliente INT
id_estandar INT
estado ENUM('pendiente','en_proceso','cumple','no_cumple','no_aplica')
calificacion DECIMAL(5,2)  -- Calculada automaticamente
fecha_cumplimiento DATE
evidencia_path VARCHAR(255)
observaciones TEXT
created_at TIMESTAMP
updated_at TIMESTAMP
```

---

## 7. Archivos Modificados en Esta Fase

### 7.1 Controladores

| Archivo | Cambios |
|---------|---------|
| EstandaresClienteController.php | Calificacion automatica, acepta AJAX y POST |

### 7.2 Modelos

| Archivo | Cambios |
|---------|---------|
| EstandarMinimoModel.php | Campo criterio en allowedFields |
| ClienteEstandaresModel.php | Metodo actualizarEvaluacion() |

### 7.3 Vistas

| Archivo | Cambios |
|---------|---------|
| estandares/dashboard.php | IDs en cards, AJAX con actualizacion en tiempo real |
| estandares/detalle.php | Criterio de verificacion, calificacion automatica |

---

## 8. Conclusion

### 8.1 Mejoras Implementadas

1. **Criterio de verificacion** - Cada estandar muestra la pregunta de auditoria oficial
2. **Calificacion automatica** - Se calcula segun el estado (Res. 0312/2019)
3. **Actualizacion en tiempo real** - Los cards del dashboard se actualizan sin recargar
4. **Inicializacion controlada** - Requiere doble confirmacion

### 8.2 Beneficios

- El consultor puede ver exactamente que debe verificar para cada estandar
- No hay errores manuales en la calificacion
- Mejor experiencia de usuario con feedback inmediato
- Proceso de inicializacion mas seguro

---

*Documento actualizado: Enero 2026*
*Proyecto: EnterpriseSST - Modulo de Documentacion*
*Parte 6 de 7*
