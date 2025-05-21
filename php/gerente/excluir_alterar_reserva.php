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
                              FROM reservas_quarto r 
                              JOIN quartos q ON r.id_quarto = q.id_quarto 
                              WHERE r.id_reserva = ? AND r.status = 'CONFIRMADA'");
        $stmt->bind_param("i", $reserva_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $reserva = $result->fetch_assoc();
            $cliente_id = $reserva['id_hos'];
            $quarto_id = $reserva['id_quarto'];
            $preco_diaria = $reserva['preco_diaria'];
            
            // 2. Remove a reserva
            $stmt_delete = $conn->prepare("UPDATE reservas_quarto SET status  = 'ENCERRADA' WHERE id_reserva = ?;");
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
            $stmt = $conn->prepare("UPDATE reservas_quarto SET data_reserva = ? 
                                  WHERE id_reserva = ? AND status = 'CONFIRMADA'");
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
        FROM reservas_quarto r
        JOIN hospede h ON r.id_hos = h.id_hos
        JOIN quartos q ON r.id_quarto = q.id_quarto
        WHERE r.status = 'CONFIRMADA'
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
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(180deg, rgba(107, 57, 42, 0.946) 6%, rgba(133,78,57,1) 18%, rgb(203, 164, 122) 60%, rgba(217, 192, 164, 0.339) 90%, rgba(255,255,255,1) 110%);
            background-attachment: fixed;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 30px;
            margin-top: 2rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(203, 164, 122, 0.3);
            max-width: 1200px;
        }
        
        .logo {
            width: 20rem;
            display: block;
            margin: 0 auto 2rem auto;
            filter: drop-shadow(2px 2px 4px rgba(0, 0, 0, 0.3));
        }
        
        h1, h2 {
            color: #5a3022;
            text-shadow: 1px 1px 2px rgba(255, 255, 255, 0.5);
            margin-bottom: 1.5rem;
        }
        
        .reserva-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            background-color: rgba(255, 255, 255, 0.9);
            transition: transform 0.3s;
        }
        
        .reserva-card:hover {
            transform: translateY(-5px);
        }
        
        .reserva-header {
            background: linear-gradient(135deg, rgba(133,78,57,1) 0%, rgba(107, 57, 42, 0.946) 100%);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 15px;
        }
        
        .btn-primary {
            background-color: #6b392a;
            border-color: #5a3022;
        }
        
        .btn-primary:hover {
            background-color: #5a3022;
            border-color: #4a281b;
        }
        
        .btn-outline-primary {
            color: #6b392a;
            border-color: #6b392a;
        }
        
        .btn-outline-primary:hover {
            background-color: #6b392a;
            color: white;
        }
        
        .btn-outline-danger {
            color: #9e4a3b;
            border-color: #9e4a3b;
        }
        
        .btn-outline-danger:hover {
            background-color: #9e4a3b;
            color: white;
        }
        
        .form-alterar {
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 8px;
            padding: 20px;
            margin-top: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(203, 164, 122, 0.5);
        }
        
        .form-control:focus {
            border-color: #c44512;
            box-shadow: 0 0 0 0.25rem rgba(194, 87, 44, 0.25);
        }
        
        .alert {
            border-radius: 8px;
            border: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #5a3022;
        }
        
        .info-value {
            color: #333;
        }
        .btn-outline-primary {
            color: #6b392a;
            border-color: #6b392a;
        }
        
        .btn-outline-primary:hover {
            background-color: #6b392a;
            color: white;
        }

    </style>
</head>
<body>
<body>
    <div class="container">
        <a href="../../html/gerente/tela_gerente.html" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>

        <img src="../../img/logo_hoteel.png" alt="Logo do Hotel" class="logo">
        <h1 class="text-center"><i class="bi bi-calendar-check"></i> Gerenciamento de Reservas Confirmadas</h1>
        
        <?php if ($mensagem): ?>
            <div class="alert alert-<?= $tipo_mensagem ?> alert-dismissible fade show" role="alert">
                <?= $mensagem ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>


        <div class="reservas-list">
            <h2 class="mb-4"><i class="bi bi-list-check"></i> Reservas Confirmadas</h2>
            
            <?php if (count($reservas_ativas) > 0): ?>
                <?php foreach ($reservas_ativas as $reserva): ?>
                    <div class="card reserva-card mb-4">
                        <div class="card-header reserva-header">
                            <h4 class="card-title mb-0">
                                <i class="bi bi-card-checklist"></i> Reserva #<?= $reserva['id_reserva'] ?>
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <p><span class="info-label"><i class="bi bi-person"></i> Hóspede:</span> 
                                       <span class="info-value"><?= htmlspecialchars($reserva['nome_hospede']) ?></span></p>
                                    <p><span class="info-label"><i class="bi bi-door-open"></i> Quarto:</span> 
                                       <span class="info-value"><?= htmlspecialchars($reserva['nome_quarto']) ?> (ID: <?= $reserva['id_quarto'] ?>)</span></p>
                                    <p><span class="info-label"><i class="bi bi-calendar-date"></i> Data Atual:</span> 
                                       <span class="info-value"><?= date('d/m/Y', strtotime($reserva['data_reserva'])) ?></span></p>
                                    <p><span class="info-label"><i class="bi bi-cash-coin"></i> Valor Diária:</span> 
                                       <span class="info-value">R$ <?= number_format($reserva['preco_diaria'], 2, ',', '.') ?></span></p>
                                </div>
                                <div class="col-md-4 text-end">
                                    <button class="btn btn-outline-primary btn-alterar-data mb-2" 
                                            data-reserva-id="<?= $reserva['id_reserva'] ?>">
                                        <i class="bi bi-calendar-event"></i> Alterar Data
                                    </button>
                                    <a href="?excluir=<?= $reserva['id_reserva'] ?>" 
                                       class="btn btn-outline-danger"
                                       onclick="return confirm('Tem certeza que deseja encerrar esta reserva? O quarto será liberado.')">
                                        <i class="bi bi-trash"></i> Encerrar Reserva
                                    </a>
                                </div>
                            </div>
                            
                            <div class="form-alterar" id="form-<?= $reserva['id_reserva'] ?>" style="display: none;">
                                <form method="post" action="">
                                    <input type="hidden" name="reserva_id" value="<?= $reserva['id_reserva'] ?>">
                                    <div class="row g-3 align-items-center">
                                        <div class="col-md-4">
                                            <label for="nova_data" class="form-label"><i class="bi bi-calendar-plus"></i> Nova Data:</label>
                                        </div>
                                        <div class="col-md-4">
                                            <input type="date" class="form-control" name="nova_data" 
                                                   value="<?= date('Y-m-d', strtotime($reserva['data_reserva'])) ?>" 
                                                   min="<?= date('Y-m-d') ?>" required>
                                        </div>
                                        <div class="col-md-4">
                                            <button type="submit" name="alterar_data" class="btn btn-primary me-2">
                                                <i class="bi bi-check-circle"></i> Confirmar
                                            </button>
                                            <button type="button" class="btn btn-secondary btn-cancelar">
                                                <i class="bi bi-x-circle"></i> Cancelar
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Nenhuma reserva confirmada no momento.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
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