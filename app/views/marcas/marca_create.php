<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" type="image/png" sizes="192x192" href="<?= URL_BASE ?>/assets/images/icon-192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="<?= URL_BASE ?>/assets/images/icon-512.png">
    <link rel="shortcut icon" href="<?= URL_BASE ?>/favicon.ico">
    <link rel="apple-touch-icon" href="<?= URL_BASE ?>/assets/images/apple-touch-icon.png">
<title>Nova Marca • Contabi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= URL_BASE_CSS ?>/style.css">
</head>
<body>
<?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

<div class="main-content">
    <div class="topbar">
        <span class="page-title">Nova Marca</span>
        <a href="<?= URL_BASE ?>/marcas" class="btn btn-outline-secondary btn-sm">Voltar</a>
    </div>

    <div class="card col-lg-8">
        <div class="card-body p-4">
            <form action="<?= URL_BASE ?>/marcas/salvar" method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Cliente</label>
                    <select name="cliente_id" class="form-select" required>
                        <option value="">Selecione o cliente</option>
                        <?php foreach ($clientes as $cliente): ?>
                            <option value="<?= $cliente['id'] ?>">
                                <?= htmlspecialchars($cliente['nome']) ?> (<?= htmlspecialchars($cliente['cpf_cnpj']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nº Processo INPI</label>
                        <input type="text" name="numero_processo" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Titular</label>
                        <input type="text" name="titular" class="form-control" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Classe Nice</label>
                        <input type="number" name="classe_nice" class="form-control" min="1" max="45" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Apresentação</label>
                        <select name="apresentacao" class="form-select">
                            <option value="Nominal">Nominal</option>
                            <option value="Figurativa">Figurativa</option>
                            <option value="Mista">Mista</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            <option value="Em análise">Em análise</option>
                            <option value="Concedida">Concedida</option>
                            <option value="Arquivada">Arquivada</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Data de Depósito</label>
                        <input type="text" name="data_deposito" class="form-control" placeholder="dd/mm/aaaa" maxlength="10" pattern="\d{2}/\d{2}/\d{4}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Data de Concessão</label>
                        <input type="text" name="data_concessao" class="form-control" placeholder="dd/mm/aaaa" maxlength="10" pattern="\d{2}/\d{2}/\d{4}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Data de Vencimento</label>
                        <input type="text" name="data_vencimento" class="form-control" placeholder="dd/mm/aaaa" maxlength="10" pattern="\d{2}/\d{2}/\d{4}">
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary">Salvar Marca</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>