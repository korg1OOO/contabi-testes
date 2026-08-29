<?php
namespace app\controllers;

use app\core\Controller;
use app\repositories\ClienteRepository;
use app\repositories\MarcaRepository;
use app\services\PrazoService;

class MarcaController extends Controller
{
    private MarcaRepository $repository;

    public function __construct()
    {
        $this->repository = new MarcaRepository();
    }

    public function index()
    {
        $this->autenticacaoRequired();
        $usuarioId = $this->isAdmin() ? null : (int)$this->usuarioLogado()['id'];
        $clienteId = (int)($_GET['cliente_id'] ?? 0);
        $data['marcas'] = $clienteId > 0
            ? $this->repository->getAllByCliente($clienteId, $usuarioId)
            : $this->repository->getAll($usuarioId);
        $data['cliente_id'] = $clienteId;
        $data['csrf_token'] = $this->csrfToken();
        $this->view('marcas/marca_list', $data);
    }

    public function cadastrar()
    {
        $this->autenticacaoRequired();
        $usuarioId = $this->isAdmin() ? null : (int)$this->usuarioLogado()['id'];
        $data['clientes'] = (new ClienteRepository())->getClientes($usuarioId);
        $data['csrf_token'] = $this->csrfToken();
        $this->view('marcas/marca_create', $data);
    }

    public function salvar()
    {
        $this->autenticacaoRequired();
        $this->validarCsrf();
        $usuarioId = $this->isAdmin() ? null : (int)$this->usuarioLogado()['id'];
        $marcaData = $this->dadosFormulario();
        $cliente = (new ClienteRepository())->getClienteById($marcaData['cliente_id'], $usuarioId);
        if (!$cliente) {
            $this->negarAcesso();
        }
        if ($this->repository->processoExisteParaUsuario($marcaData['numero_processo'], (int)$cliente['usuario_id'])) {
            $data['erros']['geral'] = 'Este número de processo já está cadastrado na sua carteira.';
            $data['clientes'] = (new ClienteRepository())->getClientes($usuarioId);
            $data['csrf_token'] = $this->csrfToken();
            $this->view('marcas/marca_create', $data);
            return;
        }
        try {
            $conn = $this->repository->getConnection();
            $conn->beginTransaction();
            $marcaData['id'] = $this->repository->save($marcaData);
            (new PrazoService())->syncPrazosFromMarca($marcaData);
            $conn->commit();
            $this->redirect(URL_BASE . '/marcas');
        } catch (\Throwable $e) {
            if ($this->repository->getConnection()->inTransaction()) {
                $this->repository->getConnection()->rollBack();
            }
            error_log($e->getMessage());
            $data['erros']['geral'] = 'Erro ao salvar marca. Tente novamente.';
            $data['clientes'] = (new ClienteRepository())->getClientes($usuarioId);
            $data['csrf_token'] = $this->csrfToken();
            $this->view('marcas/marca_create', $data);
        }
    }

    public function editar()
    {
        $this->autenticacaoRequired();
        $usuarioId = $this->isAdmin() ? null : (int)$this->usuarioLogado()['id'];
        $data['marca'] = $this->repository->findById((int)($_GET['id'] ?? 0), $usuarioId);
        if (!$data['marca']) {
            $this->negarAcesso();
        }
        $data['csrf_token'] = $this->csrfToken();
        $this->view('marcas/marca_edit', $data);
    }

    public function atualizar()
    {
        $this->autenticacaoRequired();
        $this->validarCsrf();
        $usuarioId = $this->isAdmin() ? null : (int)$this->usuarioLogado()['id'];
        $marcaData = $this->dadosFormulario();
        $marcaData['id'] = (int)($_POST['id'] ?? 0);
        if (!$this->repository->findById($marcaData['id'], $usuarioId)) {
            $this->negarAcesso();
        }
        $cliente = (new ClienteRepository())->getClienteById($marcaData['cliente_id'], $usuarioId);
        if (!$cliente) {
            $this->negarAcesso();
        }
        if ($this->repository->processoExisteParaUsuario($marcaData['numero_processo'], (int)$cliente['usuario_id'], $marcaData['id'])) {
            $data['marca'] = $marcaData;
            $data['erros']['geral'] = 'Este número de processo já está cadastrado na sua carteira.';
            $data['csrf_token'] = $this->csrfToken();
            $this->view('marcas/marca_edit', $data);
            return;
        }
        try {
            $conn = $this->repository->getConnection();
            $conn->beginTransaction();
            $this->repository->update($marcaData, $usuarioId);
            (new PrazoService())->syncPrazosFromMarca($marcaData);
            $conn->commit();
            $this->redirect(URL_BASE . '/marcas');
        } catch (\Throwable $e) {
            if ($this->repository->getConnection()->inTransaction()) {
                $this->repository->getConnection()->rollBack();
            }
            error_log($e->getMessage());
            $data['marca'] = $marcaData;
            $data['erros']['geral'] = 'Erro ao atualizar marca.';
            $data['csrf_token'] = $this->csrfToken();
            $this->view('marcas/marca_edit', $data);
        }
    }

    public function excluir()
    {
        $this->autenticacaoRequired();
        $this->validarCsrf();
        $usuarioId = $this->isAdmin() ? null : (int)$this->usuarioLogado()['id'];
        $id = (int)($_POST['id'] ?? 0);
        $marca = $this->repository->findById($id, $usuarioId);
        if (!$marca) {
            $this->negarAcesso();
        }
        $this->repository->delete($id, $usuarioId);
        $this->redirect(URL_BASE . '/marcas?cliente_id=' . (int)$marca['cliente_id']);
    }

    private function dadosFormulario(): array
    {
        $nullable = fn(string $campo) => trim($_POST[$campo] ?? '') ?: null;
        return [
            'cliente_id' => (int)($_POST['cliente_id'] ?? 0),
            'numero_processo' => trim($_POST['numero_processo'] ?? ''),
            'titular' => trim($_POST['titular'] ?? ''),
            'classe_nice' => (int)($_POST['classe_nice'] ?? 0),
            'status' => trim($_POST['status'] ?? 'Em análise'),
            'data_deposito' => $this->dataParaBanco($_POST['data_deposito'] ?? '') ?? '',
            'data_concessao' => $this->dataParaBanco($_POST['data_concessao'] ?? ''),
            'data_vencimento' => $this->dataParaBanco($_POST['data_vencimento'] ?? ''),
            'data_proxima_anuidade' => $nullable('data_proxima_anuidade'),
            'data_renovacao' => $nullable('data_renovacao'),
            'data_oposicao' => $nullable('data_oposicao'),
            'data_prorrogacao' => $nullable('data_prorrogacao'),
            'data_manifestacao' => $nullable('data_manifestacao'),
            'apresentacao' => trim($_POST['apresentacao'] ?? ''),
            'observacoes' => $nullable('observacoes')
        ];
    }
    private function dataParaBanco(?string $data): ?string
    {
        $data = trim((string)$data);
        if ($data === '') {
            return null;
        }

        $obj = \DateTime::createFromFormat('!d/m/Y', $data);
        $erros = \DateTime::getLastErrors();

        if (!$obj || ($erros !== false && ($erros['warning_count'] > 0 || $erros['error_count'] > 0))) {
            throw new \InvalidArgumentException('Data inválida. Use o formato dd/mm/aaaa.');
        }

        return $obj->format('Y-m-d');
    }

}
