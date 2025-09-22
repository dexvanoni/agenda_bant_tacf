<?php
// Limpar qualquer output anterior
ob_clean();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Verificar se o usuário está logado
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Não autorizado']);
    exit;
}

try {
    // Incluir configuração do banco de dados
    require_once '../config/database.php';
    
    // Usar a variável $conn do database.php
    $pdo = $conn;
    
    // Verificar se a conexão foi estabelecida
    if (!$pdo) {
        throw new Exception('Erro na conexão com o banco de dados');
    }
    
    // Obter dados do JSON
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Dados inválidos');
    }
    
    $id = $input['id'] ?? null;
    $novaDataTeste = $input['nova_data_teste'] ?? null;
    
    // Validar dados
    if (!$id || !$novaDataTeste) {
        throw new Exception('ID e nova data são obrigatórios');
    }
    
    // Validar formato da data
    $dataFormatada = DateTime::createFromFormat('Y-m-d', $novaDataTeste);
    if (!$dataFormatada || $dataFormatada->format('Y-m-d') !== $novaDataTeste) {
        throw new Exception('Formato de data inválido');
    }
    
    // Verificar se o agendamento existe e obter dados atuais
    $stmt = $pdo->prepare("
        SELECT a.id, a.observacoes, a.data_liberada_id, dl.data as data_atual
        FROM agendamentos a
        JOIN datas_liberadas dl ON a.data_liberada_id = dl.id
        WHERE a.id = ?
    ");
    $stmt->execute([$id]);
    $agendamento = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$agendamento) {
        throw new Exception('Agendamento não encontrado');
    }
    
    // Verificar se já existe uma data liberada para a nova data
    $stmt = $pdo->prepare("SELECT id FROM datas_liberadas WHERE data = ?");
    $stmt->execute([$novaDataTeste]);
    $novaDataLiberada = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$novaDataLiberada) {
        throw new Exception('A data selecionada não está liberada para agendamentos. Entre em contato com o administrador para liberar esta data.');
    }
    
    // Preparar observação automática
    $dataAtualFormatada = date('d/m/Y', strtotime($agendamento['data_atual']));
    $novaDataFormatada = date('d/m/Y', strtotime($novaDataTeste));
    $observacaoAutomatica = " - DATA DO TESTE ALTERADA PELO ADMINISTRADOR DE {$dataAtualFormatada} PARA {$novaDataFormatada}.";
    
    // Concatenar com observações existentes se houver
    $observacoesFinais = $agendamento['observacoes'];
    if (!empty($observacoesFinais)) {
        $observacoesFinais .= "\n\n" . $observacaoAutomatica;
    } else {
        $observacoesFinais = $observacaoAutomatica;
    }
    
    // Atualizar o agendamento com a nova data liberada e as observações
    $stmt = $pdo->prepare("
        UPDATE agendamentos 
        SET data_liberada_id = ?, 
            observacoes = ?,
            updated_at = NOW()
        WHERE id = ?
    ");
    
    $resultado = $stmt->execute([$novaDataLiberada['id'], $observacoesFinais, $id]);
    
    if ($resultado) {
        echo json_encode([
            'success' => true, 
            'message' => 'Data do teste alterada com sucesso',
            'nova_data' => $novaDataTeste,
            'nova_data_formatada' => $novaDataFormatada,
            'data_anterior_formatada' => $dataAtualFormatada
        ]);
    } else {
        throw new Exception('Erro ao atualizar a data do teste');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?>
