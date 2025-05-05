<?php
include "../conex.php";

// Processar a consulta
$resultado = null;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $campo = $_POST['campo'] ?? '';
    $valor = $_POST['valor'] ?? '';
    
    if (!empty($campo)) {
        $sql = "SELECT * FROM quartos WHERE $campo LIKE ?";
        $stmt = $conn->prepare($sql);
        $valor_param = "%$valor%";
        $stmt->bind_param("s", $valor_param);
        $stmt->execute();
        $resultado = $stmt->get_result();
    } else {
        $sql = "SELECT * FROM quartos";
        $resultado = $conn->query($sql);
    }
} else {
    // Exibir todos os quartos inicialmente
    $sql = "SELECT * FROM quartos";
    $resultado = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Quartos</title>
    <img src="../../img/logo_hoteel.png" alt="">
    <style>
        img{
            width: 20rem;
            display: block;
            margin: 0 auto;
        } 
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
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .search-form {
            margin-bottom: 20px;
            padding: 20px;
            background-color: #f0f0f0;
            border-radius: 5px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }
        .search-form label {
            font-weight: bold;
        }
        .search-form select, .search-form input {
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        .search-form button {
            padding: 8px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .search-form button:hover {
            background-color: #45a049;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #4CAF50;
            color: white;
            position: sticky;
            top: 0;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #e9e9e9;
        }
        .no-results {
            color: #d9534f;
            font-weight: bold;
            text-align: center;
            padding: 20px;
            font-size: 18px;
        }
        .price {
            font-weight: bold;
            color: #2a6496;
        }
        .actions {
            white-space: nowrap;
        }
        .actions button {
            padding: 5px 10px;
            margin-right: 5px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        .edit-btn {
            background-color: #f0ad4e;
            color: white;
        }
        .delete-btn {
            background-color: #d9534f;
            color: white;
        }
        @media (max-width: 768px) {
            .search-form {
                flex-direction: column;
                align-items: stretch;
            }
            table {
                font-size: 14px;
            }
            th, td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Consulta de Quartos</h1>
        
        <div class="search-form">
            <form method="post" action="">
                <label for="campo">Buscar por:</label>
                <select name="campo" id="campo">
                    <option value="">Todos os quartos</option>
                    <option value="tipo">Tipo</option>
                    <option value="nome">Nome</option>
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
                        <th>Número</th>
                        <th>Tipo</th>
                        <th>Preço Diária</th>
                        <th>Andar</th>
                        <th>Disponível</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($quarto = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($quarto['id_quarto']); ?></td>
                        <td><?php echo htmlspecialchars($quarto['nome']); ?></td>
                        <td><?php echo htmlspecialchars($quarto['num_quarto']); ?></td>
                        <td><?php echo htmlspecialchars($quarto['tipo']); ?></td>
                        <td class="price">R$ <?php echo number_format($quarto['preco_diaria'], 2, ',', '.'); ?></td>
                        <td><?php echo htmlspecialchars($quarto['andar']); ?></td>
                        <td><?php echo htmlspecialchars($quarto['disponivel']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-results">Nenhum quarto encontrado.</p>
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
