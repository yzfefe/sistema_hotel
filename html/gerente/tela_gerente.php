<?php 
session_start();
include "../../php/conex.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.html");
    exit();
}
 
// Verifica se é realmente um gerente
if ($_SESSION['role'] !== 'gerente') {
    header("Location: ../login.html");
    exit();
}

$user_id = $_SESSION['user_id'];  
$query = "SELECT nome FROM gerente WHERE id_ger = $user_id";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caminho das Pedras - Rustic Hotel</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="shortcut icon" type="imagex/png" href="../../img/aba.png">
    <style>
        body {
            background: linear-gradient(180deg, rgba(107, 57, 42, 0.946) 6%, rgba(133,78,57,1) 18%, rgb(203, 164, 122) 60%, rgba(217, 192, 164, 0.339) 90%, rgba(255,255,255,1) 110%);
            min-height: 100vh;
            font-family: 'Playfair Display', serif;
        }
        
        .container-custom {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-top: 30px;
            margin-bottom: 30px;
            border: 1px solid rgba(203, 164, 122, 0.3);
        }
        
        .logo {
            max-height: 150px;
            filter: drop-shadow(2px 2px 4px rgba(0,0,0,0.3));
            margin-bottom: 20px;
        }
        
        .btn-custom {
        width: 100%;
        padding: 15px;
        margin: 10px 0;
        font-weight: 600;
        border: none;
        transition: all 0.3s;
        color: white;
    }
    
    /* Sequência de cores - ESCURO no topo para CLARO embaixo */
    .btn-serv {
        background-color: #5A3022; /* Mais escuro - topo */
    }
    
    .btn-prom-servico {
        background-color: #5A3022;
    }
   
    .btn-cad {
        background-color: #6B392A;
    }
    
    .btn-consult {
        background-color: #6B392A;
    }
    
    .btn-prom-quarto {
        background-color: #7A4A3B;
    }
    
    .btn-exc {
        background-color: #7A4A3B;
    }
    
    .btn-quarto {
        background-color: #8A5A4C;
    }
    
    .btn-list {
        background-color: #8A5A4C;
    }
    
    .btn-relat {
        background-color:rgb(180, 114, 96); /* Mais claro - parte inferior */
    }
    
    .btn-exc2 {
        background-color: rgb(180, 114, 96);
    }
    
    .btn-sair {
        background-color: #dc3545;
        width: 10% /* Vermelho terroso - mantém destaque fixo */
    }
    
    .btn-custom:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        opacity: 0.9;
        filter: brightness(110%);
    }
        
        
        .welcome-text {
            color: #5a3022;
            font-weight: 700;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .btn-container {
            max-width: 600px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        
        .admin-banner {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin: 20px 0;
            border-left: 5px solid #6b392a;
            text-align: center;
        }
        
        @media (max-width: 768px) {
            .btn-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container container-custom">
        <button class="btn btn-custom btn-sair" onclick="red()">
            <i class="bi bi-box-arrow-right"></i> SAIR
        </button>
        <div class="text-center">
            <img src="../../img/logo_hoteel.png" alt="Caminho das Pedras - Rustic Hotel" class="logo">
            
            <?php if ($result->num_rows > 0):?>
                <?php while ($gerente = $result->fetch_assoc()): ?>
                    <h2 class="welcome-text">Bem-vindo(a), Gerente <?= htmlspecialchars($gerente['nome'])?></h2>
                <?php endwhile?>
            <?php endif;?>
            
            <div class="admin-banner">
                <h5><i class="bi bi-shield-lock"></i> Painel Administrativo</h5>
                <p>Gerencie todas as operações do hotel</p>
            </div>
            
            <div class="btn-container">
    <!-- TOPO - CORES MAIS ESCURAS -->
    <button class="btn btn-custom btn-serv" onclick="red1()">
        <i class="bi bi-plus-circle"></i> CADASTRAR SERVIÇO
    </button>
    
    <button class="btn btn-custom btn-prom-servico" onclick="red6()">
        <i class="bi bi-tag"></i> PROMOÇÃO SERVIÇOS
    </button>
    
    <button class="btn btn-custom btn-cad" onclick="red2()">
        <i class="bi bi-person-plus"></i> CADASTRAR RECEPCIONISTA
    </button>
    
    <button class="btn btn-custom btn-consult" onclick="red7()">
        <i class="bi bi-search"></i> CONSULTAR RECEPCIONISTA
    </button>
    
    <button class="btn btn-custom btn-prom-quarto" onclick="red3()">
        <i class="bi bi-percent"></i> PROMOÇÃO QUARTOS
    </button>
    
    <button class="btn btn-custom btn-exc" onclick="red8()">
        <i class="bi bi-calendar-x"></i> GERENCIAR RESERVAS
    </button>
    
    <button class="btn btn-custom btn-quarto" onclick="red4()">
        <i class="bi bi-house-door"></i> CADASTRAR QUARTO
    </button>
    
    <button class="btn btn-custom btn-list" onclick="red9()">
        <i class="bi bi-bar-chart"></i> QUARTOS MAIS USADOS
    </button>
    
    <!-- PARTE INFERIOR - CORES MAIS CLARAS -->
    <button class="btn btn-custom btn-relat" onclick="red5()">
        <i class="bi bi-file-earmark-text"></i> RELATÓRIOS
    </button>
    
    <button class="btn btn-custom btn-exc2" onclick="red10()">
        <i class="bi bi-people"></i> GERENCIAR HÓSPEDES
    </button>
    
    <!-- BOTÃO SAIR - COR FIXA DIFERENCIADA -->
    
</div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function red1(){ window.location.href = "http://localhost/sistema_hotel-main/html/gerente/cadastrar_servicos.php" }
        function red2(){ window.location.href = "http://localhost/sistema_hotel-main/html/gerente/registrar_recep.html" }
        function red3(){ window.location.href = "http://localhost/sistema_hotel-main/php/gerente/cadastrar_quartos_promocoes.php" }
        function red4(){ window.location.href = "http://localhost/sistema_hotel-main/html/gerente/cadastrar_quartos.html" }
        function red5(){ window.location.href = "http://localhost/sistema_hotel-main/php/gerente/relatorios_tela.php" }
        function red6(){ window.location.href = "http://localhost/sistema_hotel-main/php/gerente/cadastrar_servicos_promocoes.php" }
        function red7(){ window.location.href = "http://localhost/sistema_hotel-main/php/gerente/consultar_recepcionista.php" }
        function red8(){ window.location.href = "http://localhost/sistema_hotel-main/php/gerente/excluir_alterar_reserva.php" }
        function red9(){ window.location.href = "http://localhost/sistema_hotel-main/php/gerente/quartos_mais_usados.php" }
        function red10(){ window.location.href = "http://localhost/sistema_hotel-main/php/gerente/excluir_alterar_hospede.php" }
        function red(){ window.location.href = "http://localhost/sistema_hotel-main/html/login.html" }
    </script>
</body>
</html>