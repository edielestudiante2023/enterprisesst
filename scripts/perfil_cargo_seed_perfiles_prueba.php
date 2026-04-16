<?php
/**
 * Seed de 10 perfiles de cargo completos para cliente 18 (CLIENTE DE VALIDACION).
 *
 * SOLO LOCAL. Datos realistas hardcoded (no llama a IA para evitar consumo tokens).
 * Cada perfil incluye:
 *   - Objetivo del cargo (texto profesional)
 *   - Reporta a, colaboradores, edad/civil/genero
 *   - 6-8 funciones especificas realistas
 *   - 3-4 indicadores con formula + meta + ponderacion
 *   - 4-5 competencias del catalogo del cliente con nivel 3-5
 *   - Datos del aprobador (Edison Cuervo, Director TH)
 *   - Estado: aprobado (fecha hoy)
 *
 * NO genera imagen de firma del aprobador (requiere interaccion UI).
 * Idempotente: si ya existe perfil para un cargo, lo salta.
 *
 * Uso:
 *   php scripts/perfil_cargo_seed_perfiles_prueba.php
 */

$ID_CLIENTE = 18;

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=empresas_sst;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    echo "Conexion LOCAL OK\n\n";
} catch (Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

// Resolver cargos del cliente (por nombre)
$cargosMap = [];
foreach ($pdo->query("SELECT id, nombre_cargo FROM tbl_cargos_cliente WHERE id_cliente={$ID_CLIENTE} AND activo=1") as $r) {
    $cargosMap[$r['nombre_cargo']] = (int)$r['id'];
}
echo "Cargos disponibles del cliente: " . count($cargosMap) . "\n";

// Resolver competencias del cliente (por nombre)
$compMap = [];
foreach ($pdo->query("SELECT id_competencia, nombre FROM tbl_competencia_cliente WHERE id_cliente={$ID_CLIENTE} AND activo=1") as $r) {
    $compMap[$r['nombre']] = (int)$r['id_competencia'];
}
echo "Competencias del diccionario: " . count($compMap) . "\n\n";

// 10 perfiles de prueba
$perfiles = [
    // ============================================================
    'Gerente General' => [
        'objetivo' => 'Liderar estrategicamente la organizacion definiendo y ejecutando el plan estrategico corporativo, asegurando la sostenibilidad financiera, el cumplimiento normativo, la gestion efectiva del talento humano y la creacion de valor para accionistas, clientes y colaboradores.',
        'reporta_a' => 'Junta Directiva',
        'colaboradores' => 'Todos los directores de area',
        'edad_min' => '>=35',
        'estado_civil' => 'indiferente',
        'genero' => 'indiferente',
        'funciones' => [
            'Definir la estrategia corporativa y el plan de negocios anual alineado con los objetivos de la Junta Directiva.',
            'Supervisar la ejecucion presupuestal y la gestion financiera de la organizacion garantizando rentabilidad y sostenibilidad.',
            'Liderar el comite directivo y facilitar la toma de decisiones estrategicas transversales.',
            'Representar legalmente a la empresa ante autoridades, clientes corporativos, proveedores estrategicos y entidades regulatorias.',
            'Asegurar el cumplimiento del marco normativo vigente (SARLAFT, HSE, laboral, tributario).',
            'Aprobar las politicas corporativas y velar por su adecuada implementacion en todos los niveles.',
            'Rendir informes periodicos de gestion a la Junta Directiva y accionistas.',
            'Promover una cultura organizacional de integridad, excelencia operacional y orientacion al cliente.',
        ],
        'indicadores' => [
            ['Asegurar el cumplimiento del plan estrategico corporativo', 'Cumplimiento Plan Estrategico', '(Iniciativas ejecutadas / Iniciativas planeadas) * 100', 'trimestral', '>= 90 %', '30 %', 'Estrategia'],
            ['Garantizar la rentabilidad financiera', 'Margen EBITDA', '(EBITDA / Ingresos operacionales) * 100', 'mensual', '>= 18 %', '30 %', 'Financiero'],
            ['Asegurar la satisfaccion del cliente', 'NPS Corporativo', 'Score promedio encuesta NPS', 'semestral', '>= 70 puntos', '20 %', 'Cliente'],
            ['Asegurar cumplimiento normativo', 'Hallazgos criticos de auditoria', 'Numero de hallazgos criticos no cerrados', 'semestral', '= 0', '20 %', 'Cumplimiento'],
        ],
        'competencias' => [
            ['Liderazgo', 5],
            ['Pensamiento Analitico', 5],
            ['Direccion de Personas', 5],
            ['Impacto e Influencia', 5],
            ['Orientacion al Logro', 5],
        ],
    ],
    // ============================================================
    'Director de Riesgos' => [
        'objetivo' => 'Disenar e implementar el marco integral de gestion de riesgos (credito, operacional, liquidez, mercado y SARLAFT) asegurando la identificacion, medicion, monitoreo y mitigacion oportuna de los riesgos materiales a los que se expone la organizacion.',
        'reporta_a' => 'Gerente General',
        'colaboradores' => 'Equipo de riesgos (5 analistas)',
        'edad_min' => '>=32',
        'estado_civil' => 'indiferente',
        'genero' => 'indiferente',
        'funciones' => [
            'Disenar y mantener actualizada la politica integral de administracion de riesgos conforme a normativa.',
            'Supervisar la medicion y seguimiento de los indicadores de riesgo (VaR, perdida esperada, exposicion).',
            'Coordinar la ejecucion de stress tests y analisis de escenarios adversos.',
            'Liderar el comite de riesgos y presentar informes mensuales a la Gerencia y Junta Directiva.',
            'Validar modelos internos de scoring de credito y cupos de exposicion.',
            'Asegurar el cumplimiento del marco SARLAFT y el reporte oportuno a UIAF.',
            'Capacitar al equipo en metodologias de evaluacion de riesgos y mejores practicas del sector.',
            'Aprobar excepciones a politicas de credito dentro de su alcance autorizado.',
        ],
        'indicadores' => [
            ['Mantener niveles de riesgo dentro del apetito autorizado', 'Exposicion VaR', '(VaR actual / Limite VaR aprobado) * 100', 'mensual', '<= 85 %', '40 %', 'Riesgo'],
            ['Asegurar oportunidad reporte SARLAFT', 'Reportes UIAF a tiempo', '(Reportes a tiempo / Total reportes) * 100', 'mensual', '= 100 %', '30 %', 'Cumplimiento'],
            ['Mantener calidad de la cartera', 'Indice de cartera vencida', '(Cartera > 30 dias / Cartera total) * 100', 'mensual', '<= 4 %', '30 %', 'Financiero'],
        ],
        'competencias' => [
            ['Pensamiento Analitico', 5],
            ['Conciencia Financiera', 5],
            ['Experiencia Funcional/Tecnica', 5],
            ['Responsabilidad por Resultados', 4],
            ['Integridad', 5],
        ],
    ],
    // ============================================================
    'Analista Contable' => [
        'objetivo' => 'Garantizar la exactitud y confiabilidad de la informacion financiera mediante el registro, analisis y control de las transacciones contables, asegurando el cumplimiento de las normativas tributarias vigentes y proporcionando datos oportunos para la toma de decisiones.',
        'reporta_a' => 'Contador',
        'colaboradores' => 'No aplica',
        'edad_min' => '>25',
        'estado_civil' => 'indiferente',
        'genero' => 'indiferente',
        'funciones' => [
            'Registrar las cuentas por pagar aplicando correctamente los impuestos segun las normas contables vigentes.',
            'Gestionar el registro y control de activos fijos incluyendo su depreciacion conforme a las politicas.',
            'Elaborar conciliaciones bancarias mensuales comparando registros contables con extractos bancarios.',
            'Preparar y revisar los borradores de declaraciones de impuestos garantizando exactitud y oportunidad.',
            'Apoyar en el proceso de facturacion incluyendo emision y revision de ingresos y autorretenciones.',
            'Analizar y conciliar cuentas contables para asegurar la exactitud de los saldos financieros.',
            'Participar en la preparacion de informacion para auditorias financieras internas y externas.',
        ],
        'indicadores' => [
            ['Asegurar conciliaciones bancarias oportunas', 'Oportunidad en conciliaciones', '(Conciliaciones a tiempo / Total esperadas) * 100', 'mensual', '>= 95 %', '40 %', 'Procesos'],
            ['Garantizar precision del registro contable', 'Errores en registros', 'Numero de ajustes posteriores al cierre', 'mensual', '<= 3', '30 %', 'Calidad'],
            ['Cumplimiento tributario', 'Declaraciones presentadas a tiempo', '(Declaraciones a tiempo / Total) * 100', 'mensual', '= 100 %', '30 %', 'Cumplimiento'],
        ],
        'competencias' => [
            ['Pensamiento Analitico', 4],
            ['Preocupacion por el Orden y la Calidad', 5],
            ['Responsabilidad por Resultados', 4],
            ['Experiencia Funcional/Tecnica', 4],
        ],
    ],
    // ============================================================
    'Contador' => [
        'objetivo' => 'Responsable de la correcta preparacion, revision y certificacion de los estados financieros de la organizacion conforme a los marcos normativos vigentes (NIIF, tributario), asegurando la integridad de la informacion contable y el cumplimiento de las obligaciones fiscales.',
        'reporta_a' => 'Director Financiero',
        'colaboradores' => 'Analistas contables (3)',
        'edad_min' => '>=30',
        'estado_civil' => 'indiferente',
        'genero' => 'indiferente',
        'funciones' => [
            'Preparar y certificar los estados financieros mensuales y anuales bajo NIIF.',
            'Supervisar el cierre contable mensual y asegurar la conciliacion de todas las cuentas.',
            'Revisar y firmar declaraciones tributarias (IVA, renta, retenciones, ICA) para presentacion oportuna.',
            'Atender auditorias externas y revisorias fiscales proporcionando documentacion soporte.',
            'Supervisar el trabajo del equipo de analistas contables y validar la calidad de los registros.',
            'Mantener actualizado el conocimiento sobre cambios normativos tributarios y contables.',
            'Elaborar informes financieros gerenciales y analisis de variaciones presupuestales.',
        ],
        'indicadores' => [
            ['Oportunidad cierre contable', 'Dias de cierre mensual', 'Dias habiles desde fin de mes hasta firma EEFF', 'mensual', '<= 5 dias', '40 %', 'Procesos'],
            ['Calidad EEFF', 'Ajustes detectados por auditoria', 'Numero de ajustes significativos propuestos por auditoria externa', 'anual', '<= 2', '30 %', 'Calidad'],
            ['Cumplimiento tributario', 'Multas DIAN recibidas', 'Numero y valor de sanciones por incumplimiento tributario', 'trimestral', '= 0', '30 %', 'Cumplimiento'],
        ],
        'competencias' => [
            ['Conciencia Financiera', 5],
            ['Pensamiento Analitico', 5],
            ['Experiencia Funcional/Tecnica', 5],
            ['Integridad', 5],
            ['Direccion de Personas', 3],
        ],
    ],
    // ============================================================
    'Director de Talento Humano' => [
        'objetivo' => 'Liderar la estrategia integral de gestion del talento humano que permita atraer, desarrollar, retener y gestionar el ciclo de vida laboral de los colaboradores, alineando las practicas de TH con la estrategia corporativa y asegurando ambientes de trabajo productivos y seguros.',
        'reporta_a' => 'Gerente General',
        'colaboradores' => 'Equipo TH (5 personas)',
        'edad_min' => '>=32',
        'estado_civil' => 'indiferente',
        'genero' => 'indiferente',
        'funciones' => [
            'Disenar la estrategia de talento humano alineada con los objetivos corporativos.',
            'Supervisar los procesos de reclutamiento, seleccion, contratacion y desvinculacion de personal.',
            'Liderar los programas de formacion, desarrollo y plan carrera de los colaboradores.',
            'Administrar la politica salarial, los beneficios y la compensacion variable.',
            'Gestionar el clima organizacional y los planes de mejora derivados de mediciones periodicas.',
            'Asegurar el cumplimiento de la normativa laboral colombiana y las obligaciones ante entes de control.',
            'Coordinar con SST los programas de bienestar y seguridad y salud en el trabajo.',
            'Presidir el comite de convivencia laboral y atender los procesos disciplinarios.',
        ],
        'indicadores' => [
            ['Retener talento clave', 'Rotacion voluntaria anual', '(Retiros voluntarios / Headcount promedio) * 100', 'semestral', '<= 12 %', '30 %', 'Talento'],
            ['Desarrollar competencias', 'Horas de capacitacion per capita', 'Horas totales capacitacion / Headcount', 'anual', '>= 40 horas', '25 %', 'Desarrollo'],
            ['Mejorar clima organizacional', 'Indice de clima', 'Score promedio encuesta anual', 'anual', '>= 80 puntos', '25 %', 'Clima'],
            ['Cumplimiento legal', 'Procesos laborales perdidos', 'Numero de procesos con fallo en contra', 'anual', '= 0', '20 %', 'Legal'],
        ],
        'competencias' => [
            ['Desarrollo de Personas', 5],
            ['Comprension Interpersonal', 5],
            ['Direccion de Personas', 5],
            ['Liderazgo', 5],
            ['Integridad', 5],
        ],
    ],
    // ============================================================
    'Coordinador SG-SST' => [
        'objetivo' => 'Coordinar la implementacion, mantenimiento y mejora continua del Sistema de Gestion de Seguridad y Salud en el Trabajo (SG-SST) conforme al Decreto 1072 de 2015 y la Resolucion 0312 de 2019, promoviendo ambientes de trabajo seguros y saludables.',
        'reporta_a' => 'Director de Talento Humano',
        'colaboradores' => 'Auxiliar SST',
        'edad_min' => '>=28',
        'estado_civil' => 'indiferente',
        'genero' => 'indiferente',
        'funciones' => [
            'Ejecutar y documentar el SG-SST cumpliendo los estandares minimos de la Resolucion 0312.',
            'Identificar peligros, evaluar y valorar los riesgos segun metodologia GTC 45.',
            'Disenar y ejecutar el Plan Anual de Trabajo (PTA) del SG-SST.',
            'Coordinar el programa de examenes medicos ocupacionales con la IPS autorizada.',
            'Investigar los accidentes e incidentes de trabajo y proponer acciones correctivas.',
            'Supervisar el uso correcto de los elementos de proteccion personal y dotacion.',
            'Apoyar al COPASST y al Comite de Convivencia Laboral en el ejercicio de sus funciones.',
            'Capacitar a los trabajadores en temas de prevencion de riesgos laborales.',
            'Reportar indicadores de SST a la alta direccion y entidades de control.',
        ],
        'indicadores' => [
            ['Reducir accidentalidad laboral', 'Indice de Frecuencia (IF)', '(Numero accidentes * 200000) / Horas hombre trabajadas', 'mensual', '<= 3', '30 %', 'Seguridad'],
            ['Cumplimiento Plan Anual SST', 'Cumplimiento PTA', '(Actividades ejecutadas / Planeadas) * 100', 'trimestral', '>= 95 %', '30 %', 'Planificacion'],
            ['Aumentar cobertura capacitacion', 'Cobertura capacitacion SST', '(Trabajadores capacitados / Total trabajadores) * 100', 'trimestral', '>= 90 %', '20 %', 'Formacion'],
            ['Auditoria estandares minimos', 'Estandares minimos cumplidos', '(Items cumplidos / Total items 0312) * 100', 'anual', '>= 85 %', '20 %', 'Cumplimiento'],
        ],
        'competencias' => [
            ['Experiencia Funcional/Tecnica', 5],
            ['Preocupacion por el Orden y la Calidad', 5],
            ['Habilidades de Planeacion', 4],
            ['Busqueda de Informacion', 4],
        ],
    ],
    // ============================================================
    'Analista de Credito' => [
        'objetivo' => 'Evaluar, estructurar y recomendar la aprobacion o negacion de solicitudes de credito conforme a las politicas y modelos de riesgo de la entidad, asegurando el crecimiento sano de la cartera y la minimizacion del riesgo de incumplimiento.',
        'reporta_a' => 'Jefe de Credito',
        'colaboradores' => 'No aplica',
        'edad_min' => '>=25',
        'estado_civil' => 'indiferente',
        'genero' => 'indiferente',
        'funciones' => [
            'Analizar la capacidad de pago y las fuentes de ingreso de los solicitantes de credito.',
            'Verificar referencias personales, comerciales y bancarias de los solicitantes.',
            'Calcular el scoring crediticio utilizando las herramientas internas y centrales de riesgo.',
            'Estructurar propuestas de credito ajustadas al perfil del cliente y las politicas vigentes.',
            'Preparar las fichas de credito para presentacion al comite correspondiente.',
            'Hacer seguimiento a la documentacion legal de garantias y perfeccionamiento de las operaciones.',
            'Monitorear la evolucion de la cartera asignada e identificar alertas tempranas.',
        ],
        'indicadores' => [
            ['Calidad del analisis', 'Cartera vencida de creditos aprobados', '(Cartera vencida > 60 dias / Cartera aprobada) * 100', 'trimestral', '<= 3 %', '40 %', 'Riesgo'],
            ['Productividad', 'Solicitudes analizadas por mes', 'Numero de solicitudes procesadas', 'mensual', '>= 80', '30 %', 'Procesos'],
            ['Oportunidad', 'Tiempo promedio de analisis', 'Dias promedio desde recepcion hasta recomendacion', 'mensual', '<= 2 dias', '30 %', 'Servicio'],
        ],
        'competencias' => [
            ['Pensamiento Analitico', 5],
            ['Conciencia Financiera', 4],
            ['Busqueda de Informacion', 4],
            ['Preocupacion por el Orden y la Calidad', 4],
        ],
    ],
    // ============================================================
    'Analista de Soporte' => [
        'objetivo' => 'Proporcionar soporte tecnico de primer y segundo nivel a los usuarios internos de la organizacion, garantizando la disponibilidad, continuidad y adecuado funcionamiento de los equipos, sistemas y aplicaciones corporativas.',
        'reporta_a' => 'Administrador de Infraestructura',
        'colaboradores' => 'No aplica',
        'edad_min' => '>=22',
        'estado_civil' => 'indiferente',
        'genero' => 'indiferente',
        'funciones' => [
            'Atender los casos reportados por los usuarios a traves del sistema de mesa de ayuda.',
            'Diagnosticar y resolver problemas de hardware, software y conectividad en primera instancia.',
            'Realizar el mantenimiento preventivo de equipos de computo e impresion.',
            'Instalar y configurar software corporativo en equipos nuevos y existentes.',
            'Gestionar las cuentas de usuario, permisos y accesos en los sistemas corporativos.',
            'Documentar las soluciones aplicadas en la base de conocimiento interna.',
            'Escalar al equipo de infraestructura los incidentes que requieran atencion especializada.',
        ],
        'indicadores' => [
            ['Oportunidad en resolucion', 'Tiempo promedio de respuesta (SLA 1er nivel)', 'Minutos promedio desde apertura hasta primera respuesta', 'mensual', '<= 30 min', '30 %', 'Servicio'],
            ['Resolucion en primer contacto', 'First Call Resolution', '(Tickets resueltos sin escalar / Total tickets) * 100', 'mensual', '>= 70 %', '40 %', 'Calidad'],
            ['Satisfaccion del usuario', 'Encuesta post-resolucion', 'Score promedio encuesta (1-5)', 'mensual', '>= 4.2', '30 %', 'Cliente interno'],
        ],
        'competencias' => [
            ['Orientacion al Cliente', 5],
            ['Experiencia Funcional/Tecnica', 4],
            ['Busqueda de Informacion', 4],
            ['Flexibilidad', 4],
        ],
    ],
    // ============================================================
    'Oficial de Ciberseguridad' => [
        'objetivo' => 'Proteger los activos de informacion de la organizacion mediante la definicion, implementacion y monitoreo de controles de seguridad que mitiguen los riesgos de confidencialidad, integridad y disponibilidad conforme al marco ISO 27001 y la normativa aplicable.',
        'reporta_a' => 'CIO',
        'colaboradores' => 'No aplica',
        'edad_min' => '>=28',
        'estado_civil' => 'indiferente',
        'genero' => 'indiferente',
        'funciones' => [
            'Monitorear 24/7 los eventos de seguridad detectados por el SIEM corporativo.',
            'Ejecutar pruebas de vulnerabilidades periodicas sobre la infraestructura critica.',
            'Investigar y responder a incidentes de seguridad de la informacion conforme al playbook.',
            'Mantener actualizado el inventario de activos criticos y su clasificacion de riesgo.',
            'Coordinar con equipos de TI la aplicacion oportuna de parches y actualizaciones de seguridad.',
            'Sensibilizar a los usuarios en buenas practicas de seguridad (phishing, contrasenas, proteccion de datos).',
            'Apoyar al DPO en el cumplimiento de la Ley 1581 de proteccion de datos personales.',
        ],
        'indicadores' => [
            ['Reducir ventana de exposicion', 'Tiempo medio de parcheo criticos', 'Dias desde publicacion parche hasta despliegue', 'mensual', '<= 7 dias', '30 %', 'Seguridad'],
            ['Atencion de incidentes', 'MTTR incidentes de seguridad', 'Horas promedio de resolucion', 'mensual', '<= 4 horas', '30 %', 'Respuesta'],
            ['Concientizacion', 'Tasa de click en phishing simulado', '(Usuarios que clicaron / Total campana) * 100', 'trimestral', '<= 10 %', '20 %', 'Cultura'],
            ['Auditorias', 'Hallazgos criticos pendientes', 'Numero de hallazgos criticos abiertos', 'semestral', '= 0', '20 %', 'Cumplimiento'],
        ],
        'competencias' => [
            ['Experiencia Funcional/Tecnica', 5],
            ['Pensamiento Analitico', 5],
            ['Autocontrol', 4],
            ['Integridad', 5],
        ],
    ],
    // ============================================================
    'Asesor Comercial Ahorro' => [
        'objetivo' => 'Asesorar y captar recursos del publico a traves de productos de ahorro, brindando experiencia de servicio de alta calidad que permita el cumplimiento de las metas comerciales de la oficina y la fidelizacion de los clientes.',
        'reporta_a' => 'Jefe de Captacion',
        'colaboradores' => 'No aplica',
        'edad_min' => '>=23',
        'estado_civil' => 'indiferente',
        'genero' => 'indiferente',
        'funciones' => [
            'Atender a los clientes y prospectos ofreciendo el portafolio de productos de ahorro.',
            'Realizar la apertura de cuentas de ahorro, corriente y CDT conforme al manual operativo.',
            'Ejecutar campanas comerciales de captacion segun planes definidos por la gerencia.',
            'Diligenciar el formato SARLAFT y realizar la debida diligencia al momento de la vinculacion.',
            'Gestionar las renovaciones de CDT y promover la retencion de saldos.',
            'Atender reclamos y solicitudes de clientes relacionados con sus productos pasivos.',
            'Cumplir con las metas individuales de captacion mensual y numero de clientes nuevos.',
        ],
        'indicadores' => [
            ['Cumplimiento meta captacion', 'Meta comercial individual', '(Captacion real / Meta asignada) * 100', 'mensual', '>= 100 %', '40 %', 'Comercial'],
            ['Fidelizacion de clientes', 'Tasa de retencion CDT', '(CDT renovados / CDT vencidos) * 100', 'mensual', '>= 75 %', '30 %', 'Retencion'],
            ['Calidad del servicio', 'Encuesta de satisfaccion cliente', 'Score promedio (1-5)', 'mensual', '>= 4.5', '30 %', 'Cliente'],
        ],
        'competencias' => [
            ['Orientacion al Cliente', 5],
            ['Orientacion al Logro', 5],
            ['Comprension Interpersonal', 4],
            ['Negociacion', 4],
        ],
    ],
];

echo "Perfiles a crear: " . count($perfiles) . "\n\n";

$perfilInsert = $pdo->prepare("INSERT INTO tbl_perfil_cargo
    (id_cliente, id_cargo_cliente, objetivo_cargo, reporta_a, colaboradores_a_cargo, edad_min, estado_civil, genero, funciones_especificas, aprobador_nombre, aprobador_cargo, aprobador_cedula, fecha_aprobacion, version_actual, estado)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 'aprobado')");
$checkExistente = $pdo->prepare("SELECT id_perfil_cargo FROM tbl_perfil_cargo WHERE id_cargo_cliente = ? LIMIT 1");

$insIndicador = $pdo->prepare("INSERT INTO tbl_perfil_cargo_indicador
    (id_perfil_cargo, objetivo_proceso, nombre_indicador, formula, periodicidad, meta, ponderacion, objetivo_calidad_impacta, generado_ia, orden)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, ?)");

$insCompetencia = $pdo->prepare("INSERT INTO tbl_perfil_cargo_competencia
    (id_perfil_cargo, id_competencia, nivel_requerido, orden)
    VALUES (?, ?, ?, ?)");

$APROBADOR_NOMBRE = 'Edison Ernesto Cuervo Rodriguez';
$APROBADOR_CARGO  = 'Director de Talento Humano';
$APROBADOR_CEDULA = '79876543';
$FECHA_APROB      = date('Y-m-d');

$creados = 0; $saltados = 0; $errores = 0;

foreach ($perfiles as $cargoNombre => $p) {
    $idCargo = $cargosMap[$cargoNombre] ?? null;
    if ($idCargo === null) {
        echo "  ??  Cargo no encontrado: {$cargoNombre}\n";
        $errores++;
        continue;
    }

    $checkExistente->execute([$idCargo]);
    if ($checkExistente->fetch()) {
        echo "  SKIP {$cargoNombre} — ya existe perfil\n";
        $saltados++;
        continue;
    }

    $pdo->beginTransaction();
    try {
        // Insert perfil
        $perfilInsert->execute([
            $ID_CLIENTE,
            $idCargo,
            $p['objetivo'],
            $p['reporta_a'],
            $p['colaboradores'],
            $p['edad_min'],
            $p['estado_civil'],
            $p['genero'],
            json_encode($p['funciones'], JSON_UNESCAPED_UNICODE),
            $APROBADOR_NOMBRE,
            $APROBADOR_CARGO,
            $APROBADOR_CEDULA,
            $FECHA_APROB,
        ]);
        $idPerfil = (int)$pdo->lastInsertId();

        // Insert indicadores
        foreach ($p['indicadores'] as $i => $ind) {
            $insIndicador->execute([
                $idPerfil,
                $ind[0], // objetivo_proceso
                $ind[1], // nombre_indicador
                $ind[2], // formula
                $ind[3], // periodicidad
                $ind[4], // meta
                $ind[5], // ponderacion
                $ind[6], // objetivo_calidad_impacta
                $i + 1,  // orden
            ]);
        }

        // Insert competencias
        $compInsertadas = 0;
        foreach ($p['competencias'] as $i => $comp) {
            $compNombre = $comp[0];
            $nivel = $comp[1];
            $idComp = $compMap[$compNombre] ?? null;
            if ($idComp === null) {
                echo "      ??  Competencia no encontrada: {$compNombre}\n";
                continue;
            }
            $insCompetencia->execute([$idPerfil, $idComp, $nivel, $i + 1]);
            $compInsertadas++;
        }

        $pdo->commit();
        echo "  OK   [{$idPerfil}] {$cargoNombre}  (funciones=" . count($p['funciones']) . ", indicadores=" . count($p['indicadores']) . ", competencias={$compInsertadas})\n";
        $creados++;
    } catch (Throwable $e) {
        $pdo->rollBack();
        echo "  ERR  {$cargoNombre}: " . $e->getMessage() . "\n";
        $errores++;
    }
}

echo "\n-- Resumen --\n";
echo "  Creados:  {$creados}\n";
echo "  Saltados: {$saltados}\n";
echo "  Errores:  {$errores}\n";

$total = (int)$pdo->query("SELECT COUNT(*) FROM tbl_perfil_cargo WHERE id_cliente={$ID_CLIENTE}")->fetchColumn();
$aprobados = (int)$pdo->query("SELECT COUNT(*) FROM tbl_perfil_cargo WHERE id_cliente={$ID_CLIENTE} AND estado='aprobado'")->fetchColumn();
echo "  Total perfiles cliente {$ID_CLIENTE}: {$total} (aprobados: {$aprobados})\n";

echo "\nLISTO\n";
