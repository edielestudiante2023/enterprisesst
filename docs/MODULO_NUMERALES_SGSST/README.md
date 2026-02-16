# Documentación del Módulo de Numerales SG-SST

Este directorio contiene toda la documentación técnica para el sistema de generación de documentos SST según los numerales de la Resolución 0312/2019.

---

## Estructura de Carpetas

### [01_ARQUITECTURA_GENERAL/](01_ARQUITECTURA_GENERAL/)
Reglas del proyecto, arquitectura escalable, mapeo de numerales y árboles de decisión.

| Archivo | Descripción |
|---------|-------------|
| [0_REGLAS_PROYECTO.md](01_ARQUITECTURA_GENERAL/0_REGLAS_PROYECTO.md) | Reglas críticas, patrones y lecciones aprendidas |
| [ARQUITECTURA_DOCUMENTOS_SST_V2.md](01_ARQUITECTURA_GENERAL/ARQUITECTURA_DOCUMENTOS_SST_V2.md) | Arquitectura escalable para 100+ tipos de documentos |
| [NUMERALES_CON_PROGRAMAS.md](01_ARQUITECTURA_GENERAL/NUMERALES_CON_PROGRAMAS.md) | Mapeo de numerales que requieren programas |
| [ZZ_93_ARBOLES_DECISION.md](01_ARQUITECTURA_GENERAL/ZZ_93_ARBOLES_DECISION.md) | Árboles de decisión: rutas de generación |

### [02_GENERACION_IA/](02_GENERACION_IA/)
Todo sobre generación con IA: arquitectura, prompts, troubleshooting y marco normativo.

| Archivo | Descripción |
|---------|-------------|
| [ARQUITECTURA_GENERACION_IA_DOCUMENTOS.md](02_GENERACION_IA/ARQUITECTURA_GENERACION_IA_DOCUMENTOS.md) | Arquitectura para flujo `programa_con_pta` |
| [SISTEMA_PROMPTS_IA.md](02_GENERACION_IA/SISTEMA_PROMPTS_IA.md) | Cómo se alimentan y ejecutan prompts IA |
| [ESTADO_36_MODULOS_GENERADORES_IA.md](02_GENERACION_IA/ESTADO_36_MODULOS_GENERADORES_IA.md) | Estado de los 36 módulos generadores |
| [INSUMOS_IA_PREGENERACION.md](02_GENERACION_IA/INSUMOS_IA_PREGENERACION.md) | Marco normativo como insumo previo a la IA |
| [README_MARCO_NORMATIVO.md](02_GENERACION_IA/README_MARCO_NORMATIVO.md) | Índice del módulo marco normativo |
| [INTEGRACION_MARCO_NORMATIVO_SWEETALERT.md](02_GENERACION_IA/INTEGRACION_MARCO_NORMATIVO_SWEETALERT.md) | Marco normativo integrado en SweetAlert |
| [VALORES_HARDCODEADOS_MARCO_NORMATIVO.md](02_GENERACION_IA/VALORES_HARDCODEADOS_MARCO_NORMATIVO.md) | Valores hardcodeados del marco normativo |
| [1_A_TROUBLESHOOTING_GENERACION_IA.md](02_GENERACION_IA/1_A_TROUBLESHOOTING_GENERACION_IA.md) | Problemas comunes al generar con IA |
| [1_A_REPARAR_IA_TIPO_A_UNA_PARTE.md](02_GENERACION_IA/1_A_REPARAR_IA_TIPO_A_UNA_PARTE.md) | Guía reparación docs Tipo A (1 parte) |
| [TROUBLESHOOTING_CODIGOS_DOCUMENTOS.md](02_GENERACION_IA/TROUBLESHOOTING_CODIGOS_DOCUMENTOS.md) | Fix para códigos DOC-GEN-001 incorrectos |
| [TROUBLESHOOTING_MARCO_LEGAL_IA.md](02_GENERACION_IA/TROUBLESHOOTING_MARCO_LEGAL_IA.md) | Fix marco legal con normas faltantes |
| [ZZ_11_PROMPTNUEVO.md](02_GENERACION_IA/ZZ_11_PROMPTNUEVO.md) | Prompt para iniciar nuevo módulo |
| [ZZ_22_PROMPTREPARACIONES.md](02_GENERACION_IA/ZZ_22_PROMPTREPARACIONES.md) | Prompt para reparar módulos existentes |
| [ZZ_33_PROMPT_REPARACION_3PARTES.md](02_GENERACION_IA/ZZ_33_PROMPT_REPARACION_3PARTES.md) | Prompt para reparar módulos 3 partes |

### [03_MODULO_3_PARTES/](03_MODULO_3_PARTES/)
Instructivos del módulo de 3 partes (Tipo B): PTA, Indicadores, Documento IA.

| Archivo | Descripción |
|---------|-------------|
| [AA_3_PARTES_PROGRAMA.md](03_MODULO_3_PARTES/AA_3_PARTES_PROGRAMA.md) | Arquitectura del módulo de 3 partes |
| [ZZ_77_PREPARACION.md](03_MODULO_3_PARTES/ZZ_77_PREPARACION.md) | Requisitos UI obligatorios antes de crear módulo |
| [ZZ_80_PARTE1.md](03_MODULO_3_PARTES/ZZ_80_PARTE1.md) | Parte 1: Generador de actividades PTA |
| [ZZ_81_PARTE2.md](03_MODULO_3_PARTES/ZZ_81_PARTE2.md) | Parte 2: Generador de indicadores |
| [ZZ_95_PARTE3.md](03_MODULO_3_PARTES/ZZ_95_PARTE3.md) | Parte 3: Generación del documento formal |
| [ZZ_96_PARTE4.md](03_MODULO_3_PARTES/ZZ_96_PARTE4.md) | Crear un nuevo tipo de documento SST |
| [ZZ_97_PARTE5.md](03_MODULO_3_PARTES/ZZ_97_PARTE5.md) | UX/UI y experiencia de usuario |
| [ZZ_98_COMO_AGREGAR_PROGRAMA.md](03_MODULO_3_PARTES/ZZ_98_COMO_AGREGAR_PROGRAMA.md) | Cómo agregar un programa Type B |
| [ZZ_90_PARTESWEETALERT.md](03_MODULO_3_PARTES/ZZ_90_PARTESWEETALERT.md) | SweetAlert de verificación de datos |
| [ZZ_91_MENSAJESTOAST.md](03_MODULO_3_PARTES/ZZ_91_MENSAJESTOAST.md) | Sistema de mensajes Toast |

### [04_FIRMAS_ELECTRONICAS/](04_FIRMAS_ELECTRONICAS/)
Sistema de firmas, delegado SST, dashboard y vista previa.

| Archivo | Descripción |
|---------|-------------|
| [1_A_SISTEMA_FIRMAS_DOCUMENTOS.md](04_FIRMAS_ELECTRONICAS/1_A_SISTEMA_FIRMAS_DOCUMENTOS.md) | Sistema completo de firmas electrónicas |
| [1_A_IMPACTO_DELEGADO_SST.md](04_FIRMAS_ELECTRONICAS/1_A_IMPACTO_DELEGADO_SST.md) | Impacto de activar Delegado SST |
| [1_A_DASHBOARD_FIRMAS.md](04_FIRMAS_ELECTRONICAS/1_A_DASHBOARD_FIRMAS.md) | Dashboard centralizado de firmas |
| [1_A_VISTA_PREVIA_FIRMA.md](04_FIRMAS_ELECTRONICAS/1_A_VISTA_PREVIA_FIRMA.md) | Vista previa en página de firma |
| [1_A_VISTA_PREVIA_FIRMA_FUTURO.md](04_FIRMAS_ELECTRONICAS/1_A_VISTA_PREVIA_FIRMA_FUTURO.md) | Mejoras futuras: PDF embebido, etc. |

### [05_VERSIONAMIENTO/](05_VERSIONAMIENTO/)
Sistema de versiones, historial y estandarización.

| Archivo | Descripción |
|---------|-------------|
| [1_AA_VERSIONAMIENTO.md](05_VERSIONAMIENTO/1_AA_VERSIONAMIENTO.md) | Guía completa del sistema de versionamiento |
| [SISTEMA_VERSIONES_ESTANDARIZADO.md](05_VERSIONAMIENTO/SISTEMA_VERSIONES_ESTANDARIZADO.md) | Servicio centralizado de versiones |
| [ZZ_98_HISTORIAL_VERSIONES.md](05_VERSIONAMIENTO/ZZ_98_HISTORIAL_VERSIONES.md) | Ciclo de vida, snapshot, máquina de estados |

### [06_ESTILOS_PLANTILLAS/](06_ESTILOS_PLANTILLAS/)
Estilos y estructura de vistas Web, PDF y Word.

| Archivo | Descripción |
|---------|-------------|
| [2_AA_ WEB.md](06_ESTILOS_PLANTILLAS/2_AA_%20WEB.md) | Estilos vista web: estructura, Bootstrap |
| [3_AA_PDF_ENCABEZADO.md](06_ESTILOS_PLANTILLAS/3_AA_PDF_ENCABEZADO.md) | PDF: encabezado formal |
| [3_AA_PDF_CUERPO_DOCUMENTO.md](06_ESTILOS_PLANTILLAS/3_AA_PDF_CUERPO_DOCUMENTO.md) | PDF: cuerpo del documento |
| [3_AA_PDF_CONTROL_CAMBIOS.md](06_ESTILOS_PLANTILLAS/3_AA_PDF_CONTROL_CAMBIOS.md) | PDF: tabla control de cambios |
| [3_AA_PDF_FIRMAS.md](06_ESTILOS_PLANTILLAS/3_AA_PDF_FIRMAS.md) | PDF: sección de firmas |
| [4_AA_WORD_ENCABEZADO.md](06_ESTILOS_PLANTILLAS/4_AA_WORD_ENCABEZADO.md) | Word: encabezado con estilos MSO |
| [4_AA_WORD_CUERPO_DOCUMENTO.md](06_ESTILOS_PLANTILLAS/4_AA_WORD_CUERPO_DOCUMENTO.md) | Word: contenido |
| [4_AA_WORD_CONTROL_CAMBIOS.md](06_ESTILOS_PLANTILLAS/4_AA_WORD_CONTROL_CAMBIOS.md) | Word: tabla control de cambios |
| [4_AA_WORD_FIRMAS.md](06_ESTILOS_PLANTILLAS/4_AA_WORD_FIRMAS.md) | Word: firmas formato imprimible |

### [07_COMPONENTES_UI/](07_COMPONENTES_UI/)
Componentes reutilizables de interfaz: toolbar, tablas, ReportList, editor.

| Archivo | Descripción |
|---------|-------------|
| [ZZ_97_TOOLBAR_DOCUMENTOS.md](07_COMPONENTES_UI/ZZ_97_TOOLBAR_DOCUMENTOS.md) | Estándar toolbar de documentos SST |
| [5_AA_PDF_ENLACE_Y_TABLA.md](07_COMPONENTES_UI/5_AA_PDF_ENLACE_Y_TABLA.md) | Adjuntar archivo/enlace + tabla |
| [6_AA_COMPONENTE_TABLA_DOCUMENTOS_SST.md](07_COMPONENTES_UI/6_AA_COMPONENTE_TABLA_DOCUMENTOS_SST.md) | Componente tabla documentos SST |
| [AA_7_EDITOR_CONFIGURADO.md](07_COMPONENTES_UI/AA_7_EDITOR_CONFIGURADO.md) | Configuración de acciones de documento |
| [AA_REPORTLIST_CARPETA.md](07_COMPONENTES_UI/AA_REPORTLIST_CARPETA.md) | ReportList para carpetas SST |
| [AA_REPORTLIST_CARPETA_CON_FASES.md](07_COMPONENTES_UI/AA_REPORTLIST_CARPETA_CON_FASES.md) | ReportList para carpetas con fases |
| [DASHBOARD_DOCUMENTOS_SST.md](07_COMPONENTES_UI/DASHBOARD_DOCUMENTOS_SST.md) | Dashboard gestión documentos por cliente |

### [08_COMITES_SST/](08_COMITES_SST/)
Conformación, miembros, recomposición y elecciones de comités.

| Archivo | Descripción |
|---------|-------------|
| [SISTEMA_COMITES_ELECCIONES.md](08_COMITES_SST/SISTEMA_COMITES_ELECCIONES.md) | Sistema de conformación de comités |
| [ESTRUCTURA_MIEMBROS_COMITE.md](08_COMITES_SST/ESTRUCTURA_MIEMBROS_COMITE.md) | Estructura de almacenamiento de miembros |
| [MODULO_RECOMPOSICION_COMITES.md](08_COMITES_SST/MODULO_RECOMPOSICION_COMITES.md) | Reemplazo de miembros durante vigencia |
| [ZZ_99_INTEGRACION_COMITES_VERSIONAMIENTO.md](08_COMITES_SST/ZZ_99_INTEGRACION_COMITES_VERSIONAMIENTO.md) | Integración comités con versionamiento |

### [09_INDICADORES/](09_INDICADORES/)
Módulo de indicadores, dashboard jerárquico y fichas técnicas.

| Archivo | Descripción |
|---------|-------------|
| [ACCESO_MODULO_INDICADORES.md](09_INDICADORES/ACCESO_MODULO_INDICADORES.md) | Puntos de entrada al módulo indicadores |
| [ZZ_94_DASHBOARD_INDICADORES.md](09_INDICADORES/ZZ_94_DASHBOARD_INDICADORES.md) | Dashboard jerárquico de indicadores |
| [ZZ_99_FICHAS_TECNICAS_INDICADORES.md](09_INDICADORES/ZZ_99_FICHAS_TECNICAS_INDICADORES.md) | Fichas técnicas y matriz de objetivos |

### [10_DOCUMENTOS_ESPECIFICOS/](10_DOCUMENTOS_ESPECIFICOS/)
Documentación de módulos y documentos individuales.

| Archivo | Descripción |
|---------|-------------|
| [POLITICA_DESCONEXION_LABORAL.md](10_DOCUMENTOS_ESPECIFICOS/POLITICA_DESCONEXION_LABORAL.md) | Política de desconexión laboral (Ley 2191/2022) |
| [ACCESO_MODULO_ACTAS.md](10_DOCUMENTOS_ESPECIFICOS/ACCESO_MODULO_ACTAS.md) | Acceso al módulo de actas desde dashboards |
| [INSTRUCTIVO_DUPLICACION_MODULOS.md](10_DOCUMENTOS_ESPECIFICOS/INSTRUCTIVO_DUPLICACION_MODULOS.md) | Cómo duplicar módulos de numerales |

---

## Arquitectura del Sistema

```
┌─────────────────────────────────────────────────────────────────────┐
│                    FLUJO DE DOCUMENTOS SST                          │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│  1. CONFIGURACIÓN (Base de Datos)                                  │
│     ├── tbl_doc_tipo_configuracion    → Tipos de documento         │
│     ├── tbl_doc_secciones_config      → Secciones + prompts IA     │
│     ├── tbl_doc_firmantes_config      → Firmantes por tipo         │
│     └── tbl_doc_plantilla_carpeta     → Mapeo a carpetas           │
│                                                                     │
│  2. GENERACIÓN (Controlador + Servicios)                           │
│     ├── DocumentosSSTController       → Flujo principal            │
│     ├── DocumentoConfigService        → Lee config de BD           │
│     ├── FirmanteService               → Obtiene firmantes          │
│     └── IADocumentacionService        → Genera con OpenAI          │
│                                                                     │
│  3. VISTAS (Renderizado)                                           │
│     ├── generar_con_ia.php            → Editor de secciones        │
│     ├── documento_generico.php        → Vista previa WEB           │
│     ├── pdf_template_generico.php     → Exportación PDF            │
│     └── word_template_generico.php    → Exportación Word           │
│                                                                     │
│  4. FIRMAS (Aprobación)                                            │
│     ├── FirmaElectronicaController    → Solicitud de firmas        │
│     ├── tbl_doc_firma_solicitudes     → Registro de solicitudes    │
│     └── tbl_doc_firma_evidencias      → Evidencias firmadas        │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
```

---

## Comparación de Formatos

### Dimensiones Clave

| Elemento | WEB | PDF | WORD |
|----------|-----|-----|------|
| Ancho logo | 150px | 120px | 80px |
| Ancho info | 170px | 140px | 120px |
| Font títulos | 1.1rem | 11pt | 11pt |
| Line-height | 1.7 | 1.2 | 1.0 |
| Gradientes | Sí | No | No |
| Border-radius | Sí | No | No |
| Iconos | Bootstrap Icons | No | No |

### Paleta de Colores

| Uso | Color | Hex |
|-----|-------|-----|
| Títulos sección | Azul | #0d6efd |
| Control cambios | Azul → Morado | #0d6efd → #6610f2 |
| Firmas | Verde | #198754 → #20c997 |
| Texto principal | Negro | #333 |
| Bordes | Negro | #333 |

---

## Referencias Externas

- [PROMPT_NUEVO_DOCUMENTO_SST.md](../../PROMPT_NUEVO_DOCUMENTO_SST.md) - Guía completa para crear documentos
- [ARQUITECTURA_DOCUMENTOS_SST.md](../ARQUITECTURA_DOCUMENTOS_SST.md) - Arquitectura general del sistema
- [GESTION_DOCUMENTOS_SST.md](../GESTION_DOCUMENTOS_SST.md) - Gestión y versionamiento

---

**Última actualización:** Febrero 2026
