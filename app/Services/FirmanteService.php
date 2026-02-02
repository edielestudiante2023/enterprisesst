<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use Config\Database;

/**
 * FirmanteService
 *
 * Servicio centralizado para manejar la lógica de firmantes de documentos.
 * Elimina la duplicación de código en vistas y controladores.
 *
 * Uso:
 *   $firmanteService = new FirmanteService();
 *   $firmantes = $firmanteService->obtenerFirmantesDocumento($tipoDocumento, $contexto, $cliente, $consultor);
 */
class FirmanteService
{
    protected BaseConnection $db;
    protected DocumentoConfigService $configService;

    public function __construct()
    {
        $this->db = Database::connect();
        $this->configService = new DocumentoConfigService();
    }

    /**
     * Obtiene los firmantes configurados para un documento con sus datos completos
     *
     * @param string $tipoDocumento Tipo de documento
     * @param array $contexto Contexto SST del cliente
     * @param array $cliente Datos del cliente
     * @param array|null $consultor Datos del consultor
     * @param array $firmasElectronicas Firmas electrónicas existentes
     * @return array Firmantes con datos completos para renderizar
     */
    public function obtenerFirmantesDocumento(
        string $tipoDocumento,
        array $contexto,
        array $cliente,
        ?array $consultor = null,
        array $firmasElectronicas = []
    ): array {
        // Obtener configuración de firmantes desde BD
        $firmantesConfig = $this->configService->obtenerFirmantesConfig($tipoDocumento);

        // Si no hay configuración en BD, usar lógica legacy
        if (empty($firmantesConfig)) {
            return $this->obtenerFirmantesLegacy($tipoDocumento, $contexto, $cliente, $consultor, $firmasElectronicas);
        }

        $firmantes = [];

        foreach ($firmantesConfig as $config) {
            $firmante = $this->construirFirmante(
                $config,
                $contexto,
                $cliente,
                $consultor,
                $firmasElectronicas
            );

            if ($firmante) {
                $firmantes[] = $firmante;
            }
        }

        return $firmantes;
    }

    /**
     * Construye los datos de un firmante específico
     */
    protected function construirFirmante(
        array $config,
        array $contexto,
        array $cliente,
        ?array $consultor,
        array $firmasElectronicas
    ): ?array {
        $tipo = $config['firmante_tipo'];

        $firmante = [
            'tipo' => $tipo,
            'columna_encabezado' => $config['columna_encabezado'],
            'rol_display' => $config['rol_display'],
            'orden' => (int)$config['orden'],
            'mostrar_licencia' => (bool)($config['mostrar_licencia'] ?? false),
            'mostrar_cedula' => (bool)($config['mostrar_cedula'] ?? false),
            'nombre' => '',
            'cargo' => '',
            'cedula' => '',
            'licencia' => '',
            'firma_imagen' => null,
            'firma_archivo' => null
        ];

        switch ($tipo) {
            case 'representante_legal':
                $firmante['nombre'] = $contexto['representante_legal_nombre']
                    ?? $cliente['nombre_rep_legal']
                    ?? $cliente['representante_legal']
                    ?? '';
                $firmante['cargo'] = 'Representante Legal';
                $firmante['cedula'] = $cliente['cedula_rep_legal'] ?? '';

                // Firma electrónica
                if (!empty($firmasElectronicas['representante_legal']['evidencia']['firma_imagen'])) {
                    $firmante['firma_imagen'] = $firmasElectronicas['representante_legal']['evidencia']['firma_imagen'];
                }
                break;

            case 'responsable_sst':
            case 'consultor_sst':
                $firmante['nombre'] = $consultor['nombre_consultor'] ?? '';
                $firmante['cargo'] = $tipo === 'responsable_sst' ? 'Responsable del SG-SST' : 'Consultor SST';
                $firmante['cedula'] = $consultor['cedula_consultor'] ?? '';
                $firmante['licencia'] = $consultor['numero_licencia'] ?? '';
                $firmante['firma_archivo'] = $consultor['firma_consultor'] ?? null;
                break;

            case 'delegado_sst':
                $firmante['nombre'] = $contexto['delegado_sst_nombre'] ?? '';
                $firmante['cargo'] = $contexto['delegado_sst_cargo'] ?? 'Delegado SST';
                $firmante['cedula'] = $contexto['delegado_sst_cedula'] ?? '';

                if (!empty($firmasElectronicas['delegado_sst']['evidencia']['firma_imagen'])) {
                    $firmante['firma_imagen'] = $firmasElectronicas['delegado_sst']['evidencia']['firma_imagen'];
                }
                break;

            case 'vigia_sst':
                $firmante['nombre'] = $contexto['vigia_sst_nombre'] ?? '';
                $firmante['cargo'] = 'Vigía de SST';
                $firmante['cedula'] = $contexto['vigia_sst_cedula'] ?? '';

                if (!empty($firmasElectronicas['vigia_sst']['evidencia']['firma_imagen'])) {
                    $firmante['firma_imagen'] = $firmasElectronicas['vigia_sst']['evidencia']['firma_imagen'];
                }
                break;

            case 'copasst':
                $firmante['nombre'] = $contexto['copasst_presidente_nombre'] ?? '';
                $firmante['cargo'] = 'Presidente COPASST';
                $firmante['cedula'] = $contexto['copasst_presidente_cedula'] ?? '';

                if (!empty($firmasElectronicas['copasst']['evidencia']['firma_imagen'])) {
                    $firmante['firma_imagen'] = $firmasElectronicas['copasst']['evidencia']['firma_imagen'];
                }
                break;

            default:
                return null;
        }

        return $firmante;
    }

    /**
     * Lógica legacy de firmantes (para compatibilidad durante migración)
     */
    protected function obtenerFirmantesLegacy(
        string $tipoDocumento,
        array $contexto,
        array $cliente,
        ?array $consultor,
        array $firmasElectronicas
    ): array {
        $estandares = $contexto['estandares_aplicables'] ?? 60;
        $requiereDelegado = !empty($contexto['requiere_delegado_sst']);

        // Obtener firmantes definidos en config legacy
        $firmantesDefinidos = $this->configService->obtenerFirmantes($tipoDocumento);

        // Si hay firmantes definidos específicamente, usarlos
        if (!empty($firmantesDefinidos)) {
            $firmantes = [];

            foreach ($firmantesDefinidos as $orden => $tipo) {
                $firmante = [
                    'tipo' => $tipo,
                    'orden' => $orden + 1,
                    'nombre' => '',
                    'cargo' => '',
                    'cedula' => '',
                    'licencia' => '',
                    'firma_imagen' => null,
                    'firma_archivo' => null,
                    'mostrar_licencia' => false,
                    'mostrar_cedula' => false
                ];

                switch ($tipo) {
                    case 'representante_legal':
                        $firmante['columna_encabezado'] = 'Aprobó / Representante Legal';
                        $firmante['nombre'] = $contexto['representante_legal_nombre']
                            ?? $cliente['nombre_rep_legal']
                            ?? $cliente['representante_legal'] ?? '';
                        $firmante['cargo'] = 'Representante Legal';
                        if (!empty($firmasElectronicas['representante_legal']['evidencia']['firma_imagen'])) {
                            $firmante['firma_imagen'] = $firmasElectronicas['representante_legal']['evidencia']['firma_imagen'];
                        }
                        break;

                    case 'responsable_sst':
                        $firmante['columna_encabezado'] = 'Elaboró / Responsable del SG-SST';
                        $firmante['nombre'] = $consultor['nombre_consultor'] ?? '';
                        $firmante['cargo'] = 'Responsable del SG-SST';
                        $firmante['licencia'] = $consultor['numero_licencia'] ?? '';
                        $firmante['firma_archivo'] = $consultor['firma_consultor'] ?? null;
                        $firmante['mostrar_licencia'] = true;
                        break;

                    case 'delegado_sst':
                        $firmante['columna_encabezado'] = 'Revisó / Delegado SST';
                        $firmante['nombre'] = $contexto['delegado_sst_nombre'] ?? '';
                        $firmante['cargo'] = $contexto['delegado_sst_cargo'] ?? 'Delegado SST';
                        if (!empty($firmasElectronicas['delegado_sst']['evidencia']['firma_imagen'])) {
                            $firmante['firma_imagen'] = $firmasElectronicas['delegado_sst']['evidencia']['firma_imagen'];
                        }
                        break;
                }

                $firmantes[] = $firmante;
            }

            // Ordenar por posición lógica (responsable_sst primero si existe)
            usort($firmantes, function ($a, $b) {
                $prioridad = ['responsable_sst' => 1, 'delegado_sst' => 2, 'vigia_sst' => 2, 'representante_legal' => 3];
                return ($prioridad[$a['tipo']] ?? 99) <=> ($prioridad[$b['tipo']] ?? 99);
            });

            return $firmantes;
        }

        // Lógica por estándares (fallback completo)
        return $this->obtenerFirmantesPorEstandares($estandares, $requiereDelegado, $contexto, $cliente, $consultor, $firmasElectronicas);
    }

    /**
     * Obtiene firmantes basados en número de estándares (lógica original)
     */
    protected function obtenerFirmantesPorEstandares(
        int $estandares,
        bool $requiereDelegado,
        array $contexto,
        array $cliente,
        ?array $consultor,
        array $firmasElectronicas
    ): array {
        $firmantes = [];

        // Siempre: Consultor SST (Elaboró)
        $firmantes[] = [
            'tipo' => 'consultor_sst',
            'columna_encabezado' => 'Elaboró / Consultor SST',
            'nombre' => $consultor['nombre_consultor'] ?? '',
            'cargo' => 'Consultor SST',
            'licencia' => $consultor['numero_licencia'] ?? '',
            'cedula' => $consultor['cedula_consultor'] ?? '',
            'firma_archivo' => $consultor['firma_consultor'] ?? null,
            'firma_imagen' => null,
            'mostrar_licencia' => true,
            'orden' => 1
        ];

        // Si estándares > 10 o requiere delegado: agregar revisor
        if ($estandares > 10 || $requiereDelegado) {
            if ($requiereDelegado) {
                $firmantes[] = [
                    'tipo' => 'delegado_sst',
                    'columna_encabezado' => 'Revisó / Delegado SST',
                    'nombre' => $contexto['delegado_sst_nombre'] ?? '',
                    'cargo' => $contexto['delegado_sst_cargo'] ?? 'Delegado SST',
                    'cedula' => $contexto['delegado_sst_cedula'] ?? '',
                    'licencia' => '',
                    'firma_archivo' => null,
                    'firma_imagen' => $firmasElectronicas['delegado_sst']['evidencia']['firma_imagen'] ?? null,
                    'mostrar_licencia' => false,
                    'orden' => 2
                ];
            } else {
                $cargoRevisor = $estandares <= 21 ? 'Vigía de SST' : 'COPASST';
                $firmantes[] = [
                    'tipo' => $estandares <= 21 ? 'vigia_sst' : 'copasst',
                    'columna_encabezado' => "Revisó / $cargoRevisor",
                    'nombre' => '',
                    'cargo' => $cargoRevisor,
                    'cedula' => '',
                    'licencia' => '',
                    'firma_archivo' => null,
                    'firma_imagen' => null,
                    'mostrar_licencia' => false,
                    'orden' => 2
                ];
            }
        }

        // Siempre: Representante Legal (Aprobó)
        $firmantes[] = [
            'tipo' => 'representante_legal',
            'columna_encabezado' => 'Aprobó / Representante Legal',
            'nombre' => $contexto['representante_legal_nombre']
                ?? $cliente['nombre_rep_legal']
                ?? $cliente['representante_legal'] ?? '',
            'cargo' => 'Representante Legal',
            'cedula' => $cliente['cedula_rep_legal'] ?? '',
            'licencia' => '',
            'firma_archivo' => null,
            'firma_imagen' => $firmasElectronicas['representante_legal']['evidencia']['firma_imagen'] ?? null,
            'mostrar_licencia' => false,
            'orden' => count($firmantes) + 1
        ];

        return $firmantes;
    }

    /**
     * Determina el número de columnas para la tabla de firmas
     */
    public function obtenerNumeroColumnas(array $firmantes): int
    {
        return count($firmantes);
    }

    /**
     * Calcula el ancho de cada columna según el número de firmantes
     */
    public function obtenerAnchoColumna(int $numFirmantes): string
    {
        return match ($numFirmantes) {
            1 => '100%',
            2 => '50%',
            3 => '33.33%',
            default => (100 / $numFirmantes) . '%'
        };
    }
}
