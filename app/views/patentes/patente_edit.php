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
        <a href="<?= URL_BASE ?>/patentes?cliente_id=<?= $patente['cliente_id'] ?>" class="btn btn-outline-secondary btn-sm">Voltar</a>
    </div>

    <div class="card col-lg-8">
        <div class="card-body p-4">
            <form action="<?= URL_BASE ?>/patentes/atualizar" method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <input type="hidden" name="id" value="<?= $patente['id'] ?>">
                <input type="hidden" name="cliente_id" value="<?= $patente['cliente_id'] ?>">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nº Processo INPI</label>
                        <input type="text" name="numero_processo" class="form-control" value="<?= htmlspecialchars($patente['numero_processo']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Titular</label>
                        <input type="text" name="titular" class="form-control" value="<?= htmlspecialchars($patente['titular']) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tipo de Patente</label>
                        <select name="tipo_patente" class="form-select">
                            <option value="Patente de Invenção" <?= $patente['tipo_patente'] == 'Patente de Invenção' ? 'selected' : '' ?>>Patente de Invenção</option>
                            <option value="Modelo de Utilidade" <?= $patente['tipo_patente'] == 'Modelo de Utilidade' ? 'selected' : '' ?>>Modelo de Utilidade</option>
                            <option value="Desenho Industrial" <?= $patente['tipo_patente'] == 'Desenho Industrial' ? 'selected' : '' ?>>Desenho Industrial</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            <option value="Depositada" <?= $patente['status'] == 'Depositada' ? 'selected' : '' ?>>Depositada</option>
                            <option value="Em exame" <?= $patente['status'] == 'Em exame' ? 'selected' : '' ?>>Em exame</option>
                            <option value="Concedida" <?= $patente['status'] == 'Concedida' ? 'selected' : '' ?>>Concedida</option>
                            <option value="Arquivada" <?= $patente['status'] == 'Arquivada' ? 'selected' : '' ?>>Arquivada</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Data de Depósito</label>
                        <input type="text" name="data_deposito" class="form-control" placeholder="dd/mm/aaaa" maxlength="10" pattern="\d{2}/\d{2}/\d{4}" value="<?= !empty($patente['data_deposito']) ? date('d/m/Y', strtotime($patente['data_deposito'])) : '' ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Data de Concessão</label>
                        <input type="text" name="data_concessao" class="form-control" placeholder="dd/mm/aaaa" maxlength="10" pattern="\d{2}/\d{2}/\d{4}" value="<?= !empty($patente['data_concessao']) ? date('d/m/Y', strtotime($patente['data_concessao'])) : '' ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Data de Vencimento</label>
                        <input type="text" name="data_vencimento" class="form-control" placeholder="dd/mm/aaaa" maxlength="10" pattern="\d{2}/\d{2}/\d{4}" value="<?= !empty($patente['data_vencimento']) ? date('d/m/Y', strtotime($patente['data_vencimento'])) : '' ?>">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Inventores</label>
                        <textarea name="inventores" class="form-control" rows="2"><?= htmlspecialchars($patente['inventores'] ?? '') ?></textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Resumo</label>
                        <textarea name="resumo" class="form-control" rows="3"><?= htmlspecialchars($patente['resumo'] ?? '') ?></textarea>
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
</body>
</html>