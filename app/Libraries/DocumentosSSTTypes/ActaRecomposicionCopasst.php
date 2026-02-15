<?php

namespace App\Libraries\DocumentosSSTTypes;

class ActaRecomposicionCopasst extends AbstractActaRecomposicion
{
    public function __construct()
    {
        $this->tipoComite = 'COPASST';
        $this->nombreComite = 'COPASST';
        $this->estandarNumeral = '1.1.6';
        $this->codigoBase = 'FT-SST-156';
    }

    public function getTipoDocumento(): string
    {
        return 'acta_recomposicion_copasst';
    }
}
