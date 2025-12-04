<?php
session_start();

// Configuration Google OAuth
// Obtenez vos identifiants sur https://console.cloud.google.com/
define('GOOGLE_CLIENT_ID', '1073543052416-oe9qtoemanh1qsv9c30q7i3jiikp4eoj.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');
define('GOOGLE_REDIRECT_URI', 'https://boulixien.velocitystudios.fr/chatbot/callback.php');

// Configuration OpenAI
// Obtenez votre clé API sur https://platform.openai.com/
define('OPENAI_API_KEY', 'VOTRE_OPENAI_API_KEY');
define('OPENAI_MODEL', 'gpt-4o-mini');

// Configuration de base
define('BASE_URL', 'https://boulixien.velocitystudios.fr/chatbot/chatbot/');
define('SITE_NAME', 'Chatbot Philosophique');

// Timezone
date_default_timezone_set('Europe/Paris');

// Autoloader simple
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/../classes/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});
?>

