<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_admin();

$orders = db()->query(
    'SELECT o.*, u.name, u.email
     FROM orders o
     JOIN users u ON u.id = o.user_id
     ORDER BY o.created_at DESC'
)->fetchAll();
$itemStatement = db()->prepare(
    'SELECT product_name, quantity, unit_price FROM order_items WHERE order_id = ?'
);

$title = 'Pedidos';
require dirname(__DIR__) . '/includes/header.php';
?>
<main class="content-wrap">
    <h1 class="page-title">Pedidos</h1>
    <?php require __DIR__ . '/_nav.php'; ?>

    <?php if (!$orders): ?>
        <div class="panel-card">Ainda não há pedidos registrados.</div>
    <?php endif; ?>

    <?php foreach ($orders as $order): ?>
        <?php
        $itemStatement->execute([$order['id']]);
        $items = $itemStatement->fetchAll();
        ?>
        <section class="panel-card mb-3">
            <div class="d-flex justify-content-between flex-wrap gap-2">
                <div>
                    <strong>Pedido #<?= (int) $order['id'] ?> · <?= h($order['name']) ?></strong>
                    <div>
                        <small>
                            <?= h($order['email']) ?> ·
                            <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                        </small>
                    </div>
                </div>
                <div>
                    <?= h(strtoupper($order['payment_method'])) ?> ·
                    <strong><?= money($order['total']) ?></strong>
                </div>
            </div>

            <ul class="mb-0 mt-3">
                <?php foreach ($items as $item): ?>
                    <li>
                        <?= h($item['product_name']) ?> —
                        <?= (int) $item['quantity'] ?> ×
                        <?= money($item['unit_price']) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endforeach; ?>
</main>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
