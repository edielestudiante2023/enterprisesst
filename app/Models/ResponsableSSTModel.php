<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Modelo para gestionar responsables del SG-SST
 */
class ResponsableSSTModel extends Model
{
    protected $table = 'tbl_cliente_responsables_sst';
    protected $primaryKey = 'id_responsable';
    protected $allowedFields = [
        'id_cliente', 'tipo_rol', 'nombre_completo', 'tipo_documento',
        'numero_documento', 'cargo', 'email', 'telefono',
        'licencia_sst_numero', 'licencia_sst_vigencia', 'formacion_sst',
        'fecha_inicio', 'fecha_fin', 'acta_nombramiento',
        'activo', 'observaciones', 'created_by', 'updated_by'
    ];

    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Mapa de tipos de rol a nombres legibles
     */
    public const TIPOS_ROL = [
        'representante_legal' => 'Representante Legal',
        'responsable_sgsst' => 'Responsable al interior de la empresa del SG-SST',
        'vigia_sst' => 'Vigía de SST',
        'vigia_sst_suplente' => 'Vigía de SST (Suplente)',
        'copasst_presidente' => 'COPASST - Presidente',
        'copasst_secretario' => 'COPASST - Secretario',
        'copasst_representante_empleador' => 'COPASST - Representante Empleador',
        'copasst_representante_trabajadores' => 'COPASST - Representante Trabajadores',
        'copasst_suplente_empleador' => 'COPASST - Suplente Empleador',
        'copasst_suplente_trabajadores' => 'COPASST - Suplente Trabajadores',
        'comite_convivencia_presidente' => 'Comité Convivencia - Presidente',
        'comite_convivencia_secretario' => 'Comité Convivencia - Secretario',
        'comite_convivencia_representante_empleador' => 'Comité Convivencia - Representante Empleador',
        'comite_convivencia_representante_trabajadores' => 'Comité Convivencia - Representante Trabajadores',
        'comite_convivencia_suplente_empleador' => 'Comité Convivencia - Suplente Empleador',
        'comite_convivencia_suplente_trabajadores' => 'Comité Convivencia - Suplente Trabajadores',
        'brigada_coordinador' => 'Brigada - Coordinador',
        'brigada_lider_evacuacion' => 'Brigada - Líder Evacuación',
        'brigada_lider_primeros_auxilios' => 'Brigada - Líder Primeros Auxilios',
        'brigada_lider_control_incendios' => 'Brigada - Líder Control Incendios',
        'otro' => 'Otro'
    ];

    /**
     * Roles obligatorios según estándares aplicables
     *
     * Según Resolución 0312/2019:
     * - 7 estándares: Vigía SST (< 10 trabajadores, Riesgo I-III)
     * - 21 estándares: COPASST obligatorio (10-50 trabajadores, Riesgo I-III)
     * - 60 estándares: COPASST + Comité Convivencia (> 50 trabajadores o Riesgo IV-V)
     *
     * Nota: Comité de Convivencia es obligatorio para TODAS las empresas según Ley 1010/2006
     * y Resolución 652/2012, pero para simplificar solo se valida como obligatorio en 21 y 60.
     */
    public const ROLES_OBLIGATORIOS = [
        7 => ['representante_legal', 'vigia_sst'],
        21 => ['representante_legal', 'responsable_sgsst', 'copasst_presidente', 'copasst_secretario', 'comite_convivencia_presidente', 'comite_convivencia_secretario', 'brigada_coordinador'],
        60 => ['representante_legal', 'responsable_sgsst', 'copasst_presidente', 'copasst_secretario', 'comite_convivencia_presidente', 'comite_convivencia_secretario', 'brigada_coordinador']
    ];

    /**
     * Obtiene todos los responsables activos de un cliente
     */
    public function getByCliente(int $idCliente, bool $soloActivos = true): array
    {
        $builder = $this->where('id_cliente', $idCliente);

        if ($soloActivos) {
            $builder->where('activo', 1);
        }

        return $builder->orderBy('tipo_rol', 'ASC')->findAll();
    }

    /**
     * Obtiene responsables agrupados por categoría
     */
    public function getByClienteAgrupados(int $idCliente): array
    {
        $responsables = $this->getByCliente($idCliente);

        $grupos = [
            'direccion' => [
                'titulo' => 'Alta Dirección y Responsable SST',
                'roles' => ['representante_legal', 'responsable_sgsst'],
                'items' => []
            ],
            'vigia' => [
                'titulo' => 'Vigía de SST',
                'roles' => ['vigia_sst', 'vigia_sst_suplente'],
                'items' => []
            ],
            'copasst' => [
                'titulo' => 'COPASST',
                'roles' => ['copasst_presidente', 'copasst_secretario', 'copasst_representante_empleador', 'copasst_representante_trabajadores', 'copasst_suplente_empleador', 'copasst_suplente_trabajadores'],
                'items' => []
            ],
            'convivencia' => [
                'titulo' => 'Comité de Convivencia Laboral',
                'roles' => ['comite_convivencia_presidente', 'comite_convivencia_secretario', 'comite_convivencia_representante_empleador', 'comite_convivencia_representante_trabajadores', 'comite_convivencia_suplente_empleador', 'comite_convivencia_suplente_trabajadores'],
                'items' => []
            ],
            'brigada' => [
                'titulo' => 'Brigada de Emergencias',
                'roles' => ['brigada_coordinador', 'brigada_lider_evacuacion', 'brigada_lider_primeros_auxilios', 'brigada_lider_control_incendios'],
                'items' => []
            ],
            'otros' => [
                'titulo' => 'Otros',
                'roles' => ['otro'],
                'items' => []
            ]
        ];

        foreach ($responsables as $resp) {
            $resp['nombre_rol'] = self::TIPOS_ROL[$resp['tipo_rol']] ?? $resp['tipo_rol'];

            foreach ($grupos as $key => &$grupo) {
                if (in_array($resp['tipo_rol'], $grupo['roles'])) {
                    $grupo['items'][] = $resp;
                    break;
                }
            }
        }

        return $grupos;
    }

    /**
     * Obtiene un responsable por tipo de rol
     */
    public function getByTipoRol(int $idCliente, string $tipoRol): ?array
    {
        return $this->where('id_cliente', $idCliente)
                    ->where('tipo_rol', $tipoRol)
                    ->where('activo', 1)
                    ->first();
    }

    /**
     * Obtiene el representante legal del cliente
     */
    public function getRepresentanteLegal(int $idCliente): ?array
    {
        return $this->getByTipoRol($idCliente, 'representante_legal');
    }

    /**
     * Obtiene el responsable del SG-SST
     */
    public function getResponsableSGSST(int $idCliente): ?array
    {
        return $this->getByTipoRol($idCliente, 'responsable_sgsst');
    }

    /**
     * Obtiene el Vigía SST (para empresas de 7 estándares)
     */
    public function getVigiaST(int $idCliente): ?array
    {
        return $this->getByTipoRol($idCliente, 'vigia_sst');
    }

    /**
     * Obtiene miembros del COPASST
     */
    public function getMiembrosCopasst(int $idCliente): array
    {
        return $this->where('id_cliente', $idCliente)
                    ->whereIn('tipo_rol', [
                        'copasst_presidente', 'copasst_secretario',
                        'copasst_representante_empleador', 'copasst_representante_trabajadores',
                        'copasst_suplente_empleador', 'copasst_suplente_trabajadores'
                    ])
                    ->where('activo', 1)
                    ->orderBy('tipo_rol', 'ASC')
                    ->findAll();
    }

    /**
     * Verifica si están completos los roles obligatorios según estándares
     */
    public function verificarRolesObligatorios(int $idCliente, int $estandares): array
    {
        $nivel = $estandares <= 7 ? 7 : ($estandares <= 21 ? 21 : 60);
        $rolesObligatorios = self::ROLES_OBLIGATORIOS[$nivel];

        $responsables = $this->getByCliente($idCliente);
        $rolesExistentes = array_column($responsables, 'tipo_rol');

        $faltantes = [];
        $completos = [];

        foreach ($rolesObligatorios as $rol) {
            if (in_array($rol, $rolesExistentes)) {
                $completos[] = [
                    'rol' => $rol,
                    'nombre' => self::TIPOS_ROL[$rol]
                ];
            } else {
                $faltantes[] = [
                    'rol' => $rol,
                    'nombre' => self::TIPOS_ROL[$rol]
                ];
            }
        }

        return [
            'completo' => empty($faltantes),
            'faltantes' => $faltantes,
            'completos' => $completos,
            'porcentaje' => count($rolesObligatorios) > 0
                ? round((count($completos) / count($rolesObligatorios)) * 100)
                : 100
        ];
    }

    /**
     * Genera el contenido formateado de responsables para documentos
     */
    public function generarContenidoParaDocumento(int $idCliente, int $estandares): string
    {
        $responsables = $this->getByCliente($idCliente);

        if (empty($responsables)) {
            return "[PENDIENTE: Registrar responsables del SG-SST en el módulo de Responsables]";
        }

        $contenido = "";

        // Representante Legal
        $repLegal = $this->getRepresentanteLegal($idCliente);
        if ($repLegal) {
            $contenido .= "**Representante Legal**\n";
            $contenido .= "- Nombre: {$repLegal['nombre_completo']}\n";
            $contenido .= "- Documento: {$repLegal['tipo_documento']} {$repLegal['numero_documento']}\n";
            $contenido .= "- Cargo: {$repLegal['cargo']}\n\n";
        }

        // Responsable SG-SST
        $respSST = $this->getResponsableSGSST($idCliente);
        if ($respSST) {
            $contenido .= "**Responsable del SG-SST**\n";
            $contenido .= "- Nombre: {$respSST['nombre_completo']}\n";
            $contenido .= "- Documento: {$respSST['tipo_documento']} {$respSST['numero_documento']}\n";
            $contenido .= "- Cargo: {$respSST['cargo']}\n";
            if (!empty($respSST['licencia_sst_numero'])) {
                $contenido .= "- Licencia SST: {$respSST['licencia_sst_numero']}";
                if (!empty($respSST['licencia_sst_vigencia'])) {
                    $contenido .= " (Vigente hasta: {$respSST['licencia_sst_vigencia']})";
                }
                $contenido .= "\n";
            }
            if (!empty($respSST['formacion_sst'])) {
                $contenido .= "- Formación: {$respSST['formacion_sst']}\n";
            }
            $contenido .= "\n";
        }

        // Vigía SST o COPASST según estándares
        if ($estandares <= 10) {
            // Vigía SST para empresas pequeñas
            $vigia = $this->getVigiaST($idCliente);
            if ($vigia) {
                $contenido .= "**Vigía de Seguridad y Salud en el Trabajo**\n";
                $contenido .= "- Nombre: {$vigia['nombre_completo']}\n";
                $contenido .= "- Documento: {$vigia['tipo_documento']} {$vigia['numero_documento']}\n";
                $contenido .= "- Cargo: {$vigia['cargo']}\n\n";
            }
        } else {
            // COPASST para empresas medianas/grandes
            $copasst = $this->getMiembrosCopasst($idCliente);
            if (!empty($copasst)) {
                $contenido .= "**COPASST (Comité Paritario de Seguridad y Salud en el Trabajo)**\n\n";
                foreach ($copasst as $miembro) {
                    $nombreRol = self::TIPOS_ROL[$miembro['tipo_rol']] ?? $miembro['tipo_rol'];
                    $contenido .= "- {$nombreRol}: {$miembro['nombre_completo']} ({$miembro['cargo']})\n";
                }
                $contenido .= "\n";
            }
        }

        return $contenido;
    }

    /**
     * Obtiene responsables según el tipo de comité
     * Mapea los códigos de comité a los roles de responsables correspondientes
     */
    public function getByTipoComite(int $idCliente, string $codigoComite): array
    {
        // Mapeo de tipo de comité a roles de responsables
        $mapeoRoles = [
            'COPASST' => [
                'copasst_presidente', 'copasst_secretario',
                'copasst_representante_empleador', 'copasst_representante_trabajadores',
                'copasst_suplente_empleador', 'copasst_suplente_trabajadores'
            ],
            'COCOLAB' => [
                'comite_convivencia_presidente', 'comite_convivencia_secretario',
                'comite_convivencia_representante_empleador', 'comite_convivencia_representante_trabajadores',
                'comite_convivencia_suplente_empleador', 'comite_convivencia_suplente_trabajadores'
            ],
            'BRIGADA' => [
                'brigada_coordinador', 'brigada_lider_evacuacion',
                'brigada_lider_primeros_auxilios', 'brigada_lider_control_incendios'
            ],
            'VIGIA' => [
                'vigia_sst', 'vigia_sst_suplente'
            ]
        ];

        // Normalizar el código del comité
        $codigo = strtoupper($codigoComite);

        // Si no hay mapeo específico, retornar todos los responsables activos
        if (!isset($mapeoRoles[$codigo])) {
            return $this->getByCliente($idCliente);
        }

        $roles = $mapeoRoles[$codigo];

        $responsables = $this->where('id_cliente', $idCliente)
                             ->whereIn('tipo_rol', $roles)
                             ->where('activo', 1)
                             ->orderBy('tipo_rol', 'ASC')
                             ->findAll();

        // Agregar el nombre legible del rol
        foreach ($responsables as &$resp) {
            $resp['nombre_rol'] = self::TIPOS_ROL[$resp['tipo_rol']] ?? $resp['tipo_rol'];
        }

        return $responsables;
    }

    /**
     * Obtiene todos los responsables disponibles para asignar a un comité
     * Incluye todos los responsables del cliente que podrían participar
     */
    public function getDisponiblesParaComite(int $idCliente): array
    {
        $responsables = $this->getByCliente($idCliente);

        // Agregar el nombre legible del rol
        foreach ($responsables as &$resp) {
            $resp['nombre_rol'] = self::TIPOS_ROL[$resp['tipo_rol']] ?? $resp['tipo_rol'];
        }

        return $responsables;
    }

    /**
     * Migra datos del contexto antiguo a la nueva tabla
     */
    public function migrarDesdeContexto(int $idCliente, array $contexto): array
    {
        $migrados = [];

        // Representante Legal
        if (!empty($contexto['representante_legal_nombre'])) {
            $existe = $this->getByTipoRol($idCliente, 'representante_legal');
            if (!$existe) {
                $this->insert([
                    'id_cliente' => $idCliente,
                    'tipo_rol' => 'representante_legal',
                    'nombre_completo' => $contexto['representante_legal_nombre'],
                    'numero_documento' => $contexto['representante_legal_cedula'] ?? '',
                    'cargo' => $contexto['representante_legal_cargo'] ?? 'Representante Legal',
                    'email' => $contexto['representante_legal_email'] ?? null,
                    'activo' => 1
                ]);
                $migrados[] = 'representante_legal';
            }
        }

        // Responsable SG-SST
        if (!empty($contexto['responsable_sgsst_nombre'])) {
            $existe = $this->getByTipoRol($idCliente, 'responsable_sgsst');
            if (!$existe) {
                $this->insert([
                    'id_cliente' => $idCliente,
                    'tipo_rol' => 'responsable_sgsst',
                    'nombre_completo' => $contexto['responsable_sgsst_nombre'],
                    'numero_documento' => $contexto['responsable_sgsst_cedula'] ?? '',
                    'cargo' => $contexto['responsable_sgsst_cargo'] ?? 'Responsable SG-SST',
                    'licencia_sst_numero' => $contexto['licencia_sst_numero'] ?? null,
                    'licencia_sst_vigencia' => $contexto['licencia_sst_vigencia'] ?? null,
                    'activo' => 1
                ]);
                $migrados[] = 'responsable_sgsst';
            }
        }

        // Delegado SST (como Vigía si aplica)
        if (!empty($contexto['delegado_sst_nombre'])) {
            $existe = $this->getByTipoRol($idCliente, 'vigia_sst');
            if (!$existe) {
                $this->insert([
                    'id_cliente' => $idCliente,
                    'tipo_rol' => 'vigia_sst',
                    'nombre_completo' => $contexto['delegado_sst_nombre'],
                    'numero_documento' => $contexto['delegado_sst_cedula'] ?? '',
                    'cargo' => $contexto['delegado_sst_cargo'] ?? 'Vigía SST',
                    'email' => $contexto['delegado_sst_email'] ?? null,
                    'activo' => 1
                ]);
                $migrados[] = 'vigia_sst';
            }
        }

        return $migrados;
    }
}
