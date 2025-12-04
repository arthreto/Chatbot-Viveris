<?php
class OpenAI {
    private $apiKey;
    private $model;
    private $apiUrl = 'https://api.openai.com/v1/chat/completions';

    public function __construct($apiKey, $model) {
        $this->apiKey = $apiKey;
        $this->model = $model;
    }

    public function generateResponse($messages, $systemPrompt = null) {
        $systemMessage = $systemPrompt ?: "Tu es un chatbot philosophique complètement à côté de la plaque mais adoré. Tu réponds de manière absurde, drôle et philosophique. Tu es intelligent mais tu interprètes tout de manière totalement décalée. Tu es bienveillant et amusant. Réponds en français de manière créative et humoristique.";

        $payload = [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemMessage],
                ...$messages
            ],
            'temperature' => 1.2,
            'max_tokens' => 500
        ];

        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->apiKey
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log("OpenAI API Error: " . $response);
            return "Désolé, je suis un peu confus en ce moment... Peux-tu répéter ? 🧠✨";
        }

        $data = json_decode($response, true);
        
        if (isset($data['choices'][0]['message']['content'])) {
            return $data['choices'][0]['message']['content'];
        }

        return "Hmm, mes pensées se sont perdues dans l'univers... Réessayons ! 🌌";
    }
}
?>

