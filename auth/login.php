<?php
require_once '../config.php';

if (isLoggedIn()) {
    redirect('../index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate($_POST['csrf_token'] ?? '')) {
        $error = 'Некорректный CSRF токен. Обновите страницу и попробуйте снова.';
    } else {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        
        // Редирект на предыдущую страницу или на главную
        $redirect_url = $_SESSION['redirect_url'] ?? '../index.php';
        unset($_SESSION['redirect_url']);
        redirect($redirect_url);
    } else {
        $error = 'Неверный email или пароль';
    }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход - ShoeStore</title>
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
                        <li><a href="../catalog/catalog.php">Каталог</a></li>
                    </ul>
                </nav>
                <div class="header-actions">
                    <a href="register.php" class="btn-register">Регистрация</a>
                </div>
            </div>
        </div>
    </header>

    <section class="section">
        <div class="container">
            <div class="auth-form">
                <h2 class="section-title">Вход в аккаунт</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                    <div class="form-group">
                        <label>Email:</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Пароль:</label>
                        <input type="password" name="password" required>
                    </div>
                    
                    <button type="submit" class="btn">Войти</button>
                </form>
                
                <p style="text-align: center; margin-top: 20px;">
                    Нет аккаунта? <a href="register.php">Зарегистрируйтесь</a>
                </p>
            </div>
        </div>
    </section>
</body>
</html>