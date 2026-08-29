<?php
namespace app\repositories;

use app\database\ConnectionFactory;
use PDO;

class DashboardRepository
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = ConnectionFactory::getConnection();
    }

    public function getMetricas(?int $usuarioId = null): array
    {
        $filtro = $usuarioId !== null ? ' WHERE c.usuario_id = :usuario_id' : '';
        $params = $usuarioId !== null ? ['usuario_id' => $usuarioId] : [];

        $clientes = $this->valor('SELECT COUNT(*) FROM clientes c' . $filtro, $params);
        $marcas = $this->valor('SELECT COUNT(*) FROM marcas m INNER JOIN clientes c ON m.cliente_id = c.id' . $filtro, $params);
        $patentes = $this->valor('SELECT COUNT(*) FROM patentes p INNER JOIN clientes c ON p.cliente_id = c.id' . $filtro, $params);

        return [
            'clientes' => $clientes,
            'marcas' => $marcas,
            'patentes' => $patentes,
            'processos' => $marcas + $patentes
        ];
    }

    public function getProcessosPorMes(?int $usuarioId = null): array
    {
        $sql = "SELECT DATE_FORMAT(criado, '%Y-%m') mes, SUM(total) total FROM (
                    SELECT m.criado, 1 total FROM marcas m INNER JOIN clientes c ON m.cliente_id = c.id";
        $params = [];
        if ($usuarioId !== null) {
            $sql .= ' WHERE c.usuario_id = :usuario_marca';
            $params['usuario_marca'] = $usuarioId;
        }
        $sql .= " UNION ALL SELECT p.criado, 1 total FROM patentes p INNER JOIN clientes c ON p.cliente_id = c.id";
        if ($usuarioId !== null) {
            $sql .= ' WHERE c.usuario_id = :usuario_patente';
            $params['usuario_patente'] = $usuarioId;
        }
        $sql .= ") processos WHERE criado >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)
                 GROUP BY DATE_FORMAT(criado, '%Y-%m') ORDER BY mes";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStatusProcessos(?int $usuarioId = null): array
    {
        $sql = 'SELECT status, COUNT(*) total FROM (';
        $sql .= 'SELECT m.status FROM marcas m INNER JOIN clientes c ON m.cliente_id = c.id';
        $params = [];
        if ($usuarioId !== null) {
            $sql .= ' WHERE c.usuario_id = :usuario_marca';
            $params['usuario_marca'] = $usuarioId;
        }
        $sql .= ' UNION ALL SELECT p.status FROM patentes p INNER JOIN clientes c ON p.cliente_id = c.id';
        if ($usuarioId !== null) {
            $sql .= ' WHERE c.usuario_id = :usuario_patente';
            $params['usuario_patente'] = $usuarioId;
        }
        $sql .= ') dados GROUP BY status ORDER BY total DESC LIMIT 8';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProcessosRecentes(?int $usuarioId = null): array
    {
        $sql = "SELECT * FROM (
                    SELECT 'Marca' tipo, m.id, m.numero_processo, m.titular titulo, m.status, c.nome cliente, m.criado
                    FROM marcas m INNER JOIN clientes c ON m.cliente_id = c.id";
        $params = [];
        if ($usuarioId !== null) {
            $sql .= ' WHERE c.usuario_id = :usuario_marca';
            $params['usuario_marca'] = $usuarioId;
        }
        $sql .= " UNION ALL
                    SELECT 'Patente' tipo, p.id, p.numero_processo, p.titular titulo, p.status, c.nome cliente, p.criado
                    FROM patentes p INNER JOIN clientes c ON p.cliente_id = c.id";
        if ($usuarioId !== null) {
            $sql .= ' WHERE c.usuario_id = :usuario_patente';
            $params['usuario_patente'] = $usuarioId;
        }
        $sql .= ') dados ORDER BY criado DESC LIMIT 6';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function valor(string $sql, array $params): int
    {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }
}
