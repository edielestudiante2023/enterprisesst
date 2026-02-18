<?php

namespace App\Libraries\DocumentosSSTTypes;

/**
 * Clase ProcedimientoControlDocumental
 *
 * Implementa la generación del Procedimiento de Control Documental del SG-SST
 * para el estándar 2.5.1 de la Resolución 0312/2019.
 *
 * @package App\Libraries\DocumentosSSTTypes
 * @author Enterprise SST
 * @version 1.0
 */
class ProcedimientoControlDocumental extends AbstractDocumentoSST
{
    public function getTipoDocumento(): string
    {
        return 'procedimiento_control_documental';
    }

    public function getNombre(): string
    {
        return 'Procedimiento de Control Documental del SG-SST';
    }

    public function getDescripcion(): string
    {
        return 'Establece las directrices para la elaboración, revisión, aprobación, distribución y conservación de documentos del SG-SST';
    }

    public function getEstandar(): ?string
    {
        return '2.5.1';
    }

    public function getSecciones(): array
    {
        return [
            ['numero' => 1, 'nombre' => 'Objetivo', 'key' => 'objetivo'],
            ['numero' => 2, 'nombre' => 'Alcance', 'key' => 'alcance'],
            ['numero' => 3, 'nombre' => 'Definiciones', 'key' => 'definiciones'],
            ['numero' => 4, 'nombre' => 'Marco Normativo', 'key' => 'marco_normativo'],
            ['numero' => 5, 'nombre' => 'Responsabilidades', 'key' => 'responsabilidades'],
            ['numero' => 6, 'nombre' => 'Tipos de Documentos del SG-SST', 'key' => 'tipos_documentos'],
            ['numero' => 7, 'nombre' => 'Estructura y Codificación', 'key' => 'codificacion'],
            ['numero' => 8, 'nombre' => 'Elaboración de Documentos', 'key' => 'elaboracion'],
            ['numero' => 9, 'nombre' => 'Revisión y Aprobación', 'key' => 'revision_aprobacion'],
            ['numero' => 10, 'nombre' => 'Distribución y Acceso', 'key' => 'distribucion'],
            ['numero' => 11, 'nombre' => 'Control de Cambios', 'key' => 'control_cambios'],
            ['numero' => 12, 'nombre' => 'Conservación y Retención', 'key' => 'conservacion'],
            ['numero' => 13, 'nombre' => 'Gestion de Documentos Externos', 'key' => 'documentos_externos'],
            ['numero' => 14, 'nombre' => 'Listado Maestro de Documentos', 'key' => 'listado_maestro'],
            ['numero' => 15, 'nombre' => 'Disposición Final', 'key' => 'disposicion_final'],
        ];
    }

    public function getFirmantesRequeridos(int $estandares): array
    {
        // El procedimiento de control documental requiere 2 firmas
        return ['representante_legal', 'responsable_sst'];
    }

    public function getContenidoEstatico(string $seccionKey, array $cliente, ?array $contexto, int $estandares, int $anio): string
    {
        $nombreEmpresa = $cliente['nombre_cliente'] ?? 'la empresa';
        $comite = $this->getTextoComite($estandares);

        $contenidos = [
            'objetivo' => "Establecer las directrices para la elaboración, revisión, aprobación, distribución, conservación y control de los documentos del Sistema de Gestión de Seguridad y Salud en el Trabajo (SG-SST) de {$nombreEmpresa}.\n\nEste procedimiento garantiza la trazabilidad documental y el cumplimiento del estándar 2.5.1 de la Resolución 0312 de 2019, asegurando que la documentación del SG-SST esté disponible, actualizada y sea accesible para su consulta.",

            'alcance' => "Este procedimiento aplica a todos los documentos que hacen parte del Sistema de Gestión de Seguridad y Salud en el Trabajo de {$nombreEmpresa}, incluyendo:\n\n- Políticas\n- Programas\n- Procedimientos\n- Planes\n- Formatos\n- Matrices\n- Manuales\n- Reglamentos\n- Actas\n- Registros\n\nAplica a todo el personal involucrado en la elaboración, revisión, aprobación y uso de documentos del SG-SST.",

            'definiciones' => "**Documento:** Información y su medio de soporte (papel, digital, fotografía, etc.).\n\n**Registro:** Documento que presenta resultados obtenidos o proporciona evidencia de actividades realizadas.\n\n**Versión:** Identificación del estado de evolución de un documento.\n\n**Control Documental:** Conjunto de actividades para gestionar y controlar los documentos.\n\n**Listado Maestro:** Relación de todos los documentos del SG-SST con su información de control.\n\n**Documento Obsoleto:** Documento que ha sido reemplazado por una versión más reciente.\n\n**Retención Documental:** Tiempo mínimo que debe conservarse un documento.\n\n**Documento Controlado:** Documento cuya distribución está controlada y se mantiene actualizado.",

            'marco_normativo' => "**Normativa aplicable al control documental:**\n\n- **Decreto 1072 de 2015:** Artículo 2.2.4.6.12 - Requisitos de documentación del SG-SST\n- **Resolución 0312 de 2019:** Estándar 2.5.1 - Archivo y retención documental del SG-SST\n- **Ley 594 de 2000:** Ley General de Archivos\n- **Decreto 1080 de 2015:** Reglamento del Sector Cultura (archivos)\n- **GTC-ISO 9001:** Lineamientos de control de documentos y registros",

            'responsabilidades' => "**Representante Legal:**\n- Aprobar políticas y documentos estratégicos del SG-SST\n- Asignar recursos para la gestión documental\n\n**Responsable del SG-SST:**\n- Elaborar y actualizar la documentación del SG-SST\n- Mantener actualizado el Listado Maestro de Documentos\n- Controlar las versiones de los documentos\n- Gestionar la distribución y acceso a documentos\n- Garantizar la conservación según tiempos de retención\n\n**{$comite}:**\n- Revisar documentos relacionados con sus funciones\n- Reportar necesidades de actualización documental\n\n**Trabajadores:**\n- Utilizar únicamente documentos vigentes\n- Reportar documentos desactualizados u obsoletos",

            'tipos_documentos' => "| Tipo | Prefijo | Descripción | Ejemplos |\n|------|---------|-------------|----------|\n| Política | POL | Directrices de alto nivel aprobadas por la alta dirección | POL-SST-001 |\n| Programa | PRG | Conjunto de actividades planificadas con objetivos específicos | PRG-CAP-001 |\n| Procedimiento | PRO | Describe cómo realizar una actividad específica | PRO-DOC-001 |\n| Plan | PLA | Acciones programadas para alcanzar objetivos | PLA-EME-001 |\n| Formato | FT | Plantillas para registro de datos e información | FT-SST-001 |\n| Matriz | MTZ | Herramientas de análisis e identificación | MTZ-PEL-001 |\n| Manual | MAN | Guías completas sobre un tema | MAN-SST-001 |\n| Reglamento | REG | Normas internas de obligatorio cumplimiento | REG-HSI-001 |",

            'codificacion' => "**Estructura del código de documentos:**\n\n```\nPREFIJO - TEMA - CONSECUTIVO\n```\n\n**Donde:**\n- **PREFIJO:** Indica el tipo de documento (POL, PRG, PRO, PLA, FT, MTZ, MAN, REG)\n- **TEMA:** Abreviatura del tema principal (SST, CAP, EME, EPP, etc.)\n- **CONSECUTIVO:** Número secuencial de 3 dígitos (001, 002, 003...)\n\n**Ejemplos de codificación:**\n- PRG-CAP-001: Programa de Capacitación en SST\n- POL-SST-001: Política de Seguridad y Salud en el Trabajo\n- PRO-INV-001: Procedimiento de Investigación de ATEL\n- FT-SST-004: Formato de Presupuesto del SG-SST\n\n**Versionamiento:**\n- Versión Mayor (1.0, 2.0, 3.0): Cambios significativos en estructura o contenido\n- Versión Menor (1.1, 1.2, 2.1): Ajustes menores, correcciones o actualizaciones",

            'elaboracion' => "**Proceso para elaborar documentos del SG-SST:**\n\n1. **Identificación de necesidad:** El Responsable del SG-SST o el área competente identifica la necesidad de crear un nuevo documento.\n\n2. **Elaboración del borrador:** Se redacta el documento siguiendo la estructura estándar establecida.\n\n3. **Revisión técnica:** El Responsable del SG-SST verifica que el contenido sea correcto y cumpla con la normatividad.\n\n4. **Aprobación:** El nivel de aprobación corresponde según el tipo de documento.\n\n5. **Codificación:** Se asigna el código único según el sistema de codificación.\n\n6. **Registro:** Se incluye en el Listado Maestro de Documentos.\n\n**Estructura estándar de documentos:**\n- Encabezado institucional (logo, título, código, versión, fecha)\n- Objetivo\n- Alcance\n- Definiciones (si aplica)\n- Contenido principal\n- Responsabilidades\n- Registros asociados\n- Control de cambios\n- Firmas de aprobación",

            'revision_aprobacion' => "**Niveles de aprobación según tipo de documento:**\n\n| Tipo | Elabora | Revisa | Aprueba |\n|------|---------|--------|--------|\n| Políticas | Responsable SST | Gerencia | Representante Legal |\n| Programas | Responsable SST | {$comite} | Representante Legal |\n| Procedimientos | Responsable SST | Área involucrada | Responsable SST |\n| Formatos | Responsable SST | - | Responsable SST |\n\n**Firma electrónica:**\n- Los documentos del SG-SST pueden ser firmados electrónicamente\n- Cada firma incluye: Nombre completo, Cargo, Fecha, Firma digital/imagen\n- Se genera código de verificación único (QR)\n\n**Frecuencia de revisión:**\n- Documentos estratégicos (políticas, programas): Revisión anual\n- Documentos operativos: Según necesidad o cambios normativos",

            'distribucion' => "**Distribución de documentos:**\n- Los documentos aprobados se publican en el sistema de gestión documental\n- Se notifica a los responsables cuando hay nuevas versiones disponibles\n- El acceso está controlado según el perfil de usuario\n\n**Control de copias:**\n- Solo se consideran válidas las versiones digitales publicadas en el sistema\n- Las copias impresas NO son documentos controlados\n- Cada documento incluye la leyenda: \"Copia controlada - Válida solo en formato digital\"\n\n**Documentos obsoletos:**\n- Se identifican claramente con marca de agua \"OBSOLETO\"\n- Se retiran de circulación activa\n- Se conservan en archivo histórico según tiempos de retención",

            'control_cambios' => "**Tipos de cambio:**\n\n- **Cambio Mayor (versión X.0):** Modificaciones significativas en estructura, alcance o contenido principal del documento.\n- **Cambio Menor (versión X.Y):** Correcciones ortográficas, actualizaciones de datos, ajustes de formato.\n\n**Proceso de gestión de cambios:**\n1. Identificar la necesidad de modificación\n2. Elaborar propuesta de cambio documentada\n3. Revisar y aprobar el cambio según niveles\n4. Actualizar el número de versión\n5. Registrar en la tabla de control de cambios\n6. Comunicar a los usuarios afectados\n\n**Registro de cambios:**\nCada documento incluye tabla con el historial:\n\n| Versión | Fecha | Descripción del cambio | Aprobó |\n|---------|-------|------------------------|--------|\n\n**IMPORTANTE:** Todos los documentos del SG-SST deben conservarse por un mínimo de 20 años.",

            'conservacion' => "**Tiempos de retención documental:**\n\nSegún la Resolución 0312 de 2019 y normativa laboral colombiana:\n\n| Tipo de Documento | Tiempo Mínimo | Observación |\n|-------------------|---------------|-------------|\n| Historias clínicas ocupacionales | 20 años | Después de retiro del trabajador |\n| Exámenes médicos ocupacionales | 20 años | Desde fecha del examen |\n| Investigaciones de AT/EL | 20 años | Desde fecha del evento |\n| Programas y procedimientos | 20 años | Desde última versión vigente |\n| Actas del {$comite} | 20 años | Desde fecha del acta |\n| Registros de capacitación | 20 años | Desde fecha de la actividad |\n| Matrices de peligros | 20 años | Cada versión generada |\n\n**Condiciones de conservación:**\n- Almacenamiento digital con respaldos periódicos\n- Protección contra acceso no autorizado\n- Verificación de integridad (hash de documento)\n- Archivo histórico para documentos obsoletos dentro del periodo de retención",

            'documentos_externos' => "**Definición de documento externo:**\n\nSe consideran documentos externos aquellos generados por entidades ajenas a {$nombreEmpresa} pero que son aplicables o necesarios para el funcionamiento del SG-SST. Incluyen:\n\n- Normativas legales vigentes (Leyes, Decretos, Resoluciones del Ministerio de Trabajo)\n- Guías técnicas y circulares de la ARL\n- Documentos de la EPS, AFP y entidades del Sistema General de Seguridad Social\n- Normas técnicas colombianas (NTC, GTC)\n- Estándares internacionales de referencia (ISO 45001)\n\n**Ubicación en el sistema documental:**\n\nLos documentos externos se gestionan en la sub-carpeta **2.5.1.1 - Listado Maestro de Documentos Externos**, ubicada dentro del estándar 2.5.1 (Archivo y retención documental del SG-SST). Esta carpeta es exclusiva para documentación de origen externo, separada de los documentos internos generados por {$nombreEmpresa}.\n\n**Procedimiento de registro:**\n\nPara registrar un documento externo en el sistema, el Responsable del SG-SST sigue estos pasos:\n\n1. Acceder a la carpeta **2.5.1.1 - Listado Maestro de Documentos Externos** en el módulo de documentación\n2. Hacer clic en el botón **\"Adjuntar Documento Externo\"**\n3. Seleccionar el tipo de carga:\n   - **Subir Archivo:** Cargar el documento digital directamente al sistema. Formatos aceptados: PDF, Excel, Word, Imagen (JPG, PNG). Tamaño máximo: 10 MB\n   - **Pegar Enlace:** Registrar un enlace a la fuente oficial o repositorio en la nube (Google Drive, OneDrive, página web institucional)\n4. Completar los campos del formulario:\n   - **Descripción del documento** (obligatorio): Nombre o título del documento externo\n   - **Origen / Entidad emisora:** Organización que emite el documento (ej: Ministerio de Trabajo, ARL, Secretaría de Salud)\n   - **Año** (obligatorio): Año de expedición o vigencia del documento\n   - **Observaciones:** Notas adicionales sobre vigencia, aplicabilidad o requisitos\n5. Confirmar el registro haciendo clic en **\"Adjuntar\"**\n\n**Campos del registro de documentos externos:**\n\n| Campo | Descripción | Obligatorio |\n|-------|-------------|-------------|\n| Descripción | Nombre o título del documento externo | Sí |\n| Origen / Entidad emisora | Organización que emite el documento | No |\n| Tipo de carga | Archivo digital (PDF, Excel, Word, Imagen) o Enlace externo | Sí |\n| Año | Año de expedición o vigencia | Sí |\n| Observaciones | Notas sobre vigencia, aplicabilidad, requisitos | No |\n\n**Control y actualización:**\n\n- El Responsable del SG-SST verifica la vigencia de los documentos externos **al menos una vez al año** o cuando se conozcan cambios normativos\n- Cuando una norma es derogada o modificada, se registra el nuevo documento y se agrega una observación en el registro anterior indicando la norma que lo reemplaza\n- Los cambios normativos relevantes se comunican al {$comite} y al personal involucrado\n\n**Conservación y acceso:**\n\n- Los documentos externos permanecen almacenados en la carpeta 2.5.1.1 del sistema de gestión documental\n- El sistema permite tanto la carga directa de archivos como el registro de enlaces a fuentes oficiales\n- Cada registro incluye la entidad emisora (origen) para garantizar la trazabilidad\n- El acceso está disponible para consulta de todo el personal autorizado del SG-SST",

            'listado_maestro' => "El Listado Maestro de Documentos del SG-SST de {$nombreEmpresa} se mantiene actualizado de forma automática en el sistema de gestión documental.\n\n**Información registrada por cada documento:**\n\n- Código único del documento\n- Nombre/Título del documento\n- Tipo de documento (Política, Programa, Procedimiento, etc.)\n- Versión vigente\n- Fecha de aprobación\n- Estado (Vigente/Obsoleto)\n- Responsable de elaboración\n- Ubicación en el sistema\n\n**Actualización:**\nEl listado se actualiza automáticamente cada vez que:\n- Se crea un nuevo documento\n- Se modifica una versión existente\n- Se declara obsoleto un documento\n- Cambia el estado de un documento\n\n**Nota:** La tabla completa del Listado Maestro se genera dinámicamente desde el sistema de gestión documental.",

            'disposicion_final' => "**Criterios para disposición final de documentos:**\n\n1. **Verificación de retención:** Confirmar que se ha cumplido el tiempo mínimo de retención (20 años para la mayoría de documentos del SG-SST)\n\n2. **Verificación legal:** Confirmar que no existen procesos legales, auditorías o investigaciones en curso que requieran el documento\n\n3. **Documentación:** Registrar la decisión de disposición en acta formal\n\n**Métodos de disposición:**\n\n- **Eliminación segura:** Destrucción física o digital que impida la recuperación de información (trituración, borrado certificado)\n\n- **Transferencia:** Envío a archivo histórico permanente si el documento tiene valor histórico o testimonial\n\n- **Digitalización:** Para documentos en papel, convertir a formato digital antes de eliminar el soporte físico\n\n**Acta de eliminación:**\nDebe registrar:\n- Listado de documentos eliminados (código, nombre, fechas)\n- Fecha de eliminación\n- Método utilizado\n- Responsable de la eliminación\n- Firma de autorización\n\n**ADVERTENCIA:** Nunca eliminar documentos antes de cumplir el tiempo mínimo de retención legal (20 años)."
        ];

        return $contenidos[$seccionKey] ?? parent::getContenidoEstatico($seccionKey, $cliente, $contexto, $estandares, $anio);
    }
}
