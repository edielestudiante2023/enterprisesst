<?php

namespace App\Libraries\DocumentosSSTTypes;

class ActaConstitucionBrigada extends AbstractActaConstitucion
{
    public function __construct()
    {
        $this->tipoComite = 'BRIGADA';
        $this->nombreComite = 'Brigada de Emergencias';
        $this->estandarNumeral = null;
    }

    public function getTipoDocumento(): string
    {
        return 'acta_constitucion_brigada';
    }
}
