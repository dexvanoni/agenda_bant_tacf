<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '1q2w3e4r');
define('DB_NAME', 'agenda_bant_tacf');

try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("set names utf8mb4");
    $conn->exec("set character_set_connection=utf8mb4");
    $conn->exec("set character_set_client=utf8mb4");
    $conn->exec("set character_set_results=utf8mb4");
} catch(PDOException $e) {
    echo "Erro na conexão: " . $e->getMessage();
    exit();
}
?> 
