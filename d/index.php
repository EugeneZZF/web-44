<?php
// Устанавливаем кодировку UTF-8
header('Content-Type: text/html; charset=UTF-8');

// Если метод запроса GET, просто отображаем форму
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Если есть параметр save, выводим сообщение об успешном сохранении
    if (!empty($_GET['save'])) {
        print('Спасибо, результаты сохранены.');
    }

    // Получаем значения из куки, если есть
    session_start();

    // Проверяем, авторизован ли пользователь
    if (empty($_SESSION['user_id'])) {
        // Если отправлена форма логина
        if (!empty($_POST['login']) && !empty($_POST['password'])) {
            $user = 'u68585'; // Замените на ваш логин
            $pass = '6687463'; // Замените на ваш пароль
            $db = new PDO('mysql:host=localhost;dbname=u68585', $user, $pass, [
                PDO::ATTR_PERSISTENT => true,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            $stmt = $db->prepare("SELECT id, password FROM users WHERE login = :login");
            $stmt->execute([':login' => $_POST['login']]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($userData && password_verify($_POST['password'], $userData['password'])) {
                $_SESSION['user_id'] = $userData['id'];
                header('Location: ' . $_SERVER['REQUEST_URI']);
                exit();
            } else {
                print('Неверный логин или пароль.<br>');
            }
        }
        // Форма логина
        print('<form method="POST">
            <label>Логин: <input name="login" required></label><br>
            <label>Пароль: <input type="password" name="password" required></label><br>
            <input type="submit" value="Войти">
        </form>');
        exit();
    }

    // Если пользователь не существует в БД, предложить регистрацию
    $user = 'u68585'; // Замените на ваш логин
    $pass = '6687463'; // Замените на ваш пароль
    $db = new PDO('mysql:host=localhost;dbname=u68585', $user, $pass, [
        PDO::ATTR_PERSISTENT => true,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $stmt = $db->prepare("SELECT id FROM users WHERE id = :id");
    $stmt->execute([':id' => $_SESSION['user_id']]);
    if (!$stmt->fetch()) {
        // Регистрация нового пользователя
        if (!empty($_POST['new_login']) && !empty($_POST['new_password'])) {
            $hash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (login, password) VALUES (:login, :password)");
            $stmt->execute([
                ':login' => $_POST['new_login'],
                ':password' => $hash
            ]);
            $_SESSION['user_id'] = $db->lastInsertId();
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit();
        }
        // Форма регистрации
        print('<form method="POST">
            <label>Придумайте логин: <input name="new_login" required></label><br>
            <label>Придумайте пароль: <input type="password" name="new_password" required></label><br>
            <input type="submit" value="Зарегистрироваться">
        </form>');
        exit();
    }

    $values = [
        'full_name' => isset($_COOKIE['full_name']) ? $_COOKIE['full_name'] : '',
        'phone' => isset($_COOKIE['phone']) ? $_COOKIE['phone'] : '',
        'email' => isset($_COOKIE['email']) ? $_COOKIE['email'] : '',
        'dob' => isset($_COOKIE['dob']) ? $_COOKIE['dob'] : '',
        'gender' => isset($_COOKIE['gender']) ? $_COOKIE['gender'] : '',
        'bio' => isset($_COOKIE['bio']) ? $_COOKIE['bio'] : '',
        'languages' => isset($_COOKIE['languages']) ? json_decode($_COOKIE['languages']) : []
    ];

    // Подключаем файл с формой и передаём значения
    include('form.php');
    exit();
}

// Иначе, если запрос был методом POST, проверяем данные и сохраняем их в БД

// Инициализируем массив для ошибок
$errors = [];

// Проверка поля ФИО
if (empty($_POST['full_name'])) {
    $errors[] = 'Заполните ФИО.';
} elseif (!preg_match('/^[a-zA-Zа-яА-Я\s]{1,150}$/u', $_POST['full_name'])) {
    $errors[] = 'ФИО должно содержать только буквы и пробелы и быть не длиннее 150 символов.';
}

// Проверка поля Телефон
if (empty($_POST['phone'])) {
    $errors[] = 'Заполните телефон.';
} elseif (!preg_match('/^\+?\d{10,15}$/', $_POST['phone'])) {
    $errors[] = 'Телефон должен быть в формате +7XXXXXXXXXX или XXXXXXXXXX.';
}

// Проверка поля Email
if (empty($_POST['email'])) {
    $errors[] = 'Заполните email.';
} elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Некорректный email.';
}

// Проверка поля Дата рождения
if (empty($_POST['dob'])) {
    $errors[] = 'Заполните дату рождения.';
} elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['dob'])) {
    $errors[] = 'Некорректный формат даты рождения.';
}

// Проверка поля Пол
if (empty($_POST['gender'])) {
    $errors[] = 'Выберите пол.';
} elseif (!in_array($_POST['gender'], ['male', 'female'])) {
    $errors[] = 'Некорректное значение пола.';
}

// Проверка поля Языки программирования
if (empty($_POST['languages'])) {
    $errors[] = 'Выберите хотя бы один язык программирования.';
} else {
    $allowedLanguages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];
    foreach ($_POST['languages'] as $language) {
        if (!in_array($language, $allowedLanguages)) {
            $errors[] = 'Некорректный язык программирования.';
            break;
        }
    }
}

// Проверка поля Биография
if (empty($_POST['bio'])) {
    $errors[] = 'Заполните биографию.';
}

// Проверка чекбокса "С контрактом ознакомлен"
if (empty($_POST['contract'])) {
    $errors[] = 'Необходимо ознакомиться с контрактом.';
}

// Если есть ошибки, выводим их и завершаем выполнение скрипта
if (!empty($errors)) {
    foreach ($errors as $error) {
        print($error . '<br>');
    }
    exit();
}

// Подключение к базе данных
$user = 'u68585'; // Замените на ваш логин
$pass = '6687463'; // Замените на ваш пароль
$db = new PDO('mysql:host=localhost;dbname=u68585', $user, $pass, [
    PDO::ATTR_PERSISTENT => true,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

try {
    // Начало транзакции
    $db->beginTransaction();

    // Сохранение основной информации о заявке
    $stmt = $db->prepare("INSERT INTO applications (full_name, phone, email, birth_date, gender, bio) 
                          VALUES (:full_name, :phone, :email, :birth_date, :gender, :bio)");
    $stmt->execute([
        ':full_name' => $_POST['full_name'],
        ':phone' => $_POST['phone'],
        ':email' => $_POST['email'],
        ':birth_date' => $_POST['dob'],
        ':gender' => $_POST['gender'],
        ':bio' => $_POST['bio']
    ]);

    // Получение ID последней вставленной записи
    $application_id = $db->lastInsertId();

    // Сохранение выбранных языков программирования
    $stmt = $db->prepare("SELECT id FROM languages WHERE name = :name");
    $insertLang = $db->prepare("INSERT INTO languages (name) VALUES (:name)");
    $linkStmt = $db->prepare("INSERT INTO application_languages (application_id, language_id) 
                              VALUES (:application_id, :language_id)");

    foreach ($_POST['languages'] as $language) {
        $stmt->execute([':name' => $language]);
        $languageData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$languageData) {
            $insertLang->execute([':name' => $language]);
            $language_id = $db->lastInsertId();
        } else {
            $language_id = $languageData['id'];
        }

        $linkStmt->execute([
            ':application_id' => $application_id,
            ':language_id' => $language_id
        ]);
    }

    // Завершение транзакции
    $db->commit();

    // Перенаправление на страницу с сообщением об успешном сохранении
    header('Location: ?save=1');
} catch (PDOException $e) {
    // Откат транзакции в случае ошибки
    $db->rollBack();
    print('Ошибка при сохранении данных: ' . $e->getMessage());
    exit();
}
?>
