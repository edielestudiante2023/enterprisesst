<?php

namespace App\Libraries\DocumentosSSTTypes;

class SocializacionMiembrosCopasst extends AbstractSocializacionMiembros
{
    public function __construct()
    {
        $this->tipoComite        = 'COPASST';
        $this->nombreLargoComite = 'Comite Paritario de Seguridad y Salud en el Trabajo (COPASST)';
        $this->codigoFt          = 'FT-SST-201';
    }

    public function getTipoDocumento(): string
    {
        return 'socializacion_miembros_copasst';
    }

    public function getMensajeComite(string $nombreEmpresa): string
    {
        return "Estimados colaboradores de {$nombreEmpresa},\n\n"
             . "Desde el Comite Paritario de Seguridad y Salud en el Trabajo (COPASST), "
             . "reafirmamos nuestro compromiso con la promocion de un entorno laboral seguro "
             . "y saludable para todos. Trabajamos diariamente para identificar y mitigar "
             . "riesgos, garantizando el bienestar de cada miembro de nuestra organizacion.\n\n"
             . "Si tienen inquietudes, sugerencias o necesitan apoyo en temas relacionados con "
             . "seguridad y salud en el trabajo, estamos a su disposicion. Pueden comunicarse "
             . "con cualquiera de los miembros del comite a traves de los correos institucionales.\n\n"
             . "La seguridad y salud de todos es nuestra prioridad.";
    }
}
