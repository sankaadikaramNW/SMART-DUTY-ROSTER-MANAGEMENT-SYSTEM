<?php
/**
 * Database Singleton Connection class using PDO
 */

class Database {
    private static $instance = null;
    private $conn;

    // Private constructor to prevent direct instantiation
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false, // True prepared statements
            ];
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            throw new Exception("Database Connection Failed: " . $e->getMessage());
        }
    }

    // Get connection instance
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // Return active connection
    public function getConnection() {
        return $this->conn;
    }
}
