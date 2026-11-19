<?php
namespace app\services;

use app\repositories\UsuarioRepository;

class AutenticacaoService
{
    private UsuarioRepository $repo;

    public function __construct()
    {
        $this->repo = new UsuarioRepository();
    }

    public function logar(string $cpf, string $senha): array
    {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        $usuario = $this->repo->findByCpfQualquerStatus($cpf);

        if (!$usuario || !password_verify($senha, $usuario->getSenha())) {
            return ['sucesso' => false, 'erro' => 'CPF ou senha incorretos.'];
        }

        if (!$usuario->isAtivo()) {
            return [
                'sucesso' => false,
                'erro' => 'Esta conta está desativada. Entre em contato com o administrador do sistema.'
            ];
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        session_regenerate_id(true);

        $_SESSION['usuario'] = [
            'id'     => $usuario->getId(),
            'nome'   => $usuario->getNome(),
            'cpf'    => $usuario->getCpf(),
            'perfil' => $usuario->getPerfil()
        ];

        return ['sucesso' => true];
    }

    public function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }

        session_destroy();
    }
}