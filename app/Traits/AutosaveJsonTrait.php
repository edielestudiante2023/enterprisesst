<?php

namespace App\Traits;

trait AutosaveJsonTrait
{
    protected function isAutosaveRequest(): bool
    {
        return $this->request->isAJAX()
            || $this->request->getHeaderLine('X-Autosave') === '1';
    }

    protected function autosaveJsonSuccess(int $id, array $extra = [])
    {
        return $this->response->setJSON(array_merge([
            'success'  => true,
            'id'       => $id,
            'saved_at' => date('H:i:s'),
        ], $extra));
    }

    protected function autosaveJsonError(string $message, int $statusCode = 400)
    {
        return $this->response->setJSON([
            'success' => false,
            'message' => $message,
        ])->setStatusCode($statusCode);
    }
}
