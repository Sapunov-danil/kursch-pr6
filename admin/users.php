<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'delete_user':
                $user_id = $_POST['user_id'];
                if ($user_id != $_SESSION['user_id']) {
                    try {
                        $pdo->beginTransaction();
                        
                        // Получаем все заказы пользователя
                        $orderStmt = $pdo->prepare("SELECT id FROM orders WHERE user_id = ?");
                        $orderStmt->execute([$user_id]);
                        $orders = $orderStmt->fetchAll();
                        
                        // Удаляем order_items для каждого заказа
                        foreach ($orders as $order) {
                            $pdo->prepare("DELETE FROM order_items WHERE order_id = ?")->execute([$order['id']]);
                        }
                        
                        // Удаляем заказы пользователя
                        $pdo->prepare("DELETE FROM orders WHERE user_id = ?")->execute([$user_id]);
                        
                        // Удаляем пользователя
                        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                        $stmt->execute([$user_id]);
                        
                        $pdo->commit();
                        $_SESSION['message'] = 'Пользователь удален!';
                        
                    } catch (Exception $e) {
                        $pdo->rollBack();
                        $_SESSION['error'] = 'Ошибка при удалении пользователя: ' . $e->getMessage();
                    }
                } else {
                    $_SESSION['error'] = 'Нельзя удалить самого себя!';
                }
                break;
                
            case 'update_role':
                $user_id = $_POST['user_id'];
                $role = $_POST['role'];
                if ($user_id != $_SESSION['user_id']) {
                    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
                    $stmt->execute([$role, $user_id]);
                    $_SESSION['message'] = 'Роль пользователя обновлена!';
                } else {
                    $_SESSION['error'] = 'Нельзя изменить свою собственную роль!';
                }
                break;

            case 'add_user':
                $email = $_POST['email'];
                $password = $_POST['password'];
                $first_name = $_POST['first_name'];
                $last_name = $_POST['last_name'];
                $phone = $_POST['phone'];
                $role = $_POST['role'];

                // Проверяем, существует ли email
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $_SESSION['error'] = 'Пользователь с таким email уже существует!';
                } else {
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO users (email, password_hash, first_name, last_name, phone, role) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$email, $password_hash, $first_name, $last_name, $phone, $role]);
                    $_SESSION['message'] = 'Пользователь успешно добавлен!';
                }
                break;
        }
    }
    redirect('users.php');
}

// Получаем список пользователей
$users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление пользователями - ShoeStore</title>
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
                <h2 class="section-title">Управление пользователями</h2>
                <a href="dashboard.php" class="btn btn-secondary">Назад в админку</a>
            </div>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?= $_SESSION['error'] ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <!-- Форма добавления пользователя -->
            <div class="card">
                <div class="card-header">
                    <h3>Добавить нового пользователя</h3>
                </div>
                <div class="card-body">
                    <form method="POST" class="user-form">
                        <input type="hidden" name="action" value="add_user">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Имя *</label>
                                <input type="text" name="first_name" required>
                            </div>
                            <div class="form-group">
                                <label>Фамилия *</label>
                                <input type="text" name="last_name" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Email *</label>
                                <input type="email" name="email" required>
                            </div>
                            <div class="form-group">
                                <label>Телефон</label>
                                <input type="text" name="phone">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Пароль *</label>
                                <input type="password" name="password" required>
                            </div>
                            <div class="form-group">
                                <label>Роль *</label>
                                <select name="role" required>
                                    <option value="user">Пользователь</option>
                                    <option value="admin">Администратор</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Добавить пользователя</button>
                    </form>
                </div>
            </div>

            <!-- Список пользователей -->
            <div class="card">
                <div class="card-header">
                    <h3>Список пользователей (<?= count($users) ?>)</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Имя</th>
                                    <th>Email</th>
                                    <th>Телефон</th>
                                    <th>Роль</th>
                                    <th>Дата регистрации</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= $user['id'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td><?= htmlspecialchars($user['phone'] ?? 'Не указан') ?></td>
                                    <td>
                                        <form method="POST" class="role-form">
                                            <input type="hidden" name="action" value="update_role">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <select name="role" onchange="this.form.submit()" <?= $user['id'] == $_SESSION['user_id'] ? 'disabled' : '' ?>>
                                                <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>Пользователь</option>
                                                <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Администратор</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td><?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></td>
                                    <td>
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="delete_user">
                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Удалить пользователя?')">
                                                🗑️ Удалить
                                            </button>
                                        </form>
                                        <?php else: ?>
                                        <span class="text-muted">Текущий пользователь</span>
                                        <?php endif; ?>
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