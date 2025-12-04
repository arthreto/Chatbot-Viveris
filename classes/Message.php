<?php
class Message {
    private $conn;
    private $table = 'messages';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($chatId, $role, $content) {
        $query = "INSERT INTO " . $this->table . " 
                  (chat_id, role, content, created_at) 
                  VALUES (:chat_id, :role, :content, datetime('now'))";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':chat_id', $chatId);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':content', $content);

        return $stmt->execute();
    }

    public function getAllByChat($chatId) {
        $query = "SELECT * FROM " . $this->table . " 
                  WHERE chat_id = :chat_id 
                  ORDER BY created_at ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':chat_id', $chatId);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getHistoryForAI($chatId, $limit = 10) {
        $query = "SELECT role, content FROM " . $this->table . " 
                  WHERE chat_id = :chat_id 
                  ORDER BY created_at DESC 
                  LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':chat_id', $chatId);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        $messages = $stmt->fetchAll();
        return array_reverse($messages);
    }
}
?>

