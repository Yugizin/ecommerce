<?php
require_once __DIR__ . '/bootstrap.php';

$title = $title ?? 'Aurora';
?>
<!doctype html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Aurora — escolhas que acompanham o seu ritmo.">
        <title><?= h($title) ?> · Aurora</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link
            href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap"
            rel="stylesheet"
        >
        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
            rel="stylesheet"
        >
        <link href="/ecommerce-schunck/assets/css/style.css" rel="stylesheet">
    </head>
    <body>
        <div class="announcement">
            Frete grátis a partir de R$ 299 · Parcele em até 6x
        </div>

        <nav class="navbar navbar-expand-lg shop-nav">
            <div class="container">
                <a class="navbar-brand" href="/ecommerce-schunck/">
                    <span class="brand-mark">a</span>
                    aurora<span class="brand-dot">.</span>
                </a>

                <button
                    class="navbar-toggler"
                    data-bs-toggle="collapse"
                    data-bs-target="#nav"
                    aria-controls="nav"
                    aria-expanded="false"
                    aria-label="Abrir menu"
                >
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="nav">
                    <form class="search-form mx-lg-auto" action="/ecommerce-schunck/" method="get">
                        <span aria-hidden="true">⌕</span>
                        <input
                            name="q"
                            value="<?= h($_GET['q'] ?? '') ?>"
                            placeholder="O que você procura?"
                            aria-label="Buscar produtos"
                        >
                        <button type="submit">Buscar</button>
                    </form>

                    <div class="nav-actions">
                        <a href="/ecommerce-schunck/" class="nav-link">Loja</a>

                        <?php if (current_user()): ?>
                            <a href="/ecommerce-schunck/account.php" class="nav-link">
                                Olá, <?= h(explode(' ', current_user()['name'])[0]) ?>
                            </a>

                            <?php if (current_user()['role'] === 'admin'): ?>
                                <a href="/ecommerce-schunck/admin/" class="nav-link">Admin</a>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="/ecommerce-schunck/login.php" class="nav-link">Entrar</a>
                        <?php endif; ?>

                        <a href="/ecommerce-schunck/cart.php" class="cart-link">
                            Sacola <span><?= cart_count() ?></span>
                        </a>
                    </div>
                </div>
            </div>
        </nav>
