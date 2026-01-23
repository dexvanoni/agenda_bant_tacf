<?php
header('Content-Type: text/html; charset=UTF-8');
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

// Buscar configuração do aviso popup
$stmt = $conn->query("SELECT * FROM aviso_popup WHERE ativo = 1 ORDER BY id DESC LIMIT 1");
$aviso_popup = $stmt->fetch(PDO::FETCH_ASSOC);

// Se não existir aviso ativo, usar valores padrão
if (!$aviso_popup) {
    $aviso_popup = [
        'titulo' => 'Atenção aos horários do TACF',
        'conteudo' => '<p class="mb-2"><strong>A Partir do dia 15 DE SETEMBRO</strong>, os horários de realização do TACF serão os seguintes:</p><ul class="mb-0"><li><strong>Segunda a Quinta-feira</strong> das 14:30h às 16h</li><li><strong>Sexta-feira</strong> das 08h às 09:30h</li></ul>',
        'ativo' => 1
    ];
}
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
        :root {
            --primary-color: #1a237e;
            --primary-light: #3949ab;
            --primary-dark: #0d47a1;
            --success-color: #4caf50;
            --warning-color: #ff9800;
            --danger-color: #f44336;
            --card-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --card-shadow-hover: 0 8px 30px rgba(0, 0, 0, 0.12);
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .fc-event {
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .fc-event:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .header-bant {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 3rem;
            box-shadow: 0 4px 20px rgba(26, 35, 126, 0.3);
            position: relative;
            overflow: hidden;
        }

        .header-bant::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="grid" width="100" height="100" patternUnits="userSpaceOnUse"><path d="M 100 0 L 0 0 0 100" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }

        .header-bant .container {
            position: relative;
            z-index: 1;
        }

        .header-bant h1 {
            font-weight: 700;
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .header-bant p {
            font-size: 1.1rem;
            opacity: 0.95;
        }

        /* Espaçamento melhorado */
        main {
            padding-bottom: 3rem;
        }

        /* Lista numerada melhorada */
        .info-card ol li {
            margin-bottom: 1rem;
            line-height: 1.7;
        }

        .info-card ol li:last-child {
            margin-bottom: 0;
        }

        /* Textarea melhorado */
        textarea.form-control {
            border-radius: 12px;
            min-height: 100px;
            resize: vertical;
        }

        /* Labels melhorados */
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }

        /* Badge de status */
        .badge {
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            font-weight: 600;
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
        
        /* Cards modernos */
        .card {
            border: none;
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            overflow: hidden;
            background: white;
            margin-bottom: 2rem;
        }

        .card:hover {
            box-shadow: var(--card-shadow-hover);
            transform: translateY(-5px);
        }

        .card-header-modern {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            color: white;
            padding: 1.5rem;
            border-bottom: none;
            font-weight: 600;
            font-size: 1.25rem;
        }

        .card-body {
            padding: 2rem;
        }

        .card-title {
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
            display: flex;
            align-items: center;
        }

        .card-title i {
            margin-right: 0.75rem;
            font-size: 1.75rem;
        }

        /* Calendário em destaque */
        .calendario-container {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: var(--card-shadow);
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }

        .calendario-container:hover {
            box-shadow: var(--card-shadow-hover);
        }

        #calendario {
            background: white;
        }

        /* FullCalendar customizado */
        .fc {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .fc-toolbar {
            margin-bottom: 1.5rem;
        }

        .fc-toolbar-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .fc-button {
            border-radius: 10px;
            padding: 0.5rem 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .fc-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .fc-button-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }

        .fc-button-primary:hover {
            background: var(--primary-light);
            border-color: var(--primary-light);
        }

        .fc-daygrid-day {
            border-radius: 8px;
            margin: 2px;
            transition: all 0.3s ease;
        }

        .fc-daygrid-day:hover {
            background: rgba(26, 35, 126, 0.05);
        }

        /* Cards laterais */
        .sidebar-cards {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .info-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
        }

        .info-card:hover {
            box-shadow: var(--card-shadow-hover);
            transform: translateY(-3px);
        }

        .info-card h5 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 3px solid var(--primary-light);
            display: flex;
            align-items: center;
        }

        .info-card h5 i {
            margin-right: 0.75rem;
            font-size: 1.5rem;
        }

        .info-card .card-text {
            line-height: 1.8;
            color: #555;
            font-size: 0.95rem;
        }

        .reagendamento-card {
            background: linear-gradient(135deg, #fff5e6 0%, #ffe8cc 100%);
            border-left: 5px solid var(--warning-color);
        }

        .reagendamento-card h5 {
            color: var(--warning-color);
            border-bottom-color: var(--warning-color);
        }

        /* Botões modernos */
        .btn {
            border-radius: 12px;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
            box-shadow: 0 4px 15px rgba(26, 35, 126, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(26, 35, 126, 0.4);
        }

        .btn-warning {
            background: linear-gradient(135deg, var(--warning-color) 0%, #ffb74d 100%);
            box-shadow: 0 4px 15px rgba(255, 152, 0, 0.3);
        }

        .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 152, 0, 0.4);
        }

        .btn-light {
            background: white;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }

        .btn-light:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }

        /* Formulários */
        .form-control, .form-select {
            border-radius: 12px;
            border: 2px solid #e0e0e0;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(26, 35, 126, 0.15);
            transform: translateY(-2px);
        }

        /* Alerts modernos */
        .alert {
            border-radius: 15px;
            border: none;
            padding: 1.25rem 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .alert-info {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            color: var(--primary-dark);
            border-left: 4px solid var(--primary-color);
        }

        /* Responsividade para dispositivos móveis */
        @media (max-width: 768px) {
            .header-bant h1 {
                font-size: 1.75rem;
            }

            .header-bant p {
                font-size: 0.95rem;
            }

            .calendario-container {
                padding: 1rem;
                margin-bottom: 1.5rem;
            }

            .card-body {
                padding: 1.5rem;
            }

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

            .sidebar-cards {
                gap: 1.5rem;
            }
        }

        /* Animações */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .calendario-container,
        .info-card,
        .reagendamento-card {
            animation: fadeInUp 0.6s ease-out;
        }

        .reagendamento-card {
            animation-delay: 0.2s;
            animation-fill-mode: both;
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
    <main class="container-fluid px-4">
        <div class="row g-4">
            <!-- Coluna do Calendário -->
            <div class="col-lg-8 col-xl-9">
                <div class="calendario-container">
                    <h3 class="mb-4" style="color: var(--primary-color); font-weight: 700;">
                        <i class="fas fa-calendar-alt me-2"></i>Calendário de Agendamentos
                    </h3>
                    <div id="calendario"></div>
                </div>
            </div>
            
            <!-- Coluna Lateral com Cards -->
            <div class="col-lg-4 col-xl-3">
                <div class="sidebar-cards">
                    <!-- Card Informações Gerais -->
                    <div class="info-card">
                        <h5>
                            <i class="fas fa-info-circle"></i>Informações Gerais
                        </h5>
                        <div class="card-text">
                            <ol class="mb-0" style="padding-left: 1.25rem;">
                                <li class="mb-3">No dia de realização do TACF, cada militar deverá estar em posse da sua <strong>FICHA DE ANAMNESE PREENCHIDA</strong>. Sendo assim, é <strong>RESPONSABILIDADE DO MILITAR</strong> preencher a Ficha de Anamnese com, no mínimo, 10 (dez) dias de antecedência ao dia agendado para realizar o TACF.</li>
                                <li class="mb-3">Ainda que o militar esteja impossibilitado de realizar o TACF por decisão da Junta de Saúde, ele deverá preencher a Ficha de Anamnese e de Avaliação do TACF e anexar o resultado da Junta e a cópia do boletim de publicação no SIGTACF. <strong class="text-danger">NESSE CASO, O MILITAR NÃO DEVE REALIZAR O AGENDAMENTO</strong>. No entanto, esse militar deve ir a SEF para entregar os documentos referentes a dispensa médica, bem como para assinar a lista de presença.</li>
                                <li class="mb-3">A realização do 2º TACF / 2025 nesta unidade só será permitida, mediante agendamento prévio. Não será permitido, <strong>EM HIPÓTESE ALGUMA</strong>, a realização do 2º TACF / 2025 sem que o militar esteja agendado.</li>
                                <li class="mb-3">Cada militar que estiver com seu agendamento confirmado, tem a responsabilidade de gerar sua indisponibilidade para escala de serviço no E-risaer. O documento para comprovar essa indisponibilidade, exigido pelo E-risaer, será a cópia do próprio Zimbra recebido com a confirmação do agendamento.</li>
                                <li class="mb-0">Cada militar deve apresentar-se na SEF para realização do TACF, com o 9º uniforme completo (incluindo tênis branco). A troca do tênis, caso o militar deseje, só será permitida quando o militar for realizar a etapa da corrida.</li>
                            </ol>
                        </div>
                    </div>
                    
                    <!-- Card REAGENDAMENTO -->
                    <div class="card reagendamento-card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-calendar-alt"></i>REAGENDAMENTO
                            </h5>
                            <p class="card-text text-muted mb-4" style="font-size: 0.95rem;">
                                Busque seu agendamento e solicite um reagendamento caso necessário.
                            </p>
                            
                            <form id="formBuscarReagendamento">
                                <div class="mb-3">
                                    <label for="cpfReagendamento" class="form-label fw-semibold">CPF</label>
                                    <input type="text" class="form-control" id="cpfReagendamento" 
                                           name="cpf" placeholder="000.000.000-00" maxlength="14" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="emailReagendamento" class="form-label fw-semibold">Email</label>
                                    <input type="email" class="form-control" id="emailReagendamento" 
                                           name="email" placeholder="seu.email@fab.mil.br" required>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100" id="btnBuscarReagendamento">
                                    <i class="fas fa-search me-2"></i>Buscar Agendamento
                                </button>
                            </form>
                            
                            <!-- Área de exibição do agendamento encontrado -->
                            <div id="agendamentoEncontrado" class="mt-4" style="display: none;">
                                <hr class="my-4">
                                <h6 class="mb-3 fw-bold" style="color: var(--warning-color);">
                                    <i class="fas fa-check-circle me-2"></i>Agendamento Encontrado
                                </h6>
                                <div class="alert alert-info">
                                    <p class="mb-2"><strong>Data do Teste:</strong> <span id="dataTesteEncontrado"></span></p>
                                    <p class="mb-2"><strong>Status:</strong> <span id="statusEncontrado"></span></p>
                                    <p class="mb-0"><strong>Militar:</strong> <span id="militarEncontrado"></span></p>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="motivoReagendamento" class="form-label fw-semibold">
                                        Motivo do Reagendamento <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control" id="motivoReagendamento" 
                                              name="motivo_reagendamento" rows="3" 
                                              placeholder="Descreva o motivo do reagendamento..." required></textarea>
                                </div>
                                
                                <button type="button" class="btn btn-warning w-100" id="btnReagendar">
                                    <i class="fas fa-calendar-check me-2"></i>REAGENDAR
                                </button>
                            </div>
                            
                            <!-- Mensagens de erro/sucesso -->
                            <div id="mensagemReagendamento" class="mt-3"></div>
                        </div>
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

    <!-- Aviso Modal -->
    <?php if ($aviso_popup['ativo']): ?>
    <div class="modal fade" id="avisoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-warning text-dark border-0">
                    <h5 class="modal-title"><?php echo htmlspecialchars($aviso_popup['titulo']); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <?php echo $aviso_popup['conteudo']; ?>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Modal de Autenticação LDAP -->
    <div class="modal fade" id="autenticacaoModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title">
                        <i class="fas fa-lock me-2"></i>Autenticação Necessária
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Para realizar o reagendamento, é necessário autenticar-se com suas credenciais do domínio.</p>
                    <form id="formAutenticacao">
                        <input type="hidden" id="agendamentoIdAutenticacao">
                        <input type="hidden" id="motivoReagendamentoAutenticacao">
                        
                        <div class="mb-3">
                            <label for="loginAutenticacao" class="form-label">Login (Domínio)</label>
                            <input type="text" class="form-control" id="loginAutenticacao" 
                                   name="login" placeholder="usuario ou usuario@BANT.INTRAER" required>
                            <div class="form-text">Digite seu login do Active Directory</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="senhaAutenticacao" class="form-label">Senha</label>
                            <input type="password" class="form-control" id="senhaAutenticacao" 
                                   name="password" required>
                        </div>
                        
                        <div id="mensagemAutenticacao" class="alert" style="display: none;"></div>
                    </form>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnConfirmarAutenticacao">
                        <i class="fas fa-sign-in-alt me-2"></i>Autenticar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Seleção de Nova Data -->
    <div class="modal fade" id="selecaoDataModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-success text-white border-0">
                    <h5 class="modal-title">
                        <i class="fas fa-calendar-day me-2"></i>Selecione a Nova Data
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Selecione a nova data para o seu teste físico (TACF):</p>
                    <div id="calendarioReagendamento"></div>
                    <input type="hidden" id="dataSelecionadaReagendamento">
                    <div id="mensagemDataReagendamento" class="mt-3"></div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" id="btnConfirmarDataReagendamento" disabled>
                        <i class="fas fa-check me-2"></i>Confirmar Data
                    </button>
                </div>
            </div>
        </div>
    </div>


    <!-- Footer -->
    <footer class="mt-5 py-4" style="background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%); border-top: 1px solid rgba(0,0,0,0.1);">
        <div class="container text-center">
            <p class="mb-0 text-muted">
                <i class="fas fa-copyright me-1"></i><?php echo date('Y'); ?> ETIC - BANT | Desenvolvido por 2S VANONI
            </p>
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

            // Exibir aviso ao carregar a página (apenas se o aviso estiver ativo)
            <?php if ($aviso_popup['ativo']): ?>
            var avisoModal = new bootstrap.Modal(document.getElementById('avisoModal'));
            avisoModal.show();
            <?php endif; ?>

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

            // ========== CÓDIGO DE REAGENDAMENTO ==========
            
            // Variáveis globais para reagendamento
            let agendamentoAtual = null;
            let calendarioReagendamento = null;
            
            // Formatação de CPF no campo de reagendamento
            document.getElementById('cpfReagendamento').addEventListener('input', function(e) {
                let cpf = e.target.value;
                e.target.value = formatarCPF(cpf);
            });
            
            // Buscar agendamento para reagendamento
            document.getElementById('formBuscarReagendamento').addEventListener('submit', function(e) {
                e.preventDefault();
                
                let cpf = document.getElementById('cpfReagendamento').value.replace(/\D/g, '');
                let email = document.getElementById('emailReagendamento').value.trim();
                
                if (cpf.length !== 11) {
                    mostrarMensagemReagendamento('Por favor, insira um CPF válido.', 'danger');
                    return;
                }
                
                if (!email) {
                    mostrarMensagemReagendamento('Por favor, insira um email válido.', 'danger');
                    return;
                }
                
                let btnBuscar = document.getElementById('btnBuscarReagendamento');
                btnBuscar.disabled = true;
                btnBuscar.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Buscando...';
                
                let formData = new FormData();
                formData.append('cpf', cpf);
                formData.append('email', email);
                
                fetch('buscar_agendamento_reagendamento.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    btnBuscar.disabled = false;
                    btnBuscar.innerHTML = '<i class="fas fa-search me-2"></i>Buscar Agendamento';
                    
                    if (data.success) {
                        agendamentoAtual = data.agendamento;
                        exibirAgendamentoEncontrado(data.agendamento);
                        mostrarMensagemReagendamento('Agendamento encontrado! Preencha o motivo do reagendamento.', 'success');
                    } else {
                        mostrarMensagemReagendamento(data.message, 'danger');
                        document.getElementById('agendamentoEncontrado').style.display = 'none';
                    }
                })
                .catch(error => {
                    btnBuscar.disabled = false;
                    btnBuscar.innerHTML = '<i class="fas fa-search me-2"></i>Buscar Agendamento';
                    mostrarMensagemReagendamento('Erro ao buscar agendamento. Tente novamente.', 'danger');
                });
            });
            
            // Exibir agendamento encontrado
            function exibirAgendamentoEncontrado(agendamento) {
                document.getElementById('dataTesteEncontrado').textContent = agendamento.data_teste_formatada;
                document.getElementById('statusEncontrado').textContent = agendamento.status_texto;
                document.getElementById('militarEncontrado').textContent = agendamento.posto_graduacao + ' ' + agendamento.nome_guerra;
                document.getElementById('agendamentoEncontrado').style.display = 'block';
                document.getElementById('motivoReagendamento').value = '';
            }
            
            // Mostrar mensagem no card de reagendamento
            function mostrarMensagemReagendamento(mensagem, tipo) {
                let div = document.getElementById('mensagemReagendamento');
                div.className = 'alert alert-' + tipo;
                div.textContent = mensagem;
                div.style.display = 'block';
                
                if (tipo === 'success') {
                    setTimeout(() => {
                        div.style.display = 'none';
                    }, 5000);
                }
            }
            
            // Botão REAGENDAR - Validar e abrir modal de seleção de data
            document.getElementById('btnReagendar').addEventListener('click', function() {
                let motivo = document.getElementById('motivoReagendamento').value.trim();
                
                if (!motivo) {
                    mostrarMensagemReagendamento('Por favor, informe o motivo do reagendamento.', 'danger');
                    return;
                }
                
                if (!agendamentoAtual) {
                    mostrarMensagemReagendamento('Erro: Agendamento não encontrado. Busque novamente.', 'danger');
                    return;
                }
                
                // Validar reagendamento antes de continuar
                let formData = new FormData();
                formData.append('agendamento_id', agendamentoAtual.id);
                
                fetch('validar_reagendamento.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Abrir modal de seleção de data
                        inicializarCalendarioReagendamento();
                        document.getElementById('motivoReagendamentoAutenticacao').value = motivo;
                        let modal = new bootstrap.Modal(document.getElementById('selecaoDataModal'));
                        modal.show();
                    } else {
                        mostrarMensagemReagendamento(data.message, 'danger');
                    }
                })
                .catch(error => {
                    mostrarMensagemReagendamento('Erro ao validar reagendamento. Tente novamente.', 'danger');
                });
            });
            
            // Inicializar calendário para seleção de nova data
            function inicializarCalendarioReagendamento() {
                let calendarioEl = document.getElementById('calendarioReagendamento');
                
                // Destruir calendário existente se houver
                if (calendarioReagendamento) {
                    calendarioReagendamento.destroy();
                }
                
                calendarioReagendamento = new FullCalendar.Calendar(calendarioEl, {
                    locale: 'pt-br',
                    initialView: 'dayGridMonth',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: ''
                    },
                    selectable: true,
                    select: function(info) {
                        let dataSelecionada = info.startStr;
                        document.getElementById('dataSelecionadaReagendamento').value = dataSelecionada;
                        document.getElementById('btnConfirmarDataReagendamento').disabled = false;
                        
                        // Verificar se há vagas
                        fetch('get_agendamentos.php')
                            .then(response => response.json())
                            .then(eventos => {
                                let evento = eventos.find(e => e.start === dataSelecionada);
                                if (!evento || evento.bloqueada) {
                                    document.getElementById('mensagemDataReagendamento').innerHTML = 
                                        '<div class="alert alert-warning">Não há vagas para esta data. Escolha outra.</div>';
                                    document.getElementById('btnConfirmarDataReagendamento').disabled = true;
                                } else {
                                    document.getElementById('mensagemDataReagendamento').innerHTML = 
                                        '<div class="alert alert-success">Data disponível! Clique em "Confirmar Data" para continuar.</div>';
                                }
                            });
                    },
                    events: 'get_agendamentos.php',
                    eventDidMount: function(info) {
                        if (info.event.extendedProps.bloqueada) {
                            info.el.classList.add('bg-danger', 'text-white');
                            info.el.innerHTML = 'Sem vagas';
                        } else {
                            info.el.classList.add('bg-success', 'text-white');
                            let vagasDisponiveis = info.event.extendedProps.limite - info.event.extendedProps.total;
                            info.el.innerHTML = 'Disponível<br>(' + vagasDisponiveis + ' vagas)';
                        }
                    },
                    eventClick: function(info) {
                        if (info.event.extendedProps.bloqueada) {
                            document.getElementById('mensagemDataReagendamento').innerHTML = 
                                '<div class="alert alert-warning">Não há vagas para esta data. Escolha outra.</div>';
                            document.getElementById('btnConfirmarDataReagendamento').disabled = true;
                            return;
                        }
                        let dataSelecionada = info.event.startStr;
                        document.getElementById('dataSelecionadaReagendamento').value = dataSelecionada;
                        document.getElementById('btnConfirmarDataReagendamento').disabled = false;
                        document.getElementById('mensagemDataReagendamento').innerHTML = 
                            '<div class="alert alert-success">Data disponível! Clique em "Confirmar Data" para continuar.</div>';
                    }
                });
                
                calendarioReagendamento.render();
            }
            
            // Confirmar data selecionada e abrir modal de autenticação
            document.getElementById('btnConfirmarDataReagendamento').addEventListener('click', function() {
                let dataSelecionada = document.getElementById('dataSelecionadaReagendamento').value;
                
                if (!dataSelecionada) {
                    document.getElementById('mensagemDataReagendamento').innerHTML = 
                        '<div class="alert alert-danger">Por favor, selecione uma data.</div>';
                    return;
                }
                
                // Fechar modal de seleção de data
                let modalData = bootstrap.Modal.getInstance(document.getElementById('selecaoDataModal'));
                modalData.hide();
                
                // Preparar dados para autenticação
                document.getElementById('agendamentoIdAutenticacao').value = agendamentoAtual.id;
                document.getElementById('loginAutenticacao').value = '';
                document.getElementById('senhaAutenticacao').value = '';
                document.getElementById('mensagemAutenticacao').style.display = 'none';
                
                // Abrir modal de autenticação
                let modalAuth = new bootstrap.Modal(document.getElementById('autenticacaoModal'));
                modalAuth.show();
            });
            
            // Processar autenticação e reagendamento
            document.getElementById('btnConfirmarAutenticacao').addEventListener('click', function() {
                let login = document.getElementById('loginAutenticacao').value.trim();
                let senha = document.getElementById('senhaAutenticacao').value;
                let agendamentoId = document.getElementById('agendamentoIdAutenticacao').value;
                let motivo = document.getElementById('motivoReagendamentoAutenticacao').value;
                let novaData = document.getElementById('dataSelecionadaReagendamento').value;
                
                if (!login || !senha) {
                    mostrarMensagemAutenticacao('Por favor, preencha login e senha.', 'danger');
                    return;
                }
                
                let btnConfirmar = document.getElementById('btnConfirmarAutenticacao');
                btnConfirmar.disabled = true;
                btnConfirmar.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processando...';
                
                let formData = new FormData();
                formData.append('agendamento_id', agendamentoId);
                formData.append('login', login);
                formData.append('password', senha);
                formData.append('motivo_reagendamento', motivo);
                formData.append('nova_data', novaData);
                
                fetch('processar_reagendamento.php', {
                    method: 'POST',
                    body: formData
                })
                .then(async response => {
                    let data = null;
                    try {
                        data = await response.json();
                    } catch (e) {
                        throw new Error('Resposta inválida do servidor');
                    }
                    
                    if (!response.ok || !data || !data.success) {
                        let message = (data && data.message) ? data.message : 'Falha ao processar reagendamento.';
                        throw new Error(message);
                    }
                    return data;
                })
                .then(data => {
                    // Fechar modal de autenticação
                    let modalAuth = bootstrap.Modal.getInstance(document.getElementById('autenticacaoModal'));
                    modalAuth.hide();
                    
                    // Mostrar mensagem de sucesso
                    let mensagemSucesso = 'Reagendamento realizado com sucesso!\n\n';
                    mensagemSucesso += 'Data Original: ' + data.data_original + '\n';
                    mensagemSucesso += 'Nova Data: ' + data.data_nova + '\n\n';
                    mensagemSucesso += 'Você receberá um email de confirmação.';
                    
                    if (data.emails && (data.emails.comunicacao === false || data.emails.solicitante === false)) {
                        let falhas = [];
                        if (data.emails.comunicacao === false) falhas.push('comunicação');
                        if (data.emails.solicitante === false) falhas.push('solicitante');
                        mensagemSucesso += '\n\nAtenção: Houve falha ao enviar e-mail para: ' + falhas.join(', ') + '.';
                    }
                    
                    alert(mensagemSucesso);
                    
                    // Limpar formulário e recarregar página
                    document.getElementById('formBuscarReagendamento').reset();
                    document.getElementById('agendamentoEncontrado').style.display = 'none';
                    document.getElementById('mensagemReagendamento').style.display = 'none';
                    agendamentoAtual = null;
                    
                    // Recarregar página para atualizar calendário
                    location.reload();
                })
                .catch(error => {
                    btnConfirmar.disabled = false;
                    btnConfirmar.innerHTML = '<i class="fas fa-sign-in-alt me-2"></i>Autenticar';
                    mostrarMensagemAutenticacao('Erro: ' + error.message, 'danger');
                });
            });
            
            // Mostrar mensagem no modal de autenticação
            function mostrarMensagemAutenticacao(mensagem, tipo) {
                let div = document.getElementById('mensagemAutenticacao');
                div.className = 'alert alert-' + tipo;
                div.textContent = mensagem;
                div.style.display = 'block';
            }
            
            // Limpar formulário quando modal de autenticação for fechado
            document.getElementById('autenticacaoModal').addEventListener('hidden.bs.modal', function() {
                document.getElementById('formAutenticacao').reset();
                document.getElementById('mensagemAutenticacao').style.display = 'none';
                let btnConfirmar = document.getElementById('btnConfirmarAutenticacao');
                btnConfirmar.disabled = false;
                btnConfirmar.innerHTML = '<i class="fas fa-sign-in-alt me-2"></i>Autenticar';
            });

        });
    </script>
</body>
</html> 
