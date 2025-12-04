<?php
require_once 'config/config.php';
require_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if (!$db) {
        throw new Exception("Impossible de se connecter à la base de données.");
    }
} catch (Exception $e) {
    // Afficher une page d'erreur claire
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Erreur de configuration - Chatbot Philosophique</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
                background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
                color: #f1f5f9;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 2rem;
            }
            .error-container {
                max-width: 600px;
                background: #1e293b;
                padding: 2rem;
                border-radius: 12px;
                border: 2px solid #ef4444;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            }
            h1 {
                color: #ef4444;
                margin-bottom: 1rem;
                font-size: 1.5rem;
            }
            .error-message {
                background: #334155;
                padding: 1rem;
                border-radius: 8px;
                margin: 1rem 0;
                border-left: 4px solid #ef4444;
            }
            .solution {
                background: #334155;
                padding: 1rem;
                border-radius: 8px;
                margin-top: 1rem;
                border-left: 4px solid #10b981;
            }
            .solution h3 {
                color: #10b981;
                margin-bottom: 0.5rem;
            }
            code {
                background: #0f172a;
                padding: 0.25rem 0.5rem;
                border-radius: 4px;
                font-family: 'Courier New', monospace;
                color: #f59e0b;
            }
            ul {
                margin-left: 1.5rem;
                margin-top: 0.5rem;
            }
            li {
                margin: 0.5rem 0;
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <h1>⚠️ Erreur de configuration de la base de données</h1>
            <div class="error-message">
                <strong>Erreur :</strong><br>
                <?php echo htmlspecialchars($e->getMessage()); ?>
            </div>
            <div class="solution">
                <h3>🔧 Solution : Activer l'extension SQLite</h3>
                <p>L'extension PDO SQLite n'est pas activée sur votre serveur. Voici comment l'activer :</p>
                <ul>
                    <li><strong>Sur Ubuntu/Debian :</strong><br>
                        <code>sudo apt-get install php8.3-sqlite3</code><br>
                        Puis redémarrer PHP-FPM : <code>sudo systemctl restart php8.3-fpm</code>
                    </li>
                    <li><strong>Sur CentOS/RHEL :</strong><br>
                        <code>sudo yum install php-pdo php-sqlite3</code><br>
                        Puis redémarrer PHP-FPM
                    </li>
                    <li><strong>Vérification :</strong><br>
                        Créez un fichier <code>phpinfo.php</code> avec <code>&lt;?php phpinfo(); ?&gt;</code> et cherchez "pdo_sqlite"
                    </li>
                </ul>
                <p style="margin-top: 1rem;"><strong>Alternative :</strong> Si vous ne pouvez pas activer SQLite, vous pouvez utiliser MySQL en modifiant <code>config/database.php</code></p>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Vérifier si l'utilisateur est connecté
$isLoggedIn = isset($_SESSION['user_id']);
$user = null;

if ($isLoggedIn) {
    require_once 'classes/User.php';
    $userObj = new User($db);
    $user = $userObj->getById($_SESSION['user_id']);
}

// URL de connexion Google
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
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <?php if (!$isLoggedIn): ?>
            <!-- Page d'accueil non connecté -->
            <div class="welcome-screen">
                <div class="mascot-container">
                    <div class="mascot" id="mascot">
                        <div class="mascot-face">
                            <div class="eye left-eye"></div>
                            <div class="eye right-eye"></div>
                            <div class="mouth"></div>
                        </div>
                    </div>
                </div>
                <h1 class="welcome-title animate-fade-in">Veux-tu discuter avec un chatbot intelligent, révolutionnaire et philosophe ?</h1>
                <p class="welcome-subtitle animate-fade-in-delay">Un chatbot complètement à côté de la plaque mais adoré ! 🧠✨</p>
                <a href="<?php echo htmlspecialchars($googleAuthUrl); ?>" class="btn-google animate-bounce-in">
                    <svg width="20" height="20" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    Se connecter avec Google
                </a>
            </div>
        <?php else: ?>
            <!-- Interface principale du chat -->
            <div class="chat-container">
                <header class="chat-header">
                    <div class="user-info">
                        <img src="<?php echo htmlspecialchars($user['picture']); ?>" alt="Avatar" class="user-avatar">
                        <span class="user-name"><?php echo htmlspecialchars($user['name']); ?></span>
                    </div>
                    <a href="logout.php" class="btn-logout">Déconnexion</a>
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
        <?php endif; ?>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>

