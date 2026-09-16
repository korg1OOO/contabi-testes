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

    <title>Importar RPI • Contabi</title>

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

    <style>
        .rpi-processing-overlay {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: rgba(15, 23, 42, 0.58);
            backdrop-filter: blur(2px);
        }

        .rpi-processing-overlay.show {
            display: flex;
        }

        .rpi-processing-card {
            width: min(460px, 100%);
            background: #fff;
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.22);
            text-align: center;
        }

        .rpi-processing-spinner {
            width: 3rem;
            height: 3rem;
        }
    </style>
</head>

<body>

<?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

<div class="main-content">

    <div class="topbar">
        <div>
            <span class="page-title">
                Importação da RPI
            </span>

            <div class="text-muted small">
                Importe manualmente os arquivos oficiais do INPI
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-xl-8 col-lg-10">

            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-cloud-arrow-up me-2"></i>
                        Importação manual da RPI
                    </h5>
                </div>

                <div class="card-body p-4">

                    <?php if (!empty($sucesso)): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle me-2"></i>
                            <?= htmlspecialchars($sucesso) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($erro)): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <?= htmlspecialchars($erro) ?>
                        </div>
                    <?php endif; ?>

                    <div class="alert alert-info">
                        <div class="d-flex gap-2">
                            <i class="bi bi-info-circle-fill"></i>

                            <div>
                                <strong>Arquivos da RPI</strong>

                                <div class="mt-1">
                                    Para marcas, envie o XML oficial para alimentar
                                    a base utilizada na busca de colidências.
                                </div>

                                <div class="mt-1">
                                    PDFs textuais também podem ser utilizados para
                                    localizar despachos relacionados aos processos
                                    cadastrados. (evite enviar PDFs compostos por imagens)
                                </div>

                                <div class="mt-1">
                                    Para patentes, são aceitos ZIP, XML, TXT ou PDF textual.
                                </div>
                            </div>
                        </div>
                    </div>

                    <form
                        id="form-rpi-importacao"
                        method="POST"
                        action="<?= URL_BASE ?>/rpi/importar"
                        enctype="multipart/form-data"
                    >
                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?= htmlspecialchars($this->csrfToken()) ?>"
                        >

                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Tipo da revista
                                </label>

                                <select
                                    name="tipo_revista"
                                    class="form-select"
                                    required
                                >
                                    <option value="">
                                        Selecione
                                    </option>

                                    <option
                                        value="marcas"
                                        <?= ($_POST['tipo_revista'] ?? '') === 'marcas'
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        Marcas
                                    </option>

                                    <option
                                        value="patentes"
                                        <?= ($_POST['tipo_revista'] ?? '') === 'patentes'
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        Patentes
                                    </option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Arquivo oficial
                                </label>

                                <input
                                    id="arquivo-rpi"
                                    type="file"
                                    name="arquivo_rpi"
                                    class="form-control"
                                    accept=".zip,.xml,.txt,.pdf,application/zip,application/xml,text/xml,text/plain,application/pdf"
                                    required
                                >

                                <div class="form-text">
                                    Formatos aceitos: ZIP, XML, TXT e PDF.
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Número da revista
                                </label>

                                <input
                                    type="text"
                                    name="numero_revista"
                                    class="form-control"
                                    placeholder="Ex.: 2899"
                                    value="<?= htmlspecialchars(
                                        $_POST['numero_revista'] ?? ''
                                    ) ?>"
                                >

                                <div class="form-text">
                                    Opcional quando o arquivo já informa
                                    o número da revista.
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Data de publicação
                                </label>

                                <input
                                    type="text"
                                    id="data_publicacao"
                                    name="data_publicacao"
                                    class="form-control"
                                    placeholder="dd/mm/aaaa"
                                    maxlength="10"
                                    inputmode="numeric"
                                    value="<?= htmlspecialchars(
                                        $_POST['data_publicacao'] ?? ''
                                    ) ?>"
                                >

                                <div class="form-text">
                                    Opcional quando o arquivo já informa
                                    a data.
                                </div>
                            </div>

                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a
                                href="<?= URL_BASE ?>/rpi/listar"
                                class="btn btn-light"
                            >
                                <i class="bi bi-list-ul me-1"></i>
                                Ver despachos
                            </a>

                            <button
                                id="btn-importar-rpi"
                                type="submit"
                                class="btn btn-primary"
                            >
                                <i class="bi bi-cloud-arrow-up me-1"></i>
                                Importar e processar
                            </button>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>

</div>

<div
    id="rpi-processing-overlay"
    class="rpi-processing-overlay"
    aria-hidden="true"
>
    <div class="rpi-processing-card" role="status" aria-live="polite">
        <div
            class="spinner-border text-primary rpi-processing-spinner"
            aria-hidden="true"
        ></div>

        <h5 class="mt-4 mb-2">
            Importando RPI...
        </h5>

        <p class="mb-2">
            Enviando e processando o arquivo.
        </p>

        <p class="text-muted small mb-0">
            Dependendo do tamanho da revista, esse processo pode levar alguns minutos.
            Não feche nem atualize esta página.
        </p>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const campoData = document.getElementById('data_publicacao');
    const formulario = document.getElementById('form-rpi-importacao');
    const botaoImportar = document.getElementById('btn-importar-rpi');
    const overlay = document.getElementById('rpi-processing-overlay');

    if (campoData) {
        campoData.addEventListener('input', function () {
            let valor = this.value.replace(/\D/g, '');

            if (valor.length > 8) {
                valor = valor.substring(0, 8);
            }

            if (valor.length > 4) {
                valor =
                    valor.substring(0, 2)
                    + '/'
                    + valor.substring(2, 4)
                    + '/'
                    + valor.substring(4);
            } else if (valor.length > 2) {
                valor =
                    valor.substring(0, 2)
                    + '/'
                    + valor.substring(2);
            }

            this.value = valor;
        });
    }

    if (formulario) {
        formulario.addEventListener('submit', function () {
            if (botaoImportar) {
                botaoImportar.disabled = true;
                botaoImportar.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>'
                    + 'Importando...';
            }

            if (overlay) {
                overlay.classList.add('show');
                overlay.setAttribute('aria-hidden', 'false');
            }
        });
    }
});
</script>

</body>
</html>
