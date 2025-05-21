<?php
session_start();
include "../conex.php";

// Consulta para obter os quartos mais utilizados
$query = "SELECT 
            q.id_quarto,
            q.nome,
            q.num_quarto, 
            q.tipo, 
            q.preco_diaria,
            q.andar,
            q.disponivel,
            COUNT(r.id_reserva) as total_reservas
          FROM quartos q
          LEFT JOIN reservas_quarto r ON q.id_quarto = r.id_quarto
          GROUP BY q.id_quarto, q.nome, q.num_quarto, q.tipo, q.preco_diaria, q.andar, q.disponivel
          ORDER BY total_reservas DESC, q.num_quarto ASC
          LIMIT 10"; // Limita aos 10 quartos mais usados

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quartos Mais Utilizados</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Ícones do Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
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
            margin-bottom: 0.5rem;
        }
        
        .subtitle {
            color: #6b392a;
            margin-bottom: 2rem;
        }
        
        .quarto-card {
            transition: transform 0.3s, box-shadow 0.3s;
            border: none;
            border-radius: 10px;
            overflow: hidden;
            height: 100%;
        }
        
        .quarto-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.15);
        }
        
        .card-header {
            background: linear-gradient(135deg, rgba(133,78,57,1) 0%, rgba(107, 57, 42, 0.946) 100%);
            color: white;
            border-bottom: none;
        }
        
        .badge-reservas {
            background-color: #e9d8c8;
            color: #6b392a;
            font-size: 1rem;
        }
        
        .disponivel {
            color: #2e8b57;
            font-weight: 500;
        }
        
        .indisponivel {
            color: #c44512;
            font-weight: 500;
        }
        
        .card-body {
            background-color: rgba(255, 255, 255, 0.95);
        }
        
        .info-label {
            color: #5a3022;
            font-weight: 500;
        }
        
        .info-value {
            color: #333;
            font-weight: 400;
        }
        
        .card-footer {
            background-color: rgba(233, 216, 200, 0.5);
            border-top: 1px solid rgba(203, 164, 122, 0.3);
        }
        
        .btn-outline-primary {
            color: #6b392a;
            border-color: #6b392a;
        }
        
        .btn-outline-primary:hover {
            background-color: #6b392a;
            color: white;
        }
        
        .star-rating {
            letter-spacing: 2px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <a href="../../html/gerente/tela_gerente.html" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>

        <img src="../../img/logo_hoteel.png" alt="Logo do Hotel" class="logo">
        <div class="text-center mb-4">
            <h1><i class="bi bi-graph-up"></i> Quartos Mais Utilizados</h1>
            <p class="subtitle">Ranking dos quartos com maior número de reservas</p>
        </div>

        <?php if ($result && $result->num_rows > 0): ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php while ($quarto = $result->fetch_assoc()): ?>
                    <div class="col">
                        <div class="card h-100 quarto-card">
                            <div class="card-header">
                                <h5 class="card-title mb-0 d-flex justify-content-between align-items-center">
                                    <span>
                                        <i class="bi bi-door-open"></i> <?= htmlspecialchars($quarto['nome']) ?> 
                                        <small>(#<?= $quarto['num_quarto'] ?>)</small>
                                    </span>
                                    <span class="badge badge-reservas rounded-pill">
                                        <?= $quarto['total_reservas'] ?> reserva<?= $quarto['total_reservas'] != 1 ? 's' : '' ?>
                                    </span>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-3">
                                    <span class="info-label"><i class="bi bi-house-door"></i> Tipo:</span>
                                    <span class="info-value"><?= htmlspecialchars($quarto['tipo']) ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-3">
                                    <span class="info-label"><i class="bi bi-cash-coin"></i> Diária:</span>
                                    <span class="info-value">R$ <?= number_format($quarto['preco_diaria'], 2, ',', '.') ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-3">
                                    <span class="info-label"><i class="bi bi-building"></i> Andar:</span>
                                    <span class="info-value"><?= $quarto['andar'] ?>º</span>
                                </div>
                                <div class="d-flex justify-content-between mb-3">
                                    <span class="info-label"><i class="bi bi-check-circle"></i> Status:</span>
                                    <span class="<?= $quarto['disponivel'] ? 'disponivel' : 'indisponivel' ?>">
                                        <?= $quarto['disponivel'] ? 'Disponível' : 'Indisponível' ?>
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="info-label"><i class="bi bi-star"></i> Popularidade:</span>
                                    <div class="star-rating">
                                        <?php 
                                        $stars = min(5, ceil($quarto['total_reservas'] / 5)); // Máximo de 5 estrelas
                                        for ($i = 0; $i < $stars; $i++): ?>
                                            <i class="bi bi-star-fill text-warning"></i>
                                        <?php endfor; ?>
                                        <?php for ($i = $stars; $i < 5; $i++): ?>
                                            <i class="bi bi-star text-warning"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <small class="text-muted d-flex align-items-center">
                                    <i class="bi bi-info-circle me-2"></i>
                                    <?php 
                                    if ($quarto['total_reservas'] == 0) {
                                        echo "Nenhuma reserva registrada";
                                    } elseif ($quarto['total_reservas'] == 1) {
                                        echo "Reservado 1 vez";
                                    } else {
                                        echo "Reservado {$quarto['total_reservas']} vezes";
                                    }
                                    ?>
                                </small>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-warning text-center" role="alert">
                <i class="bi bi-exclamation-triangle"></i> Nenhum dado de quarto encontrado ou não há reservas registradas.
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>