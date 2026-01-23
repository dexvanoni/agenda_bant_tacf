<?php
require_once 'config/database.php';
require_once 'config/email.php';
require_once 'lib/LdapAuth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit();
}

try {
    // Validar dados obrigatórios
    $required_fields = [
        'agendamento_id', 'login', 'password', 'motivo_reagendamento', 'nova_data'
    ];

    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            throw new Exception("Campo obrigatório não preenchido: {$field}");
        }
    }

    $agendamentoId = (int)$_POST['agendamento_id'];
    $login = trim($_POST['login']);
    $password = $_POST['password'];
    $motivoReagendamento = trim($_POST['motivo_reagendamento']);
    $novaData = $_POST['nova_data'];

    // Validar formato da data
    $dataNova = DateTime::createFromFormat('Y-m-d', $novaData);
    if (!$dataNova) {
        throw new Exception("Data inválida");
    }

    // Autenticar via LDAP
    $ldapAuth = new LdapAuth();
    if (!$ldapAuth->authenticate($login, $password)) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Credenciais inválidas. Verifique seu login e senha do domínio.'
        ]);
        exit();
    }

    // Buscar agendamento original
    $stmt = $conn->prepare("
        SELECT 
            a.*,
            dl.data as data_teste_original,
            dl.limite_agendamentos
        FROM agendamentos a
        JOIN datas_liberadas dl ON a.data_liberada_id = dl.id
        WHERE a.id = ?
    ");
    
    $stmt->execute([$agendamentoId]);
    $agendamentoOriginal = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$agendamentoOriginal) {
        throw new Exception("Agendamento original não encontrado");
    }

    // Verificar se o status permite reagendamento
    if (!in_array($agendamentoOriginal['status'], ['aprovado', 'pendente'])) {
        throw new Exception("Este agendamento não pode ser reagendado");
    }

    // Validar se a nova data está liberada e se há vagas
    $stmt = $conn->prepare("SELECT id, limite_agendamentos FROM datas_liberadas WHERE data = ?");
    $stmt->execute([$novaData]);
    $dataLiberada = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$dataLiberada) {
        throw new Exception('Esta data não está liberada para agendamento.');
    }

    // Contar agendamentos já feitos para esta data (excluindo cancelados)
    $stmt = $conn->prepare("SELECT COUNT(*) FROM agendamentos WHERE data_liberada_id = ? AND status != 'cancelado'");
    $stmt->execute([$dataLiberada['id']]);
    $totalAgendamentos = $stmt->fetchColumn();
    
    if ($totalAgendamentos >= $dataLiberada['limite_agendamentos']) {
        throw new Exception('Não há mais vagas para esta data.');
    }

    // Verificar se não está tentando reagendar para a mesma data
    if ($novaData === $agendamentoOriginal['data_inicio']) {
        throw new Exception('A nova data não pode ser a mesma da data original.');
    }

    // Iniciar transação
    $conn->beginTransaction();

    try {
        // Cancelar agendamento original
        $stmt = $conn->prepare("UPDATE agendamentos SET status = 'cancelado' WHERE id = ?");
        $stmt->execute([$agendamentoId]);

        // Criar novo agendamento vinculado ao original
        $stmt = $conn->prepare("
            INSERT INTO agendamentos (
                agendamento_original_id,
                data_liberada_id,
                posto_graduacao,
                nome_completo,
                nome_guerra,
                cpf,
                email,
                contato,
                observacoes,
                motivo_reagendamento,
                data_inicio,
                data_fim,
                status,
                data_reagendamento,
                usuario_reagendamento
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendente', NOW(), ?)
        ");

        $stmt->execute([
            $agendamentoId,
            $dataLiberada['id'],
            $agendamentoOriginal['posto_graduacao'],
            $agendamentoOriginal['nome_completo'],
            $agendamentoOriginal['nome_guerra'],
            $agendamentoOriginal['cpf'],
            $agendamentoOriginal['email'],
            $agendamentoOriginal['contato'],
            $agendamentoOriginal['observacoes'],
            $motivoReagendamento,
            $novaData,
            $novaData,
            $login
        ]);

        $novoAgendamentoId = $conn->lastInsertId();

        // Buscar configurações
        $stmt = $conn->query("SELECT * FROM configuracoes LIMIT 1");
        $config = $stmt->fetch(PDO::FETCH_ASSOC);

        // Preparar mensagens de email
        $dataOriginalFormatada = date('d/m/Y', strtotime($agendamentoOriginal['data_teste_original']));
        $dataNovaFormatada = $dataNova->format('d/m/Y');

        // Email para comunicação social
        $assuntoComunicacao = "Reagendamento de TACF";
        $mensagemComunicacao = "
            <h2>Reagendamento de TACF Realizado</h2>
            <p><strong>Agendamento Original:</strong></p>
            <ul>
                <li><strong>Data:</strong> {$dataOriginalFormatada}</li>
                <li><strong>Militar:</strong> {$agendamentoOriginal['posto_graduacao']} {$agendamentoOriginal['nome_guerra']}</li>
                <li><strong>Status Original:</strong> " . ucfirst($agendamentoOriginal['status']) . "</li>
            </ul>
            <p><strong>Novo Agendamento:</strong></p>
            <ul>
                <li><strong>Data:</strong> {$dataNovaFormatada}</li>
                <li><strong>Motivo do Reagendamento:</strong> " . nl2br(htmlspecialchars($motivoReagendamento)) . "</li>
            </ul>
            <p>O novo agendamento está com status <strong>pendente</strong> e aguarda sua aprovação.</p>
            <p>Acesse o sistema para aprovar ou cancelar este reagendamento.</p>
        ";

        // Email para o solicitante
        $assuntoSolicitante = "Confirmação de Reagendamento do TACF";
        $mensagemSolicitante = "
            <h2>Reagendamento Realizado com Sucesso</h2>
            <p>Olá {$agendamentoOriginal['posto_graduacao']} {$agendamentoOriginal['nome_guerra']},</p>
            <p>Seu reagendamento foi realizado com sucesso e está aguardando aprovação.</p>
            <p><strong>Data Original:</strong> {$dataOriginalFormatada}</p>
            <p><strong>Nova Data:</strong> {$dataNovaFormatada}</p>
            <p><strong>Motivo:</strong> " . nl2br(htmlspecialchars($motivoReagendamento)) . "</p>
            <p>Você receberá um email quando o status do seu reagendamento for atualizado.</p>
        ";

        // Enviar emails
        $statusEmailComunicacao = false;
        $statusEmailSolicitante = false;

        if (!empty($config['email_comunicacao'])) {
            $statusEmailComunicacao = enviarEmail($config['email_comunicacao'], $assuntoComunicacao, $mensagemComunicacao);
        }

        $statusEmailSolicitante = enviarEmail($agendamentoOriginal['email'], $assuntoSolicitante, $mensagemSolicitante);

        // Confirmar transação
        $conn->commit();

        // Retornar sucesso
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Reagendamento realizado com sucesso',
            'agendamento_original_id' => $agendamentoId,
            'novo_agendamento_id' => $novoAgendamentoId,
            'data_original' => $dataOriginalFormatada,
            'data_nova' => $dataNovaFormatada,
            'emails' => [
                'comunicacao' => $statusEmailComunicacao,
                'solicitante' => $statusEmailSolicitante
            ]
        ]);

    } catch (Exception $e) {
        // Reverter transação em caso de erro
        $conn->rollBack();
        throw $e;
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_type' => 'validation_error'
    ]);
}
?>

