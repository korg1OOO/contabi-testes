<?php

namespace app\controllers;

use app\core\Controller;
use app\repositories\ClienteRepository;
use app\repositories\RelatorioRepository;
use app\repositories\RelatorioAutomaticoRepository;
use app\services\SimplePdfService;
use Throwable;

class RelatorioController extends Controller
{
    private RelatorioRepository $repository;

    private array $tiposPermitidos = [
        'marcas_cliente',
        'patentes_cliente',
        'vencimentos',
        'situacao_processos'
    ];

    public function __construct()
    {
        $this->repository = new RelatorioRepository();
    }

    public function index()
    {
        $this->autenticacaoRequired();

        $usuarioId = $this->isAdmin()
            ? null
            : (int) $this->usuarioLogado()['id'];

        $this->view('relatorios/relatorio_index', [
            'clientes' => (new ClienteRepository())->getClientes($usuarioId),
            'relatoriosAutomaticos' => (new RelatorioAutomaticoRepository())->listar($usuarioId)
        ]);
    }

    public function visualizar()
    {
        $this->autenticacaoRequired();

        $usuarioId = $this->isAdmin()
            ? null
            : (int) $this->usuarioLogado()['id'];

        $tipo = trim($_GET['tipo'] ?? '');
        $filtros = $this->filtros();

        $erro = null;
        $dados = [];
        $semProcessosAtivos = null;

        if (!in_array($tipo, $this->tiposPermitidos, true)) {
            $erro = 'O tipo de relatório informado é inválido.';
        } elseif (($erroPeriodo = $this->erroPeriodo($filtros)) !== null) {
            $erro = $erroPeriodo;
        } else {
            try {
                $semProcessosAtivos = !$this->repository->temProcessosAtivos(
                    $usuarioId,
                    $filtros
                );
                $dados = $this->repository->gerar(
                    $tipo,
                    $usuarioId,
                    $filtros
                );

                $dados = $this->formatarDadosParaExibicao($dados);
            } catch (Throwable $e) {
                $erro = 'Não foi possível gerar o relatório. Tente novamente.';
            }
        }

        $this->view('relatorios/relatorio_view', [
            'tipo' => $tipo,
            'titulo' => $this->titulo($tipo),
            'dados' => $dados,
            'filtros' => $filtros,
            'semProcessosAtivos' => $semProcessosAtivos,
            'erro' => $erro
        ]);
    }

    public function pdf()
    {
        $this->autenticacaoRequired();

        $usuarioId = $this->isAdmin()
            ? null
            : (int) $this->usuarioLogado()['id'];

        $tipo = trim($_GET['tipo'] ?? '');
        $filtros = $this->filtros();

        if (
            !in_array($tipo, $this->tiposPermitidos, true)
            || !$this->datasValidas($filtros)
        ) {
            $this->redirect(URL_BASE . '/relatorios');
        }

        try {
            $semProcessosAtivos = !$this->repository->temProcessosAtivos(
                $usuarioId,
                $filtros
            );
            $dados = $this->repository->gerar(
                $tipo,
                $usuarioId,
                $filtros
            );

            $dados = $this->formatarDadosParaExibicao($dados);

            $pdf = (new SimplePdfService())->gerar(
                $this->titulo($tipo),
                $dados,
                $semProcessosAtivos
                    ? 'Aviso: sua carteira não possui processos ativos para este relatório.'
                    : null
            );
        } catch (Throwable $e) {
            $this->redirect(URL_BASE . '/relatorios');
        }

        $nomeArquivo = 'relatorio-'
            . preg_replace('/[^a-z0-9_-]/i', '-', $tipo)
            . '.pdf';

        header('Content-Type: application/pdf');
        header(
            'Content-Disposition: attachment; filename="'
            . $nomeArquivo
            . '"'
        );
        header('Content-Length: ' . strlen($pdf));

        echo $pdf;
        exit;
    }

    private function filtros(): array
    {
        return [
            'cliente_id' => (int) ($_GET['cliente_id'] ?? 0),

            'data_inicial' => $this->dataParaBanco(
                trim($_GET['data_inicial'] ?? '')
            ),

            'data_final' => $this->dataParaBanco(
                trim($_GET['data_final'] ?? '')
            )
        ];
    }

    private function dataParaBanco(string $data): string
    {
        if ($data === '') {
            return '';
        }

        $dataBr = \DateTime::createFromFormat('d/m/Y', $data);

        if (
            $dataBr !== false
            && $dataBr->format('d/m/Y') === $data
        ) {
            return $dataBr->format('Y-m-d');
        }

        $dataBanco = \DateTime::createFromFormat('Y-m-d', $data);

        if (
            $dataBanco !== false
            && $dataBanco->format('Y-m-d') === $data
        ) {
            return $data;
        }

        return $data;
    }

    private function datasValidas(array $filtros): bool
    {
        return $this->erroPeriodo($filtros) === null;
    }

    private function erroPeriodo(array $filtros): ?string
    {
        $inicial = $filtros['data_inicial'];
        $final = $filtros['data_final'];

        if ($inicial !== '' && $final === '') {
            return 'Informe também a data final para filtrar por período (ou deixe as duas datas em branco para ver todo o histórico).';
        }

        if ($inicial === '' && $final !== '') {
            return 'Informe também a data inicial para filtrar por período (ou deixe as duas datas em branco para ver todo o histórico).';
        }

        foreach (['data_inicial' => 'inicial', 'data_final' => 'final'] as $campo => $rotulo) {
            if ($filtros[$campo] !== '' && !$this->dataValidaBanco($filtros[$campo])) {
                return "A data {$rotulo} é inválida. Use o formato dd/mm/aaaa.";
            }
        }

        if ($inicial !== '' && $inicial > $final) {
            return 'A data inicial não pode ser posterior à data final.';
        }

        return null;
    }

    private function dataValidaBanco(string $data): bool
    {
        $objeto = \DateTime::createFromFormat('Y-m-d', $data);

        return $objeto !== false
            && $objeto->format('Y-m-d') === $data;
    }

    private function formatarDadosParaExibicao(array $dados): array
    {
        foreach ($dados as &$linha) {
            foreach ($linha as &$valor) {
                if (!is_string($valor)) {
                    continue;
                }

                $valor = $this->formatarValorData($valor);
            }

            unset($valor);
        }

        unset($linha);

        return $dados;
    }

    private function formatarValorData(string $valor): string
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor)) {
            $data = \DateTime::createFromFormat(
                'Y-m-d',
                $valor
            );

            if ($data !== false) {
                return $data->format('d/m/Y');
            }
        }

        if (
            preg_match(
                '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',
                $valor
            )
        ) {
            $data = \DateTime::createFromFormat(
                'Y-m-d H:i:s',
                $valor
            );

            if ($data !== false) {
                return $data->format('d/m/Y H:i');
            }
        }

        return $valor;
    }

    private function titulo(string $tipo): string
    {
        return match ($tipo) {
            'marcas_cliente' => 'Marcas por cliente',
            'patentes_cliente' => 'Patentes por cliente',
            'vencimentos' => 'Vencimentos por período',
            'situacao_processos' => 'Situação atual dos processos',
            default => 'Relatório Contabi'
        };
    }
}
