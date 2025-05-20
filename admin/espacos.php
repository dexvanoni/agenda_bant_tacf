<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $stmt = $conn->prepare("
                    INSERT INTO espacos (nome, descricao, capacidade, status)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([
                    $_POST['nome'],
                    $_POST['descricao'],
                    $_POST['capacidade'],
                    $_POST['status']
                ]);
                break;

            case 'update':
                $stmt = $conn->prepare("
                    UPDATE espacos 
                    SET nome = ?, descricao = ?, capacidade = ?, status = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $_POST['nome'],
                    $_POST['descricao'],
                    $_POST['capacidade'],
                    $_POST['status'],
                    $_POST['id']
                ]);
                break;

            case 'delete':
                $stmt = $conn->prepare("DELETE FROM espacos WHERE id = ?");
                $stmt->execute([$_POST['id']]);
                break;
        }
        
        header('Location: espacos.php');
        exit();
    }
}

// Buscar espaços
$stmt = $conn->query("SELECT * FROM espacos ORDER BY nome");
$espacos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Espaços - BANT</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
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
                    <h1>Gerenciar Espaços</h1>
                    <p class="mb-0">Base Aérea de Natal</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="index.php" class="btn btn-light me-2">Voltar</a>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalEspaco">
                        <i class="fas fa-plus"></i> Novo Espaço
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Conteúdo Principal -->
    <main class="container">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="tabelaEspacos">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Descrição</th>
                                <th>Capacidade</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($espacos as $espaco): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($espaco['nome']); ?></td>
                                <td><?php echo htmlspecialchars($espaco['descricao']); ?></td>
                                <td><?php echo $espaco['capacidade']; ?> pessoas</td>
                                <td>
                                    <span class="badge bg-<?php echo $espaco['status'] === 'ativo' ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($espaco['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary" 
                                            onclick="editarEspaco(<?php echo htmlspecialchars(json_encode($espaco)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" 
                                            onclick="confirmarExclusao(<?php echo $espaco['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal Espaço -->
    <div class="modal fade" id="modalEspaco" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Espaço</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        <input type="hidden" name="id" id="espaco_id">
                        
                        <div class="mb-3">
                            <label class="form-label">Nome</label>
                            <input type="text" class="form-control" name="nome" id="espaco_nome" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Descrição</label>
                            <textarea class="form-control" name="descricao" id="espaco_descricao" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Capacidade</label>
                            <input type="number" class="form-control" name="capacidade" id="espaco_capacidade" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" id="espaco_status" required>
                                <option value="ativo">Ativo</option>
                                <option value="inativo">Inativo</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmação de Exclusão -->
    <div class="modal fade" id="modalConfirmacao" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Tem certeza que deseja excluir este espaço?</p>
                </div>
                <div class="modal-footer">
                    <form method="POST">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="espaco_excluir_id">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Excluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-light mt-5 py-3">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Base Aérea de Natal. Todos os direitos reservados.</p>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#tabelaEspacos').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json'
                }
            });
        });

        function editarEspaco(espaco) {
            document.getElementById('espaco_id').value = espaco.id;
            document.getElementById('espaco_nome').value = espaco.nome;
            document.getElementById('espaco_descricao').value = espaco.descricao;
            document.getElementById('espaco_capacidade').value = espaco.capacidade;
            document.getElementById('espaco_status').value = espaco.status;
            
            document.querySelector('#modalEspaco form').elements['action'].value = 'update';
            new bootstrap.Modal(document.getElementById('modalEspaco')).show();
        }

        function confirmarExclusao(id) {
            document.getElementById('espaco_excluir_id').value = id;
            new bootstrap.Modal(document.getElementById('modalConfirmacao')).show();
        }

        // Resetar formulário ao abrir modal para novo espaço
        document.getElementById('modalEspaco').addEventListener('show.bs.modal', function (event) {
            if (!event.relatedTarget) return; // Se não foi aberto pelo botão "Novo Espaço"
            
            document.getElementById('espaco_id').value = '';
            document.getElementById('espaco_nome').value = '';
            document.getElementById('espaco_descricao').value = '';
            document.getElementById('espaco_capacidade').value = '';
            document.getElementById('espaco_status').value = 'ativo';
            document.querySelector('#modalEspaco form').elements['action'].value = 'create';
        });
    </script>
</body>
</html> 