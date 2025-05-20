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
    $required_fields = [
        'espaco_id', 'nome_solicitante', 'posto_graduacao', 'setor', 'ramal',
        'nome_evento', 'categoria_evento', 'quantidade_participantes',
        'data_inicio', 'data_fim'
    ];

    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Campo obrigatório não preenchido: {$field}");
        }
    }

	// Converter datas para o formato MySQL
    $data_inicio = new DateTime($_POST['data_inicio']);
    $data_fim = new DateTime($_POST['data_fim']);
    
    $data_inicio_mysql = $data_inicio->format('Y-m-d H:i:s');
    $data_fim_mysql = $data_fim->format('Y-m-d H:i:s');

    // Buscar configurações
    $stmt = $conn->query("SELECT * FROM configuracoes LIMIT 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);

    // Validar antecedência
    $agora = new DateTime();
    $diferenca = $agora->diff($data_inicio);
    $horas_antecedencia = ($diferenca->days * 24) + $diferenca->h;

    if ($horas_antecedencia < $config['antecedencia_horas']) {
        throw new Exception("O agendamento deve ser feito com pelo menos {$config['antecedencia_horas']} horas de antecedência");
    }

    // Validar duração máxima
    $duracao = $data_inicio->diff($data_fim);
    
    if ($duracao->h > $config['max_horas_consecutivas']) {
        throw new Exception("A duração máxima permitida é de {$config['max_horas_consecutivas']} horas");
    }

    // Verificar conflitos
    $stmt = $conn->prepare("
        SELECT COUNT(*) FROM agendamentos 
        WHERE espaco_id = ? 
        AND status != 'cancelado'
        AND (
            (data_inicio BETWEEN ? AND ?) OR
            (data_fim BETWEEN ? AND ?) OR
            (? BETWEEN data_inicio AND data_fim)
        )
    ");
    
    $stmt->execute([
        $_POST['espaco_id'],
	$data_inicio_mysql,
        $data_fim_mysql,
        $data_inicio_mysql,
        $data_fim_mysql,
        $data_inicio_mysql
    ]);
    
    if ($stmt->fetchColumn() > 0) {
        throw new Exception("Já existe um agendamento para este horário");
    }

    // Inserir agendamento
    $stmt = $conn->prepare("
        INSERT INTO agendamentos (
            espaco_id, nome_solicitante, posto_graduacao, setor, ramal,
            nome_evento, categoria_evento, quantidade_participantes,
            apoio_rancho, apoio_ti, observacoes, data_inicio, data_fim
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $_POST['espaco_id'],
        $_POST['nome_solicitante'],
        $_POST['posto_graduacao'],
        $_POST['setor'],
        $_POST['ramal'],
        $_POST['nome_evento'],
        $_POST['categoria_evento'],
        $_POST['quantidade_participantes'],
        isset($_POST['apoio_rancho']) ? 1 : 0,
        isset($_POST['apoio_ti']) ? 1 : 0,
	isset($_POST['observacoes']) ? $_POST['observacoes'] : null,
	$data_inicio_mysql,
        $data_fim_mysql
    ]);

	$agendamento_id = $conn->lastInsertId();

    // Buscar informações do espaço
    $stmt = $conn->prepare("SELECT nome FROM espacos WHERE id = ?");
    $stmt->execute([$_POST['espaco_id']]);
    $espaco = $stmt->fetch(PDO::FETCH_ASSOC);

    // Enviar email para a comunicação social
    $assunto = "Novo Agendamento - Sistema BANT";
    $mensagem = "
        <h2>Novo Agendamento Realizado</h2>
        <p><strong>Evento:</strong> {$_POST['nome_evento']}</p>
        <p><strong>Espaço:</strong> {$espaco['nome']}</p>
	<p><strong>Data:</strong> " . $data_inicio->format('d/m/Y') . "</p>
        <p><strong>Horário:</strong> " . $data_inicio->format('H:i') . " às " . $data_fim->format('H:i') . "</p>
        <p><strong>Solicitante:</strong> {$_POST['posto_graduacao']} {$_POST['nome_solicitante']}</p>
        <p><strong>Setor:</strong> {$_POST['setor']}</p>
        <p><strong>Ramal:</strong> {$_POST['ramal']}</p>
        <p><strong>Número de Participantes:</strong> {$_POST['quantidade_participantes']}</p>
        <p><strong>Apoio Rancho:</strong> " . (isset($_POST['apoio_rancho']) ? "Sim" : "Não") . "</p>
        <p><strong>Apoio TI:</strong> " . (isset($_POST['apoio_ti']) ? "Sim" : "Não") . "</p>
	<p><strong>Observações:</strong> " . (isset($_POST['observacoes']) ? $_POST['observacoes'] : "Nenhuma") . "</p>
        <p>Acesse o sistema para aprovar ou cancelar este agendamento.</p>
    ";

    // Enviar email para a comunicação social
    enviarEmail('dex.vanoni@gmail.com', $assunto, $mensagem);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 
