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
          LEFT JOIN reservas r ON q.id_quarto = r.id_quarto
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
    <img src="../../img/logo_hoteel.png" alt="">
    <style>
        img{
            width: 20rem;
            display: block;
            margin: 0 auto;
        }
        .quarto-card {
            transition: transform 0.3s;
        }
        .quarto-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .disponivel {
            color: #198754;
        }
        .indisponivel {
            color: #dc3545;
        }
        .badge-usage {
            font-size: 1rem;
            background-color: #0d6efd;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="text-center">
                    <i class="bi bi-graph-up"></i> Quartos Mais Utilizados
                </h1>
                <p class="text-muted text-center">Ranking dos quartos com maior número de reservas</p>
            </div>
        </div>

        <?php if ($result && $result->num_rows > 0): ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php while ($quarto = $result->fetch_assoc()): ?>
                    <div class="col">
                        <div class="card h-100 quarto-card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title mb-0">
                                    <?= htmlspecialchars($quarto['nome']) ?> (#<?= $quarto['num_quarto'] ?>)
                                    <span class="badge bg-warning text-dark float-end">
                                        <?= $quarto['total_reservas'] ?> reservas
                                    </span>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span><i class="bi bi-house-door"></i> Tipo:</span>
                                    <strong><?= htmlspecialchars($quarto['tipo']) ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span><i class="bi bi-cash-coin"></i> Diária:</span>
                                    <strong>R$ <?= number_format($quarto['preco_diaria'], 2, ',', '.') ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span><i class="bi bi-building"></i> Andar:</span>
                                    <strong><?= $quarto['andar'] ?>º</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span><i class="bi bi-check-circle"></i> Status:</span>
                                    <strong class="<?= $quarto['disponivel'] ? 'disponivel' : 'indisponivel' ?>">
                                        <?= $quarto['disponivel'] ? 'Disponível' : 'Indisponível' ?>
                                    </strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span><i class="bi bi-star"></i> Popularidade:</span>
                                    <div>
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
                            <div class="card-footer bg-light">
                                <small class="text-muted">
                                    <i class="bi bi-info-circle"></i> 
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
            <div class="alert alert-warning" role="alert">
                <i class="bi bi-exclamation-triangle"></i> Nenhum dado de quarto encontrado ou não há reservas registradas.
            </div>
        <?php endif; ?>

        <div class="row mt-4">
            <div class="col-12">
                <a href="../../html/gerente/tela_gerente.html" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left"></i> Voltar ao Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
