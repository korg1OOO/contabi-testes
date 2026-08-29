<?php

namespace app\models;

class Cliente
{
    private int $idCliente;
    private int $usuarioIdUsuario;
    private string $nomeCliente;
    private string $tipoPessoaCliente;
    private string $documentoIdentificadorCliente;
    private string $procuracaoAnexaCliente;

    public function __construct(int $idCliente, int $usuarioIdUsuario, string $nomeCliente, string $tipoPessoaCliente, string $documentoIdentificadorCliente, string $procuracaoAnexaCliente)
    {
        $this->idCliente                      = $idCliente;
        $this->usuarioIdUsuario               = $usuarioIdUsuario;
        $this->nomeCliente                    = $nomeCliente;
        $this->tipoPessoaCliente              = $tipoPessoaCliente;
        $this->documentoIdentificadorCliente  = $documentoIdentificadorCliente;
        $this->procuracaoAnexaCliente         = $procuracaoAnexaCliente;
    }

    public function getIdCliente(): int { return $this->idCliente; }
    public function getUsuarioIdUsuario(): int { return $this->usuarioIdUsuario; }
    public function getNomeCliente(): string { return $this->nomeCliente; }
    public function getTipoPessoaCliente(): string { return $this->tipoPessoaCliente; }
    public function getDocumentoIdentificadorCliente(): string { return $this->documentoIdentificadorCliente; }
    public function getProcuracaoAnexaCliente(): string { return $this->procuracaoAnexaCliente; }

    public function setNomeCliente(string $v): self { $this->nomeCliente = $v; return $this; }
    public function setTipoPessoaCliente(string $v): self { $this->tipoPessoaCliente = $v; return $this; }
    public function setDocumentoIdentificadorCliente(string $v): self { $this->documentoIdentificadorCliente = $v; return $this; }
    public function setProcuracaoAnexaCliente(string $v): self { $this->procuracaoAnexaCliente = $v; return $this; }

    public static function arrayParaObjeto(array $d): self
    {
        return new self($d['idCliente'], $d['Usuario_idUsuario'], $d['nomeCliente'], $d['tipoPessoaCliente'], $d['documentoIdentificadorCliente'], $d['procuracaoAnexaCliente']);
    }
}
