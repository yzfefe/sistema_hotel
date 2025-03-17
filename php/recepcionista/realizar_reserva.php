<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserva de Quarto</title>
</head>
<body>
    <h1>Realizar Reserva</h1>
    <form action="process_realizar_reserva.php" method="post">
        

        <label for="cliente">Selecione o Cliente:</label><br>
        <select id="cliente" name="cliente_id" required>
            <?php
            // Conectar ao banco e listar os quartos disponíveis
            include '../conex.php';
            $sql = "SELECT id_hos, nome, telefone FROM hospede";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['id_hos'] . "'>Hóspede " . $row['nome'] . " - " . $row['telefone'] . "</option>";
                }
            } else {
                echo "<option disabled>Não há quartos disponíveis</option>";
            }
            ?>
        </select><br>
        

        <h2>Escolha do Quarto</h2>
        <label for="quarto">Selecione o Quarto:</label><br>
        <select id="quarto" name="quarto_id" required>
            <?php
            // Conectar ao banco e listar os quartos disponíveis
            include '../conex.php';
            $sql = "SELECT id_quarto, nome, tipo FROM quartos";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['id_quarto'] . "'>Quarto " . $row['nome'] . " - " . $row['tipo'] . "</option>";
                }
            } else {
                echo "<option disabled>Não há quartos disponíveis</option>";
            }
            ?>
        </select><br>

        <input type="submit" value="Realizar Reserva">
    </form>
</body>
</html>
