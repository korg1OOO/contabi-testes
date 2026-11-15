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

        try {
            $this->repository->saveCliente($dados);
        } catch (\PDOException $e) {
            error_log('Erro ao cadastrar cliente: ' . $e->getMessage());
            $this->view('clientes/cliente_create', [
                'csrf_token' => $this->csrfToken(),
                'cliente' => $dados,
                'erros' => ['geral' => 'Não foi possível salvar o cliente. Verifique os dados e tente novamente.']
            ]);
            return;
        }

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

        try {
            $this->repository->updateCliente($dados, $usuarioId);
        } catch (\PDOException $e) {
            error_log('Erro ao atualizar cliente: ' . $e->getMessage());
            $this->view('clientes/cliente_edit', [
                'csrf_token' => $this->csrfToken(),
                'cliente' => $dados,
                'erros' => ['geral' => 'Não foi possível atualizar o cliente. Verifique os dados e tente novamente.']
            ]);
            return;
        }

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

    private function validarFormulario(array &$dados): array
    {
        $erros = [];

        if ($dados['nome'] === '') {
            $erros['nome'] = 'Nome é obrigatório.';
        }

        if (Validador::tamanho($dados['nome']) > 200) {
            $erros['nome'] = 'O nome deve ter até 200 caracteres.';
        }
        if (Validador::tamanho($dados['email']) > 150) {
            $erros['email'] = 'O e-mail deve ter até 150 caracteres.';
        }

        $telefone = trim($dados['telefone']);
        if ($telefone !== '') {
            $numeros = preg_replace('/\D/', '', $telefone) ?? '';
            if (!preg_match('/^[0-9()\s+.-]+$/D', $telefone)
                || !in_array(strlen($numeros), [10, 11], true)) {
                $erros['telefone'] = 'Informe um telefone com DDD e 10 ou 11 dígitos.';
            } else {
                $ddd = substr($numeros, 0, 2);
                $parte = substr($numeros, 2);
                $separador = strlen($numeros) === 11 ? 5 : 4;
                $dados['telefone'] = sprintf('(%s) %s-%s', $ddd, substr($parte, 0, $separador), substr($parte, $separador));
            }
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
