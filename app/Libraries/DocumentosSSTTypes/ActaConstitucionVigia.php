<?php

namespace App\Libraries\DocumentosSSTTypes;

class ActaConstitucionVigia extends AbstractActaConstitucion
{
    public function __construct()
    {
        $this->tipoComite = 'VIGIA';
        $this->nombreComite = 'Vigia SST';
        $this->estandarNumeral = '1.1.6';
    }

    public function getTipoDocumento(): string
    {
        return 'acta_constitucion_vigia';
    }
}
