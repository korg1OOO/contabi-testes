<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" type="image/png" sizes="192x192" href="<?= URL_BASE ?>/assets/images/icon-192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="<?= URL_BASE ?>/assets/images/icon-512.png">
    <link rel="shortcut icon" href="<?= URL_BASE ?>/favicon.ico">
    <link rel="apple-touch-icon" href="<?= URL_BASE ?>/assets/images/apple-touch-icon.png">
<title>Patentes • Contabi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= URL_BASE_CSS ?>/style.css">
</head>
<body>
<?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

<div class="main-content">
    <div class="topbar">
        <span class="page-title"><?= $cliente_id > 0 ? 'Patentes do Cliente' : 'Patentes' ?></span>
        <a href="<?= URL_BASE ?>/patentes/cadastrar<?= $cliente_id > 0 ? '?cliente_id=' . $cliente_id : '' ?>" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> Nova Patente
        </a>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="px-4 py-3">Nº Processo</th>
                            <th class="px-4 py-3">Titular</th>
                            <th class="px-4 py-3">Tipo</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Depósito</th>
                            <th class="px-4 py-3 text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($patentes)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">Nenhuma patente cadastrada.</td></tr>
                        <?php else: ?>
                            <?php foreach ($patentes as $p): ?>
                                <tr>
                                    <td class="px-4 py-3 fw-semibold"><?= htmlspecialchars($p['numero_processo']) ?></td>
                                    <td class="px-4 py-3"><?= htmlspecialchars($p['titular']) ?></td>
                                    <td class="px-4 py-3"><?= htmlspecialchars($p['tipo_patente'] ?? '—') ?></td>
                                    <td class="px-4 py-3">
                                        <span class="badge bg-secondary"><?= htmlspecialchars($p['status']) ?></span>
                                    </td>
                                    <td class="px-4 py-3"><?= date('d/m/Y', strtotime($p['data_deposito'])) ?></td>
                                    <td class="px-4 py-3 text-end">
                                        <a href="<?= URL_BASE ?>/patentes/editar?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form method="post" action="<?= URL_BASE ?>/patentes/excluir" class="d-inline" onsubmit="return confirm('Excluir patente?')">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>