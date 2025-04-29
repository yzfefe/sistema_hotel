<?php
include "../conex.php";

$feedback = '';
$hospede = null;
$reservas = [];
$total_gasto = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Buscar hóspede
    if (isset($_POST['buscar_hospede'])) {
        $termo = trim($_POST['termo_busca']);
        
        $sql = "SELECT * FROM hospede WHERE nome LIKE ? OR cpf = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $termo_like = "%$termo%";
        $stmt->bind_param("sss", $termo_like, $termo, $termo);
        
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $hospede = $result->fetch_assoc();
                
                // Buscar reservas do hóspede
                $sql_reservas = "SELECT r.*, q.nome as nome_quarto, q.tipo, q.preco_diaria 
                                FROM reservas r
                                JOIN quartos q ON r.id_quarto = q.id_quarto
                                WHERE r.id_hos = ? AND r.status_atual = 'CONFIRMADA'";
                $stmt_res = $conn->prepare($sql_reservas);
                $stmt_res->bind_param("i", $hospede['id_hos']);
                $stmt_res->execute();
                $reservas_result = $stmt_res->get_result();
                
                while ($reserva = $reservas_result->fetch_assoc()) {
                    $reservas[] = $reserva;
                }
            } else {
                $feedback = '<div class="alert alert-warning">Nenhum hóspede encontrado</div>';
            }
        } else {
            $feedback = '<div class="alert alert-danger">Erro na busca: ' . $stmt->error . '</div>';
        }
    }
    
    // Gerar conta
    if (isset($_POST['gerar_conta'])) {
        $id_hos = (int)$_POST['id_hos'];
        $id_reserva = (int)$_POST['id_reserva'];
        
        // Calcular dias de hospedagem
        $sql_reserva = "SELECT data_reserva, id_quarto FROM reservas WHERE id_reserva = ?";
        $stmt = $conn->prepare($sql_reserva);
        $stmt->bind_param("i", $id_reserva);
        $stmt->execute();
        $reserva = $stmt->get_result()->fetch_assoc();
        
        $data_inicio = new DateTime($reserva['data_reserva']);
        $data_fim = new DateTime(); // Data atual
        $dias = $data_fim->diff($data_inicio)->days;
        $dias = max(1, $dias); // Mínimo 1 dia
        
        // Obter preço da diária
        $sql_quarto = "SELECT preco_diaria FROM quartos WHERE id_quarto = ?";
        $stmt = $conn->prepare($sql_quarto);
        $stmt->bind_param("i", $reserva['id_quarto']);
        $stmt->execute();
        $preco_diaria = $stmt->get_result()->fetch_assoc()['preco_diaria'];
        
        $total_conta = $dias * $preco_diaria;
        
        // Atualizar gastos do hóspede
        $sql_update = "UPDATE hospede SET 
                      gastos_atuais = gastos_atuais + ?,
                      gastos_totais = gastos_totais + ?
                      WHERE id_hos = ?";
        $stmt = $conn->prepare($sql_update);
        $stmt->bind_param("ddi", $total_conta, $total_conta, $id_hos);
        $stmt->execute();
        
        // Encerrar reserva
        $sql_encerrar = "UPDATE reservas SET 
                         data_encerrada = CURDATE(),
                         status_atual = 'Encerrada'
                         WHERE id_reserva = ?";
        $stmt = $conn->prepare($sql_encerrar);
        $stmt->bind_param("i", $id_reserva);
        $stmt->execute();
        
        $feedback = '<div class="alert alert-success">Conta gerada com sucesso! Total: R$ ' . 
                   number_format($total_conta, 2, ',', '.') . '</div>';
        
        // Recarregar dados do hóspede
        $sql_hospede = "SELECT * FROM hospede WHERE id_hos = ?";
        $stmt = $conn->prepare($sql_hospede);
        $stmt->bind_param("i", $id_hos);
        $stmt->execute();
        $hospede = $stmt->get_result()->fetch_assoc();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Contas - Hotel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .card-hospede {
            border-left: 5px solid #0d6efd;
        }
        .card-reserva {
            transition: transform 0.3s;
        }
        .card-reserva:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .badge-ativa {
            background-color: #198754;
        }
        .badge-encerrada {
            background-color: #6c757d;
        }
        .total-box {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="text-center">
                    <i class="bi bi-cash-stack"></i> Sistema de Contas
                </h1>
                <p class="text-center text-muted">Gerenciamento de contas de hóspedes</p>
            </div>
        </div>

        <?php if (!empty($feedback)) echo $feedback; ?>

        <!-- Busca de Hóspede -->
        <div class="row mb-4">
            <div class="col-md-8 mx-auto">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-search"></i> Buscar Hóspede</h5>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="input-group">
                                <input type="text" name="termo_busca" class="form-control" 
                                       placeholder="Nome, CPF ou E-mail" required>
                                <button type="submit" name="buscar_hospede" class="btn btn-primary">
                                    <i class="bi bi-search"></i> Buscar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dados do Hóspede -->
        <?php if ($hospede): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card card-hospede shadow-sm">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="bi bi-person-circle"></i> <?= htmlspecialchars($hospede['nome']) ?>
                                <span class="float-end">
                                    <span class="badge bg-info">ID: <?= $hospede['id_hos'] ?></span>
                                </span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><i class="bi bi-envelope"></i> <strong>E-mail:</strong> <?= htmlspecialchars($hospede['email']) ?></p>
                                    <p><i class="bi bi-credit-card"></i> <strong>CPF:</strong> <?= htmlspecialchars($hospede['cpf']) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><i class="bi bi-telephone"></i> <strong>Telefone:</strong> <?= htmlspecialchars($hospede['telefone']) ?></p>
                                    <p><i class="bi bi-wallet2"></i> <strong>Gastos Totais:</strong> R$ <?= number_format($hospede['gastos_totais'], 2, ',', '.') ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reservas Ativas -->
            <?php if (!empty($reservas)): ?>
                <div class="row">
                    <div class="col-12">
                        <h4 class="mb-3"><i class="bi bi-calendar-check"></i> Reservas Ativas</h4>
                        
                        <?php foreach ($reservas as $reserva): ?>
                            <div class="card card-reserva mb-3">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0">
                                        <i class="bi bi-door-closed"></i> <?= htmlspecialchars($reserva['nome_quarto']) ?>
                                        <span class="badge bg-secondary">#<?= $reserva['num_quarto'] ?></span>
                                    </h5>
                                    <span class="badge badge-ativa">Ativa</span>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <p><strong>Tipo:</strong> <?= htmlspecialchars($reserva['tipo']) ?></p>
                                            <p><strong>Diária:</strong> R$ <?= number_format($reserva['preco_diaria'], 2, ',', '.') ?></p>
                                        </div>
                                        <div class="col-md-4">
                                            <p><strong>Check-in:</strong> <?= date('d/m/Y', strtotime($reserva['data_reserva'])) ?></p>
                                            <p><strong>Dias hospedado:</strong> <?= date_diff(new DateTime($reserva['data_reserva']), new DateTime())->days ?></p>
                                        </div>
                                        <div class="col-md-4">
                                            <form method="post">
                                                <input type="hidden" name="id_hos" value="<?= $hospede['id_hos'] ?>">
                                                <input type="hidden" name="id_reserva" value="<?= $reserva['id_reserva'] ?>">
                                                <button type="submit" name="gerar_conta" class="btn btn-danger float-end">
                                                    <i class="bi bi-receipt"></i> Gerar Conta
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Nenhuma reserva ativa encontrada para este hóspede.
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>