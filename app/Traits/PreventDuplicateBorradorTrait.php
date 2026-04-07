<?php

namespace App\Traits;

/**
 * Previene duplicados de borradores en inspecciones.
 * Si ya existe un borrador para el mismo cliente + fecha + consultor,
 * redirige al existente en vez de crear uno nuevo.
 *
 * Uso en store():
 *   $existing = $this->reuseExistingBorrador($this->model, 'fecha_inspeccion', '/inspecciones/tipo/edit/');
 *   if ($existing) return $existing;
 */
trait PreventDuplicateBorradorTrait
{
    protected function reuseExistingBorrador($model, string $dateField, string $editUrlBase)
    {
        $idCliente  = $this->request->getPost('id_cliente');
        $fecha      = $this->request->getPost($dateField);
        $idConsultor = session()->get('user_id');

        if (!$idCliente || !$fecha) {
            return null;
        }

        $existing = $model->where('id_cliente', $idCliente)
            ->where($dateField, $fecha)
            ->where('id_consultor', $idConsultor)
            ->whereIn('estado', ['borrador', 'pendiente_firma'])
            ->first();

        if (!$existing) {
            return null;
        }

        $existingId = $existing['id'] ?? null;
        if (!$existingId) {
            $pk = $model->primaryKey ?? 'id';
            $existingId = $existing[$pk] ?? null;
        }

        if (!$existingId) {
            return null;
        }

        if ($this->isAutosaveRequest()) {
            return $this->response->setJSON([
                'success'  => true,
                'id'       => $existingId,
                'saved_at' => date('H:i:s'),
                'reused'   => true,
            ]);
        }

        return redirect()->to($editUrlBase . $existingId)
            ->with('msg', 'Ya existe un borrador para este cliente y fecha. Continuando edicion.');
    }
}
