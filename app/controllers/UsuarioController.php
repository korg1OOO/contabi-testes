<?php
namespace app\controllers;

use app\core\Controller;
use app\models\Usuario;
use app\repositories\UsuarioRepository;
use app\services\ExclusaoDadosService;
use app\helpers\Validador;
use RuntimeException;

class UsuarioController extends Controller
{
    private UsuarioRepository $repository;

    public function __construct()
    {
        $this->repository = new UsuarioRepository();
    }

    public function index()
    {
        $this->autenticacaoRequired();

        if (!$this->isAdmin()) {
            $this->redirect(URL_BASE . '/dashboard');
            return;
        }

        $data['usuarios'] = $this->repository->getAll();
        $this->view('usuarios/usuario_list', $data);
    }

    public function cadastrar()
    {
        $this->autenticacaoRequired();

        if (!$this->isAdmin()) {
            $this->redirect(URL_BASE . '/dashboard');
            return;
        }

        $this->view('usuarios/usuario_create');
    }

    public function salvar()
    {
        $erros = [];
        $old = $_POST;

        $nome     = trim($_POST['nome'] ?? '');
        $cpf      = preg_replace('/[^0-9]/', '', $_POST['cpf'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $senha    = $_POST['senha'] ?? '';
        $perfil   = $_POST['perfil'] ?? 'consultor';

        if (empty($nome)) $erros['nome'] = 'Nome é obrigatório';
        if (!Validador::cpfValido($cpf)) $erros['cpf'] = 'CPF inválido';
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $erros['email'] = 'E-mail inválido';
        if (empty($senha)) $erros['senha'] = 'Senha é obrigatória';

        if (!empty($senha)) {
            if (strlen($senha) < 8) $erros['senha'] = 'Mínimo 8 caracteres';
            if (!preg_match('/[A-Z]/', $senha)) $erros['senha'] = 'Precisa de letra maiúscula';
            if (!preg_match('/[0-9]/', $senha)) $erros['senha'] = 'Precisa de número';
            if (!preg_match('/[^A-Za-z0-9]/', $senha)) $erros['senha'] = 'Precisa de caractere especial';
        }

        if (!empty($erros)) {
            $data['erros'] = $erros;
            $data['old'] = $old;
            $this->view('usuarios/usuario_create', $data);
            return;
        }

        $usuario = new Usuario(0, $nome, $cpf, $email ?: null, $telefone ?: null, $senha, $perfil);

        if ($this->repository->save($usuario)) {
            $this->redirect(URL_BASE . '/login?cadastro=sucesso');
        } else {
            $data['erros']['geral'] = 'Erro ao salvar usuário';
            $data['old'] = $old;
            $this->view('usuarios/usuario_create', $data);
        }
    }

    public function editar()
    {
        $this->autenticacaoRequired();

        if (!$this->isAdmin()) {
            $this->redirect(URL_BASE . '/dashboard');
            return;
        }

        $id = (int)($_GET['id'] ?? 0);
        $usuario = $this->repository->findById($id);

        if (!$usuario) {
            $this->redirect(URL_BASE . '/usuarios');
            return;
        }

        $data['usuario'] = [
            'id'       => $usuario->getId(),
            'nome'     => $usuario->getNome(),
            'cpf'      => $usuario->getCpf(),
            'email'    => $usuario->getEmail(),
            'telefone' => $usuario->getTelefone(),
            'perfil'   => $usuario->getPerfil()
        ];

        $this->view('usuarios/usuario_edit', $data);
    }

    public function atualizar()
    {
        $this->autenticacaoRequired();

        if (!$this->isAdmin()) {
            $this->redirect(URL_BASE . '/dashboard');
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        $nome = trim($_POST['nome'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $perfil = $_POST['perfil'] ?? 'consultor';

        $usuarioAtual = $this->repository->findById($id);
        if (!$usuarioAtual) {
            $this->redirect(URL_BASE . '/usuarios');
            return;
        }

        $usuario = new Usuario($id, $nome, $usuarioAtual->getCpf(), $email ?: null, $telefone ?: null, '', $perfil);

        $this->repository->update($usuario);
        $this->redirect(URL_BASE . '/usuarios?atualizado=1');
    }

    public function excluir()
    {
        $this->autenticacaoRequired();

        if (!$this->isAdmin()) {
            $this->redirect(URL_BASE . '/dashboard');
            return;
        }

        $id = (int)($_GET['id'] ?? 0);
        $this->repository->delete($id);
        $this->redirect(URL_BASE . '/usuarios');
    }

    public function excluirDados()
    {
        $this->autenticacaoRequired();
        $this->view('usuarios/excluir_dados');
    }

    public function confirmarExclusaoDados()
    {
        $this->autenticacaoRequired();
        $this->validarCsrf();
        $senha = $_POST['senha'] ?? '';
        $confirmacao = trim($_POST['confirmacao'] ?? '');
        if ($confirmacao !== 'EXCLUIR') {
            $this->view('usuarios/excluir_dados', ['erro' => 'Digite EXCLUIR para confirmar a operação.']);
            return;
        }
        try {
            (new ExclusaoDadosService())->excluir((int)$this->usuarioLogado()['id'], $senha);
            session_unset();
            session_destroy();
            $this->redirect(URL_BASE . '/login?conta_excluida=1');
        } catch (RuntimeException $e) {
            $this->view('usuarios/excluir_dados', ['erro' => $e->getMessage()]);
        }
    }

    public function register()
    {
        $this->view('usuarios/register');
    }

    public function registerSave()
    {
        $erros = [];
        $old = $_POST;

        $nome     = trim($_POST['nome'] ?? '');
        $cpf      = preg_replace('/[^0-9]/', '', $_POST['cpf'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $senha    = $_POST['senha'] ?? '';

        if (empty($nome)) $erros['nome'] = 'Nome é obrigatório';
        if (!Validador::cpfValido($cpf)) $erros['cpf'] = 'CPF inválido';
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $erros['email'] = 'E-mail inválido';
        if (empty($senha)) $erros['senha'] = 'Senha é obrigatória';

        if (!empty($senha)) {
            if (strlen($senha) < 8) $erros['senha'] = 'Mínimo 8 caracteres';
            if (!preg_match('/[A-Z]/', $senha)) $erros['senha'] = 'Precisa de letra maiúscula';
            if (!preg_match('/[0-9]/', $senha)) $erros['senha'] = 'Precisa de número';
            if (!preg_match('/[^A-Za-z0-9]/', $senha)) $erros['senha'] = 'Precisa de caractere especial';
        }

        if (!empty($erros)) {
            $data['erros'] = $erros;
            $data['old'] = $old;
            $this->view('usuarios/register', $data);
            return;
        }

        $usuario = new Usuario(0, $nome, $cpf, $email ?: null, $telefone ?: null, $senha, 'consultor');

        if ($this->repository->save($usuario)) {
            $this->redirect(URL_BASE . '/login?cadastro=sucesso');
        } else {
            $data['erros']['geral'] = 'Este CPF já está cadastrado.';
            $data['old'] = $old;
            $this->view('usuarios/register', $data);
        }
    }
}