<?php

namespace App\Libraries\DocumentosSSTTypes;

class SocializacionCronogramaBrigada extends AbstractSocializacionCronograma
{
    public function __construct()
    {
        $this->tipoComite        = 'BRIGADA';
        $this->nombreLargoComite = 'Brigada de Emergencias';
        $this->codigoFt          = 'FT-SST-213';
    }

    public function getTipoDocumento(): string
    {
        return 'socializacion_cronograma_brigada';
    }

    public function getMensajeCronograma(string $nombreEmpresa, int $anio): string
    {
        return "Te compartimos el cronograma de reuniones y simulacros de la Brigada de "
             . "Emergencias {$anio} de {$nombreEmpresa}.\n\n"
             . "Estos espacios son fundamentales para mantener al equipo entrenado en evacuacion, "
             . "primeros auxilios, control de incendios y rescate. Tu colaboracion en los simulacros "
             . "es clave para que toda la organizacion responda de forma coordinada ante una "
             . "emergencia real. Te pedimos atender los anuncios previos a cada actividad y, "
             . "cuando se realicen simulacros generales, seguir las instrucciones de los brigadistas "
             . "y dirigirte al punto de encuentro definido en el plan de emergencias.";
    }
}
