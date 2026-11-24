<?php
require_once __DIR__ . '/includes/bootstrap.php';

$statement = db()->prepare(
    'SELECT p.*, c.name AS category_name
     FROM products p
     JOIN categories c ON c.id = p.category_id
     WHERE p.id = ? AND p.active = 1'
);
$statement->execute([(int) ($_GET['id'] ?? 0)]);
$product = $statement->fetch();

if (!$product) {
    http_response_code(404);
    $title = 'Produto não encontrado';
    require __DIR__ . '/includes/header.php';
    ?>
    <main class="content-wrap">
        <h1 class="page-title">Produto não encontrado</h1>
        <a href="/ecommerce-schunck/">Voltar à loja</a>
    </main>
    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
}

$title = $product['name'];
require __DIR__ . '/includes/header.php';
?>
<main class="content-wrap">
    <div class="product-detail">
        <img src="<?= h($product['image_url']) ?>" alt="<?= h($product['name']) ?>">

        <div>
            <span class="eyebrow"><?= h($product['category_name']) ?></span>
            <h1 class="page-title mt-3"><?= h($product['name']) ?></h1>
            <p><?= h($product['description']) ?></p>
            <h2 class="h3 mb-4"><?= money($product['price']) ?></h2>

            <?php if ((int) $product['stock'] > 0): ?>
                <p>Em estoque · <?= (int) $product['stock'] ?> unidades disponíveis</p>
                <form action="/ecommerce-schunck/cart.php" method="post">
                    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>">

                    <label class="form-label" for="quantity">Quantidade</label>
                    <input
                        class="form-control mb-3"
                        id="quantity"
                        type="number"
                        name="quantity"
                        min="1"
                        max="<?= (int) $product['stock'] ?>"
                        value="1"
                        style="max-width: 120px"
                    >
                    <button class="btn-shop">Adicionar à sacola ↗</button>
                </form>
            <?php else: ?>
                <p>Produto indisponível</p>
            <?php endif; ?>
        </div>
    </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
