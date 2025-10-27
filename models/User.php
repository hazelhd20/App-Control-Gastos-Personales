<?php
/**
 * User Model
 */

class User {
    private $conn;
    private $table = 'users';

    public $id;
    public $full_name;
    public $email;
    public $phone;
    public $occupation;
    public $password;
    public $reset_token;
    public $reset_token_expiry;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Register a new user
     */
    public function register() {
        $query = "INSERT INTO " . $this->table . " 
                  SET full_name = :full_name,
                      email = :email,
                      phone = :phone,
                      occupation = :occupation,
                      password = :password";

        $stmt = $this->conn->prepare($query);

        // Hash password
        $hashed_password = password_hash($this->password, PASSWORD_BCRYPT);

        $stmt->bindParam(':full_name', $this->full_name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':occupation', $this->occupation);
        $stmt->bindParam(':password', $hashed_password);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    /**
     * Login user
     */
    public function login($email, $password) {
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch();
            if (password_verify($password, $row['password'])) {
                return $row;
            }
        }
        return false;
    }

    /**
     * Check if email exists
     */
    public function emailExists($email) {
        $query = "SELECT id FROM " . $this->table . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    /**
     * Get user by ID
     */
    public function getById($id) {
        $query = "SELECT id, full_name, email, phone, occupation, created_at 
                  FROM " . $this->table . " 
                  WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetch();
        }
        return false;
    }

    /**
     * Update user profile
     */
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET full_name = :full_name,
                      phone = :phone,
                      occupation = :occupation";

        // Only update email if it's different
        if (!empty($this->email)) {
            $query .= ", email = :email";
        }

        $query .= " WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':full_name', $this->full_name);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':occupation', $this->occupation);
        $stmt->bindParam(':id', $this->id);

        if (!empty($this->email)) {
            $stmt->bindParam(':email', $this->email);
        }

        return $stmt->execute();
    }

    /**
     * Generate password reset token
     */
    public function generateResetToken($email) {
        // First, check if email exists
        if (!$this->emailExists($email)) {
            return false;
        }

        // Generate token
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+' . TOKEN_VALIDITY . ' minutes'));

        $query = "UPDATE " . $this->table . " 
                  SET reset_token = :token,
                      reset_token_expiry = :expiry
                  WHERE email = :email";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':expiry', $expiry);
        $stmt->bindParam(':email', $email);

        if ($stmt->execute()) {
            return $token;
        }
        return false;
    }

    /**
     * Verify reset token
     */
    public function verifyResetToken($token) {
        $query = "SELECT id, email FROM " . $this->table . " 
                  WHERE reset_token = :token 
                  AND reset_token_expiry > NOW() 
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetch();
        }
        return false;
    }

    /**
     * Reset password
     */
    public function resetPassword($token, $new_password) {
        $user = $this->verifyResetToken($token);
        
        if (!$user) {
            return false;
        }

        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

        $query = "UPDATE " . $this->table . " 
                  SET password = :password,
                      reset_token = NULL,
                      reset_token_expiry = NULL
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':id', $user['id']);

        return $stmt->execute();
    }

    /**
     * Update password
     */
    public function updatePassword($user_id, $new_password) {
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

        $query = "UPDATE " . $this->table . " 
                  SET password = :password
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':id', $user_id);

        return $stmt->execute();
    }
}

