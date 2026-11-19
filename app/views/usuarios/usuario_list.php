<?php
$usuarios = $usuarios ?? [];
$usuariosAtivos = array_values(array_filter(
    $usuarios,
    static fn(array $usuario): bool => (bool) ($usuario['ativo'] ?? false)
));
$usuariosDesativados = array_values(array_filter(
    $usuarios,
    static fn(array $usuario): bool => !(bool) ($usuario['ativo'] ?? false)
));
$totalUsuarios = count($usuarios);

$abasUsuarios = [
    'ativos' => [
        'titulo' => 'Ativos',
        'usuarios' => $usuariosAtivos,
        'acao' => 'Desativar',
        'rota' => 'desativar',
        'icone' => 'bi-person-dash',
        'estilo' => 'danger',
        'confirmacao' => 'Tem certeza que deseja desativar este usuário?',
        'vazio' => 'Nenhum usuário ativo.'
    ],
    'desativados' => [
        'titulo' => 'Desativados',
        'usuarios' => $usuariosDesativados,
        'acao' => 'Reativar',
        'rota' => 'ativar',
        'icone' => 'bi-person-check',
        'estilo' => 'success',
        'confirmacao' => 'Deseja reativar este usuário?',
        'vazio' => 'Nenhum usuário desativado.'
    ]
];

$abaInicial = isset($_GET['desativado']) ? 'desativados' : 'ativos';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" sizes="192x192" href="<?= URL_BASE ?>/assets/images/icon-192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="<?= URL_BASE ?>/assets/images/icon-512.png">
    <link rel="shortcut icon" href="<?= URL_BASE ?>/favicon.ico">
    <link rel="apple-touch-icon" href="<?= URL_BASE ?>/assets/images/apple-touch-icon.png">
    <title>Usuários • Contabi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= URL_BASE_CSS ?>/style.css">
</head>
<body>
<?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

<div class="main-content">
    <div class="topbar">
        <span class="page-title">Usuários</span>
        <div class="d-flex align-items-center gap-3">
            <div class="text-end">
                <div class="text-muted small">Total de usuários cadastrados</div>
                <strong class="fs-5"><?= $totalUsuarios ?></strong>
            </div>
            <a href="<?= URL_BASE ?>/usuarios/cadastrar" class="btn btn-primary btn-sm">
                <i class="bi bi-person-plus"></i> Novo Usuário
            </a>
        </div>
    </div>

    <?php
    if (isset($_GET['desativado'])) {
        $sucesso = 'Usuário desativado. Ele está na aba “Desativados”.';
    } elseif (isset($_GET['ativado'])) {
        $sucesso = 'Usuário reativado. Ele está na aba “Ativos”.';
    } elseif (isset($_GET['cadastro'])) {
        $sucesso = 'Usuário cadastrado com sucesso.';
    } elseif (isset($_GET['atualizado'])) {
        $sucesso = 'Usuário atualizado com sucesso.';
    }
    require __DIR__ . '/../partials/alertas.php';
    ?>

    <div class="card">
        <div class="card-header bg-white border-bottom-0 pt-3 px-4">
            <ul class="nav nav-tabs" id="usuarios-tabs" role="tablist">
                <?php foreach ($abasUsuarios as $chave => $aba): ?>
                    <?php $estaAtiva = $abaInicial === $chave; ?>
                    <li class="nav-item" role="presentation">
                        <button
                            class="nav-link <?= $estaAtiva ? 'active' : '' ?>"
                            id="usuarios-<?= $chave ?>-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#usuarios-<?= $chave ?>"
                            type="button"
                            role="tab"
                            aria-controls="usuarios-<?= $chave ?>"
                            aria-selected="<?= $estaAtiva ? 'true' : 'false' ?>"
                        >
                            <?= $aba['titulo'] ?>
                            <span class="badge rounded-pill <?= $chave === 'ativos' ? 'bg-primary' : 'bg-secondary' ?> ms-1">
                                <?= count($aba['usuarios']) ?>
                            </span>
                        </button>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="card-body p-0">
            <div class="tab-content" id="usuarios-tabs-content">
                <?php foreach ($abasUsuarios as $chave => $aba): ?>
                    <?php $estaAtiva = $abaInicial === $chave; ?>
                    <div
                        class="tab-pane fade <?= $estaAtiva ? 'show active' : '' ?>"
                        id="usuarios-<?= $chave ?>"
                        role="tabpanel"
                        aria-labelledby="usuarios-<?= $chave ?>-tab"
                        tabindex="0"
                    >
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-3">Nome</th>
                                        <th class="px-4 py-3">E-mail</th>
                                        <th class="px-4 py-3">Telefone</th>
                                        <th class="px-4 py-3">Perfil</th>
                                        <th class="px-4 py-3 text-end">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($aba['usuarios'])): ?>
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                <?= $aba['vazio'] ?>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($aba['usuarios'] as $u): ?>
                                            <tr>
                                                <td class="px-4 py-3 align-middle fw-semibold">
                                                    <?= htmlspecialchars($u['nome']) ?>
                                                </td>
                                                <td class="px-4 py-3 align-middle">
                                                    <?= htmlspecialchars($u['email'] ?? '—') ?>
                                                </td>
                                                <td class="px-4 py-3 align-middle">
                                                    <?= htmlspecialchars($u['telefone'] ?? '—') ?>
                                                </td>
                                                <td class="px-4 py-3 align-middle">
                                                    <span class="badge bg-secondary">
                                                        <?= htmlspecialchars(ucfirst($u['perfil'])) ?>
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 align-middle text-end">
                                                    <a
                                                        href="<?= URL_BASE ?>/usuarios/editar?id=<?= (int) $u['id'] ?>"
                                                        class="btn btn-sm btn-outline-secondary"
                                                        title="Editar usuário"
                                                        aria-label="Editar <?= htmlspecialchars($u['nome'], ENT_QUOTES, 'UTF-8') ?>"
                                                    >
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <form
                                                        action="<?= URL_BASE ?>/usuarios/<?= $aba['rota'] ?>"
                                                        method="post"
                                                        class="d-inline"
                                                        onsubmit="return confirm('<?= $aba['confirmacao'] ?>')"
                                                    >
                                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                                        <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                                                        <button
                                                            type="submit"
                                                            class="btn btn-sm btn-outline-<?= $aba['estilo'] ?>"
                                                            title="<?= $aba['acao'] ?> usuário"
                                                            aria-label="<?= $aba['acao'] ?> <?= htmlspecialchars($u['nome'], ENT_QUOTES, 'UTF-8') ?>"
                                                        >
                                                            <i class="bi <?= $aba['icone'] ?>"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
