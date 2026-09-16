<?php

namespace app\core;

class Controller
{
    public function view(string $view, ?array $data = null)
    {
        if ($data) {
            extract($data);
        }

        $path = __DIR__ . "/../views/{$view}.php";

        if (!file_exists($path)) {
            http_response_code(404);
            echo 'Página não encontrada.';
            return;
        }

        require $path;
    }

    public function redirect(string $url)
    {
        header("Location: {$url}");
        exit;
    }

    public function autenticacaoRequired(): bool
    {
        $this->startSession();

        if (!isset($_SESSION['usuario'])) {
            $this->redirect(URL_BASE . '/login');
        }

        return true;
    }

    public function usuarioLogado(): ?array
    {
        $this->startSession();
        return $_SESSION['usuario'] ?? null;
    }

    public function isAdmin(): bool
    {
        $user = $this->usuarioLogado();
        return $user && ($user['perfil'] ?? '') === 'administrador';
    }

    public function csrfToken(): string
    {
        $this->startSession();

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    public function validarCsrf(): void
    {
        $this->startSession();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && empty($_FILES)) {
            $contentLength = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);

            if ($contentLength > 0) {
                http_response_code(413);
                exit(
                    'O envio não pôde ser processado. '
                    . 'O arquivo enviado pode ser maior que o limite permitido pelo servidor. '
                    . 'Tente um arquivo menor ou verifique os limites upload_max_filesize e post_max_size do PHP.'
                );
            }
        }

        $tokenRecebido = $_POST['csrf_token'] ?? '';
        $tokenSessao = $_SESSION['csrf_token'] ?? '';

        if (
            $tokenRecebido === ''
            || $tokenSessao === ''
            || !hash_equals($tokenSessao, $tokenRecebido)
        ) {
            http_response_code(419);
            exit(
                'A página ficou desatualizada ou sua sessão expirou. '
                . 'Atualize a página e tente novamente.'
            );
        }
    }

    protected function negarAcesso(): void
    {
        http_response_code(403);
        exit('Você não possui permissão para acessar este registro.');
    }

    private function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}
