<?php

namespace app\services;

use app\repositories\NotificacaoRepository;
use app\repositories\PrazoRepository;

class PrazoService
{
    private PrazoRepository $repository;

    public function __construct()
    {
        $this->repository = new PrazoRepository();
    }

    public function syncPrazosFromMarca(array $marca): void
    {
        $this->sincronizarDatasDoAtivo('marca', (int) $marca['id'], [
            'anuidade' => $marca['data_proxima_anuidade'] ?? null,
            'renovacao_marca' => $marca['data_renovacao'] ?? null,
            'oposicao' => $marca['data_oposicao'] ?? null,
            'prorrogacao' => $marca['data_prorrogacao'] ?? null,
            'manifestacao' => $marca['data_manifestacao'] ?? null,
            'vencimento' => $marca['data_vencimento'] ?? null
        ]);
    }

    public function syncPrazosFromPatente(array $patente): void
    {
        $this->sincronizarDatasDoAtivo('patente', (int) $patente['id'], [
            'anuidade' => $patente['data_proxima_anuidade'] ?? null,
            'manifestacao' => $patente['data_manifestacao'] ?? null,
            'prorrogacao' => $patente['data_prorrogacao'] ?? null,
            'vencimento' => $patente['data_vencimento'] ?? null
        ]);
    }

    private function sincronizarDatasDoAtivo(
        string $tipoAtivo,
        int $ativoId,
        array $prazos
    ): void {
        foreach ($prazos as $tipoPrazo => $dataVencimento) {
            $this->repository->sincronizarPrazoAtivo(
                $tipoAtivo,
                $ativoId,
                $tipoPrazo,
                $dataVencimento
            );
        }
    }

    public function processarNotificacoesDePrazos(
        int $usuarioId
    ): void {
        $notificacaoRepo = new NotificacaoRepository();

        $prazos = $this->repository
            ->getPrazosCriticosDoUsuario($usuarioId);

        foreach ($prazos as $prazo) {
            $diasRestantes = $this->calcularDiasRestantes(
                $prazo['data_vencimento']
            );

            $tipoAlerta = $this->definirTipoNotificacao(
                $diasRestantes
            );

            if ($tipoAlerta === null) {
                continue;
            }

            if (
                $notificacaoRepo->existeNotificacaoPrazo(
                    (int) $prazo['id'],
                    $tipoAlerta
                )
            ) {
                continue;
            }

            $nomeTipo = ucfirst(
                str_replace(
                    '_',
                    ' ',
                    $prazo['tipo']
                )
            );

            $numeroProcesso =
                $prazo['numero_processo']
                ?? 'não informado';

            $titulo = $this->tituloNotificacao(
                $tipoAlerta,
                $nomeTipo
            );

            $mensagem = $this->mensagemNotificacao(
                $tipoAlerta,
                $nomeTipo,
                $numeroProcesso,
                $diasRestantes,
                $prazo['data_vencimento']
            );

            $notificacaoRepo->criarNotificacao([
                'usuario_id' => $usuarioId,
                'tipo' => 'prazo_critico',
                'titulo' => $titulo,
                'mensagem' => $mensagem,

                'link' => URL_BASE
                    . '/prazos?prazo_id='
                    . (int) $prazo['id']
                    . '&alerta='
                    . $tipoAlerta
            ]);
        }
    }

    private function calcularDiasRestantes(
        string $dataVencimento
    ): int {
        $hoje = new \DateTime('today');

        $vencimento = new \DateTime(
            $dataVencimento
        );

        return (int) $hoje
            ->diff($vencimento)
            ->format('%r%a');
    }

    private function definirTipoNotificacao(
        int $dias
    ): ?string {
        if ($dias < 0) {
            return 'prazo_vencido';
        }

        if ($dias <= 7) {
            return 'prazo_7_dias';
        }

        if ($dias <= 15) {
            return 'prazo_15_dias';
        }

        if ($dias <= 30) {
            return 'prazo_30_dias';
        }

        return null;
    }

    private function tituloNotificacao(
        string $tipo,
        string $nomePrazo
    ): string {
        return match ($tipo) {
            'prazo_vencido' =>
                'Prazo vencido: ' . $nomePrazo,

            'prazo_7_dias' =>
                'Prazo urgente: ' . $nomePrazo,

            'prazo_15_dias' =>
                'Prazo próximo: ' . $nomePrazo,

            'prazo_30_dias' =>
                'Atenção ao prazo: ' . $nomePrazo,

            default =>
                'Prazo: ' . $nomePrazo
        };
    }

    private function mensagemNotificacao(
        string $tipo,
        string $nomePrazo,
        string $numeroProcesso,
        int $diasRestantes,
        string $dataVencimento
    ): string {
        $dataBr = date(
            'd/m/Y',
            strtotime($dataVencimento)
        );

        if ($tipo === 'prazo_vencido') {
            $diasVencido = abs($diasRestantes);

            return "O prazo de {$nomePrazo} do processo "
                . "{$numeroProcesso} venceu em {$dataBr}, "
                . "há {$diasVencido} dia(s).";
        }

        if ($diasRestantes === 0) {
            return "O prazo de {$nomePrazo} do processo "
                . "{$numeroProcesso} vence hoje ({$dataBr}).";
        }

        return "O prazo de {$nomePrazo} do processo "
            . "{$numeroProcesso} vence em "
            . "{$diasRestantes} dia(s), em {$dataBr}.";
    }
}
