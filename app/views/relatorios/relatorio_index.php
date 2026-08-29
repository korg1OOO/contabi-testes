<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" type="image/png" sizes="192x192" href="<?= URL_BASE ?>/assets/images/icon-192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="<?= URL_BASE ?>/assets/images/icon-512.png">
    <link rel="shortcut icon" href="<?= URL_BASE ?>/favicon.ico">
    <link rel="apple-touch-icon" href="<?= URL_BASE ?>/assets/images/apple-touch-icon.png">
<title>Relatórios • Contabi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= URL_BASE_CSS ?>/style.css">
</head>
<body>
<?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

<?php
$relatorios = [
    'marcas_cliente' => [
        'Marcas por cliente',
        'bi-award',
        'Relação de marcas e seus clientes'
    ],
    'patentes_cliente' => [
        'Patentes por cliente',
        'bi-lightbulb',
        'Relação de patentes e seus clientes'
    ],
    'vencimentos' => [
        'Vencimentos por período',
        'bi-calendar-event',
        'Prazos e vencimentos da agenda'
    ],
    'situacao_processos' => [
        'Situação dos processos',
        'bi-bar-chart',
        'Status atual de marcas e patentes'
    ]
];
?>

<div class="main-content">
    <div class="topbar">
        <div>
            <span class="page-title">Relatórios</span>
            <div class="text-muted small">Consulte e exporte informações da carteira</div>
        </div>
    </div>

    <div class="row g-4">
        <?php foreach ($relatorios as $tipo => $dados): ?>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-body p-4">
                        <div class="report-icon">
                            <i class="bi <?= $dados[1] ?>"></i>
                        </div>

                        <h5><?= $dados[0] ?></h5>
                        <p class="text-muted"><?= $dados[2] ?></p>

                        <form method="get" action="<?= URL_BASE ?>/relatorios/visualizar" class="row g-2">
                            <input type="hidden" name="tipo" value="<?= $tipo ?>">

                            <div class="col-12">
                                <select name="cliente_id" class="form-select">
                                    <option value="0">Todos os clientes</option>

                                    <?php foreach ($clientes as $cliente): ?>
                                        <option value="<?= (int) $cliente['id'] ?>">
                                            <?= htmlspecialchars($cliente['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-6">
    <input
        type="text"
        name="data_inicial"
        class="form-control"
        title="Data inicial"
        placeholder="dd/mm/aaaa"
        maxlength="10"
        inputmode="numeric"
        pattern="\d{2}/\d{2}/\d{4}"
    >
</div>

<div class="col-6">
    <input
        type="text"
        name="data_final"
        class="form-control"
        title="Data final"
        placeholder="dd/mm/aaaa"
        maxlength="10"
        inputmode="numeric"
        pattern="\d{2}/\d{2}/\d{4}"
    >
</div>

                            <div class="col-12 d-grid">
                                <button class="btn btn-primary">Gerar relatório</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="card mt-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-1">Relatórios semanais automáticos</h5>
                    <div class="text-muted small">Gerados para a carteira do usuário</div>
                </div>
                <i class="bi bi-clock-history fs-3 text-primary"></i>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <?php if (isset($_SESSION['usuario']) && $_SESSION['usuario']['perfil'] === 'administrador'): ?><th>Usuário</th><?php endif; ?>
                            <th>Relatório</th>
                            <th>Período</th>
                            <th>Gerado em</th>
                            <th class="text-end">Arquivo</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($relatoriosAutomaticos)): ?>
                        <tr><td colspan="5" class="text-center text-muted py-4">Nenhum relatório semanal gerado.</td></tr>
                    <?php else: ?>
                        <?php foreach ($relatoriosAutomaticos as $relatorio): ?>
                            <tr>
                                <?php if (isset($_SESSION['usuario']) && $_SESSION['usuario']['perfil'] === 'administrador'): ?><td><?= htmlspecialchars($relatorio['usuario_nome']) ?></td><?php endif; ?>
                                <td><?= htmlspecialchars($relatorio['titulo']) ?></td>
                                <td><?= date('d/m/Y', strtotime($relatorio['periodo_inicial'])) ?> a <?= date('d/m/Y', strtotime($relatorio['periodo_final'])) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($relatorio['criado'])) ?></td>
                                <td class="text-end"><a href="<?= URL_BASE ?>/assets/relatorios/<?= rawurlencode($relatorio['arquivo']) ?>" class="btn btn-sm btn-outline-primary" download><i class="bi bi-download"></i> PDF</a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
<script src="<?= URL_BASE ?>/assets/js/mascaras.js"></script>
</body>
</html>
