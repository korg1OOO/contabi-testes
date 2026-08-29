<?php
namespace app\repositories;

use app\database\ConnectionFactory;
use app\models\Usuario;
use PDO;

class UsuarioRepository
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = ConnectionFactory::getConnection();
    }

    public function findByCpf(string $cpf): ?Usuario
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);
        $stmt = $this->conn->prepare("SELECT * FROM usuarios WHERE cpf = :cpf AND ativo = 1 LIMIT 1");
        $stmt->execute(['cpf' => $cpf]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? Usuario::fromArray($data) : null;
    }

    public function findById(int $id): ?Usuario
    {
        $stmt = $this->conn->prepare("SELECT * FROM usuarios WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? Usuario::fromArray($data) : null;
    }

    public function getAtivos(): array
    {
        $stmt = $this->conn->query("SELECT id, nome, email FROM usuarios WHERE ativo = 1 ORDER BY id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAll(): array
    {
        $stmt = $this->conn->query("SELECT * FROM usuarios ORDER BY nome");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function save(Usuario $usuario): bool
    {
        $sql = "INSERT INTO usuarios (nome, cpf, email, telefone, senha, perfil, ativo) 
                VALUES (:nome, :cpf, :email, :telefone, :senha, :perfil, :ativo)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            'nome'     => $usuario->getNome(),
            'cpf'      => $usuario->getCpf(),
            'email'    => $usuario->getEmail(),
            'telefone' => $usuario->getTelefone(),
            'senha'    => password_hash($usuario->getSenha(), PASSWORD_DEFAULT),
            'perfil'   => $usuario->getPerfil(),
            'ativo'    => $usuario->isAtivo() ? 1 : 0
        ]);
    }

    public function update(Usuario $usuario): bool
    {
        $sql = "UPDATE usuarios SET 
                    nome = :nome, 
                    email = :email, 
                    telefone = :telefone, 
                    perfil = :perfil 
                WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            'nome'     => $usuario->getNome(),
            'email'    => $usuario->getEmail(),
            'telefone' => $usuario->getTelefone(),
            'perfil'   => $usuario->getPerfil(),
            'id'       => $usuario->getId()
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM usuarios WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}