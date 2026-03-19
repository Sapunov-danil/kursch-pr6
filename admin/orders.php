<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}

// Обработка изменения статуса заказа
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$status, $order_id]);
    $_SESSION['message'] = 'Статус заказа обновлен!';
    redirect('orders.php');
}

// Получаем список заказов с информацией о пользователях
$orders = $pdo->query("
    SELECT o.*, u.first_name, u.last_name, u.email, u.phone,
           (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) as items_count
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление заказами - ShoeStore</title>
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
            <div class="page-header">
                <h2 class="section-title">Управление заказами</h2>
                <div class="header-actions">
                    <a href="dashboard.php" class="btn btn-secondary">Назад в админку</a>
                    <a href="export_orders.php" class="btn btn-success">📊 Экспорт в Excel</a>
                </div>
            </div>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h3>Список заказов (<?= count($orders) ?>)</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Пользователь</th>
                                    <th>Контакты</th>
                                    <th>Товары</th>
                                    <th>Сумма</th>
                                    <th>Статус</th>
                                    <th>Оплата</th>
                                    <th>Дата заказа</th>
                                    <th>Адрес доставки</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><?= $order['id'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></strong>
                                        <br><small><?= htmlspecialchars($order['email']) ?></small>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($order['email']) ?>
                                        <?php if ($order['phone']): ?>
                                            <br><small><?= htmlspecialchars($order['phone']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge"><?= $order['items_count'] ?> шт.</span>
                                    </td>
                                    <td><strong><?= number_format($order['total_amount'], 0, '', ' ') ?> ₽</strong></td>
                                    <td>
                                        <form method="POST" class="status-form">
                                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                            <select name="status" onchange="this.form.submit()" class="status-select status-<?= $order['status'] ?>">
                                                <option value="ожидание" <?= $order['status'] == 'ожидание' ? 'selected' : '' ?>>Ожидание</option>
                                                <option value="подтвержден" <?= $order['status'] == 'подтвержден' ? 'selected' : '' ?>>Подтвержден</option>
                                                <option value='доставлен' <?= $order['status'] == 'доставлен' ? 'selected' : '' ?>>Доставлен</option>
                                                <option value='отменен' <?= $order['status'] == 'отменен' ? 'selected' : '' ?>>Отменен</option>
                                            </select>
                                            <input type="hidden" name="update_status" value="1">
                                        </form>
                                    </td>
                                    <td>
                                        <span class="badge badge-payment"><?= $order['payment_method'] ?></span>
                                    </td>
                                    <td><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></td>
                                    <td>
                                        <small><?= htmlspecialchars($order['shipping_address']) ?></small>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>
</html>