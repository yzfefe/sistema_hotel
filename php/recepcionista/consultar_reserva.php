<?php
include "../conex.php";
$resultado = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $campo = $_POST['campo'] ?? '';
    $valor = $_POST['valor'] ?? '';
    
    if (!empty($campo)) {
        $sql = "SELECT r.*, h.nome as nome_hospede, q.nome as nome_quarto 
                FROM reservas r
                JOIN hospede h ON r.id_hos = h.id_hos
                JOIN quartos q ON r.id_quarto = q.id_quarto
                WHERE $campo LIKE ?";
        $stmt = $conn->prepare($sql);
        $valor_param = "%$valor%";
        $stmt->bind_param("s", $valor_param);
        $stmt->execute();
        $resultado = $stmt->get_result();
    } else {
        $sql = "SELECT r.*, h.nome as nome_hospede, q.nome as nome_quarto 
                FROM reservas r
                JOIN hospede h ON r.id_hos = h.id_hos
                JOIN quartos q ON r.id_quarto = q.id_quarto";
        $resultado = $conn->query($sql);
    }
} else {
    // Exibir todas as reservas inicialmente
    $sql = "SELECT r.*, h.nome as nome_hospede, q.nome as nome_quarto 
            FROM reservas r
            JOIN hospede h ON r.id_hos = h.id_hos
            JOIN quartos q ON r.id_quarto = q.id_quarto";
    $resultado = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Reservas</title>
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
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }
        .search-form {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f0f0f0;
            border-radius: 5px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: center;
        }
        .search-form label {
            margin-right: 5px;
            font-weight: bold;
        }
        .search-form select, 
        .search-form input {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .search-form button {
            padding: 8px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .search-form button:hover {
            background-color: #45a049;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 14px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
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
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
        }
        .status-confirmada {
            color: #28a745;
            font-weight: bold;
        }
        .status-cancelada {
            color: #dc3545;
            font-weight: bold;
        }
        .status-pendente {
            color: #ffc107;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Consulta de Reservas</h1>
        
        <div class="search-form">
            <form method="post" action="">
                <label for="campo">Buscar por:</label>
                <select name="campo" id="campo">
                    <option value="">Todas as reservas</option>
                    <option value="h.nome">Nome do Hóspede</option>
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
                        <th>ID Reserva</th>
                        <th>Hóspede</th>
                        <th>Quarto</th>
                        <th>Data Reserva</th>
                        <th>Data Encerrada</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($reserva = $resultado->fetch_assoc()): 
                        // Determina a classe CSS baseada no status
                        $status_class = '';
                        switch(strtoupper($reserva['status_atual'])) {
                            case 'CONFIRMADA':
                                $status_class = 'status-confirmada';
                                break;
                            case 'CANCELADA':
                                $status_class = 'status-cancelada';
                                break;
                            case 'PENDENTE':
                                $status_class = 'status-pendente';
                                break;
                            default:
                                $status_class = '';
                        }
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($reserva['id_reserva']); ?></td>
                        <td><?php echo htmlspecialchars($reserva['nome_hospede']); ?></td>
                        <td><?php echo htmlspecialchars($reserva['nome_quarto']); ?></td>
                        <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($reserva['data_reserva']))); ?></td>
                        <td><?php echo $reserva['data_encerrada'] ? htmlspecialchars(date('d/m/Y', strtotime($reserva['data_encerrada']))) : '--'; ?></td>
                        <td class="<?php echo $status_class; ?>">
                            <?php echo htmlspecialchars($reserva['status_atual']); ?>
                        </td>
                        
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-results">Nenhuma reserva encontrada.</p>
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