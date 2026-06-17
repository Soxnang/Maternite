<?php
namespace App\Config;

use PDO;
use PDOException;

class Database {
    private static $instance = null;
    private $conn;
    
    private function __construct() {
        try {
            // Configuration
            $host = getenv('DB_HOST') ?: 'localhost';
            $dbname = getenv('DB_NAME') ?: 'maternite_db';
            $username = getenv('DB_USER') ?: 'maternite_user';
            $password = getenv('DB_PASSWORD') ?: 'Maternite@2024#';
            $port = getenv('DB_PORT') ?: '3306';
            $charset = 'utf8mb4';
            
            $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";
            
            $this->conn = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 30,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES $charset COLLATE utf8mb4_unicode_ci"
            ]);
            
        } catch (PDOException $e) {
            error_log("Erreur de connexion DB: " . $e->getMessage());
            throw new \Exception("Impossible de se connecter à la base de données");
        }
    }
    
    public static function getConnection() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->conn;
    }
    
    public static function testConnection() {
        try {
            $conn = self::getConnection();
            $conn->query("SELECT 1");
            return ['success' => true, 'message' => 'Connexion réussie'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
