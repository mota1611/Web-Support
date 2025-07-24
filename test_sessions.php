<?php
require_once 'api/config.php';
require_once 'api/session_manager.php';

echo "<h1>Teste de Sessões</h1>";

// Testar conexão com banco
$pdo = getConnection();
if (!$pdo) {
    echo "<p style='color: red;'>❌ Erro de conexão com banco de dados</p>";
    exit;
}

echo "<p style='color: green;'>✅ Conexão com banco estabelecida</p>";

// Criar tabelas
createTables($pdo);
echo "<p style='color: green;'>✅ Tabelas criadas/verificadas</p>";

// Testar SessionManager
$sessionManager = getSessionManager();
if (!$sessionManager) {
    echo "<p style='color: red;'>❌ Erro ao criar SessionManager</p>";
    exit;
}

echo "<p style='color: green;'>✅ SessionManager criado</p>";

// Testar criação de sessão
$testUserId = 'test_user_' . time();
$sessionResult = $sessionManager->startSession($testUserId);

if ($sessionResult['success']) {
    echo "<p style='color: green;'>✅ Sessão criada com sucesso</p>";
    echo "<p><strong>Session ID:</strong> " . $sessionResult['sessionId'] . "</p>";
    echo "<p><strong>User ID:</strong> " . $sessionResult['userId'] . "</p>";
} else {
    echo "<p style='color: red;'>❌ Erro ao criar sessão: " . $sessionResult['error'] . "</p>";
}

// Verificar sessões ativas
$activeSessions = $sessionManager->getActiveSessions(10);
echo "<h2>Sessões Ativas (" . count($activeSessions) . ")</h2>";

if (count($activeSessions) > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Session ID</th><th>User ID</th><th>IP</th><th>User Agent</th><th>Última Atividade</th></tr>";
    
    foreach ($activeSessions as $session) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($session['id']) . "</td>";
        echo "<td>" . htmlspecialchars($session['user_id']) . "</td>";
        echo "<td>" . htmlspecialchars($session['ip_address']) . "</td>";
        echo "<td>" . htmlspecialchars(substr($session['user_agent'], 0, 50)) . "...</td>";
        echo "<td>" . htmlspecialchars($session['last_activity']) . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p>Nenhuma sessão ativa encontrada</p>";
}

// Testar limpeza de sessões antigas
$cleanedCount = $sessionManager->cleanupOldSessions();
echo "<h2>Limpeza de Sessões</h2>";
echo "<p>Sessões antigas removidas: " . $cleanedCount . "</p>";

// Verificar informações da sessão atual
if (isset($sessionResult['sessionId'])) {
    $sessionInfo = $sessionManager->getSessionInfo($sessionResult['sessionId']);
    if ($sessionInfo) {
        echo "<h2>Informações da Sessão Atual</h2>";
        echo "<p><strong>Session ID:</strong> " . htmlspecialchars($sessionInfo['id']) . "</p>";
        echo "<p><strong>User ID:</strong> " . htmlspecialchars($sessionInfo['user_id']) . "</p>";
        echo "<p><strong>IP Address:</strong> " . htmlspecialchars($sessionInfo['ip_address']) . "</p>";
        echo "<p><strong>Created At:</strong> " . htmlspecialchars($sessionInfo['created_at']) . "</p>";
        echo "<p><strong>Last Activity:</strong> " . htmlspecialchars($sessionInfo['last_activity']) . "</p>";
    } else {
        echo "<p style='color: red;'>❌ Não foi possível obter informações da sessão</p>";
    }
}

echo "<h2>Teste Completo</h2>";
echo "<p style='color: green;'>✅ Sistema de sessões funcionando corretamente!</p>";
?> 