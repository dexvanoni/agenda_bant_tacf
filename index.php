<?php
require_once 'config/database.php';

// Buscar espaços ativos
$stmt = $conn->query("SELECT * FROM espacos WHERE status = 'ativo' ORDER BY nome");
$espacos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar últimos agendamentos para cada espaço
$ultimos_agendamentos = [];
foreach ($espacos as $espaco) {
    $stmt = $conn->prepare("
        SELECT nome_evento, data_inicio, status 
        FROM agendamentos 
        WHERE espaco_id = ? 
        AND status != 'cancelado'
        ORDER BY data_inicio DESC 
        LIMIT 5
    ");
    $stmt->execute([$espaco['id']]);
    $ultimos_agendamentos[$espaco['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Agendamento - BANT</title>
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
        .card {
            margin-bottom: 1.5rem;
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .ultimos-agendamentos {
            margin-top: 1rem;
            font-size: 0.9rem;
        }
        .ultimos-agendamentos .list-group-item {
            padding: 0.5rem 1rem;
        }
        .status-badge {
            font-size: 0.8rem;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header-bant">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1>Sistema de Agendamento</h1>
                    <p class="mb-0">Base Aérea de Natal</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="admin/login.php" class="btn btn-light">Área Administrativa</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Conteúdo Principal -->
    <main class="container">
        <div class="row">
            <?php foreach ($espacos as $espaco): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($espaco['nome']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($espaco['descricao']); ?></p>
                        <p class="card-text">
                            <small class="text-muted">
                                <i class="fas fa-users"></i> Capacidade: <?php echo $espaco['capacidade']; ?> pessoas
                            </small>
                        </p>
                        
                        <!-- Últimos Agendamentos -->
                        <div class="ultimos-agendamentos">
                            <h6 class="mb-2">Últimos Agendamentos:</h6>
                            <?php if (!empty($ultimos_agendamentos[$espaco['id']])): ?>
                                <div class="list-group">
                                    <?php foreach ($ultimos_agendamentos[$espaco['id']] as $agendamento): ?>
                                        <div class="list-group-item">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong><?php echo htmlspecialchars($agendamento['nome_evento']); ?></strong>
                                                    <br>
                                                    <small>
                                                        <?php 
                                                        $data = new DateTime($agendamento['data_inicio']);
                                                        echo $data->format('d/m/Y H:i');
                                                        ?>
                                                    </small>
                                                </div>
                                                <span class="badge <?php 
                                                    echo $agendamento['status'] === 'aprovado' ? 'bg-success' : 
                                                        ($agendamento['status'] === 'pendente' ? 'bg-warning' : 'bg-secondary');
                                                ?> status-badge">
                                                    <?php echo ucfirst($agendamento['status']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted mb-0">Nenhum agendamento recente</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="agendamento.php?espaco=<?php echo $espaco['id']; ?>" class="btn btn-primary w-100">
                            Agendar
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-light mt-5 py-3">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Base Aérea de Natal. ETIC | Desenvolvimento.</p>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
