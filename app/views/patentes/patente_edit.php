<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" type="image/png" sizes="192x192" href="<?= URL_BASE ?>/assets/images/icon-192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="<?= URL_BASE ?>/assets/images/icon-512.png">
    <link rel="shortcut icon" href="<?= URL_BASE ?>/favicon.ico">
    <link rel="apple-touch-icon" href="<?= URL_BASE ?>/assets/images/apple-touch-icon.png">
<title>Editar Patente • Contabi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= URL_BASE_CSS ?>/style.css">
</head>
<body>
<?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

<div class="main-content">
    <div class="topbar">
        <span class="page-title">Editar Patente</span>
        <a href="<?= URL_BASE ?>/patentes?cliente_id=<?= (int)($patente['cliente_id'] ?? 0) ?>" class="btn btn-outline-secondary btn-sm">Voltar</a>
    </div>

    <div class="card col-lg-8">
        <div class="card-body p-4">
            <form action="<?= URL_BASE ?>/patentes/atualizar" method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <?php require __DIR__ . '/../partials/alertas.php'; ?>
                <input type="hidden" name="id" value="<?= (int)($patente['id'] ?? 0) ?>">
                <input type="hidden" name="cliente_id" value="<?= (int)($patente['cliente_id'] ?? 0) ?>">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nº Processo INPI</label>
                        <input type="text" name="numero_processo" class="form-control <?= isset($erros['numero_processo']) ? 'is-invalid' : '' ?>" maxlength="30" value="<?= htmlspecialchars((string)($patente['numero_processo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Titular</label>
                        <input type="text" name="titular" class="form-control <?= isset($erros['titular']) ? 'is-invalid' : '' ?>" maxlength="200" value="<?= htmlspecialchars((string)($patente['titular'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tipo de Patente</label>
                        <select name="tipo_patente" class="form-select">
                            <option value="Patente de Invenção" <?= ($patente['tipo_patente'] ?? '') === 'Patente de Invenção' ? 'selected' : '' ?>>Patente de Invenção</option>
                            <option value="Modelo de Utilidade" <?= ($patente['tipo_patente'] ?? '') === 'Modelo de Utilidade' ? 'selected' : '' ?>>Modelo de Utilidade</option>
                            <option value="Desenho Industrial" <?= ($patente['tipo_patente'] ?? '') === 'Desenho Industrial' ? 'selected' : '' ?>>Desenho Industrial</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            <option value="Depositada" <?= ($patente['status'] ?? '') === 'Depositada' ? 'selected' : '' ?>>Depositada</option>
                            <option value="Em exame" <?= ($patente['status'] ?? '') === 'Em exame' ? 'selected' : '' ?>>Em exame</option>
                            <option value="Concedida" <?= ($patente['status'] ?? '') === 'Concedida' ? 'selected' : '' ?>>Concedida</option>
                            <option value="Arquivada" <?= ($patente['status'] ?? '') === 'Arquivada' ? 'selected' : '' ?>>Arquivada</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Data de Depósito</label>
                        <input type="text" name="data_deposito" class="form-control <?= isset($erros['data_deposito']) ? 'is-invalid' : '' ?>" placeholder="dd/mm/aaaa" maxlength="10" pattern="\d{2}/\d{2}/\d{4}" value="<?= htmlspecialchars(\app\helpers\Validador::dataParaExibicao($patente['data_deposito'] ?? null), ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Data de Concessão</label>
                        <input type="text" name="data_concessao" class="form-control <?= isset($erros['data_concessao']) ? 'is-invalid' : '' ?>" placeholder="dd/mm/aaaa" maxlength="10" pattern="\d{2}/\d{2}/\d{4}" value="<?= htmlspecialchars(\app\helpers\Validador::dataParaExibicao($patente['data_concessao'] ?? null), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Data de Vencimento</label>
                        <input type="text" name="data_vencimento" class="form-control <?= isset($erros['data_vencimento']) ? 'is-invalid' : '' ?>" placeholder="dd/mm/aaaa" maxlength="10" pattern="\d{2}/\d{2}/\d{4}" value="<?= htmlspecialchars(\app\helpers\Validador::dataParaExibicao($patente['data_vencimento'] ?? null), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Próxima Anuidade</label>
                        <input type="text" name="data_proxima_anuidade" class="form-control <?= isset($erros['data_proxima_anuidade']) ? 'is-invalid' : '' ?>" placeholder="dd/mm/aaaa" maxlength="10" pattern="\d{2}/\d{2}/\d{4}" value="<?= htmlspecialchars(\app\helpers\Validador::dataParaExibicao($patente['data_proxima_anuidade'] ?? null), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Prorrogação</label>
                        <input type="text" name="data_prorrogacao" class="form-control <?= isset($erros['data_prorrogacao']) ? 'is-invalid' : '' ?>" placeholder="dd/mm/aaaa" maxlength="10" pattern="\d{2}/\d{2}/\d{4}" value="<?= htmlspecialchars(\app\helpers\Validador::dataParaExibicao($patente['data_prorrogacao'] ?? null), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Manifestação</label>
                        <input type="text" name="data_manifestacao" class="form-control <?= isset($erros['data_manifestacao']) ? 'is-invalid' : '' ?>" placeholder="dd/mm/aaaa" maxlength="10" pattern="\d{2}/\d{2}/\d{4}" value="<?= htmlspecialchars(\app\helpers\Validador::dataParaExibicao($patente['data_manifestacao'] ?? null), ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Inventores</label>
                        <textarea name="inventores" class="form-control" rows="2"><?= htmlspecialchars((string)($patente['inventores'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Resumo</label>
                        <textarea name="resumo" class="form-control" rows="3"><?= htmlspecialchars((string)($patente['resumo'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary">Atualizar Patente</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= URL_BASE ?>/assets/js/mascaras.js?v=2"></script>
</body>
</html>
