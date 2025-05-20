<?php
// Verificar requisitos do sistema
$requisitos = [
    'PHP >= 8.0' => version_compare(PHP_VERSION, '8.0.0', '>='),
    'Extensão PDO' => extension_loaded('pdo'),
    'Extensão PDO MySQL' => extension_loaded('pdo_mysql'),
    'Extensão OpenSSL' => extension_loaded('openssl'),
    'Extensão Mbstring' => extension_loaded('mbstring'),
    'Composer' => file_exists('composer.phar') || shell_exec('composer --version') !== null
];

$todos_requisitos_ok = !in_array(false, $requisitos);

// Processar instalação
$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // 1. Instalar Composer se não estiver instalado
        if (!file_exists('composer.phar') && shell_exec('composer --version') === null) {
            $mensagem .= "Instalando Composer...<br>";
            $composer_install = shell_exec('php -r "copy(\'https://getcomposer.org/installer\', \'composer-setup.php\');"');
            $composer_install .= shell_exec('php composer-setup.php');
            $composer_install .= shell_exec('php -r "unlink(\'composer-setup.php\');"');
            $mensagem .= "Composer instalado com sucesso!<br>";
        }

        // 2. Instalar dependências via Composer
        $mensagem .= "Instalando dependências via Composer...<br>";
        $composer_install = shell_exec('composer install');
        $mensagem .= "Dependências instaladas com sucesso!<br>";

        // 3. Criar banco de dados
        $mensagem .= "Criando banco de dados...<br>";
        $sql = file_get_contents('database/schema.sql');
        $pdo = new PDO("mysql:host=localhost", "root", "1q2w3e4r");
        $pdo->exec($sql);
        $mensagem .= "Banco de dados criado com sucesso!<br>";

        // 4. Configurar arquivo de email
        $mensagem .= "Configurando arquivo de email...<br>";
        $email_config = file_get_contents('config/email.php');
        $email_config = str_replace(
            ['seu-email@gmail.com', 'sua-senha-de-app'],
            [$_POST['email'], $_POST['senha_app']],
            $email_config
        );
        file_put_contents('config/email.php', $email_config);
        $mensagem .= "Configuração de email concluída!<br>";

        // 5. Criar usuário admin
        $mensagem .= "Criando usuário administrador...<br>";
        $pdo = new PDO("mysql:host=localhost;dbname=agenda_bant", "root", "1q2w3e4r");
        $senha_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO usuarios (username, password, tipo) VALUES (?, ?, 'admin')");
        $stmt->execute(['admin', $senha_hash]);
        $mensagem .= "Usuário administrador criado com sucesso!<br>";

        $tipo_mensagem = 'success';
    } catch (Exception $e) {
        $mensagem = "Erro durante a instalação: " . $e->getMessage();
        $tipo_mensagem = 'danger';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalação - Sistema de Agendamento BANT</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
                    <h1>Instalação do Sistema</h1>
                    <p class="mb-0">Base Aérea de Natal</p>
                </div>
            </div>
        </div>
    </header>

    <!-- Conteúdo Principal -->
    <main class="container">
        <?php if ($mensagem): ?>
        <div class="alert alert-<?php echo $tipo_mensagem; ?> alert-dismissible fade show" role="alert">
            <?php echo $mensagem; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Verificação de Requisitos -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Verificação de Requisitos</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Requisito</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requisitos as $requisito => $status): ?>
                            <tr>
                                <td><?php echo $requisito; ?></td>
                                <td>
                                    <?php if ($status): ?>
                                    <span class="badge bg-success">OK</span>
                                    <?php else: ?>
                                    <span class="badge bg-danger">Falha</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php if ($todos_requisitos_ok): ?>
        <!-- Formulário de Instalação -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Configuração do Sistema</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Email do Gmail</label>
                        <input type="email" class="form-control" name="email" required>
                        <div class="form-text">
                            Email que será usado para enviar as notificações
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Senha de App do Gmail</label>
                        <input type="password" class="form-control" name="senha_app" required>
                        <div class="form-text">
                            <a href="https://support.google.com/accounts/answer/185833" target="_blank">
                                Como gerar uma senha de app do Gmail?
                            </a>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Iniciar Instalação</button>
                </form>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-danger">
            Por favor, corrija os requisitos que falharam antes de prosseguir com a instalação.
        </div>
        <?php endif; ?>
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