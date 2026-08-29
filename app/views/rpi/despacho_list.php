<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <link
        rel="icon"
        type="image/png"
        sizes="192x192"
        href="<?= URL_BASE ?>/assets/images/icon-192.png"
    >

    <link
        rel="icon"
        type="image/png"
        sizes="512x512"
        href="<?= URL_BASE ?>/assets/images/icon-512.png"
    >

    <link
        rel="shortcut icon"
        href="<?= URL_BASE ?>/favicon.ico"
    >

    <link
        rel="apple-touch-icon"
        href="<?= URL_BASE ?>/assets/images/apple-touch-icon.png"
    >

    <title>Despachos da RPI • Contabi</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="<?= URL_BASE_CSS ?>/style.css"
    >
</head>

<body>

<?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

<div class="main-content">

    <div class="topbar">
        <div>
            <span class="page-title">
                Despachos da RPI
            </span>

            <div class="text-muted small">
                Despachos associados aos processos da sua carteira
            </div>
        </div>

        <div>
            <a
                href="<?= URL_BASE ?>/rpi/upload"
                class="btn btn-primary"
            >
                <i class="bi bi-cloud-arrow-up me-1"></i>
                Importar RPI
            </a>
        </div>
    </div>

    <div class="card">

        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-journal-text me-2"></i>
                    Despachos importados
                </h5>

                <span class="text-muted small">
                    <?= count($despachos ?? []) ?> registro(s)
                </span>
            </div>
        </div>

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover mb-0">

                    <thead>
                        <tr>
                            <th class="px-4 py-3">
                                Revista
                            </th>

                            <th class="px-4 py-3">
                                Nº Processo
                            </th>

                            <th class="px-4 py-3">
                                Código
                            </th>

                            <th class="px-4 py-3">
                                Descrição
                            </th>

                            <th class="px-4 py-3">
                                Data
                            </th>

                            <th class="px-4 py-3">
                                Status
                            </th>

                            <th class="px-4 py-3 text-end">
                                Ações
                            </th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if (empty($despachos)): ?>

                            <tr>
                                <td
                                    colspan="7"
                                    class="text-center py-5 text-muted"
                                >
                                    <i
                                        class="bi bi-inbox fs-3 d-block mb-2"
                                    ></i>

                                    Nenhum despacho importado ainda.
                                </td>
                            </tr>

                        <?php else: ?>

                            <?php foreach ($despachos as $d): ?>

                                <tr>

                                    <td class="px-4 py-3">
                                        <?= htmlspecialchars(
                                            $d['numero_revista'] ?? ''
                                        ) ?>
                                    </td>

                                    <td class="px-4 py-3 fw-semibold">
                                        <?= htmlspecialchars(
                                            $d['numero_processo'] ?? ''
                                        ) ?>
                                    </td>

                                    <td class="px-4 py-3">

                                        <?php if (!empty($d['codigo_despacho'])): ?>

                                            <span class="badge bg-secondary">
                                                <?= htmlspecialchars(
                                                    $d['codigo_despacho']
                                                ) ?>
                                            </span>

                                        <?php else: ?>

                                            <span class="text-muted">
                                                —
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                    <td class="px-4 py-3">

                                        <?= htmlspecialchars(
                                            mb_strimwidth(
                                                $d['descricao'] ?? '',
                                                0,
                                                70,
                                                '...'
                                            )
                                        ) ?>

                                    </td>

                                    <td class="px-4 py-3">

                                        <?php if (!empty($d['data_publicacao'])): ?>

                                            <?= date(
                                                'd/m/Y',
                                                strtotime(
                                                    $d['data_publicacao']
                                                )
                                            ) ?>

                                        <?php else: ?>

                                            —

                                        <?php endif; ?>

                                    </td>

                                    <td class="px-4 py-3">

                                        <?php if (!empty($d['processado'])): ?>

                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle me-1"></i>
                                                Processado
                                            </span>

                                        <?php else: ?>

                                            <span class="badge bg-warning text-dark">
                                                <i class="bi bi-clock me-1"></i>
                                                Pendente
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                    <td class="px-4 py-3 text-end">

                                        <?php if (empty($d['processado'])): ?>

                                            <form
                                                action="<?= URL_BASE ?>/rpi/marcar-processado"
                                                method="post"
                                                class="d-inline"
                                            >

                                                <input
                                                    type="hidden"
                                                    name="csrf_token"
                                                    value="<?= htmlspecialchars(
                                                        $csrf_token
                                                    ) ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="id"
                                                    value="<?= (int) $d['id'] ?>"
                                                >

                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-outline-success"
                                                >
                                                    <i class="bi bi-check2-circle me-1"></i>
                                                    Processar
                                                </button>

                                            </form>

                                        <?php else: ?>

                                            <span class="text-muted small">
                                                —
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

</body>
</html>