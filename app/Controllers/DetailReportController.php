<?php

namespace App\Controllers;

use App\Models\DetailReportModel;
use CodeIgniter\Controller;

class DetailReportController extends Controller
{
    // Listar todos los registros
    public function detailReportList()
    {
        $model = new DetailReportModel();
        $data['detailReports'] = $model->findAll();
        return view('consultant/detailreportlist', $data);
    }

    // Mostrar el formulario para agregar un nuevo registro
    public function detailReportAdd()
    {
        return view('consultant/detailreportadd');
    }

    // Procesar el formulario de agregar
    public function detailReportAddPost()
    {
        $model = new DetailReportModel();

        // Validación
        if (!$this->validate([
            'detail_report' => 'required|max_length[255]'
        ])) {
            return view('consultant/detailreportadd', [
                'validation' => $this->validator
            ]);
        }

        // Guardar datos
        $model->save([
            'detail_report' => $this->request->getPost('detail_report')
        ]);

        return redirect()->to('/detailreportlist')->with('success', 'Detalle de reporte agregado exitosamente.');
    }

    // Mostrar el formulario para editar un registro existente
    public function detailReportEdit($id = null)
    {
        $model = new DetailReportModel();
        $data['detailReport'] = $model->find($id);

        if (!$data['detailReport']) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Detalle de reporte no encontrado');
        }

        return view('consultant/detailreportedit', $data);
    }

    // Procesar el formulario de edición
    public function detailReportEditPost()
    {
        $model = new DetailReportModel();
        $id = $this->request->getPost('id_detailreport');

        // Validación
        if (!$this->validate([
            'detail_report' => 'required|max_length[255]'
        ])) {
            return view('consultant/detailreportedit', [
                'validation' => $this->validator,
                'detailReport' => [
                    'id_detailreport' => $id,
                    'detail_report' => $this->request->getPost('detail_report')
                ]
            ]);
        }

        // Actualizar datos
        $model->update($id, [
            'detail_report' => $this->request->getPost('detail_report')
        ]);

        return redirect()->to('/detailreportlist')->with('success', 'Detalle de reporte actualizado exitosamente.');
    }

    // Eliminar un registro
    public function detailReportDelete($id = null)
    {
        $model = new DetailReportModel();
        $model->delete($id);
        return redirect()->to('/detailreportlist')->with('success', 'Detalle de reporte eliminado exitosamente.');
    }
}
