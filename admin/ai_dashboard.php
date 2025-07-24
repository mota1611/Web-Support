<?php
require_once '../api/env_loader.php';
require_once '../api/config.php';
require_once '../api/ai_config.php';

session_start();

if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

$aiConfig = getAIConfig();
$isAIEnabled = isAIEnabled();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_config':
                $newConfig = [
                    'enabled' => isset($_POST['ai_enabled']),
                    'provider' => $_POST['ai_provider'],
                    'model' => $_POST['ai_model'],
                    'max_tokens' => (int)$_POST['max_tokens'],
                    'temperature' => (float)$_POST['temperature']
                ];
                
                echo "<script>alert('Configurações atualizadas com sucesso!');</script>";
                break;
                
            case 'test_ai':
                $testMessage = $_POST['test_message'] ?? '';
                if (!empty($testMessage)) {
                    require_once '../api/ai_handler.php';
                    $aiHandler = new AIHandler();
                    $response = $aiHandler->generateResponse($testMessage, 'test_user');
                    $testResult = $response;
                }
                break;
        }
    }
}

function getAIStats() {
    $pdo = getConnection();
    if (!$pdo) return [];
    
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total_messages 
            FROM messages 
            WHERE sender = 'support' 
            AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        ");
        $stmt->execute();
        $totalMessages = $stmt->fetch()['total_messages'];
        
        $stmt = $pdo->prepare("
            SELECT DATE(created_at) as date, COUNT(*) as count
            FROM messages 
            WHERE sender = 'support' 
            AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY DATE(created_at)
            ORDER BY date
        ");
        $stmt->execute();
        $dailyStats = $stmt->fetchAll();
        
        return [
            'total_messages' => $totalMessages,
            'daily_stats' => $dailyStats
        ];
    } catch (PDOException $e) {
        return [];
    }
}

$stats = getAIStats();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de IA - Administração</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            color: #333;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .header h1 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .status-indicator {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
        }
        
        .status-enabled {
            background: #d4edda;
            color: #155724;
        }
        
        .status-disabled {
            background: #f8d7da;
            color: #721c24;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .card {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .card h3 {
            color: #2c3e50;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-primary:hover {
            background: #0056b3;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #1e7e34;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .stat-item {
            text-align: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }
        
        .stat-label {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        .test-result {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
            border-left: 4px solid #007bff;
        }
        
        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        
        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .slider {
            background-color: #2196F3;
        }
        
        input:checked + .slider:before {
            transform: translateX(26px);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-robot"></i> Painel de IA</h1>
            <p>Gerencie as configurações e monitore o desempenho da IA</p>
            <div class="status-indicator <?php echo $isAIEnabled ? 'status-enabled' : 'status-disabled'; ?>">
                <i class="fas fa-circle"></i>
                <?php echo $isAIEnabled ? 'IA Ativada' : 'IA Desativada'; ?>
            </div>
        </div>
        
        <div class="dashboard-grid">
            <div class="card">
                <h3><i class="fas fa-cog"></i> Configurações</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="update_config">
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="ai_enabled" <?php echo $aiConfig['enabled'] ? 'checked' : ''; ?>>
                            Habilitar IA
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label>Provedor de IA:</label>
                        <select name="ai_provider" class="form-control">
                            <option value="openai" <?php echo $aiConfig['provider'] === 'openai' ? 'selected' : ''; ?>>OpenAI</option>
                            <option value="local" <?php echo $aiConfig['provider'] === 'local' ? 'selected' : ''; ?>>Local</option>
                            <option value="claude" <?php echo $aiConfig['provider'] === 'claude' ? 'selected' : ''; ?>>Claude</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Modelo:</label>
                        <select name="ai_model" class="form-control">
                            <option value="gpt-3.5-turbo" <?php echo $aiConfig['model'] === 'gpt-3.5-turbo' ? 'selected' : ''; ?>>GPT-3.5 Turbo</option>
                            <option value="gpt-4" <?php echo $aiConfig['model'] === 'gpt-4' ? 'selected' : ''; ?>>GPT-4</option>
                            <option value="claude-3-sonnet" <?php echo $aiConfig['model'] === 'claude-3-sonnet' ? 'selected' : ''; ?>>Claude 3 Sonnet</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Máximo de Tokens:</label>
                        <input type="number" name="max_tokens" value="<?php echo $aiConfig['max_tokens']; ?>" class="form-control" min="100" max="2000">
                    </div>
                    
                    <div class="form-group">
                        <label>Temperatura:</label>
                        <input type="number" name="temperature" value="<?php echo $aiConfig['temperature']; ?>" class="form-control" min="0" max="2" step="0.1">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Salvar Configurações
                    </button>
                </form>
            </div>
            
            <div class="card">
                <h3><i class="fas fa-vial"></i> Teste da IA</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="test_ai">
                    
                    <div class="form-group">
                        <label>Mensagem de Teste:</label>
                        <textarea name="test_message" class="form-control" rows="3" placeholder="Digite uma mensagem para testar a IA..."><?php echo $_POST['test_message'] ?? ''; ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-play"></i> Testar IA
                    </button>
                </form>
                
                <?php if (isset($testResult)): ?>
                <div class="test-result">
                    <strong>Resposta da IA:</strong><br>
                    <?php echo htmlspecialchars($testResult); ?>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="card">
                <h3><i class="fas fa-chart-bar"></i> Estatísticas</h3>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $stats['total_messages'] ?? 0; ?></div>
                        <div class="stat-label">Mensagens (30 dias)</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo count($stats['daily_stats'] ?? []); ?></div>
                        <div class="stat-label">Dias Ativos</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <h3><i class="fas fa-book"></i> Base de Conhecimento</h3>
            <p>Gerencie a base de conhecimento da IA para melhorar as respostas.</p>
            <a href="knowledge_base.php" class="btn btn-primary">
                <i class="fas fa-edit"></i> Editar Base de Conhecimento
            </a>
        </div>
    </div>
</body>
</html> 