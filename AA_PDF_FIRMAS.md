Estilos SECCIÓN FIRMAS PDF - Referencia Técnica
1. TIPOS DE FIRMAS DISPONIBLES
Lógica de Determinación (líneas 428-452)
Variable	Condición	Tipo de Firma
$esFirmaFisica	tipo_firma === 'fisica' o tipo_documento === 'responsabilidades_trabajadores_sgsst'	Tabla múltiples trabajadores
$soloFirmaConsultor	solo_firma_consultor o tipo_documento === 'responsabilidades_responsable_sgsst'	1 firmante: Consultor
$soloFirmaRepLegal	solo_firma_rep_legal	1 firmante: Rep. Legal
$firmasRepLegalYSegundo	Doc responsabilidades rep legal + segundo firmante	2 firmantes: Rep. Legal + Vigía/Delegado
$esSoloDosFirmantes	estandares <= 10 y no requiere delegado	2 firmantes: Consultor + Rep. Legal
Default	estandares > 10 o requiere delegado	3 firmantes: Consultor + Vigía/COPASST + Rep. Legal
2. TÍTULO DE LA SECCIÓN
Código (líneas 535-538)

<div style="margin-top: 25px;">
    <div style="background-color: #198754; color: white; padding: 8px 12px; font-weight: bold; font-size: 10pt;">
        FIRMAS DE APROBACIÓN
    </div>
Estilos del Título
Propiedad	Valor
margin-top (contenedor)	25px
background-color	#198754 (Verde Bootstrap)
color	white
padding	8px 12px
font-weight	bold
font-size	10pt
Variantes del Título
Condición	Texto
$soloFirmaConsultor o $soloFirmaRepLegal	FIRMA DE ACEPTACION
$firmasRepLegalYSegundo	FIRMAS DE ACEPTACION
Default	FIRMAS DE APROBACIÓN
3. TIPO A: SOLO FIRMA CONSULTOR (1 Firmante)
Código (líneas 540-566)

<table class="tabla-contenido" style="width: 100%; margin-top: 0;">
    <tr>
        <th style="width: 100%; background-color: #e9ecef; color: #333; text-align: center;">
            RESPONSABLE DEL SG-SST
        </th>
    </tr>
    <tr>
        <td style="vertical-align: top; padding: 15px; text-align: center;">
            <div style="margin-bottom: 5px;"><strong>Nombre:</strong> <?= $consultorNombre ?></div>
            <div style="margin-bottom: 5px;"><strong>Documento:</strong> <?= $consultorCedula ?></div>
            <div style="margin-bottom: 5px;"><strong>Licencia SST:</strong> <?= $consultorLicencia ?></div>
            <div style="margin-bottom: 5px;"><strong>Cargo:</strong> <?= $consultorCargo ?></div>
        </td>
    </tr>
    <tr>
        <td style="padding: 15px; text-align: center; vertical-align: bottom;">
            <?php if (!empty($firmaConsultorBase64)): ?>
                <img src="<?= $firmaConsultorBase64 ?>" alt="Firma" style="max-height: 60px; max-width: 180px;"><br>
            <?php endif; ?>
            <div style="border-top: 1px solid #333; width: 50%; margin: 5px auto 0; padding-top: 3px;">
                <small style="color: #666;">Firma</small>
            </div>
        </td>
    </tr>
</table>
Estructura Visual

┌─────────────────────────────────────────────────────────────────┐
│                    RESPONSABLE DEL SG-SST                       │
│              (#e9ecef, center, 100%)                            │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  Nombre: _______________                                        │
│  Documento: _______________                                     │
│  Licencia SST: _______________                                  │
│  Cargo: Consultor SST / Responsable del SG-SST                  │
│  (center, padding: 15px)                                        │
│                                                                 │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│                      [IMAGEN FIRMA]                             │
│                    ─────────────────                            │
│                         Firma                                   │
│               (línea width: 50%)                                │
└─────────────────────────────────────────────────────────────────┘
Imagen de Firma Digital

<img src="<?= $firmaConsultorBase64 ?>" alt="Firma" style="max-height: 60px; max-width: 180px;">
Propiedad	Valor
max-height	60px
max-width	180px
Formato	Base64 Data URI
4. TIPO B: SOLO FIRMA REP. LEGAL (1 Firmante)
Código (líneas 568-601)

<table class="tabla-contenido" style="width: 100%; margin-top: 0;">
    <tr>
        <th style="width: 100%; background-color: #e9ecef; color: #333;">
            Aprobó / Representante Legal
        </th>
    </tr>
    <tr>
        <td style="vertical-align: top; padding: 15px; height: 120px;">
            <div style="display: flex; justify-content: space-between;">
                <div style="width: 60%;">
                    <div style="margin-bottom: 8px;"><strong>Nombre:</strong> <?= $repLegalNombre ?></div>
                    <div style="margin-bottom: 8px;"><strong>Cargo:</strong> <?= $repLegalCargo ?></div>
                    <div style="margin-bottom: 8px;"><strong>Documento:</strong> <?= $repLegalCedula ?></div>
                </div>
                <div style="width: 35%; text-align: center; padding-top: 10px;">
                    <?php if ($firmaRepLegalPdf): ?>
                        <img src="<?= $firmaRepLegalPdf['evidencia']['firma_imagen'] ?>" style="max-height: 60px; max-width: 150px;">
                    <?php endif; ?>
                    <div style="border-top: 1px solid #333; width: 80%; margin: 5px auto 0; padding-top: 3px;">
                        <small style="color: #666;">Firma</small>
                    </div>
                </div>
            </div>
        </td>
    </tr>
</table>
Layout con Flexbox
Elemento	Width	Contenido
Datos izquierda	60%	Nombre, Cargo, Documento
Firma derecha	35%	Imagen + línea
5. TIPO C: REP. LEGAL + VIGÍA/DELEGADO (2 Firmantes)
Código (líneas 603-660)

<table class="tabla-contenido" style="width: 100%; margin-top: 0;">
    <tr>
        <th style="width: 50%; background-color: #e9ecef; color: #333;">REPRESENTANTE LEGAL</th>
        <th style="width: 50%; background-color: #e9ecef; color: #333;">VIGÍA SST / DELEGADO SST</th>
    </tr>
    <tr>
        <!-- REPRESENTANTE LEGAL -->
        <td style="vertical-align: top; padding: 12px; height: 100px;">
            <div style="margin-bottom: 5px;"><strong>Nombre:</strong> <?= $repLegalNombre ?></div>
            <div style="margin-bottom: 5px;"><strong>Documento:</strong> <?= $repLegalCedula ?></div>
            <div style="margin-bottom: 5px;"><strong>Cargo:</strong> <?= $repLegalCargo ?></div>
        </td>
        <!-- VIGIA/DELEGADO SST -->
        <td style="vertical-align: top; padding: 12px; height: 100px;">
            <div style="margin-bottom: 5px;"><strong>Nombre:</strong> <?= $segundoNombre ?></div>
            <div style="margin-bottom: 5px;"><strong>Documento:</strong> <?= $segundoCedula ?></div>
            <div style="margin-bottom: 5px;"><strong>Cargo:</strong> <?= $segundoRol ?></div>
        </td>
    </tr>
    <tr>
        <!-- Fila de firmas alineadas -->
        <td style="padding: 10px 12px; text-align: center; vertical-align: bottom;">
            <?php if ($firmaRepLegalPdfDoc): ?>
                <img src="<?= $firmaRepLegalPdfDoc['evidencia']['firma_imagen'] ?>" style="max-height: 56px; max-width: 168px;">
            <?php endif; ?>
            <div style="border-top: 1px solid #333; width: 80%; margin: 5px auto 0; padding-top: 3px;">
                <small style="color: #666;">Firma</small>
            </div>
        </td>
        <td style="padding: 10px 12px; text-align: center; vertical-align: bottom;">
            <?php if ($firmaSegundoPdf): ?>
                <img src="<?= $firmaSegundoPdf['evidencia']['firma_imagen'] ?>" style="max-height: 56px; max-width: 168px;">
            <?php endif; ?>
            <div style="border-top: 1px solid #333; width: 80%; margin: 5px auto 0; padding-top: 3px;">
                <small style="color: #666;">Firma</small>
            </div>
        </td>
    </tr>
</table>
Estructura Visual

┌────────────────────────────┬────────────────────────────┐
│    REPRESENTANTE LEGAL     │    VIGÍA SST / DELEGADO    │
│       (50%, #e9ecef)       │       (50%, #e9ecef)       │
├────────────────────────────┼────────────────────────────┤
│                            │                            │
│  Nombre: ___________       │  Nombre: ___________       │
│  Documento: ________       │  Documento: ________       │
│  Cargo: Rep. Legal         │  Cargo: Vigía SST          │
│  (padding: 12px)           │  (padding: 12px)           │
│  (height: 100px)           │  (height: 100px)           │
│                            │                            │
├────────────────────────────┼────────────────────────────┤
│                            │                            │
│     [IMAGEN FIRMA]         │     [IMAGEN FIRMA]         │
│     ─────────────────      │     ─────────────────      │
│          Firma             │          Firma             │
│     (línea width: 80%)     │     (línea width: 80%)     │
└────────────────────────────┴────────────────────────────┘
6. TIPO D: CONSULTOR + REP. LEGAL (2 Firmantes - 7 Estándares)
Código (líneas 662-706)

<table class="tabla-contenido" style="width: 100%; margin-top: 0;">
    <tr>
        <th style="width: 50%; background-color: #e9ecef; color: #333;">Elaboró / Consultor SST</th>
        <th style="width: 50%; background-color: #e9ecef; color: #333;">Aprobó / Representante Legal</th>
    </tr>
    <tr>
        <!-- CONSULTOR SST -->
        <td style="vertical-align: top; padding: 12px; height: 140px;">
            <div style="margin-bottom: 5px;"><strong>Nombre:</strong> <?= $consultorNombre ?></div>
            <div style="margin-bottom: 5px;"><strong>Cargo:</strong> <?= $consultorCargo ?></div>
            <div style="margin-bottom: 5px;"><strong>Licencia SST:</strong> <?= $consultorLicencia ?></div>
        </td>
        <!-- REPRESENTANTE LEGAL -->
        <td style="vertical-align: top; padding: 12px; height: 140px;">
            <div style="margin-bottom: 5px;"><strong>Nombre:</strong> <?= $repLegalNombre ?></div>
            <div style="margin-bottom: 5px;"><strong>Cargo:</strong> <?= $repLegalCargo ?></div>
        </td>
    </tr>
    <tr>
        <!-- Fila de firmas alineadas -->
        <td style="padding: 10px 12px; text-align: center; vertical-align: bottom;">
            <?php if (!empty($firmaConsultorBase64)): ?>
                <img src="<?= $firmaConsultorBase64 ?>" style="max-height: 56px; max-width: 168px;">
            <?php endif; ?>
            <div style="border-top: 1px solid #333; width: 80%; margin: 5px auto 0; padding-top: 3px;">
                <small style="color: #666;">Firma</small>
            </div>
        </td>
        <td style="padding: 10px 12px; text-align: center; vertical-align: bottom;">
            <?php if ($firmaRepLegalPdf2): ?>
                <img src="<?= $firmaRepLegalPdf2['evidencia']['firma_imagen'] ?>" style="max-height: 56px; max-width: 168px;">
            <?php endif; ?>
            <div style="border-top: 1px solid #333; width: 80%; margin: 5px auto 0; padding-top: 3px;">
                <small style="color: #666;">Firma</small>
            </div>
        </td>
    </tr>
</table>
7. TIPO E: 3 FIRMANTES (Estándar 21+ Estándares)
Código (líneas 708-786)

<table class="tabla-contenido" style="width: 100%; margin-top: 0;">
    <tr>
        <th style="width: 33.33%; background-color: #e9ecef; color: #333;">Elaboró</th>
        <th style="width: 33.33%; background-color: #e9ecef; color: #333;">Revisó / Vigía SST</th>
        <th style="width: 33.33%; background-color: #e9ecef; color: #333;">Aprobó</th>
    </tr>
    <tr>
        <!-- CONSULTOR SST / ELABORO -->
        <td style="vertical-align: top; padding: 10px; height: 70px;">
            <div style="margin-bottom: 4px; font-size: 9pt;"><strong>Nombre:</strong> <?= $consultorNombre ?></div>
            <div style="margin-bottom: 4px; font-size: 9pt;"><strong>Cargo:</strong> <?= $consultorCargo ?></div>
        </td>
        <!-- DELEGADO SST o COPASST/VIGIA / REVISO -->
        <td style="vertical-align: top; padding: 10px; height: 70px;">
            <div style="margin-bottom: 4px; font-size: 9pt;"><strong>Nombre:</strong> <?= $delegadoNombre ?></div>
            <div style="margin-bottom: 4px; font-size: 9pt;"><strong>Cargo:</strong> Vigía SST / COPASST</div>
        </td>
        <!-- REPRESENTANTE LEGAL / APROBO -->
        <td style="vertical-align: top; padding: 10px; height: 70px;">
            <div style="margin-bottom: 4px; font-size: 9pt;"><strong>Nombre:</strong> <?= $repLegalNombre ?></div>
            <div style="margin-bottom: 4px; font-size: 9pt;"><strong>Cargo:</strong> <?= $repLegalCargo ?></div>
        </td>
    </tr>
    <tr>
        <!-- Fila de firmas alineadas -->
        <td style="padding: 8px 10px; text-align: center; vertical-align: bottom;">
            <?php if (!empty($firmaConsultorBase64)): ?>
                <img src="<?= $firmaConsultorBase64 ?>" style="max-height: 49px; max-width: 140px;">
            <?php endif; ?>
            <div style="border-top: 1px solid #333; width: 85%; margin: 5px auto 0; padding-top: 3px;">
                <small style="color: #666; font-size: 7pt;">Firma</small>
            </div>
        </td>
        <td style="padding: 8px 10px; text-align: center; vertical-align: bottom;">
            <?php if ($firmaDelegadoPdf): ?>
                <img src="<?= $firmaDelegadoPdf['evidencia']['firma_imagen'] ?>" style="max-height: 49px; max-width: 140px;">
            <?php endif; ?>
            <div style="border-top: 1px solid #333; width: 85%; margin: 5px auto 0; padding-top: 3px;">
                <small style="color: #666; font-size: 7pt;">Firma</small>
            </div>
        </td>
        <td style="padding: 8px 10px; text-align: center; vertical-align: bottom;">
            <?php if ($firmaRepLegalPdf3): ?>
                <img src="<?= $firmaRepLegalPdf3['evidencia']['firma_imagen'] ?>" style="max-height: 49px; max-width: 140px;">
            <?php endif; ?>
            <div style="border-top: 1px solid #333; width: 85%; margin: 5px auto 0; padding-top: 3px;">
                <small style="color: #666; font-size: 7pt;">Firma</small>
            </div>
        </td>
    </tr>
</table>
Estructura Visual

┌──────────────────┬───────────────────┬──────────────────┐
│     Elaboró      │      Revisó       │     Aprobó       │
│     (33.33%)     │     (33.33%)      │     (33.33%)     │
│     #e9ecef      │      #e9ecef      │     #e9ecef      │
├──────────────────┼───────────────────┼──────────────────┤
│                  │                   │                  │
│  Nombre: ___     │  Nombre: ___      │  Nombre: ___     │
│  Cargo:          │  Cargo:           │  Cargo:          │
│  Consultor SST   │  Vigía/COPASST    │  Rep. Legal      │
│  (font-size:9pt) │  (font-size:9pt)  │  (font-size:9pt) │
│  (height: 70px)  │  (height: 70px)   │  (height: 70px)  │
│                  │                   │                  │
├──────────────────┼───────────────────┼──────────────────┤
│                  │                   │                  │
│  [IMAGEN FIRMA]  │  [IMAGEN FIRMA]   │  [IMAGEN FIRMA]  │
│  ─────────────   │  ─────────────    │  ─────────────   │
│     Firma        │     Firma         │     Firma        │
│  (width: 85%)    │  (width: 85%)     │  (width: 85%)    │
│  (7pt)           │  (7pt)            │  (7pt)           │
└──────────────────┴───────────────────┴──────────────────┘
8. TIPO F: FIRMA FÍSICA (Trabajadores)
Código (líneas 469-531)

<!-- Salto de página -->
<div style="page-break-before: always;"></div>

<!-- Encabezado repetido -->
<table class="encabezado-formal"><!-- Mismo encabezado --></table>

<!-- Instrucciones -->
<div style="background: #e7f3ff; padding: 10px; margin-bottom: 15px; border-left: 3px solid #0d6efd; font-size: 9pt;">
    <strong>Instrucciones:</strong> Con mi firma certifico haber leido, entendido y aceptado...
</div>

<!-- Tabla de firmas -->
<table class="tabla-contenido" style="width: 100%; border-collapse: collapse; font-size: 8pt;">
    <tr>
        <th style="width: 30px; background-color: #f8f9fa; border: 1px solid #333; padding: 5px; text-align: center;">No.</th>
        <th style="width: 70px; background-color: #f8f9fa; border: 1px solid #333; padding: 5px; text-align: center;">Fecha</th>
        <th style="background-color: #f8f9fa; border: 1px solid #333; padding: 5px; text-align: center;">Nombre Completo</th>
        <th style="width: 80px; background-color: #f8f9fa; border: 1px solid #333; padding: 5px; text-align: center;">Cedula</th>
        <th style="width: 100px; background-color: #f8f9fa; border: 1px solid #333; padding: 5px; text-align: center;">Cargo / Area</th>
        <th style="width: 90px; background-color: #f8f9fa; border: 1px solid #333; padding: 5px; text-align: center;">Firma</th>
    </tr>
    <?php for ($i = 1; $i <= $filasFirma; $i++): ?>
    <tr>
        <td style="border: 1px solid #333; padding: 8px 5px; text-align: center; height: 25px;"><?= $i ?></td>
        <td style="border: 1px solid #333; padding: 8px 5px;"></td>
        <td style="border: 1px solid #333; padding: 8px 5px;"></td>
        <td style="border: 1px solid #333; padding: 8px 5px;"></td>
        <td style="border: 1px solid #333; padding: 8px 5px;"></td>
        <td style="border: 1px solid #333; padding: 8px 5px;"></td>
    </tr>
    <?php endfor; ?>
</table>
Columnas Tabla Firma Física
#	Columna	Ancho	Alineación TH	Height Fila
1	No.	30px	center	25px
2	Fecha	70px	center	25px
3	Nombre Completo	Flexible	center	25px
4	Cédula	80px	center	25px
5	Cargo / Área	100px	center	25px
6	Firma	90px	center	25px
Caja de Instrucciones

<div style="background: #e7f3ff; padding: 10px; margin-bottom: 15px; border-left: 3px solid #0d6efd; font-size: 9pt;">
Propiedad	Valor
background	#e7f3ff
padding	10px
margin-bottom	15px
border-left	3px solid #0d6efd
font-size	9pt
9. ESTILOS COMUNES DE TABLAS DE FIRMA
Tabla Principal

<table class="tabla-contenido" style="width: 100%; margin-top: 0;">
Propiedad	Valor
width	100%
margin-top	0
border-collapse	collapse (heredado)
Encabezados (TH)
Propiedad	Valor
background-color	#e9ecef
color	#333
border	1px solid #999 (heredado)
padding	5px 8px (heredado)
Celdas de Datos
Tipo	Height	Padding	Font-size
1 Firmante	-	15px	heredado
2 Firmantes	100px - 140px	12px	heredado
3 Firmantes	70px	10px	9pt
Celdas de Firma con Imagen

<td style="padding: 10px 12px; text-align: center; vertical-align: bottom;">
    <img src="<?= $firmaBase64 ?>" style="max-height: 56px; max-width: 168px;">
    <div style="border-top: 1px solid #333; width: 80%; margin: 5px auto 0; padding-top: 3px;">
        <small style="color: #666;">Firma</small>
    </div>
</td>
10. DIMENSIONES DE IMÁGENES DE FIRMA
Tipo	max-height	max-width
1 Firmante (Consultor)	60px	180px
1 Firmante (Rep Legal)	60px	150px
2 Firmantes	56px	168px
3 Firmantes	49px	140px
11. DIMENSIONES SEGÚN TIPO
Tipo	Columnas	Ancho Col	Height Datos	Width Línea	Font Firma
1 Firmante	1	100%	-	50%	small
2 Firmantes	2	50%	100-140px	80%	small
3 Firmantes	3	33.33%	70px	85%	7pt
Firma Física	6	Variable	25px	N/A	8pt
12. PALETA DE COLORES FIRMAS PDF
Elemento	Color	Hex
Título barra fondo	Verde Bootstrap	#198754
Título barra texto	Blanco	white
TH fondo	Gris claro	#e9ecef
TH texto	Gris oscuro	#333
Bordes tabla	Gris medio	#999
Línea firma	Negro	#333
Texto "Firma"	Gris	#666
Instrucciones fondo	Azul claro	#e7f3ff
Instrucciones borde	Azul	#0d6efd
TH firma física fondo	Gris muy claro	#f8f9fa
Bordes firma física	Negro	#333
13. TIPOGRAFÍA FIRMAS PDF
Elemento	Fuente	Tamaño
Título barra	DejaVu Sans	10pt bold
TH tabla	DejaVu Sans	heredado (9pt)
TD datos 3 firmantes	DejaVu Sans	9pt
Texto "Firma" 3 firmantes	DejaVu Sans	7pt
Texto "Firma" otros	DejaVu Sans	small
Tabla firma física	DejaVu Sans	8pt
Instrucciones	DejaVu Sans	9pt
14. COMPARACIÓN PDF vs WORD
Aspecto	PDF	Word
Título padding	8px 12px	5px 8px
Título font-size	10pt	heredado (11pt)
margin-top contenedor	25px	20px
Imágenes firma	Sí (Base64)	No
Firma electrónica	tbl_doc_firma_evidencias	No
TH padding	5px 8px (heredado)	4px
TD padding 3 firmantes	10px	5px
Height datos 3 firmantes	70px	no definido
Width línea 3 firmantes	85%	65%
Salto página firma física	page-break-before: always	<br clear="all" style="page-break-before:always">
15. SNIPPET REUTILIZABLE - 3 FIRMANTES PDF

<!-- FIRMAS DE APROBACIÓN - PDF 3 FIRMANTES -->
<div style="margin-top: 25px;">
    <div style="background-color: #198754; color: white; padding: 8px 12px; font-weight: bold; font-size: 10pt;">
        FIRMAS DE APROBACIÓN
    </div>
    <table class="tabla-contenido" style="width: 100%; margin-top: 0;">
        <tr>
            <th style="width: 33.33%; background-color: #e9ecef; color: #333;">Elaboró</th>
            <th style="width: 33.33%; background-color: #e9ecef; color: #333;">Revisó / Vigía SST</th>
            <th style="width: 33.33%; background-color: #e9ecef; color: #333;">Aprobó</th>
        </tr>
        <tr>
            <td style="vertical-align: top; padding: 10px; height: 70px;">
                <div style="margin-bottom: 4px; font-size: 9pt;"><strong>Nombre:</strong> ________________</div>
                <div style="margin-bottom: 4px; font-size: 9pt;"><strong>Cargo:</strong> Consultor SST</div>
            </td>
            <td style="vertical-align: top; padding: 10px; height: 70px;">
                <div style="margin-bottom: 4px; font-size: 9pt;"><strong>Nombre:</strong> ________________</div>
                <div style="margin-bottom: 4px; font-size: 9pt;"><strong>Cargo:</strong> Vigía SST</div>
            </td>
            <td style="vertical-align: top; padding: 10px; height: 70px;">
                <div style="margin-bottom: 4px; font-size: 9pt;"><strong>Nombre:</strong> ________________</div>
                <div style="margin-bottom: 4px; font-size: 9pt;"><strong>Cargo:</strong> Representante Legal</div>
            </td>
        </tr>
        <tr>
            <td style="padding: 8px 10px; text-align: center; vertical-align: bottom;">
                <?php if (!empty($firmaConsultorBase64)): ?>
                    <img src="<?= $firmaConsultorBase64 ?>" style="max-height: 49px; max-width: 140px;"><br>
                <?php endif; ?>
                <div style="border-top: 1px solid #333; width: 85%; margin: 5px auto 0; padding-top: 3px;">
                    <small style="color: #666; font-size: 7pt;">Firma</small>
                </div>
            </td>
            <td style="padding: 8px 10px; text-align: center; vertical-align: bottom;">
                <div style="border-top: 1px solid #333; width: 85%; margin: 5px auto 0; padding-top: 3px;">
                    <small style="color: #666; font-size: 7pt;">Firma</small>
                </div>
            </td>
            <td style="padding: 8px 10px; text-align: center; vertical-align: bottom;">
                <div style="border-top: 1px solid #333; width: 85%; margin: 5px auto 0; padding-top: 3px;">
                    <small style="color: #666; font-size: 7pt;">Firma</small>
                </div>
            </td>
        </tr>
    </table>
</div>
Comando para Replicar
"Usa la sección FIRMAS PDF estándar: título barra #198754 white padding 8px 12px font 10pt bold, margin-top 25px, TH #e9ecef #333 33.33%, TD height 70px padding 10px font 9pt, fila firma vertical-align bottom, imagen firma max 49x140px, línea firma border-top #333 width 85%, texto Firma #666 7pt"