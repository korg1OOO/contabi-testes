<?php

namespace app\services;

use app\models\Despacho;
use app\models\Prazo;
use app\repositories\DespachoRepository;
use app\repositories\PrazoRepository;

class DespachoService
{
    private DespachoRepository $despachoRepo;
    private PrazoRepository $prazoRepo;

    public function __construct()
    {
        $this->despachoRepo = new DespachoRepository();
        $this->prazoRepo    = new PrazoRepository();
    }

    public function getDespachosByPropriedade(int $idPi): array
    {
        return $this->despachoRepo->getDespachosByPropriedade($idPi);
    }

    public function saveDespacho(Despacho $despacho, Prazo $prazo): bool
    {
        $idPrazo = $this->prazoRepo->savePrazo($prazo);
        if (!$idPrazo) return false;
        $despacho->setPrazoId($idPrazo);
        return $this->despachoRepo->saveDespacho($despacho);
    }

    public function deleteDespacho(int $id): bool { return $this->despachoRepo->deleteDespacho($id); }
}
