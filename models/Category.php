<?php
/**
 * Category Model
 */

class Category {
    private $conn;
    private $table = 'categories';

    public $id;
    public $user_id;
    public $name;
    public $type;
    public $icon;
    public $color;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Create a new category
     */
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  SET user_id = :user_id,
                      name = :name,
                      type = :type,
                      icon = :icon,
                      color = :color";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':user_id', $this->user_id);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':type', $this->type);
        $stmt->bindParam(':icon', $this->icon);
        $stmt->bindParam(':color', $this->color);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    /**
     * Get all categories for a user (including defaults)
     */
    public function getCategoriesByUser($user_id, $type = null) {
        if ($type) {
            $query = "SELECT id, user_id, name, type, icon, color 
                      FROM " . $this->table . " 
                      WHERE (user_id = :user_id OR user_id IS NULL)
                      AND type = :type
                      ORDER BY user_id DESC, name ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':type', $type);
        } else {
            $query = "SELECT id, user_id, name, type, icon, color 
                      FROM " . $this->table . " 
                      WHERE (user_id = :user_id OR user_id IS NULL)
                      ORDER BY user_id DESC, name ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get only user's custom categories
     */
    public function getCustomCategoriesByUser($user_id, $type = null) {
        if ($type) {
            $query = "SELECT id, user_id, name, type, icon, color 
                      FROM " . $this->table . " 
                      WHERE user_id = :user_id AND type = :type
                      ORDER BY name ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':type', $type);
        } else {
            $query = "SELECT id, user_id, name, type, icon, color 
                      FROM " . $this->table . " 
                      WHERE user_id = :user_id
                      ORDER BY type ASC, name ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
        }
        
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get a category by ID
     */
    public function getById($id, $user_id) {
        $query = "SELECT id, user_id, name, type, icon, color 
                  FROM " . $this->table . " 
                  WHERE id = :id AND (user_id = :user_id OR user_id IS NULL)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        return $stmt->fetch();
    }

    /**
     * Update category
     */
    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET name = :name,
                      icon = :icon,
                      color = :color
                  WHERE id = :id AND user_id = :user_id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':icon', $this->icon);
        $stmt->bindParam(':color', $this->color);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':user_id', $this->user_id);

        return $stmt->execute();
    }

    /**
     * Delete category (only user's custom categories)
     */
    public function delete($id, $user_id) {
        $query = "DELETE FROM " . $this->table . " 
                  WHERE id = :id AND user_id = :user_id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':user_id', $user_id);

        return $stmt->execute();
    }

    /**
     * Check if category exists for user
     */
    public function exists($name, $type, $user_id) {
        $query = "SELECT COUNT(*) as count 
                  FROM " . $this->table . " 
                  WHERE name = :name AND type = :type 
                  AND (user_id = :user_id OR user_id IS NULL)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();

        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    /**
     * Get available icons for a category type (Font Awesome icons)
     */
    public static function getIconsByType($type) {
        if ($type === 'expense') {
            return [
                'fa-utensils', 'fa-car', 'fa-home', 'fa-pills', 'fa-book', 'fa-tshirt', 'fa-lightbulb', 'fa-pizza-slice', 'fa-beer', 'fa-film',
                'fa-gamepad', 'fa-futbol', 'fa-palette', 'fa-shopping-bag', 'fa-spray-can', 'fa-soap', 'fa-wallet', 'fa-dollar-sign', 'fa-gift', 'fa-box',
                'fa-subway', 'fa-plane', 'fa-university', 'fa-hospital', 'fa-mobile-alt', 'fa-laptop', 'fa-desktop', 'fa-tv', 'fa-music', 'fa-camera'
            ];
        } else {
            return [
                'fa-briefcase', 'fa-laptop-code', 'fa-chart-line', 'fa-wallet', 'fa-gift', 'fa-dollar-sign', 'fa-university', 'fa-credit-card', 'fa-mobile-alt', 'fa-handshake',
                'fa-graduation-cap', 'fa-trophy', 'fa-star', 'fa-birthday-cake', 'fa-rocket', 'fa-lightbulb', 'fa-bell', 'fa-bullseye', 'fa-gem', 'fa-magic'
            ];
        }
    }

    /**
     * Get available colors
     */
    public static function getColors() {
        return [
            '#FF6B6B', '#4ECDC4', '#95E1D3', '#F38181', '#AA96DA',
            '#FCBAD3', '#A8D8EA', '#FFAAA5', '#C7CEEA', '#10B981',
            '#3B82F6', '#8B5CF6', '#F59E0B', '#EC4899', '#14B8A6',
            '#EF4444', '#6366F1', '#84CC16', '#F97316', '#06B6D4'
        ];
    }
}

