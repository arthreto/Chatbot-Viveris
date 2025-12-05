<?php
class User {
    private $conn;
    private $table = 'users';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function createOrUpdate($googleId, $email, $name, $picture) {
        $existing = $this->getByGoogleId($googleId);
        
        if ($existing) {
            $query = "UPDATE " . $this->table . "
                      SET email = :email, name = :name, picture = :picture, updated_at = datetime('now')
                      WHERE google_id = :google_id";
        } else {
            $query = "INSERT INTO " . $this->table . "
                      (google_id, email, name, picture, created_at, updated_at) 
                      VALUES (:google_id, :email, :name, :picture, datetime('now'), datetime('now'))";
        }

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':google_id', $googleId);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':picture', $picture);

        if ($stmt->execute()) {
            return $this->getByGoogleId($googleId);
        }
        return false;
    }

    public function getByGoogleId($googleId) {
        $query = "SELECT * FROM " . $this->table . " WHERE google_id = :google_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':google_id', $googleId);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }
}
?>

