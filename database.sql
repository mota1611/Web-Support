CREATE DATABASE IF NOT EXISTS chat_support CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE chat_support;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users (
    id VARCHAR(50) PRIMARY KEY,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_last_activity (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(128) PRIMARY KEY,
    user_id VARCHAR(50) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_id (user_id),
    INDEX idx_last_activity (last_activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO messages (id, user_id, text, sender, timestamp) VALUES
(1704067200000, 'user_example', 'Olá, preciso de ajuda!', 'user', '2024-01-01 10:00:00'),
(1704067260000, 'user_example', 'Olá! Como posso ajudá-lo hoje?', 'support', '2024-01-01 10:01:00'),
(1704067320000, 'user_example', 'Tenho um problema com meu pedido', 'user', '2024-01-01 10:02:00'),
(1704067380000, 'user_example', 'Entendo. Vou verificar isso para você. Pode me informar o número do pedido?', 'support', '2024-01-01 10:03:00');

INSERT INTO users (id) VALUES ('user_example');

-- Comentários sobre a estrutura:
-- 
-- 1. messages: Armazena todas as mensagens do chat
--    - id: ID único da mensagem (timestamp em milissegundos)
--    - user_id: ID do usuário que enviou/recebeu a mensagem
--    - text: Conteúdo da mensagem
--    - sender: Quem enviou (user ou support)
--    - timestamp: Quando a mensagem foi enviada
--    - file_url: URL do arquivo anexado (se houver)
--
-- 2. users: Registra usuários ativos
--    - id: ID único do usuário
--    - created_at: Quando o usuário foi criado
--    - last_activity: Última atividade do usuário
--
-- 3. file_uploads: Registra uploads de arquivos
--    - id: ID único do upload
--    - user_id: ID do usuário que fez o upload
--    - original_name: Nome original do arquivo
--    - file_name: Nome do arquivo no servidor
--    - file_url: URL para acessar o arquivo
--    - file_size: Tamanho do arquivo em bytes
--
-- 4. sessions: Para futuras funcionalidades de sessão
--    - id: ID da sessão
--    - user_id: ID do usuário
--    - ip_address: IP do usuário
--    - user_agent: Navegador/dispositivo do usuário

-- Índices criados para otimizar consultas:
-- - idx_user_id: Para buscar mensagens de um usuário específico
-- - idx_timestamp: Para ordenar mensagens por data
-- - idx_sender: Para filtrar por tipo de remetente
-- - idx_last_activity: Para limpeza de sessões antigas
-- - idx_upload_date: Para gerenciar arquivos antigos 