<?php

namespace app\controllers;

use app\core\Controller;
use app\repositories\DespachoRpiRepository;
use app\services\RpiImportacaoService;
use Throwable;

class RpiController extends Controller
{
    private DespachoRpiRepository $despachoRepo;

    public function __construct()
    {
        $this->despachoRepo = new DespachoRpiRepository();
    }

    public function index()
    {
        $this->autenticacaoRequired();
        $this->view('rpi/upload_rpi');
    }

    public function importar()
    {
        $this->autenticacaoRequired();
        $this->validarCsrf();
        $usuario = $this->usuarioLogado();

        if (!isset($_FILES['arquivo_rpi'])) {
            $this->view('rpi/upload_rpi', ['erro' => 'Selecione um arquivo da RPI.']);
            return;
        }

        $arquivo = $_FILES['arquivo_rpi'];
        if ($arquivo['error'] !== UPLOAD_ERR_OK) {
            $this->view('rpi/upload_rpi', ['erro' => $this->mensagemErroUpload((int)$arquivo['error'])]);
            return;
        }

        $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        if (!in_array($extensao, ['zip', 'xml', 'txt', 'pdf'], true)) {
            $this->view('rpi/upload_rpi', ['erro' => 'Envie um arquivo ZIP, XML, TXT ou PDF válido.']);
            return;
        }

        $tipo = trim($_POST['tipo_revista'] ?? '');
        $numeroRevista = trim($_POST['numero_revista'] ?? '') ?: null;

        try {
            $dataPublicacao = $this->dataParaBanco($_POST['data_publicacao'] ?? '');
        } catch (\InvalidArgumentException $e) {
            $this->view('rpi/upload_rpi', ['erro' => $e->getMessage()]);
            return;
        }

        try {
            $conn = $this->despachoRepo->getConnection();
            $conn->beginTransaction();

            $resultado = (new RpiImportacaoService())->importar(
                $arquivo['tmp_name'],
                $arquivo['name'],
                $tipo,
                (int)$usuario['id'],
                $numeroRevista,
                $dataPublicacao
            );

            $conn->commit();

            $mensagem = $resultado['despachos'] . ' despacho(s) associado(s) à sua carteira.';
            if ($tipo === 'marcas') {
                $mensagem .= ' ' . $resultado['marcas_indexadas'] . ' registro(s) oficial(is) indexado(s) para busca de colidências.';
                if ($extensao === 'pdf') {
                    $mensagem .= ' Em PDF, os despachos podem ser associados, mas a indexação estruturada de colidências continua sendo feita pelo XML oficial.';
                }
            }
            if ($resultado['duplicados'] > 0) {
                $mensagem .= ' ' . $resultado['duplicados'] . ' despacho(s) já existente(s) foram ignorado(s).';
            }

            $this->view('rpi/upload_rpi', ['sucesso' => $mensagem, 'resultado' => $resultado]);
        } catch (Throwable $e) {
            $conn = $this->despachoRepo->getConnection();
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $this->view('rpi/upload_rpi', ['erro' => $e->getMessage()]);
        }
    }

    public function listar()
{
    $this->autenticacaoRequired();

    $usuario = $this->usuarioLogado();

    $this->view('rpi/despacho_list', [
        'despachos' => $this->despachoRepo->getDespachosByUsuario(
            (int) $usuario['id']
        ),
        'csrf_token' => $this->csrfToken()
    ]);
}

    public function marcarProcessado()
    {
        $this->autenticacaoRequired();
        $this->validarCsrf();
        $id = (int)($_POST['id'] ?? 0);
        $usuarioId = (int)$this->usuarioLogado()['id'];
        if ($id > 0) {
            $this->despachoRepo->marcarComoProcessadoDoUsuario($id, $usuarioId);
        }
        $this->redirect(URL_BASE . '/rpi/listar');
    }

    private function dataParaBanco(string $data): ?string
    {
        $data = trim($data);
        if ($data === '') {
            return null;
        }

        $objeto = \DateTime::createFromFormat('d/m/Y', $data);
        if ($objeto === false || $objeto->format('d/m/Y') !== $data) {
            throw new \InvalidArgumentException('Informe a data de publicação no formato dd/mm/aaaa.');
        }

        return $objeto->format('Y-m-d');
    }

    private function mensagemErroUpload(int $codigo): string
    {
        return match ($codigo) {
            UPLOAD_ERR_INI_SIZE => 'O arquivo ultrapassa o limite de upload definido no PHP.',
            UPLOAD_ERR_FORM_SIZE => 'O arquivo ultrapassa o limite permitido pelo formulário.',
            UPLOAD_ERR_PARTIAL => 'O arquivo foi enviado apenas parcialmente.',
            UPLOAD_ERR_NO_FILE => 'Nenhum arquivo foi selecionado.',
            UPLOAD_ERR_NO_TMP_DIR => 'A pasta temporária do PHP não está disponível.',
            UPLOAD_ERR_CANT_WRITE => 'O PHP não conseguiu gravar o arquivo temporário.',
            UPLOAD_ERR_EXTENSION => 'Uma extensão do PHP interrompeu o upload.',
            default => 'Não foi possível receber o arquivo enviado.'
        };
    }
}
