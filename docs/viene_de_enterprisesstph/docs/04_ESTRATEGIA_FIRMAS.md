# Estrategia de Firmas - Acta de Visita

---

## 1. Sistemas de Firma Existentes en el Proyecto

El proyecto ya tiene **2 subsistemas de firma** con enfoques diferentes:

| Aspecto | SST Docs (FirmaElectronicaController) | Contratos (ContractController) |
|---------|---------------------------------------|-------------------------------|
| **Almacenamiento** | Base64 LONGTEXT en BD (`tbl_doc_firma_evidencias.firma_imagen`) | PNG archivo en disco (`uploads/firmas/`) |
| **PDF engine** | DOMPDF (`<img src="data:...">` inline) | TCPDF (`$pdf->Image($filePath)`) |
| **Flujo** | Remoto: se envia link por email, firman por separado (cadena) | Remoto: link por email, firma unica |
| **Canvas** | Con DPR (devicePixelRatio), crop inteligente, 150px height | Sin DPR, raw toDataURL |
| **Multi-firma** | Si (Delegado SST -> Rep. Legal, en cadena) | No (solo Rep. Legal) |
| **Token** | `bin2hex(random_bytes(32))`, 7 dias TTL | `bin2hex(random_bytes(32))`, 7 dias TTL |
| **Proteccion anti-accidental** | 3 capas (multi-touch, pixeles, SweetAlert) | 3 capas (multi-touch, pixeles, SweetAlert) |

---

## 2. Caso del Acta de Visita: Es DIFERENTE a ambos

El acta de visita tiene un contexto unico:

| Aspecto | SST Docs / Contratos | Acta de Visita |
|---------|----------------------|----------------|
| **Donde se firma** | Remoto (cada quien desde su dispositivo) | **PRESENCIAL** (todos en el mismo celular) |
| **Quien firma** | Un firmante a la vez, con su propio link | **3 personas seguidas** en la misma sesion |
| **Dispositivo** | PC o celular del firmante | **Celular del consultor** (PWA) |
| **Token necesario** | Si (link unico por email) | **NO** (sesion autenticada del consultor) |
| **Momento** | Despues de generar el doc (asincronico) | **Durante la visita** (sincronico) |

### Flujo real en campo

```
Consultor llena el acta en su celular
    |
    v
Toca "Ir a firmas"
    |
    v
Le pasa el celular al Administrador
    -> Administrador firma en el canvas
    -> Devuelve el celular
    |
    v
Le pasa el celular al Vigia (si aplica)
    -> Vigia firma en el canvas
    -> Devuelve el celular
    |
    v
Consultor firma el mismo
    |
    v
Toca "Finalizar y generar PDF"
    -> PDF se genera con las 3 firmas incrustadas
```

**Es el mismo flujo de AppSheet:** el consultor pasa el celular, cada quien firma, y listo.

---

## 3. Estrategia Recomendada: Hibrido (Lo mejor de ambos)

### Canvas: Del sistema SST (firmar.php) -- el mas robusto

- **devicePixelRatio** para pantallas de alta densidad (celulares modernos)
- **exportarFirmaOptimizada()** con bounding box crop + resize a 150px height
- **3 capas anti-accidental** (multi-touch filter, 100 pixeles minimo, SweetAlert preview)
- `touch-action: none` en CSS

### Almacenamiento: PNG en disco (como contratos) -- mas eficiente

- Guardar en `uploads/inspecciones/firmas/`
- Nombre: `firma_{tipo}_{id_acta}_{timestamp}.png`
- En BD solo se guarda la ruta (VARCHAR 255), no el base64
- Razon: el base64 en LONGTEXT de los SST docs funciona pero es ineficiente para volumen

### PDF: DOMPDF (como SST docs) -- consistente con el modulo

- Las firmas se cargan del disco y se convierten a base64 inline para DOMPDF:

```php
$firmaPath = FCPATH . $acta['firma_administrador'];
$firmaBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($firmaPath));
// En el template: <img src="<?= $firmaBase64 ?>" width="150">
```

### Sin tokens ni links: Sesion directa

- No se necesita token ni link por email
- El consultor esta logueado en la PWA, la sesion es la autenticacion
- Las firmas se guardan via AJAX `POST /inspecciones/acta-visita/firma/{id}`

---

## 4. Implementacion Detallada

### 4.1 Pantalla de Firmas (`firma.php`)

Pagina unica con 3 canvas, uno por firmante. Flujo paso a paso:

```
+------------------------------------------+
|  Firmas del Acta                         |
|                                          |
|  PASO 1 de 3: Firma del Administrador    |
|  EDITA SANABRIA ESPINOZA                 |
|  +------------------------------------+  |
|  |                                    |  |
|  |        (canvas firma)             |  |
|  |                                    |  |
|  +------------------------------------+  |
|  [Limpiar]                               |
|                                          |
|  [-> Siguiente: Firma del Vigia]         |
+------------------------------------------+
```

Despues de firmar cada paso:

```
+------------------------------------------+
|  PASO 2 de 3: Firma del Vigia SST        |
|  PEDRO PEREZ                             |
|  +------------------------------------+  |
|  |        (canvas firma)             |  |
|  +------------------------------------+  |
|  [Limpiar]  [Omitir - No aplica]         |
|                                          |
|  [<- Anterior] [-> Siguiente]            |
+------------------------------------------+
```

Ultimo paso:

```
+------------------------------------------+
|  PASO 3 de 3: Firma del Consultor        |
|  EDISON CUERVO                           |
|  +------------------------------------+  |
|  |        (canvas firma)             |  |
|  +------------------------------------+  |
|  [Limpiar]                               |
|                                          |
|  [<- Anterior]                           |
|  [Finalizar y generar PDF]               |
+------------------------------------------+
```

### 4.2 Logica de que firmas mostrar

Se determina automaticamente segun los integrantes del acta:

```javascript
// Pseudocodigo
const firmantes = [];

// Buscar integrante con rol ADMINISTRADOR
const admin = integrantes.find(i => i.rol === 'ADMINISTRADOR');
if (admin) firmantes.push({ tipo: 'administrador', nombre: admin.nombre });

// Buscar integrante con rol VIGIA SST
const vigia = integrantes.find(i => i.rol.includes('VIG'));
if (vigia) firmantes.push({ tipo: 'vigia', nombre: vigia.nombre });

// Consultor siempre firma
firmantes.push({ tipo: 'consultor', nombre: session.nombre_consultor });
```

**Reglas:**
- Si hay integrante con rol ADMINISTRADOR -> aparece canvas "Firma del Administrador"
- Si hay integrante con rol que contiene "VIG" (VIGIA SST) -> aparece canvas "Firma del Vigia" con opcion "No aplica"
- Firma del Consultor -> SIEMPRE aparece, es obligatoria
- Minimo 1 firma (consultor), maximo 3 firmas

### 4.3 Canvas JavaScript (reutilizado de firmar.php)

```javascript
class SignatureCanvas {
    constructor(canvasId, options = {}) {
        this.canvas = document.getElementById(canvasId);
        this.ctx = this.canvas.getContext('2d');
        this.dibujando = false;
        this.hayDibujo = false;
        this.dpr = window.devicePixelRatio || 1;
        this.setup();
    }

    setup() {
        // DPR-aware sizing
        const rect = this.canvas.getBoundingClientRect();
        this.canvas.width = rect.width * this.dpr;
        this.canvas.height = 200 * this.dpr;
        this.ctx.scale(this.dpr, this.dpr);
        this.canvas.style.touchAction = 'none';

        // Stroke style
        this.ctx.strokeStyle = '#000';
        this.ctx.lineWidth = 3;
        this.ctx.lineCap = 'round';
        this.ctx.lineJoin = 'round';

        // Mouse events
        this.canvas.addEventListener('mousedown', (e) => this.iniciar(e));
        this.canvas.addEventListener('mousemove', (e) => this.dibujar(e));
        this.canvas.addEventListener('mouseup', () => this.terminar());
        this.canvas.addEventListener('mouseout', () => this.terminar());

        // Touch events con filtro multi-touch (Layer 1)
        this.canvas.addEventListener('touchstart', (e) => {
            e.preventDefault();
            if (e.touches.length > 1) return;
            this.iniciar(e.touches[0]);
        });
        this.canvas.addEventListener('touchmove', (e) => {
            e.preventDefault();
            if (e.touches.length > 1) { this.terminar(); return; }
            this.dibujar(e.touches[0]);
        });
        this.canvas.addEventListener('touchend', () => this.terminar());
    }

    getPos(e) {
        const rect = this.canvas.getBoundingClientRect();
        return { x: e.clientX - rect.left, y: e.clientY - rect.top };
    }

    iniciar(e) {
        this.dibujando = true;
        this.hayDibujo = true;
        const pos = this.getPos(e);
        this.ctx.beginPath();
        this.ctx.moveTo(pos.x, pos.y);
    }

    dibujar(e) {
        if (!this.dibujando) return;
        const pos = this.getPos(e);
        this.ctx.lineTo(pos.x, pos.y);
        this.ctx.stroke();
    }

    terminar() {
        this.dibujando = false;
    }

    limpiar() {
        this.ctx.setTransform(1, 0, 0, 1, 0, 0);
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        this.setup();
        this.hayDibujo = false;
    }

    // Validacion Layer 2: minimo 100 pixeles oscuros
    validarMinPixeles(minimo = 100) {
        const imgData = this.ctx.getImageData(0, 0, this.canvas.width, this.canvas.height).data;
        let pixelesOscuros = 0;
        for (let i = 3; i < imgData.length; i += 4) {
            if (imgData[i] > 0) pixelesOscuros++;
        }
        return pixelesOscuros >= minimo;
    }

    // Export optimizado: crop + resize a 150px height
    exportar() {
        const imageData = this.ctx.getImageData(0, 0, this.canvas.width, this.canvas.height);
        const data = imageData.data;

        let minX = this.canvas.width, minY = this.canvas.height, maxX = 0, maxY = 0;
        for (let y = 0; y < this.canvas.height; y++) {
            for (let x = 0; x < this.canvas.width; x++) {
                const alpha = data[(y * this.canvas.width + x) * 4 + 3];
                if (alpha > 0) {
                    if (x < minX) minX = x;
                    if (x > maxX) maxX = x;
                    if (y < minY) minY = y;
                    if (y > maxY) maxY = y;
                }
            }
        }

        if (maxX <= minX || maxY <= minY) return this.canvas.toDataURL('image/png');

        const padding = 20;
        minX = Math.max(0, minX - padding);
        minY = Math.max(0, minY - padding);
        maxX = Math.min(this.canvas.width, maxX + padding);
        maxY = Math.min(this.canvas.height, maxY + padding);

        const tempCanvas = document.createElement('canvas');
        const tempCtx = tempCanvas.getContext('2d');
        const cropW = maxX - minX;
        const cropH = maxY - minY;
        const finalH = 150;
        const finalW = Math.round(finalH * (cropW / cropH));

        tempCanvas.width = finalW;
        tempCanvas.height = finalH;
        tempCtx.drawImage(this.canvas, minX, minY, cropW, cropH, 0, 0, finalW, finalH);

        return tempCanvas.toDataURL('image/png');
    }

    // Preview para SweetAlert (Layer 3)
    getPreview() {
        return this.exportar();
    }
}
```

### 4.4 Guardado Server-Side

```php
// POST /inspecciones/acta-visita/firma/{id}
public function saveFirma($id)
{
    $acta = $this->actaModel->find($id);
    $tipo = $this->request->getPost('tipo');       // 'administrador', 'vigia', 'consultor'
    $firmaBase64 = $this->request->getPost('firma_imagen'); // data:image/png;base64,...

    // Decodificar base64 a PNG
    $firmaData = explode(',', $firmaBase64);
    $firmaDecoded = base64_decode(end($firmaData));

    // Guardar archivo
    $dir = FCPATH . 'uploads/inspecciones/firmas/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $nombreArchivo = "firma_{$tipo}_{$id}_" . time() . '.png';
    file_put_contents($dir . $nombreArchivo, $firmaDecoded);

    // Guardar ruta en BD
    $campo = "firma_{$tipo}"; // firma_administrador, firma_vigia, firma_consultor
    $this->actaModel->update($id, [
        $campo => "uploads/inspecciones/firmas/{$nombreArchivo}"
    ]);

    return $this->response->setJSON(['success' => true, 'campo' => $campo]);
}
```

### 4.5 Incrustar en PDF (DOMPDF)

```php
// En ActaVisitaController::generatePdf($id)
$acta = $this->actaModel->find($id);

$firmas = [];
foreach (['administrador', 'vigia', 'consultor'] as $tipo) {
    $campo = "firma_{$tipo}";
    if (!empty($acta[$campo])) {
        $path = FCPATH . $acta[$campo];
        if (file_exists($path)) {
            $firmas[$tipo] = 'data:image/png;base64,' . base64_encode(file_get_contents($path));
        }
    }
}

// Pasar a la vista DOMPDF
$data['firmas'] = $firmas;
$html = view('inspecciones/acta_visita/pdf', $data);

// En pdf.php:
// <img src="<?= $firmas['administrador'] ?? '' ?>" width="150">
```

---

## 5. Estructura de Archivos de Firma

```
uploads/
  inspecciones/
    firmas/
      firma_administrador_1_1708432000.png
      firma_vigia_1_1708432001.png
      firma_consultor_1_1708432002.png
      firma_administrador_2_1708518400.png
      ...
    fotos/
      foto_1_1708432000.jpg
      ...
    pdfs/
      acta_visita_1_20260219.pdf
      ...
```

---

## 6. Permisos en Produccion

Igual que `uploads/firmas/` existente:

```bash
# En servidor de produccion
mkdir -p /www/wwwroot/phorizontal/enterprisesstph/uploads/inspecciones/firmas
mkdir -p /www/wwwroot/phorizontal/enterprisesstph/uploads/inspecciones/fotos
mkdir -p /www/wwwroot/phorizontal/enterprisesstph/uploads/inspecciones/pdfs
chown -R www:www /www/wwwroot/phorizontal/enterprisesstph/uploads/inspecciones
chmod -R 775 /www/wwwroot/phorizontal/enterprisesstph/uploads/inspecciones
```

---

## 7. Resumen de la Estrategia

| Aspecto | Decision | Razon |
|---------|----------|-------|
| Canvas JS | Clase `SignatureCanvas` reutilizable (basada en firmar.php) | Mejor implementacion: DPR, crop, anti-accidental |
| Almacenamiento | PNG en disco (`uploads/inspecciones/firmas/`) | Eficiente, no infla la BD con LONGTEXT |
| BD | VARCHAR(255) con ruta al archivo | Consistente con contratos |
| PDF | DOMPDF con base64 inline (`<img src="data:...">`) | Consistente con SST docs |
| Flujo | Paso a paso (1 canvas a la vez), presencial | Ergonomico en celular, un firmante a la vez |
| Proteccion | 3 capas (multi-touch, pixeles, SweetAlert) | Probado y funcionando en produccion |
| Tokens | NO se usan | Firma presencial, no remota |
| Clase reutilizable | `SignatureCanvas` como clase JS | Se reutiliza para futuras inspecciones |
