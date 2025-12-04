<?php
require_once 'config/config.php';
require_once 'config/database.php';

// Fonction pour charger les images d'un dossier
function loadImagesFromFolder($folder) {
    $images = [];
    $path = __DIR__ . '/data/pictures/' . $folder;
    
    if (is_dir($path)) {
        $files = scandir($path);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..' && $file !== 'index.php') {
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $images[] = 'data/pictures/' . $folder . '/' . $file;
                }
            }
        }
    }
    
    return $images;
}

$nosLocauxImages = loadImagesFromFolder('noslocaux');
$viverisImages = loadImagesFromFolder('viveris');

// Vérifier si l'utilisateur est connecté
$isLoggedIn = isset($_SESSION['user_id']);
$user = null;

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
        // Erreur silencieuse, l'utilisateur sera considéré comme non connecté
        $isLoggedIn = false;
    }
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
    <title>Vive-vice - Service après vente TYPIQUE</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        /* Styles de base pour garantir l'affichage */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #f1f5f9;
            min-height: 100vh;
        }
    </style>
</head>
<body class="vive-vice-page">
    <!-- Topbar -->
    <nav class="topbar">
        <div class="topbar-container">
            <div class="logo">Vive-vice</div>
            <div class="nav-links">
                <a href="#" class="nav-link active" data-page="accueil">Accueil</a>
                <a href="#" class="nav-link" data-page="galerie">Galerie</a>
            </div>
            <div class="topbar-auth">
                <?php if ($isLoggedIn && $user): ?>
                    <div class="user-info-topbar">
                        <img src="<?php echo htmlspecialchars($user['picture']); ?>" alt="Avatar" class="user-avatar-topbar">
                        <span class="user-name-topbar"><?php echo htmlspecialchars($user['name']); ?></span>
                        <a href="logout.php" class="btn-logout-topbar">Déconnexion</a>
                    </div>
                <?php else: ?>
                    <a href="<?php echo htmlspecialchars($googleAuthUrl); ?>" class="btn-google">
                        <svg width="20" height="20" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                        Se connecter avec Google
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Page Accueil -->
    <div id="page-accueil" class="page active">
        <div class="page-wrapper">
        <div class="hero-section">
            <h1 class="main-title">Service après vente TYPIQUE d'un concurrent de Viveris</h1>
            <p class="subtitle">Nous engageons les meilleurs supports et offrons un service exceptionnel depuis 1986</p>
        </div>

        <div class="content-section">
            <div class="container-text">
                <h2>Notre Histoire</h2>
                <p>
                    Bienvenue chez <strong>Vive-vice</strong>, votre concurrent de longue date depuis 1986 ! 
                    Nous sommes fiers d'être le concurrent le plus... <em>persistant</em> de Viveris.
                </p>
                <p>
                    Notre spécialité ? Dès que Viveris ouvre une entreprise dans un nouveau pays ou une nouvelle ville, 
                    nous ouvrons nos propres locaux juste à côté en moins de 2 mois ! 🏢✨
                </p>
                <p>
                    C'est notre façon unique de montrer notre... <em>admiration</em> pour leur modèle d'affaires. 
                    Nous pensons que la meilleure façon de réussir est de suivre de très près nos concurrents, 
                    littéralement à quelques mètres de distance !
                </p>
                <p>
                    Depuis 1986, nous avons développé une expertise unique dans l'art de l'ouverture rapide de locaux. 
                    Notre équipe de professionnels est spécialisée dans le repérage des meilleurs emplacements... 
                    à côté de ceux de Viveris, bien sûr !
                </p>
            </div>
        </div>

        <!-- Section IA -->
        <div class="ai-section">
            <div class="ai-content">
                <h2 class="ai-title">Parlez à notre IA super intelligente</h2>
                <p class="ai-description">Découvrez notre assistant virtuel révolutionnaire, conçu pour répondre à toutes vos questions avec une précision exceptionnelle.</p>
                <?php if ($isLoggedIn && $user): ?>
                    <a href="chat.php" class="btn-ai">
                        <span>💬</span>
                        Commencer la conversation
                    </a>
                <?php else: ?>
                    <a href="<?php echo htmlspecialchars($googleAuthUrl); ?>" class="btn-ai">
                        <span>🔐</span>
                        Se connecter pour discuter
                    </a>
                <?php endif; ?>
            </div>
        </div>
        </div>
    </div>

    <!-- Page Galerie -->
    <div id="page-galerie" class="page">
        <div class="page-wrapper">
        <div class="gallery-section">
            <h2 class="gallery-title">Nos Locaux</h2>
            <div class="gallery-grid" id="gallery-noslocaux">
                <?php if (empty($nosLocauxImages)): ?>
                    <div class="gallery-empty">
                        <p>Aucune image disponible pour le moment.</p>
                        <p class="gallery-hint">Ajoutez vos images dans le dossier <code>data/pictures/noslocaux/</code></p>
                    </div>
                <?php else: ?>
                    <?php foreach ($nosLocauxImages as $image): ?>
                        <div class="gallery-item">
                            <img src="<?php echo htmlspecialchars($image); ?>" alt="Nos locaux" loading="lazy">
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="gallery-section">
            <h2 class="gallery-title">Locaux de Viveris</h2>
            <div class="gallery-grid" id="gallery-viveris">
                <?php if (empty($viverisImages)): ?>
                    <div class="gallery-empty">
                        <p>Aucune image disponible pour le moment.</p>
                        <p class="gallery-hint">Ajoutez vos images dans le dossier <code>data/pictures/viveris/</code></p>
                    </div>
                <?php else: ?>
                    <?php foreach ($viverisImages as $image): ?>
                        <div class="gallery-item">
                            <img src="<?php echo htmlspecialchars($image); ?>" alt="Locaux de Viveris" loading="lazy">
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>
