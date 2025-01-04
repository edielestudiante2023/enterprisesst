<?php

namespace App\Controllers;

use App\Models\KpiDefinitionModel;
use CodeIgniter\Controller;

class KpiDefinitionController extends Controller
{
    // Listar todas las definiciones de KPI
    public function listKpiDefinitions()
    {
        $kpiDefinitionModel = new KpiDefinitionModel();
        $kpiDefinitions = $kpiDefinitionModel->findAll(); // Obtener todas las definiciones

        return view('consultant/list_kpi_definitions', ['kpiDefinitions' => $kpiDefinitions]);
    }

    // Mostrar el formulario para añadir una nueva definición de KPI
    public function addKpiDefinition()
    {
        return view('consultant/add_kpi_definition');
    }

    // Procesar el formulario para añadir una nueva definición de KPI
    public function addKpiDefinitionPost()
    {
        $kpiDefinitionModel = new KpiDefinitionModel();

        $data = [
            'name_kpi_definition' => $this->request->getVar('name_kpi_definition'),
            'comment_kpi_definition' => $this->request->getVar('comment_kpi_definition')
        ];

        if ($kpiDefinitionModel->save($data)) {
            return redirect()->to('/listKpiDefinitions')->with('msg', 'Definición de KPI agregada exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al agregar la definición de KPI');
        }
    }

    // Mostrar el formulario para editar una definición de KPI
    public function editKpiDefinition($id)
    {
        $kpiDefinitionModel = new KpiDefinitionModel();
        $kpiDefinition = $kpiDefinitionModel->find($id);

        if (!$kpiDefinition) {
            return redirect()->to('/listKpiDefinitions')->with('msg', 'Definición no encontrada');
        }

        return view('consultant/edit_kpi_definition', ['kpiDefinition' => $kpiDefinition]);
    }

    // Procesar el formulario para actualizar una definición de KPI
    public function editKpiDefinitionPost($id)
    {
        $kpiDefinitionModel = new KpiDefinitionModel();

        $data = [
            'name_kpi_definition' => $this->request->getVar('name_kpi_definition'),
            'comment_kpi_definition' => $this->request->getVar('comment_kpi_definition')
        ];

        if ($kpiDefinitionModel->update($id, $data)) {
            return redirect()->to('/listKpiDefinitions')->with('msg', 'Definición de KPI actualizada exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al actualizar la definición de KPI');
        }
    }

    // Eliminar una definición de KPI
    public function deleteKpiDefinition($id)
    {
        $kpiDefinitionModel = new KpiDefinitionModel();
        $kpiDefinitionModel->delete($id);

        return redirect()->to('/listKpiDefinitions')->with('msg', 'Definición de KPI eliminada exitosamente');
    }
}
