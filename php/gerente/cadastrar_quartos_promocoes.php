<?php
include "../conex.php";

// Variável para mensagens de feedback
$feedback = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Busca de quartos
    if (isset($_POST['search'])) {
        $search = trim($_POST['search']);
        $sql = "SELECT * FROM quartos WHERE nome LIKE ? OR num_quarto = ?";
        $stmt = $conn->prepare($sql);
        
        $search_like = "%$search%";
        $stmt->bind_param("si", $search_like, $search);
        
        if ($stmt->execute()) {
            $result = $stmt->get_result();
        } else {
            $feedback = '<div class="alert alert-danger">Erro ao buscar quartos: ' . $stmt->error . '</div>';
        }
    }
    
    // Aplicar/Atualizar promoção
    if (isset($_POST['update'])) {
        $id_quarto = (int)$_POST['id_quarto'];
        $preco_promocional = (float)$_POST['preco_promocional'];
        $data_inicio = $_POST['data_inicio'];
        $data_fim = $_POST['data_fim'];
        
        // Verificar se já existe promoção para este quarto
        $check_sql = "SELECT id FROM promocoes_quartos WHERE id_quarto = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $id_quarto);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            // Atualizar promoção existente
            $update_sql = "UPDATE promocoes_quartos SET 
                          preco_promocional = ?, 
                          data_inicio = ?, 
                          data_fim = ?,
                          data_atualizacao = NOW()
                          WHERE id_quarto = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("dssi", $preco_promocional, $data_inicio, $data_fim, $id_quarto);
        } else {
            // Inserir nova promoção
            $insert_sql = "INSERT INTO promocoes_quartos 
                          (id_quarto, preco_promocional, data_inicio, data_fim, data_criacao, disponivel) 
                          VALUES (?, ?, ?, ?, NOW(), TRUE)";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param("idss", $id_quarto, $preco_promocional, $data_inicio, $data_fim);
        }
        
        if ($stmt->execute()) {
            $feedback = '<div class="alert alert-success">Promoção aplicada com sucesso!</div>';
        } else {
            $feedback = '<div class="alert alert-danger">Erro ao aplicar promoção: ' . $stmt->error . '</div>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Promoções de Quartos</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(180deg, rgba(107, 57, 42, 0.946) 6%, rgba(133,78,57,1) 18%, rgb(203, 164, 122) 60%, rgba(217, 192, 164, 0.339) 90%, rgba(255,255,255,1) 110%);
            background-attachment: fixed;
            min-height: 100vh;
        }
        
        .container {
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            padding: 2rem;
            margin-top: 2rem;
            margin-bottom: 2rem;
        }
        
        .card-quarto {
            transition: transform 0.3s;
            border: none;
            border-radius: 10px;
            overflow: hidden;
            background-color: rgba(255, 255, 255, 0.95);
        }
        
        .card-quarto:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }
        
        .card-header {
            background: linear-gradient(135deg, rgba(133,78,57,1) 0%, rgba(107, 57, 42, 0.946) 100%);
            border-bottom: none;
        }
        
        .preco-original {
            text-decoration: line-through;
            color: #6c757d;
        }
        
        .preco-promocao {
            color: #c44512;
            font-weight: bold;
        }
        
        .btn-primary {
            background-color: #6b392a;
            border-color: #5a3022;
        }
        
        .btn-primary:hover {
            background-color: #5a3022;
            border-color: #4a281b;
        }
        
        .btn-success {
            background-color: #8a5a44;
            border-color: #7a4a34;
        }
        
        .btn-success:hover {
            background-color: #7a4a34;
            border-color: #6a3a24;
        }
        
        .form-control:focus {
            border-color: #c44512;
            box-shadow: 0 0 0 0.25rem rgba(194, 87, 44, 0.25);
        }
        
        .input-group-text {
            background-color: #e9d8c8;
            color: #6b392a;
        }
        
        h1 {
            color: #fff;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .alert {
            border-radius: 8px;
        }
        
        .badge {
            background-color: #e9d8c8 !important;
            color: #6b392a !important;
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
    <div class="container py-5">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                <a href="../../html/gerente/tela_gerente.html" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left"></i> Voltar
                </a>
                    <h1 class="text-center mb-0">
                        <i class="bi bi-percent"></i> Gerenciar Promoções de Quartos
                    </h1>
                    <div style="width: 100px;"></div> <!-- Espaço vazio para alinhamento -->
                </div>
            </div>
        </div>

        <!-- Feedback messages -->
        <?php if (!empty($feedback)) echo $feedback; ?>

        <!-- Search Form -->
        <div class="row mb-4">
            <div class="col-md-8 mx-auto">
                <form method="post" class="card shadow-sm" style="background-color: rgba(255, 255, 255, 0.95);">
                    <div class="card-body">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control form-control-lg" 
                                   placeholder="Buscar por nome ou número do quarto..." required>
                            <button class="btn btn-primary" type="submit">
                                <i class="bi bi-search"></i> Buscar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Results Section -->
        <?php if (isset($result)): ?>
            <div class="row">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($quarto = $result->fetch_assoc()): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card card-quarto h-100">
                                <div class="card-header text-white">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-door-open"></i> <?= htmlspecialchars($quarto['nome']) ?>
                                        <span class="badge float-end">
                                            #<?= $quarto['num_quarto'] ?>
                                        </span>
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form method="post">
                                        <input type="hidden" name="id_quarto" value="<?= $quarto['id_quarto'] ?>">
                                        
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label class="form-label">Tipo:</label>
                                                <input type="text" class="form-control" value="<?= htmlspecialchars($quarto['tipo']) ?>" readonly>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">Andar:</label>
                                                <input type="text" class="form-control" value="<?= $quarto['andar'] ?>" readonly>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Preço Diária Original:</label>
                                            <div class="input-group">
                                                <span class="input-group-text">R$</span>
                                                <input type="text" class="form-control preco-original" 
                                                       value="<?= number_format($quarto['preco_diaria'], 2, ',', '.') ?>" readonly>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="preco_promocional" class="form-label">Preço Promocional:</label>
                                            <div class="input-group">
                                                <span class="input-group-text">R$</span>
                                                <input type="number" step="0.01" class="form-control preco-promocao" 
                                                       id="preco_promocional" name="preco_promocional" 
                                                       value="<?= number_format($quarto['preco_diaria'] * 0.9, 2, '.', '') ?>" 
                                                       min="0" required>
                                            </div>
                                            <small class="text-muted">Sugerimos 10% de desconto (altere se necessário)</small>
                                        </div>
                                        
                                        <div class="row mb-3">
                                            <div class="col-md-6">
                                                <label for="data_inicio" class="form-label">Data Início:</label>
                                                <input type="date" class="form-control" id="data_inicio" 
                                                       name="data_inicio" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="data_fim" class="form-label">Data Fim:</label>
                                                <input type="date" class="form-control" id="data_fim" 
                                                       name="data_fim" required>
                                            </div>
                                        </div>
                                        
                                        <div class="d-grid">
                                            <button type="submit" name="update" class="btn btn-success">
                                                <i class="bi bi-check-circle"></i> Aplicar Promoção
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-warning text-center">
                            <i class="bi bi-exclamation-triangle"></i> Nenhum quarto encontrado com os critérios de busca.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Set default dates (today + 7 days)
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date();
            const nextWeek = new Date();
            nextWeek.setDate(today.getDate() + 7);
            
            document.getElementById('data_inicio').valueAsDate = today;
            document.getElementById('data_fim').valueAsDate = nextWeek;
        });
    </script>
</body>
</html>