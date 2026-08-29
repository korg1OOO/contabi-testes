<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" type="image/png" sizes="192x192" href="<?= URL_BASE ?>/assets/images/icon-192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="<?= URL_BASE ?>/assets/images/icon-512.png">
    <link rel="shortcut icon" href="<?= URL_BASE ?>/favicon.ico">
    <link rel="apple-touch-icon" href="<?= URL_BASE ?>/assets/images/apple-touch-icon.png">
<title>Agenda • Contabi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= URL_BASE_CSS ?>/style.css">
</head>
<body>
<?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

<div class="main-content">
    <div class="topbar">
        <div>
            <span class="page-title">Agenda</span>
            <div class="text-muted small">Gerencie os prazos dos processos</div>
        </div>

        <a href="<?= URL_BASE ?>/prazos/cadastrar" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>
            Cadastrar prazo
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card summary-card border-danger">
                <div class="summary-number text-danger"><?= $resumo['vencidos'] ?></div>
                <div>Vencidos</div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card summary-card border-warning">
                <div class="summary-number text-warning"><?= $resumo['urgentes'] ?></div>
                <div>Urgentes em 7 dias</div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card summary-card border-info">
                <div class="summary-number text-info"><?= $resumo['atencao'] ?></div>
                <div>Atenção em 8 a 15 dias</div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card summary-card border-success">
                <div class="summary-number text-success"><?= $resumo['no_prazo'] ?></div>
                <div>No prazo</div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="get" action="<?= URL_BASE ?>/prazos" class="row g-3 align-items-end">
                <div class="col-lg-5">
                    <label class="form-label">Busca</label>
                    <input
                        type="text"
                        name="busca"
                        value="<?= htmlspecialchars($filtros['busca']) ?>"
                        class="form-control"
                        placeholder="Processo, titular ou cliente"
                    >
                </div>

                <div class="col-md-3">
                    <label class="form-label">Período</label>
                    <select name="periodo" class="form-select">
                        <option value="0">Todos</option>

                        <?php foreach ([30, 60, 90, 180, 365] as $periodo): ?>
                            <option
                                value="<?= $periodo ?>"
                                <?= $filtros['periodo'] === $periodo ? 'selected' : '' ?>
                            >
                                Próximos <?= $periodo ?> dias
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Todos</option>

                        <?php foreach (['pendente', 'cumprido', 'vencido', 'cancelado'] as $status): ?>
                            <option
                                value="<?= $status ?>"
                                <?= $filtros['status'] === $status ? 'selected' : '' ?>
                            >
                                <?= ucfirst($status) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2 d-grid">
                    <button class="btn btn-outline-primary">Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Processo</th>
                        <th>Cliente</th>
                        <th>Tipo</th>
                        <th>Vencimento</th>
                        <th>Situação</th>
                        <th>Status</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$prazos): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                Nenhum prazo encontrado.
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($prazos as $prazo): ?>
                        <?php
                        $dias = (int) (new DateTime())
                            ->diff(new DateTime($prazo['data_vencimento']))
                            ->format('%r%a');

                        $classe = $dias < 0
                            ? 'prazo-vencido'
                            : ($dias <= 7
                                ? 'prazo-urgente'
                                : ($dias <= 30 ? 'prazo-notif' : 'prazo-ok'));

                        $texto = $dias < 0
                            ? 'Vencido há ' . abs($dias) . ' dias'
                            : ($dias === 0 ? 'Vence hoje' : 'Vence em ' . $dias . ' dias');
                        ?>

                        <tr id="prazo-<?= (int) $prazo['id'] ?>">
                            <td>
                                <strong><?= htmlspecialchars($prazo['numero_processo']) ?></strong>
                                <div class="small text-muted">
                                    <?= htmlspecialchars($prazo['titulo']) ?>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($prazo['cliente_nome']) ?></td>
                            <td><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $prazo['tipo']))) ?></td>
                            <td><?= date('d/m/Y', strtotime($prazo['data_vencimento'])) ?></td>
                            <td>
                                <span class="prazo-badge <?= $classe ?>"><?= $texto ?></span>
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?= ucfirst($prazo['status']) ?></span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a
                                        href="<?= URL_BASE ?>/prazos/editar?id=<?= (int) $prazo['id'] ?>"
                                        class="btn btn-outline-primary"
                                    >
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <?php if ($prazo['status'] === 'pendente'): ?>
                                        <form method="post" action="<?= URL_BASE ?>/prazos/concluir">
                                            <input
                                                type="hidden"
                                                name="csrf_token"
                                                value="<?= htmlspecialchars($csrf_token) ?>"
                                            >
                                            <input type="hidden" name="id" value="<?= (int) $prazo['id'] ?>">
                                            <button class="btn btn-outline-success" title="Concluir">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <form
                                        method="post"
                                        action="<?= URL_BASE ?>/prazos/excluir"
                                        onsubmit="return confirm('Excluir este prazo?')"
                                    >
                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?= htmlspecialchars($csrf_token) ?>"
                                        >
                                        <input type="hidden" name="id" value="<?= (int) $prazo['id'] ?>">
                                        <button class="btn btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<?php if (!empty($_GET['prazo_id'])): ?>
    <script>
        document
            .getElementById('prazo-<?= (int) $_GET['prazo_id'] ?>')
            ?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    </script>
<?php endif; ?>
</body>
</html>
