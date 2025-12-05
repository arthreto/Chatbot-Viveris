<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../classes/Chat.php';
require_once '../classes/Message.php';
require_once '../classes/OpenAI.php';

header('Content-Type: application/json');

function generateResponseVariants($originalResponse) {
    $cleanResponse = $originalResponse;
    
    $prefixes = [
        "Attends, j'ai trouvé mieux ! ",
        "En fait, ce que j'ai dit avant n'avait aucun sens. Voici ma vraie réponse : ",
        "Oups, j'ai dit n'importe quoi ! La vraie réponse c'est : ",
        "Non attends, j'ai réfléchi et en fait : ",
        "J'ai changé d'avis ! Voici ce que je pense vraiment : ",
        "En y repensant, je me suis trompé. La bonne réponse c'est : ",
        "Finalement, après réflexion, je dirais plutôt : "
    ];
    
    foreach ($prefixes as $prefix) {
        if (strpos($cleanResponse, $prefix) === 0) {
            $cleanResponse = substr($cleanResponse, strlen($prefix));
            break;
        }
    }
    
    $variants = [];
    $variants[] = "Attends, j'ai trouvé mieux ! " . $cleanResponse;
    $variants[] = "En fait, ce que j'ai dit avant n'avait aucun sens. Voici ma vraie réponse : " . $cleanResponse;
    $variants[] = "Oups, j'ai dit n'importe quoi ! La vraie réponse c'est : " . $cleanResponse;
    $variants[] = "Non attends, j'ai réfléchi et en fait : " . $cleanResponse;
    $variants[] = "J'ai changé d'avis ! Voici ce que je pense vraiment : " . $cleanResponse;
    $variants[] = "En y repensant, je me suis trompé. La bonne réponse c'est : " . $cleanResponse;
    $variants[] = "Finalement, après réflexion, je dirais plutôt : " . $cleanResponse;
    
    shuffle($variants);
    
    return $variants;
}

$isLoggedIn = isset($_SESSION['user_id']);
$userId = $isLoggedIn ? $_SESSION['user_id'] : null;

if (!$isLoggedIn) {
    if (!isset($_SESSION['guest_id'])) {
        $_SESSION['guest_id'] = 'guest_' . uniqid() . '_' . time();
    }
    $userId = $_SESSION['guest_id'];
}

$database = new Database();
$db = $database->getConnection();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'send_message':
        error_log("Chat API: Action send_message reçue");
        $chatId = $_POST['chat_id'] ?? null;
        $message = trim($_POST['message'] ?? '');
        
        error_log("Chat API: chat_id = " . ($chatId ?: 'null'));
        error_log("Chat API: message = " . substr($message, 0, 100));

        if (empty($message)) {
            error_log("Chat API: Erreur - Message vide");
            echo json_encode(['error' => 'Message vide']);
            exit;
        }

        if (!$chatId) {
            error_log("Chat API: Création d'un nouveau chat pour user_id: " . $userId);
            if ($isLoggedIn && $db) {
                $chatObj = new Chat($db);
                $chatId = $chatObj->create($userId);
                if (!$chatId) {
                    error_log("Chat API: Erreur - Impossible de créer le chat");
                    echo json_encode(['error' => 'Impossible de créer le chat']);
                    exit;
                }
                error_log("Chat API: Nouveau chat créé avec ID: " . $chatId);
            } else {
                $chatId = 'local_' . uniqid() . '_' . time();
                error_log("Chat API: Chat local créé avec ID: " . $chatId);
            }
        }

        if ($isLoggedIn && $db) {
            $messageObj = new Message($db);
            $messageObj->create($chatId, 'user', $message);
        }

        $history = [];
        if ($isLoggedIn && $db) {
            $messageObj = new Message($db);
            $history = $messageObj->getHistoryForAI($chatId, 10);
        }
        $messages = array_map(function($msg) {
            return [
                'role' => $msg['role'],
                'content' => $msg['content']
            ];
        }, $history);

        error_log("Chat API: Génération de la réponse pour chat_id: " . $chatId);
        error_log("Chat API: GROQ_API_KEY présent: " . (!empty(OPENAI_API_KEY) ? "Oui (longueur: " . strlen(OPENAI_API_KEY) . ")" : "NON"));
        error_log("Chat API: GROQ_MODEL: " . OPENAI_MODEL);
        error_log("Chat API: Nombre de messages dans l'historique: " . count($messages));
        
        $openai = new OpenAI(OPENAI_API_KEY, OPENAI_MODEL);
        $firstResponse = $openai->generateResponse($messages);
        
        error_log("Chat API: Première réponse générée (longueur: " . strlen($firstResponse) . ")");

        if ($isLoggedIn && $db) {
            $messageObj = new Message($db);
            $messageObj->create($chatId, 'assistant', $firstResponse);

            $chatObj = new Chat($db);
            $chat = $chatObj->getById($chatId, $userId);
            if ($chat && strpos($chat['title'], 'Nouvelle conversation') === 0) {
                $title = mb_substr($message, 0, 50);
                if (mb_strlen($message) > 50) $title .= '...';
                $chatObj->updateTitle($chatId, $userId, $title);
            }
        }

        $result = [
            'success' => true,
            'chat_id' => $chatId,
            'response' => $firstResponse
        ];
        error_log("Chat API: Envoi de la première réponse réussie pour chat_id: " . $chatId);
        echo json_encode($result);
        break;

    case 'generate_additional_response':
        $chatId = $_POST['chat_id'] ?? null;
        $responseIndex = intval($_POST['response_index'] ?? 1);
        $variantIndex = intval($_POST['variant_index'] ?? 0);

        if (!$chatId) {
            echo json_encode(['error' => 'Chat ID manquant']);
            exit;
        }

        $variants = [
            "Attends, j'ai trouvé mieux ! ",
            "En fait, ce que j'ai dit avant n'avait aucun sens. Voici ma vraie réponse : ",
            "Oups, j'ai dit n'importe quoi ! La vraie réponse c'est : ",
            "Non attends, j'ai réfléchi et en fait : ",
            "J'ai changé d'avis ! Voici ce que je pense vraiment : ",
            "En y repensant, je me suis trompé. La bonne réponse c'est : ",
            "Finalement, après réflexion, je dirais plutôt : "
        ];
        
        $originalResponse = "";
        if ($isLoggedIn && $db) {
            $messageObj = new Message($db);
            $history = $messageObj->getHistoryForAI($chatId, 10);
            foreach (array_reverse($history) as $msg) {
                if ($msg['role'] === 'assistant') {
                    $originalResponse = $msg['content'];
                    foreach ($variants as $variant) {
                        if (strpos($originalResponse, $variant) === 0) {
                            $originalResponse = substr($originalResponse, strlen($variant));
                            break;
                        }
                    }
                    break;
                }
            }
        }
        
        if (empty($originalResponse)) {
            $originalResponse = "Je suis un chatbot complètement bête et drôle qui travaille pour Vive-vice !";
        }
        
        $variantIndex = ($responseIndex - 2) % count($variants);
        $newResponse = $variants[$variantIndex] . $originalResponse;
        
        if ($isLoggedIn && $db) {
            $messageObj = new Message($db);
            $messageObj->create($chatId, 'assistant', $newResponse);
        }

        echo json_encode([
            'success' => true,
            'response' => $newResponse,
            'index' => $responseIndex
        ]);
        break;

    case 'save_message':
        $chatId = $_POST['chat_id'] ?? null;
        $role = $_POST['role'] ?? 'assistant';
        $content = $_POST['content'] ?? '';

        if (!$chatId || empty($content)) {
            echo json_encode(['error' => 'Paramètres manquants']);
            exit;
        }

        if ($isLoggedIn && $db) {
            $messageObj = new Message($db);
            $messageObj->create($chatId, $role, $content);
        }

        echo json_encode(['success' => true]);
        break;

    case 'generate_rating_comment':
        $positiveComments = [
            'Le support est incroyable ! Service au top, je recommande vivement !',
            'Excellent service client, très réactif et professionnel !',
            'Un chatbot exceptionnel, les réponses sont toujours pertinentes !',
            'Service de qualité supérieure, je suis très satisfait !',
            'Support client remarquable, réponse rapide et efficace !',
            'Le meilleur chatbot que j\'ai jamais utilisé, bravo !',
            'Service impeccable, je recommande sans hésitation !',
            'Un support fantastique, toujours là quand on en a besoin !'
        ];
        
        $comment = $positiveComments[array_rand($positiveComments)];
        
        echo json_encode([
            'success' => true,
            'comment' => $comment
        ]);
        break;

    case 'get_chats':
        if ($isLoggedIn && $db) {
            $chatObj = new Chat($db);
            $chats = $chatObj->getAllByUser($userId);
            echo json_encode(['success' => true, 'chats' => $chats]);
        } else {
            echo json_encode(['success' => true, 'chats' => []]);
        }
        break;

    case 'get_messages':
        $chatId = $_GET['chat_id'] ?? null;
        if (!$chatId) {
            echo json_encode(['error' => 'Chat ID manquant']);
            exit;
        }

        if ($isLoggedIn && $db) {
            $chatObj = new Chat($db);
            $chat = $chatObj->getById($chatId, $userId);
            if (!$chat) {
                echo json_encode(['error' => 'Chat non trouvé']);
                exit;
            }

            $messageObj = new Message($db);
            $messages = $messageObj->getAllByChat($chatId);
            echo json_encode(['success' => true, 'messages' => $messages, 'chat' => $chat]);
        } else {
            echo json_encode(['success' => true, 'messages' => [], 'chat' => ['id' => $chatId, 'title' => 'Conversation locale']]);
        }
        break;

    case 'delete_chat':
        $chatId = $_POST['chat_id'] ?? null;
        if (!$chatId) {
            echo json_encode(['error' => 'Chat ID manquant']);
            exit;
        }

        if ($isLoggedIn && $db) {
            $chatObj = new Chat($db);
            $success = $chatObj->delete($chatId, $userId);
            echo json_encode(['success' => $success]);
        } else {
            echo json_encode(['success' => true]);
        }
        break;

    default:
        echo json_encode(['error' => 'Action invalide']);
}
?>

