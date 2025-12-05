<?php
require_once 'config/config.php';
require_once 'config/database.php';

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
    <title>Vive-vice - Service après vente TYPIQUE</title>
    <link rel="icon" type="image/png" href="assets/logo.png">
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
            background: linear-gradient(135deg, rgb(36, 59, 49) 0%, rgb(75, 137, 98) 100%);
            color: rgb(192, 208, 190);
            min-height: 100vh;
        }
    </style>
</head>
<body class="vive-vice-page">
    <!-- Topbar -->
    <nav class="topbar">
        <div class="topbar-container">
            <a href="https://boulixien.velocitystudios.fr" class="logo" title="Retour à l'accueil principal" style="text-decoration: none;">
                <img src="assets/logo.png" alt="Logo" class="logo-img">
                Vive-vice
            </a>
            <div class="nav-links">
                <a href="#" class="nav-link active" data-page="accueil">Accueil</a>
                <a href="#" class="nav-link" data-page="galerie">Galerie</a>
                <a href="#" class="nav-link" data-page="information">Information</a>
                <a href="#" class="nav-link" data-page="cgu">CGU</a>
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

        <!-- Section IA -->
        <div class="ai-section">
            <div class="ai-content">
                <h2 class="ai-title">Parlez à notre IA super intelligente</h2>
                <p class="ai-description">Découvrez notre assistant virtuel révolutionnaire, conçu pour répondre à toutes vos questions avec une précision exceptionnelle.</p>
                <a href="chat.php" class="btn-ai">
                    <span>💬</span>
                    Commencer la conversation
                </a>
            </div>
        </div>

        <!-- Section Histoire -->
        <div class="content-section">
            <div class="container-text">
                <h2>Notre Histoire</h2>
                <p>
                    Bienvenue chez <strong>Vive-vice</strong>, un élément de longue date (depuis 1986) ! 
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
        </div>
    </div>

    <!-- Page Information -->
    <div id="page-information" class="page">
        <div class="page-wrapper">
        <div class="content-section">
            <div class="container-text">
                <div style="background: rgba(16, 185, 129, 0.1); border-left: 4px solid #10b981; padding: 1rem; margin-bottom: 2rem; border-radius: 8px;">
                    <p style="margin: 0; color: #10b981; font-weight: 600;">ℹ️ Cette page est la seule page sérieuse et non-troll du site.</p>
                </div>
                <h2>Informations Techniques</h2>
                <p style="margin-bottom: 2rem;">
                    Cette page présente le fonctionnement technique de notre plateforme de chatbot, 
                    développée par l'équipe Boulixien.
                </p>
                
                <h3>Architecture du Système</h3>
                <p>
                    Notre plateforme utilise une architecture web moderne basée sur PHP pour le backend 
                    et JavaScript pour les interactions côté client. Le système de chatbot intègre l'API Groq 
                    pour la génération de réponses intelligentes.
                </p>
                
                <h3>Fonctionnement du Chatbot</h3>
                <p>
                    Le chatbot fonctionne selon un système de réponses multiples optimisé pour réduire 
                    la consommation de ressources. Voici comment cela fonctionne :
                </p>
                <ol style="margin-left: 2rem; margin-top: 1rem; line-height: 2;">
                    <li><strong>Réception du message utilisateur</strong> : L'utilisateur envoie un message via l'interface web.</li>
                    <li><strong>Génération de la réponse principale</strong> : Le système effectue un unique appel API vers Groq pour générer la première réponse intelligente.</li>
                    <li><strong>Création de variantes locales</strong> : À partir de cette réponse principale, le système génère localement 7 variantes en ajoutant des préfixes contextuels (par exemple : "Attends, j'ai trouvé mieux !", "En fait, ce que j'ai dit avant n'avait aucun sens...").</li>
                    <li><strong>Affichage progressif</strong> : Les 8 réponses (1 principale + 7 variantes) sont affichées progressivement toutes les 20 secondes, avec un indicateur de frappe pendant 5 secondes avant chaque message.</li>
                </ol>
                
                <h3>Optimisation Écologique</h3>
                <p>
                    Cette approche technique permet de réduire significativement la consommation énergétique 
                    en limitant les appels API externes. Au lieu d'effectuer 8 appels API séparés (ce qui 
                    multiplierait la consommation de ressources), notre système n'effectue qu'un seul appel 
                    et génère les variantes localement.
                </p>
                <p>
                    <strong>Bénéfices :</strong>
                </p>
                <ul style="margin-left: 2rem; margin-top: 1rem; line-height: 2;">
                    <li>Réduction de 87.5% des appels API (1 appel au lieu de 8)</li>
                    <li>Diminution de la latence réseau</li>
                    <li>Optimisation des coûts d'infrastructure</li>
                    <li>Réduction de l'empreinte carbone liée aux requêtes réseau</li>
                </ul>
                
                <h3>Stockage des Données</h3>
                <p>
                    Les conversations sont stockées de deux manières selon le statut de l'utilisateur :
                </p>
                <ul style="margin-left: 2rem; margin-top: 1rem; line-height: 2;">
                    <li><strong>Utilisateurs connectés</strong> : Les conversations sont sauvegardées dans une base de données SQLite, permettant une persistance à long terme et un accès multi-appareils.</li>
                    <li><strong>Utilisateurs invités</strong> : Les conversations sont stockées localement dans le navigateur via localStorage, offrant une expérience sans authentification tout en préservant la confidentialité.</li>
                </ul>
                
                <h3>Authentification</h3>
                <p>
                    Le système intègre l'authentification OAuth2 via Google, permettant aux utilisateurs 
                    de se connecter de manière sécurisée et d'accéder à leurs conversations depuis n'importe 
                    quel appareil.
                </p>
                
                <h3>Technologies Utilisées</h3>
                <ul style="margin-left: 2rem; margin-top: 1rem; line-height: 2;">
                    <li><strong>Backend</strong> : PHP 8+, SQLite (PDO)</li>
                    <li><strong>Frontend</strong> : HTML5, CSS3, JavaScript (ES6+)</li>
                    <li><strong>API IA</strong> : Groq API</li>
                    <li><strong>Authentification</strong> : Google OAuth2</li>
                    <li><strong>Stockage local</strong> : localStorage (Web Storage API)</li>
                </ul>
                
                <h3 style="margin-top: 3rem;">Crédits</h3>
                <p style="margin-top: 1rem;">
                    Images utilisées sur ce site :
                </p>
                <ul style="margin-left: 2rem; margin-top: 1rem; line-height: 2;">
                    <li><a href="https://jack35.wordpress.com/2012/11/21/des-chercheurs-redemarrent-un-ordinateur-vieux-de-plus-de-60-ans/" target="_blank" rel="noopener noreferrer" style="color: var(--text-muted); text-decoration: underline;">Image 1</a> - Source : jack35.wordpress.com</li>
                    <li><a href="https://www.weodeo.com/digitalisation/serveur-local-ou-datacenter-que-choisir" target="_blank" rel="noopener noreferrer" style="color: var(--text-muted); text-decoration: underline;">Image 2</a> - Source : weodeo.com</li>
                </ul>
            </div>
        </div>
        </div>
    </div>

    <!-- Page CGU -->
    <div id="page-cgu" class="page">
        <div class="page-wrapper">
        <div class="content-section">
            <div class="container-text">
                <div style="background: rgba(239, 68, 68, 0.1); border-left: 4px solid #ef4444; padding: 1rem; margin-bottom: 2rem; border-radius: 8px;">
                    <p style="margin: 0; color: #ef4444; font-weight: 600;">Cette page est une page TROLL et humoristique. Ne prenez rien au sérieux !</p>
                </div>
                <h2>Conditions Générales d'Utilisation</h2>
                <p style="margin-bottom: 2rem; font-size: 0.9rem; color: var(--text-muted);">
                    Dernière mise à jour : Jamais (on est trop occupés à vendre vos données)
                </p>
                
                <h3>1. Vente de Vos Données Personnelles</h3>
                <p>
                    En utilisant notre service, vous acceptez que nous vendions toutes vos données personnelles 
                    au plus offrant. Nous les revendons à des entreprises douteuses, des gouvernements étrangers, 
                    et même à votre voisin si il paie assez cher. Vos messages, votre historique de navigation, 
                    vos photos de chatons, tout y passe !
                </p>
                <p>
                    <strong>Prix de vente approximatif :</strong> Vos données valent environ 0,03€ sur le marché noir. 
                    On en fait une fortune, merci beaucoup !
                </p>
                
                <h3>2. Propriété Intellectuelle</h3>
                <p>
                    Tout ce que vous écrivez sur notre plateforme nous appartient désormais. Vos idées brillantes, 
                    vos poèmes, vos recettes secrètes... Tout est à nous maintenant ! On peut même les revendre 
                    comme si c'était les nôtres. C'est dans les CGU, vous avez signé !
                </p>
                
                <h3>3. Responsabilité</h3>
                <p>
                    Nous ne sommes responsables de RIEN. Si notre chatbot vous donne de mauvais conseils et que 
                    vous perdez votre emploi, c'est votre problème. Si il vous dit de manger 50 bananes par jour 
                    et que vous tombez malade, c'est encore votre problème. On s'en lave les mains !
                </p>
                
                <h3>4. Modification du Service</h3>
                <p>
                    On peut changer n'importe quoi, n'importe quand, sans vous prévenir. On peut transformer 
                    le chatbot en distributeur de bonbons, on peut vendre le site à des aliens, on peut 
                    tout simplement le fermer demain. Vous n'avez aucun recours. C'est la vie !
                </p>
                
                <h3>5. Résiliation</h3>
                <p>
                    Vous ne pouvez pas résilier votre compte. Une fois que vous êtes entré, vous êtes piégé 
                    pour l'éternité. Même après votre mort, on continuera à vendre vos données. C'est comme 
                    un abonnement Netflix, mais en pire !
                </p>
                
                <h3>6. Cookies et Traçage</h3>
                <p>
                    On utilise TOUS les cookies possibles. On vous suit partout, même dans votre salle de bain. 
                    On sait quand vous vous brossez les dents, combien de temps vous passez sur les réseaux sociaux, 
                    et on vend toutes ces infos. Big Brother, c'est nous !
                </p>
                
                <h3>7. Données Bancaires</h3>
                <p>
                    Même si on ne vous demande pas vos données bancaires, on les a quand même. On les a piratées. 
                    C'est un secret, mais maintenant vous le savez. Ne le dites à personne !
                </p>
                
                <h3>8. Clause de Non-Responsabilité Absolue</h3>
                <p>
                    Si quelque chose de mal arrive à cause de notre service (et ça arrivera), c'est 100% votre faute. 
                    On n'a rien à voir là-dedans. On est innocents comme des agneaux. Bêêê !
                </p>
                
                <h3>9. Droit Applicable</h3>
                <p>
                    Ces CGU sont régies par les lois de la République de Banania, un pays qui n'existe pas. 
                    En cas de litige, vous devrez vous battre en duel avec notre PDG. Armes autorisées : 
                    épées, haches, ou conversations philosophiques.
                </p>
                
                <h3>10. Acceptation</h3>
                <p>
                    En utilisant ce site, vous acceptez TOUT ce qui est écrit ci-dessus, même si vous ne l'avez pas lu. 
                    C'est comme ça que ça marche. On vous a eu !
                </p>
                
                <div style="background: rgba(239, 68, 68, 0.1); border: 2px dashed #ef4444; padding: 2rem; margin-top: 3rem; border-radius: 8px; text-align: center;">
                    <p style="margin: 0; font-size: 1.2rem; font-weight: 700; color: #ef4444;">
                        RAPPEL : Cette page est TROLL ! Ne prenez rien au sérieux !
                    </p>
                    <p style="margin-top: 1rem; color: var(--text-muted);">
                        En réalité, nous respectons votre vie privée et vos données sont protégées. 
                        Mais c'était marrant, non ?
                    </p>
                </div>
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
