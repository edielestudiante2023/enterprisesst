<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\KpiPolicyModel;
use App\Models\ObjectivesPolicyModel;
use App\Models\KpisModel;
use App\Models\KpiTypeModel;
use App\Models\KpiDefinitionModel;
use App\Models\DataOwnerModel;
use App\Models\VariableNumeratorModel;
use App\Models\VariableDenominatorModel;
use App\Models\MeasurementPeriodModel;
use App\Models\ClientKpiModel; // Faltaba este uso
use CodeIgniter\Controller;
use App\Models\ConsultantModel;
use App\Models\ClientPoliciesModel; // Usaremos este modelo para client_policies
use App\Models\DocumentVersionModel; // Usaremos este modelo para client_policies
use App\Models\PolicyTypeModel;

class kpitodoslosobjetivosController extends Controller
{
    public function todoslosKpi()
    {
        // Obtener el ID del cliente desde la sesión
        $session = session();
        $clientId = $session->get('user_id'); // Asegúrate de que este ID es el del cliente autenticado
    
        // Modelos que necesitamos para obtener los datos relacionados
        $clientKpiModel = new ClientKpiModel();
        $clientModel = new ClientModel();
        $kpiPolicyModel = new KpiPolicyModel();
        $objectivesModel = new ObjectivesPolicyModel();
        $kpisModel = new KpisModel();
        $kpiTypeModel = new KpiTypeModel();
        $kpiDefinitionModel = new KpiDefinitionModel();
        $dataOwnerModel = new DataOwnerModel();
        $numeratorModel = new VariableNumeratorModel();
        $denominatorModel = new VariableDenominatorModel();
        $consultantModel = new ConsultantModel();
        $clientPoliciesModel = new ClientPoliciesModel();
        $policyTypeModel = new PolicyTypeModel();
        $versionModel = new DocumentVersionModel();
    
        // Obtener el cliente
        $client = $clientModel->find($clientId);
        if (!$client) {
            return redirect()->to('/dashboardclient')->with('error', 'No se pudo encontrar la información del cliente');
        }
    
        // Obtener los datos del consultor relacionado con el cliente
        $consultant = $consultantModel->find($client['id_consultor']);
        if (!$consultant) {
            return redirect()->to('/dashboardclient')->with('error', 'No se pudo encontrar la información del consultor');
        }
    
        // Obtener la política de alcohol y drogas del cliente
        $policyTypeId = 46; // Ajusta según sea necesario
        $clientPolicy = $clientPoliciesModel->where('client_id', $clientId)
            ->where('policy_type_id', $policyTypeId)
            ->orderBy('id', 'DESC')
            ->first();
        if (!$clientPolicy) {
            return redirect()->to('/dashboardclient')->with('error', 'No se encontró este documento para este cliente.');
        }
    
        // Obtener el tipo de política
        $policyType = $policyTypeModel->find($policyTypeId);
    
        // Obtener la versión más reciente del documento
        $latestVersion = $versionModel->where('client_id', $clientId)
            ->where('policy_type_id', $policyTypeId)
            ->orderBy('created_at', 'DESC')
            ->first();
        if (!$latestVersion) {
            return redirect()->to('/dashboardclient')->with('error', 'No se encontró un versionamiento para este documento de este cliente.');
        }
    
        // Obtener todas las versiones del documento
        $allVersions = $versionModel->where('client_id', $clientId)
            ->where('policy_type_id', $policyTypeId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    
        if (!$allVersions) {
            return redirect()->to('/dashboardclient')->with('error', 'No se encontró un versionamiento para este documento de este cliente.');
        }
    
        // Obtener solo los KPIs del cliente autenticado
        $clientKpis = $clientKpiModel->where('id_cliente', $clientId)->findAll();
    
        // Crear un array para almacenar los datos procesados
        $kpisData = [];
    
        foreach ($clientKpis as $kpi) {
            // Obtener los nombres correspondientes a partir de los IDs
            $cliente = $clientModel->find($kpi['id_cliente']);
            $kpiPolicy = $kpiPolicyModel->find($kpi['id_kpi_policy']);
            $objective = $objectivesModel->find($kpi['id_objectives']);
            $kpiData = $kpisModel->find($kpi['id_kpis']);
            $kpiType = $kpiTypeModel->find($kpi['id_kpi_type']);
            $kpiDefinition = $kpiDefinitionModel->find($kpi['id_kpi_definition']);
            $dataOwner = $dataOwnerModel->find($kpi['id_data_owner']);
    
            // Variables para el cálculo del promedio
            $sumIndicadores = 0;
            $validIndicadores = 0;
    
            // Construir la estructura de periodos y consultar los textos de numerador y denominador
            $periodos = [];
            for ($i = 1; $i <= 12; $i++) {
                // Consulta las descripciones de numerador y denominador
                $numerador = $numeratorModel->find($kpi['variable_numerador_' . $i]);
                $denominador = $denominatorModel->find($kpi['variable_denominador_' . $i]);
    
                // Obtener el valor del indicador
                $indicador = $kpi['valor_indicador_' . $i];
    
                // Verificar si el numerador y denominador son ambos diferentes de 0
                if ($kpi['dato_variable_numerador_' . $i] != 0 && $kpi['dato_variable_denominador_' . $i] != 0) {
                    // Sumar el valor del indicador y aumentar el contador de indicadores válidos
                    $sumIndicadores += $indicador;
                    $validIndicadores++;
                }
    
                // Agregar los valores de los periodos al arreglo
                $periodos[] = [
                    'numerador' => $numerador['numerator_variable_text'] ?? 'Numerador no encontrado',
                    'denominador' => $denominador['denominator_variable_text'] ?? 'Denominador no encontrado',
                    'dato_variable_numerador' => $kpi['dato_variable_numerador_' . $i],
                    'dato_variable_denominador' => $kpi['dato_variable_denominador_' . $i],
                    'valor_indicador' => $indicador
                ];
            }
    
            // Calcular el promedio solo si hay indicadores válidos
            $promedioIndicadores = ($validIndicadores > 0) ? ($sumIndicadores / $validIndicadores) : 0;
    
            // Agregar los datos del KPI junto con los periodos y el promedio
            $kpisData[] = [
                'id_client_kpi' => $kpi['id_client_kpi'],
                'year' => $kpi['year'],
                'month' => $kpi['month'],
                'kpi_interpretation' => $kpi['kpi_interpretation'],
                'cliente' => $cliente['nombre_cliente'] ?? 'Cliente no encontrado',
                'kpi_policy' => $kpiPolicy['policy_kpi_definition'] ?? 'Política no encontrada',
                'objective' => $objective['name_objectives'] ?? 'Objetivo no encontrado',
                'kpi' => $kpiData['kpi_name'] ?? 'KPI no encontrado',
                'kpi_type' => $kpiType['kpi_type'] ?? 'Tipo de KPI no encontrado',
                'kpi_definition' => $kpiDefinition['name_kpi_definition'] ?? 'Definición no encontrada',
                'kpi_target' => $kpi['kpi_target'],
                'kpi_formula' => $kpi['kpi_formula'],
                'positions_should_know_result' => $kpi['positions_should_know_result'],
                'data_source' => $kpi['data_source'],
                'data_owner' => isset($dataOwner['data_owner']) ? $dataOwner['data_owner'] : 'Sin responsable',
                'gran_total_indicador' => $kpi['gran_total_indicador'],
                'periodicidad' => $kpi['periodicidad'],
                'promedio_indicadores' => $promedioIndicadores, // Promedio calculado
                'periodos' => $periodos, // Periodos con las descripciones de numerador y denominador
                'analisis_datos' => $kpi['analisis_datos'],
                'seguimiento1' => $kpi['seguimiento1'],
                'seguimiento2' => $kpi['seguimiento2'],
                'seguimiento3' => $kpi['seguimiento3'],
            ];
        }
    
        // Pasar los datos a la vista
        $viewData = [
            'clientKpis' => $kpisData,
            'client' => $client,
            'consultant' => $consultant,
            'clientPolicy' => $clientPolicy,
            'policyType' => $policyType,
            'latestVersion' => $latestVersion,
            'allVersions' => $allVersions,
        ];
    
        return view('client/sgsst/kpi/todosloskpi', $viewData);
    }
    
}
