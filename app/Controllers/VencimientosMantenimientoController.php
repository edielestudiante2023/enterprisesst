<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\VencimientosMantenimientoModel;
use App\Models\ClientModel;
use App\Models\ConsultantModel;
use App\Models\MantenimientoModel;
use SendGrid\Mail\Mail;

class VencimientosMantenimientoController extends BaseController
{
    /**
     * Mostrar el formulario para agregar un nuevo vencimiento.
     */
    public function addVencimientosMantenimiento()
    {
        // Instanciar los modelos necesarios
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $mantenimientoModel = new MantenimientoModel();

        // Obtener los datos para los select de clientes, consultores y mantenimientos
        $clientes = $clientModel->findAll();
        $consultores = $consultantModel->findAll();
        $mantenimientos = $mantenimientoModel->findAll();

        // Cargar la vista con los datos
        return view('consultant/vencimientos/addVencimientosMantenimiento', [
            'clientes' => $clientes,
            'consultores' => $consultores,
            'mantenimientos' => $mantenimientos,
        ]);
    }

    /**
     * Procesar el formulario de agregar un nuevo vencimiento.
     */
    public function addpostVencimientosMantenimiento()
    {
        // Instanciar el modelo de vencimientos
        $vencimientosModel = new VencimientosMantenimientoModel();

        // Recoger los datos del formulario
        $data = [
            'id_mantenimiento'    => $this->request->getVar('id_mantenimiento'),
            'id_cliente'          => $this->request->getVar('id_cliente'),
            'id_consultor'        => $this->request->getVar('id_consultor'),
            'fecha_vencimiento'   => $this->request->getVar('fecha_vencimiento'),
            'estado_actividad'    => $this->request->getVar('estado_actividad'),
            'fecha_realizacion'   => $this->request->getVar('fecha_realizacion'),
            'observaciones'       => $this->request->getVar('observaciones'),
        ];

        // Obtener el id_cliente para mantenerlo en la URL de redireccion
        $clienteId = $this->request->getVar('id_cliente');

        // Guardar los datos en la base de datos
        if ($vencimientosModel->save($data)) {
            return redirect()->to(base_url('vencimientos') . '?cliente_id=' . $clienteId)->with('msg', 'Vencimiento agregado exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al guardar el vencimiento.')->withInput();
        }
    }

    /**
     * Mostrar el formulario para editar un vencimiento existente.
     *
     * @param int $id ID del vencimiento a editar.
     */
    public function editVencimientosMantenimiento($id)
    {
        // Instanciar los modelos necesarios
        $vencimientosModel = new VencimientosMantenimientoModel();
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $mantenimientoModel = new MantenimientoModel();

        // Buscar el vencimiento por ID
        $vencimiento = $vencimientosModel->find($id);

        if (!$vencimiento) {
            return redirect()->back()->with('msg', 'Vencimiento no encontrado.');
        }

        // Obtener los datos para los select
        $clientes = $clientModel->findAll();
        $consultores = $consultantModel->findAll();
        $mantenimientos = $mantenimientoModel->findAll();

        // Cargar la vista con los datos
        return view('consultant/vencimientos/editVencimientosMantenimiento', [
            'vencimiento'   => $vencimiento,
            'clientes'      => $clientes,
            'consultores'   => $consultores,
            'mantenimientos' => $mantenimientos,
        ]);
    }

    /**
     * Procesar el formulario de edicion de un vencimiento.
     *
     * @param int $id ID del vencimiento a actualizar.
     */
    public function editpostVencimientosMantenimiento($id)
    {
        // Instanciar el modelo de vencimientos
        $vencimientosModel = new VencimientosMantenimientoModel();

        // Recoger los datos del formulario
        $data = [
            'id_vencimientos_mmttos' => $id,
            'id_mantenimiento'       => $this->request->getVar('id_mantenimiento'),
            'id_cliente'             => $this->request->getVar('id_cliente'),
            'id_consultor'           => $this->request->getVar('id_consultor'),
            'fecha_vencimiento'      => $this->request->getVar('fecha_vencimiento'),
            'estado_actividad'       => $this->request->getVar('estado_actividad'),
            'fecha_realizacion'      => $this->request->getVar('fecha_realizacion'),
            'observaciones'          => $this->request->getVar('observaciones'),
        ];

        // Obtener el id_cliente para mantenerlo en la URL de redireccion
        $clienteId = $this->request->getVar('id_cliente');

        // Actualizar los datos en la base de datos
        if ($vencimientosModel->save($data)) {
            return redirect()->to(base_url('vencimientos') . '?cliente_id=' . $clienteId)->with('msg', 'Vencimiento actualizado exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al actualizar el vencimiento.')->withInput();
        }
    }

    /**
     * Eliminar un vencimiento especifico.
     *
     * @param int $id ID del vencimiento a eliminar.
     */
    public function deleteVencimientosMantenimiento($id)
    {
        // Instanciar el modelo de vencimientos
        $vencimientosModel = new VencimientosMantenimientoModel();

        // Obtener el vencimiento antes de eliminarlo para conservar el id_cliente
        $vencimiento = $vencimientosModel->find($id);
        $clienteId = $vencimiento ? $vencimiento['id_cliente'] : null;

        // Eliminar el vencimiento
        if ($vencimientosModel->delete($id)) {
            $redirectUrl = base_url('vencimientos');
            if ($clienteId) {
                $redirectUrl .= '?cliente_id=' . $clienteId;
            }
            return redirect()->to($redirectUrl)->with('msg', 'Vencimiento eliminado exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al eliminar el vencimiento.');
        }
    }

    /**
     * Listar todos los vencimientos con informacion descriptiva.
     */
    public function listVencimientosMantenimiento()
    {
        // Instanciar los modelos necesarios
        $vencimientosModel = new VencimientosMantenimientoModel();
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $mantenimientoModel = new MantenimientoModel();

        // Obtener el filtro de cliente de la URL o POST
        $clienteId = $this->request->getGet('cliente_id') ?? $this->request->getPost('cliente_id');

        // Obtener lista de clientes para el filtro
        $clientes = $clientModel->findAll();

        // Si no se ha seleccionado un cliente, mostrar solo el formulario de filtro
        if (empty($clienteId)) {
            return view('consultant/vencimientos/listVencimientosMantenimiento', [
                'vencimientos_sin_ejecutar' => [],
                'vencimientos_ejecutados' => [],
                'clientes' => $clientes,
                'mostrar_filtro' => true,
                'cliente_seleccionado' => null,
            ]);
        }

        // Obtener vencimientos filtrados por cliente
        $vencimientos = $vencimientosModel->where('id_cliente', $clienteId)->findAll();

        // Preparar los datos descriptivos separados por estado
        $vencimientosSinEjecutar = [];
        $vencimientosEjecutados = [];

        foreach ($vencimientos as $vencimiento) {
            $cliente = $clientModel->find($vencimiento['id_cliente']);
            $consultor = $consultantModel->find($vencimiento['id_consultor']);
            $mantenimiento = $mantenimientoModel->find($vencimiento['id_mantenimiento']);

            $dataVencimiento = [
                'id'                 => $vencimiento['id_vencimientos_mmttos'],
                'cliente'            => $cliente ? $cliente['nombre_cliente'] : 'Desconocido',
                'consultor'          => $consultor ? $consultor['nombre_consultor'] : 'Desconocido',
                'mantenimiento'      => $mantenimiento ? $mantenimiento['detalle_mantenimiento'] : 'No especificado',
                'fecha_vencimiento'  => $vencimiento['fecha_vencimiento'],
                'estado_actividad'   => ucfirst($vencimiento['estado_actividad']),
                'fecha_realizacion'  => $vencimiento['fecha_realizacion'],
                'observaciones'      => $vencimiento['observaciones'] ?? 'N/A',
            ];

            // Separar por estado
            if (strtolower($vencimiento['estado_actividad']) === 'ejecutado') {
                $vencimientosEjecutados[] = $dataVencimiento;
            } else {
                $vencimientosSinEjecutar[] = $dataVencimiento;
            }
        }

        // Cargar la vista con los datos separados
        return view('consultant/vencimientos/listVencimientosMantenimiento', [
            'vencimientos_sin_ejecutar' => $vencimientosSinEjecutar,
            'vencimientos_ejecutados' => $vencimientosEjecutados,
            'clientes' => $clientes,
            'mostrar_filtro' => false,
            'cliente_seleccionado' => $clienteId,
        ]);
    }

    /**
     * Enviar correos electronicos para vencimientos proximos.
     */
    public function sendEmailsForUpcomingVencimientos()
    {
        $vencimientosModel = new VencimientosMantenimientoModel();
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $mantenimientoModel = new MantenimientoModel();

        // Obtener vencimientos proximos
        $vencimientos = $vencimientosModel->getUpcomingVencimientos();

        // Log de depuracion para ver fechas y resultados
        log_message('debug', 'Intentando obtener vencimientos entre ' . date('Y-m-d') . ' y ' . date('Y-m-d', strtotime('+30 days')));
        log_message('debug', 'Vencimientos encontrados: ' . print_r($vencimientos, true));

        if (empty($vencimientos)) {
            log_message('error', 'No hay vencimientos proximos para enviar correos.');
            return redirect()->to(base_url('vencimientos'))->with('msg', 'No hay vencimientos proximos para enviar.');
        }

        foreach ($vencimientos as $vencimiento) {
            $cliente = $clientModel->find($vencimiento['id_cliente']);
            $consultor = $consultantModel->find($vencimiento['id_consultor']);
            $mantenimiento = $mantenimientoModel->find($vencimiento['id_mantenimiento']);

            // Validar que cliente y consultor existan
            if (!$cliente || !$consultor) {
                log_message('error', "Error: Cliente o consultor no encontrados para vencimiento ID: {$vencimiento['id_vencimientos_mmttos']}");
                continue;
            }

            // Verificar si los correos son validos
            if (empty($cliente['correo_cliente']) || !filter_var($cliente['correo_cliente'], FILTER_VALIDATE_EMAIL)) {
                log_message('error', "Correo del cliente no valido o vacio: " . ($cliente['correo_cliente'] ?? 'No definido'));
                continue;
            }

            if (empty($consultor['correo_consultor']) || !filter_var($consultor['correo_consultor'], FILTER_VALIDATE_EMAIL)) {
                log_message('error', "Correo del consultor no valido o vacio: " . ($consultor['correo_consultor'] ?? 'No definido'));
                continue;
            }

            // Eliminar correos duplicados
            $destinatarios = array_unique([$cliente['correo_cliente'], $consultor['correo_consultor']]);

            // Log de destinatarios
            log_message('debug', 'Destinatarios del correo: ' . implode(', ', $destinatarios));

            $email = new \SendGrid\Mail\Mail();
            $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "Cycloid Talent");
            $email->setSubject("Recordatorio de Vencimiento");

            // Agregar destinatarios unicos
            foreach ($destinatarios as $correo) {
                $email->addTo($correo);
            }

            $email->addContent(
                "text/html",
                "<p><strong>Estimado/a {$cliente['nombre_cliente']}</strong>,</p>

                 <p>Nos dirigimos a usted con el firme proposito de recordarle que el mantenimiento <strong>{$mantenimiento['detalle_mantenimiento']}</strong> tiene su fecha de vencimiento programada para el <strong>{$vencimiento['fecha_vencimiento']}</strong>.</p>

                 <p>En <strong>Cycloid Talent</strong>, entendemos la importancia de un mantenimiento oportuno para garantizar la seguridad y el correcto funcionamiento de sus instalaciones. Como expertos en <strong>Seguridad y Salud en el Trabajo (SST)</strong>, estamos comprometidos en apoyarle en la planificacion y control de estas actividades, asegurando que cada proceso se ejecute de manera eficiente y conforme a la normativa.</p>

                 <p>Le recomendamos gestionar este mantenimiento con antelacion para evitar riesgos y garantizar la continuidad operativa. Si necesita orientacion o soporte, nuestro equipo esta disponible para asistirle en cada paso.</p>

                 <p><strong>Su seguridad y tranquilidad son nuestra prioridad!</strong> No dude en contactarnos para cualquier consulta adicional.</p>

                 <p>Saludos cordiales,</p>
                 <p><strong>Cycloid Talent</strong></p>"
            );

            // Intentar enviar el correo
            try {
                $response = \App\Libraries\SendGridMailer::send($email);
                log_message('info', "Correo enviado con codigo: " . $response->statusCode());
            } catch (\Exception $e) {
                log_message('error', "Error al enviar correo: " . $e->getMessage());
            }
        }

        return redirect()->to(base_url('vencimientos'))->with('msg', 'Correos enviados correctamente.');
    }

    /**
     * Funcion auxiliar para enviar correos electronicos utilizando SendGrid.
     */
    private function sendEmail($clientEmail, $consultantEmail, $subject, $content)
    {
        $email = new Mail();
        $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "Cycloid Talent");
        $email->setSubject($subject);
        $email->addTo($clientEmail);
        $email->addTo($consultantEmail);
        $email->addContent("text/html", $content);

        // Obtener la clave API de SendGrid desde las variables de entorno
        $sendgridApiKey = getenv('SENDGRID_API_KEY');

        if (!$sendgridApiKey) {
            log_message('error', 'Clave API de SendGrid no configurada.');
            return;
        }

        try {
            // Enviar el correo
            $response = \App\Libraries\SendGridMailer::send($email);
            log_message('debug', 'Correo enviado con exito. Status Code: ' . $response->statusCode());
        } catch (\Exception $e) {
            log_message('error', 'Error al enviar el correo: ' . $e->getMessage());
        }
    }

    public function sendEmailsAutomatically()
    {
        // Instanciar los modelos necesarios
        $vencimientosModel = new VencimientosMantenimientoModel();
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $mantenimientoModel = new MantenimientoModel();

        // Obtener vencimientos proximos a menos de 30 dias y sin ejecutar
        $vencimientos = $vencimientosModel->getUpcomingVencimientos();

        if (empty($vencimientos)) {
            log_message('info', 'No hay vencimientos proximos para enviar correos.');
            return;
        }

        foreach ($vencimientos as $vencimiento) {
            // Obtener datos del cliente y consultor
            $cliente = $clientModel->find($vencimiento['id_cliente']);
            $consultor = $consultantModel->find($vencimiento['id_consultor']);

            // Validar que ambos tengan correo registrado
            if (empty($cliente['correo_cliente']) || empty($consultor['correo_consultor'])) {
                log_message('error', "Faltan correos electronicos para el vencimiento ID: {$vencimiento['id_vencimientos_mmttos']}");
                continue;
            }

            // Obtener detalles del mantenimiento
            $mantenimiento = $mantenimientoModel->find($vencimiento['id_mantenimiento']);
            $tituloMantenimiento = $mantenimiento ? $mantenimiento['detalle_mantenimiento'] : 'Mantenimiento no especificado';

            // Crear contenido del correo
            $emailContent = "
            <h3>Recordatorio de Vencimiento</h3>
            <p>Estimado/a <strong>{$cliente['nombre_cliente']}</strong> y Consultor <strong>{$consultor['nombre_consultor']}</strong>,</p>
            <p>El mantenimiento <strong>{$tituloMantenimiento}</strong> esta proximo a vencer el dia <strong>{$vencimiento['fecha_vencimiento']}</strong>.</p>
            <p>Por favor, tomen las acciones necesarias para su ejecucion antes de la fecha de vencimiento.</p>
            <p>Saludos,</p>
            <p><strong>Cycloid Talent</strong></p>
        ";

            // Enviar correos electronicos
            $this->sendEmail(
                $cliente['correo_cliente'],
                $consultor['correo_consultor'],
                'Recordatorio de Vencimiento de Mantenimiento',
                $emailContent
            );
        }

        log_message('info', 'Correos electronicos de recordatorio enviados automaticamente.');
    }

    public function testEmailForVencimiento($id)
    {
        $vencimientosModel = new VencimientosMantenimientoModel();
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $mantenimientoModel = new MantenimientoModel();

        $vencimiento = $vencimientosModel->find($id);
        if (!$vencimiento) {
            return 'Vencimiento no encontrado.';
        }

        $cliente = $clientModel->find($vencimiento['id_cliente']);
        $consultor = $consultantModel->find($vencimiento['id_consultor']);
        $mantenimiento = $mantenimientoModel->find($vencimiento['id_mantenimiento']);

        $email = new \SendGrid\Mail\Mail();
        $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "Cycloid Talent");
        $email->setSubject("Recordatorio de Vencimiento");
        $email->addTo($cliente['correo_cliente']);
        $email->addTo($consultor['correo_consultor']);
        $email->addContent(
            "text/html",
            "<p>El mantenimiento <strong>{$mantenimiento['detalle_mantenimiento']}</strong> esta proximo a vencer el <strong>{$vencimiento['fecha_vencimiento']}</strong>.</p>"
        );

        try {
            $response = \App\Libraries\SendGridMailer::send($email);
            log_message('info', 'SendGrid Response: ' . $response->body());
            return "Correo enviado. Codigo de estado: " . $response->statusCode() . "<br>Respuesta de SendGrid: " . $response->body();
        } catch (\Exception $e) {
            log_message('error', 'Error al enviar correo de prueba: ' . $e->getMessage());
            return "Error al enviar correo: " . $e->getMessage();
        }
    }

    public function sendSelectedEmails()
    {
        // Recoger los IDs enviados (arreglo de IDs)
        $selectedIds = $this->request->getPost('selected');

        if (empty($selectedIds)) {
            return redirect()->to(base_url('vencimientos'))->with('msg', 'No se seleccionaron vencimientos.');
        }

        // Instanciar los modelos necesarios
        $vencimientosModel = new VencimientosMantenimientoModel();
        $clientModel = new ClientModel();
        $consultantModel = new ConsultantModel();
        $mantenimientoModel = new MantenimientoModel();

        // Recorrer cada ID seleccionado y enviar el correo
        foreach ($selectedIds as $id) {
            $vencimiento = $vencimientosModel->find($id);
            if (!$vencimiento) {
                continue;
            }

            $cliente = $clientModel->find($vencimiento['id_cliente']);
            $consultor = $consultantModel->find($vencimiento['id_consultor']);
            $mantenimiento = $mantenimientoModel->find($vencimiento['id_mantenimiento']);

            // Validar que existan y que los correos sean validos
            if (!$cliente || !$consultor) {
                continue;
            }
            if (empty($cliente['correo_cliente']) || !filter_var($cliente['correo_cliente'], FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            if (empty($consultor['correo_consultor']) || !filter_var($consultor['correo_consultor'], FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            // Preparar el contenido del correo
            $emailContent = "
          <p>Hola <strong>{$cliente['nombre_cliente']}</strong>,</p>
          <p>El mantenimiento <strong>{$mantenimiento['detalle_mantenimiento']}</strong> tiene su fecha de vencimiento programada para el <strong>{$vencimiento['fecha_vencimiento']}</strong>.</p>
          <p>Por favor, verifica los detalles y toma las medidas necesarias.</p>
        ";

            // Reutilizamos el metodo sendEmail existente para enviar el correo
            $this->sendEmail(
                $cliente['correo_cliente'],
                $consultor['correo_consultor'],
                'Recordatorio de Vencimiento de Mantenimiento',
                $emailContent
            );
        }

        return redirect()->to(base_url('vencimientos'))->with('msg', 'Correos enviados a los registros seleccionados.');
    }
}
