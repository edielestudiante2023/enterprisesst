<?php

namespace App\Controllers;

use App\Models\KpisModel;
use CodeIgniter\Controller;

class KpisController extends Controller
{
    // Listar todos los KPIs
    public function listKpis()
    {
        $kpisModel = new KpisModel();
        $kpis = $kpisModel->findAll(); // Obtener todos los KPIs

        return view('consultant/list_kpis', ['kpis' => $kpis]);
    }

    // Mostrar el formulario para añadir un nuevo KPI
    public function addKpi()
    {
        return view('consultant/add_kpi');
    }

    // Procesar el formulario para añadir un nuevo KPI
    public function addKpiPost()
    {
        $kpisModel = new KpisModel();

        $data = [
            'kpi_name' => $this->request->getVar('kpi_name'),
            'hpi_comments' => $this->request->getVar('hpi_comments')
        ];

        if ($kpisModel->save($data)) {
            return redirect()->to('/listKpis')->with('msg', 'KPI agregado exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al agregar el KPI');
        }
    }

    // Mostrar el formulario para editar un KPI
    public function editKpi($id)
    {
        $kpisModel = new KpisModel();
        $kpi = $kpisModel->find($id);

        if (!$kpi) {
            return redirect()->to('/listKpis')->with('msg', 'KPI no encontrado');
        }

        return view('consultant/edit_kpi', ['kpi' => $kpi]);
    }

    // Procesar el formulario para actualizar un KPI
    public function editKpiPost($id)
    {
        $kpisModel = new KpisModel();

        $data = [
            'kpi_name' => $this->request->getVar('kpi_name'),
            'hpi_comments' => $this->request->getVar('hpi_comments')
        ];

        if ($kpisModel->update($id, $data)) {
            return redirect()->to('/listKpis')->with('msg', 'KPI actualizado exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al actualizar el KPI');
        }
    }

    // Eliminar un KPI
    public function deleteKpi($id)
    {
        $kpisModel = new KpisModel();
        $kpisModel->delete($id);

        return redirect()->to('/listKpis')->with('msg', 'KPI eliminado exitosamente');
    }
}
