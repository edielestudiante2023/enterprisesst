-- ===========================================================
-- SQL para ajustar producción - Detalle de Estándar
-- Fecha: 2026-01-23
-- Descripción: Agrega campo criterio (pregunta de verificación)
--              a la tabla de estándares mínimos
-- ===========================================================

-- 1. Agregar campo criterio a tbl_estandares_minimos
ALTER TABLE tbl_estandares_minimos
ADD COLUMN criterio TEXT NULL AFTER nombre;

-- 2. Actualizar los criterios (preguntas de verificación) según Res. 0312/2019
-- Ciclo PLANEAR - RECURSOS

UPDATE tbl_estandares_minimos SET criterio = '¿Cuenta con un responsable para la dirección del Sistema de Gestión de la Seguridad y salud en el trabajo y este cumple con el perfil definido por los estándares mínimos según el tamaño y con la aprobación del curso de capacitación virtual de 50 horas?' WHERE item = '1.1.1';

UPDATE tbl_estandares_minimos SET criterio = '¿Se evidencia la asignación y comunicación de Responsabilidades específicas en Seguridad y Salud en el Trabajo SST a todos los niveles de la organización, incluida la alta dirección?' WHERE item = '1.1.2';

UPDATE tbl_estandares_minimos SET criterio = '¿Son definidos y asignados los recursos necesarios (financieros, humanos, técnicos, tecnológicos y de otra índole), para la gestión del SG-SST?' WHERE item = '1.1.3';

UPDATE tbl_estandares_minimos SET criterio = 'Todos los trabajadores independientemente de su forma de vinculación o contratación están afiliados al Sistema General de Riesgos Laborales con aportes conforme a la normatividad y en la respectiva clase de riesgo' WHERE item = '1.1.4';

UPDATE tbl_estandares_minimos SET criterio = '¿Se identifican y relacionan en el SG-SST los trabajadores dedicados de forma permanente a las actividades de alto riesgo según el decreto 2090 de 2003, se les está cotizando el monto establecido en el Sistema de Pensiones y la empresa ha realizado la identificación de peligros, evaluación y valoración de riesgos y la definición del cargo según estándares mínimos?' WHERE item = '1.1.5';

UPDATE tbl_estandares_minimos SET criterio = '¿La empresa de acuerdo con el número de trabajadores cuenta con Comité paritario / vigía de seguridad y salud en el trabajo vigente y está documentada su conformación con acta, convocatoria y elección y existe actas de reunión mensuales?' WHERE item = '1.1.6';

UPDATE tbl_estandares_minimos SET criterio = '¿El Comité paritario de seguridad y salud en el trabajo / Vigía está(n) capacitado(s) en seguridad y salud en el trabajo para el cumplimiento de sus responsabilidades según la ley?' WHERE item = '1.1.7';

UPDATE tbl_estandares_minimos SET criterio = '¿La empresa cuenta con un comité de convivencia laboral vigente que está constituido a través de un documento de conformación y evidencia el cumplimiento de sus funciones de acuerdo con la legislación vigente por medio de actas de reunión mínimo trimestrales e informes de gestión?' WHERE item = '1.1.8';

-- Ciclo PLANEAR - CAPACITACIÓN

UPDATE tbl_estandares_minimos SET criterio = '¿Existe un programa de capacitación anual en promoción y prevención revisado con el COPASST que define los requisitos de conocimiento y práctica en SST, incluye la identificación de peligros y control de los riesgos prioritarios, es extensivo a todos los niveles de la organización?' WHERE item = '1.2.1';

UPDATE tbl_estandares_minimos SET criterio = '¿Se evidencia el cumplimiento del programa anual de capacitación y de los procesos de inducción y reinducción en seguridad y salud en el trabajo previa al inicio de sus labores que cubre a todos los trabajadores independientemente de su forma de vinculación y/o contratación e incluye la descripción de las actividades a realizar, información de la identificación de riesgo, evaluación y valoración de riesgos y establecimiento de controles para prevención de los ATEL?' WHERE item = '1.2.2';

UPDATE tbl_estandares_minimos SET criterio = '¿Los responsables del SG-SST cuentan con el certificado de aprobación del curso de capacitación virtual de 50 horas definido por el Ministerio del Trabajo?' WHERE item = '1.2.3';

-- Ciclo PLANEAR - GESTIÓN INTEGRAL

UPDATE tbl_estandares_minimos SET criterio = '¿Se tiene elaborada por escrito de acuerdo con la normatividad por lo cual incluye los objetivos de la política de SST, expresa el compromiso de la alta dirección, el alcance sobre todos los centros de trabajo y todos los trabajadores, está publicada con fecha y firma del representante legal, es revisada anualmente, hace parte de las políticas de gestión de la empresa y ha sido comunicada al COPASST y divulgada, se conoce y es accesible por todos los niveles de la organización?' WHERE item = '2.1.1';

UPDATE tbl_estandares_minimos SET criterio = '¿Están definidos los objetivos del sistema de gestión de seguridad y salud en el trabajo y se expresan de conformidad con la política de SST, son claros, medibles, cuantificables, y tienen metas, son coherentes con el plan de trabajo anual y la normatividad vigente, están documentados y firmados por el empleador, están alineados con las prioridades definidas en SST, son adecuados a la empresa, son revisados, evaluados y actualizados si es necesario mínimo anualmente teniendo en cuenta las nuevas prioridades y resultados de la auditoría de cumplimiento y la revisión por la alta dirección anuales y son comunicados a los trabajadores?' WHERE item = '2.2.1';

UPDATE tbl_estandares_minimos SET criterio = '¿La empresa realizó la evaluación inicial del SG-SST (identificando las prioridades en SST de acuerdo con el procedimiento existente para su realización), y/o la autoevaluación de estándares mínimos a través de su encargado del SG-SST o personal externo con la formación establecida y sus resultados son aplicados para establecer o actualizar el plan de trabajo anual del SG-SST?' WHERE item = '2.3.1';

UPDATE tbl_estandares_minimos SET criterio = '¿La empresa diseña y define un plan de trabajo anual con seguimiento y planes de mejora para su cumplimiento que cuenta con un cronograma que identifica las actividades a implementar, con los objetivos del SG-SST, metas, recursos y responsables y se encuentra firmado por el empleador y el encargado de SST?' WHERE item = '2.4.1';

UPDATE tbl_estandares_minimos SET criterio = '¿El Sistema de Gestión de la Seguridad y Salud en el Trabajo está documentado y es fácilmente identificable y accesible, cuenta con un sistema de archivo o retención documental y cumple con la documentación mínima y registros según la normatividad vigente?' WHERE item = '2.5.1';

UPDATE tbl_estandares_minimos SET criterio = '¿Se evaluaron los resultados de la rendición de cuentas de las personas de todos los niveles de la organización con responsabilidades en el SG-SST en relación con su desempeño?' WHERE item = '2.6.1';

UPDATE tbl_estandares_minimos SET criterio = '¿Se cuenta con una matriz legal actualizada, que identifica la normatividad vigente del Sistema General de riesgos laborales aplicables y que debe cumplir la organización incluyendo estándares mínimos?' WHERE item = '2.7.1';

UPDATE tbl_estandares_minimos SET criterio = '¿Se cuenta con mecanismos para recibir y responder a la las comunicaciones internas de participación de los trabajadores y/o contratistas en la implementación del SG-SST a través de autorreportes, construcción de normas de seguridad entre otros y para dar respuesta a las comunicaciones externas relativas a la seguridad y salud en el trabajo?' WHERE item = '2.8.1';

UPDATE tbl_estandares_minimos SET criterio = '¿Existe un procedimiento de adquisiciones que identifique y evalúe las especificaciones en seguridad y salud en el trabajo de las compras de productos y servicios incluida la matriz de EPP?' WHERE item = '2.9.1';

UPDATE tbl_estandares_minimos SET criterio = '¿Están considerados los aspectos de SST y el cumplimiento de estándares mínimos en el procedimiento de evaluación y selección de proveedores y contratistas?' WHERE item = '2.10.1';

UPDATE tbl_estandares_minimos SET criterio = '¿Se cuenta con un procedimiento de gestión del cambio que permita para evaluar el impacto sobre la seguridad y salud en el trabajo que puedan generar los cambios internos y externos a la empresa y que informe y capacite a los trabajadores en los mismos?' WHERE item = '2.11.1';

-- Ciclo HACER - GESTIÓN DE LA SALUD

UPDATE tbl_estandares_minimos SET criterio = '¿Se cuenta con información actualizada con la descripción sociodemográfica de los trabajadores, la caracterización de las condiciones de salud, la evaluación y análisis de las estadísticas de salud tanto de origen laboral como común y los resultados de las evaluaciones médicas ocupacionales del último año?' WHERE item = '3.1.1';

UPDATE tbl_estandares_minimos SET criterio = '¿Existe un plan de acción con actividades de medicina preventiva y del trabajo de conformidad con las prioridades y los hallazgos de la morbilidad del diagnóstico de las condiciones de salud de los trabajadores y los peligros y riesgos de intervención prioritarios?' WHERE item = '3.1.2';

UPDATE tbl_estandares_minimos SET criterio = '¿Se remite información al médico que realiza las evaluaciones ocupacionales con los perfiles del cargo, con la descripción de las tareas y el medio en el cual se desarrollará la labor respectiva?' WHERE item = '3.1.3';

UPDATE tbl_estandares_minimos SET criterio = '¿Se realizan las evaluaciones médicas ocupacionales de acuerdo con la normatividad y los peligros a los que se encuentre expuesto el trabajador y están definidas su frecuencia acordes con la magnitud de los riesgos, el estado de salud del trabajador y las recomendaciones de los Programas de Vigilancia Epidemiológica? Se comunican los resultados por escrito a los trabajadores y estos se constarán en su historia médica' WHERE item = '3.1.4';

UPDATE tbl_estandares_minimos SET criterio = '¿La empresa tiene custodia de las historias clínicas ya sea a cargo de una institución prestadora de servicios de Seguridad y Salud en el Trabajo o del médico que practica los exámenes ocupacionales en la empresa?' WHERE item = '3.1.5';

UPDATE tbl_estandares_minimos SET criterio = '¿La empresa acata y hace seguimiento a las recomendaciones y restricciones médico laborales por parte de la EPS o ARL de los trabajadores para la realización de sus funciones, de ser necesario adecúa su puesto de trabajo, los reubica o realiza readaptación laboral? la empresa conserva documentos de soporte de recibido por parte de quienes califican' WHERE item = '3.1.6';

UPDATE tbl_estandares_minimos SET criterio = '¿Hay un programa para promover estilos de vida y entorno saludable incluyendo campañas específicas tendientes a la prevención y el control de la fármaco dependencia, el alcoholismo y el tabaquismo entre otros?' WHERE item = '3.1.7';

UPDATE tbl_estandares_minimos SET criterio = '¿En la sede hay suministro permanente de agua potable, servicios sanitarios y mecanismos para disponer de excretas y basuras?' WHERE item = '3.1.8';

UPDATE tbl_estandares_minimos SET criterio = '¿La empresa elimina los residuos sólidos, líquidos o gaseosos que se producen así como los residuos peligrosos de forma que no se ponga en riesgo a los trabajadores?' WHERE item = '3.1.9';

-- Ciclo HACER - REGISTRO Y REPORTE

UPDATE tbl_estandares_minimos SET criterio = '¿Existe un procedimiento para realizar reporte dentro de los 2 días hábiles siguientes y la investigación de los accidentes de trabajo y enfermedades laborales y se evidencia su documentación y cumplimiento bajo la Resolución 1401 de 2007 y se reporta a la dirección territorial el accidente grave y mortal así como las enfermedades laborales calificadas?' WHERE item = '3.2.1';

UPDATE tbl_estandares_minimos SET criterio = '¿Se investigan todos los accidentes e incidentes de trabajo y las enfermedades laborales cuando son diagnosticadas como laborales determinando las causas básicas e inmediatas y la posibilidad que se presenten nuevos casos y se realiza seguimiento a las acciones y recomendaciones realizadas para otros trabajadores potencialmente expuestos?' WHERE item = '3.2.2';

UPDATE tbl_estandares_minimos SET criterio = '¿Se tiene un registro estadístico de los incidentes y de los accidentes de trabajo, así como de las enfermedades laborales que ocurren, se realiza un análisis de este informe y de las causas y sus resultados y las conclusiones derivadas se presentan a la alta dirección y son usadas para el mejoramiento del SG-SST?' WHERE item = '3.2.3';

-- Ciclo HACER - VIGILANCIA

UPDATE tbl_estandares_minimos SET criterio = '¿Los objetivos incluyen el control de la accidentalidad y enfermedad laborales en términos de severidad y la empresa la mide como mínimo una vez al año y realizó la clasificación del origen del peligro / riesgo que la generó?' WHERE item = '3.3.1';

UPDATE tbl_estandares_minimos SET criterio = '¿Los objetivos incluyen el control de la accidentalidad y enfermedad laborales en términos de frecuencia y la empresa la mide como mínimo una vez al año y realizó la clasificación del origen del peligro / riesgo que la generó?' WHERE item = '3.3.2';

UPDATE tbl_estandares_minimos SET criterio = '¿La empresa realizó la clasificación del origen del peligro / riesgo que generó mortalidad por accidentes de trabajo y enfermedades laborales y mide el indicador respectivo como mínimo una vez al año?' WHERE item = '3.3.3';

UPDATE tbl_estandares_minimos SET criterio = '¿La empresa mide la prevalencia de enfermedades laborales como mínimo una vez al año y realizó la clasificación del origen del peligro / riesgo que la generó?' WHERE item = '3.3.4';

UPDATE tbl_estandares_minimos SET criterio = '¿La empresa mide la incidencia de enfermedades laborales como mínimo una vez al año y realizó la clasificación del origen del peligro / riesgo que la generó?' WHERE item = '3.3.5';

UPDATE tbl_estandares_minimos SET criterio = '¿La empresa mide el ausentismo por enfermedad laboral y común y por accidentes de trabajo como mínimo una vez al año y realizó la clasificación del origen del peligro / riesgo que lo generó?' WHERE item = '3.3.6';

-- Ciclo HACER - GESTIÓN DE PELIGROS Y RIESGOS

UPDATE tbl_estandares_minimos SET criterio = '¿Existe una metodología aplicada para la identificación peligros, evaluación y valoración de riesgos y establecimiento de controles con alcance a todos los procesos, actividades rutinarias y no rutinarias, máquinas y equipos y a todos los trabajadores independientemente de su forma de vinculación o contratación y están identificados aquellos que son prioritarios?' WHERE item = '4.1.1';

UPDATE tbl_estandares_minimos SET criterio = '¿La identificación de los peligros, evaluación y valoración de los riesgos y establecimiento de controles contó con la participación de los trabajadores, incluyó todos los centros de trabajo, procesos, actividades rutinarias y no rutinarias, el número total de expuestos y es actualizada como mínimo una vez al año, con los cambios en la organización y sus procesos y/o ante la ocurrencia de accidentes de trabajo mortales y eventos catastróficos?' WHERE item = '4.1.2';

UPDATE tbl_estandares_minimos SET criterio = '¿La empresa procesa, manipula o trabaja con agentes o sustancias catalogadas como carcinogénicas o con toxicidad aguda, causantes de enfermedades incluidas en la tabla de enfermedades laborales y prioriza los riesgos asociados a estas y realiza acciones de prevención e intervención al respecto?' WHERE item = '4.1.3';

UPDATE tbl_estandares_minimos SET criterio = '¿Se realizan mediciones ambientales de los riesgos prioritarios provenientes de peligros químicos, físicos y/o biológicos y sus resultados está documentados y son remitidos al COPASST o vigía?' WHERE item = '4.1.4';

-- Ciclo HACER - MEDIDAS DE PREVENCIÓN Y CONTROL

UPDATE tbl_estandares_minimos SET criterio = '¿Se han implementado medidas de control acorde al resultado identificación de los peligros, evaluación y valoración de los riesgos (matriz de identificación de peligros, evaluación y control de riesgos), donde se priorizan las intervenciones a los riesgos más críticos? Se ejecutan acorde al esquema de jerarquización' WHERE item = '4.2.1';

UPDATE tbl_estandares_minimos SET criterio = '¿Se verifica la aplicación por parte de los trabajadores de las medidas de prevención y control y se cuenta con un proceso de reportes de los trabajadores que permita evaluar la efectividad de las medidas de control?' WHERE item = '4.2.2';

UPDATE tbl_estandares_minimos SET criterio = '¿Se cuenta con programas de promoción y prevención / programa de prevención y protección de la seguridad y salud de las personas para los peligros identificados y orientados a los factores de riesgo prioritarios incluido el plan estratégico de seguridad vial si este aplica?' WHERE item = '4.2.3';

UPDATE tbl_estandares_minimos SET criterio = '¿Se realizan inspecciones sistemáticas a las instalaciones, maquinaria o equipos, incluidos los relacionados con la prevención y atención de emergencias con participación del Comité Paritario o Vigía de Seguridad y Salud en el Trabajo?' WHERE item = '4.2.4';

UPDATE tbl_estandares_minimos SET criterio = '¿Se realiza el mantenimiento periódico a máquinas, herramientas, equipo, instalaciones, equipos de emergencia y redes eléctricas teniendo en cuenta informes de inspecciones o reporte de condiciones inseguras?' WHERE item = '4.2.5';

UPDATE tbl_estandares_minimos SET criterio = '¿Se le suministra a los trabajadores que lo requieran los EPP y se le reponen oportunamente según su uso, se verifica el cumplimiento por parte de los contratistas, se lleva registro de su entrega y de la realización de capacitación sobre el uso de los mismos?' WHERE item = '4.2.6';

-- Ciclo HACER - GESTIÓN DE AMENAZAS

UPDATE tbl_estandares_minimos SET criterio = '¿Se tiene un plan de prevención, preparación y respuesta ante emergencias que identifica las amenazas, analiza la vulnerabilidad e incluye política, objetivos, alcance, responsables, planos de las instalaciones, con las áreas y salidas de emergencia, señalización, simulacros mínimo anuales y con alcance, divulgación y capacitación a los trabajadores en todas las jornadas y centros de trabajo?' WHERE item = '5.1.1';

UPDATE tbl_estandares_minimos SET criterio = '¿La brigada está conformada, entrenada, dotada y capacitada y organizada según las necesidades y el tamaño de la empresa? (primeros auxilios, contraincendio, evacuación entre otras)' WHERE item = '5.1.2';

-- Ciclo VERIFICAR

UPDATE tbl_estandares_minimos SET criterio = '¿Se evidencian canales de comunicación y la participación efectiva de los trabajadores en los procesos de mejoramiento continuo, aportando recomendaciones para la revisión por la alta dirección hacia el fortalecimiento del SG-SST?' WHERE item = '6.1.1';

UPDATE tbl_estandares_minimos SET criterio = '¿Se cuenta con un programa anual de auditoría al SG-SST con la participación del Comité Paritario o Vigía de la Seguridad y Salud en el Trabajo?' WHERE item = '6.1.2';

UPDATE tbl_estandares_minimos SET criterio = '¿Se evidencia el cumplimiento de los procesos de auditoría de acuerdo con el alcance establecido en la normatividad (Decreto 1072 y estándares mínimos), y en compañía con el COPASST?' WHERE item = '6.1.3';

UPDATE tbl_estandares_minimos SET criterio = '¿Existe evidencia de las revisiones hechas por la Alta Gerencia al sistema de gestión de seguridad y salud en el trabajo mínimo una vez al año y de acuerdo con lo establecido en la normatividad vigente, sus resultados son comunicados al Comité Paritario o Vigía de la seguridad y salud en el trabajo y al responsable del SG-SST?' WHERE item = '6.1.4';

-- Ciclo ACTUAR

UPDATE tbl_estandares_minimos SET criterio = '¿Existe evidencia, documentación y responsables de la identificación de no conformidades y de la implementación de las acciones preventivas, correctivas y de mejora necesarias con base en los resultados de la supervisión, inspecciones, medición de indicadores, recomendaciones del COPASST o vigía entre otros?' WHERE item = '7.1.1';

UPDATE tbl_estandares_minimos SET criterio = '¿Se identifican medidas correctivas, preventivas y/o de mejora para el SG-SST del cumplimiento de los objetivos, de los resultados de las medidas de intervención y de los programas de promoción y prevención?' WHERE item = '7.1.2';

UPDATE tbl_estandares_minimos SET criterio = '¿Desde los resultados de las investigaciones de incidentes y ATEL y la determinación de sus causas básicas e inmediatas, se identifican deficiencias y se evidencia el cumplimiento de acciones preventivas, correctivas y de mejora hacia el SG-SST?' WHERE item = '7.1.3';

UPDATE tbl_estandares_minimos SET criterio = '¿Existe un plan de acción aprobado por la Gerencia o medidas correctivas, con base en la revisión por la alta dirección y tiene en cuenta los requerimientos y las recomendaciones emitidas por autoridades administrativas y/o por la ARL?' WHERE item = '7.1.4';

-- ===========================================================
-- FIN DEL SCRIPT
-- ===========================================================
