<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" type="image/png" sizes="192x192" href="<?= URL_BASE ?>/assets/images/icon-192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="<?= URL_BASE ?>/assets/images/icon-512.png">
    <link rel="shortcut icon" href="<?= URL_BASE ?>/favicon.ico">
    <link rel="apple-touch-icon" href="<?= URL_BASE ?>/assets/images/apple-touch-icon.png">
<title>Documentos • Contabi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= URL_BASE_CSS ?>/style.css">
</head>
<body>
<?php require_once __DIR__ . '/../partials/sidebar.php'; ?>
<div class="main-content">
    <div class="topbar">
        <div>
            <span class="page-title">Documentos</span>
            <div class="text-muted small">Arquivos vinculados aos clientes e processos</div>
        </div>
        <a href="<?= URL_BASE ?>/documentos/cadastrar" class="btn btn-primary btn-sm"><i class="bi bi-upload"></i> Novo documento</a>
    </div>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Arquivo</th>
                        <th>Tipo</th>
                        <th>Vinculado a</th>
                        <th>Enviado por</th>
                        <th>Data</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($documentos)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">Nenhum documento cadastrado.</td></tr>
                <?php else: ?>
                    <?php foreach ($documentos as $documento): ?>
                        <?php $vinculo = $documento['cliente_nome'] ?: ($documento['marca_processo'] ? 'Marca ' . $documento['marca_processo'] : 'Patente ' . $documento['patente_processo']); ?>
                        <tr>
                            <td><?= htmlspecialchars($documento['nome_arquivo']) ?></td>
                            <td><?= htmlspecialchars(ucfirst($documento['tipo'])) ?></td>
                            <td><?= htmlspecialchars($vinculo ?: 'Não informado') ?></td>
                            <td><?= htmlspecialchars($documento['usuario_nome'] ?? 'Usuário removido') ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($documento['criado'])) ?></td>
                            <td class="text-end">
                                <a href="<?= URL_BASE ?>/documentos/baixar?id=<?= (int)$documento['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-download"></i></a>
                                <form method="post" action="<?= URL_BASE ?>/documentos/excluir" class="d-inline" onsubmit="return confirm('Excluir documento?')">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                                    <input type="hidden" name="id" value="<?= (int)$documento['id'] ?>">
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
</body>
</html>
