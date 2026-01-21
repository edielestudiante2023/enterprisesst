<?php

namespace App\Models;

use CodeIgniter\Model;

class SessionModel extends Model
{
    protected $table = 'tbl_sesiones_usuario';
    protected $primaryKey = 'id_sesion';
    protected $allowedFields = [
        'id_usuario',
        'inicio_sesion',
        'fin_sesion',
        'duracion_segundos',
        'ip_address',
        'user_agent',
        'estado'
    ];

    /**
     * Iniciar una nueva sesión para el usuario
     */
    public function iniciarSesion(int $idUsuario, ?string $ip = null, ?string $userAgent = null): int
    {
        // Cerrar sesiones activas anteriores del mismo usuario
        $this->cerrarSesionesActivas($idUsuario);

        $data = [
            'id_usuario' => $idUsuario,
            'inicio_sesion' => date('Y-m-d H:i:s'),
            'ip_address' => $ip,
            'user_agent' => $userAgent ? substr($userAgent, 0, 500) : null,
            'estado' => 'activa'
        ];

        $this->insert($data);
        return $this->getInsertID();
    }

    /**
     * Cerrar sesión activa del usuario
     */
    public function cerrarSesion(int $idSesion): bool
    {
        $sesion = $this->find($idSesion);

        if (!$sesion || $sesion['estado'] !== 'activa') {
            return false;
        }

        $inicio = strtotime($sesion['inicio_sesion']);
        $fin = time();
        $duracion = $fin - $inicio;

        return $this->update($idSesion, [
            'fin_sesion' => date('Y-m-d H:i:s'),
            'duracion_segundos' => $duracion,
            'estado' => 'cerrada'
        ]);
    }

    /**
     * Cerrar sesiones activas de un usuario (cuando inicia nueva sesión)
     */
    public function cerrarSesionesActivas(int $idUsuario): void
    {
        $sesionesActivas = $this->where('id_usuario', $idUsuario)
                                ->where('estado', 'activa')
                                ->findAll();

        foreach ($sesionesActivas as $sesion) {
            $inicio = strtotime($sesion['inicio_sesion']);
            $fin = time();
            $duracion = $fin - $inicio;

            $this->update($sesion['id_sesion'], [
                'fin_sesion' => date('Y-m-d H:i:s'),
                'duracion_segundos' => $duracion,
                'estado' => 'cerrada'
            ]);
        }
    }

    /**
     * Obtener sesión activa de un usuario
     */
    public function getSesionActiva(int $idUsuario): ?array
    {
        return $this->where('id_usuario', $idUsuario)
                    ->where('estado', 'activa')
                    ->first();
    }

    /**
     * Obtener resumen de consumo de todos los usuarios
     */
    public function getResumenConsumo(?string $fechaInicio = null, ?string $fechaFin = null): array
    {
        $builder = $this->db->table('tbl_usuarios u');
        $builder->select('
            u.id_usuario,
            u.nombre_completo,
            u.email,
            u.tipo_usuario,
            COUNT(s.id_sesion) as total_sesiones,
            COALESCE(SUM(s.duracion_segundos), 0) as tiempo_total_segundos,
            SEC_TO_TIME(COALESCE(SUM(s.duracion_segundos), 0)) as tiempo_total_formato,
            MAX(s.inicio_sesion) as ultima_sesion,
            COALESCE(AVG(s.duracion_segundos), 0) as promedio_duracion_segundos
        ');
        $builder->join('tbl_sesiones_usuario s', 'u.id_usuario = s.id_usuario AND s.estado != "activa"', 'left');

        if ($fechaInicio) {
            $builder->where('s.inicio_sesion >=', $fechaInicio . ' 00:00:00');
        }
        if ($fechaFin) {
            $builder->where('s.inicio_sesion <=', $fechaFin . ' 23:59:59');
        }

        $builder->groupBy('u.id_usuario, u.nombre_completo, u.email, u.tipo_usuario');
        $builder->orderBy('tiempo_total_segundos', 'DESC');

        return $builder->get()->getResultArray();
    }

    /**
     * Obtener historial de sesiones de un usuario
     */
    public function getHistorialUsuario(int $idUsuario, int $limit = 50): array
    {
        return $this->where('id_usuario', $idUsuario)
                    ->orderBy('inicio_sesion', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Obtener estadísticas generales
     */
    public function getEstadisticasGenerales(?string $fechaInicio = null, ?string $fechaFin = null): array
    {
        $builder = $this->db->table('tbl_sesiones_usuario');
        $builder->select('
            COUNT(*) as total_sesiones,
            COUNT(DISTINCT id_usuario) as usuarios_unicos,
            COALESCE(SUM(duracion_segundos), 0) as tiempo_total_segundos,
            COALESCE(AVG(duracion_segundos), 0) as promedio_duracion
        ');
        $builder->where('estado !=', 'activa');

        if ($fechaInicio) {
            $builder->where('inicio_sesion >=', $fechaInicio . ' 00:00:00');
        }
        if ($fechaFin) {
            $builder->where('inicio_sesion <=', $fechaFin . ' 23:59:59');
        }

        return $builder->get()->getRowArray();
    }

    /**
     * Obtener sesiones por día para gráfica
     */
    public function getSesionesPorDia(?string $fechaInicio = null, ?string $fechaFin = null): array
    {
        $builder = $this->db->table('tbl_sesiones_usuario');
        $builder->select('
            DATE(inicio_sesion) as fecha,
            COUNT(*) as total_sesiones,
            COUNT(DISTINCT id_usuario) as usuarios_unicos,
            COALESCE(SUM(duracion_segundos), 0) as tiempo_total
        ');
        $builder->where('estado !=', 'activa');

        if ($fechaInicio) {
            $builder->where('inicio_sesion >=', $fechaInicio . ' 00:00:00');
        }
        if ($fechaFin) {
            $builder->where('inicio_sesion <=', $fechaFin . ' 23:59:59');
        }

        $builder->groupBy('DATE(inicio_sesion)');
        $builder->orderBy('fecha', 'ASC');

        return $builder->get()->getResultArray();
    }

    /**
     * Marcar sesiones expiradas (para ejecutar periódicamente)
     * Sesiones activas por más de 8 horas se consideran expiradas
     */
    public function marcarSesionesExpiradas(): int
    {
        $limite = date('Y-m-d H:i:s', strtotime('-8 hours'));

        $sesionesExpiradas = $this->where('estado', 'activa')
                                  ->where('inicio_sesion <', $limite)
                                  ->findAll();

        $count = 0;
        foreach ($sesionesExpiradas as $sesion) {
            // Asumimos duración máxima de 8 horas para sesiones expiradas
            $this->update($sesion['id_sesion'], [
                'fin_sesion' => date('Y-m-d H:i:s', strtotime($sesion['inicio_sesion'] . ' +8 hours')),
                'duracion_segundos' => 8 * 60 * 60, // 8 horas en segundos
                'estado' => 'expirada'
            ]);
            $count++;
        }

        return $count;
    }
}
