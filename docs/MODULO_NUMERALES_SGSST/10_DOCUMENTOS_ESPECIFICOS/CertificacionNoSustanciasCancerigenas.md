# Certificacion de No Sustancias Cancerigenas / Toxicidad Aguda

## Metadata
- **tipo_documento:** `certificacion_no_sustancias_cancerigenas`
- **estandar:** 4.1.3 (carpeta de Identificacion de Sustancias Cancerigenas o con Toxicidad Aguda)
- **flujo:** **AUTO-GENERADO** (sin IA, sin editor de secciones — patron B, igual que `certificacion_no_alto_riesgo`)
- **categoria:** `certificaciones`
- **tipoCarpetaFases:** `identificacion_sustancias_cancerigenas` (carpeta existente)
- **codigo_plantilla:** `CERT-NSC`
- **clase:** No usa AbstractDocumentoSST. Es un controlador autonomo `PzcertificacionSustanciasCancerigenasController`.
- **firmantes:** Delegado SST (si esta configurado en contexto) + Representante Legal — igual que el de alto riesgo.

## Patron

Replica EXACTAMENTE el patron de `certificacion_no_alto_riesgo` (1.1.5):
- **NO** entra en `tbl_doc_tipo_configuracion` (no es flujo `secciones_ia`).
- **SOLO** entra en `tbl_doc_plantillas` con `codigo_sugerido='CERT-NSC'` y mapeo a `tbl_doc_plantilla_carpeta` con `codigo_carpeta='4.1.3'`.
- El controlador `crear()` toma datos del cliente + contexto SST e INSERTA un registro en `tbl_documentos_sst` con `estado='aprobado'` directamente (sin pasar por editor IA).
- Vista del documento: `app/Views/documentos_sst/certificacion_sustancias_cancerigenas.php` (clon del template del de alto riesgo).
- La carpeta 4.1.3 gana un dropdown con 2 opciones: "Procedimiento Identificacion (IA)" + "Certificacion No Sustancias Cancerigenas".

## URLs (cloning del 1.1.5)
- `POST /documentos-sst/{idCliente}/crear-certificacion-sustancias-cancerigenas`
- `GET  /documentos-sst/{idCliente}/certificacion-sustancias-cancerigenas/{anio}`
- `POST /documentos-sst/{idCliente}/regenerar-certificacion-sustancias-cancerigenas/{anio}`

## Texto base de la certificacion (4 secciones JSON)

| key | Contenido |
|---|---|
| `encabezado` | "[Ciudad], [DD] de [mes] de [YYYY]\nA quien corresponda:" |
| `certificacion` | "Por medio de la presente, [EMPRESA], identificada con NIT [NIT], CERTIFICA que en cumplimiento del numeral 4.1.3 - Identificacion de sustancias catalogadas como cancerigenas o con toxicidad aguda de la Resolucion 0312 de 2019 del Ministerio del Trabajo, del Decreto 1072 de 2015, de la Resolucion 2400 de 1979, del Decreto 1496 de 2018 (Sistema Globalmente Armonizado de Clasificacion y Etiquetado de Productos Quimicos - SGA/GHS) y demas normatividad aplicable, en sus procesos productivos, administrativos, operativos y de mantenimiento NO se manejan, almacenan, manipulan, transportan ni transforman sustancias clasificadas como cancerigenas (Grupos 1, 2A y 2B segun la Agencia Internacional de Investigacion sobre el Cancer - IARC) ni sustancias con toxicidad aguda segun los criterios del SGA adoptados por el Decreto 1496 de 2018." |
| `complemento` | "Esta certificacion se sustenta en la revision documental de la matriz de identificacion de peligros y valoracion de riesgos, en la verificacion de las Fichas de Datos de Seguridad (FDS) de los productos quimicos utilizados, en la inspeccion fisica de las areas de trabajo realizada por el Responsable del SG-SST y en la validacion con los responsables de los procesos sobre la naturaleza de las sustancias empleadas. En consecuencia, [EMPRESA] no requiere implementar el Programa de Vigilancia Epidemiologica (PVE) para sustancias cancerigenas ni los protocolos especificos definidos en la Resolucion 1013 de 2008 (GATISO Benceno) y demas guias del Ministerio del Trabajo." |
| `cierre` | "La presente certificacion tendra vigencia anual o hasta que se modifiquen los procesos productivos de la empresa, evento en el cual debera actualizarse. Se expide a solicitud de parte interesada y para los fines que estime convenientes." |

## Marco normativo citado
- Resolucion 0312/2019 (estandar 4.1.3)
- Decreto 1072/2015
- Resolucion 2400/1979
- Decreto 1496/2018 (SGA / GHS)
- Convenio 170 OIT
- IARC (clasificacion de cancerigenos)
- Resolucion 1013/2008 (mencionada en el complemento)

## Archivos creados / modificados

| Accion | Archivo |
|---|---|
| CREAR | `docs/MODULO_NUMERALES_SGSST/10_DOCUMENTOS_ESPECIFICOS/CertificacionNoSustanciasCancerigenas.md` |
| CREAR | `app/SQL/agregar_certificacion_no_sustancias_cancerigenas.php` |
| CREAR | `app/Controllers/PzcertificacionSustanciasCancerigenasController.php` |
| CREAR | `app/Views/documentos_sst/certificacion_sustancias_cancerigenas.php` |
| MODIFICAR | `app/Config/Routes.php` (3 rutas) |
| MODIFICAR | `app/Views/documentacion/_tipos/identificacion_sustancias_cancerigenas.php` (agregar dropdown dual) |
| MODIFICAR | `app/Controllers/DocumentacionController.php` (whereIn con 2 tipos) |
| MODIFICAR | `app/Views/documentacion/_components/acciones_documento.php` (mapaRutas + urlEditar) |
