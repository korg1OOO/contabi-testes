<?php

namespace app\database;

use PDO;
use PDOException;

class DatabaseMigrator
{
    public function migrate(PDO $connection): void
    {
        $statement = $connection->prepare(
            "SELECT COUNT(*)
             FROM information_schema.tables
             WHERE table_schema = DATABASE()
               AND table_name = 'prazos'"
        );
        $statement->execute();

        if ((int) $statement->fetchColumn() === 0) {
            return;
        }

        $statement = $connection->prepare(
            "SELECT COUNT(*)
             FROM information_schema.columns
             WHERE table_schema = DATABASE()
               AND table_name = 'prazos'
               AND column_name = 'origem'"
        );
        $statement->execute();

        if ((int) $statement->fetchColumn() > 0) {
            return;
        }

        try {
            $connection->exec(
                "ALTER TABLE prazos
                 ADD COLUMN origem ENUM('manual', 'ativo')
                 NOT NULL DEFAULT 'manual' AFTER notificado"
            );
        } catch (PDOException $exception) {
            // Another request may have applied this migration concurrently.
            $statement->execute();
            if ((int) $statement->fetchColumn() === 0) {
                throw $exception;
            }
        }

        $connection->exec(
            "UPDATE prazos p
             INNER JOIN marcas m ON p.marca_id = m.id
             SET p.origem = 'ativo'
             WHERE p.status = 'pendente'
               AND p.observacoes IS NULL
               AND (
                    (p.tipo = 'anuidade' AND p.data_vencimento = m.data_proxima_anuidade)
                    OR (p.tipo = 'renovacao_marca' AND p.data_vencimento = m.data_renovacao)
                    OR (p.tipo = 'oposicao' AND p.data_vencimento = m.data_oposicao)
                    OR (p.tipo = 'prorrogacao' AND p.data_vencimento = m.data_prorrogacao)
                    OR (p.tipo = 'manifestacao' AND p.data_vencimento = m.data_manifestacao)
                    OR (p.tipo = 'vencimento' AND p.data_vencimento = m.data_vencimento)
               )"
        );

        $connection->exec(
            "UPDATE prazos p
             INNER JOIN patentes pat ON p.patente_id = pat.id
             SET p.origem = 'ativo'
             WHERE p.status = 'pendente'
               AND p.observacoes IS NULL
               AND (
                    (p.tipo = 'anuidade' AND p.data_vencimento = pat.data_proxima_anuidade)
                    OR (p.tipo = 'manifestacao' AND p.data_vencimento = pat.data_manifestacao)
                    OR (p.tipo = 'prorrogacao' AND p.data_vencimento = pat.data_prorrogacao)
                    OR (p.tipo = 'vencimento' AND p.data_vencimento = pat.data_vencimento)
               )"
        );
    }
}
