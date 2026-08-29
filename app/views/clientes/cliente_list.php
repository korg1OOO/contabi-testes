<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" type="image/png" sizes="192x192" href="<?= URL_BASE ?>/assets/images/icon-192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="<?= URL_BASE ?>/assets/images/icon-512.png">
    <link rel="shortcut icon" href="<?= URL_BASE ?>/favicon.ico">
    <link rel="apple-touch-icon" href="<?= URL_BASE ?>/assets/images/apple-touch-icon.png">
<title>Clientes • Contabi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= URL_BASE_CSS ?>/style.css">
</head>
<body>
<?php require_once __DIR__ . '/../partials/sidebar.php'; ?>
<div class="main-content">
    <div class="topbar">
        <span class="page-title">Clientes</span>
        <a href="<?= URL_BASE ?>/clientes/cadastrar" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Novo Cliente</a>
    </div>
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="px-4 py-3">Nome</th>
                            <th class="px-4 py-3">Tipo de Pessoa</th>
                            <th class="px-4 py-3">Documento</th>
                            <th class="px-4 py-3 text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($clientes)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">Nenhum cliente cadastrado.</td></tr>
                        <?php else: ?>
                        <?php foreach ($clientes as $c): ?>
                            <tr>
                                <td class="px-4 py-3 align-middle fw-semibold"><?= htmlspecialchars($c['nome'] ?? '') ?></td>
                                <td class="px-4 py-3 align-middle"><?= htmlspecialchars(($c['tipo_pessoa'] ?? '') === 'PF' ? 'Pessoa Física' : 'Pessoa Jurídica') ?></td>
                                <td class="px-4 py-3 align-middle"><?= htmlspecialchars($c['cpf_cnpj'] ?? '') ?></td>
                                <td class="px-4 py-3 align-middle text-end">
                                    <a href="<?= URL_BASE ?>/clientes/editar?id=<?= $c['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                                    <form method="post" action="<?= URL_BASE ?>/clientes/excluir" class="d-inline" onsubmit="return confirm('Excluir cliente?')"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>"><input type="hidden" name="id" value="<?= $c['id'] ?>"><button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
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
</body></html>
