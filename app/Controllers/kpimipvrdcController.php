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

class kpimipvrdcController
 extends Controller
{



    public function mipvrdcKpi()
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

        // Obtener la política (por ejemplo, de alcohol y drogas) del cliente
        $policyTypeId = 46; // ID de la política
        $id_kpis = 2; // Primer indicador: Plan de Trabajo Anual
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

        // Obtener el KPI del cliente
        $clientKpi = $clientKpiModel->where('id_cliente', $clientId)
            ->where('id_kpis', $id_kpis)
            ->first();
        if (!$clientKpi) {
            return redirect()->to('/dashboardclient')->with('error', 'KPI no encontrado');
        }

        // Obtener la definición del KPI y otros datos relacionados
        $kpiDefinition = $kpiDefinitionModel->find($clientKpi['id_kpi_definition']);
        $kpiData = $kpisModel->find($id_kpis);
        $kpiType = $kpiTypeModel->find($clientKpi['id_kpi_type']);
        if (!$kpiType) {
            return redirect()->to('/dashboardclient')->with('error', 'No se encontró el tipo de KPI');
        }
        $dataOwner = $dataOwnerModel->find($clientKpi['id_data_owner']);

        // Inicializar variables para acumular la suma de numerador y denominador
        // (queremos sumarlos, sin dividir por el número de valores)
        $sumNumerador = 0;
        $sumDenominador = 0;

        // Para el indicador, se calculará el promedio
        $sumIndicadores = 0;
        $countIndicadores = 0;

        $periodos = [];
        for ($i = 1; $i <= 12; $i++) {
            $numerador = $numeratorModel->find($clientKpi['variable_numerador_' . $i]);
            $denominador = $denominatorModel->find($clientKpi['variable_denominador_' . $i]);

            $datoNumerador = $clientKpi['dato_variable_numerador_' . $i];
            $datoDenominador = $clientKpi['dato_variable_denominador_' . $i];
            $valorIndicador = floatval(str_replace(',', '.', $clientKpi['valor_indicador_' . $i]));


            // Para numerador y denominador: sumar solo si el valor es distinto de cero
            if ($datoNumerador != 0) {
                $sumNumerador += $datoNumerador;
            }

            if ($datoDenominador != 0) {
                $sumDenominador += $datoDenominador;
            }

            // Para el indicador: calcular el promedio (suma y conteo de valores no cero)
            if ($valorIndicador != 0) {
                $sumIndicadores += $valorIndicador;
                $countIndicadores++;
            }

            $periodos[] = [
                'numerador' => $numerador['numerator_variable_text'] ?? 'No definido',
                'denominador' => $denominador['denominator_variable_text'] ?? 'No definido',
                'dato_variable_numerador' => $datoNumerador,
                'dato_variable_denominador' => $datoDenominador,
                'valor_indicador' => $valorIndicador,
            ];
        }

        // Asignar la suma (no el promedio) para numerador y denominador
        $sumaNumerador = $sumNumerador;
        $sumaDenominador = $sumDenominador;

        // Calcular el promedio del indicador (sólo con los valores diferentes de cero)
        $promedioIndicadores = $countIndicadores > 0 ? ((float)$sumIndicadores / (float)$countIndicadores) : 0;


        // Se mantiene el gran total del indicador según lo definido en la base de datos
        $granTotalIndicador = $clientKpi['gran_total_indicador'];

        // Obtener el seguimiento y análisis de datos
        $analisis_datos = $clientKpi['analisis_datos'];
        $seguimiento1 = $clientKpi['seguimiento1'];
        $seguimiento2 = $clientKpi['seguimiento2'];
        $seguimiento3 = $clientKpi['seguimiento3'];

        // Preparar los datos para la vista
        // En la vista, se usa $promedioNumerador y $promedioDenominador para mostrar la suma
        // y se usa $promedioIndicadores (multiplicado por 100) para mostrar el promedio en porcentaje.
        $data = [
            'client' => $client,
            'consultant' => $consultant,
            'clientPolicy' => $clientPolicy,
            'policyType' => $policyType,
            'latestVersion' => $latestVersion,
            'allVersions' => $allVersions,  // Se pasan todas las versiones al footer
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
            'promedioNumerador' => $sumaNumerador,     // Suma total del numerador
            'promedioDenominador' => $sumaDenominador,   // Suma total del denominador
            'granTotalIndicador' => $granTotalIndicador,
            'promedioIndicadores' => $promedioIndicadores, // Promedio (para mostrar como % en la vista)
        ];

        return view('client/sgsst/kpi/k2mipvrdc', $data);
    }
}
