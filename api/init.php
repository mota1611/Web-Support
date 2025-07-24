<?php
require_once 'config.php';

function initializeSystem() {
    $pdo = getConnection();
    if (!$pdo) {
        return ['success' => false, 'error' => 'Erro de conexão com banco de dados'];
    }
    
    try {
        $sql = "
        CREATE TABLE IF NOT EXISTS messages (
            id BIGINT PRIMARY KEY,
            user_id VARCHAR(50) NOT NULL,
            text TEXT NOT NULL,
            sender ENUM('user', 'support') NOT NULL,
            timestamp DATETIME NOT NULL,
            file_url VARCHAR(255) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_timestamp (timestamp)
        );
        
        CREATE TABLE IF NOT EXISTS users (
            id VARCHAR(50) PRIMARY KEY,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_last_activity (last_activity)
        );
        
        CREATE TABLE IF NOT EXISTS file_uploads (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id VARCHAR(50) NOT NULL,
            original_name VARCHAR(255) NOT NULL,
            file_name VARCHAR(255) NOT NULL,
            file_url VARCHAR(255) NOT NULL,
            file_size INT NOT NULL,
            upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_upload_date (upload_date)
        );
        ";
        
        $pdo->exec($sql);
        
        ensureUploadDir();
        
        return ['success' => true, 'message' => 'Sistema inicializado com sucesso'];
        
    } catch (PDOException $e) {
        error_log("Erro na inicialização: " . $e->getMessage());
        return ['success' => false, 'error' => 'Erro ao criar tabelas: ' . $e->getMessage()];
    }
}

function checkSystemStatus() {
    $pdo = getConnection();
    if (!$pdo) {
        return ['status' => 'error', 'message' => 'Banco de dados não acessível'];
    }
    
    try {
        $tables = ['messages', 'users', 'file_uploads'];
        $missingTables = [];
        
        foreach ($tables as $table) {
            $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$table]);
            if (!$stmt->fetch()) {
                $missingTables[] = $table;
            }
        }
        
        if (!empty($missingTables)) {
            return ['status' => 'warning', 'message' => 'Tabelas faltando: ' . implode(', ', $missingTables)];
        }
        
        if (!file_exists(UPLOAD_DIR)) {
            return ['status' => 'warning', 'message' => 'Diretório de uploads não existe'];
        }
        
        if (!is_writable(UPLOAD_DIR)) {
            return ['status' => 'error', 'message' => 'Diretório de uploads não é gravável'];
        }
        
        return ['status' => 'ok', 'message' => 'Sistema funcionando corretamente'];
        
    } catch (PDOException $e) {
        return ['status' => 'error', 'message' => 'Erro ao verificar sistema: ' . $e->getMessage()];
    }
}

if (basename($_SERVER['SCRIPT_NAME']) === 'init.php') {
    $result = initializeSystem();
    jsonResponse($result);
}
?>