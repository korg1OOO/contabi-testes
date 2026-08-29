<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" type="image/png" sizes="192x192" href="<?= URL_BASE ?>/assets/images/icon-192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="<?= URL_BASE ?>/assets/images/icon-512.png">
    <link rel="shortcut icon" href="<?= URL_BASE ?>/favicon.ico">
    <link rel="apple-touch-icon" href="<?= URL_BASE ?>/assets/images/apple-touch-icon.png">
<title>Novo documento • Contabi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= URL_BASE_CSS ?>/style.css">
</head>
<body>
<?php require_once __DIR__ . '/../partials/sidebar.php'; ?>
<div class="main-content">
    <div class="topbar"><span class="page-title">Novo documento</span></div>
    <div class="card"><div class="card-body p-4">
        <?php if (isset($_GET['erro'])): ?><div class="alert alert-danger">Não foi possível enviar o arquivo.</div><?php endif; ?>
        <form method="post" action="<?= URL_BASE ?>/documentos/salvar" enctype="multipart/form-data" class="row g-3">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
            <div class="col-md-6">
                <label class="form-label">Arquivo</label>
                <input type="file" name="arquivo" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Tipo</label>
                <select name="tipo" class="form-select">
                    <option value="procuracao">Procuração</option>
                    <option value="comprovante">Comprovante</option>
                    <option value="certificado">Certificado</option>
                    <option value="outro">Outro</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Cliente</label>
                <select name="cliente_id" class="form-select"><option value="0">Nenhum</option><?php foreach ($clientes as $cliente): ?><option value="<?= (int)$cliente['id'] ?>"><?= htmlspecialchars($cliente['nome']) ?></option><?php endforeach; ?></select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Marca</label>
                <select name="marca_id" class="form-select"><option value="0">Nenhuma</option><?php foreach ($marcas as $marca): ?><option value="<?= (int)$marca['id'] ?>"><?= htmlspecialchars($marca['numero_processo'] . ' - ' . $marca['titular']) ?></option><?php endforeach; ?></select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Patente</label>
                <select name="patente_id" class="form-select"><option value="0">Nenhuma</option><?php foreach ($patentes as $patente): ?><option value="<?= (int)$patente['id'] ?>"><?= htmlspecialchars($patente['numero_processo'] . ' - ' . $patente['titular']) ?></option><?php endforeach; ?></select>
            </div>
            <div class="col-12 d-flex justify-content-end gap-2">
                <a href="<?= URL_BASE ?>/documentos" class="btn btn-outline-secondary">Cancelar</a>
                <button class="btn btn-primary">Enviar documento</button>
            </div>
        </form>
    </div></div>
</div>
</body>
</html>
