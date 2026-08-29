<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" type="image/png" sizes="192x192" href="<?= URL_BASE ?>/assets/images/icon-192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="<?= URL_BASE ?>/assets/images/icon-512.png">
    <link rel="shortcut icon" href="<?= URL_BASE ?>/favicon.ico">
    <link rel="apple-touch-icon" href="<?= URL_BASE ?>/assets/images/apple-touch-icon.png">
<title><?= htmlspecialchars($titulo) ?> • Contabi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= URL_BASE_CSS ?>/style.css">
</head>
<body>
<?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

<div class="main-content">
    <div class="topbar">
        <div>
            <span class="page-title"><?= htmlspecialchars($titulo) ?></span>
            <div class="text-muted small"><?= count($dados) ?> registro(s)</div>
        </div>

        <div class="d-flex gap-2">
            <a href="<?= URL_BASE ?>/relatorios" class="btn btn-light">Voltar</a>
            <?php if (empty($erro)): ?>
            <a
                href="<?= URL_BASE ?>/relatorios/pdf?<?= http_build_query(array_merge(['tipo' => $tipo], $filtros)) ?>"
                class="btn btn-primary"
            >
                <i class="bi bi-file-earmark-pdf me-1"></i>
                Baixar PDF
            </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($erro)): ?>
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($erro) ?>
        </div>
    <?php endif; ?>

    <div class="card">
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <?php if ($dados): ?>
                        <?php foreach (array_keys($dados[0]) as $coluna): ?>
                            <th><?= htmlspecialchars($coluna) ?></th>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tr>
            </thead>

            <tbody>
                <?php if (!$dados): ?>
                    <tr>
                        <td class="text-center text-muted py-5">
                            Nenhum registro encontrado para os filtros informados.
                        </td>
                    </tr>
                <?php endif; ?>

                <?php foreach ($dados as $linha): ?>
                    <tr>
                        <?php foreach ($linha as $valor): ?>
                            <?php
                            $valorExibido = (string) $valor;

                            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $valorExibido)) {
                                $data = \DateTime::createFromFormat('Y-m-d', $valorExibido);

                                if ($data) {
                                    $valorExibido = $data->format('d/m/Y');
                                }
                            } elseif (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $valorExibido)) {
                                $data = \DateTime::createFromFormat(
                                    'Y-m-d H:i:s',
                                    $valorExibido
                                );

                                if ($data) {
                                    $valorExibido = $data->format('d/m/Y H:i');
                                }
                            }
                            ?>

                            <td><?= htmlspecialchars($valorExibido) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
</div>
</body>
</html>
