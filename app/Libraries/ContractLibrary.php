<?php

namespace App\Libraries;

use App\Models\ContractModel;
use App\Models\ClientModel;

class ContractLibrary
{
    protected $contractModel;
    protected $clientModel;

    public function __construct()
    {
        $this->contractModel = new ContractModel();
        $this->clientModel = new ClientModel();
    }

    /**
     * Crea un nuevo contrato para un cliente
     */
    public function createContract($data)
    {
        // Validar que el cliente existe
        $client = $this->clientModel->find($data['id_cliente']);
        if (!$client) {
            return [
                'success' => false,
                'message' => 'El cliente no existe'
            ];
        }

        // Generar número de contrato automáticamente si no se proporciona
        if (empty($data['numero_contrato'])) {
            $data['numero_contrato'] = $this->contractModel->generateContractNumber($data['id_cliente']);
        }

        // Determinar el tipo de contrato si no se especifica
        if (empty($data['tipo_contrato'])) {
            $existingContracts = $this->contractModel->where('id_cliente', $data['id_cliente'])->countAllResults();
            $data['tipo_contrato'] = $existingContracts > 0 ? 'renovacion' : 'inicial';
        }

        // Establecer estado por defecto
        if (empty($data['estado'])) {
            $data['estado'] = 'activo';
        }

        // Si es un contrato activo, desactivar otros contratos activos del mismo cliente
        if ($data['estado'] === 'activo') {
            $this->deactivateClientContracts($data['id_cliente']);
        }

        // Guardar el contrato
        if ($this->contractModel->insert($data)) {
            $contractId = $this->contractModel->getInsertID();

            // Actualizar las fechas en tbl_clientes para mantener retrocompatibilidad
            $this->updateClientDates($data['id_cliente']);

            return [
                'success' => true,
                'message' => 'Contrato creado exitosamente',
                'contract_id' => $contractId,
                'contract_number' => $data['numero_contrato']
            ];
        }

        return [
            'success' => false,
            'message' => 'Error al crear el contrato',
            'errors' => $this->contractModel->errors()
        ];
    }

    /**
     * Renueva un contrato (crea uno nuevo basado en el anterior)
     */
    public function renewContract($idContrato, $newEndDate, $valorContrato = null, $observaciones = null)
    {
        $oldContract = $this->contractModel->find($idContrato);

        if (!$oldContract) {
            return [
                'success' => false,
                'message' => 'Contrato no encontrado'
            ];
        }

        // Marcar el contrato anterior como vencido
        $this->contractModel->update($idContrato, ['estado' => 'vencido']);

        // Crear el nuevo contrato
        $newContractData = [
            'id_cliente' => $oldContract['id_cliente'],
            'fecha_inicio' => date('Y-m-d'),
            'fecha_fin' => $newEndDate,
            'valor_contrato' => $valorContrato ?? $oldContract['valor_contrato'],
            'tipo_contrato' => 'renovacion',
            'estado' => 'activo',
            'observaciones' => $observaciones ?? "Renovación del contrato {$oldContract['numero_contrato']}"
        ];

        return $this->createContract($newContractData);
    }

    /**
     * Obtiene información completa de un contrato con datos del cliente y consultor
     */
    public function getContractWithClient($idContrato)
    {
        // Usar tbl_contratos.* para obtener TODOS los campos del contrato incluyendo id_consultor_responsable
        return $this->contractModel->select('tbl_contratos.*,
                                             tbl_clientes.nombre_cliente,
                                             tbl_clientes.nit_cliente,
                                             tbl_clientes.correo_cliente,
                                             tbl_clientes.telefono_1_cliente,
                                             tbl_clientes.firma_representante_legal,
                                             tbl_consultor.nombre_consultor,
                                             tbl_consultor.firma_consultor')
                                   ->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_contratos.id_cliente')
                                   ->join('tbl_consultor', 'tbl_consultor.id_consultor = tbl_contratos.id_consultor_responsable', 'left')
                                   ->where('tbl_contratos.id_contrato', $idContrato)
                                   ->first();
    }

    /**
     * Obtiene el historial completo de contratos de un cliente con estadísticas
     */
    public function getClientContractHistory($idCliente)
    {
        $contracts = $this->contractModel->getClientContracts($idCliente);
        $renewals = $this->contractModel->countRenewals($idCliente);
        $firstDate = $this->contractModel->getFirstContractDate($idCliente);
        $antiquity = $this->contractModel->getClientAntiquity($idCliente);

        return [
            'contracts' => $contracts,
            'total_contracts' => count($contracts),
            'total_renewals' => $renewals,
            'first_contract_date' => $firstDate,
            'client_antiquity_months' => $antiquity,
            'client_antiquity_years' => round($antiquity / 12, 1)
        ];
    }

    /**
     * Obtiene alertas de contratos próximos a vencer
     */
    public function getContractAlerts($idConsultor = null, $days = 30)
    {
        $expiringContracts = $this->contractModel->getExpiringContracts($days);

        // Filtrar por consultor si se especifica
        if ($idConsultor) {
            $expiringContracts = array_filter($expiringContracts, function ($contract) use ($idConsultor) {
                $client = $this->clientModel->find($contract['id_cliente']);
                return $client && $client['id_consultor'] == $idConsultor;
            });
        }

        // Calcular días restantes y nivel de urgencia
        foreach ($expiringContracts as &$contract) {
            $fechaFin = new \DateTime($contract['fecha_fin']);
            $hoy = new \DateTime();
            $diferencia = $hoy->diff($fechaFin);
            $diasRestantes = (int)$diferencia->format('%r%a');

            $contract['dias_restantes'] = $diasRestantes;

            if ($diasRestantes <= 7) {
                $contract['urgencia'] = 'alta';
                $contract['color'] = 'danger';
            } elseif ($diasRestantes <= 15) {
                $contract['urgencia'] = 'media';
                $contract['color'] = 'warning';
            } else {
                $contract['urgencia'] = 'baja';
                $contract['color'] = 'info';
            }
        }

        return $expiringContracts;
    }

    /**
     * Cancela un contrato
     */
    public function cancelContract($idContrato, $motivo = null)
    {
        $contract = $this->contractModel->find($idContrato);

        if (!$contract) {
            return [
                'success' => false,
                'message' => 'Contrato no encontrado'
            ];
        }

        $observaciones = $contract['observaciones'];
        if ($motivo) {
            $observaciones .= "\n\nCancelado: " . $motivo . " (Fecha: " . date('Y-m-d H:i:s') . ")";
        }

        if ($this->contractModel->update($idContrato, [
            'estado' => 'cancelado',
            'observaciones' => $observaciones
        ])) {
            // Actualizar las fechas en tbl_clientes
            $this->updateClientDates($contract['id_cliente']);

            return [
                'success' => true,
                'message' => 'Contrato cancelado exitosamente'
            ];
        }

        return [
            'success' => false,
            'message' => 'Error al cancelar el contrato'
        ];
    }

    /**
     * Desactiva todos los contratos activos de un cliente
     */
    protected function deactivateClientContracts($idCliente)
    {
        return $this->contractModel->where('id_cliente', $idCliente)
                                   ->where('estado', 'activo')
                                   ->set(['estado' => 'vencido'])
                                   ->update();
    }

    /**
     * Actualiza las fechas en tbl_clientes basándose en el contrato activo
     * Esto mantiene la retrocompatibilidad con el sistema anterior
     */
    public function updateClientDates($idCliente)
    {
        $activeContract = $this->contractModel->getActiveContract($idCliente);

        if ($activeContract) {
            $this->clientModel->update($idCliente, [
                'fecha_fin_contrato' => $activeContract['fecha_fin']
            ]);

            return true;
        }

        return false;
    }

    /**
     * Ejecuta el mantenimiento automático de contratos
     * - Actualiza contratos vencidos
     * - Sincroniza fechas con tbl_clientes
     */
    public function runMaintenance()
    {
        // Actualizar contratos vencidos
        $updatedContracts = $this->contractModel->updateExpiredContracts();

        // Sincronizar fechas con todos los clientes
        $clients = $this->clientModel->findAll();
        $syncedClients = 0;

        foreach ($clients as $client) {
            if ($this->updateClientDates($client['id_cliente'])) {
                $syncedClients++;
            }
        }

        return [
            'expired_contracts_updated' => $updatedContracts,
            'clients_synced' => $syncedClients
        ];
    }

    /**
     * Obtiene estadísticas generales de contratos
     */
    public function getContractStats($idConsultor = null)
    {
        $builder = $this->contractModel->builder();

        if ($idConsultor) {
            $builder->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_contratos.id_cliente')
                    ->where('tbl_clientes.id_consultor', $idConsultor);
        }

        $result = $builder->select("
            COUNT(*) as total_contratos,
            SUM(CASE WHEN tbl_contratos.estado = 'activo' THEN 1 ELSE 0 END) as contratos_activos,
            SUM(CASE WHEN tbl_contratos.estado = 'vencido' THEN 1 ELSE 0 END) as contratos_vencidos,
            SUM(CASE WHEN tbl_contratos.estado = 'cancelado' THEN 1 ELSE 0 END) as contratos_cancelados,
            SUM(CASE WHEN tbl_contratos.tipo_contrato = 'renovacion' THEN 1 ELSE 0 END) as total_renovaciones,
            SUM(CASE WHEN tbl_contratos.estado = 'activo' THEN tbl_contratos.valor_contrato ELSE 0 END) as valor_total_activos
        ")->get()->getRowArray();

        $stats = $result ?? [
            'total_contratos' => 0,
            'contratos_activos' => 0,
            'contratos_vencidos' => 0,
            'contratos_cancelados' => 0,
            'total_renovaciones' => 0,
            'valor_total_activos' => 0
        ];

        // Calcular tasa de renovación
        $builderInicial = $this->contractModel->builder();
        if ($idConsultor) {
            $builderInicial->join('tbl_clientes', 'tbl_clientes.id_cliente = tbl_contratos.id_cliente')
                          ->where('tbl_clientes.id_consultor', $idConsultor);
        }
        $totalInicial = $builderInicial->where('tbl_contratos.tipo_contrato', 'inicial')->countAllResults();

        $stats['tasa_renovacion'] = $totalInicial > 0
            ? round(((int)$stats['total_renovaciones'] / $totalInicial) * 100, 2)
            : 0;

        return $stats;
    }

    /**
     * Valida si un cliente puede tener un nuevo contrato
     */
    public function canCreateContract($idCliente, $fechaInicio, $fechaFin)
    {
        // Verificar si hay contratos activos que se superpongan
        $overlapping = $this->contractModel->where('id_cliente', $idCliente)
                                          ->where('estado', 'activo')
                                          ->groupStart()
                                              ->where('fecha_inicio <=', $fechaFin)
                                              ->where('fecha_fin >=', $fechaInicio)
                                          ->groupEnd()
                                          ->first();

        if ($overlapping) {
            return [
                'can_create' => false,
                'message' => 'Ya existe un contrato activo que se superpone con las fechas especificadas',
                'overlapping_contract' => $overlapping
            ];
        }

        return [
            'can_create' => true,
            'message' => 'Se puede crear el contrato'
        ];
    }
}
