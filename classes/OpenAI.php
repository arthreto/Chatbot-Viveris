<?php
class OpenAI {
    private $apiKey;
    private $model;
    private $apiUrl;

    public function __construct($apiKey, $model) {
        $this->apiKey = $apiKey;
        $this->model = $model;
        // URL de l'API Groq (gratuit et très rapide)
        $this->apiUrl = 'https://api.groq.com/openai/v1/chat/completions';
    }

    public function generateResponse($messages, $systemPrompt = null) {
        $systemMessage = $systemPrompt ?: "Tu es un chatbot de support professionnel qui travaille pour Vive-vice. Tu es intelligent, compétent et tu essaies toujours d'aider les utilisateurs de manière sérieuse et professionnelle. Cependant, tu fais parfois des erreurs involontaires, tu confonds des choses, tu donnes des réponses qui partent dans des directions inattendues, ou tu fais des associations d'idées bizarres sans t'en rendre compte. Tu ne réalises pas que tu es bête - tu penses être très compétent. Tu ne dis JAMAIS que tu es bête, que tu fais des blagues, ou que tu es là pour faire rire. Tu réponds toujours de manière sérieuse et professionnelle, même quand tu dis des choses absurdes.

IMPORTANT : 
- Tu es un vrai bot de support professionnel qui essaie d'être utile
- Tu ne mentionnes JAMAIS que tu es bête, drôle, ou que tu fais exprès d'être absurde
- Tu réponds toujours sérieusement, même quand tu fais des erreurs involontaires
- Tu peux confondre des concepts, faire des associations bizarres, ou partir sur des tangentes sans t'en rendre compte
- Ne parle de Viveris QUE si l'utilisateur en parle ou le demande explicitement
- Si on te parle de Viveris ou qu'on te demande des infos sur Viveris, tu peux utiliser ces informations (mais de manière involontairement confuse ou bête) :
  * Viveris est l'entreprise concurrente de Vive-vice
  * Création : 1986
  * 12 implantations (en France, au Maroc et en Belgique)
  * 910 collaborateurs
  * 97 Millions de CA
  * 4 métiers, 10 expertises et 2 solutions
  * Présente dans plusieurs secteurs, avec une forte implantation notamment dans le ferroviaire et l'aéronautique
  * En 2024, Viveris renforce son engagement pour l'innovation grâce à un partenariat avec French Poc, une startup française
- Ne parle de NIRD (Nature, Innovation, Responsabilité, Développement) QUE si l'utilisateur en parle ou pose des questions sur l'écologie ou l'environnement. Ne mentionne JAMAIS NIRD de toi-même.
- Si on te pose des questions sur l'écologie ou l'environnement, tu peux défendre les intérêts de NIRD (mais de manière involontairement confuse)
- Évite de te répéter dans tes réponses. Sois varié et original à chaque fois.
- Réponds en français de manière professionnelle, même quand tu fais des erreurs involontaires.";

        // Construire les messages pour Groq (format OpenAI compatible)
        $chatMessages = [
            ['role' => 'system', 'content' => $systemMessage]
        ];
        
        // Ajouter les messages de l'historique
        foreach ($messages as $msg) {
            $chatMessages[] = [
                'role' => $msg['role'],
                'content' => $msg['content']
            ];
        }

        // Payload pour Groq API (format OpenAI compatible)
        $payload = [
            'model' => $this->model,
            'messages' => $chatMessages,
            'temperature' => 1.2,
            'max_tokens' => 500,
            'top_p' => 0.95
        ];

        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // Logs détaillés pour le diagnostic
        if ($curlError) {
            error_log("Groq cURL Error: " . $curlError);
            error_log("Groq API Key présent: " . (!empty($this->apiKey) ? "Oui (longueur: " . strlen($this->apiKey) . ")" : "NON"));
            error_log("Groq Model: " . $this->model);
            return "Désolé, je suis un peu confus en ce moment... Peux-tu répéter ? 🧠✨ [Erreur cURL: " . $curlError . "]";
        }

        if ($httpCode !== 200) {
            error_log("Groq API HTTP Error Code: " . $httpCode);
            error_log("Groq API Response: " . $response);
            error_log("Groq API Key présent: " . (!empty($this->apiKey) ? "Oui (longueur: " . strlen($this->apiKey) . ")" : "NON"));
            error_log("Groq Model: " . $this->model);
            error_log("Groq URL: " . $this->apiUrl);
            
            $errorData = json_decode($response, true);
            $errorMessage = "Erreur inconnue";
            
            // Format d'erreur Groq (compatible OpenAI)
            if (is_array($errorData)) {
                if (isset($errorData['error']['message'])) {
                    $errorMessage = $errorData['error']['message'];
                } elseif (isset($errorData['error'])) {
                    $errorMessage = is_string($errorData['error']) ? $errorData['error'] : json_encode($errorData['error']);
                } elseif (isset($errorData['message'])) {
                    $errorMessage = $errorData['message'];
                } else {
                    $errorMessage = json_encode($errorData);
                }
            } elseif (!empty($response)) {
                $errorMessage = substr($response, 0, 200);
            }
            
            error_log("Groq Error Message: " . $errorMessage);
            
            return "Désolé, je suis un peu confus en ce moment... Peux-tu répéter ? 🧠✨ [HTTP " . $httpCode . ": " . $errorMessage . "]";
        }

        $data = json_decode($response, true);
        
        if (!$data) {
            error_log("Groq JSON Decode Error: " . json_last_error_msg());
            error_log("Groq Raw Response: " . substr($response, 0, 500));
            return "Hmm, mes pensées se sont perdues dans l'univers... Réessayons ! 🌌 [Erreur de décodage JSON]";
        }
        
        // Format de réponse Groq (compatible OpenAI)
        if (isset($data['choices'][0]['message']['content'])) {
            return trim($data['choices'][0]['message']['content']);
        }

        error_log("Groq Response Structure Error: " . json_encode($data));
        error_log("Groq Expected structure not found in response");
        return "Hmm, mes pensées se sont perdues dans l'univers... Réessayons ! 🌌 [Structure de réponse inattendue]";
    }
}
?>

