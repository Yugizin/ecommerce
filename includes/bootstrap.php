<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']),
    ]);

    session_start();
}

require_once dirname(__DIR__) . '/config/database.php';

function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function money(float|string $value): string
{
    return 'R$ ' . number_format((float) $value, 2, ',', '.');
}

function csrf_token(): string
{
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}

function verify_csrf(): void
{
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(419);
        exit('Sessão expirada. Volte e tente novamente.');
    }
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_login(): void
{
    if (!current_user()) {
        header('Location: /ecommerce-schunck/login.php');
        exit;
    }
}

function require_admin(): void
{
    require_login();

    if (current_user()['role'] !== 'admin') {
        http_response_code(403);
        exit('Acesso restrito à administração.');
    }
}

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function cart_count(): int
{
    return array_sum($_SESSION['cart'] ?? []);
}
