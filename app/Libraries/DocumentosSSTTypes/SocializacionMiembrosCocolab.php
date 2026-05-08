<?php

namespace App\Libraries\DocumentosSSTTypes;

class SocializacionMiembrosCocolab extends AbstractSocializacionMiembros
{
    public function __construct()
    {
        $this->tipoComite        = 'COCOLAB';
        $this->nombreLargoComite = 'Comite de Convivencia Laboral';
        $this->codigoFt          = 'FT-SST-202';
    }

    public function getTipoDocumento(): string
    {
        return 'socializacion_miembros_cocolab';
    }

    public function getMensajeComite(string $nombreEmpresa): string
    {
        return "Estimados colaboradores de {$nombreEmpresa},\n\n"
             . "El Comite de Convivencia Laboral, en cumplimiento de la Resolucion 652 de 2012 "
             . "(modificada por la Resolucion 3461 de 2025) y la Ley 1010 de 2006, tiene como "
             . "proposito contribuir a la prevencion del acoso laboral y promover relaciones "
             . "respetuosas, armoniosas y constructivas en el trabajo.\n\n"
             . "Si vives o eres testigo de una situacion de acoso laboral, conducta abusiva o "
             . "conflicto interpersonal en el trabajo, el comite es un canal seguro y "
             . "CONFIDENCIAL para presentar tu queja. Tu identidad y la informacion compartida "
             . "estaran protegidas conforme a la normatividad vigente.\n\n"
             . "Comunicate con nosotros a traves de los correos institucionales de los miembros "
             . "del comite. Estamos para escucharte.";
    }
}
