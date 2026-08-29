<?php
namespace app\repositories;

use app\database\ConnectionFactory;
use PDO;

class DocumentoRepository
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = ConnectionFactory::getConnection();
    }

    public function listar(?int $usuarioId): array
    {
        $sql = 'SELECT d.*, c.nome cliente_nome, m.numero_processo marca_processo, p.numero_processo patente_processo, u.nome usuario_nome
                FROM documentos d
                LEFT JOIN clientes c ON d.cliente_id = c.id
                LEFT JOIN marcas m ON d.marca_id = m.id
                LEFT JOIN patentes p ON d.patente_id = p.id
                LEFT JOIN usuarios u ON d.uploaded_by = u.id
                LEFT JOIN clientes cm ON m.cliente_id = cm.id
                LEFT JOIN clientes cp ON p.cliente_id = cp.id
                WHERE 1=1';
        $params = [];
        if ($usuarioId !== null) {
            $sql .= ' AND COALESCE(c.usuario_id, cm.usuario_id, cp.usuario_id) = :usuario_id';
            $params['usuario_id'] = $usuarioId;
        }
        $sql .= ' ORDER BY d.criado DESC';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function salvar(array $dados): bool
    {
        $stmt = $this->conn->prepare('INSERT INTO documentos (marca_id, patente_id, cliente_id, nome_arquivo, caminho_arquivo, tipo, tamanho_bytes, uploaded_by) VALUES (:marca_id, :patente_id, :cliente_id, :nome_arquivo, :caminho_arquivo, :tipo, :tamanho_bytes, :uploaded_by)');
        return $stmt->execute($dados);
    }

    public function buscar(int $id, ?int $usuarioId): array|false
    {
        $sql = 'SELECT d.* FROM documentos d
                LEFT JOIN clientes c ON d.cliente_id = c.id
                LEFT JOIN marcas m ON d.marca_id = m.id
                LEFT JOIN patentes p ON d.patente_id = p.id
                LEFT JOIN clientes cm ON m.cliente_id = cm.id
                LEFT JOIN clientes cp ON p.cliente_id = cp.id
                WHERE d.id = :id';
        $params = ['id' => $id];
        if ($usuarioId !== null) {
            $sql .= ' AND COALESCE(c.usuario_id, cm.usuario_id, cp.usuario_id) = :usuario_id';
            $params['usuario_id'] = $usuarioId;
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function excluir(int $id, ?int $usuarioId): bool
    {
        $documento = $this->buscar($id, $usuarioId);
        if (!$documento) {
            return false;
        }
        $stmt = $this->conn->prepare('DELETE FROM documentos WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
