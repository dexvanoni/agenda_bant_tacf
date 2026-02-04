<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Processamento de ações (adicionar, editar, excluir)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['adicionar_datas'])) {
        $datas = explode(',', $_POST['datas']);
        $limite = (int)$_POST['limite_agendamentos'];
        foreach ($datas as $data) {
            $data = trim($data);
            if ($data) {
                $stmt = $conn->prepare("INSERT IGNORE INTO datas_liberadas (data, limite_agendamentos) VALUES (?, ?)");
                $stmt->execute([$data, $limite]);
            }
        }
    } elseif (isset($_POST['editar_limite'])) {
        $id = (int)$_POST['id'];
        $novo_limite = (int)$_POST['novo_limite'];
        $stmt = $conn->prepare("UPDATE datas_liberadas SET limite_agendamentos = ? WHERE id = ?");
        $stmt->execute([$novo_limite, $id]);
    } elseif (isset($_POST['excluir_data'])) {
        $id = (int)$_POST['id'];
        $stmt = $conn->prepare("DELETE FROM datas_liberadas WHERE id = ?");
        $stmt->execute([$id]);
    }
    header('Location: datas_liberadas.php');
    exit();
}

// Buscar datas liberadas
$stmt = $conn->query("SELECT * FROM datas_liberadas ORDER BY data ASC");
$datas_liberadas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Datas Liberadas - Administração</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
            min-height: 100vh;
        }

        .page-header {
            margin-bottom: 2rem;
            padding: 1.5rem 1.25rem;
            border-radius: 1rem;
            background: linear-gradient(135deg, #1a237e 0%, #3949ab 100%);
            color: #fff;
            box-shadow: 0 4px 20px rgba(26, 35, 126, 0.35);
        }

        .page-header h2 {
            margin: 0;
            font-weight: 600;
        }

        .page-header p {
            margin: 0.25rem 0 0;
            opacity: 0.9;
        }

        .card {
            border-radius: 1rem;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
            border: none;
        }

        .card-header {
            border-radius: 1rem 1rem 0 0 !important;
            font-weight: 600;
        }

        .table thead th {
            background-color: #f3f4f8;
            border-bottom-width: 0;
        }

        .badge-data {
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="page-header d-flex justify-content-between align-items-center">
            <div>
                <h2 class="d-flex align-items-center">
                    <i class="fas fa-calendar-check me-2"></i>
                    Datas Liberadas para Agendamento
                </h2>
                <p>Gerencie os dias disponíveis para o TACF e o limite máximo de agendamentos por data.</p>
            </div>
            <a href="index.php" class="btn btn-outline-light">
                <i class="fas fa-arrow-left me-1"></i> Voltar
            </a>
        </div>

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <i class="fas fa-plus-circle me-2"></i>Adicionar novas datas
                    </div>
                    <div class="card-body">
                        <form method="POST" class="row g-3">
                            <div class="col-12">
                                <label for="datas" class="form-label">Datas (AAAA-MM-DD, separadas por vírgula)</label>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="datas" 
                                    name="datas" 
                                    placeholder="2026-03-10,2026-03-11" 
                                    required
                                >
                                <div class="form-text">
                                    Informe uma ou mais datas no formato <strong>AAAA-MM-DD</strong>, separadas por vírgula.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="limite_agendamentos" class="form-label">Limite de Agendamentos</label>
                                <input 
                                    type="number" 
                                    class="form-control" 
                                    id="limite_agendamentos" 
                                    name="limite_agendamentos" 
                                    min="1" 
                                    required
                                >
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <button type="submit" name="adicionar_datas" class="btn btn-success w-100">
                                    <i class="fas fa-plus me-1"></i> Adicionar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-list-ul me-2"></i>Datas já liberadas
                    </div>
                    <div class="card-body">
                        <?php if (empty($datas_liberadas)): ?>
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-info-circle me-1"></i>
                                Nenhuma data liberada cadastrada até o momento.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Data do TACF</th>
                                            <th class="text-center">Limite de Agendamentos</th>
                                            <th class="text-center">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($datas_liberadas as $data): ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-light text-dark badge-data">
                                                    <i class="fas fa-calendar-day me-1 text-primary"></i>
                                                    <?= date('d/m/Y', strtotime($data['data'])) ?>
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <form method="POST" class="d-inline-flex align-items-center justify-content-center">
                                                    <input type="hidden" name="id" value="<?= $data['id'] ?>">
                                                    <input 
                                                        type="number" 
                                                        name="novo_limite" 
                                                        value="<?= $data['limite_agendamentos'] ?>" 
                                                        min="1" 
                                                        class="form-control form-control-sm me-2" 
                                                        style="width: 80px;"
                                                    >
                                                    <button type="submit" name="editar_limite" class="btn btn-primary btn-sm">
                                                        <i class="fas fa-save"></i>
                                                    </button>
                                                </form>
                                            </td>
                                            <td class="text-center">
                                                <form method="POST" onsubmit="return confirm('Tem certeza que deseja excluir esta data?');" class="d-inline-block">
                                                    <input type="hidden" name="id" value="<?= $data['id'] ?>">
                                                    <button type="submit" name="excluir_data" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 