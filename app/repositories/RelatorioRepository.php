<?php
namespace app\repositories;

use app\database\ConnectionFactory;
use PDO;

class RelatorioRepository
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = ConnectionFactory::getConnection();
    }

    public function gerar(string $tipo, ?int $usuarioId, array $filtros): array
    {
        return match ($tipo) {
            'marcas_cliente' => $this->marcasPorCliente($usuarioId, $filtros),
            'patentes_cliente' => $this->patentesPorCliente($usuarioId, $filtros),
            'vencimentos' => $this->vencimentos($usuarioId, $filtros),
            'situacao_processos' => $this->situacaoProcessos($usuarioId, $filtros),
            default => []
        };
    }

    private function marcasPorCliente(?int $usuarioId, array $filtros): array
    {
        $sql = "SELECT c.nome AS cliente, c.cpf_cnpj AS documento, m.numero_processo AS processo, m.titular AS titular, m.classe_nice AS classe, m.status AS status, DATE_FORMAT(m.data_deposito, '%d/%m/%Y') AS deposito FROM marcas m INNER JOIN clientes c ON m.cliente_id = c.id WHERE 1=1";
        $dados = $this->executar($sql, $usuarioId, $filtros, 'm.data_deposito');

        return $this->formatarColunas($dados, [
            'cliente' => 'Cliente',
            'documento' => 'Documento',
            'processo' => 'Processo',
            'titular' => 'Titular',
            'classe' => 'Classe',
            'status' => 'Status',
            'deposito' => 'Depósito'
        ]);
    }

    private function patentesPorCliente(?int $usuarioId, array $filtros): array
    {
        $sql = "SELECT c.nome AS cliente, c.cpf_cnpj AS documento, p.numero_processo AS processo, p.titular AS titular, p.tipo_patente AS tipo, p.status AS status, DATE_FORMAT(p.data_deposito, '%d/%m/%Y') AS deposito FROM patentes p INNER JOIN clientes c ON p.cliente_id = c.id WHERE 1=1";
        $dados = $this->executar($sql, $usuarioId, $filtros, 'p.data_deposito');

        return $this->formatarColunas($dados, [
            'cliente' => 'Cliente',
            'documento' => 'Documento',
            'processo' => 'Processo',
            'titular' => 'Titular',
            'tipo' => 'Tipo',
            'status' => 'Status',
            'deposito' => 'Depósito'
        ]);
    }

    private function vencimentos(?int $usuarioId, array $filtros): array
    {
        $sql = "SELECT c.nome AS cliente, COALESCE(m.numero_processo, pat.numero_processo) AS processo, CASE WHEN p.marca_id IS NOT NULL THEN 'Marca' ELSE 'Patente' END AS ativo, REPLACE(p.tipo, '_', ' ') AS tipo, DATE_FORMAT(p.data_vencimento, '%d/%m/%Y') AS vencimento, p.status AS status FROM prazos p LEFT JOIN marcas m ON p.marca_id = m.id LEFT JOIN patentes pat ON p.patente_id = pat.id LEFT JOIN clientes c ON (m.cliente_id = c.id OR pat.cliente_id = c.id) WHERE 1=1";
        $dados = $this->executar($sql, $usuarioId, $filtros, 'p.data_vencimento');

        return $this->formatarColunas($dados, [
            'cliente' => 'Cliente',
            'processo' => 'Processo',
            'ativo' => 'Ativo',
            'tipo' => 'Tipo',
            'vencimento' => 'Vencimento',
            'status' => 'Status'
        ]);
    }

    private function situacaoProcessos(?int $usuarioId, array $filtros): array
    {
        $sql = "SELECT * FROM (
                    SELECT c.usuario_id, c.id AS cliente_id, c.nome AS cliente, 'Marca' AS tipo, m.numero_processo AS processo, m.titular AS titular, m.status AS status, m.data_deposito AS data_filtro
                    FROM marcas m
                    INNER JOIN clientes c ON m.cliente_id = c.id
                    UNION ALL
                    SELECT c.usuario_id, c.id AS cliente_id, c.nome AS cliente, 'Patente' AS tipo, p.numero_processo AS processo, p.titular AS titular, p.status AS status, p.data_deposito AS data_filtro
                    FROM patentes p
                    INNER JOIN clientes c ON p.cliente_id = c.id
                ) AS dados
                WHERE 1=1";
        $params = [];

        if ($usuarioId !== null) {
            $sql .= ' AND usuario_id = :usuario_id';
            $params['usuario_id'] = $usuarioId;
        }

        if (!empty($filtros['cliente_id'])) {
            $sql .= ' AND cliente_id = :cliente_id';
            $params['cliente_id'] = (int)$filtros['cliente_id'];
        }

        if (!empty($filtros['data_inicial'])) {
            $sql .= ' AND data_filtro >= :data_inicial';
            $params['data_inicial'] = $filtros['data_inicial'];
        }

        if (!empty($filtros['data_final'])) {
            $sql .= ' AND data_filtro <= :data_final';
            $params['data_final'] = $filtros['data_final'];
        }

        $sql .= ' ORDER BY cliente, tipo, processo';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($dados as &$linha) {
            unset($linha['usuario_id'], $linha['cliente_id'], $linha['data_filtro']);
        }
        unset($linha);

        return $this->formatarColunas($dados, [
            'cliente' => 'Cliente',
            'tipo' => 'Tipo',
            'processo' => 'Processo',
            'titular' => 'Titular',
            'status' => 'Status'
        ]);
    }

    private function executar(string $sql, ?int $usuarioId, array $filtros, string $campoData): array
    {
        $params = [];

        if ($usuarioId !== null) {
            $sql .= ' AND c.usuario_id = :usuario_id';
            $params['usuario_id'] = $usuarioId;
        }

        if (!empty($filtros['cliente_id'])) {
            $sql .= ' AND c.id = :cliente_id';
            $params['cliente_id'] = (int)$filtros['cliente_id'];
        }

        if (!empty($filtros['data_inicial'])) {
            $sql .= ' AND ' . $campoData . ' >= :data_inicial';
            $params['data_inicial'] = $filtros['data_inicial'];
        }

        if (!empty($filtros['data_final'])) {
            $sql .= ' AND ' . $campoData . ' <= :data_final';
            $params['data_final'] = $filtros['data_final'];
        }

        $sql .= ' ORDER BY c.nome';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function formatarColunas(array $dados, array $colunas): array
    {
        $resultado = [];

        foreach ($dados as $linha) {
            $formatada = [];

            foreach ($colunas as $origem => $titulo) {
                $formatada[$titulo] = $linha[$origem] ?? '';
            }

            $resultado[] = $formatada;
        }

        return $resultado;
    }
}
