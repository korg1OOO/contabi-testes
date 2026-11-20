<?php

namespace app\controllers;

use app\core\Controller;
use app\repositories\PrazoRepository;
use app\services\PrazoService;

class PrazoController extends Controller
{
    private const ERRO_TIPO_PRAZO = 'Renovação de marca e oposição só podem ser vinculadas a marcas.';

    private PrazoRepository $repository;
    private PrazoService $service;

    public function __construct()
    {
        $this->repository = new PrazoRepository();
        $this->service = new PrazoService();
    }

    public function index()
    {
        $this->autenticacaoRequired();

        $usuarioId = $this->isAdmin()
            ? null
            : (int) $this->usuarioLogado()['id'];

        if ($usuarioId !== null) {
            $this->service->processarNotificacoesDePrazos($usuarioId);
        }

        $filtros = [
            'busca' => trim($_GET['busca'] ?? ''),
            'status' => trim($_GET['status'] ?? ''),
            'periodo' => (int) ($_GET['periodo'] ?? 0)
        ];

        $this->view('prazos/prazo_list', [
            'prazos' => $this->repository->getAllPrazosComProcesso(
                $usuarioId,
                $filtros
            ),
            'resumo' => $this->repository->getResumo($usuarioId),
            'filtros' => $filtros,
            'csrf_token' => $this->csrfToken()
        ]);
    }

    public function cadastrar()
    {
        $this->autenticacaoRequired();

        $usuarioId = $this->isAdmin()
            ? null
            : (int) $this->usuarioLogado()['id'];

        $this->view('prazos/prazo_create', [
            'ativos' => $this->repository->getAtivosDisponiveis($usuarioId),
            'csrf_token' => $this->csrfToken()
        ]);
    }

    public function salvar()
    {
        $this->autenticacaoRequired();
        $this->validarCsrf();

        $usuarioId = $this->isAdmin()
            ? null
            : (int) $this->usuarioLogado()['id'];

        $erros = $this->errosCamposObrigatorios();

        if ($erros) {
            $this->exibirFormularioCadastro($usuarioId, $erros);
            return;
        }

        try {
            $data = $this->dadosFormulario();
        } catch (\InvalidArgumentException $e) {
            $this->exibirFormularioCadastro($usuarioId, ['data_vencimento' => $e->getMessage()]);
            return;
        }

        if (!$this->ativoPermitido($data, $usuarioId)) {
            $this->negarAcesso();
        }

        if (!$this->tipoPrazoPermitido($data)) {
            $this->exibirFormularioCadastro($usuarioId, ['tipo' => self::ERRO_TIPO_PRAZO]);
            return;
        }

        $this->repository->save($data);

        if ($usuarioId !== null) {
            $this->service->processarNotificacoesDePrazos($usuarioId);
        }

        $this->redirect(URL_BASE . '/prazos');
    }

    public function editar()
    {
        $this->autenticacaoRequired();

        $usuarioId = $this->isAdmin()
            ? null
            : (int) $this->usuarioLogado()['id'];

        $prazo = $this->repository->findById(
            (int) ($_GET['id'] ?? 0),
            $usuarioId
        );

        if (!$prazo) {
            $this->negarAcesso();
        }

        $this->view('prazos/prazo_edit', [
            'prazo' => $prazo,
            'ativos' => $this->repository->getAtivosDisponiveis($usuarioId),
            'csrf_token' => $this->csrfToken()
        ]);
    }

    public function atualizar()
    {
        $this->autenticacaoRequired();
        $this->validarCsrf();

        $usuarioId = $this->isAdmin()
            ? null
            : (int) $this->usuarioLogado()['id'];

        $id = (int) ($_POST['id'] ?? 0);

        $prazoAtual = $this->repository->findById($id, $usuarioId);

        if (!$prazoAtual) {
            $this->negarAcesso();
        }

        $erros = $this->errosCamposObrigatorios();

        if ($erros) {
            $this->exibirFormularioEdicao($prazoAtual, $usuarioId, $erros);
            return;
        }

        try {
            $data = $this->dadosFormulario();
        } catch (\InvalidArgumentException $e) {
            $this->exibirFormularioEdicao($prazoAtual, $usuarioId, ['data_vencimento' => $e->getMessage()]);
            return;
        }

        $data['id'] = $id;

        if (!$this->ativoPermitido($data, $usuarioId)) {
            $this->negarAcesso();
        }

        if (!$this->tipoPrazoPermitido($data)) {
            $this->exibirFormularioEdicao($prazoAtual, $usuarioId, ['tipo' => self::ERRO_TIPO_PRAZO]);
            return;
        }

        $this->repository->update($data, $usuarioId);

        if ($usuarioId !== null) {
            $this->service->processarNotificacoesDePrazos($usuarioId);
        }

        $this->redirect(URL_BASE . '/prazos');
    }

    public function concluir()
    {
        $this->autenticacaoRequired();
        $this->validarCsrf();

        $usuarioId = $this->isAdmin()
            ? null
            : (int) $this->usuarioLogado()['id'];

        $id = (int) ($_POST['id'] ?? 0);

        if (!$this->repository->findById($id, $usuarioId)) {
            $this->negarAcesso();
        }

        $this->repository->alterarStatus(
            $id,
            'cumprido',
            $usuarioId
        );

        $this->redirect(URL_BASE . '/prazos');
    }

    public function excluir()
    {
        $this->autenticacaoRequired();
        $this->validarCsrf();

        $usuarioId = $this->isAdmin()
            ? null
            : (int) $this->usuarioLogado()['id'];

        $id = (int) ($_POST['id'] ?? 0);

        if (!$this->repository->findById($id, $usuarioId)) {
            $this->negarAcesso();
        }

        $this->repository->delete($id, $usuarioId);

        $this->redirect(URL_BASE . '/prazos');
    }

    private function dadosFormulario(): array
    {
        $ativo = explode(
            ':',
            trim($_POST['ativo'] ?? '')
        );

        $tipoAtivo = $ativo[0] ?? '';
        $ativoId = (int) ($ativo[1] ?? 0);

        $dataVencimentoBr = trim(
            $_POST['data_vencimento'] ?? ''
        );

        if ($dataVencimentoBr === '') {
            throw new \InvalidArgumentException(
                'A data de vencimento é obrigatória.'
            );
        }

        $data = \DateTime::createFromFormat(
            'd/m/Y',
            $dataVencimentoBr
        );

        if (
            $data === false
            || $data->format('d/m/Y') !== $dataVencimentoBr
        ) {
            throw new \InvalidArgumentException(
                'Informe a data de vencimento no formato dd/mm/aaaa.'
            );
        }

        $dataVencimento = $data->format('Y-m-d');

        return [
            'marca_id' =>
                $tipoAtivo === 'marca'
                    ? $ativoId
                    : null,

            'patente_id' =>
                $tipoAtivo === 'patente'
                    ? $ativoId
                    : null,

            'tipo' =>
                trim($_POST['tipo'] ?? 'outro'),

            'data_vencimento' =>
                $dataVencimento,

            'data_alerta_30' =>
                (clone $data)
                    ->modify('-30 days')
                    ->format('Y-m-d'),

            'data_alerta_15' =>
                (clone $data)
                    ->modify('-15 days')
                    ->format('Y-m-d'),

            'data_alerta_7' =>
                (clone $data)
                    ->modify('-7 days')
                    ->format('Y-m-d'),

            'status' =>
                trim($_POST['status'] ?? 'pendente'),

            'observacoes' =>
                trim($_POST['observacoes'] ?? '')
                    ?: null
        ];
    }

    private function ativoPermitido(
        array $data,
        ?int $usuarioId
    ): bool {
        foreach (
            $this->repository->getAtivosDisponiveis($usuarioId)
            as $ativo
        ) {
            if (
                $ativo['tipo'] === 'marca'
                && (int) $ativo['id']
                    === (int) $data['marca_id']
            ) {
                return true;
            }

            if (
                $ativo['tipo'] === 'patente'
                && (int) $ativo['id']
                    === (int) $data['patente_id']
            ) {
                return true;
            }
        }

        return false;
    }

    private function errosCamposObrigatorios(): array
    {
        $erros = [];

        if (trim($_POST['ativo'] ?? '') === '') {
            $erros['ativo'] = 'Selecione o processo.';
        }

        if (trim($_POST['tipo'] ?? '') === '') {
            $erros['tipo'] = 'Selecione o tipo de prazo.';
        }

        if (trim($_POST['data_vencimento'] ?? '') === '') {
            $erros['data_vencimento'] = 'A data de vencimento é obrigatória.';
        }

        return $erros;
    }

    private function exibirFormularioCadastro(?int $usuarioId, array $erros): void
    {
        $this->view('prazos/prazo_create', [
            'ativos' => $this->repository->getAtivosDisponiveis($usuarioId),
            'csrf_token' => $this->csrfToken(),
            'erros' => $erros,
            'old' => $_POST
        ]);
    }

    private function exibirFormularioEdicao(array $prazoAtual, ?int $usuarioId, array $erros): void
    {
        $this->view('prazos/prazo_edit', [
            'prazo' => $prazoAtual,
            'ativos' => $this->repository->getAtivosDisponiveis($usuarioId),
            'csrf_token' => $this->csrfToken(),
            'erros' => $erros
        ]);
    }

    private function tipoPrazoPermitido(array $data): bool
    {
        $tiposMarca = [
            'anuidade',
            'renovacao_marca',
            'oposicao',
            'prorrogacao',
            'manifestacao',
            'vencimento',
            'outro'
        ];
        $tiposPatente = [
            'anuidade',
            'prorrogacao',
            'manifestacao',
            'vencimento',
            'outro'
        ];

        if (!empty($data['marca_id'])) {
            return in_array($data['tipo'], $tiposMarca, true);
        }

        if (!empty($data['patente_id'])) {
            return in_array($data['tipo'], $tiposPatente, true);
        }

        return false;
    }
}
