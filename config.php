<?php
// config.php
session_start();

$host = '127.0.0.1';
$db   = 'shoe_store';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Функции для работы с пользователями
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function getUserById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function redirect($url) {
    header("Location: $url");
    exit;
}

// CSRF protection (Synchronizer Token pattern).
// Token хранится в сессии и должен быть передан клиентом при небезопасных запросах.
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        // random_bytes -> base64url, чтобы токен корректно передавался как строка.
        $_SESSION['csrf_token'] = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }
    return $_SESSION['csrf_token'];
}

function csrf_validate($token): bool {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals((string)$_SESSION['csrf_token'], (string)$token);
}

// Функция для загрузки изображений
function uploadImage($file) {
    $target_dir = "uploads/products/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $filename = uniqid() . '.' . $imageFileType;
    $target_file = $target_dir . $filename;
    
    // Проверка на валидность изображения
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return false;
    }
    
    // Проверка размера файла (максимум 2MB)
    if ($file["size"] > 2000000) {
        return false;
    }
    
    // Разрешенные форматы
    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
        return false;
    }
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $filename;
    }
    
    return false;
}
?>