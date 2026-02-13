<?php

namespace App\Libraries;

use Dompdf\Dompdf;
use Dompdf\Options;

class ContractPDFGenerator
{
    protected $dompdf;

    public function __construct()
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        $this->dompdf = new Dompdf($options);
        $this->dompdf->setPaper('letter', 'portrait');
    }

    /**
     * Genera el PDF del contrato
     */
    public function generateContract($contractData)
    {
        $html = $this->buildHTML($contractData);
        $this->dompdf->loadHtml($html);
        $this->dompdf->render();

        return $this->dompdf;
    }

    /**
     * Construye el HTML completo del contrato
     */
    private function buildHTML($data)
    {
        $fechaHoy = new \DateTime();

        $html = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body {
                    font-family: Helvetica, Arial, sans-serif;
                    font-size: 10pt;
                    line-height: 1.6;
                    margin: 15mm;
                }
                .header {
                    width: 100%;
                    margin-bottom: 15px;
                }
                .header img {
                    height: 40px;
                }
                .header-left {
                    float: left;
                }
                .header-right {
                    float: right;
                }
                .clearfix::after {
                    content: "";
                    clear: both;
                    display: table;
                }
                h1 {
                    font-size: 14pt;
                    text-align: center;
                    margin: 10px 0;
                }
                h2 {
                    font-size: 11pt;
                    text-align: center;
                    margin: 5px 0 15px 0;
                }
                h3 {
                    font-size: 11pt;
                    text-align: center;
                    margin: 10px 0;
                }
                .clause-title {
                    font-weight: bold;
                    font-size: 10pt;
                    margin-top: 10px;
                    margin-bottom: 5px;
                }
                .content {
                    text-align: justify;
                    margin-bottom: 10px;
                }
                .signatures {
                    margin-top: 30px;
                    width: 100%;
                }
                .signature-box {
                    width: 45%;
                    display: inline-block;
                    vertical-align: top;
                    text-align: center;
                }
                .signature-line {
                    border-top: 1px solid black;
                    margin-top: 60px;
                    padding-top: 5px;
                }
                .signature-center {
                    text-align: center;
                    margin-top: 30px;
                }
                .page-break {
                    page-break-before: always;
                }
            </style>
        </head>
        <body>';

        // Header con logos
        $logoCycloid = FCPATH . 'uploads/logocycloidsinfondo.png';
        $logoSST = FCPATH . 'uploads/logosst.png';

        $html .= '<div class="header clearfix">';
        if (file_exists($logoCycloid)) {
            $logoData = base64_encode(file_get_contents($logoCycloid));
            $html .= '<div class="header-left"><img src="data:image/png;base64,' . $logoData . '"></div>';
        }
        if (file_exists($logoSST)) {
            $logoData = base64_encode(file_get_contents($logoSST));
            $html .= '<div class="header-right"><img src="data:image/png;base64,' . $logoData . '"></div>';
        }
        $html .= '</div>';

        // Título
        $html .= '<h1>CONTRATO DE PRESTACIÓN DE SERVICIOS</h1>';
        $html .= '<h2>ENTRE ' . strtoupper($data['nombre_cliente']) . ' - PROPIEDAD HORIZONTAL Y CYCLOID TALENT S.A.S.</h2>';

        // Introducción
        $html .= '<div class="content">' . $this->buildIntroduction($data) . '</div>';

        // Cláusulas
        $html .= '<h3>CLÁUSULAS</h3>';

        $html .= $this->addClause('PRIMERA - OBJETO DEL CONTRATO', $this->buildClausulaObjeto($data));
        $html .= $this->addClause('SEGUNDA - EJECUCIÓN DEL CONTRATO', $this->buildClausulaEjecucion($data));
        $html .= $this->addClause('TERCERA - OBLIGACIONES', $this->buildClausulaObligaciones());
        $html .= $this->addClause('CUARTA - DURACIÓN', $this->buildClausulaDuracion($data));
        $html .= $this->addClause('QUINTA - EXCLUSIÓN DE LA RELACIÓN LABORAL', $this->buildClausulaExclusionLaboral());
        $html .= $this->addClause('SEXTA - CLÁUSULA DE CONFIDENCIALIDAD', $this->buildClausulaConfidencialidad());
        $html .= $this->addClause('SÉPTIMA - VALOR DEL CONTRATO - FORMA DE PAGO Y PENALIDADES', $this->buildClausulaValor($data));
        $html .= $this->addClause('OCTAVA - PROCEDENCIA DE RECURSOS', $this->buildClausulaProcedencia());
        $html .= $this->addClause('DÉCIMA - LEALTAD PROFESIONAL', $this->buildClausulaLealtad());
        $html .= $this->addClause('ONCEAVA - PREVENCIÓN DEL RIESGO DE LAVADO DE ACTIVOS', $this->buildClausulaSAGRILAFT());
        $html .= $this->addClause('DOCEAVA - ALTO RIESGO EN LA COPROPIEDAD', $this->buildClausulaAltoRiesgo());
        $html .= $this->addClause('TRECEAVA - AUTORIZACIÓN PARA USO DIGITAL DE LA FIRMA', $this->buildClausulaFirmaDigital());

        // Terminación
        $html .= '<div class="content">' . $this->buildTerminacion() . '</div>';

        // Firmas
        $html .= $this->buildSignaturesHTML($data);

        $html .= '</body></html>';

        return $html;
    }

    /**
     * Agrega una cláusula al HTML
     */
    private function addClause($title, $content)
    {
        return '<div class="clause-title">' . $title . '</div><div class="content">' . $content . '</div>';
    }

    /**
     * Construye la introducción del contrato
     */
    private function buildIntroduction($data)
    {
        $intro = "Entre <b>" . strtoupper($data['nombre_cliente']) . "</b> NIT <b>" . $data['nit_cliente'] . "</b>; entidad legalmente existente y constituida, ";
        $intro .= "con domicilio principal en " . $data['direccion_cliente'] . ", representado por ";
        $intro .= "<b>" . strtoupper($data['nombre_rep_legal_cliente']) . "</b>, mayor de edad, identificada con cédula de ciudadanía número ";
        $intro .= "<b>" . $data['cedula_rep_legal_cliente'] . "</b>, en adelante y para los efectos del presente contrato se denominará <b>EL CONTRATANTE</b> de una parte, ";
        $intro .= "y de la otra <b>CYCLOID TALENT S.A.S</b>, NIT. <b>901.653.912-2</b>; entidad legalmente existente y constituida, ";
        $intro .= "con domicilio principal en la ciudad de Soacha Cundinamarca, Cl 13 No. 31 - 106, representada por ";
        $intro .= "<b>" . strtoupper($data['nombre_rep_legal_contratista']) . "</b>, mayor de edad, identificada con cédula de ciudadanía número ";
        $intro .= "<b>" . $data['cedula_rep_legal_contratista'] . "</b>, en adelante y para los efectos del presente contrato se denominará <b>EL CONTRATISTA</b>, ";
        $intro .= "han acordado celebrar un contrato de prestación de servicios el cual se regirá por las siguientes:";

        return $intro;
    }

    /**
     * Cláusula Primera - Objeto
     */
    private function buildClausulaObjeto($data)
    {
        // Si hay texto personalizado (generado con IA o escrito manualmente), usarlo
        if (!empty($data['clausula_primera_objeto'])) {
            return nl2br($data['clausula_primera_objeto']);
        }

        // Fallback: texto hardcodeado
        $texto = "<b>EL CONTRATISTA</b> se compromete a proporcionar servicios de consultoría para la gestión del Sistema de Gestión de Seguridad y Salud en el Trabajo (SG-SST) a favor de <b>EL CONTRATANTE</b> mediante la plataforma <b>EnterpriseSST</b>. ";
        $texto .= "Además, se asignará al profesional SG-SST <b>" . $data['nombre_responsable_sgsst'] . "</b>, identificado con cédula de ciudadanía <b>" . $data['cedula_responsable_sgsst'] . "</b> y licencia ocupacional número <b>" . $data['licencia_responsable_sgsst'] . "</b>, para garantizar el cumplimiento de los estándares mínimos de la <b>Resolución 0312 de 2019</b>.";

        return $texto;
    }

    /**
     * Cláusula Segunda - Ejecución
     */
    private function buildClausulaEjecucion($data)
    {
        $frecuencia = strtoupper($data['frecuencia_visitas']);
        $texto = "La ejecución de este contrato se realizará principalmente mediante la plataforma <b>EnterpriseSST</b>. ";
        $texto .= "Adicionalmente, <b>EL CONTRATISTA</b> llevará a cabo visitas presenciales periódicas, con una frecuencia mínima <b>" . $frecuencia . "</b>.";

        return $texto;
    }

    /**
     * Cláusula Tercera - Obligaciones
     */
    private function buildClausulaObligaciones()
    {
        $texto = "Las partes se comprometen a cumplir con las siguientes obligaciones:<br><br>";
        $texto .= "<b>DE PARTE DEL CONTRATANTE:</b><br>";
        $texto .= "1. Realizar el pago del valor estipulado en la cláusula séptima.<br>";
        $texto .= "2. Verificar los documentos proporcionados por <b>EL CONTRATISTA</b>.<br>";
        $texto .= "3. Participar activamente en la construcción y ejecución de los planes de acción.<br><br>";
        $texto .= "<b>OBLIGACIONES DE EL CONTRATISTA:</b><br>";
        $texto .= "1. Evaluar los estándares mínimos según la <b>Resolución 0312 de 2019</b>.<br>";
        $texto .= "2. Mantener y actualizar continuamente el sistema de gestión de SST.<br>";
        $texto .= "3. Proporcionar todos los documentos y reportes requeridos.";

        return $texto;
    }

    /**
     * Cláusula Cuarta - Duración
     */
    private function buildClausulaDuracion($data)
    {
        if (!empty($data['clausula_cuarta_duracion'])) {
            return nl2br($data['clausula_cuarta_duracion']);
        }

        $fechaInicio = new \DateTime($data['fecha_inicio']);
        $fechaFin = new \DateTime($data['fecha_fin']);
        $diff = $fechaInicio->diff($fechaFin);
        $meses = ($diff->y * 12) + $diff->m;

        $texto = "La duración de este contrato es de <b>" . $meses . " meses</b> desde el <b>" . $fechaInicio->format('d/m/Y') . "</b>";
        $texto .= " hasta el <b>" . $fechaFin->format('d/m/Y') . "</b>.<br><br>";
        $texto .= "<b>PARÁGRAFO:</b> Sobre el presente contrato no opera la prórroga automática.";

        return $texto;
    }

    /**
     * Cláusula Quinta - Exclusión Laboral
     */
    private function buildClausulaExclusionLaboral()
    {
        return "Dada la naturaleza de este contrato, no existirá relación laboral alguna entre <b>EL CONTRATANTE</b> y <b>EL CONTRATISTA</b>.";
    }

    /**
     * Cláusula Sexta - Confidencialidad
     */
    private function buildClausulaConfidencialidad()
    {
        return "<b>EL CONTRATISTA</b> deberá mantener la confidencialidad sobre toda la información de <b>EL CONTRATANTE</b> que conozca o a la que tenga acceso.";
    }

    /**
     * Cláusula Séptima - Valor y Forma de Pago
     */
    private function buildClausulaValor($data)
    {
        $valorTotal = number_format($data['valor_contrato'], 0, ',', '.');
        $valorMensual = number_format($data['valor_mensual'], 0, ',', '.');

        $texto = "El valor del presente contrato es de <b>$" . $valorTotal . " PESOS M/CTE ANTES DE IVA</b>, ";
        $texto .= "pagadero de forma <b>MENSUAL</b> en <b>" . $data['numero_cuotas'] . " cuotas</b> de <b>$" . $valorMensual . "</b>.<br><br>";
        $texto .= "Las facturas deberán ser pagadas dentro de los <b>ocho (8) días calendario</b>.";

        return $texto;
    }

    /**
     * Cláusula Octava - Procedencia de Recursos
     */
    private function buildClausulaProcedencia()
    {
        return "<b>EL CONTRATANTE</b> declara que los recursos son de procedencia lícita y no están vinculados con el lavado de activos.";
    }

    /**
     * Cláusula Décima - Lealtad Profesional
     */
    private function buildClausulaLealtad()
    {
        return "Las partes acuerdan que no podrán vincular laboralmente personal del cual hubieran conocido su desempeño profesional a causa de este contrato.";
    }

    /**
     * Cláusula Onceava - SAGRILAFT
     */
    private function buildClausulaSAGRILAFT()
    {
        return "<b>LAS PARTES</b> certifican que sus recursos no provienen ni se destinan al ejercicio de ninguna actividad ilícita.";
    }

    /**
     * Cláusula Doceava - Alto Riesgo
     */
    private function buildClausulaAltoRiesgo()
    {
        return "Toda actividad de alto riesgo deberá contar con la aprobación y revisión documental antes de su ejecución.";
    }

    /**
     * Cláusula Treceava - Firma Digital
     */
    private function buildClausulaFirmaDigital()
    {
        return "<b>EL CONTRATANTE</b> autoriza expresamente a <b>EL CONTRATISTA</b> a utilizar la firma digital para documentos del SG-SST.";
    }

    /**
     * Terminación del Contrato
     */
    private function buildTerminacion()
    {
        return "<b>TERMINACIÓN DEL CONTRATO:</b> El presente contrato se terminará por mutuo acuerdo, incumplimiento de obligaciones, o cualquier otra causa prevista en la ley.";
    }

    /**
     * Genera una etiqueta <img> con la firma en base64 si el archivo existe.
     * Acepta ruta completa o nombre de archivo (busca en uploads/).
     */
    private function getSignatureImage($filePath, $height = 60)
    {
        log_message('info', '[ContractPDF] getSignatureImage() input: ' . ($filePath ?: '(vacío)'));

        if (empty($filePath)) {
            log_message('warning', '[ContractPDF] Firma vacía, retornando cadena vacía');
            return '';
        }

        // Construir lista de rutas candidatas
        $candidates = [$filePath];
        $basename = basename($filePath);
        if ($basename !== $filePath) {
            // Si es ruta completa, también probar alternativas
            $candidates[] = FCPATH . 'uploads/' . $basename;
            $candidates[] = ROOTPATH . 'public/uploads/' . $basename;
        } else {
            // Si solo es nombre de archivo, buscar en ubicaciones comunes
            $candidates = [
                FCPATH . 'uploads/' . $filePath,
                ROOTPATH . 'public/uploads/' . $filePath,
            ];
        }

        foreach ($candidates as $path) {
            log_message('info', '[ContractPDF] Probando: ' . $path . ' → exists=' . (file_exists($path) ? 'SI' : 'NO'));
            if (file_exists($path) && is_file($path)) {
                $firmaData = file_get_contents($path);
                $firmaMime = mime_content_type($path);
                $base64 = base64_encode($firmaData);
                log_message('info', '[ContractPDF] Firma OK: ' . $path . ' (' . strlen($firmaData) . ' bytes, mime=' . $firmaMime . ')');
                return '<img src="data:' . $firmaMime . ';base64,' . $base64 . '" height="' . $height . '">';
            }
        }

        log_message('error', '[ContractPDF] Firma NO encontrada en ninguna ruta para: ' . $filePath);
        return '';
    }

    /**
     * Construye el HTML de las firmas
     */
    private function buildSignaturesHTML($data)
    {
        $fechaHoy = new \DateTime();

        log_message('info', '[ContractPDF] === GENERANDO FIRMAS ===');
        log_message('info', '[ContractPDF] FCPATH = ' . FCPATH);
        log_message('info', '[ContractPDF] firma_representante_legal = ' . ($data['firma_representante_legal'] ?? 'NULL'));
        log_message('info', '[ContractPDF] firma_consultor = ' . ($data['firma_consultor'] ?? 'NULL'));

        // Firma del representante legal de Cycloid (FIRMA_DIANITA.jpg)
        $firmaContratista = $this->getSignatureImage(FCPATH . 'img/FIRMA_DIANITA.jpg');

        // Firma del representante legal del cliente
        // Primero intentar firma digital (del sistema de firma electrónica de contratos)
        $firmaCliente = '';
        if (!empty($data['firma_cliente_imagen']) && file_exists(FCPATH . $data['firma_cliente_imagen'])) {
            $firmaCliente = $this->getSignatureImage(FCPATH . $data['firma_cliente_imagen']);
        } elseif (!empty($data['firma_representante_legal'])) {
            $firmaCliente = $this->getSignatureImage($data['firma_representante_legal']);
        }

        // Firma del consultor (responsable SG-SST)
        $firmaConsultor = '';
        if (!empty($data['firma_consultor'])) {
            $firmaConsultor = $this->getSignatureImage($data['firma_consultor']);
        }

        log_message('info', '[ContractPDF] Resultados: contratista=' . ($firmaContratista ? 'SI' : 'NO') .
            ', cliente=' . ($firmaCliente ? 'SI' : 'NO') .
            ', consultor=' . ($firmaConsultor ? 'SI' : 'NO'));

        $html = '<div style="margin-top: 30px;">';
        $html .= '<p>Las partes firman el presente documento el día ' . $fechaHoy->format('d/m/Y') . '.</p>';

        $html .= '<table style="width: 100%; margin-top: 30px;">';
        $html .= '<tr>';

        // Firma contratista (Cycloid)
        $html .= '<td style="width: 45%; text-align: center; vertical-align: bottom;">';
        if ($firmaContratista) {
            $html .= '<div style="margin-bottom: 5px;">' . $firmaContratista . '</div>';
        } else {
            $html .= '<div style="height: 60px;"></div>';
        }
        $html .= '<div style="border-top: 1px solid black; padding-top: 5px;">';
        $html .= '<b>' . strtoupper($data['nombre_rep_legal_contratista']) . '</b><br>';
        $html .= 'C.C. ' . $data['cedula_rep_legal_contratista'] . '<br>';
        $html .= 'Representante Legal<br>';
        $html .= '<b>CYCLOID TALENT S.A.S.</b>';
        $html .= '</div></td>';

        $html .= '<td style="width: 10%;"></td>';

        // Firma cliente (representante legal)
        $html .= '<td style="width: 45%; text-align: center; vertical-align: bottom;">';
        if ($firmaCliente) {
            $html .= '<div style="margin-bottom: 5px;">' . $firmaCliente . '</div>';
        } else {
            $html .= '<div style="height: 60px;"></div>';
        }
        $html .= '<div style="border-top: 1px solid black; padding-top: 5px;">';
        $html .= '<b>' . strtoupper($data['nombre_rep_legal_cliente']) . '</b><br>';
        $html .= 'C.C. ' . $data['cedula_rep_legal_cliente'] . '<br>';
        $html .= 'Representante Legal<br>';
        $html .= '<b>' . strtoupper($data['nombre_cliente']) . '</b>';
        $html .= '</div></td>';

        $html .= '</tr>';
        $html .= '</table>';

        // Responsable SG-SST (consultor)
        $html .= '<div style="text-align: center; margin-top: 40px;">';
        $html .= '<p><b>RESPONSABLE SG-SST ASIGNADO</b></p>';
        if ($firmaConsultor) {
            $html .= '<div style="margin-bottom: 5px;">' . $firmaConsultor . '</div>';
        } else {
            $html .= '<div style="height: 60px;"></div>';
        }
        $html .= '<div style="border-top: 1px solid black; width: 200px; margin: 0 auto; padding-top: 5px;">';
        $html .= '<b>' . strtoupper($data['nombre_responsable_sgsst']) . '</b><br>';
        $html .= 'C.C. ' . $data['cedula_responsable_sgsst'] . '<br>';
        $html .= 'Profesional SG-SST';
        $html .= '</div></div>';

        $html .= '</div>';

        return $html;
    }

    /**
     * Guarda el PDF en la ruta especificada
     */
    public function save($filePath)
    {
        file_put_contents($filePath, $this->dompdf->output());
        return $filePath;
    }

    /**
     * Descarga el PDF directamente
     */
    public function download($filename)
    {
        $this->dompdf->stream($filename, ['Attachment' => true]);
    }

    /**
     * Retorna el PDF como string
     */
    public function getString()
    {
        return $this->dompdf->output();
    }

    /**
     * Muestra el PDF en el navegador
     */
    public function stream($filename)
    {
        $this->dompdf->stream($filename, ['Attachment' => false]);
    }
}
