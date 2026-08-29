<?php
namespace app\repositories;

use app\database\ConnectionFactory;
use app\helpers\SimilaridadeHelper;
use PDO;

class MarcaRepository
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = ConnectionFactory::getConnection();
    }

    public function getAll(?int $usuarioId = null): array
    {
        $sql = 'SELECT m.* FROM marcas m INNER JOIN clientes c ON m.cliente_id = c.id WHERE 1=1';
        $params = [];
        if ($usuarioId !== null) {
            $sql .= ' AND c.usuario_id = :usuario_id';
            $params['usuario_id'] = $usuarioId;
        }
        $sql .= ' ORDER BY m.data_deposito DESC';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllByCliente(int $clienteId, ?int $usuarioId = null): array
    {
        $sql = 'SELECT m.* FROM marcas m INNER JOIN clientes c ON m.cliente_id = c.id WHERE m.cliente_id = :cliente_id';
        $params = ['cliente_id' => $clienteId];
        if ($usuarioId !== null) {
            $sql .= ' AND c.usuario_id = :usuario_id';
            $params['usuario_id'] = $usuarioId;
        }
        $sql .= ' ORDER BY m.data_deposito DESC';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id, ?int $usuarioId = null): array|false
    {
        $sql = 'SELECT m.* FROM marcas m INNER JOIN clientes c ON m.cliente_id = c.id WHERE m.id = :id';
        $params = ['id' => $id];
        if ($usuarioId !== null) {
            $sql .= ' AND c.usuario_id = :usuario_id';
            $params['usuario_id'] = $usuarioId;
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    public function save(array $data): int
    {
        $sql = 'INSERT INTO marcas (cliente_id, numero_processo, titular, classe_nice, status, data_deposito, data_concessao, data_vencimento, data_proxima_anuidade, data_renovacao, data_oposicao, data_prorrogacao, data_manifestacao, apresentacao, observacoes) VALUES (:cliente_id, :numero_processo, :titular, :classe_nice, :status, :data_deposito, :data_concessao, :data_vencimento, :data_proxima_anuidade, :data_renovacao, :data_oposicao, :data_prorrogacao, :data_manifestacao, :apresentacao, :observacoes)';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($data);
        return (int)$this->conn->lastInsertId();
    }

    public function update(array $data, ?int $usuarioId = null): bool
    {
        $sql = 'UPDATE marcas m INNER JOIN clientes c ON m.cliente_id = c.id SET m.cliente_id = :cliente_id, m.numero_processo = :numero_processo, m.titular = :titular, m.classe_nice = :classe_nice, m.status = :status, m.data_deposito = :data_deposito, m.data_concessao = :data_concessao, m.data_vencimento = :data_vencimento, m.data_proxima_anuidade = :data_proxima_anuidade, m.data_renovacao = :data_renovacao, m.data_oposicao = :data_oposicao, m.data_prorrogacao = :data_prorrogacao, m.data_manifestacao = :data_manifestacao, m.apresentacao = :apresentacao, m.observacoes = :observacoes WHERE m.id = :id';
        if ($usuarioId !== null) {
            $sql .= ' AND c.usuario_id = :usuario_id';
            $data['usuario_id'] = $usuarioId;
        }
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($data);
    }

    public function delete(int $id, ?int $usuarioId = null): bool
    {
        $sql = 'DELETE m FROM marcas m INNER JOIN clientes c ON m.cliente_id = c.id WHERE m.id = :id';
        $params = ['id' => $id];
        if ($usuarioId !== null) {
            $sql .= ' AND c.usuario_id = :usuario_id';
            $params['usuario_id'] = $usuarioId;
        }
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

    public function buscarMarcasSimilares(string $termo, int $classeNice, ?int $usuarioId = null, float $limite = 70): array
    {
        $sql = 'SELECT m.*, c.nome AS nome_cliente FROM marcas m INNER JOIN clientes c ON m.cliente_id = c.id WHERE m.classe_nice = :classe_nice';
        $params = ['classe_nice' => $classeNice];
        if ($usuarioId !== null) {
            $sql .= ' AND c.usuario_id = :usuario_id';
            $params['usuario_id'] = $usuarioId;
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $resultados = [];
        foreach ($stmt->fetchAll() as $marca) {
            $similaridade = SimilaridadeHelper::textual($termo, $marca['titular']);
            if ($similaridade >= $limite) {
                $marca['similaridade'] = $similaridade;
                $resultados[] = $marca;
            }
        }
        usort($resultados, fn(array $a, array $b) => $b['similaridade'] <=> $a['similaridade']);
        return $resultados;
    }

    public function findByNumeroProcessoAndUsuario(string $numeroProcesso, int $usuarioId): ?array
    {
        $stmt = $this->conn->prepare('SELECT m.* FROM marcas m INNER JOIN clientes c ON m.cliente_id = c.id WHERE m.numero_processo = :numero_processo AND c.usuario_id = :usuario_id LIMIT 1');
        $stmt->execute(['numero_processo' => $numeroProcesso, 'usuario_id' => $usuarioId]);
        return $stmt->fetch() ?: null;
    }

    public function getConnection(): PDO
    {
        return $this->conn;
    }
}
