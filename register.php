<?php
require_once __DIR__ . '/includes/bootstrap.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
        $error = 'Informe seu nome, um e-mail válido e uma senha com pelo menos 8 caracteres.';
    } else {
        try {
            $statement = db()->prepare(
                'INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)'
            );
            $statement->execute([
                $name,
                $email,
                password_hash($password, PASSWORD_DEFAULT),
            ]);

            $_SESSION['user'] = [
                'id' => (int) db()->lastInsertId(),
                'name' => $name,
                'email' => $email,
                'role' => 'customer',
            ];
            session_regenerate_id(true);
            redirect('/ecommerce-schunck/account.php');
        } catch (PDOException $exception) {
            $error = $exception->getCode() === '23000'
                ? 'Este e-mail já possui cadastro.'
                : 'Não foi possível criar sua conta.';
        }
    }
}

$title = 'Criar conta';
require __DIR__ . '/includes/header.php';
?>
<main class="content-wrap" style="max-width: 520px">
    <h1 class="page-title">Crie sua conta.</h1>
    <p class="text-muted">Um espaço para acompanhar suas compras Aurora.</p>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= h($error) ?></div>
    <?php endif; ?>

    <form class="form-card" method="post">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

        <label class="form-label" for="name">Nome</label>
        <input
            class="form-control mb-3"
            id="name"
            name="name"
            required
            maxlength="120"
            autocomplete="name"
        >

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
            minlength="8"
            autocomplete="new-password"
        >

        <button class="btn-shop w-100">Criar conta</button>
    </form>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
