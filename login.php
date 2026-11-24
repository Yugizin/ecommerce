<?php
require_once __DIR__ . '/includes/bootstrap.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $statement = db()->prepare(
        'SELECT id, name, email, password_hash, role FROM users WHERE email = ?'
    );
    $statement->execute([$email]);
    $user = $statement->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = $user;
        redirect('/ecommerce-schunck/account.php');
    }

    $error = 'E-mail ou senha inválidos.';
}

$title = 'Entrar';
require __DIR__ . '/includes/header.php';
?>
<main class="content-wrap" style="max-width: 520px">
    <h1 class="page-title">Boas-vindas de volta.</h1>
    <p class="text-muted">Entre para acompanhar seus pedidos e continuar suas escolhas.</p>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= h($error) ?></div>
    <?php endif; ?>

    <form class="form-card" method="post">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

        <label class="form-label" for="email">E-mail</label>
        <input
            class="form-control mb-3"
            id="email"
            type="email"
            name="email"
            required
            autocomplete="email"
        >

        <label class="form-label" for="password">Senha</label>
        <input
            class="form-control mb-4"
            id="password"
            type="password"
            name="password"
            required
            autocomplete="current-password"
        >

        <button class="btn-shop w-100">Entrar</button>
        <p class="mt-3 mb-0 text-center">
            Ainda não tem conta?
            <a href="/ecommerce-schunck/register.php"><u>Cadastre-se</u></a>
        </p>
    </form>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
