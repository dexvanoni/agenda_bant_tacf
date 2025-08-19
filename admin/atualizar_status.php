<?php
session_start();
require_once '../config/database.php';
require_once '../config/email.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit();
}

// Receber dados do POST
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['ids']) || !isset($data['status'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit();
}

$ids = $data['ids'];
$status = $data['status'];

if (!in_array($status, ['aprovado', 'cancelado'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Status inválido']);
    exit();
}

try {
    // Buscar configurações
    $stmt = $conn->query("SELECT * FROM configuracoes LIMIT 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);

    // Buscar agendamentos selecionados
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    $stmt = $conn->prepare("
        SELECT a.*, dl.data as data_liberada
        FROM agendamentos a 
        JOIN datas_liberadas dl ON a.data_liberada_id = dl.id
        WHERE a.id IN ($placeholders)
    ");
    $stmt->execute($ids);
    $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Atualizar status dos agendamentos
    $stmt = $conn->prepare("
        UPDATE agendamentos 
        SET status = ?, 
            updated_at = NOW() 
        WHERE id IN ($placeholders)
    ");
    $params = array_merge([$status], $ids);
    $stmt->execute($params);

    // Enviar emails de notificação
    
    /*
    foreach ($agendamentos as $agendamento) {
        $assunto = "Atualização de Agendamento - Sistema BANT";
        $mensagem = "
            <h2>Status do Agendamento Atualizado</h2>
            <p>Olá {$agendamento['nome_completo']},</p>
            <p>O status do seu agendamento foi atualizado.</p>
            <p><strong>Data:</strong> " . date('d/m/Y', strtotime($agendamento['data_liberada'])) . "</p>
            <p><strong>Novo Status:</strong> " . ucfirst($status) . "</p>
        ";

        // Enviar email para o solicitante
        enviarEmail($agendamento['email'], $assunto, $mensagem);

        // Enviar email para a comunicação social
        enviarEmail($config['email_comunicacao'], $assunto, $mensagem);
    }
    */
    echo json_encode(['success' => true, 'message' => 'Status atualizado com sucesso']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar status: ' . $e->getMessage()]);
} 