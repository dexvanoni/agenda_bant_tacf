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
</head>
<body>
    <div class="container mt-4">
        <h2>Datas Liberadas para Agendamento</h2>
        <a href="index.php" class="btn btn-secondary mb-3"><i class="fas fa-arrow-left"></i> Voltar</a>
        <form method="POST" class="row g-3 mb-4">
            <div class="col-md-5">
                <label for="datas" class="form-label">Datas (AAAA-MM-DD, separadas por vírgula)</label>
                <input type="text" class="form-control" id="datas" name="datas" placeholder="2024-06-10,2024-06-11" required>
            </div>
            <div class="col-md-3">
                <label for="limite_agendamentos" class="form-label">Limite de Agendamentos</label>
                <input type="number" class="form-control" id="limite_agendamentos" name="limite_agendamentos" min="1" required>
            </div>
            <div class="col-md-2 align-self-end">
                <button type="submit" name="adicionar_datas" class="btn btn-success w-100"><i class="fas fa-plus"></i> Adicionar</button>
            </div>
        </form>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Data do TACF</th>
                    <th>Limite de Agendamentos</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($datas_liberadas as $data): ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($data['data'])) ?></td>
                    <td>
                        <form method="POST" class="d-flex align-items-center">
                            <input type="hidden" name="id" value="<?= $data['id'] ?>">
                            <input type="number" name="novo_limite" value="<?= $data['limite_agendamentos'] ?>" min="1" class="form-control form-control-sm me-2" style="width: 80px;">
                            <button type="submit" name="editar_limite" class="btn btn-primary btn-sm"><i class="fas fa-save"></i></button>
                        </form>
                    </td>
                    <td>
                        <form method="POST" onsubmit="return confirm('Tem certeza que deseja excluir esta data?');">
                            <input type="hidden" name="id" value="<?= $data['id'] ?>">
                            <button type="submit" name="excluir_data" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html> 