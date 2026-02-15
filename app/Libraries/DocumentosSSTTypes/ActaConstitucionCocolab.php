<?php

namespace App\Libraries\DocumentosSSTTypes;

class ActaConstitucionCocolab extends AbstractActaConstitucion
{
    public function __construct()
    {
        $this->tipoComite = 'COCOLAB';
        $this->nombreComite = 'Comite de Convivencia Laboral';
        $this->estandarNumeral = '1.1.8';
    }

    public function getTipoDocumento(): string
    {
        return 'acta_constitucion_cocolab';
    }
}
