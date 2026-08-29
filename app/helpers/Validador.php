<?php

namespace app\helpers;

class Validador
{
    private array $erros = [];

    public function obrigatorio(string $campo, mixed $valor, ?string $mensagem = null)
    {
        if (empty($valor) && $valor !== '0') {
            $this->erros[$campo] = $mensagem ?? "O campo {$campo} é obrigatório";
        }

        return $this;
    }

    public function temErros(): bool
    {
        return !empty($this->erros);
    }

    public function getErros()
    {
        return $this->erros;
    }

    public static function cpfValido(string $cpf): bool
    {
        $cpf = preg_replace('/\D/', '', $cpf) ?? '';

        if (strlen($cpf) !== 11) {
            return false;
        }

        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $soma = 0;

            for ($i = 0; $i < $t; $i++) {
                $soma += (int) $cpf[$i] * (($t + 1) - $i);
            }

            $digito = ((10 * $soma) % 11) % 10;

            if ((int) $cpf[$t] !== $digito) {
                return false;
            }
        }

        return true;
    }

    public static function cnpjValido(string $cnpj): bool
    {
        $cnpj = preg_replace('/\D/', '', $cnpj) ?? '';

        if (strlen($cnpj) !== 14) {
            return false;
        }

        if (preg_match('/^(\d)\1{13}$/', $cnpj)) {
            return false;
        }

        $calcularDigito = static function (string $base, array $pesos): int {
            $soma = 0;

            foreach ($pesos as $indice => $peso) {
                $soma += (int) $base[$indice] * $peso;
            }

            $resto = $soma % 11;

            return $resto < 2 ? 0 : 11 - $resto;
        };

        $primeiro = $calcularDigito(
            substr($cnpj, 0, 12),
            [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]
        );

        if ((int) $cnpj[12] !== $primeiro) {
            return false;
        }

        $segundo = $calcularDigito(
            substr($cnpj, 0, 13),
            [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]
        );

        return (int) $cnpj[13] === $segundo;
    }
}
