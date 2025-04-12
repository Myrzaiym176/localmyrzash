<?php

$uploadDir = "uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$dataFile = 'data.json';
$dataList = file_exists($dataFile) ? json_decode(file_get_contents($dataFile), true) : [];


if (isset($_POST['clear_all'])) {
    file_put_contents($dataFile, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    $dataList = [];
}


if ($_SERVER["REQUEST_METHOD"] === "POST" && !isset($_POST['clear_all'])) {
    $name = htmlspecialchars($_POST['name'] ?? '');
    $age = htmlspecialchars($_POST['age'] ?? '');
    $about = htmlspecialchars($_POST['about'] ?? '');
    $photoPath = '';

    if (!empty($_FILES["photo"]["name"])) {
        $fileName = time() . "_" . basename($_FILES["photo"]["name"]); 
        $uploadFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $uploadFile)) {
            $photoPath = $uploadFile;
        }
    }

    $dataList[] = [
        'name' => $name,
        'age' => $age,
        'about' => $about,
        'photo' => $photoPath
    ];

    file_put_contents($dataFile, json_encode($dataList, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Список </title>
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
    </style>
</head>
<body>
    <div class="cont">
        <div class="login-form">
            <form action="" method="post" enctype="multipart/form-data">
                <p>ФИО: <input type="text" name="name" /></p>
                <p>Возраст: <input type="text" name="age" /></p>
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
            <?php foreach ($dataList as $myr): ?>
                <div class="myr">
                    <ul>
                        <li><strong>ФИО:</strong> <?= $myr['name'] ?></li>
                        <li><strong>Возраст:</strong> <?= $myr['age'] ?></li>
                       
                        <?php if (!empty($myr['photo'])): ?>
                            <li><strong>Фото:</strong><br><img src="<?= $myr['photo'] ?>" alt="Фото"></li>
                        <?php endif; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>