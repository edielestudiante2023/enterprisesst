<?php

namespace App\Libraries\DocumentosSSTTypes;

use App\Services\DocumentoConfigService;

/**
 * Clase ProcedimientoEvaluacionesMedicas
 *
 * Implementa el Procedimiento de Evaluaciones Medicas Ocupacionales del SG-SST
 * para el estandar 3.1.1 de la Resolucion 0312/2019.
 *
 * Normativa de referencia:
 * - Resolucion 2346 de 2007 (evaluaciones medicas ocupacionales)
 * - Resolucion 1918 de 2009 (modifica arts. 11 y 17 de la Res. 2346/2007)
 * - Decreto 1072 de 2015 (Decreto Unico Reglamentario del Sector Trabajo)
 * - Resolucion 0312 de 2019 (Estandares Minimos del SG-SST)
 *
 * TIPO A: Solo documento formal con IA (sin actividades PTA ni indicadores)
 *
 * @package App\Libraries\DocumentosSSTTypes
 * @author Enterprise SST
 * @version 1.0
 */
class ProcedimientoEvaluacionesMedicas extends AbstractDocumentoSST
{
    protected ?DocumentoConfigService $configService = null;
    protected ?array $configCache = null;

    protected function getConfigService(): DocumentoConfigService
    {
        if ($this->configService === null) {
            $this->configService = new DocumentoConfigService();
        }
        return $this->configService;
    }

    protected function getConfig(): array
    {
        if ($this->configCache === null) {
            $this->configCache = $this->getConfigService()->obtenerTipoDocumento($this->getTipoDocumento());
        }
        return $this->configCache;
    }

    public function getTipoDocumento(): string
    {
        return 'procedimiento_evaluaciones_medicas';
    }

    public function getNombre(): string
    {
        $config = $this->getConfig();
        return $config['nombre'] ?? 'Procedimiento de Evaluaciones Medicas Ocupacionales';
    }

    public function getDescripcion(): string
    {
        $config = $this->getConfig();
        return $config['descripcion'] ?? 'Establece la metodologia para la realizacion de evaluaciones medicas ocupacionales, descripcion sociodemografica y diagnostico de condiciones de salud';
    }

    public function getEstandar(): ?string
    {
        return '3.1.1';
    }

    public function getSecciones(): array
    {
        $seccionesBD = $this->getConfigService()->obtenerSecciones($this->getTipoDocumento());

        if (empty($seccionesBD)) {
            return $this->getSeccionesFallback();
        }

        $secciones = [];
        foreach ($seccionesBD as $s) {
            $secciones[] = [
                'numero' => (int)($s['numero'] ?? 0),
                'nombre' => $s['nombre'] ?? '',
                'key' => $s['key'] ?? $s['seccion_key'] ?? ''
            ];
        }

        return $secciones;
    }

    protected function getSeccionesFallback(): array
    {
        return [
            ['numero' => 1, 'nombre' => 'Objetivo', 'key' => 'objetivo'],
            ['numero' => 2, 'nombre' => 'Alcance', 'key' => 'alcance'],
            ['numero' => 3, 'nombre' => 'Definiciones', 'key' => 'definiciones'],
            ['numero' => 4, 'nombre' => 'Marco Legal', 'key' => 'marco_legal'],
            ['numero' => 5, 'nombre' => 'Tipos de Evaluaciones Medicas Ocupacionales', 'key' => 'tipos_evaluaciones_medicas'],
            ['numero' => 6, 'nombre' => 'Frecuencia de Evaluaciones segun Riesgos', 'key' => 'frecuencia_evaluaciones'],
            ['numero' => 7, 'nombre' => 'Profesiograma', 'key' => 'profesiograma'],
            ['numero' => 8, 'nombre' => 'Descripcion Sociodemografica', 'key' => 'descripcion_sociodemografica'],
            ['numero' => 9, 'nombre' => 'Diagnostico de Condiciones de Salud', 'key' => 'diagnostico_condiciones_salud'],
            ['numero' => 10, 'nombre' => 'Comunicacion de Resultados al Trabajador', 'key' => 'comunicacion_resultados'],
            ['numero' => 11, 'nombre' => 'Restricciones y Recomendaciones Medicas', 'key' => 'restricciones_recomendaciones'],
            ['numero' => 12, 'nombre' => 'Custodia de Historias Clinicas', 'key' => 'custodia_historias_clinicas'],
            ['numero' => 13, 'nombre' => 'Responsabilidades', 'key' => 'responsabilidades'],
        ];
    }

    public function getFirmantesRequeridos(int $estandares): array
    {
        $config = $this->getConfig();
        if (!empty($config['firmantes'])) {
            return array_column($config['firmantes'], 'firmante_tipo');
        }

        return ['responsable_sst', 'representante_legal'];
    }

    protected function getPromptFallback(string $seccionKey, int $estandares): string
    {
        $comite = $this->getTextoComite($estandares);

        $prompts = [
            'objetivo' => "Genera el objetivo del Procedimiento de Evaluaciones Medicas Ocupacionales.
Debe mencionar:
- El proposito de establecer la metodologia para la realizacion de evaluaciones medicas ocupacionales
- El cumplimiento de la Resolucion 2346 de 2007 y la Resolucion 0312 de 2019 (estandar 3.1.1)
- La importancia de determinar las condiciones de salud de los trabajadores
- La relacion con los peligros y riesgos identificados en el SG-SST
Maximo 2 parrafos concisos.",

            'alcance' => "Define el alcance del procedimiento de evaluaciones medicas. Debe especificar:
- Aplica a todos los trabajadores (directos, contratistas, temporales) de la empresa
- Cubre todos los tipos de evaluaciones medicas ocupacionales (ingreso, periodicas, egreso, post-incapacidad, cambio de cargo)
- Incluye la elaboracion del profesiograma, descripcion sociodemografica y diagnostico de condiciones de salud
- A quien aplica: alta direccion, responsable SST, medico con licencia en SST, {$comite}, trabajadores
Maximo 2 parrafos.",

            'definiciones' => "Genera las definiciones clave para el procedimiento de evaluaciones medicas. INCLUIR OBLIGATORIAMENTE:
- Evaluacion Medica Ocupacional
- Profesiograma
- Descripcion Sociodemografica
- Diagnostico de Condiciones de Salud
- Aptitud Medica
- Historia Clinica Ocupacional
- Certificado de Aptitud
- Recomendaciones medicas
- Restricciones medicas
- Vigilancia Epidemiologica
CANTIDAD: 10-12 definiciones basadas en la Resolucion 2346 de 2007 y normativa colombiana.",

            'marco_legal' => "Genera el marco legal aplicable a las evaluaciones medicas ocupacionales en Colombia.
INCLUIR OBLIGATORIAMENTE con una breve descripcion de cada norma:
- Resolucion 2346 de 2007: Regula la practica de evaluaciones medicas ocupacionales y el manejo y contenido de las historias clinicas ocupacionales
- Resolucion 1918 de 2009: Modifica los articulos 11 y 17 de la Resolucion 2346 de 2007 (custodia de historias clinicas y contratacion de evaluaciones)
- Decreto 1072 de 2015: Decreto Unico Reglamentario del Sector Trabajo, articulos 2.2.4.6.24 (evaluaciones medicas ocupacionales)
- Resolucion 0312 de 2019: Estandares Minimos del SG-SST, estandar 3.1.1
- Ley 1562 de 2012: Sistema General de Riesgos Laborales
- Resolucion 2764 de 2022: Bateria de riesgo psicosocial
Presentar en formato de listado con numero de norma, ano y descripcion breve.",

            'tipos_evaluaciones_medicas' => "Describe los tipos de evaluaciones medicas ocupacionales segun la Resolucion 2346 de 2007.
OBLIGATORIO describir cada tipo con su definicion, objetivo y momento de realizacion:

1. **Evaluacion medica de ingreso o pre-ocupacional:** Antes de vincular al trabajador. Determinar aptitud para el cargo.
2. **Evaluaciones medicas periodicas (programadas):** Durante la vigencia del vinculo laboral. Frecuencia segun riesgos.
3. **Evaluaciones medicas periodicas (por cambio de ocupacion):** Cuando el trabajador cambia de puesto con nuevos riesgos.
4. **Evaluacion medica de egreso o post-ocupacional:** Al terminar la relacion laboral.
5. **Evaluaciones medicas post-incapacidad o por reintegro:** Despues de incapacidad prolongada.

Para cada tipo incluir: momento de realizacion, objetivo principal y examenes complementarios sugeridos.",

            'frecuencia_evaluaciones' => "Define la frecuencia de las evaluaciones medicas periodicas segun los factores de riesgo.
Presentar como tabla o listado:

- **Riesgo quimico:** Segun sustancia y nivel de exposicion (anual o semestral)
- **Riesgo fisico (ruido):** Audiometria anual o semestral segun nivel de exposicion
- **Riesgo biomecanico:** Evaluacion osteomuscular anual
- **Riesgo psicosocial:** Evaluacion segun bateria de riesgo psicosocial
- **Trabajo en alturas:** Evaluacion semestral o anual segun la Resolucion 4272 de 2021
- **Riesgo biologico:** Segun protocolo de la empresa

La frecuencia debe ajustarse al nivel de riesgo de la empresa y los peligros identificados en la matriz de peligros.
Mencionar que el medico con licencia en SST define la frecuencia en el profesiograma.",

            'profesiograma' => "Describe que es el profesiograma y su importancia en el procedimiento.
Debe incluir:
- Definicion del profesiograma como herramienta que define los examenes medicos por cargo
- Contenido minimo: cargo, descripcion de funciones, peligros asociados, examenes de ingreso/periodicos/egreso requeridos
- Quien lo elabora: medico con licencia en Salud Ocupacional o SST
- Frecuencia de actualizacion: cuando cambien los cargos, riesgos o normativa
- Relacion con la matriz de identificacion de peligros
- Importancia para definir la aptitud medica del trabajador

Enfatizar que el profesiograma es la base tecnica para todas las evaluaciones medicas.",

            'descripcion_sociodemografica' => "Describe como se elabora la descripcion sociodemografica de los trabajadores.
Segun el Decreto 1072 de 2015 y la Resolucion 0312 de 2019:

- Variables minimas: edad, sexo, escolaridad, estado civil, estrato, vivienda, numero de personas a cargo, antiguedad en la empresa, antiguedad en el cargo, tipo de contratacion
- Fuentes de informacion: encuesta sociodemografica, historias clinicas ocupacionales
- Frecuencia de actualizacion: minimo anual
- Responsable de la consolidacion: responsable del SG-SST
- Uso de la informacion: base para el diagnostico de condiciones de salud y programas de PVE
- Presentacion: tablas y graficos estadisticos

Mencionar que esta informacion es insumo para el diagnostico de condiciones de salud.",

            'diagnostico_condiciones_salud' => "Describe como se elabora el diagnostico de condiciones de salud de los trabajadores.
Segun la Resolucion 2346 de 2007 y el Decreto 1072 de 2015:

- Fuentes: resultados de evaluaciones medicas ocupacionales, ausentismo laboral, morbilidad
- Contenido minimo: prevalencia e incidencia de enfermedad, principales diagnosticos, restricciones medicas, indicadores de ausentismo
- Analisis por: grupo etario, genero, area, cargo, antiguedad, tipo de riesgo
- Recomendaciones del medico: programas de vigilancia epidemiologica, actividades de promocion y prevencion
- Frecuencia: minimo anual, o cuando cambien las condiciones de trabajo
- Responsable: medico con licencia en SST
- Confidencialidad: no incluir datos individuales, solo estadisticas agregadas

Resaltar que el diagnostico alimenta los SVE y el plan de trabajo anual del SG-SST.",

            'comunicacion_resultados' => "Describe como se comunican los resultados de las evaluaciones medicas a los trabajadores.
Segun el articulo 2.2.4.6.24 del Decreto 1072 de 2015 y la Resolucion 2346 de 2007:

- **Al trabajador:** Se le entrega copia del certificado de aptitud con recomendaciones (SIN revelar diagnosticos especificos al empleador)
- **Al empleador:** Solo recibe el certificado de aptitud (apto, apto con restricciones, no apto) y las recomendaciones
- **Plazo:** Resultados dentro de los 5 dias habiles siguientes a la evaluacion
- **Medio:** Por escrito, con firma de recibido del trabajador
- **Confidencialidad:** Los diagnosticos son confidenciales entre medico y trabajador (Resolucion 2346/2007, art. 16)
- **Restricciones y recomendaciones:** Se comunican al area de SST para seguimiento y al jefe inmediato para implementacion

Enfatizar la obligacion legal de comunicar los resultados por escrito.",

            'restricciones_recomendaciones' => "Describe el manejo de restricciones y recomendaciones medicas.
Debe incluir:

- **Definicion:** Diferencia entre restriccion (limitacion obligatoria) y recomendacion (sugerencia de mejora)
- **Registro:** Formato de seguimiento con: trabajador, cargo, restriccion/recomendacion, fecha, responsable de seguimiento
- **Comunicacion:** Al trabajador, jefe inmediato y area de SST
- **Seguimiento:** Verificacion periodica de cumplimiento de restricciones
- **Reubicacion laboral:** Procedimiento cuando las restricciones impiden el desempeno del cargo
- **Plazo de implementacion:** Inmediato para restricciones, segun cronograma para recomendaciones

Vincular con el {$comite} como organo de seguimiento al cumplimiento.",

            'custodia_historias_clinicas' => "Describe las obligaciones de custodia de historias clinicas ocupacionales.
Segun la Resolucion 2346 de 2007 (art. 16-17) y la Resolucion 1918 de 2009:

- **Responsable de la custodia:** El prestador de servicios de salud que practica las evaluaciones (IPS con licencia en SST)
- **NO** el empleador: El empleador NO puede tener, conservar ni revisar las historias clinicas
- **Tiempo de conservacion:** Minimo 20 anos despues de la ultima anotacion
- **Acceso:** Solo el trabajador, autoridades judiciales, autoridades de salud en caso de emergencia
- **Traslado:** Si la IPS cierra, debe entregar las historias clinicas a la nueva IPS o al trabajador
- **El empleador custodia:** Unicamente el certificado de aptitud medica y las recomendaciones/restricciones

Enfatizar que esta es una obligacion legal frecuentemente incumplida y verificada por los auditores.",

            'responsabilidades' => "Define las responsabilidades en la gestion de evaluaciones medicas ocupacionales:

**Alta Direccion / Representante Legal:**
- Garantizar los recursos para la realizacion de evaluaciones medicas
- Contratar servicios de IPS con licencia en SST
- No solicitar pruebas no autorizadas (embarazo, VIH sin consentimiento)

**Responsable del SG-SST:**
- Coordinar la realizacion de evaluaciones medicas segun profesiograma
- Asegurar que se realicen en los tiempos establecidos
- Realizar seguimiento a restricciones y recomendaciones
- Actualizar la descripcion sociodemografica y el diagnostico de condiciones de salud

**Medico con licencia en SST:**
- Practicar las evaluaciones medicas ocupacionales
- Elaborar el profesiograma
- Emitir certificados de aptitud y recomendaciones
- Custodiar las historias clinicas ocupacionales
- Elaborar el diagnostico de condiciones de salud

**{$comite}:**
- Conocer los resultados agregados del diagnostico de condiciones de salud
- Hacer seguimiento a las restricciones y recomendaciones medicas

**Trabajadores:**
- Asistir a las evaluaciones medicas programadas
- Informar sobre condiciones de salud que puedan afectar su desempeno
- Cumplir las restricciones y recomendaciones medicas"
        ];

        return $prompts[$seccionKey] ?? "Genera el contenido para la seccion '{$seccionKey}' del Procedimiento de Evaluaciones Medicas Ocupacionales segun la Resolucion 2346 de 2007, la Resolucion 0312 de 2019 (estandar 3.1.1) y el Decreto 1072 de 2015.";
    }

    public function getContenidoEstatico(string $seccionKey, array $cliente, ?array $contexto, int $estandares, int $anio): string
    {
        $nombreEmpresa = $cliente['nombre_cliente'] ?? 'la empresa';
        $comite = $this->getTextoComite($estandares);

        $contenidos = [
            'objetivo' => "Establecer la metodologia para la realizacion de las evaluaciones medicas ocupacionales (de ingreso, periodicas y de egreso) en {$nombreEmpresa}, asi como la elaboracion de la descripcion sociodemografica y el diagnostico de condiciones de salud de los trabajadores.\n\nEste procedimiento garantiza el cumplimiento de la Resolucion 2346 de 2007, la Resolucion 1918 de 2009, el articulo 2.2.4.6.24 del Decreto 1072 de 2015 y el estandar 3.1.1 de la Resolucion 0312 de 2019.",

            'alcance' => "Este procedimiento aplica a todos los trabajadores de {$nombreEmpresa}, incluyendo trabajadores directos, contratistas, temporales y demas personal que desarrolle actividades en las instalaciones o en representacion de la empresa.\n\nCubre la totalidad de las evaluaciones medicas ocupacionales: ingreso, periodicas programadas, por cambio de ocupacion, de egreso, post-incapacidad y de reintegro laboral.",

            'definiciones' => "**Evaluacion Medica Ocupacional:** Acto medico mediante el cual se interroga y examina a un trabajador, con el fin de monitorear la exposicion a factores de riesgo y determinar la existencia de consecuencias en la persona por dicha exposicion.\n\n**Profesiograma:** Herramienta que establece los examenes medicos requeridos por cargo, segun los peligros identificados.\n\n**Descripcion Sociodemografica:** Perfil estadistico de la poblacion trabajadora que incluye variables como edad, sexo, escolaridad, estado civil, estrato y antiguedad.\n\n**Diagnostico de Condiciones de Salud:** Resultado del procedimiento sistematico para determinar el conjunto de condiciones que definen el estado de salud de la poblacion trabajadora.\n\n**Certificado de Aptitud:** Documento que emite el medico con licencia en SST indicando si el trabajador es apto, apto con restricciones o no apto para el cargo.\n\n**Historia Clinica Ocupacional:** Conjunto de documentos que contienen datos, valoraciones e informacion generada con ocasion de la atencion medica ocupacional del trabajador.",

            'marco_legal' => "**Resolucion 2346 de 2007:** Por la cual se regula la practica de evaluaciones medicas ocupacionales y el manejo y contenido de las historias clinicas ocupacionales.\n\n**Resolucion 1918 de 2009:** Modifica los articulos 11 y 17 de la Resolucion 2346 de 2007, sobre custodia de historias clinicas y contratacion de servicios de evaluacion.\n\n**Decreto 1072 de 2015:** Decreto Unico Reglamentario del Sector Trabajo. Articulo 2.2.4.6.24: obligacion del empleador de realizar evaluaciones medicas ocupacionales.\n\n**Resolucion 0312 de 2019:** Estandares Minimos del SG-SST. Estandar 3.1.1: Evaluaciones medicas ocupacionales.\n\n**Ley 1562 de 2012:** Por la cual se modifica el Sistema General de Riesgos Laborales.\n\n**Resolucion 2764 de 2022:** Adopta la Bateria de instrumentos para la evaluacion de factores de riesgo psicosocial.",

            'tipos_evaluaciones_medicas' => "**1. Evaluacion Medica de Ingreso:** Se realiza antes de que el trabajador inicie labores en {$nombreEmpresa}. Objetivo: determinar la aptitud del aspirante para el cargo segun los riesgos a los que estara expuesto.\n\n**2. Evaluaciones Medicas Periodicas Programadas:** Se realizan durante la vigencia del vinculo laboral, con frecuencia definida en el profesiograma segun los peligros del cargo.\n\n**3. Evaluacion por Cambio de Ocupacion:** Se realiza cuando el trabajador cambia de puesto de trabajo y se expone a nuevos factores de riesgo.\n\n**4. Evaluacion Medica de Egreso:** Se practica cuando termina la relacion laboral, dentro de los cinco dias siguientes a la terminacion.\n\n**5. Evaluacion Post-incapacidad o de Reintegro:** Se realiza despues de una incapacidad prolongada para verificar las condiciones de salud antes del reintegro.",

            'frecuencia_evaluaciones' => "La frecuencia de las evaluaciones medicas periodicas se define segun los factores de riesgo identificados en la matriz de peligros y el profesiograma de {$nombreEmpresa}:\n\n- **Riesgo quimico:** Anual o semestral segun nivel de exposicion\n- **Riesgo fisico (ruido):** Audiometria anual\n- **Riesgo biomecanico:** Evaluacion osteomuscular anual\n- **Riesgo psicosocial:** Segun bateria de riesgo psicosocial\n- **Trabajo en alturas:** Segun Resolucion 4272 de 2021\n\nLa frecuencia especifica es definida por el medico con licencia en SST en el profesiograma.",

            'profesiograma' => "El profesiograma de {$nombreEmpresa} es elaborado por el medico con licencia en SST y contiene para cada cargo: nombre del cargo, funciones principales, peligros asociados, examenes medicos requeridos en ingreso, periodicos y egreso, y la frecuencia de cada examen.\n\nSe actualiza cuando hay cambios en los cargos, los riesgos o la normativa aplicable. Es el documento base para la programacion y realizacion de todas las evaluaciones medicas ocupacionales.",

            'descripcion_sociodemografica' => "La descripcion sociodemografica de {$nombreEmpresa} se actualiza al menos una vez al ano e incluye las siguientes variables: edad, sexo, escolaridad, estado civil, estrato socioeconomico, tipo de vivienda, personas a cargo, antiguedad en la empresa, antiguedad en el cargo y tipo de contratacion.\n\nLa informacion se obtiene de la encuesta sociodemografica aplicada a todos los trabajadores y se presenta en tablas y graficos estadisticos. Es insumo fundamental para el diagnostico de condiciones de salud.",

            'diagnostico_condiciones_salud' => "El diagnostico de condiciones de salud de {$nombreEmpresa} se elabora a partir de los resultados consolidados de las evaluaciones medicas ocupacionales. Incluye: prevalencia e incidencia de enfermedades, principales diagnosticos, restricciones medicas, indicadores de ausentismo y recomendaciones del medico.\n\nSe analiza por grupo etario, genero, area, cargo y tipo de riesgo. No incluye datos individuales, solo estadisticas agregadas. Se actualiza al menos una vez al ano y alimenta los programas de vigilancia epidemiologica y el plan de trabajo anual.",

            'comunicacion_resultados' => "Los resultados de las evaluaciones medicas se comunican por escrito:\n\n- **Al trabajador:** Copia del certificado de aptitud con recomendaciones, dentro de los 5 dias habiles siguientes\n- **Al empleador:** Certificado de aptitud (apto, apto con restricciones, no apto) y recomendaciones. NO se entregan diagnosticos individuales\n- **Confidencialidad:** Los diagnosticos son confidenciales entre el medico y el trabajador (Resolucion 2346/2007)\n\nSe conserva evidencia de la entrega con firma de recibido del trabajador.",

            'restricciones_recomendaciones' => "Las restricciones y recomendaciones medicas se registran en un formato de seguimiento que incluye: nombre del trabajador, cargo, tipo de restriccion o recomendacion, fecha de emision y responsable de seguimiento.\n\nSe comunican al trabajador, al jefe inmediato y al area de SST. El {$comite} realiza seguimiento al cumplimiento de las restricciones medicas.",

            'custodia_historias_clinicas' => "Segun la Resolucion 2346 de 2007 y la Resolucion 1918 de 2009:\n\n- Las historias clinicas ocupacionales son custodiadas por la IPS que practica las evaluaciones\n- {$nombreEmpresa} NO custodia historias clinicas, unicamente los certificados de aptitud y las recomendaciones\n- El tiempo de conservacion es minimo 20 anos despues de la ultima anotacion\n- El acceso es restringido: solo el trabajador, autoridades judiciales o de salud en emergencia",

            'responsabilidades' => "**Alta Direccion:** Garantizar los recursos para evaluaciones medicas y contratar IPS con licencia en SST.\n\n**Responsable del SG-SST:** Coordinar la realizacion de evaluaciones segun profesiograma, hacer seguimiento a restricciones y recomendaciones, actualizar descripcion sociodemografica y diagnostico de condiciones de salud.\n\n**Medico con licencia en SST:** Practicar evaluaciones, elaborar profesiograma y diagnostico de condiciones de salud, emitir certificados de aptitud, custodiar historias clinicas.\n\n**{$comite}:** Conocer resultados agregados del diagnostico de salud y hacer seguimiento a restricciones medicas.\n\n**Trabajadores:** Asistir a evaluaciones medicas programadas, informar condiciones de salud relevantes y cumplir restricciones medicas."
        ];

        return $contenidos[$seccionKey] ?? parent::getContenidoEstatico($seccionKey, $cliente, $contexto, $estandares, $anio);
    }
}
