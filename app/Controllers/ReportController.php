<?php

namespace App\Controllers;

use App\Models\{ReporteModel, ClientModel, ReportTypeModel, DetailReportModel};
use CodeIgniter\Controller;
use SendGrid\Mail\Mail;

echo realpath(__DIR__ . '/../../vendor/autoload.php');

require __DIR__ . '/../../vendor/autoload.php';



class ReportController extends Controller
{
    public function index()
    {
        $reporteModel = new ReporteModel();
        $reportTypeModel = new ReportTypeModel();
        $clientModel = new ClientModel();
        $detailReportModel = new DetailReportModel();

        $reports = $reporteModel->findAll();
        $reportTypes = $reportTypeModel->findAll();
        $clients = $clientModel->findAll();
        $details = $detailReportModel->findAll();

        $data = [
            'reports' => $reports,
            'reportTypes' => $reportTypes,
            'clients' => $clients,
            'details' => $details
        ];

        return view('consultant/add_report', $data);
    }

    public function reportList()
    {
        $reporteModel = new ReporteModel();
        $reportTypeModel = new ReportTypeModel();
        $clientModel = new ClientModel();
        $detailReportModel = new DetailReportModel();

        $reports = $reporteModel->findAll();
        $reportTypes = $reportTypeModel->findAll();
        $clients = $clientModel->findAll();
        $details = $detailReportModel->findAll();

        $data = [
            'reports' => $reports,
            'reportTypes' => $reportTypes,
            'clients' => $clients,
            'details' => $details
        ];

        return view('consultant/report_list', $data);
    }

    public function addReport()
    {
        $reporteModel = new ReporteModel();
        $reportTypeModel = new ReportTypeModel();
        $clientModel = new ClientModel();
        $detailReportModel = new DetailReportModel();

        $reports = $reporteModel->findAll();
        $reportTypes = $reportTypeModel->findAll();
        $clients = $clientModel->findAll();
        $details = $detailReportModel->findAll();

        $data = [
            'reports' => $reports,
            'reportTypes' => $reportTypes,
            'clients' => $clients,
            'details' => $details
        ];

        return view('consultant/add_report', $data);
    }

    public function addReportPost()
    {
        $reporteModel = new ReporteModel();
        $clientModel = new ClientModel();
        $reportTypeModel = new ReportTypeModel();
        $detailReportModel = new DetailReportModel();

        $idCliente = $this->request->getVar('id_cliente');
        $client = $clientModel->find($idCliente);
        log_message('debug', 'Cliente recuperado: ' . print_r($client, true));

        if (!$client) {
            return redirect()->back()->with('msg', 'Cliente no encontrado');
        }

        // Validar existencia de id_report_type
        $reportType = $reportTypeModel->find($this->request->getVar('id_report_type'));
        if (!$reportType) {
            return redirect()->back()->with('msg', 'Tipo de reporte no válido');
        }

        // Validar existencia de id_detailreport
        $detailReport = $detailReportModel->find($this->request->getVar('id_detailreport'));
        if (!$detailReport) {
            return redirect()->back()->with('msg', 'Detalle de reporte no válido');
        }

        // Procesar archivo
        $file = $this->request->getFile('archivo');
        $nitCliente = $client['nit_cliente'];
        $uploadPath = ROOTPATH . 'public/uploads/' . $nitCliente;

        // Crear directorio si no existe
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        if ($file && $file->isValid() && !$file->hasMoved()) {
            $fileName = $file->getRandomName();
            $file->move($uploadPath, $fileName);
        } else {
            return redirect()->back()->with('msg', 'Error al subir archivo. Asegúrese de seleccionar un archivo válido.');
        }

        // Guardar datos
        $data = [
            'titulo_reporte' => $this->request->getVar('titulo_reporte'),
            'id_detailreport' => $this->request->getVar('id_detailreport'),
            'id_report_type' => $this->request->getVar('id_report_type'),
            'id_cliente' => $idCliente,
            'estado' => $this->request->getVar('estado'),
            'observaciones' => $this->request->getVar('observaciones'),
            'enlace' => base_url('uploads/' . $nitCliente . '/' . $fileName),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($reporteModel->save($data)) {
            log_message('debug', 'Guardado exitoso, llamando a sendEmailToClient');
            // Llamar a sendEmailToClient con el ID del cliente
            $this->sendEmailToClient($idCliente, $data['titulo_reporte'], $data['enlace']);

            return redirect()->to('/reportList')->with('msg', 'Reporte agregado exitosamente');
        } else {
            return redirect()->back()->with('msg', 'Error al guardar reporte en la base de datos.');
        }
    }

    private function sendEmailToClient($idCliente, $tituloReporte, $enlace)
    {
        // Validar el enlace antes de proceder
        if (!filter_var($enlace, FILTER_VALIDATE_URL)) {
            log_message('error', 'El enlace generado no es válido: ' . $enlace);
            return; // Finaliza si el enlace no es válido
        }

        // Obtener los datos del cliente desde el modelo
        $clientModel = new \App\Models\ClientModel();
        $cliente = $clientModel->find($idCliente);

        // Validar si se encuentra el cliente
        if (!$cliente || empty($cliente['correo_cliente'])) {
            log_message('error', "No se encontró el cliente o el correo no está disponible para id_cliente: $idCliente");
            return;
        }

        $toEmail = $cliente['correo_cliente']; // Correo dinámico
        $nombreCliente = $cliente['nombre_cliente']; // Nombre dinámico del cliente

        // Crear el objeto Mail para enviar el correo
        $email = new \SendGrid\Mail\Mail();
        $email->setFrom("notificacion.cycloidtalent@cycloidtalent.com", "Cycloid Talent");
        $email->setSubject("Nuevo documento añadido en su aplicación Enterprisesst");
        $email->addTo($toEmail);

        // Crear el contenido del correo, incluyendo el nombre del cliente
        $emailContent = "
        <h3>Estimado/a $nombreCliente</h3>
        <p style='text-align: justify;'>Nos complace informarle que hemos añadido el documento <strong>$tituloReporte</strong> a su aplicación Enterprisesst. Este soporte evidencia los avances de nuestra gestión en Seguridad y Salud en el Trabajo (SG-SST).</p>
        <p style='text-align: justify;'>El documento <strong>$tituloReporte</strong> ya está disponible para su consulta inmediata en la sección de documentos dentro de su aplicación. Le invitamos a acceder a su plataforma de manera ágil y sencilla siguiendo el enlace:</p>
        
       
            <div style='text-align: center;'>
                <a href='https://dashboard.cycloidtalent.com/login' target='_blank' style='display: inline-block; text-decoration: none; text-align: center;'>
                <img src='http://localhost/enterprisesst/public/img/logoenterprisesstblancoslogan.png' alt='Logo Enterprise SST' style='width: 400px; border: none;'>
                <p style='margin: 10px 0 0; color: #0056b3; font-size: 16px; font-weight: bold;'>Haz clic aquí</p>
                </a>
            </div>


        
        <p style='text-align: justify;'>
            En <strong>Cycloid Talent</strong>, nos distinguimos por ser aliados estratégicos en la administración del SG-SST. Nuestro compromiso es ofrecerle soluciones innovadoras y personalizadas que potencien la seguridad y el bienestar en su organización. Con Enterprisesst, no solo recibe herramientas de gestión, sino también el respaldo de un equipo de expertos enfocados en brindarle resultados sobresalientes.
            </p>

            <p style='text-align: justify;'>
            Le recordamos que nuestro equipo está disponible para atender cualquier inquietud o requerimiento adicional que pueda tener. Si necesita orientación sobre cómo aprovechar al máximo este documento o cualquier otro servicio de nuestro portafolio, no dude en ponerse en contacto con nosotros.
            </p>

            <p style='text-align: justify; font-size: 1.1em; font-weight: bold;'>
            Gracias por confiar en Cycloid Talent, donde su tranquilidad y éxito son nuestra prioridad.
            </p>

            <p style='text-align: center; font-size: 0.9em; color: #6c757d;'>
            Para más información, visite nuestra página web o contáctenos directamente a través de nuestros canales de atención.
            </p>

            ";


        $email->addContent("text/html", $emailContent);

        // Cargar la clave API desde las variables de entorno
        $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));

        try {
            // Enviar el correo
            $response = $sendgrid->send($email);
            log_message('debug', 'SendGrid Response Status Code: ' . $response->statusCode());
            log_message('debug', 'SendGrid Response Body: ' . $response->body());
        } catch (\Exception $e) {
            log_message('error', 'Excepción al enviar el correo: ' . $e->getMessage());
        }
    }






    public function editReport($id)
    {
        $reporteModel = new ReporteModel();
        $reportTypeModel = new ReportTypeModel();
        $clientModel = new ClientModel();
        $detailReportModel = new DetailReportModel();

        $report = $reporteModel->find($id);

        if (!$report) {
            return redirect()->to('/reportList')->with('msg', 'Reporte no encontrado.');
        }

        $reportTypes = $reportTypeModel->findAll();
        $clients = $clientModel->findAll();
        $details = $detailReportModel->findAll();

        $data = [
            'report' => $report,
            'reportTypes' => $reportTypes,
            'clients' => $clients,
            'details' => $details
        ];

        return view('consultant/edit_report', $data);
    }

    public function editReportPost($id)
    {
        $reporteModel = new ReporteModel();
        $clientModel = new ClientModel();
        $reportTypeModel = new ReportTypeModel();
        $detailReportModel = new DetailReportModel();

        // Validar existencia del reporte
        $reporte = $reporteModel->find($id);
        if (!$reporte) {
            return redirect()->to('/reportList')->with('msg', 'Reporte no encontrado');
        }

        // Validar existencia del cliente
        $cliente = $clientModel->find($this->request->getVar('id_cliente'));
        if (!$cliente) {
            return redirect()->to('/reportList')->with('msg', 'Cliente no encontrado');
        }

        // Validar existencia de id_report_type
        $reportType = $reportTypeModel->find($this->request->getVar('id_report_type'));
        if (!$reportType) {
            return redirect()->back()->with('msg', 'Tipo de reporte no válido');
        }

        // Validar existencia de id_detailreport
        $detailReport = $detailReportModel->find($this->request->getVar('id_detailreport'));
        if (!$detailReport) {
            return redirect()->back()->with('msg', 'Detalle de reporte no válido');
        }

        $nitCliente = $cliente['nit_cliente'];

        // Procesar datos enviados desde el formulario
        $data = [
            'titulo_reporte' => $this->request->getVar('titulo_reporte'),
            'id_detailreport' => $this->request->getVar('id_detailreport'),
            'id_report_type' => $this->request->getVar('id_report_type'),
            'id_cliente' => $this->request->getVar('id_cliente'),
            'estado' => $this->request->getVar('estado'),
            'observaciones' => $this->request->getVar('observaciones'),
        ];

        // Procesar archivo subido (opcional)
        $file = $this->request->getFile('archivo');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newFileName = $file->getRandomName();
            $clientFolder = ROOTPATH . 'public/uploads/' . $nitCliente;

            // Crear carpeta si no existe
            if (!is_dir($clientFolder)) {
                mkdir($clientFolder, 0777, true);
            }

            // Mover archivo al directorio del cliente
            $file->move($clientFolder, $newFileName);

            // Actualizar enlace en los datos
            $data['enlace'] = base_url('uploads/' . $nitCliente . '/' . $newFileName);
        } else {
            // Mantener el enlace original si no se subió un archivo nuevo
            $data['enlace'] = $reporte['enlace'];
        }

        // Actualizar fecha de modificación
        $data['updated_at'] = date('Y-m-d H:i:s');

        // Actualizar el reporte en la base de datos
        if ($reporteModel->update($id, $data)) {
            return redirect()->to('/reportList')->with('msg', 'Reporte actualizado exitosamente');
        } else {
            return redirect()->to('/reportList')->with('msg', 'Error al actualizar el reporte');
        }
    }


    public function deleteReport($id)
    {
        $reporteModel = new ReporteModel();
        $reporteModel->delete($id);
        return redirect()->to('/reportList')->with('msg', 'Reporte eliminado exitosamente');
    }
}
