<?php

$alertaListar = static function ($fonte): array {
    if (is_array($fonte)) {
        return array_values(array_filter(array_map('strval', $fonte), static fn ($m) => trim($m) !== ''));
    }

    return is_string($fonte) && trim($fonte) !== '' ? [$fonte] : [];
};

$alertaErros = array_values(array_unique(array_merge(
    $alertaListar($erros ?? null),
    $alertaListar($erro ?? null)
)));
$alertaAvisos = $alertaListar($aviso ?? null);
$alertaSucessos = $alertaListar($sucesso ?? null);

$alertaRender = static function (array $mensagens, string $classe, string $icone, string $titulo): void {
    if (!$mensagens) {
        return;
    }
    ?>
    <div class="alert <?= $classe ?> alert-contabi d-flex align-items-start gap-2" role="<?= $classe === 'alert-danger' ? 'alert' : 'status' ?>">
        <i class="bi <?= $icone ?> flex-shrink-0"></i>
        <div>
            <?php if (count($mensagens) === 1): ?>
                <?= htmlspecialchars($mensagens[0], ENT_QUOTES, 'UTF-8') ?>
            <?php else: ?>
                <strong><?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?></strong>
                <ul class="mb-0 mt-1 ps-3">
                    <?php foreach ($mensagens as $mensagem): ?>
                        <li><?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
    <?php
};

$alertaRender($alertaErros, 'alert-danger', 'bi-exclamation-triangle-fill', 'Não foi possível continuar. Corrija os itens abaixo:');
$alertaRender($alertaAvisos, 'alert-warning', 'bi-info-circle-fill', 'Atenção:');
$alertaRender($alertaSucessos, 'alert-success', 'bi-check-circle-fill', 'Tudo certo:');

unset($alertaListar, $alertaErros, $alertaAvisos, $alertaSucessos, $alertaRender);
