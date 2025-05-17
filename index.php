<?php
$host = 'localhost';
$user = 'root';  
$pass = '';      
$db = 'myrzash'; 

$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}
$conn->query("CREATE DATABASE IF NOT EXISTS $db");
$conn->select_db($db);

$query = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    age VARCHAR(255) NOT NULL,
    photo VARCHAR(255) DEFAULT NULL
)";
$conn->query($query);

$uploadDir = "uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if (isset($_POST['clear_all'])) {
    $conn->query("DELETE FROM users");
}
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM users WHERE id = $id");
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit();
}
if (isset($_POST['update'])) {
    $id = intval($_POST['id']);
    $name = htmlspecialchars($_POST['name']);
    $age = htmlspecialchars($_POST['age']);
    $photoPath = '';

    if (!empty($_FILES["photo"]["name"])) {
        $fileName = time() . "_" . basename($_FILES["photo"]["name"]);
        $uploadFile = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $uploadFile)) {
            $photoPath = $uploadFile;
            $conn->query("UPDATE users SET name='$name', age='$age', photo='$photoPath' WHERE id=$id");
        }
    } else {
        $conn->query("UPDATE users SET name='$name', age='$age' WHERE id=$id");
    }

    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit();
}
if ($_SERVER["REQUEST_METHOD"] === "POST" && !isset($_POST['update']) && !isset($_POST['clear_all'])) {
    $name = htmlspecialchars($_POST['name'] ?? '');
    $age = htmlspecialchars($_POST['age'] ?? '');
    $photoPath = '';

    if (!empty($_FILES["photo"]["name"])) {
        $fileName = time() . "_" . basename($_FILES["photo"]["name"]);
        $uploadFile = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $uploadFile)) {
            $photoPath = $uploadFile;
        }
    }

    $stmt = $conn->prepare("INSERT INTO users (name, age, photo) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $age, $photoPath);
    $stmt->execute();
    $stmt->close();
}

$result = $conn->query("SELECT * FROM users ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Список</title>
    <style>
        .con { width: 80%; margin: auto; }
        .form { margin-bottom: 40px; }
        .myr { margin-bottom: 30px; border-bottom: 1px solid #ccc; padding-bottom: 10px; }
        .myr img { max-width: 200px; height: auto; }

        body {
          display: flex;
          justify-content: center;
          align-items: center;
          min-height: 100vh;
          background: #eceffc;
        }

        .btn {
          padding: 8px 20px;
          border-radius: 10px;
          overflow: hidden;
        }

        .login-form {
          display: flex;
          flex-direction: column;
          align-items: center;
          padding: 50px 40px;
          color: white;
          background: rgba(0, 0, 0, 0.8);
          border-radius: 10px;
        }

        table {
          width: 100%;
          background: white;
          color: black;
          border-collapse: collapse;
          margin-top: 20px;
        }

        th, td {
          padding: 10px;
          border: 1px solid #ccc;
          text-align: center;
        }

        .actions button {
          margin: 2px;
        }
    </style>
</head>
<body>
    <div class="cont">
        <div class="login-form">
            <form action="" method="post" enctype="multipart/form-data">
                <p>ФИО: <input type="text" name="name" required /></p>
                <p>Возраст: <input type="text" name="age" required /></p>
                <p>Загрузите файл: <input type="file" name="photo" /></p>
                <p class="btn">
                    <input type="submit" value="Отправить">
                </p>
                <p class="btn">
                    <button type="submit" name="clear_all" class="btn" style="background-color: red;">Очистить список</button>
                </p>
            </form>
        </div>

        <div class="login-form">
            <h3>Список:</h3>
            <table>
                <tr>
                    <th>ФИО</th>
                    <th>Возраст</th>
                    <th>Фото</th>
                    <th>Действия</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['age']) ?></td>
                        <td>
                            <?php if (!empty($row['photo'])): ?>
                                <img src="<?= $row['photo'] ?>" style="width:100px;">
                            <?php else: ?>
                                Нет фото
                            <?php endif; ?>
                        </td>
                        <td class="actions">
                            <form action="" method="post" enctype="multipart/form-data" style="display:inline-block;">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                <input type="text" name="name" value="<?= htmlspecialchars($row['name']) ?>" required>
                                <input type="text" name="age" value="<?= htmlspecialchars($row['age']) ?>" required>
                                <input type="file" name="photo">
                                <button type="submit" name="update" class="btn" style="background-color: orange;">Изменить</button>
                            </form>
                            <a href="?delete=<?= $row['id'] ?>" class="btn" style="background-color: red; color:white; padding:8px 12px; display:inline-block; text-decoration:none;">Удалить</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
    </div>
</body>
</html>