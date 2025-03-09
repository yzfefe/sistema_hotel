<?php 
    include "conex.php";
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $nome = $conn->real_escape_string($_POST['nome']);
        $preco = $conn->real_escape_string($_POST['preco']);
        $horario_disponivel = $conn->real_escape_string($_POST['horario_disponivel']);
    
    
    $sql_check = "SELECT * FROM serviços WHERE nome = '$nome' ";
    $result_check = $conn->query($sql_check);

    if ($result_check->num_rows > 0) {
        $msg = "Usuário já existe!";
    

        // Inserir novo usuário no banco de dados (corrigida a query)
        $sql = "INSERT INTO serviços (nome, preço, horario_disponivel) 
                VALUES ('$nome', '$preco', '$horario_disponivel' )";
        if ($conn->query($sql) === TRUE) {
            $msg = "Registro bem sucedido";
        } else {
            $msg = "Erro ao registrar: " . $conn->error;
        }
    }
}

$conn->close();
?>