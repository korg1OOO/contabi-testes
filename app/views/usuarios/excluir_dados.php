<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="192x192" href="<?= URL_BASE ?>/assets/images/icon-192.png">
    <link rel="shortcut icon" href="<?= URL_BASE ?>/favicon.ico">
    <title>Excluir meus dados • Contabi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= URL_BASE_CSS ?>/style.css">
</head>
<body>
<?php require_once __DIR__ . '/../partials/sidebar.php'; ?>
<div class="main-content">
    <div class="topbar"><span class="page-title">Excluir meus dados</span></div>
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <strong><i class="bi bi-exclamation-triangle me-2"></i>Exclusão definitiva da conta</strong>
                </div>
                <div class="card-body">
                    <?php if (!empty($erro)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
                    <?php endif; ?>
                    <p>Esta operação excluirá sua conta e os dados vinculados à sua carteira, incluindo clientes, marcas, patentes, prazos, documentos, notificações e relatórios automáticos.</p>
                    <p class="fw-semibold text-danger">A operação não pode ser desfeita.</p>
                    <form method="POST" action="<?= URL_BASE ?>/minha-conta/excluir-dados">
                        <input type="hidden" name="csrf_token" value="<?= $this->csrfToken() ?>">
                        <div class="mb-3">
                            <label class="form-label">Senha atual</label>
                            <input type="password" name="senha" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Digite EXCLUIR para confirmar</label>
                            <input type="text" name="confirmacao" class="form-control" required autocomplete="off">
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="<?= URL_BASE ?>/dashboard" class="btn btn-outline-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-danger">Excluir conta e dados</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
