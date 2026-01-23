<?php
require_once 'config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit();
}

if (!isset($_POST['cpf']) || empty($_POST['cpf'])) {
    echo json_encode(['success' => false, 'message' => 'CPF não fornecido']);
    exit();
}

try {
    $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf']); // Remove caracteres não numéricos
    
    if (strlen($cpf) !== 11) {
        echo json_encode(['success' => false, 'message' => 'CPF inválido']);
        exit();
    }
    
    // Verificar se já existe agendamento ativo para este CPF (excluindo cancelados)
    $stmt = $conn->prepare("
        SELECT id, data_inicio, status, posto_graduacao, nome_guerra 
        FROM agendamentos 
        WHERE cpf = ? AND status != 'cancelado'
        ORDER BY data_inicio DESC 
        LIMIT 1
    ");
    
    $stmt->execute([$cpf]);
    $agendamento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($agendamento) {
        $data_formatada = date('d/m/Y', strtotime($agendamento['data_inicio']));
        $status_texto = '';
        
        switch ($agendamento['status']) {
            case 'pendente':
                $status_texto = 'pendente de aprovação';
                break;
            case 'aprovado':
                $status_texto = 'aprovado';
                break;
            default:
                $status_texto = $agendamento['status'];
                break;
        }
        
        echo json_encode([
            'success' => true,
            'ja_agendado' => true,
            'message' => "O agendamento é único. Caso haja necessidade de reagendamento, solicite através de Ofício via Cadeia de Comando.",
            'agendamento' => [
                'id' => $agendamento['id'],
                'data' => $data_formatada,
                'status' => $agendamento['status'],
                'status_texto' => $status_texto,
                'posto_graduacao' => $agendamento['posto_graduacao'],
                'nome_guerra' => $agendamento['nome_guerra']
            ]
        ]);
    } else {
        // Verificar se existe agendamento cancelado para informar o usuário
        $stmt = $conn->prepare("
            SELECT id, data_inicio, status, posto_graduacao, nome_guerra 
            FROM agendamentos 
            WHERE cpf = ? AND status = 'cancelado'
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        
        $stmt->execute([$cpf]);
        $agendamento_cancelado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($agendamento_cancelado) {
            $data_cancelada = date('d/m/Y', strtotime($agendamento_cancelado['data_inicio']));
            
            echo json_encode([
                'success' => true,
                'ja_agendado' => false,
                'message' => 'CPF disponível para agendamento',
                'agendamento_anterior' => [
                    'data' => $data_cancelada,
                    'status' => 'cancelado',
                    'posto_graduacao' => $agendamento_cancelado['posto_graduacao'],
                    'nome_guerra' => $agendamento_cancelado['nome_guerra']
                ]
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'ja_agendado' => false,
                'message' => 'CPF disponível para agendamento'
            ]);
        }
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Erro interno do servidor: ' . $e->getMessage()
    ]);
}
?>
