<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_admin();

$counts = [
    'products' => (int) db()->query(
        'SELECT COUNT(*) FROM products WHERE active = 1'
    )->fetchColumn(),
    'orders' => (int) db()->query('SELECT COUNT(*) FROM orders')->fetchColumn(),
    'customers' => (int) db()->query(
        "SELECT COUNT(*) FROM users WHERE role = 'customer'"
    )->fetchColumn(),
];

$orders = db()->query(
    'SELECT o.*, u.name, u.email
     FROM orders o
     JOIN users u ON u.id = o.user_id
     ORDER BY o.created_at DESC
     LIMIT 20'
)->fetchAll();

$title = 'Administração';
require dirname(__DIR__) . '/includes/header.php';
?>
<main class="content-wrap">
    <span class="eyebrow">AURORA STORE</span>
    <h1 class="page-title mt-2">Painel administrativo</h1>
    <?php require __DIR__ . '/_nav.php'; ?>

    <div class="stat-grid">
        <div class="stat-box">
            <small>Produtos ativos</small>
            <strong><?= $counts['products'] ?></strong>
        </div>
        <div class="stat-box">
            <small>Pedidos recebidos</small>
            <strong><?= $counts['orders'] ?></strong>
        </div>
        <div class="stat-box">
            <small>Clientes</small>
            <strong><?= $counts['customers'] ?></strong>
        </div>
    </div>

    <section class="panel-card">
        <h2 class="h4">Pedidos recentes</h2>
        <div class="table-responsive">
            <table class="table mt-3">
                <thead>
                    <tr>
                        <th>Pedido</th>
                        <th>Cliente</th>
                        <th>Data</th>
                        <th>Pagamento</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?= (int) $order['id'] ?></td>
                            <td>
                                <?= h($order['name']) ?><br>
                                <small><?= h($order['email']) ?></small>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                            <td><?= h(strtoupper($order['payment_method'])) ?></td>
                            <td><?= money($order['total']) ?></td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$orders): ?>
                        <tr>
                            <td colspan="5">Ainda não há pedidos.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
