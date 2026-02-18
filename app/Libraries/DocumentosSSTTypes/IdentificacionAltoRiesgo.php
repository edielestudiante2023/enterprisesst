<?php

namespace App\Libraries\DocumentosSSTTypes;

use App\Services\DocumentoConfigService;

/**
 * Clase IdentificacionAltoRiesgo
 *
 * Implementa la generacion del documento de Identificacion de Trabajadores
 * de Alto Riesgo y Cotizacion de Pension Especial.
 * Estandar 1.1.5 de la Resolucion 0312/2019
 *
 * Documento de 1 parte (directo, flujo secciones_ia).
 * Lee configuracion (secciones, prompts, firmantes) desde BD.
 *
 * @package App\Libraries\DocumentosSSTTypes
 * @author Enterprise SST
 * @version 1.0
 */
class IdentificacionAltoRiesgo extends AbstractDocumentoSST
{
    protected ?DocumentoConfigService $configService = null;
    protected ?array $configCache = null;

    protected function getConfigService(): DocumentoConfigService
    {
        if ($this->configService === null) {
            $this->configService = new DocumentoConfigService();
        }
        return $this->configService;
    }

    protected function getConfig(): array
    {
        if ($this->configCache === null) {
            $this->configCache = $this->getConfigService()->obtenerTipoDocumento($this->getTipoDocumento());
        }
        return $this->configCache;
    }

    public function getTipoDocumento(): string
    {
        return 'identificacion_alto_riesgo';
    }

    public function getNombre(): string
    {
        $config = $this->getConfig();
        return $config['nombre'] ?? 'Identificacion de Trabajadores de Alto Riesgo y Cotizacion de Pension Especial';
    }

    public function getDescripcion(): string
    {
        $config = $this->getConfig();
        return $config['descripcion'] ?? 'Metodologia para identificar trabajadores que desarrollen actividades clasificadas como de alto riesgo conforme al Decreto 2090 de 2003';
    }

    public function getEstandar(): ?string
    {
        return '1.1.5';
    }

    public function getSecciones(): array
    {
        $seccionesBD = $this->getConfigService()->obtenerSecciones($this->getTipoDocumento());

        if (!empty($seccionesBD)) {
            $secciones = [];
            foreach ($seccionesBD as $s) {
                $secciones[] = [
                    'numero' => (int)($s['numero'] ?? 0),
                    'nombre' => $s['nombre'] ?? '',
                    'key' => $s['key'] ?? $s['seccion_key'] ?? ''
                ];
            }
            return $secciones;
        }

        return $this->getSeccionesFallback();
    }

    protected function getSeccionesFallback(): array
    {
        return [
            ['numero' => 1, 'nombre' => 'Objetivo', 'key' => 'objetivo'],
            ['numero' => 2, 'nombre' => 'Alcance', 'key' => 'alcance'],
            ['numero' => 3, 'nombre' => 'Marco Normativo', 'key' => 'marco_normativo'],
            ['numero' => 4, 'nombre' => 'Definiciones Clave', 'key' => 'definiciones'],
            ['numero' => 5, 'nombre' => 'Responsabilidades', 'key' => 'responsabilidades'],
            ['numero' => 6, 'nombre' => 'Identificacion de Cargos y Actividades', 'key' => 'identificacion_cargos'],
            ['numero' => 7, 'nombre' => 'Analisis frente al Decreto 2090 de 2003', 'key' => 'analisis_decreto'],
            ['numero' => 8, 'nombre' => 'Determinacion de Aplicabilidad', 'key' => 'determinacion'],
            ['numero' => 9, 'nombre' => 'Gestion de Cotizacion Especial', 'key' => 'cotizacion_especial'],
            ['numero' => 10, 'nombre' => 'Registros y Evidencias', 'key' => 'registros'],
        ];
    }

    public function getFirmantesRequeridos(int $estandares): array
    {
        $config = $this->getConfig();
        if (!empty($config['firmantes'])) {
            return array_column($config['firmantes'], 'firmante_tipo');
        }

        return ['responsable_sst', 'representante_legal'];
    }

    protected function getPromptFallback(string $seccionKey, int $estandares): string
    {
        $comite = $this->getTextoComite($estandares);

        $prompts = [
            'objetivo' => "Genera el objetivo del procedimiento para identificar trabajadores de alto riesgo conforme al Decreto 2090 de 2003 y garantizar la cotizacion a pension especial cuando aplique. Debe ser conciso y referir la normativa. Maximo 2 parrafos.",

            'alcance' => "Define el alcance: aplica a todos los cargos y puestos de trabajo, personal directo, temporal o tercerizado cuando aplique. Formato: lista con vinetas.",

            'marco_normativo' => "Lista el marco normativo: Decreto 2090 de 2003, Resolucion 0312 de 2019, Decreto 1072 de 2015. Incluir nota: este procedimiento no crea requisitos adicionales a la norma. NO usar tablas Markdown.",

            'definiciones' => "Define: Actividad de alto riesgo (segun Decreto 2090/2003), Pension especial (cotizacion adicional para trabajadores de alto riesgo). Maximo 4-5 definiciones relevantes. Formato: termino en **negrita** seguido de definicion.",

            'responsabilidades' => "Define responsabilidades por rol: Empleador (garantizar cumplimiento), Responsable SG-SST (ejecutar identificacion), Talento Humano/Nomina (aplicar cotizacion), ARL (emitir conceptos tecnicos), {$comite} (participar en revision). Formato: rol en **negrita** seguido de lista.",

            'identificacion_cargos' => "Paso 1: Describir revision de organigrama, perfiles de cargo y descripcion real de actividades. Indicar evidencias obligatorias: listado de cargos analizados, perfil de cargo.",

            'analisis_decreto' => "Paso 2: Describir como el responsable SG-SST compara actividades reales con las clasificadas como alto riesgo en el Decreto 2090/2003. Indicar que el analisis es por actividad, no solo por nombre del cargo. Evidencias: matriz de analisis normativo.",

            'determinacion' => "Paso 3: Describir como se concluye si aplica o no alto riesgo. Indicar evidencias: documento tecnico de identificacion con conclusion justificada, firma y fecha.",

            'cotizacion_especial' => "Paso 4: Describir proceso cuando se identifican trabajadores de alto riesgo: informar a Talento Humano, verificar cotizacion PILA, validar afiliacion al fondo de pensiones. Evidencias: soporte PILA, certificacion fondo, listado de trabajadores.",

            'registros' => "Resume todos los registros y evidencias que genera este procedimiento: listado de cargos, matriz de analisis, documento tecnico de identificacion, soportes PILA (si aplica). Formato: lista con vinetas.",
        ];

        return $prompts[$seccionKey] ?? "Genera el contenido para la seccion '{$seccionKey}' del procedimiento de Identificacion de Trabajadores de Alto Riesgo segun la Resolucion 0312/2019 y el Decreto 2090/2003.";
    }

    public function getContenidoEstatico(string $seccionKey, array $cliente, ?array $contexto, int $estandares, int $anio): string
    {
        $nombreEmpresa = $cliente['nombre_cliente'] ?? 'la empresa';
        $comite = $this->getTextoComite($estandares);

        $contenidos = [
            'objetivo' => "Establecer la metodologia para identificar los trabajadores de {$nombreEmpresa} que desarrollen actividades clasificadas como de alto riesgo conforme al Decreto 2090 de 2003, y garantizar la cotizacion a pension especial cuando aplique.\n\nEste procedimiento da cumplimiento al estandar 1.1.5 de la Resolucion 0312 de 2019 y al Decreto 1072 de 2015.",

            'alcance' => "Este procedimiento aplica a:\n\n- Todos los cargos y puestos de trabajo de {$nombreEmpresa}\n- Personal directo de la organizacion\n- Personal temporal o en mision (cuando aplique)\n- Contratistas que realicen actividades en las instalaciones",

            'marco_normativo' => "**Normativa aplicable:**\n\n- **Decreto 2090 de 2003:** Por el cual se definen las actividades de alto riesgo para la salud del trabajador y se modifican y senalan las condiciones, requisitos y beneficios del regimen de pensiones de los trabajadores que laboran en dichas actividades.\n- **Decreto 1072 de 2015:** Decreto Unico Reglamentario del Sector Trabajo.\n- **Resolucion 0312 de 2019:** Estandares Minimos del Sistema de Gestion de Seguridad y Salud en el Trabajo.\n- **Ley 100 de 1993:** Sistema de Seguridad Social Integral.",

            'definiciones' => "**Actividad de alto riesgo:** Actividad laboral que por su naturaleza o condiciones de trabajo implica la exposicion del trabajador a factores de riesgo que pueden generar una disminucion en su expectativa de vida saludable, conforme al Decreto 2090 de 2003.\n\n**Pension especial de vejez:** Prestacion economica a la que tienen derecho los trabajadores que hayan laborado en actividades de alto riesgo, con un menor numero de semanas cotizadas y edad.\n\n**Cotizacion adicional:** Aporte adicional al sistema de pensiones que debe realizar el empleador para los trabajadores clasificados en actividades de alto riesgo.\n\n**Perfil de cargo:** Documento que describe las funciones, responsabilidades y condiciones del puesto de trabajo.",

            'responsabilidades' => "**Empleador / Representante Legal:**\n- Garantizar el cumplimiento de las obligaciones derivadas de la identificacion de alto riesgo\n- Asignar los recursos necesarios para la cotizacion especial\n\n**Responsable del SG-SST:**\n- Ejecutar la identificacion de actividades de alto riesgo\n- Elaborar el documento tecnico de identificacion\n- Mantener actualizada la informacion\n\n**Talento Humano / Nomina:**\n- Aplicar la cotizacion adicional a pension cuando se identifique alto riesgo\n- Verificar la afiliacion correcta al fondo de pensiones\n\n**{$comite}:**\n- Participar en la revision del procedimiento\n- Conocer los resultados de la identificacion",

            'identificacion_cargos' => "**Paso 1: Identificacion de Cargos y Actividades**\n\nEl Responsable del SG-SST debe:\n\n1. Revisar el organigrama vigente de {$nombreEmpresa}\n2. Obtener los perfiles de cargo actualizados\n3. Realizar descripcion de las actividades reales ejecutadas por cada cargo\n4. Documentar las condiciones de exposicion\n\n**Evidencias:**\n- Listado de cargos analizados\n- Perfiles de cargo actualizados\n- Descripcion de actividades reales",

            'analisis_decreto' => "**Paso 2: Analisis frente al Decreto 2090 de 2003**\n\nEl Responsable del SG-SST debe comparar las actividades reales de cada cargo con las actividades clasificadas como de alto riesgo en el Decreto 2090 de 2003.\n\n**Importante:** El analisis se realiza por actividad ejecutada, no unicamente por el nombre del cargo.\n\n**Actividades clasificadas como alto riesgo (Art. 2, Decreto 2090/2003):**\n- Trabajos en mineria subterranea\n- Trabajos con exposicion a altas temperaturas\n- Trabajos con exposicion a radiaciones ionizantes\n- Trabajos con exposicion a sustancias comprobadamente cancerigenas\n- Actividades de la Unidad Administrativa de Aeronautica Civil\n\n**Evidencias:**\n- Matriz de analisis normativo",

            'determinacion' => "**Paso 3: Determinacion de Aplicabilidad**\n\nCon base en el analisis del Paso 2, el Responsable del SG-SST elabora un documento tecnico que concluye:\n\n- **Aplica alto riesgo:** Se identificaron actividades que coinciden con las del Decreto 2090/2003 → Proceder al Paso 4\n- **No aplica alto riesgo:** Ninguna actividad coincide → Documentar la conclusion\n\n**Evidencias:**\n- Documento tecnico de identificacion con conclusion justificada\n- Firma del responsable y fecha de elaboracion",

            'cotizacion_especial' => "**Paso 4: Gestion de Cotizacion Especial**\n\nCuando se identifican trabajadores en actividades de alto riesgo:\n\n1. Informar a Talento Humano / Nomina sobre los cargos identificados\n2. Verificar la cotizacion en PILA con el porcentaje adicional correspondiente\n3. Validar la afiliacion al fondo de pensiones que administre el regimen de prima media\n4. Mantener actualizado el listado de trabajadores expuestos\n\n**Evidencias:**\n- Soporte de cotizacion PILA\n- Certificacion del fondo de pensiones\n- Listado actualizado de trabajadores en alto riesgo",

            'registros' => "**Registros y Evidencias del Procedimiento:**\n\n- Listado de cargos analizados\n- Perfiles de cargo actualizados\n- Matriz de analisis frente al Decreto 2090/2003\n- Documento tecnico de identificacion (con conclusion, firma y fecha)\n- Soportes de cotizacion PILA (si aplica alto riesgo)\n- Certificacion del fondo de pensiones (si aplica)\n- Listado de trabajadores en actividades de alto riesgo (si aplica)\n\n**Periodicidad de actualizacion:** Cada vez que se creen nuevos cargos, se modifiquen actividades existentes, o cuando cambien las condiciones de exposicion.",
        ];

        return $contenidos[$seccionKey] ?? parent::getContenidoEstatico($seccionKey, $cliente, $contexto, $estandares, $anio);
    }
}
