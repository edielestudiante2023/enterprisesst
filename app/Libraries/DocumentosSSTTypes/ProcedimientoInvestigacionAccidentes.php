<?php

namespace App\Libraries\DocumentosSSTTypes;

use App\Services\DocumentoConfigService;

/**
 * Clase ProcedimientoInvestigacionAccidentes
 *
 * Implementa la generacion del Procedimiento de Investigacion de Incidentes,
 * Accidentes de Trabajo y Enfermedades Laborales para el estandar 3.2.1
 * de la Resolucion 0312/2019.
 *
 * TIPO A: Solo Parte 3 (documento formal con IA, sin actividades PTA ni indicadores)
 *
 * @package App\Libraries\DocumentosSSTTypes
 * @author Enterprise SST
 * @version 1.0
 */
class ProcedimientoInvestigacionAccidentes extends AbstractDocumentoSST
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
        return 'procedimiento_investigacion_accidentes';
    }

    public function getNombre(): string
    {
        return 'Procedimiento de Investigacion de Incidentes, Accidentes de Trabajo y Enfermedades Laborales';
    }

    public function getDescripcion(): string
    {
        return 'Establece la metodologia para investigar incidentes, accidentes de trabajo y enfermedades laborales, determinando causas basicas e inmediatas, y realizando seguimiento a las acciones correctivas';
    }

    public function getEstandar(): ?string
    {
        return '3.2.1';
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
            ['numero' => 4, 'nombre' => 'Marco Legal', 'key' => 'marco_legal'],
            ['numero' => 5, 'nombre' => 'Responsabilidades', 'key' => 'responsabilidades'],
            ['numero' => 6, 'nombre' => 'Reporte de Accidentes de Trabajo y Enfermedades Laborales', 'key' => 'reporte_at_el'],
            ['numero' => 7, 'nombre' => 'Procedimiento de Investigacion de Incidentes y Accidentes', 'key' => 'investigacion_incidentes_accidentes'],
            ['numero' => 8, 'nombre' => 'Investigacion de Enfermedades Laborales', 'key' => 'investigacion_enfermedades'],
            ['numero' => 9, 'nombre' => 'Determinacion de Causas Basicas e Inmediatas', 'key' => 'causas_basicas_inmediatas'],
            ['numero' => 10, 'nombre' => 'Acciones Correctivas y Preventivas', 'key' => 'acciones_correctivas_preventivas'],
            ['numero' => 11, 'nombre' => 'Seguimiento y Verificacion de Acciones', 'key' => 'seguimiento_verificacion'],
            ['numero' => 12, 'nombre' => 'Registros y Evidencias', 'key' => 'registros_evidencias'],
        ];
    }

    public function getFirmantesRequeridos(int $estandares): array
    {
        return ['responsable_sst', 'representante_legal'];
    }

    public function getContenidoEstatico(string $seccionKey, array $cliente, ?array $contexto, int $estandares, int $anio): string
    {
        $nombreEmpresa = $cliente['nombre_cliente'] ?? 'la empresa';
        $comite = $this->getTextoComite($estandares);

        $contenidos = [
            'objetivo' => "Establecer la metodologia para la investigacion de incidentes, accidentes de trabajo y enfermedades laborales que se presenten en {$nombreEmpresa}, con el fin de determinar las causas basicas e inmediatas de cada evento, definir acciones correctivas y preventivas, y realizar seguimiento a su implementacion para proteger a los trabajadores potencialmente expuestos.\n\nEste procedimiento da cumplimiento al estandar 3.2.1 de la Resolucion 0312 de 2019, al articulo 2.2.4.6.32 del Decreto 1072 de 2015 y a la Resolucion 1401 de 2007, garantizando que todos los incidentes, accidentes de trabajo y enfermedades laborales sean debidamente reportados a la ARL, EPS y Direccion Territorial del Ministerio de Trabajo, e investigados para prevenir su recurrencia.",

            'alcance' => "Este procedimiento aplica a:\n\n- Todos los trabajadores directos de {$nombreEmpresa}\n- Contratistas y subcontratistas\n- Trabajadores en mision y temporales\n- Visitantes y terceros en las instalaciones\n\nCubre la investigacion de:\n\n- Incidentes de trabajo (eventos sin lesion)\n- Accidentes de trabajo leves, graves y mortales\n- Enfermedades laborales diagnosticadas como de origen laboral\n\nAplica en todas las sedes, centros de trabajo y actividades externas realizadas en nombre de la organizacion.",

            'definiciones' => "**Accidente de Trabajo:** Todo suceso repentino que sobrevenga por causa o con ocasion del trabajo, y que produzca en el trabajador una lesion organica, una perturbacion funcional o psiquiatrica, una invalidez o la muerte (Ley 1562 de 2012, art. 3).\n\n**Incidente de Trabajo:** Suceso acaecido en el curso del trabajo o en relacion con este, que tuvo el potencial de ser un accidente, en el que hubo personas involucradas sin que sufrieran lesiones o se presentaran danos a la propiedad y/o perdida en los procesos.\n\n**Enfermedad Laboral:** La contraida como resultado de la exposicion a factores de riesgo inherentes a la actividad laboral o del medio en el que el trabajador se ha visto obligado a trabajar (Ley 1562 de 2012, art. 4).\n\n**Causa Inmediata:** Circunstancias que se presentan justamente antes del contacto; por lo general son observables o se hacen sentir. Se clasifican en actos inseguros y condiciones inseguras.\n\n**Causa Basica:** Causas reales que se manifiestan detras de los sintomas; razones por las cuales ocurren los actos y condiciones inseguros. Se clasifican en factores personales y factores del trabajo.\n\n**FURAT:** Formato Unico de Reporte de Accidentes de Trabajo, establecido por la Resolucion 156 de 2005.\n\n**FUREL:** Formato Unico de Reporte de Enfermedad Laboral, establecido por la Resolucion 156 de 2005.\n\n**Investigacion de Accidente o Incidente:** Proceso sistematico de determinacion y ordenacion de causas, hechos o situaciones que generaron o favorecieron la ocurrencia del accidente o incidente (Resolucion 1401 de 2007).",

            'responsabilidades' => "**Alta Direccion / Representante Legal:**\n- Garantizar que se investiguen todos los incidentes, accidentes y enfermedades laborales\n- Asignar los recursos necesarios para la investigacion y las acciones correctivas\n- Reportar los accidentes graves y mortales a la Direccion Territorial del Ministerio de Trabajo dentro de los 2 dias habiles\n- Revisar los resultados de las investigaciones y aprobar las acciones correctivas\n\n**Responsable del SG-SST:**\n- Liderar y coordinar la investigacion de todos los eventos\n- Diligenciar y enviar el FURAT a la ARL dentro de los 2 dias habiles siguientes al accidente\n- Diligenciar el FUREL cuando se diagnostique enfermedad laboral\n- Conformar el equipo investigador segun la severidad del evento\n- Definir acciones correctivas y preventivas con el equipo investigador\n- Realizar seguimiento al cumplimiento de las acciones\n- Mantener los registros y evidencias durante minimo 20 anos\n\n**{$comite}:**\n- Participar en la investigacion de accidentes e incidentes\n- Verificar el cumplimiento de las acciones correctivas y preventivas\n- Proponer medidas de prevencion basadas en las investigaciones\n- Hacer seguimiento en las reuniones ordinarias\n\n**Trabajadores:**\n- Reportar inmediatamente cualquier incidente, accidente o condicion de salud de origen laboral\n- Colaborar con la investigacion proporcionando informacion veraz\n- Cumplir las acciones correctivas y preventivas establecidas\n- Participar en las actividades de socializacion de lecciones aprendidas",
        ];

        return $contenidos[$seccionKey] ?? parent::getContenidoEstatico($seccionKey, $cliente, $contexto, $estandares, $anio);
    }
}
