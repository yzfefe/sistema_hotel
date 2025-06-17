<?php 
    include "../../php/conex.php";
    require_once '../../php/check_session.php';

    // Verificação específica para recepcionista (exemplo)
    if ($_SESSION['role'] != 'recepcionista') {
        header("Location: ../../html/login.html");
        exit();
    }
    $user_id = $_SESSION['user_id'];  

    $query = "SELECT nome FROM recepcionista WHERE id_recep = $user_id";
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
        }
        
        .btn-cad {
            background-color: #5A3022;
            color: white;
        }
        
        .btn-reserv {
            background-color: #5a3022;
            color: white;
        }
        
        .btn-consult-h {
            background-color: #6B392A;
            color: white;
        }
        
        .btn-consult-r {
            background-color: #7A4A3B;
            color: white;
        }
        
        .btn-consult-q {
            background-color: #8A5A4C;
            color: white;
        }
        
        .btn-fec {
            background-color: rgb(161, 98, 80);
            color: white;
        }

        .btn-fec-sal {
            background-color: rgb(180, 114, 96);
            color: white;
        }
        .btn-res-sal{
            background-color: rgb(197, 151, 138);
            color: white;
        }
        .btn-sair {
            background-color: #dc3545;
            color: white;
            width: 10%
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
                <?php while ($quarto = $result->fetch_assoc()): ?>
                    <h2 class="welcome-text">Seja bem vindo(a) Recepcionista, <?= htmlspecialchars($quarto['nome'])?></h2>
                <?php endwhile?>
            <?php endif;?>
            
            <div class="btn-container">
                <button class="btn btn-custom btn-cad" onclick="red1()">
                    <i class="bi bi-person-plus"></i> CADASTRAR HÓSPEDE
                </button>  
                <button class="btn btn-custom btn-reserv" onclick="red2()">
                    <i class="bi bi-calendar-check"></i> RESERVAR QUARTO
                </button> 
                <button class="btn btn-custom btn-consult-h" onclick="red3()">
                    <i class="bi bi-search"></i> CONSULTAR HÓSPEDES
                </button>
                <button class="btn btn-custom btn-consult-r" onclick="red4()">
                    <i class="bi bi-journal-text"></i> CONSULTAR RESERVA
                </button>
                <button class="btn btn-custom btn-consult-q" onclick="red5()">
                    <i class="bi bi-door-open"></i> CONSULTAR QUARTO
                </button>
                <button class="btn btn-custom btn-fec" onclick="red6()">
                    <i class="bi bi-cash-stack"></i> FECHAR ESTADIA
                </button>
                <button class="btn btn-custom btn-fec-sal" onclick="red7()">
                    <i class="bi bi-cash-stack"></i> FECHAR SALÃO
                </button>
                <button class="btn btn-custom btn-res-sal" onclick="red8()">
                    <i class="bi bi-calendar-check"></i> ALUGAR SALÃO
                </button>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function red1(){
            window.location.href = "http://localhost/sistema_hotel-main/html/recepcionista/registrar_hospede.html"
        }
        function red2(){
            window.location.href = "http://localhost/sistema_hotel-main/php/recepcionista/realizar_reserva.php"
        }
        
        function red(){
            window.location.href = "http://localhost/sistema_hotel-main/html/login.html"
        }

        function red3(){
            window.location.href = "http://localhost/sistema_hotel-main/php/recepcionista/consultar_hospede.php"
        }

        function red4(){
            window.location.href = "http://localhost/sistema_hotel-main/php/recepcionista/consultar_reserva.php"
        }

        function red5(){
            window.location.href = "http://localhost/sistema_hotel-main/php/recepcionista/consultar_quarto.php"
        }

        function red6(){
            window.location.href = "http://localhost/sistema_hotel-main/php/recepcionista/fecha_estadia.php"
        }
        function red7(){
            window.location.href = "http://localhost/sistema_hotel-main/php/recepcionista/finalizar_estadia_salao.php"
        }
        function red8(){
            window.location.href = "http://localhost/sistema_hotel-main/php/recepcionista/alugar_salao.php"
        }
    </script>
</body>
</html>