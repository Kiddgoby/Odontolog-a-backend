<?php
function getDatabaseConnection() {
    $host = 'localhost';
    $dbname = 'dentalclinic_database';
    $username = 'root';
    $password = '';
    
    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $conn;
    } catch (PDOException $e) {
        error_log("Error de conexión: " . $e->getMessage());
        throw $e;
    }
}
?>
