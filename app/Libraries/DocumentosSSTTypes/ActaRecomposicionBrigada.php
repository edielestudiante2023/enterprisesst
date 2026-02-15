<?php

namespace App\Libraries\DocumentosSSTTypes;

class ActaRecomposicionBrigada extends AbstractActaRecomposicion
{
    public function __construct()
    {
        $this->tipoComite = 'BRIGADA';
        $this->nombreComite = 'Brigada de Emergencias';
        $this->estandarNumeral = null;
        $this->codigoBase = 'FT-SST-156';
    }

    public function getTipoDocumento(): string
    {
        return 'acta_recomposicion_brigada';
    }
}
