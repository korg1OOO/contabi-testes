<nav class="sidebar">
    <div class="brand"><img src="<?= URL_BASE ?>/assets/images/logo-contabi-branca.png" alt="Logo Contabi" class="brand-logo"></div>
    <div class="nav-section">Principal</div>
    <a href="<?= URL_BASE ?>/dashboard" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/dashboard') ? 'active' : '' ?>"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="<?= URL_BASE ?>/clientes" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/clientes') ? 'active' : '' ?>"><i class="bi bi-people"></i> Clientes</a>
    <div class="nav-section">Processos</div>
    <a href="<?= URL_BASE ?>/marcas" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/marcas') ? 'active' : '' ?>"><i class="bi bi-award"></i> Marcas</a>
    <a href="<?= URL_BASE ?>/patentes" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/patentes') ? 'active' : '' ?>"><i class="bi bi-lightbulb"></i> Patentes</a>
    <div class="nav-section">Ferramentas</div>
    <a href="<?= URL_BASE ?>/prazos" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/prazos') ? 'active' : '' ?>"><i class="bi bi-calendar-event"></i> Agenda</a>
    <a href="<?= URL_BASE ?>/rpi/listar" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/rpi') ? 'active' : '' ?>"><i class="bi bi-file-earmark-text"></i> Despachos RPI</a>
    <a href="<?= URL_BASE ?>/busca/colisoes" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/busca') ? 'active' : '' ?>"><i class="bi bi-search"></i> Colidências</a>
    <a href="<?= URL_BASE ?>/documentos" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/documentos') ? 'active' : '' ?>"><i class="bi bi-folder2-open"></i> Documentos</a>
    <a href="<?= URL_BASE ?>/relatorios" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/relatorios') ? 'active' : '' ?>"><i class="bi bi-file-earmark-bar-graph"></i> Relatórios</a>
    <a href="<?= URL_BASE ?>/notificacoes" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/notificacoes') ? 'active' : '' ?>"><i class="bi bi-bell"></i> Notificações</a>
    <?php if (isset($_SESSION['usuario']) && $_SESSION['usuario']['perfil'] === 'administrador'): ?>
        <div class="nav-section">Administração</div>
        <a href="<?= URL_BASE ?>/usuarios" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/usuarios') ? 'active' : '' ?>"><i class="bi bi-person-gear"></i> Usuários</a>
    <?php endif; ?>
    <div class="mt-auto">
        <a href="<?= URL_BASE ?>/minha-conta/excluir-dados" class="nav-link text-danger"><i class="bi bi-person-x"></i> Excluir meus dados</a>
        <a href="<?= URL_BASE ?>/logout" class="nav-link"><i class="bi bi-box-arrow-left"></i> Sair</a>
    </div>
</nav>
