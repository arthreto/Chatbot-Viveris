# Chatbot Philosophique 🧠✨

Un chatbot moderne, responsive et animé avec une IA "complètement à côté de la plaque" mais adorée, intégrant GPT-4o-mini et l'authentification Google.

## Fonctionnalités

- 🎨 Interface moderne et fluide avec animations
- 📱 Design responsive (compatible mobile)
- 🔐 Authentification via Google OAuth
- 🤖 Intégration OpenAI (GPT-4o-mini)
- 💾 Sauvegarde des conversations en base de données
- 🎭 Mascotte animée
- 💬 Gestion de multiples conversations

## Installation

### 1. Prérequis

- PHP 7.4 ou supérieur
- Extension **PDO SQLite** activée (voir ci-dessous)
- Serveur web (Apache/Nginx) ou PHP built-in server
- Compte Google Cloud Platform (pour OAuth)
- Clé API OpenAI

### 2. Activation de SQLite

**Important :** L'extension PDO SQLite doit être activée sur votre serveur.

#### Sur Ubuntu/Debian :
```bash
sudo apt-get update
sudo apt-get install php8.3-sqlite3  # Remplacez 8.3 par votre version PHP
sudo systemctl restart php8.3-fpm     # Redémarrer PHP-FPM
```

#### Sur CentOS/RHEL :
```bash
sudo yum install php-pdo php-sqlite3
sudo systemctl restart php-fpm
```

#### Vérification :
Créez un fichier `phpinfo.php` avec le contenu suivant :
```php
<?php phpinfo(); ?>
```
Puis accédez-y via votre navigateur et cherchez "pdo_sqlite". Vous pouvez aussi utiliser le script de vérification : `check.php`

### 3. Configuration de la base de données

**Aucune configuration nécessaire !** 🎉

La base de données SQLite est créée automatiquement au premier accès. Le fichier `data/chatbot.db` sera généré automatiquement dans le dossier `data/`.

**Note :** Assurez-vous que PHP a les permissions d'écriture dans le dossier `data/`. Si le dossier n'existe pas, il sera créé automatiquement.

**Vérification rapide :** Accédez à `check.php` dans votre navigateur pour vérifier que tout est correctement configuré.

### 3. Configuration Google OAuth

1. Allez sur [Google Cloud Console](https://console.cloud.google.com/)
2. Créez un nouveau projet ou sélectionnez un projet existant
3. Activez l'API Google+ (ou Google Identity)
4. Créez des identifiants OAuth 2.0 :
   - Type : Application Web
   - URI de redirection autorisée : `http://localhost/Chatbot/callback.php`
5. Copiez le Client ID et le Client Secret

### 5. Configuration OpenAI

1. Créez un compte sur [OpenAI](https://platform.openai.com/)
2. Générez une clé API
3. Notez votre clé API

### 6. Configuration de l'application

Modifiez le fichier `config/config.php` avec vos identifiants :

```php
// Configuration Google OAuth
define('GOOGLE_CLIENT_ID', '1073543052416-oe9qtoemanh1qsv9c30q7i3jiikp4eoj.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');
define('GOOGLE_REDIRECT_URI', 'https://boulixien.velocitystudios.fr/chatbot/callback.php');

// Configuration OpenAI
define('OPENAI_API_KEY', 'VOTRE_OPENAI_API_KEY');
define('OPENAI_MODEL', 'gpt-4o-mini');
```

**Important :** Modifiez `GOOGLE_REDIRECT_URI` avec l'URL réelle de votre site en production.

### 7. Lancement

#### Avec le serveur PHP intégré :
```bash
php -S localhost:8000
```

Puis ouvrez `http://localhost:8000` dans votre navigateur.

#### Avec Apache/Nginx :
Placez les fichiers dans le répertoire de votre serveur web et accédez-y via votre navigateur.

## Structure du projet

```
Chatbot/
├── assets/
│   ├── css/
│   │   └── style.css          # Styles CSS avec animations
│   └── js/
│       └── main.js            # JavaScript pour l'interactivité
├── classes/
│   ├── User.php               # Gestion des utilisateurs
│   ├── Chat.php               # Gestion des conversations
│   ├── Message.php            # Gestion des messages
│   └── OpenAI.php             # Intégration OpenAI
├── config/
│   ├── config.php             # Configuration générale
│   └── database.php           # Connexion à la base de données
├── api/
│   └── chat.php               # API pour les requêtes AJAX
├── database.sql               # Script SQL pour créer la base
├── index.php                  # Page principale
├── callback.php               # Callback Google OAuth
├── logout.php                 # Déconnexion
└── README.md                  # Ce fichier
```

## Utilisation

1. Accédez à la page d'accueil
2. Cliquez sur "Se connecter avec Google"
3. Autorisez l'application
4. Commencez à discuter avec le chatbot philosophique !

## Personnalisation

### Modifier le prompt système

Dans `classes/OpenAI.php`, modifiez le `$systemPrompt` pour changer la personnalité du chatbot.

### Modifier les couleurs

Dans `assets/css/style.css`, modifiez les variables CSS dans `:root`.

### Modifier le modèle OpenAI

Dans `config/config.php`, changez `OPENAI_MODEL` (ex: `gpt-4`, `gpt-3.5-turbo`).

## Sécurité

- ⚠️ Ne commitez jamais vos clés API dans le dépôt Git
- ⚠️ Utilisez HTTPS en production
- ⚠️ Validez et échappez toutes les entrées utilisateur
- ⚠️ Utilisez des requêtes préparées (déjà implémenté)

## Support

Pour toute question ou problème, vérifiez :
- Les logs d'erreur PHP
- La console du navigateur (F12)
- La configuration de votre base de données
- La validité de vos clés API

## Licence

Ce projet est fourni tel quel, à des fins éducatives et de démonstration.

