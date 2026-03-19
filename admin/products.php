<?php
require_once '../config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../auth/login.php');
}

// Обработка действий
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = $_POST['name'];
                $description = $_POST['description'];
                $brand = $_POST['brand'];
                $category = $_POST['category'];
                $type = $_POST['type'];
                $price = $_POST['price'];
                $size = $_POST['size'];
                
                // Загрузка изображения
                $image = null;
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $image = uploadImage($_FILES['image']);
                }
                
                $stmt = $pdo->prepare("INSERT INTO shoes (name, description, brand, category, type, price, size, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $description, $brand, $category, $type, $price, $size, $image]);
                $_SESSION['message'] = 'Товар успешно добавлен!';
                break;
                
            case 'edit':
                $id = $_POST['id'];
                $name = $_POST['name'];
                $description = $_POST['description'];
                $brand = $_POST['brand'];
                $category = $_POST['category'];
                $type = $_POST['type'];
                $price = $_POST['price'];
                $size = $_POST['size'];
                
                // Если загружено новое изображение
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $image = uploadImage($_FILES['image']);
                    $stmt = $pdo->prepare("UPDATE shoes SET name=?, description=?, brand=?, category=?, type=?, price=?, size=?, image=? WHERE id=?");
                    $stmt->execute([$name, $description, $brand, $category, $type, $price, $size, $image, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE shoes SET name=?, description=?, brand=?, category=?, type=?, price=?, size=? WHERE id=?");
                    $stmt->execute([$name, $description, $brand, $category, $type, $price, $size, $id]);
                }
                $_SESSION['message'] = 'Товар успешно обновлен!';
                break;
                
            case 'delete':
                $id = $_POST['id'];
                // Псевдоудаление - устанавливаем флаг deleted в 1
                $stmt = $pdo->prepare("UPDATE shoes SET deleted = 1 WHERE id=?");
                $stmt->execute([$id]);
                $_SESSION['message'] = 'Товар успешно удален!';
                break;

            case 'restore':
                $id = $_POST['id'];
                // Восстановление товара
                $stmt = $pdo->prepare("UPDATE shoes SET deleted = 0 WHERE id=?");
                $stmt->execute([$id]);
                $_SESSION['message'] = 'Товар успешно восстановлен!';
                break;
        }
    }
    redirect('products.php');
}

// Получаем список товаров (только неудаленные)
$products = $pdo->query("SELECT * FROM shoes WHERE deleted = 0 ORDER BY id DESC")->fetchAll();

// Получаем список удаленных товаров для раздела корзины
$deleted_products = $pdo->query("SELECT * FROM shoes WHERE deleted = 1 ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление товарами - ShoeStore</title>
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
                <h2 class="section-title">Управление товарами</h2>
                <a href="dashboard.php" class="btn btn-secondary">Назад в админку</a>
            </div>

            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>

            <!-- Форма добавления товара -->
            <div class="card">
                <div class="card-header">
                    <h3>Добавить новый товар</h3>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data" class="product-form">
                        <input type="hidden" name="action" value="add">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Название *</label>
                                <input type="text" name="name" placeholder="Название товара" required>
                            </div>
                            <div class="form-group">
                                <label>Бренд *</label>
                                <input type="text" name="brand" placeholder="Бренд" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Описание</label>
                            <textarea name="description" placeholder="Описание товара" rows="3"></textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Категория *</label>
                                <select name="category" required>
                                    <option value="мужская">Мужская</option>
                                    <option value="женская">Женская</option>
                                    <option value="детская">Детская</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Тип *</label>
                                <select name="type" required>
                                    <option value="кроссовки">Кроссовки</option>
                                    <option value="туфли">Туфли</option>
                                    <option value="ботинки">Ботинки</option>
                                    <option value="сапоги">Сапоги</option>
                                    <option value="сандали">Сандали</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Цена *</label>
                                <input type="number" name="price" placeholder="Цена" step="0.01" min="0" required>
                            </div>
                            <div class="form-group">
                                <label>Размер *</label>
                                <input type="text" name="size" placeholder="Размер" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Изображение товара</label>
                            <input type="file" name="image" accept="image/*" class="file-input">
                        </div>
                        <button type="submit" class="btn btn-primary">Добавить товар</button>
                    </form>
                </div>
            </div>

            <!-- Список товаров -->
            <div class="card">
                <div class="card-header">
                    <h3>Список товаров (<?= count($products) ?>)</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Изображение</th>
                                    <th>Название</th>
                                    <th>Бренд</th>
                                    <th>Категория</th>
                                    <th>Тип</th>
                                    <th>Цена</th>
                                    <th>Размер</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?= $product['id'] ?></td>
                                    <td>
                                        <?php if ($product['image']): ?>
                                            <img src="uploads/products/<?= htmlspecialchars($product['image']) ?>" 
                                                 alt="<?= htmlspecialchars($product['name']) ?>" 
                                                 class="product-thumb">
                                        <?php else: ?>
                                            <div class="product-icon">
                                                <?php 
                                                $icons = [
                                                    'кроссовки' => '👟',
                                                    'туфли' => '👞',
                                                    'ботинки' => '🥾',
                                                    'сапоги' => '👢',
                                                    'сандали' => '👡'
                                                ];
                                                echo $icons[$product['type']] ?? '👟';
                                                ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($product['name']) ?></strong>
                                        <?php if ($product['description']): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars(substr($product['description'], 0, 50)) ?>...</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($product['brand']) ?></td>
                                    <td><span class="badge badge-category"><?= $product['category'] ?></span></td>
                                    <td><span class="badge badge-type"><?= $product['type'] ?></span></td>
                                    <td><strong><?= number_format($product['price'], 0, '', ' ') ?> ₽</strong></td>
                                    <td><span class="badge"><?= $product['size'] ?></span></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-sm btn-warning" onclick="editProduct(<?= $product['id'] ?>)">
                                                ✏️ Редактировать
                                            </button>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $product['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Удалить товар?')">
                                                    🗑️ Удалить
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Список удаленных товаров -->
            <div class="card">
                <div class="card-header">
                    <h3>Корзина удаленных товаров (<?= count($deleted_products) ?>)</h3>
                </div>
                <div class="card-body">
                    <?php if (count($deleted_products) > 0): ?>
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Изображение</th>
                                        <th>Название</th>
                                        <th>Бренд</th>
                                        <th>Категория</th>
                                        <th>Тип</th>
                                        <th>Цена</th>
                                        <th>Размер</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($deleted_products as $product): ?>
                                    <tr style="opacity: 0.6;">
                                        <td><?= $product['id'] ?></td>
                                        <td>
                                            <?php if ($product['image']): ?>
                                                <img src="uploads/products/<?= htmlspecialchars($product['image']) ?>" 
                                                    alt="<?= htmlspecialchars($product['name']) ?>" 
                                                    class="product-thumb">
                                            <?php else: ?>
                                                <div class="product-icon">
                                                    <?php 
                                                    $icons = [
                                                        'кроссовки' => '👟',
                                                        'туфли' => '👞',
                                                        'ботинки' => '🥾',
                                                        'сапоги' => '👢',
                                                        'сандали' => '👡'
                                                    ];
                                                    echo $icons[$product['type']] ?? '👟';
                                                    ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($product['name']) ?></strong>
                                            <?php if ($product['description']): ?>
                                                <br><small class="text-muted"><?= htmlspecialchars(substr($product['description'], 0, 50)) ?>...</small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($product['brand']) ?></td>
                                        <td><span class="badge badge-category"><?= $product['category'] ?></span></td>
                                        <td><span class="badge badge-type"><?= $product['type'] ?></span></td>
                                        <td><strong><?= number_format($product['price'], 0, '', ' ') ?> ₽</strong></td>
                                        <td><span class="badge"><?= $product['size'] ?></span></td>
                                        <td>
                                            <div class="action-buttons">
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="restore">
                                                    <input type="hidden" name="id" value="<?= $product['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-success">
                                                        ♻️ Восстановить
                                                    </button>
                                                </form>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= $product['id'] ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Окончательно удалить товар? Это действие нельзя отменить!')">
                                                        🗑️ Удалить навсегда
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Корзина пуста</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Модальное окно редактирования -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Редактировать товар</h3>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <form id="editForm" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="editId">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Название *</label>
                            <input type="text" name="name" id="editName" required>
                        </div>
                        <div class="form-group">
                            <label>Бренд *</label>
                            <input type="text" name="brand" id="editBrand" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Описание</label>
                        <textarea name="description" id="editDescription" rows="3"></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Категория *</label>
                            <select name="category" id="editCategory" required>
                                <option value="мужская">Мужская</option>
                                <option value="женская">Женская</option>
                                <option value="детская">Детская</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Тип *</label>
                            <select name="type" id="editType" required>
                                <option value="кроссовки">Кроссовки</option>
                                <option value="туфли">Туфли</option>
                                <option value="ботинки">Ботинки</option>
                                <option value="сапоги">Сапоги</option>
                                <option value="сандали">Сандали</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Цена *</label>
                            <input type="number" name="price" id="editPrice" step="0.01" min="0" required>
                        </div>
                        <div class="form-group">
                            <label>Размер *</label>
                            <input type="text" name="size" id="editSize" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Изображение товара</label>
                        <input type="file" name="image" accept="image/*" class="file-input">
                        <small>Оставьте пустым, чтобы сохранить текущее изображение</small>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeModal()">Отмена</button>
                        <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const modal = document.getElementById('editModal');
        const closeBtn = document.querySelector('.close');

        function editProduct(id) {
            fetch(`get_product.php?id=${id}`)
                .then(response => response.json())
                .then(product => {
                    // Проверяем, не удален ли товар
                    if (product.deleted === 1) {
                        alert('Нельзя редактировать удаленный товар. Сначала восстановите его.');
                        return;
                    }
                    
                    document.getElementById('editId').value = product.id;
                    document.getElementById('editName').value = product.name;
                    document.getElementById('editDescription').value = product.description || '';
                    document.getElementById('editBrand').value = product.brand;
                    document.getElementById('editCategory').value = product.category;
                    document.getElementById('editType').value = product.type;
                    document.getElementById('editPrice').value = product.price;
                    document.getElementById('editSize').value = product.size;
                    
                    modal.style.display = 'block';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Ошибка загрузки данных товара');
                });
        }

        function closeModal() {
            modal.style.display = 'none';
        }

        closeBtn.addEventListener('click', closeModal);

        window.addEventListener('click', function(event) {
            if (event.target == modal) {
                closeModal();
            }
        });
    </script>
</body>
</html>