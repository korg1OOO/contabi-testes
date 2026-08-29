<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" sizes="192x192" href="<?= URL_BASE ?>/assets/images/icon-192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="<?= URL_BASE ?>/assets/images/icon-512.png">
    <link rel="shortcut icon" href="<?= URL_BASE ?>/favicon.ico">
    <link rel="apple-touch-icon" href="<?= URL_BASE ?>/assets/images/apple-touch-icon.png">
    <title>Busca de Colidências • Contabi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= URL_BASE_CSS ?>/style.css">
</head>
<body>
<?php require_once __DIR__ . '/../partials/sidebar.php'; ?>
<div class="main-content">
    <div class="topbar">
        <span class="page-title">Busca de Colidências</span>
    </div>

    <div class="alert alert-info d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span>A busca utiliza registros oficiais de marcas importados dos arquivos RM da RPI.</span>
        <span class="small">
            <?= number_format((int)($resumoBase['total'] ?? 0), 0, ',', '.') ?> registros
            <?php if (!empty($resumoBase['ultima_revista'])): ?>
                • última revista <?= htmlspecialchars($resumoBase['ultima_revista']) ?>
            <?php endif; ?>
        </span>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?= URL_BASE ?>/busca/colisoes">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nome ou palavra da marca</label>
                        <input type="text" name="termo" class="form-control" value="<?= htmlspecialchars($termo ?? '') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Classe Nice</label>
                        <select name="classe_nice" class="form-select" required>
                            <option value="">Selecione</option>
                            <?php for ($i = 1; $i <= 45; $i++): ?>
                                <option value="<?= $i ?>" <?= (int)($classe_nice ?? 0) === $i ? 'selected' : '' ?>>Classe <?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if (!empty($erro)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>

    <?php if (($termo ?? '') !== '' && empty($erro)): ?>
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <strong>Resultados oficiais da classe <?= (int)$classe_nice ?></strong>
                <span>Critério mínimo: 70%</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($resultados)): ?>
                    <div class="p-4 text-center text-muted">Nenhuma possível colidência encontrada.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th class="px-4">Maior índice</th>
                                    <th>Marca</th>
                                    <th>Critério</th>
                                    <th>Processo</th>
                                    <th>Titular</th>
                                    <th>Status</th>
                                    <th>RPI</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($resultados as $marca): ?>
                                    <?php
                                    $cor = $marca['similaridade'] >= 90 ? 'danger' : ($marca['similaridade'] >= 80 ? 'warning' : 'info');
                                    ?>
                                    <tr>
                                        <td class="px-4"><span class="badge bg-<?= $cor ?>"><?= number_format((float)$marca['similaridade'], 2, ',', '.') ?>%</span></td>
                                        <td>
                                            <strong><?= htmlspecialchars($marca['nome_marca']) ?></strong>
                                            <div class="small text-muted">Fonética <?= number_format((float)$marca['fonetica'], 2, ',', '.') ?>% • Textual <?= number_format((float)$marca['textual'], 2, ',', '.') ?>%</div>
                                        </td>
                                        <td><?= htmlspecialchars($marca['criterio']) ?></td>
                                        <td><?= htmlspecialchars($marca['numero_processo']) ?></td>
                                        <td><?= htmlspecialchars($marca['titular'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($marca['status'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($marca['numero_revista'] ?? '-') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
