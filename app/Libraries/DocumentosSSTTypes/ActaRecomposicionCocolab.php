<?php

namespace App\Libraries\DocumentosSSTTypes;

class ActaRecomposicionCocolab extends AbstractActaRecomposicion
{
    public function __construct()
    {
        $this->tipoComite = 'COCOLAB';
        $this->nombreComite = 'Comite de Convivencia Laboral';
        $this->estandarNumeral = '1.1.8';
        $this->codigoBase = 'FT-SST-155';
    }

    public function getTipoDocumento(): string
    {
        return 'acta_recomposicion_cocolab';
    }
}
