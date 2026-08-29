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

    <title>Editar prazo • Contabi</title>

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
                Editar prazo
            </span>

            <div class="text-muted small">
                <?= htmlspecialchars($prazo['numero_processo']) ?>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-4">

            <form
                method="post"
                action="<?= URL_BASE ?>/prazos/atualizar"
                class="row g-3"
            >
                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars($csrf_token) ?>"
                >

                <input
                    type="hidden"
                    name="id"
                    value="<?= (int) $prazo['id'] ?>"
                >

                <div class="col-12">
                    <label class="form-label">
                        Processo
                    </label>

                    <select
                        name="ativo"
                        class="form-select"
                        required
                    >
                        <?php foreach ($ativos as $ativo): ?>

                            <?php
                            $selecionado =
                                (
                                    $ativo['tipo'] === 'marca'
                                    && (int) $prazo['marca_id']
                                        === (int) $ativo['id']
                                )
                                ||
                                (
                                    $ativo['tipo'] === 'patente'
                                    && (int) $prazo['patente_id']
                                        === (int) $ativo['id']
                                );
                            ?>

                            <option
                                value="<?= htmlspecialchars($ativo['tipo']) ?>:<?= (int) $ativo['id'] ?>"
                                <?= $selecionado ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars(
                                    ucfirst($ativo['tipo'])
                                    . ' • '
                                    . $ativo['numero_processo']
                                    . ' • '
                                    . $ativo['titular']
                                    . ' • '
                                    . $ativo['cliente_nome']
                                ) ?>
                            </option>

                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">
                        Tipo de prazo
                    </label>

                    <select
                        name="tipo"
                        class="form-select"
                        required
                    >
                        <?php foreach ([
                            'anuidade' => 'Anuidade',
                            'renovacao_marca' => 'Renovação de marca',
                            'oposicao' => 'Oposição',
                            'prorrogacao' => 'Prorrogação',
                            'manifestacao' => 'Manifestação',
                            'outro' => 'Outro'
                        ] as $valor => $label): ?>

                            <option
                                value="<?= htmlspecialchars($valor) ?>"
                                <?= $prazo['tipo'] === $valor ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($label) ?>
                            </option>

                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">
                        Vencimento
                    </label>

                    <input
                        type="text"
                        name="data_vencimento"
                        value="<?=
                            !empty($prazo['data_vencimento'])
                                ? htmlspecialchars(
                                    date(
                                        'd/m/Y',
                                        strtotime(
                                            $prazo['data_vencimento']
                                        )
                                    )
                                )
                                : ''
                        ?>"
                        class="form-control"
                        placeholder="dd/mm/aaaa"
                        maxlength="10"
                        inputmode="numeric"
                        pattern="\d{2}/\d{2}/\d{4}"
                        required
                    >
                </div>

                <div class="col-md-3">
                    <label class="form-label">
                        Status
                    </label>

                    <select
                        name="status"
                        class="form-select"
                    >
                        <?php foreach ([
                            'pendente' => 'Pendente',
                            'cumprido' => 'Cumprido',
                            'vencido' => 'Vencido',
                            'cancelado' => 'Cancelado'
                        ] as $valor => $label): ?>

                            <option
                                value="<?= htmlspecialchars($valor) ?>"
                                <?= $prazo['status'] === $valor ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($label) ?>
                            </option>

                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label">
                        Observações
                    </label>

                    <textarea
                        name="observacoes"
                        class="form-control"
                        rows="4"
                    ><?= htmlspecialchars($prazo['observacoes'] ?? '') ?></textarea>
                </div>

                <div class="col-12 d-flex justify-content-end gap-2">
                    <a
                        href="<?= URL_BASE ?>/prazos"
                        class="btn btn-light"
                    >
                        Cancelar
                    </a>

                    <button class="btn btn-primary">
                        Salvar alterações
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

</body>
</html>