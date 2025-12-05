<?php
require_once 'config/config.php';
require_once 'config/database.php';

$isLoggedIn = isset($_SESSION['user_id']);
$user = null;
$db = null;

if ($isLoggedIn) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        if ($db) {
            require_once 'classes/User.php';
            $userObj = new User($db);
            $user = $userObj->getById($_SESSION['user_id']);
        }
    } catch (Exception $e) {
        $isLoggedIn = false;
    }
}

$googleAuthUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
    'client_id' => GOOGLE_CLIENT_ID,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope' => 'openid email profile',
    'access_type' => 'online'
]);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Chatbot Philosophique</title>
    <link rel="icon" type="image/png" href="assets/logo.png">
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
                    <?php if ($isLoggedIn && $user): ?>
                        <img src="<?php echo htmlspecialchars($user['picture']); ?>" alt="Avatar" class="user-avatar">
                        <span class="user-name"><?php echo htmlspecialchars($user['name']); ?></span>
                    <?php else: ?>
                        <div class="user-avatar" style="background: var(--bg-lighter); border: 2px solid var(--border); display: flex; align-items: center; justify-content: center; color: var(--text-muted); font-weight: 600;">👤</div>
                        <span class="user-name">Invité</span>
                    <?php endif; ?>
                </div>
                <div class="header-actions">
                    <a href="index.php" class="btn-back">← Retour</a>
                    <?php if ($isLoggedIn && $user): ?>
                        <a href="logout.php" class="btn-logout">Déconnexion</a>
                    <?php else: ?>
                        <a href="<?php echo htmlspecialchars($googleAuthUrl); ?>" class="btn-logout">Se connecter</a>
                    <?php endif; ?>
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
                                <img src="assets/mascotte.png" alt="Mascotte" style="width: 100%; height: 100%; object-fit: contain;">
                            </div>
                            <h2>Salut je suis le tchatbot Linuxien !</h2>
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
                        <button id="ratingBtn" class="btn-rating" style="display: none; width: 100%; margin-top: 1rem; padding: 1rem; background: var(--bg-lighter); border: 2px solid var(--border); border-radius: 12px; color: var(--text); font-weight: 600; cursor: pointer;">
                            ⭐ Noter le bot
                        </button>
                    </div>
                    
                    <!-- Modal de notation -->
                    <div id="ratingModal" class="rating-modal" style="display: none;">
                        <div class="rating-modal-content">
                            <h2>Noter le service</h2>
                            <div class="stars-container" id="starsContainer">
                                <span class="star" data-rating="1">☆</span>
                                <span class="star" data-rating="2">☆</span>
                                <span class="star" data-rating="3">☆</span>
                                <span class="star" data-rating="4">☆</span>
                                <span class="star" data-rating="5">☆</span>
                            </div>
                            <textarea id="ratingComment" placeholder="Écrivez votre commentaire..." rows="4" style="width: 100%; margin-top: 1rem; padding: 1rem; background: var(--bg); border: 2px solid var(--border); border-radius: 8px; color: var(--text); font-family: inherit; resize: vertical;"></textarea>
                            <div class="rating-modal-actions">
                                <button id="submitRating" class="btn-submit-rating">Valider</button>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>

