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
$routes->get('/report_dashboard/(:num)', 'ClienteReportController::index/$1');
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
$routes->get('/politicaacosoSexual/(:num)', 'PzpoliticaacososexualController::politicaacosoSexual/$1');
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
$routes->get('/acosoSexual/(:num)', 'PzacososexualController::acosoSexual/$1');
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

/* $routes->get('/listClientKpisFull/(:num)', 'ClientKpiController::listClientKpisFull/$1'); */
$routes->get('/listClientKpisFull', 'ClientKpiController::listClientKpisFull');


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
// Rutas para generación automática y actualización de fechas
$routes->post('/cronogCapacitacion/generate', 'CronogcapacitacionController::generate');
$routes->post('/cronogCapacitacion/updateDateByMonth', 'CronogcapacitacionController::updateDateByMonth');
$routes->get('/cronogCapacitacion/getClients', 'CronogcapacitacionController::getClients');
$routes->get('/cronogCapacitacion/getClientContract', 'CronogcapacitacionController::getClientContract');
$routes->post('/cronogCapacitacion/socializarEmail', 'CronogcapacitacionController::socializarEmail');

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
$routes->get('client/panel/(:num)', 'ClientPanelController::showPanel/$1');

// Vista de documentos SST para cliente (solo lectura)
$routes->get('client/mis-documentos-sst', 'ClienteDocumentosSstController::index');
$routes->get('client/mis-documentos-sst/(:num)', 'ClienteDocumentosSstController::index/$1');
$routes->get('client/mis-documentos-sst/carpeta/(:num)', 'ClienteDocumentosSstController::carpeta/$1');

// Aprobaciones pendientes - ELIMINADO: Se usa PDF con firma electrónica en su lugar

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

$routes->post('/recalcularConteoDias', 'PendientesController::recalcularConteoDias');

// Rutas API para operaciones vía AJAX
$routes->get('api/getClientes', 'PlanDeTrabajoAnualController::getClientes');
$routes->get('api/getActividadesAjax', 'PlanDeTrabajoAnualController::getActividadesAjax');
$routes->post('api/updatePlanDeTrabajo', 'PlanDeTrabajoAnualController::updatePlanDeTrabajo');
$routes->get('listPlanDeTrabajoAnualAjax', 'PlanDeTrabajoAnualController::listPlanDeTrabajoAnualAjax');


$routes->get('api/getClientes', 'EvaluationController::getClientes');
$routes->get('api/getEvaluaciones', 'EvaluationController::getEvaluaciones');
$routes->post('api/updateEvaluacion', 'EvaluationController::updateEvaluacion');
$routes->get('listEvaluacionesAjax', 'EvaluationController::listEvaluacionesAjax');

$routes->get('api/getClientes', 'CronogcapacitacionController::getClientes');
$routes->get('api/getCronogramasAjax', 'CronogcapacitacionController::getCronogramasAjax');
$routes->post('api/updatecronogCapacitacion', 'CronogcapacitacionController::updatecronogCapacitacion');
$routes->get('listcronogCapacitacionAjax', 'CronogcapacitacionController::listcronogCapacitacionAjax');

$routes->get('api/getClientes', 'PendientesController::getClientes');
$routes->get('api/getPendientesAjax', 'PendientesController::getPendientesAjax');
$routes->post('api/updatePendiente', 'PendientesController::updatePendiente');
$routes->get('listPendientesAjax', 'PendientesController::listPendientesAjax');

$routes->get('consultor/dashboard', 'ConsultorTablaItemsController::index');
$routes->get('consultant/dashboard', 'ConsultantDashboardController::index');

// Ver vista del cliente como consultor/admin
$routes->get('consultor/selector-cliente', 'ConsultorTablaItemsController::selectorCliente');
$routes->get('consultor/vista-cliente/(:num)', 'ConsultorTablaItemsController::vistaCliente/$1');

// Vista de listado (ya existente)
$routes->get('/pta-cliente-nueva/list', 'PtaClienteNuevaController::listPtaClienteNuevaModel');

// Rutas para Agregar Registro
$routes->get('/pta-cliente-nueva/add', 'PtaClienteNuevaController::addPtaClienteNuevaModel');
$routes->post('/pta-cliente-nueva/addpost', 'PtaClienteNuevaController::addpostPtaClienteNuevaModel');

// Rutas para Editar Registro
$routes->get('/pta-cliente-nueva/edit/(:num)', 'PtaClienteNuevaController::editPtaClienteNuevaModel/$1');
$routes->post('/pta-cliente-nueva/editpost/(:num)', 'PtaClienteNuevaController::editpostPtaClienteNuevaModel/$1');

// Ruta para edición inline (ya definida)
$routes->post('/pta-cliente-nueva/editinginline', 'PtaClienteNuevaController::editinginlinePtaClienteNuevaModel');

// Ruta para exportar a Excel (CSV)
$routes->get('/pta-cliente-nueva/excel', 'PtaClienteNuevaController::exportExcelPtaClienteNuevaModel');
$routes->get('/pta-cliente-nueva/delete/(:num)', 'PtaClienteNuevaController::deletePtaClienteNuevaModel/$1');

$routes->get('/getVersionsByClient/(:num)', 'VersionController::getVersionsByClient/$1');

$routes->get('consultant/actualizar_pta_cliente', 'CsvUploadController::index'); // Carga la vista
$routes->post('csv/upload', 'CsvUploadController::upload'); // Procesa el CSV

$routes->post('/pta-cliente-nueva/updateCerradas', 'PtaClienteNuevaController::updateCerradas');

// Gestión Rápida: asignar fecha_propuesta al último día del mes seleccionado
$routes->post('/pta-cliente-nueva/updateDateByMonth', 'PtaClienteNuevaController::updateDateByMonth');

// Ruta para socialización del Plan de Trabajo por email
$routes->post('/socializacion/send-plan-trabajo', 'SocializacionEmailController::sendPlanTrabajo');
// Ruta para socialización de Evaluación de Estándares Mínimos por email
$routes->post('/socializacion/send-evaluacion-estandares', 'SocializacionEmailController::sendEvaluacionEstandares');

$routes->post('api/getCronogramasAjax', 'CronogramaCapacitacionController::getCronogramasAjax');

$routes->post('api/recalcularConteoDias', 'PendientesController::recalcularConteoDias');
$routes->get('api/getClientIndicators', 'EvaluationController::getClientIndicators');

/* Rutas de Gestión de Usuarios: */


$routes->get('/admin/users', 'UserController::listUsers');
$routes->get('/admin/users/add', 'UserController::addUser');
$routes->post('/admin/users/add', 'UserController::addUserPost');
$routes->get('/admin/users/edit/(:num)', 'UserController::editUser/$1');
$routes->post('/admin/users/edit/(:num)', 'UserController::editUserPost/$1');
$routes->get('/admin/users/delete/(:num)', 'UserController::deleteUser/$1');
$routes->get('/admin/users/toggle/(:num)', 'UserController::toggleStatus/$1');
$routes->get('/admin/users/reset-password/(:num)', 'UserController::resetPassword/$1');
/* Ruta de cuenta bloqueada: */


$routes->get('/auth/blocked', 'AuthController::blocked');
/* Rutas de Consumo de Plataforma:
 */

$routes->get('/admin/usage', 'UsageController::index');
$routes->get('/admin/usage/user/(:num)', 'UsageController::userDetail/$1');
$routes->get('/admin/usage/export-csv', 'UsageController::exportCsv');
$routes->get('/admin/usage/chart-data', 'UsageController::chartData');
/* Rutas de Recuperación de Contraseña: */


$routes->get('/forgot-password', 'AuthController::forgotPassword');
$routes->post('/forgot-password', 'AuthController::forgotPasswordPost');
$routes->get('/reset-password/(:any)', 'AuthController::resetPassword/$1');
$routes->post('/reset-password/(:any)', 'AuthController::resetPasswordPost/$1');

// Cliente
$routes->get('client/dashboard-estandares/(:num)', 'ClientDashboardEstandaresController::index/$1');

// Consultor
$routes->get('consultant/dashboard-estandares', 'ConsultantDashboardEstandaresController::index');

// Cliente
$routes->get('client/dashboard-pendientes/(:num)', 'ClientDashboardPendientesController::index/$1');
$routes->get('client/dashboard-plan-trabajo/(:num)', 'ClientDashboardPlanTrabajoController::index/$1');
$routes->get('client/dashboard-capacitaciones/(:num)', 'ClientDashboardCapacitacionesController::index/$1');

// Consultor
$routes->get('consultant/dashboard-pendientes', 'ConsultantDashboardPendientesController::index');
$routes->get('consultant/dashboard-plan-trabajo', 'ConsultantDashboardPlanTrabajoController::index');
$routes->get('consultant/dashboard-capacitaciones', 'ConsultantDashboardCapacitacionesController::index');

// Lista y alertas
$routes->get('/contracts', 'ContractController::index');
$routes->get('/contracts/alerts', 'ContractController::alerts');

// Ver contrato
$routes->get('/contracts/view/(:num)', 'ContractController::view/$1');

// Crear contrato
$routes->get('/contracts/create', 'ContractController::create');
$routes->get('/contracts/create/(:num)', 'ContractController::create/$1');
$routes->post('/contracts/store', 'ContractController::store');

// Renovar contrato
$routes->get('/contracts/renew/(:num)', 'ContractController::renew/$1');
$routes->post('/contracts/process-renewal', 'ContractController::processRenewal');

// Cancelar contrato
$routes->get('/contracts/cancel/(:num)', 'ContractController::cancel/$1');
$routes->post('/contracts/cancel/(:num)', 'ContractController::cancel/$1');

// Historial de cliente
$routes->get('/contracts/client-history/(:num)', 'ContractController::clientHistory/$1');

// Mantenimiento
$routes->get('/contracts/maintenance', 'ContractController::maintenance');

// API
$routes->get('/api/contracts/active/(:num)', 'ContractController::getActiveContract/$1');
$routes->get('/api/contracts/stats', 'ContractController::getStats');

// Generación IA de cláusula cuarta
$routes->post('/contracts/generar-clausula-ia', 'ContractController::generarClausulaIA');

// Editar datos y generar PDF
$routes->get('/contracts/edit-contract-data/(:num)', 'ContractController::editContractData/$1');
$routes->post('/contracts/save-and-generate/(:num)', 'ContractController::saveAndGeneratePDF/$1');
$routes->get('/contracts/download-pdf/(:num)', 'ContractController::downloadPDF/$1');
$routes->get('/contracts/diagnostico-firmas/(:num)', 'ContractController::diagnosticoFirmas/$1'); // TEMPORAL - eliminar

// Documentación por contrato
$routes->get('/contracts/documentacion/(:num)', 'DocumentacionContratoController::previsualizarDocumentacion/$1');
$routes->get('/contracts/descargar-documentacion/(:num)', 'DocumentacionContratoController::descargarDocumentacion/$1');
$routes->get('/contracts/seleccionar-documentacion/(:num)', 'DocumentacionContratoController::seleccionarDocumentacion/$1');
$routes->get('/contracts/filtrar-documentacion/(:num)', 'DocumentacionContratoController::filtrarDocumentacion/$1');
$routes->get('/contracts/descargar-filtrado/(:num)', 'DocumentacionContratoController::descargarFiltrado/$1');
$routes->get('/contracts/documentacion-cliente/(:num)', 'DocumentacionContratoController::seleccionarDocumentacion/$1');
$routes->get('/contracts/descargar-documentacion-cliente/(:num)', 'DocumentacionContratoController::descargarPorCliente/$1');

/* *********************MÓDULO DOCUMENTACIÓN SST ****************************************/

// Dashboard de documentación
$routes->get('/documentacion/instructivo', 'DocumentacionController::instructivo');
$routes->get('/documentacion', 'DocumentacionController::index');
$routes->get('/documentacion/(:num)', 'DocumentacionController::index/$1');
$routes->get('/documentacion/seleccionar-cliente', 'DocumentacionController::seleccionarCliente');
$routes->get('/documentacion/carpeta/(:num)', 'DocumentacionController::carpeta/$1');
$routes->get('/documentacion/documentos/(:num)', 'DocumentacionController::documentos/$1');
$routes->get('/documentacion/ver/(:num)', 'DocumentacionController::verDocumento/$1');
$routes->get('/documentacion/buscar/(:num)', 'DocumentacionController::buscar/$1');
$routes->get('/documentacion/proximos-revision/(:num)', 'DocumentacionController::proximosRevision/$1');
$routes->post('/documentacion/generar-estructura', 'DocumentacionController::generarEstructura');
$routes->get('/documentacion/arbol-carpetas/(:num)', 'DocumentacionController::getArbolCarpetas/$1');

// Generador de documentos
$routes->get('/documentacion/nuevo/(:num)', 'GeneradorDocumentoController::nuevo/$1');
$routes->post('/documentacion/configurar/(:num)', 'GeneradorDocumentoController::configurar/$1');
$routes->get('/documentacion/configurar/(:num)', 'GeneradorDocumentoController::configurar/$1');
$routes->post('/documentacion/crear/(:num)', 'GeneradorDocumentoController::crear/$1');
$routes->get('/documentacion/editar/(:num)', 'GeneradorDocumentoController::editar/$1');
$routes->get('/documentacion/editar-seccion/(:num)/(:num)', 'GeneradorDocumentoController::editarSeccion/$1/$2');
$routes->post('/documentacion/guardar-seccion', 'GeneradorDocumentoController::guardarSeccion');
$routes->post('/documentacion/aprobar-seccion', 'GeneradorDocumentoController::aprobarSeccion');
$routes->post('/documentacion/generar-ia', 'GeneradorDocumentoController::generarConIA');
$routes->get('/documentacion/vista-previa/(:num)', 'GeneradorDocumentoController::vistaPrevia/$1');
$routes->post('/documentacion/finalizar/(:num)', 'GeneradorDocumentoController::finalizar/$1');

// Estándares del cliente
$routes->get('/estandares', 'EstandaresClienteController::seleccionarCliente');
$routes->get('/estandares/seleccionar-cliente', 'EstandaresClienteController::seleccionarCliente');
$routes->get('/estandares/(:num)', 'EstandaresClienteController::index/$1');
$routes->get('/estandares/detalle/(:num)/(:num)', 'EstandaresClienteController::detalle/$1/$2');
$routes->post('/estandares/actualizar-estado', 'EstandaresClienteController::actualizarEstado');
$routes->get('/estandares/inicializar/(:num)', 'EstandaresClienteController::inicializar/$1');
$routes->get('/estandares/transiciones/(:num)', 'EstandaresClienteController::transiciones/$1');
$routes->post('/estandares/aplicar-transicion/(:num)', 'EstandaresClienteController::aplicarTransicion/$1');
$routes->post('/estandares/detectar-cambio', 'EstandaresClienteController::detectarCambio');
$routes->get('/estandares/pendientes/(:num)', 'EstandaresClienteController::pendientes/$1');
$routes->get('/estandares/catalogo', 'EstandaresClienteController::catalogo');
$routes->get('/estandares/exportar/(:num)', 'EstandaresClienteController::exportarReporte/$1');

// Contexto SST del Cliente
$routes->get('/contexto', 'ContextoClienteController::index');
$routes->get('/contexto/(:num)', 'ContextoClienteController::ver/$1');
$routes->post('/contexto/guardar', 'ContextoClienteController::guardar');
$routes->get('/contexto/json/(:num)', 'ContextoClienteController::getContextoJson/$1');

// Responsables del SG-SST
$routes->get('/responsables-sst/(:num)', 'ResponsablesSSTController::index/$1');
$routes->get('/responsables-sst/(:num)/crear', 'ResponsablesSSTController::crear/$1');
$routes->get('/responsables-sst/(:num)/editar/(:num)', 'ResponsablesSSTController::editar/$1/$2');
$routes->post('/responsables-sst/(:num)/guardar', 'ResponsablesSSTController::guardar/$1');
$routes->post('/responsables-sst/(:num)/eliminar/(:num)', 'ResponsablesSSTController::eliminar/$1/$2');
$routes->get('/responsables-sst/(:num)/api', 'ResponsablesSSTController::apiObtener/$1');
$routes->get('/responsables-sst/(:num)/verificar', 'ResponsablesSSTController::apiVerificar/$1');
$routes->post('/responsables-sst/(:num)/migrar', 'ResponsablesSSTController::migrar/$1');

// Firma electrónica
$routes->get('/firma/solicitar/(:num)', 'FirmaElectronicaController::solicitar/$1');
$routes->post('/firma/crear-solicitud', 'FirmaElectronicaController::crearSolicitud');
$routes->get('/firma/firmar/(:any)', 'FirmaElectronicaController::firmar/$1');
$routes->post('/firma/procesar', 'FirmaElectronicaController::procesarFirma');
$routes->get('/firma/confirmacion/(:any)', 'FirmaElectronicaController::confirmacion/$1');
$routes->get('/firma/estado/(:num)', 'FirmaElectronicaController::estado/$1');
$routes->post('/firma/reenviar/(:num)', 'FirmaElectronicaController::reenviar/$1');
$routes->post('/firma/cancelar/(:num)', 'FirmaElectronicaController::cancelar/$1');
$routes->get('/firma/audit-log/(:num)', 'FirmaElectronicaController::auditLog/$1');
$routes->get('/firma/verificar/(:any)', 'FirmaElectronicaController::verificar/$1');
$routes->post('/firma/firmar-interno/(:num)', 'FirmaElectronicaController::firmarInterno/$1');
$routes->get('/firma/certificado-pdf/(:num)', 'FirmaElectronicaController::certificadoPDF/$1');

// Control Documental ISO - Versionamiento
$routes->get('/control-documental/historial/(:num)', 'ControlDocumentalController::historial/$1');
$routes->get('/control-documental/ver-version/(:num)', 'ControlDocumentalController::verVersion/$1');
$routes->get('/control-documental/comparar/(:num)', 'ControlDocumentalController::comparar/$1');
$routes->get('/control-documental/nueva-version/(:num)', 'ControlDocumentalController::nuevaVersion/$1');
$routes->post('/control-documental/crear-version/(:num)', 'ControlDocumentalController::crearVersion/$1');
$routes->get('/control-documental/restaurar/(:num)', 'ControlDocumentalController::restaurar/$1');
$routes->post('/control-documental/marcar-obsoleto/(:num)', 'ControlDocumentalController::marcarObsoleto/$1');
$routes->post('/control-documental/aprobar/(:num)', 'ControlDocumentalController::aprobar/$1');
$routes->get('/control-documental/tabla-cambios/(:num)', 'ControlDocumentalController::tablaControlCambios/$1');
$routes->get('/control-documental/encabezado/(:num)', 'ControlDocumentalController::generarEncabezado/$1');

// Exportación de documentos
$routes->get('/exportar/pdf/(:num)', 'ExportacionDocumentoController::pdf/$1');
$routes->get('/exportar/pdf-borrador/(:num)', 'ExportacionDocumentoController::pdfBorrador/$1');
$routes->get('/exportar/word/(:num)', 'ExportacionDocumentoController::word/$1');
$routes->get('/exportar/zip/(:num)', 'ExportacionDocumentoController::zip/$1');
$routes->get('/exportar/descargar/(:num)', 'ExportacionDocumentoController::descargar/$1');
$routes->get('/exportar/vista-impresion/(:num)', 'ExportacionDocumentoController::vistaImpresion/$1');
$routes->get('/exportar/historial/(:num)', 'ExportacionDocumentoController::historial/$1');

// Indicadores del SG-SST
$routes->get('/indicadores-sst/(:num)', 'IndicadoresSSTController::index/$1');
$routes->get('/indicadores-sst/(:num)/crear', 'IndicadoresSSTController::crear/$1');
$routes->get('/indicadores-sst/(:num)/editar/(:num)', 'IndicadoresSSTController::editar/$1/$2');
$routes->post('/indicadores-sst/(:num)/guardar', 'IndicadoresSSTController::guardar/$1');
$routes->post('/indicadores-sst/(:num)/medir/(:num)', 'IndicadoresSSTController::registrarMedicion/$1/$2');
$routes->post('/indicadores-sst/(:num)/eliminar/(:num)', 'IndicadoresSSTController::eliminar/$1/$2');
$routes->post('/indicadores-sst/(:num)/generar-sugeridos', 'IndicadoresSSTController::generarSugeridos/$1');
$routes->get('/indicadores-sst/(:num)/api', 'IndicadoresSSTController::apiObtener/$1');
$routes->get('/indicadores-sst/(:num)/verificar', 'IndicadoresSSTController::apiVerificar/$1');
$routes->get('/indicadores-sst/historico/(:num)', 'IndicadoresSSTController::apiHistorico/$1');

// Dashboard jerárquico de indicadores (ZZ_94)
$routes->get('/indicadores-sst/(:num)/dashboard', 'IndicadoresSSTController::dashboard/$1');
$routes->get('/indicadores-sst/(:num)/api/dashboard', 'IndicadoresSSTController::apiDashboard/$1');

// Fichas Técnicas de Indicadores (ZZ_99)
$routes->get('/indicadores-sst/(:num)/ficha-tecnica/(:num)', 'IndicadoresSSTController::fichaTecnica/$1/$2');
$routes->post('/indicadores-sst/(:num)/ficha-tecnica/(:num)/actualizar-campo', 'IndicadoresSSTController::actualizarCampoFicha/$1/$2');
$routes->get('/indicadores-sst/(:num)/ficha-tecnica/(:num)/pdf', 'IndicadoresSSTController::fichaTecnicaPDF/$1/$2');
$routes->get('/indicadores-sst/(:num)/ficha-tecnica/(:num)/word', 'IndicadoresSSTController::fichaTecnicaWord/$1/$2');
$routes->get('/indicadores-sst/(:num)/matriz-objetivos-metas', 'IndicadoresSSTController::matrizObjetivosMetas/$1');
$routes->get('/indicadores-sst/(:num)/matriz-objetivos-metas/pdf', 'IndicadoresSSTController::matrizObjetivosMetasPDF/$1');

// Generador IA de Indicadores (General - Grupos C+D)
$routes->get('/indicadores-sst/(:num)/ia/contexto', 'IndicadoresSSTController::previsualizarContextoIA/$1');
$routes->post('/indicadores-sst/(:num)/ia/preview', 'IndicadoresSSTController::previewIndicadoresIA/$1');
$routes->post('/indicadores-sst/(:num)/ia/guardar', 'IndicadoresSSTController::guardarIndicadoresIA/$1');
$routes->post('/indicadores-sst/(:num)/ia/regenerar', 'IndicadoresSSTController::regenerarIndicadorIA/$1');

// Generador IA de Actividades desde Indicadores (Ingenieria Inversa)
$routes->get('/indicadores-sst/(:num)/actividades-ia/contexto', 'IndicadoresSSTController::previsualizarContextoActividades/$1');
$routes->post('/indicadores-sst/(:num)/actividades-ia/preview', 'IndicadoresSSTController::previewActividadesIA/$1');
$routes->post('/indicadores-sst/(:num)/actividades-ia/guardar', 'IndicadoresSSTController::guardarActividadesIA/$1');
$routes->post('/indicadores-sst/(:num)/actividades-ia/regenerar', 'IndicadoresSSTController::regenerarActividadIA/$1');

// Generador IA - Cronograma, PTA, Indicadores
$routes->get('/generador-ia/(:num)', 'GeneradorIAController::index/$1');
$routes->get('/generador-ia/(:num)/preview-cronograma', 'GeneradorIAController::previewCronograma/$1');
$routes->post('/generador-ia/(:num)/generar-cronograma', 'GeneradorIAController::generarCronograma/$1');
$routes->get('/generador-ia/(:num)/preview-pta', 'GeneradorIAController::previewPTA/$1');
$routes->post('/generador-ia/(:num)/generar-pta-cronograma', 'GeneradorIAController::generarPTADesdeCronograma/$1');
$routes->post('/generador-ia/(:num)/generar-pta-completo', 'GeneradorIAController::generarPTACompleto/$1');
$routes->get('/generador-ia/(:num)/preview-indicadores', 'GeneradorIAController::previewIndicadores/$1');
$routes->post('/generador-ia/(:num)/generar-indicadores', 'GeneradorIAController::generarIndicadores/$1');
$routes->post('/generador-ia/(:num)/generar-flujo-completo', 'GeneradorIAController::generarFlujoCompleto/$1');
$routes->post('/generador-ia/(:num)/generar-programa-capacitacion', 'GeneradorIAController::generarProgramaCapacitacion/$1');
$routes->get('/generador-ia/(:num)/resumen', 'GeneradorIAController::resumen/$1');

// Módulo 3.1.2 - Programa de Promoción y Prevención en Salud
$routes->get('/generador-ia/(:num)/pyp-salud', 'GeneradorIAController::pypSalud/$1');
$routes->get('/generador-ia/(:num)/preview-actividades-pyp', 'GeneradorIAController::previewActividadesPyP/$1');
$routes->post('/generador-ia/(:num)/generar-actividades-pyp', 'GeneradorIAController::generarActividadesPyP/$1');
$routes->get('/generador-ia/(:num)/resumen-pyp-salud', 'GeneradorIAController::resumenPyPSalud/$1');

// Módulo 3.1.2 - Indicadores de PyP Salud
$routes->get('/generador-ia/(:num)/indicadores-pyp-salud', 'GeneradorIAController::indicadoresPyPSalud/$1');
$routes->get('/generador-ia/(:num)/preview-indicadores-pyp', 'GeneradorIAController::previewIndicadoresPyP/$1');
$routes->post('/generador-ia/(:num)/generar-indicadores-pyp', 'GeneradorIAController::generarIndicadoresPyP/$1');

// Módulo 2.2.1 - Objetivos del SG-SST (Parte 1)
$routes->get('/generador-ia/(:num)/objetivos-sgsst', 'GeneradorIAController::objetivosSgsst/$1');
$routes->get('/generador-ia/(:num)/preview-objetivos', 'GeneradorIAController::previewObjetivos/$1');
$routes->post('/generador-ia/(:num)/generar-objetivos', 'GeneradorIAController::generarObjetivos/$1');
$routes->delete('/generador-ia/(:num)/eliminar-objetivo/(:num)', 'GeneradorIAController::eliminarObjetivo/$1/$2');
$routes->delete('/generador-ia/(:num)/eliminar-todos-objetivos', 'GeneradorIAController::eliminarTodosObjetivos/$1');
$routes->post('/generador-ia/(:num)/regenerar-objetivo', 'GeneradorIAController::regenerarObjetivo/$1');

// Módulo 2.2.1 - Indicadores de Objetivos (Parte 2)
$routes->get('/generador-ia/(:num)/indicadores-objetivos', 'GeneradorIAController::indicadoresObjetivos/$1');
$routes->get('/generador-ia/(:num)/preview-indicadores-objetivos', 'GeneradorIAController::previewIndicadoresObjetivos/$1');
$routes->post('/generador-ia/(:num)/generar-indicadores-objetivos', 'GeneradorIAController::generarIndicadoresObjetivos/$1');
$routes->post('/generador-ia/(:num)/regenerar-indicador', 'GeneradorIAController::regenerarIndicador/$1');

// Módulo 1.2.1 - Programa de Capacitación SST
$routes->get('/generador-ia/(:num)/capacitacion-sst', 'GeneradorIAController::capacitacionSst/$1');
$routes->get('/generador-ia/(:num)/preview-capacitaciones-sst', 'GeneradorIAController::previewCapacitacionesSst/$1');
$routes->post('/generador-ia/(:num)/generar-capacitaciones-sst', 'GeneradorIAController::generarCapacitacionesSst/$1');
$routes->get('/generador-ia/(:num)/resumen-capacitacion-sst', 'GeneradorIAController::resumenCapacitacionSst/$1');
$routes->post('/generador-ia/(:num)/regenerar-capacitacion', 'GeneradorIAController::regenerarCapacitacion/$1');
// Indicadores de Capacitación SST (Parte 2 del módulo 1.2.1)
$routes->get('/generador-ia/(:num)/preview-indicadores-capacitacion', 'GeneradorIAController::previewIndicadoresCapacitacion/$1');
$routes->post('/generador-ia/(:num)/generar-indicadores-capacitacion', 'GeneradorIAController::generarIndicadoresCapacitacion/$1');
$routes->post('/generador-ia/(:num)/regenerar-indicador-capacitacion', 'GeneradorIAController::regenerarIndicadorCapacitacion/$1');

// Módulo 3.1.7 - Estilos de Vida Saludable y Entornos Saludables (Parte 1: Actividades)
$routes->get('/generador-ia/(:num)/estilos-vida-saludable', 'GeneradorIAController::estilosVidaSaludable/$1');
$routes->get('/generador-ia/(:num)/preview-actividades-estilos-vida', 'GeneradorIAController::previewActividadesEstilosVida/$1');
$routes->post('/generador-ia/(:num)/generar-actividades-estilos-vida', 'GeneradorIAController::generarActividadesEstilosVida/$1');
$routes->get('/generador-ia/(:num)/resumen-estilos-vida', 'GeneradorIAController::resumenEstilosVida/$1');
// Módulo 3.1.7 - Estilos de Vida Saludable (Parte 2: Indicadores)
$routes->get('/generador-ia/(:num)/indicadores-estilos-vida', 'GeneradorIAController::indicadoresEstilosVida/$1');
$routes->get('/generador-ia/(:num)/preview-indicadores-estilos-vida', 'GeneradorIAController::previewIndicadoresEstilosVida/$1');
$routes->post('/generador-ia/(:num)/generar-indicadores-estilos-vida', 'GeneradorIAController::generarIndicadoresEstilosVida/$1');

// Módulo 3.1.4 - Evaluaciones Medicas Ocupacionales (Parte 1: Actividades)
$routes->get('/generador-ia/(:num)/evaluaciones-medicas-ocupacionales', 'GeneradorIAController::evaluacionesMedicasOcupacionales/$1');
$routes->get('/generador-ia/(:num)/preview-actividades-evaluaciones-medicas', 'GeneradorIAController::previewActividadesEvaluacionesMedicas/$1');
$routes->post('/generador-ia/(:num)/generar-actividades-evaluaciones-medicas', 'GeneradorIAController::generarActividadesEvaluacionesMedicas/$1');
$routes->get('/generador-ia/(:num)/resumen-evaluaciones-medicas', 'GeneradorIAController::resumenEvaluacionesMedicas/$1');
// Módulo 3.1.4 - Evaluaciones Medicas Ocupacionales (Parte 2: Indicadores)
$routes->get('/generador-ia/(:num)/indicadores-evaluaciones-medicas', 'GeneradorIAController::indicadoresEvaluacionesMedicas/$1');
$routes->get('/generador-ia/(:num)/preview-indicadores-evaluaciones-medicas', 'GeneradorIAController::previewIndicadoresEvaluacionesMedicas/$1');
$routes->post('/generador-ia/(:num)/generar-indicadores-evaluaciones-medicas', 'GeneradorIAController::generarIndicadoresEvaluacionesMedicas/$1');

// Módulo 4.2.5 - Mantenimiento Periodico (Parte 1: Actividades)
$routes->get('/generador-ia/(:num)/mantenimiento-periodico', 'GeneradorIAController::mantenimientoPeriodico/$1');
$routes->get('/generador-ia/(:num)/preview-actividades-mantenimiento', 'GeneradorIAController::previewActividadesMantenimiento/$1');
$routes->post('/generador-ia/(:num)/generar-actividades-mantenimiento', 'GeneradorIAController::generarActividadesMantenimiento/$1');
$routes->get('/generador-ia/(:num)/resumen-mantenimiento', 'GeneradorIAController::resumenMantenimiento/$1');
// Módulo 4.2.5 - Mantenimiento Periodico (Parte 2: Indicadores)
$routes->get('/generador-ia/(:num)/indicadores-mantenimiento-periodico', 'GeneradorIAController::indicadoresMantenimientoPeriodico/$1');
$routes->get('/generador-ia/(:num)/preview-indicadores-mantenimiento', 'GeneradorIAController::previewIndicadoresMantenimiento/$1');
$routes->post('/generador-ia/(:num)/generar-indicadores-mantenimiento', 'GeneradorIAController::generarIndicadoresMantenimiento/$1');
// Módulo 4.2.5 - Mantenimiento Periodico (Soportes)
$routes->post('/documentos-sst/adjuntar-soporte-mantenimiento-periodico', 'DocumentosSSTController::adjuntarSoporteMantenimientoPeriodico');

// 4.2.3 PVE Riesgo Biomecanico (Parte 1: Actividades)
$routes->get('/generador-ia/(:num)/pve-riesgo-biomecanico', 'GeneradorIAController::pveRiesgoBiomecanico/$1');
$routes->get('/generador-ia/(:num)/preview-actividades-pve-biomecanico', 'GeneradorIAController::previewActividadesPveBiomecanico/$1');
$routes->post('/generador-ia/(:num)/generar-actividades-pve-biomecanico', 'GeneradorIAController::generarActividadesPveBiomecanico/$1');
$routes->get('/generador-ia/(:num)/resumen-pve-biomecanico', 'GeneradorIAController::resumenPveBiomecanico/$1');
// 4.2.3 PVE Riesgo Biomecanico (Parte 2: Indicadores)
$routes->get('/generador-ia/(:num)/indicadores-pve-biomecanico', 'GeneradorIAController::indicadoresPveBiomecanico/$1');
$routes->get('/generador-ia/(:num)/preview-indicadores-pve-biomecanico', 'GeneradorIAController::previewIndicadoresPveBiomecanico/$1');
$routes->post('/generador-ia/(:num)/generar-indicadores-pve-biomecanico', 'GeneradorIAController::generarIndicadoresPveBiomecanico/$1');

// 4.2.3 PVE Riesgo Psicosocial (Parte 1: Actividades)
$routes->get('/generador-ia/(:num)/pve-riesgo-psicosocial', 'GeneradorIAController::pveRiesgoPsicosocial/$1');
$routes->get('/generador-ia/(:num)/preview-actividades-pve-psicosocial', 'GeneradorIAController::previewActividadesPvePsicosocial/$1');
$routes->post('/generador-ia/(:num)/generar-actividades-pve-psicosocial', 'GeneradorIAController::generarActividadesPvePsicosocial/$1');
$routes->get('/generador-ia/(:num)/resumen-pve-psicosocial', 'GeneradorIAController::resumenPvePsicosocial/$1');
// 4.2.3 PVE Riesgo Psicosocial (Parte 2: Indicadores)
$routes->get('/generador-ia/(:num)/indicadores-pve-psicosocial', 'GeneradorIAController::indicadoresPvePsicosocial/$1');
$routes->get('/generador-ia/(:num)/preview-indicadores-pve-psicosocial', 'GeneradorIAController::previewIndicadoresPvePsicosocial/$1');
$routes->post('/generador-ia/(:num)/generar-indicadores-pve-psicosocial', 'GeneradorIAController::generarIndicadoresPvePsicosocial/$1');

// Módulo 1.2.2 - Inducción y Reinducción
$routes->get('/induccion-etapas/(:num)', 'InduccionEtapasController::index/$1');
$routes->get('/induccion-etapas/(:num)/generar', 'InduccionEtapasController::generar/$1');
$routes->post('/induccion-etapas/(:num)/generar', 'InduccionEtapasController::generarPost/$1');
$routes->post('/induccion-etapas/(:num)/aprobar', 'InduccionEtapasController::aprobar/$1');
$routes->get('/induccion-etapas/(:num)/generar-pta', 'InduccionEtapasController::generarPTA/$1');
$routes->post('/induccion-etapas/(:num)/enviar-pta', 'InduccionEtapasController::enviarPTA/$1');
$routes->get('/induccion-etapas/(:num)/generar-indicadores', 'InduccionEtapasController::generarIndicadores/$1');
$routes->post('/induccion-etapas/(:num)/enviar-indicadores', 'InduccionEtapasController::enviarIndicadores/$1');
$routes->post('/induccion-etapas/(:num)/ajustar-indicador', 'InduccionEtapasController::ajustarIndicador/$1');
$routes->get('/induccion-etapas/(:num)/api', 'InduccionEtapasController::getEtapasJson/$1');
$routes->post('/induccion-etapas/etapa/(:num)/aprobar', 'InduccionEtapasController::aprobarEtapa/$1');
$routes->post('/induccion-etapas/etapa/(:num)/desaprobar', 'InduccionEtapasController::desaprobarEtapa/$1');
$routes->post('/induccion-etapas/etapa/(:num)/tema', 'InduccionEtapasController::agregarTema/$1');
$routes->delete('/induccion-etapas/etapa/(:num)/tema/(:num)', 'InduccionEtapasController::eliminarTema/$1/$2');
$routes->post('/induccion-etapas/etapa/(:num)/editar-temas', 'InduccionEtapasController::editarTemas/$1');

// Documentos SST generados
$routes->get('/documentos-sst/(:num)/programa-capacitacion/(:num)', 'DocumentosSSTController::programaCapacitacion/$1/$2');

// Procedimiento de Control Documental (2.5.1)
$routes->get('/documentos-sst/(:num)/procedimiento-control-documental/(:num)', 'DocumentosSSTController::procedimientoControlDocumental/$1/$2');
$routes->post('/documentos-sst/(:num)/crear-control-documental', 'DocumentosSSTController::crearControlDocumental/$1');

// Programa de Induccion y Reinduccion (1.2.2)
$routes->get('/documentos-sst/(:num)/programa-induccion-reinduccion/(:num)', 'DocumentosSSTController::programaInduccionReinduccion/$1/$2');

// Programa de Promocion y Prevencion en Salud (3.1.2)
$routes->get('/documentos-sst/(:num)/programa-promocion-prevencion-salud/(:num)', 'DocumentosSSTController::programaPromocionPrevencionSalud/$1/$2');

// Procedimiento de Matriz Legal (2.7.1)
$routes->get('/documentos-sst/(:num)/procedimiento-matriz-legal/(:num)', 'DocumentosSSTController::procedimientoMatrizLegal/$1/$2');

// 2.2.1 Plan de Objetivos y Metas del SG-SST
$routes->get('/documentos-sst/(:num)/plan-objetivos-metas/(:num)', 'DocumentosSSTController::planObjetivosMetas/$1/$2');

// 2.1.1 Políticas de SST
$routes->get('/documentos-sst/(:num)/politica-sst-general/(:num)', 'DocumentosSSTController::politicaSstGeneral/$1/$2');
$routes->get('/documentos-sst/(:num)/politica-prevencion-emergencias/(:num)', 'DocumentosSSTController::politicaPrevencionEmergencias/$1/$2');

// 1.1.8 Manual de Convivencia Laboral
$routes->get('/documentos-sst/(:num)/manual-convivencia-laboral/(:num)', 'DocumentosSSTController::manualConvivenciaLaboral/$1/$2');

// 2.8.1 Mecanismos de Comunicación, Auto Reporte en SG-SST
$routes->get('/documentos-sst/(:num)/mecanismos-comunicacion/(:num)', 'DocumentosSSTController::mecanismosComunicacion/$1/$2');

// 3.1.1 Procedimiento de Evaluaciones Médicas Ocupacionales
$routes->get('/documentos-sst/(:num)/procedimiento-evaluaciones-medicas/(:num)', 'DocumentosSSTController::procedimientoEvaluacionesMedicas/$1/$2');

// 2.9.1 Procedimiento de Adquisiciones en SST
$routes->get('/documentos-sst/(:num)/procedimiento-adquisiciones/(:num)', 'DocumentosSSTController::procedimientoAdquisiciones/$1/$2');

// 2.10.1 Evaluacion y Seleccion de Proveedores y Contratistas
$routes->get('/documentos-sst/(:num)/procedimiento-evaluacion-proveedores/(:num)', 'DocumentosSSTController::procedimientoEvaluacionProveedores/$1/$2');

// 2.11.1 Procedimiento de Gestion del Cambio
$routes->get('/documentos-sst/(:num)/procedimiento-gestion-cambio/(:num)', 'DocumentosSSTController::procedimientoGestionCambio/$1/$2');

// 3.1.7 Programa de Estilos de Vida Saludable
$routes->get('/documentos-sst/(:num)/programa-estilos-vida-saludable/(:num)', 'DocumentosSSTController::programaEstilosVidaSaludable/$1/$2');
$routes->post('/documentos-sst/adjuntar-soporte-estilos-vida', 'DocumentosSSTController::adjuntarSoporteEstilosVida');

// 3.1.4 Programa de Evaluaciones Medicas Ocupacionales
$routes->get('/documentos-sst/(:num)/programa-evaluaciones-medicas-ocupacionales/(:num)', 'DocumentosSSTController::programaEvaluacionesMedicasOcupacionales/$1/$2');

// 3.2.1 Procedimiento de Investigacion de Accidentes de Trabajo y Enfermedades Laborales
$routes->get('/documentos-sst/(:num)/procedimiento-investigacion-accidentes/(:num)', 'DocumentosSSTController::procedimientoInvestigacionAccidentes/$1/$2');
$routes->post('/documentos-sst/adjuntar-soporte-investigacion-accidentes', 'DocumentosSSTController::adjuntarSoporteInvestigacionAccidentes');

// 3.2.2 Investigacion de Incidentes, Accidentes y Enfermedades Laborales
$routes->get('/documentos-sst/(:num)/procedimiento-investigacion-incidentes/(:num)', 'DocumentosSSTController::procedimientoInvestigacionIncidentes/$1/$2');
$routes->post('/documentos-sst/adjuntar-soporte-investigacion-incidentes', 'DocumentosSSTController::adjuntarSoporteInvestigacionIncidentes');

// 4.1.1 Metodologia Identificacion de Peligros y Valoracion de Riesgos
$routes->get('/documentos-sst/(:num)/metodologia-identificacion-peligros/(:num)', 'DocumentosSSTController::metodologiaIdentificacionPeligros/$1/$2');
$routes->post('/documentos-sst/adjuntar-soporte-metodologia-peligros', 'DocumentosSSTController::adjuntarSoporteMetodologiaPeligros');

// 4.1.3 Identificacion de Sustancias Cancerigenas o con Toxicidad Aguda
$routes->get('/documentos-sst/(:num)/identificacion-sustancias-cancerigenas/(:num)', 'DocumentosSSTController::identificacionSustanciasCancerigenas/$1/$2');
$routes->post('/documentos-sst/adjuntar-soporte-sustancias-cancerigenas', 'DocumentosSSTController::adjuntarSoporteSustanciasCancerigenas');

// 4.2.3 PVE Riesgo Biomecanico
$routes->get('/documentos-sst/(:num)/pve-riesgo-biomecanico/(:num)', 'DocumentosSSTController::pveRiesgoBiomecanico/$1/$2');
$routes->post('/documentos-sst/adjuntar-soporte-pve-biomecanico', 'DocumentosSSTController::adjuntarSoportePveBiomecanico');
// 4.2.3 PVE Riesgo Psicosocial
$routes->get('/documentos-sst/(:num)/pve-riesgo-psicosocial/(:num)', 'DocumentosSSTController::pveRiesgoPsicosocial/$1/$2');
$routes->post('/documentos-sst/adjuntar-soporte-pve-psicosocial', 'DocumentosSSTController::adjuntarSoportePvePsicosocial');

// 4.2.5 Programa de Mantenimiento Periodico de Instalaciones, Equipos, Maquinas, Herramientas
$routes->get('/documentos-sst/(:num)/programa-mantenimiento-periodico/(:num)', 'DocumentosSSTController::programaMantenimientoPeriodico/$1/$2');

// Asignacion de Responsable SG-SST (Patron B - controlador independiente)
$routes->post('/documentos-sst/(:num)/crear-asignacion-responsable-sst', 'PzasignacionresponsableSstController::crear/$1');
$routes->get('/documentos-sst/(:num)/asignacion-responsable-sst/(:num)', 'PzasignacionresponsableSstController::ver/$1/$2');
$routes->post('/documentos-sst/(:num)/regenerar-asignacion-responsable-sst/(:num)', 'PzasignacionresponsableSstController::regenerar/$1/$2');

// 1.1.2 Responsabilidades en el SG-SST (4 documentos separados)
// Responsabilidades del Representante Legal (firma digital)
$routes->post('/documentos-sst/(:num)/crear-responsabilidades-rep-legal', 'PzresponsabilidadesRepLegalController::crear/$1');
$routes->get('/documentos-sst/(:num)/responsabilidades-rep-legal/(:num)', 'PzresponsabilidadesRepLegalController::ver/$1/$2');
$routes->post('/documentos-sst/(:num)/regenerar-responsabilidades-rep-legal/(:num)', 'PzresponsabilidadesRepLegalController::regenerar/$1/$2');

// Responsabilidades del Responsable SG-SST (firma consultor)
$routes->post('/documentos-sst/(:num)/crear-responsabilidades-responsable-sst', 'PzresponsabilidadesResponsableSstController::crear/$1');
$routes->get('/documentos-sst/(:num)/responsabilidades-responsable-sst/(:num)', 'PzresponsabilidadesResponsableSstController::ver/$1/$2');
$routes->post('/documentos-sst/(:num)/regenerar-responsabilidades-responsable-sst/(:num)', 'PzresponsabilidadesResponsableSstController::regenerar/$1/$2');

// Responsabilidades de Trabajadores y Contratistas (formato imprimible)
$routes->post('/documentos-sst/(:num)/crear-responsabilidades-trabajadores', 'PzresponsabilidadesTrabajadoresController::crear/$1');
$routes->get('/documentos-sst/(:num)/responsabilidades-trabajadores/(:num)', 'PzresponsabilidadesTrabajadoresController::ver/$1/$2');
$routes->post('/documentos-sst/(:num)/regenerar-responsabilidades-trabajadores/(:num)', 'PzresponsabilidadesTrabajadoresController::regenerar/$1/$2');

// Responsabilidades del Vigia SST (firma digital, SOLO para 7 estandares)
$routes->post('/documentos-sst/(:num)/crear-responsabilidades-vigia-sst', 'PzresponsabilidadesVigiaSstController::crear/$1');
$routes->get('/documentos-sst/(:num)/responsabilidades-vigia-sst/(:num)', 'PzresponsabilidadesVigiaSstController::ver/$1/$2');
$routes->post('/documentos-sst/(:num)/regenerar-responsabilidades-vigia-sst/(:num)', 'PzresponsabilidadesVigiaSstController::regenerar/$1/$2');

// Generador de documentos por secciones con IA
$routes->get('/documentos/generar/(:segment)/(:num)', 'DocumentosSSTController::generarConIA/$1/$2');
$routes->get('/documentos/previsualizar-datos/(:segment)/(:num)', 'DocumentosSSTController::previsualizarDatos/$1/$2');
$routes->post('/documentos/generar-seccion', 'DocumentosSSTController::generarSeccionIA');
$routes->post('/documentos/guardar-seccion', 'DocumentosSSTController::guardarSeccion');
$routes->post('/documentos/aprobar-seccion', 'DocumentosSSTController::aprobarSeccion');
$routes->get('/documentos/pdf/(:num)', 'DocumentosSSTController::generarPDF/$1');
$routes->get('/documentos-sst/exportar-pdf/(:num)', 'DocumentosSSTController::exportarPDF/$1');
$routes->get('/documentos-sst/exportar-word/(:num)', 'DocumentosSSTController::exportarWord/$1');
$routes->get('/documentos-sst/publicar-pdf/(:num)', 'DocumentosSSTController::publicarPDF/$1');
$routes->post('/documentos-sst/adjuntar-firmado', 'DocumentosSSTController::adjuntarFirmado');
$routes->post('/documentos-sst/adjuntar-planilla-srl', 'DocumentosSSTController::adjuntarPlanillaSRL');
$routes->post('/documentos-sst/adjuntar-soporte-verificacion', 'DocumentosSSTController::adjuntarSoporteVerificacion');
$routes->post('/documentos-sst/adjuntar-soporte-auditoria', 'DocumentosSSTController::adjuntarSoporteAuditoria');
$routes->post('/documentos-sst/adjuntar-soporte-epp', 'DocumentosSSTController::adjuntarSoporteEPP');
$routes->post('/documentos-sst/adjuntar-soporte-emergencias', 'DocumentosSSTController::adjuntarSoporteEmergencias');
$routes->post('/documentos-sst/adjuntar-soporte-brigada', 'DocumentosSSTController::adjuntarSoporteBrigada');
$routes->post('/documentos-sst/adjuntar-soporte-revision', 'DocumentosSSTController::adjuntarSoporteRevision');
$routes->post('/documentos-sst/adjuntar-soporte-agua', 'DocumentosSSTController::adjuntarSoporteAgua');
$routes->post('/documentos-sst/adjuntar-soporte-residuos', 'DocumentosSSTController::adjuntarSoporteResiduos');
$routes->post('/documentos-sst/adjuntar-soporte-mediciones', 'DocumentosSSTController::adjuntarSoporteMediciones');
$routes->post('/documentos-sst/adjuntar-soporte-medidas-control', 'DocumentosSSTController::adjuntarSoporteMedidasControl');
$routes->post('/documentos-sst/adjuntar-soporte-diagnostico-salud', 'DocumentosSSTController::adjuntarSoporteDiagnosticoSalud');
$routes->post('/documentos-sst/adjuntar-soporte-perfiles-medico', 'DocumentosSSTController::adjuntarSoportePerfilesMedico');
$routes->post('/documentos-sst/adjuntar-soporte-evaluaciones-medicas', 'DocumentosSSTController::adjuntarSoporteEvaluacionesMedicas');
$routes->post('/documentos-sst/adjuntar-soporte-custodia-hc', 'DocumentosSSTController::adjuntarSoporteCustodiaHC');
$routes->post('/documentos-sst/adjuntar-soporte-curso-50h', 'DocumentosSSTController::adjuntarSoporteCurso50h');
$routes->post('/documentos-sst/adjuntar-soporte-evaluacion-prioridades', 'DocumentosSSTController::adjuntarSoporteEvaluacionPrioridades');
$routes->post('/documentos-sst/adjuntar-soporte-plan-objetivos', 'DocumentosSSTController::adjuntarSoportePlanObjetivos');
$routes->post('/documentos-sst/adjuntar-soporte-rendicion', 'DocumentosSSTController::adjuntarSoporteRendicion');
$routes->post('/documentos-sst/adjuntar-soporte-copasst', 'DocumentosSSTController::adjuntarSoporteCopasst');
$routes->post('/documentos-sst/adjuntar-soporte-capacitacion-copasst', 'DocumentosSSTController::adjuntarSoporteCapacitacionCopasst');
$routes->post('/documentos-sst/adjuntar-soporte-convivencia', 'DocumentosSSTController::adjuntarSoporteConvivencia');
$routes->post('/documentos-sst/adjuntar-soporte-pyp-salud', 'DocumentosSSTController::adjuntarSoportePypSalud');
$routes->post('/documentos-sst/adjuntar-soporte-induccion', 'DocumentosSSTController::adjuntarSoporteInduccion');
$routes->post('/documentos-sst/adjuntar-soporte-matriz-legal', 'DocumentosSSTController::adjuntarSoporteMatrizLegal');
$routes->post('/documentos-sst/adjuntar-soporte-mecanismos-comunicacion', 'DocumentosSSTController::adjuntarSoporteMecanismosComunicacion');
$routes->post('/documentos-sst/adjuntar-soporte-evaluacion-proveedores', 'DocumentosSSTController::adjuntarSoporteEvaluacionProveedores');
$routes->post('/documentos-sst/adjuntar-soporte-gestion-cambio', 'DocumentosSSTController::adjuntarSoporteGestionCambio');

// Aprobacion y versionamiento de documentos SST
$routes->post('/documentos-sst/aprobar-documento', 'DocumentosSSTController::aprobarDocumento');
$routes->post('/documentos-sst/iniciar-nueva-version', 'DocumentosSSTController::iniciarNuevaVersion');
$routes->get('/documentos-sst/historial-versiones/(:num)', 'DocumentosSSTController::historialVersiones/$1');
$routes->post('/documentos-sst/restaurar-version', 'DocumentosSSTController::restaurarVersion');
$routes->post('/documentos-sst/cancelar-nueva-version', 'DocumentosSSTController::cancelarNuevaVersion');
$routes->get('/documentos-sst/descargar-version-pdf/(:num)', 'DocumentosSSTController::descargarVersionPDF/$1');

// Temporal: Ejecutar migraciones SQL (ELIMINAR DESPUES DE USAR)
$routes->get('/sql-runner/insertar-plantillas-responsabilidades', 'SqlRunnerController::insertarPlantillasResponsabilidades');
$routes->get('/sql-runner/columnas-firma-presupuesto', 'SqlRunnerController::columnasFirmaPresupuesto');
$routes->get('/sql-runner/diagnostico-audit/(:num)/(:num)', 'SqlRunnerController::diagnosticoAudit/$1/$2');
$routes->get('/sql-runner/reparar-audit/(:num)', 'SqlRunnerController::repararAudit/$1');
$routes->get('/sql-runner/diagnostico-firmas/(:num)', 'SqlRunnerController::diagnosticoFirmas/$1');
$routes->get('/sql-runner/forzar-firmado/(:num)', 'SqlRunnerController::forzarFirmado/$1');
$routes->get('/sql-runner/diagnostico-presupuesto/(:num)/(:num)', 'SqlRunnerController::diagnosticoPresupuesto/$1/$2');
$routes->get('/sql-runner/corregir-usuario-miembro', 'SqlRunnerController::corregirUsuarioMiembro');
$routes->get('/sql-runner/resetear-password-miembro', 'SqlRunnerController::resetearPasswordMiembro');
$routes->get('/sql-runner/diagnostico-usuarios-miembro', 'SqlRunnerController::diagnosticoUsuariosMiembro');
$routes->get('/sql-runner/buscar-usuario/(:segment)', 'SqlRunnerController::buscarUsuario/$1');
$routes->get('/sql-runner/buscar-usuario', 'SqlRunnerController::buscarUsuario');
$routes->get('/sql-runner/probar-login', 'SqlRunnerController::probarLogin');
$routes->get('/sql-runner/ver-triggers', 'SqlRunnerController::verTriggers');
$routes->get('/sql-runner/agregar-miembro-enum', 'SqlRunnerController::agregarMiembroEnum');
$routes->get('/sql-runner/estandarizar-versiones', 'SqlRunnerController::estandarizarVersiones');

/* *********************MÓDULO PRESUPUESTO SST (1.1.3) ****************************************/

// Vista principal del presupuesto
$routes->get('/documentos-sst/presupuesto/(:num)', 'PzpresupuestoSstController::index/$1');
$routes->get('/documentos-sst/presupuesto/(:num)/(:num)', 'PzpresupuestoSstController::index/$1/$2');

// Vista preview (formato vertical con botones de exportar)
$routes->get('/documentos-sst/presupuesto/preview/(:num)/(:num)', 'PzpresupuestoSstController::preview/$1/$2');
// Ruta alternativa: /documentos-sst/{idCliente}/presupuesto/preview/{anio}
$routes->get('/documentos-sst/(:num)/presupuesto/preview/(:num)', 'PzpresupuestoSstController::preview/$1/$2');

// Acciones AJAX
$routes->post('/documentos-sst/presupuesto/agregar-item', 'PzpresupuestoSstController::agregarItem');
$routes->post('/documentos-sst/presupuesto/actualizar-monto', 'PzpresupuestoSstController::actualizarMonto');
$routes->post('/documentos-sst/presupuesto/actualizar-item', 'PzpresupuestoSstController::actualizarItem');
$routes->post('/documentos-sst/presupuesto/eliminar-item', 'PzpresupuestoSstController::eliminarItem');
$routes->get('/documentos-sst/presupuesto/totales/(:num)', 'PzpresupuestoSstController::getTotales/$1');

// Cambiar estado
$routes->get('/documentos-sst/presupuesto/estado/(:num)/(:segment)', 'PzpresupuestoSstController::cambiarEstado/$1/$2');

// Exportación
$routes->get('/documentos-sst/presupuesto/pdf/(:num)/(:num)', 'PzpresupuestoSstController::exportarPdf/$1/$2');
$routes->get('/documentos-sst/presupuesto/word/(:num)/(:num)', 'PzpresupuestoSstController::exportarWord/$1/$2');
$routes->get('/documentos-sst/presupuesto/excel/(:num)/(:num)', 'PzpresupuestoSstController::exportarExcel/$1/$2');

// Copiar presupuesto de otro año
$routes->get('/documentos-sst/presupuesto/copiar/(:num)/(:num)/(:num)', 'PzpresupuestoSstController::copiarDeAnio/$1/$2/$3');

// Enviar a firmas (email con enlace)
$routes->post('/documentos-sst/presupuesto/enviar-firmas', 'PzpresupuestoSstController::enviarAprobacion');

// Pagina de firma publica
$routes->get('/presupuesto/aprobar/(:segment)', 'PzpresupuestoSstController::paginaFirma/$1');
$routes->post('/presupuesto/procesar-firma', 'PzpresupuestoSstController::procesarFirma');

// Vista de consulta para clientes (solo lectura)
$routes->get('/presupuesto/consulta/(:segment)', 'PzpresupuestoSstController::vistaCliente/$1');
$routes->post('/documentos-sst/presupuesto/generar-token-consulta', 'PzpresupuestoSstController::generarTokenConsulta');

// Crear nueva versión del presupuesto
$routes->post('/documentos-sst/presupuesto/nueva-version/(:num)/(:num)', 'PzpresupuestoSstController::crearNuevaVersion/$1/$2');

/* *****************************************************************************
 * MÓDULO DE CONFORMACIÓN DE COMITÉS - ELECCIONES SST
 * COPASST, COCOLAB, Brigada, Vigía
 * *****************************************************************************/

// Dashboard de comités electorales por cliente
$routes->get('/comites-elecciones/(:num)', 'ComitesEleccionesController::dashboard/$1');

// Crear nuevo proceso electoral
$routes->get('/comites-elecciones/(:num)/nuevo', 'ComitesEleccionesController::nuevoProceso/$1');
$routes->get('/comites-elecciones/(:num)/nuevo/(:segment)', 'ComitesEleccionesController::nuevoProceso/$1/$2');
$routes->post('/comites-elecciones/guardar-proceso', 'ComitesEleccionesController::guardarProceso');

// Ver proceso específico
$routes->get('/comites-elecciones/(:num)/proceso/(:num)', 'ComitesEleccionesController::verProceso/$1/$2');
$routes->post('/comites-elecciones/proceso/(:num)/cambiar-estado/(:segment)', 'ComitesEleccionesController::cambiarEstado/$1/$2');

// Fase 2: Inscripción de candidatos
$routes->get('/comites-elecciones/proceso/(:num)/inscribir/(:segment)', 'ComitesEleccionesController::inscribirCandidato/$1/$2');
$routes->get('/comites-elecciones/proceso/(:num)/inscribir', 'ComitesEleccionesController::inscribirCandidato/$1');
$routes->post('/comites-elecciones/guardar-candidato', 'ComitesEleccionesController::guardarCandidato');
$routes->get('/comites-elecciones/proceso/(:num)/candidatos', 'ComitesEleccionesController::listaCandidatos/$1');
$routes->get('/comites-elecciones/candidato/(:num)/editar', 'ComitesEleccionesController::editarCandidato/$1');
$routes->post('/comites-elecciones/candidato/(:num)/actualizar', 'ComitesEleccionesController::actualizarCandidato/$1');
$routes->post('/comites-elecciones/candidato/(:num)/eliminar', 'ComitesEleccionesController::eliminarCandidato/$1');
$routes->get('/comites-elecciones/candidato/(:num)', 'ComitesEleccionesController::verCandidato/$1');

// Fase 3: Sistema de Votacion Electronica
$routes->post('/comites-elecciones/proceso/(:num)/iniciar-votacion', 'ComitesEleccionesController::iniciarVotacion/$1');
$routes->get('/comites-elecciones/proceso/(:num)/censo', 'ComitesEleccionesController::censovotantes/$1');
$routes->post('/comites-elecciones/proceso/agregar-votante', 'ComitesEleccionesController::agregarVotante');
$routes->post('/comites-elecciones/proceso/importar-votantes', 'ComitesEleccionesController::importarVotantes');
$routes->get('/comites-elecciones/proceso/(:num)/plantilla-csv', 'ComitesEleccionesController::descargarPlantillaCSV/$1');
$routes->post('/comites-elecciones/proceso/importar-csv', 'ComitesEleccionesController::importarCSV');
$routes->post('/comites-elecciones/votante/(:num)/enviar-enlace', 'ComitesEleccionesController::enviarEnlaceVotante/$1');
$routes->post('/comites-elecciones/proceso/(:num)/enviar-enlaces-todos', 'ComitesEleccionesController::enviarEnlacesTodos/$1');
$routes->get('/comites-elecciones/proceso/(:num)/resultados', 'ComitesEleccionesController::resultadosVotacion/$1');
$routes->post('/comites-elecciones/proceso/(:num)/finalizar-votacion', 'ComitesEleccionesController::finalizarVotacion/$1');

// Fase 4: Completar Proceso
$routes->post('/comites-elecciones/proceso/(:num)/completar', 'ComitesEleccionesController::completarProceso/$1');

// Administración de procesos electorales (admin/consultant)
$routes->get('/comites-elecciones/admin/procesos', 'ComitesEleccionesController::administrarProcesos');
$routes->post('/comites-elecciones/admin/reabrir-proceso', 'ComitesEleccionesController::reabrirProceso');
$routes->post('/comites-elecciones/admin/cancelar-proceso', 'ComitesEleccionesController::cancelarProcesoElectoral');

// Jurados de votacion
$routes->post('/comites-elecciones/jurado/agregar', 'ComitesEleccionesController::agregarJurado');
$routes->get('/comites-elecciones/proceso/(:num)/jurados', 'ComitesEleccionesController::obtenerJurados/$1');
$routes->post('/comites-elecciones/jurado/(:num)/eliminar', 'ComitesEleccionesController::eliminarJurado/$1');
$routes->get('/comites-elecciones/proceso/(:num)/buscar-trabajador', 'ComitesEleccionesController::buscarTrabajadorJurado/$1');

// Acta de Constitucion del Comite
$routes->get('/comites-elecciones/proceso/(:num)/acta', 'ComitesEleccionesController::generarActaConstitucion/$1');
$routes->get('/comites-elecciones/proceso/(:num)/acta/pdf', 'ComitesEleccionesController::generarActaConstitucionPDF/$1');
$routes->get('/comites-elecciones/proceso/(:num)/acta/descargar', 'ComitesEleccionesController::descargarActaConstitucion/$1');
$routes->get('/comites-elecciones/proceso/(:num)/acta/word', 'ComitesEleccionesController::exportarActaWord/$1');

// Firmas electronicas del Acta de Constitucion
$routes->get('/comites-elecciones/proceso/(:num)/firmas', 'ComitesEleccionesController::solicitarFirmasActa/$1');
$routes->post('/comites-elecciones/proceso/crear-solicitudes-acta', 'ComitesEleccionesController::crearSolicitudesActa');
$routes->get('/comites-elecciones/proceso/(:num)/firmas/estado', 'ComitesEleccionesController::estadoFirmasActa/$1');

// Recomposicion de Comites
$routes->get('/comites-elecciones/proceso/(:num)/recomposiciones', 'ComitesEleccionesController::listarRecomposiciones/$1');
$routes->get('/comites-elecciones/proceso/(:num)/recomposicion/nueva', 'ComitesEleccionesController::nuevaRecomposicion/$1');
$routes->post('/comites-elecciones/proceso/guardar-recomposicion', 'ComitesEleccionesController::guardarRecomposicion');
$routes->get('/comites-elecciones/proceso/(:num)/recomposicion/(:num)', 'ComitesEleccionesController::verRecomposicion/$1/$2');
$routes->get('/comites-elecciones/proceso/(:num)/recomposicion/(:num)/acta-pdf', 'ComitesEleccionesController::generarActaRecomposicionPdf/$1/$2');
$routes->get('/comites-elecciones/proceso/(:num)/siguiente-votacion', 'ComitesEleccionesController::getSiguienteEnVotacion/$1');

// Firmas de Recomposicion
$routes->get('/comites-elecciones/proceso/(:num)/recomposicion/(:num)/firmas', 'ComitesEleccionesController::solicitarFirmasRecomposicion/$1/$2');
$routes->post('/comites-elecciones/proceso/crear-solicitudes-recomposicion', 'ComitesEleccionesController::crearSolicitudesFirmaRecomposicion');
$routes->get('/comites-elecciones/proceso/(:num)/recomposicion/(:num)/firmas/estado', 'ComitesEleccionesController::estadoFirmasRecomposicion/$1/$2');
$routes->get('/comites-elecciones/firma/reenviar/(:num)', 'ComitesEleccionesController::reenviarFirmaRecomposicion/$1');

// Rutas PUBLICAS de votacion (sin autenticacion)
$routes->get('/votar/(:alphanum)', 'ComitesEleccionesController::votarAcceso/$1');
$routes->post('/votar/validar', 'ComitesEleccionesController::validarVotante');
$routes->get('/votar/emitir/(:alphanum)', 'ComitesEleccionesController::votarPublico/$1');
$routes->post('/votar/registrar', 'ComitesEleccionesController::registrarVoto');

/* *****************************************************************************
 * MÓDULO DE ACTAS - COPASST, COCOLAB, BRIGADA, GENERALES
 * *****************************************************************************/

// Dashboard de comités por cliente (consultor)
$routes->get('/actas/(:num)', 'ActasController::index/$1');

// Comités
$routes->get('/actas/(:num)/nuevo-comite', 'ActasController::nuevoComite/$1');
$routes->post('/actas/(:num)/guardar-comite', 'ActasController::guardarComite/$1');
$routes->get('/actas/(:num)/comite/(:num)', 'ActasController::verComite/$2');

// Miembros del comité
$routes->get('/actas/comite/(:num)/nuevo-miembro', 'ActasController::nuevoMiembro/$1');
$routes->post('/actas/comite/(:num)/guardar-miembro', 'ActasController::guardarMiembro/$1');
$routes->get('/actas/comite/(:num)/editar-miembro/(:num)', 'ActasController::editarMiembro/$1/$2');
$routes->post('/actas/comite/(:num)/actualizar-miembro/(:num)', 'ActasController::actualizarMiembro/$1/$2');
$routes->post('/actas/miembro/(:num)/retirar', 'ActasController::retirarMiembro/$1');
$routes->post('/actas/miembro/(:num)/reenviar-acceso', 'ActasController::reenviarAccesoMiembro/$1');

// Actas - Consultor
$routes->get('/actas/comite/(:num)/preparar-reunion', 'ActasController::prepararReunion/$1');
$routes->get('/actas/comite/(:num)/nueva-acta', 'ActasController::nuevaActa/$1');
$routes->post('/actas/comite/(:num)/guardar-acta', 'ActasController::guardarActa/$1');
$routes->get('/actas/editar/(:num)', 'ActasController::editarActa/$1');
$routes->post('/actas/editar/(:num)', 'ActasController::actualizarActa/$1');
$routes->get('/actas/comite/(:num)/acta/(:num)', 'ActasController::verActa/$2');
$routes->post('/actas/comite/(:num)/acta/(:num)/actualizar', 'ActasController::actualizarActa/$2');
$routes->post('/actas/comite/(:num)/acta/(:num)/enviar-firmas', 'ActasController::enviarAFirmas/$2');
$routes->post('/actas/comite/(:num)/acta/(:num)/cerrar', 'ActasController::cerrarActa/$2');
$routes->get('/actas/comite/(:num)/acta/(:num)/firmas', 'ActasController::estadoFirmas/$2');
$routes->post('/actas/comite/(:num)/acta/(:num)/reenviar-todos', 'ActasController::reenviarTodos/$2');
$routes->post('/actas/comite/(:num)/acta/(:num)/reenviar/(:num)', 'ActasController::reenviarAsistente/$2/$3');
$routes->get('/actas/ver/(:num)', 'ActasController::verActa/$1');
$routes->post('/actas/cerrar/(:num)', 'ActasController::cerrarActa/$1');
$routes->get('/actas/firmas/(:num)', 'ActasController::estadoFirmas/$1');
$routes->post('/actas/reenviar-firma/(:num)', 'ActasController::reenviarNotificacionFirma/$1');
$routes->get('/actas/pdf/(:num)', 'ActasController::exportarPDF/$1');
$routes->get('/actas/comite/(:num)/acta/(:num)/pdf', 'ActasController::exportarPDF/$2');
$routes->get('/actas/comite/(:num)/acta/(:num)/word', 'ActasController::exportarWord/$2');
$routes->get('/actas/firma-imagen/(:num)', 'ActasController::firmaImagen/$1');

// Compromisos - Consultor
$routes->get('/actas/(:num)/compromisos', 'ActasController::compromisos/$1');
$routes->get('/actas/(:num)/comite/(:num)/compromisos', 'ActasController::compromisosComite/$1/$2');
$routes->post('/actas/compromiso/(:num)/actualizar', 'ActasController::actualizarCompromiso/$1');
$routes->post('/actas/compromiso/(:num)/completar', 'ActasController::completarCompromiso/$1');

// Firmas públicas (sin login, acceso por token)
$routes->get('/acta/firmar/(:segment)', 'ActaFirmaPublicaController::firmar/$1');
$routes->post('/acta/firmar/(:segment)', 'ActaFirmaPublicaController::procesarFirma/$1');
$routes->get('/acta/firma-exitosa/(:segment)', 'ActaFirmaPublicaController::firmaExitosa/$1');

// Ver acta por token (público)
$routes->get('/acta/ver/(:segment)', 'ActaFirmaPublicaController::verActa/$1');

// Actualizar tarea por token (público)
$routes->get('/acta/tarea/(:segment)', 'ActaFirmaPublicaController::actualizarTarea/$1');
$routes->post('/acta/tarea/(:segment)', 'ActaFirmaPublicaController::procesarActualizacionTarea/$1');

// Verificar código de acta (público)
$routes->get('/acta/verificar', 'ActaFirmaPublicaController::verificarActa');
$routes->post('/acta/verificar', 'ActaFirmaPublicaController::verificarActa');

// ============================================
// MÓDULO DE ACCIONES CORRECTIVAS
// Numerales 7.1.1, 7.1.2, 7.1.3, 7.1.4 - Resolución 0312
// ============================================

// Dashboard general (seleccionar cliente)
$routes->get('/acciones-correctivas', 'AccionesCorrectivasController::dashboard');

// Dashboard de un cliente
$routes->get('/acciones-correctivas/(:num)', 'AccionesCorrectivasController::index/$1');

// Vista por numeral (para embeber en carpetas)
$routes->get('/acciones-correctivas/(:num)/numeral/(:segment)', 'AccionesCorrectivasController::porNumeral/$1/$2');

// Hallazgos
$routes->get('/acciones-correctivas/(:num)/hallazgos', 'AccionesCorrectivasController::hallazgos/$1');
$routes->get('/acciones-correctivas/(:num)/hallazgo/crear', 'AccionesCorrectivasController::crearHallazgo/$1');
$routes->get('/acciones-correctivas/(:num)/hallazgo/crear/(:segment)', 'AccionesCorrectivasController::crearHallazgo/$1/$2');
$routes->post('/acciones-correctivas/(:num)/hallazgo/guardar', 'AccionesCorrectivasController::guardarHallazgo/$1');
$routes->get('/acciones-correctivas/(:num)/hallazgo/(:num)', 'AccionesCorrectivasController::verHallazgo/$1/$2');

// Acciones
$routes->get('/acciones-correctivas/(:num)/hallazgo/(:num)/accion/crear', 'AccionesCorrectivasController::crearAccion/$1/$2');
$routes->post('/acciones-correctivas/(:num)/hallazgo/(:num)/accion/guardar', 'AccionesCorrectivasController::guardarAccion/$1/$2');
$routes->get('/acciones-correctivas/(:num)/accion/(:num)', 'AccionesCorrectivasController::verAccion/$1/$2');
$routes->post('/acciones-correctivas/(:num)/accion/(:num)/cambiar-estado', 'AccionesCorrectivasController::cambiarEstadoAccion/$1/$2');

// Seguimientos y Evidencias
$routes->post('/acciones-correctivas/(:num)/accion/(:num)/avance', 'AccionesCorrectivasController::registrarAvance/$1/$2');
$routes->post('/acciones-correctivas/(:num)/accion/(:num)/evidencia', 'AccionesCorrectivasController::subirEvidencia/$1/$2');
$routes->post('/acciones-correctivas/(:num)/accion/(:num)/comentario', 'AccionesCorrectivasController::registrarComentario/$1/$2');
$routes->get('/acciones-correctivas/evidencia/(:num)/descargar', 'AccionesCorrectivasController::descargarEvidencia/$1');
$routes->get('/acciones-correctivas/(:num)/hallazgo/(:num)/evidencia', 'AccionesCorrectivasController::descargarEvidenciaHallazgo/$1/$2');

// Verificación de Efectividad
$routes->post('/acciones-correctivas/(:num)/accion/(:num)/verificacion', 'AccionesCorrectivasController::registrarVerificacion/$1/$2');

// Análisis de Causa Raíz con IA
$routes->get('/acciones-correctivas/(:num)/accion/(:num)/analisis-causa-raiz', 'AccionesCorrectivasController::analisisCausaRaiz/$1/$2');
$routes->post('/acciones-correctivas/(:num)/accion/(:num)/analisis-ia', 'AccionesCorrectivasController::procesarAnalisisIA/$1/$2');
$routes->post('/acciones-correctivas/(:num)/accion/(:num)/causa-raiz', 'AccionesCorrectivasController::guardarCausaRaiz/$1/$2');

// Reportes
$routes->get('/acciones-correctivas/(:num)/reporte/pdf', 'AccionesCorrectivasController::reportePDF/$1');
$routes->get('/acciones-correctivas/(:num)/reporte/excel', 'AccionesCorrectivasController::exportarExcel/$1');

// API Endpoints (AJAX)
$routes->get('/acciones-correctivas/(:num)/api/estadisticas', 'AccionesCorrectivasController::apiEstadisticas/$1');
$routes->get('/acciones-correctivas/(:num)/api/hallazgos', 'AccionesCorrectivasController::apiHallazgos/$1');
$routes->get('/acciones-correctivas/(:num)/api/acciones', 'AccionesCorrectivasController::apiAcciones/$1');

// ============================================
// MATRIZ LEGAL SST
// ============================================
$routes->get('/matriz-legal', 'MatrizLegalController::index');
$routes->get('/matriz-legal/datatable', 'MatrizLegalController::datatable');
$routes->get('/matriz-legal/ver/(:num)', 'MatrizLegalController::ver/$1');
$routes->get('/matriz-legal/crear', 'MatrizLegalController::crear');
$routes->post('/matriz-legal/guardar', 'MatrizLegalController::guardar');
$routes->get('/matriz-legal/editar/(:num)', 'MatrizLegalController::editar/$1');
$routes->post('/matriz-legal/eliminar/(:num)', 'MatrizLegalController::eliminar/$1');
$routes->get('/matriz-legal/importar', 'MatrizLegalController::importarCSV');
$routes->post('/matriz-legal/preview-csv', 'MatrizLegalController::previewCSV');
$routes->post('/matriz-legal/procesar-csv', 'MatrizLegalController::procesarCSV');
$routes->get('/matriz-legal/buscar-ia', 'MatrizLegalController::buscarIA');
$routes->post('/matriz-legal/procesar-busqueda-ia', 'MatrizLegalController::procesarBusquedaIA');
$routes->post('/matriz-legal/guardar-desde-ia', 'MatrizLegalController::guardarDesdeIA');
$routes->get('/matriz-legal/exportar', 'MatrizLegalController::exportar');
$routes->get('/matriz-legal/descargar-muestra', 'MatrizLegalController::descargarMuestra');

// ============================================
// ACCESO DE MIEMBROS AUTENTICADOS (con login)
// ============================================
$routes->group('miembro', ['filter' => 'miembro'], function($routes) {
    $routes->get('dashboard', 'MiembroAuthController::dashboard');
    $routes->get('comite/(:num)', 'MiembroAuthController::verComite/$1');
    $routes->get('comite/(:num)/nueva-acta', 'MiembroAuthController::nuevaActa/$1');
    $routes->post('comite/(:num)/guardar-acta', 'MiembroAuthController::guardarActa/$1');
    $routes->get('acta/(:num)', 'MiembroAuthController::verActa/$1');
    $routes->get('acta/(:num)/pdf', 'MiembroAuthController::descargarPDF/$1');
    $routes->post('acta/(:num)/cerrar', 'MiembroAuthController::cerrarActa/$1');
    $routes->get('compromisos', 'MiembroAuthController::misCompromisos');
});

// Acceso de miembros del comité (por token - legacy/alternativo)
$routes->get('/miembro-token/(:segment)', 'MiembroComiteController::index/$1');
$routes->get('/miembro-token/(:segment)/comite/(:num)', 'MiembroComiteController::verComite/$1/$2');
$routes->get('/miembro-token/(:segment)/acta/(:num)', 'MiembroComiteController::verActa/$1/$2');
$routes->get('/miembro-token/(:segment)/comite/(:num)/nueva-acta', 'MiembroComiteController::nuevaActa/$1/$2');
$routes->post('/miembro-token/(:segment)/comite/(:num)/guardar-acta', 'MiembroComiteController::guardarActa/$1/$2');
$routes->get('/miembro-token/(:segment)/acta/(:num)/editar', 'MiembroComiteController::editarActa/$1/$2');
$routes->post('/miembro-token/(:segment)/acta/(:num)/cerrar', 'MiembroComiteController::cerrarActa/$1/$2');
$routes->get('/miembro-token/(:segment)/compromisos', 'MiembroComiteController::misCompromisos/$1');