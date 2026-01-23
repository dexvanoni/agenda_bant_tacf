<?php
require_once 'config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit();
}

if (!isset($_POST['cpf']) || empty($_POST['cpf']) || !isset($_POST['email']) || empty($_POST['email'])) {
    echo json_encode(['success' => false, 'message' => 'CPF e Email são obrigatórios']);
    exit();
}

try {
    $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf']); // Remove caracteres não numéricos
    $email = trim($_POST['email']);
    
    if (strlen($cpf) !== 11) {
        echo json_encode(['success' => false, 'message' => 'CPF inválido']);
        exit();
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email inválido']);
        exit();
    }
    
    // Buscar configurações
    $stmt = $conn->query("SELECT dias_minimos_reagendamento FROM configuracoes LIMIT 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);
    $diasMinimos = $config['dias_minimos_reagendamento'] ?? 3;
    
    // Buscar agendamentos futuros (aprovados ou pendentes) para este CPF e email
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
            dl.data as data_teste,
            dl.limite_agendamentos,
            DATEDIFF(dl.data, CURDATE()) as dias_restantes
        FROM agendamentos a
        JOIN datas_liberadas dl ON a.data_liberada_id = dl.id
        WHERE a.cpf = ? 
            AND a.email = ?
            AND a.status IN ('aprovado', 'pendente')
            AND dl.data >= CURDATE()
        ORDER BY dl.data ASC
        LIMIT 1
    ");
    
    $stmt->execute([$cpf, $email]);
    $agendamento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$agendamento) {
        echo json_encode([
            'success' => false,
            'message' => 'Nenhum agendamento futuro encontrado para este CPF e Email. Verifique se os dados estão corretos e se o agendamento está aprovado ou pendente.'
        ]);
        exit();
    }
    
    // Verificar se está dentro do prazo mínimo para reagendamento
    $diasRestantes = (int)$agendamento['dias_restantes'];
    
    if ($diasRestantes < $diasMinimos) {
        echo json_encode([
            'success' => false,
            'message' => "Não é possível reagendar. O reagendamento deve ser feito com pelo menos {$diasMinimos} dias de antecedência. Faltam apenas {$diasRestantes} dia(s) para o teste.",
            'agendamento' => [
                'id' => $agendamento['id'],
                'data_teste' => date('d/m/Y', strtotime($agendamento['data_teste'])),
                'dias_restantes' => $diasRestantes
            ]
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
    
    $stmt->execute([$cpf]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalReagendamentos = (int)$resultado['total_reagendamentos'];
    
    // Buscar limite configurado
    $stmt = $conn->query("SELECT max_reagendamentos_6meses FROM configuracoes LIMIT 1");
    $configLimite = $stmt->fetch(PDO::FETCH_ASSOC);
    $maxReagendamentos = $configLimite['max_reagendamentos_6meses'] ?? 1;
    
    if ($totalReagendamentos >= $maxReagendamentos) {
        echo json_encode([
            'success' => false,
            'message' => "Você já realizou {$totalReagendamentos} reagendamento(s) nos últimos 6 meses. O limite máximo permitido é de {$maxReagendamentos} reagendamento(s).",
            'total_reagendamentos' => $totalReagendamentos,
            'max_reagendamentos' => $maxReagendamentos
        ]);
        exit();
    }
    
    // Retornar agendamento encontrado e válido para reagendamento
    echo json_encode([
        'success' => true,
        'message' => 'Agendamento encontrado e válido para reagendamento',
        'agendamento' => [
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

