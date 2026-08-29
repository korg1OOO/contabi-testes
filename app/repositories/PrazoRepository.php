<?php
namespace app\repositories;

use app\database\ConnectionFactory;
use PDO;

class PrazoRepository
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = ConnectionFactory::getConnection();
    }

    public function getPrazosByCliente(int $clienteId): array
    {
        $stmt = $this->conn->prepare("SELECT p.* FROM prazos p LEFT JOIN marcas m ON p.marca_id = m.id LEFT JOIN patentes pat ON p.patente_id = pat.id LEFT JOIN clientes c ON (m.cliente_id = c.id OR pat.cliente_id = c.id) WHERE c.id = :cliente_id ORDER BY p.data_vencimento ASC");
        $stmt->execute(['cliente_id' => $clienteId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllPrazosComProcesso(?int $usuarioId = null, array $filtros = []): array
    {
        $sql = "SELECT p.*, COALESCE(m.numero_processo, pat.numero_processo) numero_processo, COALESCE(m.titular, pat.titular) titulo, c.nome cliente_nome, CASE WHEN p.marca_id IS NOT NULL THEN 'Marca' ELSE 'Patente' END ativo_tipo FROM prazos p LEFT JOIN marcas m ON p.marca_id = m.id LEFT JOIN patentes pat ON p.patente_id = pat.id LEFT JOIN clientes c ON (m.cliente_id = c.id OR pat.cliente_id = c.id) WHERE 1=1";
        $params = [];
        if ($usuarioId !== null) {
            $sql .= ' AND c.usuario_id = :usuario_id';
            $params['usuario_id'] = $usuarioId;
        }
        if (!empty($filtros['status'])) {
            $sql .= ' AND p.status = :status';
            $params['status'] = $filtros['status'];
        }
        if (!empty($filtros['periodo']) && (int)$filtros['periodo'] > 0) {
            $sql .= ' AND p.data_vencimento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ' . (int)$filtros['periodo'] . ' DAY)';
        }
        if (!empty($filtros['busca'])) {
            $sql .= ' AND (COALESCE(m.numero_processo, pat.numero_processo) LIKE :busca OR COALESCE(m.titular, pat.titular) LIKE :busca OR c.nome LIKE :busca)';
            $params['busca'] = '%' . $filtros['busca'] . '%';
        }
        $sql .= ' ORDER BY p.data_vencimento ASC';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProximosPrazos(?int $usuarioId = null, int $limite = 6): array
    {
        $sql = "SELECT p.*, COALESCE(m.numero_processo, pat.numero_processo) numero_processo, COALESCE(m.titular, pat.titular) titulo, c.nome cliente_nome FROM prazos p LEFT JOIN marcas m ON p.marca_id = m.id LEFT JOIN patentes pat ON p.patente_id = pat.id LEFT JOIN clientes c ON (m.cliente_id = c.id OR pat.cliente_id = c.id) WHERE p.status = 'pendente'";
        $params = [];
        if ($usuarioId !== null) {
            $sql .= ' AND c.usuario_id = :usuario_id';
            $params['usuario_id'] = $usuarioId;
        }
        $sql .= ' ORDER BY p.data_vencimento ASC LIMIT ' . max(1, $limite);
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getResumo(?int $usuarioId = null): array
    {
        $sql = "SELECT SUM(CASE WHEN p.status = 'pendente' AND p.data_vencimento < CURDATE() THEN 1 ELSE 0 END) vencidos, SUM(CASE WHEN p.status = 'pendente' AND p.data_vencimento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) urgentes, SUM(CASE WHEN p.status = 'pendente' AND p.data_vencimento BETWEEN DATE_ADD(CURDATE(), INTERVAL 8 DAY) AND DATE_ADD(CURDATE(), INTERVAL 15 DAY) THEN 1 ELSE 0 END) atencao, SUM(CASE WHEN p.status = 'pendente' AND p.data_vencimento > DATE_ADD(CURDATE(), INTERVAL 15 DAY) THEN 1 ELSE 0 END) no_prazo FROM prazos p LEFT JOIN marcas m ON p.marca_id = m.id LEFT JOIN patentes pat ON p.patente_id = pat.id LEFT JOIN clientes c ON (m.cliente_id = c.id OR pat.cliente_id = c.id) WHERE 1=1";
        $params = [];
        if ($usuarioId !== null) {
            $sql .= ' AND c.usuario_id = :usuario_id';
            $params['usuario_id'] = $usuarioId;
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        return array_map('intval', array_merge(['vencidos' => 0, 'urgentes' => 0, 'atencao' => 0, 'no_prazo' => 0], $resultado));
    }

    public function getAtivosDisponiveis(?int $usuarioId = null): array
    {
        $sql = "SELECT 'marca' tipo, m.id, m.numero_processo, m.titular, c.nome cliente_nome FROM marcas m INNER JOIN clientes c ON m.cliente_id = c.id";
        $params = [];
        if ($usuarioId !== null) {
            $sql .= ' WHERE c.usuario_id = :usuario_marca';
            $params['usuario_marca'] = $usuarioId;
        }
        $sql .= " UNION ALL SELECT 'patente' tipo, p.id, p.numero_processo, p.titular, c.nome cliente_nome FROM patentes p INNER JOIN clientes c ON p.cliente_id = c.id";
        if ($usuarioId !== null) {
            $sql .= ' WHERE c.usuario_id = :usuario_patente';
            $params['usuario_patente'] = $usuarioId;
        }
        $sql .= ' ORDER BY cliente_nome, numero_processo';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id, ?int $usuarioId = null): array|false
    {
        $sql = "SELECT p.*, COALESCE(m.numero_processo, pat.numero_processo) numero_processo FROM prazos p LEFT JOIN marcas m ON p.marca_id = m.id LEFT JOIN patentes pat ON p.patente_id = pat.id LEFT JOIN clientes c ON (m.cliente_id = c.id OR pat.cliente_id = c.id) WHERE p.id = :id";
        $params = ['id' => $id];
        if ($usuarioId !== null) {
            $sql .= ' AND c.usuario_id = :usuario_id';
            $params['usuario_id'] = $usuarioId;
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function save(array $data): bool
    {
        $stmt = $this->conn->prepare('INSERT INTO prazos (marca_id, patente_id, tipo, data_vencimento, data_alerta_30, data_alerta_15, data_alerta_7, status, observacoes) VALUES (:marca_id, :patente_id, :tipo, :data_vencimento, :data_alerta_30, :data_alerta_15, :data_alerta_7, :status, :observacoes)');
        return $stmt->execute($data);
    }

    public function update(array $data, ?int $usuarioId = null): bool
    {
        $sql = "UPDATE prazos p LEFT JOIN marcas m ON p.marca_id = m.id LEFT JOIN patentes pat ON p.patente_id = pat.id LEFT JOIN clientes c ON (m.cliente_id = c.id OR pat.cliente_id = c.id) SET p.marca_id = :marca_id, p.patente_id = :patente_id, p.tipo = :tipo, p.data_vencimento = :data_vencimento, p.data_alerta_30 = :data_alerta_30, p.data_alerta_15 = :data_alerta_15, p.data_alerta_7 = :data_alerta_7, p.status = :status, p.observacoes = :observacoes WHERE p.id = :id";
        if ($usuarioId !== null) {
            $sql .= ' AND c.usuario_id = :usuario_id';
            $data['usuario_id'] = $usuarioId;
        }
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($data);
    }

    public function alterarStatus(int $id, string $status, ?int $usuarioId = null): bool
    {
        $sql = "UPDATE prazos p LEFT JOIN marcas m ON p.marca_id = m.id LEFT JOIN patentes pat ON p.patente_id = pat.id LEFT JOIN clientes c ON (m.cliente_id = c.id OR pat.cliente_id = c.id) SET p.status = :status WHERE p.id = :id";
        $params = ['id' => $id, 'status' => $status];
        if ($usuarioId !== null) {
            $sql .= ' AND c.usuario_id = :usuario_id';
            $params['usuario_id'] = $usuarioId;
        }
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id, ?int $usuarioId = null): bool
    {
        $sql = "DELETE p FROM prazos p LEFT JOIN marcas m ON p.marca_id = m.id LEFT JOIN patentes pat ON p.patente_id = pat.id LEFT JOIN clientes c ON (m.cliente_id = c.id OR pat.cliente_id = c.id) WHERE p.id = :id";
        $params = ['id' => $id];
        if ($usuarioId !== null) {
            $sql .= ' AND c.usuario_id = :usuario_id';
            $params['usuario_id'] = $usuarioId;
        }
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

    public function deleteByMarca(int $marcaId): bool
    {
        $stmt = $this->conn->prepare('DELETE FROM prazos WHERE marca_id = :marca_id');
        return $stmt->execute(['marca_id' => $marcaId]);
    }

    public function deleteByPatente(int $patenteId): bool
    {
        $stmt = $this->conn->prepare('DELETE FROM prazos WHERE patente_id = :patente_id');
        return $stmt->execute(['patente_id' => $patenteId]);
    }

    public function countPrazosCriticos(?int $usuarioId = null): int
    {
        $sql = "SELECT COUNT(*) FROM prazos p LEFT JOIN marcas m ON p.marca_id = m.id LEFT JOIN patentes pat ON p.patente_id = pat.id LEFT JOIN clientes c ON (m.cliente_id = c.id OR pat.cliente_id = c.id) WHERE p.status = 'pendente' AND p.data_vencimento <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)";
        $params = [];
        if ($usuarioId !== null) {
            $sql .= ' AND c.usuario_id = :usuario_id';
            $params['usuario_id'] = $usuarioId;
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function getPrazosCriticosDoUsuario(int $usuarioId): array
    {
        return $this->getAllPrazosComProcesso($usuarioId, ['status' => 'pendente', 'periodo' => 30]);
    }
}
