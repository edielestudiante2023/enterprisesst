<?php

namespace App\Libraries\DocumentosSSTTypes;

use App\Services\DocumentoConfigService;

/**
 * Clase IdentificacionSustanciasCancerigenas
 *
 * Implementa la Identificacion de Sustancias Catalogadas como Cancerigenas
 * o con Toxicidad Aguda para el estandar 4.1.3 de la Resolucion 0312/2019.
 *
 * Permite documentar si la empresa procesa, manipula o trabaja con agentes
 * o sustancias cancerigenas o con toxicidad aguda, priorizar los riesgos
 * asociados y establecer acciones de prevencion e intervencion.
 *
 * TIPO A: Solo documento formal con IA (sin actividades PTA ni indicadores)
 *
 * @package App\Libraries\DocumentosSSTTypes
 * @author Enterprise SST
 * @version 1.0
 */
class IdentificacionSustanciasCancerigenas extends AbstractDocumentoSST
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
        return 'identificacion_sustancias_cancerigenas';
    }

    public function getNombre(): string
    {
        $config = $this->getConfig();
        return $config['nombre'] ?? 'Identificacion de Sustancias Cancerigenas o con Toxicidad Aguda';
    }

    public function getDescripcion(): string
    {
        $config = $this->getConfig();
        return $config['descripcion'] ?? 'Identifica sustancias cancerigenas o con toxicidad aguda, prioriza riesgos asociados y establece acciones de prevencion e intervencion';
    }

    public function getEstandar(): ?string
    {
        return '4.1.3';
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
            ['numero' => 5, 'nombre' => 'Responsabilidades', 'key' => 'responsabilidades'],
            ['numero' => 6, 'nombre' => 'Inventario de Sustancias Cancerigenas y con Toxicidad Aguda', 'key' => 'inventario_sustancias'],
            ['numero' => 7, 'nombre' => 'Clasificacion segun SGA y IARC', 'key' => 'clasificacion_sga_iarc'],
            ['numero' => 8, 'nombre' => 'Evaluacion y Priorizacion de Riesgos Asociados', 'key' => 'evaluacion_priorizacion'],
            ['numero' => 9, 'nombre' => 'Medidas de Prevencion e Intervencion', 'key' => 'medidas_prevencion'],
            ['numero' => 10, 'nombre' => 'Vigilancia de la Salud de Trabajadores Expuestos', 'key' => 'vigilancia_salud'],
            ['numero' => 11, 'nombre' => 'Indicadores de Gestion', 'key' => 'indicadores'],
            ['numero' => 12, 'nombre' => 'Registros y Evidencias', 'key' => 'registros_evidencias'],
        ];
    }

    public function getFirmantesRequeridos(int $estandares): array
    {
        $config = $this->getConfig();
        if (!empty($config['firmantes'])) {
            return array_column($config['firmantes'], 'firmante_tipo');
        }

        return ['consultor_sst', 'responsable_sst', 'representante_legal'];
    }

    protected function getPromptFallback(string $seccionKey, int $estandares): string
    {
        $comite = $this->getTextoComite($estandares);

        $prompts = [
            'objetivo' => "Genera el objetivo del documento de Identificacion de Sustancias Cancerigenas o con Toxicidad Aguda.
Debe mencionar:
- Identificar si la empresa procesa, manipula o trabaja con agentes o sustancias catalogadas como cancerigenas o con toxicidad aguda
- Que dichas sustancias estan incluidas en la Tabla de Enfermedades Laborales (Decreto 1477/2014)
- Priorizar los riesgos asociados a estas sustancias
- Establecer acciones de prevencion e intervencion
- Cumplimiento del Decreto 1072/2015 (art. 2.2.4.6.15 y 2.2.4.6.23) y Resolucion 0312/2019 (estandar 4.1.3)
Maximo 2 parrafos concisos.",

            'alcance' => "Define el alcance del documento. Debe especificar:
- Aplica a todos los procesos, actividades, areas y puestos de trabajo donde se procesen, manipulen, almacenen, transporten o se tenga contacto con sustancias cancerigenas o con toxicidad aguda
- Incluye clasificacion IARC grupos 1, 2A y 2B, y categorias de toxicidad aguda SGA 1-4
- Aplica a todos los trabajadores: directos, contratistas, subcontratistas, temporales
- Cubre todas las sedes y centros de trabajo
- Incluye materias primas, insumos, productos intermedios, finales, residuos y subproductos
Maximo 2 parrafos.",

            'definiciones' => "Genera las definiciones clave. INCLUIR OBLIGATORIAMENTE:
- Sustancia cancerigena
- Toxicidad aguda
- Agente quimico
- IARC (Agencia Internacional para la Investigacion del Cancer)
- Grupo 1 IARC (cancerigeno confirmado para humanos)
- Grupo 2A IARC (probablemente cancerigeno)
- Grupo 2B IARC (posiblemente cancerigeno)
- Sistema Globalmente Armonizado (SGA)
- Ficha de Datos de Seguridad (FDS/SDS)
- Valor Limite Permisible (TLV)
- Tabla de Enfermedades Laborales
- Exposicion ocupacional
- Pictograma de peligro
- Categoria de toxicidad aguda (SGA)
- Vigilancia epidemiologica
CANTIDAD: 14-16 definiciones.",

            'marco_legal' => "Genera el marco legal aplicable. INCLUIR:
- Decreto 1072 de 2015, art. 2.2.4.6.15 (Identificacion de peligros y valoracion de riesgos)
- Decreto 1072 de 2015, art. 2.2.4.6.23 (Gestion de peligros y riesgos)
- Resolucion 0312 de 2019, estandar 4.1.3
- Decreto 1477 de 2014 (Tabla de Enfermedades Laborales - Seccion I Grupo I agentes cancerigenos)
- Decreto 1496 de 2018 (Adopcion del SGA sexta edicion revisada)
- Ley 55 de 1993 (Convenio OIT 170 sobre seguridad en la utilizacion de productos quimicos)
- Resolucion 2400 de 1979 (Higiene y seguridad industrial)
- Resolucion 2346 de 2007 (Evaluaciones medicas ocupacionales)
Presentar en formato tabla.",

            'responsabilidades' => "Define las responsabilidades:

**Alta Direccion / Representante Legal:**
- Garantizar la identificacion de sustancias cancerigenas y con toxicidad aguda
- Asignar recursos para mediciones ambientales, EPP y controles
- Priorizar los riesgos asociados a estas sustancias
- Aprobar medidas de prevencion e intervencion

**Responsable del SG-SST:**
- Elaborar y mantener el inventario de sustancias peligrosas
- Evaluar la exposicion y priorizar riesgos
- Coordinar mediciones de higiene industrial
- Articular con el Programa de Vigilancia Epidemiologica
- Comunicar resultados al {$comite} y a la alta direccion

**{$comite}:**
- Participar en la identificacion de sustancias peligrosas
- Verificar que se implementen los controles
- Proponer medidas de prevencion adicionales

**Trabajadores:**
- Reportar exposicion a sustancias peligrosas
- Usar correctamente los EPP asignados
- Participar en capacitaciones sobre riesgo quimico
- Cumplir con los procedimientos de manejo seguro",

            'inventario_sustancias' => "Describe como se realiza el inventario de sustancias cancerigenas y con toxicidad aguda:

**Fuentes de informacion:**
- Fichas de Datos de Seguridad (FDS) de todas las sustancias utilizadas
- Tabla de Enfermedades Laborales (Decreto 1477/2014, Seccion I, Grupo I)
- Clasificacion IARC (monografias actualizadas)
- Listados SGA de sustancias peligrosas
- Matriz de peligros y riesgos (peligro quimico)
- Registros de compras y almacen

**Proceso de inventario:**
1. Listar todas las sustancias quimicas presentes en la empresa
2. Revisar la FDS de cada sustancia (seccion 2: identificacion de peligros, seccion 11: informacion toxicologica)
3. Cruzar con la Tabla de Enfermedades Laborales para identificar cancerigenos listados
4. Verificar clasificacion IARC (grupos 1, 2A, 2B)
5. Verificar clasificacion SGA de toxicidad aguda (categorias 1-4)
6. Registrar en formato de inventario

**Formato de inventario:**
Nombre sustancia, numero CAS, clasificacion IARC, categoria SGA, area/proceso de uso, cantidad almacenada, numero de trabajadores expuestos, FDS disponible (si/no), fecha de actualizacion.",

            'clasificacion_sga_iarc' => "Describe los sistemas de clasificacion aplicables:

**Clasificacion IARC (Agencia Internacional para la Investigacion del Cancer):**
- **Grupo 1:** Cancerigeno confirmado para humanos (ej: asbesto, benceno, formaldehido, silice cristalina, humo de tabaco)
- **Grupo 2A:** Probablemente cancerigeno (ej: glifosato, emisiones de frituras, trabajo nocturno)
- **Grupo 2B:** Posiblemente cancerigeno (ej: gasolina, negro de humo, talco)

**Sistema Globalmente Armonizado (SGA) - Toxicidad aguda:**
- **Categoria 1:** Mortal (oral: DL50 <= 5 mg/kg) - Pictograma: calavera y tibias cruzadas
- **Categoria 2:** Mortal (oral: 5 < DL50 <= 50 mg/kg) - Pictograma: calavera y tibias cruzadas
- **Categoria 3:** Toxico (oral: 50 < DL50 <= 300 mg/kg) - Pictograma: calavera y tibias cruzadas
- **Categoria 4:** Nocivo (oral: 300 < DL50 <= 2000 mg/kg) - Pictograma: signo de admiracion

**Pictograma SGA para carcinogenicidad:** Peligro para la salud (silueta humana con estrella)

**Etiquetado:** Segun Decreto 1496/2018, toda sustancia debe estar etiquetada con pictogramas SGA, palabra de advertencia, indicaciones de peligro y consejos de prudencia.",

            'evaluacion_priorizacion' => "Describe como se evaluan y priorizan los riesgos asociados:

**Evaluacion de la exposicion:**
1. Identificar vias de ingreso (inhalacion, contacto dermico, ingestion)
2. Estimar concentracion ambiental vs TLV (Valores Limite Permisibles)
3. Determinar tiempo y frecuencia de exposicion
4. Evaluar controles existentes (fuente, medio, individuo)

**Priorizacion de riesgos (requisito explicito del estandar 4.1.3):**
- Sustancias IARC Grupo 1: SIEMPRE riesgo NO aceptable, intervencion inmediata
- Sustancias IARC Grupo 2A: Riesgo alto, intervencion prioritaria
- Sustancias SGA Categoria 1-2 toxicidad aguda: Riesgo critico
- Considerar numero de trabajadores expuestos
- Considerar poblacion vulnerable (embarazadas, menores de edad, personas con condiciones preexistentes)
- Evaluar exposiciones simultaneas a multiples sustancias

**Actualizacion de la Matriz de Peligros:**
Los riesgos por sustancias cancerigenas y con toxicidad aguda deben quedar priorizados en la matriz, con nivel de riesgo I o II, y plan de accion inmediato.",

            'medidas_prevencion' => "Describe las medidas de prevencion e intervencion segun jerarquia de controles:

**1. Eliminacion:** Modificar el proceso para eliminar la sustancia cancerigena o con toxicidad aguda.

**2. Sustitucion:** Reemplazar la sustancia por una de menor peligrosidad. Para cancerigenos Grupo 1 IARC, la sustitucion debe ser la primera opcion evaluada.

**3. Controles de ingenieria:** Sistemas cerrados de manejo, ventilacion localizada (extractores), cabinas de seguridad, automatizacion de procesos, sistemas de contencion de derrames.

**4. Controles administrativos:** Procedimientos de trabajo seguro, senalizacion SGA, capacitacion especifica en riesgo quimico, restriccion de acceso a areas de almacenamiento, rotacion de personal, permisos de trabajo para actividades con exposicion, FDS accesibles en todos los puntos de uso.

**5. EPP (ultimo recurso):** Respiradores con filtros especificos para quimicos, guantes de nitrilo/neopreno (segun sustancia), gafas hermeticas o careta facial, overol quimico desechable.

**Protocolo de emergencias quimicas:**
Procedimiento ante derrames, fugas o exposicion accidental. Kit de derrames. Duchas y lavaojos de emergencia. Linea de atencion toxicologica.",

            'vigilancia_salud' => "Describe la vigilancia de la salud para trabajadores expuestos:

**Evaluaciones medicas especificas:**
- Ingreso: Examen clinico + paraclínicos segun sustancia de exposicion
- Periodicas: Frecuencia semestral o segun concepto medico ocupacional
- Egreso: Con recomendacion de seguimiento post-ocupacional
- Post-ocupacional: Seguimiento por tiempo de latencia (cancer: 10-30 anos)

**Biomarcadores segun tipo de exposicion:**
- Benceno: acido S-fenilmercapturico en orina, hemograma completo
- Formaldehido: pruebas de funcion respiratoria, citologia nasal
- Plomo: plumbemia, protoporfirina zinc
- Asbesto: radiografia de torax, espirometria
- Silice: radiografia torax segun clasificacion OIT

**Programa de Vigilancia Epidemiologica:**
- Indicado para toda exposicion a cancerigenos confirmados (IARC Grupo 1)
- Registro de cohorte de expuestos
- Seguimiento de morbilidad sentida
- Articulacion con EPS y ARL

**Comunicacion de resultados:** Al trabajador (certificado aptitud + recomendaciones). Al empleador (solo aptitud, nunca diagnostico). Confidencialidad de historias clinicas.

**Conservacion de registros:** Minimo 30 anos para exposicion a sustancias cancerigenas.",

            'indicadores' => "Genera indicadores de gestion del programa:

**Indicadores de proceso:**
- % sustancias con FDS actualizada = (sustancias con FDS / total sustancias) x 100. Meta: 100%. Frecuencia: semestral.
- % trabajadores expuestos con evaluacion medica especifica = (expuestos evaluados / total expuestos) x 100. Meta: 100%. Frecuencia: semestral.
- % capacitaciones en riesgo quimico ejecutadas = (capacitaciones realizadas / programadas) x 100. Meta: >= 90%. Frecuencia: trimestral.
- % mediciones ambientales realizadas = (mediciones ejecutadas / programadas) x 100. Meta: >= 90%. Frecuencia: anual.
- Cobertura del inventario de sustancias = (areas inventariadas / total areas) x 100. Meta: 100%. Frecuencia: anual.

**Indicadores de resultado:**
- Niveles de exposicion dentro de TLV = (mediciones dentro de TLV / total mediciones) x 100. Meta: 100%. Frecuencia: anual.
- Enfermedades laborales por exposicion a cancerigenos = numero casos. Meta: 0. Frecuencia: anual.
- % sustancias cancerigenas sustituidas = (sustancias sustituidas / identificadas como sustituibles) x 100. Meta: creciente. Frecuencia: anual.
- % controles implementados = (controles implementados / controles definidos) x 100. Meta: >= 90%. Frecuencia: semestral.",

            'registros_evidencias' => "Describe los registros y evidencias que se deben mantener:

**Registros obligatorios:**
1. Inventario de sustancias cancerigenas y con toxicidad aguda (actualizado)
2. Fichas de Datos de Seguridad (FDS) de todas las sustancias
3. Matriz de Peligros y Riesgos actualizada con peligro quimico priorizado
4. Evaluaciones medicas ocupacionales especificas
5. Resultados de mediciones de higiene industrial (mediciones ambientales)
6. Certificados de capacitacion en riesgo quimico y manejo de sustancias peligrosas
7. Registros de entrega y reposicion de EPP quimico
8. Procedimientos de manejo seguro de sustancias peligrosas
9. Plan de emergencia quimica (derrames, fugas, exposicion accidental)
10. Programa de sustitucion de sustancias (cuando aplique)
11. Actas de socializacion al {$comite}

**Conservacion:** Minimo 30 anos para registros de exposicion a sustancias cancerigenas (criterio especial por tiempo de latencia del cancer). Demas registros: minimo 20 anos (Resolucion 0312/2019).

**Acceso:** Responsable SST, alta direccion, auditor, {$comite}, ARL, autoridades de inspeccion y vigilancia."
        ];

        return $prompts[$seccionKey] ?? "Genera el contenido para la seccion '{$seccionKey}' del documento de Identificacion de Sustancias Cancerigenas o con Toxicidad Aguda segun la Resolucion 0312/2019 (estandar 4.1.3) y el Decreto 1072/2015. Debe abordar la identificacion, priorizacion de riesgos y acciones de prevencion e intervencion para sustancias cancerigenas y con toxicidad aguda.";
    }

    public function getContenidoEstatico(string $seccionKey, array $cliente, ?array $contexto, int $estandares, int $anio): string
    {
        $nombreEmpresa = $cliente['nombre_cliente'] ?? 'la empresa';
        $comite = $this->getTextoComite($estandares);

        $contenidos = [
            'objetivo' => "Identificar si {$nombreEmpresa} procesa, manipula o trabaja con agentes o sustancias catalogadas como cancerigenas o con toxicidad aguda, causantes de enfermedades incluidas en la Tabla de Enfermedades Laborales (Decreto 1477 de 2014), priorizar los riesgos asociados a estas sustancias y establecer acciones de prevencion e intervencion orientadas a proteger la salud de los trabajadores.\n\nEste documento da cumplimiento a los articulos 2.2.4.6.15 y 2.2.4.6.23 del Decreto 1072 de 2015 y al estandar 4.1.3 de la Resolucion 0312 de 2019, en concordancia con el Decreto 1477 de 2014, el Decreto 1496 de 2018 (Sistema Globalmente Armonizado) y la Ley 55 de 1993 (Convenio OIT 170).",

            'alcance' => "Este documento aplica a todos los procesos, actividades, areas y puestos de trabajo de {$nombreEmpresa} donde se procesen, manipulen, almacenen, transporten o se tenga contacto con sustancias catalogadas como cancerigenas (grupos IARC 1, 2A y 2B) o con toxicidad aguda (categorias SGA 1 a 4). Incluye materias primas, insumos, productos intermedios, productos finales, residuos y subproductos.\n\nAplica a todos los trabajadores directos, contratistas, subcontratistas, temporales y visitantes en todas las sedes y centros de trabajo, asi como en actividades externas donde pueda presentarse exposicion a dichas sustancias.",

            'definiciones' => "**Sustancia cancerigena:** Agente quimico, fisico o biologico capaz de provocar cancer o aumentar su incidencia en los seres humanos.\n\n**Toxicidad aguda:** Efectos adversos que se manifiestan tras la exposicion a una dosis unica o varias dosis de una sustancia en un periodo corto (24 horas o menos).\n\n**IARC:** Agencia Internacional para la Investigacion del Cancer, organismo de la OMS que clasifica los agentes segun su potencial cancerigeno.\n\n**Grupo 1 IARC:** Cancerigeno confirmado para humanos (evidencia suficiente).\n\n**Grupo 2A IARC:** Probablemente cancerigeno para humanos (evidencia limitada en humanos, suficiente en animales).\n\n**Sistema Globalmente Armonizado (SGA):** Sistema internacional de clasificacion y etiquetado de productos quimicos, adoptado en Colombia mediante Decreto 1496 de 2018.\n\n**Ficha de Datos de Seguridad (FDS):** Documento de 16 secciones que proporciona informacion sobre los peligros de una sustancia quimica y las medidas de proteccion.\n\n**Valor Limite Permisible (TLV):** Concentracion maxima de una sustancia en el aire del ambiente de trabajo a la que un trabajador puede estar expuesto sin efectos adversos.",

            'marco_legal' => "**Decreto 1072 de 2015:**\n- Articulo 2.2.4.6.15: Identificacion de peligros, evaluacion y valoracion de los riesgos.\n- Articulo 2.2.4.6.23: Gestion de los peligros y riesgos.\n\n**Resolucion 0312 de 2019:**\n- Estandar 4.1.3: Identificacion de sustancias catalogadas como cancerigenas o con toxicidad aguda.\n\n**Decreto 1477 de 2014:**\n- Tabla de Enfermedades Laborales (Seccion I, Grupo I: agentes cancerigenos).\n\n**Decreto 1496 de 2018:**\n- Adopcion del Sistema Globalmente Armonizado de Clasificacion y Etiquetado de Productos Quimicos (SGA, sexta edicion revisada).\n\n**Ley 55 de 1993:**\n- Aprueba Convenio OIT 170 sobre la seguridad en la utilizacion de productos quimicos en el trabajo.\n\n**Resolucion 2346 de 2007:**\n- Evaluaciones medicas ocupacionales.",

            'responsabilidades' => "**Alta Direccion / Representante Legal:**\n- Garantizar la identificacion de sustancias cancerigenas y con toxicidad aguda\n- Asignar recursos para mediciones ambientales, EPP especifico y controles de ingenieria\n- Priorizar los riesgos asociados en la toma de decisiones\n\n**Responsable del SG-SST:**\n- Elaborar y mantener actualizado el inventario de sustancias peligrosas\n- Evaluar la exposicion ocupacional y priorizar riesgos\n- Coordinar mediciones de higiene industrial\n- Articular con programas de vigilancia epidemiologica\n- Comunicar resultados al {$comite} y a la alta direccion\n\n**{$comite}:**\n- Participar en la identificacion de sustancias peligrosas\n- Verificar la implementacion de controles\n- Proponer medidas de prevencion adicionales\n\n**Trabajadores:**\n- Reportar exposicion a sustancias peligrosas\n- Usar correctamente los EPP asignados\n- Participar en capacitaciones de riesgo quimico\n- Cumplir procedimientos de manejo seguro",

            'inventario_sustancias' => "{$nombreEmpresa} mantiene un inventario actualizado de todas las sustancias quimicas presentes en sus procesos, identificando aquellas catalogadas como cancerigenas o con toxicidad aguda.\n\nEl inventario se elabora mediante: revision de Fichas de Datos de Seguridad (FDS), cruce con la Tabla de Enfermedades Laborales (Decreto 1477/2014, Seccion I, Grupo I), consulta de la clasificacion IARC y verificacion de la clasificacion SGA de toxicidad aguda.\n\nPara cada sustancia se registra: nombre, numero CAS, clasificacion IARC, categoria SGA, area y proceso de uso, cantidad almacenada, numero de trabajadores expuestos y disponibilidad de la FDS.",

            'clasificacion_sga_iarc' => "**Clasificacion IARC:**\n- Grupo 1: Cancerigeno confirmado (ej: asbesto, benceno, formaldehido, silice cristalina)\n- Grupo 2A: Probablemente cancerigeno (ej: glifosato, emisiones de frituras a alta temperatura)\n- Grupo 2B: Posiblemente cancerigeno (ej: gasolina, negro de humo, talco)\n\n**Clasificacion SGA - Toxicidad aguda:**\n- Categoria 1: Mortal (DL50 oral <= 5 mg/kg)\n- Categoria 2: Mortal (DL50 oral 5-50 mg/kg)\n- Categoria 3: Toxico (DL50 oral 50-300 mg/kg)\n- Categoria 4: Nocivo (DL50 oral 300-2000 mg/kg)\n\nToda sustancia en {$nombreEmpresa} debe estar etiquetada segun el SGA (Decreto 1496/2018) con pictogramas, palabra de advertencia, indicaciones de peligro y consejos de prudencia.",

            'evaluacion_priorizacion' => "La evaluacion de riesgos asociados a sustancias cancerigenas y con toxicidad aguda se realiza considerando: vias de ingreso (inhalacion, contacto dermico, ingestion), concentracion ambiental vs TLV, tiempo y frecuencia de exposicion, y controles existentes.\n\nLa priorizacion sigue el criterio del estandar 4.1.3: sustancias IARC Grupo 1 se clasifican SIEMPRE como riesgo no aceptable con intervencion inmediata. Grupo 2A como riesgo alto con intervencion prioritaria. Sustancias SGA Categoria 1-2 de toxicidad aguda como riesgo critico.\n\nLos riesgos por sustancias cancerigenas y con toxicidad aguda quedan priorizados en la Matriz de Peligros y Riesgos con nivel de riesgo I o II, con plan de accion definido.",

            'medidas_prevencion' => "Las medidas de prevencion e intervencion siguen la jerarquia de controles:\n\n1. **Eliminacion:** Modificar procesos para eliminar la sustancia peligrosa.\n2. **Sustitucion:** Reemplazar por sustancias de menor peligrosidad (primera opcion para cancerigenos Grupo 1).\n3. **Controles de ingenieria:** Sistemas cerrados, ventilacion localizada, cabinas de seguridad, automatizacion.\n4. **Controles administrativos:** Procedimientos de trabajo seguro, senalizacion SGA, capacitacion en riesgo quimico, restriccion de acceso, FDS accesibles en puntos de uso.\n5. **EPP:** Respiradores con filtros quimicos, guantes de nitrilo/neopreno, gafas hermeticas, overol quimico.\n\nSe cuenta con protocolo de emergencias quimicas ante derrames, fugas o exposicion accidental.",

            'vigilancia_salud' => "Los trabajadores expuestos a sustancias cancerigenas o con toxicidad aguda son incluidos en el Programa de Vigilancia Epidemiologica con:\n\n- Evaluaciones medicas de ingreso con paraclinicos especificos segun sustancia\n- Evaluaciones periodicas semestrales o segun concepto medico ocupacional\n- Evaluaciones de egreso con recomendacion de seguimiento post-ocupacional\n- Biomarcadores de exposicion y efecto segun tipo de sustancia\n\nLos resultados se comunican al trabajador segun Resolucion 2346/2007. Los registros de exposicion a cancerigenos se conservan por minimo 30 anos.",

            'indicadores' => "**Indicadores de proceso:**\n- Cobertura de inventario: (sustancias con FDS / total sustancias) x 100. Meta: 100%.\n- Evaluaciones medicas especificas: (expuestos evaluados / total expuestos) x 100. Meta: 100%.\n- Capacitaciones ejecutadas: (realizadas / programadas) x 100. Meta: >= 90%.\n- Mediciones ambientales: (ejecutadas / programadas) x 100. Meta: >= 90%.\n\n**Indicadores de resultado:**\n- Niveles dentro de TLV: (mediciones conformes / total mediciones) x 100. Meta: 100%.\n- Enfermedades laborales por cancerigenos: numero de casos. Meta: 0.\n- Sustancias sustituidas: porcentaje creciente anual.\n- Controles implementados: (implementados / definidos) x 100. Meta: >= 90%.",

            'registros_evidencias' => "**Registros obligatorios:**\n1. Inventario de sustancias cancerigenas y con toxicidad aguda\n2. Fichas de Datos de Seguridad (FDS) actualizadas\n3. Matriz de Peligros y Riesgos con peligro quimico priorizado\n4. Evaluaciones medicas ocupacionales especificas\n5. Resultados de mediciones de higiene industrial\n6. Certificados de capacitacion en riesgo quimico\n7. Registros de entrega de EPP quimico\n8. Procedimientos de manejo seguro\n9. Plan de emergencia quimica\n10. Actas de socializacion al {$comite}\n\n**Conservacion:** 30 anos para registros de exposicion a cancerigenos. 20 anos para demas registros.\n**Acceso:** Responsable SST, alta direccion, auditor, {$comite}, ARL."
        ];

        return $contenidos[$seccionKey] ?? parent::getContenidoEstatico($seccionKey, $cliente, $contexto, $estandares, $anio);
    }
}
