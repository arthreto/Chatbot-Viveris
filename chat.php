<?php
require_once 'config/config.php';
require_once 'config/database.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Impossible de se connecter à la base de données.");
    }
} catch (Exception $e) {
    header('Location: index.php?error=db_error');
    exit;
}

require_once 'classes/User.php';
$userObj = new User($db);
$user = $userObj->getById($_SESSION['user_id']);

if (!$user) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Chatbot Philosophique</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <!-- Interface principale du chat -->
        <div class="chat-container">
            <header class="chat-header">
                <div class="user-info">
                    <img src="<?php echo htmlspecialchars($user['picture']); ?>" alt="Avatar" class="user-avatar">
                    <span class="user-name"><?php echo htmlspecialchars($user['name']); ?></span>
                </div>
                <div class="header-actions">
                    <a href="index.php" class="btn-back">← Retour</a>
                    <a href="logout.php" class="btn-logout">Déconnexion</a>
                </div>
            </header>

            <div class="main-content">
                <aside class="sidebar">
                    <button class="btn-new-chat" id="newChatBtn">
                        <span>+</span> Nouvelle conversation
                    </button>
                    <div class="chats-list" id="chatsList">
                        <!-- Les conversations seront chargées ici -->
                    </div>
                </aside>

                <main class="chat-area">
                    <div class="chat-messages" id="chatMessages">
                        <div class="welcome-message">
                            <div class="mascot-small">
                                <div class="mascot-face">
                                    <div class="eye left-eye"></div>
                                    <div class="eye right-eye"></div>
                                    <div class="mouth"></div>
                                </div>
                            </div>
                            <h2>Salut ! Je suis ton chatbot philosophique ! 🧠✨</h2>
                            <p>Pose-moi n'importe quelle question, je vais répondre de manière complètement décalée mais géniale !</p>
                        </div>
                    </div>
                    <div class="chat-input-container">
                        <form id="chatForm" class="chat-form">
                            <input type="text" id="messageInput" placeholder="Tape ton message ici..." autocomplete="off">
                            <button type="submit" id="sendBtn" class="btn-send">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="22" y1="2" x2="11" y2="13"></line>
                                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                                </svg>
                            </button>
                        </form>
                    </div>
                </main>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>

