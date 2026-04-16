<?php

namespace App\Services;

/**
 * Normaliza fotos del catálogo maestro EPP a 400x400 JPG calidad 75.
 *
 * Acepta JPG o PNG. Usa GD nativo. Rechaza < 200px o > 2 MB.
 */
class MatrizEppFotoService
{
    public const LADO      = 400;
    public const CALIDAD   = 75;
    public const MIN_LADO  = 200;
    public const MAX_BYTES = 2097152; // 2 MB
    public const DIR       = FCPATH . 'uploads/epp_maestro/';

    public function guardar(int $idEpp, string $tmpPath, string $mime, int $size): array
    {
        if ($size <= 0 || $size > self::MAX_BYTES) {
            return ['ok' => false, 'error' => 'Peso del archivo fuera de rango (maximo 2 MB)'];
        }
        if (!in_array($mime, ['image/jpeg', 'image/jpg', 'image/png'], true)) {
            return ['ok' => false, 'error' => 'Formato no admitido. Usa JPG o PNG'];
        }

        $info = @getimagesize($tmpPath);
        if (!$info) {
            return ['ok' => false, 'error' => 'No se pudo leer la imagen'];
        }
        [$w, $h] = $info;
        if ($w < self::MIN_LADO || $h < self::MIN_LADO) {
            return ['ok' => false, 'error' => 'Imagen demasiado pequena (minimo ' . self::MIN_LADO . 'x' . self::MIN_LADO . ' px)'];
        }

        if (!function_exists('imagecreatefromjpeg')) {
            return ['ok' => false, 'error' => 'Libreria GD no disponible en el servidor'];
        }

        $src = $mime === 'image/png'
            ? @imagecreatefrompng($tmpPath)
            : @imagecreatefromjpeg($tmpPath);
        if (!$src) {
            return ['ok' => false, 'error' => 'No se pudo decodificar la imagen'];
        }

        // Recorte cuadrado centrado
        $lado = min($w, $h);
        $srcX = (int)(($w - $lado) / 2);
        $srcY = (int)(($h - $lado) / 2);

        $dst = imagecreatetruecolor(self::LADO, self::LADO);
        // Fondo blanco (en caso de PNG transparente)
        $blanco = imagecolorallocate($dst, 255, 255, 255);
        imagefilledrectangle($dst, 0, 0, self::LADO, self::LADO, $blanco);

        imagecopyresampled(
            $dst, $src,
            0, 0, $srcX, $srcY,
            self::LADO, self::LADO, $lado, $lado
        );

        if (!is_dir(self::DIR)) {
            @mkdir(self::DIR, 0775, true);
        }
        $destino = self::DIR . $idEpp . '.jpg';
        $ok = imagejpeg($dst, $destino, self::CALIDAD);

        imagedestroy($src);
        imagedestroy($dst);

        if (!$ok) {
            return ['ok' => false, 'error' => 'No se pudo guardar la imagen'];
        }

        return [
            'ok' => true,
            'path' => 'uploads/epp_maestro/' . $idEpp . '.jpg',
        ];
    }

    public function eliminar(int $idEpp): void
    {
        $f = self::DIR . $idEpp . '.jpg';
        if (is_file($f)) {
            @unlink($f);
        }
    }
}
