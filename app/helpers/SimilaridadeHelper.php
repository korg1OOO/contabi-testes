<?php

namespace app\helpers;

class SimilaridadeHelper
{
    public static function normalizar(string $texto): string
    {
        $texto = trim($texto);

        $texto = function_exists('mb_strtolower')
            ? mb_strtolower($texto, 'UTF-8')
            : strtolower($texto);

        $convertido = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);

        if ($convertido !== false) {
            $texto = $convertido;
        }

        $texto = preg_replace('/[^a-z0-9\s]/', ' ', $texto);

        return trim(preg_replace('/\s+/', ' ', $texto));
    }

    public static function semEspacos(string $texto): string
    {
        return str_replace(' ', '', self::normalizar($texto));
    }

    public static function chaveFonetica(string $texto): string
    {
        $texto = self::normalizar($texto);

        $substituicoes = [
            '/ph/' => 'f',
            '/th/' => 't',
            '/sh|ch/' => 'x',
            '/lh/' => 'li',
            '/nh/' => 'ni',
            '/rr/' => 'r',
            '/ss/' => 's',
            '/qu/' => 'k',
            '/gu(?=[ei])/' => 'g',
            '/c(?=[ei])/' => 's',
            '/g(?=[ei])/' => 'j',
            '/[cqk]/' => 'k',
            '/[sz]/' => 's',
            '/y/' => 'i',
            '/w/' => 'v',
            '/h/' => '',
            '/m(?=\s|$)/' => 'n'
        ];

        foreach ($substituicoes as $padrao => $valor) {
            $texto = preg_replace($padrao, $valor, $texto);
        }

        $palavras = array_values(array_filter(explode(' ', trim($texto))));
        $chaves = [];

        foreach ($palavras as $palavra) {
            if ($palavra === '') {
                continue;
            }

            $primeira = $palavra[0] ?? '';
            $resto = substr($palavra, 1);
            $resto = preg_replace('/[aeiou]/', '', $resto);
            $resto = preg_replace('/(.)\1+/', '$1', $resto);
            $chaves[] = $primeira . $resto;
        }

        return implode(' ', $chaves);
    }

    public static function textual(string $a, string $b): float
    {
        return self::melhorComparacao($a, $b, false);
    }

    public static function fonetica(string $a, string $b): float
    {
        return self::melhorComparacao($a, $b, true);
    }

    private static function melhorComparacao(string $termo, string $nome, bool $fonetica): float
    {
        $termoNormalizado = self::normalizar($termo);
        $nomeNormalizado = self::normalizar($nome);

        if ($termoNormalizado === '' || $nomeNormalizado === '') {
            return 0;
        }

        $tokensTermo = array_values(array_filter(explode(' ', $termoNormalizado)));
        $tokensNome = array_values(array_filter(explode(' ', $nomeNormalizado)));

        $melhor = self::compararDireto($termoNormalizado, $nomeNormalizado, $fonetica);
        $quantidadeTokens = count($tokensTermo);

        if ($quantidadeTokens === 0) {
            return round($melhor, 2);
        }

        if ($quantidadeTokens === 1) {
            foreach ($tokensNome as $tokenNome) {
                $score = self::compararDireto($tokensTermo[0], $tokenNome, $fonetica);

                if ($score > $melhor) {
                    $melhor = $score;
                }
            }

            return round($melhor, 2);
        }

        $totalTokensNome = count($tokensNome);

        if ($totalTokensNome >= $quantidadeTokens) {
            for ($i = 0; $i <= $totalTokensNome - $quantidadeTokens; $i++) {
                $janela = implode(' ', array_slice($tokensNome, $i, $quantidadeTokens));
                $score = self::compararDireto($termoNormalizado, $janela, $fonetica);

                if ($score > $melhor) {
                    $melhor = $score;
                }
            }
        }

        return round($melhor, 2);
    }

    private static function compararDireto(string $a, string $b, bool $fonetica): float
    {
        if ($fonetica) {
            $a = str_replace(' ', '', self::chaveFonetica($a));
            $b = str_replace(' ', '', self::chaveFonetica($b));

            return self::percentualLevenshtein($a, $b);
        }

        $a = self::semEspacos($a);
        $b = self::semEspacos($b);

        if ($a === '' || $b === '') {
            return 0;
        }

        similar_text($a, $b, $percentual);

        return round($percentual, 2);
    }

    private static function percentualLevenshtein(string $a, string $b): float
    {
        if ($a === '' || $b === '') {
            return 0;
        }

        $maiorComprimento = max(strlen($a), strlen($b));

        if ($maiorComprimento === 0) {
            return 100;
        }

        $distancia = levenshtein($a, $b);
        $percentual = (1 - ($distancia / $maiorComprimento)) * 100;

        return round(max(0, $percentual), 2);
    }

    public static function palavraChave(string $termo, string $nome): float
    {
        $termoNormalizado = self::normalizar($termo);
        $nomeNormalizado = self::normalizar($nome);

        if ($termoNormalizado === '' || $nomeNormalizado === '') {
            return 0;
        }

        if (str_contains($nomeNormalizado, $termoNormalizado)) {
            return 100;
        }

        $tokensTermo = array_values(array_filter(
            explode(' ', $termoNormalizado),
            fn($valor) => strlen($valor) > 1
        ));

        $tokensNome = array_values(array_filter(
            explode(' ', $nomeNormalizado),
            fn($valor) => strlen($valor) > 1
        ));

        if (!$tokensTermo || !$tokensNome) {
            return 0;
        }

        $encontrados = 0;

        foreach ($tokensTermo as $token) {
            foreach ($tokensNome as $tokenNome) {
                if (
                    $token === $tokenNome
                    || str_contains($tokenNome, $token)
                    || str_contains($token, $tokenNome)
                ) {
                    $encontrados++;
                    break;
                }
            }
        }

        return round(($encontrados / count($tokensTermo)) * 100, 2);
    }

    public static function analisar(string $termo, string $nome): array
    {
        $textual = self::textual($termo, $nome);
        $fonetica = self::fonetica($termo, $nome);
        $palavraChave = self::palavraChave($termo, $nome);
        $similaridade = max($fonetica, $textual);
        $criterios = [];

        if ($fonetica >= 70) {
            $criterios[] = 'Fonética';
        }

        if ($textual >= 70) {
            $criterios[] = 'Textual';
        }

        if ($palavraChave >= 70) {
            $criterios[] = 'Palavra-chave';
        }

        return [
            'textual' => $textual,
            'fonetica' => $fonetica,
            'palavra_chave' => $palavraChave,
            'similaridade' => round($similaridade, 2),
            'criterio' => implode(', ', $criterios)
        ];
    }
}
