<?php
include "../conex.php";
$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['search'])) {
        $search = $_POST['search'];
        // Alteração na consulta SQL para permitir busca por nome ou cpf
        $stmt = $conn->prepare("SELECT * FROM hospede WHERE cpf = ? OR nome LIKE ?");
        $search_param = "%$search%"; // Usar LIKE para permitir pesquisa por nome
        $stmt->bind_param("ss", $search, $search_param);
        $stmt->execute();
        $result = $stmt->get_result();
    }
    
    if (isset($_POST['update'])) {
        $id_hos = $_POST['id_hos'];
        $nome = $_POST['nome'];
        $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf']); // Remove formatação
        $email = $_POST['email'];
        $rg = $_POST['rg'];
        $telefone = preg_replace('/[^0-9]/', '', $_POST['telefone']); // Remove formatação
        $endereco = $_POST['endereco'];
        
        if(strlen($cpf) != 11){ // Verifica os 11 dígitos reais
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
        // Alteração para desativar o hóspede (status_atual = 'INATIVO')
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
    <!-- Link do Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .msg-erro { color: red; }
        .msg-sucesso { color: green; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h2 class="text-center mb-4">Gerenciar Hóspedes</h2>
        
        <!-- Área para exibir mensagens -->
        <?php if (!empty($msg)): ?>
            <div class="alert <?= strpos($msg, 'Sucesso') !== false ? 'alert-success' : 'alert-danger' ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($msg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Formulário de Busca -->
        <h3>Buscar Hóspede</h3>
        <form method="post" class="d-flex mb-4">
            <input type="text" name="search" class="form-control" placeholder="Digite o CPF ou Nome" required>
            <button type="submit" class="btn btn-primary ms-2">Buscar</button>
        </form>

        <?php if (isset($result) && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <form method="post">
                    <input type="hidden" name="id_hos" value="<?= htmlspecialchars($row['id_hos']) ?>">

                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome</label>
                        <input type="text" name="nome" id="nome" class="form-control" value="<?= htmlspecialchars($row['nome']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="cpf" class="form-label">CPF</label>
                        <input type="text" name="cpf" id="cpf" class="form-control" value="<?= htmlspecialchars($row['cpf']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($row['email']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="rg" class="form-label">RG</label>
                        <input type="text" name="rg" id="rg" class="form-control" value="<?= htmlspecialchars($row['rg']) ?>">
                    </div>

                    <div class="mb-3">
                        <label for="telefone" class="form-label">Telefone</label>
                        <input type="text" name="telefone" id="telefone" class="form-control" value="<?= htmlspecialchars($row['telefone']) ?>">
                    </div>

                    <div class="mb-3">
                        <label for="endereco" class="form-label">Endereço</label>
                        <input type="text" name="endereco" id="endereco" class="form-control" value="<?= htmlspecialchars($row['endereco']) ?>">
                    </div>

                    <button type="submit" name="update" class="btn btn-success me-2">Atualizar</button>
                    <button type="submit" name="deactivate" class="btn btn-danger">Desativar</button>
                </form>
            <?php endwhile; ?>
        <?php elseif (isset($result)): ?>
            <div class="alert alert-warning mt-3">Nenhum hóspede encontrado.</div>
        <?php endif; ?>
    </div>

    <!-- Scripts do Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

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
    </script>
</body>
</html>
