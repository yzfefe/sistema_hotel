<?php
require_once '../check_session.php';

if ($_SESSION['role'] != 'gerente') {
    header("Location: ../../html/login.html");
    exit();
}
include "../conex.php";
$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['search'])) {
        $search = $_POST['search'];
        $stmt = $conn->prepare("SELECT * FROM recepcionista WHERE cpf = ?");
        $stmt->bind_param("s", $search);
        $stmt->execute();
        $result = $stmt->get_result();
    }
    
    if (isset($_POST['update'])) {
        $id_recep = $_POST['id_recep'];
        $nome = $_POST['nome'];
        $cpf = $_POST['cpf'];
        $email = $_POST['email'];
        $rg = $_POST['rg'];
        $telefone = $_POST['telefone'];
        $endereco = $_POST['endereco'];
        
        if(strlen($cpf) != 14){
            $msg = "Erro: O CPF deve conter 11 dígitos numéricos";
        } elseif(strlen($telefone) !=15){
            $msg = "Erro: O telefone deve ter 10 ou 11 dígitos (DDD + número)";
        } else {
            $stmt = $conn->prepare("UPDATE recepcionista SET nome=?, cpf=?, email=?, rg=?, telefone=?, endereco=? WHERE id_recep=?");
            $stmt->bind_param("ssssssi", $nome, $cpf, $email, $rg, $telefone, $endereco, $id_recep);
            
            if($stmt->execute()) {
                $msg = "Sucesso: Dados atualizados com sucesso!";
            } else {
                $msg = "Erro: " . $stmt->error;
            }
        }
    }
    
    if (isset($_POST['delete'])) {
        $id_recep = $_POST['id_recep'];
        $stmt = $conn->prepare("DELETE FROM recepcionista WHERE id_recep=?");
        $stmt->bind_param("i", $id_recep);
        
        if($stmt->execute()) {
            $msg = "Sucesso: Recepcionista excluído com sucesso!";
        } else {
            $msg = "Erro: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Recepcionistas</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="shortcut icon" type="imagex/png" href="../../img/aba.png">
    <style>
        body {
            background: linear-gradient(180deg, rgba(107, 57, 42, 0.946) 6%, rgba(133,78,57,1) 18%, rgb(203, 164, 122) 60%, rgba(217, 192, 164, 0.339) 90%, rgba(255,255,255,1) 110%);
            background-attachment: fixed;
            min-height: 100vh;
            padding-top: 20px;
        }
        
        .container-custom {
            max-width: 800px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid rgba(203, 164, 122, 0.3);
        }
        
        .form-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(203, 164, 122, 0.3);
        }
        
        .btn-action {
            margin-right: 10px;
            transition: all 0.3s;
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
        
        .btn-danger {
            background-color: #9e4a3b;
            border-color: #8e3a2b;
        }
        
        .btn-danger:hover {
            background-color: #8e3a2b;
            border-color: #7e2a1b;
        }
        
        .form-label {
            font-weight: 500;
            color: #5a3022;
        }
        
        h2, h3 {
            color: #5a3022;
            text-shadow: 1px 1px 2px rgba(255, 255, 255, 0.5);
        }
        
        .alert {
            border-radius: 8px;
            border: none;
        }
        
        .form-control:focus {
            border-color: #c44512;
            box-shadow: 0 0 0 0.25rem rgba(194, 87, 44, 0.25);
        }
        
        .input-group-text {
            background-color: #e9d8c8;
            color: #6b392a;
            border-color: #d4c1b1;
        }
        
        .bi {
            margin-right: 5px;
        }
        
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .header-title {
            flex-grow: 1;
            text-align: center;
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
    <div class="container container-custom">
        <div class="header-container">
        <a href="../../html/gerente/tela_gerente.php" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
            <div class="header-title">
                <h2 class="mb-0"><i class="bi bi-person-badge"></i> Gerenciar Recepcionistas</h2>
            </div>
            <div style="width: 100px;"></div> <!-- Espaçamento para alinhamento -->
        </div>
        
        <!-- Área para exibir mensagens -->
        <?php if (!empty($msg)): ?>
            <div class="alert <?= strpos($msg, 'Sucesso') !== false ? 'alert-success' : 'alert-danger' ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="form-section">
            <h3 class="mb-3"><i class="bi bi-search"></i> Buscar Recepcionista</h3>
            <form method="post" class="row g-3">
                <div class="col-md-9">
                    <input type="text" name="search" class="form-control" id="searchCpf" 
                        placeholder="Digite o CPF (111.111.111-11)" maxlength="14">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                </div>
            </form>
        </div>

        <?php if (isset($result) && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="form-section">
                    <h3 class="mb-3"><i class="bi bi-person-lines-fill"></i> Editar Recepcionista</h3>
                    <form method="post" class="row g-3">
                        <input type="hidden" name="id_recep" value="<?= htmlspecialchars($row['id_recep']) ?>">

                        <div class="col-md-12">
                            <label for="nome" class="form-label">Nome Completo</label>
                            <input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars($row['nome']) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label for="cpf" class="form-label">CPF</label>
                            <input type="text" class="form-control" id="cpf" name="cpf" value="<?= htmlspecialchars($row['cpf']) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label for="rg" class="form-label">RG</label>
                            <input type="text" class="form-control" id="rg" name="rg" value="<?= htmlspecialchars($row['rg']) ?>">
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label">E-mail</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($row['email']) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label for="telefone" class="form-label">Telefone</label>
                            <input type="text" class="form-control" id="telefone" name="telefone" value="<?= htmlspecialchars($row['telefone']) ?>">
                        </div>

                        <div class="col-12">
                            <label for="endereco" class="form-label">Endereço</label>
                            <input type="text" class="form-control" id="endereco" name="endereco" value="<?= htmlspecialchars($row['endereco']) ?>">
                        </div>

                        <div class="col-12 mt-4">
                            <button type="submit" name="update" class="btn btn-success btn-action">
                                <i class="bi bi-check-circle"></i> Atualizar
                            </button>
                            <button type="submit" name="delete" class="btn btn-danger btn-action" onclick="return confirm('Tem certeza que deseja excluir este recepcionista?')">
                                <i class="bi bi-trash"></i> Excluir
                            </button>
                        </div>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php elseif (isset($result)): ?>
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i> Nenhum recepcionista encontrado.
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Formatação do CPF
        document.getElementById("cpf").addEventListener("input", function() {
            let cpf = this.value.replace(/\D/g, "");
            if (cpf.length > 11) cpf = cpf.slice(0, 11);
            
            if (cpf.length > 9) {
                this.value = cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, "$1.$2.$3-$4");
            } else if (cpf.length > 6) {
                this.value = cpf.replace(/(\d{3})(\d{3})(\d{1,3})/, "$1.$2.$3");
            } else if (cpf.length > 3) {
                this.value = cpf.replace(/(\d{3})(\d{1,3})/, "$1.$2");
            } else {
                this.value = cpf;
            }
        });

        // Formatação do telefone
        document.getElementById("telefone").addEventListener("input", function() {
            let tel = this.value.replace(/\D/g, "");
            if (tel.length > 11) tel = tel.slice(0, 11);
            
            if (tel.length > 10) {
                this.value = tel.replace(/(\d{2})(\d{5})(\d{4})/, "($1) $2-$3");
            } else if (tel.length > 6) {
                this.value = tel.replace(/(\d{2})(\d{4})(\d{0,4})/, "($1) $2-$3");
            } else if (tel.length > 2) {
                this.value = tel.replace(/(\d{2})(\d{0,5})/, "($1) $2");
            } else {
                this.value = tel;
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
    const cpfInput = document.getElementById('searchCpf');
    
    if(cpfInput) {
        cpfInput.addEventListener('input', function(e) {
            // Remove tudo que não é dígito
            let value = this.value.replace(/\D/g, '');

            // Aplica a formatação
            if(value.length > 9) {
                value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
            } else if(value.length > 6) {
                value = value.replace(/(\d{3})(\d{3})(\d{1,3})/, '$1.$2.$3');
            } else if(value.length > 3) {
                value = value.replace(/(\d{3})(\d{1,3})/, '$1.$2');
            }
            
            // Atualiza o valor do campo
            this.value = value;
        });

        
    }
});
    </script>
</body>
</html>