<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Configuração do Banco de Dados - XAMPP</h2>";

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'chat_support';

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "✅ Conectado ao MySQL com sucesso<br>";
    
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Banco de dados '$dbname' criado/verificado<br>";
    
    $pdo->exec("USE `$dbname`");
    echo "✅ Banco de dados selecionado<br>";
    
    $sql_messages = "
    CREATE TABLE IF NOT EXISTS messages (
        id BIGINT PRIMARY KEY,
        user_id VARCHAR(50) NOT NULL,
        text TEXT NOT NULL,
        sender ENUM('user', 'support') NOT NULL,
        timestamp DATETIME NOT NULL,
        file_url VARCHAR(255) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_timestamp (timestamp),
        INDEX idx_sender (sender)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    $pdo->exec($sql_messages);
    echo "✅ Tabela 'messages' criada/verificada<br>";
    
    $sql_users = "
    CREATE TABLE IF NOT EXISTS users (
        id VARCHAR(50) PRIMARY KEY,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_last_activity (last_activity)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    $pdo->exec($sql_users);
    echo "✅ Tabela 'users' criada/verificada<br>";
    
    $sql_uploads = "
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    $pdo->exec($sql_uploads);
    echo "✅ Tabela 'file_uploads' criada/verificada<br>";
    
    $sql_sessions = "
    CREATE TABLE IF NOT EXISTS sessions (
        id VARCHAR(128) PRIMARY KEY,
        user_id VARCHAR(50) NOT NULL,
        ip_address VARCHAR(45) NOT NULL,
        user_agent TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_user_id (user_id),
        INDEX idx_last_activity (last_activity)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ";
    
    $pdo->exec($sql_sessions);
    echo "✅ Tabela 'sessions' criada/verificada<br>";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM messages");
    $count = $stmt->fetch()['count'];
    
    if ($count == 0) {
        echo "📝 Inserindo dados de exemplo...<br>";
        
        $pdo->exec("INSERT INTO messages (id, user_id, text, sender, timestamp) VALUES
        (1704067200000, 'user_example', 'Olá, preciso de ajuda!', 'user', '2024-01-01 10:00:00'),
        (1704067260000, 'user_example', 'Olá! Como posso ajudá-lo hoje?', 'support', '2024-01-01 10:01:00'),
        (1704067320000, 'user_example', 'Tenho um problema com meu pedido', 'user', '2024-01-01 10:02:00'),
        (1704067380000, 'user_example', 'Entendo. Vou verificar isso para você. Pode me informar o número do pedido?', 'support', '2024-01-01 10:03:00')");
        
        $pdo->exec("INSERT INTO users (id) VALUES ('user_example')");
        
        echo "✅ Dados de exemplo inseridos<br>";
    } else {
        echo "ℹ️ Tabela já contém dados<br>";
    }
            
    $tables = ['messages', 'users', 'file_uploads', 'sessions'];
    echo "<h3>Verificação das Tabelas:</h3>";
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->fetch()) {
            $count_stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $row_count = $count_stmt->fetch()['count'];
            echo "✅ Tabela '$table' existe com $row_count registros<br>";
        } else {
            echo "❌ Tabela '$table' não foi criada<br>";
        }
    }
    
    echo "<h3>🎉 Configuração concluída com sucesso!</h3>";
    echo "<p>Agora você pode testar a API em: <a href='http://localhost/api/get_messages.php?userId=user_example' target='_blank'>http://localhost/api/get_messages.php?userId=user_example</a></p>";
    
} catch (PDOException $e) {
    echo "<h3>❌ Erro na configuração:</h3>";
    echo "<p>Erro: " . $e->getMessage() . "</p>";
    echo "<h4>Verifique:</h4>";
    echo "<ul>";
    echo "<li>Se o XAMPP está rodando (Apache e MySQL)</li>";
    echo "<li>Se o MySQL está na porta padrão (3306)</li>";
    echo "<li>Se o usuário 'root' tem permissões</li>";
    echo "<li>Se não há senha configurada para o root</li>";
    echo "</ul>";
}
?> 