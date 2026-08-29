<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" type="image/png" sizes="192x192" href="<?= URL_BASE ?>/assets/images/icon-192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="<?= URL_BASE ?>/assets/images/icon-512.png">
    <link rel="shortcut icon" href="<?= URL_BASE ?>/favicon.ico">
    <link rel="apple-touch-icon" href="<?= URL_BASE ?>/assets/images/apple-touch-icon.png">
<title>Notificações • Contabi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= URL_BASE_CSS ?>/style.css">
</head>
<body>
<?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

<div class="main-content">
    <div class="topbar">
        <div>
            <span class="page-title">Notificações</span>
            <div class="text-muted small">Alertas de prazos e despachos</div>
        </div>

        <form method="post" action="<?= URL_BASE ?>/notificacoes/marcar-todas">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <button class="btn btn-outline-primary">Marcar todas como lidas</button>
        </form>
    </div>

    <div class="card">
        <div class="list-group list-group-flush">
            <?php if (!$notificacoes): ?>
                <div class="text-center text-muted py-5">Nenhuma notificação.</div>
            <?php endif; ?>

            <?php foreach ($notificacoes as $notificacao): ?>
                <div class="list-group-item py-3 <?= !$notificacao['lida'] ? 'notification-unread' : '' ?>">
                    <div class="d-flex gap-3">
                        <div class="notification-icon">
                            <i class="bi <?= $notificacao['tipo'] === 'prazo_critico' ? 'bi-alarm text-warning' : 'bi-info-circle text-primary' ?>"></i>
                        </div>

                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between">
                                <strong><?= htmlspecialchars($notificacao['titulo']) ?></strong>
                                <small class="text-muted">
                                    <?= date('d/m/Y H:i', strtotime($notificacao['criado'])) ?>
                                </small>
                            </div>

                            <p class="mb-2 text-muted">
                                <?= htmlspecialchars($notificacao['mensagem']) ?>
                            </p>

                            <div class="d-flex gap-2">
                                <?php if ($notificacao['link']): ?>
                                    <a href="<?= htmlspecialchars($notificacao['link']) ?>" class="btn btn-sm btn-outline-primary">
                                        Abrir
                                    </a>
                                <?php endif; ?>

                                <?php if (!$notificacao['lida']): ?>
                                    <form method="post" action="<?= URL_BASE ?>/notificacoes/marcar-lida">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $notificacao['id'] ?>">
                                        <button class="btn btn-sm btn-light">Marcar como lida</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
</body>
</html>
