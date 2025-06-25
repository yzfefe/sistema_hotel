<?php
require_once '../check_session.php';

// Verificação específica para recepcionista (exemplo)
if ($_SESSION['role'] != 'recepcionista') {
    header("Location: ../../html/login.html");
    exit();
}
include "../conex.php";
$resultado = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $campo = $_POST['campo'] ?? '';
    $valor = $_POST['valor'] ?? '';
    
    if (!empty($campo)) {
        $sql = "SELECT r.*, h.nome as nome_hospede, q.nome as nome_quarto 
                FROM reservas_quarto r
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
                FROM reservas_quarto r
                JOIN hospede h ON r.id_hos = h.id_hos
                JOIN quartos q ON r.id_quarto = q.id_quarto";
        $resultado = $conn->query($sql);
    }
} else {
    // Exibir todas as reservas inicialmente
    $sql = "SELECT r.*, h.nome as nome_hospede, q.nome as nome_quarto 
            FROM reservas_quarto r
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
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --color-primary: #c49a6c;
            --color-secondary: #6b392a;
            --color-light: #f5e7d8;
            --color-dark: #3a2618;
        }
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
            max-width: 1200px;
        }
        
        .logo {
            width: 20rem;
            display: block;
            margin: 0 auto 2rem auto;
            filter: drop-shadow(2px 2px 4px rgba(0, 0, 0, 0.3));
        }
        
        h1 {
            color: #5a3022;
            text-shadow: 1px 1px 2px rgba(255, 255, 255, 0.5);
            margin-bottom: 1.5rem;
            text-align: center;
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
        
        .btn-primary {
            background-color: #6b392a;
            border-color: #5a3022;
        }
        
        .btn-primary:hover {
            background-color: #5a3022;
            border-color: #4a281b;
        }
        
        .table {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .table thead {
            background: linear-gradient(135deg, rgba(133,78,57,1) 0%, rgba(107, 57, 42, 0.946) 100%);
            color: white;
        }
        
        .table th {
            border-bottom: none;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(233, 216, 200, 0.3);
        }
        
        .no-results {
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            color: #5a3022;
            font-weight: 500;
        }
        
        .form-select:focus, .form-control:focus {
            border-color: #c44512;
            box-shadow: 0 0 0 0.25rem rgba(194, 87, 44, 0.25);
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.85rem;
        }
        
        .status-confirmada {
            background-color: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }
        
        .status-cancelada {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }
        
        .status-pendente {
            background-color: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }
        
        .badge-hospede {
            background-color: #e9d8c8;
            color: #6b392a;
            font-weight: 500;
        }
        
        .badge-quarto {
            background-color: #d4e6f1;
            color: #2a6496;
            font-weight: 500;
        }
        .btn-outline-primary {
            border-color: var(--color-secondary);
            color: var(--color-secondary);
        }

        .btn-outline-primary:hover {
            background-color: var(--color-secondary);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="d-flex justify-content-start mb-4">
            <a href="../../html/recepcionista/tela_recep.php" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left me-1"></i> Voltar
            </a>
        </div>
        <img src="../../img/logo_hoteel.png" alt="Logo do Hotel" class="logo">
        <h1><i class="bi bi-calendar-check"></i> Consulta de Reservas</h1>
        
        <div class="search-form">
            <form method="post" action="" class="row g-3">
                <div class="col-md-4">
                    <label for="campo" class="form-label">Buscar por:</label>
                    <select name="campo" id="campo" class="form-select">
                        <option value="">Todas as reservas</option>
                        <option value="h.nome">Nome do Hóspede</option>
                        <option value="q.nome">Nome do Quarto</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="valor" class="form-label">Termo de busca:</label>
                    <input type="text" name="valor" id="valor" class="form-control" placeholder="Digite o termo de busca">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                </div>
            </form>
        </div>
        
        <?php if ($resultado && $resultado->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
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
                            switch(strtoupper($reserva['status'])) {
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
                            <td><?= htmlspecialchars($reserva['id_reserva']) ?></td>
                            <td>
                                <span class="badge badge-hospede rounded-pill">
                                    <i class="bi bi-person"></i> <?= htmlspecialchars($reserva['nome_hospede']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-quarto rounded-pill">
                                    <i class="bi bi-door-open"></i> <?= htmlspecialchars($reserva['nome_quarto']) ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars(date('d/m/Y', strtotime($reserva['data_reserva']))) ?></td>
                            <td><?= $reserva['data_encerrada'] ? htmlspecialchars(date('d/m/Y', strtotime($reserva['data_encerrada']))) : '--' ?></td>
                            <td>
                                <span class="status-badge <?= $status_class ?>">
                                    <?= htmlspecialchars($reserva['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="no-results">
                <i class="bi bi-exclamation-circle"></i> Nenhuma reserva encontrada.
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Fechar conexão
if (isset($conn)) {
    $conn->close();
}
?>