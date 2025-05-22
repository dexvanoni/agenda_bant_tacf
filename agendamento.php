<?php
require_once 'config/database.php';

if (!isset($_GET['espaco'])) {
    header('Location: index.php');
    exit();
}

$espaco_id = (int)$_GET['espaco'];
$stmt = $conn->prepare("SELECT * FROM espacos WHERE id = ? AND status = 'ativo'");
$stmt->execute([$espaco_id]);
$espaco = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$espaco) {
    header('Location: index.php');
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
    <title>Agendamento - <?php echo htmlspecialchars($espaco['nome']); ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .fc-event {
            cursor: pointer;
        }
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
                    <h1>Agendamento</h1>
                    <p class="mb-0"><?php echo htmlspecialchars($espaco['nome']); ?></p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="index.php" class="btn btn-light">Voltar</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Conteúdo Principal -->
    <main class="container">
        <div class="row">
            <div class="col-md-8">
                <div id="calendario"></div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Informações do Espaço</h5>
                        <p class="card-text"><?php echo htmlspecialchars($espaco['descricao']); ?></p>
                        <p class="card-text">
                            <small class="text-muted">
                                <i class="fas fa-users"></i> Capacidade: <?php echo $espaco['capacidade']; ?> pessoas
                            </small>
                        </p>
                        <hr>
                        <h5>Regras de Agendamento</h5>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-clock"></i> Antecedência mínima: <?php echo $config['antecedencia_horas']; ?> horas</li>
                            <li><i class="fas fa-hourglass-half"></i> Máximo de horas consecutivas: <?php echo $config['max_horas_consecutivas']; ?> horas</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal de Agendamento -->
    <div class="modal fade" id="agendamentoModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Novo Agendamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Data e Hora Selecionadas:</strong>
                        <span id="dataHoraSelecionada"></span>
                    </div>
                    <form id="formAgendamento">
                        <input type="hidden" id="data_inicio" name="data_inicio">
                        <input type="hidden" id="data_fim" name="data_fim">
                        <input type="hidden" id="espaco_id" name="espaco_id">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Data</label>
                                <input type="date" class="form-control" id="data_agendamento" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Hora Início</label>
                                <input type="time" class="form-control" id="hora_inicio" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Duração (horas)</label>
                                <select class="form-control" id="duracao" required>
                                    <?php 
                                    $max_horas = $config['max_horas_consecutivas'];
                                    for ($i = 1; $i <= $max_horas; $i++) {
                                        echo "<option value='{$i}'>" . $i . " hora" . ($i > 1 ? 's' : '') . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nome do Solicitante</label>
                                <input type="text" class="form-control" name="nome_solicitante" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Posto/Graduação</label>
                                <input type="text" class="form-control" name="posto_graduacao" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Setor</label>
                                <input type="text" class="form-control" name="setor" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Ramal</label>
                                <input type="text" class="form-control" name="ramal" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Seu Email</label>
                                <input type="email" class="form-control" name="email_solicitante" required>
                                <small class="text-muted">Você receberá uma confirmação por email quando o status do agendamento for atualizado.</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nome do Evento</label>
                                <input type="text" class="form-control" id="nome_evento" name="nome_evento" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Categoria do Evento</label>
                                <input type="text" class="form-control" name="categoria_evento" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Quantidade de Participantes</label>
                                <input type="number" class="form-control" name="quantidade_participantes" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Observações/Links de Reunião</label>
                            <textarea class="form-control" name="observacoes" rows="3" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnSalvarAgendamento">Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Bar Modal -->
    <div class="modal fade" id="progressModal" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                    <h5 class="mb-3">Salvando agendamento...</h5>
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" 
                             style="width: 100%"></div>
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
    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/pt-br.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendario');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                locale: 'pt-br',
                initialView: 'timeGridWeek',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                slotMinTime: '08:00:00',
                slotMaxTime: '18:00:00',
                allDaySlot: false,
                selectable: true,
                select: function(info) {
                    var data = info.start;
                    var dataFormatada = data.toLocaleDateString('pt-BR');
                    var horaFormatada = data.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
                    
                    // Verificar se a data é passada
                    if (data < new Date()) {
                        alert('Não é possível agendar datas ou horários passados.');
                        calendar.unselect();
                        return;
                    }
                    
                    document.getElementById('dataHoraSelecionada').textContent = dataFormatada + ' às ' + horaFormatada;
                    
                    // Preencher os campos de data e hora
                    document.getElementById('data_agendamento').value = data.toISOString().split('T')[0];
                    document.getElementById('hora_inicio').value = data.toTimeString().slice(0, 5);
                    
                    // Atualizar datas no formulário
                    atualizarDatas();
                    
                    document.getElementById('espaco_id').value = '<?php echo $espaco_id; ?>';
                    
                    var modal = new bootstrap.Modal(document.getElementById('agendamentoModal'));
                    modal.show();
                },
                events: 'get_agendamentos.php?espaco=<?php echo $espaco_id; ?>'
            });
            calendar.render();

            // Função para atualizar as datas quando o usuário alterar os campos
            function atualizarDatas() {
                var data = document.getElementById('data_agendamento').value;
                var hora = document.getElementById('hora_inicio').value;
                var duracao = parseInt(document.getElementById('duracao').value);
                
                // Criar data no fuso horário local
                var dataInicio = new Date(data + 'T' + hora + ':00-03:00');
                var dataFim = new Date(dataInicio);
                dataFim.setHours(dataFim.getHours() + duracao);
                
                // Converter para ISO string mantendo o fuso horário local
                document.getElementById('data_inicio').value = dataInicio.toISOString();
                document.getElementById('data_fim').value = dataFim.toISOString();
                
                // Atualizar texto da data/hora selecionada
                document.getElementById('dataHoraSelecionada').textContent = 
                    dataInicio.toLocaleDateString('pt-BR') + ' às ' + 
                    dataInicio.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
            }

            // Adicionar listeners para atualizar as datas quando os campos mudarem
            document.getElementById('data_agendamento').addEventListener('change', atualizarDatas);
            document.getElementById('hora_inicio').addEventListener('change', atualizarDatas);
            document.getElementById('duracao').addEventListener('change', atualizarDatas);

            // Salvar agendamento
            document.getElementById('btnSalvarAgendamento').addEventListener('click', function() {
                // Validar data/hora
                var dataInicio = new Date(document.getElementById('data_inicio').value);
                if (dataInicio < new Date()) {
                    alert('Não é possível agendar datas ou horários passados.');
                    return;
                }

                const formData = new FormData(document.getElementById('formAgendamento'));
                formData.append('espaco_id', '<?php echo $espaco_id; ?>');

                // Mostrar modal de progresso
                var progressModal = new bootstrap.Modal(document.getElementById('progressModal'));
                progressModal.show();

                fetch('salvar_agendamento.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    progressModal.hide();
                    if (data.success) {
                        alert('Agendamento realizado com sucesso!');
                        location.reload();
                    } else {
                        alert('Erro ao realizar agendamento: ' + data.message);
                    }
                })
                .catch(error => {
                    progressModal.hide();
                    alert('Erro ao realizar agendamento: ' + error.message);
                });
            });
        });
    </script>
</body>
</html> 