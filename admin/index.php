<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Buscar estatísticas
$stmt = $conn->query("SELECT COUNT(*) FROM agendamentos");
$total_agendamentos = $stmt->fetchColumn();

$stmt = $conn->query("SELECT COUNT(*) FROM agendamentos WHERE status = 'pendente'");
$agendamentos_pendentes = $stmt->fetchColumn();

$stmt = $conn->query("SELECT COUNT(*) FROM espacos WHERE status = 'ativo'");
$espacos_ativos = $stmt->fetchColumn();

// Buscar últimos agendamentos
$stmt = $conn->query("
    SELECT a.*, e.nome as espaco_nome 
    FROM agendamentos a 
    JOIN espacos e ON a.espaco_id = e.id 
    ORDER BY a.created_at DESC 
    LIMIT 5
");
$ultimos_agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - BANT</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .header-bant {
            background-color: #1a237e;
            color: white;
            padding: 1rem 0;
            margin-bottom: 2rem;
        }
        .card-stats {
            transition: transform 0.2s;
        }
        .card-stats:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header-bant">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1>Painel Administrativo</h1>
                    <p class="mb-0">Base Aérea de Natal</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="../index.php" class="btn btn-light me-2">Ver Site</a>
                    <a href="logout.php" class="btn btn-danger">Sair</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Conteúdo Principal -->
    <main class="container">
        <!-- Menu -->
        <div class="row mb-4">
            <div class="col">
                <div class="btn-group">
                    <a href="espacos.php" class="btn btn-primary">
                        <i class="fas fa-building"></i> Espaços
                    </a>
                    <a href="configuracoes.php" class="btn btn-primary">
                        <i class="fas fa-cog"></i> Configurações
                    </a>
                    <a href="relatorios.php" class="btn btn-primary">
                        <i class="fas fa-chart-bar"></i> Relatórios
                    </a>
                </div>
            </div>
        </div>

        <!-- Estatísticas -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card card-stats bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total de Agendamentos</h5>
                        <p class="card-text display-4"><?php echo $total_agendamentos; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-stats bg-warning text-white">
                    <div class="card-body">
                        <h5 class="card-title">Agendamentos Pendentes</h5>
                        <p class="card-text display-4"><?php echo $agendamentos_pendentes; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-stats bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Espaços Ativos</h5>
                        <p class="card-text display-4"><?php echo $espacos_ativos; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Últimos Agendamentos -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Últimos Agendamentos</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Evento</th>
                                <th>Espaço</th>
                                <th>Solicitante</th>
                                <th>Data</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ultimos_agendamentos as $agendamento): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($agendamento['nome_evento']); ?></td>
                                <td><?php echo htmlspecialchars($agendamento['espaco_nome']); ?></td>
                                <td><?php echo htmlspecialchars($agendamento['nome_solicitante']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($agendamento['data_inicio'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $agendamento['status'] === 'aprovado' ? 'success' : 
                                            ($agendamento['status'] === 'pendente' ? 'warning' : 'danger'); 
                                    ?>">
                                        <?php echo ucfirst($agendamento['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="visualizar_agendamento.php?id=<?php echo $agendamento['id']; ?>" 
                                       class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-light mt-5 py-3">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Base Aérea de Natal. Todos os direitos reservados.</p>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 