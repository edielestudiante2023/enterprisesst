<?php

namespace App\Libraries\DocumentosSSTTypes;

/**
 * Clase ReglamentoHigieneSeguridadIndustrial
 *
 * Reglamento de Higiene y Seguridad Industrial - Estándar 1.2.4
 * Basado en Resolución 1016/1989, Código Sustantivo del Trabajo (arts. 349-352),
 * Decreto 1072/2015 y Resolución 0312/2019 estándar 1.2.4.
 *
 * Tipo A (secciones_ia): IA genera contenido personalizado para cada empresa.
 * Nota: tipo_documento en BD es 'reglamento_higiene_seguridad' (sin 'industrial')
 *
 * @package App\Libraries\DocumentosSSTTypes
 */
class ReglamentoHigieneSeguridadIndustrial extends AbstractDocumentoSST
{
    public function getTipoDocumento(): string
    {
        return 'reglamento_higiene_seguridad';
    }

    public function getNombre(): string
    {
        return 'Reglamento de Higiene y Seguridad Industrial';
    }

    public function getDescripcion(): string
    {
        return 'Documento obligatorio que establece las normas internas de higiene y seguridad industrial para los trabajadores. Obligatorio para empresas con 10 o más trabajadores permanentes según Resolución 1016/1989 y artículos 349-352 del Código Sustantivo del Trabajo. Cumplimiento del estándar 1.2.4 de la Resolución 0312/2019.';
    }

    public function getEstandar(): ?string
    {
        return '1.2.4';
    }

    public function getSecciones(): array
    {
        return [
            ['numero' => 1,  'nombre' => 'Prescripciones Generales',              'key' => 'prescripciones_generales'],
            ['numero' => 2,  'nombre' => 'Obligaciones del Empleador',             'key' => 'obligaciones_empleador'],
            ['numero' => 3,  'nombre' => 'Obligaciones de los Trabajadores',       'key' => 'obligaciones_trabajadores'],
            ['numero' => 4,  'nombre' => 'Medidas de Higiene Industrial',          'key' => 'higiene_industrial'],
            ['numero' => 5,  'nombre' => 'Medidas de Seguridad Industrial',        'key' => 'seguridad_industrial'],
            ['numero' => 6,  'nombre' => 'Normas para Uso de Equipos y Maquinaria', 'key' => 'uso_equipos_maquinaria'],
            ['numero' => 7,  'nombre' => 'Elementos de Protección Personal',       'key' => 'elementos_proteccion_personal'],
            ['numero' => 8,  'nombre' => 'Señalización y Demarcación',             'key' => 'senalizacion_demarcacion'],
            ['numero' => 9,  'nombre' => 'Orden y Limpieza',                       'key' => 'orden_limpieza'],
            ['numero' => 10, 'nombre' => 'Procedimiento ante Accidente',           'key' => 'procedimiento_accidente'],
            ['numero' => 11, 'nombre' => 'Sanciones por Incumplimiento',           'key' => 'sanciones'],
        ];
    }

    public function getFirmantesRequeridos(int $estandares): array
    {
        return ['representante_legal', 'responsable_sst'];
    }

    public function getContenidoEstatico(string $seccionKey, array $cliente, ?array $contexto, int $estandares, int $anio): string
    {
        $nombreEmpresa = $cliente['nombre_cliente'] ?? 'la empresa';
        $nit = $cliente['nit'] ?? '';
        $comite = $this->getTextoComite($estandares);

        $contenidos = [
            'prescripciones_generales' => "El presente Reglamento de Higiene y Seguridad Industrial de {$nombreEmpresa} (NIT: {$nit}) establece las normas que deben cumplirse en todos los procesos y lugares de trabajo, en cumplimiento de los artículos 349 a 352 del Código Sustantivo del Trabajo, la Resolución 1016 de 1989, el Decreto 1072 de 2015 y la Resolución 0312 de 2019.\n\nEste reglamento es de obligatorio cumplimiento para todos los trabajadores directos, contratistas, aprendices, practicantes y visitantes durante su permanencia en las instalaciones de la empresa.\n\nTodo trabajador que ingrese a la empresa deberá leer y firmar este reglamento como constancia de conocimiento y aceptación.",

            'obligaciones_empleador' => "{$nombreEmpresa} se compromete a:\n\n1. Suministrar los elementos de protección personal requeridos sin costo para el trabajador.\n2. Afiliar a todos los trabajadores al Sistema General de Riesgos Laborales desde el primer día de trabajo.\n3. Investigar todos los accidentes e incidentes de trabajo.\n4. Reportar los accidentes de trabajo a la ARL dentro de los 2 días hábiles siguientes.\n5. Capacitar a los trabajadores en seguridad y salud en el trabajo.\n6. Realizar exámenes médicos ocupacionales de ingreso, periódicos y de retiro.\n7. Implementar y mantener el Sistema de Gestión de Seguridad y Salud en el Trabajo (SG-SST).\n8. Conformar y mantener el {$comite}.\n9. Mantener actualizadas la matriz de peligros y los procedimientos seguros.\n10. Proporcionar ambientes de trabajo seguros y saludables.",

            'obligaciones_trabajadores' => "Todo trabajador de {$nombreEmpresa} está obligado a:\n\n1. Cumplir las normas del presente reglamento y las instrucciones del SG-SST.\n2. Usar en todo momento los elementos de protección personal asignados.\n3. Reportar inmediatamente todo accidente, incidente o condición insegura.\n4. No operar máquinas o equipos sin autorización y capacitación previa.\n5. Asistir a las capacitaciones y programas de salud programados.\n6. No presentarse al trabajo bajo influencia de alcohol o drogas.\n7. Mantener su área de trabajo limpia y ordenada.\n8. Participar en los simulacros de emergencia.\n9. Cooperar con las investigaciones de accidentes.\n10. Conocer las rutas de evacuación y puntos de encuentro de emergencia.",

            'higiene_industrial' => "Normas de higiene industrial en {$nombreEmpresa}:\n\nHigiene personal:\n- Lavado de manos frecuente con agua y jabón\n- Uso de ropa de trabajo limpia y adecuada a la actividad\n- No consumir alimentos en áreas de trabajo con exposición a riesgos químicos o biológicos\n\nCondiciones sanitarias del lugar de trabajo:\n- Servicios sanitarios en cantidad suficiente (1 por cada 15 trabajadores)\n- Agua potable disponible en todas las áreas\n- Iluminación mínima de 500 lux en puestos de trabajo\n- Ventilación adecuada en todos los espacios de trabajo\n- Control de ruido ocupacional (límite 85 dB en 8 horas)\n\nControl de sustancias peligrosas:\n- Almacenamiento en lugares ventilados y señalizados\n- Fichas de seguridad (SDS/MSDS) disponibles\n- Disposición adecuada de residuos peligrosos",

            'seguridad_industrial' => "Normas de seguridad industrial aplicables en {$nombreEmpresa}:\n\nTrabajo seguro:\n- Identificar y controlar los peligros antes de iniciar cualquier tarea\n- Aplicar el análisis de trabajo seguro (ATS) en tareas de alto riesgo\n- Permisos de trabajo para actividades en alturas, espacios confinados y trabajo en caliente\n\nControl de energías peligrosas:\n- Aplicar procedimiento LOTO (Lockout/Tagout) para mantenimiento de equipos\n- Verificar que los equipos estén desenergizados antes de intervención\n\nTrabajo en alturas (si aplica):\n- Capacitación y certificación obligatoria según Resolución 4272/2021\n- Uso de arnés, eslinga y puntos de anclaje certificados\n- Permiso de trabajo en alturas vigente\n\nManejo manual de cargas:\n- Peso máximo hombres: 25 kg, mujeres: 12.5 kg (solo o solo con técnica)\n- Técnica correcta: doblar rodillas, espalda recta, carga pegada al cuerpo",

            'uso_equipos_maquinaria' => "Normas para el uso seguro de equipos y maquinaria en {$nombreEmpresa}:\n\n1. Solo el personal capacitado y autorizado puede operar equipos y maquinaria.\n2. Verificar el estado del equipo antes de cada uso (inspección pre-operacional).\n3. No remover guardas de seguridad o dispositivos de protección.\n4. Reportar inmediatamente cualquier falla o anomalía del equipo.\n5. No realizar mantenimiento con el equipo energizado.\n6. Los equipos eléctricos deben tener polo a tierra funcional.\n7. Las herramientas manuales deben estar en buen estado (sin mangos rotos, sin bordes filosos sin protección).\n8. El uso de teléfonos celulares está prohibido durante la operación de maquinaria.\n9. Las instrucciones del fabricante (manual de operación) deben seguirse en todo momento.",

            'elementos_proteccion_personal' => "Política de Elementos de Protección Personal (EPP) de {$nombreEmpresa}:\n\nPrincipio: El EPP es la última barrera de control. Su uso NO exime de implementar controles de ingeniería y administrativos.\n\nObligaciones:\n- La empresa suministra el EPP sin costo para el trabajador\n- El trabajador DEBE usar el EPP asignado mientras esté expuesto al riesgo\n- Está prohibido prestar o compartir EPP de uso personal\n\nEPP mínimos según área (personalizar según actividad de la empresa):\n\nAdministrativo: No requerido habitualmente\nOperativo: Casco, calzado de seguridad, guantes según tarea\nMantenimiento: Gafas, guantes, protección auditiva\nLaboratorio/Químicos: Guantes de nitrilo, gafas, delantal, careta\n\nMantenimiento del EPP:\n- Inspección antes de cada uso\n- Limpieza y almacenamiento adecuado\n- Reposición cuando esté dañado o al fin de su vida útil",

            'senalizacion_demarcacion' => "Sistema de señalización y demarcación en {$nombreEmpresa}:\n\nTipos de señales (según colores de seguridad NTC 1461):\n- Rojo: Prohibición, peligro, equipos de incendio\n- Amarillo: Advertencia, precaución, riesgo\n- Verde: Condición segura, primeros auxilios, evacuación\n- Azul: Obligación (uso de EPP)\n\nDemarcación de áreas:\n- Pasillos y vías de circulación: líneas amarillas o blancas\n- Áreas de almacenamiento: delimitadas y señalizadas\n- Equipos de emergencia: señalizados y accesibles\n- Rutas de evacuación: flechas y señales de salida fluorescentes\n\nMantenimiento:\n- Inspección mensual del estado de la señalización\n- Reposición inmediata de señales dañadas o ilegibles\n- Señalización temporal en trabajos en mantenimiento",

            'orden_limpieza' => "Normas de orden y limpieza en {$nombreEmpresa} (Programa 5S):\n\nSeiri (Clasificar): Eliminar lo innecesario del área de trabajo. Solo mantener lo que se usa.\n\nSeiton (Ordenar): Un lugar para cada cosa y cada cosa en su lugar. Identificar ubicaciones.\n\nSeiso (Limpiar): Limpiar e inspeccionar el área de trabajo al inicio y al final de la jornada.\n\nSeiketsu (Estandarizar): Establecer normas y procedimientos de limpieza.\n\nShitsuke (Disciplina): Cumplir consistentemente las normas establecidas.\n\nNormas específicas:\n- Los pasillos y rutas de evacuación deben permanecer SIEMPRE despejados\n- Los residuos se depositan en los recipientes correspondientes según tipo\n- Los derrames se limpian inmediatamente (sin excepción)\n- Los materiales no se apilan en zonas no demarcadas para ese fin\n- Los cables eléctricos no deben cruzar pasillos expuestos",

            'procedimiento_accidente' => "Procedimiento a seguir ante un accidente de trabajo en {$nombreEmpresa}:\n\nPrimera respuesta (primeros 5 minutos):\n1. Dar la alarma y llamar al brigadista de primeros auxilios\n2. Proteger el área para evitar más accidentados\n3. Proporcionar primeros auxilios básicos al lesionado\n4. Llamar a servicios de emergencia si se requiere (125 ambulancia)\n5. No mover al accidentado si se sospecha lesión en columna\n\nReporte y notificación:\n- Notificar al supervisor inmediato ANTES de 2 horas\n- La empresa reporta a la ARL ANTES de 2 días hábiles\n- Si hay muerte o accidente grave: notificar al Ministerio del Trabajo\n\nInvestigación:\n- Iniciar la investigación en las primeras 24 horas\n- Equipo investigador: supervisor, trabajador, representante del {$comite}\n- Documentar usando el formato de investigación de accidentes\n- Establecer causas básicas e inmediatas\n- Implementar medidas correctivas en los plazos establecidos",

            'sanciones' => "Sanciones por incumplimiento del presente reglamento:\n\nNivel 1 - Amonestación verbal:\n- Primera falta leve (omisión menor, falta de orden en área de trabajo)\n- Registro en hoja de vida laboral\n\nNivel 2 - Amonestación escrita:\n- Reincidencia en falta leve o primera falta moderada\n- No uso de EPP asignado\n- No reporte de incidentes o condiciones inseguras\n\nNivel 3 - Suspensión sin sueldo (1 a 5 días):\n- Falta grave (retirar guardas de seguridad, operar equipos sin autorización)\n- Reincidencia en falta moderada\n- Presentarse en estado de embriaguez o bajo efectos de drogas\n\nNivel 4 - Despido con justa causa (Código Sustantivo del Trabajo, art. 62):\n- Falta muy grave que ponga en peligro la vida propia o de otros\n- Reincidencia después de suspensión\n- Agresión física a compañeros dentro de las instalaciones\n\nTodo proceso sancionatorio garantiza el derecho de defensa y el debido proceso."
        ];

        return $contenidos[$seccionKey] ?? parent::getContenidoEstatico($seccionKey, $cliente, $contexto, $estandares, $anio);
    }
}
