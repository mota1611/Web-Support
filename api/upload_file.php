<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Método não permitido'], 405);
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    jsonResponse(['error' => 'Nenhum arquivo enviado ou erro no upload'], 400);
}

$file = $_FILES['file'];
$userId = isset($_POST['userId']) ? sanitizeInput($_POST['userId']) : '';

if (empty($userId)) {
    jsonResponse(['error' => 'ID do usuário é obrigatório'], 400);
}

$validation = validateFile($file);
if (!$validation['valid']) {
    jsonResponse(['error' => $validation['error']], 400);
}

ensureUploadDir();

try {
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $fileName = uniqid() . '_' . time() . '.' . $extension;
    $filePath = UPLOAD_DIR . $fileName;
    
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        jsonResponse(['error' => 'Erro ao salvar arquivo'], 500);
    }
    
    $fileUrl = 'uploads/' . $fileName;
    
    $pdo = getConnection();
    if (!$pdo) {
        jsonResponse(['error' => 'Erro de conexão com banco de dados'], 500);
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO file_uploads (user_id, original_name, file_name, file_url, file_size, upload_date)
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([
        $userId,
        $file['name'],
        $fileName,
        $fileUrl,
        $file['size']
    ]);
    
    jsonResponse([
        'success' => true,
        'message' => 'Arquivo enviado com sucesso',
        'fileUrl' => $fileUrl,
        'fileName' => $file['name'],
        'fileSize' => $file['size']
    ]);
    
} catch (Exception $e) {
    error_log("Erro no upload: " . $e->getMessage());
    jsonResponse(['error' => 'Erro interno do servidor'], 500);
}
?>