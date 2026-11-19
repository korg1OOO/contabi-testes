<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="icon" type="image/png" sizes="192x192" href="<?= URL_BASE ?>/assets/images/icon-192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="<?= URL_BASE ?>/assets/images/icon-512.png">
    <link rel="shortcut icon" href="<?= URL_BASE ?>/favicon.ico">
    <link rel="apple-touch-icon" href="<?= URL_BASE ?>/assets/images/apple-touch-icon.png">

    <title>Novo prazo • Contabi</title>

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
            <span class="page-title">Cadastrar prazo</span>
            <div class="text-muted small">
                Adicione um vencimento à agenda
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-4">

            <form
                method="post"
                action="<?= URL_BASE ?>/prazos/salvar"
                class="row g-3"
            >

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= htmlspecialchars($csrf_token) ?>"
                >

                <div class="col-12">
                    <?php require __DIR__ . '/../partials/alertas.php'; ?>
                </div>

                <div class="col-12">
                    <label class="form-label">
                        Processo
                    </label>

                    <select
                        name="ativo"
                        id="prazo-ativo"
                        class="form-select"
                        required
                    >
                        <option value="">
                            Selecione
                        </option>

                        <?php foreach ($ativos as $ativo): ?>
                            <?php $valorAtivo = $ativo['tipo'] . ':' . (int) $ativo['id']; ?>
                            <option
                                value="<?= htmlspecialchars($valorAtivo) ?>"
                                data-tipo-ativo="<?= htmlspecialchars($ativo['tipo']) ?>"
                                <?= ($old['ativo'] ?? '') === $valorAtivo ? 'selected' : '' ?>
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
                        id="prazo-tipo"
                        class="form-select <?= isset($erros['tipo']) ? 'is-invalid' : '' ?>"
                        required
                    >
                        <option value="">Selecione primeiro um processo</option>

                        <?php foreach ([
                            'anuidade' => ['Anuidade', 'marca patente'],
                            'renovacao_marca' => ['Renovação de marca', 'marca'],
                            'oposicao' => ['Oposição', 'marca'],
                            'prorrogacao' => ['Prorrogação', 'marca patente'],
                            'manifestacao' => ['Manifestação', 'marca patente'],
                            'vencimento' => ['Vencimento', 'marca patente'],
                            'outro' => ['Outro', 'marca patente']
                        ] as $valor => [$label, $tiposAtivo]): ?>

                            <option
                                value="<?= $valor ?>"
                                data-tipos-ativo="<?= htmlspecialchars($tiposAtivo) ?>"
                                <?= ($old['tipo'] ?? '') === $valor ? 'selected' : '' ?>
                            >
                                <?= $label ?>
                            </option>

                        <?php endforeach; ?>

                    </select>
                    <div class="form-text text-danger" data-tipo-prazo-feedback hidden>
                        Renovação de marca e oposição só podem ser vinculadas a marcas.
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="form-label">
                        Vencimento
                    </label>

                    <input
                        type="text"
                        name="data_vencimento"
                        value="<?= htmlspecialchars($old['data_vencimento'] ?? '') ?>"
                        class="form-control <?= isset($erros['data_vencimento']) ? 'is-invalid' : '' ?>"
                        placeholder="dd/mm/aaaa"
                        maxlength="10"
                        inputmode="numeric"
                        pattern="\d{2}/\d{2}/\d{4}"
                        title="Informe a data no formato dd/mm/aaaa"
                        autocomplete="off"
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
                        <option value="pendente">
                            Pendente
                        </option>

                        <option value="cumprido">
                            Cumprido
                        </option>

                        <option value="cancelado">
                            Cancelado
                        </option>
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
                    ><?= htmlspecialchars($old['observacoes'] ?? '') ?></textarea>
                </div>

                <div class="col-12 d-flex justify-content-end gap-2">

                    <a
                        href="<?= URL_BASE ?>/prazos"
                        class="btn btn-light"
                    >
                        Cancelar
                    </a>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Cadastrar prazo
                    </button>

                </div>

            </form>

        </div>
    </div>

</div>

<script src="<?= URL_BASE ?>/assets/js/mascaras.js"></script>

</body>
</html>
