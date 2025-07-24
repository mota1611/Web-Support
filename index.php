<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Chat - Suporte</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="chat-container">
        <div class="chat-header">
            <div class="header-info">
                <i class="fas fa-headset"></i>
                <h2>Sistema de Suporte</h2>
            </div>
            <div class="status-indicator">
                <span class="status-dot online"></span>
                <span class="status-text">Online</span>
            </div>
        </div>

        <div class="chat-messages" id="chatMessages">
            <!-- Mensagens serão carregadas aqui -->
        </div>

        <div class="chat-input-container">
            <form id="messageForm" class="message-form">
                <div class="input-group">
                    <input type="text" id="messageInput" placeholder="Digite sua mensagem..." maxlength="500">
                    <button type="submit" id="sendButton">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
                <div class="message-actions">
                    <button type="button" id="attachFile" class="action-btn">
                        <i class="fas fa-paperclip"></i>
                    </button>
                    <button type="button" id="emojiBtn" class="action-btn">
                        <i class="fas fa-smile"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/chat.js"></script>
</body>
</html> 