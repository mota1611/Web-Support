class ChatSystem {
    constructor() {
        this.messages = [];
        this.isTyping = false;
        this.userId = this.generateUserId();
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadMessages();
        this.startPolling();
        this.showWelcomeMessage();
    }

    bindEvents() {
        const messageForm = document.getElementById('messageForm');
        const messageInput = document.getElementById('messageInput');
        const sendButton = document.getElementById('sendButton');

        messageForm.addEventListener('submit', (e) => {
            e.preventDefault();
            this.sendMessage();
        });

        messageInput.addEventListener('input', () => {
            this.updateSendButton();
            this.handleTyping();
        });

        messageInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
        });

        document.getElementById('attachFile').addEventListener('click', () => {
            this.handleFileAttachment();
        });

        document.getElementById('emojiBtn').addEventListener('click', () => {
            this.handleEmoji();
        });
    }

    generateUserId() {
        return 'user_' + Math.random().toString(36).substr(2, 9);
    }

    updateSendButton() {
        const messageInput = document.getElementById('messageInput');
        const sendButton = document.getElementById('sendButton');
        
        if (messageInput.value.trim()) {
            sendButton.disabled = false;
        } else {
            sendButton.disabled = true;
        }
    }

    async sendMessage() {
        const messageInput = document.getElementById('messageInput');
        const message = messageInput.value.trim();

        if (!message) return;

        const messageData = {
            id: Date.now(),
            text: message,
            sender: 'user',
            timestamp: new Date().toISOString(),
            userId: this.userId
        };

        this.addMessageToUI(messageData);
        messageInput.value = '';
        this.updateSendButton();

        try {
            const response = await fetch('api/send_message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(messageData)
            });

            if (!response.ok) {
                throw new Error('Erro ao enviar mensagem');
            }

            setTimeout(() => {
                this.generateAIResponse(message);
            }, Math.random() * 1000 + 500);

        } catch (error) {
            console.error('Erro:', error);
            this.showError('Erro ao enviar mensagem. Tente novamente.');
        }
    }

    async generateAIResponse(userMessage) {
        try {
            const response = await fetch('api/ai_handler.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    text: userMessage,
                    userId: this.userId
                })
            });

            if (!response.ok) {
                throw new Error('Erro ao gerar resposta da IA');
            }

            const data = await response.json();
            
            if (data.success) {
                const supportMessage = {
                    id: data.messageId,
                    text: data.response,
                    sender: 'support',
                    timestamp: new Date().toISOString(),
                    userId: this.userId
                };

                this.addMessageToUI(supportMessage);
            } else {
                this.simulateSupportResponse(userMessage);
            }

        } catch (error) {
            console.error('Erro na IA:', error);
            this.simulateSupportResponse(userMessage);
        }
    }

    simulateSupportResponse(userMessage) {
        const responses = [
            "Obrigado pelo contato! Como posso ajudá-lo hoje?",
            "Entendo sua dúvida. Vou verificar isso para você.",
            "Estou analisando sua solicitação. Um momento, por favor.",
            "Já estou verificando essa informação no sistema.",
            "Perfeito! Vou resolver isso imediatamente.",
            "Obrigado pela paciência. Estou trabalhando na sua solicitação.",
            "Vou transferir sua solicitação para o departamento responsável.",
            "Sua solicitação foi registrada com sucesso. Ticket #" + Math.floor(Math.random() * 10000)
        ];

        const randomResponse = responses[Math.floor(Math.random() * responses.length)];
        
        const supportMessage = {
            id: Date.now(),
            text: randomResponse,
            sender: 'support',
            timestamp: new Date().toISOString(),
            userId: this.userId
        };

        this.addMessageToUI(supportMessage);
    }

    addMessageToUI(message) {
        const chatMessages = document.getElementById('chatMessages');
        const messageElement = this.createMessageElement(message);
        
        chatMessages.appendChild(messageElement);
        this.scrollToBottom();
        
        this.messages.push(message);
    }

    createMessageElement(message) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${message.sender}`;
        messageDiv.dataset.messageId = message.id;

        const avatar = document.createElement('div');
        avatar.className = 'message-avatar';
        avatar.textContent = message.sender === 'user' ? 'U' : 'S';

        const content = document.createElement('div');
        content.className = 'message-content';
        content.textContent = message.text;

        const time = document.createElement('div');
        time.className = 'message-time';
        time.textContent = this.formatTime(message.timestamp);

        content.appendChild(time);
        messageDiv.appendChild(avatar);
        messageDiv.appendChild(content);

        return messageDiv;
    }

    formatTime(timestamp) {
        const date = new Date(timestamp);
        return date.toLocaleTimeString('pt-BR', {
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    scrollToBottom() {
        const chatMessages = document.getElementById('chatMessages');
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    handleTyping() {
        if (!this.isTyping) {
            this.isTyping = true;
            this.showTypingIndicator();
        }
        
        clearTimeout(this.typingTimeout);
        this.typingTimeout = setTimeout(() => {
            this.hideTypingIndicator();
            this.isTyping = false;
        }, 1000);
    }

    showTypingIndicator() {
        const chatMessages = document.getElementById('chatMessages');
        const existingIndicator = chatMessages.querySelector('.typing-indicator');
        
        if (!existingIndicator) {
            const typingDiv = document.createElement('div');
            typingDiv.className = 'typing-indicator';
            typingDiv.innerHTML = `
                <span>Suporte está digitando</span>
                <div class="typing-dots">
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                </div>
            `;
            chatMessages.appendChild(typingDiv);
            this.scrollToBottom();
        }
    }

    hideTypingIndicator() {
        const typingIndicator = document.querySelector('.typing-indicator');
        if (typingIndicator) {
            typingIndicator.remove();
        }
    }

    showWelcomeMessage() {
        const welcomeMessage = {
            id: Date.now(),
            text: "Olá! Bem-vindo ao nosso sistema de suporte. Como posso ajudá-lo hoje?",
            sender: 'support',
            timestamp: new Date().toISOString(),
            userId: this.userId
        };

        setTimeout(() => {
            this.addMessageToUI(welcomeMessage);
        }, 1000);
    }

    async loadMessages() {
        try {
            console.log('Carregando mensagens para userId:', this.userId);
            const response = await fetch(`api/get_messages.php?userId=${this.userId}`);
            console.log('Status da resposta:', response.status);
            
            if (response.ok) {
                const data = await response.json();
                console.log('Dados recebidos da API:', data);
                console.log('Tipo de data:', typeof data);
                console.log('Tipo de data.messages:', typeof data.messages);
                console.log('É array?', Array.isArray(data.messages));
                
                if (data.success && Array.isArray(data.messages)) {
                    console.log('Processando', data.messages.length, 'mensagens');
                    data.messages.forEach(message => {
                        this.addMessageToUI(message);
                        this.messages.push(message);
                    });
                } else {
                    console.error('Resposta inválida da API:', data);
                    if (Array.isArray(data)) {
                        console.log('Data é um array, processando diretamente');
                        data.forEach(message => {
                            this.addMessageToUI(message);
                            this.messages.push(message);
                        });
                    }
                }
            } else {
                console.error('Erro na resposta da API:', response.status, response.statusText);
                const errorText = await response.text();
                console.error('Texto do erro:', errorText);
            }
        } catch (error) {
            console.error('Erro ao carregar mensagens:', error);
        }
    }

    startPolling() {
        setInterval(() => {
            this.checkNewMessages();
        }, 5000);
    }

    async checkNewMessages() {
        try {
            const lastMessageId = this.messages.length > 0 ? 
                Math.max(...this.messages.map(m => m.id)) : 0;
            
            console.log('Verificando novas mensagens, lastId:', lastMessageId);
            const response = await fetch(`api/get_messages.php?userId=${this.userId}&lastId=${lastMessageId}`);
            console.log('Status da resposta (checkNewMessages):', response.status);
            
            if (response.ok) {
                const data = await response.json();
                console.log('Dados recebidos (checkNewMessages):', data);
                console.log('Tipo de data.messages (checkNewMessages):', typeof data.messages);
                console.log('É array? (checkNewMessages):', Array.isArray(data.messages));
                
                if (data.success && Array.isArray(data.messages)) {
                    console.log('Processando', data.messages.length, 'novas mensagens');
                    data.messages.forEach(message => {
                        if (!this.messages.find(m => m.id === message.id)) {
                            this.addMessageToUI(message);
                            this.messages.push(message);
                        }
                    });
                } else {
                    console.error('Resposta inválida da API (checkNewMessages):', data);
                    if (Array.isArray(data)) {
                        console.log('Data é um array, processando diretamente (checkNewMessages)');
                        data.forEach(message => {
                            if (!this.messages.find(m => m.id === message.id)) {
                                this.addMessageToUI(message);
                                this.messages.push(message);
                            }
                        });
                    }
                }
            } else {
                console.error('Erro na resposta da API (checkNewMessages):', response.status, response.statusText);
                const errorText = await response.text();
                console.error('Texto do erro (checkNewMessages):', errorText);
            }
        } catch (error) {
            console.error('Erro ao verificar novas mensagens:', error);
        }
    }

    handleFileAttachment() {
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = 'image/*,.pdf,.doc,.docx,.txt';
        input.onchange = (e) => {
            const file = e.target.files[0];
            if (file) {
                this.uploadFile(file);
            }
        };
        input.click();
    }

    async uploadFile(file) {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('userId', this.userId);

        try {
            const response = await fetch('api/upload_file.php', {
                method: 'POST',
                body: formData
            });

            if (response.ok) {
                const result = await response.json();
                const messageData = {
                    id: Date.now(),
                    text: `Arquivo enviado: ${file.name}`,
                    sender: 'user',
                    timestamp: new Date().toISOString(),
                    userId: this.userId,
                    fileUrl: result.fileUrl
                };
                this.addMessageToUI(messageData);
            } else {
                throw new Error('Erro ao fazer upload');
            }
        } catch (error) {
            console.error('Erro no upload:', error);
            this.showError('Erro ao enviar arquivo. Tente novamente.');
        }
    }

    handleEmoji() {
        const emojis = ['😊', '👍', '👎', '❤️', '😢', '😡', '🎉', '🔥', '💯', '✨'];
        const emoji = emojis[Math.floor(Math.random() * emojis.length)];
        
        const messageInput = document.getElementById('messageInput');
        messageInput.value += emoji;
        messageInput.focus();
    }

    showError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = message;
        errorDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #f44336;
            color: white;
            padding: 15px 20px;
            border-radius: 5px;
            z-index: 1000;
            animation: slideIn 0.3s ease;
        `;

        document.body.appendChild(errorDiv);

        setTimeout(() => {
            errorDiv.remove();
        }, 3000);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new ChatSystem();
});

const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
`;
document.head.appendChild(style);