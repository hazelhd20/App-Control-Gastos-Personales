<?php
/**
 * Database Configuration and Connection
 * 
 * INSTRUCTIONS:
 * 1. Copy this file and rename it to Database.php
 * 2. Update the database credentials below with your actual values
 * 3. Never commit Database.php to version control (it's in .gitignore)
 */

class Database {
    private $host = 'localhost';
    private $db_name = 'control_gastos';
    private $username = 'root';
    private $password = '';
    private $conn;

    /**
     * Get database connection
     */
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
        }

        return $this->conn;
    }
}

