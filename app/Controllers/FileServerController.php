<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

/**
 * FileServerController
 *
 * Sirve archivos desde UPLOADS_PATH (fuera de public/).
 * Reemplaza el acceso directo que antes tenían en public/uploads/.
 *
 * Ruta: GET /serve-file/(:any) → FileServerController::serve/$1
 */
class FileServerController extends BaseController
{
    /**
     * Sirve un archivo desde UPLOADS_PATH.
     *
     * @param string ...$segments Segmentos de ruta (ej: "901103223", "archivo.pdf")
     */
    public function serve(string ...$segments): ResponseInterface
    {
        // Verificar sesión activa
        $session = session();
        if (!$session->get('isLoggedIn') && !$session->get('client_logged_in')) {
            return redirect()->to('/login');
        }

        // Reconstruir ruta relativa
        $relativePath = implode('/', $segments);

        // Sanitizar: prevenir directory traversal
        $relativePath = str_replace(['..', "\0"], '', $relativePath);
        $relativePath = ltrim($relativePath, '/');

        if (empty($relativePath)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Construir ruta absoluta
        $filePath = rtrim(UPLOADS_PATH, '/\\') . '/' . $relativePath;

        // Verificar que el archivo existe
        if (!is_file($filePath)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(
                'Archivo no encontrado: ' . $relativePath
            );
        }

        // Detectar MIME type
        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';

        // Para PDFs e imágenes: mostrar inline. Para otros: descargar.
        $inlineTypes = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
        ];

        $disposition = in_array($mimeType, $inlineTypes) ? 'inline' : 'attachment';
        $fileName = basename($filePath);

        return $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setHeader('Content-Disposition', $disposition . '; filename="' . $fileName . '"')
            ->setHeader('Content-Length', (string) filesize($filePath))
            ->setHeader('Cache-Control', 'private, max-age=3600')
            ->setBody(file_get_contents($filePath));
    }
}
