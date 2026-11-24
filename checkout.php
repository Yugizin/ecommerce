<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $paymentMethod = $_POST['payment_method'] ?? '';

    if (!in_array($paymentMethod, ['pix', 'boleto', 'cartao'], true)) {
        $_SESSION['flash'] = 'Selecione uma forma de pagamento válida.';
        redirect('/ecommerce-schunck/checkout.php');
    }

    $cart = $_SESSION['cart'] ?? [];

    if (!$cart) {
        redirect('/ecommerce-schunck/cart.php');
    }

    $pdo = db();

    try {
        $pdo->beginTransaction();
        $selectedProducts = [];
        $total = 0;

        foreach ($cart as $productId => $quantity) {
            $statement = $pdo->prepare(
                'SELECT id, name, price, stock, active FROM products WHERE id = ? FOR UPDATE'
            );
            $statement->execute([(int) $productId]);
            $product = $statement->fetch();

            if (
                !$product
                || !(int) $product['active']
                || (int) $product['stock'] < (int) $quantity
            ) {
                throw new RuntimeException(
                    'Um item ficou sem estoque suficiente. Revise sua sacola.'
                );
            }

            $selectedProducts[] = [
                'product' => $product,
                'quantity' => (int) $quantity,
            ];
            $total += (float) $product['price'] * (int) $quantity;
        }

        $orderStatement = $pdo->prepare(
            'INSERT INTO orders (user_id, total, payment_method) VALUES (?, ?, ?)'
        );
        $orderStatement->execute([
            current_user()['id'],
            $total,
            $paymentMethod,
        ]);
        $orderId = (int) $pdo->lastInsertId();

        $itemStatement = $pdo->prepare(
            'INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stockStatement = $pdo->prepare(
            'UPDATE products SET stock = stock - ? WHERE id = ?'
        );

        foreach ($selectedProducts as $entry) {
            $product = $entry['product'];
            $quantity = $entry['quantity'];

            $itemStatement->execute([
                $orderId,
                $product['id'],
                $product['name'],
                $quantity,
                $product['price'],
            ]);
            $stockStatement->execute([$quantity, $product['id']]);
        }

        $pdo->commit();
        $_SESSION['cart'] = [];
        $_SESSION['last_order'] = [
            'id' => $orderId,
            'total' => $total,
            'payment' => $paymentMethod,
        ];

        redirect('/ecommerce-schunck/checkout.php?ok=1');
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $_SESSION['flash'] = $exception instanceof RuntimeException
            ? $exception->getMessage()
            : 'Não foi possível concluir o pedido. Tente novamente.';
        redirect('/ecommerce-schunck/checkout.php');
    }
}

$items = [];
$total = 0;

foreach ($_SESSION['cart'] ?? [] as $productId => $quantity) {
    $statement = db()->prepare('SELECT * FROM products WHERE id = ? AND active = 1');
    $statement->execute([(int) $productId]);
    $product = $statement->fetch();

    if ($product) {
        $quantity = (int) $quantity;
        $total += (float) $product['price'] * $quantity;
        $product['quantity'] = $quantity;
        $items[] = $product;
    }
}

$title = 'Finalizar pedido';
require __DIR__ . '/includes/header.php';
?>
<main class="content-wrap" style="max-width: 850px">
    <h1 class="page-title">Finalizar pedido</h1>

    <?php if (isset($_GET['ok']) && isset($_SESSION['last_order'])): ?>
        <?php $order = $_SESSION['last_order']; ?>
        <div class="form-card text-center py-5">
            <div style="font-size: 42px; color: #536b4e">✓</div>
            <h2 class="mt-3">Pedido confirmado!</h2>
            <p class="text-muted">
                Pedido #<?= (int) $order['id'] ?> ·
                <?= money($order['total']) ?> ·
                Pagamento via <?= h(strtoupper($order['payment'])) ?>
            </p>
            <p>Esta é uma simulação acadêmica; nenhum pagamento real foi processado.</p>
            <a class="btn-shop d-inline-block mt-2" href="/ecommerce-schunck/account.php">
                Acompanhar meus pedidos
            </a>
        </div>
    <?php elseif (!$items): ?>
        <div class="form-card">
            Sua sacola está vazia. <a href="/ecommerce-schunck/">Voltar à loja</a>
        </div>
    <?php else: ?>
        <?php if (!empty($_SESSION['flash'])): ?>
            <div class="alert alert-warning"><?= h($_SESSION['flash']) ?></div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-md-7">
                <form class="form-card" method="post">
                    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                    <h2 class="h5 mb-3">Forma de pagamento</h2>

                    <?php foreach (['pix' => 'PIX', 'boleto' => 'Boleto', 'cartao' => 'Cartão'] as $value => $label): ?>
                        <label class="d-flex gap-2 align-items-center border p-3 mb-2">
                            <input type="radio" name="payment_method" value="<?= h($value) ?>" required>
                            <?= h($label) ?>
                        </label>
                    <?php endforeach; ?>

                    <button class="btn-shop w-100 mt-3">Confirmar pedido</button>
                </form>
            </div>

            <div class="col-md-5">
                <div class="form-card">
                    <h2 class="h5">Resumo</h2>

                    <?php foreach ($items as $product): ?>
                        <div class="d-flex justify-content-between my-3">
                            <span>
                                <?= h($product['name']) ?> × <?= (int) $product['quantity'] ?>
                            </span>
                            <span>
                                <?= money((float) $product['price'] * (int) $product['quantity']) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>

                    <hr>
                    <div class="d-flex justify-content-between">
                        <strong>Total</strong>
                        <strong><?= money($total) ?></strong>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
