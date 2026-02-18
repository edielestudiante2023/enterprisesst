<?php

namespace App\Controllers;

use App\Models\DocSeccionesConfigModel;
use CodeIgniter\Controller;

class DocSeccionesConfigController extends Controller
{
    protected DocSeccionesConfigModel $model;

    public function __construct()
    {
        $this->model = new DocSeccionesConfigModel();
    }

    public function index()
    {
        $data['secciones'] = $this->model->getConTipoConfig();
        return view('admin/secciones_config/index', $data);
    }

    public function create()
    {
        $data['tipos_config'] = $this->model->getTiposConfig();
        return view('admin/secciones_config/create', $data);
    }

    public function store()
    {
        $post = $this->request->getPost();

        $this->model->insert([
            'id_tipo_config'     => $post['id_tipo_config'],
            'numero'             => $post['numero'],
            'nombre'             => $post['nombre'],
            'seccion_key'        => $post['seccion_key'],
            'prompt_ia'          => $post['prompt_ia'] ?? null,
            'tipo_contenido'     => $post['tipo_contenido'],
            'tabla_dinamica_tipo'=> $post['tabla_dinamica_tipo'] ?? null,
            'es_obligatoria'     => isset($post['es_obligatoria']) ? 1 : 0,
            'orden'              => $post['orden'],
            'activo'             => isset($post['activo']) ? 1 : 0,
        ]);

        return redirect()->to(site_url('listSeccionesConfig'))
            ->with('success', 'Sección creada correctamente.');
    }

    public function edit($id)
    {
        $seccion = $this->model->find($id);
        if (!$seccion) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Sección #$id no encontrada");
        }

        $data['seccion']      = $seccion;
        $data['tipos_config'] = $this->model->getTiposConfig();
        return view('admin/secciones_config/edit', $data);
    }

    public function update($id)
    {
        $seccion = $this->model->find($id);
        if (!$seccion) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Sección #$id no encontrada");
        }

        $post = $this->request->getPost();

        $this->model->update($id, [
            'id_tipo_config'     => $post['id_tipo_config'],
            'numero'             => $post['numero'],
            'nombre'             => $post['nombre'],
            'seccion_key'        => $post['seccion_key'],
            'prompt_ia'          => $post['prompt_ia'] ?? null,
            'tipo_contenido'     => $post['tipo_contenido'],
            'tabla_dinamica_tipo'=> $post['tabla_dinamica_tipo'] ?? null,
            'es_obligatoria'     => isset($post['es_obligatoria']) ? 1 : 0,
            'orden'              => $post['orden'],
            'activo'             => isset($post['activo']) ? 1 : 0,
        ]);

        return redirect()->to(site_url('listSeccionesConfig'))
            ->with('success', 'Sección actualizada correctamente.');
    }

    public function delete($id)
    {
        $seccion = $this->model->find($id);
        if (!$seccion) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException("Sección #$id no encontrada");
        }

        $this->model->delete($id);

        return redirect()->to(site_url('listSeccionesConfig'))
            ->with('success', 'Sección eliminada correctamente.');
    }
}
