<?php

namespace App\Libraries\DocumentosSSTTypes;

use App\Models\CompetenciaEscalaClienteModel;
use App\Models\CompetenciaClienteModel;
use App\Models\CompetenciaNivelClienteModel;
use App\Models\ClienteCompetenciaCargoModel;
use App\Services\DocumentoConfigService;

/**
 * Clase DiccionarioCompetenciasCliente
 *
 * Documento de 1 parte (flujo secciones_ia) que publica el Diccionario de
 * Competencias propio del cliente: escala 1-5, catalogo de competencias por
 * familia, rubricas por nivel y matriz de competencias por cargo.
 *
 * Los datos de competencias viven en tablas scoped por id_cliente
 * (tbl_competencia_cliente, tbl_competencia_nivel_cliente,
 * tbl_competencia_escala_cliente, tbl_cliente_competencia_cargo).
 *
 * Ver: docs/MODULO_DICCIONARIO_COMPETENCIAS/ARQUITECTURA.md
 */
class DiccionarioCompetenciasCliente extends AbstractDocumentoSST
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
        return 'diccionario_competencias_cliente';
    }

    public function getNombre(): string
    {
        $config = $this->getConfig();
        return $config['nombre'] ?? 'Diccionario de Competencias';
    }

    public function getDescripcion(): string
    {
        $config = $this->getConfig();
        return $config['descripcion'] ?? 'Catalogo de competencias del cliente con escala, rubricas por nivel y matriz de asignacion por cargo.';
    }

    public function getEstandar(): ?string
    {
        return null;
    }

    public function getSecciones(): array
    {
        $seccionesBD = $this->getConfigService()->obtenerSecciones($this->getTipoDocumento());

        if (!empty($seccionesBD)) {
            $out = [];
            foreach ($seccionesBD as $s) {
                $out[] = [
                    'numero' => (int)($s['numero'] ?? 0),
                    'nombre' => $s['nombre'] ?? '',
                    'key'    => $s['key'] ?? $s['seccion_key'] ?? '',
                ];
            }
            return $out;
        }

        return $this->getSeccionesFallback();
    }

    protected function getSeccionesFallback(): array
    {
        return [
            ['numero' => 1, 'nombre' => 'Objetivo',                          'key' => 'objetivo'],
            ['numero' => 2, 'nombre' => 'Alcance',                           'key' => 'alcance'],
            ['numero' => 3, 'nombre' => 'Marco conceptual de competencias',  'key' => 'marco_conceptual'],
            ['numero' => 4, 'nombre' => 'Escala de evaluacion',              'key' => 'escala_evaluacion'],
            ['numero' => 5, 'nombre' => 'Catalogo de competencias',          'key' => 'catalogo_competencias'],
            ['numero' => 6, 'nombre' => 'Matriz de competencias por cargo',  'key' => 'matriz_cargo_competencia'],
            ['numero' => 7, 'nombre' => 'Responsabilidades',                 'key' => 'responsabilidades'],
        ];
    }

    public function getFirmantesRequeridos(int $estandares): array
    {
        $config = $this->getConfig();
        if (!empty($config['firmantes'])) {
            return array_column($config['firmantes'], 'firmante_tipo');
        }
        return ['representante_legal', 'responsable_sst', 'consultor_sst'];
    }

    /**
     * Sobrescribe el contexto base para inyectar el diccionario del cliente.
     * La IA recibe la escala, competencias por familia y matriz cargo-competencia
     * para poder personalizar las secciones textuales (objetivo, alcance,
     * marco conceptual, responsabilidades).
     */
    public function getContextoBase(array $cliente, ?array $contexto): string
    {
        $base = parent::getContextoBase($cliente, $contexto);

        $idCliente = (int)($cliente['id_cliente'] ?? 0);
        if ($idCliente <= 0) {
            return $base;
        }

        $escalaModel     = new CompetenciaEscalaClienteModel();
        $competenciaMdl  = new CompetenciaClienteModel();
        $nivelModel      = new CompetenciaNivelClienteModel();
        $matrizModel     = new ClienteCompetenciaCargoModel();

        $escala          = $escalaModel->porCliente($idCliente);
        $porFamilia      = $competenciaMdl->porClienteAgrupadas($idCliente);
        $nivelesPorComp  = $nivelModel->porCliente($idCliente);
        $matriz          = $matrizModel->matrizCliente($idCliente);

        $bloque = "\n\nDICCIONARIO DE COMPETENCIAS DEL CLIENTE:\n";
        $bloque .= "Total familias: " . count($porFamilia) . " | Total competencias: "
                 . array_sum(array_map('count', $porFamilia)) . " | Cargos con asignacion: "
                 . count(array_unique(array_column($matriz, 'id_cargo_cliente'))) . "\n";

        // Escala
        if (!empty($escala)) {
            $bloque .= "\nESCALA 1-5:\n";
            foreach ($escala as $e) {
                $bloque .= "- Nivel {$e['nivel']} {$e['nombre']} ({$e['etiqueta']})\n";
            }
        }

        // Competencias por familia (nombre + definicion, sin descripcion de niveles para no saturar)
        if (!empty($porFamilia)) {
            $bloque .= "\nCATALOGO (agrupado por familia):\n";
            foreach ($porFamilia as $familia => $lista) {
                $bloque .= "* Familia: {$familia}\n";
                foreach ($lista as $c) {
                    $def = mb_substr(trim((string)($c['definicion'] ?? '')), 0, 200);
                    $bloque .= "  - {$c['numero']}. {$c['nombre']}: {$def}\n";
                }
            }
        }

        // Matriz cargo-competencia resumida
        if (!empty($matriz)) {
            $bloque .= "\nMATRIZ CARGO-COMPETENCIA (resumen):\n";
            $porCargo = [];
            foreach ($matriz as $m) {
                $porCargo[$m['nombre_cargo']][] = "{$m['nombre_competencia']} (nivel {$m['nivel_requerido']})";
            }
            foreach ($porCargo as $cargo => $items) {
                $bloque .= "- {$cargo}: " . implode(', ', $items) . "\n";
            }
        }

        $bloque .= "\nUSA este diccionario como referencia al redactar las secciones textuales. No inventes competencias que no esten en el catalogo del cliente.\n";

        return $base . $bloque;
    }

    protected function getPromptFallback(string $seccionKey, int $estandares): string
    {
        $prompts = [
            'objetivo'         => 'Redacta el objetivo del Diccionario de Competencias para la empresa. 1 parrafo, tono formal.',
            'alcance'          => 'Redacta el alcance: aplica a todos los cargos definidos; uso transversal en Talento Humano, SST y gerencia.',
            'marco_conceptual' => 'Explica brevemente: que es una competencia, por que se agrupan en familias (logro, ayuda_servicio, influencia, gerenciales, cognitivas, eficacia_personal), para que sirve la escala 1-5 y como se lee la matriz cargo-competencia.',
            'responsabilidades'=> 'Lista las responsabilidades: Representante Legal aprueba, Talento Humano mantiene, Lideres de area aplican en seleccion y evaluacion, Trabajadores conocen su perfil.',
        ];
        return $prompts[$seccionKey] ?? "Genera el contenido para la seccion '{$seccionKey}' del Diccionario de Competencias.";
    }
}
