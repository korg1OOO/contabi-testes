<?php
namespace app\controllers;

use app\core\Controller;
use app\repositories\DashboardRepository;
use app\repositories\NotificacaoRepository;
use app\repositories\PrazoRepository;
use app\services\PrazoService;

class DashboardController extends Controller
{
    public function index()
    {
        $this->autenticacaoRequired();
        $usuario = $this->usuarioLogado();
        $isAdmin = $this->isAdmin();
        $usuarioId = $isAdmin ? null : (int)$usuario['id'];

        if (!$isAdmin) {
            (new PrazoService())->processarNotificacoesDePrazos((int)$usuario['id']);
        }

        $dashboardRepo = new DashboardRepository();
        $prazoRepo = new PrazoRepository();
        $notificacaoRepo = new NotificacaoRepository();

        $this->view('dashboard', [
            'usuario' => $usuario,
            'isAdmin' => $isAdmin,
            'metricas' => $dashboardRepo->getMetricas($usuarioId),
            'processosPorMes' => $dashboardRepo->getProcessosPorMes($usuarioId),
            'statusProcessos' => $dashboardRepo->getStatusProcessos($usuarioId),
            'processosRecentes' => $dashboardRepo->getProcessosRecentes($usuarioId),
            'proximosPrazos' => $prazoRepo->getProximosPrazos($usuarioId, 6),
            'prazosCriticos' => $prazoRepo->countPrazosCriticos($usuarioId),
            'notificacoes' => $notificacaoRepo->getNotificacoesNaoLidas((int)$usuario['id'])
        ]);
    }
}
