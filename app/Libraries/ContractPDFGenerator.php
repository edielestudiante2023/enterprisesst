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
        $html .= '<h2>ENTRE ' . strtoupper($data['nombre_cliente']) . ' Y CYCLOID TALENT S.A.S.</h2>';

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
        $html .= $this->addClause('NOVENA - CESIÓN', $this->buildClausulaCesion());
        $html .= $this->addClause('DÉCIMA - LEALTAD PROFESIONAL', $this->buildClausulaLealtad());
        $html .= $this->addClause('ONCEAVA - PREVENCIÓN DEL RIESGO DE LAVADO DE ACTIVOS, FINANCIACIÓN DEL TERRORISMO Y FINANCIACIÓN DE LA PROLIFERACIÓN DE ARMAS DE DESTRUCCIÓN MASIVA - SAGRILAFT', $this->buildClausulaSAGRILAFT());
        $html .= $this->addClause('DOCEAVA - ALTO RIESGO EN LA EMPRESA', $this->buildClausulaAltoRiesgo());
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
        $intro = "Entre <b>" . strtoupper($data['nombre_cliente']) . "</b>, NIT <b>" . $data['nit_cliente'] . "</b>; entidad legalmente existente y constituida, ";
        $intro .= "con domicilio principal en la ciudad de " . ($data['direccion_cliente'] ?? '') . " representada por ";
        $intro .= "<b>" . strtoupper($data['nombre_rep_legal_cliente']) . "</b>, mayor de edad, identificado con cédula de ciudadanía número ";
        $intro .= "<b>" . $data['cedula_rep_legal_cliente'] . "</b> en adelante y para los efectos del presente contrato se denominará <b>EL CONTRATANTE</b> de una parte, ";
        $intro .= "y de la otra <b>CYCLOID TALENT S.A.S</b>, NIT. <b>901.653.912-2</b>; entidad legalmente existente y constituida, ";
        $intro .= "con domicilio principal en la ciudad de Bogotá, CRA 78K N. 65F-10 SUR representada por ";
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

        // Texto completo basado en modelo de contrato
        $texto = "<b>EL CONTRATISTA</b> se compromete a proporcionar servicios de consultoría para la gestión del Sistema de Gestión de Seguridad y Salud en el Trabajo (SG-SST) a favor de <b>EL CONTRATANTE</b> mediante la plataforma <b>EnterpriseSST</b>. ";
        $texto .= "Esta plataforma facilita la gestión documental, la programación de actividades y el monitoreo en tiempo real de los planes de trabajo. ";
        $texto .= "Además, se asignará al profesional en SG-SST <b>" . strtoupper($data['nombre_responsable_sgsst']) . "</b>, identificado con cédula de ciudadanía número <b>" . $data['cedula_responsable_sgsst'] . "</b> y licencia ocupacional número <b>" . $data['licencia_responsable_sgsst'] . "</b>, ";
        $texto .= "para garantizar el cumplimiento de los estándares mínimos de la <b>Resolución 0312 de 2019</b>. ";
        $texto .= "Estos servicios incluirán la supervisión y seguimiento continuo del sistema, la capacitación constante de los colaboradores, y la implementación de medidas preventivas que contribuyan a mejorar la seguridad laboral. ";
        $texto .= "A través de EnterpriseSST, se realizará una gestión integral, permitiendo la automatización de reportes, la programación de actividades preventivas y el seguimiento de indicadores de desempeño en tiempo real, ";
        $texto .= "asegurando que todas las acciones realizadas estén alineadas con los requisitos legales y los objetivos del sistema de gestión para la empresa.";

        return $texto;
    }

    /**
     * Cláusula Segunda - Ejecución
     */
    private function buildClausulaEjecucion($data)
    {
        $frecuencia = strtoupper($data['frecuencia_visitas']);
        $texto = "La ejecución de este contrato se realizará principalmente mediante la plataforma <b>EnterpriseSST</b>, que proporcionará acceso continuo a toda la documentación, cronogramas y recursos necesarios para la gestión del SG-SST. ";
        $texto .= "La utilización de la plataforma permitirá a <b>EL CONTRATANTE</b> monitorear el avance de todas las actividades en tiempo real, proporcionando transparencia y control sobre cada aspecto del sistema. ";
        $texto .= "Adicionalmente, <b>EL CONTRATISTA</b> llevará a cabo visitas presenciales periódicas, con una frecuencia <b>" . $frecuencia . "</b> en las instalaciones principales de <b>EL CONTRATANTE</b>. ";
        $texto .= "Dichas visitas incluirán inspecciones detalladas para evaluar las condiciones de trabajo, verificar el uso adecuado de los equipos de protección personal (EPP), y fomentar una cultura de seguridad a través de la interacción directa con los colaboradores. ";
        $texto .= "Estas visitas también permitirán detectar posibles desviaciones o riesgos no documentados y tomar medidas correctivas inmediatas para mitigar cualquier amenaza a la seguridad y salud en la empresa. ";
        $texto .= "Al final de cada visita, se realizará un informe detallado que será compartido con <b>EL CONTRATANTE</b> para asegurar la trazabilidad de las acciones tomadas.";

        return $texto;
    }

    /**
     * Cláusula Tercera - Obligaciones
     */
    private function buildClausulaObligaciones()
    {
        $texto = "Las partes se comprometen a cumplir con las siguientes obligaciones:<br><br>";

        $texto .= "<b>DE PARTE DEL CONTRATANTE:</b><br><br>";
        $texto .= "1. Realizar el pago del valor estipulado en la cláusula séptima, asegurando que las obligaciones financieras se cumplan en los plazos establecidos, para evitar interrupciones en la prestación de servicios.<br>";
        $texto .= "2. Verificar los documentos proporcionados por <b>EL CONTRATISTA</b>, que acrediten su idoneidad y profesionalismo, tales como exámenes médicos periódicos, certificados de trabajo en alturas, afiliación y pago de aportes al sistema de seguridad social, entre otros.<br>";
        $texto .= "3. Participar activamente en la construcción y ejecución de los planes de acción propuestos por <b>EL CONTRATISTA</b> a través de la plataforma EnterpriseSST.<br>";
        $texto .= "4. Asegurar el acceso y uso adecuado de la plataforma EnterpriseSST por parte de todos los actores relevantes, garantizando la capacitación del personal involucrado en su uso.<br>";
        $texto .= "5. En caso de ser necesario, contratar a un profesional idóneo para auditar la gestión llevada a cabo por <b>EL CONTRATISTA</b>.<br><br>";

        $texto .= "<b>OBLIGACIONES DE EL CONTRATISTA:</b><br><br>";
        $texto .= "1. Evaluar los estándares mínimos según la <b>Resolución 0312 de 2019</b>, demostrando un nivel de cumplimiento igual o superior al 89.75%, y registrar esta información en la plataforma EnterpriseSST.<br>";
        $texto .= "2. Mantener y actualizar continuamente el sistema de gestión de SST en la plataforma EnterpriseSST, alineando los procesos con los cambios normativos y las mejores prácticas del sector.<br>";
        $texto .= "3. Proporcionar todos los documentos, reportes y evidencias requeridos a través de la plataforma EnterpriseSST, garantizando el acceso en tiempo real a <b>EL CONTRATANTE</b>.<br>";
        $texto .= "4. Realizar modificaciones necesarias a los formatos de gestión, previa aprobación de la gerencia, manteniéndolos disponibles en la plataforma.<br>";
        $texto .= "5. Planificar, organizar y dirigir las actividades que promuevan el cumplimiento de los estándares mínimos utilizando la gestión proporcionada por EnterpriseSST. Dichas actividades incluirán capacitaciones regulares, simulacros de emergencia, y campañas de concienciación sobre riesgos laborales.<br>";
        $texto .= "6. Reportar al Ministerio de Trabajo, manteniendo toda la información y evidencia debidamente documentada en la plataforma EnterpriseSST.<br>";
        $texto .= "7. Realizar visitas en las instalaciones cuando sea necesario, documentando las observaciones y gestionando los reportes a través de EnterpriseSST.<br>";
        $texto .= "8. Entregar informes detallados de cada visita y auditoría, especificando las acciones correctivas recomendadas y el seguimiento correspondiente.";

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

        $texto = "La duración de este contrato es de <b>(" . $meses . ") meses</b> contados a partir de la fecha de la firma y con finalización máxima a <b>" . $fechaFin->format('d') . " de " . $this->mesEnEspanol($fechaFin->format('n')) . " de " . $fechaFin->format('Y') . "</b>.<br><br>";
        $texto .= "<b>PARÁGRAFO PRIMERO:</b> Sobre el presente contrato no opera la prórroga automática. Por lo anterior, la intención de prórroga deberá ser discutida entre las partes al finalizar el plazo inicialmente aquí pactado y deberá constar por escrito.";

        return $texto;
    }

    /**
     * Retorna el nombre del mes en español
     */
    private function mesEnEspanol($mes)
    {
        $meses = [1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril', 5 => 'mayo', 6 => 'junio',
                  7 => 'julio', 8 => 'agosto', 9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'];
        return $meses[(int)$mes] ?? '';
    }

    /**
     * Cláusula Quinta - Exclusión Laboral
     */
    private function buildClausulaExclusionLaboral()
    {
        return "Dada la naturaleza de este contrato, no existirá relación laboral alguna entre <b>EL CONTRATANTE</b> y <b>EL CONTRATISTA</b>, o el personal que éste contrate para apoyar la ejecución del objeto contractual. <b>EL CONTRATISTA</b> se compromete con <b>EL CONTRATANTE</b> a ejecutar en forma independiente y con plena autonomía técnica, el objeto mencionado en este documento.";
    }

    /**
     * Cláusula Sexta - Confidencialidad
     */
    private function buildClausulaConfidencialidad()
    {
        $texto = "<b>EL CONTRATISTA</b> deberá mantener la confidencialidad sobre toda la información de <b>EL CONTRATANTE</b> que conozca o a la que tenga acceso. ";
        $texto .= "Se tendrá como información confidencial cualquier información no divulgada que posea legítimamente <b>EL CONTRATANTE</b> que pueda usarse en alguna actividad académica, productiva, industrial o comercial y que sea susceptible de comunicarse a un tercero. ";
        $texto .= "La información confidencial incluye también toda información recibida de terceros que <b>EL CONTRATISTA</b> está obligado a tratar como confidencial, así como las informaciones orales que <b>EL CONTRATANTE</b> identifique como confidencial.";
        return $texto;
    }

    /**
     * Cláusula Séptima - Valor y Forma de Pago
     */
    private function buildClausulaValor($data)
    {
        $valorTotal = number_format($data['valor_contrato'], 0, ',', '.');
        $valorMensual = number_format($data['valor_mensual'], 0, ',', '.');
        $numCuotas = $data['numero_cuotas'];
        $banco = $data['banco'] ?? 'Davivienda';
        $tipoCuenta = $data['tipo_cuenta'] ?? 'ahorros';
        $cuentaBancaria = $data['cuenta_bancaria'] ?? '108900260762';

        $fechaInicio = new \DateTime($data['fecha_inicio']);
        $fechaFin = new \DateTime($data['fecha_fin']);
        $mesInicio = ucfirst($this->mesEnEspanol($fechaInicio->format('n'))) . ' ' . $fechaInicio->format('Y');
        $mesFin = ucfirst($this->mesEnEspanol($fechaFin->format('n'))) . ' ' . $fechaFin->format('Y');

        $texto = "El valor del presente contrato es de <b>($" . $valorTotal . ") PESOS M/CTE ANTES DE IVA</b>, ";
        $texto .= "los cuales serán pagados de forma <b>MENSUAL</b> en <b>" . $numCuotas . " facturas</b> por valor de <b>($" . $valorMensual . ") ANTES DE IVA</b> ";
        $texto .= "desde " . $mesInicio . " a " . $mesFin . ", sujeto a la revisión de las actividades realizadas durante el mes y la presentación de reportes en EnterpriseSST. ";
        $texto .= "Las facturas emitidas por <b>EL CONTRATISTA</b> deberán ser pagadas por <b>EL CONTRATANTE</b> dentro los <b>ocho (8) días calendario</b> contados a partir de la fecha de su emisión. ";
        $texto .= "En caso de que el pago no se realice en el plazo estipulado, el saldo adeudado incurrirá en mora.<br><br>";

        $texto .= "<b>PARÁGRAFO ADICIONAL – AJUSTE POR INCREMENTO DEL SMLV:</b> Las partes acuerdan que el valor mensual del contrato se ajustará automáticamente a partir del 1° de enero del año siguiente, ";
        $texto .= "en el mismo porcentaje en que se incremente el Salario Mínimo Legal Mensual Vigente (SMLV) fijado por el Gobierno Nacional. ";
        $texto .= "Este ajuste se aplicará a los pagos correspondientes y el nuevo valor ajustado se reflejará en la respectiva factura y será reconocido por <b>EL CONTRATANTE</b> conforme a lo estipulado en la presente cláusula.<br><br>";

        $texto .= "<b>INTERESES POR MORA:</b> Si el pago de una factura no se ha realizado a los <b>sesenta (60) días calendario</b> posterior a su fecha de vencimiento, ";
        $texto .= "<b>EL CONTRATANTE</b> deberá pagar a <b>EL CONTRATISTA</b> un interés de mora del <b>uno punto cinco por ciento (1,5%) mensual</b>, calculado sobre el valor base de la factura (antes de impuestos). ";
        $texto .= "Este interés será cobrado en la siguiente factura emitida por <b>EL CONTRATISTA</b> reflejándose como un ajuste adicional al monto total a pagar. ";
        $texto .= "<b>EL CONTRATISTA</b> de manera previa hará la presentación de factura y revisión de la misma, por transferencia bancaria al banco <b>" . $banco . "</b>, cuenta de <b>" . $tipoCuenta . "</b> No. <b>" . $cuentaBancaria . "</b> a nombre de <b>EL CONTRATISTA</b>.<br><br>";

        $texto .= "<b>PARÁGRAFO:</b> Serán requisitos indispensables para el pago que <b>EL CONTRATISTA</b> presente planilla integrada de liquidación de aportes (PILA) que acredite el pago al Sistema General de Seguridad Social Integral.";

        return $texto;
    }

    /**
     * Cláusula Octava - Procedencia de Recursos
     */
    private function buildClausulaProcedencia()
    {
        $texto = "<b>EL CONTRATANTE</b> declara bajo la gravedad de juramento que los recursos, fondos, dineros, activos o bienes relacionados con este contrato, son de procedencia lícita y no están vinculados con el lavado de activos ni con ninguno de sus delitos fuente, ";
        $texto .= "así como que el destino de los recursos, fondos, dineros, activos o bienes producto de los mismos no van a ser destinados para la financiación del terrorismo o cualquier otra conducta delictiva, ";
        $texto .= "de acuerdo con las normas penales y las que sean aplicables en Colombia, sin perjuicio de las acciones legales pertinentes derivadas del incumplimiento de esta declaración.";
        return $texto;
    }

    /**
     * Cláusula Novena - Cesión
     */
    private function buildClausulaCesion()
    {
        return "<b>EL CONTRATISTA</b> no podrá ceder total ni parcialmente, así como subcontratar, la ejecución del presente contrato, salvo previa autorización expresa y escrita de <b>EL CONTRATANTE</b>.";
    }

    /**
     * Cláusula Décima - Lealtad Profesional
     */
    private function buildClausulaLealtad()
    {
        $texto = "Las partes acuerdan que no podrán vincular laboralmente dentro de sus compañías a personal de planta, del cual hubiera conocido su desempeño profesional a causa de la relación que surgió en la ejecución del presente contrato de prestación de servicios. ";
        $texto .= "En caso de que alguna de las partes, omita esta cláusula habrá lugar a efectuar un cobro equivalente a <b>doce (12) salarios mínimos mensuales vigentes</b> por cada trabajador. ";
        $texto .= "Las partes entienden que éste es un reconocimiento del costo incurrido, para contratar y capacitar a este empleado. Las partes reconocen que esto es una estimación previa legítima de los costos por la pérdida de los trabajadores.";
        return $texto;
    }

    /**
     * Cláusula Onceava - SAGRILAFT
     */
    private function buildClausulaSAGRILAFT()
    {
        $texto = "<b>LAS PARTES</b> certifican que sus recursos no provienen ni se destinan al ejercicio de ninguna actividad ilícita o de actividades conexas al lavado de activos, provenientes de éstas o de actividades relacionadas con la financiación del terrorismo. ";
        $texto .= "<b>LAS PARTES</b>, en su calidad de sujetos responsables de contar con un Sistema de Autocontrol y Gestión del Riesgo de Lavado de Activos y Financiación del Terrorismo (SAGRILAFT), ";
        $texto .= "podrán cruzar y solicitar en cualquier momento la información de sus clientes con las listas para el control de lavado de activos y financiación del terrorismo (LA/FT), ";
        $texto .= "administradas por cualquier autoridad nacional o extranjera, tales como la lista de la Oficina de Control de Activos en el Exterior – OFAC emitida por la Oficina del Tesoro de los Estados Unidos de Norte América, ";
        $texto .= "la lista de la Organización de las Naciones Unidas y otras listas públicas relacionadas con el tema del LA/FT (en adelante las Listas). ";
        $texto .= "Las Partes aceptan, entienden y conocen, de manera voluntaria e inequívoca, que, en cumplimiento de su obligación legal de prevenir y controlar el LA/FT, ";
        $texto .= "y atendiendo la normatividad vigente y la jurisprudencia de la Corte Constitucional sobre la materia, podrán terminar unilateralmente el presente contrato, en cualquier momento y sin previo aviso, ";
        $texto .= "cuando las Partes, cualquiera de sus accionistas, socios, miembros de Junta Directiva y/o sus representantes legales llegare a ser: ";
        $texto .= "(i) Vinculado por parte de las autoridades nacionales e internacionales a cualquier tipo de investigación por delitos de narcotráfico, terrorismo, secuestro, lavado de activos, financiación del terrorismo y administración de recursos relacionados con actividades terroristas u otros delitos relacionados con el LA/FT y/o cualquier delito colateral o subyacente a los mencionados (en adelante Delitos de LA/FT); ";
        $texto .= "(ii) Incluido en las listas anteriormente mencionadas o en las que en el futuro se llegaren a conocer; ";
        $texto .= "(iii) Condenado por parte de las autoridades nacionales o internacionales en cualquier tipo de proceso judicial relacionado con la comisión de los Delitos de LA/FT; y/o ";
        $texto .= "(iv) Señalado públicamente por cualquier medio de amplia difusión nacional (Prensa, Radio, televisión, etc.), como investigado por delitos de LA/FT.";
        return $texto;
    }

    /**
     * Cláusula Doceava - Alto Riesgo
     */
    private function buildClausulaAltoRiesgo()
    {
        $texto = "Toda actividad ejecutada dentro de la empresa que implique alto riesgo, como trabajos en espacios confinados o trabajos en alturas, ";
        $texto .= "deberá contar con la aprobación y la revisión documental de los contratistas responsables antes de su ejecución. ";
        $texto .= "En caso de que el profesional a cargo del Sistema de Gestión en Seguridad y Salud en el Trabajo (SST) no tenga conocimiento previo de dichas actividades ";
        $texto .= "y no haya aprobado los documentos ATS (Análisis de Trabajo Seguro) y demás documentos pertinentes, y estas se lleven a cabo sin su supervisión, ";
        $texto .= "<b>EL CONTRATISTA</b> no asumirá ninguna responsabilidad administrativa ni civil por cualquier accidente o incidente que pueda ocurrir.<br><br>";
        $texto .= "<b>PARÁGRAFO:</b> Este contrato podrá ser terminado en caso de incumplimiento de esta cláusula, así como en casos en que se detecte la ejecución de actividades sin la supervisión debida, ";
        $texto .= "considerando dicho incumplimiento como un motivo de terminación justificada.";
        return $texto;
    }

    /**
     * Cláusula Treceava - Firma Digital
     */
    private function buildClausulaFirmaDigital()
    {
        $texto = "<b>EL CONTRATANTE</b> autoriza expresamente a <b>EL CONTRATISTA</b> a utilizar la firma digital consignada en el presente contrato de servicio para su extracción y uso en formato digital. ";
        $texto .= "Esta firma digital podrá ser aplicada a todos los documentos relacionados con el Sistema de Gestión de Seguridad y Salud en el Trabajo (SG-SST), ";
        $texto .= "incluyendo, pero no limitándose a, políticas, objetivos, planes de trabajo, informes, y cualquier otro documento que sea necesario según lo dispuesto en la normatividad vigente en Colombia, ";
        $texto .= "tales como la Resolución 0312 de 2019 o cualquier otra disposición legal aplicable. ";
        $texto .= "El Cliente manifiesta que comprende y acepta que la firma digital extraída tendrá la misma validez y efectos jurídicos que la firma manuscrita original para todos los fines legales y administrativos dentro del marco del SG-SST. ";
        $texto .= "Asimismo, <b>EL CONTRATANTE</b> se compromete a notificar por escrito a <b>EL CONTRATISTA</b> en caso de cualquier cambio en la representación legal que implique una modificación de la firma autorizada. ";
        $texto .= "<b>EL CONTRATISTA</b> se compromete a utilizar la firma digital del Cliente exclusivamente para los fines descritos en la presente cláusula ";
        $texto .= "y a adoptar todas las medidas necesarias para garantizar su seguridad y confidencialidad conforme a la normativa vigente sobre protección de datos y seguridad de la información.";
        return $texto;
    }

    /**
     * Terminación del Contrato
     */
    private function buildTerminacion()
    {
        $texto = "<b>TERMINACIÓN DEL CONTRATO:</b> El presente contrato se terminará por las siguientes causas:<br><br>";
        $texto .= "1. Mutuo acuerdo.<br>";
        $texto .= "2. Incumplimiento de las obligaciones a cargo de cualquiera de las partes.<br>";
        $texto .= "3. Liquidación obligatoria, forzosa, o inicio de cualquier trámite concursal de cualquiera de las partes.<br>";
        $texto .= "4. Inclusión de cualquiera de las partes en listados multilaterales sobre financiación del terrorismo o lavado de activos.<br>";
        $texto .= "5. Actuar en forma contraria a las buenas costumbres o la ética empresarial.<br>";
        $texto .= "6. Cualquier otra causa prevista en la ley o en el presente documento.<br>";
        $texto .= "7. Fuerza mayor o caso fortuito debidamente comprobado.<br>";
        $texto .= "8. Cualquier incumplimiento en la confidencialidad o uso indebido de la información gestionada mediante la plataforma EnterpriseSST.";
        return $texto;
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
        if (!empty($data['email_cliente'])) {
            $html .= '<br>e-mail: ' . $data['email_cliente'];
        }
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
