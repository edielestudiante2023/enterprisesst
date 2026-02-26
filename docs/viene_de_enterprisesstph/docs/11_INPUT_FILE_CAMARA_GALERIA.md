# 11 - Input File: Camara y Galeria en Movil

## Estado: RESUELTO v2 (2026-02-23)

Bug compartido entre Acta de Visita e Inspeccion Locativa.

---

## El Bug (v1)

Al tocar el input de foto en el celular, se abria **solo la camara** sin dar opcion de seleccionar desde la **galeria de fotos**.

### Causa raiz

El atributo HTML `capture="environment"` le dice al navegador movil que abra directamente la camara trasera, saltandose el chooser nativo del sistema operativo.

```html
<!-- MAL: abre solo la camara, no permite galeria -->
<input type="file" accept="image/*" capture="environment">
```

### Comportamiento de `capture` por plataforma

| Valor | Android Chrome | iOS Safari |
|-------|---------------|------------|
| `capture="environment"` | Abre camara trasera directo | Abre camara directo |
| `capture="user"` | Abre camara frontal directo | Abre camara directo |
| **Sin `capture`** | **Muestra chooser: Camara / Archivos / Galeria** | **Muestra chooser: Camara / Galeria** |

---

## Bug v2: Sin `capture` solo abre galeria en algunos dispositivos

Al quitar `capture="environment"`, en algunos celulares el input `<input type="file" accept="image/*">` abria **solo la galeria** sin dar opcion de camara. El comportamiento del chooser nativo es **inconsistente entre dispositivos y versiones de SO**.

---

## Solucion final: Dos botones separados

En lugar de depender del chooser nativo del navegador (que varia por dispositivo), se usan **dos botones explicitos** que controlan el atributo `capture` programaticamente:

```html
<div class="photo-input-group">
    <!-- Input oculto — el name se mantiene para form submission -->
    <input type="file" name="hallazgo_imagen[]" class="file-preview" accept="image/*" style="display:none;">

    <!-- Dos botones visibles -->
    <div class="d-flex gap-1">
        <button type="button" class="btn btn-sm btn-outline-secondary btn-photo-camera">
            <i class="fas fa-camera"></i> Camara
        </button>
        <button type="button" class="btn btn-sm btn-outline-primary btn-photo-gallery">
            <i class="fas fa-images"></i> Galeria
        </button>
    </div>

    <!-- Preview de la foto seleccionada -->
    <div class="preview-img mt-1"></div>
</div>
```

### JavaScript

```javascript
// Camara: agrega capture="environment" antes de abrir
// Galeria: remueve capture para que abra el file picker
document.addEventListener('click', function(e) {
    const cameraBtn = e.target.closest('.btn-photo-camera');
    const galleryBtn = e.target.closest('.btn-photo-gallery');
    if (!cameraBtn && !galleryBtn) return;

    const group = (cameraBtn || galleryBtn).closest('.photo-input-group');
    const input = group.querySelector('input[type="file"]');

    if (cameraBtn) {
        input.setAttribute('capture', 'environment');
    } else {
        input.removeAttribute('capture');
    }
    input.click();
});

// Preview: busca el .preview-img dentro del .photo-input-group
document.addEventListener('change', function(e) {
    if (!e.target.classList.contains('file-preview')) return;
    const group = e.target.closest('.photo-input-group');
    const previewDiv = group ? group.querySelector('.preview-img') : null;
    if (!previewDiv) return;

    previewDiv.innerHTML = '';
    if (e.target.files && e.target.files[0]) {
        const reader = new FileReader();
        reader.onload = function(ev) {
            previewDiv.innerHTML = '<img src="' + ev.target.result + '" ...>' +
                '<div><i class="fas fa-check-circle"></i> Foto lista</div>';
        };
        reader.readAsDataURL(e.target.files[0]);
    }
});
```

### Por que funciona

- **Boton Camara** agrega `capture="environment"` al input oculto → el navegador abre la camara directamente
- **Boton Galeria** remueve `capture` → el navegador abre el file picker / galeria
- El usuario tiene control explicito de que quiere hacer, sin depender del chooser nativo del SO

---

## Archivos corregidos

| Archivo | Cambios |
|---------|---------|
| `app/Views/inspecciones/inspeccion_locativa/form.php` | 3 bloques (PHP existente, JS template nuevo, JS template autoguardado) + handlers JS |
| `app/Views/inspecciones/acta_visita/form.php` | 1 bloque (fotos[]) + handlers JS |

---

## Regla para futuros modulos

**SIEMPRE usar el patron de dos botones** (Camara + Galeria) para inputs de foto en inspecciones:

1. Input `type="file"` oculto con `style="display:none;"`
2. Boton Camara con clase `btn-photo-camera`
3. Boton Galeria con clase `btn-photo-gallery`
4. Contenedor `div.photo-input-group`
5. Preview `div.preview-img`

**NUNCA** usar un input file visible directo — el comportamiento del chooser nativo es impredecible entre dispositivos.

---

## Cuando SI usar capture directo (sin botones)

El unico caso valido seria si el input es **exclusivamente** para captura en vivo (ej: firma digital, scanner QR) donde NUNCA se necesita acceso a galeria.
