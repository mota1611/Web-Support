<?php
require_once 'env_loader.php';
require_once 'config.php';
require_once 'ai_config.php';

class AIHandler {
    private $pdo;
    private $apiKey;
    private $useLocalAI;
    
    public function __construct() {
        $this->pdo = getConnection();
        $this->apiKey = getenv('OPENAI_API_KEY') ?: '';
        $this->useLocalAI = empty($this->apiKey);
    }
    
    public function generateResponse($userMessage, $userId, $conversationHistory = []) {
        if ($this->useLocalAI) {
            return $this->generateLocalResponse($userMessage, $conversationHistory);
        } else {
            return $this->generateAIResponse($userMessage, $conversationHistory);
        }
    }
    
    private function generateAIResponse($userMessage, $conversationHistory) {
        $url = 'https://api.openai.com/v1/chat/completions';
        
        $messages = [
            [
                'role' => 'system',
                'content' => $this->getSystemPrompt()
            ]
        ];
        
        foreach ($conversationHistory as $msg) {
            $messages[] = [
                'role' => $msg['sender'] === 'user' ? 'user' : 'assistant',
                'content' => $msg['text']
            ];
        }
        
        $messages[] = [
            'role' => 'user',
            'content' => $userMessage
        ];
        
        $data = [
            'model' => 'gpt-3.5-turbo',
            'messages' => $messages,
            'max_tokens' => 500,
            'temperature' => 0.7
        ];
        
        $headers = [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $result = json_decode($response, true);
            if (isset($result['choices'][0]['message']['content'])) {
                return $result['choices'][0]['message']['content'];
            }
        }
        
        return $this->generateLocalResponse($userMessage, $conversationHistory);
    }
    
    private function generateLocalResponse($userMessage, $conversationHistory) {
        $message = strtolower(trim($userMessage));
        
        $knowledgeResponse = $this->searchKnowledgeBase($message);
        if ($knowledgeResponse) {
            return $knowledgeResponse;
        }
        
        $sentiment = $this->analyzeSentiment($message);
        
        $responses = $this->getKeywordResponses($message, $sentiment);
        
        if (!empty($responses)) {
            return $responses[array_rand($responses)];
        }
        
        return $this->getGenericResponse($sentiment);
    }
    
    private function searchKnowledgeBase($message) {
        $knowledgeFile = KNOWLEDGE_BASE_FILE;
        
        if (!file_exists($knowledgeFile)) {
            return null;
        }
        
        $knowledge = json_decode(file_get_contents($knowledgeFile), true);
        if (!$knowledge) {
            return null;
        }
            
        if (isset($knowledge['faq'])) {
            foreach ($knowledge['faq'] as $faq) {
                foreach ($faq['keywords'] as $keyword) {
                    if (strpos($message, $keyword) !== false) {
                        return $faq['answer'];
                    }
                }
            }
        }
        
        if (isset($knowledge['common_issues'])) {
            foreach ($knowledge['common_issues'] as $issue) {
                foreach ($issue['keywords'] as $keyword) {
                    if (strpos($message, $keyword) !== false) {
                        $solutions = implode("\n• ", $issue['solutions']);
                        return "Entendo que você está enfrentando problemas com {$issue['issue']}. Aqui estão algumas soluções:\n\n• {$solutions}\n\nSe essas soluções não resolverem, posso conectá-lo com um especialista.";
                    }
                }
            }
        }
        
        return null;
    }
    
    private function getSystemPrompt() {
        return "Você é um assistente de suporte técnico profissional e amigável. 
        Suas respostas devem ser:
        - Úteis e informativas
        - Em português brasileiro
        - Profissionais mas amigáveis
        - Concisas mas completas
        - Sempre oferecendo ajuda adicional quando apropriado
        
        Seja sempre prestativo e tente resolver os problemas do usuário da melhor forma possível.";
    }
    
    private function analyzeSentiment($message) {
        $positiveWords = ['obrigado', 'obrigada', 'valeu', 'legal', 'bom', 'ótimo', 'excelente', 'perfeito', '👍', '❤️'];
        $negativeWords = ['problema', 'erro', 'falha', 'bug', 'não funciona', 'quebrado', 'ruim', 'péssimo', '😢', '😡'];
        $urgentWords = ['urgente', 'crítico', 'emergência', 'falha', 'não consigo', 'bloqueado'];
        
        $positiveCount = 0;
        $negativeCount = 0;
        $urgentCount = 0;
        
        foreach ($positiveWords as $word) {
            if (strpos($message, $word) !== false) {
                $positiveCount++;
            }
        }
        
        foreach ($negativeWords as $word) {
            if (strpos($message, $word) !== false) {
                $negativeCount++;
            }
        }
        
        foreach ($urgentWords as $word) {
            if (strpos($message, $word) !== false) {
                $urgentCount++;
            }
        }
        
        if ($urgentCount > 0) return 'urgent';
        if ($negativeCount > $positiveCount) return 'negative';
        if ($positiveCount > $negativeCount) return 'positive';
        return 'neutral';
    }
    
    private function getKeywordResponses($message, $sentiment) {
        $responses = [];
        
        if (strpos($message, 'senha') !== false || strpos($message, 'login') !== false) {
            $responses[] = "Para redefinir sua senha, acesse nossa página de recuperação de senha ou entre em contato com nosso suporte técnico. Posso ajudá-lo com mais alguma coisa?";
            $responses[] = "Se você está tendo problemas com login, posso guiá-lo pelo processo de recuperação de senha. Gostaria de ajuda com isso?";
        }
        
        if (strpos($message, 'pagamento') !== false || strpos($message, 'fatura') !== false) {
            $responses[] = "Para questões relacionadas a pagamentos e faturas, posso conectá-lo com nosso departamento financeiro. Qual é o problema específico?";
            $responses[] = "Entendo sua preocupação com pagamentos. Vou verificar as informações da sua conta. Pode me fornecer mais detalhes?";
        }
        
        if (strpos($message, 'tecnico') !== false || strpos($message, 'suporte') !== false) {
            $responses[] = "Estou aqui para ajudá-lo! Sou o assistente de suporte técnico. Como posso ser útil hoje?";
            $responses[] = "Perfeito! Sou especializado em suporte técnico. Conte-me qual é o problema que você está enfrentando.";
        }
        
        if (strpos($message, 'erro') !== false || strpos($message, 'problema') !== false) {
            $responses[] = "Entendo que você está enfrentando um problema. Vou ajudá-lo a resolver isso. Pode me dar mais detalhes sobre o erro?";
            $responses[] = "Vou investigar esse problema para você. Para que eu possa ajudá-lo melhor, pode me fornecer mais informações sobre o que está acontecendo?";
        }
        
        if (strpos($message, 'obrigado') !== false || strpos($message, 'valeu') !== false) {
            $responses[] = "De nada! Fico feliz em ter ajudado. Se precisar de mais alguma coisa, estarei aqui! 😊";
            $responses[] = "Por nada! É um prazer poder ajudar. Se surgir qualquer outra dúvida, não hesite em perguntar!";
        }
        
        return $responses;
    }
    
    private function getGenericResponse($sentiment) {
        $responses = [
            'positive' => [
                "Que ótimo! Fico feliz em saber que as coisas estão funcionando bem. Como posso ajudá-lo hoje?",
                "Perfeito! Estou aqui para continuar ajudando. Há mais alguma coisa em que posso ser útil?",
                "Excelente! Se precisar de mais alguma coisa, estarei aqui para ajudar! 😊"
            ],
            'negative' => [
                "Entendo sua frustração. Vou fazer o possível para ajudá-lo a resolver isso. Pode me dar mais detalhes?",
                "Sinto muito que você esteja passando por isso. Vou investigar e encontrar a melhor solução para você.",
                "Não se preocupe, vou ajudá-lo a resolver esse problema. Vamos trabalhar juntos para encontrar uma solução."
            ],
            'urgent' => [
                "Entendo que isso é urgente. Vou priorizar sua solicitação e trabalhar para resolver isso o mais rápido possível.",
                "Vou tratar isso como prioridade máxima. Pode me dar mais detalhes para que eu possa agir imediatamente?",
                "Compreendo a urgência. Vou mobilizar nossa equipe para resolver isso rapidamente."
            ],
            'neutral' => [
                "Como posso ajudá-lo hoje? Estou aqui para resolver qualquer dúvida ou problema que você tenha.",
                "Olá! Sou seu assistente de suporte. Como posso ser útil para você hoje?",
                "Estou aqui para ajudar! Conte-me qual é sua dúvida ou problema e vou trabalhar para resolvê-lo."
            ]
        ];
        
        $sentimentResponses = $responses[$sentiment] ?? $responses['neutral'];
        return $sentimentResponses[array_rand($sentimentResponses)];
    }
    
    public function getConversationHistory($userId, $limit = 10) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT text, sender, timestamp 
                FROM messages 
                WHERE user_id = ? 
                ORDER BY timestamp DESC 
                LIMIT ?
            ");
            $stmt->execute([$userId, $limit]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erro ao buscar histórico: " . $e->getMessage());
            return [];
        }
    }
    
    public function saveAIResponse($userId, $response, $messageId) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO messages (id, user_id, text, sender, timestamp) 
                VALUES (?, ?, ?, 'support', NOW())
            ");
            $stmt->execute([$messageId, $userId, $response]);
            return true;
        } catch (PDOException $e) {
            error_log("Erro ao salvar resposta da IA: " . $e->getMessage());
            return false;
        }
    }
}
        
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['text']) || !isset($input['userId'])) {
        jsonResponse(['error' => 'Dados inválidos'], 400);
    }
    
    $aiHandler = new AIHandler();
    $conversationHistory = $aiHandler->getConversationHistory($input['userId']);
    $aiResponse = $aiHandler->generateResponse($input['text'], $input['userId'], $conversationHistory);
    
    $messageId = time() . rand(1000, 9999);
    $aiHandler->saveAIResponse($input['userId'], $aiResponse, $messageId);
    
    jsonResponse([
        'success' => true,
        'response' => $aiResponse,
        'messageId' => $messageId
    ]);
}
?> 