<?php
include "../conex.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['search'])) {
        $search = $_POST['search'];
        $sql = "SELECT * FROM recepcionista WHERE cpf = '$search'";
        $result = $conn->query($sql);
    }
    
    if (isset($_POST['update'])) {
        $id_recep= $_POST['id_recep'];
        $nome = $_POST['nome'];
        $cpf = $_POST['cpf'];
        $email = $_POST['email'];
        $rg = $_POST['rg'];
        $telefone = $_POST['telefone'];
        $endereco = $_POST['endereco'];
        if(strlen($cpf) != 14){
            $msg = "O CPF deve conter 11 dígitos";
        }elseif(strlen($telefone) != 15){
            $msg = "O número de telefone deve ter esse formato: (XX) XXXXX-XXXX";
        }else{
        $sql = "UPDATE recepcionista SET nome='$nome', cpf='$cpf', email='$email', rg = '$rg', telefone = '$telefone', endereco = '$endereco' WHERE id_recep = $id_recep";
        $conn->query($sql);
        echo "Dados atualizados com sucesso!";
        }
    }
    
    if (isset($_POST['delete'])) {
        $id_recep = $_POST['id_recep'];
        $sql = "DELETE FROM recepcionista WHERE id_recep=$id_recep";
        $conn->query($sql);
        echo "Recepcionista excluído com sucesso!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gerenciar Recepcionistas</title>
</head>
<body>
    <h2>Buscar Recepcionista</h2>
    <form method="post">
        <input type="text" name="search" placeholder="Digite o CPF ou Nome">
        <button type="submit">Buscar</button>
    </form>

    <?php if (isset($result) && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <form method="post">
                <input type="hidden" name="id_recep" value="<?php echo $row['id_recep']; ?>">

                <label for="nome">Nome:</label>
                <input type="text" name="nome" value="<?php echo $row['nome']; ?>">

                <label for="cpf">CPF:</label>
                <input type="text" id="cpf" name="cpf" maxlength="14" minlength="14" onkeypress="cpf()" value="<?php echo $row['cpf']; ?>">

                <label for="email">E-mail:</label>
                <input type="email" name="email" value="<?php echo $row['email']; ?>">

                <label for="rg">RG:</label>
                <input type="text" name="rg" value="<?php echo $row['rg']; ?>">

                <label for="telefone">Telefone: </label>
                <input type="text" name="telefone" id="telefone" value="<?php echo $row['telefone']; ?>">

                <label for="endereco">Endereço:</label>
                <input type="text" name="endereco" value="<?php echo $row['endereco']; ?>">

                <button type="submit" name="update">Atualizar</button>
                <button type="submit" name="delete">Excluir</button>
            </form>
        <?php endwhile; ?>
    <?php elseif (isset($result)): ?>
        <p>Nenhuma recepcionista encontrada.</p>
    <?php endif; ?>

    <script>
        document.querySelector("#cpf").addEventListener("input", function() {
            let cpf = this.value.replace(/\D/g, ""); // Remove tudo que não for número
            if (cpf.length > 11) cpf = cpf.slice(0, 11); // Limita a 11 caracteres numéricos
    
            // Formata como XXX.XXX.XXX-XX
            if (cpf.length > 9) {
                cpf = cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, "$1.$2.$3-$4");
            } else if (cpf.length > 6) {
                cpf = cpf.replace(/(\d{3})(\d{3})(\d{1,3})/, "$1.$2.$3");
            } else if (cpf.length > 3) {
                cpf = cpf.replace(/(\d{3})(\d{1,3})/, "$1.$2");
            }
            this.value = cpf;
        });

        document.querySelector("#telefone").addEventListener("input", function () {
            let tel = this.value.replace(/\D/g, ""); // Remove tudo que não for número

            if (tel.length > 11) tel = tel.slice(0, 11); 
            if (tel.length > 10) {
                tel = tel.replace(/(\d{2})(\d{5})(\d{4})/, "($1) $2-$3");
            } else if (tel.length > 6) {
                tel = tel.replace(/(\d{2})(\d{4})(\d{0,4})/, "($1) $2-$3");
            } else if (tel.length > 2) {
                tel = tel.replace(/(\d{2})(\d{0,5})/, "($1) $2");
            } else if (tel.length > 0) {
                tel = tel.replace(/(\d{0,2})/, "($1");
            }

            this.value = tel;
        });
    </script>
</body>
</html>
