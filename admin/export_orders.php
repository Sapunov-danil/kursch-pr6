<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}

// Получаем данные заказов с детализацией товаров
$orders = $pdo->query("
    SELECT o.*, u.first_name, u.last_name, u.email, u.phone,
           oi.shoe_id, oi.quantity, oi.price_at_order,
           s.name as shoe_name, s.brand, s.size
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN shoes s ON oi.shoe_id = s.id
    ORDER BY o.created_at DESC
")->fetchAll();

// Группируем заказы с их товарами
$groupedOrders = [];
foreach ($orders as $order) {
    $orderId = $order['id'];
    if (!isset($groupedOrders[$orderId])) {
        $groupedOrders[$orderId] = [
            'order' => $order,
            'items' => []
        ];
    }
    if ($order['shoe_id']) {
        $groupedOrders[$orderId]['items'][] = [
            'shoe_name' => $order['shoe_name'],
            'brand' => $order['brand'],
            'size' => $order['size'],
            'quantity' => $order['quantity'],
            'price' => $order['price_at_order']
        ];
    }
}

// Устанавливаем заголовки для XLS
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename=orders_export_' . date('Y-m-d_H-i') . '.xls');
header('Pragma: no-cache');
header('Expires: 0');

// Начинаем вывод HTML таблицы (Excel понимает HTML)
?>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        .header { background-color: #4CAF50; color: white; font-weight: bold; }
        .subheader { background-color: #E8F5E8; font-weight: bold; }
        .total { background-color: #FFE0B2; font-weight: bold; }
        .border { border: 1px solid #ddd; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <table border="1" cellpadding="5" cellspacing="0" width="100%">
        <tr class="header">
            <td colspan="8" style="text-align: center; font-size: 18px; padding: 15px;">
                ОТЧЕТ ПО ЗАКАЗАМ - ShoeStore
            </td>
        </tr>
        <tr class="header">
            <td width="8%">ID заказа</td>
            <td width="12%">Номер заказа</td>
            <td width="15%">Клиент</td>
            <td width="15%">Email</td>
            <td width="10%">Телефон</td>
            <td width="10%">Статус</td>
            <td width="15%">Способ оплаты</td>
            <td width="15%">Дата заказа</td>
        </tr>
        
        <?php 
        $totalRevenue = 0;
        $totalOrders = count($groupedOrders);
        
        foreach ($groupedOrders as $orderData): 
            $order = $orderData['order'];
            $items = $orderData['items'];
            $totalRevenue += $order['total_amount'];
        ?>
        <tr class="subheader">
            <td><?= $order['id'] ?></td>
            <td><?= $order['order_number'] ?></td>
            <td><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></td>
            <td><?= htmlspecialchars($order['email']) ?></td>
            <td><?= $order['phone'] ? htmlspecialchars($order['phone']) : 'Не указан' ?></td>
            <td class="text-center"><?= ucfirst($order['status']) ?></td>
            <td class="text-center"><?= ucfirst($order['payment_method']) ?></td>
            <td class="text-center"><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></td>
        </tr>
        
        <tr>
            <td colspan="8" style="padding: 0;">
                <table width="100%" cellpadding="5" cellspacing="0" style="background-color: #f9f9f9;">
                    <tr style="background-color: #E3F2FD;">
                        <td width="40%"><strong>Товар</strong></td>
                        <td width="20%"><strong>Бренд</strong></td>
                        <td width="10%" class="text-center"><strong>Размер</strong></td>
                        <td width="10%" class="text-center"><strong>Кол-во</strong></td>
                        <td width="20%" class="text-right"><strong>Цена</strong></td>
                    </tr>
                    
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['shoe_name']) ?></td>
                        <td><?= htmlspecialchars($item['brand']) ?></td>
                        <td class="text-center"><?= htmlspecialchars($item['size']) ?></td>
                        <td class="text-center"><?= $item['quantity'] ?></td>
                        <td class="text-right"><?= number_format($item['price'], 0, '', ' ') ?> ₽</td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <tr style="background-color: #FFF3E0;">
                        <td colspan="4" class="text-right"><strong>Общая сумма заказа:</strong></td>
                        <td class="text-right"><strong><?= number_format($order['total_amount'], 0, '', ' ') ?> ₽</strong></td>
                    </tr>
                    
                    <tr>
                        <td colspan="5">
                            <strong>Адрес доставки:</strong> <?= htmlspecialchars($order['shipping_address']) ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        
        <tr><td colspan="8" style="height: 10px; background-color: #fff;"></td></tr>
        <?php endforeach; ?>
        
        <!-- Итоговая статистика -->
        <tr class="total">
            <td colspan="4" class="text-right"><strong>ВСЕГО ЗАКАЗОВ:</strong></td>
            <td class="text-center"><strong><?= $totalOrders ?></strong></td>
            <td colspan="2" class="text-right"><strong>ОБЩАЯ ВЫРУЧКА:</strong></td>
            <td class="text-right"><strong><?= number_format($totalRevenue, 0, '', ' ') ?> ₽</strong></td>
        </tr>
        
        <tr>
            <td colspan="8" style="text-align: center; padding: 10px; color: #666;">
                Отчет сгенерирован: <?= date('d.m.Y H:i') ?>
            </td>
        </tr>
    </table>
</body>
</html>
<?php
exit;
?>