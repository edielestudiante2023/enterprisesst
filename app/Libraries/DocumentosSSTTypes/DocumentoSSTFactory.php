<?php

namespace App\Libraries\DocumentosSSTTypes;

/**
 * Factory DocumentoSSTFactory
 *
 * Crea instancias del tipo de documento correcto basándose en el tipo solicitado.
 * Usa convención de nombres para detectar clases automáticamente.
 *
 * CÓMO AGREGAR UN NUEVO TIPO DE DOCUMENTO:
 * 1. Crear una clase que extienda AbstractDocumentoSST
 * 2. Nombrar la clase en PascalCase (ej: MatrizRiesgos para 'matriz_riesgos')
 * 3. Colocar en app/Libraries/DocumentosSSTTypes/
 * 4. El Factory la detectará automáticamente
 *
 * @package App\Libraries\DocumentosSSTTypes
 * @author Enterprise SST
 * @version 1.0
 */
class DocumentoSSTFactory
{
    /**
     * Mapeo manual de tipos a clases (para casos especiales)
     * Si el tipo no está aquí, se usa la convención de nombres
     */
    private static array $tiposRegistrados = [
        'programa_capacitacion' => ProgramaCapacitacion::class,
        'procedimiento_control_documental' => ProcedimientoControlDocumental::class,
        'programa_promocion_prevencion_salud' => ProgramaPromocionPrevencionSalud::class,
        'programa_induccion_reinduccion' => ProgramaInduccionReinduccion::class,
        'procedimiento_matriz_legal' => ProcedimientoMatrizLegal::class,
        // 2.1.1 Políticas de SST
        'politica_sst_general' => PoliticaSstGeneral::class,
        'politica_alcohol_drogas' => PoliticaAlcoholDrogas::class,
        'politica_acoso_laboral' => PoliticaAcosoLaboral::class,
        'politica_violencias_genero' => PoliticaViolenciasGenero::class,
        'politica_discriminacion' => PoliticaDiscriminacion::class,
        'politica_prevencion_emergencias' => PoliticaPrevencionEmergencias::class,
        // 1.1.8 Conformacion Comite de Convivencia
        'manual_convivencia_laboral' => ManualConvivenciaLaboral::class,
        // 2.2.1 Objetivos y Metas del SG-SST
        'plan_objetivos_metas' => PlanObjetivosMetas::class,
        // 2.8.1 Mecanismos de Comunicación, Auto Reporte
        'mecanismos_comunicacion_sgsst' => MecanismosComunicacionSgsst::class,
        // 3.1.1 Procedimiento de Evaluaciones Médicas Ocupacionales
        'procedimiento_evaluaciones_medicas' => ProcedimientoEvaluacionesMedicas::class,
        // 2.9.1 Procedimiento de Adquisiciones en SST
        'procedimiento_adquisiciones' => ProcedimientoAdquisiciones::class,
        // 2.10.1 Evaluacion y Seleccion de Proveedores y Contratistas
        'procedimiento_evaluacion_proveedores' => ProcedimientoEvaluacionProveedores::class,
        // 2.11.1 Procedimiento de Gestion del Cambio
        'procedimiento_gestion_cambio' => ProcedimientoGestionCambio::class,
        // 3.1.7 Estilos de Vida Saludable y Entornos Saludables
        'programa_estilos_vida_saludable' => ProgramaEstilosVidaSaludable::class,
        // 3.1.4 Programa de Evaluaciones Medicas Ocupacionales
        'programa_evaluaciones_medicas_ocupacionales' => ProgramaEvaluacionesMedicasOcupacionales::class,
        // 3.2.1 Procedimiento de Investigacion de Accidentes de Trabajo y Enfermedades Laborales
        'procedimiento_investigacion_accidentes' => ProcedimientoInvestigacionAccidentes::class,
        // 3.2.2 Investigacion de Incidentes, Accidentes de Trabajo y Enfermedades Laborales
        'procedimiento_investigacion_incidentes' => ProcedimientoInvestigacionIncidentes::class,
        // 4.1.1 Metodologia para la Identificacion de Peligros, Evaluacion y Valoracion de Riesgos
        'metodologia_identificacion_peligros' => MetodologiaIdentificacionPeligros::class,
        // 4.1.3 Identificacion de Sustancias Cancerigenas o con Toxicidad Aguda
        'identificacion_sustancias_cancerigenas' => IdentificacionSustanciasCancerigenas::class,
        // 4.2.3 Programas de Seguridad - PVEs
        'pve_riesgo_biomecanico' => PveRiesgoBiomecanico::class,
        'pve_riesgo_psicosocial' => PveRiesgoPsicosocial::class,
        // 4.2.5 Mantenimiento Periodico de Instalaciones, Equipos, Maquinas, Herramientas
        'programa_mantenimiento_periodico' => ProgramaMantenimientoPeriodico::class,
        // 1.1.5 Identificacion de Trabajadores de Alto Riesgo y Cotizacion de Pension Especial
        'identificacion_alto_riesgo' => IdentificacionAltoRiesgo::class,
        // Actas de Constitucion - Comites Electorales
        'acta_constitucion_copasst' => ActaConstitucionCopasst::class,
        'acta_constitucion_cocolab' => ActaConstitucionCocolab::class,
        'acta_constitucion_brigada' => ActaConstitucionBrigada::class,
        'acta_constitucion_vigia' => ActaConstitucionVigia::class,
        // Actas de Recomposicion - Comites Electorales
        'acta_recomposicion_copasst' => ActaRecomposicionCopasst::class,
        'acta_recomposicion_cocolab' => ActaRecomposicionCocolab::class,
        'acta_recomposicion_brigada' => ActaRecomposicionBrigada::class,
        'acta_recomposicion_vigia' => ActaRecomposicionVigia::class,
    ];

    /**
     * Cache de instancias creadas (Singleton por tipo)
     */
    private static array $instancias = [];

    /**
     * Crea o retorna una instancia del tipo de documento solicitado
     *
     * @param string $tipoDocumento El tipo de documento (ej: 'programa_capacitacion')
     * @return DocumentoSSTInterface Instancia del documento
     * @throws \InvalidArgumentException Si el tipo no existe
     */
    public static function crear(string $tipoDocumento): DocumentoSSTInterface
    {
        // Verificar cache primero
        if (isset(self::$instancias[$tipoDocumento])) {
            return self::$instancias[$tipoDocumento];
        }

        // Buscar en mapeo manual
        if (isset(self::$tiposRegistrados[$tipoDocumento])) {
            $clase = self::$tiposRegistrados[$tipoDocumento];
            self::$instancias[$tipoDocumento] = new $clase();
            return self::$instancias[$tipoDocumento];
        }

        // Intentar por convención de nombres (snake_case -> PascalCase)
        $nombreClase = self::snakeToPascal($tipoDocumento);
        $claseCompleta = "App\\Libraries\\DocumentosSSTTypes\\{$nombreClase}";

        if (class_exists($claseCompleta)) {
            $instancia = new $claseCompleta();
            if ($instancia instanceof DocumentoSSTInterface) {
                self::$instancias[$tipoDocumento] = $instancia;
                return $instancia;
            }
        }

        // Si no se encuentra, lanzar excepción
        throw new \InvalidArgumentException(
            "Tipo de documento '{$tipoDocumento}' no encontrado. " .
            "Asegúrese de que existe la clase {$claseCompleta} y que implementa DocumentoSSTInterface."
        );
    }

    /**
     * Verifica si un tipo de documento está soportado
     *
     * @param string $tipoDocumento El tipo a verificar
     * @return bool True si el tipo está soportado
     */
    public static function existe(string $tipoDocumento): bool
    {
        // Verificar en mapeo manual
        if (isset(self::$tiposRegistrados[$tipoDocumento])) {
            return true;
        }

        // Verificar por convención de nombres
        $nombreClase = self::snakeToPascal($tipoDocumento);
        $claseCompleta = "App\\Libraries\\DocumentosSSTTypes\\{$nombreClase}";

        return class_exists($claseCompleta) &&
               is_subclass_of($claseCompleta, DocumentoSSTInterface::class);
    }

    /**
     * Obtiene todos los tipos de documentos registrados
     *
     * @return array Lista de tipos disponibles con su información
     */
    public static function getTiposDisponibles(): array
    {
        $tipos = [];

        foreach (self::$tiposRegistrados as $tipo => $clase) {
            try {
                $instancia = self::crear($tipo);
                $tipos[$tipo] = [
                    'nombre' => $instancia->getNombre(),
                    'descripcion' => $instancia->getDescripcion(),
                    'estandar' => $instancia->getEstandar(),
                    'secciones' => count($instancia->getSecciones()),
                ];
            } catch (\Exception $e) {
                // Ignorar tipos con error
                log_message('warning', "Error al cargar tipo de documento {$tipo}: " . $e->getMessage());
            }
        }

        return $tipos;
    }

    /**
     * Registra un nuevo tipo de documento en el factory
     * Útil para registrar tipos dinámicamente desde otros módulos
     *
     * @param string $tipoDocumento El identificador del tipo
     * @param string $clase La clase completa que implementa DocumentoSSTInterface
     */
    public static function registrar(string $tipoDocumento, string $clase): void
    {
        if (!class_exists($clase)) {
            throw new \InvalidArgumentException("La clase {$clase} no existe");
        }

        if (!is_subclass_of($clase, DocumentoSSTInterface::class)) {
            throw new \InvalidArgumentException("La clase {$clase} no implementa DocumentoSSTInterface");
        }

        self::$tiposRegistrados[$tipoDocumento] = $clase;

        // Limpiar cache si existía
        unset(self::$instancias[$tipoDocumento]);
    }

    /**
     * Limpia el cache de instancias (útil para testing)
     */
    public static function limpiarCache(): void
    {
        self::$instancias = [];
    }

    /**
     * Convierte snake_case a PascalCase
     * Ej: 'programa_capacitacion' -> 'ProgramaCapacitacion'
     *
     * @param string $snakeCase Texto en snake_case
     * @return string Texto en PascalCase
     */
    private static function snakeToPascal(string $snakeCase): string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $snakeCase)));
    }
}
