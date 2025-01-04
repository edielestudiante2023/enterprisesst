<?php

namespace App\Controllers;

use App\Models\KpiPolicyModel;
use CodeIgniter\Controller;

class KpiPolicyController extends Controller
{
    // Listar todas las políticas de KPI
    public function listKpiPolicies()
    {
        $kpiPolicyModel = new KpiPolicyModel();
        $kpiPolicies = $kpiPolicyModel->findAll(); // Obtener todas las políticas

        return view('consultant/list_kpi_policies', ['kpiPolicies' => $kpiPolicies]);
    }

    // Mostrar el formulario para añadir una nueva política de KPI
    public function addKpiPolicy()
    {
        return view('consultant/add_kpi_policy');
    }

    // Procesar el formulario para añadir una nueva política de KPI
    public function addKpiPolicyPost()
    {
        $kpiPolicyModel = new KpiPolicyModel();

        $data = [
            'policy_kpi_definition' => $this->request->getVar('policy_kpi_definition'),
            'policy_kpi_comments' => $this->request->getVar('policy_kpi_comments')
        ];

        if ($kpiPolicyModel->save($data)) {
            return redirect()->to('/listKpiPolicies')->with('msg', 'Política de KPI agregada exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al agregar la política de KPI');
        }
    }

    // Mostrar el formulario para editar una política de KPI
    public function editKpiPolicy($id)
    {
        $kpiPolicyModel = new KpiPolicyModel();
        $kpiPolicy = $kpiPolicyModel->find($id);

        if (!$kpiPolicy) {
            return redirect()->to('/listKpiPolicies')->with('msg', 'Política de KPI no encontrada');
        }

        return view('consultant/edit_kpi_policy', ['kpiPolicy' => $kpiPolicy]);
    }

    // Procesar el formulario para actualizar una política de KPI
    public function editKpiPolicyPost($id)
    {
        $kpiPolicyModel = new KpiPolicyModel();

        $data = [
            'policy_kpi_definition' => $this->request->getVar('policy_kpi_definition'),
            'policy_kpi_comments' => $this->request->getVar('policy_kpi_comments')
        ];

        if ($kpiPolicyModel->update($id, $data)) {
            return redirect()->to('/listKpiPolicies')->with('msg', 'Política de KPI actualizada exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al actualizar la política de KPI');
        }
    }

    // Eliminar una política de KPI
    public function deleteKpiPolicy($id)
    {
        $kpiPolicyModel = new KpiPolicyModel();
        $kpiPolicyModel->delete($id);

        return redirect()->to('/listKpiPolicies')->with('msg', 'Política de KPI eliminada exitosamente');
    }
}
