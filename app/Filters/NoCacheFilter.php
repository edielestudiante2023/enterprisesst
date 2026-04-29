<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * NoCacheFilter
 *
 * Agrega cabeceras Cache-Control: no-store... a las respuestas HTML dinamicas
 * para evitar que el navegador sirva versiones cacheadas (causa de bugs como
 * "el form muestra 0 asistentes cuando la BD tiene 7" o "el dashboard muestra
 * conteos viejos").
 *
 * Solo aplica a respuestas con Content-Type text/html. PDFs, imagenes, JSON
 * y demas se dejan cachear normalmente para no perder performance.
 */
class NoCacheFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $contentType = (string) $response->getHeaderLine('Content-Type');
        // Solo HTML dinamico
        if ($contentType !== '' && stripos($contentType, 'text/html') === false) {
            return null;
        }

        $response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->setHeader('Pragma', 'no-cache');
        $response->setHeader('Expires', '0');
        return null;
    }
}
