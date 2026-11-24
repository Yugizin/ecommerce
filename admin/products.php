<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_admin();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $action = $_POST['action'] ?? '';
    $productId = (int) ($_POST['id'] ?? 0);

    if ($action === 'delete') {
        $statement = db()->prepare('UPDATE products SET active = 0 WHERE id = ?');
        $statement->execute([$productId]);
        redirect('/ecommerce-schunck/admin/products.php?done=1');
    }

    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float) ($_POST['price'] ?? 0);
    $stock = max(0, (int) ($_POST['stock'] ?? 0));
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $imageUrl = trim($_POST['image_url'] ?? '');
    $badge = trim($_POST['badge'] ?? '');

    if (
        $name === ''
        || $slug === ''
        || $description === ''
        || $price <= 0
        || $categoryId < 1
        || !filter_var($imageUrl, FILTER_VALIDATE_URL)
    ) {
        $error = 'Preencha os campos com valores válidos e informe uma URL de imagem.';
    } else {
        try {
            if ($action === 'save' && $productId) {
                $statement = db()->prepare(
                    'UPDATE products
                     SET category_id = ?, name = ?, slug = ?, description = ?, price = ?,
                         stock = ?, image_url = ?, badge = ?, active = 1
                     WHERE id = ?'
                );
                $statement->execute([
                    $categoryId,
                    $name,
                    $slug,
                    $description,
                    $price,
                    $stock,
                    $imageUrl,
                    $badge ?: null,
                    $productId,
                ]);
            } else {
                $statement = db()->prepare(
                    'INSERT INTO products
                        (category_id, name, slug, description, price, stock, image_url, badge)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
                );
                $statement->execute([
                    $categoryId,
                    $name,
                    $slug,
                    $description,
                    $price,
                    $stock,
                    $imageUrl,
                    $badge ?: null,
                ]);
            }

            redirect('/ecommerce-schunck/admin/products.php?done=1');
        } catch (PDOException $exception) {
            $error = 'Não foi possível salvar. Verifique se o slug já está em uso.';
        }
    }
}

$editProduct = null;

if (isset($_GET['edit'])) {
    $statement = db()->prepare('SELECT * FROM products WHERE id = ?');
    $statement->execute([(int) $_GET['edit']]);
    $editProduct = $statement->fetch();
}

$categories = db()->query('SELECT * FROM categories ORDER BY name')->fetchAll();
$products = db()->query(
    'SELECT p.*, c.name AS category_name
     FROM products p
     JOIN categories c ON c.id = p.category_id
     ORDER BY p.active DESC, p.id'
)->fetchAll();

$title = 'Produtos';
require dirname(__DIR__) . '/includes/header.php';
?>
<main class="content-wrap">
    <h1 class="page-title">Produtos</h1>
    <?php require __DIR__ . '/_nav.php'; ?>

    <?php if (isset($_GET['done'])): ?>
        <div class="alert alert-success">Alteração salva.</div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= h($error) ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-5">
            <form class="form-card" method="post">
                <h2 class="h5"><?= $editProduct ? 'Editar produto' : 'Novo produto' ?></h2>
                <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" value="<?= (int) ($editProduct['id'] ?? 0) ?>">

                <label class="form-label mt-3" for="name">Nome</label>
                <input
                    class="form-control mb-3"
                    id="name"
                    name="name"
                    value="<?= h($editProduct['name'] ?? '') ?>"
                    required
                >

                <label class="form-label" for="slug">Slug único (ex.: fone-novo)</label>
                <input
                    class="form-control mb-3"
                    id="slug"
                    name="slug"
                    value="<?= h($editProduct['slug'] ?? '') ?>"
                    required
                >

                <label class="form-label" for="category">Categoria</label>
                <select class="form-select mb-3" id="category" name="category_id" required>
                    <?php foreach ($categories as $category): ?>
                        <option
                            value="<?= (int) $category['id'] ?>"
                            <?= (int) ($editProduct['category_id'] ?? 0) === (int) $category['id'] ? 'selected' : '' ?>
                        >
                            <?= h($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label class="form-label" for="description">Descrição</label>
                <textarea
                    class="form-control mb-3"
                    id="description"
                    name="description"
                    rows="3"
                    required
                ><?= h($editProduct['description'] ?? '') ?></textarea>

                <div class="row">
                    <div class="col">
                        <label class="form-label" for="price">Preço (R$)</label>
                        <input
                            class="form-control mb-3"
                            id="price"
                            type="number"
                            step="0.01"
                            min="0.01"
                            name="price"
                            value="<?= h((string) ($editProduct['price'] ?? '')) ?>"
                            required
                        >
                    </div>
                    <div class="col">
                        <label class="form-label" for="stock">Estoque</label>
                        <input
                            class="form-control mb-3"
                            id="stock"
                            type="number"
                            min="0"
                            name="stock"
                            value="<?= h((string) ($editProduct['stock'] ?? 0)) ?>"
                            required
                        >
                    </div>
                </div>

                <label class="form-label" for="image_url">URL da imagem</label>
                <input
                    class="form-control mb-3"
                    id="image_url"
                    type="url"
                    name="image_url"
                    value="<?= h($editProduct['image_url'] ?? '') ?>"
                    required
                >

                <label class="form-label" for="badge">Selo (opcional)</label>
                <input
                    class="form-control mb-3"
                    id="badge"
                    name="badge"
                    value="<?= h($editProduct['badge'] ?? '') ?>"
                >

                <button class="btn-shop">Salvar produto</button>
                <?php if ($editProduct): ?>
                    <a class="btn btn-outline-secondary" href="/ecommerce-schunck/admin/products.php">
                        Cancelar
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <div class="col-lg-7">
            <div class="panel-card">
                <h2 class="h5">Catálogo</h2>
                <div class="table-responsive">
                    <table class="table mt-3">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Preço</th>
                                <th>Estoque</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>
                                        <?= h($product['name']) ?><br>
                                        <small><?= h($product['category_name']) ?></small>
                                    </td>
                                    <td><?= money($product['price']) ?></td>
                                    <td><?= (int) $product['stock'] ?></td>
                                    <td><?= $product['active'] ? 'Ativo' : 'Inativo' ?></td>
                                    <td>
                                        <a href="?edit=<?= (int) $product['id'] ?>">Editar</a>
                                        <?php if ($product['active']): ?>
                                            <form
                                                method="post"
                                                data-confirm="Desativar este produto?"
                                                class="d-inline"
                                            >
                                                <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <input
                                                    type="hidden"
                                                    name="id"
                                                    value="<?= (int) $product['id'] ?>"
                                                >
                                                <button class="btn btn-link text-danger btn-sm">
                                                    Desativar
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
