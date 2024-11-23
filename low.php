<?php

if (isset($_GET['Submit'])) {
    // Получаем пользовательский ввод
    $id = $_GET['id'];

    // Удаляем пробелы по краям
    $id = trim($id);

    // Фильтрация ввода: проверяем только на цифры с помощью регулярного выражения
    if (!preg_match('/^\d+$/', $id)) {
        http_response_code(400); // HTTP 400: Некорректный запрос
        echo '<pre>Invalid input detected. User ID must be numeric only, with no special characters allowed.</pre>';
        exit;
    }

    // Преобразуем в целое число
    $id = (int)$id;

    $exists = false;

    switch ($_DVWA['SQLI_DB']) {
        case MYSQL:
            // Подготовленный запрос для MySQL
            $query = "SELECT first_name, last_name FROM users WHERE user_id = ?";
            try {
                $stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $query);
                if ($stmt === false) {
                    throw new Exception("Database error: Unable to prepare statement");
                }
                mysqli_stmt_bind_param($stmt, "i", $id); // "i" для целого числа
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                // Проверяем, существуют ли результаты
                if ($result && mysqli_num_rows($result) > 0) {
                    $exists = true;
                }
                mysqli_stmt_close($stmt);
            } catch (Exception $e) {
                http_response_code(500); // HTTP 500: Ошибка сервера
                echo '<pre>Database error occurred: ' . $e->getMessage() . '</pre>';
                exit;
            }
            break;

        case SQLITE:
            global $sqlite_db_connection;

            // Подготовленный запрос для SQLite
            $query = "SELECT first_name, last_name FROM users WHERE user_id = :id";
            try {
                $stmt = $sqlite_db_connection->prepare($query);
                $stmt->bindValue(':id', $id, SQLITE3_INTEGER); // Тип INTEGER
                $results = $stmt->execute();
                $row = $results->fetchArray();
                $exists = $row !== false;
                $stmt->close();
            } catch (Exception $e) {
                http_response_code(500); // HTTP 500: Ошибка сервера
                echo '<pre>Database error occurred: ' . $e->getMessage() . '</pre>';
                exit;
            }
            break;
    }

    // Результат для пользователя
    if ($exists) {
        echo '<pre>User ID exists in the database.</pre>';
    } else {
        header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
        echo '<pre>User ID is MISSING from the database.</pre>';
    }
}
?>
