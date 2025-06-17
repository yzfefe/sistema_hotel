<?php
require_once '../../php/check_session.php';

  if ($_SESSION['role'] != 'administrador') {
      header("Location: ../../html/login.html");
      exit();
  }
include "../../php/conex.php";

$user_id = $_SESSION['user_id'];
$query = "SELECT nome FROM administrador WHERE id_adm = $user_id";
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
            max-width: 500px;
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
            background-color: #4a392a;
            color: white;
        }
        
        .btn-relat {
            background-color: #5a4a3b;
            color: white;
        }
        
        .btn-sair {
            background-color: #dc3545;
            color: white;
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
        
        .admin-banner {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin: 20px 0;
            border-left: 5px solid #4a392a;
            text-align: center;
        }
        
        .btn-container {
            max-width: 400px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="container container-custom">
        <div class="text-center">
            <img src="../../img/logo_hoteel.png" alt="Caminho das Pedras - Rustic Hotel" class="logo">
            
            <?php if ($result->num_rows > 0): ?>
                <?php while ($admin = $result->fetch_assoc()): ?>
                    <h2 class="welcome-text">Bem-vindo(a), Admin <?= htmlspecialchars($admin['nome']) ?></h2>
                <?php endwhile ?>
            <?php endif; ?>
            
            <div class="admin-banner">
                <h5><i class="bi bi-shield-lock"></i> Painel de Administração</h5>
                <p>Controle total do sistema</p>
            </div>
            
            <div class="btn-container">
                <button class="btn btn-custom btn-cad" onclick="redirecionar()">
                    <i class="bi bi-person-plus"></i> CADASTRAR GERENTE
                </button>
                
                <button class="btn btn-custom btn-relat" onclick="red()">
                    <i class="bi bi-file-earmark-text"></i> RELATÓRIOS
                </button>
                
                <button class="btn btn-custom btn-sair" onclick="red2()">
                    <i class="bi bi-box-arrow-right"></i> SAIR
                </button>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function redirecionar() {
            window.location.href = "http://localhost/sistema_hotel-main/html/adm/registrar_gerente.html";
        }

        function red() {
            window.location.href = "http://localhost/sistema_hotel-main/php/admin/relatorios_tela.php";
        }
        
        function red2() {
            window.location.href = "http://localhost/sistema_hotel-main/html/login.html";
        }
    </script>
</body>
</html>