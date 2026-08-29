<?php
namespace app\services;

use app\database\ConnectionFactory;
use app\repositories\UsuarioRepository;
use RuntimeException;

class ExclusaoDadosService
{
    public function excluir(int $usuarioId, string $senha): void
    {
        $usuarioRepo = new UsuarioRepository();
        $usuario = $usuarioRepo->findById($usuarioId);
        if (!$usuario || !password_verify($senha, $usuario->getSenha())) {
            throw new RuntimeException('A senha informada está incorreta.');
        }

        $conn = ConnectionFactory::getConnection();
        $arquivos = $this->arquivosDoUsuario($usuarioId);

        try {
            $conn->beginTransaction();
            $conn->prepare('DELETE FROM clientes WHERE usuario_id = :usuario_id')->execute(['usuario_id' => $usuarioId]);
            $conn->prepare('DELETE FROM notificacoes WHERE usuario_id = :usuario_id')->execute(['usuario_id' => $usuarioId]);
            $conn->prepare('DELETE FROM relatorios_automaticos WHERE usuario_id = :usuario_id')->execute(['usuario_id' => $usuarioId]);
            $conn->prepare('DELETE FROM logs_alteracoes WHERE usuario_id = :usuario_id')->execute(['usuario_id' => $usuarioId]);
            $conn->prepare('DELETE FROM usuarios WHERE id = :id')->execute(['id' => $usuarioId]);
            $conn->commit();
        } catch (\Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            throw new RuntimeException('Não foi possível excluir os dados da conta.');
        }

        foreach ($arquivos as $arquivo) {
            $caminho = $this->resolverCaminho($arquivo);
            if ($caminho && is_file($caminho)) {
                @unlink($caminho);
            }
        }
    }

    private function arquivosDoUsuario(int $usuarioId): array
    {
        $conn = ConnectionFactory::getConnection();
        $sql = 'SELECT DISTINCT d.caminho_arquivo arquivo
                FROM documentos d
                LEFT JOIN clientes c ON d.cliente_id = c.id
                LEFT JOIN marcas m ON d.marca_id = m.id
                LEFT JOIN patentes p ON d.patente_id = p.id
                LEFT JOIN clientes cm ON m.cliente_id = cm.id
                LEFT JOIN clientes cp ON p.cliente_id = cp.id
                WHERE COALESCE(c.usuario_id, cm.usuario_id, cp.usuario_id, d.uploaded_by) = :usuario_id
                UNION
                SELECT CONCAT("public/assets/relatorios/", arquivo) FROM relatorios_automaticos WHERE usuario_id = :usuario_id';
        $stmt = $conn->prepare($sql);
        $stmt->execute(['usuario_id' => $usuarioId]);
        return array_filter(array_column($stmt->fetchAll(), 'arquivo'));
    }

    private function resolverCaminho(string $arquivo): ?string
    {
        if ($arquivo === '') {
            return null;
        }
        if (str_starts_with($arquivo, '/')) {
            return $arquivo;
        }
        return dirname(__DIR__, 2) . '/' . ltrim($arquivo, '/');
    }
}
