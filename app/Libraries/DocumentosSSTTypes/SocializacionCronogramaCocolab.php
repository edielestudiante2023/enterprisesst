<?php

namespace App\Libraries\DocumentosSSTTypes;

class SocializacionCronogramaCocolab extends AbstractSocializacionCronograma
{
    public function __construct()
    {
        $this->tipoComite        = 'COCOLAB';
        $this->nombreLargoComite = 'Comite de Convivencia Laboral';
        $this->codigoFt          = 'FT-SST-212';
    }

    public function getTipoDocumento(): string
    {
        return 'socializacion_cronograma_cocolab';
    }

    public function getMensajeCronograma(string $nombreEmpresa, int $anio): string
    {
        return "Te compartimos el cronograma de reuniones del Comite de Convivencia Laboral "
             . "{$anio} de {$nombreEmpresa}.\n\n"
             . "El comite sesiona periodicamente conforme a la Resolucion 652 de 2012 "
             . "(modificada por la Resolucion 3461 de 2025) y la Ley 1010 de 2006. Si necesitas "
             . "presentar una queja relacionada con acoso laboral o conducta abusiva, puedes "
             . "comunicarte con cualquier miembro del comite en cualquier momento; tu solicitud "
             . "sera atendida con la confidencialidad que la norma exige.\n\n"
             . "Estas fechas son la oportunidad formal para que el comite analice los casos "
             . "presentados, pero no son el unico canal: el comite esta a tu disposicion siempre.";
    }
}
