<?php

namespace app\services;

use app\models\Cliente;
use app\repositories\ClienteRepository;

class ClienteService
{
    private ClienteRepository $repository;

    public function __construct() { $this->repository = new ClienteRepository(); }

    public function getClientes(): array { return $this->repository->getClientes(); }
    public function getClienteById(int $id): array|false { return $this->repository->getClienteById($id); }
    public function saveCliente(Cliente $c): bool { return $this->repository->saveCliente($c); }
    public function updateCliente(Cliente $c): bool { return $this->repository->updateCliente($c); }
    public function deleteCliente(int $id): bool { return $this->repository->deleteCliente($id); }
}
