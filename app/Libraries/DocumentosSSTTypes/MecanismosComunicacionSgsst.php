<?php

namespace App\Libraries\DocumentosSSTTypes;

use App\Services\DocumentoConfigService;

/**
 * Clase MecanismosComunicacionSgsst
 *
 * Implementa la generación del documento de Mecanismos de Comunicación,
 * Auto Reporte en SG-SST para el estándar 2.8.1 de la Resolución 0312/2019.
 *
 * @package App\Libraries\DocumentosSSTTypes
 * @author Enterprise SST
 * @version 1.0
 */
class MecanismosComunicacionSgsst extends AbstractDocumentoSST
{
    protected ?DocumentoConfigService $configService = null;

    protected function getConfigService(): DocumentoConfigService
    {
        if ($this->configService === null) {
            $this->configService = new DocumentoConfigService();
        }
        return $this->configService;
    }

    public function getTipoDocumento(): string
    {
        return 'mecanismos_comunicacion_sgsst';
    }

    public function getNombre(): string
    {
        return 'Mecanismos de Comunicación, Auto Reporte en SG-SST';
    }

    public function getDescripcion(): string
    {
        return 'Establece los canales y procedimientos para la comunicación interna, externa y auto reporte de condiciones de trabajo y salud en el SG-SST';
    }

    public function getEstandar(): ?string
    {
        return '2.8.1';
    }

    public function getSecciones(): array
    {
        // Intentar obtener secciones desde BD
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

        // Fallback con secciones hardcodeadas
        return [
            ['numero' => 1, 'nombre' => 'Objetivo', 'key' => 'objetivo'],
            ['numero' => 2, 'nombre' => 'Alcance', 'key' => 'alcance'],
            ['numero' => 3, 'nombre' => 'Definiciones', 'key' => 'definiciones'],
            ['numero' => 4, 'nombre' => 'Responsabilidades', 'key' => 'responsabilidades'],
            ['numero' => 5, 'nombre' => 'Mecanismos de Comunicación Interna', 'key' => 'comunicacion_interna'],
            ['numero' => 6, 'nombre' => 'Mecanismos de Comunicación Externa', 'key' => 'comunicacion_externa'],
            ['numero' => 7, 'nombre' => 'Procedimiento de Auto Reporte', 'key' => 'auto_reporte'],
            ['numero' => 8, 'nombre' => 'Canales de Recepción de Inquietudes', 'key' => 'canales_inquietudes'],
            ['numero' => 9, 'nombre' => 'Registro y Seguimiento', 'key' => 'registro_seguimiento'],
        ];
    }

    public function getFirmantesRequeridos(int $estandares): array
    {
        if ($estandares <= 10) {
            return ['responsable_sst', 'representante_legal'];
        }

        return ['responsable_sst', 'representante_legal', 'copasst'];
    }

    public function getContenidoEstatico(string $seccionKey, array $cliente, ?array $contexto, int $estandares, int $anio): string
    {
        $nombreEmpresa = $cliente['nombre_cliente'] ?? 'la empresa';
        $comite = $this->getTextoComite($estandares);

        $contenidos = [
            'objetivo' => "Establecer los mecanismos de comunicación interna, externa y los procedimientos de auto reporte que permitan a {$nombreEmpresa} garantizar que los trabajadores, contratistas y partes interesadas puedan recibir, documentar y responder a las comunicaciones relacionadas con la Seguridad y Salud en el Trabajo.\n\nEste procedimiento da cumplimiento al estándar 2.8.1 de la Resolución 0312 de 2019, asegurando canales efectivos para recolectar inquietudes, ideas, aportes y el reporte de condiciones de trabajo y salud por parte de los trabajadores.",

            'alcance' => "Este procedimiento aplica a:\n\n- Todos los trabajadores directos de {$nombreEmpresa}\n- Contratistas y subcontratistas\n- Proveedores con acceso a las instalaciones\n- Visitantes\n- Partes interesadas externas\n\nCubre todos los procesos de comunicación interna, comunicación externa y auto reporte relacionados con el Sistema de Gestión de Seguridad y Salud en el Trabajo, en todas las sedes y centros de trabajo de la organización.",

            'definiciones' => "**Comunicación Interna:** Intercambio de información entre los diferentes niveles y áreas de {$nombreEmpresa} relacionada con el SG-SST.\n\n**Comunicación Externa:** Intercambio de información entre {$nombreEmpresa} y partes interesadas externas (ARL, EPS, autoridades, proveedores, comunidad).\n\n**Auto Reporte:** Proceso mediante el cual el trabajador reporta por iniciativa propia las condiciones de su estado de salud y de su entorno de trabajo.\n\n**Condiciones de Trabajo:** Conjunto de variables que definen la realización de una tarea específica y el entorno en que se realiza.\n\n**Condiciones de Salud:** Estado físico, mental y social del trabajador, que puede verse afectado por las condiciones de trabajo.\n\n**Canal de Comunicación:** Medio a través del cual se transmite la información (verbal, escrito, digital).\n\n**Buzón de Sugerencias:** Medio físico o virtual para que los trabajadores depositen inquietudes, sugerencias o reportes.\n\n**Reporte de Peligros:** Comunicación formal de una condición o acto que puede generar un accidente o enfermedad laboral.\n\n**Partes Interesadas:** Personas u organizaciones que pueden afectar, verse afectadas o percibirse afectadas por las actividades de SST.",

            'responsabilidades' => "**Alta Dirección / Representante Legal:**\n- Aprobar los canales oficiales de comunicación del SG-SST\n- Asignar los recursos necesarios para el funcionamiento de los canales\n- Dar respuesta oportuna a comunicaciones de alto impacto\n- Revisar periódicamente la efectividad de los mecanismos\n\n**Responsable del SG-SST:**\n- Diseñar, implementar y mantener los canales de comunicación\n- Atender y dar trámite a los reportes recibidos\n- Generar informes periódicos de comunicación y auto reporte\n- Garantizar la confidencialidad de los reportes cuando aplique\n- Capacitar a los trabajadores en el uso de los canales\n\n**{$comite}:**\n- Canalizar las inquietudes de los trabajadores\n- Verificar que se dé respuesta oportuna a los reportes\n- Participar en la revisión de la efectividad de los canales\n- Promover el uso de los mecanismos de auto reporte\n\n**Trabajadores:**\n- Utilizar los canales oficiales de comunicación\n- Reportar oportunamente condiciones de trabajo y salud\n- Participar activamente en los espacios de comunicación\n- Atender las comunicaciones recibidas relacionadas con SST",
        ];

        return $contenidos[$seccionKey] ?? parent::getContenidoEstatico($seccionKey, $cliente, $contexto, $estandares, $anio);
    }
}
