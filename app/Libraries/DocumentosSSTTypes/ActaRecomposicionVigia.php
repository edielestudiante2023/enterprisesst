<?php

namespace App\Libraries\DocumentosSSTTypes;

class ActaRecomposicionVigia extends AbstractActaRecomposicion
{
    public function __construct()
    {
        $this->tipoComite = 'VIGIA';
        $this->nombreComite = 'Vigia SST';
        $this->estandarNumeral = '1.1.6';
        $this->codigoBase = 'FT-SST-156';
    }

    public function getTipoDocumento(): string
    {
        return 'acta_recomposicion_vigia';
    }
}
