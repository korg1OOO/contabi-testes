<?php

namespace app\models;

class Despacho
{
    private int $idDespacho;
    private ?string $tipoEventoDespacho;
    private int $prazoId;
    private int $propriedadeIntelectualId;

    public function __construct(int $id, ?string $tipoEventoDespacho, int $prazoId, int $propriedadeIntelectualId)
    {
        $this->idDespacho               = $id;
        $this->tipoEventoDespacho       = $tipoEventoDespacho;
        $this->prazoId                  = $prazoId;
        $this->propriedadeIntelectualId = $propriedadeIntelectualId;
    }

    public function getIdDespacho(): int { return $this->idDespacho; }
    public function getTipoEventoDespacho(): ?string { return $this->tipoEventoDespacho; }
    public function getPrazoId(): int { return $this->prazoId; }
    public function getPropriedadeIntelectualId(): int { return $this->propriedadeIntelectualId; }
    public function setPrazoId(int $v): self { $this->prazoId = $v; return $this; }
    public function setTipoEventoDespacho(?string $v): self { $this->tipoEventoDespacho = $v; return $this; }

    public static function arrayParaObjeto(array $d): self
    {
        return new self($d['idDespacho'], $d['tipoEventoDespacho'] ?? null, $d['prazo_idPrazo'], $d['propriedade_intelectual_idPropriedadeIntelectual']);
    }
}
