<?php

namespace App\Libraries\DocumentosSSTTypes;

use App\Models\EppClienteModel;
use App\Services\DocumentoConfigService;

/**
 * Matriz de EPP y Dotacion (SST-MT-G-003).
 *
 * Documento Tipo A (flujo secciones_ia) que publica la matriz del cliente
 * con EPP y dotacion asignados (snapshot editable + foto del maestro).
 *
 * Los datos viven en tbl_epp_cliente (snapshot por cliente) +
 * tbl_epp_maestro (catalogo global, provee foto y relacion de categoria).
 *
 * Ver: docs/MODULO_MATRIZ_EPP/ARQUITECTURA.md
 */
class MatrizEpp extends AbstractDocumentoSST
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
        return 'matriz_epp';
    }

    public function getNombre(): string
    {
        $config = $this->getConfig();
        return $config['nombre'] ?? 'Matriz de Elementos de Proteccion Personal y Dotacion';
    }

    public function getDescripcion(): string
    {
        $config = $this->getConfig();
        return $config['descripcion'] ?? 'Matriz por cliente con EPP y dotacion, normas, mantenimiento, frecuencia y momentos de uso. Basado en SST-MT-G-003.';
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
            ['numero' => 1, 'nombre' => 'Objetivo',                             'key' => 'objetivo'],
            ['numero' => 2, 'nombre' => 'Alcance',                              'key' => 'alcance'],
            ['numero' => 3, 'nombre' => 'Marco legal y normativo',              'key' => 'marco_legal'],
            ['numero' => 4, 'nombre' => 'Responsabilidades',                    'key' => 'responsabilidades'],
            ['numero' => 5, 'nombre' => 'Matriz de EPP',                        'key' => 'matriz_epp'],
            ['numero' => 6, 'nombre' => 'Matriz de Dotacion',                   'key' => 'matriz_dotacion'],
            ['numero' => 7, 'nombre' => 'Criterios de entrega y reposicion',    'key' => 'entrega_reposicion'],
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
     * Inyecta al contexto base la matriz de EPP y dotacion del cliente
     * para que la IA pueda personalizar las secciones textuales
     * (objetivo, alcance, marco_legal, responsabilidades, entrega_reposicion).
     *
     * Las secciones 5 y 6 (matriz_epp / matriz_dotacion) son tabla_dinamica
     * y se alimentan en la fase de render del documento, no en el prompt IA.
     */
    public function getContextoBase(array $cliente, ?array $contexto): string
    {
        $base = parent::getContextoBase($cliente, $contexto);

        $idCliente = (int)($cliente['id_cliente'] ?? 0);
        if ($idCliente <= 0) {
            return $base;
        }

        $clienteModel = new EppClienteModel();
        $epps      = $clienteModel->matrizCliente($idCliente, 'EPP');
        $dotacion  = $clienteModel->matrizCliente($idCliente, 'DOTACION');

        $bloque  = "\n\nMATRIZ DE EPP Y DOTACION DEL CLIENTE:\n";
        $bloque .= "Total EPP: " . count($epps) . " | Total prendas de dotacion: " . count($dotacion) . "\n";

        if (!empty($epps)) {
            $bloque .= "\nELEMENTOS DE PROTECCION PERSONAL (por categoria):\n";
            $porCatEpp = [];
            foreach ($epps as $e) {
                $porCatEpp[$e['categoria_nombre'] ?? 'Sin categoria'][] = $e;
            }
            foreach ($porCatEpp as $cat => $lista) {
                $bloque .= "* {$cat}:\n";
                foreach ($lista as $it) {
                    $norma = mb_substr(trim((string)($it['norma'] ?? '')), 0, 120);
                    $bloque .= "  - {$it['elemento']} (norma: {$norma})\n";
                }
            }
        }

        if (!empty($dotacion)) {
            $bloque .= "\nDOTACION:\n";
            foreach ($dotacion as $it) {
                $bloque .= "  - {$it['elemento']} (" . ($it['frecuencia_cambio'] ?? '') . ")\n";
            }
        }

        $bloque .= "\nUSA esta matriz como referencia al redactar las secciones textuales. "
                 . "No inventes EPP o dotacion que no esten en esta lista. "
                 . "Las secciones tabla 5 (Matriz de EPP) y 6 (Matriz de Dotacion) se inyectaran "
                 . "automaticamente en el render del documento, no las redactes tu.\n";

        return $base . $bloque;
    }

    protected function getPromptFallback(string $seccionKey, int $estandares): string
    {
        $prompts = [
            'objetivo'           => 'Redacta el objetivo de la Matriz de EPP y Dotacion para la empresa. 1 parrafo formal.',
            'alcance'            => 'Alcance: aplica a todos los trabajadores, contratistas y visitantes cuando corresponda, en todas las areas operativas y administrativas con riesgos.',
            'marco_legal'        => 'Marco legal colombiano sobre EPP y dotacion: CST Art. 230, Ley 9/1979, Resolucion 2400/1979, Decreto 1072/2015, Resolucion 0312/2019, NTC/ANSI aplicables. 2-3 parrafos.',
            'responsabilidades'  => 'Lista de responsabilidades: Empleador (suministro, calidad, reposicion), Responsable SST (seleccion por riesgo, capacitacion), Lideres de area (supervision), Trabajadores (uso correcto, reporte).',
            'entrega_reposicion' => 'Criterios de entrega y reposicion: entrega inicial al ingreso, registro individual, reposicion por frecuencia, reposicion anticipada por deterioro, capacitacion y constancia firmada.',
        ];
        return $prompts[$seccionKey] ?? "Genera el contenido para la seccion '{$seccionKey}' de la Matriz de EPP y Dotacion.";
    }
}
