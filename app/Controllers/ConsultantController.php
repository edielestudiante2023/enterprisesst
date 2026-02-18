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
            $logo->move(ROOTPATH . 'public/uploads', $logoName); // Cambiado WRITEPATH por ROOTPATH
        }

        if ($firma && $firma->isValid() && !$firma->hasMoved()) {
            $firmaName = $firma->getRandomName();
            $firma->move(ROOTPATH . 'public/uploads', $firmaName); // Cambiado WRITEPATH por ROOTPATH
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
            'id_consultor' => $id_consultor,  // Modificado para usar el valor del formulario
            'logo' => $logoName,
            'firma_representante_legal' => $firmaName,
            'estandares' => $this->request->getVar('estandares'),
        ];

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

            session()->setFlashdata('msg', 'Cliente agregado exitosamente.');
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
            'estandares' => $this->request->getVar('estandares')
        ];

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
}
