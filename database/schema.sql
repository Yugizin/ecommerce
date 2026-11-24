CREATE DATABASE IF NOT EXISTS aurora_store
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE aurora_store;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL UNIQUE,
    slug VARCHAR(100) NOT NULL UNIQUE
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NOT NULL,
    name VARCHAR(160) NOT NULL,
    slug VARCHAR(180) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    stock INT UNSIGNED NOT NULL DEFAULT 0,
    image_url VARCHAR(500) NOT NULL,
    badge VARCHAR(40) NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories (id),
    INDEX idx_products_active_category (active, category_id)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    payment_method ENUM('pix', 'boleto', 'cartao') NOT NULL,
    status ENUM('confirmed', 'cancelled') NOT NULL DEFAULT 'confirmed',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users (id)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    product_name VARCHAR(160) NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    unit_price DECIMAL(10, 2) NOT NULL,
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE,
    CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products (id)
) ENGINE = InnoDB;

INSERT IGNORE INTO categories (name, slug)
VALUES
    ('Áudio', 'audio'),
    ('Acessórios', 'acessorios'),
    ('Bem-estar', 'bem-estar');

INSERT IGNORE INTO products (
    category_id,
    name,
    slug,
    description,
    price,
    stock,
    image_url,
    badge
)
VALUES
    (
        1,
        'Fone Studio Wave',
        'fone-studio-wave',
        'Som imersivo, conexão Bluetooth estável e até 30 horas de bateria para acompanhar sua rotina.',
        349.90,
        18,
        'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=900&q=85',
        'Mais vendido'
    ),
    (
        2,
        'Mochila Urban Move',
        'mochila-urban-move',
        'Design leve e resistente, com compartimento acolchoado para notebook e espaço para o essencial.',
        289.00,
        12,
        'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=900&q=85',
        'Novo'
    ),
    (
        3,
        'Garrafa Flow 750ml',
        'garrafa-flow-750',
        'Aço inoxidável com parede dupla para manter sua bebida na temperatura ideal por mais tempo.',
        119.90,
        24,
        'https://images.unsplash.com/photo-1602143407151-7111542de6e8?auto=format&fit=crop&w=900&q=85',
        NULL
    ),
    (
        2,
        'Óculos Solar Horizon',
        'oculos-solar-horizon',
        'Armação contemporânea e lentes com proteção UV400 para dias ao ar livre.',
        199.90,
        9,
        'https://images.unsplash.com/photo-1511499767150-a48a237f0083?auto=format&fit=crop&w=900&q=85',
        'Edição limitada'
    );
