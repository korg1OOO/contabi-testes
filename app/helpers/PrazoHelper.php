<?php

namespace app\helpers;

class PrazoHelper
{
    public static function diasRestantes(string $dataVencimento): int
    {
        $hoje = new \DateTimeImmutable('today');
        $vencimento = (new \DateTimeImmutable($dataVencimento))->setTime(0, 0);

        return (int) $hoje->diff($vencimento)->format('%r%a');
    }

    public static function situacao(string $dataVencimento, string $status = 'pendente'): array
    {
        if ($status === 'cumprido') {
            return ['chave' => 'cumprido', 'classe' => 'prazo-concluido', 'rotulo' => 'Cumprido', 'texto' => 'Cumprido'];
        }

        if ($status === 'cancelado') {
            return ['chave' => 'cancelado', 'classe' => 'prazo-concluido', 'rotulo' => 'Cancelado', 'texto' => 'Cancelado'];
        }

        $dias = self::diasRestantes($dataVencimento);

        if ($dias < 0) {
            $n = abs($dias);
            return [
                'chave' => 'vencido',
                'classe' => 'prazo-vencido',
                'rotulo' => 'Vencido',
                'texto' => 'Vencido há ' . $n . ($n === 1 ? ' dia' : ' dias'),
            ];
        }

        $texto = match ($dias) {
            0 => 'Vence hoje',
            1 => 'Vence amanhã',
            default => 'Vence em ' . $dias . ' dias',
        };

        if ($dias <= 7) {
            return ['chave' => 'urgente', 'classe' => 'prazo-urgente', 'rotulo' => 'Urgente', 'texto' => $texto];
        }

        if ($dias <= 15) {
            return ['chave' => 'atencao', 'classe' => 'prazo-atencao', 'rotulo' => 'Atenção', 'texto' => $texto];
        }

        return ['chave' => 'no_prazo', 'classe' => 'prazo-ok', 'rotulo' => 'No prazo', 'texto' => $texto];
    }
}
