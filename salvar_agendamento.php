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
        'posto_graduacao', 'nome_completo', 'nome_guerra', 'email', 'contato', 'data_agendamento'
    ];

    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Campo obrigatório não preenchido: {$field}");
        }
    }

    // Validar formato do email
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Email inválido");
    }

    // Validar se a data está liberada e se há vagas
    $data_agendamento = $_POST['data_agendamento'];
    $data_agendamento_dt = new DateTime($data_agendamento);
    $stmt = $conn->prepare("SELECT id, limite_agendamentos FROM datas_liberadas WHERE data = ?");
    $stmt->execute([$data_agendamento]);
    $data_liberada = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$data_liberada) {
        throw new Exception('Esta data não está liberada para agendamento.');
    }

    // Contar agendamentos já feitos para esta data (excluindo cancelados)
    $stmt = $conn->prepare("SELECT COUNT(*) FROM agendamentos WHERE data_liberada_id = ? AND status != 'cancelado'");
    $stmt->execute([$data_liberada['id']]);
    $total_agendamentos = $stmt->fetchColumn();
    if ($total_agendamentos >= $data_liberada['limite_agendamentos']) {
        throw new Exception('Não há mais vagas para esta data.');
    }

    // Inserir agendamento
    $stmt = $conn->prepare("
        INSERT INTO agendamentos (
            data_liberada_id, posto_graduacao, nome_completo, nome_guerra, email, contato, observacoes, data_inicio, data_fim
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $data_liberada['id'],
        $_POST['posto_graduacao'],
        $_POST['nome_completo'],
        $_POST['nome_guerra'],
        $_POST['email'],
        $_POST['contato'],
        $_POST['observacoes'],
        $data_agendamento,
        $data_agendamento
    ]);

    $agendamento_id = $conn->lastInsertId();

    // Buscar configurações (e-mail da comunicação)
    $stmt = $conn->query("SELECT * FROM configuracoes LIMIT 1");
    $config = $stmt->fetch(PDO::FETCH_ASSOC);

    // Enviar email para a comunicação social
    $assunto = "Novo Agendamento de TACF";
    $mensagem = "
        <h2>Novo Agendamento Realizado</h2>
        <p><strong>Data do TACF:</strong> " . $data_agendamento_dt->format('d/m/Y') . "</p>
        <p><strong>Solicitante:</strong> {$_POST['posto_graduacao']} {$_POST['nome_guerra']}</p>
        <p><strong>Email:</strong> {$_POST['email']}</p>
        <p>Acesse o sistema para aprovar ou cancelar este agendamento.</p>
    ";

        // Enviar email para a comunicação social
        if (!empty($config['email_comunicacao'])) {
            enviarEmail($config['email_comunicacao'], $assunto, $mensagem);
        }

    // Enviar email de confirmação para o solicitante
    $assunto_solicitante = "Confirmação de Agendamento do TACF";
    $mensagem_solicitante = "
        <h2>Seu Agendamento foi Registrado</h2>
        <p>Olá {$_POST['posto_graduacao']} {$_POST['nome_guerra']},</p>
        <p>Seu agendamento foi registrado com sucesso e está aguardando aprovação.</p>
        <p><strong>Data do TACF:</strong> " . $data_agendamento_dt->format('d/m/Y') . "</p>
        <p>Você receberá um email quando o status do seu agendamento for atualizado.</p>
    ";

    // Enviar emails e coletar status
    $status_email_comunicacao = null;
    if (!empty($config['email_comunicacao'])) {
        $status_email_comunicacao = enviarEmail($config['email_comunicacao'], $assunto, $mensagem);
    }
    $status_email_solicitante = enviarEmail($_POST['email'], $assunto_solicitante, $mensagem_solicitante);
    

    // Retornar sucesso com status 200
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Agendamento realizado com sucesso',
        'agendamento_id' => $agendamento_id,
        'emails' => [
            'comunicacao' => $status_email_comunicacao,
            'solicitante' => $status_email_solicitante
        ]
    ]);
    return true;
} catch (Exception $e) {
    // Retornar erro com status 400 apenas para erros de validação
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage(),
        'error_type' => 'validation_error'
    ]);
}

?> 
