<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\ReporteModel;
use App\Models\PlanModel;
use App\Models\CronogcapacitacionModel;
use App\Models\SimpleEvaluationModel;
use App\Models\DashboardItemModel;
use App\Libraries\WorkPlanLibrary;
use App\Libraries\TrainingLibrary;
use App\Libraries\StandardsLibrary;
use App\Models\IndicadorSSTModel;
use App\Models\UserModel;
use App\Models\RoleModel;
use CodeIgniter\Controller;

class ConsultantController extends Controller
{
    public function index()
    {
        $clientModel = new ClientModel();
        $dashboardItemModel = new DashboardItemModel();

        // Obtener todos los clientes activos para el selector de actas
        $clientes = $clientModel->where('estado', 'activo')->findAll();

        // Obtener todos los items del dashboard para la tabla (sin filtrar por rol)
        $items = $dashboardItemModel->findAll();

        return view('consultant/dashboard', [
            'clientes' => $clientes,
            'items' => $items
        ]);
    }

    public function addClient()
    {
        $consultantModel = new ConsultantModel();
        $consultants = $consultantModel->findAll(); // Recupera todos los consultores

        // Verifica que los consultores se están cargando
        if (empty($consultants)) {
            log_message('error', 'No se encontraron consultores en la base de datos.');
        }

        // Pasa los consultores a la vista
        $data = [
            'consultants' => $consultants
        ];
        return view('consultant/add_client', $data);
    }





    public function addClientPost()
    {
        $clientModel = new ClientModel();

        // Aquí añadimos el código para obtener el id_consultor desde el formulario
        $id_consultor = $this->request->getPost('id_consultor');
        if (empty($id_consultor)) {
            return redirect()->back()->with('error', 'Debe seleccionar un consultor.');
        }

        $logo = $this->request->getFile('logo');
        $firma = $this->request->getFile('firma_representante_legal');

        $logoName = null;
        $firmaName = null;

        if ($logo && $logo->isValid() && !$logo->hasMoved()) {
            $logoName = $logo->getRandomName();
            $logo->move(ROOTPATH . 'public/uploads', $logoName);
        }

        if ($firma && $firma->isValid() && !$firma->hasMoved()) {
            $firmaName = $firma->getRandomName();
            $firma->move(ROOTPATH . 'public/uploads', $firmaName);
        }

        // Procesar archivos de documentos del cliente
        $docFiles = ['rut_archivo', 'camara_comercio_archivo', 'cedula_rep_legal_archivo', 'oferta_comercial_archivo'];
        $docNames = [];
        foreach ($docFiles as $fieldName) {
            $file = $this->request->getFile($fieldName);
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $docNames[$fieldName] = $file->getRandomName();
                $file->move(ROOTPATH . 'public/uploads', $docNames[$fieldName]);
            }
        }

        $data = [
            'datetime' => date('Y-m-d H:i:s'),
            'fecha_ingreso' => $this->request->getVar('fecha_ingreso'),
            'nit_cliente' => $this->request->getVar('nit_cliente'),
            'nombre_cliente' => $this->request->getVar('nombre_cliente'),
            'usuario' => $this->request->getVar('usuario'),
            'password' => password_hash($this->request->getVar('password'), PASSWORD_BCRYPT),
            'correo_cliente' => $this->request->getVar('correo_cliente'),
            'telefono_1_cliente' => $this->request->getVar('telefono_1_cliente'),
            'telefono_2_cliente' => $this->request->getVar('telefono_2_cliente'),
            'direccion_cliente' => $this->request->getVar('direccion_cliente'),
            'persona_contacto_compras' => $this->request->getVar('persona_contacto_compras'),
            'codigo_actividad_economica' => $this->request->getVar('codigo_actividad_economica'),
            'nombre_rep_legal' => $this->request->getVar('nombre_rep_legal'),
            'cedula_rep_legal' => $this->request->getVar('cedula_rep_legal'),
            'fecha_fin_contrato' => $this->request->getVar('fecha_fin_contrato'),
            'ciudad_cliente' => $this->request->getVar('ciudad_cliente'),
            'estado' => 'activo',
            'id_consultor' => $id_consultor,
            'logo' => $logoName,
            'firma_representante_legal' => $firmaName,
            'estandares' => $this->request->getVar('estandares'),
            'vendedor' => $this->request->getVar('vendedor'),
            'persona_contacto_operaciones' => $this->request->getVar('persona_contacto_operaciones'),
            'persona_contacto_pagos' => $this->request->getVar('persona_contacto_pagos'),
            'horarios_y_dias' => $this->request->getVar('horarios_y_dias'),
            'frecuencia_servicio' => $this->request->getVar('frecuencia_servicio'),
            'plazo_cartera' => $this->request->getVar('plazo_cartera'),
            'fecha_cierre_facturacion' => $this->request->getVar('fecha_cierre_facturacion'),
        ];

        // Agregar archivos de documentos al data
        foreach ($docFiles as $fieldName) {
            if (isset($docNames[$fieldName])) {
                $data[$fieldName] = $docNames[$fieldName];
            }
        }

        if ($clientModel->save($data)) {
            // Obtener el ID del cliente recién creado
            $clientId = $clientModel->getInsertID();
            $nombreCliente = $this->request->getVar('nombre_cliente');

            // Recuperar el NIT del cliente recién guardado
            $nitCliente = $this->request->getVar('nit_cliente');

            // Crear la carpeta para el cliente en public/uploads/{nit_cliente}
            $uploadPath = ROOTPATH . 'public/uploads/' . $nitCliente;

            if (!is_dir($uploadPath)) { // Verificar si la carpeta ya existe
                mkdir($uploadPath, 0777, true); // Crear la carpeta con permisos 0777
            }

            // Los documentos SST se consumen directamente desde DocumentLibrary (app/Libraries/DocumentLibrary.php)
            // No se insertan registros en BD, todos los clientes leen de la misma librería estática

            // Resumen de auto-generación
            $resumen = [];

            // Generar automáticamente el Plan de Trabajo Año 1
            try {
                $tipoServicio = strtolower($this->request->getVar('estandares'));
                $workPlanLibrary = new WorkPlanLibrary();

                // Obtener las actividades del Año 1 según el tipo de servicio
                $activities = $workPlanLibrary->getActivities($clientId, 1, $tipoServicio);

                // Insertar las actividades
                if (!empty($activities)) {
                    $planModel = new PlanModel();
                    $insertedCount = 0;

                    foreach ($activities as $activity) {
                        if ($planModel->insert($activity)) {
                            $insertedCount++;
                        }
                    }

                    log_message('info', "Plan de Trabajo generado automáticamente para cliente ID {$clientId}: {$insertedCount} actividades insertadas");
                    $resumen['plan_trabajo'] = $insertedCount;
                }
            } catch (\Exception $e) {
                // Log del error pero no interrumpir el flujo
                log_message('error', 'Error al generar Plan de Trabajo automático: ' . $e->getMessage());
            }

            // Generar automáticamente el Cronograma de Capacitaciones
            try {
                $tipoServicio = strtolower($this->request->getVar('estandares'));
                $trainingLibrary = new TrainingLibrary();

                // Obtener las capacitaciones según el tipo de servicio
                $trainings = $trainingLibrary->getTrainings($clientId, $tipoServicio);

                // Insertar las capacitaciones
                if (!empty($trainings)) {
                    $cronogModel = new CronogcapacitacionModel();
                    $insertedCount = 0;

                    foreach ($trainings as $training) {
                        if ($cronogModel->insert($training)) {
                            $insertedCount++;
                        }
                    }

                    log_message('info', "Cronograma de Capacitaciones generado automáticamente para cliente ID {$clientId}: {$insertedCount} capacitaciones insertadas");
                    $resumen['capacitaciones'] = $insertedCount;
                }
            } catch (\Exception $e) {
                // Log del error pero no interrumpir el flujo
                log_message('error', 'Error al generar Cronograma de Capacitaciones automático: ' . $e->getMessage());
            }

            // Generar automáticamente los Estándares Mínimos
            try {
                $standardsLibrary = new StandardsLibrary();

                // Obtener los estándares mínimos desde el CSV maestro
                $standards = $standardsLibrary->getStandards($clientId);

                // Insertar los estándares
                if (!empty($standards)) {
                    $evaluationModel = new SimpleEvaluationModel();
                    $insertedCount = 0;

                    foreach ($standards as $standard) {
                        if ($evaluationModel->insert($standard)) {
                            $insertedCount++;
                        }
                    }

                    log_message('info', "Estándares Mínimos generados automáticamente para cliente ID {$clientId}: {$insertedCount} estándares insertados");
                    $resumen['estandares'] = $insertedCount;
                }
            } catch (\Exception $e) {
                // Log del error pero no interrumpir el flujo
                log_message('error', 'Error al generar Estándares Mínimos automáticos: ' . $e->getMessage());
            }

            // Generar automáticamente los 18 Indicadores Legales Obligatorios
            // Decreto 1072/2015 + Resolución 0312/2019
            try {
                $indicadorModel = new IndicadorSSTModel();
                $resultado = $indicadorModel->crearIndicadoresLegales($clientId);
                log_message('info', "Indicadores Legales generados para cliente ID {$clientId}: {$resultado['creados']} creados, {$resultado['corregidos']} corregidos");
                $resumen['indicadores'] = $resultado['creados'];
            } catch (\Exception $e) {
                log_message('error', 'Error al generar Indicadores Legales: ' . $e->getMessage());
            }

            // Enviar email de felicitación por cliente nuevo
            try {
                $this->enviarEmailClienteNuevo(
                    $nombreCliente,
                    $nitCliente,
                    $this->request->getVar('ciudad_cliente') ?? '',
                    $this->request->getVar('vendedor') ?? '',
                    $this->request->getVar('frecuencia_servicio') ?? ''
                );
            } catch (\Exception $e) {
                log_message('error', 'Error al enviar email de felicitación: ' . $e->getMessage());
            }

            // Crear automáticamente usuario de acceso al portal cliente
            $credencialesMsg = '';
            try {
                $correoCliente = $this->request->getVar('correo_cliente');
                $passwordPlano = $this->request->getVar('password');

                if (!empty($correoCliente) && !empty($passwordPlano)) {
                    $userModel = new UserModel();
                    $roleModel = new RoleModel();

                    // Verificar que no exista ya un usuario con ese email
                    $existente = $userModel->findByEmail($correoCliente);

                    if (!$existente) {
                        $userId = $userModel->createUser([
                            'email'           => $correoCliente,
                            'password'        => $passwordPlano,
                            'nombre_completo' => $nombreCliente,
                            'tipo_usuario'    => 'client',
                            'id_entidad'      => $clientId,
                            'estado'          => 'activo',
                        ]);

                        if ($userId) {
                            // Asignar rol de cliente
                            $role = $roleModel->findByName('client');
                            if ($role) {
                                $roleModel->assignRoleToUser($userId, $role['id_rol']);
                            }

                            // Enviar credenciales por email al cliente
                            $emailSent = $this->enviarCredencialesCliente(
                                $correoCliente,
                                $nombreCliente,
                                $passwordPlano
                            );

                            $resumen['usuario_portal'] = true;
                            if ($emailSent) {
                                $credencialesMsg = ' Credenciales enviadas a ' . $correoCliente;
                                log_message('info', "Usuario portal creado y credenciales enviadas para cliente ID {$clientId}");
                            } else {
                                $credencialesMsg = ' Usuario creado pero no se pudo enviar email. Contraseña: ' . $passwordPlano;
                                log_message('error', "Usuario portal creado pero falló envío email para cliente ID {$clientId}");
                            }
                        } else {
                            log_message('error', 'Error al crear usuario portal para cliente ID ' . $clientId);
                        }
                    } else {
                        log_message('info', "Ya existe usuario con email {$correoCliente}, no se creó duplicado");
                        $credencialesMsg = ' (El email ya tenía cuenta de usuario existente)';
                    }
                }
            } catch (\Exception $e) {
                log_message('error', 'Error al crear usuario portal automático: ' . $e->getMessage());
            }

            session()->setFlashdata('msg', 'Cliente agregado exitosamente.' . $credencialesMsg);
            session()->setFlashdata('cliente_creado', [
                'id'      => $clientId,
                'nombre'  => $nombreCliente,
                'resumen' => $resumen,
            ]);

            return redirect()->to('/addClient');
        } else {
            session()->setFlashdata('msg', 'Error al agregar cliente');
            return redirect()->to('/addClient');
        }
    }






    public function addConsultant()
    {
        return view('consultant/add_consultant');
    }







    public function addConsultantPost()
    {
        $consultantModel = new ConsultantModel();

        $data = [
            'nombre_consultor' => $this->request->getVar('nombre_consultor'),
            'cedula_consultor' => $this->request->getVar('cedula_consultor'),
            'usuario' => $this->request->getVar('usuario'),
            'password' => password_hash($this->request->getVar('password'), PASSWORD_BCRYPT),
            'correo_consultor' => $this->request->getVar('correo_consultor'),
            'telefono_consultor' => $this->request->getVar('telefono_consultor'),
            'numero_licencia' => $this->request->getVar('numero_licencia'),

            'id_cliente' => $this->request->getVar('id_cliente'),
        ];

        // Manejar la subida de la foto
        $photo = $this->request->getFile('foto_consultor');
        if ($photo && $photo->isValid() && !$photo->hasMoved()) {
            $photoName = $photo->getRandomName();
            $photo->move(ROOTPATH . 'public/uploads', $photoName);
            $data['foto_consultor'] = $photoName;
        }

        // Manejar la subida de la firma
        $signature = $this->request->getFile('firma_consultor');
        if ($signature && $signature->isValid() && !$signature->hasMoved()) {
            $signatureName = $signature->getRandomName();
            $signature->move(ROOTPATH . 'public/uploads', $signatureName);
            $data['firma_consultor'] = $signatureName;
        }

        if ($consultantModel->save($data)) {
            return redirect()->to('/addConsultant')->with('msg', 'Consultor agregado exitosamente');
        } else {
            return redirect()->to('/addConsultant')->with('msg', 'Error al agregar consultor');
        }
    }

    public function listConsultants()
    {
        $consultantModel = new ConsultantModel();
        $consultants = $consultantModel->findAll();

        $data = [
            'consultants' => $consultants
        ];

        return view('consultant/list_consultants', $data);
    }

    public function editConsultant($id)
    {
        $consultantModel = new ConsultantModel();
        $consultant = $consultantModel->find($id);

        if ($this->request->getMethod() === 'post') {
            $data = [
                'nombre_consultor' => $this->request->getVar('nombre_consultor'),
                'cedula_consultor' => $this->request->getVar('cedula_consultor'),
                'usuario' => $this->request->getVar('usuario'),
                'correo_consultor' => $this->request->getVar('correo_consultor'),
                'telefono_consultor' => $this->request->getVar('telefono_consultor'),
                'numero_licencia' => $this->request->getVar('numero_licencia'),
                'rol' => $this->request->getVar('rol')
            ];

            $photo = $this->request->getFile('foto_consultor');
            if ($photo && $photo->isValid() && !$photo->hasMoved()) {
                $photoName = $photo->getRandomName();
                $photo->move(ROOTPATH . 'public/uploads', $photoName); // Guarda en la carpeta correcta
                $data['foto_consultor'] = $photoName;
            }


            if ($consultantModel->update($id, $data)) {
                session()->setFlashdata('msg', 'Consultor actualizado exitosamente');
                return redirect()->to('/listConsultants');
            } else {
                session()->setFlashdata('msg', 'Error al actualizar consultor');
                return redirect()->to('/addConsultant');
            }
        }

        $data = ['consultant' => $consultant];
        return view('consultant/edit_consultant', $data);
    }

    public function deleteConsultant($id)
    {
        $consultantModel = new ConsultantModel();
        if ($consultantModel->delete($id)) {
            session()->setFlashdata('msg', 'Consultor eliminado exitosamente');
        } else {
            session()->setFlashdata('msg', 'Error al eliminar consultor');
        }

        return redirect()->to('/listConsultants');
    }

    public function showPhoto($id)
    {
        $consultantModel = new ConsultantModel();
        $consultant = $consultantModel->find($id);

        if (!$consultant || empty($consultant['foto_consultor'])) {
            return redirect()->to('/listConsultants')->with('msg', 'Foto no encontrada o consultor no tiene foto.');
        }

        $data = [
            'foto' => $consultant['foto_consultor']
        ];

        return view('consultant/show_photo', $data);
    }


    public function editConsultantPost($id)
    {
        $consultantModel = new ConsultantModel();
        $consultant = $consultantModel->find($id);

        if (!$consultant) {
            return redirect()->to('/listConsultants')->with('msg', 'Consultor no encontrado');
        }

        // Datos que siempre se actualizarán
        $data = [
            'nombre_consultor' => $this->request->getVar('nombre_consultor'),
            'cedula_consultor' => $this->request->getVar('cedula_consultor'),
            'usuario' => $this->request->getVar('usuario'),
            'correo_consultor' => $this->request->getVar('correo_consultor'),
            'telefono_consultor' => $this->request->getVar('telefono_consultor'),
            'numero_licencia' => $this->request->getVar('numero_licencia'),
            'rol' => $this->request->getVar('rol'),
            'id_cliente' => $this->request->getVar('id_cliente')
        ];

        // Manejar la subida de una nueva imagen
        $newPhoto = $this->request->getFile('foto_consultor');
        if ($newPhoto && $newPhoto->isValid() && !$newPhoto->hasMoved()) {
            $newPhotoName = $newPhoto->getRandomName();
            $newPhoto->move(ROOTPATH . 'public/uploads', $newPhotoName);

            // Eliminar la imagen anterior si existe
            if (!empty($consultant['foto_consultor']) && file_exists(ROOTPATH . 'public/uploads/' . $consultant['foto_consultor'])) {
                unlink(ROOTPATH . 'public/uploads/' . $consultant['foto_consultor']);
            }

            // Actualizar el campo en la base de datos
            $data['foto_consultor'] = $newPhotoName;
        }



        // Manejar la subida de una nueva firma
        $newSignature = $this->request->getFile('firma_consultor');
        if ($newSignature && $newSignature->isValid() && !$newSignature->hasMoved()) {
            $newSignatureName = $newSignature->getRandomName();
            $newSignature->move(ROOTPATH . 'public/uploads', $newSignatureName);

            // Eliminar la firma anterior si existe
            if (!empty($consultant['firma_consultor']) && file_exists(ROOTPATH . 'public/uploads/' . $consultant['firma_consultor'])) {
                unlink(ROOTPATH . 'public/uploads/' . $consultant['firma_consultor']);
            }

            // Actualizar el campo en la base de datos
            $data['firma_consultor'] = $newSignatureName;
        }


        // Guardar los datos actualizados
        if ($consultantModel->update($id, $data)) {
            return redirect()->to('/listConsultants')->with('msg', 'Consultor actualizado exitosamente');
        } else {
            return redirect()->to('/editConsultant/' . $id)->with('msg', 'Error al actualizar consultor');
        }
    }

    public function listClients()
    {
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        // Obtener todos los clientes
        $clients = $clientModel->findAll();

        // Recorrer los clientes y agregar el nombre del consultor correspondiente
        foreach ($clients as &$client) {
            $consultant = $consultantModel->find($client['id_consultor']);
            $client['nombre_consultor'] = $consultant ? $consultant['nombre_consultor'] : 'No asignado';
        }

        return view('consultant/list_clients', ['clients' => $clients]);
    }




    public function editClient($id)
    {
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();

        $client = $clientModel->find($id);
        $consultants = $consultantModel->findAll();

        if (!$client) {
            return redirect()->to('/listClients')->with('error', 'Cliente no encontrado.');
        }

        $data = [
            'client' => $client,
            'consultants' => $consultants
        ];

        return view('consultant/edit_client', $data);
    }



    public function updateClient($id)
    {
        $clientModel = new ClientModel();
        $client = $clientModel->find($id);

        if (!$client) {
            return redirect()->to('/listClients')->with('msg', 'Cliente no encontrado');
        }

        // Datos que siempre se actualizarán
        $data = [
            'fecha_ingreso' => $this->request->getVar('fecha_ingreso'),
            'nombre_cliente' => $this->request->getVar('nombre_cliente'),
            'nit_cliente' => $this->request->getVar('nit_cliente'),
            'usuario' => $this->request->getVar('usuario'),
            'correo_cliente' => $this->request->getVar('correo_cliente'),
            'telefono_1_cliente' => $this->request->getVar('telefono_1_cliente'),
            'telefono_2_cliente' => $this->request->getVar('telefono_2_cliente'),
            'direccion_cliente' => $this->request->getVar('direccion_cliente'),
            'persona_contacto_compras' => $this->request->getVar('persona_contacto_compras'),
            'codigo_actividad_economica' => $this->request->getVar('codigo_actividad_economica'),
            'nombre_rep_legal' => $this->request->getVar('nombre_rep_legal'),
            'cedula_rep_legal' => $this->request->getVar('cedula_rep_legal'),
            'fecha_fin_contrato' => $this->request->getVar('fecha_fin_contrato'),
            'ciudad_cliente' => $this->request->getVar('ciudad_cliente'),
            'estado' => $this->request->getVar('estado'),
            'id_consultor' => $this->request->getVar('id_consultor'),
            'estandares' => $this->request->getVar('estandares'),
            'vendedor' => $this->request->getVar('vendedor'),
            'persona_contacto_operaciones' => $this->request->getVar('persona_contacto_operaciones'),
            'persona_contacto_pagos' => $this->request->getVar('persona_contacto_pagos'),
            'horarios_y_dias' => $this->request->getVar('horarios_y_dias'),
            'frecuencia_servicio' => $this->request->getVar('frecuencia_servicio'),
            'plazo_cartera' => $this->request->getVar('plazo_cartera'),
            'fecha_cierre_facturacion' => $this->request->getVar('fecha_cierre_facturacion'),
        ];

        // Manejar la subida de documentos del cliente (RUT, Cámara, Cédula RL, Oferta)
        $docFiles = ['rut_archivo', 'camara_comercio_archivo', 'cedula_rep_legal_archivo', 'oferta_comercial_archivo'];
        foreach ($docFiles as $fieldName) {
            $file = $this->request->getFile($fieldName);
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $newName = $file->getRandomName();
                $file->move(ROOTPATH . 'public/uploads', $newName);

                // Eliminar archivo anterior si existe
                if (!empty($client[$fieldName]) && file_exists(ROOTPATH . 'public/uploads/' . $client[$fieldName])) {
                    unlink(ROOTPATH . 'public/uploads/' . $client[$fieldName]);
                }

                $data[$fieldName] = $newName;
            }
        }

        // Manejar la subida de un nuevo logo
        $newLogo = $this->request->getFile('logo');
        if ($newLogo && $newLogo->isValid() && !$newLogo->hasMoved()) {
            $newLogoName = $newLogo->getRandomName();
            $newLogo->move(ROOTPATH . 'public/uploads', $newLogoName);

            // Eliminar el logo anterior si existe
            if (!empty($client['logo']) && file_exists(ROOTPATH . 'public/uploads/' . $client['logo'])) {
                unlink(ROOTPATH . 'public/uploads/' . $client['logo']);
            }

            // Actualizar el campo en la base de datos
            $data['logo'] = $newLogoName;
        }

        // Manejar la subida de una nueva firma
        $newSignature = $this->request->getFile('firma_representante_legal');
        if ($newSignature && $newSignature->isValid() && !$newSignature->hasMoved()) {
            $newSignatureName = $newSignature->getRandomName();
            $newSignature->move(ROOTPATH . 'public/uploads', $newSignatureName);

            // Eliminar la firma anterior si existe
            if (!empty($client['firma_representante_legal']) && file_exists(ROOTPATH . 'public/uploads/' . $client['firma_representante_legal'])) {
                unlink(ROOTPATH . 'public/uploads/' . $client['firma_representante_legal']);
            }

            // Actualizar el campo en la base de datos
            $data['firma_representante_legal'] = $newSignatureName;
        }

        // Guardar los datos actualizados
        if ($clientModel->update($id, $data)) {
            return redirect()->to('/listClients')->with('msg', 'Cliente actualizado exitosamente');
        } else {
            return redirect()->to('/editClient/' . $id)->with('msg', 'Error al actualizar cliente');
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Paz y Salvo por todo concepto
    // ─────────────────────────────────────────────────────────────

    /**
     * Emite el paz y salvo: verifica que no haya ítems abiertos y envía email con SendGrid.
     */
    public function emitirPazYSalvo($id)
    {
        $db              = \Config\Database::connect();
        $clientModel     = new ClientModel();
        $consultantModel = new \App\Models\ConsultantModel();

        $client = $clientModel->find($id);
        if (!$client) {
            return redirect()->to('/listClients')->with('error', 'Cliente no encontrado.');
        }

        // ── 1. Verificar ítems abiertos en las 3 tablas ──────────────────
        $ptaAbiertas = $db->query(
            "SELECT COUNT(*) AS total FROM tbl_pta_cliente
             WHERE id_cliente = ? AND estado_actividad IN ('ABIERTA','GESTIONANDO')",
            [$id]
        )->getRow()->total;

        $cronAbiertas = $db->query(
            "SELECT COUNT(*) AS total FROM tbl_cronog_capacitacion
             WHERE id_cliente = ? AND estado IN ('PROGRAMADA','REPROGRAMADA')",
            [$id]
        )->getRow()->total;

        $pendAbiertas = $db->query(
            "SELECT COUNT(*) AS total FROM tbl_pendientes
             WHERE id_cliente = ? AND estado IN ('ABIERTA','SIN RESPUESTA DEL CLIENTE')",
            [$id]
        )->getRow()->total;

        $totalAbiertos = $ptaAbiertas + $cronAbiertas + $pendAbiertas;

        if ($totalAbiertos > 0) {
            $detalle = [];
            if ($ptaAbiertas  > 0) $detalle[] = "{$ptaAbiertas} actividad(es) abierta(s) en el PTA";
            if ($cronAbiertas > 0) $detalle[] = "{$cronAbiertas} sesión(es) pendiente(s) en Cronograma";
            if ($pendAbiertas > 0) $detalle[] = "{$pendAbiertas} pendiente(s) sin cerrar";

            return redirect()->to('/editClient/' . $id)
                ->with('error', 'No se puede emitir el Paz y Salvo: ' . implode('; ', $detalle) . '.');
        }

        // ── 2. Obtener consultor asignado ─────────────────────────────────
        $consultor       = $consultantModel->find($client['id_consultor']);
        $correoConsultor = $consultor['correo_consultor'] ?? '';
        $nombreConsultor = $consultor['nombre_consultor'] ?? 'Consultor';

        // ── 3. Preparar variables para el template ────────────────────────
        $tzBogota = new \DateTimeZone('America/Bogota');
        $ahora    = new \DateTime('now', $tzBogota);

        $meses = ['enero','febrero','marzo','abril','mayo','junio',
                  'julio','agosto','septiembre','octubre','noviembre','diciembre'];
        $fechaEmisionCompleta = $ahora->format('d') . ' de ' . $meses[(int)$ahora->format('n') - 1]
            . ' de ' . $ahora->format('Y') . ' a las ' . $ahora->format('H:i') . ' (UTC-5)';
        $fechaEmisionCorta = $ahora->format('d/m/Y');
        $fechaIngreso      = date('d/m/Y', strtotime($client['fecha_ingreso']));

        $htmlBody = view('emails/paz_y_salvo', [
            'nombre_cliente'         => $client['nombre_cliente'],
            'nit_cliente'            => $client['nit_cliente'],
            'ciudad_cliente'         => $client['ciudad_cliente'],
            'fecha_ingreso'          => $fechaIngreso,
            'fecha_emision_corta'    => $fechaEmisionCorta,
            'fecha_emision_completa' => $fechaEmisionCompleta,
            'nombre_consultor'       => $nombreConsultor,
        ]);

        // ── 4. Enviar con SendGrid ────────────────────────────────────────
        $mail = new \SendGrid\Mail\Mail();
        $mail->setFrom('notificacion.cycloidtalent@cycloidtalent.com', 'EnterpriseSST - Cycloid Talent');
        $mail->setSubject('Paz y Salvo por Todo Concepto — ' . $client['nombre_cliente']);
        $mail->addContent('text/html', $htmlBody);

        // TO: correo del cliente
        $mail->addTo($client['correo_cliente'], $client['nombre_cliente']);

        // CC: consultor + correos fijos
        $ccs = array_filter([
            $correoConsultor,
            'businesscycloidtalent@gmail.com',
            'diana.cuestas@cycloidtalent.com',
        ]);
        foreach ($ccs as $cc) {
            $mail->addCc($cc);
        }

        $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
        try {
            $response = $sendgrid->send($mail);
            if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                log_message('info', "Paz y Salvo enviado para cliente ID {$id} ({$client['nombre_cliente']})");
                return redirect()->to('/editClient/' . $id)
                    ->with('msg', 'Paz y Salvo emitido y enviado correctamente a ' . $client['correo_cliente'] . '.');
            } else {
                log_message('error', 'SendGrid Paz y Salvo error: ' . $response->body());
                return redirect()->to('/editClient/' . $id)
                    ->with('error', 'Error al enviar el email (código ' . $response->statusCode() . ').');
            }
        } catch (\Exception $e) {
            log_message('error', 'SendGrid excepción Paz y Salvo: ' . $e->getMessage());
            return redirect()->to('/editClient/' . $id)
                ->with('error', 'Error al enviar el email: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Acciones de estado del cliente
    // ─────────────────────────────────────────────────────────────

    /**
     * Reactivar cliente: estado='activo' + borra registros en 3 tablas relacionadas.
     * Conserva en tbl_clientes: nombre_cliente, nit_cliente, fecha_ingreso (y todo lo demás).
     */
    public function reactivarCliente($id)
    {
        $db = \Config\Database::connect();
        $clientModel = new ClientModel();

        $client = $clientModel->find($id);
        if (!$client) {
            return redirect()->to('/listClients')->with('error', 'Cliente no encontrado.');
        }

        // Borrar todos los registros relacionados de las 3 tablas
        $db->query("DELETE FROM tbl_pta_cliente WHERE id_cliente = ?", [$id]);
        $db->query("DELETE FROM tbl_cronog_capacitacion WHERE id_cliente = ?", [$id]);
        $db->query("DELETE FROM tbl_pendientes WHERE id_cliente = ?", [$id]);

        // Actualizar solo el estado del cliente
        $clientModel->update($id, ['estado' => 'activo']);

        return redirect()->to('/editClient/' . $id)
            ->with('msg', 'Cliente reactivado. Historial de actividades borrado. El cliente puede comenzar desde cero.');
    }

    /**
     * Retirar cliente: estado='inactivo' + marca todas sus actividades como CERRADA POR FIN CONTRATO.
     */
    public function retirarCliente($id)
    {
        $db = \Config\Database::connect();
        $clientModel = new ClientModel();

        $client = $clientModel->find($id);
        if (!$client) {
            return redirect()->to('/listClients')->with('error', 'Cliente no encontrado.');
        }

        // Cerrar todas las actividades en las 3 tablas relacionadas
        $db->query("UPDATE tbl_pta_cliente SET estado_actividad = 'CERRADA POR FIN CONTRATO' WHERE id_cliente = ?", [$id]);
        $db->query("UPDATE tbl_cronog_capacitacion SET estado = 'CERRADA POR FIN CONTRATO' WHERE id_cliente = ?", [$id]);
        $db->query("UPDATE tbl_pendientes SET estado = 'CERRADA POR FIN CONTRATO' WHERE id_cliente = ?", [$id]);

        // Marcar cliente como inactivo
        $clientModel->update($id, ['estado' => 'inactivo']);

        return redirect()->to('/editClient/' . $id)
            ->with('msg', 'Cliente retirado. Todas sus actividades han sido cerradas por fin de contrato.');
    }

    /**
     * Marcar cliente como pendiente: solo actualiza estado, no toca tablas relacionadas.
     */
    public function marcarPendiente($id)
    {
        $clientModel = new ClientModel();

        $client = $clientModel->find($id);
        if (!$client) {
            return redirect()->to('/listClients')->with('error', 'Cliente no encontrado.');
        }

        $clientModel->update($id, ['estado' => 'pendiente']);

        return redirect()->to('/editClient/' . $id)
            ->with('msg', 'Cliente marcado como pendiente.');
    }

    // ─────────────────────────────────────────────────────────────

    public function deleteClient($id)
    {
        $clientModel = new ClientModel();

        try {
            // Intentar eliminar el cliente
            $client = $clientModel->find($id);
            if ($client) {
                // Eliminar las imágenes relacionadas si existen
                if (!empty($client['logo']) && file_exists(ROOTPATH . 'public/uploads/' . $client['logo'])) {
                    unlink(ROOTPATH . 'public/uploads/' . $client['logo']);
                }
                if (!empty($client['firma_representante_legal']) && file_exists(ROOTPATH . 'public/uploads/' . $client['firma_representante_legal'])) {
                    unlink(ROOTPATH . 'public/uploads/' . $client['firma_representante_legal']);
                }
                // Intentar eliminar el cliente
                $clientModel->delete($id);

                return redirect()->to('/listClients')->with('msg', 'Cliente eliminado exitosamente');
            } else {
                return redirect()->to('/listClients')->with('msg', 'Cliente no encontrado');
            }
        } catch (\Exception $e) {
            // Capturar la excepción y mostrar un mensaje de advertencia
            return redirect()->to('/listClients')->with('error', 'No puedes eliminar clientes que ya tienen registros grabados en la base de datos. Póngase en contacto con su administrador.');
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Email de felicitación por cliente nuevo (SendGrid)
    // ─────────────────────────────────────────────────────────────

    private function enviarEmailClienteNuevo(string $nombreCliente, string $nit, string $ciudad, string $vendedor = '', string $frecuenciaServicio = '')
    {
        require_once ROOTPATH . 'vendor/autoload.php';

        $email = new \SendGrid\Mail\Mail();
        $email->setFrom(
            getenv('SENDGRID_FROM_EMAIL') ?: 'notificacion.cycloidtalent@cycloidtalent.com',
            getenv('SENDGRID_FROM_NAME') ?: 'Enterprise SST'
        );
        $email->setSubject("NUEVO CLIENTE GANADO — {$nombreCliente}");

        $destinatarios = [
            'natalia.jimenez@cycloidtalent.com',
            'diana.cuestas@cycloidtalent.com',
            'edison.cuervo@cycloidtalent.com',
            'solangel.cuervo@cycloidtalent.com',
            'eleyson.segura@cycloidtalent.com',
        ];

        foreach ($destinatarios as $dest) {
            $email->addTo($dest);
        }

        $fecha = date('d/m/Y');

        $html = view('emails/cliente_nuevo', [
            'nombre_cliente'      => $nombreCliente,
            'nit'                 => $nit,
            'ciudad'              => $ciudad,
            'vendedor'            => $vendedor,
            'frecuencia_servicio' => $frecuenciaServicio,
            'fecha'               => $fecha,
        ]);

        $email->addContent('text/html', $html);

        $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
        $response = $sendgrid->send($email);

        if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
            log_message('info', "Email de felicitación enviado para cliente: {$nombreCliente}");
        } else {
            log_message('error', "Error al enviar email de felicitación. Status: {$response->statusCode()} Body: {$response->body()}");
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Email de credenciales de acceso al portal cliente (SendGrid)
    // ─────────────────────────────────────────────────────────────

    private function enviarCredencialesCliente(string $email, string $nombre, string $password): bool
    {
        require_once ROOTPATH . 'vendor/autoload.php';

        $loginUrl = base_url('/login');

        $html = '
        <!DOCTYPE html>
        <html>
        <head><meta charset="UTF-8"></head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
            <div style="background: linear-gradient(135deg, #1c2437, #2c3e50); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
                <h1 style="color: #ffffff; margin: 0;">Enterprise SST</h1>
            </div>
            <div style="background: #ffffff; padding: 30px; border: 1px solid #e9ecef; border-top: none;">
                <h2 style="color: #1c2437;">¡Bienvenido/a, ' . htmlspecialchars($nombre) . '!</h2>
                <p>Se ha creado tu cuenta en la plataforma <strong>Enterprise SST</strong>.</p>
                <p>A continuación encontrarás tus credenciales de acceso:</p>
                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
                    <p style="margin: 5px 0;"><strong>Usuario (correo):</strong></p>
                    <p style="margin: 5px 0 15px; color: #1c2437; font-size: 16px;">' . htmlspecialchars($email) . '</p>
                    <p style="margin: 5px 0;"><strong>Contraseña:</strong></p>
                    <p style="margin: 5px 0; font-size: 20px; font-weight: bold; color: #bd9751; letter-spacing: 1px;">' . htmlspecialchars($password) . '</p>
                </div>
                <p><strong>Por seguridad, te recomendamos cambiar tu contraseña después de tu primer inicio de sesión.</strong></p>
                <div style="text-align: center; margin: 30px 0;">
                    <a href="' . $loginUrl . '" style="background: linear-gradient(135deg, #1c2437, #2c3e50); color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold;">Iniciar Sesión</a>
                </div>
                <p style="color: #666; font-size: 14px;">Si tienes alguna pregunta o necesitas ayuda, no dudes en contactar al administrador del sistema.</p>
            </div>
            <div style="background: #f8f9fa; padding: 20px; text-align: center; border-radius: 0 0 10px 10px; border: 1px solid #e9ecef; border-top: none;">
                <p style="margin: 0; color: #666; font-size: 12px;">© 2024 Cycloid Talent SAS - Todos los derechos reservados</p>
                <p style="margin: 5px 0 0; color: #666; font-size: 12px;">NIT: 901.653.912</p>
            </div>
        </body>
        </html>';

        $mailObj = new \SendGrid\Mail\Mail();
        $mailObj->setFrom(
            getenv('SENDGRID_FROM_EMAIL') ?: 'notificacion.cycloidtalent@cycloidtalent.com',
            getenv('SENDGRID_FROM_NAME') ?: 'Enterprise SST'
        );
        $mailObj->setSubject('Bienvenido a Enterprise SST - Tus credenciales de acceso');
        $mailObj->addTo($email, $nombre);
        $mailObj->addContent('text/html', $html);

        $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
        $response = $sendgrid->send($mailObj);

        $success = $response->statusCode() >= 200 && $response->statusCode() < 300;

        if ($success) {
            log_message('info', "Credenciales enviadas a: {$email}");
        } else {
            log_message('error', "Error al enviar credenciales a {$email}. Status: {$response->statusCode()} Body: {$response->body()}");
        }

        return $success;
    }
}
