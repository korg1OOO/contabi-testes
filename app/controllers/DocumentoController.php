<?php
namespace app\controllers;

use app\core\Controller;
use app\repositories\ClienteRepository;
use app\repositories\DocumentoRepository;
use app\repositories\MarcaRepository;
use app\repositories\PatenteRepository;
use app\services\UploadService;

class DocumentoController extends Controller
{
    private DocumentoRepository $repository;

    public function __construct()
    {
        $this->repository = new DocumentoRepository();
    }

    public function index()
    {
        $this->autenticacaoRequired();
        $usuarioId = $this->isAdmin() ? null : (int)$this->usuarioLogado()['id'];
        $this->view('documentos/documento_list', [
            'documentos' => $this->repository->listar($usuarioId),
            'csrf_token' => $this->csrfToken()
        ]);
    }

    public function cadastrar()
    {
        $this->autenticacaoRequired();
        $usuarioId = $this->isAdmin() ? null : (int)$this->usuarioLogado()['id'];
        $this->view('documentos/documento_create', [
            'clientes' => (new ClienteRepository())->getClientes($usuarioId),
            'marcas' => (new MarcaRepository())->getAll($usuarioId),
            'patentes' => (new PatenteRepository())->getAll($usuarioId),
            'csrf_token' => $this->csrfToken()
        ]);
    }

    public function salvar()
{
    $this->autenticacaoRequired();
    $this->validarCsrf();

    if (!isset($_FILES['arquivo'])) {
        $_SESSION['erro_documento'] =
            'Nenhum arquivo foi recebido pelo servidor.';

        $this->redirect(
            URL_BASE . '/documentos/cadastrar'
        );
    }

    $marcaId = $this->idOuNull($_POST['marca_id'] ?? null);
    $patenteId = $this->idOuNull($_POST['patente_id'] ?? null);
    $clienteId = $this->idOuNull($_POST['cliente_id'] ?? null);
    $usuarioId = $this->isAdmin() ? null : (int)$this->usuarioLogado()['id'];

    if ($clienteId !== null && !(new ClienteRepository())->getClienteById($clienteId, $usuarioId)) {
        $this->negarAcesso();
    }
    if ($marcaId !== null && !(new MarcaRepository())->findById($marcaId, $usuarioId)) {
        $this->negarAcesso();
    }
    if ($patenteId !== null && !(new PatenteRepository())->findById($patenteId, $usuarioId)) {
        $this->negarAcesso();
    }

    $nomeSalvo = null;

    try {
        $uploadService = new UploadService();

        $nomeSalvo = $uploadService->upload(
            $_FILES['arquivo']
        );

        $salvou = $this->repository->salvar([
            'marca_id' => $marcaId,

            'patente_id' => $patenteId,

            'cliente_id' => $clienteId,

            'nome_arquivo' =>
                basename($_FILES['arquivo']['name']),

            'caminho_arquivo' =>
                $nomeSalvo,

            'tipo' =>
                $_POST['tipo'] ?? 'outro',

            'tamanho_bytes' =>
                (int) $_FILES['arquivo']['size'],

            'uploaded_by' =>
                (int) $this->usuarioLogado()['id']
        ]);

        if (!$salvou) {
            throw new \Exception(
                'Não foi possível registrar o documento no banco de dados.'
            );
        }

        $this->redirect(
            URL_BASE . '/documentos?salvo=1'
        );
    } catch (\Throwable $e) {
        if ($nomeSalvo !== null) {
            $arquivoFisico =
                UPLOAD_PATH
                . DIRECTORY_SEPARATOR
                . $nomeSalvo;

            if (is_file($arquivoFisico)) {
                @unlink($arquivoFisico);
            }
        }

        $_SESSION['erro_documento'] =
            DEV_ENVIRONMENT
                ? $e->getMessage()
                : 'Não foi possível enviar o documento.';

        $this->redirect(
            URL_BASE . '/documentos/cadastrar'
        );
    }
}

    public function baixar()
    {
        $this->autenticacaoRequired();
        $usuarioId = $this->isAdmin() ? null : (int)$this->usuarioLogado()['id'];
        $documento = $this->repository->buscar((int)($_GET['id'] ?? 0), $usuarioId);
        if (!$documento) {
            $this->negarAcesso();
        }
        $caminho = UPLOAD_PATH . '/' . $documento['caminho_arquivo'];
        if (!is_file($caminho)) {
            http_response_code(404);
            exit('Arquivo não encontrado.');
        }
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . rawurlencode($documento['nome_arquivo']) . '"');
        header('Content-Length: ' . filesize($caminho));
        readfile($caminho);
        exit;
    }

    public function excluir()
    {
        $this->autenticacaoRequired();
        $this->validarCsrf();
        $usuarioId = $this->isAdmin() ? null : (int)$this->usuarioLogado()['id'];
        $documento = $this->repository->buscar((int)($_POST['id'] ?? 0), $usuarioId);
        if (!$documento) {
            $this->negarAcesso();
        }
        $this->repository->excluir((int)$documento['id'], $usuarioId);
        $caminho = UPLOAD_PATH . '/' . $documento['caminho_arquivo'];
        if (is_file($caminho)) {
            unlink($caminho);
        }
        $this->redirect(URL_BASE . '/documentos');
    }

    private function idOuNull(mixed $valor): ?int
    {
        $id = (int)$valor;
        return $id > 0 ? $id : null;
    }
}
