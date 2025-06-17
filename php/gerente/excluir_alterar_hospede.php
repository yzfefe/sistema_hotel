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
        $stmt = $conn->prepare("SELECT * FROM hospede WHERE cpf = ? OR nome LIKE ?");
        $search_param = "%$search%";
        $stmt->bind_param("ss", $search, $search_param);
        $stmt->execute();
        $result = $stmt->get_result();
    }

    if (isset($_POST['update'])) {
        $id_hos = $_POST['id_hos'];
        $nome = $_POST['nome'];
        $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf']);
        $email = $_POST['email'];
        $rg = $_POST['rg'];
        $telefone = preg_replace('/[^0-9]/', '', $_POST['telefone']);
        $endereco = $_POST['endereco'];

        if(strlen($cpf) != 11){
            $msg = "Erro: O CPF deve conter 11 dígitos numéricos";
        } elseif(strlen($telefone) < 10 || strlen($telefone) > 11) {
            $msg = "Erro: O telefone deve ter 10 ou 11 dígitos (DDD + número)";
        } else {
            $stmt = $conn->prepare("UPDATE hospede SET nome=?, cpf=?, email=?, rg=?, telefone=?, endereco=? WHERE id_hos=?");
            $stmt->bind_param("ssssssi", $nome, $cpf, $email, $rg, $telefone, $endereco, $id_hos);

            if($stmt->execute()) {
                $msg = "Sucesso: Dados do hóspede atualizados com sucesso!";
            } else {
                $msg = "Erro: " . $stmt->error;
            }
        }
    }

    if (isset($_POST['deactivate'])) {
        $id_hos = $_POST['id_hos'];
        $stmt = $conn->prepare("UPDATE hospede SET status_atual = 'INATIVO' WHERE id_hos=?");
        $stmt->bind_param("i", $id_hos);

        if($stmt->execute()) {
            $msg = "Sucesso: Hóspede desativado com sucesso!";
        } else {
            $msg = "Erro: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Hóspedes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="shortcut icon" type="imagex/png" href="../../img/aba.png">
    <style>
        body {
            background: linear-gradient(180deg, rgba(107, 57, 42, 0.946) 6%, rgba(133,78,57,1) 18%, rgb(203, 164, 122) 60%, rgba(217, 192, 164, 0.339) 90%, rgba(255,255,255,1) 110%);
            background-attachment: fixed;
            min-height: 100vh;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 30px;
            margin-top: 2rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(203, 164, 122, 0.3);
            position: relative;
        }

        .logo {
            width: 20rem;
            display: block;
            margin: 0 auto 2rem auto;
            filter: drop-shadow(2px 2px 4px rgba(0, 0, 0, 0.3));
        }

        h2 {
            color: #5a3022;
            text-shadow: 1px 1px 2px rgba(255, 255, 255, 0.5);
            margin-bottom: 1.5rem;
        }

        h3 {
            color: #6b392a;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid rgba(203, 164, 122, 0.5);
            padding-bottom: 0.5rem;
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

        .btn-outline-primary {
            color: #6b392a;
            border-color: #6b392a;
        }
        
        .btn-outline-primary:hover {
            background-color: #6b392a;
            color: white;
        }

        .form-control:focus {
            border-color: #c44512;
            box-shadow: 0 0 0 0.25rem rgba(194, 87, 44, 0.25);
        }

        .alert {
            border-radius: 8px;
            border: none;
        }

        .search-form {
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .form-label {
            font-weight: 500;
            color: #5a3022;
        }

        .btn {
            transition: all 0.3s;
        }

        .back-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            z-index: 100;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <a href="../../html/gerente/tela_gerente.php" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
        
        <img src="../../img/logo_hoteel.png" alt="Logo do Hotel" class="logo">
        <h2 class="text-center"><i class="bi bi-people-fill"></i> Gerenciar Hóspedes</h2>

        <?php if (!empty($msg)): ?>
            <div class="alert <?= strpos($msg, 'Sucesso') !== false ? 'alert-success' : 'alert-danger' ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="search-form">
            <h3><i class="bi bi-search"></i> Buscar Hóspede</h3>
            <form method="post" class="row g-3">
                <div class="col-md-9">
                    <input type="text" name="search" class="form-control form-control-lg" placeholder="Digite o CPF ou Nome" required>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                </div>
            </form>
        </div>

        <?php if (isset($result) && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="card mb-4 border-0 shadow">
                    <div class="card-body">
                        <h3 class="card-title"><i class="bi bi-person-lines-fill"></i> Editar Hóspede</h3>
                        <form method="post">
                            <input type="hidden" name="id_hos" value="<?= htmlspecialchars($row['id_hos']) ?>">

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="nome" class="form-label">Nome</label>
                                    <input type="text" name="nome" id="nome" class="form-control" value="<?= htmlspecialchars($row['nome']) ?>" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="cpf" class="form-label">CPF</label>
                                    <input type="text" name="cpf" id="cpf" class="form-control" value="<?= htmlspecialchars($row['cpf']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="rg" class="form-label">RG</label>
                                    <input type="text" name="rg" id="rg" class="form-control" value="<?= htmlspecialchars($row['rg']) ?>">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="email" class="form-label">E-mail</label>
                                    <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($row['email']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="telefone" class="form-label">Telefone</label>
                                    <input type="text" name="telefone" id="telefone" class="form-control" value="<?= htmlspecialchars($row['telefone']) ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="endereco" class="form-label">Endereço</label>
                                <input type="text" name="endereco" id="endereco" class="form-control" value="<?= htmlspecialchars($row['endereco']) ?>">
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="submit" name="update" class="btn btn-success me-md-2">
                                    <i class="bi bi-check-circle"></i> Atualizar
                                </button>
                                <button type="submit" name="deactivate" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja desativar este hóspede?')">
                                    <i class="bi bi-person-x"></i> Desativar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php elseif (isset($result)): ?>
            <div class="alert alert-warning text-center mt-3">
                <i class="bi bi-exclamation-triangle"></i> Nenhum hóspede encontrado.
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
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
    </script>
</body>
</html>