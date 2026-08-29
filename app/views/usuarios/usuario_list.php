<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" type="image/png" sizes="192x192" href="<?= URL_BASE ?>/assets/images/icon-192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="<?= URL_BASE ?>/assets/images/icon-512.png">
    <link rel="shortcut icon" href="<?= URL_BASE ?>/favicon.ico">
    <link rel="apple-touch-icon" href="<?= URL_BASE ?>/assets/images/apple-touch-icon.png">
<title>Usuários • Contabi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= URL_BASE_CSS ?>/style.css">
</head>
<body>
<?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

<div class="main-content">
    <div class="topbar">
        <span class="page-title">Usuários</span>
        <a href="<?= URL_BASE ?>/usuarios/cadastrar" class="btn btn-primary btn-sm">
            <i class="bi bi-person-plus"></i> Novo Usuário
        </a>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="px-4 py-3">Nome</th>
                            <th class="px-4 py-3">E-mail</th>
                            <th class="px-4 py-3">Telefone</th>
                            <th class="px-4 py-3">Perfil</th>
                            <th class="px-4 py-3 text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($usuarios)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    Nenhum usuário cadastrado.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($usuarios as $u): ?>
                                <tr>
                                    <td class="px-4 py-3 align-middle fw-semibold">
                                        <?= htmlspecialchars($u['nome']) ?>
                                    </td>
                                    <td class="px-4 py-3 align-middle">
                                        <?= htmlspecialchars($u['email'] ?? '—') ?>
                                    </td>
                                    <td class="px-4 py-3 align-middle">
                                        <?= htmlspecialchars($u['telefone'] ?? '—') ?>
                                    </td>
                                    <td class="px-4 py-3 align-middle">
                                        <span class="badge bg-secondary">
                                            <?= htmlspecialchars(ucfirst($u['perfil'])) ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 align-middle text-end">
                                        <a href="<?= URL_BASE ?>/usuarios/editar?id=<?= $u['id'] ?>" 
                                           class="btn btn-sm btn-outline-secondary">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="<?= URL_BASE ?>/usuarios/excluir?id=<?= $u['id'] ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Tem certeza que deseja excluir este usuário?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
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