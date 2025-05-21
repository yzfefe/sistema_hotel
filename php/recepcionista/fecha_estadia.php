<?php
include "../conex.php";

$feedback = '';
$hospede = null;
$reservas = [];
$total_gasto = 0;
$conta_gerada = null; // Adicionado para armazenar os dados da conta gerada

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
                                FROM reservas_quarto r
                                JOIN quartos q ON r.id_quarto = q.id_quarto
                                WHERE r.id_hos = ? AND r.status = 'CONFIRMADA'";
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
    
    // conta
    if (isset($_POST['gerar_conta'])) {
        $id_hos = (int)$_POST['id_hos'];
        $id_reserva = (int)$_POST['id_reserva'];
        
        // Calcular dias de hospedagem
        $sql_reserva = "SELECT data_reserva, id_quarto FROM reservas_quarto WHERE id_reserva = ?";
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
        
        // Armazenar dados da conta para exibição
        $conta_gerada = [
            'id_hos' => $id_hos,
            'id_reserva' => $id_reserva,
            'dias' => $dias,
            'preco_diaria' => $preco_diaria,
            'total' => $total_conta,
            'data_inicio' => $reserva['data_reserva'],
            'data_fim' => date('Y-m-d')
        ];
    }
    
    // Processar pagamento (novo)
    if (isset($_POST['processar_pagamento'])) {
        $id_hos = (int)$_POST['id_hos'];
        $id_reserva = (int)$_POST['id_reserva'];
        $total_conta = (float)$_POST['total_conta'];
        
        // Atualizar gastos do hóspede
        $sql_update = "UPDATE hospede SET 
                      gastos_atuais = gastos_atuais + ?,
                      gastos_totais = gastos_totais + ?
                      WHERE id_hos = ?";
        $stmt = $conn->prepare($sql_update);
        $stmt->bind_param("ddi", $total_conta, $total_conta, $id_hos);
        $stmt->execute();

        $sql_gastos_atuais = "UPDATE hospede SET gastos_atuais = 0.00 WHERE id_hos = ?";
        $stmt = $conn->prepare($sql_gastos_atuais);
        $stmt->bind_param("i", $id_hos);
        $stmt->execute();
        
        // Encerrar reserva
        $sql_encerrar = "UPDATE reservas_quarto SET 
                         data_encerrada = CURDATE(),
                         status = 'ENCERRADA'
                         WHERE id_reserva = ?";
        $stmt = $conn->prepare($sql_encerrar);
        $stmt->bind_param("i", $id_reserva);
        $stmt->execute();

        $sql_quarto_update = "UPDATE quartos SET disponivel = TRUE WHERE id_quarto = ?";
        $stmt = $conn->prepare($sql_quarto_update);
        $stmt->bind_param("i", $_POST['id_quarto']);
        $stmt->execute();
        
        $feedback = '<div class="alert alert-success">Pagamento processado com sucesso! Total: R$ ' . 
                   number_format($total_conta, 2, ',', '.') . '</div>';
        
        // Limpar dados da conta gerada após pagamento
        $conta_gerada = null;
        
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
        :root {
            --color-primary: #c49a6c;
            --color-secondary: #6b392a;
            --color-light: #f5e7d8;
            --color-dark: #3a2618;
        }
        
        body {
            background: linear-gradient(180deg, rgba(107, 57, 42, 0.946) 6%, rgba(133,78,57,1) 18%, rgb(203, 164, 122) 60%, rgba(217, 192, 164, 0.339) 90%, rgba(255,255,255,1) 110%);
            font-family: 'Playfair Display', serif;
            min-height: 100vh;
        }
        
        .logo {
            width: 20rem;
            display: block;
            margin: 0 auto 2rem;
            filter: drop-shadow(2px 2px 4px rgba(0,0,0,0.3));
        }
        
        .container-main {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-top: 30px;
            margin-bottom: 30px;
        }
        
        .header-title {
            color: var(--color-secondary);
            font-weight: 700;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
            border-bottom: 2px solid var(--color-primary);
            padding-bottom: 10px;
            margin-bottom: 25px;
            text-align: center;
        }
        
        .card-hospede {
            border-left: 5px solid var(--color-primary);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .card-reserva {
            transition: all 0.3s ease;
            border-radius: 10px;
            overflow: hidden;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }
        
        .card-reserva:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        
        .card-header {
            background-color: var(--color-secondary);
            color: white;
            font-weight: 600;
        }
        
        .badge-ativa {
            background-color: #198754;
        }
        
        .badge-encerrada {
            background-color: #6c757d;
        }
        
        .btn-primary {
            background-color: var(--color-primary);
            border-color: var(--color-primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--color-secondary);
            border-color: var(--color-secondary);
        }
        
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        
        .btn-danger:hover {
            background-color: #bb2d3b;
            border-color: #bb2d3b;
        }
        
        .payment-method {
            cursor: pointer;
            transition: all 0.3s;
            border-radius: 8px;
            border: 2px solid transparent;
            padding: 15px;
            text-align: center;
        }
        
        .payment-method:hover {
            transform: scale(1.03);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-color: var(--color-primary);
        }
        
        .payment-method.selected {
            border: 2px solid var(--color-primary);
            background-color: rgba(196, 154, 108, 0.1);
        }
        
        .payment-method i {
            font-size: 2rem;
            color: var(--color-secondary);
            margin-bottom: 10px;
        }
        
        .modal-content {
            border-radius: 15px;
            overflow: hidden;
        }
        
        .modal-header {
            background-color: var(--color-secondary);
            color: white;
        }
        
        .list-group-item {
            border-left: none;
            border-right: none;
        }
        
        .list-group-item:last-child {
            border-bottom: none;
            font-weight: 600;
            color: var(--color-secondary);
        }
        
        .text-primary {
            color: var(--color-primary) !important;
        }
        
        .text-secondary {
            color: var(--color-secondary) !important;
        }
        .btn-outline-primary {
            border-color: var(--color-secondary);
            color: var(--color-secondary);
        }

        .btn-outline-primary:hover {
            background-color: var(--color-secondary);
            color: white;
        }
    </style>
</head>
<body>
    
    <img src="../../img/logo_hoteel.png" alt="Hotel Logo" class="logo">

    <div class="container container-main">
    <div class="d-flex justify-content-start mb-4">
        <a href="../../html/recepcionista/tela_recep.html" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i> Voltar
         </a>
    </div>
        <h1 class="header-title">
            <i class="bi bi-cash-stack"></i> Sistema de Contas
        </h1>
        <p class="text-center text-muted mb-4">Gerenciamento de contas de hóspedes</p>

        <?php if (!empty($feedback)) echo $feedback; ?>

        <!-- Busca de Hóspede -->
        <div class="row mb-4">
            <div class="col-md-8 mx-auto">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0 text-white"><i class="bi bi-search"></i> Buscar Hóspede</h5>
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
                    <div class="card card-hospede">
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
                                    <p><i class="bi bi-envelope text-primary"></i> <strong>E-mail:</strong> <?= htmlspecialchars($hospede['email']) ?></p>
                                    <p><i class="bi bi-credit-card text-primary"></i> <strong>CPF:</strong> <?= htmlspecialchars($hospede['cpf']) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><i class="bi bi-telephone text-primary"></i> <strong>Telefone:</strong> <?= htmlspecialchars($hospede['telefone']) ?></p>
                                    <p><i class="bi bi-wallet2 text-primary"></i> <strong>Gastos Totais:</strong> R$ <?= number_format($hospede['gastos_totais'], 2, ',', '.') ?></p>
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
                        <h4 class="mb-3 text-secondary"><i class="bi bi-calendar-check"></i> Reservas Ativas</h4>
                        
                        <?php foreach ($reservas as $reserva): ?>
                            <div class="card card-reserva">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0 text-white">
                                        <i class="bi bi-door-closed"></i> <?= htmlspecialchars($reserva['nome_quarto']) ?>
                                    </h5>
                                    <span class="badge badge-ativa">Ativa</span>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <p><i class="bi bi-tag text-primary"></i> <strong>Tipo:</strong> <?= htmlspecialchars($reserva['tipo']) ?></p>
                                            <p><i class="bi bi-currency-dollar text-primary"></i> <strong>Diária:</strong> R$ <?= number_format($reserva['preco_diaria'], 2, ',', '.') ?></p>
                                        </div>
                                        <div class="col-md-4">
                                            <p><i class="bi bi-calendar-check text-primary"></i> <strong>Check-in:</strong> <?= date('d/m/Y', strtotime($reserva['data_reserva'])) ?></p>
                                            <p><i class="bi bi-calendar-range text-primary"></i> <strong>Dias hospedado:</strong> <?= date_diff(new DateTime($reserva['data_reserva']), new DateTime())->days ?></p>
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

    <!-- Modal de Pagamento -->
    <?php if ($conta_gerada): ?>
    <div class="modal fade show" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" style="display: block; background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-white" id="paymentModalLabel">
                        <i class="bi bi-credit-card"></i> Processar Pagamento
                    </h5>
                </div>
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h5 class="text-secondary">Detalhes da Conta</h5>
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span><i class="bi bi-calendar-range text-primary"></i> Período:</span>
                                    <span><?= date('d/m/Y', strtotime($conta_gerada['data_inicio'])) ?> a <?= date('d/m/Y', strtotime($conta_gerada['data_fim'])) ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span><i class="bi bi-calendar-day text-primary"></i> Dias de Hospedagem:</span>
                                    <span><?= $conta_gerada['dias'] ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span><i class="bi bi-cash-coin text-primary"></i> Valor Diária:</span>
                                    <span>R$ <?= number_format($conta_gerada['preco_diaria'], 2, ',', '.') ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between fw-bold">
                                    <span><i class="bi bi-receipt text-primary"></i> Total a Pagar:</span>
                                    <span>R$ <?= number_format($conta_gerada['total'], 2, ',', '.') ?></span>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5 class="text-secondary">Método de Pagamento</h5>
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="payment-method" onclick="selectPayment('credit')">
                                        <i class="bi bi-credit-card-2-front"></i>
                                        <p class="mb-0">Cartão de Crédito</p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="payment-method" onclick="selectPayment('debit')">
                                        <i class="bi bi-credit-card"></i>
                                        <p class="mb-0">Cartão de Débito</p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="payment-method" onclick="selectPayment('pix')">
                                        <i class="bi bi-qr-code"></i>
                                        <p class="mb-0">PIX</p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="payment-method" onclick="selectPayment('cash')">
                                        <i class="bi bi-cash-coin"></i>
                                        <p class="mb-0">Dinheiro</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="paymentDetails" class="mt-3" style="display: none;">
                                <h6 class="mb-3 text-secondary">Detalhes do Pagamento</h6>
                                <form id="paymentForm" method="post">
                                    <input type="hidden" name="id_hos" value="<?= $conta_gerada['id_hos'] ?>">
                                    <input type="hidden" name="id_reserva" value="<?= $conta_gerada['id_reserva'] ?>">
                                    <input type="hidden" name="total_conta" value="<?= $conta_gerada['total'] ?>">
                                    <input type="hidden" name="id_quarto" value="<?= $reserva['id_quarto'] ?>">
                                    <input type="hidden" name="payment_method" id="paymentMethod" value="">

                                    <!-- Alerta de erro -->
                                    <div id="paymentError" class="alert alert-danger d-none" role="alert">
                                        <i class="bi bi-exclamation-triangle-fill"></i> Erro no pagamento.
                                    </div>

                                    <!-- Campos de cartão -->
                                    <div id="creditFields" style="display: none;">
                                        <div class="mb-3">
                                            <label for="cardNumber" class="form-label">Número do Cartão</label>
                                            <input type="text" class="form-control" id="cardNumber" placeholder="1234 5678 9012 3456">
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="cardExpiry" class="form-label">Validade</label>
                                                <input type="text" class="form-control" id="cardExpiry" placeholder="MM/AA">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="cardCvv" class="form-label">CVV</label>
                                                <input type="text" class="form-control" id="cardCvv" placeholder="123">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="cardName" class="form-label">Nome no Cartão</label>
                                            <input type="text" class="form-control" id="cardName" placeholder="Nome como no cartão">
                                        </div>
                                    </div>
                                    <div id="cardNumberError" class="text-danger mt-1" style="display: none;">
                                        O número do cartão deve conter exatamente 16 dígitos.
                                    </div>

                                    <!-- Campos PIX -->
                                    <div id="pixFields" style="display: none;">
                                        <div class="alert alert-info">
                                            <i class="bi bi-info-circle"></i> Escaneie o QR Code abaixo para realizar o pagamento.
                                            <div class="text-center my-3">
                                                <i class="bi bi-qr-code fs-1"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Campos Dinheiro -->
                                    <div id="cashFields" style="display: none;">
                                        <div class="alert alert-warning">
                                            <i class="bi bi-exclamation-triangle"></i> O troco será fornecido no momento do pagamento.
                                        </div>
                                    </div>

                                    <button type="submit" name="processar_pagamento" class="btn btn-primary w-100 mt-3">
                                        <i class="bi bi-check-circle"></i> Confirmar Pagamento
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <form method="post">
                        <button type="submit" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Fechar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Selecionar método de pagamento
        function selectPayment(method) {
            // Remove todas as seleções
            document.querySelectorAll('.payment-method').forEach(el => {
                el.classList.remove('selected');
            });
            
            // Adiciona seleção ao método clicado
            event.currentTarget.classList.add('selected');
            
            // Mostra os detalhes do pagamento
            document.getElementById('paymentDetails').style.display = 'block';
            document.getElementById('paymentMethod').value = method;
            
            // Esconde todos os campos específicos
            document.getElementById('creditFields').style.display = 'none';
            document.getElementById('pixFields').style.display = 'none';
            document.getElementById('cashFields').style.display = 'none';
            
            // Mostra apenas os campos relevantes
            if (method === 'credit' || method === 'debit') {
                document.getElementById('creditFields').style.display = 'block';
            } else if (method === 'pix') {
                document.getElementById('pixFields').style.display = 'block';
            } else if (method === 'cash') {
                document.getElementById('cashFields').style.display = 'block';
            }
        }
    // Selecionar método de pagamento
        function selectPayment(method) {
            document.querySelectorAll('.payment-method').forEach(el => el.classList.remove('selected'));
            event.currentTarget.classList.add('selected');

            document.getElementById('paymentDetails').style.display = 'block';
            document.getElementById('paymentMethod').value = method;

            document.getElementById('creditFields').style.display = 'none';
            document.getElementById('pixFields').style.display = 'none';
            document.getElementById('cashFields').style.display = 'none';

            if (method === 'credit' || method === 'debit') {
                document.getElementById('creditFields').style.display = 'block';
            } else if (method === 'pix') {
                document.getElementById('pixFields').style.display = 'block';
            } else if (method === 'cash') {
                document.getElementById('cashFields').style.display = 'block';
            }
        }

        // Validação do formulário de pagamento
        document.getElementById('paymentForm').addEventListener('submit', function (e) {
            const method = document.getElementById('paymentMethod').value;

            if (method === 'credit' || method === 'debit') {
                const numero = document.getElementById('cardNumber').value.trim();
                const validade = document.getElementById('cardExpiry').value.trim();
                const cvv = document.getElementById('cardCvv').value.trim();
                const nome = document.getElementById('cardName').value.trim();

                const errorDiv = document.getElementById('paymentError');
                errorDiv.classList.add('d-none'); // Oculta erros anteriores

                const regexNumero = /^[0-9\s]{16,19}$/;
                const regexValidade = /^(0[1-9]|1[0-2])\/\d{2}$/;
                const regexCvv = /^\d{3,4}$/;

                if (!numero || !validade || !cvv || !nome) {
                    e.preventDefault();
                    errorDiv.textContent = "Todos os campos do cartão devem ser preenchidos.";
                    errorDiv.classList.remove('d-none');
                    return;
                }

                if (!regexNumero.test(numero)) {
                    e.preventDefault();
                    errorDiv.textContent = "Número do cartão inválido. Deve conter 16 dígitos.";
                    errorDiv.classList.remove('d-none');
                    return;
                }

                if (!regexValidade.test(validade)) {
                    e.preventDefault();
                    errorDiv.textContent = "Validade inválida. Use o formato MM/AA.";
                    errorDiv.classList.remove('d-none');
                    return;
                }

                if (!regexCvv.test(cvv)) {
                    e.preventDefault();
                    errorDiv.textContent = "CVV inválido. Deve conter 3 ou 4 dígitos.";
                    errorDiv.classList.remove('d-none');
                    return;
                }

                if (nome.length < 3) {
                    e.preventDefault();
                    errorDiv.textContent = "Nome no cartão muito curto.";
                    errorDiv.classList.remove('d-none');
                    return;
                }
            }
        });

        // Máscara para número do cartão
        document.getElementById('cardNumber').addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '').substring(0, 16);
            value = value.replace(/(.{4})/g, '$1 ').trim();
            e.target.value = value;
        });

        // Máscara para validade (MM/AA)
        document.getElementById('cardExpiry').addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '').substring(0, 4);
            if (value.length >= 3) {
                value = value.replace(/(\d{2})(\d{1,2})/, '$1/$2');
            }
            e.target.value = value;
        });

        // Apenas números no CVV
        document.getElementById('cardCvv').addEventListener('input', function (e) {
            e.target.value = e.target.value.replace(/\D/g, '').substring(0, 4);
        });
        // Máscara para número do cartão
        document.getElementById('cardNumber').addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '').substring(0, 16);
            value = value.replace(/(.{4})/g, '$1 ').trim();
            e.target.value = value;

            // Remove mensagem de erro ao digitar
            document.getElementById('cardNumberError').style.display = 'none';
        });

        // Máscara para validade (MM/AA)
        document.getElementById('cardExpiry').addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '').substring(0, 4);
            if (value.length >= 3) {
                value = value.replace(/(\d{2})(\d{1,2})/, '$1/$2');
            }
            e.target.value = value;
        });

        // Apenas números no CVV
        document.getElementById('cardCvv').addEventListener('input', function (e) {
            e.target.value = e.target.value.replace(/\D/g, '').substring(0, 4);
        });

        // Validação ao enviar o formulário
        document.getElementById('paymentForm').addEventListener('submit', function (e) {
            const method = document.getElementById('paymentMethod').value;
            if (method === 'credit' || method === 'debit') {
                const cardNumber = document.getElementById('cardNumber').value.replace(/\D/g, '');
                if (cardNumber.length !== 16) {
                    e.preventDefault();
                    document.getElementById('cardNumberError').style.display = 'block';
                    return false;
                }
            }
        });
    </script>
</body>
</html>