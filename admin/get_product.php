<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    $stmt = $pdo->prepare("SELECT * FROM shoes WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if ($product) {
        header('Content-Type: application/json');
        echo json_encode($product);
    } else {
        header('HTTP/1.1 404 Not Found');
    }
} else {
    header('HTTP/1.1 400 Bad Request');
}
?>