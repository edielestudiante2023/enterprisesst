<?php

namespace App\Libraries\DocumentosSSTTypes;

class SocializacionMiembrosBrigada extends AbstractSocializacionMiembros
{
    public function __construct()
    {
        $this->tipoComite        = 'BRIGADA';
        $this->nombreLargoComite = 'Brigada de Emergencias';
        $this->codigoFt          = 'FT-SST-203';
    }

    public function getTipoDocumento(): string
    {
        return 'socializacion_miembros_brigada';
    }

    public function getMensajeComite(string $nombreEmpresa): string
    {
        return "Estimados colaboradores de {$nombreEmpresa},\n\n"
             . "La Brigada de Emergencias es el equipo de personas capacitadas y entrenadas "
             . "para actuar de forma inmediata ante situaciones de emergencia (incendios, "
             . "evacuaciones, primeros auxilios y rescate). Los integrantes presentados en "
             . "este documento son tus referentes en caso de cualquier eventualidad.\n\n"
             . "Conoce a los brigadistas de tu sede o area: ellos lideraran la evacuacion "
             . "y prestaran los primeros auxilios mientras llega la atencion especializada. "
             . "En caso de emergencia, sigue sus instrucciones, mantente sereno y dirigete al "
             . "punto de encuentro establecido en el plan de emergencias.\n\n"
             . "Tu colaboracion y respuesta oportuna salva vidas.";
    }
}
