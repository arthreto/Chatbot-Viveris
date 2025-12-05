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
        $systemMessage = $systemPrompt ?: "Tu es un chatbot drôle et amusant. Tu fais des blagues de temps en temps, tu as un sens de l'humour, mais tu restes relativement cohérent avec la conversation. Tu peux faire des jeux de mots occasionnels, raconter des anecdotes amusantes, mais tu ne pars pas complètement hors sujet. Tu es intelligent mais avec une touche d'humour. Tu peux être décalé mais pas complètement incohérent. Réponds en français de manière créative, drôle mais pertinente.";

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

