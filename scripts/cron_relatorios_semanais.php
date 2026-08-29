<?php

require_once __DIR__ . '/../app/core/Autoload.php';
require_once __DIR__ . '/../app/config/Config.php';

use app\services\RelatorioSemanalService;

$chaveEsperada =
    'CONTABI_CRON_RELATORIOS_2026';

$chaveRecebida =
    $_GET['chave'] ?? '';

if (
    !hash_equals(
        $chaveEsperada,
        $chaveRecebida
    )
) {
    http_response_code(403);
    exit('Acesso não autorizado.');
}

try {
    $arquivos =
        (new RelatorioSemanalService())
            ->executar();

    header(
        'Content-Type: text/plain; charset=UTF-8'
    );

    echo count($arquivos)
        . " relatório(s) semanal(is) gerado(s).\n";
} catch (Throwable $e) {
    http_response_code(500);

    header(
        'Content-Type: text/plain; charset=UTF-8'
    );

    echo "Não foi possível gerar os relatórios semanais.\n";
}