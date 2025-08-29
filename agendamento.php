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
        .form-control.is-valid {
            border-color: #198754;
            padding-right: calc(1.5em + 0.75rem);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }
        .form-control.is-invalid {
            border-color: #dc3545;
            padding-right: calc(1.5em + 0.75rem);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath d='m5.8 4.6 1.4 1.4M7.2 4.6l-1.4 1.4'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }
        
        /* Estilos personalizados para o modal */
        .modal-content {
            border-radius: 15px;
            overflow: hidden;
        }
        
        .modal-header {
            background: linear-gradient(135deg, #1a237e 0%, #3949ab 100%) !important;
        }
        
        .form-floating > .form-control,
        .form-floating > .form-select {
            border-radius: 10px;
            border: 2px solid #e0e0e0;
            transition: all 0.3s ease;
        }
        
        .form-floating > .form-control:focus,
        .form-floating > .form-select:focus {
            border-color: #1a237e;
            box-shadow: 0 0 0 0.2rem rgba(26, 35, 126, 0.25);
            transform: translateY(-2px);
        }
        
        .form-floating > label {
            color: #666;
            font-weight: 500;
        }
        
        .form-floating > .form-control:focus ~ label,
        .form-floating > .form-select:focus ~ label {
            color: #1a237e;
        }
        
        .btn-lg {
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-lg:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .alert-primary {
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        /* Animação para o modal */
        .modal.fade .modal-dialog {
            transform: scale(0.8);
            transition: transform 0.3s ease-out;
        }
        
        .modal.show .modal-dialog {
            transform: scale(1);
        }
        
        /* Estilo para campos obrigatórios */
        .form-floating > .form-control:required,
        .form-floating > .form-select:required {
            border-left: 4px solid #1a237e;
        }
        
        /* Hover effects para campos */
        .form-floating > .form-control:hover,
        .form-floating > .form-select:hover {
            border-color: #9fa8da;
            transform: translateY(-1px);
        }
        
        /* Responsividade para dispositivos móveis */
        @media (max-width: 768px) {
            .modal-dialog.modal-lg {
                margin: 1rem;
                max-width: calc(100% - 2rem);
            }
            
            .row .col-md-6 {
                margin-bottom: 1rem;
            }
            
            .btn-lg {
                padding: 0.75rem 1.5rem;
                font-size: 1rem;
            }
        }
        
        /* Melhorias para o campo de observações */
        .form-floating > textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }
        
        /* Estilo para o campo CPF com feedback */
        #cpfFeedback {
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        
        /* Estilo para feedback de agendamento cancelado */
        .valid-feedback {
            display: block;
            color: #198754;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        
        .valid-feedback::before {
            content: "✓ ";
            font-weight: bold;
        }
        
        /* Animação para o alerta de data selecionada */
        .alert-primary {
            animation: slideInDown 0.5s ease-out;
        }
        
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="fas fa-calendar-plus fa-2x"></i>
                        </div>
                        <div>
                            <h5 class="modal-title mb-0">Novo Agendamento de TACF</h5>
                            <small class="text-white-50">Preencha os dados para realizar seu agendamento</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body p-4">
                    <!-- Data Selecionada -->
                    <div class="alert alert-primary border-0 mb-4" style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-calendar-day text-primary me-3 fa-lg"></i>
                            <div>
                                <strong class="text-primary">Data Selecionada para o TACF:</strong>
                                <div class="h5 text-primary mb-0 mt-1">
                                    <span id="dataSelecionada"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <form id="formAgendamento">
                        <input type="hidden" id="data_agendamento" name="data_agendamento">
                        
                        <!-- Primeira linha: CPF e Posto/Graduação -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="cpf" id="cpf" required 
                                           placeholder="000.000.000-00" maxlength="14">
                                    <label for="cpf">
                                        <i class="fas fa-id-card me-2"></i>CPF <span class="text-danger">*</span>
                                    </label>
                                    <div id="cpfFeedback" class="invalid-feedback"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select class="form-select" name="posto_graduacao" id="posto_graduacao" required>
                                        <option value="">Selecione</option>
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
                                    <label for="posto_graduacao">
                                        <i class="fas fa-star me-2"></i>Posto/Graduação
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Segunda linha: Nome Completo e Nome de Guerra -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="nome_completo" id="nome_completo" required>
                                    <label for="nome_completo">
                                        <i class="fas fa-user me-2"></i>Nome Completo
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="nome_guerra" id="nome_guerra" required>
                                    <label for="nome_guerra">
                                        <i class="fas fa-user-tie me-2"></i>Nome de Guerra
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Terceira linha: Email e Contato -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="email" class="form-control" name="email" id="email" required>
                                    <label for="email">
                                        <i class="fas fa-envelope me-2"></i>Email
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="contato" id="contato" required>
                                    <label for="contato">
                                        <i class="fas fa-phone me-2"></i>Contato
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Observações -->
                        <div class="mb-4">
                            <div class="form-floating">
                                <textarea class="form-control" name="observacoes" id="observacoes" 
                                          style="height: 100px" required></textarea>
                                <label for="observacoes">
                                    <i class="fas fa-comment-alt me-2"></i>Observações
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                
                <div class="modal-footer bg-light border-0 p-4">
                    <button type="button" class="btn btn-outline-secondary btn-lg px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancelar
                    </button>
                    <button type="button" class="btn btn-primary btn-lg px-4" id="btnSalvarAgendamento">
                        <i class="fas fa-save me-2"></i>Salvar Agendamento
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Bar Modal -->
    <div class="modal fade" id="progressModal" data-bs-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-body text-center p-5">
                    <div class="spinner-border text-primary mb-4" style="width: 3rem; height: 3rem;" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                    <h5 class="text-primary mb-3">Salvando Agendamento</h5>
                    <p class="text-muted mb-4">Aguarde enquanto processamos sua solicitação...</p>
                    <div class="progress" style="height: 8px; border-radius: 10px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                             role="progressbar" 
                             style="width: 100%; border-radius: 10px;"></div>
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

            // Função para formatar CPF
            function formatarCPF(cpf) {
                cpf = cpf.replace(/\D/g, '');
                cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2');
                cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2');
                cpf = cpf.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                return cpf;
            }

            // Função para validar CPF
            function validarCPF(cpf) {
                cpf = cpf.replace(/\D/g, '');
                if (cpf.length !== 11) return false;
                
                // Verifica se todos os dígitos são iguais
                if (/^(\d)\1+$/.test(cpf)) return false;
                
                // Validação do primeiro dígito verificador
                let soma = 0;
                for (let i = 0; i < 9; i++) {
                    soma += parseInt(cpf.charAt(i)) * (10 - i);
                }
                let resto = 11 - (soma % 11);
                let dv1 = (resto === 10 || resto === 11) ? 0 : resto;
                
                // Validação do segundo dígito verificador
                soma = 0;
                for (let i = 0; i < 10; i++) {
                    soma += parseInt(cpf.charAt(i)) * (11 - i);
                }
                resto = 11 - (soma % 11);
                let dv2 = (resto === 10 || resto === 11) ? 0 : resto;
                
                return (parseInt(cpf.charAt(9)) === dv1 && parseInt(cpf.charAt(10)) === dv2);
            }

            // Event listener para formatação do CPF
            document.getElementById('cpf').addEventListener('input', function(e) {
                let cpf = e.target.value;
                e.target.value = formatarCPF(cpf);
            });

            // Event listener para verificação do CPF quando sair do campo
            document.getElementById('cpf').addEventListener('blur', function(e) {
                let cpf = e.target.value.replace(/\D/g, '');
                let cpfInput = e.target;
                let feedback = document.getElementById('cpfFeedback');
                
                if (cpf.length === 11) {
                    if (!validarCPF(cpf)) {
                        cpfInput.classList.add('is-invalid');
                        feedback.textContent = 'CPF inválido';
                        return;
                    }
                    
                    // Verificar se já existe agendamento para este CPF
                    let formData = new FormData();
                    formData.append('cpf', cpf);
                    
                    fetch('verificar_cpf.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            if (data.ja_agendado) {
                                cpfInput.classList.add('is-invalid');
                                feedback.textContent = data.message;
                                
                                // Mostrar modal com informações do agendamento existente
                                let modal = new bootstrap.Modal(document.getElementById('agendamentoModal'));
                                modal.hide();
                                
                                setTimeout(() => {
                                    alert(data.message + '\n\nDetalhes do agendamento existente:\n' +
                                          'Data: ' + data.agendamento.data + '\n' +
                                          'Status: ' + data.agendamento.status_texto + '\n' +
                                          'Militar: ' + data.agendamento.posto_graduacao + ' ' + data.agendamento.nome_guerra);
                                }, 100);
                            } else {
                                cpfInput.classList.remove('is-invalid');
                                cpfInput.classList.add('is-valid');
                                
                                // Verificar se há agendamento cancelado anterior
                                if (data.agendamento_anterior) {
                                    feedback.textContent = 'CPF válido - Agendamento anterior cancelado em ' + data.agendamento_anterior.data;
                                    feedback.className = 'valid-feedback';
                                    feedback.style.display = 'block';
                                } else {
                                    feedback.textContent = '';
                                    feedback.style.display = 'none';
                                }
                            }
                        } else {
                            cpfInput.classList.add('is-invalid');
                            feedback.textContent = data.message;
                        }
                    })
                    .catch(error => {
                        cpfInput.classList.add('is-invalid');
                        feedback.textContent = 'Erro ao verificar CPF';
                    });
                } else {
                    cpfInput.classList.remove('is-invalid', 'is-valid');
                    feedback.textContent = '';
                }
            });

            document.getElementById('btnSalvarAgendamento').addEventListener('click', function() {
                var form = document.getElementById('formAgendamento');
                if (!form.reportValidity()) {
                    return;
                }
                
                // Verificar se o CPF é válido antes de enviar
                let cpf = document.getElementById('cpf').value.replace(/\D/g, '');
                if (!validarCPF(cpf)) {
                    alert('Por favor, insira um CPF válido.');
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
