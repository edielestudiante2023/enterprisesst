<?php

namespace App\Libraries\DocumentosSST;

/**
 * Programa de Capacitacion en Promocion y Prevencion (PYP)
 *
 * Estandar 1.2.1 de la Resolucion 0312/2019
 * "Programa de capacitacion promocion y prevencion PYP"
 *
 * Este programa establece las actividades de formacion orientadas a:
 * - Promocion de la salud en el trabajo
 * - Prevencion de accidentes y enfermedades laborales
 * - Cumplimiento del Decreto 1072/2015 Art. 2.2.4.6.11
 *
 * Aplica a todos los niveles de estandares (7, 21 y 60)
 */
class ProgramaCapacitacionPYP extends DocumentoBase
{
    public string $codigo = 'PRG-CAP';
    public string $nombre = 'Programa de Capacitacion en Promocion y Prevencion';
    public string $descripcion = 'Programa de capacitacion PYP segun estandar 1.2.1 de la Res. 0312/2019. Define las actividades de formacion en SST para trabajadores.';
    public string $estandar = '1.2.1';
    public string $carpetaPhva = '1.2';
    public string $cicloPhva = 'PLANEAR';
    public int $idTipo = 3; // Tipo: Programa

    public bool $aplica7 = true;
    public bool $aplica21 = true;
    public bool $aplica60 = true;

    /**
     * Estructura de 13 secciones para el Programa de Capacitacion PYP
     */
    public function getSecciones(): array
    {
        return [
            [
                'key' => 'introduccion',
                'titulo' => 'INTRODUCCION',
                'orden' => 1,
                'tipo' => 'ia',
                'prompt' => $this->getPromptIntroduccion(),
                'variables' => ['empresa', 'actividad_economica', 'nivel_riesgo', 'total_trabajadores'],
                'longitud_max' => 400
            ],
            [
                'key' => 'objetivos',
                'titulo' => 'OBJETIVOS',
                'orden' => 2,
                'tipo' => 'ia',
                'prompt' => $this->getPromptObjetivos(),
                'variables' => ['empresa', 'peligros_identificados', 'nivel_riesgo'],
                'longitud_max' => 500
            ],
            [
                'key' => 'alcance',
                'titulo' => 'ALCANCE',
                'orden' => 3,
                'tipo' => 'ia',
                'prompt' => $this->getPromptAlcance(),
                'variables' => ['empresa', 'total_trabajadores', 'sedes'],
                'longitud_max' => 300
            ],
            [
                'key' => 'marco_legal',
                'titulo' => 'MARCO LEGAL',
                'orden' => 4,
                'tipo' => 'ia',
                'prompt' => $this->getPromptMarcoLegal(),
                'variables' => ['actividad_economica'],
                'longitud_max' => 600
            ],
            [
                'key' => 'definiciones',
                'titulo' => 'DEFINICIONES',
                'orden' => 5,
                'tipo' => 'ia',
                'prompt' => $this->getPromptDefiniciones(),
                'variables' => [],
                'longitud_max' => 800
            ],
            [
                'key' => 'diagnostico',
                'titulo' => 'DIAGNOSTICO DE NECESIDADES DE CAPACITACION',
                'orden' => 6,
                'tipo' => 'ia',
                'prompt' => $this->getPromptDiagnostico(),
                'variables' => ['empresa', 'peligros_identificados', 'actividad_economica', 'nivel_riesgo'],
                'longitud_max' => 600
            ],
            [
                'key' => 'temas_capacitacion',
                'titulo' => 'TEMAS DE CAPACITACION',
                'orden' => 7,
                'tipo' => 'ia',
                'prompt' => $this->getPromptTemas(),
                'variables' => ['peligros_identificados', 'nivel_riesgo', 'actividad_economica'],
                'longitud_max' => 1000
            ],
            [
                'key' => 'cronograma',
                'titulo' => 'CRONOGRAMA DE CAPACITACION',
                'orden' => 8,
                'tipo' => 'ia',
                'prompt' => $this->getPromptCronograma(),
                'variables' => ['empresa'],
                'longitud_max' => 800
            ],
            [
                'key' => 'metodologia',
                'titulo' => 'METODOLOGIA',
                'orden' => 9,
                'tipo' => 'ia',
                'prompt' => $this->getPromptMetodologia(),
                'variables' => ['total_trabajadores'],
                'longitud_max' => 500
            ],
            [
                'key' => 'indicadores',
                'titulo' => 'INDICADORES DE GESTION',
                'orden' => 10,
                'tipo' => 'ia',
                'prompt' => $this->getPromptIndicadores(),
                'variables' => ['empresa'],
                'longitud_max' => 600
            ],
            [
                'key' => 'responsables',
                'titulo' => 'RESPONSABLES',
                'orden' => 11,
                'tipo' => 'ia',
                'prompt' => $this->getPromptResponsables(),
                'variables' => ['empresa', 'responsable_sst'],
                'longitud_max' => 500
            ],
            [
                'key' => 'recursos',
                'titulo' => 'RECURSOS',
                'orden' => 12,
                'tipo' => 'ia',
                'prompt' => $this->getPromptRecursos(),
                'variables' => ['empresa', 'total_trabajadores'],
                'longitud_max' => 400
            ],
            [
                'key' => 'registros',
                'titulo' => 'REGISTROS Y FORMATOS',
                'orden' => 13,
                'tipo' => 'ia',
                'prompt' => $this->getPromptRegistros(),
                'variables' => [],
                'longitud_max' => 400
            ]
        ];
    }

    public function getVariablesRequeridas(): array
    {
        return [
            'empresa',
            'nit',
            'actividad_economica',
            'nivel_riesgo',
            'total_trabajadores',
            'peligros_identificados',
            'responsable_sst',
            'sedes'
        ];
    }

    // =========================================================================
    // PROMPTS ESPECIFICOS PARA CADA SECCION
    // =========================================================================

    private function getPromptIntroduccion(): string
    {
        return "Genera la introduccion del Programa de Capacitacion en Promocion y Prevencion (PYP) para la empresa.

INSTRUCCIONES:
1. Inicia explicando la importancia de la capacitacion en SST para la prevencion de accidentes y enfermedades laborales
2. Menciona que este programa da cumplimiento al estandar 1.2.1 de la Resolucion 0312/2019
3. Relaciona con el Decreto 1072/2015, Articulo 2.2.4.6.11 (Capacitacion en SST)
4. Contextualiza segun la actividad economica y nivel de riesgo de la empresa
5. Menciona el compromiso de la alta direccion con la formacion de los trabajadores

FORMATO:
- 3-4 parrafos
- Lenguaje tecnico pero comprensible
- Usa el nombre real de la empresa (no 'la empresa')
- 200-300 palabras";
    }

    private function getPromptObjetivos(): string
    {
        return "Genera los objetivos del Programa de Capacitacion PYP.

ESTRUCTURA REQUERIDA:

**OBJETIVO GENERAL:**
Un objetivo que abarque el proposito principal del programa de capacitacion, orientado a desarrollar competencias en SST y prevenir accidentes/enfermedades.

**OBJETIVOS ESPECIFICOS:**
Generar 5-6 objetivos especificos que sean:
- Medibles y verificables
- Alineados con los peligros identificados de la empresa
- Orientados a promocion de la salud y prevencion
- Relacionados con el cumplimiento normativo

EJEMPLOS DE OBJETIVOS ESPECIFICOS:
- Capacitar al 100% de trabajadores en induccion SST
- Desarrollar competencias para identificar peligros
- Formar brigadas de emergencia
- Reducir accidentalidad mediante formacion en riesgos especificos

NOTA: Los objetivos deben ser SMART (Especificos, Medibles, Alcanzables, Relevantes, con Tiempo definido)";
    }

    private function getPromptAlcance(): string
    {
        return "Define el alcance del Programa de Capacitacion PYP.

DEBE ESPECIFICAR:

1. **Poblacion cubierta:**
   - Trabajadores directos (planta)
   - Trabajadores en mision/temporales
   - Contratistas y subcontratistas
   - Personal en practicas/aprendices
   - Visitantes (si aplica)

2. **Cobertura geografica:**
   - Todas las sedes de la empresa
   - Mencionar si hay trabajo en campo o instalaciones del cliente

3. **Momentos de aplicacion:**
   - Induccion (ingreso)
   - Reinduccion (periodica)
   - Capacitacion especifica por cargo/riesgo
   - Actualizacion normativa

4. **Exclusiones** (si las hay)

FORMATO: Lista con vinetas, maximo 15 items";
    }

    private function getPromptMarcoLegal(): string
    {
        return "Lista el marco normativo aplicable al Programa de Capacitacion en SST para Colombia.

GENERAR UNA TABLA con las siguientes columnas:
| Norma | Descripcion | Articulos Aplicables |

INCLUIR OBLIGATORIAMENTE:

1. **Decreto 1072 de 2015** - Decreto Unico Reglamentario del Sector Trabajo
   - Art. 2.2.4.6.11: Capacitacion en SST
   - Art. 2.2.4.6.12: Documentacion
   - Art. 2.2.4.6.35: Capacitacion obligatoria

2. **Resolucion 0312 de 2019** - Estandares Minimos SG-SST
   - Estandar 1.2.1: Programa de capacitacion PYP

3. **Resolucion 2400 de 1979** - Estatuto de Seguridad Industrial

4. **Ley 1562 de 2012** - Sistema General de Riesgos Laborales

5. **Normas especificas segun actividad economica:**
   - Resolucion 1409/2012 si hay trabajo en alturas
   - Resolucion 0312/2019 para brigadas de emergencia
   - Resolucion 2646/2008 para riesgo psicosocial
   - Otras segun peligros identificados

NOTA: Incluir maximo 10-12 normas las mas relevantes";
    }

    private function getPromptDefiniciones(): string
    {
        return "Genera un glosario de terminos tecnicos para el Programa de Capacitacion PYP.

INCLUIR LOS SIGUIENTES TERMINOS (minimo 12, maximo 18):

- Capacitacion
- Competencia laboral
- Induccion
- Reinduccion
- Entrenamiento
- Promocion de la salud
- Prevencion
- Peligro
- Riesgo
- Accidente de trabajo
- Enfermedad laboral
- SG-SST (Sistema de Gestion de Seguridad y Salud en el Trabajo)
- Matriz de peligros
- EPP (Elementos de Proteccion Personal)
- Condiciones de salud
- Acto inseguro
- Condicion insegura

FORMATO:
**Termino:** Definicion clara y concisa basada en normativa colombiana.

NOTA: Ordenar alfabeticamente";
    }

    private function getPromptDiagnostico(): string
    {
        return "Genera la metodologia de diagnostico de necesidades de capacitacion para la empresa.

ESTRUCTURA:

1. **FUENTES DE INFORMACION PARA EL DIAGNOSTICO:**
   - Matriz de identificacion de peligros (IPEVR)
   - Accidentalidad historica
   - Resultados de inspecciones de seguridad
   - Examenes medicos ocupacionales
   - Sugerencias del COPASST
   - Requisitos legales aplicables
   - Descripcion de cargos y tareas criticas

2. **METODOLOGIA DE IDENTIFICACION:**
   Explicar como se identifican las necesidades de capacitacion basadas en:
   - Peligros prioritarios de la empresa
   - Requisitos normativos
   - Brechas de competencias detectadas

3. **RESULTADOS DEL DIAGNOSTICO:**
   Basado en los peligros identificados de la empresa, indicar las areas prioritarias de capacitacion.
   Usar marcador [COMPLETAR] donde se requiera informacion especifica del diagnostico real.

FORMATO: Mezcla de parrafos y listas";
    }

    private function getPromptTemas(): string
    {
        return "Genera la matriz de temas de capacitacion para el Programa PYP.

ESTRUCTURA - TABLA con columnas:
| No. | Tema de Capacitacion | Dirigido a | Intensidad (horas) | Frecuencia | Tipo (Induccion/Periodica/Especifica) |

INCLUIR TEMAS EN TRES CATEGORIAS:

**A. CAPACITACIONES DE INDUCCION (obligatorias para todos):**
1. Politica y objetivos del SG-SST
2. Reglamento de Higiene y Seguridad Industrial
3. Identificacion de peligros y reporte de condiciones
4. Uso correcto de EPP
5. Procedimiento de emergencias y evacuacion
6. Derechos y deberes en el SGRL

**B. CAPACITACIONES PERIODICAS (reinduccion):**
7. Actualizacion del SG-SST (anual)
8. Estilos de vida saludable
9. Prevencion de riesgo psicosocial

**C. CAPACITACIONES ESPECIFICAS (segun peligros identificados):**
Agregar temas segun los peligros de la empresa:
- Si hay riesgo biomecanico: Higiene postural, pausas activas
- Si hay trabajo en alturas: Curso certificado trabajo seguro en alturas
- Si hay riesgo quimico: Manejo seguro de sustancias quimicas
- Si hay riesgo electrico: Seguridad electrica
- Si hay riesgo mecanico: Operacion segura de maquinaria
- Si hay conductores: Seguridad vial
- Otros segun actividad economica

TOTAL: Minimo 12, maximo 20 temas";
    }

    private function getPromptCronograma(): string
    {
        return "Genera el cronograma anual de capacitaciones para el Programa PYP.

FORMATO - TABLA tipo Gantt simplificado:
| No. | Tema | Ene | Feb | Mar | Abr | May | Jun | Jul | Ago | Sep | Oct | Nov | Dic |

CRITERIOS DE DISTRIBUCION:

1. **Primer trimestre (Ene-Mar):**
   - Induccion al SG-SST
   - Plan de emergencias
   - Politica y objetivos

2. **Segundo trimestre (Abr-Jun):**
   - Identificacion de peligros
   - Uso de EPP
   - Riesgos especificos del cargo

3. **Tercer trimestre (Jul-Sep):**
   - Estilos de vida saludable
   - Prevencion de riesgo psicosocial
   - Manejo de emergencias (simulacro)

4. **Cuarto trimestre (Oct-Dic):**
   - Reinduccion anual
   - Evaluacion del programa
   - Actualizacion normativa

MARCAR CON 'X' los meses donde se ejecutara cada capacitacion.

NOTA: Distribuir las capacitaciones de forma equilibrada en el ano, evitando concentrar todo en un solo periodo.";
    }

    private function getPromptMetodologia(): string
    {
        return "Describe la metodologia para ejecutar las capacitaciones del Programa PYP.

INCLUIR:

1. **MODALIDADES DE CAPACITACION:**
   - Presencial
   - Virtual sincronica
   - Virtual asincronica (plataforma e-learning)
   - Mixta (blended)

2. **TECNICAS PEDAGOGICAS:**
   - Exposicion teorica
   - Talleres practicos
   - Estudios de caso
   - Simulaciones y simulacros
   - Demostraciones
   - Videos y material audiovisual

3. **DURACION DE LAS SESIONES:**
   - Induccion: 2-4 horas
   - Capacitaciones periodicas: 1-2 horas
   - Capacitaciones especificas: segun tema

4. **EVALUACION DEL APRENDIZAJE:**
   - Evaluacion escrita (pre y post test)
   - Evaluacion practica (si aplica)
   - Criterio de aprobacion: minimo 80%
   - Retroalimentacion y refuerzo

5. **CAPACITADORES:**
   - Personal interno competente
   - Responsable del SG-SST
   - ARL
   - Proveedores externos especializados

FORMATO: Lista estructurada con vinetas";
    }

    private function getPromptIndicadores(): string
    {
        return "Define los indicadores de gestion para el Programa de Capacitacion PYP.

GENERAR TABLA con columnas:
| Indicador | Formula | Meta | Frecuencia | Responsable |

INCLUIR MINIMO 5 INDICADORES:

1. **INDICADORES DE COBERTURA:**
   - Cobertura de induccion = (Trabajadores con induccion / Total trabajadores nuevos) x 100
   - Meta: 100%

   - Cobertura de capacitacion = (Trabajadores capacitados / Total trabajadores) x 100
   - Meta: >= 90%

2. **INDICADORES DE CUMPLIMIENTO:**
   - Cumplimiento del cronograma = (Capacitaciones ejecutadas / Capacitaciones programadas) x 100
   - Meta: >= 90%

3. **INDICADORES DE EFECTIVIDAD:**
   - Evaluacion promedio = Suma de calificaciones / Total evaluados
   - Meta: >= 80%

   - Tasa de aprobacion = (Trabajadores aprobados / Total evaluados) x 100
   - Meta: >= 95%

4. **INDICADORES DE IMPACTO:**
   - Reduccion de accidentes post-capacitacion (comparativo semestral)
   - Reduccion de actos inseguros detectados

NOTA: Indicar formula exacta y unidad de medida";
    }

    private function getPromptResponsables(): string
    {
        return "Define los roles y responsabilidades para el Programa de Capacitacion PYP.

GENERAR TABLA con columnas:
| Rol | Responsabilidades |

ROLES A INCLUIR:

1. **ALTA DIRECCION / GERENCIA:**
   - Aprobar el programa y asignar recursos
   - Participar en capacitaciones de liderazgo en SST
   - Asegurar tiempo para capacitacion de trabajadores

2. **RESPONSABLE DEL SG-SST:**
   - Disenar y actualizar el programa de capacitacion
   - Coordinar la ejecucion del cronograma
   - Gestionar capacitadores internos y externos
   - Evaluar la efectividad del programa
   - Mantener registros de capacitacion

3. **COPASST / VIGIA SST:**
   - Participar en la identificacion de necesidades
   - Proponer temas de capacitacion
   - Verificar la ejecucion del programa

4. **JEFES / SUPERVISORES:**
   - Facilitar la asistencia de su equipo
   - Identificar necesidades especificas
   - Reforzar lo aprendido en el trabajo

5. **TRABAJADORES:**
   - Asistir puntualmente a las capacitaciones
   - Participar activamente
   - Aplicar lo aprendido
   - Aprobar las evaluaciones

6. **ARL:**
   - Brindar asesoria tecnica
   - Apoyar con capacitaciones especializadas
   - Proporcionar material educativo";
    }

    private function getPromptRecursos(): string
    {
        return "Identifica los recursos necesarios para ejecutar el Programa de Capacitacion PYP.

CATEGORIAS:

1. **RECURSOS HUMANOS:**
   - Responsable del SG-SST (coordinacion)
   - Capacitadores internos
   - Capacitadores externos (ARL, proveedores)
   - Personal administrativo de apoyo

2. **RECURSOS FISICOS:**
   - Sala de capacitacion o auditorio
   - Mobiliario (sillas, mesas)
   - Equipos audiovisuales (proyector, pantalla, sonido)
   - Tablero o papelografo

3. **RECURSOS TECNOLOGICOS:**
   - Computador portatil
   - Plataforma virtual (si aplica)
   - Internet
   - Software de presentaciones

4. **MATERIALES:**
   - Material didactico impreso
   - Formatos de asistencia
   - Evaluaciones
   - Certificados
   - EPP para demostraciones (si aplica)

5. **RECURSOS FINANCIEROS:**
   - Presupuesto estimado anual: [COMPLETAR]
   - Incluir: honorarios capacitadores externos, materiales, refrigerios, certificados

NOTA: El presupuesto debe estar incluido en el plan anual de SST";
    }

    private function getPromptRegistros(): string
    {
        return "Lista los registros y formatos asociados al Programa de Capacitacion PYP.

GENERAR TABLA con columnas:
| Codigo | Nombre del Formato | Responsable | Frecuencia | Retencion |

FORMATOS REQUERIDOS:

1. **FOR-CAP-001** - Formato de Asistencia a Capacitacion
   - Responsable: Responsable SST
   - Frecuencia: Por cada capacitacion
   - Retencion: 20 anos

2. **FOR-CAP-002** - Evaluacion de Capacitacion (Pre y Post Test)
   - Responsable: Capacitador
   - Frecuencia: Por cada capacitacion
   - Retencion: 20 anos

3. **FOR-CAP-003** - Certificado de Capacitacion
   - Responsable: Responsable SST
   - Frecuencia: Por trabajador capacitado
   - Retencion: 20 anos

4. **FOR-CAP-004** - Matriz de Capacitacion por Trabajador
   - Responsable: Responsable SST
   - Frecuencia: Actualizacion continua
   - Retencion: Vigencia del trabajador + 5 anos

5. **FOR-CAP-005** - Evaluacion de Efectividad de Capacitacion
   - Responsable: Responsable SST
   - Frecuencia: Trimestral
   - Retencion: 5 anos

6. **FOR-CAP-006** - Informe de Indicadores de Capacitacion
   - Responsable: Responsable SST
   - Frecuencia: Trimestral
   - Retencion: 5 anos

NOTA: Los tiempos de retencion se basan en el Art. 2.2.4.6.13 del Decreto 1072/2015";
    }
}
