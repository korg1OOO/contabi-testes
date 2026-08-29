<?php
namespace app\services;

use app\repositories\DespachoRpiRepository;
use app\repositories\NotificacaoRepository;
use DOMDocument;
use DOMXPath;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SimpleXMLElement;
use ZipArchive;

class RpiAutomacaoService
{
    private string $paginaRpi = 'https://revistas.inpi.gov.br/rpi/';
    private DespachoRpiRepository $despachoRepo;
    private NotificacaoRepository $notificacaoRepo;

    public function __construct()
    {
        $this->despachoRepo = new DespachoRpiRepository();
        $this->notificacaoRepo = new NotificacaoRepository();
    }

    public function executar(): array
    {
        $html = $this->baixar($this->paginaRpi);
        $publicacao = $this->identificarPublicacao($html);
        $edicao = $publicacao['edicao'];
        $links = $publicacao['links'];
        $importados = [];

        foreach ($links as $link) {
            $temporario = tempnam(sys_get_temp_dir(), 'contabi-rpi-');
            file_put_contents($temporario, $this->baixar($link));
            foreach ($this->arquivosXml($temporario, $link) as $xmlPath) {
                foreach ($this->lerDespachos($xmlPath) as $despacho) {
                    foreach ($this->despachoRepo->buscarCarteirasPorProcesso($despacho['numero_processo']) as $carteira) {
                        $dados = [
                            'numero_revista' => $edicao['numero'],
                            'data_publicacao' => $edicao['data'],
                            'numero_processo' => $despacho['numero_processo'],
                            'codigo_despacho' => $despacho['codigo_despacho'],
                            'descricao' => $despacho['descricao'],
                            'marca_id' => $carteira['marca_id'],
                            'patente_id' => $carteira['patente_id'],
                            'processado' => false
                        ];
                        if ($this->despachoRepo->existe($dados)) {
                            continue;
                        }
                        $this->despachoRepo->save($dados);
                        $this->notificacaoRepo->criarNotificacao([
                            'usuario_id' => (int)$carteira['usuario_id'],
                            'tipo' => 'novo_despacho',
                            'titulo' => 'Novo despacho na RPI',
                            'mensagem' => 'Processo ' . $despacho['numero_processo'] . ' recebeu o despacho ' . $despacho['codigo_despacho'] . '.',
                            'link' => URL_BASE . '/rpi/listar'
                        ]);
                        $importados[] = $dados;
                    }
                }
            }
            if (is_file($temporario)) {
                unlink($temporario);
            }
        }

        return $importados;
    }

    private function identificarPublicacao(string $html): array
    {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();
        $xpath = new DOMXPath($dom);

        foreach ($xpath->query('//tr') as $linha) {
            $textoLinha = trim(preg_replace('/\s+/', ' ', $linha->textContent));
            if (!preg_match('/\b(\d{4})\b.*?(\d{4}-\d{2}-\d{2})/', $textoLinha, $match)) {
                continue;
            }
            $links = [];
            foreach ($xpath->query('.//a[@href]', $linha) as $node) {
                $href = trim($node->getAttribute('href'));
                if (preg_match('/\.(xml|zip)(\?|$)/i', $href)) {
                    $links[] = $this->urlAbsoluta($href);
                }
            }
            if ($links) {
                return [
                    'edicao' => ['numero' => $match[1], 'data' => $match[2]],
                    'links' => array_values(array_unique($links))
                ];
            }
        }

        $links = [];
        foreach ($xpath->query('//a[@href]') as $node) {
            $href = trim($node->getAttribute('href'));
            if (preg_match('/\.(xml|zip)(\?|$)/i', $href)) {
                $links[] = $this->urlAbsoluta($href);
            }
        }
        return [
            'edicao' => ['numero' => date('Ymd'), 'data' => date('Y-m-d')],
            'links' => array_values(array_unique($links))
        ];
    }

    private function arquivosXml(string $arquivo, string $url): array
    {
        if (preg_match('/\.xml(\?|$)/i', $url)) {
            return [$arquivo];
        }
        $diretorio = sys_get_temp_dir() . '/contabi-rpi-' . bin2hex(random_bytes(6));
        mkdir($diretorio, 0777, true);
        $zip = new ZipArchive();
        if ($zip->open($arquivo) !== true) {
            return [];
        }
        $zip->extractTo($diretorio);
        $zip->close();
        $arquivos = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($diretorio));
        foreach ($iterator as $item) {
            if ($item->isFile() && strtolower($item->getExtension()) === 'xml') {
                $arquivos[] = $item->getPathname();
            }
        }
        return $arquivos;
    }

    private function lerDespachos(string $arquivo): array
    {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_file($arquivo, SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOCDATA);
        libxml_clear_errors();
        if (!$xml) {
            return [];
        }
        $resultados = [];
        $this->percorrer($xml, $resultados);
        $unicos = [];
        foreach ($resultados as $item) {
            $chave = $item['numero_processo'] . '|' . $item['codigo_despacho'] . '|' . $item['descricao'];
            $unicos[$chave] = $item;
        }
        return array_values($unicos);
    }

    private function percorrer(SimpleXMLElement $node, array &$resultados): void
    {
        $dados = $this->normalizarNo($node);
        if ($dados['numero_processo'] !== '') {
            $resultados[] = $dados;
        }
        foreach ($node->children() as $child) {
            $this->percorrer($child, $resultados);
        }
    }

    private function normalizarNo(SimpleXMLElement $node): array
    {
        $campos = [];
        foreach ($node->attributes() as $nome => $valor) {
            $campos[$this->normalizarNome((string)$nome)] = trim((string)$valor);
        }
        foreach ($node->children() as $nome => $valor) {
            if ($valor->count() === 0) {
                $campos[$this->normalizarNome((string)$nome)] = trim((string)$valor);
            }
        }
        $numero = $this->primeiro($campos, ['numeroprocesso', 'processo', 'numero', 'applicationnumber', 'processnumber']);
        $numero = preg_replace('/\D/', '', $numero);
        if (strlen($numero) < 8) {
            $numero = '';
        }
        return [
            'numero_processo' => $numero,
            'codigo_despacho' => $this->primeiro($campos, ['codigodespacho', 'despacho', 'codigo', 'dispatchcode', 'coddespacho']) ?: 'RPI',
            'descricao' => $this->primeiro($campos, ['descricao', 'textodespacho', 'texto', 'complemento', 'description']) ?: 'Despacho publicado na Revista da Propriedade Industrial'
        ];
    }

    private function primeiro(array $campos, array $nomes): string
    {
        foreach ($nomes as $nome) {
            if (!empty($campos[$nome])) {
                return $campos[$nome];
            }
        }
        return '';
    }

    private function normalizarNome(string $nome): string
    {
        $nome = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $nome) ?: $nome;
        return strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $nome));
    }

    private function baixar(string $url): string
    {
        $contexto = stream_context_create(['http' => ['timeout' => 60, 'user_agent' => 'Contabi/1.0']]);
        $conteudo = @file_get_contents($url, false, $contexto);
        if ($conteudo === false) {
            throw new \RuntimeException('Não foi possível baixar a RPI.');
        }
        return $conteudo;
    }

    private function urlAbsoluta(string $url): string
    {
        if (preg_match('/^https?:\/\//i', $url)) {
            return $url;
        }
        if (str_starts_with($url, '//')) {
            return 'https:' . $url;
        }
        return 'https://revistas.inpi.gov.br' . (str_starts_with($url, '/') ? $url : '/rpi/' . $url);
    }
}
