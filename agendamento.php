<?php
require_once 'config/database.php';

/*if (!isset($_GET['espaco'])) {
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
*/
// Buscar configurações
$stmt = $conn->query("SELECT * FROM configuracoes LIMIT 1");
$config = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendamento</title>
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
        .campo-somente-leitura {
            background-color:rgb(165, 165, 165) !important;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header-bant">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1>Agendamento de Teste Físico da BANT</h1>
                    <p class="mb-0">Realize seu agendamento de teste físico de forma rápida e fácil.</p>
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
            <div class="col-md-7">
                <div id="calendario"></div>
            </div>
            <div class="col-md-5">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Informações Gerais</h5>
                        <p class="card-text">1. No dia de realização do TACF, cada militar deverá estar em posse da sua FICHA DE ANAMNESE PREENCHIDA. Sendo assim, é RESPONSABILIDADE DO MILITAR preencher a Ficha de Anamnese com, no mínimo, 10 (dez) dias de antecedência ao dia agendado para realizar o TACF.
<br> 2. Ainda que o militar esteja impossibilitado de realizar o TACF por decisão da Junta de Saúde, ele deverá preencher a Ficha de Anamnese e de Avaliação do TACF e anexar o resultado da Junta e a cópia do boletim de publicação no SIGTACF. Nesse caso, o militar não necessita realizar o agendamento. No entanto, esse militar deve ir a SEF para entregar os documentos referentes a dispensa médica, bem como para assinar a lista de presença.
<br> 3. A realização do 2º TACF / 2025 nesta unidade só será permitida, mediante agendamento prévio. Não será permitido, EM HIPÓTESE ALGUMA, a realização do 2º TACF / 2025 sem que o militar esteja agendado. 
<br> 4. Cada militar que estiver com seu agendamento confirmado, tem a responsabilidade de gerar sua indisponibilidade para escala de serviço no E-risaer. O documento para comprovar essa indisponibilidade, exigido pelo E-risaer, será a cópia do próprio Zimbra recebido com a confirmação do agendamento.
<br> 5. Cada militar deve apresentar-se na SEF para realização do TACF, com o 9º uniforme completo (incluindo tênis branco). A troca do tênis, caso o militar deseje, só será permitida quando o militar for realizar a etapa da corrida.</p>
                        <!--
                        <hr>
                        <h5>Regras de Agendamento</h5>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-clock"></i> Antecedência mínima: <?php echo $config['antecedencia_horas']; ?> horas</li>
                            <li><i class="fas fa-hourglass-half"></i> Máximo de horas consecutivas: <?php echo $config['max_horas_consecutivas']; ?> horas</li>
                        </ul>
                        -->
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal de Agendamento -->
    <div class="modal fade" id="agendamentoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Novo Agendamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <strong>Data Selecionada:</strong>
                        <span id="dataSelecionada"></span>
                    </div>
                    <form id="formAgendamento">
                        <input type="hidden" id="data_agendamento" name="data_agendamento">
                        <div class="mb-3">
                            <label class="form-label">Posto/Graduação</label>
                            <select class="form-control" name="posto_graduacao" required>
                                <option value="">Selecione</option>
                                <option value="Brig">Brigadeiro</option>
                                <option value="Cel">Coronel</option>
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
                        <div class="mb-3">
                            <label class="form-label">Nome Completo</label>
                            <input type="text" class="form-control" name="nome_completo" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nome de Guerra</label>
                            <input type="text" class="form-control" name="nome_guerra" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contato</label>
                            <input type="text" class="form-control" name="contato" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Observações</label>
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
            <p class="mb-0">&copy; <?php echo date('Y'); ?> ETIC - BANT</p>
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
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: ''
                },
                selectable: true,
                select: function(info) {
                    var data = info.startStr;
                    // Buscar eventos para saber se a data está bloqueada
                    fetch('get_agendamentos.php')
                        .then(response => response.json())
                        .then(eventos => {
                            var evento = eventos.find(e => e.start === data);
                            if (!evento || evento.bloqueada) {
                                alert('Não há vagas para esta data. Escolha outra.');
                                return;
                            }
                            document.getElementById('dataSelecionada').textContent = data;
                            document.getElementById('data_agendamento').value = data;
                            var modal = new bootstrap.Modal(document.getElementById('agendamentoModal'));
                            modal.show();
                        });
                },
                events: 'get_agendamentos.php',
                eventDidMount: function(info) {
                    if (info.event.extendedProps.bloqueada) {
                        info.el.classList.add('bg-danger', 'text-white');
                        info.el.innerHTML = 'Sem vagas';
                    } else {
                        info.el.classList.add('bg-success', 'text-white');
			var vagasDisponiveis = info.event.extendedProps.limite - info.event.extendedProps.total;
                        info.el.innerHTML = 'Disponível<br>(' + vagasDisponiveis + ' vagas)';
                    }
                },
                eventClick: function(info) {
                    if (info.event.extendedProps.bloqueada) {
                        alert('Não há vagas para esta data. Escolha outra.');
                        return;
                    }
                    document.getElementById('dataSelecionada').textContent = info.event.startStr;
                    document.getElementById('data_agendamento').value = info.event.startStr;
                    var modal = new bootstrap.Modal(document.getElementById('agendamentoModal'));
                    modal.show();
                }
            });
            calendar.render();

            document.getElementById('btnSalvarAgendamento').addEventListener('click', function() {
                var form = document.getElementById('formAgendamento');
                if (!form.reportValidity()) {
                    return;
                }
                var formData = new FormData(form);

                var progressModal = new bootstrap.Modal(document.getElementById('progressModal'));
                progressModal.show();

                fetch('salvar_agendamento.php', {
                    method: 'POST',
                    body: formData
                })
                .then(async function(response) {
                    var data = null;
                    try { data = await response.json(); } catch (e) {}
                    if (!response.ok || !data || !data.success) {
                        var message = (data && data.message) ? data.message : 'Falha ao salvar o agendamento.';
                        throw new Error(message);
                    }
                    return data;
                })
                .then(function(data) {
                    progressModal.hide();
                    if (data.emails && (data.emails.comunicacao === false || data.emails.solicitante === false)) {
                        var falhas = [];
                        if (data.emails.comunicacao === false) falhas.push('comunicação');
                        if (data.emails.solicitante === false) falhas.push('solicitante');
                        alert('Agendamento salvo, mas houve falha ao enviar e-mail para: ' + falhas.join(', ') + '.');
                    } else {
                        alert('Agendamento realizado com sucesso!');
                    }
                    location.reload();
                })
                .catch(function(error) {
                    progressModal.hide();
                    alert('Erro: ' + error.message);
                });

            });
        });
    </script>
</body>
</html> 
