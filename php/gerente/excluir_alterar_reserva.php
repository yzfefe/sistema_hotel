<?php
include '../conex.php';

// Inicializa variáveis
$mensagem = '';
$tipo_mensagem = '';

// Processa exclusão de reserva
if (isset($_GET['excluir'])) {
    $reserva_id = $_GET['excluir'];
    
    // Inicia transação
    $conn->begin_transaction();
    
    try {
        // 1. Obtém dados da reserva
        $stmt = $conn->prepare("SELECT r.id_hos, r.id_quarto, q.preco_diaria 
                              FROM reservas r 
                              JOIN quartos q ON r.id_quarto = q.id_quarto 
                              WHERE r.id_reserva = ? AND r.status_atual = 'CONFIRMADA'");
        $stmt->bind_param("i", $reserva_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $reserva = $result->fetch_assoc();
            $cliente_id = $reserva['id_hos'];
            $quarto_id = $reserva['id_quarto'];
            $preco_diaria = $reserva['preco_diaria'];
            
            // 2. Remove a reserva
            $stmt_delete = $conn->prepare("UPDATE reservas SET status_atual  = 'ENCERRADA' WHERE id_reserva = ?;");
            $stmt_delete->bind_param("i", $reserva_id);
            $stmt_delete->execute();
            
            // 3. Libera o quarto
            $stmt_quarto = $conn->prepare("UPDATE quartos SET disponivel = TRUE WHERE id_quarto = ?");
            $stmt_quarto->bind_param("i", $quarto_id);
            $stmt_quarto->execute();
            
            // 4. Atualiza gastos do hóspede (subtrai o valor)
            $stmt_gastos = $conn->prepare("UPDATE hospede 
                                         SET gastos_atuais = gastos_atuais - ? 
                                         WHERE id_hos = ?");
            $stmt_gastos->bind_param("di", $preco_diaria, $cliente_id);
            $stmt_gastos->execute();
            
            // Confirma transação
            $conn->commit();
            $mensagem = "Reserva Encerrada com sucesso! Quarto liberado.";
            $tipo_mensagem = "success";
        } else {
            throw new Exception("Reserva confirmada não encontrada");
        }
    } catch (Exception $e) {
        // Rollback em caso de erro
        $conn->rollback();
        $mensagem = "Erro ao excluir reserva: " . $e->getMessage();
        $tipo_mensagem = "danger";
    }
}

// Processa alteração de data
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['alterar_data'])) {
    $reserva_id = $_POST['reserva_id'] ?? null;
    $nova_data = $_POST['nova_data'] ?? null;

    if (!$reserva_id || !$nova_data) {
        $mensagem = "Todos os campos são obrigatórios!";
        $tipo_mensagem = "danger";
    } else {
        try {
            // Atualiza apenas a data da reserva confirmada
            $stmt = $conn->prepare("UPDATE reservas SET data_reserva = ? 
                                  WHERE id_reserva = ? AND status_atual = 'CONFIRMADA'");
            $stmt->bind_param("si", $nova_data, $reserva_id);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                $mensagem = "Data da reserva atualizada com sucesso!";
                $tipo_mensagem = "success";
            } else {
                $mensagem = "Nenhuma reserva confirmada encontrada para atualizar";
                $tipo_mensagem = "warning";
            }
        } catch (Exception $e) {
            $mensagem = "Erro ao atualizar data: " . $e->getMessage();
            $tipo_mensagem = "danger";
        }
    }
}

// Busca reservas ativas para exibição
$reservas_ativas = [];
$sql = "SELECT r.id_reserva, r.data_reserva, h.nome AS nome_hospede, 
               q.nome AS nome_quarto, q.preco_diaria, q.id_quarto
        FROM reservas r
        JOIN hospede h ON r.id_hos = h.id_hos
        JOIN quartos q ON r.id_quarto = q.id_quarto
        WHERE r.status_atual = 'CONFIRMADA'
        ORDER BY r.data_reserva";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $reservas_ativas[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Reservas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        .reserva-item {
            border: 1px solid #ddd;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        .form-alterar {
            background-color: #fff;
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
            border: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">Gerenciamento de Reservas Confirmadas</h1>
        
        <?php if ($mensagem): ?>
            <div class="alert alert-<?= $tipo_mensagem ?> alert-dismissible fade show" role="alert">
                <?= $mensagem ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="reservas-list">
            <h2 class="mb-3">Reservas Confirmadas</h2>
            
            <?php if (count($reservas_ativas) > 0): ?>
                <?php foreach ($reservas_ativas as $reserva): ?>
                    <div class="reserva-item">
                        <div class="row">
                            <div class="col-md-8">
                                <h4>Reserva #<?= $reserva['id_reserva'] ?></h4>
                                <p><strong>Hóspede:</strong> <?= htmlspecialchars($reserva['nome_hospede']) ?></p>
                                <p><strong>Quarto:</strong> <?= htmlspecialchars($reserva['nome_quarto']) ?> (ID: <?= $reserva['id_quarto'] ?>)</p>
                                <p><strong>Data Atual:</strong> <?= date('d/m/Y', strtotime($reserva['data_reserva'])) ?></p>
                                <p><strong>Valor Diária:</strong> R$ <?= number_format($reserva['preco_diaria'], 2, ',', '.') ?></p>
                            </div>
                            <div class="col-md-4 text-end">
                                <button class="btn btn-outline-primary btn-alterar-data" 
                                        data-reserva-id="<?= $reserva['id_reserva'] ?>">
                                    Alterar Data
                                </button>
                                <a href="?excluir=<?= $reserva['id_reserva'] ?>" 
                                   class="btn btn-outline-danger"
                                   onclick="return confirm('Tem certeza que deseja excluir esta reserva? O quarto será liberado.')">
                                    Excluir Reserva
                                </a>
                            </div>
                        </div>
                        
                        <div class="form-alterar" id="form-<?= $reserva['id_reserva'] ?>" style="display: none;">
                            <form method="post" action="">
                                <input type="hidden" name="reserva_id" value="<?= $reserva['id_reserva'] ?>">
                                <div class="row g-3 align-items-center">
                                    <div class="col-auto">
                                        <label for="nova_data" class="col-form-label">Nova Data:</label>
                                    </div>
                                    <div class="col-auto">
                                        <input type="date" class="form-control" name="nova_data" 
                                               value="<?= date('Y-m-d', strtotime($reserva['data_reserva'])) ?>" 
                                               min="<?= date('Y-m-d') ?>" required>
                                    </div>
                                    <div class="col-auto">
                                        <button type="submit" name="alterar_data" class="btn btn-primary">
                                            Confirmar
                                        </button>
                                        <button type="button" class="btn btn-secondary btn-cancelar">
                                            Cancelar
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-info">Nenhuma reserva confirmada no momento.</div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mostra/oculta formulário de alteração de data
        document.querySelectorAll('.btn-alterar-data').forEach(btn => {
            btn.addEventListener('click', function() {
                const reservaId = this.getAttribute('data-reserva-id');
                const form = document.getElementById(`form-${reservaId}`);
                
                // Oculta todos os outros formulários
                document.querySelectorAll('.form-alterar').forEach(f => {
                    if (f !== form) f.style.display = 'none';
                });
                
                // Alterna o formulário atual
                form.style.display = form.style.display === 'none' ? 'block' : 'none';
            });
        });

        // Cancela a alteração de data
        document.querySelectorAll('.btn-cancelar').forEach(btn => {
            btn.addEventListener('click', function() {
                this.closest('.form-alterar').style.display = 'none';
            });
        });
    </script>
</body>
</html>

<?php
// Fecha a conexão
$conn->close();
?>
