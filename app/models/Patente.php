<?php
namespace app\models;

class Patente
{
    private int $id;
    private int $clienteId;
    private string $numeroProcesso;
    private string $titular;
    private string $status;
    private string $dataDeposito;
    private ?string $dataConcessao = null;
    private ?string $dataVencimento = null;
    private ?string $tipoPatente = null;
    private ?string $inventores = null;
    private ?string $resumo = null;
    private ?string $dataProximaAnuidade = null;
    private ?string $dataManifestacao = null;
    private ?string $dataProrrogacao = null;
    private ?string $observacoes = null;

    public function getId(): int { return $this->id; }
    public function getClienteId(): int { return $this->clienteId; }
    public function getNumeroProcesso(): string { return $this->numeroProcesso; }
    public function getTitular(): string { return $this->titular; }
    public function getStatus(): string { return $this->status; }
    public function getDataDeposito(): string { return $this->dataDeposito; }
    public function getTipoPatente(): ?string { return $this->tipoPatente; }

    public static function fromArray(array $data): self
    {
        $patente = new self();
        $patente->id = (int) $data['id'];
        $patente->clienteId = (int) $data['cliente_id'];
        $patente->numeroProcesso = $data['numero_processo'];
        $patente->titular = $data['titular'];
        $patente->status = $data['status'];
        $patente->dataDeposito = $data['data_deposito'];
        $patente->dataConcessao = $data['data_concessao'] ?? null;
        $patente->dataVencimento = $data['data_vencimento'] ?? null;
        $patente->tipoPatente = $data['tipo_patente'] ?? null;
        $patente->inventores = $data['inventores'] ?? null;
        $patente->resumo = $data['resumo'] ?? null;
        $patente->dataProximaAnuidade = $data['data_proxima_anuidade'] ?? null;
        $patente->dataManifestacao = $data['data_manifestacao'] ?? null;
        $patente->dataProrrogacao = $data['data_prorrogacao'] ?? null;
        $patente->observacoes = $data['observacoes'] ?? null;
        return $patente;
    }
}