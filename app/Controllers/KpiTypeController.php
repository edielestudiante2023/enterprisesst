<?php

namespace App\Controllers;

use App\Models\KpiTypeModel;
use CodeIgniter\Controller;

class KpiTypeController extends Controller
{
    // Muestra la lista de tipos de KPI
    public function listKpiTypes()
    {
        $model = new KpiTypeModel();
        $kpiTypes = $model->findAll(); // Obtener todos los tipos de KPI

        return view('consultant/list_kpi_types', ['kpiTypes' => $kpiTypes]);
    }

    // Muestra el formulario para añadir un nuevo tipo de KPI
    public function addKpiType()
    {
        return view('consultant/add_kpi_type');
    }

    // Procesa el formulario para añadir un nuevo tipo de KPI (método POST)
    public function addKpiTypePost()
    {
        $model = new KpiTypeModel();

        $data = [
            'kpi_type' => $this->request->getVar('kpi_type'),
            'kpi_type_comment' => $this->request->getVar('kpi_type_comment')
        ];

        if ($model->save($data)) {
            return redirect()->to('/listKpiTypes')->with('msg', 'Tipo de KPI agregado exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al agregar el tipo de KPI');
        }
    }

    // Muestra el formulario para editar un tipo de KPI
    public function editKpiType($id)
    {
        $model = new KpiTypeModel();
        $kpiType = $model->find($id);

        if (!$kpiType) {
            return redirect()->to('/listKpiTypes')->with('msg', 'Tipo de KPI no encontrado');
        }

        return view('consultant/edit_kpi_type', ['kpiType' => $kpiType]);
    }

    // Procesa el formulario para actualizar un tipo de KPI (método POST)
    public function editKpiTypePost($id)
    {
        $model = new KpiTypeModel();

        $data = [
            'kpi_type' => $this->request->getVar('kpi_type'),
            'kpi_type_comment' => $this->request->getVar('kpi_type_comment')
        ];

        if ($model->update($id, $data)) {
            return redirect()->to('/listKpiTypes')->with('msg', 'Tipo de KPI actualizado exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al actualizar el tipo de KPI');
        }
    }

    // Elimina un tipo de KPI
    public function deleteKpiType($id)
    {
        $model = new KpiTypeModel();
        $model->delete($id);

        return redirect()->to('/listKpiTypes')->with('msg', 'Tipo de KPI eliminado exitosamente');
    }
}
