<?php

namespace app\services;

use Exception;

class UploadService
{
    private array $extensoesPermitidas = [
        'jpg',
        'jpeg',
        'png',
        'pdf',
        'gif',
        'doc',
        'docx'
    ];

    private int $tamanhoMaximo = 5 * 1024 * 1024;

    private string $uploadPath;

    public function __construct(?string $path = null)
    {
        $this->uploadPath = $path ?? UPLOAD_PATH;

        if (!is_dir($this->uploadPath)) {
            if (
                !mkdir($this->uploadPath, 0777, true)
                && !is_dir($this->uploadPath)
            ) {
                throw new Exception(
                    'Não foi possível criar a pasta de uploads.'
                );
            }
        }

        if (!is_writable($this->uploadPath)) {
            throw new Exception(
                'A pasta de uploads não possui permissão de escrita.'
            );
        }
    }

    public function upload(array $file): string
    {
        if (!isset($file['error'])) {
            throw new Exception('Arquivo de upload inválido.');
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception(
                $this->mensagemErroUpload((int) $file['error'])
            );
        }

        if (
            !isset($file['tmp_name'])
            || !is_uploaded_file($file['tmp_name'])
        ) {
            throw new Exception(
                'O arquivo temporário do upload não é válido.'
            );
        }

        if ((int) $file['size'] <= 0) {
            throw new Exception('O arquivo enviado está vazio.');
        }

        if ((int) $file['size'] > $this->tamanhoMaximo) {
            throw new Exception(
                'Arquivo muito grande. O tamanho máximo permitido é 5 MB.'
            );
        }

        $extensao = strtolower(
            pathinfo($file['name'], PATHINFO_EXTENSION)
        );

        if (
            $extensao === ''
            || !in_array(
                $extensao,
                $this->extensoesPermitidas,
                true
            )
        ) {
            throw new Exception(
                'Tipo de arquivo não permitido.'
            );
        }

        $novoNome =
            bin2hex(random_bytes(16))
            . '.'
            . $extensao;

        $destino =
            $this->uploadPath
            . DIRECTORY_SEPARATOR
            . $novoNome;

        if (!move_uploaded_file($file['tmp_name'], $destino)) {
            throw new Exception(
                'Não foi possível mover o arquivo para a pasta de uploads.'
            );
        }

        return $novoNome;
    }

    private function mensagemErroUpload(int $codigo): string
    {
        return match ($codigo) {
            UPLOAD_ERR_INI_SIZE =>
                'O arquivo ultrapassa o limite definido no PHP.',

            UPLOAD_ERR_FORM_SIZE =>
                'O arquivo ultrapassa o limite permitido pelo formulário.',

            UPLOAD_ERR_PARTIAL =>
                'O arquivo foi enviado apenas parcialmente.',

            UPLOAD_ERR_NO_FILE =>
                'Nenhum arquivo foi selecionado.',

            UPLOAD_ERR_NO_TMP_DIR =>
                'A pasta temporária do PHP não está disponível.',

            UPLOAD_ERR_CANT_WRITE =>
                'O PHP não conseguiu gravar o arquivo no disco.',

            UPLOAD_ERR_EXTENSION =>
                'Uma extensão do PHP interrompeu o upload.',

            default =>
                'Ocorreu um erro desconhecido durante o upload.'
        };
    }
}