<?php
require_once __DIR__ . '/env_loader.php';

define('AI_ENABLED', true);
define('AI_PROVIDER', 'local'); // 'openai', 'local', 'claude'
define('AI_MODEL', 'gpt-3.5-turbo'); // 'gpt-3.5-turbo', 'gpt-4', 'claude-3-sonnet'
define('AI_MAX_TOKENS', 500);
define('AI_TEMPERATURE', 0.7);

define('OPENAI_API_KEY', getenv('OPENAI_API_KEY') ?: '');
define('ANTHROPIC_API_KEY', getenv('ANTHROPIC_API_KEY') ?: '');

define('CONVERSATION_HISTORY_LIMIT', 10);
define('ENABLE_SENTIMENT_ANALYSIS', true);
define('ENABLE_KEYWORD_DETECTION', true);

// Base de conhecimento
define('KNOWLEDGE_BASE_ENABLED', true);
define('KNOWLEDGE_BASE_FILE', __DIR__ . '/../data/knowledge_base.json');

define('FALLBACK_TO_LOCAL', true);
define('LOCAL_AI_ENABLED', true);

define('RESPONSE_DELAY_MIN', 500); // milissegundos
define('RESPONSE_DELAY_MAX', 2000); // milissegundos

$KEYWORDS = [
    'login' => [
        'senha', 'login', 'acesso', 'conta', 'usuário', 'autenticação'
    ],
    'payment' => [
        'pagamento', 'fatura', 'boleto', 'cartão', 'pix', 'transferência'
    ],
    'technical' => [
        'erro', 'problema', 'bug', 'falha', 'não funciona', 'quebrado'
    ],
    'urgent' => [
        'urgente', 'crítico', 'emergência', 'bloqueado', 'não consigo'
    ],
    'positive' => [
        'obrigado', 'obrigada', 'valeu', 'legal', 'bom', 'ótimo', 'excelente'
    ]
];

$SENTIMENT_RESPONSES = [
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

function isAIEnabled() {
    return AI_ENABLED && (LOCAL_AI_ENABLED || !empty(OPENAI_API_KEY) || !empty(ANTHROPIC_API_KEY));
}

function getAIConfig() {
    return [
        'enabled' => AI_ENABLED,
        'provider' => AI_PROVIDER,
        'model' => AI_MODEL,
        'max_tokens' => AI_MAX_TOKENS,
        'temperature' => AI_TEMPERATURE,
        'fallback_enabled' => FALLBACK_TO_LOCAL,
        'local_enabled' => LOCAL_AI_ENABLED
    ];
}

function getKeywords() {
    global $KEYWORDS;
    return $KEYWORDS;
}

function getSentimentResponses() {
    global $SENTIMENT_RESPONSES;
    return $SENTIMENT_RESPONSES;
}
?> 