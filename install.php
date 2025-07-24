<?php
require_once 'api/config.php';

if (file_exists('installed.txt')) {
    die('Sistema já foi instalado. Remova o arquivo installed.txt para reinstalar.');
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalação - Sistema de Chat</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .install-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        .step {
            margin-bottom: 30px;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }
        .step.success {
            background: #f0f8ff;
            border-left-color: #4CAF50;
        }
        .step.error {
            background: #fff5f5;
            border-left-color: #f44336;
        }
        .step h3 {
            margin: 0 0 10px 0;
            color: #333;
        }
        .step p {
            margin: 0;
            color: #666;
        }
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            margin: 10px 5px;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
        }
        .form-group input:focus {
            border-color: #667eea;
            outline: none;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <h1 style="text-align: center; color: #333; margin-bottom: 30px;">
            🚀 Instalação do Sistema de Chat
        </h1>

        <?php
        $errors = [];
        $success = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dbHost = $_POST['db_host'] ?? '';
            $dbName = $_POST['db_name'] ?? '';
            $dbUser = $_POST['db_user'] ?? '';
            $dbPass = $_POST['db_pass'] ?? '';

            if (empty($dbHost) || empty($dbName) || empty($dbUser)) {
                $errors[] = 'Todos os campos são obrigatórios';
            }

            if (empty($errors)) {
                try {
                    $pdo = new PDO(
                        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
                        $dbUser,
                        $dbPass,
                        [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                        ]
                    );

                    $configContent = file_get_contents('api/config.php');
                    $configContent = str_replace(
                        ["define('DB_HOST', 'localhost');", "define('DB_NAME', 'chat_support');", "define('DB_USER', 'root');", "define('DB_PASS', '');"],
                        ["define('DB_HOST', '{$dbHost}');", "define('DB_NAME', '{$dbName}');", "define('DB_USER', '{$dbUser}');", "define('DB_PASS', '{$dbPass}');"],
                        $configContent
                    );
                    
                    if (file_put_contents('api/config.php', $configContent)) {
                        require_once 'api/init.php';
                        $result = initializeSystem();
                        
                        if ($result['success']) {
                            file_put_contents('installed.txt', date('Y-m-d H:i:s'));
                            $success = true;
                        } else {
                            $errors[] = $result['error'];
                        }
                    } else {
                        $errors[] = 'Erro ao salvar configurações';
                    }

                } catch (PDOException $e) {
                    $errors[] = 'Erro de conexão com banco de dados: ' . $e->getMessage();
                }
            }
        }
        ?>

        <?php if ($success): ?>
            <div class="step success">
                <h3>✅ Instalação Concluída!</h3>
                <p>O sistema foi instalado com sucesso. Você pode agora acessar o chat.</p>
            </div>
            <div style="text-align: center; margin-top: 30px;">
                <a href="index.php" class="btn">Acessar Sistema de Chat</a>
            </div>
        <?php else: ?>
            <form method="POST">
                <div class="form-group">
                    <label for="db_host">Host do Banco de Dados:</label>
                    <input type="text" id="db_host" name="db_host" value="localhost" required>
                </div>

                <div class="form-group">
                    <label for="db_name">Nome do Banco de Dados:</label>
                    <input type="text" id="db_name" name="db_name" value="chat_support" required>
                </div>

                <div class="form-group">
                    <label for="db_user">Usuário do Banco:</label>
                    <input type="text" id="db_user" name="db_user" value="root" required>
                </div>

                <div class="form-group">
                    <label for="db_pass">Senha do Banco:</label>
                    <input type="password" id="db_pass" name="db_pass" value="">
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="step error">
                        <h3>❌ Erros encontrados:</h3>
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div style="text-align: center; margin-top: 30px;">
                    <button type="submit" class="btn">Instalar Sistema</button>
                </div>
            </form>

            <div class="step">
                <h3>📋 Pré-requisitos:</h3>
                <ul>
                    <li>PHP 7.4 ou superior</li>
                    <li>MySQL 5.7 ou superior</li>
                    <li>Extensões PHP: PDO, PDO_MySQL</li>
                    <li>Banco de dados criado</li>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</body>
</html> 