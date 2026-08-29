<?php
namespace app\services;

use app\models\Usuario;
use app\repositories\UsuarioRepository;

class UsuarioService
{
    private UsuarioRepository $repository;

    public function __construct()
    {
        $this->repository = new UsuarioRepository();
    }

    public function getUsuarios(): array
    {
        return $this->repository->getAll();
    }

    public function getUsuarioById(int $id): ?Usuario
    {
        return $this->repository->findById($id);
    }

    public function saveUsuario(Usuario $usuario): bool
    {
        // Verifica se já existe usuário com o mesmo CPF
        if ($this->repository->findByCpf($usuario->getCpf())) {
            return false;
        }

        return $this->repository->save($usuario);
    }

    public function updateUsuario(Usuario $usuario): bool
    {
        return $this->repository->update($usuario);
    }

    public function deleteUsuario(int $id): bool
    {
        return $this->repository->delete($id);
    }
}