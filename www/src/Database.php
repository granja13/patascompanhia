<?php
require __DIR__ . '/../app/site_functions.php';

class Database {
    private static $instance;

    public static function getConnection() {
        if (!self::$instance) {
            try {
                self::$instance = new PDO('mysql:host=localhost;dbname=patas', 'root', '');
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                error_404();
                exit();
            }
        }
        return self::$instance;
    }
}

try {
    $conn = Database::getConnection();
    //echo "Conexão bem-sucedida!";
} catch (PDOException $e) {
    error_404();
}
?>
