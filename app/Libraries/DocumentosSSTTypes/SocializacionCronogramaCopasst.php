<?php

namespace App\Libraries\DocumentosSSTTypes;

class SocializacionCronogramaCopasst extends AbstractSocializacionCronograma
{
    public function __construct()
    {
        $this->tipoComite        = 'COPASST';
        $this->nombreLargoComite = 'COPASST';
        $this->codigoFt          = 'FT-SST-211';
    }

    public function getTipoDocumento(): string
    {
        return 'socializacion_cronograma_copasst';
    }

    public function getMensajeCronograma(string $nombreEmpresa, int $anio): string
    {
        return "Te compartimos el cronograma de reuniones del COPASST {$anio} de {$nombreEmpresa}.\n\n"
             . "Estas fechas te permitiran anticiparte para llevar solicitudes, recomendaciones o "
             . "temas que desees que sean tratados en el comite. Tu participacion fortalece la "
             . "cultura del autocuidado y la mejora continua en seguridad y salud en el trabajo.";
    }
}
