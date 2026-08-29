<?php

namespace app\controllers;

use app\core\Controller;
use app\helpers\Validador;
use app\repositories\ClienteRepository;

class ClienteController extends Controller
{
    private ClienteRepository $repository;

    public function __construct()
    {
        $this->repository = new ClienteRepository();
    }

    public function index()
    {
        $this->autenticacaoRequired();

        $usuarioId = $this->isAdmin()
            ? null
            : (int) $this->usuarioLogado()['id'];

        $data['clientes'] = $this->repository->getClientes($usuarioId);
        $data['csrf_token'] = $this->csrfToken();

        $this->view('clientes/cliente_list', $data);
    }

    public function cadastrar()
    {
        $this->autenticacaoRequired();

        $this->view('clientes/cliente_create', [
            'csrf_token' => $this->csrfToken()
        ]);
    }

    public function salvar()
    {
        $this->autenticacaoRequired();
        $this->validarCsrf();

        $dados = $this->dadosFormulario();
        $erros = $this->validarFormulario($dados);

        if ($erros) {
            $this->view('clientes/cliente_create', [
                'csrf_token' => $this->csrfToken(),
                'cliente' => $dados,
                'erros' => $erros
            ]);
            return;
        }

        $dados['usuario_id'] = (int) $this->usuarioLogado()['id'];

        $this->repository->saveCliente($dados);

        $this->redirect(URL_BASE . '/clientes');
    }

    public function editar()
    {
        $this->autenticacaoRequired();

        $usuarioId = $this->isAdmin()
            ? null
            : (int) $this->usuarioLogado()['id'];

        $cliente = $this->repository->getClienteById(
            (int) ($_GET['id'] ?? 0),
            $usuarioId
        );

        if (!$cliente) {
            $this->negarAcesso();
        }

        $this->view('clientes/cliente_edit', [
            'cliente' => $cliente,
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

        $dados = $this->dadosFormulario();
        $dados['id'] = (int) ($_POST['id'] ?? 0);

        if (!$this->repository->getClienteById($dados['id'], $usuarioId)) {
            $this->negarAcesso();
        }

        $erros = $this->validarFormulario($dados);

        if ($erros) {
            $this->view('clientes/cliente_edit', [
                'cliente' => $dados,
                'csrf_token' => $this->csrfToken(),
                'erros' => $erros
            ]);
            return;
        }

        $this->repository->updateCliente($dados, $usuarioId);

        $this->redirect(URL_BASE . '/clientes');
    }

    public function excluir()
    {
        $this->autenticacaoRequired();
        $this->validarCsrf();

        $usuarioId = $this->isAdmin()
            ? null
            : (int) $this->usuarioLogado()['id'];

        $id = (int) ($_POST['id'] ?? 0);

        if (!$this->repository->getClienteById($id, $usuarioId)) {
            $this->negarAcesso();
        }

        $this->repository->deleteCliente($id, $usuarioId);

        $this->redirect(URL_BASE . '/clientes');
    }

    private function dadosFormulario(): array
    {
        $tipo = strtoupper(trim($_POST['tipo_pessoa'] ?? 'PJ'));

        if (!in_array($tipo, ['PF', 'PJ'], true)) {
            $tipo = 'PJ';
        }

        return [
            'nome' => trim($_POST['nome'] ?? ''),
            'tipo_pessoa' => $tipo,
            'cpf_cnpj' => preg_replace(
                '/\D/',
                '',
                $_POST['cpf_cnpj'] ?? ''
            ) ?? '',
            'email' => trim($_POST['email'] ?? ''),
            'telefone' => trim($_POST['telefone'] ?? ''),
            'endereco' => trim($_POST['endereco'] ?? ''),
            'observacoes' => trim($_POST['observacoes'] ?? '')
        ];
    }

    private function validarFormulario(array $dados): array
    {
        $erros = [];

        if ($dados['nome'] === '') {
            $erros['nome'] = 'Nome é obrigatório.';
        }

        if ($dados['tipo_pessoa'] === 'PF') {
            if (!Validador::cpfValido($dados['cpf_cnpj'])) {
                $erros['cpf_cnpj'] = 'CPF inválido.';
            }
        } elseif (!Validador::cnpjValido($dados['cpf_cnpj'])) {
            $erros['cpf_cnpj'] = 'CNPJ inválido.';
        }

        if (
            $dados['email'] !== ''
            && !filter_var($dados['email'], FILTER_VALIDATE_EMAIL)
        ) {
            $erros['email'] = 'E-mail inválido.';
        }

        return $erros;
    }
}
