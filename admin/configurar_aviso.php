<?php
header('Content-Type: text/html; charset=UTF-8');
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Buscar configuração atual do aviso
$stmt = $conn->query("SELECT * FROM aviso_popup WHERE ativo = 1 ORDER BY id DESC LIMIT 1");
$aviso_atual = $stmt->fetch(PDO::FETCH_ASSOC);

// Se não existir configuração, criar uma padrão
if (!$aviso_atual) {
    $stmt = $conn->prepare("INSERT INTO aviso_popup (titulo, conteudo, ativo) VALUES (?, ?, 1)");
    $stmt->execute([
        'Atenção aos horários do TACF',
        '<p class="mb-2"><strong>A Partir do dia 15 DE SETEMBRO</strong>, os horários de realização do TACF serão os seguintes:</p><ul class="mb-0"><li><strong>Segunda a Quinta-feira</strong> das 14:30h às 16h</li><li><strong>Sexta-feira</strong> das 08h às 09:30h</li></ul>'
    ]);
    
    // Buscar novamente
    $stmt = $conn->query("SELECT * FROM aviso_popup WHERE ativo = 1 ORDER BY id DESC LIMIT 1");
    $aviso_atual = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurar Aviso Popup - BANT</title>
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
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .btn-custom {
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .preview-container {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 1rem;
            background-color: #f8f9fa;
        }
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e0e0e0;
            transition: all 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: #1a237e;
            box-shadow: 0 0 0 0.2rem rgba(26, 35, 126, 0.25);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header-bant">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="fas fa-exclamation-triangle me-2"></i>Configurar Aviso Popup</h1>
                    <p class="mb-0">Configure o aviso que aparece na página de agendamento</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="index.php" class="btn btn-light me-2">
                        <i class="fas fa-arrow-left"></i> Voltar ao Painel
                    </a>
                    <a href="../index.php" class="btn btn-outline-light">Ver Site</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Conteúdo Principal -->
    <main class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">
                            <i class="fas fa-edit me-2"></i>Configuração do Aviso
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="formAviso">
                            <div class="mb-4">
                                <label for="titulo" class="form-label">
                                    <i class="fas fa-heading me-1"></i>Título do Aviso
                                </label>
                                <input type="text" class="form-control" id="titulo" name="titulo" 
                                       value="<?php echo htmlspecialchars($aviso_atual['titulo']); ?>" 
                                       placeholder="Digite o título do aviso" required>
                            </div>
                            
                            <div class="mb-4">
                                <label for="conteudo" class="form-label">
                                    <i class="fas fa-align-left me-1"></i>Conteúdo do Aviso
                                </label>
                                <textarea class="form-control" id="conteudo" name="conteudo" rows="8" required><?php echo htmlspecialchars($aviso_atual['conteudo']); ?></textarea>
                            </div>
                            
                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="ativo" name="ativo" 
                                           <?php echo $aviso_atual['ativo'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="ativo">
                                        <i class="fas fa-toggle-on me-1"></i>Aviso Ativo (será exibido na página de agendamento)
                                    </label>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-warning btn-custom">
                                    <i class="fas fa-save me-2"></i>Salvar Configuração
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-custom" id="btnPreview">
                                    <i class="fas fa-eye me-2"></i>Visualizar Preview
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-custom" id="btnReset">
                                    <i class="fas fa-undo me-2"></i>Restaurar Padrão
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>Preview do Aviso
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="preview-container" id="previewContainer">
                            <div class="alert alert-warning border-0">
                                <h6 class="alert-heading" id="previewTitulo">
                                    <?php echo htmlspecialchars($aviso_atual['titulo']); ?>
                                </h6>
                                <div id="previewConteudo">
                                    <?php echo $aviso_atual['conteudo']; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-lightbulb me-1"></i>
                                <strong>Dica:</strong> Use HTML básico para formatar o conteúdo. 
                                Tags permitidas: &lt;p&gt;, &lt;strong&gt;, &lt;em&gt;, &lt;ul&gt;, &lt;li&gt;, &lt;br&gt;
                            </small>
                        </div>
                    </div>
                </div>
                
                <div class="card mt-3">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-history me-2"></i>Última Atualização
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-1">
                            <strong>Data:</strong> <?php echo date('d/m/Y H:i', strtotime($aviso_atual['updated_at'])); ?>
                        </p>
                        <p class="mb-0">
                            <strong>Status:</strong> 
                            <span class="badge <?php echo $aviso_atual['ativo'] ? 'bg-success' : 'bg-secondary'; ?>">
                                <?php echo $aviso_atual['ativo'] ? 'Ativo' : 'Inativo'; ?>
                            </span>
                        </p>
                    </div>
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
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('formAviso');
            const tituloInput = document.getElementById('titulo');
            const conteudoInput = document.getElementById('conteudo');
            const ativoCheckbox = document.getElementById('ativo');
            const previewTitulo = document.getElementById('previewTitulo');
            const previewConteudo = document.getElementById('previewConteudo');
            const btnPreview = document.getElementById('btnPreview');
            const btnReset = document.getElementById('btnReset');
            
            // Função para atualizar preview
            function atualizarPreview() {
                previewTitulo.textContent = tituloInput.value || 'Título do Aviso';
                previewConteudo.innerHTML = conteudoInput.value || '<p>Conteúdo do aviso...</p>';
            }
            
            // Event listeners para atualização em tempo real
            tituloInput.addEventListener('input', atualizarPreview);
            conteudoInput.addEventListener('input', atualizarPreview);
            
            // Botão de preview
            btnPreview.addEventListener('click', function() {
                atualizarPreview();
                alert('Preview atualizado! Verifique a área de preview ao lado.');
            });
            
            // Botão de reset
            btnReset.addEventListener('click', function() {
                if (confirm('Deseja restaurar o aviso para o padrão? Esta ação não pode ser desfeita.')) {
                    tituloInput.value = 'Atenção aos horários do TACF';
                    conteudoInput.value = '<p class="mb-2"><strong>A Partir do dia 15 DE SETEMBRO</strong>, os horários de realização do TACF serão os seguintes:</p><ul class="mb-0"><li><strong>Segunda a Quinta-feira</strong> das 14:30h às 16h</li><li><strong>Sexta-feira</strong> das 08h às 09:30h</li></ul>';
                    ativoCheckbox.checked = true;
                    atualizarPreview();
                }
            });
            
            // Submissão do formulário
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const formData = new FormData(form);
                
                try {
                    const response = await fetch('salvar_aviso.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        alert('Configuração salva com sucesso!');
                        location.reload();
                    } else {
                        alert('Erro ao salvar: ' + data.message);
                    }
                } catch (error) {
                    alert('Erro ao processar a requisição: ' + error.message);
                }
            });
        });
    </script>
</body>
</html>
