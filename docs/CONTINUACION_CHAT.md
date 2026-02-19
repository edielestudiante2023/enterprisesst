# PROMPT: Estado despues de nivelar UX Contexto IA en vistas generador_ia

**Fecha:** 2026-02-19
**Estado:** COMPLETADO - 14 vistas actualizadas, sintaxis validada

---

## Que se hizo

### Tarea: Nivelar UX del card "Contexto para la IA" en 14 vistas

Se replico el patron de 3 columnas de `objetivos_sgsst.php` (la vista de referencia) en las 14 vistas restantes del generador IA.

### Parte 1 - Actividades (7 vistas actualizadas):

| # | Archivo | Cambio |
|---|---------|--------|
| 1 | `pve_riesgo_psicosocial.php` | 2-col → 3-col + Estado Actual separado |
| 2 | `pve_riesgo_biomecanico.php` | 2-col (datos+estado) → 3-col + Estado Actual separado |
| 3 | `estilos_vida_saludable.php` | 2-col (datos+peligros) → 3-col con infraestructura |
| 4 | `evaluaciones_medicas_ocupacionales.php` | 2-col → 3-col con infraestructura |
| 5 | `mantenimiento_periodico.php` | 2-col → 3-col, conservando seccion Inventario Activos |
| 6 | `pyp_salud.php` | 2-col → 3-col con infraestructura |
| 7 | `capacitacion_sst.php` | 2-col → 3-col con infraestructura |

### Parte 2 - Indicadores (7 vistas actualizadas):

| # | Archivo | Cambio |
|---|---------|--------|
| 8 | `indicadores_pve_psicosocial.php` | Card contexto NUEVO (colapsado por defecto) |
| 9 | `indicadores_pve_biomecanico.php` | Card contexto NUEVO (colapsado) |
| 10 | `indicadores_estilos_vida_saludable.php` | Card contexto NUEVO (colapsado) |
| 11 | `indicadores_evaluaciones_medicas_ocupacionales.php` | Card contexto NUEVO (colapsado) |
| 12 | `indicadores_mantenimiento_periodico.php` | Card contexto NUEVO (colapsado) |
| 13 | `indicadores_pyp_salud.php` | Card contexto NUEVO (colapsado) |
| 14 | `indicadores_objetivos.php` | Card contexto NUEVO (colapsado, entre info y instrucciones) |

### Patron aplicado en cada vista:

**Parte 1 (Actividades):**
- PHP prep block ANTES del card (variables $riesgo, $colorRiesgo, $peligros, $infraestructura)
- Card header: titulo + subtitulo + boton "Editar Contexto" (target="_blank") + collapse chevron
- 3 columnas (col-md-4): Datos Empresa | Infraestructura SST | Peligros Identificados
- Observaciones colapsable con nl2br y scroll
- Textarea instrucciones IA con placeholder contextualizado

**Parte 2 (Indicadores):**
- Card colapsado por defecto (collapse sin show)
- Boton "Ver contexto IA" en vez de chevron
- Mismas 3 columnas
- SIN textarea instrucciones

### Estilos aplicados:
- Peligros: `bg-danger-subtle text-danger` (font-size: 0.7rem, max-height: 120px scroll)
- Infraestructura: `bg-success-subtle text-success` con icono `bi-check`
- Riesgo ARL: badge coloreado con match() (I,II=success, III=warning, IV,V=danger)
- Observaciones: `alert alert-light border small`, max-height: 100px scroll
- Link contexto: `base_url('contexto/' . $cliente['id_cliente'])` con target="_blank"

---

## Pendiente para verificar visualmente

1. Probar en navegador con cliente 18 (tiene datos ricos)
2. Verificar collapse funciona en todas las vistas
3. Verificar "Editar Contexto" abre en nueva pestana
4. Verificar scroll de peligros y observaciones
5. Verificar que indicadores muestran "Ver contexto IA" colapsado

---

## Tarea completada anteriormente (mismo chat)

### Backend: Fix hardcodeo en 15 services PHP
- Todos los services usan `construirContextoCompleto()` de ObjetivosSgsstService
- Documentado en `docs/MODULO_NUMERALES_SGSST/FIX_HARDCODEO_BLUEPRINT.md`
- Auditoria: 7 rojos → todos verdes, 8 amarillos → todos verdes
