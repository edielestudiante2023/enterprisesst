<?php

namespace App\Controllers;

use App\Models\MeasurementPeriodModel;
use CodeIgniter\Controller;

class MeasurementPeriodController extends Controller
{
    // Listar todos los periodos de medición
    public function listMeasurementPeriods()
    {
        $periodModel = new MeasurementPeriodModel();
        $measurementPeriods = $periodModel->findAll(); // Obtener todos los periodos de medición

        return view('consultant/list_measurement_periods', ['measurementPeriods' => $measurementPeriods]);
    }

    // Mostrar el formulario para añadir un nuevo periodo de medición
    public function addMeasurementPeriod()
    {
        return view('consultant/add_measurement_period');
    }

    // Procesar el formulario para añadir un nuevo periodo de medición
    public function addMeasurementPeriodPost()
    {
        $periodModel = new MeasurementPeriodModel();

        $data = [
            'measurement_period' => $this->request->getVar('measurement_period'),
            'comment_measurement_period' => $this->request->getVar('comment_measurement_period')
        ];

        if ($periodModel->save($data)) {
            return redirect()->to('/listMeasurementPeriods')->with('msg', 'Periodo de Medición agregado exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al agregar el Periodo de Medición');
        }
    }

    // Mostrar el formulario para editar un periodo de medición
    public function editMeasurementPeriod($id)
    {
        $periodModel = new MeasurementPeriodModel();
        $measurementPeriod = $periodModel->find($id);

        if (!$measurementPeriod) {
            return redirect()->to('/listMeasurementPeriods')->with('msg', 'Periodo de Medición no encontrado');
        }

        return view('consultant/edit_measurement_period', ['measurementPeriod' => $measurementPeriod]);
    }

    // Procesar el formulario para actualizar un periodo de medición
    public function editMeasurementPeriodPost($id)
    {
        $periodModel = new MeasurementPeriodModel();

        $data = [
            'measurement_period' => $this->request->getVar('measurement_period'),
            'comment_measurement_period' => $this->request->getVar('comment_measurement_period')
        ];

        if ($periodModel->update($id, $data)) {
            return redirect()->to('/listMeasurementPeriods')->with('msg', 'Periodo de Medición actualizado exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al actualizar el Periodo de Medición');
        }
    }

    // Eliminar un periodo de medición
    public function deleteMeasurementPeriod($id)
    {
        $periodModel = new MeasurementPeriodModel();
        $periodModel->delete($id);

        return redirect()->to('/listMeasurementPeriods')->with('msg', 'Periodo de Medición eliminado exitosamente');
    }
}
