<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" type="image/png" sizes="192x192" href="<?= URL_BASE ?>/assets/images/icon-192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="<?= URL_BASE ?>/assets/images/icon-512.png">
    <link rel="shortcut icon" href="<?= URL_BASE ?>/favicon.ico">
    <link rel="apple-touch-icon" href="<?= URL_BASE ?>/assets/images/apple-touch-icon.png">
<title>Editar Usuário • Contabi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= URL_BASE_CSS ?>/style.css">
</head>
<body>
<?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

<div class="main-content">
    <div class="topbar">
        <span class="page-title">Editar Usuário</span>
        <a href="<?= URL_BASE ?>/usuarios" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>

    <div class="card col-lg-8">
        <div class="card-body p-4">
            <form action="<?= URL_BASE ?>/usuarios/atualizar" method="post">
                <input type="hidden" name="id" value="<?= $usuario['id'] ?>">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nome Completo</label>
                        <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($usuario['nome']) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">CPF</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($usuario['cpf']) ?>" disabled>
                        <small class="text-muted">O CPF não pode ser alterado.</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">E-mail</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($usuario['email'] ?? '') ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Telefone</label>
                        <input type="text" name="telefone" class="form-control" value="<?= htmlspecialchars($usuario['telefone'] ?? '') ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Perfil</label>
                        <select name="perfil" class="form-select" required>
                            <option value="consultor" <?= $usuario['perfil'] == 'consultor' ? 'selected' : '' ?>>Consultor</option>
                            <option value="agente_pi" <?= $usuario['perfil'] == 'agente_pi' ? 'selected' : '' ?>>Agente de PI</option>
                            <option value="administrador" <?= $usuario['perfil'] == 'administrador' ? 'selected' : '' ?>>Administrador</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check-circle"></i> Atualizar Usuário
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>