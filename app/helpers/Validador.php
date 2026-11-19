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

    public static function tamanho(string $valor): int
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($valor, 'UTF-8');
        }
        $total = preg_match_all('/./us', $valor);
        return $total === false ? strlen($valor) : $total;
    }

    /** Retorna Y-m-d somente para data civil válida em dd/mm/aaaa. */
    public static function dataParaBanco(string $valor): ?string
    {
        $valor = trim($valor);
        if (!preg_match('/^([0-9]{2})\/([0-9]{2})\/([0-9]{4})$/D', $valor, $partes)) {
            return null;
        }
        $dia = (int) $partes[1];
        $mes = (int) $partes[2];
        $ano = (int) $partes[3];

        if ($ano < 1000 || !checkdate($mes, $dia, $ano)) {
            return null;
        }
        return sprintf('%04d-%02d-%02d', $ano, $mes, $dia);
    }

    public static function dataParaExibicao(?string $valor): string
    {
        $valor = trim((string) $valor);
        if (preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/D', $valor, $partes)
            && checkdate((int) $partes[2], (int) $partes[3], (int) $partes[1])) {
            return sprintf('%02d/%02d/%04d', (int) $partes[3], (int) $partes[2], (int) $partes[1]);
        }
        return $valor;
    }

    public static function validarDatasRegistro(array &$dados): array
    {
        $campos = [
            'data_deposito' => 'Data de Depósito',
            'data_concessao' => 'Data de Concessão',
            'data_vencimento' => 'Data de Vencimento',
            'data_proxima_anuidade' => 'Data da Próxima Anuidade',
            'data_renovacao' => 'Data de Renovação',
            'data_oposicao' => 'Data de Oposição',
            'data_prorrogacao' => 'Data de Prorrogação',
            'data_manifestacao' => 'Data de Manifestação',
        ];
        $erros = [];
        foreach ($campos as $campo => $rotulo) {
            if (!array_key_exists($campo, $dados)) {
                continue;
            }
            $valor = trim((string) ($dados[$campo] ?? ''));
            if ($valor === '') {
                $dados[$campo] = null;
                if ($campo === 'data_deposito') {
                    $erros[$campo] = 'Informe a Data de Depósito.';
                }
                continue;
            }
            $convertida = self::dataParaBanco($valor);
            if ($convertida === null) {
                $erros[$campo] = $rotulo . ' inválida. Informe uma data real em dd/mm/aaaa.';
            } else {
                $dados[$campo] = $convertida;
            }
        }

        if (!isset($erros['data_deposito']) && !empty($dados['data_deposito'])
            && $dados['data_deposito'] > date('Y-m-d')) {
            $erros['data_deposito'] = 'A Data de Depósito não pode ser uma data futura.';
        }

        if (!isset($erros['data_deposito']) && !empty($dados['data_deposito'])) {
            if (!isset($erros['data_vencimento']) && !empty($dados['data_vencimento'])
                && $dados['data_vencimento'] < $dados['data_deposito']) {
                $erros['data_vencimento'] = 'O vencimento não pode ser anterior à data de depósito.';
            }
            if (!isset($erros['data_concessao']) && !empty($dados['data_concessao'])
                && $dados['data_concessao'] < $dados['data_deposito']) {
                $erros['data_concessao'] = 'A concessão não pode ser anterior à data de depósito.';
            }
        }
        return $erros;
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
