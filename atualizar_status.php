<?php
require_once 'config/database.php';
require_once 'config/email.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit();
}

try {
    // Validar dados
    if (!isset($_POST['agendamento_id']) || !isset($_POST['status'])) {
        throw new Exception("Dados incompletos");
    }

    $agendamento_id = (int)$_POST['agendamento_id'];
    $status = $_POST['status'];

    // Validar status
    if (!in_array($status, ['pendente', 'aprovado', 'cancelado'])) {
        throw new Exception("Status inválido");
    }

    // Buscar informações do agendamento
    $stmt = $conn->prepare("
        SELECT a.*, dl.data as data_liberada 
        FROM agendamentos a 
        JOIN datas_liberadas dl ON a.data_liberada_id = dl.id 
        WHERE a.id = ?
    ");
    $stmt->execute([$agendamento_id]);
    $agendamento = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$agendamento) {
        throw new Exception("Agendamento não encontrado");
    }

    // Atualizar status
    $stmt = $conn->prepare("UPDATE agendamentos SET status = ? WHERE id = ?");
    $stmt->execute([$status, $agendamento_id]);

    // Preparar mensagem de email baseada no status
    $status_texto = [
        'aprovado' => 'aprovado',
        'cancelado' => 'cancelado',
        'pendente' => 'colocado em análise'
    ];

    $assunto = "Atualização de Status - Agendamento Sistema BANT";
    $mensagem = "
        <h2>Status do seu Agendamento foi Atualizado</h2>
        <p>Olá {$agendamento['nome_completo']},</p>
        <p>O status do seu agendamento foi {$status_texto[$status]}.</p>
        <p><strong>Data:</strong> " . date('d/m/Y', strtotime($agendamento['data_liberada'])) . "</p>
        <p><strong>Militar:</strong> {$agendamento['posto_graduacao']} {$agendamento['nome_guerra']}</p>
    ";

    if ($status === 'cancelado') {
        $mensagem .= "<p>Seu agendamento foi cancelado. Caso precise reagendar, por favor, faça uma nova solicitação.</p>";
    } elseif ($status === 'aprovado') {
        $mensagem .= "<p>Seu agendamento foi aprovado! Você pode utilizar o espaço conforme agendado.</p>";
    } else {
        $mensagem .= "<p>Seu agendamento está em análise. Você receberá uma nova notificação quando houver uma atualização.</p>";
    }

    // Enviar email para o solicitante
    enviarEmail($agendamento['email'], $assunto, $mensagem);

    // Enviar email para a comunicação social
    $stmt = $conn->query("SELECT email_comunicacao FROM configuracoes LIMIT 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    enviarEmail($config['email_comunicacao'], $assunto, $mensagem);

    // Retornar sucesso
    echo json_encode([
        'success' => true,
        'message' => 'Status atualizado com sucesso'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 