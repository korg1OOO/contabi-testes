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
        $this->repository->deleteByMarca(
            (int) $marca['id']
        );

        $prazosParaCriar = [];
        $datasUsadas = [];

        $this->adicionarPrazo(
            $prazosParaCriar,
            $datasUsadas,
            (int) $marca['id'],
            null,
            'anuidade',
            $marca['data_proxima_anuidade'] ?? null
        );

        $this->adicionarPrazo(
            $prazosParaCriar,
            $datasUsadas,
            (int) $marca['id'],
            null,
            'renovacao_marca',
            $marca['data_renovacao'] ?? null
        );

        $this->adicionarPrazo(
            $prazosParaCriar,
            $datasUsadas,
            (int) $marca['id'],
            null,
            'oposicao',
            $marca['data_oposicao'] ?? null
        );

        $this->adicionarPrazo(
            $prazosParaCriar,
            $datasUsadas,
            (int) $marca['id'],
            null,
            'prorrogacao',
            $marca['data_prorrogacao'] ?? null
        );

        $this->adicionarPrazo(
            $prazosParaCriar,
            $datasUsadas,
            (int) $marca['id'],
            null,
            'manifestacao',
            $marca['data_manifestacao'] ?? null
        );

        $this->adicionarPrazo(
            $prazosParaCriar,
            $datasUsadas,
            (int) $marca['id'],
            null,
            'vencimento',
            $marca['data_vencimento'] ?? null
        );

        foreach ($prazosParaCriar as $prazo) {
            $this->repository->save($prazo);
        }
    }

    public function syncPrazosFromPatente(array $patente): void
    {
        $this->repository->deleteByPatente(
            (int) $patente['id']
        );

        $prazosParaCriar = [];
        $datasUsadas = [];

        $this->adicionarPrazo(
            $prazosParaCriar,
            $datasUsadas,
            null,
            (int) $patente['id'],
            'anuidade',
            $patente['data_proxima_anuidade'] ?? null
        );

        $this->adicionarPrazo(
            $prazosParaCriar,
            $datasUsadas,
            null,
            (int) $patente['id'],
            'manifestacao',
            $patente['data_manifestacao'] ?? null
        );

        $this->adicionarPrazo(
            $prazosParaCriar,
            $datasUsadas,
            null,
            (int) $patente['id'],
            'prorrogacao',
            $patente['data_prorrogacao'] ?? null
        );

        $this->adicionarPrazo(
            $prazosParaCriar,
            $datasUsadas,
            null,
            (int) $patente['id'],
            'vencimento',
            $patente['data_vencimento'] ?? null
        );

        foreach ($prazosParaCriar as $prazo) {
            $this->repository->save($prazo);
        }
    }

    private function adicionarPrazo(
        array &$prazos,
        array &$datasUsadas,
        ?int $marcaId,
        ?int $patenteId,
        string $tipo,
        ?string $data
    ): void {
        if (
            empty($data)
            || isset($datasUsadas[$data])
        ) {
            return;
        }

        $datasUsadas[$data] = true;

        $prazos[] = $this->criarPrazo(
            $marcaId,
            $patenteId,
            $tipo,
            $data
        );
    }

    private function criarPrazo(
        ?int $marcaId,
        ?int $patenteId,
        string $tipo,
        string $dataVencimento
    ): array {
        $dataVenc = new \DateTime($dataVencimento);

        return [
            'marca_id' => $marcaId,
            'patente_id' => $patenteId,
            'tipo' => $tipo,
            'data_vencimento' => $dataVencimento,

            'data_alerta_30' => (clone $dataVenc)
                ->modify('-30 days')
                ->format('Y-m-d'),

            'data_alerta_15' => (clone $dataVenc)
                ->modify('-15 days')
                ->format('Y-m-d'),

            'data_alerta_7' => (clone $dataVenc)
                ->modify('-7 days')
                ->format('Y-m-d'),

            'status' => 'pendente',
            'observacoes' => null
        ];
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