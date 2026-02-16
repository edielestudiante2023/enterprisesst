# INSTRUCTIVO: SweetAlert de Verificacion de Datos antes de Generar con IA

## Por Que Existe Este Control

Sin este paso, **NO habia garantia** de que la IA usara datos reales del cliente.
El documento podia generarse con datos inventados o genericos y el usuario no tenia forma de saberlo.

Este SweetAlert es un **punto de control critico** que:

- Muestra al usuario EXACTAMENTE que datos alimentaran la IA
- Le da confianza de que el documento sera personalizado con sus datos reales
- Permite cancelar si los datos estan incompletos o incorrectos

---

## Flujo Visual

```
Usuario da clic en "Generar con IA"
         |
         v
  Loading: "Consultando datos..."
         |
         v
  AJAX GET /documentos/previsualizar-datos/{tipo}/{id_cliente}
         |
         v
  Backend detecta flujo del documento:
  - Si flujo = programa_con_pta (3 partes):
    consulta 3 fuentes:
    - tbl_pta_cliente (Actividades)
    - tbl_indicadores_sst (Indicadores)
    - tbl_cliente_contexto_sst (Contexto)
  - Si flujo = secciones_ia (1 parte):
    consulta 1 fuente:
    - tbl_cliente_contexto_sst (Contexto)
    (NO consulta PTA ni Indicadores)
  + SIEMPRE consulta marco normativo:
    - tbl_marco_normativo (Insumos IA - Pregeneracion)
         |
         v
  SweetAlert muestra resumen:
  +---------------------------------------+
  | Plan de Trabajo (29 actividades):     |
  |   - Induccion SST (Ene 2026)         |
  |   - Capacitacion alturas (Mar 2026)  |
  |   - ...                              |
  |                                       |
  | Indicadores (3 configurados):         |
  |   - Cobertura (Meta: 90%)            |
  |   - Cumplimiento (Meta: 100%)        |
  |                                       |
  | Marco Normativo:                      |
  |   Estado: Vigente (21 dias - 2026-01)|
  |   Metodo: boton                      |
  |   Resolucion 312 de 2019, Ley 1562...|
  |                                       |
  | Contexto:                             |
  |   Empresa: ACME S.A.S                |
  |   Actividad: Construccion            |
  |   Riesgo: V | Trabajadores: 280      |
  |   Estandares: 60                     |
  |   Peligros: ruido, alturas, quimicos |
  |   Estructuras: COPASST, Brigada      |
  |   Observaciones: Obra en zona rural  |
  |                                       |
  |     [ Cancelar ]  [ Generar con IA ] |
  +---------------------------------------+
         |
         v
  Si confirma -> Procede generacion IA
  Si cancela  -> No hace nada
```

---

## Campos de Contexto del Cliente

### Campos Relevantes para la IA (se muestran en el SweetAlert)

| Campo | Tabla/Columna | Por Que es Relevante |
|-------|---------------|----------------------|
| Empresa | `tbl_clientes.nombre_cliente` | Personaliza el documento |
| Actividad economica | `tbl_cliente_contexto_sst.actividad_economica_principal` (fallback: `sector_economico`, `codigo_actividad_economica`) | Define sector, riesgos propios, normativa aplicable |
| Nivel de riesgo ARL | `tbl_cliente_contexto_sst.nivel_riesgo_arl` (I-V) | Determina exigencias legales y profundidad de programas |
| Total trabajadores | `tbl_cliente_contexto_sst.total_trabajadores` | Define COPASST vs Vigia, estandares minimos (7/21/60) |
| Estandares aplicables | `tbl_cliente_contexto_sst.estandares_aplicables` (7/21/60) | La IA ajusta complejidad y extension del documento |
| Peligros identificados | `tbl_cliente_contexto_sst.peligros_identificados` (JSON) | Contextualizan programas con riesgos REALES |
| COPASST / Vigia | `tbl_cliente_contexto_sst.tiene_copasst`, `tiene_vigia_sst` | Para referenciar el comite correcto |
| Comite Convivencia | `tbl_cliente_contexto_sst.tiene_comite_convivencia` | Relevante en politicas de convivencia |
| Brigada Emergencias | `tbl_cliente_contexto_sst.tiene_brigada_emergencias` | Para programas de emergencias y prevencion |
| Observaciones contexto | `tbl_cliente_contexto_sst.observaciones_contexto` | Contexto cualitativo que enriquece la generacion |

### Campos NO Relevantes para la IA (son administrativos)

| Campo | Uso Real |
|-------|----------|
| NIT | Encabezados del PDF, no afecta contenido IA |
| ARL actual | Dato administrativo |
| Sedes, turnos | Dato administrativo |
| Datos de firmantes | Para firma electronica, no para contenido |
| Licencia SST | Dato legal del responsable |

---

## Marco Normativo (Insumos IA - Pregeneración)

### Por Qué se Incluye en el SweetAlert

El marco normativo es **crítico** para generar documentos con legislación actualizada. Mostrar su estado en el SweetAlert permite al consultor:

1. **Verificar vigencia:** Saber si la IA usará normativa actualizada o conocimiento base (puede estar desactualizado)
2. **Decidir actualizar:** Si el marco está vencido, puede ir al panel de "Insumos IA" y actualizarlo antes de generar
3. **Tener confianza:** Ver que el documento se generará con las normas correctas

### Información que se Muestra

| Campo | Descripción |
|-------|-------------|
| Estado | **Vigente** (verde) si tiene menos de `vigencia_dias`, **Vencido** (rojo) si superó el límite |
| Fecha actualización | Cuándo fue la última actualización del marco normativo |
| Días transcurridos | Cuántos días han pasado desde la última actualización |
| Método | Cómo se actualizó: `automatico`, `boton`, `confirmacion`, `manual` |
| Preview del texto | Primeros 200 caracteres del marco normativo |

### Casos Posibles

#### ✅ Marco Normativo Vigente
```
Marco Normativo:
  Estado: Vigente (Actualizado hace 21 días - 2026-01-25)
  Método: boton
  Resolución 312 de 2019, Ley 1562 de 2012, Decreto 1072 de 2015...
```

#### ⚠️ Marco Normativo Vencido
```
Marco Normativo:
  Estado: Vencido (Actualizado hace 95 días - 2025-11-10)
  Método: automatico
  Resolución 0312 de 2019, Ley 1562 de 2012...
```

**Recomendación:** Ir al panel "Insumos IA - Pregeneración" y actualizar antes de generar.

#### ⚠️ Sin Marco Normativo
```
Marco Normativo:
  No hay marco normativo registrado. La IA usará su conocimiento base (puede estar desactualizado).
```

**Recomendación:** Consultar marco normativo con IA antes de generar el documento.

---

## Endpoint Backend

### Ruta

```php
// app/Config/Routes.php
$routes->get('documentos/previsualizar-datos/(:segment)/(:num)',
    'DocumentosSSTController::previsualizarDatos/$1/$2');
```

### Controlador

```php
// app/Controllers/DocumentosSSTController.php

public function previsualizarDatos(string $tipoDocumento, int $idCliente)
{
    $cliente = $this->clienteModel->find($idCliente);
    $contextoModel = new ClienteContextoSstModel();
    $contexto = $contextoModel->getByCliente($idCliente);
    $anio = (int) date('Y');

    $handler = DocumentoSSTFactory::crear($tipoDocumento);

    // ══════════════════════════════════════════════
    // ACTIVIDADES DEL PLAN DE TRABAJO (Fase 1)
    // Solo para documentos de 3 partes (programa_con_pta)
    // ══════════════════════════════════════════════
    // Consulta tbl_pta_cliente filtrando por tipo_servicio
    // segun getFiltroServicioPTA($tipoDocumento)

    // ══════════════════════════════════════════════
    // INDICADORES (Fase 2)
    // Solo para documentos de 3 partes (programa_con_pta)
    // ══════════════════════════════════════════════
    // Consulta tbl_indicadores_sst filtrando por categoria
    // segun getCategoriaIndicador($tipoDocumento)

    // ══════════════════════════════════════════════
    // INSUMOS IA - PREGENERACION (Marco Normativo)
    // SIEMPRE se consulta (todos los flujos)
    // ══════════════════════════════════════════════
    $marcoNormativoInfo = [
        'existe' => false,
        'vigente' => false,
        'texto_preview' => '',
        'fecha' => '',
        'dias' => 0,
        'metodo' => '',
    ];

    try {
        $marcoService = new \App\Services\MarcoNormativoService();
        $infoMarco = $marcoService->obtenerInfo($tipoDocumento);

        if ($infoMarco['existe']) {
            $marcoNormativoInfo = [
                'existe' => true,
                'vigente' => $infoMarco['vigente'],
                'texto_preview' => mb_substr($infoMarco['texto'], 0, 200) . '...',
                'fecha' => $infoMarco['fecha'],
                'dias' => $infoMarco['dias'],
                'metodo' => $infoMarco['metodo'],
            ];
        }
    } catch (\Exception $e) {
        log_message('error', "Error consultando marco normativo: " . $e->getMessage());
    }

    // ══════════════════════════════════════════════
    // RESPUESTA JSON
    // ══════════════════════════════════════════════
    return $this->response->setJSON([
        'ok'          => true,
        'tipo'        => $handler->getNombre(),
        'flujo'       => $flujo,
        'actividades' => $actividades,    // [{nombre, mes, estado}]
        'indicadores' => $indicadores,    // [{nombre, tipo, meta}]
        'contexto'    => [
            'empresa'              => $cliente['nombre_cliente'],
            'actividad_economica'  => $contexto['actividad_economica_principal']
                                      ?? $contexto['sector_economico']
                                      ?? $cliente['codigo_actividad_economica']
                                      ?? 'No especificada',
            'nivel_riesgo'         => $contexto['nivel_riesgo_arl'],
            'total_trabajadores'   => $contexto['total_trabajadores'],
            'estandares_aplicables'=> $contexto['estandares_aplicables'] ?? 7,
            'peligros'             => $contexto['peligros_identificados'] ?? '[]',
            'tiene_copasst'        => (bool)($contexto['tiene_copasst'] ?? false),
            'tiene_vigia_sst'      => (bool)($contexto['tiene_vigia_sst'] ?? false),
            'tiene_comite_convivencia' => (bool)($contexto['tiene_comite_convivencia'] ?? false),
            'tiene_brigada'        => (bool)($contexto['tiene_brigada_emergencias'] ?? false),
            'observaciones'        => $contexto['observaciones_contexto'] ?? '',
        ],
        'marco_normativo' => $marcoNormativoInfo
    ]);
}
```

### Mapeo de Filtros por Tipo de Documento

```php
// getFiltroServicioPTA() - Mapea tipo_documento a filtros de tbl_pta_cliente
private function getFiltroServicioPTA(string $tipoDocumento): array
{
    $filtros = [
        'programa_capacitacion' => [
            ['type' => 'exact', 'value' => 'Programa de Capacitacion'],
            ['type' => 'like',  'value' => 'Capacitacion'],
            // + filtros por actividad_plandetrabajo
        ],
        'programa_promocion_prevencion_salud' => [
            ['type' => 'exact', 'value' => 'Programa PyP Salud'],
            ['type' => 'like',  'value' => 'Promocion'],
            // + filtros por actividad_plandetrabajo
        ],
        // Agregar nuevos tipos aqui
    ];
    return $filtros[$tipoDocumento] ?? [];
}

// getCategoriaIndicador() - Mapea tipo_documento a categorias de tbl_indicadores_sst
private function getCategoriaIndicador(string $tipoDocumento): array
{
    $categorias = [
        'programa_capacitacion' => ['capacitacion'],
        'programa_promocion_prevencion_salud' => ['promocion_prevencion_salud'],
        // Agregar nuevos tipos aqui
    ];
    return $categorias[$tipoDocumento] ?? [];
}
```

---

## Frontend JavaScript

### Cache de Datos

```javascript
let datosPreviewCache = null; // Cache para no consultar cada clic

async function obtenerDatosPreview() {
    if (datosPreviewCache) return datosPreviewCache;

    const url = `${BASE_URL}documentos/previsualizar-datos/${tipo}/${idCliente}`;

    const resp = await fetch(url);
    const data = await resp.json();

    if (data.ok) {
        datosPreviewCache = data;
        return data;
    }
    return null;
}
```

### Funcion mostrarVerificacionDatos()

```javascript
async function mostrarVerificacionDatos(callback) {
    // 1. Loading mientras consulta
    Swal.fire({
        title: 'Consultando datos...',
        text: 'Verificando Plan de Trabajo e Indicadores',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });

    const data = await obtenerDatosPreview();

    // 2. Si falla, dar opcion de continuar
    if (!data) {
        const errorResult = await Swal.fire({
            title: 'No se pudieron obtener los datos',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Generar de todas formas',
            cancelButtonText: 'Cancelar'
        });
        if (errorResult.isConfirmed) callback();
        return;
    }

    // 3. Construir HTML con datos reales
    let html = '<div style="text-align: left; max-height: 400px; overflow-y: auto;">';

    // --- Plan de Trabajo ---
    html += '<h6>Plan de Trabajo (' + data.actividades.length + ' actividades):</h6>';
    html += '<ul>';
    data.actividades.forEach(a => {
        html += '<li>' + a.nombre + ' (' + a.mes + ')</li>';
    });
    html += '</ul>';

    // --- Indicadores ---
    html += '<h6>Indicadores (' + data.indicadores.length + '):</h6>';
    html += '<ul>';
    data.indicadores.forEach(i => {
        html += '<li>' + i.nombre + ' (Meta: ' + i.meta + ')</li>';
    });
    html += '</ul>';

    // --- Marco Normativo ---
    if (data.marco_normativo && data.marco_normativo.existe) {
        const esVigente = data.marco_normativo.vigente;
        const icono = esVigente ? '&#9989;' : '&#9888;&#65039;';
        const estado = esVigente
            ? '<span style="color: #28a745;">Vigente</span>'
            : '<span style="color: #dc3545;">Vencido</span>';

        html += '<h6><strong>' + icono + ' Marco Normativo:</strong></h6>';
        html += '<div style="padding-left: 20px;">';
        html += '<p><strong>Estado:</strong> ' + estado + ' (Actualizado hace '
              + data.marco_normativo.dias + ' días - ' + data.marco_normativo.fecha + ')</p>';
        html += '<p><strong>Método:</strong> ' + (data.marco_normativo.metodo || 'N/A') + '</p>';
        html += '<p style="color: #6c757d;"><em>' + data.marco_normativo.texto_preview + '</em></p>';
        html += '</div>';
    } else {
        html += '<h6><strong>&#9888;&#65039; Marco Normativo:</strong></h6>';
        html += '<p style="color: #856404; padding-left: 20px;">No hay marco normativo registrado. '
              + 'La IA usará su conocimiento base (puede estar desactualizado).</p>';
    }

    // --- Contexto ---
    html += '<h6>Contexto:</h6>';
    html += '<p>Empresa: ' + data.contexto.empresa + '</p>';
    html += '<p>Actividad: ' + data.contexto.actividad_economica + '</p>';
    html += '<p>Riesgo: ' + data.contexto.nivel_riesgo
          + ' | Trabajadores: ' + data.contexto.total_trabajadores
          + ' | Estandares: ' + data.contexto.estandares_aplicables + '</p>';

    // Peligros
    let peligros = JSON.parse(data.contexto.peligros || '[]');
    if (peligros.length > 0) {
        html += '<p>Peligros: ' + peligros.join(', ') + '</p>';
    }

    // Estructuras organizacionales
    let estructuras = [];
    if (data.contexto.tiene_copasst) estructuras.push('COPASST');
    if (data.contexto.tiene_vigia_sst) estructuras.push('Vigia SST');
    if (data.contexto.tiene_comite_convivencia) estructuras.push('Comite Convivencia');
    if (data.contexto.tiene_brigada) estructuras.push('Brigada Emergencias');
    if (estructuras.length > 0) {
        html += '<p>Estructuras: ' + estructuras.join(', ') + '</p>';
    }

    // Observaciones
    if (data.contexto.observaciones && data.contexto.observaciones.trim() !== '') {
        html += '<p>Observaciones: ' + data.contexto.observaciones + '</p>';
    }

    html += '</div>';

    // 4. Mostrar SweetAlert
    const result = await Swal.fire({
        title: data.tipo,
        html: html,
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Generar con IA',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#198754',
        width: '600px'
    });

    if (result.isConfirmed) callback();
}
```

### Integracion con Botones

```javascript
// Boton individual "Generar con IA" por seccion
document.querySelectorAll('.btn-generar, .btn-regenerar').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const seccion = this.dataset.seccion;
        mostrarVerificacionDatos(() => generarSeccion(seccion));
    });
});

// Boton "Generar Todo"
btnGenerarTodo.addEventListener('click', async function() {
    // Mismo flujo pero muestra resumen de TODAS las secciones
    mostrarVerificacionDatos(() => generarTodasLasSecciones());
});
```

---

## Regla: Se Muestra UNA Sola Vez por Sesion

### Por Que

El SweetAlert es un **control de confianza previo**. Una vez que el usuario confirma que vio los datos, no tiene sentido volver a mostrarlo en cada clic de "Generar con IA" o "Regenerar". Esto causa:

- Friccion innecesaria cuando el usuario da contexto adicional y regenera
- Sensacion de loop (escribir contexto -> clic -> popup -> confirmar -> generar -> ajustar -> clic -> popup otra vez)
- No aporta valor despues de la primera confirmacion

### Implementacion

```javascript
let verificacionConfirmada = false; // Flag de sesion

async function mostrarVerificacionDatos(callback) {
    // Si ya confirmo una vez, ejecutar directamente
    if (verificacionConfirmada) {
        callback();
        return;
    }

    // ... mostrar SweetAlert ...

    if (result.isConfirmed) {
        verificacionConfirmada = true; // No volver a mostrar
        callback();
    }
}
```

El flag `verificacionConfirmada` se activa al confirmar y dura toda la sesion de la pagina. Si el usuario recarga la pagina, vuelve a aparecer.

---

## Notas Tecnicas

### Estilos Inline (NO Bootstrap classes)

SweetAlert2 usa su propio DOM separado. Las clases de Bootstrap (como `text-start`, `mb-3`, `text-muted`) **NO funcionan** dentro del HTML del SweetAlert. Se deben usar **estilos inline**:

```javascript
// MAL - no funciona en SweetAlert
html += '<div class="text-start mb-3">';

// BIEN - funciona en SweetAlert
html += '<div style="text-align: left; margin-bottom: 12px;">';
```

### Emojis como HTML Entities

En lugar de caracteres emoji que pueden no renderizarse bien, usar entidades HTML:

```javascript
'&#9989;'        // check verde
'&#9888;&#65039;' // warning
'&#127970;'      // edificio
```

### Discrepancia de Nombres de Campos

Los nombres en la tabla `tbl_cliente_contexto_sst` NO siempre coinciden con los que usa el codigo:

| Codigo busca | Tabla tiene | Solucion |
|--------------|-------------|----------|
| `nivel_riesgo` | `nivel_riesgo_arl` | Usar nombre real de BD |
| `numero_trabajadores` | `total_trabajadores` | Usar nombre real de BD |
| `actividad_economica` | `actividad_economica_principal` | Usar con fallback a `sector_economico` |

---

## Checklist para Nuevo Tipo de Documento

Al agregar un nuevo tipo de documento al sistema, asegurar:

- [ ] Agregar entrada en `getFiltroServicioPTA()` con los filtros correctos para ese tipo
- [ ] Agregar entrada en `getCategoriaIndicador()` con la categoria correcta
- [ ] Verificar que el SweetAlert muestre datos (probar en consola del navegador)
- [ ] Si los datos salen vacios, revisar que el cliente tenga registros en Parte 1 y 2

---

## Relacion con Otros Documentos

| Documento | Relacion |
|-----------|----------|
| `ZZ_80_PARTE1.md` | Las actividades que se muestran en el SweetAlert vienen de Parte 1 |
| `ZZ_81_PARTE2.md` | Los indicadores que se muestran vienen de Parte 2 |
| `ZZ_95_PARTE3.md` | El SweetAlert aparece ANTES de que la Parte 3 genere el documento |
| `ZZ_77_PREPARACION.md` | El contexto del cliente se configura en la preparacion |
| `INSUMOS_IA_PREGENERACION.md` | El marco normativo mostrado viene del módulo de Insumos IA |

---

## Historial de Cambios

| Fecha | Cambio |
|-------|--------|
| 2026-02-15 | **✅ Agregada sección de Marco Normativo** - El SweetAlert ahora muestra información de "Insumos IA - Pregeneración" (marco normativo vigente) para que el consultor verifique si la IA usará legislación actualizada antes de generar el documento |
