# ZZ_97 - Estandar Toolbar de Documentos SST

## Proposito
Definir el estandar para la barra de herramientas (toolbar) que aparece en las vistas de documentos SST. Todas las vistas deben seguir estas reglas para mantener consistencia.

## Estructura HTML Estandar

```html
<!-- Barra de herramientas -->
<div class="no-print bg-dark text-white py-2 sticky-top">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <!-- IZQUIERDA: Navegacion + Titulo -->
            <div>
                <a href="<?= base_url('documentacion/' . $cliente['id_cliente']) ?>"
                   class="btn btn-outline-light btn-sm" target="_blank">
                    <i class="bi bi-arrow-left me-1"></i>Volver
                </a>
                <span class="ms-3">
                    <i class="bi <?= esc($icono) ?> me-1"></i>
                    <?= esc($cliente['nombre_cliente']) ?> - <?= esc($documento['titulo']) ?> <?= $anio ?>
                </span>
            </div>

            <!-- DERECHA: Acciones -->
            <div>
                <!-- 1. Historial (modal) -->
                <button type="button" class="btn btn-outline-light btn-sm me-2"
                        data-bs-toggle="modal" data-bs-target="#modalHistorialVersiones">
                    <i class="bi bi-clock-history me-1"></i>Historial
                </button>

                <!-- 2. Exportaciones -->
                <a href="<?= base_url('documentos-sst/exportar-pdf/' . $documento['id_documento']) ?>"
                   class="btn btn-danger btn-sm me-2" target="_blank">
                    <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                </a>
                <a href="<?= base_url('documentos-sst/exportar-word/' . $documento['id_documento']) ?>"
                   class="btn btn-primary btn-sm me-2" target="_blank">
                    <i class="bi bi-file-earmark-word me-1"></i>Word
                </a>

                <!-- 3. Botones especificos del documento (opcionales) -->
                <!-- Ej: Actualizar Datos, Editar con IA, Excel, etc. -->

                <!-- 4. Firmas (estandar, copiar tal cual) -->
                <?php if (in_array($documento['estado'] ?? '', ['generado', 'aprobado', 'en_revision', 'pendiente_firma'])): ?>
                    <a href="<?= base_url('firma/solicitar/' . $documento['id_documento']) ?>"
                       class="btn btn-success btn-sm me-2" target="_blank">
                        <i class="bi bi-pen me-1"></i>Solicitar Firmas
                    </a>
                <?php endif; ?>
                <?php if (($documento['estado'] ?? '') === 'firmado'): ?>
                    <a href="<?= base_url('firma/estado/' . $documento['id_documento']) ?>"
                       class="btn btn-outline-success btn-sm me-2" target="_blank">
                        <i class="bi bi-patch-check me-1"></i>Ver Firmas
                    </a>
                <?php endif; ?>
                <?php if (in_array($documento['estado'] ?? '', ['generado', 'aprobado', 'en_revision', 'pendiente_firma'])): ?>
                    <a href="<?= base_url('firma/estado/' . $documento['id_documento']) ?>"
                       class="btn btn-outline-warning btn-sm me-2" target="_blank">
                        <i class="bi bi-clock-history me-1"></i>Estado Firmas
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
```

## Reglas Obligatorias

### R1: target="_blank" en todos los enlaces
Todos los `<a>` de la toolbar DEBEN tener `target="_blank"` para abrir en nueva pestana.
Esto evita perder el contexto del documento mientras se exporta o revisa firmas.

**Aplica a:** Volver, PDF, Word, Excel, Solicitar Firmas, Ver Firmas, Estado Firmas, Actualizar, Editar IA.

**NO aplica a:** Botones `<button>` que abren modales (Historial, Nueva Version, Aprobar).

### R2: Condiciones de firmas unificadas
Los 3 botones de firmas siempre usan estas condiciones:

| Boton | Condicion | Estados |
|-------|-----------|---------|
| Solicitar Firmas | `in_array(estado, ['generado','aprobado','en_revision','pendiente_firma'])` | 4 estados |
| Ver Firmas | `estado === 'firmado'` | Solo firmado |
| Estado Firmas | `in_array(estado, ['generado','aprobado','en_revision','pendiente_firma'])` | 4 estados (mismos que Solicitar) |

**Excepcion:** `responsabilidades_trabajadores.php` NO tiene botones de firma (documento estatico).

### R3: Orden de botones
1. Historial (modal)
2. PDF
3. Word
4. [Botones especificos del documento]
5. Solicitar Firmas
6. Ver Firmas
7. Estado Firmas

### R4: Historial siempre es modal
El boton Historial SIEMPRE debe ser un `<button>` que abre el modal `#modalHistorialVersiones`.
NUNCA debe ser un `<a>` con enlace directo.

### R5: Boton Volver
Siempre usar `<a>` con `base_url('documentacion/' . $cliente['id_cliente'])`.
NUNCA usar `<button onclick="history.back()">`.

## Botones Especificos por Vista

Algunos documentos tienen botones adicionales entre Word y Solicitar Firmas:

| Vista | Boton Extra | Tipo | Destino |
|-------|-------------|------|---------|
| `responsabilidades_responsable_sst.php` | Actualizar Datos | `<button>` modal | `#modalRegenerarDocumento` |
| `responsabilidades_vigia_sst.php` | Actualizar | `<button>` modal | `#modalRegenerarDocumento` |
| `responsabilidades_rep_legal.php` | Actualizar Datos | `<button>` modal | `#modalRegenerarDocumento` |
| `asignacion_responsable.php` | Actualizar Datos | `<button>` modal | `#modalRegenerarDocumento` |
| `procedimiento_matriz_legal.php` | Actualizar | `<a>` target="_blank" | URL generar |
| `plan_objetivos_metas.php` | Editar con IA | `<a>` target="_blank" | URL generar |
| `plan_objetivos_metas.php` | Aprobar / Nueva Version | `<button>` modal | `#modalAprobarDocumento` |
| `procedimiento_control_documental.php` | Nueva Version | `<button>` modal | `#modalNuevaVersion` |
| `presupuesto_preview.php` | Excel | `<a>` target="_blank" | URL exportar excel |

## Inventario de Vistas

| # | Vista | Toolbar | Tiene Firmas |
|---|-------|---------|-------------|
| 1 | `documento_generico.php` | Estandar | Si |
| 2 | `generar_con_ia.php` | Layout diferente (header+sidebar) | Si (en sidebar) |
| 3 | `responsabilidades_responsable_sst.php` | Estandar + Actualizar | Si |
| 4 | `responsabilidades_vigia_sst.php` | Estandar + Actualizar | Si |
| 5 | `responsabilidades_rep_legal.php` | Estandar + Actualizar | Si |
| 6 | `responsabilidades_trabajadores.php` | Estandar (sin firmas) | No |
| 7 | `programa_capacitacion.php` | Estandar | Si |
| 8 | `programa_induccion_reinduccion.php` | Estandar | Si |
| 9 | `programa_promocion_prevencion_salud.php` | Estandar | Si |
| 10 | `procedimiento_control_documental.php` | Estandar + Nueva Version | Si |
| 11 | `procedimiento_matriz_legal.php` | Estandar + Actualizar (link) | Si |
| 12 | `plan_objetivos_metas.php` | Estandar + Editar IA + Aprobar | Si |
| 13 | `asignacion_responsable.php` | Estandar + Actualizar | Si |
| 14 | `presupuesto_preview.php` | Estandar + Excel | Si |
| 15 | `presupuesto_cliente.php` | Sin toolbar (vista lectura) | N/A |

## Clases CSS de la Toolbar

| Elemento | Clases |
|----------|--------|
| Contenedor | `no-print bg-dark text-white py-2 sticky-top` |
| Wrapper | `container-fluid` > `d-flex justify-content-between align-items-center` |
| Boton Volver | `btn btn-outline-light btn-sm` |
| Boton Historial | `btn btn-outline-light btn-sm me-2` |
| Boton PDF | `btn btn-danger btn-sm me-2` |
| Boton Word | `btn btn-primary btn-sm me-2` |
| Boton Excel | `btn btn-success btn-sm me-2` (solo presupuesto) |
| Boton Actualizar | `btn btn-warning btn-sm me-2` |
| Solicitar Firmas | `btn btn-success btn-sm me-2` |
| Ver Firmas | `btn btn-outline-success btn-sm me-2` |
| Estado Firmas | `btn btn-outline-warning btn-sm me-2` |
