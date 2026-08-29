<?php

namespace app\repositories;

use app\database\ConnectionFactory;
use PDO;

class NotificacaoRepository
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = ConnectionFactory::getConnection();
    }

    public function criarNotificacao(array $data): bool
    {
        $stmt = $this->conn->prepare(
            'INSERT INTO notificacoes
            (usuario_id, tipo, titulo, mensagem, link)
            VALUES
            (:usuario_id, :tipo, :titulo, :mensagem, :link)'
        );

        return $stmt->execute($data);
    }

    public function existeNotificacaoPrazo(
        int $prazoId,
        string $tipo
    ): bool {
        $stmt = $this->conn->prepare(
            'SELECT COUNT(*)
             FROM notificacoes
             WHERE link LIKE :link'
        );

        $stmt->execute([
            'link' => '%prazo_id='
                . $prazoId
                . '&alerta='
                . $tipo
                . '%'
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }

    public function getNotificacoesNaoLidas(int $usuarioId): array
    {
        $stmt = $this->conn->prepare(
            'SELECT *
             FROM notificacoes
             WHERE usuario_id = :usuario_id
               AND lida = 0
             ORDER BY criado DESC
             LIMIT 20'
        );

        $stmt->execute([
            'usuario_id' => $usuarioId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTodas(int $usuarioId): array
    {
        $stmt = $this->conn->prepare(
            'SELECT *
             FROM notificacoes
             WHERE usuario_id = :usuario_id
             ORDER BY criado DESC'
        );

        $stmt->execute([
            'usuario_id' => $usuarioId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function marcarComoLida(
        int $id,
        int $usuarioId
    ): bool {
        $stmt = $this->conn->prepare(
            'UPDATE notificacoes
             SET lida = 1
             WHERE id = :id
               AND usuario_id = :usuario_id'
        );

        return $stmt->execute([
            'id' => $id,
            'usuario_id' => $usuarioId
        ]);
    }

    public function marcarTodasComoLidas(
        int $usuarioId
    ): bool {
        $stmt = $this->conn->prepare(
            'UPDATE notificacoes
             SET lida = 1
             WHERE usuario_id = :usuario_id'
        );

        return $stmt->execute([
            'usuario_id' => $usuarioId
        ]);
    }
}