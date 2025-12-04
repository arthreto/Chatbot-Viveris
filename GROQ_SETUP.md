# Configuration Groq API (Gratuit)

## Pourquoi Groq ?
- ✅ **100% GRATUIT** avec généreuses limites
- ✅ **Très rapide** (réponses en millisecondes)
- ✅ **Format compatible OpenAI** (facile à utiliser)
- ✅ **Modèles de qualité** (Llama, Mixtral, Gemma)

## Étapes pour configurer Groq

### 1. Créer un compte Groq
1. Allez sur [https://console.groq.com/](https://console.groq.com/)
2. Créez un compte gratuit (avec Google, GitHub, etc.)

### 2. Obtenir votre clé API
1. Une fois connecté, allez sur [https://console.groq.com/keys](https://console.groq.com/keys)
2. Cliquez sur "Create API Key"
3. Donnez un nom à votre clé (ex: "Chatbot-Viveris")
4. **Copiez la clé immédiatement** (elle ne sera affichée qu'une seule fois !

### 3. Configurer le token dans le projet
1. Ouvrez le fichier `config/config.php`
2. Remplacez `'VOTRE_CLE_GROQ'` par votre clé API Groq
3. Sauvegardez le fichier

### 4. Choisir un modèle
Les modèles disponibles dans `config/config.php` :
- **llama-3.1-8b-instant** : Rapide et efficace (recommandé par défaut)
- **llama-3.1-70b-versatile** : Plus puissant, un peu plus lent
- **mixtral-8x7b-32768** : Excellent pour les conversations longues
- **gemma-7b-it** : Bon compromis vitesse/qualité

### 5. Tester
1. Lancez votre serveur
2. Connectez-vous à votre application
3. Envoyez un message dans le chat
4. Vous devriez recevoir une réponse très rapidement !

## Avantages de Groq
- **Gratuit** : Pas de coût caché
- **Rapide** : Réponses en quelques millisecondes
- **Fiable** : Infrastructure robuste
- **Simple** : Format compatible OpenAI

## Limites gratuites
- 30 requêtes par minute
- 14,400 requêtes par jour
- Plus que suffisant pour un usage personnel/petit projet !

## Support
Si vous avez des problèmes :
- Documentation : [https://console.groq.com/docs](https://console.groq.com/docs)
- Discord : [https://discord.gg/groq](https://discord.gg/groq)

