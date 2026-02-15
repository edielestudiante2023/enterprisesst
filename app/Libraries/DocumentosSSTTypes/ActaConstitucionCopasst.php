<?php

namespace App\Libraries\DocumentosSSTTypes;

class ActaConstitucionCopasst extends AbstractActaConstitucion
{
    public function __construct()
    {
        $this->tipoComite = 'COPASST';
        $this->nombreComite = 'COPASST';
        $this->estandarNumeral = '1.1.6';
    }

    public function getTipoDocumento(): string
    {
        return 'acta_constitucion_copasst';
    }
}
