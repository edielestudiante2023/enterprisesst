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

class ClientKpiController extends Controller
{
    // Mostrar el formulario para agregar un nuevo KPI
    public function addClientKpi()
    {
        // Instanciamos los modelos necesarios para obtener los datos relacionados
        $clientModel = new ClientModel();
        $kpiPolicyModel = new KpiPolicyModel();
        $objectivesModel = new ObjectivesPolicyModel();
        $kpisModel = new KpisModel();
        $kpiTypeModel = new KpiTypeModel();
        $kpiDefinitionModel = new KpiDefinitionModel();
        $dataOwnerModel = new DataOwnerModel();
        $numeratorModel = new VariableNumeratorModel();
        $denominatorModel = new VariableDenominatorModel();
        $measurementPeriodModel = new MeasurementPeriodModel();

        // Consultas para obtener los datos necesarios en el formulario
        $clientes = $clientModel->findAll(); // Lista de clientes
        $kpiPolicies = $kpiPolicyModel->findAll(); // Lista de políticas KPI
        $objectives = $objectivesModel->findAll(); // Lista de objetivos
        $kpis = $kpisModel->findAll(); // Lista de KPIs
        $kpiTypes = $kpiTypeModel->findAll(); // Tipos de KPIs
        $kpiDefinitions = $kpiDefinitionModel->findAll(); // Definiciones de KPIs
        $dataOwners = $dataOwnerModel->findAll(); // Responsables de los datos
        $numerators = $numeratorModel->findAll(); // Variables del numerador
        $denominators = $denominatorModel->findAll(); // Variables del denominador
        $measurementPeriods = $measurementPeriodModel->findAll(); // Periodos de medición

        // Pasar los datos a la vista para poblar el formulario
        $data = [
            'clientes' => $clientes,
            'kpiPolicies' => $kpiPolicies,
            'objectives' => $objectives,
            'kpis' => $kpis,
            'kpiTypes' => $kpiTypes,
            'kpiDefinitions' => $kpiDefinitions,
            'dataOwners' => $dataOwners,
            'numerators' => $numerators,
            'denominators' => $denominators,
            'measurementPeriods' => $measurementPeriods,

            // Agregamos las variables numerador y denominador de cada uno de los 12 periodos
            'periodos' => range(1, 12)
        ];

        // Retornar la vista con los datos
        return view('consultant/add_client_kpi', $data);
    }

    // Procesar el formulario para agregar un nuevo KPI del cliente
    public function addClientKpiPost()
    {
        // Instanciamos el modelo del KPI
        $clientKpiModel = new ClientKpiModel();

        // Validación de los datos del formulario
        $validation = \Config\Services::validation();
        $validation->setRules([
            'id_cliente' => 'required|integer',
            'id_kpi_policy' => 'required|integer',
            'id_objectives' => 'required|integer',
            'id_kpis' => 'required|integer',
            'id_kpi_type' => 'required|integer',
            'id_kpi_definition' => 'required|integer',
            'kpi_target' => 'required|integer',
            'data_source' => 'required|string',
            'kpi_interpretation' => 'required|string',
            'periodicidad' => 'required|string', // Asegúrate de incluir periodicidad en la validación
            'year' => 'required|integer', // Validar el año
            'month' => 'required|string', // Validar el mes
            'analisis_datos' => 'permit_empty|string', // Validar el mes
            'seguimiento1' => 'permit_empty|string', // Validar el mes
            'seguimiento2' => 'permit_empty|string', // Validar el mes
            'seguimiento3' => 'permit_empty|string', // Validar el mes

            // Agregar reglas adicionales si es necesario
        ]);

        // Si la validación falla
        if (!$this->validate($validation->getRules())) {
            // Registrar los errores de validación en los logs
            log_message('error', 'Errores de validación: ' . json_encode($validation->getErrors()));

            // Redirigir con los errores
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }


        // Preparar los datos para insertarlos en la base de datos
        // Preparar los datos para insertar en la base de datos
        $data = [
            'id_cliente' => $this->request->getPost('id_cliente'),
            'id_kpi_policy' => $this->request->getPost('id_kpi_policy'),
            'id_objectives' => $this->request->getPost('id_objectives'),
            'id_kpis' => $this->request->getPost('id_kpis'),
            'id_kpi_type' => $this->request->getPost('id_kpi_type'),
            'id_kpi_definition' => $this->request->getPost('id_kpi_definition'),
            'kpi_target' => $this->request->getPost('kpi_target'),
            'data_source' => $this->request->getPost('data_source'),
            'kpi_interpretation' => $this->request->getPost('kpi_interpretation'),
            'kpi_formula' => $this->request->getPost('kpi_formula') ?: null, // Opcional
            'positions_should_know_result' => $this->request->getPost('positions_should_know_result') ?: null, // Opcional
            'id_data_owner' => $this->request->getPost('id_data_owner') ?: null, // Opcional
            'periodicidad' => $this->request->getPost('periodicidad'), // Asegúrate de capturar el valor de periodicidad
            'year' => $this->request->getPost('year'), // Capturar año
            'month' => $this->request->getPost('month'), // Capturar mes
            'analisis_datos' => $this->request->getPost('analisis_datos'),
            'seguimiento1' => $this->request->getPost('seguimiento1'),
            'seguimiento2' => $this->request->getPost('seguimiento2'),
            'seguimiento3' => $this->request->getPost('seguimiento3'),
        ];

        // Continuar con los periodos y demás lógica...


        // Bucle para agregar los valores de los periodos de 1 a 12
        for ($i = 1; $i <= 12; $i++) {
            $data['variable_numerador_' . $i] = $this->request->getPost('variable_numerador_' . $i);
            $data['dato_variable_numerador_' . $i] = $this->request->getPost('dato_variable_numerador_' . $i);
            $data['variable_denominador_' . $i] = $this->request->getPost('variable_denominador_' . $i);
            $data['dato_variable_denominador_' . $i] = $this->request->getPost('dato_variable_denominador_' . $i);

            // Calcula el valor del indicador para cada periodo
            $numerador = $this->request->getPost('dato_variable_numerador_' . $i);
            $denominador = $this->request->getPost('dato_variable_denominador_' . $i);
            $data['valor_indicador_' . $i] = $this->calculateIndicator($numerador, $denominador);
        }

        // Calcular el total acumulado para los indicadores
        $data['gran_total_indicador'] = $this->calculateTotalIndicator($data);

        // Insertar los datos en la base de datos
        if ($clientKpiModel->insert($data)) {
            // Redirigir con un mensaje de éxito
            return redirect()->to('/listClientKpis')->with('success', 'KPI agregado exitosamente');
        } else {
            // Redirigir con un mensaje de error
            return redirect()->back()->withInput()->with('error', 'Error al agregar el KPI');
        }
    }

    // Método para calcular el valor del indicador
    // Método para calcular el valor del indicador
    private function calculateIndicator($numerador, $denominador)
    {
        // Convertimos numerador y denominador a float para asegurar que sean números
        $numerador = floatval($numerador);
        $denominador = floatval($denominador);

        // Validamos que el denominador no sea 0 y que no sea nulo antes de realizar la división
        return ($denominador != 0 && !is_null($denominador)) ? ($numerador / $denominador) : 0;
    }


    // Método para calcular el total acumulado de los 12 indicadores
    private function calculateTotalIndicator($data)
    {
        $total = 0;
        for ($i = 1; $i <= 12; $i++) {
            $total += $data['valor_indicador_' . $i];
        }
        return $total;
    }


    public function editClientKpi($id_client_kpi)
    {
        // Instanciamos los modelos necesarios
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
        $measurementPeriodModel = new MeasurementPeriodModel();

        // Obtener el KPI específico
        $clientKpi = $clientKpiModel->find($id_client_kpi);
        if (!$clientKpi) {
            // Si no se encuentra el KPI, redirigir con error
            return redirect()->to('/listClientKpis')->with('error', 'El KPI no existe');
        }

        // Obtener los datos relacionados para las listas desplegables
        $clientes = $clientModel->findAll();
        $kpiPolicies = $kpiPolicyModel->findAll();
        $objectives = $objectivesModel->findAll();
        $kpis = $kpisModel->findAll();
        $kpiTypes = $kpiTypeModel->findAll();
        $kpiDefinitions = $kpiDefinitionModel->findAll();
        $dataOwners = $dataOwnerModel->findAll();
        $numerators = $numeratorModel->findAll();
        $denominators = $denominatorModel->findAll();
        $measurementPeriods = $measurementPeriodModel->findAll();

        // Pasar los datos a la vista para pre-poblar el formulario
        $data = [
            'clientKpi' => $clientKpi,
            'clientes' => $clientes,
            'kpiPolicies' => $kpiPolicies,
            'objectives' => $objectives,
            'kpis' => $kpis,
            'kpiTypes' => $kpiTypes,
            'kpiDefinitions' => $kpiDefinitions,
            'dataOwners' => $dataOwners,
            'numerators' => $numerators,
            'denominators' => $denominators,
            'measurementPeriods' => $measurementPeriods,
            'periodos' => range(1, 12)
        ];

        // Cargar la vista para editar el KPI
        return view('consultant/edit_client_kpi', $data);
    }

    public function editClientKpiPost($id_client_kpi)
    {
        // Instanciar el modelo del KPI del cliente
        $clientKpiModel = new ClientKpiModel();

        // Validación de los datos del formulario
        $validation = \Config\Services::validation();
        $validation->setRules([
            'id_cliente' => 'required|integer',
            'id_kpi_policy' => 'required|integer',
            'id_objectives' => 'required|integer',
            'id_kpis' => 'required|integer',
            'id_kpi_type' => 'required|integer',
            'id_kpi_definition' => 'required|integer',
            'kpi_target' => 'required|integer',
            'data_source' => 'required|string',
            'kpi_interpretation' => 'required|string',
            'periodicidad' => 'required|string', // Agregar periodicidad a la validación
            'year' => 'required|integer', // Validar el año
            'month' => 'required|string', // Validar el mes
            'analisis_datos' => 'permit_empty|string', // Validar el mes
            'seguimiento1' => 'permit_empty|string', // Validar el mes
            'seguimiento2' => 'permit_empty|string', // Validar el mes
            'seguimiento3' => 'permit_empty|string', // Validar el mes
            // Puedes agregar reglas adicionales para validar los periodos si es necesario
        ]);

        if (!$this->validate($validation->getRules())) {
            // Si la validación falla, redirigir con los errores
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        // Preparar los datos para actualizar en la base de datos
        $data = [
            'id_cliente' => $this->request->getPost('id_cliente'),
            'id_kpi_policy' => $this->request->getPost('id_kpi_policy'),
            'id_objectives' => $this->request->getPost('id_objectives'),
            'id_kpis' => $this->request->getPost('id_kpis'),
            'id_kpi_type' => $this->request->getPost('id_kpi_type'),
            'id_kpi_definition' => $this->request->getPost('id_kpi_definition'),
            'kpi_target' => $this->request->getPost('kpi_target'),
            'data_source' => $this->request->getPost('data_source'),
            'kpi_interpretation' => $this->request->getPost('kpi_interpretation'),
            'periodicidad' => $this->request->getPost('periodicidad'), // Asegúrate de capturar el valor de periodicidad
            'year' => $this->request->getPost('year'), // Capturar el año
            'month' => $this->request->getPost('month'), // Capturar el mes
            'analisis_datos' => $this->request->getPost('analisis_datos'),
            'seguimiento1' => $this->request->getPost('seguimiento1'),
            'seguimiento2' => $this->request->getPost('seguimiento2'),
            'seguimiento3' => $this->request->getPost('seguimiento3')

        ];
        log_message('debug', 'Datos capturados del formulario: ' . json_encode($data));
        // Bucle para actualizar los valores de los periodos de 1 a 12
        for ($i = 1; $i <= 12; $i++) {
            $data['variable_numerador_' . $i] = $this->request->getPost('variable_numerador_' . $i);
            $data['dato_variable_numerador_' . $i] = $this->request->getPost('dato_variable_numerador_' . $i);
            $data['variable_denominador_' . $i] = $this->request->getPost('variable_denominador_' . $i);
            $data['dato_variable_denominador_' . $i] = $this->request->getPost('dato_variable_denominador_' . $i);

            // Calcula el valor del indicador para cada periodo
            $numerador = $this->request->getPost('dato_variable_numerador_' . $i);
            $denominador = $this->request->getPost('dato_variable_denominador_' . $i);
            $data['valor_indicador_' . $i] = $this->calculateIndicator($numerador, $denominador);
        }
        log_message('debug', 'Datos con valores de periodos: ' . json_encode($data));
        // Calcular el total acumulado para los indicadores
        $data['gran_total_indicador'] = $this->calculateTotalIndicator($data);

        // Actualizar los datos del KPI en la base de datos
        if ($clientKpiModel->update($id_client_kpi, $data)) {
            // Redirigir con un mensaje de éxito
            return redirect()->to('/listClientKpis')->with('success', 'KPI actualizado exitosamente');
        } else {
            // Redirigir con un mensaje de error
            return redirect()->back()->withInput()->with('error', 'Error al actualizar el KPI');
        }
    }

    public function deleteClientKpi($id_client_kpi)
    {
        // Instanciamos el modelo del KPI
        $clientKpiModel = new ClientKpiModel();

        // Verificar si el KPI existe
        $clientKpi = $clientKpiModel->find($id_client_kpi);
        if (!$clientKpi) {
            // Si el KPI no existe, redirigir con un mensaje de error
            return redirect()->to('/listClientKpis')->with('error', 'El KPI no existe');
        }

        // Eliminar el KPI
        if ($clientKpiModel->delete($id_client_kpi)) {
            // Si la eliminación es exitosa, redirigir con un mensaje de éxito
            return redirect()->to('/listClientKpis')->with('success', 'KPI eliminado exitosamente');
        } else {
            // Si ocurre algún error durante la eliminación, redirigir con un mensaje de error
            return redirect()->to('/listClientKpis')->with('error', 'Error al eliminar el KPI');
        }
    }

    public function listClientKpis()
    {
        $clientKpiModel    = new ClientKpiModel();
        $clientModel       = new ClientModel();
        $kpiPolicyModel    = new KpiPolicyModel();
        $objectivesModel   = new ObjectivesPolicyModel();
        $kpisModel         = new KpisModel();
        $kpiTypeModel      = new KpiTypeModel();
        $kpiDefinitionModel = new KpiDefinitionModel();
        $dataOwnerModel    = new DataOwnerModel();
        $numeratorModel    = new VariableNumeratorModel();
        $denominatorModel  = new VariableDenominatorModel();

        // Obtener todos los clientes para el filtro
        $clientes = $clientModel->findAll();

        // Obtener el filtro enviado por GET (si existe)
        $id_cliente = $this->request->getGet('id_cliente');

        if (!empty($id_cliente)) {
            // Filtrar solo los KPIs del cliente seleccionado
            $clientKpis = $clientKpiModel->where('id_cliente', $id_cliente)->findAll();
        } else {
            // Si no se selecciona filtro, no se cargan datos en la tabla
            $clientKpis = [];
        }

        // Crear un array para almacenar los datos procesados
        $data = [];

        foreach ($clientKpis as $kpi) {
            // Obtener los nombres correspondientes a partir de los IDs
            $cliente    = $clientModel->find($kpi['id_cliente']);
            $kpiPolicy  = $kpiPolicyModel->find($kpi['id_kpi_policy']);
            $objective  = $objectivesModel->find($kpi['id_objectives']);
            $kpiData    = $kpisModel->find($kpi['id_kpis']);
            $kpiType    = $kpiTypeModel->find($kpi['id_kpi_type']);
            $kpiDefinition = $kpiDefinitionModel->find($kpi['id_kpi_definition']);
            $dataOwner  = $dataOwnerModel->find($kpi['id_data_owner']);

            // Variables para el cálculo del promedio
            $sumIndicadores = 0;
            $validIndicadores = 0;

            // Calcular el promedio solo con los indicadores válidos
            for ($i = 1; $i <= 12; $i++) {
                $indicador = $kpi['valor_indicador_' . $i];

                // Solo considerar indicadores válidos (numerador y denominador no deben ser cero)
                if ($kpi['dato_variable_numerador_' . $i] != 0 && $kpi['dato_variable_denominador_' . $i] != 0) {
                    $sumIndicadores += $indicador;
                    $validIndicadores++;
                }
            }

            // Calcular el promedio
            $promedioIndicadores = ($validIndicadores > 0) ? ($sumIndicadores / $validIndicadores) : 0;

            // Agregar los datos del KPI al arreglo de datos
            $data[] = [
                'id_client_kpi'       => $kpi['id_client_kpi'],
                'year'                => $kpi['year'],
                'month'               => $kpi['month'],
                'kpi_interpretation'  => $kpi['kpi_interpretation'],
                'cliente'             => $cliente['nombre_cliente'] ?? 'Cliente no encontrado',
                'kpi_policy'          => $kpiPolicy['policy_kpi_definition'] ?? 'Política no encontrada',
                'objective'           => $objective['name_objectives'] ?? 'Objetivo no encontrado',
                'kpi'                 => $kpiData['kpi_name'] ?? 'KPI no encontrado',
                'kpi_type'            => $kpiType['kpi_type'] ?? 'Tipo de KPI no encontrado',
                'kpi_definition'      => $kpiDefinition['name_kpi_definition'] ?? 'Definición no encontrada',
                'kpi_target'          => $kpi['kpi_target'],
                'data_source'         => $kpi['data_source'],
                'data_owner'          => isset($dataOwner['data_owner']) ? $dataOwner['data_owner'] : 'Sin responsable',
                'gran_total_indicador' => $kpi['gran_total_indicador'],
                'promedio_indicadores' => $promedioIndicadores * 100 // Promedio en formato de porcentaje
            ];
        }

        // Pasar los datos a la vista, incluyendo los clientes para el select y el filtro actual
        return view('consultant/list_client_kpis', [
            'clientKpis' => $data,
            'clientes' => $clientes,
            'selectedCliente' => $id_cliente
        ]);
    }





    public function listClientKpisFull()
    {
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

        // Obtener todos los KPIs del cliente
        $clientKpis = $clientKpiModel->findAll();

        // Crear un array para almacenar los datos procesados
        $data = [];

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
            $data[] = [
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
        return view('consultant/viewClientKpi', ['clientKpis' => $data]);
    }
}
