<?php
require_once 'config.php';
require_once 'session_manager.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['error' => 'Método não permitido'], 405);
}

$pdo = getConnection();
if (!$pdo) {
    jsonResponse(['error' => 'Erro de conexão com banco de dados'], 500);
}

createTables($pdo);

try {
    $sessionManager = getSessionManager();
    if (!$sessionManager) {
        jsonResponse(['error' => 'Erro ao inicializar gerenciador de sessões'], 500);
    }
    
    $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 50;
    if ($limit > 100) {
        $limit = 100;
    }
    
    $sessions = $sessionManager->getActiveSessions($limit);
    
    jsonResponse([
        'success' => true,
        'sessions' => $sessions,
        'count' => count($sessions)
    ]);
    
} catch (Exception $e) {
    error_log("Erro ao buscar sessões: " . $e->getMessage());
    jsonResponse(['error' => 'Erro interno do servidor'], 500);
}
?> 