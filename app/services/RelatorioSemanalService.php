<?php

namespace app\services;

use app\repositories\NotificacaoRepository;
use app\repositories\RelatorioAutomaticoRepository;
use app\repositories\RelatorioRepository;
use app\repositories\UsuarioRepository;
use RuntimeException;
use Throwable;

class RelatorioSemanalService
{
    public function executar(): array
    {
        date_default_timezone_set('America/Sao_Paulo');

        $inicio = date(
            'Y-m-d',
            strtotime('monday last week')
        );

        $fim = date(
            'Y-m-d',
            strtotime('sunday last week')
        );

        $raizProjeto = dirname(__DIR__, 2);

        if (is_dir($raizProjeto . '/public')) {
            $diretorio = $raizProjeto
                . '/public/assets/relatorios';
        } else {
            $diretorio = $raizProjeto
                . '/assets/relatorios';
        }

        if (!is_dir($diretorio)) {
            if (
                !mkdir(
                    $diretorio,
                    0777,
                    true
                )
                && !is_dir($diretorio)
            ) {
                throw new RuntimeException(
                    'Não foi possível criar o diretório de relatórios.'
                );
            }
        }

        $usuarios =
            (new UsuarioRepository())
                ->getAtivos();

        $relatorioRepo =
            new RelatorioRepository();

        $automaticoRepo =
            new RelatorioAutomaticoRepository();

        $notificacaoRepo =
            new NotificacaoRepository();

        $pdfService =
            new SimplePdfService();

        $gerados = [];

        foreach ($usuarios as $usuario) {
            $usuarioId =
                (int) $usuario['id'];

            if (
                $automaticoRepo->existePeriodo(
                    $usuarioId,
                    $inicio,
                    $fim
                )
            ) {
                continue;
            }

            try {
                $dados =
                    $relatorioRepo->gerar(
                        'situacao_processos',
                        $usuarioId,
                        [
                            'cliente_id' => 0,
                            'data_inicial' => '',
                            'data_final' => ''
                        ]
                    );

                $arquivo =
                    'relatorio-semanal-'
                    . $usuarioId
                    . '-'
                    . $inicio
                    . '-'
                    . $fim
                    . '.pdf';

                $titulo =
                    'Relatório semanal da carteira - '
                    . date(
                        'd/m/Y',
                        strtotime($inicio)
                    )
                    . ' a '
                    . date(
                        'd/m/Y',
                        strtotime($fim)
                    );

                $pdf =
                    $pdfService->gerar(
                        $titulo,
                        $dados
                    );

                $caminho =
                    $diretorio
                    . '/'
                    . $arquivo;

                if (
                    file_put_contents(
                        $caminho,
                        $pdf
                    ) === false
                ) {
                    throw new RuntimeException(
                        'Não foi possível salvar o relatório.'
                    );
                }

                $automaticoRepo->salvar([
                    'usuario_id' =>
                        $usuarioId,

                    'titulo' =>
                        $titulo,

                    'arquivo' =>
                        $arquivo,

                    'periodo_inicial' =>
                        $inicio,

                    'periodo_final' =>
                        $fim
                ]);

                if (!$dados) {
                    $notificacaoRepo
                        ->criarNotificacao([
                            'usuario_id' =>
                                $usuarioId,

                            'tipo' =>
                                'sistema',

                            'titulo' =>
                                'Relatório semanal sem registros',

                            'mensagem' =>
                                'O relatório semanal foi gerado, '
                                . 'mas não existem processos disponíveis '
                                . 'na sua carteira.',

                            'link' =>
                                URL_BASE
                                . '/assets/relatorios/'
                                . $arquivo
                        ]);
                } else {
                    $notificacaoRepo
                        ->criarNotificacao([
                            'usuario_id' =>
                                $usuarioId,

                            'tipo' =>
                                'sistema',

                            'titulo' =>
                                'Relatório semanal disponível',

                            'mensagem' =>
                                'O relatório semanal da sua carteira '
                                . 'foi gerado automaticamente para o período de '
                                . date(
                                    'd/m/Y',
                                    strtotime($inicio)
                                )
                                . ' a '
                                . date(
                                    'd/m/Y',
                                    strtotime($fim)
                                )
                                . '.',

                            'link' =>
                                URL_BASE
                                . '/assets/relatorios/'
                                . $arquivo
                        ]);
                }

                $gerados[] = $arquivo;
            } catch (Throwable $e) {
                continue;
            }
        }

        return $gerados;
    }
}