<?php
include "../conex.php";
$resultado = null;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $campo = $_POST['campo'] ?? '';
    $valor = $_POST['valor'] ?? '';
    
    if (!empty($campo)) {
        $sql = "SELECT * FROM hospede WHERE $campo LIKE ?";
        $stmt = $conn->prepare($sql);
        $valor_param = "%$valor%";
        $stmt->bind_param("s", $valor_param);
        $stmt->execute();
        $resultado = $stmt->get_result();
    } else {
        $sql = "SELECT * FROM hospede";
        $resultado = $conn->query($sql);
    }
} else {
    // Exibir todos os hóspedes inicialmente
    $sql = "SELECT * FROM hospede";
    $resultado = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Hóspedes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
        }
        .search-form {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f0f0f0;
            border-radius: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .no-results {
            color: #d9534f;
            font-weight: bold;
            text-align: center;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Consulta de Hóspedes</h1>
        
        <div class="search-form">
            <form method="post" action="">
                <label for="campo">Buscar por:</label>
                <select name="campo" id="campo">
                    <option value="">Todos os hóspedes</option>
                    <option value="nome">Nome</option>
                    <option value="cpf">CPF</option>
                </select>
                
                <label for="valor">Termo:</label>
                <input type="text" name="valor" id="valor" placeholder="Digite o termo de busca">
                
                <button type="submit">Buscar</button>
            </form>
        </div>
        
        <?php if ($resultado && $resultado->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>CPF</th>
                        <th>RG</th>
                        <th>Endereço</th>
                        <th>Telefone</th>
                        <th>Horário</th>
                        <th>Gastos Atuais</th>
                        <th>Gastos Totais</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($hospede = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($hospede['id_hos']); ?></td>
                        <td><?php echo htmlspecialchars($hospede['nome']); ?></td>
                        <td><?php echo htmlspecialchars($hospede['email']); ?></td>
                        <td><?php echo htmlspecialchars($hospede['cpf']); ?></td>
                        <td><?php echo htmlspecialchars($hospede['rg']); ?></td>
                        <td><?php echo htmlspecialchars($hospede['endereco']); ?></td>
                        <td><?php echo htmlspecialchars($hospede['telefone']); ?></td>
                        <td><?php echo htmlspecialchars($hospede['horario']); ?></td>
                        <td><?php echo htmlspecialchars($hospede['gastos_totais']); ?></td>
                        <td><?php echo htmlspecialchars($hospede['gastos_atuais']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-results">Nenhum hóspede encontrado.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
// Fechar conexão
if (isset($conn)) {
    $conn->close();
}
?>
