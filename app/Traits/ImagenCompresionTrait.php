<?php

namespace App\Traits;

/**
 * Trait compartido para comprimir fotos al subir y al generar PDFs.
 * Usado por TODOS los controllers de inspecciones que manejan fotos.
 * Portado desde enterprisesstph (PAW) - sesion 2026-03-13.
 */
trait ImagenCompresionTrait
{
    /**
     * Lee la orientacion EXIF de un JPEG sin depender de la extension exif.
     * Parsea los bytes APP1/EXIF del archivo directamente.
     * Retorna 1-8 (orientacion) o 1 si no se encuentra.
     */
    private function leerOrientacionJpeg(string $path): int
    {
        // Intentar con exif_read_data si esta disponible
        if (function_exists('exif_read_data')) {
            $exif = @exif_read_data($path);
            if ($exif && !empty($exif['Orientation'])) {
                return (int) $exif['Orientation'];
            }
            return 1;
        }

        // Fallback: parsear EXIF manualmente desde los bytes del JPEG
        $fp = @fopen($path, 'rb');
        if (!$fp) return 1;

        // Verificar SOI marker (FF D8)
        $soi = fread($fp, 2);
        if ($soi !== "\xFF\xD8") {
            fclose($fp);
            return 1;
        }

        // Buscar APP1 marker (FF E1) que contiene EXIF
        $orientation = 1;
        for ($i = 0; $i < 20; $i++) { // max 20 segmentos
            $marker = fread($fp, 2);
            if (strlen($marker) < 2 || $marker[0] !== "\xFF") break;

            $segLen = unpack('n', fread($fp, 2))[1];
            if ($segLen < 2) break;

            if ($marker[1] === "\xE1") { // APP1
                $exifData = fread($fp, min($segLen - 2, 65533));
                $orientation = $this->parsearOrientacionExif($exifData);
                break;
            }

            // Saltar este segmento
            fseek($fp, $segLen - 2, SEEK_CUR);
        }

        fclose($fp);
        return $orientation;
    }

    /**
     * Parsea el bloque APP1 EXIF para extraer la orientacion.
     */
    private function parsearOrientacionExif(string $data): int
    {
        // Debe empezar con "Exif\x00\x00"
        if (substr($data, 0, 6) !== "Exif\x00\x00") return 1;

        $tiff = substr($data, 6);
        if (strlen($tiff) < 8) return 1;

        // Byte order: II=little-endian, MM=big-endian
        $bo = substr($tiff, 0, 2);
        $le = ($bo === "II");

        // Tag 0x0112 = Orientation
        $ifdOffset = $le ? unpack('V', substr($tiff, 4, 4))[1] : unpack('N', substr($tiff, 4, 4))[1];
        if ($ifdOffset + 2 > strlen($tiff)) return 1;

        $numEntries = $le ? unpack('v', substr($tiff, $ifdOffset, 2))[1] : unpack('n', substr($tiff, $ifdOffset, 2))[1];

        for ($i = 0; $i < $numEntries; $i++) {
            $entryOffset = $ifdOffset + 2 + ($i * 12);
            if ($entryOffset + 12 > strlen($tiff)) break;

            $tag = $le ? unpack('v', substr($tiff, $entryOffset, 2))[1] : unpack('n', substr($tiff, $entryOffset, 2))[1];

            if ($tag === 0x0112) { // Orientation
                $val = $le ? unpack('v', substr($tiff, $entryOffset + 8, 2))[1] : unpack('n', substr($tiff, $entryOffset + 8, 2))[1];
                return ($val >= 1 && $val <= 8) ? $val : 1;
            }
        }

        return 1;
    }

    /**
     * Corrige la orientacion EXIF de una imagen GD.
     * Las fotos de celular guardan la rotacion como metadata EXIF,
     * pero GD ignora esta metadata al cargar la imagen.
     * No requiere la extension exif de PHP.
     */
    private function corregirOrientacionExif(string $path, $src)
    {
        $orientation = $this->leerOrientacionJpeg($path);

        switch ($orientation) {
            case 3: // 180
                $src = imagerotate($src, 180, 0);
                break;
            case 6: // 90 CW (celular en vertical, foto mas comun)
                $src = imagerotate($src, -90, 0);
                break;
            case 8: // 90 CCW
                $src = imagerotate($src, 90, 0);
                break;
        }

        return $src;
    }

    /**
     * Carga una imagen desde archivo y corrige su orientacion EXIF.
     * Retorna [resource $src, int $width, int $height] o null si falla.
     */
    private function cargarImagenConExif(string $path): ?array
    {
        $info = @getimagesize($path);
        if (!$info) return null;

        $mime = $info['mime'];

        $src = null;
        if ($mime === 'image/jpeg') {
            $src = @imagecreatefromjpeg($path);
        } elseif ($mime === 'image/png') {
            $src = @imagecreatefrompng($path);
        } elseif ($mime === 'image/webp' && function_exists('imagecreatefromwebp')) {
            $src = @imagecreatefromwebp($path);
        }
        if (!$src) return null;

        // Corregir orientacion EXIF (solo JPEG tiene EXIF)
        if ($mime === 'image/jpeg') {
            $src = $this->corregirOrientacionExif($path, $src);
        }

        // Despues de rotar, las dimensiones pueden haber cambiado
        $w = imagesx($src);
        $h = imagesy($src);

        return [$src, $w, $h];
    }

    /**
     * Comprime una imagen en disco: redimensiona a maxWidth y aplica quality JPEG.
     * Corrige orientacion EXIF automaticamente.
     * Llamar DESPUES de $file->move().
     */
    protected function comprimirImagen(string $path, int $maxWidth = 1200, int $quality = 70): void
    {
        $loaded = $this->cargarImagenConExif($path);
        if (!$loaded) return;

        [$src, $origW, $origH] = $loaded;

        if ($origW > $maxWidth) {
            $newW = $maxWidth;
            $newH = (int) round($origH * ($maxWidth / $origW));
        } else {
            $newW = $origW;
            $newH = $origH;
        }

        $dst = imagecreatetruecolor($newW, $newH);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
        imagejpeg($dst, $path, $quality);

        imagedestroy($src);
        imagedestroy($dst);
    }

    /**
     * Comprime una imagen en memoria y retorna JPEG binario (para base64 en PDFs).
     * Corrige orientacion EXIF automaticamente.
     * Reduce tamano drasticamente: 3MB foto -> ~80KB en PDF.
     */
    protected function comprimirParaPdf(string $path, int $maxWidth = 800, int $quality = 55): ?string
    {
        $loaded = $this->cargarImagenConExif($path);
        if (!$loaded) return null;

        [$src, $origW, $origH] = $loaded;

        if ($origW > $maxWidth) {
            $newW = $maxWidth;
            $newH = (int) round($origH * ($maxWidth / $origW));
        } else {
            $newW = $origW;
            $newH = $origH;
        }

        $dst = imagecreatetruecolor($newW, $newH);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

        ob_start();
        imagejpeg($dst, null, $quality);
        $data = ob_get_clean();

        imagedestroy($src);
        imagedestroy($dst);

        return $data;
    }

    /**
     * Convierte un archivo de imagen a base64 comprimido para PDF.
     * Reemplaza el patron: 'data:'.mime.';base64,'.base64_encode(file_get_contents(...))
     */
    protected function fotoABase64ParaPdf(string $path): string
    {
        $compressed = $this->comprimirParaPdf($path, 800, 55);
        if ($compressed) {
            return 'data:image/jpeg;base64,' . base64_encode($compressed);
        }
        return '';
    }
}
