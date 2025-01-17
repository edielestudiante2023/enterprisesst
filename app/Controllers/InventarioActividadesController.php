<?php

namespace App\Controllers;

use App\Models\InventarioActividadesArrayModel;
use CodeIgniter\Controller;

class InventarioActividadesController extends Controller
{
    // Listar actividades
    public function listinventarioactividades()
    {
        $model = new InventarioActividadesArrayModel();
        $actividades = $model->findAll();

        return view('consultant/listinventarioactividades', ['actividades' => $actividades]);
    }

    // Formulario para añadir una actividad
    public function addinventarioactividades()
    {
        return view('consultant/addinventarioactividades');
    }

    // Procesar la adición de una actividad
    public function addpostinventarioactividades()
    {
        $model = new InventarioActividadesArrayModel();

        $data = [
            'phva_plandetrabajo' => $this->request->getVar('phva_plandetrabajo'),
            'numeral_plandetrabajo' => $this->request->getVar('numeral_plandetrabajo'),
            'actividad_plandetrabajo' => $this->request->getVar('actividad_plandetrabajo'),
            'responsable_sugerido_plandetrabajo' => $this->request->getVar('responsable_sugerido_plandetrabajo'),
        ];

        if ($model->save($data)) {
            return redirect()->to('/listinventarioactividades')->with('success', 'Actividad añadida correctamente.');
        } else {
            return redirect()->back()->with('error', 'Error al añadir la actividad.');
        }
    }

    // Formulario para editar una actividad
    public function editinventarioactividades($id)
    {
        $model = new InventarioActividadesArrayModel();
        $actividad = $model->find($id);

        if (!$actividad) {
            return redirect()->to('/listinventarioactividades')->with('error', 'Actividad no encontrada.');
        }

        return view('consultant/editinventarioactividades', ['actividad' => $actividad]);
    }

    // Procesar la edición de una actividad
    public function editpostinventarioactividades($id)
    {
        $model = new InventarioActividadesArrayModel();

        $data = [
            'phva_plandetrabajo' => $this->request->getVar('phva_plandetrabajo'),
            'numeral_plandetrabajo' => $this->request->getVar('numeral_plandetrabajo'),
            'actividad_plandetrabajo' => $this->request->getVar('actividad_plandetrabajo'),
            'responsable_sugerido_plandetrabajo' => $this->request->getVar('responsable_sugerido_plandetrabajo'),
        ];

        if ($model->update($id, $data)) {
            return redirect()->to('/listinventarioactividades')->with('success', 'Actividad actualizada correctamente.');
        } else {
            return redirect()->back()->with('error', 'Error al actualizar la actividad.');
        }
    }

    // Eliminar una actividad
    public function deleteinventarioactividades($id)
    {
        $model = new InventarioActividadesArrayModel();

        if ($model->delete($id)) {
            return redirect()->to('/listinventarioactividades')->with('success', 'Actividad eliminada correctamente.');
        } else {
            return redirect()->to('/listinventarioactividades')->with('error', 'Error al eliminar la actividad.');
        }
    }
}