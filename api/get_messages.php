<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['error' => 'Método não permitido'], 405);
}

$userId = isset($_GET['userId']) ? sanitizeInput($_GET['userId']) : '';
$lastId = isset($_GET['lastId']) ? (int) $_GET['lastId'] : 0;
$limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 50;

if (empty($userId)) {
    jsonResponse(['error' => 'ID do usuário é obrigatório'], 400);
}

if ($limit > 100) {
    $limit = 100;
}

$pdo = getConnection();
if (!$pdo) {
    jsonResponse(['error' => 'Erro de conexão com banco de dados'], 500);
}

createTables($pdo);

try {
    $sql = "
        SELECT id, user_id, text, sender, created_at, file_url
        FROM messages 
        WHERE user_id = ?
    ";
    
    $params = [$userId];
    
    if ($lastId > 0) {
        $sql .= " AND id > ?";
        $params[] = $lastId;
    }
    
    $sql .= " ORDER BY created_at ASC LIMIT ?";
    $params[] = $limit;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    $messages = $stmt->fetchAll();
    
    $formattedMessages = [];
    foreach ($messages as $message) {
        $formattedMessages[] = [
            'id' => (int) $message['id'],
            'text' => $message['text'],
            'sender' => $message['sender'],
            'timestamp' => $message['created_at'],
            'userId' => $message['user_id'],
            'fileUrl' => $message['file_url']
        ];
    }
    
    jsonResponse([
        'success' => true,
        'messages' => $formattedMessages,
        'count' => count($formattedMessages)
    ]);
    
} catch (PDOException $e) {
    error_log("Erro ao buscar mensagens: " . $e->getMessage());
    jsonResponse(['error' => 'Erro interno do servidor'], 500);
}
?>