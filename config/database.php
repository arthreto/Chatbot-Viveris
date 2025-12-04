<?php
class Database {
    private $db_file = 'data/chatbot.db';
    private $conn;

    public function __construct() {
        // Vérifier si SQLite est disponible
        if (!extension_loaded('pdo_sqlite')) {
            throw new Exception(
                "L'extension PDO SQLite n'est pas activée sur ce serveur. " .
                "Veuillez activer l'extension php-sqlite3 ou php-pdo-sqlite dans votre configuration PHP."
            );
        }

        // Créer le dossier data s'il n'existe pas
        $data_dir = __DIR__ . '/../data';
        if (!file_exists($data_dir)) {
            if (!mkdir($data_dir, 0755, true)) {
                throw new Exception("Impossible de créer le dossier data. Vérifiez les permissions d'écriture.");
            }
        }
        
        // Vérifier les permissions d'écriture
        if (!is_writable($data_dir)) {
            throw new Exception("Le dossier data n'est pas accessible en écriture. Vérifiez les permissions.");
        }
        
        // Initialiser la base de données si elle n'existe pas
        $db_path = __DIR__ . '/../' . $this->db_file;
        if (!file_exists($db_path)) {
            $this->initializeDatabase();
        }
    }

    public function getConnection() {
        if ($this->conn === null) {
            try {
                $db_path = __DIR__ . '/../' . $this->db_file;
                $this->conn = new PDO(
                    "sqlite:" . $db_path,
                    null,
                    null,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );
                
                // Activer les clés étrangères
                $this->conn->exec("PRAGMA foreign_keys = ON");
            } catch(PDOException $e) {
                error_log("Connection Error: " . $e->getMessage());
            }
        }

        return $this->conn;
    }

    private function initializeDatabase() {
        $db_path = __DIR__ . '/../' . $this->db_file;
        
        try {
            $conn = new PDO("sqlite:" . $db_path);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Activer les clés étrangères
            $conn->exec("PRAGMA foreign_keys = ON");
        } catch(PDOException $e) {
            throw new Exception("Erreur lors de la création de la base de données SQLite: " . $e->getMessage());
        }
        
        // Créer les tables
        $sql = "
        -- Table des utilisateurs
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            google_id TEXT UNIQUE NOT NULL,
            email TEXT NOT NULL,
            name TEXT NOT NULL,
            picture TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE INDEX IF NOT EXISTS idx_google_id ON users(google_id);

        -- Table des conversations
        CREATE TABLE IF NOT EXISTS chats (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            title TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );

        CREATE INDEX IF NOT EXISTS idx_user_id ON chats(user_id);

        -- Table des messages
        CREATE TABLE IF NOT EXISTS messages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            chat_id INTEGER NOT NULL,
            role TEXT NOT NULL CHECK(role IN ('user', 'assistant')),
            content TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (chat_id) REFERENCES chats(id) ON DELETE CASCADE
        );

        CREATE INDEX IF NOT EXISTS idx_chat_id ON messages(chat_id);

        -- Trigger pour mettre à jour updated_at automatiquement
        CREATE TRIGGER IF NOT EXISTS update_users_timestamp 
        AFTER UPDATE ON users
        BEGIN
            UPDATE users SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
        END;

        CREATE TRIGGER IF NOT EXISTS update_chats_timestamp 
        AFTER UPDATE ON chats
        BEGIN
            UPDATE chats SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
        END;
        ";
        
        try {
            $conn->exec($sql);
        } catch(PDOException $e) {
            throw new Exception("Erreur lors de la création des tables: " . $e->getMessage());
        }
    }
}
?>

