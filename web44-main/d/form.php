<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Форма</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
        }
        
        .form-container {
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        label {
            display: block;
            margin-bottom: 10px;
        }
        
        input[type="text"], input[type="tel"], input[type="email"], input[type="date"], select, textarea {
            width: 95%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        
        input[type="radio"] {
            margin-right: 10px;
        }
        
        input[type="checkbox"] {
            margin-right: 10px;
        }
        
        input[type="submit"] {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        
        .radio-group  {
            margin-bottom: 20px;
        }
        .radio-group label {
          display: inline;}
        .checkbox-group {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .checkbox-group label {
            margin-left: 10px;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Форма</h2>
        <form action="index.php" method="POST">
            <!-- Поле ФИО -->
            <label for="full_name">ФИО:</label>
            <input type="text" name="full_name" id="full_name" required>

            <!-- Поле Телефон -->
            <label for="phone">Телефон:</label>
            <input type="tel" name="phone" id="phone" required>

            <!-- Поле Email -->
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required>

            <!-- Поле Дата рождения -->
            <label for="dob">Дата рождения:</label>
            <input type="date" name="dob" id="dob" required>

            <!-- Поле Пол -->
            <label>Пол:</label>
            <div class="radio-group">
                <input type="radio" name="gender" value="male" id="male" required>
                <label for="male">Мужской</label>
                <input type="radio" name="gender" value="female" id="female" required>
                <label for="female">Женский</label>
            </div>

            <!-- Поле Языки программирования -->
            <label for="languages">Языки программирования:</label>
            <select name="languages[]" id="languages" multiple required>
                <option value="Pascal">Pascal</option>
                <option value="C">C</option>
                <option value="C++">C++</option>
                <option value="JavaScript">JavaScript</option>
                <option value="PHP">PHP</option>
                <option value="Python">Python</option>
                <option value="Java">Java</option>
                <option value="Haskell">Haskell</option>
                <option value="Clojure">Clojure</option>
                <option value="Prolog">Prolog</option>
                <option value="Scala">Scala</option>
                <option value="Go">Go</option>
            </select>

            <!-- Поле Биография -->
            <label for="bio">Биография:</label>
            <textarea name="bio" id="bio" rows="5" cols="40" required></textarea>

            <!-- Чекбокс "С контрактом ознакомлен" -->
            <div class="checkbox-group">
                <input type="checkbox" name="contract" id="contract" required>
                <label for="contract">С контрактом ознакомлен</label>
            </div>

            <!-- Кнопка отправки формы -->
            <input type="submit" value="Сохранить">
        </form>
    </div>
</body>
</html>
