<?php
require_once 'config/database.php';

header('Content-Type: application/json');

try {
    // Parâmetros de filtro
    $filtros = [
        'data_teste_inicio' => $_GET['data_teste_inicio'] ?? '',
        'data_teste_fim' => $_GET['data_teste_fim'] ?? '',
        'status' => $_GET['status'] ?? '',
        'data_agendamento_inicio' => $_GET['data_agendamento_inicio'] ?? '',
        'data_agendamento_fim' => $_GET['data_agendamento_fim'] ?? '',
        'nome_completo' => $_GET['nome_completo'] ?? '',
        'nome_guerra' => $_GET['nome_guerra'] ?? '',
        'posto_graduacao' => $_GET['posto_graduacao'] ?? ''
    ];

    // Construir query base
    $sql = "
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
        WHERE 1=1
    ";
    
    $params = [];
    
    // Aplicar filtros
    if (!empty($filtros['data_teste_inicio'])) {
        $sql .= " AND dl.data >= ?";
        $params[] = $filtros['data_teste_inicio'];
    }
    
    if (!empty($filtros['data_teste_fim'])) {
        $sql .= " AND dl.data <= ?";
        $params[] = $filtros['data_teste_fim'];
    }
    
    if (!empty($filtros['status'])) {
        $sql .= " AND a.status = ?";
        $params[] = $filtros['status'];
    }
    
    if (!empty($filtros['data_agendamento_inicio'])) {
        $sql .= " AND DATE(a.created_at) >= ?";
        $params[] = $filtros['data_agendamento_inicio'];
    }
    
    if (!empty($filtros['data_agendamento_fim'])) {
        $sql .= " AND DATE(a.created_at) <= ?";
        $params[] = $filtros['data_agendamento_fim'];
    }
    
    if (!empty($filtros['nome_completo'])) {
        $sql .= " AND a.nome_completo LIKE ?";
        $params[] = '%' . $filtros['nome_completo'] . '%';
    }
    
    if (!empty($filtros['nome_guerra'])) {
        $sql .= " AND a.nome_guerra LIKE ?";
        $params[] = '%' . $filtros['nome_guerra'] . '%';
    }
    
    if (!empty($filtros['posto_graduacao'])) {
        $sql .= " AND a.posto_graduacao = ?";
        $params[] = $filtros['posto_graduacao'];
    }
    
    // Ordenação
    $sql .= " ORDER BY dl.data DESC, a.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatar dados para resposta
    $resultado = [];
    foreach ($agendamentos as $agendamento) {
        $resultado[] = [
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
            'limite_agendamentos' => $agendamento['limite_agendamentos']
        ];
    }
    
    // Verificar se é uma requisição de exportação
    if (isset($_GET['export']) && $_GET['export'] == '1') {
        // Configurar headers para download CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="agendamentos_filtrados_' . date('Y-m-d_H-i-s') . '.csv"');
        
        // Criar arquivo CSV
        $output = fopen('php://output', 'w');
        
        // BOM para UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Cabeçalhos
        fputcsv($output, [
            'ID',
            'Data do Teste',
            'Posto/Graduação',
            'Nome Completo',
            'Nome de Guerra',
            'Email',
            'Contato',
            'Status',
            'Data Agendamento',
            'Observações'
        ], ';');
        
        // Dados
        foreach ($resultado as $agendamento) {
            fputcsv($output, [
                $agendamento['id'],
                $agendamento['data_teste_formatada'],
                $agendamento['posto_graduacao'],
                $agendamento['nome_completo'],
                $agendamento['nome_guerra'],
                $agendamento['email'],
                $agendamento['contato'],
                $agendamento['status_texto'],
                $agendamento['data_agendamento'],
                $agendamento['observacoes']
            ], ';');
        }
        
        fclose($output);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $resultado,
        'total' => count($resultado)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar agendamentos: ' . $e->getMessage()
    ]);
}
?>
