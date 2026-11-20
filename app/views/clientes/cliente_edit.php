<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" sizes="192x192" href="<?= URL_BASE ?>/assets/images/icon-192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="<?= URL_BASE ?>/assets/images/icon-512.png">
    <link rel="shortcut icon" href="<?= URL_BASE ?>/favicon.ico">
    <link rel="apple-touch-icon" href="<?= URL_BASE ?>/assets/images/apple-touch-icon.png">
    <title>Editar Cliente • Contabi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= URL_BASE_CSS ?>/style.css">
</head>
<body>
<?php require_once __DIR__ . '/../partials/sidebar.php'; ?>
<div class="main-content">
    <div class="topbar">
        <span class="page-title">Editar Cliente</span>
        <a href="<?= URL_BASE ?>/clientes" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Voltar</a>
    </div>

    <div class="card col-lg-7">
        <div class="card-body p-4">
            <form novalidate action="<?= URL_BASE ?>/clientes/atualizar" method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <input type="hidden" name="id" value="<?= (int)($cliente['id'] ?? 0) ?>">

                <?php require __DIR__ . '/../partials/alertas.php'; ?>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nome</label>
                    <input type="text" name="nome" class="form-control <?= isset($erros['nome']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($cliente['nome'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Tipo de Pessoa</label>
                    <select name="tipo_pessoa" id="tipo_pessoa" class="form-select">
                        <option value="PF" <?= (($cliente['tipo_pessoa'] ?? '') === 'PF') ? 'selected' : '' ?>>Pessoa Física</option>
                        <option value="PJ" <?= (($cliente['tipo_pessoa'] ?? '') === 'PJ') ? 'selected' : '' ?>>Pessoa Jurídica</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold" id="label_cpf_cnpj">CPF / CNPJ</label>
                    <input
                        type="text"
                        name="cpf_cnpj"
                        id="cpf_cnpj"
                        class="form-control <?= isset($erros['cpf_cnpj']) ? 'is-invalid' : '' ?>"
                        value="<?= htmlspecialchars($cliente['cpf_cnpj'] ?? '') ?>"
                        maxlength="18"
                        inputmode="numeric"
                    >
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">E-mail</label>
                        <input type="email" name="email" class="form-control <?= isset($erros['email']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($cliente['email'] ?? '') ?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Telefone</label>
                        <input type="tel" name="telefone" class="form-control <?= isset($erros['telefone']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($cliente['telefone'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="(00) 00000-0000" maxlength="15" inputmode="tel" aria-describedby="telefone-ajuda">
                        <div id="telefone-ajuda" class="form-text">DDD + 8 ou 9 números (telefone opcional).</div>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Endereço</label>
                        <textarea name="endereco" class="form-control" rows="2"><?= htmlspecialchars($cliente['endereco'] ?? '') ?></textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Observações</label>
                        <textarea name="observacoes" class="form-control" rows="3"><?= htmlspecialchars($cliente['observacoes'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-circle"></i> Atualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= URL_BASE ?>/assets/js/mascaras.js?v=2"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tipo = document.getElementById('tipo_pessoa');
    const documento = document.getElementById('cpf_cnpj');
    const label = document.getElementById('label_cpf_cnpj');

    function formatarDocumento() {
        let valor = documento.value.replace(/\D/g, '');

        if (tipo.value === 'PF') {
            valor = valor.substring(0, 11);
            valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
            valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
            valor = valor.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            label.textContent = 'CPF';
            documento.placeholder = '000.000.000-00';
            documento.maxLength = 14;
        } else {
            valor = valor.substring(0, 14);
            valor = valor.replace(/^(\d{2})(\d)/, '$1.$2');
            valor = valor.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
            valor = valor.replace(/\.(\d{3})(\d)/, '.$1/$2');
            valor = valor.replace(/(\d{4})(\d)/, '$1-$2');
            label.textContent = 'CNPJ';
            documento.placeholder = '00.000.000/0000-00';
            documento.maxLength = 18;
        }

        documento.value = valor;
    }

    tipo.addEventListener('change', formatarDocumento);
    documento.addEventListener('input', formatarDocumento);
    formatarDocumento();
});
</script>
</body>
</html>
