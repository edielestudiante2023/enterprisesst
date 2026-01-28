<?php

namespace App\Controllers;

use Config\Database;
use CodeIgniter\Controller;

/**
 * Controlador temporal para ejecutar SQL de migracion
 * ELIMINAR DESPUES DE USAR
 */
class SqlRunnerController extends Controller
{
    public function insertarPlantillasResponsabilidades()
    {
        $db = Database::connect();

        try {
            // Verificar que existe la carpeta padre "Planear"
            $carpetaPlanear = $db->table('tbl_doc_carpetas')
                ->where('nombre', 'Planear')
                ->get()
                ->getRowArray();

            if (!$carpetaPlanear) {
                $db->table('tbl_doc_carpetas')->insert([
                    'nombre' => 'Planear',
                    'descripcion' => 'Documentos de la fase PLANEAR del ciclo PHVA',
                    'orden' => 1,
                    'padre_id' => null,
                    'activo' => 1,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                $idCarpetaPlanear = $db->insertID();
            } else {
                $idCarpetaPlanear = $carpetaPlanear['id_carpeta'];
            }

            // Verificar subcarpeta "Recursos"
            $carpetaRecursos = $db->table('tbl_doc_carpetas')
                ->where('nombre', 'Recursos')
                ->where('padre_id', $idCarpetaPlanear)
                ->get()
                ->getRowArray();

            if (!$carpetaRecursos) {
                $db->table('tbl_doc_carpetas')->insert([
                    'nombre' => 'Recursos',
                    'descripcion' => 'Gestion de recursos del SG-SST',
                    'orden' => 1,
                    'padre_id' => $idCarpetaPlanear,
                    'activo' => 1,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                $idCarpetaRecursos = $db->insertID();
            } else {
                $idCarpetaRecursos = $carpetaRecursos['id_carpeta'];
            }

            $plantillas = [
                [
                    'tipo_documento' => 'responsabilidades_rep_legal_sgsst',
                    'nombre' => 'Responsabilidades del Representante Legal en el SG-SST',
                    'descripcion' => 'Documento que establece las responsabilidades del Representante Legal segun Decreto 1072/2015 Art. 2.2.4.6.8. Requiere firma digital del Rep. Legal.',
                    'id_carpeta' => $idCarpetaRecursos,
                    'estandares_aplicables' => '7,21,60',
                    'patron_generacion' => 'B',
                    'requiere_firma_digital' => 1,
                    'firmante_tipo' => 'representante_legal',
                    'orden' => 2,
                    'activo' => 1,
                    'created_at' => date('Y-m-d H:i:s')
                ],
                [
                    'tipo_documento' => 'responsabilidades_responsable_sgsst',
                    'nombre' => 'Responsabilidades del Responsable del SG-SST',
                    'descripcion' => 'Documento que establece las responsabilidades del profesional responsable del SG-SST. Usa firma automatica del consultor desde su perfil.',
                    'id_carpeta' => $idCarpetaRecursos,
                    'estandares_aplicables' => '7,21,60',
                    'patron_generacion' => 'B',
                    'requiere_firma_digital' => 0,
                    'firmante_tipo' => 'consultor_sst',
                    'orden' => 3,
                    'activo' => 1,
                    'created_at' => date('Y-m-d H:i:s')
                ],
                [
                    'tipo_documento' => 'responsabilidades_trabajadores_sgsst',
                    'nombre' => 'Responsabilidades de Trabajadores y Contratistas en el SG-SST',
                    'descripcion' => 'Documento con responsabilidades de trabajadores segun Decreto 1072/2015 Art. 2.2.4.6.10. FORMATO IMPRIMIBLE con hoja de firmas para induccion.',
                    'id_carpeta' => $idCarpetaRecursos,
                    'estandares_aplicables' => '7,21,60',
                    'patron_generacion' => 'B',
                    'requiere_firma_digital' => 0,
                    'firmante_tipo' => 'firma_fisica',
                    'orden' => 4,
                    'activo' => 1,
                    'created_at' => date('Y-m-d H:i:s')
                ],
                [
                    'tipo_documento' => 'responsabilidades_vigia_sgsst',
                    'nombre' => 'Responsabilidades del Vigia de Seguridad y Salud en el Trabajo',
                    'descripcion' => 'Documento exclusivo para empresas con 7 estandares (<10 trabajadores, Riesgo I-III). Establece responsabilidades del Vigia SST. Requiere firma digital del Vigia.',
                    'id_carpeta' => $idCarpetaRecursos,
                    'estandares_aplicables' => '7',
                    'patron_generacion' => 'B',
                    'requiere_firma_digital' => 1,
                    'firmante_tipo' => 'vigia_sst',
                    'orden' => 5,
                    'activo' => 1,
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ];

            $insertados = 0;
            $actualizados = 0;

            foreach ($plantillas as $plantilla) {
                $existe = $db->table('tbl_doc_plantillas_sst')
                    ->where('tipo_documento', $plantilla['tipo_documento'])
                    ->get()
                    ->getRowArray();

                if ($existe) {
                    $db->table('tbl_doc_plantillas_sst')
                        ->where('tipo_documento', $plantilla['tipo_documento'])
                        ->update([
                            'nombre' => $plantilla['nombre'],
                            'descripcion' => $plantilla['descripcion'],
                            'estandares_aplicables' => $plantilla['estandares_aplicables'],
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                    $actualizados++;
                } else {
                    $db->table('tbl_doc_plantillas_sst')->insert($plantilla);
                    $insertados++;
                }
            }

            // Verificar resultado
            $resultados = $db->table('tbl_doc_plantillas_sst')
                ->where('tipo_documento LIKE', 'responsabilidades_%')
                ->orderBy('orden', 'ASC')
                ->get()
                ->getResultArray();

            return $this->response->setJSON([
                'success' => true,
                'message' => "Plantillas procesadas: {$insertados} insertadas, {$actualizados} actualizadas",
                'carpeta_planear_id' => $idCarpetaPlanear,
                'carpeta_recursos_id' => $idCarpetaRecursos,
                'plantillas' => $resultados
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
}
