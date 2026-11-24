# Aurora Store

Loja demonstrativa em PHP, MySQL e Bootstrap para o desafio do 4º bimestre. Inclui catálogo com busca e categorias, página de produto, conta de cliente, carrinho em sessão, checkout simulado com baixa de estoque e painel administrativo.

## Requisitos

- XAMPP com Apache, PHP 8+ e MySQL/MariaDB.
- PDO MySQL habilitado no PHP.
- Git para clonar o repositório.

## Configuração local

1. Clone o repositório para `C:\xampp\htdocs\ecommerce-schunck`.
2. No phpMyAdmin, importe `database/schema.sql`.
3. Copie `.env.example` para `.env` e ajuste o nome do banco, usuário e senha, se necessário. O arquivo `.env` é ignorado pelo Git.
4. Inicie Apache e MySQL no XAMPP e abra `http://localhost/ecommerce-schunck/`.
5. Crie uma conta pela loja. Para habilitar o painel administrativo dessa conta, execute no phpMyAdmin (substituindo o e-mail):

    ```sql
    UPDATE users SET role = 'admin' WHERE email = 'seu-email@exemplo.com';
    ```

O pagamento é apenas uma simulação acadêmica. As imagens vêm de URLs externas do Unsplash. Credenciais reais não devem ser publicadas.

## Arquivos principais

- `database/schema.sql`: tabelas e carga inicial de produtos.
- `config/database.php`: conexão PDO e leitura das variáveis do `.env`.
- `includes/`: sessão, validações e layout compartilhado.
- `admin/`: gestão de produtos e consulta de pedidos.
- `GUIA_PARA_SEU_AMIGO.md`: passos simples para compartilhar e executar o projeto.
