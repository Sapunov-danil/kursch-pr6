<?php
require_once '../config.php';

if (!isLoggedIn()) {
    redirect('../auth/login.php');
}

if (!isAdmin()) {
    $_SESSION['error'] = 'Доступ запрещен. Требуются права администратора.';
    redirect('../index.php');
}

// Получаем статистику
$users_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$products_count = $pdo->query("SELECT COUNT(*) FROM shoes")->fetchColumn();
$orders_count = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_revenue = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status = 'доставлен'")->fetchColumn() ?? 0;

// Статистика за последние 7 дней
$recent_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
$recent_revenue = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status = 'доставлен' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn() ?? 0;
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель - ShoeStore</title>
    <link rel="stylesheet" href="../styles/admin.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="header-content">
                <a href="../index.php" class="logo">Shoe<span>Store</span></a>
                <nav>
                    <ul>
                        <li><a href="../index.php">Главная</a></li>
                        <li><a href="../catalog/catalog.php">Каталог</a></li>
                    </ul>
                </nav>
                <div class="header-actions">
                    <a href="../user/profile.php" class="user-icon">👤 <?= htmlspecialchars($_SESSION['user_name']) ?></a>
                    <a href="../auth/logout.php" class="btn-logout">Выйти</a>
                </div>
            </div>
        </div>
    </header>

    <section class="section">
        <div class="container">
            <h2 class="section-title">Админ-панель</h2>

            <!-- Статистика -->
            <div class="stats-grid">
                <div class="stat-card primary">
                    <div class="stat-icon">👥</div>
                    <div class="stat-info">
                        <h3>Пользователи</h3>
                        <p class="stat-number"><?= $users_count ?></p>
                    </div>
                </div>
                <div class="stat-card success">
                    <div class="stat-icon">👟</div>
                    <div class="stat-info">
                        <h3>Товары</h3>
                        <p class="stat-number"><?= $products_count ?></p>
                    </div>
                </div>
                <div class="stat-card warning">
                    <div class="stat-icon">📦</div>
                    <div class="stat-info">
                        <h3>Заказы</h3>
                        <p class="stat-number"><?= $orders_count ?></p>
                        <small>+<?= $recent_orders ?> за 7 дней</small>
                    </div>
                </div>
            </div>

            <!-- Меню админки -->
            <div class="admin-menu">
                <a href="users.php" class="admin-menu-card">
                    <div class="admin-menu-icon">👥</div>
                    <h3>Пользователи</h3>
                    <p>Управление пользователями и ролями</p>
                </a>
                <a href="products.php" class="admin-menu-card">
                    <div class="admin-menu-icon">👟</div>
                    <h3>Товары</h3>
                    <p>Добавление и редактирование товаров</p>
                </a>
                <a href="orders.php" class="admin-menu-card">
                    <div class="admin-menu-icon">📦</div>
                    <h3>Заказы</h3>
                    <p>Управление заказами и статусами</p>
                </a>
            </div>
        </div>
    </section>
</body>
</html>
