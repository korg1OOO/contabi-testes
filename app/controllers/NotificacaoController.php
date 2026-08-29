<?php
namespace app\controllers;

use app\core\Controller;
use app\repositories\NotificacaoRepository;

class NotificacaoController extends Controller
{
    private NotificacaoRepository $repository;

    public function __construct()
    {
        $this->repository = new NotificacaoRepository();
    }

    public function index()
    {
        $this->autenticacaoRequired();
        $usuarioId = (int)$this->usuarioLogado()['id'];
        $this->view('notificacoes/notificacao_list', [
            'notificacoes' => $this->repository->getTodas($usuarioId),
            'csrf_token' => $this->csrfToken()
        ]);
    }

    public function marcarLida()
    {
        $this->autenticacaoRequired();
        $this->validarCsrf();
        $usuarioId = (int)$this->usuarioLogado()['id'];
        $this->repository->marcarComoLida((int)($_POST['id'] ?? 0), $usuarioId);
        $destino = trim($_POST['destino'] ?? '');
        $this->redirect($destino ?: URL_BASE . '/notificacoes');
    }

    public function marcarTodas()
    {
        $this->autenticacaoRequired();
        $this->validarCsrf();
        $this->repository->marcarTodasComoLidas((int)$this->usuarioLogado()['id']);
        $this->redirect(URL_BASE . '/notificacoes');
    }
}
