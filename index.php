<?php
require_once 'config.php';

// Получаем популярные товары из БД
$stmt = $pdo->query("SELECT * FROM shoes WHERE deleted = 0 ORDER BY id DESC LIMIT 4");
$popular_products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShoeStore - Интернет-магазин обуви</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <!-- Шапка -->
    <header>
        <div class="container">
            <div class="header-content">
                <a href="index.php" class="logo">Shoe<span>Store</span></a>
                
                <nav>
                    <ul>
                        <li><a href="index.php">Главная</a></li>
                        <li><a href="catalog/catalog.php">Каталог</a></li>
                        <li><a href="catalog/catalog.php?category=мужская">Мужская</a></li>
                        <li><a href="catalog/catalog.php?category=женская">Женская</a></li>
                        <li><a href="catalog/catalog.php?category=детская">Детская</a></li>
                    </ul>
                </nav>
                
                <div class="header-actions">
                    <div class="search-box">
                        <input type="text" placeholder="Поиск обуви..." id="search-input">
                    </div>
                    <?php if (isLoggedIn()): ?>
                        <a href="user/profile.php" class="user-icon">👤 <?= htmlspecialchars($_SESSION['user_name']) ?></a>
                        <?php if (isAdmin()): ?>
                            <a href="admin/dashboard.php" class="admin-link">Админка</a>
                        <?php endif; ?>
                        <a href="auth/logout.php" class="btn-logout">Выйти</a>
                    <?php else: ?>
                        <a href="auth/login.php" class="btn-login">Войти</a>
                        <a href="auth/register.php" class="btn-register">Регистрация</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Герой секция -->
    <section class="hero">
        <div class="container">
            <h1>Стильная обувь для каждого</h1>
            <p>Откройте для себя коллекцию обуви, которая сочетает в себе комфорт, качество и современный дизайн</p>
            <a href="catalog/catalog.php" class="btn">Перейти в каталог</a>
        </div>
    </section>

    <!-- Категории -->
    <section class="section">
        <div class="container">
            <h2 class="section-title">Категории</h2>
            <div class="categories">
                <div class="category-card">
                    <div class="category-icon">👞</div>
                    <h3>Мужская обувь</h3>
                    <p>Кроссовки, туфли, ботинки и другая обувь для мужчин</p>
                    <a href="catalog/catalog.php?category=мужская" class="btn btn-category">Смотреть</a>
                </div>
                <div class="category-card">
                    <div class="category-icon">👠</div>
                    <h3>Женская обувь</h3>
                    <p>Туфли, сапоги, босоножки и другая обувь для женщин</p>
                    <a href="catalog/catalog.php?category=женская" class="btn btn-category">Смотреть</a>
                </div>
                <div class="category-card">
                    <div class="category-icon">👟</div>
                    <h3>Детская обувь</h3>
                    <p>Удобная и качественная обувь для детей всех возрастов</p>
                    <a href="catalog/catalog.php?category=детская" class="btn btn-category">Смотреть</a>
                </div>
                <div class="category-card">
                    <div class="category-icon">🥾</div>
                    <h3>Спортивная обувь</h3>
                    <p>Кроссовки для бега, тренировок и отдыха</p>
                    <a href="catalog/catalog.php?type=кроссовки" class="btn btn-category">Смотреть</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Популярные товары -->
    <section class="section">
        <div class="container">
            <h2 class="section-title">Популярные товары</h2>
            <div class="products">
                <?php foreach ($popular_products as $product): ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if ($product['image']): ?>
                            <img src="admin/uploads/products/<?= htmlspecialchars($product['image']) ?>" 
                                 alt="<?= htmlspecialchars($product['name']) ?>"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div style="font-size: 60px; display: none; align-items: center; justify-content: center; height: 100%;">
                                
                            </div>
                        <?php else: ?>
                            <div style="font-size: 60px; display: flex; align-items: center; justify-content: center; height: 100%;">
                                
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <div class="product-brand"><?= htmlspecialchars($product['brand']) ?></div>
                        <h3 class="product-name"><?= htmlspecialchars($product['name']) ?></h3>
                        <div class="product-price"><?= number_format($product['price'], 0, '', ' ') ?> ₽</div>
                        <div class="product-meta">
                            <span>Размер: <?= htmlspecialchars($product['size']) ?></span>
                            <span><?= htmlspecialchars($product['category']) ?></span>
                        </div>
                        <?php if (isLoggedIn()): ?>
                            <a href="catalog/catalog.php?action=buy&id=<?= $product['id'] ?>" class="btn">Купить сейчас</a>
                        <?php else: ?>
                            <a href="auth/login.php" class="btn">Войдите чтобы купить</a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Преимущества -->
    <section class="section benefits">
        <div class="container">
            <h2 class="section-title">Почему выбирают нас</h2>
            <div class="benefit-cards">
                <div class="benefit-card">
                    <div class="benefit-icon">🚚</div>
                    <h3>Бесплатная доставка</h3>
                    <p>Бесплатная доставка при заказе от 5000 рублей</p>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon">✅</div>
                    <h3>Гарантия качества</h3>
                    <p>Все товары проходят тщательную проверку</p>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon">↩️</div>
                    <h3>Легкий возврат</h3>
                    <p>Возврат в течение 14 дней без лишних вопросов</p>
                </div>
                <div class="benefit-card">
                    <div class="benefit-icon">📞</div>
                    <h3>Поддержка 24/7</h3>
                    <p>Всегда готовы помочь с выбором и ответить на вопросы</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Подвал -->
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-column">
                    <h3>Магазин</h3>
                    <ul>
                        <li><a href="#">О нас</a></li>
                        <li><a href="#">Контакты</a></li>
                        <li><a href="#">Доставка и оплата</a></li>
                        <li><a href="#">Возврат</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Категории</h3>
                    <ul>
                        <li><a href="catalog/catalog.php?category=мужская">Мужская обувь</a></li>
                        <li><a href="catalog/catalog.php?category=женская">Женская обувь</a></li>
                        <li><a href="catalog/catalog.php?category=детская">Детская обувь</a></li>
                        <li><a href="catalog/catalog.php">Все категории</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Помощь</h3>
                    <ul>
                        <li><a href="#">Частые вопросы</a></li>
                        <li><a href="#">Размеры</a></li>
                        <li><a href="#">Уход за обувью</a></li>
                        <li><a href="#">Публичная оферта</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Контакты</h3>
                    <ul>
                        <li>+7 (999) 123-45-67</li>
                        <li>info@shoestore.ru</li>
                        <li>Москва, ул. Примерная, д. 1</li>
                    </ul>
                </div>
            </div>
            <div class="copyright">
                &copy; 2025 ShoeStore. Все права защищены.
            </div>
        </div>
    </footer>

    <script src="js/main.js"></script>
</body>
</html>