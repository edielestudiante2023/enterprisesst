# Documentación del Módulo de Numerales SG-SST

Este directorio contiene toda la documentación técnica para el sistema de generación de documentos SST según los numerales de la Resolución 0312/2019.

---

## Índice de Documentación

### 1. Guías de Desarrollo

| Archivo | Descripción |
|---------|-------------|
| [TROUBLESHOOTING_GENERACION_IA.md](TROUBLESHOOTING_GENERACION_IA.md) | Solución de problemas comunes al crear documentos |
| [SISTEMA_FIRMAS_DOCUMENTOS.md](SISTEMA_FIRMAS_DOCUMENTOS.md) | Sistema de firmas electrónicas y físicas |
| [NUMERALES_CON_PROGRAMAS.md](NUMERALES_CON_PROGRAMAS.md) | Mapeo de numerales a tipos de documento |

### 2. Especificaciones de Diseño - Vista WEB

| Archivo | Descripción |
|---------|-------------|
| [AA_ WEB.md](AA_%20WEB.md) | Estructura general, estilos Bootstrap, componentes |

### 3. Especificaciones de Diseño - Exportación PDF

| Archivo | Descripción |
|---------|-------------|
| [AA_PDF_ENCABEZADO.md](AA_PDF_ENCABEZADO.md) | Encabezado formal: logo, título, código/versión |
| [AA_PDF_CUERPO_DOCUMENTO.md](AA_PDF_CUERPO_DOCUMENTO.md) | Secciones del contenido, tipografía |
| [AA_PDF_CONTROL_CAMBIOS.md](AA_PDF_CONTROL_CAMBIOS.md) | Tabla de historial de versiones |
| [AA_PDF_FIRMAS.md](AA_PDF_FIRMAS.md) | Sección de firmas, imágenes base64 |

### 4. Especificaciones de Diseño - Exportación WORD

| Archivo | Descripción |
|---------|-------------|
| [AA_WORD_ENCABEZADO.md](AA_WORD_ENCABEZADO.md) | Encabezado con estilos MSO |
| [AA_WORD_CUERPO_DOCUMENTO.md](AA_WORD_CUERPO_DOCUMENTO.md) | Contenido con estilos Word |
| [AA_WORD_CONTROL_CAMBIOS.md](AA_WORD_CONTROL_CAMBIOS.md) | Tabla compatible con Word |
| [AA_WORD_FIRMAS.md](AA_WORD_FIRMAS.md) | Firmas formato imprimible |

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

## Checklist para Nuevos Documentos

```
[ ] 1. Configurar en BD (tipo, secciones, firmantes)
[ ] 2. Crear vista de tipo en _tipos/
[ ] 3. Agregar detección en DocumentacionController
[ ] 4. Agregar ruta en Routes.php
[ ] 5. Agregar mapeo en ClienteDocumentosSstController
[ ] 6. Probar generación con IA
[ ] 7. Probar vista previa WEB
[ ] 8. Probar exportación PDF
[ ] 9. Probar exportación Word
[ ] 10. Probar flujo de firmas
```

---

## Referencias Externas

- [PROMPT_NUEVO_DOCUMENTO_SST.md](../../PROMPT_NUEVO_DOCUMENTO_SST.md) - Guía completa para crear documentos
- [ARQUITECTURA_DOCUMENTOS_SST.md](../ARQUITECTURA_DOCUMENTOS_SST.md) - Arquitectura general del sistema
- [GESTION_DOCUMENTOS_SST.md](../GESTION_DOCUMENTOS_SST.md) - Gestión y versionamiento

---

**Última actualización:** Febrero 2026
