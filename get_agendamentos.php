<?php
require_once 'config/database.php';

// Buscar todas as datas liberadas
$stmt = $conn->query("SELECT * FROM datas_liberadas ORDER BY data ASC");
$datas_liberadas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar quantidade de agendamentos por data (excluindo cancelados)
$agendamentos_por_data = [];
$stmt2 = $conn->query("SELECT data_liberada_id, COUNT(*) as total FROM agendamentos WHERE status != 'cancelado' GROUP BY data_liberada_id");
while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
    $agendamentos_por_data[$row['data_liberada_id']] = $row['total'];
}

$eventos = [];
foreach ($datas_liberadas as $data) {
    $total = isset($agendamentos_por_data[$data['id']]) ? $agendamentos_por_data[$data['id']] : 0;
    $bloqueada = $total >= $data['limite_agendamentos'];
    $eventos[] = [
        'title' => $bloqueada ? 'Sem vagas' : 'Disponível',
        'start' => $data['data'],
        'end' => $data['data'],
        'bloqueada' => $bloqueada,
        'limite' => $data['limite_agendamentos'],
        'total' => $total
    ];
}
header('Content-Type: application/json');
echo json_encode($eventos);
?> 
