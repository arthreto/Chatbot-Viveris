# Configuration HuggingFace Inference API

## Étapes pour configurer HuggingFace

### 1. Créer un compte HuggingFace
1. Allez sur [https://huggingface.co/](https://huggingface.co/)
2. Créez un compte gratuit

### 2. Obtenir un token d'accès
1. Allez sur [https://huggingface.co/settings/tokens](https://huggingface.co/settings/tokens)
2. Cliquez sur "New token"
3. Donnez un nom à votre token (ex: "Chatbot-Viveris")
4. Sélectionnez le type "Read" (suffisant pour l'API Inference gratuite)
5. Copiez le token généré

### 3. Configurer le token dans le projet
1. Ouvrez le fichier `config/config.php`
2. Remplacez `'VOTRE_TOKEN_HUGGINGFACE'` par votre token HuggingFace
3. Sauvegardez le fichier

### 4. Choisir un modèle
Les modèles recommandés dans `config/config.php` :
- **microsoft/DialoGPT-medium** : Rapide, bon pour le chat conversationnel (par défaut)
- **mistralai/Mistral-7B-Instruct-v0.2** : Meilleure qualité, mais plus lent
- **google/flan-t5-large** : Bon pour la génération de texte général

### 5. Tester
1. Lancez votre serveur
2. Connectez-vous à votre application
3. Envoyez un message dans le chat
4. Vérifiez les logs si des erreurs apparaissent

## Notes importantes
- L'API HuggingFace Inference est **gratuite** mais peut être plus lente que ChatGPT
- Certains modèles peuvent prendre quelques secondes à charger lors du premier appel (erreur 503)
- Le code gère automatiquement le rechargement en cas d'erreur 503
- Les tokens HuggingFace sont gratuits et illimités pour l'usage personnel

