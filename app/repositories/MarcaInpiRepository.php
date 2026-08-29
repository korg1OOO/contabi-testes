<?php

namespace app\repositories;

use app\database\ConnectionFactory;
use app\helpers\SimilaridadeHelper;
use PDO;
use PDOStatement;

class MarcaInpiRepository
{
    private PDO $conn;
    private ?PDOStatement $stmtSalvar = null;

    public function __construct()
    {
        $this->conn = ConnectionFactory::getConnection();
    }

    public function tabelaExiste(): bool
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*)
             FROM information_schema.tables
             WHERE table_schema = DATABASE()
               AND table_name = 'marcas_inpi'"
        );

        $stmt->execute();

        return (int)$stmt->fetchColumn() > 0;
    }

    public function salvar(array $dados): bool
    {
        $sql = 'INSERT INTO marcas_inpi (
                    numero_processo,
                    nome_marca,
                    nome_normalizado,
                    chave_fonetica,
                    titular,
                    classe_nice,
                    status,
                    apresentacao,
                    data_deposito,
                    numero_revista
                ) VALUES (
                    :numero_processo,
                    :nome_marca,
                    :nome_normalizado,
                    :chave_fonetica,
                    :titular,
                    :classe_nice,
                    :status,
                    :apresentacao,
                    :data_deposito,
                    :numero_revista
                )
                ON DUPLICATE KEY UPDATE
                    nome_marca = VALUES(nome_marca),
                    nome_normalizado = VALUES(nome_normalizado),
                    chave_fonetica = VALUES(chave_fonetica),
                    titular = VALUES(titular),
                    status = VALUES(status),
                    apresentacao = VALUES(apresentacao),
                    data_deposito = VALUES(data_deposito),
                    numero_revista = VALUES(numero_revista),
                    atualizado = CURRENT_TIMESTAMP';

        if ($this->stmtSalvar === null) {
            $this->stmtSalvar = $this->conn->prepare($sql);
        }

        return $this->stmtSalvar->execute($dados);
    }

    public function buscarColisoes(string $termo, int $classeNice, float $limite = 70): array
    {
        if (!$this->tabelaExiste()) {
            return [];
        }

        $stmt = $this->conn->prepare(
            'SELECT
                numero_processo,
                nome_marca,
                titular,
                classe_nice,
                status,
                apresentacao,
                data_deposito,
                numero_revista
             FROM marcas_inpi
             WHERE classe_nice = :classe_nice
             ORDER BY atualizado DESC
             LIMIT 8000'
        );

        $stmt->execute([
            'classe_nice' => $classeNice
        ]);

        $resultados = [];

        while ($marca = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $analise = SimilaridadeHelper::analisar(
                $termo,
                $marca['nome_marca']
            );

            if (
                $analise['fonetica'] < $limite
                || $analise['textual'] < 60
            ) {
                continue;
            }

            $resultados[] = array_merge(
                $marca,
                $analise
            );
        }

        usort(
            $resultados,
            function (array $a, array $b): int {
                $comparacao = $b['similaridade'] <=> $a['similaridade'];

                if ($comparacao !== 0) {
                    return $comparacao;
                }

                $comparacao = $b['fonetica'] <=> $a['fonetica'];

                if ($comparacao !== 0) {
                    return $comparacao;
                }

                return $b['textual'] <=> $a['textual'];
            }
        );

        return array_slice($resultados, 0, 200);
    }

    public function resumoBase(): array
    {
        if (!$this->tabelaExiste()) {
            return [
                'total' => 0,
                'ultima_revista' => null,
                'atualizado' => null,
                'tabela_existe' => false
            ];
        }

        $stmt = $this->conn->query(
            'SELECT
                COUNT(*) AS total,
                MAX(numero_revista) AS ultima_revista,
                MAX(atualizado) AS atualizado
             FROM marcas_inpi'
        );

        $resumo = $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'total' => 0,
            'ultima_revista' => null,
            'atualizado' => null
        ];

        $resumo['tabela_existe'] = true;

        return $resumo;
    }
}
