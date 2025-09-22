<?php
header('Content-Type: application/json; charset=UTF-8');
session_start();
require_once '../config/database.php';

// Verificar se o usuário está logado como admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit();
}

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit();
}

try {
    // Obter dados do formulário
    $titulo = trim($_POST['titulo'] ?? '');
    $conteudo = trim($_POST['conteudo'] ?? '');
    $ativo = isset($_POST['ativo']) ? 1 : 0;
    
    // Validações básicas
    if (empty($titulo)) {
        throw new Exception('O título é obrigatório');
    }
    
    if (empty($conteudo)) {
        throw new Exception('O conteúdo é obrigatório');
    }
    
    if (strlen($titulo) > 255) {
        throw new Exception('O título deve ter no máximo 255 caracteres');
    }
    
    // Sanitizar conteúdo HTML (permitir apenas tags básicas)
    $conteudo = strip_tags($conteudo, '<p><strong><em><ul><li><br><span><div>');
    
    // Iniciar transação
    $conn->beginTransaction();
    
    // Desativar todos os avisos existentes
    $stmt = $conn->prepare("UPDATE aviso_popup SET ativo = 0");
    $stmt->execute();
    
    // Inserir novo aviso
    $stmt = $conn->prepare("
        INSERT INTO aviso_popup (titulo, conteudo, ativo) 
        VALUES (?, ?, ?)
    ");
    
    $stmt->execute([$titulo, $conteudo, $ativo]);
    
    // Confirmar transação
    $conn->commit();
    
    // Resposta de sucesso
    echo json_encode([
        'success' => true,
        'message' => 'Configuração salva com sucesso!',
        'data' => [
            'titulo' => $titulo,
            'conteudo' => $conteudo,
            'ativo' => $ativo
        ]
    ]);
    
} catch (Exception $e) {
    // Reverter transação em caso de erro
    if ($conn->inTransaction()) {
        $conn->rollback();
    }
    
    // Resposta de erro
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (PDOException $e) {
    // Reverter transação em caso de erro de banco
    if ($conn->inTransaction()) {
        $conn->rollback();
    }
    
    // Log do erro (em produção, usar um sistema de log adequado)
    error_log("Erro ao salvar aviso: " . $e->getMessage());
    
    // Resposta de erro
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor. Tente novamente.'
    ]);
}
?>
