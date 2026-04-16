<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\Traits\TenantScopedModel;

class CicloVisitaModel extends Model
{
    use TenantScopedModel;

    protected $table         = 'tbl_ciclos_visita';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $allowedFields = [
        'id_cliente', 'id_consultor', 'anio', 'mes_esperado', 'estandar',
        'fecha_agendada', 'id_agendamiento', 'fecha_acta', 'id_acta',
        'estatus_agenda', 'estatus_mes',
        'alerta_enviada', 'confirmacion_enviada', 'observaciones',
    ];

    /**
     * Todos los ciclos con datos de cliente y consultor
     */
    public function getAllConJoins()
    {
        return $this->select('tbl_ciclos_visita.*,
                c.nombre_cliente, c.correo_cliente, c.consultor_externo, c.email_consultor_externo, c.estandares,
                co.nombre_consultor, co.correo_consultor')
            ->join('tbl_clientes c', 'c.id_cliente = tbl_ciclos_visita.id_cliente', 'left')
            ->join('tbl_consultor co', 'co.id_consultor = tbl_ciclos_visita.id_consultor', 'left')
            ->orderBy('tbl_ciclos_visita.anio', 'DESC')
            ->orderBy('tbl_ciclos_visita.mes_esperado', 'ASC')
            ->orderBy('c.nombre_cliente', 'ASC')
            ->findAll();
    }

    /**
     * Filtrado por consultor
     */
    public function getByConsultor(int $idConsultor)
    {
        return $this->select('tbl_ciclos_visita.*,
                c.nombre_cliente, c.correo_cliente, c.consultor_externo, c.email_consultor_externo,
                co.nombre_consultor, co.correo_consultor')
            ->join('tbl_clientes c', 'c.id_cliente = tbl_ciclos_visita.id_cliente', 'left')
            ->join('tbl_consultor co', 'co.id_consultor = tbl_ciclos_visita.id_consultor', 'left')
            ->where('tbl_ciclos_visita.id_consultor', $idConsultor)
            ->orderBy('tbl_ciclos_visita.anio', 'DESC')
            ->orderBy('tbl_ciclos_visita.mes_esperado', 'ASC')
            ->findAll();
    }

    /**
     * Filtrado por mes y año
     */
    public function getByMesAnio(int $mes, int $anio)
    {
        return $this->select('tbl_ciclos_visita.*,
                c.nombre_cliente, c.correo_cliente, c.consultor_externo, c.email_consultor_externo,
                co.nombre_consultor, co.correo_consultor')
            ->join('tbl_clientes c', 'c.id_cliente = tbl_ciclos_visita.id_cliente', 'left')
            ->join('tbl_consultor co', 'co.id_consultor = tbl_ciclos_visita.id_consultor', 'left')
            ->where('tbl_ciclos_visita.mes_esperado', $mes)
            ->where('tbl_ciclos_visita.anio', $anio)
            ->orderBy('c.nombre_cliente', 'ASC')
            ->findAll();
    }

    /**
     * Vincular agendamiento a su ciclo (por cliente + mes/año de la fecha_visita)
     */
    public function vincularAgendamiento(int $idCliente, string $fechaVisita, int $idAgendamiento): void
    {
        $mes  = (int)date('n', strtotime($fechaVisita));
        $anio = (int)date('Y', strtotime($fechaVisita));

        $ciclo = $this->where('id_cliente', $idCliente)
            ->where('mes_esperado', $mes)
            ->where('anio', $anio)
            ->first();

        if ($ciclo) {
            $this->update($ciclo['id'], [
                'fecha_agendada'  => $fechaVisita,
                'id_agendamiento' => $idAgendamiento,
            ]);
        }
    }

    /**
     * Desvincular agendamiento de su ciclo (al cancelar)
     */
    public function desvincularAgendamiento(int $idAgendamiento): void
    {
        $ciclo = $this->where('id_agendamiento', $idAgendamiento)->first();

        if ($ciclo) {
            $this->update($ciclo['id'], [
                'fecha_agendada'   => null,
                'id_agendamiento'  => null,
                'estatus_agenda'   => 'pendiente',
            ]);
        }
    }

    /**
     * Genera el primer ciclo para un cliente nuevo (mes actual + offset según estándar)
     */
    public function generarPrimerCiclo(int $idCliente, int $idConsultor, string $estandar): ?int
    {
        $meses = match (strtolower(trim($estandar))) {
            'mensual'    => 1,
            'bimensual'  => 2,
            'trimestral' => 3,
            default      => 0,
        };

        if ($meses === 0) {
            return null;
        }

        $mesActual  = (int)date('n');
        $anioActual = (int)date('Y');

        // Primer ciclo = mes actual + frecuencia
        $primerMes  = $mesActual + $meses;
        $primerAnio = $anioActual;
        if ($primerMes > 12) {
            $primerMes  -= 12;
            $primerAnio += 1;
        }

        $existe = $this->where('id_cliente', $idCliente)
            ->where('mes_esperado', $primerMes)
            ->where('anio', $primerAnio)
            ->first();

        if ($existe) {
            return (int)$existe['id'];
        }

        $this->insert([
            'id_cliente'   => $idCliente,
            'id_consultor' => $idConsultor,
            'anio'         => $primerAnio,
            'mes_esperado' => $primerMes,
            'estandar'     => $estandar,
        ]);

        return (int)$this->getInsertID();
    }

    /**
     * Genera el siguiente ciclo de visita para un cliente
     */
    public function generarSiguienteCiclo(int $idCliente, string $fechaActa, string $estandar, int $idConsultor): ?int
    {
        $meses = match (strtolower(trim($estandar))) {
            'mensual'    => 1,
            'bimensual'  => 2,
            'trimestral' => 3,
            default      => 0, // Proyecto u otro → no genera
        };

        if ($meses === 0) {
            return null;
        }

        $mesActa  = (int)date('n', strtotime($fechaActa));
        $anioActa = (int)date('Y', strtotime($fechaActa));

        $nuevoMes  = $mesActa + $meses;
        $nuevoAnio = $anioActa;
        if ($nuevoMes > 12) {
            $nuevoMes  -= 12;
            $nuevoAnio += 1;
        }

        // Verificar que no exista ya un ciclo para ese cliente/mes/año
        $existe = $this->where('id_cliente', $idCliente)
            ->where('mes_esperado', $nuevoMes)
            ->where('anio', $nuevoAnio)
            ->first();

        if ($existe) {
            return (int)$existe['id'];
        }

        $this->insert([
            'id_cliente'   => $idCliente,
            'id_consultor' => $idConsultor,
            'anio'         => $nuevoAnio,
            'mes_esperado' => $nuevoMes,
            'estandar'     => $estandar,
        ]);

        return (int)$this->getInsertID();
    }
}
