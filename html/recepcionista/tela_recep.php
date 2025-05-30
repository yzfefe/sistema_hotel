<?php 
    include "../../php/conex.php";
    session_start();

    if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'recepcionista') {
        header("Location: ../login.html");
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
    <link rel="stylesheet" href="../../css/recepcionista/tela_recep.css">
</head>
<body>
    <?php if ($result->num_rows > 0):?>
        <?php while ($quarto = $result->fetch_assoc()): ?>
            <h2>Seja bem vindo(a) <?= htmlspecialchars($quarto['nome'])?></h2>
        <?php endwhile?>
    <?php endif;?>
    
        <img src="../../img/logo_hoteel.png" alt="Caminho das Pedras - Rustic Hotel">
        
            <button class="btn-cad" onclick="red1()">CADASTRAR HÓSPEDE</button>  
            <button class="btn-reserv" onclick="red2()">RESERVAR QUARTO</button> 
            <button class="btn-consult-h" onclick="red3()">CONSULTAR HÓSPEDES</button>
            <button class="btn-consult-r" onclick="red4()">CONSULTAR RESERVA</button>
            <button class="btn-consult-q" onclick="red5()">CONSULTAR QUARTO</button>
            <button class="btn-fec" onclick="red6()">FECHAR ESTADIA</button>
            <button class="btn-sair" onclick="red()">SAIR</button>
      
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

    </script>
</body>
</html>
