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

        $usuario = $this->repo->findByCpf($cpf);

        if (!$usuario || !password_verify($senha, $usuario->getSenha())) {
            return ['sucesso' => false, 'erro' => 'CPF ou senha incorretos.'];
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

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
        session_destroy();
    }
}