<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" type="image/png" sizes="192x192" href="<?= URL_BASE ?>/assets/images/icon-192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="<?= URL_BASE ?>/assets/images/icon-512.png">
    <link rel="shortcut icon" href="<?= URL_BASE ?>/favicon.ico">
    <link rel="apple-touch-icon" href="<?= URL_BASE ?>/assets/images/apple-touch-icon.png">
<title>Criar Conta • Contabi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #1a1f36; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .register-card { max-width: 520px; width: 100%; border-radius: 16px; }
    </style>
</head>
<body>
    <div class="card register-card shadow-lg">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <img src="<?= URL_BASE ?>/assets/images/logo-contabi.png" alt="Logo Contabi" style="width: 72px; height: 72px; object-fit: contain;">
                <h3 class="mt-2 fw-bold">Criar Conta na Contabi</h3>
                <p class="text-muted small">Gestão de Propriedade Industrial</p>
            </div>

            <?php if (!empty($erros)): ?>
                <div class="alert alert-danger">
                    <?php foreach ($erros as $erro): ?>
                        <div><?= $erro ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form action="<?= URL_BASE ?>/register" method="post">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Nome Completo</label>
                        <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($old['nome'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">CPF</label>
                        <input type="text" name="cpf" class="form-control" value="<?= htmlspecialchars($old['cpf'] ?? '') ?>" 
                               maxlength="14" oninput="mascaraCPF(this)" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">E-mail</label>
                        <input type="email" name="email" class="form-control <?= isset($erros['email']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($old['email'] ?? '') ?>">
                        <?php if(isset($erros['email'])): ?>
                            <div class="invalid-feedback"><?= htmlspecialchars($erros['email']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold">Telefone</label>
                        <input type="text" name="telefone" class="form-control" value="<?= htmlspecialchars($old['telefone'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">Senha</label>
                        <input type="password" name="senha" class="form-control" required>
                        <small class="text-muted">Mínimo 8 caracteres, com maiúscula, número e símbolo.</small>
                    </div>
                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-primary">Criar Conta</button>
                </div>
            </form>

            <div class="text-center mt-3">
                <a href="<?= URL_BASE ?>/login" class="text-muted small">Já tenho conta</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function mascaraCPF(campo) {
            let v = campo.value.replace(/\D/g, '');
            v = v.replace(/(\d{3})(\d)/, '$1.$2');
            v = v.replace(/(\d{3})(\d)/, '$1.$2');
            v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            campo.value = v;
        }
    </script>
<script src="<?= URL_BASE ?>/assets/js/mascaras.js"></script>
</body>
</html>