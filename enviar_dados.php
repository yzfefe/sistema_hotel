<?php
// Conexão com o banco de dados
$pdo = new PDO('mysql:host=localhost;dbname=sistema_hotel', 'root', '');

// Função para criptografar senhas
function criptografarSenha($senha) {
    return password_hash($senha, PASSWORD_DEFAULT);
}

// Inserir dados na tabela administrador
$stmt = $pdo->prepare("INSERT INTO administrador (login, senha) VALUES (?, ?)");
$stmt->execute(['adm_hotel', criptografarSenha('adm_hotel001')]);

// Inserir dados na tabela gerente
$stmt = $pdo->prepare("INSERT INTO gerente (nome, email, cpf, endereco, telefone, login, senha) 
                       VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->execute(['Carlos Silva', 'carlos.silva@exemplo.com', '12345678901', 'Avenida Brasil, 100', '21987654321', 'carlos', criptografarSenha('senha123')]);

// Inserir dados na tabela recepcionista
$stmt = $pdo->prepare("INSERT INTO recepcionista (nome, email, cpf, endereco, telefone, login, senha) 
                       VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->execute(['Ana Souza', 'ana.souza@exemplo.com', '98765432100', 'Rua das Flores, 50', '21987654322', 'ana', criptografarSenha('senha456')]);

// Inserir dados na tabela hospede
$stmt = $pdo->prepare("INSERT INTO hospede (nome, email, cpf, rg, endereco, telefone, login, senha, gastos_totais, gastos_atuais) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->execute(['João Pereira', 'joao.pereira@exemplo.com', '98765432101', 'MG123456', 'Rua Central, 100', '21987654323', 'joao', criptografarSenha('123'), 1000.00, 500.00]);

// Inserir dados na tabela quartos
$stmt = $pdo->prepare("INSERT INTO quartos (nome, num_quarto, tipo, preco_diaria, andar, disponivel) 
                       VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute(['Quarto Luxo', 101, 'Luxo', 500.00, 1, true]);
$stmt->execute(['Quarto Standard', 102, 'Standard', 200.00, 2, true]);

// Inserir dados na tabela servicos
$stmt = $pdo->prepare("INSERT INTO servicos (nome, preco, horario_comeca, horario_termina, disponivel) 
                       VALUES (?, ?, ?, ?, ?)");
$stmt->execute(['Serviço de Quarto', 50.00, '08:00:00', '22:00:00', true]);
$stmt->execute(['Lavanderia', 30.00, '08:00:00', '20:00:00', true]);

// Inserir dados na tabela promocoes_servicos
$stmt = $pdo->prepare("INSERT INTO promocoes_servicos (nome, horario_comeca, horario_termina, preco_promocional) 
                       VALUES (?, ?, ?, ?)");
$stmt->execute(['Promoção de Serviço de Quarto', '08:00:00', '12:00:00', 40.00]);

// Inserir dados na tabela promocoes_quartos
$stmt = $pdo->prepare("INSERT INTO promocoes_quartos (id_quarto, preco_promocional, data_inicio, data_fim, data_criacao, disponivel) 
                       VALUES (?, ?, ?, ?, NOW(), ?)");
$stmt->execute([1, 450.00, '2025-06-01', '2025-06-30', true]);

// Inserir dados na tabela reservas
$stmt = $pdo->prepare("INSERT INTO reservas (id_hos, id_quarto, data_reserva, status_atual) 
                       VALUES (?, ?, ?, ?)");
$stmt->execute([1, 1, '2025-05-12', 'Confirmada']);

// Inserir dados na tabela consumo
$stmt = $pdo->prepare("INSERT INTO consumo (id_hos, id_servico, data, valor) 
                       VALUES (?, ?, ?, ?)");
$stmt->execute([1, 1, '2025-05-12', 50.00]);

// Inserir dados na tabela saloes
$stmt = $pdo->prepare("INSERT INTO saloes (nome, capacidade, preco, descricao, tamanho_m2, disponibilidade) 
                       VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute(['Salão Luxo Primavera', 100, 1200.00, 'Salão elegante com iluminação natural e jardim externo.', 200, 'Disponível']);
$stmt->execute(['Espaço Celebration', 80, 950.00, 'Ideal para festas de aniversário e eventos corporativos.', 180, 'Disponível']);

// Inserir dados na tabela reservas_salao
$stmt = $pdo->prepare("INSERT INTO reservas_salao (id_hos, id_salao, data_inicio, data_fim, valor_total, status) 
                       VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([1, 1, '2025-06-01', '2025-06-02', 1200.00, 'Confirmada']);

// Inserir dados na tabela decoracoes
$stmt = $pdo->prepare("INSERT INTO decoracoes (nome, descricao, preco) 
                       VALUES (?, ?, ?)");
$stmt->execute(['Clássico Elegante', 'Decoração com tons neutros, arranjos florais e iluminação suave.', 500.00]);
$stmt->execute(['Festa Tropical', 'Temática colorida com elementos de praia, frutas e folhagens.', 450.00]);

echo "Dados inseridos com sucesso!";
?>
