<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ClientPoliciesModel; // Usaremos este modelo para client_policies
use App\Models\DocumentVersionModel; // Usaremos este modelo para client_policies
use App\Models\PolicyTypeModel; // Usaremos este modelo para client_policies
use App\Models\ClientKpiModel;
use App\Models\KpisModel;
use App\Models\KpiDefinitionModel;
use App\Models\DataOwnerModel;
use App\Models\VariableNumeratorModel;
use App\Models\VariableDenominatorModel;
use App\Models\KpiTypeModel;


use Dompdf\Dompdf;

use CodeIgniter\Controller;

class kpiaccpreventivaController extends Controller
{



    public function accpreventivaKpi()
    {
        // Obtener el ID del cliente desde la sesión
        $session = session();
        $clientId = $session->get('user_id'); // Asegúrate de que este ID es el del cliente

        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $clientPoliciesModel = new ClientPoliciesModel();
        $policyTypeModel = new PolicyTypeModel();
        $versionModel = new DocumentVersionModel();
        $clientKpiModel = new ClientKpiModel();
        $kpisModel = new KpisModel();
        $kpiDefinitionModel = new KpiDefinitionModel();
        $dataOwnerModel = new DataOwnerModel();
        $numeratorModel = new VariableNumeratorModel();
        $denominatorModel = new VariableDenominatorModel();
        $kpiTypeModel = new KpiTypeModel();


        // Obtener los datos del cliente
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
        $policyTypeId = 46; // Supongamos que el ID de la política de alcohol y drogas es 1
        $id_kpis = 6; // Primer indicador: Plan de Trabajo Anual
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

        $clientKpi = $clientKpiModel->where('id_cliente', $clientId)
            ->where('id_kpis', $id_kpis)
            ->first();

        if (!$clientKpi) {
                return view('client/sgsst/kpi/kpi_incompleto', [
                    'client' => $client,
                    'advertencia' => 'Este indicador no ha sido configurado. Por favor contacte a su consultor.',
                ]);
            }

        // Obtener la definición del KPI
        $kpiDefinition = $kpiDefinitionModel->find($clientKpi['id_kpi_definition']);
        $kpiData = $kpisModel->find($id_kpis);
        $kpiType = $kpiTypeModel->find($clientKpi['id_kpi_type']);
        if (!$kpiType) {
            return redirect()->to('/dashboardclient')->with('error', 'No se encontró el tipo de KPI');
        }

        // Obtener los datos del responsable del dato
        $dataOwner = $dataOwnerModel->find($clientKpi['id_data_owner']);

        // Obtener numeradores y denominadores para los periodos de medición
        $sumNumerador = 0;
        $countNumerador = 0;
        $sumDenominador = 0;
        $countDenominador = 0;
        $sumIndicadores = 0;
        $countIndicadores = 0;

        $periodos = [];
        for ($i = 1; $i <= 12; $i++) {
            $numerador = $numeratorModel->find($clientKpi['variable_numerador_' . $i]);
            $denominador = $denominatorModel->find($clientKpi['variable_denominador_' . $i]);

            $datoNumerador = $clientKpi['dato_variable_numerador_' . $i];
            $datoDenominador = $clientKpi['dato_variable_denominador_' . $i];
            $valorIndicador = $clientKpi['valor_indicador_' . $i];

            // Sumar los valores del numerador y denominador, omitiendo los ceros
            if ($datoNumerador != 0) {
                $sumNumerador += $datoNumerador;
                $countNumerador++;
            }

            if ($datoDenominador != 0) {
                $sumDenominador += $datoDenominador;
                $countDenominador++;
            }

            // Sumar el valor del indicador, omitiendo los ceros
            if ($valorIndicador != 0) {
                $sumIndicadores += $valorIndicador;
                $countIndicadores++;
            }


            $periodos[] = [
                'numerador' => $numerador['numerator_variable_text'] ?? 'No definido',
                'denominador' => $denominador['denominator_variable_text'] ?? 'No definido',
                'dato_variable_numerador' => $clientKpi['dato_variable_numerador_' . $i],
                'dato_variable_denominador' => $clientKpi['dato_variable_denominador_' . $i],
                'valor_indicador' => $clientKpi['valor_indicador_' . $i],
            ];
        }

        // Calcular los promedios de los numeradores, denominadores y el valor real (indicador)
        $promedioNumerador = $countNumerador > 0 ? ($sumNumerador / $countNumerador) : 0;
        $promedioDenominador = $countDenominador > 0 ? ($sumDenominador / $countDenominador) : 0;
        $promedioIndicadores = $countIndicadores > 0 ? ($sumIndicadores / $countIndicadores) : 0;



        // Calcular el gran total del indicador
        $granTotalIndicador = $clientKpi['gran_total_indicador']; // O usar el promedio de indicadores ya calculado


        // Obtener el seguimiento y análisis de datos
        $analisis_datos = $clientKpi['analisis_datos'];
        $seguimiento1 = $clientKpi['seguimiento1'];
        $seguimiento2 = $clientKpi['seguimiento2'];
        $seguimiento3 = $clientKpi['seguimiento3'];


        // Pasar los datos a la vista
        $data = [
            'client' => $client,
            'consultant' => $consultant,
            'clientPolicy' => $clientPolicy,
            'policyType' => $policyType,
            'latestVersion' => $latestVersion,
            'allVersions' => $allVersions,  // Pasamos todas las versiones al footer
            'clientKpi' => $clientKpi,
            'kpiDefinition' => $kpiDefinition,
            'kpiData' => $kpiData,
            'kpiType' => $kpiType,
            'dataOwner' => $dataOwner,
            'periodos' => $periodos,
            'analisis_datos' => $analisis_datos,
            'seguimiento1' => $seguimiento1,
            'seguimiento2' => $seguimiento2,
            'seguimiento3' => $seguimiento3,
            'clientKpi' => $clientKpi,
            'periodos' => $periodos,
            'promedioNumerador' => $promedioNumerador,
            'promedioDenominador' => $promedioDenominador,
            'granTotalIndicador' => $granTotalIndicador,
            'promedioIndicadores' => $promedioIndicadores,
        ];

        return view('client/sgsst/kpi/k5accionespreventivas', $data);
    }
}
