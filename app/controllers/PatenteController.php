<?php
namespace app\controllers;

use app\core\Controller;
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
        if (!(new ClienteRepository())->getClienteById($patenteData['cliente_id'], $usuarioId)) {
            $this->negarAcesso();
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
        if (!(new ClienteRepository())->getClienteById($patenteData['cliente_id'], $usuarioId)) {
            $this->negarAcesso();
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
            'data_deposito' => $this->dataParaBanco($_POST['data_deposito'] ?? '') ?? '',
            'data_concessao' => $this->dataParaBanco($_POST['data_concessao'] ?? ''),
            'data_vencimento' => $this->dataParaBanco($_POST['data_vencimento'] ?? ''),
            'tipo_patente' => $nullable('tipo_patente'),
            'inventores' => $nullable('inventores'),
            'resumo' => $nullable('resumo'),
            'data_proxima_anuidade' => $nullable('data_proxima_anuidade'),
            'data_manifestacao' => $nullable('data_manifestacao'),
            'data_prorrogacao' => $nullable('data_prorrogacao'),
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
