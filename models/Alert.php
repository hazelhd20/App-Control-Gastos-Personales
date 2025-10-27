<?php
/**
 * Alert Model
 */

class Alert {
    private $conn;
    private $table = 'alerts';

    public $id;
    public $user_id;
    public $type;
    public $message;
    public $is_read;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Create an alert
     */
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  SET user_id = :user_id,
                      type = :type,
                      message = :message,
                      is_read = 0";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':type', $this->type);
        $stmt->bindParam(':message', $this->message);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    /**
     * Get unread alerts for user
     */
    public function getUnreadByUserId($user_id) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE user_id = :user_id AND is_read = 0
                  ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get all alerts for user
     */
    public function getAllByUserId($user_id, $limit = 20) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE user_id = :user_id
                  ORDER BY created_at DESC
                  LIMIT :limit";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Mark alert as read
     */
    public function markAsRead($id, $user_id) {
        $query = "UPDATE " . $this->table . " 
                  SET is_read = 1
                  WHERE id = :id AND user_id = :user_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $user_id);

        return $stmt->execute();
    }

    /**
     * Mark all alerts as read for user
     */
    public function markAllAsRead($user_id) {
        $query = "UPDATE " . $this->table . " 
                  SET is_read = 1
                  WHERE user_id = :user_id AND is_read = 0";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);

        return $stmt->execute();
    }

    /**
     * Get unread count
     */
    public function getUnreadCount($user_id) {
        $query = "SELECT COUNT(*) as count FROM " . $this->table . " 
                  WHERE user_id = :user_id AND is_read = 0";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        $row = $stmt->fetch();
        return $row['count'];
    }
}

