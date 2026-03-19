<?php
require_once '../config.php';

if (!isLoggedIn()) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    redirect('auth/login.php');
}

// Получаем информацию о пользователе
$user = getUserById($_SESSION['user_id']);
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();

// Получаем количество заказов по статусам
$orders_stats = $pdo->prepare("
    SELECT status, COUNT(*) as count 
    FROM orders 
    WHERE user_id = ? 
    GROUP BY status
");
$orders_stats->execute([$_SESSION['user_id']]);
$stats = $orders_stats->fetchAll(PDO::FETCH_KEY_PAIR);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет - ShoeStore</title>
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="../styles/profile.css">
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
                    <?php if (isAdmin()): ?>
                        <a href="../admin/dashboard.php" class="admin-link">Админка</a>
                    <?php endif; ?>
                    <a href="../auth/logout.php" class="btn-logout">Выйти</a>
                </div>
            </div>
        </div>
    </header>

    <section class="section">
        <div class="container">
            <h2 class="section-title">Личный кабинет</h2>
            
            <div class="profile-container">
                <!-- Боковая панель -->
                <div class="profile-sidebar">
                    <h3 style="text-align: center; margin-bottom: 10px;"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h3>
                    <p style="text-align: center; color: var(--gray); margin-bottom: 25px;"><?= htmlspecialchars($user['email']) ?></p>
                    
                    <div class="profile-stats">
                        <div class="stat-item">
                            <span class="stat-number"><?= count($orders) ?></span>
                            <span class="stat-label">Всего заказов</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number"><?= $stats['доставлен'] ?? 0 ?></span>
                            <span class="stat-label">Доставлено</span>
                        </div>
                    </div>
                    
                    <div style="border-top: 1px solid #e9ecef; padding-top: 20px;">
                        <div style="display: flex; align-items: center; margin-bottom: 15px;">
                            <span style="font-size: 20px; margin-right: 10px;">📞</span>
                            <span><?= htmlspecialchars($user['phone'] ?? 'Не указан') ?></span>
                        </div>
                        <div style="display: flex; align-items: center;">
                            <span style="font-size: 20px; margin-right: 10px;">👤</span>
                            <span>Клиент с <?= date('d.m.Y', strtotime($user['created_at'])) ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Основной контент -->
                <div class="profile-main">
                    <div class="section-tabs">
                        <button class="tab active" onclick="switchTab('orders')">Мои заказы</button>
                        <button class="tab" onclick="switchTab('profile')">Профиль</button>
                    </div>
                    
                    <!-- Вкладка заказов -->
                    <div id="orders-tab" class="tab-content active">
                        <h3 style="margin-bottom: 25px;">История заказов</h3>
                        
                        <?php if (empty($orders)): ?>
                            <div class="no-orders">
                                <div class="no-orders-icon">📦</div>
                                <h4>Заказов пока нет</h4>
                                <p>Совершите свою первую покупку в нашем магазине</p>
                                <a href="../catalog/catalog.php" class="btn" style="margin-top: 20px;">Перейти в каталог</a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <div class="order-card">
                                    <div class="order-header">
                                        <div>
                                            <span class="order-number">Заказ #<?= $order['id'] ?></span>
                                            <span class="order-date"><?= date('d.m.Y в H:i', strtotime($order['created_at'])) ?></span>
                                        </div>
                                        <span class="status-badge status-<?= $order['status'] ?>"><?= $order['status'] ?></span>
                                    </div>
                                    
                                    <div class="order-details">
                                        <div>
                                            <div class="detail-item">
                                                <span class="detail-label">Сумма заказа:</span>
                                                <span class="detail-value"><?= number_format($order['total_amount'], 0, '', ' ') ?> ₽</span>
                                            </div>
                                            <div class="detail-item">
                                                <span class="detail-label">Способ оплаты:</span>
                                                <span class="detail-value"><?= $order['payment_method'] ?></span>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="detail-item">
                                                <span class="detail-label">Адрес доставки:</span>
                                                <span class="detail-value"><?= htmlspecialchars($order['shipping_address']) ?></span>
                                            </div>
                                            <div class="detail-item">
                                                <span class="detail-label">Номер заказа:</span>
                                                <span class="detail-value"><?= $order['order_number'] ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Вкладка профиля -->
                    <div id="profile-tab" class="tab-content">
                        <h3 style="margin-bottom: 25px;">Настройки профиля</h3>
                        
                        <div style="background: #f8f9fa; padding: 25px; border-radius: 10px; margin-bottom: 25px;">
                            <h4 style="margin-bottom: 15px;">Личная информация</h4>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                <div>
                                    <label style="display: block; margin-bottom: 5px; font-weight: 600; color: var(--gray);">Имя</label>
                                    <div style="padding: 10px; background: white; border-radius: 5px;"><?= htmlspecialchars($user['first_name']) ?></div>
                                </div>
                                <div>
                                    <label style="display: block; margin-bottom: 5px; font-weight: 600; color: var(--gray);">Фамилия</label>
                                    <div style="padding: 10px; background: white; border-radius: 5px;"><?= htmlspecialchars($user['last_name']) ?></div>
                                </div>
                                <div>
                                    <label style="display: block; margin-bottom: 5px; font-weight: 600; color: var(--gray);">Email</label>
                                    <div style="padding: 10px; background: white; border-radius: 5px;"><?= htmlspecialchars($user['email']) ?></div>
                                </div>
                                <div>
                                    <label style="display: block; margin-bottom: 5px; font-weight: 600; color: var(--gray);">Телефон</label>
                                    <div style="padding: 10px; background: white; border-radius: 5px;"><?= htmlspecialchars($user['phone'] ?? 'Не указан') ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div style="background: #e7f3ff; padding: 25px; border-radius: 10px;">
                            <h4 style="margin-bottom: 15px; color: var(--accent);">Статистика аккаунта</h4>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
                                <div style="text-align: center; padding: 15px; background: white; border-radius: 8px;">
                                    <div style="font-size: 24px; font-weight: bold; color: var(--primary);"><?= count($orders) ?></div>
                                    <div style="font-size: 12px; color: var(--gray);">Всего заказов</div>
                                </div>
                                <div style="text-align: center; padding: 15px; background: white; border-radius: 8px;">
                                    <div style="font-size: 24px; font-weight: bold; color: #27ae60;"><?= $stats['доставлен'] ?? 0 ?></div>
                                    <div style="font-size: 12px; color: var(--gray);">Доставлено</div>
                                </div>
                                <div style="text-align: center; padding: 15px; background: white; border-radius: 8px;">
                                    <div style="font-size: 24px; font-weight: bold; color: #f39c12;"><?= $stats['ожидание'] ?? 0 ?></div>
                                    <div style="font-size: 12px; color: var(--gray);">В обработке</div>
                                </div>
                                <div style="text-align: center; padding: 15px; background: white; border-radius: 8px;">
                                    <div style="font-size: 20px; font-weight: bold; color: var(--accent);">
                                        <?= date('d.m.Y', strtotime($user['created_at'])) ?>
                                    </div>
                                    <div style="font-size: 12px; color: var(--gray);">Дата регистрации</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        function switchTab(tabName) {
            // Скрыть все вкладки
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Убрать активный класс со всех кнопок
            document.querySelectorAll('.tab').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Показать выбранную вкладку
            document.getElementById(tabName + '-tab').classList.add('active');
            
            // Активировать кнопку
            event.target.classList.add('active');
        }
    </script>
</body>
</html>