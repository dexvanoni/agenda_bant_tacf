<?php
require_once '../config/database.php';

$data_filtro = isset($_GET['data']) ? $_GET['data'] : '';
$where = '';
$params = [];
if ($data_filtro) {
    $where = 'WHERE dl.data = ?';
    $params[] = $data_filtro;
}

$stmt = $conn->prepare("
    SELECT a.*, dl.data as data_liberada, dl.limite_agendamentos
    FROM agendamentos a
    JOIN datas_liberadas dl ON a.data_liberada_id = dl.id
    $where
    ORDER BY dl.data DESC, a.created_at DESC
");
$stmt->execute($params);
$agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=relatorio_agendamentos.csv');
$output = fopen('php://output', 'w');
fputcsv($output, ['Data', 'Posto/Graduação', 'Nome Completo', 'Nome de Guerra', 'Email', 'Contato', 'Observações', 'Status', 'Data/Hora Agendamento']);
foreach ($agendamentos as $a) {
    fputcsv($output, [
        $a['data_liberada'],
        $a['posto_graduacao'],
        $a['nome_completo'],
        $a['nome_guerra'],
        $a['email'],
        $a['contato'],
        $a['observacoes'],
        $a['status'],
        $a['created_at']
    ]);
}
fclose($output);
exit; 