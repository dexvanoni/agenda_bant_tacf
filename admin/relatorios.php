<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Filtro de data
$data_filtro = isset($_GET['data']) ? $_GET['data'] : '';
$where = '';
$params = [];
if ($data_filtro) {
    $where = 'WHERE dl.data = ?';
    $params[] = $data_filtro;
}

// Buscar agendamentos detalhados
$stmt = $conn->prepare("
    SELECT a.*, dl.data as data_liberada, dl.limite_agendamentos
    FROM agendamentos a
    JOIN datas_liberadas dl ON a.data_liberada_id = dl.id
    $where
    ORDER BY dl.data DESC, a.created_at DESC
");
$stmt->execute($params);
$agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Agendamentos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Relatório Detalhado de Agendamentos</h2>
        <a href="index.php" class="btn btn-secondary mb-3"><i class="fas fa-arrow-left"></i> Voltar</a>
        <form class="row g-3 mb-4" method="GET">
            <div class="col-md-4">
                <label for="data" class="form-label">Filtrar por Data</label>
                <input type="date" class="form-control" id="data" name="data" value="<?= htmlspecialchars($data_filtro) ?>">
            </div>
            <div class="col-md-2 align-self-end">
                <button type="submit" class="btn btn-primary w-100">Filtrar</button>
            </div>
            <div class="col-md-2 align-self-end">
                <a href="relatorios.php" class="btn btn-outline-secondary w-100">Limpar</a>
            </div>
        </form>
        
        <div class="card">
            <div class="card-body">
                <table id="tabelaRelatorio" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Data do TACF</th>
                            <th>P./Grad.</th>
                            <th>Nome Completo</th>
                            <th>Nome de Guerra</th>
                            <th>Email</th>
                            <th>Contato</th>
                            <th>Observações</th>
                            <th>Status</th>
                            <th>Data/Hora Agendamento</th>
     			    <th>Assinatura</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($agendamentos as $a): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($a['data_liberada'])) ?></td>
                            <td><?= htmlspecialchars($a['posto_graduacao']) ?></td>
                            <td><?= htmlspecialchars($a['nome_completo']) ?></td>
                            <td><?= htmlspecialchars($a['nome_guerra']) ?></td>
                            <td><?= htmlspecialchars($a['email']) ?></td>
                            <td><?= htmlspecialchars($a['contato']) ?></td>
                            <td><?= htmlspecialchars($a['observacoes']) ?></td>
                            <td>
                                <span class="badge bg-<?= $a['status'] === 'aprovado' ? 'success' : ($a['status'] === 'pendente' ? 'warning' : 'danger') ?>">
                                    <?= ucfirst($a['status']) ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($a['created_at'])) ?></td>
			    <td>__________________________</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <!-- DataTables Buttons -->
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#tabelaRelatorio').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/pt-BR.json'
                },
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-success btn-sm',
                        title: 'Relatório de Agendamentos - BANT',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8]
                        }
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-danger btn-sm',
                        title: 'Relatório de Agendamentos - BANT',
                        exportOptions: {
                            columns: [0, 1, 3, 5, 6, 8, 9]
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> Imprimir',
                        className: 'btn btn-info btn-sm',
                        title: 'Relatório de Agendamentos - BANT',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8]
                        }
                    }
                ],
                pageLength: 25,
                order: [[0, 'desc'], [8, 'desc']],
                responsive: true,
                columnDefs: [
                    {
                        targets: [6], // Coluna de observações
                        render: function(data, type, row) {
                            if (type === 'display') {
                                return data.length > 50 ? data.substr(0, 50) + '...' : data;
                            }
                            return data;
                        }
                    }
                ]
            });
        });
    </script>
</body>
</html> 
