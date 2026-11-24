<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();

$statement = db()->prepare(
    'SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC'
);
$statement->execute([current_user()['id']]);
$orders = $statement->fetchAll();

$title = 'Minha conta';
require __DIR__ . '/includes/header.php';
?>
<main class="content-wrap">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <span class="eyebrow">SUA CONTA</span>
            <h1 class="page-title mt-2">Olá, <?= h(current_user()['name']) ?>.</h1>
        </div>
        <a class="btn btn-outline-secondary btn-sm" href="/ecommerce-schunck/logout.php">Sair</a>
    </div>

    <h2 class="h4 mt-5 mb-3">Seus pedidos</h2>

    <?php if (!$orders): ?>
        <p class="text-muted">Você ainda não tem pedidos.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Pedido</th>
                        <th>Data</th>
                        <th>Pagamento</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?= (int) $order['id'] ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                            <td><?= h(strtoupper($order['payment_method'])) ?></td>
                            <td><?= money($order['total']) ?></td>
                            <td>Confirmado</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
