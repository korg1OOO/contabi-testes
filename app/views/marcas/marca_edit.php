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
                <?php if (!empty($erros)): ?>
                    <div class="alert alert-danger" role="alert">
                        <strong>Não foi possível salvar.</strong> Corrija as informações abaixo:
                        <ul class="mb-0 mt-1">
                            <?php foreach ($erros as $mensagem): ?>
                                <li><?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <input type="hidden" name="id" value="<?= $marca['id'] ?>">
                <input type="hidden" name="cliente_id" value="<?= $marca['cliente_id'] ?>">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nº Processo INPI</label>
                        <input type="text" name="numero_processo" class="form-control <?= isset($erros['numero_processo']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($marca['numero_processo']) ?>" maxlength="30" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Titular</label>
                        <input type="text" name="titular" class="form-control <?= isset($erros['titular']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($marca['titular']) ?>" maxlength="200" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Classe Nice</label>
                        <input type="number" name="classe_nice" class="form-control <?= isset($erros['classe_nice']) ? 'is-invalid' : '' ?>" value="<?= $marca['classe_nice'] ?>" min="1" max="45" required>
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
                        <input type="text" name="data_deposito" class="form-control <?= isset($erros['data_deposito']) ? 'is-invalid' : '' ?>" placeholder="dd/mm/aaaa" maxlength="10" pattern="\d{2}/\d{2}/\d{4}" value="<?= htmlspecialchars(\app\helpers\Validador::dataParaExibicao($marca['data_deposito'] ?? null), ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Data de Concessão</label>
                        <input type="text" name="data_concessao" class="form-control <?= isset($erros['data_concessao']) ? 'is-invalid' : '' ?>" placeholder="dd/mm/aaaa" maxlength="10" pattern="\d{2}/\d{2}/\d{4}" value="<?= htmlspecialchars(\app\helpers\Validador::dataParaExibicao($marca['data_concessao'] ?? null), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Data de Vencimento</label>
                        <input type="text" name="data_vencimento" class="form-control <?= isset($erros['data_vencimento']) ? 'is-invalid' : '' ?>" placeholder="dd/mm/aaaa" maxlength="10" pattern="\d{2}/\d{2}/\d{4}" value="<?= htmlspecialchars(\app\helpers\Validador::dataParaExibicao($marca['data_vencimento'] ?? null), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Próxima Anuidade</label>
                        <input type="text" name="data_proxima_anuidade" class="form-control" placeholder="dd/mm/aaaa" maxlength="10" pattern="\d{2}/\d{2}/\d{4}" value="<?= htmlspecialchars(\app\helpers\Validador::dataParaExibicao($marca['data_proxima_anuidade'] ?? null), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Renovação</label>
                        <input type="text" name="data_renovacao" class="form-control" placeholder="dd/mm/aaaa" maxlength="10" pattern="\d{2}/\d{2}/\d{4}" value="<?= htmlspecialchars(\app\helpers\Validador::dataParaExibicao($marca['data_renovacao'] ?? null), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Oposição</label>
                        <input type="text" name="data_oposicao" class="form-control" placeholder="dd/mm/aaaa" maxlength="10" pattern="\d{2}/\d{2}/\d{4}" value="<?= htmlspecialchars(\app\helpers\Validador::dataParaExibicao($marca['data_oposicao'] ?? null), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Prorrogação</label>
                        <input type="text" name="data_prorrogacao" class="form-control" placeholder="dd/mm/aaaa" maxlength="10" pattern="\d{2}/\d{2}/\d{4}" value="<?= htmlspecialchars(\app\helpers\Validador::dataParaExibicao($marca['data_prorrogacao'] ?? null), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Manifestação</label>
                        <input type="text" name="data_manifestacao" class="form-control" placeholder="dd/mm/aaaa" maxlength="10" pattern="\d{2}/\d{2}/\d{4}" value="<?= htmlspecialchars(\app\helpers\Validador::dataParaExibicao($marca['data_manifestacao'] ?? null), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary">Atualizar Marca</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="<?= URL_BASE ?>/assets/js/mascaras.js?v=2"></script>
</body>
</html>
