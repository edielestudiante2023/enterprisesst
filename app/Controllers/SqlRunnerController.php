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

    /**
     * Diagnosticar presupuesto por cliente y año
     * Ejecutar via: /sql-runner/diagnostico-presupuesto/11/2026
     */
    public function diagnosticoPresupuesto($idCliente, $anio)
    {
        $db = Database::connect();

        // Buscar documento de presupuesto
        $documento = $db->table('tbl_documentos_sst')
            ->where('id_cliente', $idCliente)
            ->where('tipo_documento', 'presupuesto_sst')
            ->where('anio', $anio)
            ->get()->getRowArray();

        if (!$documento) {
            return $this->response->setJSON([
                'success' => false,
                'message' => "No se encontró documento de presupuesto para cliente {$idCliente} año {$anio}"
            ]);
        }

        // Buscar presupuesto en tabla propia
        $presupuesto = $db->table('tbl_presupuesto_sst')
            ->where('id_cliente', $idCliente)
            ->where('anio', $anio)
            ->get()->getRowArray();

        // Buscar solicitudes de firma
        $solicitudes = $db->table('tbl_doc_firma_solicitudes')
            ->where('id_documento', $documento['id_documento'])
            ->orderBy('orden_firma', 'ASC')
            ->get()->getResultArray();

        $firmados = 0;
        $pendientes = 0;
        foreach ($solicitudes as $sol) {
            if ($sol['estado'] === 'firmado') $firmados++;
            elseif (in_array($sol['estado'], ['pendiente', 'esperando'])) $pendientes++;
        }

        $todasFirmadas = ($firmados === count($solicitudes) && count($solicitudes) > 0);

        return $this->response->setJSON([
            'success' => true,
            'documento' => [
                'id_documento' => $documento['id_documento'],
                'codigo' => $documento['codigo'],
                'estado' => $documento['estado'],
                'version' => $documento['version']
            ],
            'presupuesto' => [
                'id_presupuesto' => $presupuesto['id_presupuesto'] ?? null,
                'estado' => $presupuesto['estado'] ?? null
            ],
            'firmas' => [
                'total' => count($solicitudes),
                'firmadas' => $firmados,
                'pendientes' => $pendientes,
                'todas_firmadas' => $todasFirmadas
            ],
            'solicitudes' => $solicitudes,
            'problema' => $todasFirmadas && $documento['estado'] !== 'firmado'
                ? 'ENCONTRADO: Todas las firmas están completas pero el documento no está en estado firmado'
                : ($documento['estado'] === 'firmado' ? 'Sin problemas - documento firmado' : 'Firmas pendientes'),
            'solucion' => $todasFirmadas && $documento['estado'] !== 'firmado'
                ? "Ejecutar: /sql-runner/forzar-firmado/{$documento['id_documento']}"
                : null
        ]);
    }

    /**
     * Diagnosticar estado de firmas de un documento
     * Ejecutar via: /sql-runner/diagnostico-firmas/14
     */
    public function diagnosticoFirmas($idDocumento)
    {
        $db = Database::connect();

        // Obtener documento
        $documento = $db->table('tbl_documentos_sst')
            ->where('id_documento', $idDocumento)
            ->get()->getRowArray();

        // Obtener solicitudes de firma
        $solicitudes = $db->table('tbl_doc_firma_solicitudes')
            ->where('id_documento', $idDocumento)
            ->orderBy('orden_firma', 'ASC')
            ->get()->getResultArray();

        // Contar estados
        $pendientes = 0;
        $firmados = 0;
        $esperando = 0;
        $otros = 0;

        foreach ($solicitudes as $sol) {
            switch ($sol['estado']) {
                case 'firmado': $firmados++; break;
                case 'pendiente': $pendientes++; break;
                case 'esperando': $esperando++; break;
                default: $otros++; break;
            }
        }

        $todasFirmadas = ($firmados === count($solicitudes) && count($solicitudes) > 0);

        return $this->response->setJSON([
            'success' => true,
            'documento' => [
                'id' => $idDocumento,
                'codigo' => $documento['codigo'] ?? 'N/A',
                'estado_actual' => $documento['estado'] ?? 'N/A',
                'tipo' => $documento['tipo_documento'] ?? 'N/A'
            ],
            'solicitudes' => $solicitudes,
            'resumen' => [
                'total' => count($solicitudes),
                'firmados' => $firmados,
                'pendientes' => $pendientes,
                'esperando' => $esperando,
                'otros' => $otros
            ],
            'todas_firmadas' => $todasFirmadas,
            'accion_recomendada' => $todasFirmadas && $documento['estado'] !== 'firmado'
                ? 'El documento debería estar en estado "firmado". Ejecuta /sql-runner/forzar-firmado/' . $idDocumento
                : 'Estado correcto'
        ]);
    }

    /**
     * Forzar estado firmado si todas las solicitudes están firmadas
     * Ejecutar via: /sql-runner/forzar-firmado/14
     */
    public function forzarFirmado($idDocumento)
    {
        $db = Database::connect();

        // Verificar que todas las solicitudes estén firmadas
        $solicitudes = $db->table('tbl_doc_firma_solicitudes')
            ->where('id_documento', $idDocumento)
            ->get()->getResultArray();

        if (empty($solicitudes)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No hay solicitudes de firma para este documento'
            ]);
        }

        $todasFirmadas = true;
        foreach ($solicitudes as $sol) {
            if ($sol['estado'] !== 'firmado') {
                $todasFirmadas = false;
                break;
            }
        }

        if (!$todasFirmadas) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No todas las solicitudes están firmadas',
                'solicitudes' => $solicitudes
            ]);
        }

        // Actualizar estado del documento
        $db->table('tbl_documentos_sst')
            ->where('id_documento', $idDocumento)
            ->update([
                'estado' => 'firmado',
                'updated_at' => date('Y-m-d H:i:s')
            ]);

        // También actualizar el presupuesto si aplica
        $documento = $db->table('tbl_documentos_sst')
            ->where('id_documento', $idDocumento)
            ->get()->getRowArray();

        if ($documento['tipo_documento'] === 'presupuesto_sst') {
            $db->table('tbl_presupuesto_sst')
                ->where('id_cliente', $documento['id_cliente'])
                ->where('anio', $documento['anio'])
                ->update([
                    'estado' => 'aprobado',
                    'fecha_aprobacion' => date('Y-m-d H:i:s')
                ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Documento marcado como firmado exitosamente',
            'id_documento' => $idDocumento
        ]);
    }

    /**
     * Diagnosticar audit log entre dos documentos
     * Ejecutar via: /sql-runner/diagnostico-audit/7/13
     */
    public function diagnosticoAudit($idDoc1 = 7, $idDoc2 = 13)
    {
        $db = Database::connect();

        $resultados = [];

        // Obtener info de ambos documentos
        $doc1 = $db->table('tbl_documentos_sst')->where('id_documento', $idDoc1)->get()->getRowArray();
        $doc2 = $db->table('tbl_documentos_sst')->where('id_documento', $idDoc2)->get()->getRowArray();

        $resultados['documento_1'] = [
            'id' => $idDoc1,
            'codigo' => $doc1['codigo'] ?? 'N/A',
            'tipo' => $doc1['tipo_documento'] ?? 'N/A',
            'estado' => $doc1['estado'] ?? 'N/A'
        ];

        $resultados['documento_2'] = [
            'id' => $idDoc2,
            'codigo' => $doc2['codigo'] ?? 'N/A',
            'tipo' => $doc2['tipo_documento'] ?? 'N/A',
            'estado' => $doc2['estado'] ?? 'N/A'
        ];

        // Obtener solicitudes de firma para doc1
        $solicitudes1 = $db->table('tbl_doc_firma_solicitudes')
            ->where('id_documento', $idDoc1)
            ->orderBy('orden_firma', 'ASC')
            ->get()->getResultArray();

        $resultados['solicitudes_doc1'] = [];
        foreach ($solicitudes1 as $sol) {
            $auditLog = $db->table('tbl_doc_firma_audit_log')
                ->where('id_solicitud', $sol['id_solicitud'])
                ->orderBy('fecha_hora', 'ASC')
                ->get()->getResultArray();

            $resultados['solicitudes_doc1'][] = [
                'id_solicitud' => $sol['id_solicitud'],
                'firmante_tipo' => $sol['firmante_tipo'],
                'firmante_nombre' => $sol['firmante_nombre'],
                'estado' => $sol['estado'],
                'audit_count' => count($auditLog),
                'eventos' => array_column($auditLog, 'evento')
            ];
        }

        // Obtener solicitudes de firma para doc2
        $solicitudes2 = $db->table('tbl_doc_firma_solicitudes')
            ->where('id_documento', $idDoc2)
            ->orderBy('orden_firma', 'ASC')
            ->get()->getResultArray();

        $resultados['solicitudes_doc2'] = [];
        foreach ($solicitudes2 as $sol) {
            $auditLog = $db->table('tbl_doc_firma_audit_log')
                ->where('id_solicitud', $sol['id_solicitud'])
                ->orderBy('fecha_hora', 'ASC')
                ->get()->getResultArray();

            $resultados['solicitudes_doc2'][] = [
                'id_solicitud' => $sol['id_solicitud'],
                'firmante_tipo' => $sol['firmante_tipo'],
                'firmante_nombre' => $sol['firmante_nombre'],
                'estado' => $sol['estado'],
                'audit_count' => count($auditLog),
                'eventos' => array_column($auditLog, 'evento')
            ];
        }

        // Comparar eventos esperados vs reales
        $eventosEsperados = [
            'solicitud_creada',
            'email_enviado',
            'link_abierto',
            'firma_completada',
            'documento_firmado_completo'
        ];

        $resultados['eventos_esperados'] = $eventosEsperados;

        return $this->response->setJSON([
            'success' => true,
            'diagnostico' => $resultados
        ]);
    }

    /**
     * Reparar audit log de un documento - inserta eventos faltantes
     * Ejecutar via: /sql-runner/reparar-audit/13
     */
    public function repararAudit($idDocumento)
    {
        $db = Database::connect();
        $reparaciones = [];

        // Obtener solicitudes del documento
        $solicitudes = $db->table('tbl_doc_firma_solicitudes')
            ->where('id_documento', $idDocumento)
            ->orderBy('orden_firma', 'ASC')
            ->get()->getResultArray();

        foreach ($solicitudes as $sol) {
            $idSolicitud = $sol['id_solicitud'];

            // Obtener eventos existentes
            $eventosExistentes = $db->table('tbl_doc_firma_audit_log')
                ->where('id_solicitud', $idSolicitud)
                ->get()->getResultArray();

            $eventosArray = array_column($eventosExistentes, 'evento');

            // 1. Verificar solicitud_creada
            if (!in_array('solicitud_creada', $eventosArray)) {
                $db->table('tbl_doc_firma_audit_log')->insert([
                    'id_solicitud' => $idSolicitud,
                    'evento' => 'solicitud_creada',
                    'fecha_hora' => $sol['created_at'],
                    'ip_address' => '127.0.0.1',
                    'detalles' => json_encode([
                        'tipo' => $sol['firmante_tipo'],
                        'reparado' => true
                    ])
                ]);
                $reparaciones[] = "Solicitud {$idSolicitud}: agregado solicitud_creada";
            }

            // 2. Verificar email_enviado (si tiene email)
            if (!empty($sol['firmante_email']) && !in_array('email_enviado', $eventosArray)) {
                $db->table('tbl_doc_firma_audit_log')->insert([
                    'id_solicitud' => $idSolicitud,
                    'evento' => 'email_enviado',
                    'fecha_hora' => $sol['created_at'],
                    'ip_address' => '127.0.0.1',
                    'detalles' => json_encode([
                        'email' => $sol['firmante_email'],
                        'reparado' => true
                    ])
                ]);
                $reparaciones[] = "Solicitud {$idSolicitud}: agregado email_enviado";
            }

            // 3. Si está firmado, agregar eventos faltantes
            if ($sol['estado'] === 'firmado') {
                // link_abierto
                if (!in_array('link_abierto', $eventosArray) && !in_array('link_accedido', $eventosArray)) {
                    $fechaLink = date('Y-m-d H:i:s', strtotime($sol['fecha_firma']) - 60);
                    $db->table('tbl_doc_firma_audit_log')->insert([
                        'id_solicitud' => $idSolicitud,
                        'evento' => 'link_abierto',
                        'fecha_hora' => $fechaLink,
                        'ip_address' => '127.0.0.1',
                        'detalles' => json_encode(['reparado' => true])
                    ]);
                    $reparaciones[] = "Solicitud {$idSolicitud}: agregado link_abierto";
                }

                // firma_completada
                if (!in_array('firma_completada', $eventosArray)) {
                    $db->table('tbl_doc_firma_audit_log')->insert([
                        'id_solicitud' => $idSolicitud,
                        'evento' => 'firma_completada',
                        'fecha_hora' => $sol['fecha_firma'],
                        'ip_address' => '127.0.0.1',
                        'detalles' => json_encode(['reparado' => true])
                    ]);
                    $reparaciones[] = "Solicitud {$idSolicitud}: agregado firma_completada";
                }
            }
        }

        // 4. Verificar documento_firmado_completo si todas las firmas están completas
        $documento = $db->table('tbl_documentos_sst')->where('id_documento', $idDocumento)->get()->getRowArray();

        if ($documento && $documento['estado'] === 'firmado') {
            // Buscar si ya existe el evento en alguna solicitud
            $eventoDocCompleto = $db->table('tbl_doc_firma_audit_log al')
                ->join('tbl_doc_firma_solicitudes s', 's.id_solicitud = al.id_solicitud')
                ->where('s.id_documento', $idDocumento)
                ->where('al.evento', 'documento_firmado_completo')
                ->get()->getRowArray();

            if (!$eventoDocCompleto && !empty($solicitudes)) {
                // Insertar en la última solicitud firmada
                $ultimaSolicitud = end($solicitudes);
                $db->table('tbl_doc_firma_audit_log')->insert([
                    'id_solicitud' => $ultimaSolicitud['id_solicitud'],
                    'evento' => 'documento_firmado_completo',
                    'fecha_hora' => $ultimaSolicitud['fecha_firma'] ?? date('Y-m-d H:i:s'),
                    'ip_address' => '127.0.0.1',
                    'detalles' => json_encode([
                        'id_documento' => $idDocumento,
                        'reparado' => true
                    ])
                ]);
                $reparaciones[] = "Documento {$idDocumento}: agregado documento_firmado_completo";
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => empty($reparaciones) ? 'Audit log ya estaba completo' : 'Audit log reparado',
            'reparaciones' => $reparaciones
        ]);
    }

    /**
     * Resetear contraseña de usuario miembro
     * Ejecutar via: /sql-runner/resetear-password-miembro
     */
    public function resetearPasswordMiembro()
    {
        $db = Database::connect();
        $email = 'proyectoperfilesafiancol@gmail.com';
        $nuevaPassword = 'Miembro2026!';

        $usuario = $db->table('tbl_usuarios')
            ->where('email', $email)
            ->get()->getRowArray();

        if (!$usuario) {
            return $this->response->setJSON([
                'success' => false,
                'message' => "Usuario {$email} no encontrado"
            ]);
        }

        // Hashear la nueva contraseña
        $passwordHash = password_hash($nuevaPassword, PASSWORD_BCRYPT);

        // Actualizar
        $db->table('tbl_usuarios')
            ->where('id_usuario', $usuario['id_usuario'])
            ->update([
                'password' => $passwordHash,
                'estado' => 'activo',
                'intentos_fallidos' => 0,
                'fecha_bloqueo' => null
            ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Contraseña reseteada correctamente',
            'credenciales' => [
                'email' => $email,
                'password' => $nuevaPassword
            ],
            'instrucciones' => 'Usa estas credenciales para iniciar sesion'
        ]);
    }

    /**
     * Diagnosticar usuarios tipo miembro
     * Ejecutar via: /sql-runner/diagnostico-usuarios-miembro
     */
    public function diagnosticoUsuariosMiembro()
    {
        $db = Database::connect();

        // Buscar todos los usuarios miembro
        $usuarios = $db->table('tbl_usuarios')
            ->where('tipo_usuario', 'miembro')
            ->get()->getResultArray();

        $resultado = [];
        $testPassword = 'Miembro2026!';

        foreach ($usuarios as $u) {
            $passwordValido = password_verify($testPassword, $u['password'] ?? '');
            $resultado[] = [
                'id' => $u['id_usuario'],
                'email' => $u['email'],
                'nombre' => $u['nombre_completo'],
                'tipo_usuario' => $u['tipo_usuario'],
                'estado' => $u['estado'],
                'tiene_password' => !empty($u['password']),
                'password_hash_inicio' => substr($u['password'] ?? '', 0, 20) . '...',
                'password_test_valido' => $passwordValido,
                'intentos_fallidos' => $u['intentos_fallidos'] ?? 0,
                'fecha_bloqueo' => $u['fecha_bloqueo']
            ];
        }

        return $this->response->setJSON([
            'success' => true,
            'total_usuarios_miembro' => count($usuarios),
            'usuarios' => $resultado,
            'test_password' => $testPassword
        ]);
    }

    /**
     * Agregar 'miembro' al ENUM de tipo_usuario en LOCAL y PRODUCCION
     * Ejecutar via: /sql-runner/agregar-miembro-enum
     */
    public function agregarMiembroEnum()
    {
        $resultados = [];

        // 1. Modificar en LOCAL
        $dbLocal = Database::connect();
        $sqlEnum = "ALTER TABLE tbl_usuarios MODIFY COLUMN tipo_usuario ENUM('admin','consultant','client','miembro') NOT NULL";

        try {
            $dbLocal->query($sqlEnum);
            $columnaLocal = $dbLocal->query("SHOW COLUMNS FROM tbl_usuarios WHERE Field = 'tipo_usuario'")->getRowArray();
            $resultados['local'] = [
                'success' => true,
                'message' => 'ENUM modificado en LOCAL',
                'tipo_usuario' => $columnaLocal['Type']
            ];
        } catch (\Exception $e) {
            $resultados['local'] = [
                'success' => false,
                'message' => 'Error LOCAL: ' . $e->getMessage()
            ];
        }

        // 2. Modificar en PRODUCCION
        try {
            $dbProd = new \mysqli(
                'db-mysql-cycloid-do-user-18794030-0.h.db.ondigitalocean.com',
                'cycloid_userdb',
                'AVNS_iDypWizlpMRwHIORJGG',
                'empresas_sst',
                25060
            );

            // Habilitar SSL
            $dbProd->ssl_set(null, null, null, null, null);

            if ($dbProd->connect_error) {
                throw new \Exception('Conexion fallida: ' . $dbProd->connect_error);
            }

            // Modificar ENUM
            if ($dbProd->query($sqlEnum)) {
                $result = $dbProd->query("SHOW COLUMNS FROM tbl_usuarios WHERE Field = 'tipo_usuario'");
                $columnaProd = $result->fetch_assoc();
                $resultados['produccion'] = [
                    'success' => true,
                    'message' => 'ENUM modificado en PRODUCCION',
                    'tipo_usuario' => $columnaProd['Type']
                ];
            } else {
                $resultados['produccion'] = [
                    'success' => false,
                    'message' => 'Error PRODUCCION: ' . $dbProd->error
                ];
            }

            $dbProd->close();
        } catch (\Exception $e) {
            $resultados['produccion'] = [
                'success' => false,
                'message' => 'Error PRODUCCION: ' . $e->getMessage()
            ];
        }

        // 3. Ahora corregir el usuario en LOCAL
        $email = 'proyectoperfilesafiancol@gmail.com';
        $passwordHash = password_hash('Miembro2026!', PASSWORD_BCRYPT);

        try {
            $sqlUpdate = "UPDATE tbl_usuarios SET tipo_usuario = 'miembro', password = ?, estado = 'activo', intentos_fallidos = 0 WHERE email = ?";
            $stmt = $dbLocal->query($sqlUpdate, [$passwordHash, $email]);

            $usuario = $dbLocal->table('tbl_usuarios')->where('email', $email)->get()->getRowArray();
            $resultados['usuario_local'] = [
                'success' => $usuario && $usuario['tipo_usuario'] === 'miembro',
                'tipo_usuario' => $usuario['tipo_usuario'] ?? 'no encontrado'
            ];
        } catch (\Exception $e) {
            $resultados['usuario_local'] = [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }

        return $this->response->setJSON([
            'success' => ($resultados['local']['success'] ?? false) && ($resultados['produccion']['success'] ?? false),
            'resultados' => $resultados,
            'credenciales' => [
                'email' => $email,
                'password' => 'Miembro2026!'
            ]
        ]);
    }

    /**
     * Ver triggers de la tabla usuarios
     * Ejecutar via: /sql-runner/ver-triggers
     */
    public function verTriggers()
    {
        $db = Database::connect();

        // Obtener nombre de la base de datos
        $dbName = $db->getDatabase();

        // Buscar triggers en la tabla tbl_usuarios
        $triggers = $db->query("SHOW TRIGGERS FROM `{$dbName}` WHERE `Table` = 'tbl_usuarios'")->getResultArray();

        // También ver la estructura de la columna tipo_usuario
        $columna = $db->query("SHOW COLUMNS FROM tbl_usuarios WHERE Field = 'tipo_usuario'")->getRowArray();

        // Ver si hay ENUM o restricción
        $createTable = $db->query("SHOW CREATE TABLE tbl_usuarios")->getRowArray();

        return $this->response->setJSON([
            'success' => true,
            'triggers' => $triggers,
            'columna_tipo_usuario' => $columna,
            'create_table' => $createTable['Create Table'] ?? $createTable
        ]);
    }

    /**
     * Probar login de un usuario
     * Ejecutar via: /sql-runner/probar-login
     */
    public function probarLogin()
    {
        $db = Database::connect();
        $email = 'proyectoperfilesafiancol@gmail.com';
        $passwordTest = 'Miembro2026!';

        $usuario = $db->table('tbl_usuarios')
            ->where('email', $email)
            ->get()->getRowArray();

        if (!$usuario) {
            return $this->response->setJSON([
                'success' => false,
                'paso' => 'buscar_usuario',
                'message' => "Usuario no encontrado: {$email}"
            ]);
        }

        // Verificar password
        $passwordValido = password_verify($passwordTest, $usuario['password'] ?? '');

        // Verificar estado
        $estadoOk = $usuario['estado'] === 'activo';

        // Verificar tipo_usuario
        $tipoOk = !empty($usuario['tipo_usuario']);

        return $this->response->setJSON([
            'success' => $passwordValido && $estadoOk && $tipoOk,
            'diagnostico' => [
                'usuario_encontrado' => true,
                'id' => $usuario['id_usuario'],
                'email' => $usuario['email'],
                'tipo_usuario' => $usuario['tipo_usuario'] ?: '(VACIO - PROBLEMA!)',
                'estado' => $usuario['estado'],
                'password_hash' => substr($usuario['password'] ?? '', 0, 30) . '...',
                'password_test' => $passwordTest,
                'password_valido' => $passwordValido,
                'estado_activo' => $estadoOk,
                'tipo_usuario_ok' => $tipoOk
            ],
            'problemas' => array_filter([
                !$passwordValido ? 'Password NO coincide' : null,
                !$estadoOk ? 'Usuario NO esta activo (estado: ' . $usuario['estado'] . ')' : null,
                !$tipoOk ? 'tipo_usuario esta VACIO - el login no sabra donde redirigir' : null
            ]),
            'solucion' => !$passwordValido || !$estadoOk || !$tipoOk
                ? 'Ejecuta /sql-runner/corregir-usuario-miembro para corregir todos los campos'
                : 'Todos los campos estan correctos, el login deberia funcionar'
        ]);
    }

    /**
     * Buscar usuario por email (cualquier tipo)
     * Ejecutar via: /sql-runner/buscar-usuario/email@ejemplo.com
     */
    public function buscarUsuario($email = 'proyectoperfilesafiancol@gmail.com')
    {
        $db = Database::connect();

        $usuario = $db->table('tbl_usuarios')
            ->where('email', $email)
            ->get()->getRowArray();

        if (!$usuario) {
            // Buscar por LIKE
            $usuarios = $db->table('tbl_usuarios')
                ->like('email', $email)
                ->get()->getResultArray();

            return $this->response->setJSON([
                'success' => false,
                'message' => "Usuario exacto no encontrado: {$email}",
                'busqueda_parcial' => $usuarios
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'usuario' => [
                'id' => $usuario['id_usuario'],
                'email' => $usuario['email'],
                'nombre' => $usuario['nombre_completo'],
                'tipo_usuario' => $usuario['tipo_usuario'],
                'estado' => $usuario['estado'],
                'id_entidad' => $usuario['id_entidad'],
                'tiene_password' => !empty($usuario['password']),
                'intentos_fallidos' => $usuario['intentos_fallidos'] ?? 0
            ]
        ]);
    }

    /**
     * Corregir usuario miembro COMPLETO - tipo + password
     * Ejecutar via: /sql-runner/corregir-usuario-miembro
     */
    public function corregirUsuarioMiembro()
    {
        $db = Database::connect();
        $email = 'proyectoperfilesafiancol@gmail.com';
        $nuevaPassword = 'Miembro2026!';

        // Buscar el usuario
        $usuario = $db->table('tbl_usuarios')
            ->where('email', $email)
            ->get()->getRowArray();

        if (!$usuario) {
            return $this->response->setJSON([
                'success' => false,
                'message' => "Usuario {$email} no encontrado"
            ]);
        }

        $tipoAnterior = $usuario['tipo_usuario'];
        $idUsuario = $usuario['id_usuario'];
        $passwordHash = password_hash($nuevaPassword, PASSWORD_BCRYPT);

        // Usar SQL directo para asegurar la actualización
        $sql = "UPDATE tbl_usuarios SET
                    tipo_usuario = 'miembro',
                    password = ?,
                    estado = 'activo',
                    intentos_fallidos = 0,
                    fecha_bloqueo = NULL
                WHERE id_usuario = ?";

        $result = $db->query($sql, [$passwordHash, $idUsuario]);
        $affectedRows = $db->affectedRows();

        // Verificar INMEDIATAMENTE el resultado
        $usuarioActualizado = $db->table('tbl_usuarios')
            ->where('id_usuario', $idUsuario)
            ->get()->getRowArray();

        $exito = $usuarioActualizado['tipo_usuario'] === 'miembro';

        return $this->response->setJSON([
            'success' => $exito,
            'message' => $exito ? "Usuario corregido completamente" : "ERROR: tipo_usuario NO se actualizo",
            'sql_ejecutado' => $sql,
            'filas_afectadas' => $affectedRows,
            'verificacion' => [
                'tipo_anterior' => $tipoAnterior ?: '(vacio)',
                'tipo_ahora' => $usuarioActualizado['tipo_usuario'] ?: '(SIGUE VACIO!)',
                'estado_ahora' => $usuarioActualizado['estado']
            ],
            'credenciales' => [
                'email' => $email,
                'password' => $nuevaPassword
            ]
        ]);
    }

    /**
     * Agrega columnas de firma digital al presupuesto SST
     * Ejecutar via: /sql-runner/columnas-firma-presupuesto
     */
    public function columnasFirmaPresupuesto()
    {
        $db = Database::connect();
        $resultados = [];

        // Columnas para tbl_presupuesto_sst
        $columnasPresupuesto = [
            'token_firma' => 'VARCHAR(64) NULL',
            'token_expiracion' => 'DATETIME NULL',
            'cedula_firmante' => 'VARCHAR(20) NULL',
            'firma_imagen' => 'VARCHAR(255) NULL',
            'ip_firma' => 'VARCHAR(45) NULL',
            'token_consulta' => 'VARCHAR(32) NULL'
        ];

        foreach ($columnasPresupuesto as $columna => $tipo) {
            try {
                $db->query("ALTER TABLE tbl_presupuesto_sst ADD COLUMN {$columna} {$tipo}");
                $resultados[] = "OK: Columna {$columna} agregada a tbl_presupuesto_sst";
            } catch (\Exception $e) {
                if (strpos($e->getMessage(), 'Duplicate column') !== false) {
                    $resultados[] = "SKIP: {$columna} ya existe en tbl_presupuesto_sst";
                } else {
                    $resultados[] = "ERROR en {$columna}: " . $e->getMessage();
                }
            }
        }

        // NOTA: Las columnas delegado_sst_email y representante_legal_email
        // ya existen en tbl_cliente_contexto_sst (ver ClienteContextoSstModel.php)

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Migración de columnas de firma completada',
            'resultados' => $resultados
        ]);
    }
}
