<?php
class Chat {
    private $conn;
    private $table = 'chats';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($userId, $title = null) {
        if (!$title) {
            $title = 'Nouvelle conversation - ' . date('d/m/Y H:i');
        }

        $query = "INSERT INTO " . $this->table . " 
                  (user_id, title, created_at, updated_at) 
                  VALUES (:user_id, :title, datetime('now'), datetime('now'))";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':title', $title);

        if ($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function getAllByUser($userId) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE user_id = :user_id 
                  ORDER BY updated_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById($chatId, $userId) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE id = :id AND user_id = :user_id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $chatId);
        $stmt->bindParam(':user_id', $userId);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function updateTitle($chatId, $userId, $title) {
        $query = "UPDATE " . $this->table . " 
                  SET title = :title, updated_at = datetime('now') 
                  WHERE id = :id AND user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':id', $chatId);
        $stmt->bindParam(':user_id', $userId);
        return $stmt->execute();
    }

    public function delete($chatId, $userId) {
        $query = "DELETE FROM messages WHERE chat_id = :chat_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':chat_id', $chatId);
        $stmt->execute();

        $query = "DELETE FROM " . $this->table . " 
                  WHERE id = :id AND user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $chatId);
        $stmt->bindParam(':user_id', $userId);
        return $stmt->execute();
    }
}
?>

