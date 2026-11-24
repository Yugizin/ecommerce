<?php
require_once __DIR__ . '/includes/bootstrap.php';

$title = 'Descubra seu próximo favorito';
$query = trim($_GET['q'] ?? '');
$categorySlug = trim($_GET['categoria'] ?? '');

$categories = db()->query('SELECT * FROM categories ORDER BY name')->fetchAll();
$sql = 'SELECT p.*, c.name AS category_name
        FROM products p
        JOIN categories c ON c.id = p.category_id
        WHERE p.active = 1';
$parameters = [];

if ($query !== '') {
    $sql .= ' AND (p.name LIKE ? OR p.description LIKE ?)';
    $parameters[] = "%{$query}%";
    $parameters[] = "%{$query}%";
}

if ($categorySlug !== '') {
    $sql .= ' AND c.slug = ?';
    $parameters[] = $categorySlug;
}

$sql .= ' ORDER BY p.id';
$statement = db()->prepare($sql);
$statement->execute($parameters);
$products = $statement->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<main>
    <section class="hero">
        <div class="container hero-grid">
            <div class="hero-copy">
                <span class="eyebrow"><i></i> COLEÇÃO ESSENCIAL · 2026</span>
                <h1>Peças que fazem<br>parte do <em>seu ritmo.</em></h1>
                <p>
                    Design inteligente para acompanhar seus dias. Descubra escolhas que unem
                    forma, função e um toque de personalidade.
                </p>
                <a class="btn-primary-custom" href="#colecao">
                    Explorar coleção <span>↗</span>
                </a>

                <div class="hero-proof">
                    <div class="proof-avatars">
                        <b>J</b>
                        <b>M</b>
                        <b>A</b>
                    </div>
                    <span><strong>+2.400</strong> pessoas já escolheram Aurora</span>
                </div>
            </div>

            <div class="hero-visual">
                <div class="hero-image"></div>
                <div class="hero-note">
                    <span>01 / 04</span>
                    <strong>Design para viver<br>todos os dias.</strong>
                    <span class="note-arrow">↗</span>
                </div>
                <div class="hero-stamp">A<br>—<br>26</div>
            </div>
        </div>
    </section>

    <section class="benefits">
        <div class="container benefits-row">
            <div>
                <span>↗</span>
                <strong>Feito para durar</strong>
                <small>Qualidade que acompanha</small>
            </div>
            <div>
                <span>◇</span>
                <strong>Compra segura</strong>
                <small>Seus dados protegidos</small>
            </div>
            <div>
                <span>⌁</span>
                <strong>Envio cuidadoso</strong>
                <small>Do nosso espaço até você</small>
            </div>
            <div>
                <span>♡</span>
                <strong>Troca sem estresse</strong>
                <small>Até 30 dias para decidir</small>
            </div>
        </div>
    </section>

    <section class="collection section-pad" id="colecao">
        <div class="container">
            <div class="section-heading">
                <div>
                    <span class="eyebrow">CURADORIA AURORA</span>
                    <h2>Escolhas para o <em>dia a dia.</em></h2>
                </div>
                <a href="#produtos" class="text-link">
                    Ver coleção completa <span>↗</span>
                </a>
            </div>

            <div class="category-pills">
                <a
                    class="<?= $categorySlug === '' ? 'active' : '' ?>"
                    href="/ecommerce-schunck/#colecao"
                >
                    Tudo
                </a>
                <?php foreach ($categories as $category): ?>
                    <a
                        class="<?= $categorySlug === $category['slug'] ? 'active' : '' ?>"
                        href="?categoria=<?= h($category['slug']) ?>#colecao"
                    >
                        <?= h($category['name']) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="row g-4" id="produtos">
                <?php foreach ($products as $product): ?>
                    <div class="col-6 col-lg-3">
                        <article class="product-card">
                            <a
                                href="/ecommerce-schunck/product.php?id=<?= (int) $product['id'] ?>"
                                class="product-photo"
                            >
                                <img
                                    src="<?= h($product['image_url']) ?>"
                                    alt="<?= h($product['name']) ?>"
                                    loading="lazy"
                                >
                                <?php if ($product['badge']): ?>
                                    <span class="product-badge"><?= h($product['badge']) ?></span>
                                <?php endif; ?>
                                <span class="quick-view">Ver produto ↗</span>
                            </a>

                            <div class="product-info">
                                <span><?= h($product['category_name']) ?></span>
                                <a href="/ecommerce-schunck/product.php?id=<?= (int) $product['id'] ?>">
                                    <h3><?= h($product['name']) ?></h3>
                                </a>
                                <div class="price-row">
                                    <strong><?= money($product['price']) ?></strong>
                                    <small>ou 6x de <?= money((float) $product['price'] / 6) ?></small>
                                </div>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>

                <?php if (!$products): ?>
                    <p class="empty-state">Nenhum produto encontrado. Tente outra busca.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="editorial">
        <div class="container editorial-inner">
            <div class="editorial-image"></div>
            <div class="editorial-copy">
                <span class="eyebrow">MENOS, MELHOR</span>
                <h2>O essencial<br>com <em>intenção.</em></h2>
                <p>
                    Acreditamos que os melhores objetos são aqueles que você escolhe uma vez
                    e leva com você por muito tempo.
                </p>
                <a class="text-link" href="#colecao">
                    Conheça a seleção <span>↗</span>
                </a>
            </div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
