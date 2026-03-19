<?php
require_once '../config.php';

// Обработка фильтров
$category = $_GET['category'] ?? '';
$type = $_GET['type'] ?? '';
$search = $_GET['search'] ?? '';

// Построение запроса с фильтрами
$sql = "SELECT * FROM shoes WHERE deleted = 0";
$params = [];

if (!empty($category)) {
    $sql .= " AND category = ?";
    $params[] = $category;
}

if (!empty($type)) {
    $sql .= " AND type = ?";
    $params[] = $type;
}

if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR brand LIKE ? OR description LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Обработка покупки
if (isset($_GET['action']) && $_GET['action'] == 'buy' && isset($_GET['id']) && isLoggedIn()) {
    $shoe_id = $_GET['id'];
    $_SESSION['buy_product_id'] = $shoe_id;
    header('Location: checkout.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Каталог - ShoeStore</title>
    <link rel="stylesheet" href="../styles/style.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <a href="../index.php" class="logo">Shoe<span>Store</span></a>
                
                <nav>
                    <ul>
                        <li><a href="../index.php">Главная</a></li>
                        <li><a href="catalog.php">Каталог</a></li>
                        <li><a href="catalog.php?category=мужская">Мужская</a></li>
                        <li><a href="catalog.php?category=женская">Женская</a></li>
                        <li><a href="catalog.php?category=детская">Детская</a></li>
                    </ul>
                </nav>
                
                <div class="header-actions">
                    <div class="search-box">
                        <form method="GET" action="catalog.php" style="display: flex;">
                            <input type="text" name="search" placeholder="Поиск обуви..." value="<?= htmlspecialchars($search) ?>">
                            <button type="submit" style="margin-left: 5px; padding: 8px 15px; background: var(--accent); color: white; border: none; border-radius: 20px; cursor: pointer;">Найти</button>
                        </form>
                    </div>
                    <?php if (isLoggedIn()): ?>
                        <a href="../user/profile.php" class="user-icon">👤 <?= htmlspecialchars($_SESSION['user_name']) ?></a>
                        <?php if (isAdmin()): ?>
                            <a href="../admin/dashboard.php" class="admin-link">Админка</a>
                        <?php endif; ?>
                        <a href="../auth/logout.php" class="btn-logout">Выйти</a>
                    <?php else: ?>
                        <a href="../auth/login.php" class="btn-login">Войти</a>
                        <a href="../auth/register.php" class="btn-register">Регистрация</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <section class="section">
        <div class="container">
            <h2 class="section-title">Каталог обуви</h2>

            <!-- Фильтры -->
            <div class="filters">
                <h3>Фильтры</h3>
                <div class="filter-buttons">
                    <a href="catalog.php" class="btn-filter <?= empty($category) && empty($type) ? 'active' : '' ?>">Все</a>
                    <a href="catalog.php?category=мужская" class="btn-filter <?= $category == 'мужская' ? 'active' : '' ?>">Мужская</a>
                    <a href="catalog.php?category=женская" class="btn-filter <?= $category == 'женская' ? 'active' : '' ?>">Женская</a>
                    <a href="catalog.php?category=детская" class="btn-filter <?= $category == 'детская' ? 'active' : '' ?>">Детская</a>
                    <a href="catalog.php?type=кроссовки" class="btn-filter <?= $type == 'кроссовки' ? 'active' : '' ?>">Кроссовки</a>
                    <a href="catalog.php?type=туфли" class="btn-filter <?= $type == 'туфли' ? 'active' : '' ?>">Туфли</a>
                    <a href="catalog.php?type=ботинки" class="btn-filter <?= $type == 'ботинки' ? 'active' : '' ?>">Ботинки</a>
                </div>
            </div>

            <!-- Товары -->
            <div class="products">
                <?php if (empty($products)): ?>
                    <p style="text-align: center; width: 100%;">Товары не найдены.</p>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php if ($product['image']): ?>
                                <img src="../admin/uploads/products/<?= htmlspecialchars($product['image']) ?>" 
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
                                <a href="catalog.php?action=buy&id=<?= $product['id'] ?>" class="btn">Купить сейчас</a>
                            <?php else: ?>
                                <a href="../auth/login.php" class="btn">Войдите чтобы купить</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>
</body>
</html>