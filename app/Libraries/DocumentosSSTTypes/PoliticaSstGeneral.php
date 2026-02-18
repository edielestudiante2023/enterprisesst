<?php

namespace App\Libraries\DocumentosSSTTypes;

/**
 * Clase PoliticaSstGeneral
 *
 * Implementa la generación de la Política de Seguridad y Salud en el Trabajo
 * Numeral 2.1.1 de la Resolución 0312/2019
 *
 * @package App\Libraries\DocumentosSSTTypes
 * @author Enterprise SST
 * @version 1.0
 */
class PoliticaSstGeneral extends AbstractDocumentoSST
{
    public function getTipoDocumento(): string
    {
        return 'politica_sst_general';
    }

    public function getNombre(): string
    {
        return 'Política de Seguridad y Salud en el Trabajo';
    }

    public function getDescripcion(): string
    {
        return 'Política del SG-SST firmada, fechada y comunicada al COPASST según Decreto 1072/2015 y Resolución 0312/2019';
    }

    public function getEstandar(): ?string
    {
        return '2.1.1';
    }

    public function getSecciones(): array
    {
        return [
            ['numero' => 1, 'nombre' => 'Objetivo', 'key' => 'objetivo'],
            ['numero' => 2, 'nombre' => 'Alcance', 'key' => 'alcance'],
            ['numero' => 3, 'nombre' => 'Declaración de la Política', 'key' => 'declaracion'],
            ['numero' => 4, 'nombre' => 'Compromisos del Empleador', 'key' => 'compromisos_empleador'],
            ['numero' => 5, 'nombre' => 'Compromisos de los Trabajadores', 'key' => 'compromisos_trabajadores'],
            ['numero' => 6, 'nombre' => 'Marco Legal', 'key' => 'marco_legal'],
            ['numero' => 7, 'nombre' => 'Comunicación y Divulgación', 'key' => 'comunicacion'],
        ];
    }

    public function getFirmantesRequeridos(int $estandares): array
    {
        // Consultor elabora, Delegado revisa (si aplica), Rep Legal aprueba
        return ['consultor_sst', 'representante_legal'];
    }

    public function getContenidoEstatico(string $seccionKey, array $cliente, ?array $contexto, int $estandares, int $anio): string
    {
        $nombreEmpresa = $cliente['nombre_cliente'] ?? 'LA EMPRESA';
        $actividadEconomica = $contexto['actividad_economica'] ?? 'sus actividades económicas';
        $comite = $this->getTextoComite($estandares);

        $contenidos = [
            'objetivo' => "La presente política tiene como objetivo establecer el compromiso de {$nombreEmpresa} con la protección de la seguridad y salud de todos los trabajadores, mediante la identificación de peligros, evaluación y valoración de los riesgos, y el establecimiento de los respectivos controles, para prevenir accidentes de trabajo y enfermedades laborales.\n\nEsta política busca promover y mantener el más alto grado de bienestar físico, mental y social de los trabajadores en todas las ocupaciones, cumpliendo con la normatividad legal vigente en materia de Seguridad y Salud en el Trabajo.",

            'alcance' => "La Política de Seguridad y Salud en el Trabajo de {$nombreEmpresa} aplica a:\n\n- Todos los trabajadores, independientemente de su forma de contratación\n- Contratistas y subcontratistas\n- Trabajadores en misión\n- Visitantes\n- Proveedores\n- Cualquier otra persona que ingrese a las instalaciones o participe en las actividades de la empresa\n\nAplica a todas las actividades desarrolladas en las instalaciones de la empresa y fuera de ellas cuando se ejecuten actividades relacionadas con el objeto social.",

            'declaracion' => "{$nombreEmpresa}, dedicada a {$actividadEconomica}, se compromete a implementar y mantener un Sistema de Gestión de Seguridad y Salud en el Trabajo, orientado a:\n\n**Identificar, evaluar y controlar** los peligros y riesgos presentes en el ambiente de trabajo que puedan afectar la seguridad y salud de los trabajadores.\n\n**Proteger la seguridad y salud** de todos los trabajadores, mediante la mejora continua del Sistema de Gestión de la Seguridad y Salud en el Trabajo.\n\n**Cumplir la normatividad** nacional vigente aplicable en materia de riesgos laborales.\n\n**Garantizar la participación** de los trabajadores y sus representantes en el desarrollo del SG-SST.\n\nEsta política será revisada anualmente y comunicada a todos los trabajadores.",

            'compromisos_empleador' => "El Representante Legal de {$nombreEmpresa} se compromete a:\n\n1. **Definir, firmar, divulgar y actualizar** la política de SST mediante documento escrito.\n\n2. **Rendir cuentas** a quienes conforman la empresa sobre el desarrollo del SG-SST.\n\n3. **Cumplir los requisitos normativos** aplicables en materia de SST.\n\n4. **Gestionar los peligros y riesgos** identificados en el lugar de trabajo.\n\n5. **Desarrollar el plan de trabajo anual** del SG-SST.\n\n6. **Asignar los recursos** humanos, físicos y financieros para la implementación del SG-SST.\n\n7. **Garantizar la capacitación** de los trabajadores en aspectos de SST.\n\n8. **Promover la participación** de los trabajadores y del {$comite} en el SG-SST.",

            'compromisos_trabajadores' => "Los trabajadores de {$nombreEmpresa} se comprometen a:\n\n- Procurar el cuidado integral de su salud física y mental.\n\n- Suministrar información clara, veraz y completa sobre su estado de salud.\n\n- Cumplir las normas, reglamentos e instrucciones del SG-SST.\n\n- Informar oportunamente sobre condiciones de trabajo que afecten su salud.\n\n- Reportar inmediatamente cualquier accidente de trabajo o enfermedad laboral.\n\n- Participar activamente en las actividades de capacitación en SST.\n\n- Participar y contribuir al cumplimiento de los objetivos del SG-SST.",

            'marco_legal' => "La presente política se fundamenta en la siguiente normatividad:\n\n- **Constitución Política de Colombia:** Artículos 25, 48 y 49 sobre derecho al trabajo y seguridad social.\n\n- **Ley 9 de 1979:** Código Sanitario Nacional.\n\n- **Ley 1562 de 2012:** Por la cual se modifica el Sistema de Riesgos Laborales.\n\n- **Decreto 1072 de 2015:** Decreto Único Reglamentario del Sector Trabajo, Libro 2, Parte 2, Título 4, Capítulo 6.\n\n- **Resolución 0312 de 2019:** Estándares Mínimos del Sistema de Gestión de la Seguridad y Salud en el Trabajo.",

            'comunicacion' => "La Política de Seguridad y Salud en el Trabajo será:\n\n1. **Comunicada al {$comite}** para su conocimiento y participación en su difusión.\n\n2. **Publicada y difundida** a través de medios apropiados (carteleras, inducción, correo electrónico).\n\n3. **Incluida en el proceso de inducción y reinducción** de todos los trabajadores.\n\n4. **Entregada a contratistas y visitantes** como parte del proceso de ingreso.\n\n5. **Revisada anualmente** o cuando ocurran cambios significativos en la organización.\n\n6. **Actualizada** cuando sea necesario, dejando evidencia de la comunicación.\n\n_Esta política es de conocimiento y cumplimiento obligatorio para todos los trabajadores de {$nombreEmpresa}._"
        ];

        return $contenidos[$seccionKey] ?? parent::getContenidoEstatico($seccionKey, $cliente, $contexto, $estandares, $anio);
    }
}
