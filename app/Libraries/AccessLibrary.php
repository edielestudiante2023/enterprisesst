<?php

namespace App\Libraries;

/**
 * Catálogo estático de accesos/documentos del SG-SST para el módulo PDF Unificado.
 * Reemplaza tabla de BD — los 19 documentos que se incluyen en el PDF fusionado.
 */
class AccessLibrary
{
    /**
     * Catálogo completo de documentos del SG-SST
     * id_acceso => [nombre, dimension PHVA, vista relativa]
     */
    private static array $accesses = [
        1  => ['name' => 'Asignación de Responsable',              'dimension' => 'Planear',    'view' => 'client/sgsst/1planear/p1_1_1asignacion_responsable'],
        2  => ['name' => 'Asignación de Responsabilidades',        'dimension' => 'Planear',    'view' => 'client/sgsst/1planear/p1_1_2asignacion_responsabilidades'],
        3  => ['name' => 'Asignación de Vigía',                    'dimension' => 'Hacer',      'view' => 'client/sgsst/1planear/p1_1_3vigia'],
        4  => ['name' => 'Exoneración de Comité de Convivencia',   'dimension' => 'Verificar',  'view' => 'client/sgsst/1planear/p1_1_4exoneracion_cocolab'],
        5  => ['name' => 'Registro de Asistencia',                 'dimension' => 'Hacer',      'view' => 'client/sgsst/1planear/p1_1_5registro_asistencia'],
        15 => ['name' => 'Programa de Capacitación',               'dimension' => 'Planear',    'view' => 'client/sgsst/1planear/p1_2_1prgcapacitacion'],
        16 => ['name' => 'Programa de Inducción',                  'dimension' => 'Planear',    'view' => 'client/sgsst/1planear/p1_2_2prginduccion'],
        17 => ['name' => 'Evaluación de Inducción/Reinducción',    'dimension' => 'Verificar',  'view' => 'client/sgsst/1planear/p1_2_3ftevaluacioninduccion'],
        18 => ['name' => 'Política de SST',                        'dimension' => 'Planear',    'view' => 'client/sgsst/1planear/p2_1_1politicasst'],
        19 => ['name' => 'Política de Alcohol',                    'dimension' => 'Planear',    'view' => 'client/sgsst/1planear/p2_1_2politicaalcohol'],
        20 => ['name' => 'Política de Emergencias',                'dimension' => 'Planear',    'view' => 'client/sgsst/1planear/p2_1_3politicaemergencias'],
        23 => ['name' => 'Reglamento de Higiene',                  'dimension' => 'Planear',    'view' => 'client/sgsst/1planear/p2_1_6reghigsegind'],
        24 => ['name' => 'Objetivos del SG-SST',                   'dimension' => 'Planear',    'view' => 'client/sgsst/1planear/p2_2_1objetivos'],
        25 => ['name' => 'Documentos del SG-SST',                  'dimension' => 'Planear',    'view' => 'client/sgsst/1planear/p2_5_1documentacion'],
        26 => ['name' => 'Rendición de Cuentas',                   'dimension' => 'Planear',    'view' => 'client/sgsst/1planear/p2_5_2rendiciondecuentas'],
        28 => ['name' => 'Manual de Proveedores',                  'dimension' => 'Hacer',      'view' => 'client/sgsst/1planear/p2_5_4manproveedores'],
        31 => ['name' => 'Reporte de Accidente',                   'dimension' => 'Hacer',      'view' => 'client/sgsst/1planear/h1_1_3repoaccidente'],
        36 => ['name' => 'Identificación de Peligros y Riesgos',   'dimension' => 'Verificar',  'view' => 'client/sgsst/1planear/h1_1_7identfpeligriesg'],
    ];

    /**
     * Los mismos 19 IDs aplican para todos los estándares (Mensual, Bimensual, Trimestral, Proyecto).
     */
    public static function getAccessesByStandard(string $standard = 'Mensual'): array
    {
        return array_keys(self::$accesses);
    }

    /**
     * Obtiene info de un acceso por su ID.
     */
    public static function getAccess(int $idAcceso): ?array
    {
        return self::$accesses[$idAcceso] ?? null;
    }

    /**
     * Retorna todos los accesos.
     */
    public static function getAll(): array
    {
        return self::$accesses;
    }

    /**
     * Retorna accesos agrupados por dimensión PHVA, ordenados: Planear → Hacer → Verificar → Actuar.
     */
    public static function getGroupedByDimension(): array
    {
        $order = ['Planear', 'Hacer', 'Verificar', 'Actuar'];
        $grouped = [];

        foreach ($order as $dim) {
            $grouped[$dim] = [];
        }

        foreach (self::$accesses as $id => $access) {
            $grouped[$access['dimension']][$id] = $access;
        }

        // Remove empty dimensions
        return array_filter($grouped);
    }
}
