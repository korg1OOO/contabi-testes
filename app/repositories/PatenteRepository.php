<?php
namespace app\repositories;

use app\database\ConnectionFactory;
use app\helpers\SimilaridadeHelper;
use PDO;

class PatenteRepository
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = ConnectionFactory::getConnection();
    }

    public function getAll(?int $usuarioId = null): array
    {
        $sql = 'SELECT p.* FROM patentes p INNER JOIN clientes c ON p.cliente_id = c.id WHERE 1=1';
        $params = [];
        if ($usuarioId !== null) {
            $sql .= ' AND c.usuario_id = :usuario_id';
            $params['usuario_id'] = $usuarioId;
        }
        $sql .= ' ORDER BY p.data_deposito DESC';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllByCliente(int $clienteId, ?int $usuarioId = null): array
    {
        $sql = 'SELECT p.* FROM patentes p INNER JOIN clientes c ON p.cliente_id = c.id WHERE p.cliente_id = :cliente_id';
        $params = ['cliente_id' => $clienteId];
        if ($usuarioId !== null) {
            $sql .= ' AND c.usuario_id = :usuario_id';
            $params['usuario_id'] = $usuarioId;
        }
        $sql .= ' ORDER BY p.data_deposito DESC';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id, ?int $usuarioId = null): array|false
    {
        $sql = 'SELECT p.* FROM patentes p INNER JOIN clientes c ON p.cliente_id = c.id WHERE p.id = :id';
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
        $sql = 'INSERT INTO patentes (cliente_id, numero_processo, titular, status, data_deposito, data_concessao, data_vencimento, tipo_patente, inventores, resumo, data_proxima_anuidade, data_manifestacao, data_prorrogacao, observacoes) VALUES (:cliente_id, :numero_processo, :titular, :status, :data_deposito, :data_concessao, :data_vencimento, :tipo_patente, :inventores, :resumo, :data_proxima_anuidade, :data_manifestacao, :data_prorrogacao, :observacoes)';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($data);
        return (int)$this->conn->lastInsertId();
    }

    public function update(array $data, ?int $usuarioId = null): bool
    {
        $sql = 'UPDATE patentes p INNER JOIN clientes c ON p.cliente_id = c.id SET p.cliente_id = :cliente_id, p.numero_processo = :numero_processo, p.titular = :titular, p.status = :status, p.data_deposito = :data_deposito, p.data_concessao = :data_concessao, p.data_vencimento = :data_vencimento, p.tipo_patente = :tipo_patente, p.inventores = :inventores, p.resumo = :resumo, p.data_proxima_anuidade = :data_proxima_anuidade, p.data_manifestacao = :data_manifestacao, p.data_prorrogacao = :data_prorrogacao, p.observacoes = :observacoes WHERE p.id = :id';
        if ($usuarioId !== null) {
            $sql .= ' AND c.usuario_id = :usuario_id';
            $data['usuario_id'] = $usuarioId;
        }
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($data);
    }

    public function delete(int $id, ?int $usuarioId = null): bool
    {
        $sql = 'DELETE p FROM patentes p INNER JOIN clientes c ON p.cliente_id = c.id WHERE p.id = :id';
        $params = ['id' => $id];
        if ($usuarioId !== null) {
            $sql .= ' AND c.usuario_id = :usuario_id';
            $params['usuario_id'] = $usuarioId;
        }
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

    public function buscarPatentesSimilares(string $termo, ?string $tipoPatente = null, ?int $usuarioId = null, float $limite = 70): array
    {
        $sql = 'SELECT p.*, c.nome AS nome_cliente FROM patentes p INNER JOIN clientes c ON p.cliente_id = c.id WHERE 1=1';
        $params = [];
        if ($tipoPatente) {
            $sql .= ' AND p.tipo_patente = :tipo_patente';
            $params['tipo_patente'] = $tipoPatente;
        }
        if ($usuarioId !== null) {
            $sql .= ' AND c.usuario_id = :usuario_id';
            $params['usuario_id'] = $usuarioId;
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $resultados = [];
        foreach ($stmt->fetchAll() as $patente) {
            $similaridade = SimilaridadeHelper::textual($termo, trim(($patente['titular'] ?? '') . ' ' . ($patente['resumo'] ?? '')));
            if ($similaridade >= $limite) {
                $patente['similaridade'] = $similaridade;
                $resultados[] = $patente;
            }
        }
        usort($resultados, fn(array $a, array $b) => $b['similaridade'] <=> $a['similaridade']);
        return $resultados;
    }

    public function findByNumeroProcessoAndUsuario(string $numeroProcesso, int $usuarioId): ?array
    {
        $stmt = $this->conn->prepare('SELECT p.* FROM patentes p INNER JOIN clientes c ON p.cliente_id = c.id WHERE p.numero_processo = :numero_processo AND c.usuario_id = :usuario_id LIMIT 1');
        $stmt->execute(['numero_processo' => $numeroProcesso, 'usuario_id' => $usuarioId]);
        return $stmt->fetch() ?: null;
    }

    public function getConnection(): PDO
    {
        return $this->conn;
    }
}
