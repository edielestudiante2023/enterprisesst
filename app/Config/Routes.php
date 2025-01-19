<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

 $routes->cli('routes', function() use ($routes) {
    print_r($routes->getRoutes());
});

$routes->get('/', 'AuthController::login');
$routes->get('/login', 'AuthController::login');
$routes->post('/loginPost', 'AuthController::loginPost');
$routes->get('/logout', 'AuthController::logout');
$routes->get('/dashboardclient', 'ClientController::index');
$routes->get('/dashboardclient', 'ClientController::dashboard');
$routes->get('/dashboardclient', 'ClientController::dashboardSimplified');
$routes->get('/dashboard', 'ClientController::dashboard');
$routes->get('/dashboard', 'ClientController::showPanel');
$routes->get('client/dashboard', 'ClientController::dashboard');
$routes->get('client/suspended', 'AuthController::suspended');



$routes->get('/dashboardconsultant', 'ConsultantController::index');
$routes->get('/admindashboard', 'AdminDashboardController::index');

$routes->get('/addClient', 'ConsultantController::addClient');
$routes->post('/addClient', 'ConsultantController::addClientPost');

$routes->get('/prueba_form', 'PruebaController::index');
$routes->post('/prueba_save', 'PruebaController::save');

$routes->get('/addTest', 'TestController::index');
$routes->post('/addTest', 'TestController::addTestPost');

$routes->get('/addConsultant', 'ConsultantController::addConsultant');
$routes->post('/addConsultantPost', 'ConsultantController::addConsultantPost');
$routes->get('/listConsultants', 'ConsultantController::listConsultants');
$routes->get('/editConsultant/(:num)', 'ConsultantController::editConsultant/$1');
$routes->post('/editConsultant/(:num)', 'ConsultantController::editConsultant/$1');
$routes->get('/deleteConsultant/(:num)', 'ConsultantController::deleteConsultant/$1');


$routes->get('/reportList', 'ReportController::reportList');
$routes->get('/addReport', 'ReportController::addReport');
$routes->post('/addReportPost', 'ReportController::addReportPost');
$routes->get('/editReport/(:num)', 'ReportController::editReport/$1');
$routes->post('/editReportPost/(:num)', 'ReportController::editReportPost/$1');
$routes->get('/deleteReport/(:num)', 'ReportController::deleteReport/$1');

$routes->get('/report_dashboard', 'ClienteReportController::index');
$routes->get('/documento', 'DocumentoController::mostrarDocumento');

$routes->get('/showPhoto/(:num)', 'ConsultantController::showPhoto/$1');
$routes->post('/editConsultantPost/(:num)', 'ConsultantController::editConsultantPost/$1');
$routes->get('/documento', 'ClientController::documento');

$routes->get('/listClients', 'ConsultantController::listClients');
$routes->get('/editClient/(:num)', 'ConsultantController::editClient/$1');
$routes->post('/updateClient/(:num)', 'ConsultantController::updateClient/$1');
$routes->get('/deleteClient/(:num)', 'ConsultantController::deleteClient/$1');
$routes->post('/addClientPost', 'ConsultantController::addClientPost');
$routes->get('/responsableSGSST/(:num)', 'SGSSTPlanear::responsableDelSGSST/$1');

$routes->get('/error', 'ErrorController::index');

$routes->get('/reportTypes', 'ReportTypeController::index');
$routes->get('/reportTypes/add', 'ReportTypeController::add');
$routes->post('/reportTypes/addPost', 'ReportTypeController::addPost');

$routes->get('/addReportType', 'ReportTypeController::addReportType');
$routes->post('/addReportTypePost', 'ReportTypeController::addReportTypePost');

$routes->get('/listReportTypes', 'ReportTypeController::index');

$routes->get('/listReportTypes', 'ReportTypeController::listReportTypes');
$routes->get('/addReportType', 'ReportTypeController::addReportType');
$routes->post('/addReportTypePost', 'ReportTypeController::addReportTypePost');
$routes->get('/editReportType/(:num)', 'ReportTypeController::edit/$1');
$routes->post('/editReportTypePost/(:num)', 'ReportTypeController::editPost/$1');
$routes->get('/deleteReportType/(:num)', 'ReportTypeController::delete/$1');

$routes->get('/viewDocuments', 'ClientController::viewDocuments');

$routes->get('/listPolicies', 'PolicyController::listPolicies');
$routes->get('/addPolicy', 'PolicyController::addPolicy');
$routes->post('/addPolicyPost', 'PolicyController::addPolicyPost');
$routes->get('/editPolicy/(:num)', 'PolicyController::editPolicy/$1');
$routes->post('/editPolicyPost/(:num)', 'PolicyController::editPolicyPost/$1');
$routes->get('/deletePolicy/(:num)', 'PolicyController::deletePolicy/$1');

$routes->get('/listPolicyTypes', 'PolicyController::listPolicyTypes');
$routes->get('/addPolicyType', 'PolicyController::addPolicyType');
$routes->post('/addPolicyTypePost', 'PolicyController::addPolicyTypePost');
$routes->get('/editPolicyType/(:num)', 'PolicyController::editPolicyType/$1');
$routes->post('/editPolicyTypePost/(:num)', 'PolicyController::editPolicyTypePost/$1');
$routes->get('/deletePolicyType/(:num)', 'PolicyController::deletePolicyType/$1');

$routes->get('/policyNoAlcoholDrogas/(:num)', 'SGSSTPlanear::policyNoAlcoholDrogas/$1');
$routes->get('/asignacionResponsable/(:num)', 'PzasignacionresponsableController::asignacionResponsable/$1');
$routes->get('/asignacionResponsabilidades/(:num)', 'PzasignacionresponsabilidadesController::asignacionResponsabilidades/$1');
$routes->get('/prueba1/(:num)', 'Prueba1Controller::prueba1/$1');
$routes->get('/viewPolicy/(:num)', 'ClientDocumentController::viewPolicy/$1');
$routes->get('/addVersion', 'VersionController::addVersion');
$routes->post('/addVersionPost', 'VersionController::addVersionPost');
$routes->get('/editVersion/(:num)', 'VersionController::editVersion/$1');
$routes->post('/editVersionPost/(:num)', 'VersionController::editVersionPost/$1');
$routes->get('/deleteVersion/(:num)', 'VersionController::deleteVersion/$1');
$routes->get('/listVersions', 'VersionController::listVersions');
$routes->get('/generatePdfNoAlcoholDrogas', 'SGSSTPlanear::generatePdfNoAlcoholDrogas');
$routes->get('/generatePdf_asignacionResponsable', 'PzasignacionresponsableController::generatePdf_asignacionResponsable');
$routes->get('/generatePdf_asignacionResponsabilidades', 'PzasignacionresponsabilidadesController::generatePdf_asignacionResponsabilidades');

$routes->get('/asignacionVigia/(:num)', 'PzvigiaController::asignacionVigia/$1');
$routes->get('/generatePdf_asignacionVigia', 'PzvigiaController::generatePdf_asignacionVigia');
$routes->get('/exoneracionCocolab/(:num)', 'PzexoneracioncocolabController::exoneracionCocolab/$1');
$routes->get('/generatePdf_exoneracionCocolab', 'PzexoneracioncocolabController::generatePdf_exoneracionCocolab');
$routes->get('/registroAsistencia/(:num)', 'PzregistroasistenciaController::registroAsistencia/$1');
$routes->get('/generatePdf_registroAsistencia', 'PzregistroasistenciaController::generatePdf_registroAsistencia');
$routes->get('/actaCopasst/(:num)', 'PzactacopasstController::actaCopasst/$1');
$routes->get('/generatePdf_actaCopasst', 'PzactacopasstController::generatePdf_actaCopasst');
$routes->get('/inscripcionCopasst/(:num)', 'PzinscripcioncopasstController::inscripcionCopasst/$1');
$routes->get('/generatePdf_inscripcionCopasst', 'PzinscripcioncopasstController::generatePdf_inscripcionCopasst');
$routes->get('/formatoAsistencia/(:num)', 'PzformatodeasistenciaController::formatoAsistencia/$1');
$routes->get('/generatePdf_formatoAsistencia', 'PzformatodeasistenciaController::generatePdf_formatoAsistencia');
$routes->get('/confidencialidadCocolab/(:num)', 'PzconfidencialidadcocolabController::confidencialidadCocolab/$1');
$routes->get('/generatePdf_confidencialidadCocolab', 'PzconfidencialidadcocolabController::generatePdf_confidencialidadCocolab');
$routes->get('/inscripcionCocolab/(:num)', 'PzinscripcioncocolabController::inscripcionCocolab/$1');
$routes->get('/generatePdf_inscripcionCocolab', 'PzinscripcioncocolabController::generatePdf_inscripcionCocolab');
$routes->get('/quejaCocolab/(:num)', 'PzquejacocolabController::quejaCocolab/$1');
$routes->get('/generatePdf_quejaCocolab', 'PzquejacocolabController::generatePdf_quejaCocolab');
$routes->get('/manconvivenciaLaboral/(:num)', 'PzmanconvivencialaboralController::manconvivenciaLaboral/$1');
$routes->get('/generatePdf_manconvivenciaLaboral', 'PzmanconvivencialaboralController::generatePdf_manconvivenciaLaboral');
$routes->get('/prcCocolab/(:num)', 'PzprccocolabController::prcCocolab/$1');
$routes->get('/generatePdf_prcCocolab', 'PzprccocolabController::generatePdf_prcCocolab');
$routes->get('/prgCapacitacion/(:num)', 'PzprgcapacitacionController::prgCapacitacion/$1');
$routes->get('/generatePdf_prgCapacitacion', 'PzprgcapacitacionController::generatePdf_prgCapacitacion');
$routes->get('/prgInduccion/(:num)', 'PzprginduccionController::prgInduccion/$1');
$routes->get('/generatePdf_prgInduccion', 'PzprginduccionController::generatePdf_prgInduccion');
$routes->get('/ftevaluacionInduccion/(:num)', 'PzftevaluacioninduccionController::ftevaluacionInduccion/$1');
$routes->get('/generatePdf_ftevaluacionInduccion', 'PzftevaluacioninduccionController::generatePdf_ftevaluacionInduccion');
$routes->get('/politicaSst/(:num)', 'PzpoliticasstController::politicaSst/$1');
$routes->get('/generatePdf_politicaSst', 'PzpoliticasstController::generatePdf_politicaSst');
$routes->get('/politicaAlcohol/(:num)', 'PzpoliticaalcoholController::politicaAlcohol/$1');
$routes->get('/generatePdf_politicaAlcohol', 'PzpoliticaalcoholController::generatePdf_politicaAlcohol');
$routes->get('/politicaEmergencias/(:num)', 'PzpoliticaemergenciasController::politicaEmergencias/$1');
$routes->get('/generatePdf_politicaEmergencias', 'PzpoliticaemergenciasController::generatePdf_politicaEmergencias');
$routes->get('/politicaEpps/(:num)', 'PzpoliticaeppsController::politicaEpps/$1');
$routes->get('/generatePdf_politicaEpps', 'PzpoliticaeppsController::generatePdf_politicaEpps');
$routes->get('/politicaPesv/(:num)', 'PzpoliticapesvController::politicaPesv/$1');
$routes->get('/generatePdf_politicaPesv', 'PzpoliticapesvController::generatePdf_politicaPesv');
$routes->get('/regHigsegind/(:num)', 'PzreghigsegindController::regHigsegind/$1');
$routes->get('/generatePdf_regHigsegind', 'PzreghigsegindController::generatePdf_regHigsegind');
$routes->get('/oBjetivos/(:num)', 'PzobjetivosController::oBjetivos/$1');
$routes->get('/generatePdf_oBjetivos', 'PzobjetivosController::generatePdf_oBjetivos');
$routes->get('/documentosSgsst/(:num)', 'PzdocumentacionController::documentosSgsst/$1');
$routes->get('/generatePdf_documentosSgsst', 'PzdocumentacionController::generatePdf_documentosSgsst');
$routes->get('/rendicionCuentas/(:num)', 'PzrendicionController::rendicionCuentas/$1');
$routes->get('/generatePdf_rendicionCuentas', 'PzrendicionController::generatePdf_rendicionCuentas');
$routes->get('/comunicacionInterna/(:num)', 'PzcomunicacionController::comunicacionInterna/$1');
$routes->get('/generatePdf_comunicacionInterna', 'PzcomunicacionController::generatePdf_comunicacionInterna');
$routes->get('/manProveedores/(:num)', 'PzmanproveedoresController::manProveedores/$1');
$routes->get('/generatePdf_manProveedores', 'PzmanproveedoresController::generatePdf_manProveedores');
$routes->get('/saneamientoBasico/(:num)', 'PzsaneamientoController::saneamientoBasico/$1');
$routes->get('/generatePdf_saneamientoBasico', 'PzsaneamientoController::generatePdf_saneamientoBasico');
$routes->get('/medPreventiva/(:num)', 'PzmedpreventivaController::medPreventiva/$1');
$routes->get('/generatePdf_medPreventiva', 'PzmedpreventivaController::generatePdf_medPreventiva');
$routes->get('/reporteAccidente/(:num)', 'PzrepoaccidenteController::reporteAccidente/$1');
$routes->get('/generatePdf_reporteAccidente', 'PzrepoaccidenteController::generatePdf_reporteAccidente');
$routes->get('/inspeccionPlanynoplan/(:num)', 'PzinpeccionplanynoplanController::inspeccionPlanynoplan/$1');
$routes->get('/generatePdf_inspeccionPlanynoplan', 'PzinpeccionplanynoplanController::generatePdf_inspeccionPlanynoplan');
$routes->get('/funcionesyresponsabilidades/(:num)', 'HzfuncionesyrespController::funcionesyresponsabilidades/$1');
$routes->get('/entregaDotacion', 'HzentregadotacionController::entregaDotacion/$1');
$routes->get('/responsablePesv/(:num)', 'HzresponsablepesvController::responsablePesv/$1');
$routes->get('/generatePdf_responsablePesv', 'HzresponsablepesvController::generatePdf_responsablePesv');
$routes->get('/responsabilidadesSalud/(:num)', 'HzrespsaludController::responsabilidadesSalud/$1');
$routes->get('/generatePdf_responsabilidadesSalud', 'HzrespsaludController::generatePdf_responsabilidadesSalud');
$routes->get('/indentPeligros/(:num)', 'HzindentpeligroController::indentPeligros/$1');
$routes->get('/generatePdf_indentPeligros', 'HzindentpeligroController::generatePdf_indentPeligros');
$routes->get('/revisionAltagerencia/(:num)', 'HzrevaltagerenciaController::revisionAltagerencia/$1');
$routes->get('/generatePdf_revisionAltagerencia', 'HzrevaltagerenciaController::generatePdf_revisionAltagerencia');
$routes->get('/accionCorrectiva/(:num)', 'HzaccioncorrectivaController::accionCorrectiva/$1');
$routes->get('/generatePdf_accionCorrectiva', 'HzaccioncorrectivaController::generatePdf_accionCorrectiva');
$routes->get('/pausasActivas/(:num)', 'HzpausaactivaController::pausasActivas/$1');
$routes->get('/generatePdf_pausasActivas', 'HzpausaactivaController::generatePdf_pausasActivas');
$routes->get('/requisitosLegales/(:num)', 'HzreqlegalesController::requisitosLegales/$1');
$routes->get('/generatePdf_requisitosLegales', 'HzreqlegalesController::generatePdf_requisitosLegales');
$routes->get('/actaCocolab/(:num)', 'PzactacocolabController::actaCocolab/$1');
$routes->get('/generatePdf_actaCocolab', 'PzactacocolabController::generatePdf_actaCocolab');
$routes->get('/procedimientoAuditoria/(:num)', 'HzauditoriaController::procedimientoAuditoria/$1');
$routes->get('/generatePdf_procedimientoAuditoria', 'HzauditoriaController::generatePdf_procedimientoAuditoria');
$routes->get('/entregaDotacion/(:num)', 'HzentregadotacionController::entregaDotacion/$1');
$routes->get('/politicaAcoso/(:num)', 'PzpoliticaacosoController::politicaAcoso/$1');
$routes->get('/examenMedico/(:num)', 'PzexamedController::examenMedico/$1');



$routes->get('/listVigias', 'VigiaController::listVigias');
$routes->get('/addVigia', 'VigiaController::addVigia');
$routes->post('/saveVigia', 'VigiaController::saveVigia');
$routes->get('/editVigia/(:num)', 'VigiaController::editVigia/$1');
$routes->post('/updateVigia/(:num)', 'VigiaController::updateVigia/$1');
$routes->get('/deleteVigia/(:num)', 'VigiaController::deleteVigia/$1');


/* *********************KPI´S ****************************************/

$routes->get('/listKpiTypes', 'KpiTypeController::listKpiTypes');
$routes->get('/addKpiType', 'KpiTypeController::addKpiType');
$routes->post('/addKpiTypePost', 'KpiTypeController::addKpiTypePost');
$routes->get('/editKpiType/(:num)', 'KpiTypeController::editKpiType/$1');
$routes->post('/editKpiTypePost/(:num)', 'KpiTypeController::editKpiTypePost/$1');
$routes->get('/deleteKpiType/(:num)', 'KpiTypeController::deleteKpiType/$1');

$routes->get('/listKpiPolicies', 'KpiPolicyController::listKpiPolicies');
$routes->get('/addKpiPolicy', 'KpiPolicyController::addKpiPolicy');
$routes->post('/addKpiPolicyPost', 'KpiPolicyController::addKpiPolicyPost');
$routes->get('/editKpiPolicy/(:num)', 'KpiPolicyController::editKpiPolicy/$1');
$routes->post('/editKpiPolicyPost/(:num)', 'KpiPolicyController::editKpiPolicyPost/$1');
$routes->get('/deleteKpiPolicy/(:num)', 'KpiPolicyController::deleteKpiPolicy/$1');

$routes->get('/listObjectives', 'ObjectivesPolicyController::listObjectives');
$routes->get('/addObjective', 'ObjectivesPolicyController::addObjective');
$routes->post('/addObjectivePost', 'ObjectivesPolicyController::addObjectivePost');
$routes->get('/editObjective/(:num)', 'ObjectivesPolicyController::editObjective/$1');
$routes->post('/editObjectivePost/(:num)', 'ObjectivesPolicyController::editObjectivePost/$1');
$routes->get('/deleteObjective/(:num)', 'ObjectivesPolicyController::deleteObjective/$1');

$routes->get('/listKpiDefinitions', 'KpiDefinitionController::listKpiDefinitions');
$routes->get('/addKpiDefinition', 'KpiDefinitionController::addKpiDefinition');
$routes->post('/addKpiDefinitionPost', 'KpiDefinitionController::addKpiDefinitionPost');
$routes->get('/editKpiDefinition/(:num)', 'KpiDefinitionController::editKpiDefinition/$1');
$routes->post('/editKpiDefinitionPost/(:num)', 'KpiDefinitionController::editKpiDefinitionPost/$1');
$routes->get('/deleteKpiDefinition/(:num)', 'KpiDefinitionController::deleteKpiDefinition/$1');

$routes->get('/listDataOwners', 'DataOwnerController::listDataOwners');
$routes->get('/addDataOwner', 'DataOwnerController::addDataOwner');
$routes->post('/addDataOwnerPost', 'DataOwnerController::addDataOwnerPost');
$routes->get('/editDataOwner/(:num)', 'DataOwnerController::editDataOwner/$1');
$routes->post('/editDataOwnerPost/(:num)', 'DataOwnerController::editDataOwnerPost/$1');
$routes->get('/deleteDataOwner/(:num)', 'DataOwnerController::deleteDataOwner/$1');

$routes->get('/listNumeratorVariables', 'VariableNumeratorController::listNumeratorVariables');
$routes->get('/addNumeratorVariable', 'VariableNumeratorController::addNumeratorVariable');
$routes->post('/addNumeratorVariablePost', 'VariableNumeratorController::addNumeratorVariablePost');
$routes->get('/editNumeratorVariable/(:num)', 'VariableNumeratorController::editNumeratorVariable/$1');
$routes->post('/editNumeratorVariablePost/(:num)', 'VariableNumeratorController::editNumeratorVariablePost/$1');
$routes->get('/deleteNumeratorVariable/(:num)', 'VariableNumeratorController::deleteNumeratorVariable/$1');

$routes->get('/listKpis', 'KpisController::listKpis');
$routes->get('/addKpi', 'KpisController::addKpi');
$routes->post('/addKpiPost', 'KpisController::addKpiPost');
$routes->get('/editKpi/(:num)', 'KpisController::editKpi/$1');
$routes->post('/editKpiPost/(:num)', 'KpisController::editKpiPost/$1');
$routes->get('/deleteKpi/(:num)', 'KpisController::deleteKpi/$1');

$routes->get('/listDenominatorVariables', 'VariableDenominatorController::listDenominatorVariables');
$routes->get('/addDenominatorVariable', 'VariableDenominatorController::addDenominatorVariable');
$routes->post('/addDenominatorVariablePost', 'VariableDenominatorController::addDenominatorVariablePost');
$routes->get('/editDenominatorVariable/(:num)', 'VariableDenominatorController::editDenominatorVariable/$1');
$routes->post('/editDenominatorVariablePost/(:num)', 'VariableDenominatorController::editDenominatorVariablePost/$1');
$routes->get('/deleteDenominatorVariable/(:num)', 'VariableDenominatorController::deleteDenominatorVariable/$1');

$routes->get('/listMeasurementPeriods', 'MeasurementPeriodController::listMeasurementPeriods');
$routes->get('/addMeasurementPeriod', 'MeasurementPeriodController::addMeasurementPeriod');
$routes->post('/addMeasurementPeriodPost', 'MeasurementPeriodController::addMeasurementPeriodPost');
$routes->get('/editMeasurementPeriod/(:num)', 'MeasurementPeriodController::editMeasurementPeriod/$1');
$routes->post('/editMeasurementPeriodPost/(:num)', 'MeasurementPeriodController::editMeasurementPeriodPost/$1');
$routes->get('/deleteMeasurementPeriod/(:num)', 'MeasurementPeriodController::deleteMeasurementPeriod/$1');

$routes->get('/listClientKpis', 'ClientKpiController::listClientKpis');
$routes->get('/addClientKpi', 'ClientKpiController::addClientKpi');
$routes->post('/addClientKpiPost', 'ClientKpiController::addClientKpiPost');
$routes->get('/editClientKpi/(:num)', 'ClientKpiController::editClientKpi/$1');
$routes->post('/editClientKpiPost/(:num)', 'ClientKpiController::editClientKpiPost/$1');
$routes->get('/deleteClientKpi/(:num)', 'ClientKpiController::deleteClientKpi/$1');

$routes->get('/listClientKpisFull/(:num)', 'ClientKpiController::listClientKpisFull/$1');

$routes->get('/planDeTrabajoKpi/(:num)', 'kpiplandetrabajoController::plandetrabajoKpi/$1');
$routes->get('/indicadorTresPeriodos/(:num)', 'kpitresperiodosController::indicadorTresPeriodos/$1');
$routes->get('/indicadorcuatroPeriodos/(:num)', 'kpicuatroperiodosController::indicadorcuatroPeriodos/$1');
$routes->get('/indicadorseisPeriodos/(:num)', 'kpiseisperiodosController::indicadorseisPeriodos/$1');
$routes->get('/indicadordocePeriodos/(:num)', 'kpidoceperiodosController::indicadordocePeriodos/$1');
$routes->get('/indicadorAnual/(:num)', 'kpianualController::indicadorAnual/$1');
$routes->get('/mipvrdcKpi/(:num)', 'kpimipvrdcController::mipvrdcKpi/$1');
$routes->get('/gestionriesgoKpi/(:num)', 'kpigestionriesgoController::gestionriesgoKpi/$1');
$routes->get('/vigepidemiologicaKpi/(:num)', 'kpivigepidemiologicaController::vigepidemiologicaKpi/$1');
$routes->get('/evinicialKpi/(:num)', 'kpievinicialController::evinicialKpi/$1');
$routes->get('/accpreventivaKpi/(:num)', 'kpiaccpreventivaController::accpreventivaKpi/$1');
$routes->get('/cumplilegalKpi/(:num)', 'kpicumplilegalController::cumplilegalKpi/$1');
$routes->get('/capacitacionKpi/(:num)', 'kpicapacitacionController::capacitacionKpi/$1');
$routes->get('/estructuraKpi/(:num)', 'kpiestructuraController::estructuraKpi/$1');
$routes->get('/atelKpi/(:num)', 'kpatelController::atelKpi/$1');
$routes->get('/indicefrecuenciaKpi/(:num)', 'kpiindicefrecuenciaController::indicefrecuenciaKpi/$1');
$routes->get('/indiceseveridadKpi/(:num)', 'kpiindiceseveridadController::indiceseveridadKpi/$1');
$routes->get('/mortalidadKpi/(:num)', 'kpimortalidadController::mortalidadKpi/$1');
$routes->get('/prevalenciaKpi/(:num)', 'kpiprevalenciaController::prevalenciaKpi/$1');
$routes->get('/incidenciaKpi/(:num)', 'kpiincidenciaController::incidenciaKpi/$1');
$routes->get('/rehabilitacionKpi/(:num)', 'kprehabilitacionController::rehabilitacionKpi/$1');
$routes->get('/ausentismoKpi/(:num)', 'kpiausentismoController::ausentismoKpi/$1');
$routes->get('/todoslosKpi/(:num)', 'kpitodoslosobjetivosController::todoslosKpi/$1');

/* *******************************EVALUACION INICIAL***************************************** */

$routes->get('/listEvaluaciones', 'EvaluationController::listEvaluaciones');
$routes->get('/addEvaluacion', 'EvaluationController::addEvaluacion');
$routes->post('/addEvaluacionPost', 'EvaluationController::addEvaluacionPost');
$routes->get('/editEvaluacion/(:num)', 'EvaluationController::editEvaluacion/$1');
$routes->post('/editEvaluacionPost/(:num)', 'EvaluationController::editEvaluacionPost/$1');
$routes->get('/deleteEvaluacion/(:num)', 'EvaluationController::deleteEvaluacion/$1');

$routes->get('/listEvaluaciones/(:num)', 'ClientEvaluationController::listEvaluaciones/$1');


$routes->get('/listCapacitaciones', 'CapacitacionController::listCapacitaciones');
$routes->get('/addCapacitacion', 'CapacitacionController::addCapacitacion');
$routes->post('/addCapacitacionPost', 'CapacitacionController::addCapacitacionPost');
$routes->get('/editCapacitacion/(:num)', 'CapacitacionController::editCapacitacion/$1');
$routes->post('/editCapacitacionPost/(:num)', 'CapacitacionController::editCapacitacionPost/$1');
$routes->get('/deleteCapacitacion/(:num)', 'CapacitacionController::deleteCapacitacion/$1');


$routes->get('/listcronogCapacitacion', 'CronogcapacitacionController::listcronogCapacitacion');
$routes->get('/addcronogCapacitacion', 'CronogcapacitacionController::addcronogCapacitacion');
$routes->post('/addcronogCapacitacionPost', 'CronogcapacitacionController::addcronogCapacitacionPost');
$routes->get('/editcronogCapacitacion/(:num)', 'CronogcapacitacionController::editcronogCapacitacion/$1');
$routes->post('/editcronogCapacitacionPost/(:num)', 'CronogcapacitacionController::editcronogCapacitacionPost/$1');
$routes->get('/deletecronogCapacitacion/(:num)', 'CronogcapacitacionController::deletecronogCapacitacion/$1');

$routes->get('/listPlanDeTrabajoAnual', 'PlanDeTrabajoAnualController::listPlanDeTrabajoAnual');
$routes->get('/addPlanDeTrabajoAnual', 'PlanDeTrabajoAnualController::addPlanDeTrabajoAnual');
$routes->post('/addPlanDeTrabajoAnualPost', 'PlanDeTrabajoAnualController::addPlanDeTrabajoAnualPost');

$routes->get('/editPlanDeTrabajoAnual/(:num)', 'PlanDeTrabajoAnualController::editPlanDeTrabajoAnual/$1');
$routes->post('/editPlanDeTrabajoAnualPost/(:num)', 'PlanDeTrabajoAnualController::editPlanDeTrabajoAnualPost/$1');
$routes->get('/deletePlanDeTrabajoAnual/(:num)', 'PlanDeTrabajoAnualController::deletePlanDeTrabajoAnual/$1');


$routes->get('/listPendientes', 'PendientesController::listPendientes');
$routes->get('/addPendiente', 'PendientesController::addPendiente');
$routes->post('/addPendientePost', 'PendientesController::addPendientePost');
$routes->get('/editPendiente/(:num)', 'PendientesController::editPendiente/$1');
$routes->post('/editPendientePost/(:num)', 'PendientesController::editPendientePost/$1');
$routes->get('/deletePendiente/(:num)', 'PendientesController::deletePendiente/$1');

$routes->get('/listPendientesCliente/(:num)', 'ClientePendientesController::listPendientesCliente/$1');
$routes->get('/listCronogramasCliente/(:num)', 'CronogramaCapacitacionController::listCronogramasCliente/$1');
$routes->get('/listPlanTrabajoCliente/(:num)', 'ClientePlanTrabajoController::listPlanTrabajoCliente/$1');

$routes->get('/listMatricesCycloid', 'MatrizCycloidController::listMatricesCycloid');
$routes->get('/addMatrizCycloid', 'MatrizCycloidController::addMatrizCycloid');
$routes->post('/addMatrizCycloidPost', 'MatrizCycloidController::addMatrizCycloidPost');
$routes->get('/editMatrizCycloid/(:num)', 'MatrizCycloidController::editMatrizCycloid/$1');
$routes->post('/editMatrizCycloidPost/(:num)', 'MatrizCycloidController::editMatrizCycloidPost/$1');
$routes->get('/deleteMatrizCycloid/(:num)', 'MatrizCycloidController::deleteMatrizCycloid/$1');




$routes->get('lookerstudio/list', 'LookerStudioController::list');
$routes->get('lookerstudio/add', 'LookerStudioController::add');
$routes->post('lookerstudio/addPost', 'LookerStudioController::addPost');
$routes->get('lookerstudio/edit/(:num)', 'LookerStudioController::edit/$1');
$routes->post('lookerstudio/editPost/(:num)', 'LookerStudioController::editPost/$1');
$routes->get('lookerstudio/delete/(:num)', 'LookerStudioController::delete/$1');

$routes->get('/client/lista-lookerstudio', 'ClientLookerStudioController::index');

$routes->get('matrices/list', 'MatricesController::list');
$routes->get('matrices/add', 'MatricesController::add');
$routes->post('matrices/addPost', 'MatricesController::addPost');
$routes->get('matrices/edit/(:num)', 'MatricesController::edit/$1');
$routes->post('matrices/editPost/(:num)', 'MatricesController::editPost/$1');
$routes->get('matrices/delete/(:num)', 'MatricesController::delete/$1');

$routes->get('/client/lista-matrices', 'ClientMatrices::index');


$routes->get('client/panel', 'ClientPanelController::showPanel');


$routes->get('/detailreportlist', 'DetailReportController::detailReportList');
$routes->get('/detailreportadd', 'DetailReportController::detailReportAdd');
$routes->post('/detailreportadd', 'DetailReportController::detailReportAddPost');
$routes->get('/detailreportedit/(:num)', 'DetailReportController::detailReportEdit/$1');
$routes->post('/detailreportedit', 'DetailReportController::detailReportEditPost');
$routes->get('/detailreportdelete/(:num)', 'DetailReportController::detailReportDelete/$1');


$routes->post('/updatePlanDeTrabajo', 'PlanDeTrabajoAnualController::updatePlanDeTrabajo');

// Rutas en app/Config/Routes.php
$routes->get('/listinventarioactividades', 'InventarioActividadesController::listinventarioactividades');
$routes->get('/addinventarioactividades', 'InventarioActividadesController::addinventarioactividades');
$routes->post('/addinventarioactividades', 'InventarioActividadesController::addpostinventarioactividades');
$routes->get('/editinventarioactividades/(:num)', 'InventarioActividadesController::editinventarioactividades/$1');
$routes->post('/editinventarioactividades/(:num)', 'InventarioActividadesController::editpostinventarioactividades/$1');
$routes->get('/deleteinventarioactividades/(:num)', 'InventarioActividadesController::deleteinventarioactividades/$1');

$routes->get('consultant/plan', 'PlanController::index'); // Ruta para mostrar la vista
$routes->post('consultant/plan/upload', 'PlanController::upload'); // Ruta para procesar la carga

$routes->get('/nuevoListPlanTrabajoCliente/(:num)', 'NuevoClientePlanTrabajoController::nuevoListPlanTrabajoCliente/$1');

$routes->post('/updatecronogCapacitacion', 'CronogcapacitacionController::updatecronogCapacitacion');

$routes->get('consultant/csvcronogramadecapacitacion', 'CsvCronogramaDeCapacitacion::index');
$routes->post('consultant/csvcronogramadecapacitacion/upload', 'CsvCronogramaDeCapacitacion::upload');

$routes->post('updateEvaluacion', 'EvaluationController::updateEvaluacion');


$routes->post('updatePendiente', 'PendientesController::updatePendiente');

$routes->get('consultant/csvpendientes', 'CsvPendientes::index');
$routes->post('consultant/csvpendientes/upload', 'CsvPendientes::upload');

$routes->get('consultant/csvevaluacioninicial', 'CsvEvaluacionInicial::index');
$routes->post('consultant/csvevaluacioninicial/upload', 'CsvEvaluacionInicial::upload');



$routes->get('consultant/csvpoliticasparadocumentos', 'csvpoliticasparadocumentosController::index');
$routes->post('consultant/csvpoliticasparadocumentos/upload', 'csvpoliticasparadocumentosController::upload');

$routes->get('consultant/csvversionesdocumentos', 'csvversionesdocumentosController::index');
$routes->post('consultant/csvversionesdocumentos/upload', 'csvversionesdocumentosController::upload');

$routes->get('consultant/csvkpisempresas', 'csvkpiempresasController::index');
$routes->post('consultant/csvkpisempresas/upload', 'csvkpiempresasController::upload');



$routes->get('consultant/listitemdashboard', 'AdminlistdashboardController::listitemdashboard');
$routes->get('consultant/additemdashboard', 'AdminlistdashboardController::additemdashboard');
$routes->post('consultant/additemdashboardpost', 'AdminlistdashboardController::additemdashboardpost');
$routes->get('consultant/edititemdashboar/(:num)', 'AdminlistdashboardController::edititemdashboar/$1');
$routes->post('consultant/editpostitemdashboar/(:num)', 'AdminlistdashboardController::editpostitemdashboar/$1');
$routes->get('consultant/deleteitemdashboard/(:num)', 'AdminlistdashboardController::deleteitemdashboard/$1');

$routes->get('admin/dashboard', 'CustomDashboardController::index');

$routes->get('/accesosseguncliente/list', 'AccesossegunclienteController::listaccesosseguncliente');
$routes->get('/accesosseguncliente/add', 'AccesossegunclienteController::addaccesosseguncliente');
$routes->post('/accesosseguncliente/add', 'AccesossegunclienteController::addpostaccesosseguncliente');
$routes->get('/accesosseguncliente/edit/(:num)', 'AccesossegunclienteController::editaccesosseguncliente/$1');
$routes->post('/accesosseguncliente/edit', 'AccesossegunclienteController::editpostaccesosseguncliente');
$routes->get('/accesosseguncliente/delete/(:num)', 'AccesossegunclienteController::deleteaccesosseguncliente/$1');

$routes->get('/estandarcontractual/list', 'EstandarcontractualController::listestandarcontractual');
$routes->get('/estandarcontractual/add', 'EstandarcontractualController::addestandarcontractual');
$routes->post('/estandarcontractual/add', 'EstandarcontractualController::addpostestandarcontractual');
$routes->get('/estandarcontractual/edit/(:num)', 'EstandarcontractualController::editestandarcontractual/$1');
$routes->post('/estandarcontractual/edit', 'EstandarcontractualController::editpostestandarcontractual');
$routes->get('/estandarcontractual/delete/(:num)', 'EstandarcontractualController::deleteestandarcontractual/$1');

$routes->get('/accesosseguncontractualidad/list', 'AccesosseguncontractualidadController::listaccesosseguncontractualidad');
$routes->get('/accesosseguncontractualidad/add', 'AccesosseguncontractualidadController::addaccesosseguncontractualidad');
$routes->post('/accesosseguncontractualidad/add', 'AccesosseguncontractualidadController::addpostaccesosseguncontractualidad');
$routes->get('/accesosseguncontractualidad/edit/(:num)', 'AccesosseguncontractualidadController::editaccesosseguncontractualidad/$1');
$routes->post('/accesosseguncontractualidad/edit', 'AccesosseguncontractualidadController::editpostaccesosseguncontractualidad');
$routes->get('/accesosseguncontractualidad/delete/(:num)', 'AccesosseguncontractualidadController::deleteaccesosseguncontractualidad/$1');


