<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="192x192" href="<?= URL_BASE ?>/assets/images/icon-192.png">
    <link rel="shortcut icon" href="<?= URL_BASE ?>/favicon.ico">
    <title>Meus dados • Contabi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= URL_BASE_CSS ?>/style.css">
</head>
<body>
<?php require_once __DIR__ . '/../partials/sidebar.php'; ?>
<div class="main-content">
    <div class="topbar"><span class="page-title">Meus dados</span></div>

    <div class="card col-lg-8">
        <div class="card-body p-4">
            <?php require __DIR__ . '/../partials/alertas.php'; ?>

            <form novalidate method="post" action="<?= URL_BASE ?>/minha-conta/editar">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '', ENT_QUOTES, 'UTF-8') ?>">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nome Completo</label>
                        <input type="text" name="nome" class="form-control <?= isset($erros['nome']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($usuario['nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">CPF</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($usuario['cpf'] ?? '', ENT_QUOTES, 'UTF-8') ?>" disabled>
                        <small class="text-muted">O CPF não pode ser alterado.</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">E-mail</label>
                        <input type="email" name="email" class="form-control <?= isset($erros['email']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($usuario['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Telefone</label>
                        <input type="text" name="telefone" class="form-control" value="<?= htmlspecialchars($usuario['telefone'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>

                <hr class="my-4">

                <h6 class="fw-semibold mb-1">Trocar senha</h6>
                <p class="text-muted small mb-3">Preencha apenas se quiser alterar sua senha.</p>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Senha atual</label>
                        <input type="password" name="senha_atual" class="form-control <?= isset($erros['senha_atual']) ? 'is-invalid' : '' ?>" autocomplete="current-password">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Nova senha</label>
                        <input type="password" name="nova_senha" class="form-control <?= isset($erros['nova_senha']) ? 'is-invalid' : '' ?>" autocomplete="new-password">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Confirmar senha</label>
                        <input type="password" name="confirmar_senha" class="form-control <?= isset($erros['confirmar_senha']) ? 'is-invalid' : '' ?>" autocomplete="new-password">
                    </div>

                    <div class="col-12">
                        <small class="text-muted">Mínimo 8 caracteres, com maiúscula, número e símbolo.</small>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="<?= URL_BASE ?>/dashboard" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check-circle"></i> Salvar alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="<?= URL_BASE ?>/assets/js/mascaras.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
