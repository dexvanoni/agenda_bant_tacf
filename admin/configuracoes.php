<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("
        UPDATE configuracoes 
        SET email_comunicacao = ?,
            dias_minimos_reagendamento = ?,
            max_reagendamentos_6meses = ?
        WHERE id = ?
    ");
    
    $stmt->execute([
        $_POST['email_comunicacao'],
        $_POST['dias_minimos_reagendamento'],
        $_POST['max_reagendamentos_6meses'],
        $_POST['id']
    ]);
    
    header('Location: configuracoes.php?success=1');
    exit();
}

// Buscar configurações
$stmt = $conn->query("SELECT * FROM configuracoes LIMIT 1");
$config = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações - BANT</title>
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
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header-bant">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1>Configurações</h1>
                    <p class="mb-0">Base Aérea de Natal</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="index.php" class="btn btn-light">Voltar</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Conteúdo Principal -->
    <main class="container">
        <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Configurações atualizadas com sucesso!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="id" value="<?php echo $config['id']; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Email da SEF</label>
                        <input type="email" class="form-control" name="email_comunicacao" 
                               value="<?php echo $config['email_comunicacao']; ?>" required>
                        <div class="form-text">
                            Email que receberá as notificações de novos agendamentos e reagendamentos
                        </div>
                    </div>

                    <hr class="my-4">
                    <h5 class="mb-3">Configurações de Reagendamento</h5>
                    
                    <div class="mb-3">
                        <label class="form-label">Dias Mínimos para Reagendamento</label>
                        <input type="number" class="form-control" name="dias_minimos_reagendamento" 
                               value="<?php echo isset($config['dias_minimos_reagendamento']) ? $config['dias_minimos_reagendamento'] : 3; ?>" 
                               min="1" required>
                        <div class="form-text">
                            Número mínimo de dias antes da data do teste para permitir reagendamento. Exemplo: se configurado como 3, um teste agendado para dia 15 só poderá ser reagendado até o dia 12.
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Máximo de Reagendamentos em 6 Meses</label>
                        <input type="number" class="form-control" name="max_reagendamentos_6meses" 
                               value="<?php echo isset($config['max_reagendamentos_6meses']) ? $config['max_reagendamentos_6meses'] : 1; ?>" 
                               min="1" required>
                        <div class="form-text">
                            Número máximo de reagendamentos que um mesmo usuário pode realizar em um período de 6 meses.
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Salvar Configurações</button>
                </form>
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