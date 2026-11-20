<?php
namespace app\controllers;

use app\core\Controller;
use app\models\Usuario;
use app\repositories\UsuarioRepository;
use app\services\ExclusaoDadosService;
use app\helpers\Validador;
use RuntimeException;
use PDOException;

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
        $data['csrf_token'] = $this->csrfToken();
        $this->view('usuarios/usuario_list', $data);
    }

    public function cadastrar()
    {
        $this->autenticacaoRequired();

        if (!$this->isAdmin()) {
            $this->redirect(URL_BASE . '/dashboard');
            return;
        }

        $this->view('usuarios/usuario_create', ['csrf_token' => $this->csrfToken()]);
    }

    public function salvar()
    {
        $this->autenticacaoRequired();

        if (!$this->isAdmin()) {
            $this->negarAcesso();
        }

        $this->validarCsrf();

        $erros = [];
        $old = $_POST;

        $nome     = trim($_POST['nome'] ?? '');
        $cpf      = preg_replace('/[^0-9]/', '', $_POST['cpf'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $senha    = $_POST['senha'] ?? '';
        $perfil   = $_POST['perfil'] ?? 'consultor';
        $perfisPermitidos = ['administrador', 'consultor', 'agente_pi'];

        if (empty($nome)) $erros['nome'] = 'Nome é obrigatório';
        if ($cpf === '') $erros['cpf'] = 'CPF é obrigatório';
        elseif (!Validador::cpfValido($cpf)) $erros['cpf'] = 'CPF inválido';
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $erros['email'] = 'E-mail inválido';
        if (empty($senha)) $erros['senha'] = 'Senha é obrigatória';
        if (!in_array($perfil, $perfisPermitidos, true)) $erros['perfil'] = 'Perfil inválido';
        if (!isset($erros['cpf']) && ($msgCpf = $this->mensagemCpfExistente($cpf, true))) $erros['cpf'] = $msgCpf;

        if (!empty($senha)) {
            if (strlen($senha) < 8) $erros['senha'] = 'Mínimo 8 caracteres';
            if (!preg_match('/[A-Z]/', $senha)) $erros['senha'] = 'Precisa de letra maiúscula';
            if (!preg_match('/[0-9]/', $senha)) $erros['senha'] = 'Precisa de número';
            if (!preg_match('/[^A-Za-z0-9]/', $senha)) $erros['senha'] = 'Precisa de caractere especial';
        }

        if (!empty($erros)) {
            $data['erros'] = $erros;
            $data['old'] = $old;
            $data['csrf_token'] = $this->csrfToken();
            $this->view('usuarios/usuario_create', $data);
            return;
        }

        $usuario = new Usuario(0, $nome, $cpf, $email ?: null, $telefone ?: null, $senha, $perfil);

        try {
            if ($this->repository->save($usuario)) {
                $this->redirect(URL_BASE . '/usuarios?cadastro=sucesso');
            }

            $data['erros']['geral'] = 'Erro ao salvar usuário.';
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $data['erros']['cpf'] = $this->mensagemCpfExistente($cpf, true) ?? 'Este CPF já está cadastrado.';
            } else {
                error_log($e->getMessage());
                $data['erros']['geral'] = 'Erro ao salvar usuário.';
            }
        }

        $data['old'] = $old;
        $data['csrf_token'] = $this->csrfToken();
        $this->view('usuarios/usuario_create', $data);
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

        $erros = [];
        if ($nome === '') $erros['nome'] = 'Nome é obrigatório';
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $erros['email'] = 'E-mail inválido';
        if (!in_array($perfil, ['administrador', 'consultor', 'agente_pi'], true)) $erros['perfil'] = 'Perfil inválido';

        if ($erros) {
            $this->view('usuarios/usuario_edit', [
                'erros' => $erros,
                'usuario' => [
                    'id' => $id,
                    'nome' => $nome,
                    'cpf' => $usuarioAtual->getCpf(),
                    'email' => $email,
                    'telefone' => $telefone,
                    'perfil' => $perfil
                ]
            ]);
            return;
        }

        $usuario = new Usuario($id, $nome, $usuarioAtual->getCpf(), $email ?: null, $telefone ?: null, '', $perfil);

        $this->repository->update($usuario);
        $this->redirect(URL_BASE . '/usuarios?atualizado=1');
    }

    public function desativar()
    {
        $this->autenticacaoRequired();

        if (!$this->isAdmin()) {
            $this->negarAcesso();
        }

        $this->validarCsrf();
        $id = (int)($_POST['id'] ?? 0);

        if ($id <= 0 || !$this->repository->findById($id)) {
            $this->redirect(URL_BASE . '/usuarios');
        }

        $this->repository->deactivate($id);
        $this->redirect(URL_BASE . '/usuarios?desativado=1');
    }

    public function ativar()
    {
        $this->autenticacaoRequired();

        if (!$this->isAdmin()) {
            $this->negarAcesso();
        }

        $this->validarCsrf();
        $id = (int)($_POST['id'] ?? 0);

        if ($id <= 0 || !$this->repository->findById($id)) {
            $this->redirect(URL_BASE . '/usuarios');
        }

        $this->repository->activate($id);
        $this->redirect(URL_BASE . '/usuarios?ativado=1');
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
        $erros = [];
        if ($senha === '') $erros['senha'] = 'Informe sua senha.';
        if ($confirmacao !== 'EXCLUIR') $erros['confirmacao'] = 'Digite EXCLUIR para confirmar a operação.';
        if ($erros) {
            $this->view('usuarios/excluir_dados', ['erros' => $erros]);
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
        if ($cpf === '') $erros['cpf'] = 'CPF é obrigatório';
        elseif (!Validador::cpfValido($cpf)) $erros['cpf'] = 'CPF inválido';
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $erros['email'] = 'E-mail inválido';
        if (empty($senha)) $erros['senha'] = 'Senha é obrigatória';

        if (!empty($senha)) {
            if (strlen($senha) < 8) $erros['senha'] = 'Mínimo 8 caracteres';
            if (!preg_match('/[A-Z]/', $senha)) $erros['senha'] = 'Precisa de letra maiúscula';
            if (!preg_match('/[0-9]/', $senha)) $erros['senha'] = 'Precisa de número';
            if (!preg_match('/[^A-Za-z0-9]/', $senha)) $erros['senha'] = 'Precisa de caractere especial';
        }

        if (!isset($erros['cpf']) && ($msgCpf = $this->mensagemCpfExistente($cpf, false))) $erros['cpf'] = $msgCpf;

        if (!empty($erros)) {
            $data['erros'] = $erros;
            $data['old'] = $old;
            $this->view('usuarios/register', $data);
            return;
        }

        $usuario = new Usuario(0, $nome, $cpf, $email ?: null, $telefone ?: null, $senha, 'consultor');

        try {
            if ($this->repository->save($usuario)) {
                $this->redirect(URL_BASE . '/login?cadastro=sucesso');
            }

            $data['erros']['geral'] = 'Não foi possível concluir o cadastro.';
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $data['erros']['cpf'] = $this->mensagemCpfExistente($cpf, false) ?? 'Este CPF já está cadastrado.';
            } else {
                error_log($e->getMessage());
                $data['erros']['geral'] = 'Não foi possível concluir o cadastro.';
            }
        }

        $data['old'] = $old;
        $this->view('usuarios/register', $data);
    }

    private function mensagemCpfExistente(string $cpf, bool $paraAdmin): ?string
    {
        $existente = $this->repository->findByCpfQualquerStatus($cpf);

        if (!$existente) {
            return null;
        }

        if (!$existente->isAtivo()) {
            return $paraAdmin
                ? 'Este CPF pertence a uma conta desativada. Reative-a na lista de usuários.'
                : 'Este CPF pertence a uma conta desativada. Entre em contato com o administrador para reativá-la.';
        }

        return 'Este CPF já está cadastrado.';
    }
}