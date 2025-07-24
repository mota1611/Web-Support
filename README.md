# Sistema de Chat para Suporte

Um sistema de chat moderno e responsivo desenvolvido em PHP com interface web para atendimento ao cliente. Este é um projeto de **base de teste para aprendizado**, criado para demonstrar conceitos de desenvolvimento web.

## Características

- **Interface moderna e responsiva** - Design limpo e adaptável a diferentes dispositivos
- **Tempo real** - Atualização automática de mensagens
- **Upload de arquivos** - Suporte para envio de imagens, PDFs e documentos
- **Indicador de digitação** - Mostra quando o suporte está digitando
- **Emojis** - Suporte para emojis nas mensagens
- **Banco de dados MySQL** - Armazenamento seguro das conversas
- **Clean Code** - Código organizado e bem estruturado

## Pré-requisitos

- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Servidor web (Apache/Nginx)
- Extensões PHP: PDO, PDO_MySQL, JSON

## Instalação

### 1. Clone ou baixe o projeto
```bash
git clone [url-do-repositorio]
cd web-chat
```

### 2. Configure o banco de dados
- Crie um banco de dados MySQL chamado `chat_support`
- Configure as credenciais no arquivo `api/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'chat_support');
define('DB_USER', 'seu_usuario');
define('DB_PASS', 'sua_senha');
```

### 3. Configure o servidor web
- Certifique-se de que o diretório `uploads/` tenha permissões de escrita
- Configure o servidor para executar PHP

### 4. Acesse o sistema
- Abra o navegador e acesse: `http://localhost/web-chat/`

## Estrutura do Projeto

```
web-chat/
├── index.php              # Página principal
├── assets/
│   ├── css/
│   │   └── style.css      # Estilos CSS
│   └── js/
│       └── chat.js        # Lógica JavaScript
├── api/
│   ├── config.php         # Configurações
│   ├── send_message.php   # Enviar mensagens
│   ├── get_messages.php   # Buscar mensagens
│   └── upload_file.php    # Upload de arquivos
├── uploads/               # Diretório para arquivos
└── README.md             # Documentação
```

## Configuração

### Banco de Dados
O sistema criará automaticamente as tabelas necessárias na primeira execução:

- `messages` - Armazena as mensagens
- `users` - Registra usuários ativos
- `file_uploads` - Registra uploads de arquivos

### Segurança
- Validação de entrada em todos os endpoints
- Sanitização de dados
- Validação de tipos de arquivo
- Limite de tamanho de arquivo (5MB)

### Personalização
Você pode personalizar:

- **Cores**: Edite as variáveis CSS no arquivo `style.css`
- **Respostas automáticas**: Modifique o array de respostas em `chat.js`
- **Tipos de arquivo**: Altere `ALLOWED_EXTENSIONS` em `config.php`
- **Tamanho máximo**: Modifique `MAX_FILE_SIZE` em `config.php`

## Funcionalidades

### Para o Cliente
- Envio de mensagens de texto
- Upload de arquivos (imagens, PDFs, documentos)
- Visualização de histórico de conversas
- Indicador de status do suporte
- Interface responsiva para mobile

### Para o Suporte
- Visualização de todas as conversas
- Sistema de tickets automático
- Histórico completo de atendimentos
- Notificações em tempo real

## API Endpoints

### POST /api/send_message.php
Envia uma nova mensagem.

**Parâmetros:**
```json
{
  "id": 1234567890,
  "text": "Mensagem do usuário",
  "sender": "user",
  "timestamp": "2024-01-01T10:00:00Z",
  "userId": "user_abc123"
}
```

### GET /api/get_messages.php
Busca mensagens de um usuário.

**Parâmetros:**
- `userId` (obrigatório): ID do usuário
- `lastId` (opcional): ID da última mensagem recebida
- `limit` (opcional): Número máximo de mensagens (padrão: 50)

### POST /api/upload_file.php
Faz upload de um arquivo.

**Parâmetros:**
- `file`: Arquivo a ser enviado
- `userId`: ID do usuário

## Personalização

### Cores do Tema
Edite as variáveis CSS no arquivo `style.css`:

```css
:root {
  --primary-color: #667eea;
  --secondary-color: #764ba2;
  --success-color: #4CAF50;
  --error-color: #f44336;
}
```

### Respostas Automáticas
Modifique o array de respostas em `chat.js`:

```javascript
const responses = [
    "Sua mensagem personalizada aqui",
    "Outra resposta automática"
];
```

## Deploy

### Local (XAMPP/WAMP)
1. Copie os arquivos para `htdocs/`
2. Configure o banco de dados
3. Acesse via `http://localhost/web-chat/`

### Servidor Web
1. Faça upload dos arquivos via FTP
2. Configure o banco de dados no servidor
3. Ajuste as configurações em `api/config.php`

## Segurança

- Todas as entradas são validadas e sanitizadas
- Proteção contra SQL Injection usando prepared statements
- Validação de tipos de arquivo
- Limite de tamanho de upload
- Headers de segurança configurados

## Responsividade

O sistema é totalmente responsivo e funciona em:
- Desktop
- Tablet
- Smartphone

## Troubleshooting

### Erro de conexão com banco
- Verifique as credenciais em `api/config.php`
- Certifique-se de que o MySQL está rodando
- Verifique se o banco `chat_support` existe

### Upload não funciona
- Verifique permissões do diretório `uploads/`
- Confirme se o PHP tem extensão `fileinfo` habilitada
- Verifique o limite de upload no `php.ini`

### Mensagens não aparecem
- Verifique se o JavaScript está habilitado
- Confirme se as APIs estão respondendo corretamente
- Verifique o console do navegador para erros

## Suporte

Para dúvidas ou problemas:
1. Verifique os logs de erro do PHP
2. Confirme as configurações do banco de dados
3. Teste as APIs individualmente

---

**Este é um projeto de base de teste para aprendizado. Use para estudar conceitos de desenvolvimento web, APIs, banco de dados e interface de usuário.** 