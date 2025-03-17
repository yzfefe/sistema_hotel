<?php
include '../conex.php'; // Inclui o arquivo de conexão com o banco de dados

// Verifica se os dados do formulário foram enviados
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recebe os dados do formulário
    $cliente_id = $_POST['cliente_id'];
    $quarto_id = $_POST['quarto_id'];
    $data_reserva = date('Y-m-d H:i:s'); // Data e hora atual

    // Prepara a consulta SQL para inserir a reserva
    $sql_reserva = $conn->prepare("INSERT INTO reservas (id_hos, id_quarto, data_reserva) VALUES (?, ?, ?)");

    // Verifica se a consulta foi preparada com sucesso
    if ($sql_reserva === false) {
        die('Erro ao preparar a consulta: ' . $conn->error);
    }

    // Vincula os parâmetros à consulta preparada
    $sql_reserva->bind_param('iis', $cliente_id, $quarto_id, $data_reserva); // 'i' para inteiro, 's' para string (data)

    // Executa a consulta
    if ($sql_reserva->execute()) {
        echo "Reserva realizada com sucesso!";
    } else {
        echo "Erro ao realizar a reserva: " . $sql_reserva->error;
    }

    // Fecha a consulta e a conexão
    $sql_reserva->close();
    $conn->close();
}
?>
