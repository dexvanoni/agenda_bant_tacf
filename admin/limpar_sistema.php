<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

// Verificar se o usuário está logado como admin
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Acesso não autorizado']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit();
}

try {
    // Iniciar transação para garantir consistência
    $conn->beginTransaction();
    
    // Contar registros antes da limpeza
    $stmt = $conn->query("SELECT COUNT(*) FROM agendamentos");
    $total_agendamentos = $stmt->fetchColumn();
    
    $stmt = $conn->query("SELECT COUNT(*) FROM datas_liberadas");
    $total_datas = $stmt->fetchColumn();
    
    // Limpar todas as tabelas
    $conn->exec("DELETE FROM agendamentos");
    $conn->exec("DELETE FROM datas_liberadas");
    
    // Resetar auto-increment
    $conn->exec("ALTER TABLE agendamentos AUTO_INCREMENT = 1");
    $conn->exec("ALTER TABLE datas_liberadas AUTO_INCREMENT = 1");
    
    // Confirmar transação
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Sistema limpo com sucesso!',
        'dados_removidos' => [
            'agendamentos' => $total_agendamentos,
            'datas_liberadas' => $total_datas
        ]
    ]);
    
} catch (Exception $e) {
    // Reverter transação em caso de erro
    $conn->rollBack();
    
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Erro ao limpar sistema: ' . $e->getMessage()
    ]);
}
?>
