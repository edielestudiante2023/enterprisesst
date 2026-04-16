<?php

namespace App\Models;

use Config\Database;

/**
 * Acceso unificado y cacheado a los 7 catalogos GTC 45.
 * No extiende Model porque consulta multiples tablas.
 */
class Gtc45CatalogoModel
{
    protected static array $cache = [];

    protected function load(string $tabla, string $orderBy = 'orden'): array
    {
        if (!isset(self::$cache[$tabla])) {
            $db = Database::connect();
            self::$cache[$tabla] = $db->table($tabla)
                ->orderBy($orderBy, 'ASC')
                ->get()
                ->getResultArray();
        }
        return self::$cache[$tabla];
    }

    public function clasificaciones(): array
    {
        return $this->load('tbl_gtc45_clasificacion_peligro');
    }

    public function peligros(?int $idClasificacion = null): array
    {
        $todos = $this->load('tbl_gtc45_peligro_catalogo', 'nombre');
        if ($idClasificacion === null) return $todos;
        return array_values(array_filter(
            $todos,
            fn($p) => (int)$p['id_clasificacion'] === $idClasificacion
        ));
    }

    public function nivelesDeficiencia(): array    { return $this->load('tbl_gtc45_nivel_deficiencia'); }
    public function nivelesExposicion(): array     { return $this->load('tbl_gtc45_nivel_exposicion'); }
    public function nivelesConsecuencia(): array   { return $this->load('tbl_gtc45_nivel_consecuencia'); }
    public function nivelesProbabilidad(): array   { return $this->load('tbl_gtc45_nivel_probabilidad'); }
    public function nivelesRiesgo(): array         { return $this->load('tbl_gtc45_nivel_riesgo'); }

    /**
     * Interpreta un valor NP contra los rangos de nivel_probabilidad.
     */
    public function interpretarNP(int $np): ?array
    {
        foreach ($this->nivelesProbabilidad() as $n) {
            if ($np >= (int)$n['rango_min'] && $np <= (int)$n['rango_max']) {
                return $n;
            }
        }
        return null;
    }

    /**
     * Interpreta un valor NR contra los rangos de nivel_riesgo (I-IV).
     */
    public function interpretarNR(int $nr): ?array
    {
        foreach ($this->nivelesRiesgo() as $n) {
            if ($nr >= (int)$n['rango_min'] && $nr <= (int)$n['rango_max']) {
                return $n;
            }
        }
        return null;
    }

    /**
     * Bundle completo para inyectar al frontend como window.GTC45_CATALOGO.
     */
    public function bundleFrontend(): array
    {
        return [
            'clasificaciones'   => $this->clasificaciones(),
            'peligros'          => $this->peligros(),
            'nd'                => $this->nivelesDeficiencia(),
            'ne'                => $this->nivelesExposicion(),
            'nc'                => $this->nivelesConsecuencia(),
            'np'                => $this->nivelesProbabilidad(),
            'nr'                => $this->nivelesRiesgo(),
        ];
    }
}
