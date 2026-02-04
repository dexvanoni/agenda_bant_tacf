<?php
require_once 'config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit();
}

if (!isset($_POST['agendamento_id']) || empty($_POST['agendamento_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID do agendamento é obrigatório']);
    exit();
}

try {
    $agendamentoId = (int)$_POST['agendamento_id'];
    
    // Buscar configurações
    $stmt = $conn->query("SELECT dias_minimos_reagendamento, max_reagendamentos_6meses FROM configuracoes LIMIT 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    $diasMinimos = $config['dias_minimos_reagendamento'] ?? 3;
    $maxReagendamentos = $config['max_reagendamentos_6meses'] ?? 1;
    
    // Buscar agendamento
    $stmt = $conn->prepare("
        SELECT 
            a.id,
            a.cpf,
            a.email,
            a.status,
            dl.data as data_teste,
            DATEDIFF(dl.data, CURDATE()) as dias_restantes
        FROM agendamentos a
        JOIN datas_liberadas dl ON a.data_liberada_id = dl.id
        WHERE a.id = ?
    ");
    
    $stmt->execute([$agendamentoId]);
    $agendamento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$agendamento) {
        echo json_encode(['success' => false, 'message' => 'Agendamento não encontrado']);
        exit();
    }
    
    // Verificar se o status permite reagendamento
    if (!in_array($agendamento['status'], ['aprovado', 'pendente'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Este agendamento não pode ser reagendado. Apenas agendamentos aprovados ou pendentes podem ser reagendados.'
        ]);
        exit();
    }
    
    // Verificar se a data é futura
    $diasRestantes = (int)$agendamento['dias_restantes'];
    if ($diasRestantes < 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Não é possível reagendar um teste que já foi realizado.'
        ]);
        exit();
    }
    
    // Verificar se está dentro do prazo mínimo para reagendamento
    // Regra: o limite de dias mínimos só se aplica quando o agendamento já estiver APROVADO.
    if ($agendamento['status'] === 'aprovado' && $diasRestantes < $diasMinimos) {
        echo json_encode([
            'success' => false,
            'message' => "Não é possível reagendar. Para agendamentos já aprovados, o reagendamento deve ser feito com pelo menos {$diasMinimos} dia(s) de antecedência. Faltam apenas {$diasRestantes} dia(s) para o teste.",
            'dias_restantes' => $diasRestantes,
            'dias_minimos' => $diasMinimos,
            'status_atual' => $agendamento['status']
        ]);
        exit();
    }
    
    // Verificar quantidade de reagendamentos nos últimos 6 meses
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total_reagendamentos
        FROM agendamentos
        WHERE cpf = ?
            AND agendamento_original_id IS NOT NULL
            AND data_reagendamento >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    ");
    
    $stmt->execute([$agendamento['cpf']]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalReagendamentos = (int)$resultado['total_reagendamentos'];
    
    if ($totalReagendamentos >= $maxReagendamentos) {
        echo json_encode([
            'success' => false,
            'message' => "Você já realizou {$totalReagendamentos} reagendamento(s) nos últimos 6 meses. O limite máximo permitido é de {$maxReagendamentos} reagendamento(s).",
            'total_reagendamentos' => $totalReagendamentos,
            'max_reagendamentos' => $maxReagendamentos
        ]);
        exit();
    }
    
    // Todas as validações passaram
    echo json_encode([
        'success' => true,
        'message' => 'Agendamento válido para reagendamento',
        'agendamento' => [
            'id' => $agendamento['id'],
            'data_teste' => date('d/m/Y', strtotime($agendamento['data_teste'])),
            'dias_restantes' => $diasRestantes,
            'dias_minimos' => $diasMinimos,
            'total_reagendamentos' => $totalReagendamentos,
            'max_reagendamentos' => $maxReagendamentos
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Erro interno do servidor: ' . $e->getMessage()
    ]);
}
?>

