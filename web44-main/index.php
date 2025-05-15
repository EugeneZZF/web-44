<?php
header('Content-Type: text/html; charset=UTF-8');

// Установка времени жизни сессии (1 час)
session_set_cookie_params(3600);
session_start();

// Очистка сообщений об ошибках после их отображения
if (!empty($_SESSION['formErrors'])) {
    unset($_SESSION['formErrors']);
}
if (!empty($_SESSION['fieldErrors'])) {
    unset($_SESSION['fieldErrors']);
}

// Получение старых значений из куки (если они есть)
$oldValues = [];
if (!empty($_COOKIE['form_data'])) {
    $oldValues = json_decode($_COOKIE['form_data'], true);
}

// Получение ошибок из сессии
$formErrors = $_SESSION['formErrors'] ?? [];
$fieldErrors = $_SESSION['fieldErrors'] ?? [];

// Обработка GET-запроса (отображение формы)
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (!empty($_GET['save'])) {
        print('Спасибо, результаты сохранены!');
    }
    include('form.php');
    exit();
}

// Обработка POST-запроса (отправка формы)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Массивы для хранения ошибок
    $errors = false;
    $formErrors = [];
    $fieldErrors = [];
    
    // Валидация ФИО
    if (empty($_POST['fio'])) {
        $fieldErrors['fio'] = 'Поле ФИО обязательно для заполнения.';
        $errors = true;
    } elseif (!preg_match('/^[а-яА-ЯёЁa-zA-Z\s]+$/u', $_POST['fio'])) {
        $fieldErrors['fio'] = 'ФИО может содержать только буквы и пробелы.';
        $errors = true;
    } elseif (strlen($_POST['fio']) > 150) {
        $fieldErrors['fio'] = 'ФИО не должно превышать 150 символов.';
        $errors = true;
    }
    
    // Валидация телефона
    if (empty($_POST['tel'])) {
        $fieldErrors['tel'] = 'Поле телефона обязательно для заполнения.';
        $errors = true;
    } elseif (!preg_match('/^[\d\s\-\+\(\)]{6,20}$/', $_POST['tel'])) {
        $fieldErrors['tel'] = 'Телефон должен содержать от 6 до 20 цифр, пробелов, +, - или скобок.';
        $errors = true;
    }
    
    // Валидация email
    if (empty($_POST['email'])) {
        $fieldErrors['email'] = 'Поле email обязательно для заполнения.';
        $errors = true;
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $fieldErrors['email'] = 'Пожалуйста, введите корректный email.';
        $errors = true;
    }
    
    // Валидация даты рождения
    if (empty($_POST['date'])) {
        $fieldErrors['date'] = 'Поле даты рождения обязательно для заполнения.';
        $errors = true;
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['date'])) {
        $fieldErrors['date'] = 'Пожалуйста, введите дату в формате ГГГГ-ММ-ДД.';
        $errors = true;
    } else {
        $birthDate = new DateTime($_POST['date']);
        $today = new DateTime();
        $age = $today->diff($birthDate)->y;
        
        if ($age < 18) {
            $fieldErrors['date'] = 'Вы должны быть старше 18 лет.';
            $errors = true;
        }
    }
    
    // Валидация пола
    if (empty($_POST['gender']) || !in_array($_POST['gender'], ['male', 'female'])) {
        $fieldErrors['gender'] = 'Пожалуйста, выберите пол.';
        $errors = true;
    }
    
    // Валидация языков программирования
    if (empty($_POST['plang']) || !is_array($_POST['plang'])) {
        $fieldErrors['plang'] = 'Пожалуйста, выберите хотя бы один язык программирования.';
        $errors = true;
    } else {
        $allowedLanguages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Haskell', 'Clojure', 'Prolog', 'Scala'];
        foreach ($_POST['plang'] as $lang) {
            if (!in_array($lang, $allowedLanguages)) {
                $fieldErrors['plang'] = 'Выбран недопустимый язык программирования.';
                $errors = true;
                break;
            }
        }
    }
    
    // Валидация биографии
    if (empty($_POST['bio'])) {
        $fieldErrors['bio'] = 'Поле биографии обязательно для заполнения.';
        $errors = true;
    } elseif (strlen($_POST['bio']) > 500) {
        $fieldErrors['bio'] = 'Биография не должна превышать 500 символов.';
        $errors = true;
    }
    
    // Валидация чекбокса
    if (empty($_POST['check'])) {
        $fieldErrors['check'] = 'Необходимо подтвердить ознакомление с контрактом.';
        $errors = true;
    }
    
    // Если есть ошибки, сохраняем их в сессию и возвращаем на форму
    if ($errors) {
        $_SESSION['formErrors'] = ['Пожалуйста, исправьте указанные ошибки.'];
        $_SESSION['fieldErrors'] = $fieldErrors;
        $_SESSION['oldValues'] = $_POST;
        
        header('Location: index.php');
        exit();
    }
    
    // Если ошибок нет, сохраняем данные в БД
    $user = 'u68585';
    $pass = '6687463';
    $dbname = 'u68585';
    
    try {
        $db = new PDO("mysql:host=u68585.kubsu-dev.ru;dbname=$dbname", $user, $pass, [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        // Вставка основных данных
        $stmt = $db->prepare("INSERT INTO applications (fio, tel, email, birth_date, gender, bio) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['fio'],
            $_POST['tel'],
            $_POST['email'],
            $_POST['date'],
            $_POST['gender'],
            $_POST['bio']
        ]);
        
        $applicationId = $db->lastInsertId();
        
        // Вставка языков программирования
        $stmt = $db->prepare("INSERT INTO application_languages (application_id, language_id) VALUES (?, ?)");
        foreach ($_POST['plang'] as $language) {
            $langStmt = $db->prepare("SELECT id FROM languages WHERE name = ?");
            $langStmt->execute([$language]);
            $langId = $langStmt->fetchColumn();
            
            if (!$langId) {
                $langStmt = $db->prepare("INSERT INTO languages (name) VALUES (?)");
                $langStmt->execute([$language]);
                $langId = $db->lastInsertId();
            }
            
            $stmt->execute([$applicationId, $langId]);
        }
        
        // Сохраняем данные в куки на 1 год
        $formData = [
            'fio' => $_POST['fio'],
            'tel' => $_POST['tel'],
            'email' => $_POST['email'],
            'date' => $_POST['date'],
            'gender' => $_POST['gender'],
            'bio' => $_POST['bio'],
            'plang' => $_POST['plang']
        ];
        
        setcookie('form_data', json_encode($formData), time() + 3600 * 24 * 365, '/');
        
        // Перенаправляем с флагом успешного сохранения
        header('Location: ?save=1');
        exit();
    } catch (PDOException $e) {
        $_SESSION['formErrors'] = ['Ошибка при сохранении данных: ' . $e->getMessage()];
        $_SESSION['oldValues'] = $_POST;
        
        header('Location: index.php');
        exit();
    }
}
?>
