<?php
require_once '../check_session.php';

if ($_SESSION['role'] != 'gerente') {
    header("Location: ../../html/login.html");
    exit();
}
include "../conex.php";

// Variável para mensagens de feedback
$feedback = '';
$result = null; // Inicializa a variável $result

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Se a busca foi acionada
    if (isset($_POST['search'])) {
        $search = trim($_POST['search']);
        
        // Verifica se o campo de pesquisa está vazio
        if (empty($search)) {
            // Limpa os resultados anteriores
            $result = null;
            $feedback = '<div class="alert alert-info">Digite algo para pesquisar.</div>';
        } else {
            $sql = "SELECT * FROM servicos WHERE nome LIKE ? OR id_serv LIKE ?";
            $stmt = $conn->prepare($sql);

            $search_like = "%$search%";
            $stmt->bind_param("ss", $search_like, $search_like);

            if ($stmt->execute()) {
                $result = $stmt->get_result();
                if ($result->num_rows == 0) {
                    $feedback = '<div class="alert alert-warning">Nenhum serviço encontrado.</div>';
                }
            } else {
                $feedback = '<div class="alert alert-danger">Erro ao buscar serviços: ' . $stmt->error . '</div>';
            }
        }
    }
    
    // Se a atualização foi acionada
    if (isset($_POST['update'])) {
        $nome = $_POST['nome'];
        $horario_comeca = $_POST['horario_comeca'];
        $horario_termina = $_POST['horario_termina'];
        $preco_promocional = (float)$_POST['preco_promocional'];

        $sql1 = $conn->prepare("INSERT INTO promocoes_servicos (nome, horario_comeca, horario_termina, preco_promocional, disponivel) VALUES (?, ?, ?, ?, TRUE)");
        $sql1->bind_param("sssd", $nome, $horario_comeca, $horario_termina, $preco_promocional);

        if ($sql1->execute()) {
            $feedback = '<div class="alert alert-success">Promoção inserida com sucesso</div>';
        } else {
            $feedback = '<div class="alert alert-danger">Erro ao inserir Promoção: ' . $sql1->error . '</div>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Gerenciar Promoções de Serviços</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="shortcut icon" type="imagex/png" href="../../img/aba.png">
    <style>
        body {
            background: linear-gradient(180deg, rgba(107, 57, 42, 0.946) 6%, rgba(133,78,57,1) 18%, rgb(203, 164, 122) 60%, rgba(217, 192, 164, 0.339) 90%, rgba(255,255,255,1) 110%);
            background-attachment: fixed;
            min-height: 100vh;
        }
        
        .container {
            max-width: 800px;
        }
        
        .card {
            border-radius: 15px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
            border: none;
        }
        
        .card-header {
            background: linear-gradient(135deg, rgba(133,78,57,1) 0%, rgba(107, 57, 42, 0.946) 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
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
        
        .logo {
            max-height: 100px;
            filter: drop-shadow(2px 2px 4px rgba(0,0,0,0.2));
        }
        
        .form-control:focus {
            border-color: #c44512;
            box-shadow: 0 0 0 0.25rem rgba(194, 87, 44, 0.25);
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="text-center mb-4">
        <img src="../../img/logo_hoteel.png" alt="Hotel Logo" class="logo mb-3">
        <h1 class="text-white"><i class="bi bi-percent"></i> Gerenciar Promoções de Serviços</h1>
    </div>

    <?php if (!empty($feedback)) echo $feedback; ?>

    <div class="card mb-4">
        <div class="card-header">
            <h2 class="mb-0"><i class="bi bi-search"></i> Buscar Serviço</h2>
        </div>
        <div class="card-body">
            <form method="post" class="row g-3">
                <div class="col-md-9">
                    <input type="text" name="search" class="form-control form-control-lg" 
                           placeholder="Digite parte do nome ou ID do serviço" value="<?= isset($_POST['search']) ? htmlspecialchars($_POST['search']) : '' ?>">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php if (isset($result) && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="mb-0"><i class="bi bi-tag"></i> Criar Promoção</h3>
                </div>
                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="id_item" value="<?= $row['id_serv']; ?>">

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="nome" class="form-label">Nome do Serviço</label>
                                <input type="text" name="nome" id="nome" 
                                       value="<?= htmlspecialchars($row['nome']); ?>" 
                                       class="form-control" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="horario_comeca" class="form-label">Horário Inicial</label>
                                <input type="time" name="horario_comeca" id="horario_comeca" 
                                       value="<?= $row['horario_comeca']; ?>" 
                                       class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label for="horario_termina" class="form-label">Horário Final</label>
                                <input type="time" name="horario_termina" id="horario_termina" 
                                       value="<?= $row['horario_termina']; ?>" 
                                       class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="preco_promocional" class="form-label">Preço Promocional</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="number" step="0.01" name="preco_promocional" id="preco_promocional" 
                                       value="<?= $row['preco']; ?>" 
                                       class="form-control" min="0" required>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" name="update" class="btn btn-success btn-lg">
                                <i class="bi bi-check-circle"></i> Criar Promoção
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>

    <div class="text-center">
        <a href="http://localhost/sistema_hotel-main/html/gerente/tela_gerente.php" 
           class="btn btn-outline-light">
            <i class="bi bi-arrow-left"></i> Voltar ao Painel
        </a>
    </div>
</div>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>