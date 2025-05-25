<?php
session_start();

// Verifica se o usuário está logado como hóspede
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'hospede') {
    header("Location: ../login.php");
    exit();
}

include "../conex.php";

$user_id = $_SESSION['user_id'];
$mensagem = "";

// Processamento do formulário de aluguel
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['reservar_salao'])) {
        $id_salao = intval($_POST['id_salao']);
        $data_inicio = $_POST['data_inicio'];
        $data_fim = $_POST['data_fim'];
        $id_decoracao = isset($_POST['id_decoracao']) ? intval($_POST['id_decoracao']) : null;
        
        // Verifica disponibilidade do salão
        $stmt = $conn->prepare("SELECT * FROM saloes WHERE id_salao = ? AND disponibilidade = 'Disponível'");
        $stmt->bind_param("i", $id_salao);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($salao = $result->fetch_assoc()) {
            // Calcula valor total
            $dias = (strtotime($data_fim) - strtotime($data_inicio)) / (60 * 60 * 24) + 1;
            $valor_salao = $salao['preco'] * $dias;
            
            $valor_decoracao = 0;
            if ($id_decoracao) {
                $stmt = $conn->prepare("SELECT preco FROM decoracoes WHERE id_decoracao = ?");
                $stmt->bind_param("i", $id_decoracao);
                $stmt->execute();
                $decoracao = $stmt->get_result()->fetch_assoc();
                $valor_decoracao = $decoracao['preco'];
            }
            
            $valor_total = $valor_salao + $valor_decoracao;
            
            // Inicia transação
            $conn->begin_transaction();
            
            try {
                // 1. Insere reserva
                $stmt = $conn->prepare("INSERT INTO reservas_salao (id_hos, id_salao, id_decoracao, data_inicio, data_fim, valor_total) 
                                       VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iiissd", $user_id, $id_salao, $id_decoracao, $data_inicio, $data_fim, $valor_total);
                $stmt->execute();
                
                // 2. Atualiza disponibilidade do salão
                $stmt = $conn->prepare("UPDATE saloes SET disponibilidade = 'Indisponível' WHERE id_salao = ?");
                $stmt->bind_param("i", $id_salao);
                $stmt->execute();
                
                // 3. Atualiza gastos do hóspede
                $stmt = $conn->prepare("UPDATE hospede 
                                      SET gastos_atuais = gastos_atuais + ?, 
                                          gastos_totais = gastos_totais + ? 
                                      WHERE id_hos = ?");
                $stmt->bind_param("ddi", $valor_total, $valor_total, $user_id);
                $stmt->execute();
                
                $conn->commit();
                $mensagem = "success|Salão reservado com sucesso!";
            } catch (Exception $e) {
                $conn->rollback();
                $mensagem = "danger|Erro ao reservar salão: " . $e->getMessage();
            }
        } else {
            $mensagem = "danger|Salão não disponível para reserva.";
        }
    }
}

// Busca salões disponíveis
$saloes = [];
$decoracoes = [];

$result = $conn->query("SELECT * FROM saloes WHERE disponibilidade = 'Disponível' ORDER BY nome");
if ($result) {
    $saloes = $result->fetch_all(MYSQLI_ASSOC);
}

$result = $conn->query("SELECT * FROM decoracoes ORDER BY nome");
if ($result) {
    $decoracoes = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alugar Salão de Festa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --color-primary: #c49a6c;
            --color-secondary: #6b392a;
            --color-light: #f5e7d8;
            --color-dark: #3a2618;
        }
        
        body {
            background: linear-gradient(180deg, rgba(107, 57, 42, 0.946) 6%, rgba(133,78,57,1) 18%, rgb(203, 164, 122) 60%, rgba(217, 192, 164, 0.339) 90%, rgba(255,255,255,1) 110%);
            font-family: 'Playfair Display', serif;
            min-height: 100vh;
        }
        
        .container-main {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-top: 30px;
            margin-bottom: 30px;
        }
        
        .header-title {
            color: var(--color-secondary);
            font-weight: 700;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
            border-bottom: 2px solid var(--color-primary);
            padding-bottom: 10px;
            margin-bottom: 25px;
        }
        
        .card-salao {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 25px;
            background-color: white;
        }
        
        .card-salao:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        
        .card-img-top {
            height: 220px;
            object-fit: cover;
            border-bottom: 3px solid var(--color-primary);
        }
        
        .card-title {
            color: var(--color-secondary);
            font-weight: 600;
        }
        
        .btn-primary {
            background-color: var(--color-primary);
            border-color: var(--color-primary);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: var(--color-secondary);
            border-color: var(--color-secondary);
        }
        
        .btn-outline-primary {
            border-color: var(--color-primary);
            color: var(--color-primary);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--color-primary);
            color: white;
        }
        
        .modal-header {
            background-color: var(--color-secondary);
            color: white;
        }
        
        .alert-info {
            background-color: var(--color-light);
            border-color: var(--color-primary);
            color: var(--color-dark);
        }
        
        .section-title {
            color: var(--color-secondary);
            position: relative;
            padding-bottom: 10px;
            margin-bottom: 25px;
        }
        
        .section-title:after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 60px;
            height: 3px;
            background-color: var(--color-primary);
        }
        
        .badge {
            font-weight: 500;
            padding: 5px 10px;
        }
        
        .text-primary {
            color: var(--color-primary) !important;
        }
    </style>
</head>
<body>
    <div class="container container-main">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="header-title"><i class="fas fa-glass-cheers me-2"></i>Alugar Salão de Festa</h1>
            <a href="../../html/hospede/tela_hospede.html" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-1"></i> Voltar
            </a>
        </div>

        <?php if (!empty($mensagem)): 
            list($tipo, $texto) = explode('|', $mensagem, 2); ?>
            <div class="alert alert-<?= $tipo ?> alert-dismissible fade show">
                <?= htmlspecialchars($texto) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Seção de Salões Disponíveis -->
        <div class="mb-5">
            <h3 class="section-title"><i class="far fa-calendar-check me-2"></i>Salões Disponíveis</h3>
            
            <div class="row">
                <?php if (!empty($saloes)): ?>
                    <?php foreach ($saloes as $salao): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card card-salao h-100">
                                <img src="img/saloes/<?= $salao['id_salao'] ?>.jpg" class="card-img-top" alt="<?= htmlspecialchars($salao['nome']) ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($salao['nome']) ?></h5>
                                    <p class="card-text">
                                        <i class="fas fa-users me-2 text-primary"></i>Capacidade: <?= $salao['capacidade'] ?> pessoas<br>
                                        <i class="fas fa-money-bill-wave me-2 text-primary"></i>R$ <?= number_format($salao['preco'], 2, ',', '.') ?> / dia
                                    </p>
                                </div>
                                <div class="card-footer bg-white border-0">
                                    <div class="d-flex justify-content-between">
                                        <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" 
                                                data-bs-target="#detalhesModal<?= $salao['id_salao'] ?>">
                                            <i class="fas fa-info-circle me-1"></i> Detalhes
                                        </button>
                                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" 
                                                data-bs-target="#reservaModal<?= $salao['id_salao'] ?>">
                                            <i class="fas fa-calendar-plus me-1"></i> Reservar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal de Detalhes -->
                        <div class="modal fade" id="detalhesModal<?= $salao['id_salao'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title"><?= htmlspecialchars($salao['nome']) ?></h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <img src="img/saloes/<?= $salao['id_salao'] ?>.jpg" class="img-fluid rounded mb-4 w-100" alt="<?= htmlspecialchars($salao['nome']) ?>">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong><i class="fas fa-users me-2 text-primary"></i>Capacidade:</strong> <?= $salao['capacidade'] ?> pessoas</p>
                                                <p><strong><i class="fas fa-ruler-combined me-2 text-primary"></i>Tamanho:</strong> 150 m²</p>
                                                <p><strong><i class="fas fa-money-bill-wave me-2 text-primary"></i>Preço:</strong> R$ <?= number_format($salao['preco'], 2, ',', '.') ?> / dia</p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong><i class="fas fa-star me-2 text-primary"></i>Características:</strong></p>
                                                <ul class="list-unstyled">
                                                    <li><i class="fas fa-check-circle text-primary me-2"></i>Palco integrado</li>
                                                    <li><i class="fas fa-check-circle text-primary me-2"></i>Sistema de som profissional</li>
                                                    <li><i class="fas fa-check-circle text-primary me-2"></i>Iluminação ambiente</li>
                                                    <li><i class="fas fa-check-circle text-primary me-2"></i>Cozinha anexa</li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="mt-4">
                                            <h5><i class="fas fa-user-tie me-2 text-primary"></i>Responsáveis:</h5>
                                            <p><i class="fas fa-phone-alt me-2 text-primary"></i>Carlos Silva - (11) 98765-4321<br>
                                            <i class="fas fa-phone-alt me-2 text-primary"></i>Maria Oliveira - (11) 91234-5678</p>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal" 
                                                data-bs-toggle="modal" data-bs-target="#reservaModal<?= $salao['id_salao'] ?>">
                                            <i class="fas fa-calendar-plus me-1"></i> Reservar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal de Reserva -->
                        <div class="modal fade" id="reservaModal<?= $salao['id_salao'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Reservar <?= htmlspecialchars($salao['nome']) ?></h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form method="POST" action="">
                                        <div class="modal-body">
                                            <input type="hidden" name="id_salao" value="<?= $salao['id_salao'] ?>">
                                            
                                            <div class="mb-3">
                                                <label for="data_inicio<?= $salao['id_salao'] ?>" class="form-label">Data de Início</label>
                                                <input type="date" class="form-control" id="data_inicio<?= $salao['id_salao'] ?>" name="data_inicio" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label for="data_fim<?= $salao['id_salao'] ?>" class="form-label">Data de Término</label>
                                                <input type="date" class="form-control" id="data_fim<?= $salao['id_salao'] ?>" name="data_fim" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Decoração (Opcional)</label>
                                                <select class="form-select" name="id_decoracao">
                                                    <option value="">Nenhuma decoração</option>
                                                    <?php foreach ($decoracoes as $decoracao): ?>
                                                        <option value="<?= $decoracao['id_decoracao'] ?>">
                                                            <?= htmlspecialchars($decoracao['nome']) ?> - R$ <?= number_format($decoracao['preco'], 2, ',', '.') ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle me-2"></i> Valor estimado: 
                                                <span id="valorEstimado<?= $salao['id_salao'] ?>">R$ <?= number_format($salao['preco'], 2, ',', '.') ?></span>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                            <button type="submit" name="reservar_salao" class="btn btn-primary">Confirmar Reserva</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> Nenhum salão disponível no momento.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Seção de Decorações -->
        <div>
            <h3 class="section-title"><i class="fas fa-palette me-2"></i>Temas de Decoração</h3>
            
            <div class="row">
                <?php if (!empty($decoracoes)): ?>
                    <?php foreach ($decoracoes as $decoracao): ?>
                        <div class="col-md-4 col-lg-3 mb-4">
                            <div class="card card-salao h-100">
                                <img src="img/decoracoes/<?= $decoracao['id_decoracao'] ?>.jpg" class="card-img-top" alt="<?= htmlspecialchars($decoracao['nome']) ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($decoracao['nome']) ?></h5>
                                    <p class="card-text"><?= htmlspecialchars($decoracao['descricao']) ?></p>
                                    <p class="text-success fw-bold">R$ <?= number_format($decoracao['preco'], 2, ',', '.') ?></p>
                                </div>
                                <div class="card-footer bg-white border-0">
                                    <button class="btn btn-outline-primary w-100" data-bs-toggle="modal" 
                                            data-bs-target="#detalhesDecoracao<?= $decoracao['id_decoracao'] ?>">
                                        <i class="fas fa-info-circle me-1"></i> Detalhes
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Modal de Detalhes da Decoração -->
                        <div class="modal fade" id="detalhesDecoracao<?= $decoracao['id_decoracao'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title"><?= htmlspecialchars($decoracao['nome']) ?></h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <img src="img/decoracoes/<?= $decoracao['id_decoracao'] ?>.jpg" class="img-fluid rounded mb-3 w-100" alt="<?= htmlspecialchars($decoracao['nome']) ?>">
                                        <p><?= htmlspecialchars($decoracao['descricao']) ?></p>
                                        <p><strong><i class="fas fa-money-bill-wave me-2 text-primary"></i>Preço:</strong> R$ <?= number_format($decoracao['preco'], 2, ',', '.') ?></p>
                                        <p><strong><i class="fas fa-check-circle me-2 text-primary"></i>Inclui:</strong></p>
                                        <ul class="list-unstyled">
                                            <li><i class="fas fa-check text-primary me-2"></i>Decoração completa do ambiente</li>
                                            <li><i class="fas fa-check text-primary me-2"></i>Mesa de doces temática</li>
                                            <li><i class="fas fa-check text-primary me-2"></i>Arranjos de mesa personalizados</li>
                                            <li><i class="fas fa-check text-primary me-2"></i>Iluminação especial</li>
                                        </ul>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> Nenhum tema de decoração disponível.
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Cálculo do valor estimado ao alterar as datas
        document.querySelectorAll('[id^="data_inicio"], [id^="data_fim"]').forEach(input => {
            input.addEventListener('change', function() {
                const idSalao = this.id.replace(/data_(inicio|fim)/, '');
                const dataInicio = document.getElementById('data_inicio' + idSalao).value;
                const dataFim = document.getElementById('data_fim' + idSalao).value;
                
                if (dataInicio && dataFim) {
                    const diffTime = new Date(dataFim) - new Date(dataInicio);
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                    const precoDiaria = <?= json_encode(array_column($saloes, 'preco', 'id_salao')) ?>;
                    const salaoId = idSalao.replace(/\D/g, '');
                    const valorTotal = diffDays * (precoDiaria[salaoId] || 0);
                    
                    document.getElementById('valorEstimado' + idSalao).textContent = 
                        'R$ ' + valorTotal.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                }
            });
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>