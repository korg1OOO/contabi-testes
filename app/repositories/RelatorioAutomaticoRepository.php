<?php
namespace app\repositories;

use app\database\ConnectionFactory;
use PDO;

class RelatorioAutomaticoRepository
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = ConnectionFactory::getConnection();
    }

    public function salvar(array $dados): bool
    {
        $stmt = $this->conn->prepare('INSERT INTO relatorios_automaticos (usuario_id, titulo, arquivo, periodo_inicial, periodo_final) VALUES (:usuario_id, :titulo, :arquivo, :periodo_inicial, :periodo_final)');
        return $stmt->execute($dados);
    }

    public function existePeriodo(int $usuarioId, string $inicio, string $fim): bool
    {
        $stmt = $this->conn->prepare('SELECT COUNT(*) FROM relatorios_automaticos WHERE usuario_id = :usuario_id AND periodo_inicial = :periodo_inicial AND periodo_final = :periodo_final');
        $stmt->execute([
            'usuario_id' => $usuarioId,
            'periodo_inicial' => $inicio,
            'periodo_final' => $fim
        ]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function listar(?int $usuarioId): array
    {
        $sql = 'SELECT r.*, u.nome usuario_nome FROM relatorios_automaticos r INNER JOIN usuarios u ON r.usuario_id = u.id';
        $params = [];
        if ($usuarioId !== null) {
            $sql .= ' WHERE r.usuario_id = :usuario_id';
            $params['usuario_id'] = $usuarioId;
        }
        $sql .= ' ORDER BY r.criado DESC LIMIT 30';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
