<?php
namespace app\models;

class Marca
{
    private int $id;
    private int $clienteId;
    private string $numeroProcesso;
    private string $titular;
    private int $classeNice;
    private string $status;
    private string $dataDeposito;
    private ?string $dataConcessao = null;
    private ?string $dataVencimento = null;
    private ?string $dataProximaAnuidade = null;
    private ?string $dataRenovacao = null;
    private ?string $dataOposicao = null;
    private ?string $dataProrrogacao = null;
    private ?string $dataManifestacao = null;
    private string $apresentacao;
    private ?string $observacoes = null;

    public function getId(): int { return $this->id; }
    public function getClienteId(): int { return $this->clienteId; }
    public function getNumeroProcesso(): string { return $this->numeroProcesso; }
    public function getTitular(): string { return $this->titular; }
    public function getClasseNice(): int { return $this->classeNice; }
    public function getStatus(): string { return $this->status; }
    public function getDataDeposito(): string { return $this->dataDeposito; }
    public function getDataConcessao(): ?string { return $this->dataConcessao; }
    public function getDataVencimento(): ?string { return $this->dataVencimento; }
    public function getDataProximaAnuidade(): ?string { return $this->dataProximaAnuidade; }
    public function getDataRenovacao(): ?string { return $this->dataRenovacao; }
    public function getApresentacao(): string { return $this->apresentacao; }
    public function getObservacoes(): ?string { return $this->observacoes; }

    public static function fromArray(array $data): self
    {
        $marca = new self();
        $marca->id = (int) $data['id'];
        $marca->clienteId = (int) $data['cliente_id'];
        $marca->numeroProcesso = $data['numero_processo'];
        $marca->titular = $data['titular'];
        $marca->classeNice = (int) $data['classe_nice'];
        $marca->status = $data['status'];
        $marca->dataDeposito = $data['data_deposito'];
        $marca->dataConcessao = $data['data_concessao'] ?? null;
        $marca->dataVencimento = $data['data_vencimento'] ?? null;
        $marca->dataProximaAnuidade = $data['data_proxima_anuidade'] ?? null;
        $marca->dataRenovacao = $data['data_renovacao'] ?? null;
        $marca->dataOposicao = $data['data_oposicao'] ?? null;
        $marca->dataProrrogacao = $data['data_prorrogacao'] ?? null;
        $marca->dataManifestacao = $data['data_manifestacao'] ?? null;
        $marca->apresentacao = $data['apresentacao'] ?? '';
        $marca->observacoes = $data['observacoes'] ?? null;
        return $marca;
    }
}