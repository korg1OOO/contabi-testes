<?php
namespace app\repositories;

use app\database\ConnectionFactory;
use PDO;

class ClienteRepository
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = ConnectionFactory::getConnection();
    }

    public function getClientes(?int $usuarioId = null): array
    {
        $sql = 'SELECT * FROM clientes';
        $params = [];
        if ($usuarioId !== null) {
            $sql .= ' WHERE usuario_id = :usuario_id';
            $params['usuario_id'] = $usuarioId;
        }
        $sql .= ' ORDER BY nome ASC';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getClienteById(int $id, ?int $usuarioId = null): array|false
    {
        $sql = 'SELECT * FROM clientes WHERE id = :id';
        $params = ['id' => $id];
        if ($usuarioId !== null) {
            $sql .= ' AND usuario_id = :usuario_id';
            $params['usuario_id'] = $usuarioId;
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    public function saveCliente(array $data): bool
    {
        $stmt = $this->conn->prepare('INSERT INTO clientes (usuario_id, nome, tipo_pessoa, cpf_cnpj, email, telefone, endereco, observacoes) VALUES (:usuario_id, :nome, :tipo_pessoa, :cpf_cnpj, :email, :telefone, :endereco, :observacoes)');
        return $stmt->execute($data);
    }

    public function updateCliente(array $data, ?int $usuarioId = null): bool
    {
        $sql = 'UPDATE clientes SET nome = :nome, tipo_pessoa = :tipo_pessoa, cpf_cnpj = :cpf_cnpj, email = :email, telefone = :telefone, endereco = :endereco, observacoes = :observacoes WHERE id = :id';
        if ($usuarioId !== null) {
            $sql .= ' AND usuario_id = :usuario_id';
            $data['usuario_id'] = $usuarioId;
        }
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($data);
    }

    public function deleteCliente(int $id, ?int $usuarioId = null): bool
    {
        $sql = 'DELETE FROM clientes WHERE id = :id';
        $params = ['id' => $id];
        if ($usuarioId !== null) {
            $sql .= ' AND usuario_id = :usuario_id';
            $params['usuario_id'] = $usuarioId;
        }
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }
}
