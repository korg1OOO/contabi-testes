<?php
namespace app\models;

class Usuario
{
    private int $id;
    private string $nome;
    private string $cpf;
    private ?string $email;
    private ?string $telefone;
    private string $senha;
    private string $perfil;           
    private bool $ativo;
    private string $criado;
    private string $alterado;

    public function __construct(
        int $id,
        string $nome,
        string $cpf,
        ?string $email,
        ?string $telefone,
        string $senha,
        string $perfil,
        bool $ativo = true
    ) {
        $this->id       = $id;
        $this->nome     = $nome;
        $this->cpf      = $cpf;
        $this->email    = $email;
        $this->telefone = $telefone;
        $this->senha    = $senha;
        $this->perfil   = $perfil;
        $this->ativo    = $ativo;
    }

    public function getId(): int { return $this->id; }
    public function getNome(): string { return $this->nome; }
    public function getCpf(): string { return $this->cpf; }
    public function getEmail(): ?string { return $this->email; }
    public function getTelefone(): ?string { return $this->telefone; }
    public function getSenha(): string { return $this->senha; }
    public function getPerfil(): string { return $this->perfil; }
    public function isAtivo(): bool { return $this->ativo; }

    public function setNome(string $v): self { $this->nome = $v; return $this; }
    public function setCpf(string $v): self { $this->cpf = $v; return $this; }
    public function setEmail(?string $v): self { $this->email = $v; return $this; }
    public function setTelefone(?string $v): self { $this->telefone = $v; return $this; }
    public function setSenha(string $v): self { $this->senha = $v; return $this; }
    public function setPerfil(string $v): self { $this->perfil = $v; return $this; }

    public static function fromArray(array $d): self
    {
        return new self(
            (int)$d['id'],
            $d['nome'],
            $d['cpf'],
            $d['email'] ?? null,
            $d['telefone'] ?? null,
            $d['senha'],
            $d['perfil'],
            (bool)($d['ativo'] ?? true)
        );
    }
}