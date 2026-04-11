# Continuacion Chat - Modulo Investigacion Accidentes e Incidentes COPASST

> Fecha: 2026-04-11 | Estado: EN PROGRESO

## Lo que ya se hizo en este chat

### 1. Investigacion normativa profunda
- Resolucion 1401 de 2007: investigacion obligatoria de AMBOS (accidentes e incidentes)
- Decreto 1072 de 2015 Art. 2.2.4.6.32: ratifica investigacion de ambos
- Resolucion 156 de 2005: FURAT solo aplica a accidentes (no incidentes)
- Diferencias clave: incidentes NO tienen lesionado, NO tienen FURAT, NO reportan a ARL
- Decision: formulario UNIFICADO con secciones condicionales por tipo_evento, mismo PDF con contenido condicional

### 2. Base de datos (LOCAL + PROD OK)
- Script: `app/SQL/crear_modulo_investigacion_accidente.php`
- `detail_report` id=38: "Investigacion de Accidentes e Incidentes de Trabajo"
- `tbl_investigacion_accidente` (tabla principal con campos condicionales)
- `tbl_investigacion_testigos` (testigos con declaracion)
- `tbl_investigacion_evidencia` (fotos con descripcion)
- `tbl_investigacion_medidas` (plan accion: fuente/medio/trabajador)
- id_report_type=7 (Analisis de Accidentes de Trabajo), id_detailreport=38

### 3. Modelos creados
- `app/Models/InvestigacionAccidenteModel.php`
- `app/Models/InvestigacionTestigoModel.php`
- `app/Models/InvestigacionEvidenciaModel.php`
- `app/Models/InvestigacionMedidaModel.php`

### 4. Controllers creados
- `app/Controllers/Inspecciones/InvestigacionAccidenteController.php` (consultor)
  - CRUD completo + firmas canvas + WhatsApp token + PDF + reportlist + email
  - Patron: ActaVisitaController (firmas) + InspeccionLocativaController (CRUD)
- `app/Controllers/MiembroInvestigacionAccidenteController.php` (miembro COPASST)
  - CRUD + PDF + reportlist + notificarConsultor por SendGrid
  - Patron: MiembroInspeccionController

### 5. Vistas consultor creadas
- `app/Views/inspecciones/investigacion_accidente/list.php`
- `app/Views/inspecciones/investigacion_accidente/form.php` (9 secciones accordion)
- `app/Views/inspecciones/investigacion_accidente/view.php`
- `app/Views/inspecciones/investigacion_accidente/firma.php` (canvas + WhatsApp)
- `app/Views/inspecciones/investigacion_accidente/firma_remota.php` (publica)
- `app/Views/inspecciones/investigacion_accidente/pdf.php` (DOMPDF condicional)

### 6. Vistas miembro creadas
- `app/Views/inspecciones/miembro/investigacion_list.php`
- `app/Views/inspecciones/miembro/investigacion_form.php`
- `app/Views/inspecciones/miembro/investigacion_view.php`

### 7. Rutas agregadas en Routes.php
- Publicas: firma remota investigacion (sin auth)
- Grupo miembro: CRUD + finalizar + PDF
- Grupo inspecciones (consultor): CRUD + firmas + token + PDF + email + regenerar

## Que falta por hacer

1. **Testing funcional**: abrir en navegador, probar flujos
2. **Agregar enlaces** en dashboards/menus del consultor y miembro para navegar al modulo
3. **Verificar** que el PDF se genera correctamente con DOMPDF
4. **Deploy**: git add . → commit → checkout main → merge cycloid → push → checkout cycloid

## Reglas del proyecto

- NO hardcodear textos autopromocionales en PDFs
- Solo boton galeria, NO boton camara en formularios
- Textos genericos (organizacion, instalaciones), no propiedad horizontal
- EnterpriseSST en vez de Cycloid Talent en todo
- BD: scripts PHP CLI, primero LOCAL, luego --prod
- Deploy: git add . → commit → checkout main → merge cycloid → push → checkout cycloid
