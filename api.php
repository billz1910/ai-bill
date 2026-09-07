<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'config.php';

$db = getDB();

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'chats':
        // Get all chats
        echo json_encode(getChats());
        break;
        
    case 'chat':
        // Get messages for a chat
        $chatId = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($chatId) {
            echo json_encode(getMessages($chatId));
        }
        break;
        
    case 'new':
        // Create new chat
        $data = json_decode(file_get_contents('php://input'), true);
        $title = $data['title'] ?? 'New Chat';
        $chatId = createChat($title);
        echo json_encode(['id' => $chatId, 'title' => $title]);
        break;
        
    case 'delete':
        // Delete chat
        $chatId = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($chatId) {
            deleteChat($chatId);
            echo json_encode(['success' => true]);
        }
        break;
        
    case 'send':
        // Send message to AI
        $data = json_decode(file_get_contents('php://input'), true);
        $chatId = intval($data['chat_id'] ?? 0);
        $message = $data['message'] ?? '';
        $apiKey = $data['api_key'] ?? DEFAULT_API_KEY;
        $model = $data['model'] ?? DEFAULT_MODEL;
        
        if (!$chatId || !$message) {
            echo json_encode(['error' => 'Invalid request']);
            break;
        }
        
        // Save user message
        addMessage($chatId, 'user', $message);
        
        // Get chat history
        $history = getMessages($chatId);
        $messages = [];
        foreach ($history as $msg) {
            $messages[] = [
                'role' => $msg['role'],
                'content' => $msg['content']
            ];
        }
        
        // Call AI API
        $response = callAI($messages, $apiKey, $model);
        
        if ($response['success']) {
            // Save AI response
            addMessage($chatId, 'assistant', $response['content']);
            echo json_encode([
                'success' => true,
                'content' => $response['content'],
                'tokens' => $response['tokens'] ?? 0
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => $response['error']
            ]);
        }
        break;
        
    case 'settings':
        // Get or set settings
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            foreach ($data as $key => $value) {
                setSetting($key, $value);
            }
            echo json_encode(['success' => true]);
        } else {
            echo json_encode([
                'api_key' => getSetting('api_key', DEFAULT_API_KEY),
                'model' => getSetting('model', DEFAULT_MODEL),
                'max_tokens' => intval(getSetting('max_tokens', 2048)),
                'temperature' => floatval(getSetting('temperature', 0.7))
            ]);
        }
        break;
        
    default:
        echo json_encode(['error' => 'Invalid action']);
}

function callAI($messages, $apiKey, $model) {
    $url = BASE_URL . '/chat/completions';
    
    $payload = [
        'model' => $model,
        'messages' => $messages,
        'max_tokens' => intval(getSetting('max_tokens', 2048)),
        'temperature' => floatval(getSetting('temperature', 0.7))
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($response === false) {
        return ['success' => false, 'error' => 'Failed to connect to API'];
    }
    
    $data = json_decode($response, true);
    
    if ($httpCode !== 200) {
        $error = $data['error']['message'] ?? 'API error';
       
