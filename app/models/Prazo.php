<?php
namespace app\models;

class Prazo
{
    private int $id;
    private ?int $marcaId = null;
    private ?int $patenteId = null;
    private string $tipo;
    private string $dataVencimento;
    private ?string $dataAlerta30 = null;
    private ?string $dataAlerta15 = null;
    private ?string $dataAlerta7 = null;
    private bool $notificado = false;
    private string $status = 'pendente';
    private ?string $observacoes = null;

    public function getId(): int { return $this->id; }
    public function getMarcaId(): ?int { return $this->marcaId; }
    public function getPatenteId(): ?int { return $this->patenteId; }
    public function getTipo(): string { return $this->tipo; }
    public function getDataVencimento(): string { return $this->dataVencimento; }
    public function getStatus(): string { return $this->status; }

    public static function fromArray(array $data): self
    {
        $prazo = new self();
        $prazo->id = (int) $data['id'];
        $prazo->marcaId = $data['marca_id'] ? (int)$data['marca_id'] : null;
        $prazo->patenteId = $data['patente_id'] ? (int)$data['patente_id'] : null;
        $prazo->tipo = $data['tipo'];
        $prazo->dataVencimento = $data['data_vencimento'];
        $prazo->dataAlerta30 = $data['data_alerta_30'] ?? null;
        $prazo->dataAlerta15 = $data['data_alerta_15'] ?? null;
        $prazo->dataAlerta7 = $data['data_alerta_7'] ?? null;
        $prazo->notificado = (bool) $data['notificado'];
        $prazo->status = $data['status'];
        $prazo->observacoes = $data['observacoes'] ?? null;
        return $prazo;
    }
}