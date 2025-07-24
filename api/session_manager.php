<?php
require_once 'config.php';

class SessionManager {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function startSession($userId) {
        if (!session_id()) {
            session_start();
        }
        
        $sessionId = session_id();
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO sessions (id, user_id, ip_address, user_agent) 
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                    last_activity = CURRENT_TIMESTAMP,
                    ip_address = VALUES(ip_address),
                    user_agent = VALUES(user_agent)
            ");
            
            $stmt->execute([$sessionId, $userId, $ipAddress, $userAgent]);
            
            return [
                'success' => true,
                'sessionId' => $sessionId,
                'userId' => $userId
            ];
        } catch (PDOException $e) {
            error_log("Erro ao iniciar sessão: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Erro ao iniciar sessão'
            ];
        }
    }
    
    public function updateSession($userId) {
        if (!session_id()) {
            return $this->startSession($userId);
        }
        
        $sessionId = session_id();
        
        try {
            $stmt = $this->pdo->prepare("
                UPDATE sessions 
                SET last_activity = CURRENT_TIMESTAMP 
                WHERE id = ? AND user_id = ?
            ");
            
            $stmt->execute([$sessionId, $userId]);
            
            return [
                'success' => true,
                'sessionId' => $sessionId,
                'userId' => $userId
            ];
        } catch (PDOException $e) {
            error_log("Erro ao atualizar sessão: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Erro ao atualizar sessão'
            ];
        }
    }
    
    public function getActiveSessions($limit = 50) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT s.id, s.user_id, s.ip_address, s.user_agent, 
                       s.created_at, s.last_activity,
                       u.last_activity as user_last_activity
                FROM sessions s
                LEFT JOIN users u ON s.user_id = u.id
                WHERE s.last_activity > DATE_SUB(NOW(), INTERVAL 30 MINUTE)
                ORDER BY s.last_activity DESC
                LIMIT ?
            ");
            
            $stmt->execute([$limit]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erro ao buscar sessões ativas: " . $e->getMessage());
            return [];
        }
    }
    
    public function cleanupOldSessions() {
        try {
            $stmt = $this->pdo->prepare("
                DELETE FROM sessions 
                WHERE last_activity < DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ");
            
            $stmt->execute();
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Erro ao limpar sessões antigas: " . $e->getMessage());
            return 0;
        }
    }
    
    public function getSessionInfo($sessionId) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT s.*, u.last_activity as user_last_activity
                FROM sessions s
                LEFT JOIN users u ON s.user_id = u.id
                WHERE s.id = ?
            ");
            
            $stmt->execute([$sessionId]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Erro ao buscar informações da sessão: " . $e->getMessage());
            return null;
        }
    }
}

// Função helper para usar o SessionManager
function getSessionManager() {
    $pdo = getConnection();
    if (!$pdo) {
        return null;
    }
    
    createTables($pdo);
    return new SessionManager($pdo);
}
?> 