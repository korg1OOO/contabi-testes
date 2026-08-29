<?php
namespace app\controllers;

use app\core\Controller;
use app\services\AutenticacaoService;
use app\helpers\Validador;

class AutenticacaoController extends Controller
{
    private AutenticacaoService $authService;

    public function __construct()
    {
        $this->authService = new AutenticacaoService();
    }

    public function login()
    {
        $this->view('/autenticacao/login');
    }

    public function logar()
    {
        $cpf   = $_POST['cpf'] ?? null;
        $senha = $_POST['senha'] ?? null;

        if (!$cpf || !$senha) {
            $data['erros'] = ['CPF e senha são obrigatórios.'];
            $this->view('/autenticacao/login', $data);
            return;
        }

        if (!Validador::cpfValido($cpf)) {
            $data['erros'] = ['CPF inválido.'];
            $this->view('/autenticacao/login', $data);
            return;
        }

        $resultado = $this->authService->logar($cpf, $senha);

        if ($resultado['sucesso']) {
            $this->redirect(URL_BASE . '/dashboard');
        } else {
            $data['erros'] = [$resultado['erro']];
            $this->view('/autenticacao/login', $data);
        }
    }

    public function logout()
    {
        $this->authService->logout();
        $this->redirect(URL_BASE . '/login');
    }
}