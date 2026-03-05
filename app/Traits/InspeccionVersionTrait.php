<?php

namespace App\Traits;

use App\Services\DocumentoVersionService;
use CodeIgniter\Database\BaseConnection;

/**
 * Trait InspeccionVersionTrait
 *
 * Integra los módulos de inspección con el sistema centralizado de versionamiento SST.
 * Cuando se finaliza una inspección, este trait crea o actualiza un registro en
 * tbl_documentos_sst y genera la versión correspondiente en tbl_doc_versiones_sst.
 *
 * Modelo: (id_cliente + tipo_documento) = 1 documento SST.
 * Cada inspección completada agrega una nueva versión (1.0, 1.1, 2.0, …).
 *
 * Uso en el controlador:
 *   use App\Traits\InspeccionVersionTrait;
 *
 *   // Dentro de finalizar(), después de uploadToReportes():
 *   $idDoc = $this->registrarVersionDocumento(
 *       (int)$inspeccion['id_cliente'],
 *       'inspeccion_extintores',
 *       'Inspecciones de Extintores',
 *       json_encode($inspeccion),
 *       "Inspección realizada el {$inspeccion['fecha_inspeccion']}",
 *       'IE-' . $inspeccion['id_cliente'],
 *       (int)date('Y', strtotime($inspeccion['fecha_inspeccion']))
 *   );
 *   $this->inspeccionModel->update($id, ['id_documento_sst' => $idDoc]);
 */
trait InspeccionVersionTrait
{
    /**
     * Registra (o actualiza) el documento SST correspondiente a la inspección finalizada.
     *
     * @param int    $idCliente      ID del cliente
     * @param string $tipoDocumento  Slug del tipo (e.g. 'inspeccion_extintores')
     * @param string $tituloSerie    Título de la serie (e.g. 'Inspecciones de Extintores')
     * @param string $contenidoJson  JSON con los datos de la inspección (snapshot)
     * @param string $descripcion    Descripción del cambio (e.g. 'Inspección realizada el 2026-03-05')
     * @param string $codigo         Código del documento (e.g. 'IE-42')
     * @param int    $anio           Año de la inspección
     *
     * @return int  ID del documento en tbl_documentos_sst
     */
    protected function registrarVersionDocumento(
        int    $idCliente,
        string $tipoDocumento,
        string $tituloSerie,
        string $contenidoJson,
        string $descripcion,
        string $codigo,
        int    $anio
    ): int {
        $session      = session();
        $userId       = (int)($session->get('user_id') ?? 0);
        $userName     = $session->get('nombre') ?? 'Sistema';

        /** @var BaseConnection $db */
        $db = \Config\Database::connect();

        $versionService = new DocumentoVersionService();

        // Buscar si ya existe un documento SST para este cliente y tipo
        $docExistente = $db->table('tbl_documentos_sst')
            ->where('id_cliente', $idCliente)
            ->where('tipo_documento', $tipoDocumento)
            ->get()
            ->getRowArray();

        if (!$docExistente) {
            // Primera inspección: crear documento + versión 1.0
            $idDocumento = $db->table('tbl_documentos_sst')->insert([
                'id_cliente'    => $idCliente,
                'tipo_documento'=> $tipoDocumento,
                'titulo'        => $tituloSerie,
                'codigo'        => $codigo,
                'anio'          => $anio,
                'contenido'     => $contenidoJson,
                'estado'        => 'borrador',
                'version'       => null,
            ]);

            if (!$idDocumento) {
                // Fallback: no interrumpir el flujo principal si falla
                return 0;
            }

            $idDocumento = $db->insertID();

            $versionService->crearVersionInicial(
                $idDocumento,
                $userId,
                $userName,
                $descripcion
            );

            return $idDocumento;
        }

        // Documento existente: actualizar contenido y crear nueva versión
        $idDocumento = (int)$docExistente['id_documento'];

        // Preparar para nueva versión (borrador con motivo)
        $estadoActual = $docExistente['estado'];

        if (in_array($estadoActual, ['aprobado', 'pendiente_firma', 'firmado'])) {
            // Poner en borrador para permitir la nueva versión
            $db->table('tbl_documentos_sst')
                ->where('id_documento', $idDocumento)
                ->update([
                    'contenido'              => $contenidoJson,
                    'anio'                   => $anio,
                    'estado'                 => 'borrador',
                    'motivo_version'         => $descripcion,
                    'tipo_cambio_pendiente'  => 'menor',
                ]);
        } else {
            // Ya está en borrador (edge case): solo actualizar contenido
            $db->table('tbl_documentos_sst')
                ->where('id_documento', $idDocumento)
                ->update([
                    'contenido'             => $contenidoJson,
                    'anio'                  => $anio,
                    'motivo_version'        => $descripcion,
                    'tipo_cambio_pendiente' => 'menor',
                ]);
        }

        $versionService->aprobarVersion(
            $idDocumento,
            $userId,
            $userName,
            $descripcion,
            'menor'
        );

        return $idDocumento;
    }
}
