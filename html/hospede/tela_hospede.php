<?php
    include "../../php/conex.php";

    require_once '../../php/check_session.php';
    if ($_SESSION['role'] != 'hospede') {
        header("Location: ../../html/login.html");
        exit();
    }

    $user_id = $_SESSION['user_id'];  

    $query = "SELECT nome FROM hospede WHERE id_hos = $user_id";
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
        }
        
        .btn-serv {
            background-color: #5A3022;
            color: white;
        }
        
        .btn-reserv {
            background-color: #7A4A3B;
            color: white;
        }
        
        .btn-sol_serv {
            background-color: #8A5A4C;
            color: white;
        }
        
        .btn-promo {
            background-color: #c49a6c;
            color: white;
        }
        
        .btn-sair {
            background-color: #dc3545;
            color: white;
            width: 10%;
        }
        
        .btn-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            opacity: 0.9;
        }
        
        .welcome-text {
            color: #5a3022;
            font-weight: 700;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .btn-container {
            max-width: 500px;
            margin: 0 auto;
        }
        
        .promo-banner {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin: 20px 0;
            border-left: 5px solid #c49a6c;
        }
    </style>
</head>
<body>
    <div class="container container-custom">
        <button class="btn btn-custom btn-sair" onclick="red2()">
            <i class="bi bi-box-arrow-right"></i> SAIR
        </button>
        <div class="text-center">
            <img src="../../img/logo_hoteel.png" alt="Caminho das Pedras - Rustic Hotel" class="logo">
            
            <?php if ($result->num_rows > 0):?>
                <?php while ($quarto = $result->fetch_assoc()): ?>
                    <h2 class="welcome-text">Seja bem vindo(a), <?= htmlspecialchars($quarto['nome'])?></h2>
                <?php endwhile?>
            <?php endif;?>
            
            <div class="promo-banner">
                <h5><i class="bi bi-stars"></i> Promoções Especiais</h5>
                <p>Confira nossas promoções!</p>
            </div>
            
            <div class="btn-container">
                <button class="btn btn-custom btn-serv" onclick="red1()">
                    <i class="bi bi-cup-hot"></i> SERVIÇOS DE QUARTO
                </button>  
              

                <button class="btn btn-custom btn-sol_serv" onclick="red4()">
                    <i class="bi bi-percent"></i> SOLICITAR SERVIÇOS EM PROMOÇÃO
                </button>
                
                <!-- Novo botão de Lista de Promoções -->
                <button class="btn btn-custom btn-promo" onclick="red5()">
                    <i class="bi bi-list-stars"></i> LISTA DE PROMOÇÕES
                </button>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function red1(){
            window.location.href = "http://localhost/sistema_hotel-main/php/hospede/solicitar_servico.php"
        }
        function red2(){
            window.location.href = "http://localhost/sistema_hotel-main/html/login.html"
        }
        function red3(){
            window.location.href = "http://localhost/sistema_hotel-main/php/hospede/alugar_salao.php"
        }
        function red4(){
            window.location.href = "http://localhost/sistema_hotel-main/php/hospede/solicitar_promocoes.php"
        }
        function red5(){
            window.location.href = "http://localhost/sistema_hotel-main/php/hospede/listar_promocoes.php"
        }
    </script>
</body>
</html>