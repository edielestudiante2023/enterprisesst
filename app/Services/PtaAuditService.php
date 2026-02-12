<?php

namespace App\Services;

use App\Models\PtaClienteAuditModel;

class PtaAuditService
{
    private static $camposLegibles = [
        'estado_actividad'                   => 'Estado de Actividad',
        'porcentaje_avance'                  => 'Porcentaje de Avance',
        'fecha_propuesta'                    => 'Fecha Propuesta',
        'fecha_cierre'                       => 'Fecha de Cierre',
        'observaciones'                      => 'Observaciones',
        'phva_plandetrabajo'                 => 'PHVA',
        'numeral_plandetrabajo'              => 'Numeral del Plan',
        'actividad_plandetrabajo'            => 'Actividad',
        'responsable_sugerido_plandetrabajo' => 'Responsable Sugerido',
        'id_cliente'                         => 'Cliente',
        'tipo_servicio'                      => 'Tipo de Servicio',
    ];

    public static function log(
        int $idPtaCliente,
        string $accion,
        ?string $campoModificado = null,
        $valorAnterior = null,
        $valorNuevo = null,
        ?string $metodo = null,
        ?int $idCliente = null
    ): bool {
        $session = session();
        $request = service('request');

        $campoLegible = self::$camposLegibles[$campoModificado] ?? $campoModificado;

        $descripcion = match($accion) {
            'UPDATE' => "Se modificó '{$campoLegible}' de '{$valorAnterior}' a '{$valorNuevo}'",
            'INSERT' => "Se creó un nuevo registro",
            'DELETE' => "Se eliminó el registro",
            default  => "Acción: {$accion}"
        };

        $auditModel = new PtaClienteAuditModel();
        return $auditModel->insert([
            'id_ptacliente'    => $idPtaCliente,
            'id_cliente'       => $idCliente,
            'accion'           => $accion,
            'campo_modificado' => $campoModificado,
            'valor_anterior'   => $valorAnterior,
            'valor_nuevo'      => $valorNuevo,
            'id_usuario'       => $session->get('id_usuario'),
            'nombre_usuario'   => $session->get('nombre'),
            'email_usuario'    => $session->get('email'),
            'rol_usuario'      => $session->get('rol'),
            'ip_address'       => $request->getIPAddress(),
            'user_agent'       => $request->getUserAgent()->getAgentString(),
            'metodo'           => $metodo,
            'descripcion'      => $descripcion,
        ]) !== false;
    }
}
