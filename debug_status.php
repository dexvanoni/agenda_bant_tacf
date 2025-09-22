<?php
require_once 'config/database.php';

// Log de debug para verificar quando o status está sendo alterado
$log_file = 'status_debug.log';

// Função para log
function logDebug($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND | LOCK_EX);
}

// Verificar se há algum agendamento com status cancelado
$stmt = $conn->query("SELECT id, status, updated_at, cpf, nome_guerra FROM agendamentos WHERE status = 'cancelado' ORDER BY updated_at DESC");
$cancelados = $stmt->fetchAll(PDO::FETCH_ASSOC);

logDebug("=== VERIFICAÇÃO DE STATUS CANCELADO ===");
logDebug("Total de agendamentos cancelados: " . count($cancelados));

foreach ($cancelados as $agendamento) {
    logDebug("ID: {$agendamento['id']}, Status: {$agendamento['status']}, Updated: {$agendamento['updated_at']}, CPF: {$agendamento['cpf']}, Nome: {$agendamento['nome_guerra']}");
}

// Verificar histórico de mudanças (se houver uma tabela de log)
logDebug("=== FIM DA VERIFICAÇÃO ===");

echo "Debug executado. Verifique o arquivo status_debug.log para detalhes.";
?>
