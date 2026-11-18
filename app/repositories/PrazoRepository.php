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
        $data['origem'] = $data['origem'] ?? 'manual';
        $stmt = $this->conn->prepare('INSERT INTO prazos (marca_id, patente_id, tipo, data_vencimento, data_alerta_30, data_alerta_15, data_alerta_7, status, observacoes, origem) VALUES (:marca_id, :patente_id, :tipo, :data_vencimento, :data_alerta_30, :data_alerta_15, :data_alerta_7, :status, :observacoes, :origem)');
        return $stmt->execute($data);
    }

    public function sincronizarPrazoAtivo(
        string $tipoAtivo,
        int $ativoId,
        string $tipoPrazo,
        ?string $dataVencimento
    ): void {
        $campoAtivo = match ($tipoAtivo) {
            'marca' => 'marca_id',
            'patente' => 'patente_id',
            default => throw new \InvalidArgumentException('Tipo de ativo inválido.')
        };

        $sql = "SELECT id, data_vencimento
                FROM prazos
                WHERE {$campoAtivo} = :ativo_id
                  AND tipo = :tipo
                  AND origem = 'ativo'
                ORDER BY id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'ativo_id' => $ativoId,
            'tipo' => $tipoPrazo
        ]);
        $prazos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($dataVencimento === null || $dataVencimento === '') {
            foreach ($prazos as $prazo) {
                $this->excluirPrazoSincronizado((int) $prazo['id']);
            }
            return;
        }

        if (!$prazos) {
            $sql = "SELECT id, data_vencimento
                    FROM prazos
                    WHERE {$campoAtivo} = :ativo_id
                      AND tipo = :tipo
                      AND data_vencimento = :data_vencimento
                      AND origem = 'manual'
                    ORDER BY id
                    LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'ativo_id' => $ativoId,
                'tipo' => $tipoPrazo,
                'data_vencimento' => $dataVencimento
            ]);
            $prazoManual = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($prazoManual) {
                $this->conn->prepare(
                    "UPDATE prazos SET origem = 'ativo' WHERE id = :id"
                )->execute(['id' => (int) $prazoManual['id']]);
                $prazos[] = $prazoManual;
            }
        }

        if (!$prazos) {
            $this->inserirPrazoSincronizado(
                $tipoAtivo,
                $ativoId,
                $tipoPrazo,
                $dataVencimento
            );
            return;
        }

        foreach ($prazos as $prazo) {
            $prazoId = (int) $prazo['id'];

            if ($prazo['data_vencimento'] === $dataVencimento) {
                continue;
            }

            $this->removerNotificacoesDoPrazo($prazoId);
            $datasAlerta = $this->datasDeAlerta($dataVencimento);

            $this->conn->prepare(
                "UPDATE prazos
                 SET data_vencimento = :data_vencimento,
                     data_alerta_30 = :data_alerta_30,
                     data_alerta_15 = :data_alerta_15,
                     data_alerta_7 = :data_alerta_7,
                     status = 'pendente',
                     notificado = 0,
                     origem = 'ativo'
                 WHERE id = :id"
            )->execute([
                'data_vencimento' => $dataVencimento,
                'data_alerta_30' => $datasAlerta[30],
                'data_alerta_15' => $datasAlerta[15],
                'data_alerta_7' => $datasAlerta[7],
                'id' => $prazoId
            ]);
        }
    }

    private function inserirPrazoSincronizado(
        string $tipoAtivo,
        int $ativoId,
        string $tipoPrazo,
        string $dataVencimento
    ): void {
        $datasAlerta = $this->datasDeAlerta($dataVencimento);
        $marcaId = $tipoAtivo === 'marca' ? $ativoId : null;
        $patenteId = $tipoAtivo === 'patente' ? $ativoId : null;

        $this->save([
            'marca_id' => $marcaId,
            'patente_id' => $patenteId,
            'tipo' => $tipoPrazo,
            'data_vencimento' => $dataVencimento,
            'data_alerta_30' => $datasAlerta[30],
            'data_alerta_15' => $datasAlerta[15],
            'data_alerta_7' => $datasAlerta[7],
            'status' => 'pendente',
            'observacoes' => null,
            'origem' => 'ativo'
        ]);
    }

    private function datasDeAlerta(string $dataVencimento): array
    {
        $data = new \DateTime($dataVencimento);

        return [
            30 => (clone $data)->modify('-30 days')->format('Y-m-d'),
            15 => (clone $data)->modify('-15 days')->format('Y-m-d'),
            7 => (clone $data)->modify('-7 days')->format('Y-m-d')
        ];
    }

    private function excluirPrazoSincronizado(int $prazoId): void
    {
        $this->removerNotificacoesDoPrazo($prazoId);
        $this->conn->prepare(
            "DELETE FROM prazos WHERE id = :id AND origem = 'ativo'"
        )->execute(['id' => $prazoId]);
    }

    private function removerNotificacoesDoPrazo(int $prazoId): void
    {
        $this->conn->prepare(
            'DELETE FROM notificacoes WHERE link LIKE :link'
        )->execute([
            'link' => '%prazo_id=' . $prazoId . '&alerta=%'
        ]);
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
