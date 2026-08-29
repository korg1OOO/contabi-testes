<?php
namespace app\services;

use app\helpers\SimilaridadeHelper;
use app\repositories\DespachoRpiRepository;
use app\repositories\MarcaInpiRepository;
use app\repositories\MarcaRepository;
use app\repositories\NotificacaoRepository;
use app\repositories\PatenteRepository;
use RuntimeException;
use SimpleXMLElement;
use Smalot\PdfParser\Config;
use Smalot\PdfParser\Parser;
use XMLReader;
use ZipArchive;

class RpiImportacaoService
{
    private DespachoRpiRepository $despachoRepo;
    private NotificacaoRepository $notificacaoRepo;

    public function __construct()
    {
        $this->despachoRepo = new DespachoRpiRepository();
        $this->notificacaoRepo = new NotificacaoRepository();
    }

    public function importar(string $arquivo, string $nomeOriginal, string $tipo, int $usuarioId, ?string $numeroInformado = null, ?string $dataInformada = null): array
    {
        $temporarios = [];
        try {
            $arquivos = $this->prepararArquivos($arquivo, $nomeOriginal, $temporarios);
            $resultado = [
                'despachos' => 0,
                'marcas_indexadas' => 0,
                'duplicados' => 0,
                'numero_revista' => $numeroInformado,
                'data_publicacao' => $dataInformada
            ];

            if ($tipo === 'marcas') {
                $xml = $this->selecionarArquivo($arquivos, ['xml']);
                $txt = $this->selecionarArquivo($arquivos, ['txt']);
                if ($xml) {
                    $resultado = array_merge($resultado, $this->importarMarcas($xml, $usuarioId, $numeroInformado, $dataInformada));
                } elseif ($txt) {
                    $resultado = array_merge($resultado, $this->importarMarcasTexto($txt, $usuarioId, $numeroInformado, $dataInformada));
                } else {
                    throw new RuntimeException('O arquivo de marcas deve conter XML ou texto extraível de um PDF oficial da RPI.');
                }
            } elseif ($tipo === 'patentes') {
                $xml = $this->selecionarArquivo($arquivos, ['xml']);
                $txt = $this->selecionarArquivo($arquivos, ['txt']);
                if ($xml) {
                    $resultado = array_merge($resultado, $this->importarPatentesXml($xml, $usuarioId, $numeroInformado, $dataInformada));
                } elseif ($txt) {
                    $resultado = array_merge($resultado, $this->importarPatentesTxt($txt, $usuarioId, $numeroInformado, $dataInformada));
                } else {
                    throw new RuntimeException('O arquivo de patentes deve conter um XML ou TXT oficial da RPI.');
                }
            } else {
                throw new RuntimeException('Tipo de revista inválido.');
            }

            return $resultado;
        } finally {
            foreach (array_reverse($temporarios) as $caminho) {
                if (is_file($caminho)) {
                    @unlink($caminho);
                } elseif (is_dir($caminho)) {
                    $this->removerDiretorio($caminho);
                }
            }
        }
    }

    private function prepararArquivos(string $arquivo, string $nomeOriginal, array &$temporarios): array
    {
        $extensao = strtolower(pathinfo($nomeOriginal, PATHINFO_EXTENSION));

        if (in_array($extensao, ['xml', 'txt'], true)) {
            return [$arquivo];
        }

        if ($extensao === 'pdf') {
            return [$this->extrairTextoPdf($arquivo, $temporarios)];
        }

        if ($extensao !== 'zip') {
            throw new RuntimeException('Envie um arquivo ZIP, XML, TXT ou PDF.');
        }

        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('A extensão ZIP não está disponível no servidor.');
        }

        $diretorio = sys_get_temp_dir() . '/contabi-rpi-' . bin2hex(random_bytes(6));
        if (!mkdir($diretorio, 0777, true) && !is_dir($diretorio)) {
            throw new RuntimeException('Não foi possível preparar o arquivo enviado.');
        }
        $temporarios[] = $diretorio;

        $zip = new ZipArchive();
        if ($zip->open($arquivo) !== true) {
            throw new RuntimeException('Não foi possível abrir o arquivo ZIP.');
        }
        $zip->extractTo($diretorio);
        $zip->close();

        $arquivos = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($diretorio, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $item) {
            if (!$item->isFile()) {
                continue;
            }

            $ext = strtolower($item->getExtension());
            if (in_array($ext, ['xml', 'txt'], true)) {
                $arquivos[] = $item->getPathname();
            } elseif ($ext === 'pdf') {
                $arquivos[] = $this->extrairTextoPdf($item->getPathname(), $temporarios);
            }
        }

        if (!$arquivos) {
            throw new RuntimeException('O ZIP não contém XML, TXT ou PDF processável.');
        }

        return $arquivos;
    }

    private function extrairTextoPdf(string $arquivo, array &$temporarios): string
    {
        $tamanhoMaximoPdf = 30 * 1024 * 1024;
        $tamanho = filesize($arquivo);

        if ($tamanho !== false && $tamanho > $tamanhoMaximoPdf) {
            throw new RuntimeException(
                'O PDF enviado é muito grande para ser processado diretamente. ' .
                'Para revistas extensas, utilize o arquivo oficial em XML, TXT ou ZIP.'
            );
        }

        if (!class_exists(Parser::class)) {
            throw new RuntimeException(
                'O leitor de PDF não está disponível. Execute composer install.'
            );
        }

        try {
            $config = new Config();
            $config->setRetainImageContent(false);
            $config->setDecodeMemoryLimit(1000000);

            $parser = new Parser([], $config);
            $pdf = $parser->parseFile($arquivo);
            $texto = $pdf->getText();

            unset($pdf, $parser);

            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }
        } catch (\Throwable $e) {
            throw new RuntimeException(
                'Não foi possível ler o PDF enviado. ' .
                'Para revistas muito extensas, utilize XML, TXT ou ZIP.'
            );
        }

        $texto = trim(
            str_replace(
                ["\r\n", "\r"],
                "\n",
                $texto
            )
        );

        if ($texto === '') {
            throw new RuntimeException(
                'O PDF não possui texto extraível. ' .
                'Se ele for composto apenas por imagens, utilize outro arquivo oficial da RPI.'
            );
        }

        $temporario =
            sys_get_temp_dir() .
            '/contabi-rpi-pdf-' .
            bin2hex(random_bytes(8)) .
            '.txt';

        if (file_put_contents($temporario, $texto) === false) {
            throw new RuntimeException(
                'Não foi possível preparar o texto extraído do PDF.'
            );
        }

        $temporarios[] = $temporario;

        return $temporario;
    }

    private function selecionarArquivo(array $arquivos, array $extensoes): ?string
    {
        foreach ($arquivos as $arquivo) {
            if (in_array(strtolower(pathinfo($arquivo, PATHINFO_EXTENSION)), $extensoes, true)) {
                return $arquivo;
            }
        }
        return null;
    }

    private function importarMarcas(string $arquivo, int $usuarioId, ?string $numeroInformado, ?string $dataInformada): array
    {
        if (!class_exists(XMLReader::class)) {
            return $this->importarMarcasSimpleXml($arquivo, $usuarioId, $numeroInformado, $dataInformada);
        }
        $reader = new XMLReader();
        if (!$reader->open($arquivo, null, LIBXML_NONET | LIBXML_COMPACT)) {
            throw new RuntimeException('Não foi possível ler o XML de marcas.');
        }
        $numeroRevista = $numeroInformado;
        $dataPublicacao = $dataInformada;
        $indexadas = 0;
        $despachos = 0;
        $duplicados = 0;
        $inpiRepo = new MarcaInpiRepository();
        $marcaRepo = new MarcaRepository();

        while ($reader->read()) {
            if ($reader->nodeType === XMLReader::ELEMENT && $reader->localName === 'revista') {
                $numeroRevista = $numeroRevista ?: $reader->getAttribute('numero');
                $dataPublicacao = $dataPublicacao ?: $this->dataBanco($reader->getAttribute('dataPublicacao'));
            }
            if ($reader->nodeType !== XMLReader::ELEMENT || $reader->localName !== 'processo') {
                continue;
            }
            $xml = @simplexml_load_string($reader->readOuterXML(), SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOCDATA);
            if (!$xml) {
                continue;
            }
            $numeroProcesso = trim((string)$xml['numero']);
            $nomeMarca = trim((string)($xml->marca->nome ?? ''));
            if ($numeroProcesso === '' || $nomeMarca === '') {
                continue;
            }
            $titulares = [];
            foreach ($xml->titulares->titular ?? [] as $titular) {
                $nome = trim((string)$titular['nome-razao-social']);
                if ($nome !== '') {
                    $titulares[] = $nome;
                }
            }
            $titularTexto = implode('; ', $titulares);
            $apresentacao = trim((string)($xml->marca['apresentacao'] ?? ''));
            $dataDeposito = $this->dataBanco((string)($xml['data-deposito'] ?? ''));

            foreach ($xml->{'lista-classe-nice'}->{'classe-nice'} ?? [] as $classe) {
                $classeNice = (int)$classe['codigo'];
                if ($classeNice < 1 || $classeNice > 45) {
                    continue;
                }
                $status = trim((string)($classe->status ?? ''));
                $inpiRepo->salvar([
                    'numero_processo' => $numeroProcesso,
                    'nome_marca' => $nomeMarca,
                    'nome_normalizado' => SimilaridadeHelper::normalizar($nomeMarca),
                    'chave_fonetica' => SimilaridadeHelper::chaveFonetica($nomeMarca),
                    'titular' => $titularTexto ?: null,
                    'classe_nice' => $classeNice,
                    'status' => $status ?: null,
                    'apresentacao' => $apresentacao ?: null,
                    'data_deposito' => $dataDeposito,
                    'numero_revista' => $numeroRevista
                ]);
                $indexadas++;
            }

            $marca = $marcaRepo->findByNumeroProcessoAndUsuario($numeroProcesso, $usuarioId);
            if (!$marca) {
                continue;
            }
            foreach ($xml->despachos->despacho ?? [] as $despachoXml) {
                $codigo = trim((string)$despachoXml['codigo']);
                $descricao = trim((string)$despachoXml['nome']);
                $dados = $this->dadosDespacho($numeroRevista, $dataPublicacao, $numeroProcesso, $codigo, $descricao, (int)$marca['id'], null);
                if ($this->despachoRepo->existe($dados)) {
                    $duplicados++;
                    continue;
                }
                $this->despachoRepo->save($dados);
                $this->notificar($dados, $usuarioId);
                $despachos++;
            }
        }
        $reader->close();
        return [
            'despachos' => $despachos,
            'marcas_indexadas' => $indexadas,
            'duplicados' => $duplicados,
            'numero_revista' => $numeroRevista,
            'data_publicacao' => $dataPublicacao
        ];
    }

    private function importarMarcasTexto(string $arquivo, int $usuarioId, ?string $numeroInformado, ?string $dataInformada): array
    {
        $conteudo = file_get_contents($arquivo);
        if ($conteudo === false) {
            throw new RuntimeException('Não foi possível ler o texto da RPI de marcas.');
        }

        if (function_exists('mb_check_encoding') && !mb_check_encoding($conteudo, 'UTF-8')) {
            $conteudo = mb_convert_encoding($conteudo, 'UTF-8', 'Windows-1252,ISO-8859-1');
        }

        $numeroRevista = $numeroInformado;
        $dataPublicacao = $dataInformada;

        if (!$numeroRevista && preg_match('/(?:RPI|Revista)[^\d]{0,30}(\d{3,5})/iu', $conteudo, $m)) {
            $numeroRevista = $m[1];
        }
        if (!$dataPublicacao && preg_match('/\b(\d{2}\/\d{2}\/\d{4})\b/u', $conteudo, $m)) {
            $dataPublicacao = $this->dataBanco($m[1]);
        }
        if (!$numeroRevista || !$dataPublicacao) {
            throw new RuntimeException('Não foi possível identificar o número ou a data da revista no PDF. Informe esses dados no formulário.');
        }

        $linhas = preg_split('/\R/u', $conteudo) ?: [];
        $marcaRepo = new MarcaRepository();
        $despachos = 0;
        $duplicados = 0;
        $processados = [];

        foreach ($linhas as $indice => $linha) {
            if (!preg_match_all('/\b\d{9}\b/u', $linha, $matches)) {
                continue;
            }

            foreach ($matches[0] as $numeroProcesso) {
                $marca = $marcaRepo->findByNumeroProcessoAndUsuario($numeroProcesso, $usuarioId);
                if (!$marca) {
                    continue;
                }

                $inicio = max(0, $indice - 2);
                $bloco = implode("\n", array_slice($linhas, $inicio, 11));
                [$codigo, $descricao] = $this->extrairDespachoTexto($bloco, $numeroProcesso);

                if ($codigo === '' && $descricao === '') {
                    continue;
                }

                $chave = $numeroProcesso . '|' . $codigo . '|' . $descricao;
                if (isset($processados[$chave])) {
                    continue;
                }
                $processados[$chave] = true;

                $dados = $this->dadosDespacho(
                    $numeroRevista,
                    $dataPublicacao,
                    $numeroProcesso,
                    $codigo,
                    $descricao,
                    (int)$marca['id'],
                    null
                );

                if ($this->despachoRepo->existe($dados)) {
                    $duplicados++;
                    continue;
                }

                $this->despachoRepo->save($dados);
                $this->notificar($dados, $usuarioId);
                $despachos++;
            }
        }

        return [
            'despachos' => $despachos,
            'marcas_indexadas' => 0,
            'duplicados' => $duplicados,
            'numero_revista' => $numeroRevista,
            'data_publicacao' => $dataPublicacao
        ];
    }

    private function extrairDespachoTexto(string $bloco, string $numeroProcesso): array
    {
        $codigo = '';
        $descricao = '';

        foreach ([
            '/(?:c[oó]digo\s*(?:do\s*)?despacho|despacho)\s*[:\-]?\s*([0-9A-Z.\-\/]{1,30})/iu',
            '/\[([0-9A-Z.\-\/]{1,30})\]/iu'
        ] as $padrao) {
            if (preg_match($padrao, $bloco, $m)) {
                $codigo = trim($m[1]);
                break;
            }
        }

        if (preg_match('/(?:descri[cç][aã]o|nome\s*do\s*despacho)\s*[:\-]\s*(.+)$/imu', $bloco, $m)) {
            $descricao = trim(preg_replace('/\s+/u', ' ', $m[1]));
        }

        if ($descricao === '') {
            foreach (preg_split('/\R/u', $bloco) ?: [] as $linha) {
                $linha = trim(preg_replace('/\s+/u', ' ', $linha));
                if ($linha === '' || str_contains($linha, $numeroProcesso)) {
                    continue;
                }
                if ($codigo !== '' && str_contains($linha, $codigo)) {
                    $resto = trim(str_replace($codigo, '', $linha), " :-\t");
                    if (mb_strlen($resto) >= 8) {
                        $descricao = $resto;
                        break;
                    }
                }
            }
        }

        return [$codigo, $descricao];
    }

    private function importarPatentesXml(string $arquivo, int $usuarioId, ?string $numeroInformado, ?string $dataInformada): array
    {
        if (!class_exists(XMLReader::class)) {
            return $this->importarPatentesSimpleXml($arquivo, $usuarioId, $numeroInformado, $dataInformada);
        }
        $reader = new XMLReader();
        if (!$reader->open($arquivo, null, LIBXML_NONET | LIBXML_COMPACT)) {
            throw new RuntimeException('Não foi possível ler o XML de patentes.');
        }
        $numeroRevista = $numeroInformado;
        $dataPublicacao = $dataInformada;
        $despachos = 0;
        $duplicados = 0;
        $patenteRepo = new PatenteRepository();

        while ($reader->read()) {
            if ($reader->nodeType === XMLReader::ELEMENT && $reader->localName === 'revista') {
                $numeroRevista = $numeroRevista ?: $reader->getAttribute('numero');
                $dataPublicacao = $dataPublicacao ?: $this->dataBanco($reader->getAttribute('dataPublicacao'));
            }
            if ($reader->nodeType !== XMLReader::ELEMENT || $reader->localName !== 'despacho') {
                continue;
            }
            $xml = @simplexml_load_string($reader->readOuterXML(), SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOCDATA);
            if (!$xml) {
                continue;
            }
            $numeroProcesso = trim((string)($xml->{'processo-patente'}->numero ?? ''));
            if ($numeroProcesso === '') {
                continue;
            }
            $patente = $patenteRepo->findByNumeroProcessoAndUsuario($numeroProcesso, $usuarioId);
            if (!$patente) {
                continue;
            }
            $codigo = trim((string)($xml->codigo ?? ''));
            $descricao = trim((string)($xml->comentario ?? $xml->titulo ?? ''));
            $dados = $this->dadosDespacho($numeroRevista, $dataPublicacao, $numeroProcesso, $codigo, $descricao, null, (int)$patente['id']);
            if ($this->despachoRepo->existe($dados)) {
                $duplicados++;
                continue;
            }
            $this->despachoRepo->save($dados);
            $this->notificar($dados, $usuarioId);
            $despachos++;
        }
        $reader->close();
        return [
            'despachos' => $despachos,
            'marcas_indexadas' => 0,
            'duplicados' => $duplicados,
            'numero_revista' => $numeroRevista,
            'data_publicacao' => $dataPublicacao
        ];
    }

    private function importarMarcasSimpleXml(string $arquivo, int $usuarioId, ?string $numeroInformado, ?string $dataInformada): array
    {
        $handle = fopen($arquivo, 'rb');
        if (!$handle) {
            throw new RuntimeException('Não foi possível ler o XML de marcas.');
        }
        $numeroRevista = $numeroInformado;
        $dataPublicacao = $dataInformada;
        $indexadas = 0;
        $despachos = 0;
        $duplicados = 0;
        $inpiRepo = new MarcaInpiRepository();
        $marcaRepo = new MarcaRepository();
        $bloco = '';
        $capturando = false;

        while (($linha = fgets($handle)) !== false) {
            if (!$numeroRevista && preg_match('/<revista[^>]*numero="([^"]+)"/i', $linha, $m)) {
                $numeroRevista = $m[1];
            }
            if (!$dataPublicacao && preg_match('/<revista[^>]*dataPublicacao="([^"]+)"/i', $linha, $m)) {
                $dataPublicacao = $this->dataBanco($m[1]);
            }
            if (!$capturando && preg_match('/<processo\s/i', $linha)) {
                $capturando = true;
                $bloco = '';
            }
            if ($capturando) {
                $bloco .= $linha;
                if (!str_contains($linha, '</processo>')) {
                    continue;
                }
                $capturando = false;
                $processo = @simplexml_load_string($bloco, SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOCDATA);
                $bloco = '';
                if (!$processo) {
                    continue;
                }
                $numeroProcesso = trim((string)$processo['numero']);
                $nomeMarca = trim((string)($processo->marca->nome ?? ''));
                if ($numeroProcesso === '' || $nomeMarca === '') {
                    continue;
                }
                $titulares = [];
                foreach ($processo->titulares->titular ?? [] as $titular) {
                    $nome = trim((string)$titular['nome-razao-social']);
                    if ($nome !== '') {
                        $titulares[] = $nome;
                    }
                }
                $titularTexto = implode('; ', $titulares);
                $apresentacao = trim((string)($processo->marca['apresentacao'] ?? ''));
                $dataDeposito = $this->dataBanco((string)($processo['data-deposito'] ?? ''));
                foreach ($processo->{'lista-classe-nice'}->{'classe-nice'} ?? [] as $classe) {
                    $classeNice = (int)$classe['codigo'];
                    if ($classeNice < 1 || $classeNice > 45) {
                        continue;
                    }
                    $inpiRepo->salvar([
                        'numero_processo' => $numeroProcesso,
                        'nome_marca' => $nomeMarca,
                        'nome_normalizado' => SimilaridadeHelper::normalizar($nomeMarca),
                        'chave_fonetica' => SimilaridadeHelper::chaveFonetica($nomeMarca),
                        'titular' => $titularTexto ?: null,
                        'classe_nice' => $classeNice,
                        'status' => trim((string)($classe->status ?? '')) ?: null,
                        'apresentacao' => $apresentacao ?: null,
                        'data_deposito' => $dataDeposito,
                        'numero_revista' => $numeroRevista
                    ]);
                    $indexadas++;
                }
                $marca = $marcaRepo->findByNumeroProcessoAndUsuario($numeroProcesso, $usuarioId);
                if (!$marca) {
                    continue;
                }
                foreach ($processo->despachos->despacho ?? [] as $despachoXml) {
                    $dados = $this->dadosDespacho($numeroRevista, $dataPublicacao, $numeroProcesso, trim((string)$despachoXml['codigo']), trim((string)$despachoXml['nome']), (int)$marca['id'], null);
                    if ($this->despachoRepo->existe($dados)) {
                        $duplicados++;
                        continue;
                    }
                    $this->despachoRepo->save($dados);
                    $this->notificar($dados, $usuarioId);
                    $despachos++;
                }
            }
        }
        fclose($handle);
        return ['despachos' => $despachos, 'marcas_indexadas' => $indexadas, 'duplicados' => $duplicados, 'numero_revista' => $numeroRevista, 'data_publicacao' => $dataPublicacao];
    }

    private function importarPatentesSimpleXml(string $arquivo, int $usuarioId, ?string $numeroInformado, ?string $dataInformada): array
    {
        $handle = fopen($arquivo, 'rb');
        if (!$handle) {
            throw new RuntimeException('Não foi possível ler o XML de patentes.');
        }
        $numeroRevista = $numeroInformado;
        $dataPublicacao = $dataInformada;
        $despachos = 0;
        $duplicados = 0;
        $patenteRepo = new PatenteRepository();
        $bloco = '';
        $capturando = false;

        while (($linha = fgets($handle)) !== false) {
            if (!$numeroRevista && preg_match('/<revista[^>]*numero="([^"]+)"/i', $linha, $m)) {
                $numeroRevista = $m[1];
            }
            if (!$dataPublicacao && preg_match('/<revista[^>]*dataPublicacao="([^"]+)"/i', $linha, $m)) {
                $dataPublicacao = $this->dataBanco($m[1]);
            }
            if (!$capturando && preg_match('/<despacho>/i', trim($linha))) {
                $capturando = true;
                $bloco = '';
            }
            if ($capturando) {
                $bloco .= $linha;
                if (!str_contains($linha, '</despacho>')) {
                    continue;
                }
                $capturando = false;
                $despachoXml = @simplexml_load_string($bloco, SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOCDATA);
                $bloco = '';
                if (!$despachoXml) {
                    continue;
                }
                $numeroProcesso = trim((string)($despachoXml->{'processo-patente'}->numero ?? ''));
                if ($numeroProcesso === '') {
                    continue;
                }
                $patente = $patenteRepo->findByNumeroProcessoAndUsuario($numeroProcesso, $usuarioId);
                if (!$patente) {
                    continue;
                }
                $dados = $this->dadosDespacho($numeroRevista, $dataPublicacao, $numeroProcesso, trim((string)($despachoXml->codigo ?? '')), trim((string)($despachoXml->comentario ?? $despachoXml->titulo ?? '')), null, (int)$patente['id']);
                if ($this->despachoRepo->existe($dados)) {
                    $duplicados++;
                    continue;
                }
                $this->despachoRepo->save($dados);
                $this->notificar($dados, $usuarioId);
                $despachos++;
            }
        }
        fclose($handle);
        return ['despachos' => $despachos, 'marcas_indexadas' => 0, 'duplicados' => $duplicados, 'numero_revista' => $numeroRevista, 'data_publicacao' => $dataPublicacao];
    }

    private function importarPatentesTxt(string $arquivo, int $usuarioId, ?string $numeroInformado, ?string $dataInformada): array
    {
        $conteudo = file_get_contents($arquivo);
        if ($conteudo === false) {
            throw new RuntimeException('Não foi possível ler o TXT de patentes.');
        }
        if (function_exists('mb_check_encoding') && !mb_check_encoding($conteudo, 'UTF-8')) {
            $conteudo = mb_convert_encoding($conteudo, 'UTF-8', 'Windows-1252,ISO-8859-1');
        }
        preg_match('/N[oº°]?\s*(\d+)\s+de\s+(\d{2}\/\d{2}\/\d{4})/iu', $conteudo, $cabecalho);
        $numeroRevista = $numeroInformado ?: ($cabecalho[1] ?? null);
        $dataPublicacao = $dataInformada ?: $this->dataBanco($cabecalho[2] ?? null);
        $blocos = preg_split('/(?=\(Cd\)\s)/u', $conteudo) ?: [];
        $patenteRepo = new PatenteRepository();
        $despachos = 0;
        $duplicados = 0;

        foreach ($blocos as $bloco) {
            if (!preg_match('/\((?:21|11)\)\s*([^\r\n]+)/u', $bloco, $numeroMatch)) {
                continue;
            }
            $numeroProcesso = trim(preg_replace('/\s+(?:A\d|B\d|C\d|U\d)\s*$/i', '', $numeroMatch[1]));
            $patente = $patenteRepo->findByNumeroProcessoAndUsuario($numeroProcesso, $usuarioId);
            if (!$patente) {
                continue;
            }
            preg_match('/\[([0-9A-Za-z.\-]+)\]\s*$/um', trim($bloco), $codigoMatch);
            preg_match('/\(co\)\s*(.+?)(?=\n\([A-Za-z0-9]{2}\)|\z)/us', $bloco, $descricaoMatch);
            preg_match('/\(Cd\)\s*([^\r\n]+)/u', $bloco, $secaoMatch);
            $codigo = trim($codigoMatch[1] ?? '');
            $descricao = trim($descricaoMatch[1] ?? $secaoMatch[1] ?? 'Despacho publicado na RPI');
            $dados = $this->dadosDespacho($numeroRevista, $dataPublicacao, $numeroProcesso, $codigo, $descricao, null, (int)$patente['id']);
            if ($this->despachoRepo->existe($dados)) {
                $duplicados++;
                continue;
            }
            $this->despachoRepo->save($dados);
            $this->notificar($dados, $usuarioId);
            $despachos++;
        }
        return [
            'despachos' => $despachos,
            'marcas_indexadas' => 0,
            'duplicados' => $duplicados,
            'numero_revista' => $numeroRevista,
            'data_publicacao' => $dataPublicacao
        ];
    }

    private function dadosDespacho(?string $numeroRevista, ?string $dataPublicacao, string $numeroProcesso, string $codigo, string $descricao, ?int $marcaId, ?int $patenteId): array
    {
        if (!$numeroRevista || !$dataPublicacao) {
            throw new RuntimeException('Não foi possível identificar o número ou a data da revista. Informe esses dados no formulário.');
        }
        return [
            'numero_revista' => $numeroRevista,
            'data_publicacao' => $dataPublicacao,
            'numero_processo' => $numeroProcesso,
            'codigo_despacho' => $codigo,
            'descricao' => $descricao,
            'marca_id' => $marcaId,
            'patente_id' => $patenteId,
            'processado' => 0
        ];
    }

    private function notificar(array $despacho, int $usuarioId): void
    {
        $this->notificacaoRepo->criarNotificacao([
            'usuario_id' => $usuarioId,
            'tipo' => 'novo_despacho',
            'titulo' => 'Novo despacho na RPI',
            'mensagem' => 'Processo ' . $despacho['numero_processo'] . ' recebeu o despacho ' . ($despacho['codigo_despacho'] ?: 'sem código') . '.',
            'link' => URL_BASE . '/rpi/listar'
        ]);
    }

    private function dataBanco(?string $data): ?string
    {
        $data = trim((string)$data);
        if ($data === '') {
            return null;
        }
        $formatos = ['d/m/Y', 'Y-m-d'];
        foreach ($formatos as $formato) {
            $objeto = \DateTime::createFromFormat($formato, $data);
            if ($objeto && $objeto->format($formato) === $data) {
                return $objeto->format('Y-m-d');
            }
        }
        return null;
    }

    private function removerDiretorio(string $diretorio): void
    {
        $itens = scandir($diretorio) ?: [];
        foreach ($itens as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $caminho = $diretorio . DIRECTORY_SEPARATOR . $item;
            if (is_dir($caminho)) {
                $this->removerDiretorio($caminho);
            } else {
                @unlink($caminho);
            }
        }
        @rmdir($diretorio);
    }
}
