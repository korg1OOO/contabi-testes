<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" type="image/png" sizes="192x192" href="<?= URL_BASE ?>/assets/images/icon-192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="<?= URL_BASE ?>/assets/images/icon-512.png">
    <link rel="shortcut icon" href="<?= URL_BASE ?>/favicon.ico">
    <link rel="apple-touch-icon" href="<?= URL_BASE ?>/assets/images/apple-touch-icon.png">
<title>Editar Marca • Contabi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= URL_BASE_CSS ?>/style.css">
</head>
<body>
<?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

<div class="main-content">
    <div class="topbar">
        <span class="page-title">Editar Marca</span>
        <a href="<?= URL_BASE ?>/marcas?cliente_id=<?= $marca['cliente_id'] ?>" class="btn btn-outline-secondary btn-sm">Voltar</a>
    </div>

    <div class="card col-lg-8">
        <div class="card-body p-4">
            <form action="<?= URL_BASE ?>/marcas/atualizar" method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <input type="hidden" name="id" value="<?= $marca['id'] ?>">
                <input type="hidden" name="cliente_id" value="<?= $marca['cliente_id'] ?>">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nº Processo INPI</label>
                        <input type="text" name="numero_processo" class="form-control" value="<?= htmlspecialchars($marca['numero_processo']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Titular</label>
                        <input type="text" name="titular" class="form-control" value="<?= htmlspecialchars($marca['titular']) ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Classe Nice</label>
                        <input type="number" name="classe_nice" class="form-control" value="<?= $marca['classe_nice'] ?>" min="1" max="45" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Apresentação</label>
                        <select name="apresentacao" class="form-select">
                            <option value="Nominal" <?= $marca['apresentacao'] == 'Nominal' ? 'selected' : '' ?>>Nominal</option>
                            <option value="Figurativa" <?= $marca['apresentacao'] == 'Figurativa' ? 'selected' : '' ?>>Figurativa</option>
                            <option value="Mista" <?= $marca['apresentacao'] == 'Mista' ? 'selected' : '' ?>>Mista</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            <option value="Em análise" <?= $marca['status'] == 'Em análise' ? 'selected' : '' ?>>Em análise</option>
                            <option value="Concedida" <?= $marca['status'] == 'Concedida' ? 'selected' : '' ?>>Concedida</option>
                            <option value="Arquivada" <?= $marca['status'] == 'Arquivada' ? 'selected' : '' ?>>Arquivada</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Data de Depósito</label>
                        <input type="text" name="data_deposito" class="form-control" placeholder="dd/mm/aaaa" maxlength="10" pattern="\d{2}/\d{2}/\d{4}" value="<?= !empty($marca['data_deposito']) ? date('d/m/Y', strtotime($marca['data_deposito'])) : '' ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Data de Concessão</label>
                        <input type="text" name="data_concessao" class="form-control" placeholder="dd/mm/aaaa" maxlength="10" pattern="\d{2}/\d{2}/\d{4}" value="<?= !empty($marca['data_concessao']) ? date('d/m/Y', strtotime($marca['data_concessao'])) : '' ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Data de Vencimento</label>
                        <input type="text" name="data_vencimento" class="form-control" placeholder="dd/mm/aaaa" maxlength="10" pattern="\d{2}/\d{2}/\d{4}" value="<?= !empty($marca['data_vencimento']) ? date('d/m/Y', strtotime($marca['data_vencimento'])) : '' ?>">
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary">Atualizar Marca</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>