<?php
require_once '../config.php';

if (!isLoggedIn()) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    redirect('../auth/login.php');
}

// Получаем информацию о товаре для покупки
$shoe_id = $_SESSION['buy_product_id'] ?? ($_GET['shoe_id'] ?? 0);
if (!$shoe_id) {
    $_SESSION['error'] = 'Товар не выбран';
    redirect('catalog.php');
}

$stmt = $pdo->prepare("SELECT * FROM shoes WHERE id = ? AND deleted = 0");
$stmt->execute([$shoe_id]);
$product = $stmt->fetch();

if (!$product) {
    $_SESSION['error'] = 'Товар не найден';
    redirect('catalog.php');
}

// Обработка оформления заказа
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipping_address = $_POST['shipping_address'] ?? '';
    $payment_method = $_POST['payment_method'] ?? '';
    
    if (empty($shipping_address)) {
        $error = 'Укажите адрес доставки';
    } else {
        // Создаем заказ
        try {
            $pdo->beginTransaction();
            
            // Создаем заказ
            $order_number = 'ORD' . date('Ymd') . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, order_number, total_amount, shipping_address, payment_method) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $_SESSION['user_id'],
                $order_number,
                $product['price'],
                $shipping_address,
                $payment_method
            ]);
            
            $order_id = $pdo->lastInsertId();
            
            // Добавляем товар в заказ
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, shoe_id, price_at_order, quantity) VALUES (?, ?, ?, 1)");
            $stmt->execute([$order_id, $product['id'], $product['price']]);
            
            $pdo->commit();
            
            unset($_SESSION['buy_product_id']);
            $_SESSION['success'] = 'Заказ успешно оформлен! Номер вашего заказа: ' . $order_number;
            redirect('../user/profile.php');
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Ошибка при оформлении заказа: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оформление заказа - ShoeStore</title>
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
            <h2 class="section-title">Оформление заказа</h2>

            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>

            <div class="checkout-content">
                <div class="order-summary">
                    <h3>Ваш заказ</h3>
                    <div class="product-summary">
                        <div class="product-image-small">
                            <?php if ($product['image']): ?>
                                <img src="../admin/uploads/products/<?= htmlspecialchars($product['image']) ?>" 
                                     alt="<?= htmlspecialchars($product['name']) ?>">
                            <?php else: ?>
                                <div style="font-size: 40px;">
                                    
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="product-details">
                            <h4><?= htmlspecialchars($product['name']) ?></h4>
                            <p><?= htmlspecialchars($product['brand']) ?> - Размер: <?= htmlspecialchars($product['size']) ?></p>
                            <p class="product-price"><?= number_format($product['price'], 0, '', ' ') ?> ₽</p>
                        </div>
                    </div>
                    <div class="total-summary">
                        <h4>Итого: <?= number_format($product['price'], 0, '', ' ') ?> ₽</h4>
                    </div>
                </div>

                <div class="checkout-form">
                    <h3>Данные для доставки</h3>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label>Адрес доставки:</label>
                            <textarea name="shipping_address" placeholder="Укажите полный адрес доставки" required><?= htmlspecialchars($_POST['shipping_address'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Способ оплаты:</label>
                            <select name="payment_method" required>
                                <option value="карта">Банковская карта</option>
                                <option value="наличные">Наличные при получении</option>
                                <option value="онлайн">Онлайн-оплата</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn">Подтвердить заказ</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</body>
</html>