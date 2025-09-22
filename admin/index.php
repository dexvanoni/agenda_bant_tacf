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

$stmt = $conn->query("SELECT COUNT(*) FROM datas_liberadas");
$datas_liberadas = $stmt->fetchColumn();

// Buscar últimos agendamentos
/*
$stmt = $conn->query("
    SELECT a.*, dl.data as data_liberada
    FROM agendamentos a
    JOIN datas_liberadas dl ON a.data_liberada_id = dl.id
    WHERE dl.data >= CURRENT_DATE()
    ORDER BY
        CASE
            WHEN a.status = 'pendente' THEN 1
            ELSE 2
        END,
        dl.data ASC,
        a.created_at ASC
    LIMIT 50
");
$agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
*/
$stmt = $conn->query("
    SELECT a.*, dl.data as data_liberada
    FROM agendamentos a
    JOIN datas_liberadas dl ON a.data_liberada_id = dl.id
    WHERE dl.data >= CURRENT_DATE()
    ORDER BY
        dl.data ASC,
        a.created_at DESC
");
$agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);



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
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
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
                        <h5 class="card-title">Datas Liberadas</h5>
                        <p class="card-text display-4"><?php echo $datas_liberadas; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Menu -->
        <div class="row mb-4">
            <div class="col">
                <div class="btn-group">
                    <a href="datas_liberadas.php" class="btn btn-primary">
                        <i class="fas fa-calendar-check"></i> Datas Liberadas
                    </a>
                    <a href="relatorios.php" class="btn btn-primary">
                        <i class="fas fa-chart-bar"></i> Relatórios
                    </a>
                    <a href="configurar_aviso.php" class="btn btn-warning">
                        <i class="fas fa-exclamation-triangle"></i> Aviso Popup
                    </a>
                    <button type="button" class="btn btn-danger" id="btnLimparSistema">
                        <i class="fas fa-trash-alt"></i> Limpar Sistema
                    </button>
                    <!--
                    <a href="configuracoes.php" class="btn btn-primary">
                        <i class="fas fa-cog"></i> Configurações
                    </a>
                    -->
                </div>
            </div>
        </div>

        <!-- Filtros de Pesquisa -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-filter me-2"></i>Filtros de Pesquisa de Agendamentos
                        </h5>
                    </div>
                    <div class="card-body">
                        <form id="formFiltros">
                            <div class="row">
                                <!-- Filtro por Data do Teste (Range) -->
                                <div class="col-md-6 mb-3">
                                    <label for="data_teste_inicio" class="form-label">
                                        <i class="fas fa-calendar-alt me-1"></i>Data do Teste - Início
                                    </label>
                                    <input type="date" class="form-control" id="data_teste_inicio" name="data_teste_inicio">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="data_teste_fim" class="form-label">
                                        <i class="fas fa-calendar-alt me-1"></i>Data do Teste - Fim
                                    </label>
                                    <input type="date" class="form-control" id="data_teste_fim" name="data_teste_fim">
                                </div>
                                
                                <!-- Filtro por Status -->
                                <div class="col-md-4 mb-3">
                                    <label for="status" class="form-label">
                                        <i class="fas fa-info-circle me-1"></i>Status
                                    </label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="">Todos os status</option>
                                        <option value="pendente">Pendente</option>
                                        <option value="aprovado">Aprovado</option>
                                        <option value="cancelado">Cancelado</option>
                                    </select>
                                </div>
                                
                                <!-- Filtro por Data do Agendamento (Range) -->
                                <div class="col-md-4 mb-3">
                                    <label for="data_agendamento_inicio" class="form-label">
                                        <i class="fas fa-calendar-plus me-1"></i>Data Agendamento - Início
                                    </label>
                                    <input type="date" class="form-control" id="data_agendamento_inicio" name="data_agendamento_inicio">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="data_agendamento_fim" class="form-label">
                                        <i class="fas fa-calendar-plus me-1"></i>Data Agendamento - Fim
                                    </label>
                                    <input type="date" class="form-control" id="data_agendamento_fim" name="data_agendamento_fim">
                                </div>
                                
                                <!-- Filtro por Nome Completo -->
                                <div class="col-md-4 mb-3">
                                    <label for="nome_completo" class="form-label">
                                        <i class="fas fa-user me-1"></i>Nome Completo
                                    </label>
                                    <input type="text" class="form-control" id="nome_completo" name="nome_completo" placeholder="Digite o nome completo">
                                </div>
                                
                                <!-- Filtro por Nome de Guerra -->
                                <div class="col-md-4 mb-3">
                                    <label for="nome_guerra" class="form-label">
                                        <i class="fas fa-user-tie me-1"></i>Nome de Guerra
                                    </label>
                                    <input type="text" class="form-control" id="nome_guerra" name="nome_guerra" placeholder="Digite o nome de guerra">
                                </div>
                                
                                <!-- Filtro por Posto/Graduação -->
                                <div class="col-md-4 mb-3">
                                    <label for="posto_graduacao" class="form-label">
                                        <i class="fas fa-star me-1"></i>Posto/Graduação
                                    </label>
                                    <select class="form-select" id="posto_graduacao" name="posto_graduacao">
                                        <option value="">Todos os postos</option>
                                        <option value="Brig">Brigadeiro</option>
                                        <option value="TCel">Tenente-Coronel</option>
                                        <option value="Maj">Major</option>
                                        <option value="Cap">Capitão</option>
                                        <option value="1T">1º Tenente</option>
                                        <option value="2T">2º Tenente</option>
                                        <option value="Asp">Aspirante</option>
                                        <option value="SO">Suboficial</option>
                                        <option value="1S">1º Sargento</option>
                                        <option value="2S">2º Sargento</option>
                                        <option value="3S">3º Sargento</option>
                                        <option value="CB">Cabo</option>
                                        <option value="S1">S1</option>
                                        <option value="S2">S2</option>
                                        <option value="Rec">Recruta</option>
                                        <option value="Consc">Conscrito</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Botões de Ação -->
                            <div class="row">
                                <div class="col-12">
                                    <button type="button" class="btn btn-primary me-2" id="btnAplicarFiltros">
                                        <i class="fas fa-search me-1"></i>Aplicar Filtros
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary me-2" id="btnLimparFiltros">
                                        <i class="fas fa-eraser me-1"></i>Limpar Filtros
                                    </button>
                                    <button type="button" class="btn btn-success" id="btnExportarResultados">
                                        <i class="fas fa-download me-1"></i>Exportar Resultados
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Resultados da Pesquisa -->
        <div class="row mb-4" id="resultadosContainer" style="display: none;">
            <div class="col-12">
        <div class="card">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>Resultados da Pesquisa
                            <span class="badge bg-light text-dark ms-2" id="totalResultados">0</span>
                        </h5>
                <div class="btn-group">
                    <button type="button" class="btn btn-success btn-sm" id="btnAprovarSelecionados">
                        <i class="fas fa-check"></i> Aprovar Selecionados
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" id="btnCancelarSelecionados">
                        <i class="fas fa-times"></i> Cancelar Selecionados
                    </button>
                </div>
            </div>
            <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="tabelaResultados">
                                <thead class="table-dark">
                            <tr>
                                <th>
                                    <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="selecionarTodosResultados">
                                    </div>
                                </th>
                                        <th>Data do Teste</th>
                                        <th>Posto/Graduação</th>
                                        <th>Nome Completo</th>
                                        <th>Nome de Guerra</th>
                                <th>Status</th>
                                        <th>Data Agendamento</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                                <tbody id="corpoTabela">
                                    <!-- Resultados serão inseridos aqui via JavaScript -->
                        </tbody>
                    </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <!-- Progress Bar Modal -->
    <div class="modal fade" id="progressModal" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                    <h5 class="mb-3">Processando agendamentos...</h5>
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" 
                             style="width: 100%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmação para Limpeza do Sistema -->
    <div class="modal fade" id="modalLimparSistema" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-danger">
                <div class="modal-header bg-danger text-warning border-danger">
                    <h5 class="modal-title text-warning">
                        <i class="fas fa-exclamation-triangle"></i> ATENÇÃO: LIMPEZA TOTAL DO SISTEMA
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body bg-danger text-warning">
                    <div class="alert alert-warning border-warning">
                        <h6 class="alert-heading">
                            <i class="fas fa-radiation"></i> OPERAÇÃO IRREVERSÍVEL!
                        </h6>
                        <p class="mb-2">Esta ação irá:</p>
                        <ul class="mb-3">
                            <li><strong>APAGAR TODOS os agendamentos</strong> (pendentes, aprovados e cancelados)</li>
                            <li><strong>APAGAR TODAS as datas liberadas</strong></li>
                            <li><strong>ZERAR completamente o sistema</strong></li>
                            <li><strong>NÃO PODE ser desfeita</strong></li>
                        </ul>
                        <p class="mb-0"><strong>Você tem certeza absoluta que deseja continuar?</strong></p>
                    </div>
                    
                    <div class="text-center">
                        <p class="mb-3">
                            <i class="fas fa-keyboard"></i> Digite <strong>"LIMPAR"</strong> para confirmar:
                        </p>
                        <input type="text" class="form-control form-control-lg text-center mb-3" 
                               id="confirmacaoLimpeza" placeholder="Digite LIMPAR" maxlength="6">
                    </div>
                </div>
                <div class="modal-footer bg-danger border-danger">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="button" class="btn btn-warning" id="btnConfirmarLimpeza" disabled>
                        <i class="fas fa-trash-alt"></i> LIMPAR SISTEMA COMPLETAMENTE
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Detalhes do Agendamento -->
    <div class="modal fade" id="detalhesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-info text-white border-0">
                    <h5 class="modal-title">
                        <i class="fas fa-info-circle me-2"></i>Detalhes do Agendamento
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body" id="detalhesConteudo">
                    <!-- Conteúdo será inserido via JavaScript -->
                </div>
                <div class="modal-footer border-0">
                    <div class="d-flex justify-content-between w-100">
                        <div class="d-flex flex-column gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <label class="form-label mb-0">Alterar Status:</label>
                                <select class="form-select d-inline-block w-auto" id="novoStatus" style="width: auto;">
                                    <option value="pendente">Pendente</option>
                                    <option value="aprovado">Aprovado</option>
                                    <option value="cancelado">Cancelado</option>
                                </select>
                                <button type="button" class="btn btn-warning btn-sm" id="btnAlterarStatus">
                                    <i class="fas fa-save"></i> Salvar Status
                                </button>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <label class="form-label mb-0">Alterar Data do Teste:</label>
                                <input type="date" class="form-control d-inline-block w-auto" id="novaDataTeste" style="width: auto;">
                                <button type="button" class="btn btn-info btn-sm" id="btnAlterarData">
                                    <i class="fas fa-calendar-alt"></i> Salvar Data
                                </button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    </div>
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
    <!-- jQuery (requerido pelo DataTables) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    
    <script>
        // Função global para visualizar detalhes do agendamento
        function visualizarDetalhes(id) {
            const modal = new bootstrap.Modal(document.getElementById('detalhesModal'));
            const conteudo = document.getElementById('detalhesConteudo');
            
            // Armazenar ID do agendamento para uso posterior
            document.getElementById('detalhesModal').setAttribute('data-agendamento-id', id);
            
            // Mostrar loading
            conteudo.innerHTML = '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Carregando...</span></div></div>';
            modal.show();
            
            // Buscar detalhes
            fetch('../get_detalhes_agendamento.php?id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const agendamento = data.data;
                        const statusClass = {
                            'pendente': 'warning',
                            'aprovado': 'success',
                            'cancelado': 'danger'
                        }[agendamento.status] || 'secondary';
                        
                        // Definir o status atual no select
                        document.getElementById('novoStatus').value = agendamento.status;
                        
                        // Definir a data atual no campo de data (formato YYYY-MM-DD)
                        const dataTeste = agendamento.data_teste_formatada.split('/');
                        if (dataTeste.length === 3) {
                            const dataFormatada = `${dataTeste[2]}-${dataTeste[1].padStart(2, '0')}-${dataTeste[0].padStart(2, '0')}`;
                            document.getElementById('novaDataTeste').value = dataFormatada;
                        }
                        
                        conteudo.innerHTML = `
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-primary mb-3"><i class="fas fa-calendar-alt me-2"></i>Informações do Teste</h6>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Data do Teste:</label>
                                        <p class="mb-0">${agendamento.data_teste_formatada}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Status Atual:</label>
                                        <p class="mb-0"><span class="badge bg-${statusClass}">${agendamento.status_texto}</span></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-primary mb-3"><i class="fas fa-user me-2"></i>Informações do Militar</h6>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Posto/Graduação:</label>
                                        <p class="mb-0">${agendamento.posto_graduacao}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Nome Completo:</label>
                                        <p class="mb-0">${agendamento.nome_completo}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Nome de Guerra:</label>
                                        <p class="mb-0">${agendamento.nome_guerra}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-primary mb-3"><i class="fas fa-envelope me-2"></i>Contato</h6>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Email:</label>
                                        <p class="mb-0"><a href="mailto:${agendamento.email}">${agendamento.email}</a></p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Telefone:</label>
                                        <p class="mb-0">${agendamento.contato}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-primary mb-3"><i class="fas fa-clock me-2"></i>Datas</h6>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Data do Agendamento:</label>
                                        <p class="mb-0">${agendamento.data_agendamento}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Última Atualização:</label>
                                        <p class="mb-0">${agendamento.data_atualizacao}</p>
                                    </div>
                                </div>
                            </div>
                            
                            ${agendamento.observacoes ? `
                                <hr>
                                <div class="mb-3">
                                    <h6 class="text-primary mb-3"><i class="fas fa-comment-alt me-2"></i>Observações</h6>
                                    <div class="bg-light p-3 rounded">
                                        <p class="mb-0">${agendamento.observacoes}</p>
                                    </div>
                                </div>
                            ` : ''}
                        `;
                    } else {
                        conteudo.innerHTML = '<div class="alert alert-danger">Erro: ' + data.message + '</div>';
                    }
                })
                .catch(error => {
                    conteudo.innerHTML = '<div class="alert alert-danger">Erro ao carregar detalhes: ' + error.message + '</div>';
                });
        }

        document.addEventListener('DOMContentLoaded', function() {

            // Funcionalidade de limpeza do sistema
            const btnLimparSistema = document.getElementById('btnLimparSistema');
            const modalLimparSistema = new bootstrap.Modal(document.getElementById('modalLimparSistema'));
            const confirmacaoLimpeza = document.getElementById('confirmacaoLimpeza');
            const btnConfirmarLimpeza = document.getElementById('btnConfirmarLimpeza');

            // Abrir modal de limpeza
            btnLimparSistema.addEventListener('click', function() {
                modalLimparSistema.show();
                confirmacaoLimpeza.value = '';
                btnConfirmarLimpeza.disabled = true;
            });

            // Controlar habilitação do botão de confirmação
            confirmacaoLimpeza.addEventListener('input', function() {
                btnConfirmarLimpeza.disabled = (this.value.toUpperCase() !== 'LIMPAR');
            });

            // Confirmar limpeza do sistema
            btnConfirmarLimpeza.addEventListener('click', async function() {
                if (confirmacaoLimpeza.value.toUpperCase() !== 'LIMPAR') {
                    alert('Digite "LIMPAR" para confirmar a operação!');
                    return;
                }

                if (!confirm('ATENÇÃO FINAL!\n\nVocê está prestes a APAGAR TODOS os dados do sistema!\n\nEsta ação é IRREVERSÍVEL e não pode ser desfeita!\n\nConfirma que deseja continuar?')) {
                    return;
                }

                try {
                    // Mostrar modal de progresso
                    progressModal.show();
                    
                    const response = await fetch('limpar_sistema.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        }
                    });

                    const data = await response.json();
                    
                    // Esconder modais
                    progressModal.hide();
                    modalLimparSistema.hide();
                    
                    if (data.success) {
                        alert('SISTEMA LIMPO COM SUCESSO!\n\n' + 
                              'Agendamentos removidos: ' + data.dados_removidos.agendamentos + '\n' +
                              'Datas liberadas removidas: ' + data.dados_removidos.datas_liberadas + '\n\n' +
                              'O sistema foi completamente zerado.');
                        location.reload();
                    } else {
                        alert('Erro ao limpar sistema: ' + data.message);
                    }
                } catch (error) {
                    progressModal.hide();
                    alert('Erro ao processar a requisição: ' + error.message);
                }
            });

            // ===== FUNCIONALIDADE DOS FILTROS =====
            
            // Função para aplicar filtros
            function aplicarFiltros() {
                const formData = new FormData(document.getElementById('formFiltros'));
                const params = new URLSearchParams();
                
                // Adicionar apenas campos preenchidos aos parâmetros
                for (let [key, value] of formData.entries()) {
                    if (value.trim() !== '') {
                        params.append(key, value);
                    }
                }
                
                // Mostrar loading
                const resultadosContainer = document.getElementById('resultadosContainer');
                const corpoTabela = document.getElementById('corpoTabela');
                const totalResultados = document.getElementById('totalResultados');
                
                corpoTabela.innerHTML = '<tr><td colspan="7" class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Carregando...</span></div></td></tr>';
                resultadosContainer.style.display = 'block';
                
                // Fazer requisição
                fetch('../get_agendamentos_filtrados.php?' + params.toString())
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            exibirResultados(data.data);
                            totalResultados.textContent = data.total;
                        } else {
                            corpoTabela.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Erro: ' + data.message + '</td></tr>';
                            totalResultados.textContent = '0';
                        }
                    })
                    .catch(error => {
                        corpoTabela.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Erro ao carregar dados: ' + error.message + '</td></tr>';
                        totalResultados.textContent = '0';
                    });
            }
            
            // Função para exibir resultados na tabela
            function exibirResultados(agendamentos) {
                const corpoTabela = document.getElementById('corpoTabela');
                
                if (agendamentos.length === 0) {
                    corpoTabela.innerHTML = '<tr><td colspan="8" class="text-center text-muted">Nenhum agendamento encontrado com os filtros aplicados.</td></tr>';
                    return;
                }
                
                let html = '';
                agendamentos.forEach(agendamento => {
                    const statusClass = {
                        'pendente': 'warning',
                        'aprovado': 'success',
                        'cancelado': 'danger'
                    }[agendamento.status] || 'secondary';
                    
                    html += `
                        <tr>
                            <td>
                                <div class="form-check">
                                    <input class="form-check-input agendamento-checkbox-resultados" type="checkbox" 
                                           value="${agendamento.id}"
                                           data-status="${agendamento.status}">
                                </div>
                            </td>
                            <td>${agendamento.data_teste_formatada}</td>
                            <td>${agendamento.posto_graduacao}</td>
                            <td>${agendamento.nome_completo}</td>
                            <td>${agendamento.nome_guerra}</td>
                            <td><span class="badge bg-${statusClass}">${agendamento.status_texto}</span></td>
                            <td>${agendamento.data_agendamento}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="visualizarDetalhes(${agendamento.id})" title="Visualizar detalhes">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });
                
                corpoTabela.innerHTML = html;
                
                // Configurar eventos para os novos checkboxes
                configurarEventosSelecao();
            }
            
            // Função para limpar filtros
            function limparFiltros() {
                document.getElementById('formFiltros').reset();
                document.getElementById('resultadosContainer').style.display = 'none';
            }
            
            // Função para exportar resultados
            function exportarResultados() {
                const params = new URLSearchParams();
                const formData = new FormData(document.getElementById('formFiltros'));
                
                for (let [key, value] of formData.entries()) {
                    if (value.trim() !== '') {
                        params.append(key, value);
                    }
                }
                
                // Criar link de download
                const link = document.createElement('a');
                link.href = '../get_agendamentos_filtrados.php?' + params.toString() + '&export=1';
                link.download = 'agendamentos_filtrados.csv';
                link.click();
            }
            
            
            // Função para configurar eventos de seleção
            function configurarEventosSelecao() {
                const selecionarTodos = document.getElementById('selecionarTodosResultados');
                const checkboxes = document.querySelectorAll('.agendamento-checkbox-resultados');
                
                // Função para marcar/desmarcar todos
                if (selecionarTodos) {
                    selecionarTodos.addEventListener('change', function() {
                        checkboxes.forEach(checkbox => {
                            checkbox.checked = this.checked;
                        });
                    });
                }
            }
            
            // Função para atualizar status dos agendamentos selecionados
            async function atualizarStatusAgendamentos(status) {
                const checkboxes = document.querySelectorAll('.agendamento-checkbox-resultados');
                const selecionados = Array.from(checkboxes)
                    .filter(cb => cb.checked)
                    .map(cb => cb.value);

                if (selecionados.length === 0) {
                    alert('Selecione pelo menos um agendamento!');
                    return;
                }

                if (!confirm(`Deseja realmente ${status === 'aprovado' ? 'aprovar' : 'cancelar'} os agendamentos selecionados?`)) {
                    return;
                }

                try {
                    // Mostrar modal de progresso
                    const progressModal = new bootstrap.Modal(document.getElementById('progressModal'));
                    progressModal.show();

                    const response = await fetch('atualizar_status.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            ids: selecionados,
                            status: status
                        })
                    });

                    const data = await response.json();
                    
                    // Esconder modal de progresso
                    progressModal.hide();
                    
                    if (data.success) {
                        alert('Status atualizado com sucesso!');
                        // Recarregar os resultados
                        aplicarFiltros();
                    } else {
                        alert('Erro ao atualizar status: ' + data.message);
                    }
                } catch (error) {
                    // Esconder modal de progresso em caso de erro
                    const progressModal = bootstrap.Modal.getInstance(document.getElementById('progressModal'));
                    if (progressModal) progressModal.hide();
                    alert('Erro ao processar a requisição: ' + error.message);
                }
            }

            // Event listeners para os botões de filtro
            document.getElementById('btnAplicarFiltros').addEventListener('click', aplicarFiltros);
            document.getElementById('btnLimparFiltros').addEventListener('click', limparFiltros);
            document.getElementById('btnExportarResultados').addEventListener('click', exportarResultados);
            
            // Função para alterar status de um agendamento individual
            async function alterarStatusIndividual() {
                const agendamentoId = document.getElementById('detalhesModal').getAttribute('data-agendamento-id');
                const novoStatus = document.getElementById('novoStatus').value;
                
                if (!agendamentoId) {
                    alert('Erro: ID do agendamento não encontrado');
                    return;
                }
                
                if (!confirm(`Deseja realmente alterar o status para "${novoStatus}"?`)) {
                    return;
                }
                
                try {
                    // Mostrar loading no botão
                    const btnAlterarStatus = document.getElementById('btnAlterarStatus');
                    const textoOriginal = btnAlterarStatus.innerHTML;
                    btnAlterarStatus.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
                    btnAlterarStatus.disabled = true;
                    
                    const response = await fetch('atualizar_status.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            ids: [agendamentoId],
                            status: novoStatus
                        })
                    });
                    
                    const data = await response.json();
                    
                    // Restaurar botão
                    btnAlterarStatus.innerHTML = textoOriginal;
                    btnAlterarStatus.disabled = false;
                    
                    if (data.success) {
                        alert('Status alterado com sucesso!');
                        // Fechar modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('detalhesModal'));
                        modal.hide();
                        // Recarregar resultados se estiverem visíveis
                        if (document.getElementById('resultadosContainer').style.display !== 'none') {
                            aplicarFiltros();
                        }
                    } else {
                        alert('Erro ao alterar status: ' + data.message);
                    }
                } catch (error) {
                    // Restaurar botão em caso de erro
                    const btnAlterarStatus = document.getElementById('btnAlterarStatus');
                    btnAlterarStatus.innerHTML = '<i class="fas fa-save"></i> Salvar Status';
                    btnAlterarStatus.disabled = false;
                    alert('Erro ao processar a requisição: ' + error.message);
                }
            }

            // Função para alterar data do teste de um agendamento individual
            async function alterarDataTesteIndividual() {
                const agendamentoId = document.getElementById('detalhesModal').getAttribute('data-agendamento-id');
                const novaData = document.getElementById('novaDataTeste').value;
                
                if (!agendamentoId) {
                    alert('Erro: ID do agendamento não encontrado');
                    return;
                }
                
                if (!novaData) {
                    alert('Por favor, selecione uma data válida');
                    return;
                }
                
                if (!confirm(`Deseja realmente alterar a data do teste para ${novaData}?`)) {
                    return;
                }
                
                try {
                    // Mostrar loading no botão
                    const btnAlterarData = document.getElementById('btnAlterarData');
                    const textoOriginal = btnAlterarData.innerHTML;
                    btnAlterarData.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
                    btnAlterarData.disabled = true;
                    
                    const response = await fetch('atualizar_data_teste.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            id: agendamentoId,
                            nova_data_teste: novaData
                        })
                    });
                    
                    const data = await response.json();
                    
                    // Restaurar botão
                    btnAlterarData.innerHTML = textoOriginal;
                    btnAlterarData.disabled = false;
                    
                    if (data.success) {
                        alert('Data do teste alterada com sucesso!');
                        // Fechar modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('detalhesModal'));
                        modal.hide();
                        // Recarregar resultados se estiverem visíveis
                        if (document.getElementById('resultadosContainer').style.display !== 'none') {
                            aplicarFiltros();
                        }
                    } else {
                        alert('Erro ao alterar data: ' + data.message);
                    }
                } catch (error) {
                    // Restaurar botão em caso de erro
                    const btnAlterarData = document.getElementById('btnAlterarData');
                    btnAlterarData.innerHTML = '<i class="fas fa-calendar-alt"></i> Salvar Data';
                    btnAlterarData.disabled = false;
                    alert('Erro ao processar a requisição: ' + error.message);
                }
            }

            // Event listeners para os botões de ação
            document.getElementById('btnAprovarSelecionados').addEventListener('click', () => atualizarStatusAgendamentos('aprovado'));
            document.getElementById('btnCancelarSelecionados').addEventListener('click', () => atualizarStatusAgendamentos('cancelado'));
            
            // Event listener para alterar status individual
            document.getElementById('btnAlterarStatus').addEventListener('click', alterarStatusIndividual);
            
            // Event listener para alterar data individual
            document.getElementById('btnAlterarData').addEventListener('click', alterarDataTesteIndividual);
            
            // Permitir busca com Enter nos campos de texto
            document.querySelectorAll('#nome_completo, #nome_guerra').forEach(campo => {
                campo.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        aplicarFiltros();
                    }
                });
            });
        });
    </script>
</body>
</html> 