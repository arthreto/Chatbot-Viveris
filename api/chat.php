<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../classes/Chat.php';
require_once '../classes/Message.php';
require_once '../classes/OpenAI.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Non authentifié']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'send_message':
        $chatId = $_POST['chat_id'] ?? null;
        $message = trim($_POST['message'] ?? '');

        if (empty($message)) {
            echo json_encode(['error' => 'Message vide']);
            exit;
        }

        // Créer un nouveau chat si nécessaire
        if (!$chatId) {
            $chatObj = new Chat($db);
            $chatId = $chatObj->create($_SESSION['user_id']);
        }

        // Sauvegarder le message de l'utilisateur
        $messageObj = new Message($db);
        $messageObj->create($chatId, 'user', $message);

        // Récupérer l'historique
        $history = $messageObj->getHistoryForAI($chatId, 10);
        $messages = array_map(function($msg) {
            return [
                'role' => $msg['role'],
                'content' => $msg['content']
            ];
        }, $history);

        // Générer la réponse de l'IA
        $openai = new OpenAI(OPENAI_API_KEY, OPENAI_MODEL);
        $response = $openai->generateResponse($messages);

        // Sauvegarder la réponse
        $messageObj->create($chatId, 'assistant', $response);

        // Mettre à jour le titre du chat si c'est le premier message
        $chatObj = new Chat($db);
        $chat = $chatObj->getById($chatId, $_SESSION['user_id']);
        if (strpos($chat['title'], 'Nouvelle conversation') === 0) {
            $title = mb_substr($message, 0, 50);
            if (mb_strlen($message) > 50) $title .= '...';
            $chatObj->updateTitle($chatId, $_SESSION['user_id'], $title);
        }

        echo json_encode([
            'success' => true,
            'chat_id' => $chatId,
            'response' => $response
        ]);
        break;

    case 'get_chats':
        $chatObj = new Chat($db);
        $chats = $chatObj->getAllByUser($_SESSION['user_id']);
        echo json_encode(['success' => true, 'chats' => $chats]);
        break;

    case 'get_messages':
        $chatId = $_GET['chat_id'] ?? null;
        if (!$chatId) {
            echo json_encode(['error' => 'Chat ID manquant']);
            exit;
        }

        $chatObj = new Chat($db);
        $chat = $chatObj->getById($chatId, $_SESSION['user_id']);
        if (!$chat) {
            echo json_encode(['error' => 'Chat non trouvé']);
            exit;
        }

        $messageObj = new Message($db);
        $messages = $messageObj->getAllByChat($chatId);
        echo json_encode(['success' => true, 'messages' => $messages, 'chat' => $chat]);
        break;

    case 'delete_chat':
        $chatId = $_POST['chat_id'] ?? null;
        if (!$chatId) {
            echo json_encode(['error' => 'Chat ID manquant']);
            exit;
        }

        $chatObj = new Chat($db);
        $success = $chatObj->delete($chatId, $_SESSION['user_id']);
        echo json_encode(['success' => $success]);
        break;

    default:
        echo json_encode(['error' => 'Action invalide']);
}
?>

