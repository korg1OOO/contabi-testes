<?php

namespace app\services;

class SimplePdfService
{
    public function gerar(string $titulo, array $dados): string
    {
        $linhas = [
            $titulo,
            'Gerado em ' . date('d/m/Y H:i'),
            str_repeat('-', 95)
        ];

        if (!$dados) {
            $linhas[] = 'Nenhum registro encontrado.';
        } else {
            $cabecalhos = array_keys($dados[0]);
            $linhas[] = implode(' | ', $cabecalhos);
            $linhas[] = str_repeat('-', 95);

            foreach ($dados as $registro) {
                $texto = implode(
                    ' | ',
                    array_map(
                        fn($valor) => $this->normalizarTexto((string) $valor),
                        $registro
                    )
                );

                foreach ($this->quebrar($texto, 105) as $linha) {
                    $linhas[] = $linha;
                }
            }
        }

        $paginas = array_chunk($linhas, 45);

        $objetos = [];

        $objetos[1] = '<< /Type /Catalog /Pages 2 0 R >>';

        $filhos = [];
        $proximo = 4;

        foreach ($paginas as $pagina) {
            $paginaId = $proximo++;
            $conteudoId = $proximo++;

            $filhos[] = $paginaId . ' 0 R';

            $stream = "BT\n";
            $stream .= "/F1 9 Tf\n";
            $stream .= "40 800 Td\n";

            foreach ($pagina as $indice => $linha) {
                if ($indice > 0) {
                    $stream .= "0 -16 Td\n";
                }

                $stream .= '('
                    . $this->prepararTextoPdf($linha)
                    . ") Tj\n";
            }

            $stream .= 'ET';

            $objetos[$paginaId] =
                '<< /Type /Page'
                . ' /Parent 2 0 R'
                . ' /MediaBox [0 0 595 842]'
                . ' /Resources <<'
                . ' /Font << /F1 3 0 R >>'
                . ' >>'
                . ' /Contents ' . $conteudoId . ' 0 R'
                . ' >>';

            $objetos[$conteudoId] =
                '<< /Length ' . strlen($stream) . " >>\n"
                . "stream\n"
                . $stream
                . "\nendstream";
        }

        $objetos[2] =
            '<< /Type /Pages'
            . ' /Kids [' . implode(' ', $filhos) . ']'
            . ' /Count ' . count($filhos)
            . ' >>';

        $objetos[3] =
            '<< /Type /Font'
            . ' /Subtype /Type1'
            . ' /BaseFont /Helvetica'
            . ' /Encoding /WinAnsiEncoding'
            . ' >>';

        ksort($objetos);

        $pdf = "%PDF-1.4\n";

        $offsets = [0];

        foreach ($objetos as $id => $conteudo) {
            $offsets[$id] = strlen($pdf);

            $pdf .= $id
                . " 0 obj\n"
                . $conteudo
                . "\nendobj\n";
        }

        $xref = strlen($pdf);
        $max = max(array_keys($objetos));

        $pdf .= "xref\n";
        $pdf .= '0 ' . ($max + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= $max; $i++) {
            $pdf .= sprintf(
                '%010d 00000 n ',
                $offsets[$i] ?? 0
            ) . "\n";
        }

        $pdf .= "trailer\n";
        $pdf .= '<< /Size ' . ($max + 1) . ' /Root 1 0 R >>' . "\n";
        $pdf .= "startxref\n";
        $pdf .= $xref . "\n";
        $pdf .= "%%EOF";

        return $pdf;
    }

    private function normalizarTexto(string $texto): string
    {
        return preg_replace('/\s+/', ' ', trim($texto)) ?? $texto;
    }

    private function quebrar(string $texto, int $limite): array
    {
        return explode(
            "\n",
            wordwrap($texto, $limite, "\n", true)
        );
    }

    private function prepararTextoPdf(string $texto): string
    {
        $texto = $this->normalizarTexto($texto);

        $texto = mb_convert_encoding(
            $texto,
            'Windows-1252',
            'UTF-8'
        );

        return str_replace(
            ['\\', '(', ')'],
            ['\\\\', '\\(', '\\)'],
            $texto
        );
    }
}