<?php
require_once 'config.php';
require_once 'session_manager.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Método não permitido'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    jsonResponse(['error' => 'Dados inválidos'], 400);
}

$requiredFields = ['id', 'text', 'sender', 'timestamp', 'userId'];
foreach ($requiredFields as $field) {
    if (!isset($input[$field]) || empty($input[$field])) {
        jsonResponse(['error' => "Campo obrigatório não informado: {$field}"], 400);
    }
}

$messageId = (int) $input['id'];
$text = sanitizeInput($input['text']);
$sender = sanitizeInput($input['sender']);
$timestamp = sanitizeInput($input['timestamp']);
$userId = sanitizeInput($input['userId']);
$fileUrl = isset($input['fileUrl']) ? sanitizeInput($input['fileUrl']) : null;

if (!in_array($sender, ['user', 'support'])) {
    jsonResponse(['error' => 'Tipo de remetente inválido'], 400);
}

$pdo = getConnection();
if (!$pdo) {
    jsonResponse(['error' => 'Erro de conexão com banco de dados'], 500);
}

createTables($pdo);

try {
    $stmt = $pdo->prepare("SELECT id FROM messages WHERE id = ?");
    $stmt->execute([$messageId]);
    
    if ($stmt->fetch()) {
        jsonResponse(['error' => 'Mensagem já existe'], 409);
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO messages (id, user_id, text, sender, timestamp, file_url) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $messageId,
        $userId,
        $text,
        $sender,
        $timestamp,
        $fileUrl
    ]);
    
    $stmt = $pdo->prepare("
        INSERT INTO users (id) VALUES (?) 
        ON DUPLICATE KEY UPDATE last_activity = CURRENT_TIMESTAMP
    ");
    $stmt->execute([$userId]);
    
    $sessionManager = getSessionManager();
    if ($sessionManager) {
        $sessionResult = $sessionManager->updateSession($userId);
        $sessionId = $sessionResult['sessionId'] ?? session_id();
    } else {
        $sessionId = session_id() ?: uniqid('session_', true);
    }
    
    jsonResponse([
        'success' => true,
        'message' => 'Mensagem enviada com sucesso',
        'messageId' => $messageId,
        'sessionId' => $sessionId
    ]);
    
} catch (PDOException $e) {
    error_log("Erro ao salvar mensagem: " . $e->getMessage());
    jsonResponse(['error' => 'Erro interno do servidor'], 500);
}
?>