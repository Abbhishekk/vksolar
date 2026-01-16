<?php
// connect/db.php - Enhanced MySQLi connection
class Database {
    public $servername = "localhost";
    private $username = "atriprints_vksolar";
    private $password = "atriprints_vksolar";
    private $database = "atriprints_vksolar";
    public $conn;

   public function __construct() {
        try {
            $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->database);
            
            if ($this->conn->connect_error) {
                throw new Exception("Connection failed: " . $this->conn->connect_error);
            }
            
            $this->conn->set_charset("utf8mb4");
        } catch (Exception $e) {
            error_log("Database connection error: " . $e->getMessage());
            die("Database connection error. Please try again later.");
        }
    }

    public function getConnection() {
        return $this->conn;
    }
}

// Global connection instance
try {
    $database = new Database();
    $conn = $database->getConnection();
} catch (Exception $e) {
    error_log("Database initialization error: " . $e->getMessage());
    die("System initialization error.");
}
?>