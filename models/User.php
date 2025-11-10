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
    public $email_verified;
    public $verification_token;
    public $verification_token_expiry;
    public $reset_token;
    public $reset_token_expiry;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Register a new user
     */
    public function register() {
        // Generate verification token
        $verification_token = bin2hex(random_bytes(32));
        $verification_expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        $query = "INSERT INTO " . $this->table . " 
                  SET full_name = :full_name,
                      email = :email,
                      phone = :phone,
                      occupation = :occupation,
                      password = :password,
                      verification_token = :verification_token,
                      verification_token_expiry = :verification_expiry";

        $stmt = $this->conn->prepare($query);

        // Hash password
        $hashed_password = password_hash($this->password, PASSWORD_BCRYPT);

        $stmt->bindParam(':full_name', $this->full_name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':phone', $this->phone);
        $stmt->bindParam(':occupation', $this->occupation);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':verification_token', $verification_token);
        $stmt->bindParam(':verification_expiry', $verification_expiry);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            $this->verification_token = $verification_token;
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
                // Check if email is verified
                if (!$row['email_verified']) {
                    return ['error' => 'email_not_verified', 'email' => $email];
                }
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
        $query = "SELECT id, full_name, email, phone, occupation, email_verified, created_at 
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

        // Get current password to verify it's different
        $query_check = "SELECT password FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt_check = $this->conn->prepare($query_check);
        $stmt_check->bindParam(':id', $user['id']);
        $stmt_check->execute();
        
        if ($stmt_check->rowCount() > 0) {
            $current = $stmt_check->fetch();
            // Verify new password is different from current password
            if (password_verify($new_password, $current['password'])) {
                return 'same_password'; // Return special code for same password
            }
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
    public function updatePassword($user_id, $new_password, $current_password = null) {
        // Get current password to verify it's different
        $query_check = "SELECT password FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt_check = $this->conn->prepare($query_check);
        $stmt_check->bindParam(':id', $user_id);
        $stmt_check->execute();
        
        if ($stmt_check->rowCount() > 0) {
            $current = $stmt_check->fetch();
            
            // If current password provided, verify it
            if ($current_password !== null && !password_verify($current_password, $current['password'])) {
                return 'wrong_current_password';
            }
            
            // Verify new password is different from current password
            if (password_verify($new_password, $current['password'])) {
                return 'same_password'; // Return special code for same password
            }
        }

        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

        $query = "UPDATE " . $this->table . " 
                  SET password = :password
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':id', $user_id);

        return $stmt->execute();
    }

    /**
     * Verify email with token
     */
    public function verifyEmail($token) {
        $query = "SELECT id, email, full_name FROM " . $this->table . " 
                  WHERE verification_token = :token 
                  AND verification_token_expiry > NOW() 
                  AND email_verified = FALSE
                  LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':token', $token);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch();
            
            // Mark email as verified
            $update_query = "UPDATE " . $this->table . " 
                            SET email_verified = TRUE,
                                verification_token = NULL,
                                verification_token_expiry = NULL
                            WHERE id = :id";
            
            $update_stmt = $this->conn->prepare($update_query);
            $update_stmt->bindParam(':id', $user['id']);
            
            if ($update_stmt->execute()) {
                return $user;
            }
        }
        return false;
    }

    /**
     * Resend verification email
     */
    public function resendVerificationToken($email) {
        // Check if email exists and is not verified
        $query = "SELECT id FROM " . $this->table . " 
                  WHERE email = :email 
                  AND email_verified = FALSE 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Generate new token
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));

            $update_query = "UPDATE " . $this->table . " 
                            SET verification_token = :token,
                                verification_token_expiry = :expiry
                            WHERE email = :email";

            $update_stmt = $this->conn->prepare($update_query);
            $update_stmt->bindParam(':token', $token);
            $update_stmt->bindParam(':expiry', $expiry);
            $update_stmt->bindParam(':email', $email);

            if ($update_stmt->execute()) {
                return $token;
            }
        }
        return false;
    }

    /**
     * Get user by email
     */
    public function getUserByEmail($email) {
        $query = "SELECT id, full_name, email, email_verified FROM " . $this->table . " 
                  WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetch();
        }
        return false;
    }
}

