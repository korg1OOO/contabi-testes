<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" type="image/png" sizes="192x192" href="<?= URL_BASE ?>/assets/images/icon-192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="<?= URL_BASE ?>/assets/images/icon-512.png">
    <link rel="shortcut icon" href="<?= URL_BASE ?>/favicon.ico">
    <link rel="apple-touch-icon" href="<?= URL_BASE ?>/assets/images/apple-touch-icon.png">
<title>Login • Contabi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body {
            background: #1a1f36;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
        }
        
        .login-card {
            width: 100%;
            max-width: 400px;
            border-radius: 16px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }
        
        .btn-login {
            background: #7c6ff7;
            border: none;
            font-weight: 600;
            padding: 0.85rem;
            font-size: 1rem;
        }
        
        .btn-login:hover {
            background: #6558e0;
        }
        
        .form-control:focus {
            border-color: #7c6ff7;
            box-shadow: 0 0 0 0.2rem rgba(124, 111, 247, 0.25);
        }
    </style>
</head>
<body>
    <div class="card login-card shadow-lg">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <img src="<?= URL_BASE ?>/assets/images/logo-contabi.png" alt="Logo Contabi" style="width: 72px; height: 72px; object-fit: contain;">
                <h3 class="mt-2 fw-bold text-dark">Contabi</h3>
                <p class="text-muted small mb-0">Gestão de Propriedade Industrial</p>
            </div>

            <?php if (!empty($erros)): ?>
                <div class="alert alert-danger d-flex align-items-center py-2">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div><?= is_array($erros) ? implode('<br>', $erros) : $erros ?></div>
                </div>
            <?php endif; ?>

            <form action="<?= URL_BASE ?>/logar" method="post">
                <!-- CPF -->
                <div class="mb-3">
                    <label class="form-label small fw-semibold">CPF</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                        <input type="text" class="form-control" name="cpf" placeholder="000.000.000-00" 
                               maxlength="14" required oninput="mascaraCPF(this)">
                    </div>
                </div>

                <!-- Senha -->
                <div class="mb-4">
                    <label class="form-label small fw-semibold">Senha</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control" name="senha" placeholder="Sua senha" required>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-login text-white">
                        Entrar <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                </div>
            </form>

            <hr class="my-4">
            <p class="text-center text-muted small mb-0">
    Não tem conta? 
    <a href="<?= URL_BASE ?>/register" class="text-primary fw-semibold">Criar conta</a>
</p>
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
</body>
</html>