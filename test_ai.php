<?php
require_once 'api/env_loader.php';
require_once 'api/config.php';
require_once 'api/ai_config.php';
require_once 'api/ai_handler.php';

function testAI() {
    echo "<h1>🧪 Teste da IA</h1>";
    
    $aiHandler = new AIHandler();
    $testMessages = [
        "Olá, como posso redefinir minha senha?",
        "Estou com problemas para fazer pagamento",
        "Obrigado pela ajuda!",
        "Minha conta foi bloqueada, é urgente!",
        "Como cancelar minha assinatura?"
    ];
    
    foreach ($testMessages as $message) {
        echo "<div style='margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px;'>";
        echo "<strong>Usuário:</strong> " . htmlspecialchars($message) . "<br>";
        
        $response = $aiHandler->generateResponse($message, 'test_user');
        
        echo "<strong>IA:</strong> " . htmlspecialchars($response) . "<br>";
        echo "</div>";
    }
}

function checkConfig() {
    echo "<h2>⚙️ Configurações da IA</h2>";
    
    $config = getAIConfig();
    echo "<ul>";
    echo "<li><strong>IA Habilitada:</strong> " . ($config['enabled'] ? 'Sim' : 'Não') . "</li>";
    echo "<li><strong>Provedor:</strong> " . $config['provider'] . "</li>";
    echo "<li><strong>Modelo:</strong> " . $config['model'] . "</li>";
    echo "<li><strong>Máximo de Tokens:</strong> " . $config['max_tokens'] . "</li>";
    echo "<li><strong>Temperatura:</strong> " . $config['temperature'] . "</li>";
    echo "</ul>";
    
    echo "<h3>🔑 API Keys</h3>";
    echo "<ul>";
    echo "<li><strong>OpenAI API Key:</strong> " . (getenv('OPENAI_API_KEY') ? 'Configurada' : 'Não configurada') . "</li>";
    echo "<li><strong>Anthropic API Key:</strong> " . (getenv('ANTHROPIC_API_KEY') ? 'Configurada' : 'Não configurada') . "</li>";
    echo "</ul>";
}

function checkKnowledgeBase() {
    echo "<h2>📚 Base de Conhecimento</h2>";
    
    $knowledgeFile = KNOWLEDGE_BASE_FILE;
    if (file_exists($knowledgeFile)) {
        $knowledge = json_decode(file_get_contents($knowledgeFile), true);
        echo "<p>✅ Base de conhecimento carregada com sucesso</p>";
        echo "<ul>";
        echo "<li><strong>FAQ:</strong> " . count($knowledge['faq'] ?? []) . " perguntas</li>";
        echo "<li><strong>Problemas Comuns:</strong> " . count($knowledge['common_issues'] ?? []) . " categorias</li>";
        echo "</ul>";
    } else {
        echo "<p>❌ Base de conhecimento não encontrada</p>";
    }
}

// Verificar banco de dados
function checkDatabase() {
    echo "<h2>🗄️ Banco de Dados</h2>";
    
    $pdo = getConnection();
    if ($pdo) {
        echo "<p>✅ Conexão com banco de dados OK</p>";
        
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM messages");
            $stmt->execute();
            $result = $stmt->fetch();
            echo "<p><strong>Total de mensagens:</strong> " . $result['total'] . "</p>";
        } catch (PDOException $e) {
            echo "<p>❌ Erro ao consultar mensagens: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>❌ Erro de conexão com banco de dados</p>";
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste da IA - Chat de Suporte</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        h1, h2, h3 {
            color: #2c3e50;
        }
        
        .test-message {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 4px solid #007bff;
        }
        
        .status-ok {
            color: #28a745;
        }
        
        .status-error {
            color: #dc3545;
        }
        
        ul {
            list-style: none;
            padding: 0;
        }
        
        li {
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px;
        }
        
        .btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🤖 Teste da IA - Chat de Suporte</h1>
        
        <p>Esta página testa a implementação da IA no sistema de chat.</p>
        
        <div style="margin: 20px 0;">
            <a href="index.php" class="btn">📱 Ir para o Chat</a>
            <a href="admin/ai_dashboard.php" class="btn">⚙️ Painel de IA</a>
        </div>
        
        <?php
        checkConfig();
        checkKnowledgeBase();
        checkDatabase();
        testAI();
        ?>
        
        <h2>📋 Instruções</h2>
        <div class="test-message">
            <p><strong>Para usar a IA externa:</strong></p>
            <ol>
                <li>Configure a variável de ambiente OPENAI_API_KEY</li>
                <li>Ou configure ANTHROPIC_API_KEY para usar Claude</li>
                <li>Edite api/ai_config.php para escolher o provedor</li>
            </ol>
            
            <p><strong>Para usar apenas IA local:</strong></p>
            <ol>
                <li>Deixe as API keys vazias</li>
                <li>A IA local será usada automaticamente</li>
                <li>Edite data/knowledge_base.json para personalizar respostas</li>
            </ol>
        </div>
    </div>
</body>
</html> 