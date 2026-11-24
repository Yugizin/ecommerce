<?php
require_once __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $productId = (int) ($_POST['product_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($action === 'remove') {
        unset($_SESSION['cart'][$productId]);
    } elseif ($action === 'update') {
        $quantity = max(0, (int) ($_POST['quantity'] ?? 0));
        $statement = db()->prepare('SELECT stock FROM products WHERE id = ? AND active = 1');
        $statement->execute([$productId]);
        $stock = $statement->fetchColumn();

        if ($quantity === 0 || $stock === false) {
            unset($_SESSION['cart'][$productId]);
        } else {
            $_SESSION['cart'][$productId] = min($quantity, (int) $stock);
        }
    } elseif ($action === 'add') {
        $quantity = max(1, (int) ($_POST['quantity'] ?? 1));
        $statement = db()->prepare('SELECT stock FROM products WHERE id = ? AND active = 1');
        $statement->execute([$productId]);
        $stock = $statement->fetchColumn();

        if ($stock !== false && (int) $stock > 0) {
            $_SESSION['cart'][$productId] = min(
                (int) $stock,
                ($_SESSION['cart'][$productId] ?? 0) + $quantity
            );
        }
    }

    redirect('/ecommerce-schunck/cart.php');
}

$items = [];
$total = 0;
$productIds = array_keys($_SESSION['cart'] ?? []);

if ($productIds) {
    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
    $statement = db()->prepare(
        "SELECT * FROM products WHERE active = 1 AND id IN ($placeholders)"
    );
    $statement->execute($productIds);

    foreach ($statement->fetchAll() as $product) {
        $quantity = min(
            (int) ($_SESSION['cart'][$product['id']] ?? 1),
            (int) $product['stock']
        );
        $_SESSION['cart'][$product['id']] = $quantity;
        $product['quantity'] = $quantity;
        $product['subtotal'] = $quantity * (float) $product['price'];
        $total += $product['subtotal'];
        $items[] = $product;
    }
}

$title = 'Sua sacola';
require __DIR__ . '/includes/header.php';
?>
<main class="content-wrap">
    <h1 class="page-title">Sua sacola</h1>

    <?php if (!$items): ?>
        <div class="form-card">
            <p>Sua sacola está vazia por enquanto.</p>
            <a class="btn-shop d-inline-block" href="/ecommerce-schunck/">Explorar produtos</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Preço</th>
                                <th>Quantidade</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $product): ?>
                                <tr>
                                    <td>
                                        <img
                                            class="cart-thumb me-3"
                                            src="<?= h($product['image_url']) ?>"
                                            alt=""
                                        >
                                        <strong><?= h($product['name']) ?></strong>
                                    </td>
                                    <td><?= money($product['price']) ?></td>
                                    <td>
                                        <form
                                            action="/ecommerce-schunck/cart.php"
                                            method="post"
                                            class="d-flex gap-2"
                                        >
                                            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                                            <input type="hidden" name="action" value="update">
                                            <input
                                                type="hidden"
                                                name="product_id"
                                                value="<?= (int) $product['id'] ?>"
                                            >
                                            <input
                                                class="form-control"
                                                style="width: 75px"
                                                type="number"
                                                name="quantity"
                                                min="0"
                                                max="<?= (int) $product['stock'] ?>"
                                                value="<?= (int) $product['quantity'] ?>"
                                            >
                                            <button class="btn btn-outline-secondary btn-sm">Atualizar</button>
                                        </form>
                                    </td>
                                    <td>
                                        <form action="/ecommerce-schunck/cart.php" method="post">
                                            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                                            <input type="hidden" name="action" value="remove">
                                            <input
                                                type="hidden"
                                                name="product_id"
                                                value="<?= (int) $product['id'] ?>"
                                            >
                                            <button class="btn btn-link text-danger p-0">Remover</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="form-card">
                    <h2 class="h5">Resumo do pedido</h2>
                    <div class="d-flex justify-content-between my-3">
                        <span>Subtotal</span>
                        <strong><?= money($total) ?></strong>
                    </div>
                    <small class="text-muted">Frete calculado na próxima etapa.</small>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Total</strong>
                        <strong><?= money($total) ?></strong>
                    </div>
                    <a href="/ecommerce-schunck/checkout.php" class="btn-shop d-block text-center">
                        Continuar para pagamento
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
