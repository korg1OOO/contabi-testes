<?php
namespace app\controllers;

use app\core\Controller;
use app\helpers\Validador;
use app\repositories\ClienteRepository;
use app\repositories\PatenteRepository;
use app\services\PrazoService;

class PatenteController extends Controller
{
    private PatenteRepository $repository;

    public function __construct()
    {
        $this->repository = new PatenteRepository();
    }

    public function index()
    {
        $this->autenticacaoRequired();
        $usuarioId = $this->isAdmin() ? null : (int)$this->usuarioLogado()['id'];
        $clienteId = (int)($_GET['cliente_id'] ?? 0);
        $data['patentes'] = $clienteId > 0
            ? $this->repository->getAllByCliente($clienteId, $usuarioId)
            : $this->repository->getAll($usuarioId);
        $data['cliente_id'] = $clienteId;
        $data['csrf_token'] = $this->csrfToken();
        $this->view('patentes/patente_list', $data);
    }

    public function cadastrar()
    {
        $this->autenticacaoRequired();
        $usuarioId = $this->isAdmin() ? null : (int)$this->usuarioLogado()['id'];
        $data['clientes'] = (new ClienteRepository())->getClientes($usuarioId);
        $data['csrf_token'] = $this->csrfToken();
        $this->view('patentes/patente_create', $data);
    }

    public function salvar()
    {
        $this->autenticacaoRequired();
        $this->validarCsrf();
        $usuarioId = $this->isAdmin() ? null : (int)$this->usuarioLogado()['id'];
        $patenteData = $this->dadosFormulario();
        $erros = $this->validarFormulario($patenteData);
        if ($erros) {
            $this->view('patentes/patente_create', [
                'patente' => $patenteData,
                'clientes' => (new ClienteRepository())->getClientes($usuarioId),
                'csrf_token' => $this->csrfToken(),
                'erros' => $erros
            ]);
            return;
        }
        $cliente = (new ClienteRepository())->getClienteById($patenteData['cliente_id'], $usuarioId);
        if (!$cliente) {
            $this->negarAcesso();
        }
        if ($this->repository->processoExisteParaUsuario($patenteData['numero_processo'], (int)$cliente['usuario_id'])) {
            $data['patente'] = $patenteData;
            $data['erros']['geral'] = 'Este número de processo já está cadastrado na sua carteira.';
            $data['clientes'] = (new ClienteRepository())->getClientes($usuarioId);
            $data['csrf_token'] = $this->csrfToken();
            $this->view('patentes/patente_create', $data);
            return;
        }
        try {
            $conn = $this->repository->getConnection();
            $conn->beginTransaction();
            $patenteData['id'] = $this->repository->save($patenteData);
            (new PrazoService())->syncPrazosFromPatente($patenteData);
            $conn->commit();
            $this->redirect(URL_BASE . '/patentes');
        } catch (\Throwable $e) {
            if ($this->repository->getConnection()->inTransaction()) {
                $this->repository->getConnection()->rollBack();
            }
            error_log($e->getMessage());
            $data['patente'] = $patenteData;
            $data['erros']['geral'] = 'Erro ao salvar patente. Tente novamente.';
            $data['clientes'] = (new ClienteRepository())->getClientes($usuarioId);
            $data['csrf_token'] = $this->csrfToken();
            $this->view('patentes/patente_create', $data);
        }
    }

    public function editar()
    {
        $this->autenticacaoRequired();
        $usuarioId = $this->isAdmin() ? null : (int)$this->usuarioLogado()['id'];
        $data['patente'] = $this->repository->findById((int)($_GET['id'] ?? 0), $usuarioId);
        if (!$data['patente']) {
            $this->negarAcesso();
        }
        $data['csrf_token'] = $this->csrfToken();
        $this->view('patentes/patente_edit', $data);
    }

    public function atualizar()
    {
        $this->autenticacaoRequired();
        $this->validarCsrf();
        $usuarioId = $this->isAdmin() ? null : (int)$this->usuarioLogado()['id'];
        $patenteData = $this->dadosFormulario();
        $patenteData['id'] = (int)($_POST['id'] ?? 0);
        if (!$this->repository->findById($patenteData['id'], $usuarioId)) {
            $this->negarAcesso();
        }
        $erros = $this->validarFormulario($patenteData);
        if ($erros) {
            $this->view('patentes/patente_edit', [
                'patente' => $patenteData,
                'csrf_token' => $this->csrfToken(),
                'erros' => $erros
            ]);
            return;
        }
        $cliente = (new ClienteRepository())->getClienteById($patenteData['cliente_id'], $usuarioId);
        if (!$cliente) {
            $this->negarAcesso();
        }
        if ($this->repository->processoExisteParaUsuario($patenteData['numero_processo'], (int)$cliente['usuario_id'], $patenteData['id'])) {
            $data['patente'] = $patenteData;
            $data['erros']['geral'] = 'Este número de processo já está cadastrado na sua carteira.';
            $data['csrf_token'] = $this->csrfToken();
            $this->view('patentes/patente_edit', $data);
            return;
        }
        try {
            $conn = $this->repository->getConnection();
            $conn->beginTransaction();
            $this->repository->update($patenteData, $usuarioId);
            (new PrazoService())->syncPrazosFromPatente($patenteData);
            $conn->commit();
            $this->redirect(URL_BASE . '/patentes');
        } catch (\Throwable $e) {
            if ($this->repository->getConnection()->inTransaction()) {
                $this->repository->getConnection()->rollBack();
            }
            error_log($e->getMessage());
            $data['patente'] = $patenteData;
            $data['erros']['geral'] = 'Erro ao atualizar patente.';
            $data['csrf_token'] = $this->csrfToken();
            $this->view('patentes/patente_edit', $data);
        }
    }

    public function excluir()
    {
        $this->autenticacaoRequired();
        $this->validarCsrf();
        $usuarioId = $this->isAdmin() ? null : (int)$this->usuarioLogado()['id'];
        $id = (int)($_POST['id'] ?? 0);
        $patente = $this->repository->findById($id, $usuarioId);
        if (!$patente) {
            $this->negarAcesso();
        }
        $this->repository->delete($id, $usuarioId);
        $this->redirect(URL_BASE . '/patentes?cliente_id=' . (int)$patente['cliente_id']);
    }

    private function dadosFormulario(): array
    {
        $nullable = fn(string $campo) => trim($_POST[$campo] ?? '') ?: null;
        return [
            'cliente_id' => (int)($_POST['cliente_id'] ?? 0),
            'numero_processo' => trim($_POST['numero_processo'] ?? ''),
            'titular' => trim($_POST['titular'] ?? ''),
            'status' => trim($_POST['status'] ?? 'Depositada'),
            'data_deposito' => trim($_POST['data_deposito'] ?? ''),
            'data_concessao' => trim($_POST['data_concessao'] ?? ''),
            'data_vencimento' => trim($_POST['data_vencimento'] ?? ''),
            'tipo_patente' => $nullable('tipo_patente'),
            'inventores' => $nullable('inventores'),
            'resumo' => $nullable('resumo'),
            'data_proxima_anuidade' => $nullable('data_proxima_anuidade'),
            'data_manifestacao' => $nullable('data_manifestacao'),
            'data_prorrogacao' => $nullable('data_prorrogacao'),
            'observacoes' => $nullable('observacoes')
        ];
    }
    private function validarFormulario(array &$dados): array
    {
        $erros = Validador::validarDatasRegistro($dados);
        if ($dados['cliente_id'] <= 0) {
            $erros['cliente_id'] = 'Selecione o cliente.';
        }
        if ($dados['numero_processo'] === '') {
            $erros['numero_processo'] = 'Informe o número do processo.';
        } elseif (Validador::tamanho($dados['numero_processo']) > 30) {
            $erros['numero_processo'] = 'O número do processo deve ter até 30 caracteres.';
        }
        if ($dados['titular'] === '') {
            $erros['titular'] = 'Informe o titular.';
        } elseif (Validador::tamanho($dados['titular']) > 200) {
            $erros['titular'] = 'O titular deve ter até 200 caracteres.';
        }
        if (Validador::tamanho($dados['status']) > 50) {
            $erros['status'] = 'O status deve ter até 50 caracteres.';
        }
        return $erros;
    }
}
