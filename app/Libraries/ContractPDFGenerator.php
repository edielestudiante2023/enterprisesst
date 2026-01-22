<?php

namespace App\Libraries;

use TCPDF;

class ContractPDFGenerator
{
    protected $pdf;

    public function __construct()
    {
        // Crear instancia de TCPDF
        $this->pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Configuración del documento
        $this->pdf->SetCreator('Cycloid Talent S.A.S.');
        $this->pdf->SetAuthor('Cycloid Talent S.A.S.');
        $this->pdf->SetTitle('Contrato de Prestación de Servicios');

        // Quitar header y footer por defecto
        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);

        // Márgenes
        $this->pdf->SetMargins(15, 15, 15);
        $this->pdf->SetAutoPageBreak(true, 15);

        // Fuente por defecto
        $this->pdf->SetFont('helvetica', '', 10);

        // Aumentar interlineado general para mayor espaciado
        $this->pdf->setCellHeightRatio(1.6);
    }

    /**
     * Genera el PDF del contrato
     */
    public function generateContract($contractData)
    {
        $this->pdf->AddPage();

        // Agregar logos en el encabezado
        $this->addHeader();

        // Título principal
        $this->pdf->SetFont('helvetica', 'B', 14);
        $this->pdf->Cell(0, 10, 'CONTRATO DE PRESTACIÓN DE SERVICIOS', 0, 1, 'C');
        $this->pdf->Ln(3);

        // Subtítulo usando MultiCell para evitar desbordamiento
        $this->pdf->SetFont('helvetica', 'B', 11);
        $titulo = 'ENTRE ' . strtoupper($contractData['nombre_cliente']) . ' - PROPIEDAD HORIZONTAL Y CYCLOID TALENT S.A.S.';
        $this->pdf->MultiCell(0, 7, $titulo, 0, 'C');
        $this->pdf->Ln(4);

        // Introducción (con HTML para negritas)
        $this->pdf->SetFont('helvetica', '', 10);
        $intro = $this->buildIntroduction($contractData);
        $this->pdf->writeHTMLCell(0, 0, '', '', $intro, 0, 1, false, true, 'J');
        $this->pdf->Ln(3);

        // CLÁUSULAS
        $this->pdf->SetFont('helvetica', 'B', 11);
        $this->pdf->Cell(0, 8, 'CLÁUSULAS', 0, 1, 'C');
        $this->pdf->Ln(2);

        // Cláusula Primera - Objeto
        $this->addClause('PRIMERA - OBJETO DEL CONTRATO', $this->buildClausulaObjeto($contractData));

        // Cláusula Segunda - Ejecución
        $this->addClause('SEGUNDA - EJECUCIÓN DEL CONTRATO', $this->buildClausulaEjecucion($contractData));

        // Cláusula Tercera - Obligaciones
        $this->addClause('TERCERA - OBLIGACIONES', $this->buildClausulaObligaciones());

        // Cláusula Cuarta - Duración
        $this->addClause('CUARTA - DURACIÓN', $this->buildClausulaDuracion($contractData));

        // Cláusula Quinta - Exclusión Laboral
        $this->addClause('QUINTA - EXCLUSIÓN DE LA RELACIÓN LABORAL', $this->buildClausulaExclusionLaboral());

        // Cláusula Sexta - Confidencialidad
        $this->addClause('SEXTA - CLÁUSULA DE CONFIDENCIALIDAD', $this->buildClausulaConfidencialidad());

        // Cláusula Séptima - Valor y Forma de Pago
        $this->addClause('SÉPTIMA - VALOR DEL CONTRATO - FORMA DE PAGO Y PENALIDADES', $this->buildClausulaValor($contractData));

        // Cláusula Octava - Procedencia de Recursos
        $this->addClause('OCTAVA - PROCEDENCIA DE RECURSOS', $this->buildClausulaProcedencia());

        // Cláusula Novena - Cesión
        $this->addClause('NOVENA - CESIÓN', $this->buildClausulaCesion());

        // Cláusula Décima - Lealtad Profesional
        $this->addClause('DÉCIMA - LEALTAD PROFESIONAL', $this->buildClausulaLealtad());

        // Cláusula Onceava - SAGRILAFT
        $this->addClause('ONCEAVA - PREVENCIÓN DEL RIESGO DE LAVADO DE ACTIVOS', $this->buildClausulaSAGRILAFT());

        // Cláusula Doceava - Alto Riesgo
        $this->addClause('DOCEAVA - ALTO RIESGO EN LA COPROPIEDAD', $this->buildClausulaAltoRiesgo());

        // Cláusula Treceava - Autorización Firma Digital
        $this->addClause('TRECEAVA - AUTORIZACIÓN PARA USO DIGITAL DE LA FIRMA', $this->buildClausulaFirmaDigital());

        // Terminación del Contrato (con HTML para negritas)
        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->writeHTMLCell(0, 0, '', '', $this->buildTerminacion(), 0, 1, false, true, 'J');
        $this->pdf->Ln(8);

        // Fecha y Firmas
        $this->addSignatures($contractData);

        return $this->pdf;
    }

    /**
     * Agrega el encabezado con logos (tamaño reducido)
     */
    private function addHeader()
    {
        $logoCycloid = FCPATH . 'uploads/logocycloidsinfondo.png';
        $logoSST = FCPATH . 'uploads/logosst.png';

        // Logo Cycloid (izquierda) - reducido a 25mm
        if (file_exists($logoCycloid)) {
            $this->pdf->Image($logoCycloid, 15, 10, 25, 0, 'PNG');
        }

        // Logo SST (derecha) - reducido a 25mm
        if (file_exists($logoSST)) {
            $this->pdf->Image($logoSST, 170, 10, 25, 0, 'PNG');
        }

        $this->pdf->Ln(18);
    }

    /**
     * Agrega una cláusula al documento (soporta HTML para negritas)
     */
    private function addClause($title, $content)
    {
        $this->pdf->SetFont('helvetica', 'B', 10);
        $this->pdf->Cell(0, 6, $title, 0, 1);
        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->writeHTMLCell(0, 0, '', '', $content, 0, 1, false, true, 'J');
        $this->pdf->Ln(3);
    }

    /**
     * Construye la introducción del contrato con negritas en campos clave
     */
    private function buildIntroduction($data)
    {
        // Usar HTML para aplicar negritas a términos clave
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
     * Cláusula Primera - Objeto (con negritas en términos clave)
     */
    private function buildClausulaObjeto($data)
    {
        $texto = "<b>EL CONTRATISTA</b> se compromete a proporcionar servicios de consultoría para la gestión del Sistema de Gestión de Seguridad y Salud en el Trabajo (SG-SST) a favor de <b>EL CONTRATANTE</b> mediante la plataforma <b>EnterpriseSST</b>. Esta plataforma facilita la gestión documental, la programación de actividades y el monitoreo en tiempo real de los planes de trabajo. ";

        $texto .= "Además, se asignará al profesional SG-SST <b>" . $data['nombre_responsable_sgsst'] . "</b>, identificado con cédula de ciudadanía <b>" . $data['cedula_responsable_sgsst'] . "</b> y licencia ocupacional número <b>" . $data['licencia_responsable_sgsst'] . "</b>, para garantizar el cumplimiento de los estándares mínimos de la <b>Resolución 0312 de 2019</b>. ";

        $texto .= "Estos servicios incluirán la supervisión y seguimiento continuo del sistema, la capacitación a colaboradores en misión y la implementación de medidas preventivas que contribuyan a mejorar la seguridad laboral. A través de <b>EnterpriseSST</b>, se realizará una gestión integral, permitiendo la automatización de reportes, la programación de actividades preventivas y el seguimiento de indicadores de desempeño en tiempo real, asegurando que todas las acciones realizadas estén alineadas con los requisitos legales y los objetivos del sistema de gestión.";

        return $texto;
    }

    /**
     * Cláusula Segunda - Ejecución (con negritas en términos clave)
     */
    private function buildClausulaEjecucion($data)
    {
        $frecuencia = strtoupper($data['frecuencia_visitas']);

        $texto = "La ejecución de este contrato se realizará principalmente mediante la plataforma <b>EnterpriseSST</b>, que proporcionará acceso continuo a toda la documentación, cronogramas y recursos necesarios para la gestión del SG-SST. La utilización de la plataforma permitirá a <b>EL CONTRATANTE</b> monitorear el avance de todas las actividades en tiempo real, proporcionando transparencia y control sobre cada aspecto del sistema. ";

        $texto .= "Adicionalmente, <b>EL CONTRATISTA</b> llevará a cabo visitas presenciales periódicas, con una frecuencia mínima <b>" . $frecuencia . "</b> de acuerdo con el cronograma de actividades anual, y concertadas con <b>EL CONTRATANTE</b>. ";

        $texto .= "Estas visitas también permitirán detectar posibles desviaciones o riesgos no documentados y tomar medidas correctivas inmediatas para mitigar cualquier amenaza a la seguridad y salud de la comunidad en la copropiedad. Al final de cada visita, se realizará un informe detallado que será compartido con <b>EL CONTRATANTE</b> para asegurar la trazabilidad de las acciones tomadas.";

        return $texto;
    }

    /**
     * Cláusula Tercera - Obligaciones (con negritas en términos clave)
     */
    private function buildClausulaObligaciones()
    {
        $texto = "Las partes se comprometen a cumplir con las siguientes obligaciones:<br><br>";

        $texto .= "<b>DE PARTE DEL CONTRATANTE:</b><br>";
        $texto .= "1. Realizar el pago del valor estipulado en la cláusula séptima, asegurando que las obligaciones financieras se cumplan en los plazos establecidos.<br>";
        $texto .= "2. Verificar los documentos proporcionados por <b>EL CONTRATISTA</b>, que acrediten su idoneidad, licencia vigente y planilla de seguridad social mensual.<br>";
        $texto .= "3. Participar activamente en la construcción y ejecución de los planes de acción propuestos por <b>EL CONTRATISTA</b> a través de la plataforma <b>EnterpriseSST</b>.<br>";
        $texto .= "4. Asegurar el acceso y uso adecuado de la plataforma <b>EnterpriseSST</b> por parte de todos los actores relevantes.<br>";
        $texto .= "5. En caso de ser necesario, contratar a un profesional idóneo para auditar la gestión llevada a cabo por <b>EL CONTRATISTA</b>.<br><br>";

        $texto .= "<b>OBLIGACIONES DE EL CONTRATISTA:</b><br>";
        $texto .= "1. Evaluar los estándares mínimos según la <b>Resolución 0312 de 2019</b>, demostrando un nivel de cumplimiento igual o superior al <b>86.75%</b>, y registrar esta información en la plataforma <b>EnterpriseSST</b>.<br>";
        $texto .= "2. Mantener y actualizar continuamente el sistema de gestión de SST en la plataforma <b>EnterpriseSST</b>.<br>";
        $texto .= "3. Proporcionar todos los documentos, reportes y evidencias requeridos a través de la plataforma <b>EnterpriseSST</b>, garantizando el acceso en tiempo real a <b>EL CONTRATANTE</b>.<br>";
        $texto .= "4. Realizar modificaciones necesarias a los formatos de gestión, previa aprobación de la administración, manteniendo disponibles en la plataforma.<br>";
        $texto .= "5. Planificar, organizar y dirigir las actividades que promuevan el cumplimiento de los estándares mínimos utilizando la gestión proporcionada por <b>EnterpriseSST</b>. Dichas actividades incluirán capacitaciones regulares, simulacros de emergencia, y campañas de concienciación sobre riesgos laborales.<br>";
        $texto .= "6. Reportar al Ministerio de Trabajo, manteniendo toda la información y evidencia debidamente documentada en la plataforma <b>EnterpriseSST</b>.<br>";
        $texto .= "7. Realizar visitas en campo cuando sea necesario, documentando las observaciones y gestionando los reportes a través de <b>EnterpriseSST</b>.<br>";
        $texto .= "8. Entregar informes detallados de cada visita y auditoría, especificando las acciones correctivas recomendadas y el seguimiento correspondiente.";

        return $texto;
    }

    /**
     * Cláusula Cuarta - Duración (personalizable o texto por defecto)
     */
    private function buildClausulaDuracion($data)
    {
        // Si existe texto personalizado en clausula_cuarta_duracion, usar ese
        if (!empty($data['clausula_cuarta_duracion'])) {
            // Convertir saltos de línea a <br> para HTML
            $textoPersonalizado = nl2br($data['clausula_cuarta_duracion']);

            // Aplicar formato de negritas a términos clave comunes
            $textoPersonalizado = $this->aplicarNegritasTerminosClave($textoPersonalizado);

            return $textoPersonalizado;
        }

        // Si no hay texto personalizado, usar el formato por defecto
        $fechaInicio = new \DateTime($data['fecha_inicio']);
        $fechaFin = new \DateTime($data['fecha_fin']);
        $diff = $fechaInicio->diff($fechaFin);
        $meses = ($diff->y * 12) + $diff->m;

        $texto = "<b>CUARTA-DURACIÓN:</b> La duración de este contrato es de <b>(" . $meses . ") meses</b> desde el <b>" . $fechaInicio->format('d') . " de " . $this->getMesNombre($fechaInicio->format('m')) . " de " . $fechaInicio->format('Y') . "</b>";
        $texto .= " y con finalización máxima a <b>" . $fechaFin->format('d') . " de " . $this->getMesNombre($fechaFin->format('m')) . " de " . $fechaFin->format('Y') . "</b>.<br><br>";
        $texto .= "<b>PARÁGRAFO:</b> Sobre el presente contrato no opera la prórroga automática. Por lo anterior, la intención de prórroga deberá ser discutida entre las partes al finalizar el plazo inicialmente aquí pactado y deberá constar por escrito.";

        return $texto;
    }

    /**
     * Aplica negritas a términos clave en el texto personalizado
     */
    private function aplicarNegritasTerminosClave($texto)
    {
        // Términos clave que deben ir en negrita
        $terminosClave = [
            'CUARTA-DURACIÓN:',
            'CUARTA-PLAZO DE EJECUCIÓN:',
            'PARÁGRAFO PRIMERO:',
            'PARÁGRAFO SEGUNDO:',
            'PARÁGRAFO:',
            'EL CONTRATANTE',
            'EL CONTRATISTA',
            'EnterpriseSST',
            'Cycloid Talent',
            'CYCLOID TALENT S.A.S.'
        ];

        foreach ($terminosClave as $termino) {
            // Evitar duplicar las etiquetas <b> si ya existen
            if (stripos($texto, '<b>' . $termino . '</b>') === false) {
                $texto = str_ireplace($termino, '<b>' . $termino . '</b>', $texto);
            }
        }

        return $texto;
    }

    /**
     * Convierte número de mes a nombre
     */
    private function getMesNombre($mes)
    {
        $meses = [
            '01' => 'enero', '02' => 'febrero', '03' => 'marzo', '04' => 'abril',
            '05' => 'mayo', '06' => 'junio', '07' => 'julio', '08' => 'agosto',
            '09' => 'septiembre', '10' => 'octubre', '11' => 'noviembre', '12' => 'diciembre'
        ];
        return $meses[$mes];
    }

    /**
     * Cláusula Quinta - Exclusión Laboral (con negritas en términos clave)
     */
    private function buildClausulaExclusionLaboral()
    {
        return "<b>QUINTA-EXCLUSIÓN DE LA RELACIÓN LABORAL:</b> Dada la naturaleza de este contrato, no existirá relación laboral alguna entre <b>EL CONTRATANTE</b> y <b>EL CONTRATISTA</b>, o el personal que éste contrate para apoyar la ejecución del objeto contractual. <b>EL CONTRATISTA</b> se compromete con <b>EL CONTRATANTE</b> a ejecutar en forma independiente y con plena autonomía técnica, el objeto mencionado en este documento.";
    }

    /**
     * Cláusula Sexta - Confidencialidad (con negritas en términos clave)
     */
    private function buildClausulaConfidencialidad()
    {
        return "<b>SEXTA-CLÁUSULA DE CONFIDENCIALIDAD:</b> <b>EL CONTRATISTA</b> deberá mantener la confidencialidad sobre toda la información de <b>EL CONTRATANTE</b> que conozca o a la que tenga acceso. Se tendrá como información confidencial cualquier información no divulgada que posea legítimamente <b>EL CONTRATANTE</b> que pueda usarse en alguna actividad académica, productiva, industrial o comercial y que sea susceptible de comunicarse a un tercero. La información confidencial incluye también toda información recibida de terceros que <b>EL CONTRATISTA</b> está obligado a tratar como confidencial, así como las informaciones orales que <b>EL CONTRATANTE</b> identifique como confidencial.";
    }

    /**
     * Cláusula Séptima - Valor y Forma de Pago (con negritas en términos clave)
     */
    private function buildClausulaValor($data)
    {
        $valorTotal = number_format($data['valor_contrato'], 0, ',', '.');
        $valorMensual = number_format($data['valor_mensual'], 0, ',', '.');
        $numeroCuotas = $data['numero_cuotas'];

        $fechaInicio = new \DateTime($data['fecha_inicio']);
        $fechaFin = new \DateTime($data['fecha_fin']);

        $texto = "<b>SÉPTIMA-VALOR DEL CONTRATO - FORMA DE PAGO Y PENALIDADES:</b> El valor del presente contrato es de: <b>" . $this->numeroALetras($data['valor_contrato']) . " PESOS M/CTE ANTES DE IVA (\$" . $valorTotal . ")</b>, ";
        $texto .= "de forma <b>MENSUAL</b> en <b>" . $numeroCuotas . " facturas</b> por valor de <b>" . $this->numeroALetras($data['valor_mensual']) . " PESOS ANTES DE IVA (\$" . $valorMensual . ")</b> ";
        $texto .= "desde <b>" . $this->getMesNombre($fechaInicio->format('m')) . " " . $fechaInicio->format('Y') . "</b>";
        $texto .= " a <b>" . $this->getMesNombre($fechaFin->format('m')) . " " . $fechaFin->format('Y') . "</b>, ";
        $texto .= "sujeto a la revisión de las actividades realizadas y la consignación de reportes en <b>EnterpriseSST</b>. Las facturas emitidas por <b>EL CONTRATISTA</b> deberán ser pagadas por <b>EL CONTRATANTE</b> dentro los <b>ocho (8) días calendario</b> contados a partir de la fecha de su emisión. En caso de que el pago no se realice en el plazo estipulado, el saldo adeudado incurrirá en mora.<br><br>";

        $texto .= "<b>INTERESES POR MORA:</b> Si el pago de una factura no se ha realizado a los <b>sesenta (60) días calendario</b> posterior a su fecha de vencimiento, <b>EL CONTRATANTE</b> deberá pagar a <b>EL CONTRATISTA</b> un interés de mora del <b>uno punto cinco por ciento (1,5%) mensual</b>, calculado sobre el valor base de la factura (antes de impuestos). Este interés será cobrado en la siguiente factura emitida por <b>EL CONTRATISTA</b> reflejándose como un ajuste adicional al monto total a pagar. <b>EL CONTRATISTA</b> de manera previa hará la presentación de factura y revisión de la misma, por transferencia bancaria al banco <b>" . $data['banco'] . "</b>, cuenta de <b>" . $data['tipo_cuenta'] . "</b> No. <b>" . $data['cuenta_bancaria'] . "</b> a nombre de <b>EL CONTRATISTA</b>.<br><br>";

        $texto .= "<b>PARÁGRAFO:</b> Serán requisitos indispensables para el pago que <b>EL CONTRATISTA</b> presente planilla integrada de liquidación de aportes (PILA) que acredite el pago al Sistema General de Seguridad Social Integral.";

        return $texto;
    }

    /**
     * Convierte número a letras (simplificado)
     */
    private function numeroALetras($numero)
    {
        // Implementación simplificada
        $numeros = [
            1000000 => 'UN MILLÓN',
            2000000 => 'DOS MILLONES',
            3000000 => 'TRES MILLONES',
            4000000 => 'CUATRO MILLONES',
            5000000 => 'CINCO MILLONES',
            250000 => 'DOSCIENTOS CINCUENTA MIL',
            500000 => 'QUINIENTOS MIL',
            750000 => 'SETECIENTOS CINCUENTA MIL'
        ];

        return $numeros[$numero] ?? number_format($numero, 0, ',', '.');
    }

    /**
     * Cláusula Octava - Procedencia de Recursos (con negritas en términos clave)
     */
    private function buildClausulaProcedencia()
    {
        return "<b>OCTAVA-PROCEDENCIA DE RECURSOS:</b> <b>EL CONTRATANTE</b> declara bajo la gravedad de juramento que los recursos, fondos, dineros, activos o bienes relacionados con este contrato, son de procedencia lícita y no están vinculados con el lavado de activos ni con ninguno de sus delitos fuente, así como que el destino de los recursos, fondos, dineros, activos o bienes producto de los mismos no van a ser destinados para la financiación del terrorismo o cualquier otra conducta delictiva, de acuerdo con las normas penales y las que sean aplicables en Colombia, sin perjuicio de las acciones legales pertinentes derivadas del incumplimiento de esta declaración.<br><br><b>NOVENA-CESIÓN:</b> <b>EL CONTRATISTA</b> no podrá ceder total ni parcialmente, así como subcontratar, la ejecución del presente contrato, salvo previa autorización expresa y escrita de <b>EL CONTRATANTE</b>.";
    }

    /**
     * Cláusula Novena - Cesión (ELIMINADA - Ya incluida en Octava)
     */
    private function buildClausulaCesion()
    {
        return ""; // Vacío porque ya está incluido en buildClausulaProcedencia
    }

    /**
     * Cláusula Décima - Lealtad Profesional (con negritas en términos clave)
     */
    private function buildClausulaLealtad()
    {
        return "<b>DÉCIMA-LEALTAD PROFESIONAL:</b> Las partes acuerdan que no podrán vincular laboralmente dentro de sus compañías a personal de planta, del cual hubiera conocido su desempeño profesional a causa de la relación que surgió en la ejecución del presente contrato de prestación de servicios. En caso de que alguna de las partes, omita esta cláusula habrá lugar a efectuar un cobro equivalente a <b>doce (12) salarios mínimos mensuales vigentes</b> por cada trabajador. Las partes entienden que éste es un reconocimiento del costo incurrido, para contratar y capacitar a este empleado. Las partes reconocen que esto es una estimación previa legítima de los costos por la pérdida de los trabajadores.";
    }

    /**
     * Cláusula Onceava - SAGRILAFT (con negritas en términos clave)
     */
    private function buildClausulaSAGRILAFT()
    {
        return "<b>ONCEAVA-PREVENCIÓN DEL RIESGO DE LAVADO DE ACTIVOS, FINANCIACIÓN DEL TERRORISMO Y FINANCIACIÓN DE LA PROLIFERACIÓN DE ARMAS DE DESTRUCCIÓN MASIVA - SAGRILAFT/LAFT:</b> <b>LAS PARTES</b> certifican que sus recursos no provienen ni se destinan al ejercicio de ninguna actividad ilícita o de actividades conexas al lavado de activos, provenientes de éstas o de actividades relacionadas con la financiación del terrorismo. <b>LAS PARTES</b>, en su calidad de sujetos responsables de contar con un Sistema de Autocontrol y Gestión del Riesgo de Lavado de Activos y Financiación del Terrorismo (<b>SAGRILAFT</b>), podrán cruzar y solicitar en cualquier momento la información de sus clientes con las listas para el control de lavado de activos y financiación del terrorismo (LAF-1), administradas por cualquier autoridad nacional o extranjera, tales como la lista de la Oficina de Control de Activos en el Exterior (OFAC), emitida por la Oficina del Tesoro de los Estados Unidos de América, o cualquier otra lista oficial relacionada con el tema del LAF-T (en adelante las Listas). Las Partes aceptan, entienden y conocen, de manera inequívoca que en caso de incumplimiento de las obligaciones aquí contraídas y/o violación de las declaraciones aquí contenidas, así como en caso de aparecer de cualquiera de sus accionistas, socios, miembros de Junta Directiva y/o sus representantes legales inscritos en cualquiera de las listas mencionadas o en listas que en el futuro se llegaren a conocer. (iii) Condenado por parte de las autoridades competentes a investigación judicial pendiente por incumplimiento tipo penal relacionado con el LAF-T; y/o (iv) Señalado públicamente por cualquier medio de amplia difusión nacional (Prensa, Radio, Televisión, etc.), como investigado por el delito de LAF-T.";
    }

    /**
     * Cláusula Doceava - Alto Riesgo (con negritas en términos clave)
     */
    private function buildClausulaAltoRiesgo()
    {
        return "<b>DOCEAVA-ALTO RIESGO EN LA COPROPIEDAD:</b> Toda actividad ejecutada dentro de la copropiedad que implique alto riesgo, como trabajos en espacios confinados o trabajos en alturas, deberá contar con la aprobación y la revisión documental de los contratistas responsables antes de su ejecución. En caso de que el profesional a cargo del Sistema de Gestión en Seguridad y Salud en el Trabajo (SST) no tenga conocimiento previo de dichas actividades y no haya aprobado los documentos <b>ATS (Análisis de Trabajo Seguro)</b> y demás documentos pertinentes, y éstas se lleven a cabo sin su supervisión, <b>EL CONTRATISTA</b> no asumirá ninguna responsabilidad administrativa ni civil por cualquier accidente o incidente que pueda ocurrir. Por último, algunos elementos que complementan mejor los motivos de finalización si eventualmente sucede. <b>PARÁGRAFO:</b> Este contrato habrá terminado en caso de incumplimiento de ésta actividad de control y supervisión debidas, considerando dicho incumplimiento como un motivo de terminación.";
    }

    /**
     * Cláusula Treceava - Firma Digital (con negritas en términos clave)
     */
    private function buildClausulaFirmaDigital()
    {
        return "<b>TRECEAVA-AUTORIZACIÓN PARA USO DIGITAL DE LA FIRMA:</b> <b>EL CONTRATANTE</b> autoriza expresamente a <b>EL CONTRATISTA</b> a utilizar la firma digital consignada en el presente contrato de servicio para su extracción y uso en formato digital. Esta firma digital podrá ser aplicada a todos los documentos relacionados con el Sistema de Gestión de Seguridad y Salud en el Trabajo (SG-SST), sin limitación, pues es un documento elaborado mediante el Sistema de Gestión documental <b>EnterpriseSST</b>. <b>EL Cliente</b> manifiesta que la firma que registre en el documento digital que sea necesario según lo dispuesto en la normatividad vigente en Colombia, tales como la <b>Resolución 0312 de 2019</b> o cualquier otra disposición legal aplicable. <b>EL Cliente</b> manifiesta que la firma digital consignada en el presente documento tendrá el mismo valor jurídico que su firma manuscrita. Asimismo, <b>EL CONTRATISTA</b> se compromete a modificar por escrito a <b>EL CONTRATANTE</b> en caso de cualquier cambio en la representación legal que implique necesariamente participantes diferentes en la presente cláusula y a adoptar todas las medidas necesarias para garantizar su seguridad y confidencialidad conforme a la normativa vigente sobre protección de datos y seguridad de la información.";
    }

    /**
     * Terminación del Contrato (con negritas en términos clave)
     */
    private function buildTerminacion()
    {
        return "<b>TERMINACIÓN DEL CONTRATO:</b> El presente contrato se terminará por las siguientes causas: <b>1.</b> Mutuo acuerdo. <b>2.</b> Incumplimiento de las obligaciones a cargo de cualquiera de las partes. <b>3.</b> Liquidación obligatoria, forzosa, o inicio de cualquier trámite concursal de cualquiera de las partes. <b>4.</b> Inclusión de cualquiera de las partes en listados multilaterales sobre financiación del terrorismo o lavado de activos. <b>5.</b> Actuar en forma contraria a las buenas costumbres o la ética empresarial. <b>6.</b> Cualquier otra causa prevista en la ley o en el presente documento. <b>7.</b> Fuerza mayor o caso fortuito debidamente comprobado. <b>8.</b> Cualquier incumplimiento en la confidencialidad o uso indebido de la información gestionada mediante la plataforma <b>EnterpriseSST</b>.";
    }

    /**
     * Firmas - Con imágenes de firmas digitales y espacio aumentado
     */
    private function addSignatures($data)
    {
        $fechaHoy = new \DateTime();

        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->Cell(0, 8, 'Las partes firman y suscriben el presente documento el día ' . $fechaHoy->format('d') . ' de ' . $this->getMesNombre($fechaHoy->format('m')) . ' de ' . $fechaHoy->format('Y') . '.', 0, 1);
        $this->pdf->Ln(5);

        // Títulos de las firmas
        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->Cell(90, 6, 'EL CONTRATISTA', 0, 0, 'C');
        $this->pdf->Cell(10, 6, '', 0, 0);
        $this->pdf->Cell(90, 6, 'EL CONTRATANTE', 0, 1, 'C');
        $this->pdf->Ln(3);

        // Obtener posiciones para las firmas
        $leftX = 15;
        $rightX = 115;
        $signatureY = $this->pdf->GetY();

        // Firma izquierda (Cycloid Talent - Dianita)
        $firmaContratista = FCPATH . 'uploads/FIRMA_DIANITA.jpg';
        if (file_exists($firmaContratista)) {
            $this->pdf->Image($firmaContratista, $leftX + 15, $signatureY, 60, 0, '', '', '', false, 300);
        }

        // Firma derecha (Cliente - EL CONTRATANTE) - NO poner firma aquí, es del representante legal del cliente
        // Esta firma debe ser capturada del cliente, NO del consultor

        $this->pdf->Ln(25); // Espacio para las firmas

        // Líneas de firma
        $this->pdf->Cell(90, 0, '', 'B', 0);
        $this->pdf->Cell(10, 0, '', 0, 0);
        $this->pdf->Cell(90, 0, '', 'B', 1);
        $this->pdf->Ln(3);

        // Nombres - usar MultiCell para evitar overflow
        $this->pdf->SetFont('helvetica', 'B', 10);

        // Nombre izquierda
        $currentY = $this->pdf->GetY();
        $this->pdf->SetXY($leftX, $currentY);
        $this->pdf->MultiCell(90, 6, strtoupper($data['nombre_rep_legal_contratista']), 0, 'C');

        // Nombre derecha
        $this->pdf->SetXY($rightX, $currentY);
        $this->pdf->MultiCell(90, 6, strtoupper($data['nombre_rep_legal_cliente']), 0, 'C');

        $this->pdf->Ln(2);

        // Cédulas
        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->Cell(90, 5, 'C.C. No. ' . $data['cedula_rep_legal_contratista'], 0, 0, 'C');
        $this->pdf->Cell(10, 5, '', 0, 0);
        $this->pdf->Cell(90, 5, 'C.C. No. ' . $data['cedula_rep_legal_cliente'], 0, 1, 'C');

        // Cargo
        $this->pdf->Cell(90, 5, 'Representante Legal', 0, 0, 'C');
        $this->pdf->Cell(10, 5, '', 0, 0);
        $this->pdf->Cell(90, 5, 'Representante Legal', 0, 1, 'C');

        // Empresa
        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->Cell(90, 5, 'CYCLOID TALENT S.A.S.', 0, 0, 'C');
        $this->pdf->Cell(10, 5, '', 0, 0);

        // Nombre cliente (puede ser largo)
        $currentY = $this->pdf->GetY();
        $this->pdf->SetXY($rightX, $currentY);
        $this->pdf->MultiCell(90, 5, strtoupper($data['nombre_cliente']), 0, 'C');
        $this->pdf->Ln(2);

        // Emails
        $this->pdf->SetFont('helvetica', '', 8);
        $this->pdf->Cell(90, 5, $data['email_contratista'], 0, 0, 'C');
        $this->pdf->Cell(10, 5, '', 0, 0);

        // Email cliente
        $currentY = $this->pdf->GetY();
        $this->pdf->SetXY($rightX, $currentY);
        $this->pdf->MultiCell(90, 5, $data['email_cliente'], 0, 'C');

        $this->pdf->Ln(10);

        // Responsable SG-SST con firma
        $this->pdf->SetFont('helvetica', 'B', 10);
        $this->pdf->Cell(0, 6, 'RESPONSABLE SG-SST ASIGNADO', 0, 1, 'C');
        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->Cell(0, 5, 'PROFESIONAL SG-SST', 0, 1, 'C');
        $this->pdf->Ln(1);

        // Firma del consultor (centrada, tamaño reducido y mejor posicionada)
        if (!empty($data['id_consultor_responsable'])) {
            $db = \Config\Database::connect();
            $consultor = $db->table('tbl_consultor')
                           ->where('id_consultor', $data['id_consultor_responsable'])
                           ->get()
                           ->getRowArray();

            if ($consultor && !empty($consultor['firma_consultor'])) {
                $firmaConsultor = FCPATH . 'uploads/' . $consultor['firma_consultor'];
                if (file_exists($firmaConsultor)) {
                    $currentY = $this->pdf->GetY();
                    // Centrar la firma - reducida a 40mm de ancho y mejor centrada
                    $this->pdf->Image($firmaConsultor, 85, $currentY, 40, 0, '', '', '', false, 300, '', false, false);
                    $this->pdf->Ln(13);
                }
            }
        }

        $this->pdf->SetFont('helvetica', 'B', 10);
        $this->pdf->Cell(0, 6, strtoupper($data['nombre_responsable_sgsst']), 0, 1, 'C');
        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->Cell(0, 5, 'C.C. ' . $data['cedula_responsable_sgsst'], 0, 1, 'C');
        $this->pdf->Cell(0, 5, 'PROFESIONAL SG-SST', 0, 1, 'C');
        $this->pdf->Cell(0, 5, $data['email_responsable_sgsst'], 0, 1, 'C');
    }

    /**
     * Guarda el PDF en la ruta especificada
     *
     * @param string $filePath Ruta completa del archivo (incluyendo nombre y extensión)
     * @return string La ruta del archivo guardado
     */
    public function save($filePath)
    {
        $this->pdf->Output($filePath, 'F');
        return $filePath;
    }

    /**
     * Descarga el PDF directamente
     */
    public function download($filename)
    {
        $this->pdf->Output($filename, 'D');
    }

    /**
     * Retorna el PDF como string
     */
    public function getString()
    {
        return $this->pdf->Output('', 'S');
    }
}
