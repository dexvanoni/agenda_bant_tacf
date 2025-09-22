<?php
require_once 'config/database.php';

header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID do agendamento não fornecido ou inválido'
    ]);
    exit;
}

try {
    $agendamento_id = (int)$_GET['id'];
    
    $stmt = $conn->prepare("
        SELECT 
            a.id,
            a.posto_graduacao,
            a.nome_completo,
            a.nome_guerra,
            a.email,
            a.contato,
            a.observacoes,
            a.data_inicio,
            a.data_fim,
            a.status,
            a.created_at,
            a.updated_at,
            dl.data as data_teste,
            dl.limite_agendamentos
        FROM agendamentos a
        JOIN datas_liberadas dl ON a.data_liberada_id = dl.id
        WHERE a.id = ?
    ");
    
    $stmt->execute([$agendamento_id]);
    $agendamento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$agendamento) {
        echo json_encode([
            'success' => false,
            'message' => 'Agendamento não encontrado'
        ]);
        exit;
    }
    
    // Formatar dados
    $resultado = [
        'id' => $agendamento['id'],
        'posto_graduacao' => $agendamento['posto_graduacao'],
        'nome_completo' => $agendamento['nome_completo'],
        'nome_guerra' => $agendamento['nome_guerra'],
        'email' => $agendamento['email'],
        'contato' => $agendamento['contato'],
        'observacoes' => $agendamento['observacoes'],
        'data_teste' => $agendamento['data_teste'],
        'data_teste_formatada' => date('d/m/Y', strtotime($agendamento['data_teste'])),
        'status' => $agendamento['status'],
        'status_texto' => ucfirst($agendamento['status']),
        'data_agendamento' => date('d/m/Y H:i', strtotime($agendamento['created_at'])),
        'data_atualizacao' => date('d/m/Y H:i', strtotime($agendamento['updated_at'])),
        'limite_agendamentos' => $agendamento['limite_agendamentos']
    ];
    
    echo json_encode([
        'success' => true,
        'data' => $resultado
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar detalhes: ' . $e->getMessage()
    ]);
}
?>
