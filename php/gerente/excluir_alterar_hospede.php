<?php
include "../conex.php";
$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['search'])) {
        $search = $_POST['search'];
        $stmt = $conn->prepare("SELECT * FROM hospede WHERE cpf = ?");
        $stmt->bind_param("s", $search);
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
    
    if (isset($_POST['delete'])) {
        $id_hos = $_POST['id_hos'];
        $stmt = $conn->prepare("DELETE FROM hospede WHERE id_hos=?");
        $stmt->bind_param("i", $id_hos);
        
        if($stmt->execute()) {
            $msg = "Sucesso: Hóspede excluído com sucesso!";
        } else {
            $msg = "Erro: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gerenciar Hóspedes</title>
    <style>
        .msg-erro { color: red; }
        .msg-sucesso { color: green; }
    </style>
</head>
<body>
    <h2>Gerenciar Hóspedes</h2>
    
    <!-- Área para exibir mensagens -->
    <?php if (!empty($msg)): ?>
        <div class="<?= strpos($msg, 'Sucesso') !== false ? 'msg-sucesso' : 'msg-erro' ?>">
            <?= htmlspecialchars($msg) ?>
        </div>
    <?php endif; ?>

    <h3>Buscar Hóspede</h3>
    <form method="post">
        <input type="text" name="search" placeholder="Digite o CPF ou Nome">
        <button type="submit">Buscar</button>
    </form>

    <?php if (isset($result) && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <form method="post">
                <input type="hidden" name="id_hos" value="<?= htmlspecialchars($row['id_hos']) ?>">

                <label>Nome:</label>
                <input type="text" name="nome" value="<?= htmlspecialchars($row['nome']) ?>" required>

                <label>CPF:</label>
                <input type="text" name="cpf" id="cpf" value="<?= htmlspecialchars($row['cpf']) ?>" required>

                <label>E-mail:</label>
                <input type="email" name="email" value="<?= htmlspecialchars($row['email']) ?>" required>

                <label>RG:</label>
                <input type="text" name="rg" value="<?= htmlspecialchars($row['rg']) ?>">

                <label>Telefone:</label>
                <input type="text" name="telefone" id="telefone" value="<?= htmlspecialchars($row['telefone']) ?>">

                <label>Endereço:</label>
                <input type="text" name="endereco" value="<?= htmlspecialchars($row['endereco']) ?>">

                <button type="submit" name="update">Atualizar</button>
                <button type="submit" name="delete">Excluir</button>
            </form>
        <?php endwhile; ?>
    <?php elseif (isset($result)): ?>
        <p>Nenhum hóspede encontrado.</p>
    <?php endif; ?>

    <script>
        // Formatação do CPF (mantém 11 dígitos internamente)
        document.getElementById("cpf").addEventListener("input", function() {
            let cpf = this.value.replace(/\D/g, "");
            if (cpf.length > 11) cpf = cpf.slice(0, 11);
            
            // Formata visualmente
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
            
            // Formata visualmente
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
