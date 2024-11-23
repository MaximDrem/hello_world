<?php

if (isset($_GET['Submit'])) {
    $id = $_GET['id'];
    $id = trim($id);
    if (!preg_match('/^\d+$/', $id)) {
        http_response_code(400);
        echo '<pre>Invalid input detected. User ID must be numeric only, with no special characters allowed.</pre>';
        exit;
    }

    $id = (int)$id;

    $exists = false;

    switch ($_DVWA['SQLI_DB']) {
        case MYSQL:
            $query = "SELECT first_name, last_name FROM users WHERE user_id = ?";
            try {
                $stmt = mysqli_prepare($GLOBALS["___mysqli_ston"], $query);
                if ($stmt === false) {
                    throw new Exception("Database error: Unable to prepare statement");
                }
                mysqli_stmt_bind_param($stmt, "i", $id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                if ($result && mysqli_num_rows($result) > 0) {
                    $exists = true;
                }
                mysqli_stmt_close($stmt);
            } catch (Exception $e) {
                http_response_code(500); 
                echo '<pre>Database error occurred: ' . $e->getMessage() . '</pre>';
                exit;
            }
            break;

        case SQLITE:
            global $sqlite_db_connection;

            $query = "SELECT first_name, last_name FROM users WHERE user_id = :id";
            try {
                $stmt = $sqlite_db_connection->prepare($query);
                $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
                $results = $stmt->execute();
                $row = $results->fetchArray();
                $exists = $row !== false;
                $stmt->close();
            } catch (Exception $e) {
                http_response_code(500);
                echo '<pre>Database error occurred: ' . $e->getMessage() . '</pre>';
                exit;
            }
            break;
    }

    if ($exists) {
        echo '<pre>User ID exists in the database.</pre>';
    } else {
        header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
        echo '<pre>User ID is MISSING from the database.</pre>';
    }
}
?>
