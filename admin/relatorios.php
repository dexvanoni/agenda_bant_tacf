<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Filtro de data
$data_filtro = isset($_GET['data']) ? $_GET['data'] : '';
$where = 'WHERE a.status = ?';
$params = ['aprovado'];

if ($data_filtro) {
    $where .= ' AND dl.data = ?';
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
    
    <style>
        .table-responsive {
            border-radius: 0.375rem;
            overflow-x: auto;
        }
        
        #tabelaRelatorio {
            font-size: 0.9rem;
        }
        
        #tabelaRelatorio thead th {
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            background-color: #f8f9fa;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        #tabelaRelatorio tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_processing,
        .dataTables_wrapper .dataTables_paginate {
            margin-bottom: 1rem;
        }
        
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            padding: 0.375rem 0.75rem;
        }
        
        .btn-sm {
            font-size: 0.8rem;
        }
        
        .text-truncate {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        @media (max-width: 768px) {
            .table-responsive {
                font-size: 0.8rem;
            }
            
            #tabelaRelatorio th,
            #tabelaRelatorio td {
                padding: 0.5rem 0.25rem;
            }
        }
    </style>
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
        
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-table me-2"></i>Relatório de Agendamentos
                    <span class="badge bg-light text-primary ms-2"><?= count($agendamentos) ?> registros</span>
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="tabelaRelatorio" class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 100px;">Data TACF</th>
                                <th class="text-center" style="width: 80px;">P./Grad.</th>
                                <th style="min-width: 200px;">Nome Completo</th>
                                <th style="min-width: 120px;">Nome de Guerra</th>
                                <th class="text-center" style="width: 120px;">Contato</th>
                                <th class="text-center" style="width: 100px;">Status</th>
                                <th class="text-center" style="width: 140px;">Data Agendamento</th>
                                <th style="display: none;">Email</th>
                                <th style="display: none;">Observações</th>
                                <th style="display: none;">Assinatura</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($agendamentos as $a): ?>
                            <tr>
                                <td class="text-center">
                                    <span class="badge bg-info text-dark">
                                        <?= date('d/m/Y', strtotime($a['data_liberada'])) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <small class="text-muted"><?= htmlspecialchars($a['posto_graduacao']) ?></small>
                                </td>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($a['nome_completo']) ?></div>
                                </td>
                                <td>
                                    <span class="text-primary fw-semibold"><?= htmlspecialchars($a['nome_guerra']) ?></span>
                                </td>
                                <td class="text-center">
                                    <small><?= htmlspecialchars($a['contato']) ?></small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-<?= $a['status'] === 'aprovado' ? 'success' : ($a['status'] === 'pendente' ? 'warning' : 'danger') ?>">
                                        <?= ucfirst($a['status']) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <small class="text-muted">
                                        <?= date('d/m/Y H:i', strtotime($a['created_at'])) ?>
                                    </small>
                                </td>
                                <!-- Colunas ocultas para exportação -->
                                <td style="display: none;"><?= htmlspecialchars($a['email']) ?></td>
                                <td style="display: none;"><?= htmlspecialchars($a['observacoes'] ?: '---') ?></td>
                                <td style="display: none;">__________________________</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
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
                dom: '<"row"<"col-sm-12 col-md-6"B><"col-sm-12 col-md-6"f>>' +
                     '<"row"<"col-sm-12"tr>>' +
                     '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                buttons: [
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-success btn-sm me-1',
                        title: 'Relatório de Agendamentos - BANT',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9] // Todas as colunas
                        }
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-danger btn-sm me-1',
                        title: 'Relatório de Agendamentos - BANT',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        exportOptions: {
                            columns: [0, 1, 3, 4, 9] // Data TACF, P./Grad, Nome de Guerra, Contato, Assinatura
                        },
                        customize: function(doc) {
                            doc.content[1].table.widths = ['20%', '15%', '25%', '20%', '20%'];
                            doc.styles.tableHeader.fontSize = 10;
                            doc.styles.tableBodyEven.fontSize = 9;
                            doc.styles.tableBodyOdd.fontSize = 9;
                            doc.styles.tableHeader.alignment = 'center';
                            doc.styles.tableBodyEven.alignment = 'center';
                            doc.styles.tableBodyOdd.alignment = 'center';
                            
                            // Ajustar alinhamento específico para algumas colunas
                            doc.content[1].table.body.forEach(function(row, index) {
                                if (index > 0) { // Pular cabeçalho
                                    row[2].alignment = 'left'; // Nome de Guerra - alinhado à esquerda
                                    row[3].alignment = 'left'; // Contato - alinhado à esquerda
                                }
                            });
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> Imprimir',
                        className: 'btn btn-info btn-sm',
                        title: 'Relatório de Agendamentos - BANT',
                        exportOptions: {
                            columns: [0, 1, 3, 4, 9] // Data TACF, P./Grad, Nome de Guerra, Contato, Assinatura
                        },
                        customize: function(win) {
                            // Remover elementos desnecessários
                            $(win.document.body).find('table').addClass('table table-bordered');
                            $(win.document.body).find('thead th').css({
                                'background-color': '#f8f9fa',
                                'font-weight': 'bold',
                                'text-align': 'center',
                                'border': '1px solid #dee2e6'
                            });
                            $(win.document.body).find('tbody td').css({
                                'border': '1px solid #dee2e6',
                                'padding': '8px'
                            });
                            
                            // Ajustar larguras das colunas
                            $(win.document.body).find('table').css('width', '100%');
                            $(win.document.body).find('thead th:nth-child(1)').css('width', '20%'); // Data TACF
                            $(win.document.body).find('thead th:nth-child(2)').css('width', '15%'); // P./Grad
                            $(win.document.body).find('thead th:nth-child(3)').css('width', '25%'); // Nome de Guerra
                            $(win.document.body).find('thead th:nth-child(4)').css('width', '20%'); // Contato
                            $(win.document.body).find('thead th:nth-child(5)').css('width', '20%'); // Assinatura
                        }
                    }
                ],
                pageLength: 25,
                order: [[0, 'desc'], [8, 'desc']],
                responsive: {
                    details: {
                        type: 'column',
                        target: 'tr'
                    }
                },
                scrollX: true,
                autoWidth: false,
                columnDefs: [
                    {
                        targets: [0], // Data TACF
                        className: 'text-center',
                        width: '100px'
                    },
                    {
                        targets: [1], // P./Grad.
                        className: 'text-center',
                        width: '80px'
                    },
                    {
                        targets: [2], // Nome Completo
                        width: '200px'
                    },
                    {
                        targets: [3], // Nome de Guerra
                        width: '120px'
                    },
                    {
                        targets: [4], // Contato
                        className: 'text-center',
                        width: '120px'
                    },
                    {
                        targets: [5], // Status
                        className: 'text-center',
                        width: '100px'
                    },
                    {
                        targets: [6], // Data Agendamento
                        className: 'text-center',
                        width: '140px'
                    },
                    {
                        targets: [7], // Email (oculta)
                        visible: false,
                        className: 'text-center'
                    },
                    {
                        targets: [8], // Observações (oculta)
                        visible: false
                    },
                    {
                        targets: [9], // Assinatura (oculta)
                        visible: false,
                        orderable: false,
                        className: 'text-center'
                    }
                ],
                initComplete: function() {
                    // Ajustar largura da tabela após inicialização
                    this.api().columns.adjust();
                }
            });
        });
    </script>
</body>
</html> 
