<?php
include '../conex.php';

// Inicializa variáveis
$mensagem = '';
$tipo_mensagem = '';
$reserva = null;

// Processa ações (editar/excluir)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';
    $id_reserva = $_POST['id_reserva'] ?? null;
    
    if ($action == 'editar' && $id_reserva) {
        // Atualizar reserva existente
        $id_hos = $_POST['cliente_id'] ?? null;
        $id_quarto = $_POST['quarto_id'] ?? null;
        $data_reserva = $_POST['data_reserva'] ?? null;
        $data_encerrada = $_POST['data_encerrada'] ?? null;
        $status = $_POST['status'] ?? 'CONFIRMADA';
        
        $conn->begin_transaction();
        
        try {
            // 1. Obtém dados atuais da reserva
            $stmt_get = $conn->prepare("SELECT id_quarto FROM reservas WHERE id_reserva = ?");
            $stmt_get->bind_param("i", $id_reserva);
            $stmt_get->execute();
            $result = $stmt_get->get_result();
            $reserva_atual = $result->fetch_assoc();
            
            // 2. Atualiza reserva
            $stmt_update = $conn->prepare("UPDATE reservas SET 
                                         id_hos = ?, 
                                         id_quarto = ?, 
                                         data_reserva = ?, 
                                         data_encerrada = ?, 
                                         status_atual = ? 
                                         WHERE id_reserva = ?");
            $stmt_update->bind_param("iisssi", $id_hos, $id_quarto, $data_reserva, $data_encerrada, $status, $id_reserva);
            $stmt_update->execute();
            
            // 3. Libera quarto antigo se foi alterado
            if ($reserva_atual['id_quarto'] != $id_quarto) {
                $stmt_liberar = $conn->prepare("UPDATE quartos SET disponivel = TRUE WHERE id_quarto = ?");
                $stmt_liberar->bind_param("i", $reserva_atual['id_quarto']);
                $stmt_liberar->execute();
                
                // 4. Ocupa novo quarto
                $stmt_ocupar = $conn->prepare("UPDATE quartos SET disponivel = FALSE WHERE id_quarto = ?");
                $stmt_ocupar->bind_param("i", $id_quarto);
                $stmt_ocupar->execute();
            }
            
            $conn->commit();
            $mensagem = "Reserva atualizada com sucesso!";
            $tipo_mensagem = "success";
            
        } catch (Exception $e) {
            $conn->rollback();
            $mensagem = "Erro ao atualizar reserva: " . $e->getMessage();
            $tipo_mensagem = "danger";
        }
        
    } elseif ($action == 'excluir' && $id_reserva) {
        // Excluir reserva
        $conn->begin_transaction();
        
        try {
            // 1. Obtém id_quarto para liberá-lo
            $stmt_get = $conn->prepare("SELECT id_quarto FROM reservas WHERE id_reserva = ?");
            $stmt_get->bind_param("i", $id_reserva);
            $stmt_get->execute();
            $result = $stmt_get->get_result();
            $reserva = $result->fetch_assoc();
            
            if ($reserva) {
                // 2. Libera o quarto
                $stmt_quarto = $conn->prepare("UPDATE quartos SET disponivel = TRUE WHERE id_quarto = ?");
                $stmt_quarto->bind_param("i", $reserva['id_quarto']);
                $stmt_quarto->execute();
                
                // 3. Remove a reserva
                $stmt_delete = $conn->prepare("DELETE FROM reservas WHERE id_reserva = ?");
                $stmt_delete->bind_param("i", $id_reserva);
                $stmt_delete->execute();
                
                $conn->commit();
                $mensagem = "Reserva excluída com sucesso!";
                $tipo_mensagem = "success";
            } else {
                $mensagem = "Reserva não encontrada!";
                $tipo_mensagem = "danger";
            }
            
        } catch (Exception $e) {
            $conn->rollback();
            $mensagem = "Erro ao excluir reserva: " . $e->getMessage();
            $tipo_mensagem = "danger";
        }
    }
}

// Carrega dados para edição se houver ID na URL
$id_editar = $_GET['editar'] ?? null;
if ($id_editar) {
    $stmt = $conn->prepare("SELECT * FROM reservas WHERE id_reserva = ?");
    $stmt->bind_param("i", $id_editar);
    $stmt->execute();
    $result = $stmt->get_result();
    $reserva = $result->fetch_assoc();
}

// Carrega listas de hóspedes e quartos
$hospedes = [];
$quartos_disponiveis = [];
$quartos_ocupados = [];

$sql_hospedes = "SELECT id_hos, nome FROM hospede";
$result = $conn->query($sql_hospedes);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $hospedes[$row['id_hos']] = $row['nome'];
    }
}

$sql_quartos = "SELECT id_quarto, nome, disponivel FROM quartos";
$result = $conn->query($sql_quartos);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        if ($row['disponivel']) {
            $quartos_disponiveis[$row['id_quarto']] = $row['nome'];
        } else {
            $quartos_ocupados[$row['id_quarto']] = $row['nome'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $id_editar ? 'Editar' : 'Gerenciar' ?> Reserva</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1000px;
            margin: 20px auto;
            padding: 20px;
        }
        .form-container {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        .status-confirmada {
            background-color: #d4edda;
            color: #155724;
        }
        .status-cancelada {
            background-color: #f8d7da;
            color: #721c24;
        }
        .status-pendente {
            background-color: #fff3cd;
            color: #856404;
        }
        .table-responsive {
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4"><?= $id_editar ? 'Editar' : 'Gerenciar' ?> Reserva</h1>
        
        <?php if ($mensagem): ?>
            <div class="alert alert-<?= $tipo_mensagem ?> alert-dismissible fade show" role="alert">
                <?= $mensagem ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($id_editar && !$reserva): ?>
            <div class="alert alert-danger">
                Reserva não encontrada!
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-container">
                        <form method="post" action="">
                            <input type="hidden" name="action" value="<?= $id_editar ? 'editar' : '' ?>">
                            <input type="hidden" name="id_reserva" value="<?= $id_editar ?>">
                            
                            <div class="mb-3">
                                <label for="cliente_id" class="form-label">Hóspede:</label>
                                <select class="form-select" id="cliente_id" name="cliente_id" required>
                                    <?php foreach ($hospedes as $id => $nome): ?>
                                        <option value="<?= $id ?>" <?= ($reserva && $reserva['id_hos'] == $id) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($nome) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="quarto_id" class="form-label">Quarto:</label>
                                <select class="form-select" id="quarto_id" name="quarto_id" required>
                                    <optgroup label="Quartos Disponíveis">
                                        <?php foreach ($quartos_disponiveis as $id => $nome): ?>
                                            <option value="<?= $id ?>" <?= ($reserva && $reserva['id_quarto'] == $id) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($nome) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                    <optgroup label="Quartos Ocupados">
                                        <?php foreach ($quartos_ocupados as $id => $nome): ?>
                                            <option value="<?= $id ?>" <?= ($reserva && $reserva['id_quarto'] == $id) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($nome) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="data_reserva" class="form-label">Data de Reserva:</label>
                                <input type="date" class="form-control" id="data_reserva" name="data_reserva" 
                                       value="<?= $reserva ? htmlspecialchars($reserva['data_reserva']) : date('Y-m-d') ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="data_encerrada" class="form-label">Data de Encerramento (opcional):</label>
                                <input type="date" class="form-control" id="data_encerrada" name="data_encerrada" 
                                       value="<?= $reserva ? htmlspecialchars($reserva['data_encerrada'] ?? '') : '' ?>">
                            </div>
                            
                            <div class="mb-3">
                                <label for="status" class="form-label">Status:</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="CONFIRMADA" <?= ($reserva && $reserva['status_atual'] == 'CONFIRMADA') ? 'selected' : '' ?>>Confirmada</option>
                                    <option value="CANCELADA" <?= ($reserva && $reserva['status_atual'] == 'CANCELADA') ? 'selected' : '' ?>>Cancelada</option>
                                    <option value="PENDENTE" <?= ($reserva && $reserva['status_atual'] == 'PENDENTE') ? 'selected' : '' ?>>Pendente</option>
                                </select>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary"><?= $id_editar ? 'Atualizar' : 'Salvar' ?> Reserva</button>
                                <?php if ($id_editar): ?>
                                    <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='gerenciar_reservas.php'">Cancelar</button>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
                
                <?php if (!$id_editar): ?>
                <div class="col-md-6">
                    <h3>Lista de Reservas</h3>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Hóspede</th>
                                    <th>Quarto</th>
                                    <th>Data</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT r.id_reserva, r.data_reserva, r.status_atual, 
                                               h.nome as nome_hospede, q.nome as nome_quarto
                                        FROM reservas r
                                        JOIN hospede h ON r.id_hos = h.id_hos
                                        JOIN quartos q ON r.id_quarto = q.id_quarto
                                        ORDER BY r.data_reserva DESC";
                                $result = $conn->query($sql);
                                
                                if ($result && $result->num_rows > 0):
                                    while ($row = $result->fetch_assoc()):
                                        $status_class = strtolower($row['status_atual']);
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['id_reserva']) ?></td>
                                    <td><?= htmlspecialchars($row['nome_hospede']) ?></td>
                                    <td><?= htmlspecialchars($row['nome_quarto']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($row['data_reserva'])) ?></td>
                                    <td>
                                        <span class="status-badge status-<?= $status_class ?>">
                                            <?= htmlspecialchars($row['status_atual']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="?editar=<?= $row['id_reserva'] ?>" class="btn btn-sm btn-warning">Editar</a>
                                        <form method="post" action="" style="display: inline;">
                                            <input type="hidden" name="action" value="excluir">
                                            <input type="hidden" name="id_reserva" value="<?= $row['id_reserva'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza que deseja excluir esta reserva?')">Excluir</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php
                                    endwhile;
                                else:
                                ?>
                                <tr>
                                    <td colspan="6" class="text-center">Nenhuma reserva encontrada</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validação de datas
        document.querySelector('form').addEventListener('submit', function(e) {
            const dataReserva = document.getElementById('data_reserva').value;
            const dataEncerrada = document.getElementById('data_encerrada').value;
            
            if (dataEncerrada && dataEncerrada < dataReserva) {
                alert('A data de encerramento não pode ser anterior à data de reserva!');
                e.preventDefault();
            }
        });
    </script>
</body>
</html>

<?php
// Fecha a conexão
$conn->close();
?>