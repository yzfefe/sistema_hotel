<?php
// Conexão com o banco de dados
$pdo = new PDO('mysql:host=localhost;dbname=sistema_hotel', 'root', '');

// Função para criptografar senhas
function criptografarSenha($senha) {
    return password_hash($senha, PASSWORD_DEFAULT);
}

// Inserir dados na tabela administrador
$stmt = $pdo->prepare("INSERT INTO administrador (login, senha, nome) VALUES (?, ?, ?)");
$stmt->execute(['adm_hotel', criptografarSenha('adm_hotel001'), 'Elinardy']);

// Inserir dados na tabela gerente
$gerentes = [
    [
        'nome' => 'Ana Oliveira',
        'email' => 'ana.oliveira@exemplo.com',
        'cpf' => '234.567.890-12',
        'endereco' => 'Rua das Flores, 200',
        'telefone' => '(21) 98765-4322',
        'login' => 'ana',
        'senha' => 'senha456'
    ],
    [
        'nome' => 'Pedro Santos',
        'email' => 'pedro.santos@exemplo.com',
        'cpf' => '345.678.901-23',
        'endereco' => 'Avenida Paulista, 300',
        'telefone' => '(11) 98765-4323',
        'login' => 'pedro',
        'senha' => 'senha789'
    ],
    [
        'nome' => 'Mariana Costa',
        'email' => 'mariana.costa@exemplo.com',
        'cpf' => '456.789.012-34',
        'endereco' => 'Rua dos Pinheiros, 400',
        'telefone' => '(11) 98765-4324',
        'login' => 'mariana',
        'senha' => 'senha101'
    ],
    [
        'nome' => 'Ricardo Almeida',
        'email' => 'ricardo.almeida@exemplo.com',
        'cpf' => '567.890.123-45',
        'endereco' => 'Avenida Rio Branco, 500',
        'telefone' => '(21) 98765-4325',
        'login' => 'ricardo',
        'senha' => 'senha202'
    ]
];

$stmt = $pdo->prepare("INSERT INTO gerente (nome, email, cpf, endereco, telefone, login, senha) 
                       VALUES (?, ?, ?, ?, ?, ?, ?)");

foreach ($gerentes as $gerente) {
    $stmt->execute([
        $gerente['nome'],
        $gerente['email'],
        $gerente['cpf'],
        $gerente['endereco'],
        $gerente['telefone'],
        $gerente['login'],
        criptografarSenha($gerente['senha'])
    ]);
    
    echo "Gerente {$gerente['nome']} adicionado com sucesso.<br>";
}
// Inserir dados na tabela recepcionista
$recepcionistas = [
    [
        'nome' => 'Fernanda Lima',
        'email' => 'fernanda.lima@exemplo.com',
        'cpf' => '111.222.333-44',
        'rg' => '12345678',
        'endereco' => 'Avenida Central, 150',
        'telefone' => '(21) 99887-7665',
        'horario' => '08:00:00',
        'login' => 'fernanda',
        'senha' => 'senha123'
    ],
    [
        'nome' => 'Roberto Alves',
        'email' => 'roberto.alves@exemplo.com',
        'cpf' => '222.333.444-55',
        'rg' => '23456789',
        'endereco' => 'Rua das Acácias, 200',
        'telefone' => '(11) 99776-6554',
        'horario' => '14:00:00',
        'login' => 'roberto',
        'senha' => 'senha456'
    ],
    [
        'nome' => 'Juliana Mendes',
        'email' => 'juliana.mendes@exemplo.com',
        'cpf' => '333.444.555-66',
        'rg' => '34567890',
        'endereco' => 'Travessa dos Coqueiros, 35',
        'telefone' => '(21) 99665-5443',
        'horario' => '10:00:00',
        'login' => 'juliana',
        'senha' => 'senha789'
    ],
    [
        'nome' => 'Lucas Oliveira',
        'email' => 'lucas.oliveira@exemplo.com',
        'cpf' => '444.555.666-77',
        'rg' => '45678901',
        'endereco' => 'Alameda Santos, 1000',
        'telefone' => '(11) 99554-4332',
        'horario' => '16:00:00',
        'login' => 'lucas',
        'senha' => 'senha101'
    ]
];

$stmt = $pdo->prepare("INSERT INTO recepcionista (nome, email, cpf, rg, endereco, telefone, horario, login, senha) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($recepcionistas as $recepcionista) {
    $stmt->execute([
        $recepcionista['nome'],
        $recepcionista['email'],
        $recepcionista['cpf'],
        $recepcionista['rg'],
        $recepcionista['endereco'],
        $recepcionista['telefone'],
        $recepcionista['horario'],
        $recepcionista['login'],
        criptografarSenha($recepcionista['senha'])
    ]);
    
    echo "Recepcionista {$recepcionista['nome']} adicionado(a) com sucesso.<br>";
}

// Inserir dados na tabela hospede
$hospedes = [
    // Hóspedes originais (4)
    [
        'nome' => 'Maria Santos',
        'email' => 'maria.santos@exemplo.com',
        'cpf' => '111.234.333-45',
        'rg' => 'SP987654',
        'endereco' => 'Avenida Paulista, 500',
        'telefone' => '(11) 99887-7664',
        'horario' => '15:00:00',
        'status_atual' => 'ATIVO',
        'login' => 'maria',
        'senha' => 'senha456',
        'gastos_totais' => 2500.50,
        'gastos_atuais' => 0.00
    ],
    [
        'nome' => 'Carlos Oliveira',
        'email' => 'carlos.oliveira@exemplo.com',
        'cpf' => '222.333.444-56',
        'rg' => 'RJ456789',
        'endereco' => 'Rua do Catete, 200',
        'telefone' => '(21) 99776-6553',
        'horario' => '10:30:00',
        'status_atual' => 'ATIVO',
        'login' => 'carlos',
        'senha' => 'senha789',
        'gastos_totais' => 1800.00,
        'gastos_atuais' => 0.00
    ],
    [
        'nome' => 'Ana Beatriz',
        'email' => 'ana.beatriz@exemplo.com',
        'cpf' => '333.444.555-67',
        'rg' => 'MG654321',
        'endereco' => 'Alameda Santos, 300',
        'telefone' => '(11) 99665-5442',
        'horario' => '09:15:00',
        'status_atual' => 'ATIVO',
        'login' => 'anabeatriz',
        'senha' => 'senha101',
        'gastos_totais' => 3200.75,
        'gastos_atuais' => 0.00
    ],
    [
        'nome' => 'Pedro Henrique',
        'email' => 'pedro.henrique@exemplo.com',
        'cpf' => '444.555.666-78',
        'rg' => 'RS789123',
        'endereco' => 'Rua da Praia, 150',
        'telefone' => '(51) 99554-4331',
        'horario' => '14:45:00',
        'status_atual' => 'ATIVO',
        'login' => 'pedroh',
        'senha' => 'senha202',
        'gastos_totais' => 1500.00,
        'gastos_atuais' => 0.00
    ],
    // Novos hóspedes (20)
    [
        'nome' => 'Fernanda Costa',
        'email' => 'fernanda.costa@exemplo.com',
        'cpf' => '555.666.777-89',
        'rg' => 'SP123456',
        'endereco' => 'Rua Augusta, 1000',
        'telefone' => '(11) 98765-4321',
        'horario' => '11:30:00',
        'status_atual' => 'ATIVO',
        'login' => 'fernanda',
        'senha' => 'senha303',
        'gastos_totais' => 4200.00,
        'gastos_atuais' => 0.00
    ],
    [
        'nome' => 'Ricardo Almeida',
        'email' => 'ricardo.almeida@exemplo.com',
        'cpf' => '666.777.888-90',
        'rg' => 'RJ654321',
        'endereco' => 'Avenida Atlântica, 200',
        'telefone' => '(21) 97654-3210',
        'horario' => '16:20:00',
        'status_atual' => 'ATIVO',
        'login' => 'ricardo',
        'senha' => 'senha404',
        'gastos_totais' => 3800.25,
        'gastos_atuais' => 0.00
    ],
    [
        'nome' => 'Juliana Pereira',
        'email' => 'juliana.pereira@exemplo.com',
        'cpf' => '777.888.999-01',
        'rg' => 'MG789012',
        'endereco' => 'Rua da Bahia, 300',
        'telefone' => '(31) 96543-2109',
        'horario' => '09:45:00',
        'status_atual' => 'ATIVO',
        'login' => 'juliana',
        'senha' => 'senha505',
        'gastos_totais' => 2900.50,
        'gastos_atuais' => 0.00
    ],
    [
        'nome' => 'Lucas Mendes',
        'email' => 'lucas.mendes@exemplo.com',
        'cpf' => '888.999.000-12',
        'rg' => 'RS456789',
        'endereco' => 'Avenida Borges de Medeiros, 400',
        'telefone' => '(51) 95432-1098',
        'horario' => '13:15:00',
        'status_atual' => 'ATIVO',
        'login' => 'lucas',
        'senha' => 'senha606',
        'gastos_totais' => 5100.00,
        'gastos_atuais' => 0.00
    ],
    [
        'nome' => 'Amanda Rocha',
        'email' => 'amanda.rocha@exemplo.com',
        'cpf' => '999.000.111-23',
        'rg' => 'PR567890',
        'endereco' => 'Rua XV de Novembro, 500',
        'telefone' => '(41) 94321-0987',
        'horario' => '10:00:00',
        'status_atual' => 'ATIVO',
        'login' => 'amanda',
        'senha' => 'senha707',
        'gastos_totais' => 2300.75,
        'gastos_atuais' => 0.00
    ],
    [
        'nome' => 'Gustavo Lima',
        'email' => 'gustavo.lima@exemplo.com',
        'cpf' => '000.111.222-34',
        'rg' => 'SC678901',
        'endereco' => 'Rua Felipe Schmidt, 600',
        'telefone' => '(48) 93210-9876',
        'horario' => '14:30:00',
        'status_atual' => 'ATIVO',
        'login' => 'gustavo',
        'senha' => 'senha808',
        'gastos_totais' => 4700.50,
        'gastos_atuais' => 0.00
    ],
    [
        'nome' => 'Patrícia Souza',
        'email' => 'patricia.souza@exemplo.com',
        'cpf' => '111.222.333-45',
        'rg' => 'ES789012',
        'endereco' => 'Avenida Vitória, 700',
        'telefone' => '(27) 92109-8765',
        'horario' => '08:45:00',
        'status_atual' => 'ATIVO',
        'login' => 'patricia',
        'senha' => 'senha909',
        'gastos_totais' => 3200.00,
        'gastos_atuais' => 0.00
    ],
    [
        'nome' => 'Marcos Vinicius',
        'email' => 'marcos.vinicius@exemplo.com',
        'cpf' => '222.333.487-56',
        'rg' => 'BA890123',
        'endereco' => 'Avenida Sete de Setembro, 800',
        'telefone' => '(71) 91098-7654',
        'horario' => '17:00:00',
        'status_atual' => 'ATIVO',
        'login' => 'marcosv',
        'senha' => 'senha1010',
        'gastos_totais' => 4100.25,
        'gastos_atuais' => 0.00
    ],
    [
        'nome' => 'Camila Castro',
        'email' => 'camila.castro@exemplo.com',
        'cpf' => '433.444.555-67',
        'rg' => 'PE901234',
        'endereco' => 'Rua do Sol, 900',
        'telefone' => '(81) 90987-6543',
        'horario' => '12:30:00',
        'status_atual' => 'ATIVO',
        'login' => 'camila',
        'senha' => 'senha1111',
        'gastos_totais' => 2800.75,
        'gastos_atuais' => 0.00
    ],
    [
        'nome' => 'Rafael Santos',
        'email' => 'rafael.santos@exemplo.com',
        'cpf' => '444.500.666-78',
        'rg' => 'CE012345',
        'endereco' => 'Avenida Beira Mar, 1000',
        'telefone' => '(85) 89876-5432',
        'horario' => '19:15:00',
        'status_atual' => 'ATIVO',
        'login' => 'rafael',
        'senha' => 'senha1212',
        'gastos_totais' => 3900.00,
        'gastos_atuais' => 0.00
    ],
    [
        'nome' => 'Isabela Fernandes',
        'email' => 'isabela.fernandes@exemplo.com',
        'cpf' => '543.666.777-89',
        'rg' => 'DF123456',
        'endereco' => 'Quadra 100, Conjunto 200',
        'telefone' => '(61) 88765-4321',
        'horario' => '08:00:00',
        'status_atual' => 'ATIVO',
        'login' => 'isabela',
        'senha' => 'senha1313',
        'gastos_totais' => 3500.50,
        'gastos_atuais' => 0.00
    ],
    [
        'nome' => 'Daniel Oliveira',
        'email' => 'daniel.oliveira@exemplo.com',
        'cpf' => '642.777.888-90',
        'rg' => 'GO234567',
        'endereco' => 'Avenida Goiás, 1100',
        'telefone' => '(62) 87654-3210',
        'horario' => '15:45:00',
        'status_atual' => 'ATIVO',
        'login' => 'daniel',
        'senha' => 'senha1414',
        'gastos_totais' => 4400.00,
        'gastos_atuais' => 0.00
    ],
    [
        'nome' => 'Larissa Martins',
        'email' => 'larissa.martins@exemplo.com',
        'cpf' => '777.438.999-01',
        'rg' => 'MT345678',
        'endereco' => 'Rua das Palmeiras, 1200',
        'telefone' => '(65) 86543-2109',
        'horario' => '11:00:00',
        'status_atual' => 'ATIVO',
        'login' => 'larissa',
        'senha' => 'senha1515',
        'gastos_totais' => 3100.75,
        'gastos_atuais' => 0.00
    ],
    [
        'nome' => 'Thiago Silva',
        'email' => 'thiago.silva@exemplo.com',
        'cpf' => '318.999.000-12',
        'rg' => 'MS456789',
        'endereco' => 'Rua 14 de Julho, 1300',
        'telefone' => '(67) 85432-1098',
        'horario' => '18:30:00',
        'status_atual' => 'ATIVO',
        'login' => 'thiago',
        'senha' => 'senha1616',
        'gastos_totais' => 4800.25,
        'gastos_atuais' => 0.00
    ],
    [
        'nome' => 'Vanessa Costa',
        'email' => 'vanessa.costa@exemplo.com',
        'cpf' => '999.043.111-23',
        'rg' => 'PA567890',
        'endereco' => 'Travessa Quintino Bocaiúva, 1400',
        'telefone' => '(91) 84321-0987',
        'horario' => '09:30:00',
        'status_atual' => 'ATIVO',
        'login' => 'vanessa',
        'senha' => 'senha1717',
        'gastos_totais' => 2700.50,
        'gastos_atuais' => 0.00
    ],
    [
        'nome' => 'Eduardo Rocha',
        'email' => 'eduardo.rocha@exemplo.com',
        'cpf' => '000.142.222-34',
        'rg' => 'AM678901',
        'endereco' => 'Avenida Eduardo Ribeiro, 1500',
        'telefone' => '(92) 83210-9876',
        'horario' => '13:45:00',
        'status_atual' => 'ATIVO',
        'login' => 'eduardo',
        'senha' => 'senha1818',
        'gastos_totais' => 3600.00,
        'gastos_atuais' => 0.00
    ],
    [
        'nome' => 'Beatriz Alves',
        'email' => 'beatriz.alves@exemplo.com',
        'cpf' => '111.225.351-45',
        'rg' => 'AC789012',
        'endereco' => 'Rua Benjamin Constant, 1600',
        'telefone' => '(68) 82109-8765',
        'horario' => '10:15:00',
        'status_atual' => 'ATIVO',
        'login' => 'beatriz',
        'senha' => 'senha1919',
        'gastos_totais' => 2900.75,
        'gastos_atuais' => 0.00
    ],
    [
        'nome' => 'Roberto Nunes',
        'email' => 'roberto.nunes@exemplo.com',
        'cpf' => '251.333.444-56',
        'rg' => 'RR890123',
        'endereco' => 'Avenida Capitão Ene Garcez, 1700',
        'telefone' => '(95) 81098-7654',
        'horario' => '16:00:00',
        'status_atual' => 'ATIVO',
        'login' => 'roberto',
        'senha' => 'senha2020',
        'gastos_totais' => 4300.50,
        'gastos_atuais' => 0.00
    ],
    [
        'nome' => 'Tatiane Pereira',
        'email' => 'tatiane.pereira@exemplo.com',
        'cpf' => '333.455.555-67',
        'rg' => 'AP901234',
        'endereco' => 'Avenida FAB, 1800',
        'telefone' => '(96) 80987-6543',
        'horario' => '14:00:00',
        'status_atual' => 'ATIVO',
        'login' => 'tatiane',
        'senha' => 'senha2121',
        'gastos_totais' => 3800.25,
        'gastos_atuais' => 0.00
    ],
    [
        'nome' => 'Felipe Cardoso',
        'email' => 'felipe.cardoso@exemplo.com',
        'cpf' => '442.525.661-73',
        'rg' => 'TO012345',
        'endereco' => 'Quadra 101 Norte, 1900',
        'telefone' => '(63) 79876-5432',
        'horario' => '12:00:00',
        'status_atual' => 'ATIVO',
        'login' => 'felipe',
        'senha' => 'senha2222',
        'gastos_totais' => 4100.00,
        'gastos_atuais' => 0.00
    ],
    [
        'nome' => 'Gabriela Lima',
        'email' => 'gabriela.lima@exemplo.com',
        'cpf' => '451.662.712-89',
        'rg' => 'RO123456',
        'endereco' => 'Rua José de Alencar, 2000',
        'telefone' => '(69) 78765-4321',
        'horario' => '17:30:00',
        'status_atual' => 'ATIVO',
        'login' => 'gabriela',
        'senha' => 'senha2323',
        'gastos_totais' => 3300.75,
        'gastos_atuais' => 0.00
    ],
    [
        'nome' => 'Leonardo Souza',
        'email' => 'leonardo.souza@exemplo.com',
        'cpf' => '661.737.848-95',
        'rg' => 'SE234567',
        'endereco' => 'Rua Laranjeiras, 2100',
        'telefone' => '(79) 77654-3210',
        'horario' => '08:30:00',
        'status_atual' => 'ATIVO',
        'login' => 'leonardo',
        'senha' => 'senha2424',
        'gastos_totais' => 4700.50,
        'gastos_atuais' => 0.00
    ]
];

$stmt = $pdo->prepare("INSERT INTO hospede (nome, email, cpf, rg, endereco, telefone, horario, status_atual, login, senha, gastos_totais, gastos_atuais) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($hospedes as $hospede) {
    $stmt->execute([
        $hospede['nome'],
        $hospede['email'],
        $hospede['cpf'],
        $hospede['rg'],
        $hospede['endereco'],
        $hospede['telefone'],
        $hospede['horario'],
        $hospede['status_atual'],
        $hospede['login'],
        criptografarSenha($hospede['senha']),
        $hospede['gastos_totais'],
        $hospede['gastos_atuais']
    ]);
    
    echo "Hóspede {$hospede['nome']} adicionado(a) com sucesso.<br>";
}

// Inserir dados na tabela quartos
// Tipos de quarto e seus respectivos preços
$tiposQuartos = [
    'Standard' => 200.00,
    'Superior' => 300.00,
    'Luxo' => 500.00,
    'Presidencial' => 800.00
];

$stmt = $pdo->prepare("INSERT INTO quartos (nome, num_quarto, tipo, preco_diaria, andar, disponivel) 
                       VALUES (?, ?, ?, ?, ?, ?)");

// Inserir os 160 quartos (10 andares × 16 quartos por andar)
for ($andar = 1; $andar <= 9; $andar++) {
    for ($numero = 1; $numero <= 16; $numero++) {
        // Define o tipo do quarto baseado no andar e posição
        if ($andar <= 3) {
            // Andares 1-3: maioria Standard
            $tipo = ($numero <= 12) ? 'Standard' : (($numero <= 15) ? 'Superior' : 'Luxo');
        } elseif ($andar <= 6) {
            // Andares 4-6: maioria Superior
            $tipo = ($numero <= 8) ? 'Standard' : (($numero <= 14) ? 'Superior' : 'Luxo');
        } elseif ($andar <= 9) {
            // Andares 7-9: maioria Luxo
            $tipo = ($numero <= 4) ? 'Superior' : (($numero <= 14) ? 'Luxo' : 'Presidencial');
        } else {
            // Andar 10: todos Presidencial
            $tipo = 'Presidencial';
        }
        
        $preco = $tiposQuartos[$tipo];
        $nome = "Quarto " . $tipo . " " . $andar . "-" . $numero;
        
        $stmt->execute([
            $nome,
            $numero, // Número do quarto (1-16 em cada andar)
            $tipo,
            $preco,
            $andar,
            true // Todos disponíveis inicialmente
        ]);
        
        echo "Quarto {$andar}-{$numero} ({$tipo}) adicionado com sucesso.<br>";
    }
}

// Inserir dados na tabela servicos
$servicos = [
    [
        'nome' => 'Café da Manhã no Quarto',
        'preco' => 35.50,
        'horario_comeca' => '06:00:00',
        'horario_termina' => '10:30:00',
        'disponivel' => true
    ],
    [
        'nome' => 'Massagem Relaxante',
        'preco' => 120.00,
        'horario_comeca' => '09:00:00',
        'horario_termina' => '21:00:00',
        'disponivel' => true
    ],
    [
        'nome' => 'Limpeza Extra',
        'preco' => 45.00,
        'horario_comeca' => '08:00:00',
        'horario_termina' => '18:00:00',
        'disponivel' => true
    ],
    [
        'nome' => 'Serviço de Babá',
        'preco' => 80.00,
        'horario_comeca' => '07:00:00',
        'horario_termina' => '23:00:00',
        'disponivel' => true
    ],
    [
        'nome' => 'Entrega de Comidas Especiais',
        'preco' => 25.00,
        'horario_comeca' => '11:00:00',
        'horario_termina' => '23:00:00',
        'disponivel' => true
    ],
    [
        'nome' => 'Serviço de Despertar',
        'preco' => 10.00,
        'horario_comeca' => '05:00:00',
        'horario_termina' => '12:00:00',
        'disponivel' => true
    ],
    [
        'nome' => 'Decoração Especial (aniversários)',
        'preco' => 150.00,
        'horario_comeca' => '08:00:00',
        'horario_termina' => '20:00:00',
        'disponivel' => true
    ],
    [
        'nome' => 'Serviço de Concierge VIP',
        'preco' => 200.00,
        'horario_comeca' => '07:00:00',
        'horario_termina' => '22:00:00',
        'disponivel' => true
    ],
    [
        'nome' => 'Entrega de Flores',
        'preco' => 75.00,
        'horario_comeca' => '09:00:00',
        'horario_termina' => '19:00:00',
        'disponivel' => true
    ],
    [
        'nome' => 'Serviço de Pet (cuidados com animais)',
        'preco' => 60.00,
        'horario_comeca' => '08:00:00',
        'horario_termina' => '20:00:00',
        'disponivel' => true
    ]
];

$stmt = $pdo->prepare("INSERT INTO servicos (nome, preco, horario_comeca, horario_termina, disponivel) 
                       VALUES (?, ?, ?, ?, ?)");

foreach ($servicos as $servico) {
    $stmt->execute([
        $servico['nome'],
        $servico['preco'],
        $servico['horario_comeca'],
        $servico['horario_termina'],
        $servico['disponivel']
    ]);
    
    echo "Serviço '{$servico['nome']}' adicionado com sucesso.<br>";
}

$promocoes = [
    [
        'nome' => 'Happy Hour de Massagens',
        'horario_comeca' => '14:00:00',
        'horario_termina' => '17:00:00',
        'disponivel' => true,
        'preco_promocional' => 89.90
    ],
    [
        'nome' => 'Café da Manhã Tarde',
        'horario_comeca' => '09:30:00',
        'horario_termina' => '11:00:00',
        'disponivel' => true,
        'preco_promocional' => 25.00
    ],
    [
        'nome' => 'Noite do Pet',
        'horario_comeca' => '18:00:00',
        'horario_termina' => '22:00:00',
        'disponivel' => true,
        'preco_promocional' => 45.00
    ],
    [
        'nome' => 'Lavanderia Express',
        'horario_comeca' => '08:00:00',
        'horario_termina' => '10:00:00',
        'disponivel' => true,
        'preco_promocional' => 20.00
    ],
    [
        'nome' => 'Despertar Premium',
        'horario_comeca' => '05:00:00',
        'horario_termina' => '07:00:00',
        'disponivel' => true,
        'preco_promocional' => 5.00
    ],
    [
        'nome' => 'Concierge Econômico',
        'horario_comeca' => '13:00:00',
        'horario_termina' => '15:00:00',
        'disponivel' => true,
        'preco_promocional' => 150.00
    ],
    [
        'nome' => 'Decoração Relâmpago',
        'horario_comeca' => '12:00:00',
        'horario_termina' => '14:00:00',
        'disponivel' => true,
        'preco_promocional' => 110.00
    ],
    [
        'nome' => 'Babá Noturna',
        'horario_comeca' => '20:00:00',
        'horario_termina' => '23:00:00',
        'disponivel' => true,
        'preco_promocional' => 65.00
    ],
    [
        'nome' => 'Flores da Manhã',
        'horario_comeca' => '09:00:00',
        'horario_termina' => '11:00:00',
        'disponivel' => true,
        'preco_promocional' => 55.00
    ],
    [
        'nome' => 'Limpeza Flash',
        'horario_comeca' => '10:00:00',
        'horario_termina' => '12:00:00',
        'disponivel' => true,
        'preco_promocional' => 35.00
    ]
];

$stmt = $pdo->prepare("INSERT INTO promocoes_servicos (nome, horario_comeca, horario_termina, disponivel, preco_promocional) 
                       VALUES (?, ?, ?, ?, ?)");

foreach ($promocoes as $promocao) {
    $stmt->execute([
        $promocao['nome'],
        $promocao['horario_comeca'],
        $promocao['horario_termina'],
        $promocao['disponivel'],
        $promocao['preco_promocional']
    ]);
    
    echo "Promoção '{$promocao['nome']}' adicionada com sucesso.<br>";
}

// // Inserir dados na tabela promocoes_quartos
$promocoesQuartos = [
    // Promoções para quartos Standard
    [
        'id_quarto' => 3,  // Quarto Standard
        'preco_promocional' => 150.00,
        'data_inicio' => '2025-06-01',
        'data_fim' => '2025-06-30',
        'disponivel' => true
    ],
    [
        'id_quarto' => 7,  // Quarto Standard
        'preco_promocional' => 160.00,
        'data_inicio' => '2025-07-15',
        'data_fim' => '2025-08-15',
        'disponivel' => true
    ],
    // Promoções para quartos Superior
    [
        'id_quarto' => 24,  // Quarto Superior
        'preco_promocional' => 250.00,
        'data_inicio' => '2025-06-10',
        'data_fim' => '2025-07-10',
        'disponivel' => true
    ],
    [
        'id_quarto' => 35,  // Quarto Superior
        'preco_promocional' => 230.00,
        'data_inicio' => '2025-09-01',
        'data_fim' => '2025-09-30',
        'disponivel' => true
    ],

    // Promoções para quartos Luxo
    [
        'id_quarto' => 52,  // Quarto Luxo
        'preco_promocional' => 400.00,
        'data_inicio' => '2025-07-01',
        'data_fim' => '2025-07-31',
        'disponivel' => true
    ],
    [
        'id_quarto' => 68,  // Quarto Luxo
        'preco_promocional' => 420.00,
        'data_inicio' => '2025-11-15',
        'data_fim' => '2025-12-15',
        'disponivel' => true
    ],

    // Promoções para quartos Presidencial
    [
        'id_quarto' => 142,  // Quarto Presidencial
        'preco_promocional' => 650.00,
        'data_inicio' => '2025-08-20',
        'data_fim' => '2025-09-10',
        'disponivel' => true
    ],
    [
        'id_quarto' => 122,  // Quarto Presidencial
        'preco_promocional' => 700.00,
        'data_inicio' => '2025-10-01',
        'data_fim' => '2025-10-31',
        'disponivel' => true
    ],

    // Promoções para períodos específicos
    [
        'id_quarto' => 12,  // Quarto Standard
        'preco_promocional' => 120.00,
        'data_inicio' => '2025-12-01',
        'data_fim' => '2025-12-26',
        'disponivel' => true
    ],
    [
        'id_quarto' => 89,  // Quarto Luxo
        'preco_promocional' => 350.00,
        'data_inicio' => '2026-01-05',
        'data_fim' => '2026-02-05',
        'disponivel' => true
    ]
];
$stmt = $pdo->prepare("INSERT INTO promocoes_quartos 
                      (id_quarto, preco_promocional, data_inicio, data_fim, data_criacao, disponivel) 
                      VALUES (?, ?, ?, ?, NOW(), ?)");
foreach ($promocoesQuartos as $promocao) {
    $stmt->execute([
        $promocao['id_quarto'],
        $promocao['preco_promocional'],
        $promocao['data_inicio'],
        $promocao['data_fim'],
        $promocao['disponivel']
    ]);
    echo "Promoção para quarto ID {$promocao['id_quarto']} adicionada com sucesso (R$ {$promocao['preco_promocional']}).<br>";
}
// Array de reservas encerradas
$reservasEncerradas = [
    [
        'id_hos' => 1,  // Maria Santos
        'id_quarto' => 3,  // Quarto Standard
        'data_reserva' => '2024-01-15',
        'data_encerrada' => '2024-01-20',
        'status_atual' => 'ENCERRADA'
    ],
    [
        'id_hos' => 3,  // Ana Beatriz
        'id_quarto' => 24,  // Quarto Superior
        'data_reserva' => '2024-02-10',
        'data_encerrada' => '2024-02-15',
        'status_atual' => 'ENCERRADA'
    ],
    [
        'id_hos' => 5,  // Fernanda Costa
        'id_quarto' => 52,  // Quarto Luxo
        'data_reserva' => '2024-03-05',
        'data_encerrada' => '2024-03-12',
        'status_atual' => 'ENCERRADA'
    ],
    [
        'id_hos' => 7,  // Juliana Pereira
        'id_quarto' => 68,  // Quarto Luxo
        'data_reserva' => '2024-04-20',
        'data_encerrada' => '2024-04-25',
        'status_atual' => 'ENCERRADA'
    ],
    [
        'id_hos' => 9,  // Amanda Rocha
        'id_quarto' => 89,  // Quarto Luxo
        'data_reserva' => '2024-05-12',
        'data_encerrada' => '2024-05-18',
        'status_atual' => 'ENCERRADA'
    ],
    [
        'id_hos' => 11, // Marcos Vinicius
        'id_quarto' => 142, // Quarto Presidencial
        'data_reserva' => '2024-06-01',
        'data_encerrada' => '2024-06-10',
        'status_atual' => 'ENCERRADA'
    ],
    [
        'id_hos' => 13, // Daniel Oliveira
        'id_quarto' => 35, // Quarto Superior
        'data_reserva' => '2024-07-15',
        'data_encerrada' => '2024-07-20',
        'status_atual' => 'ENCERRADA'
    ],
    [
        'id_hos' => 15, // Thiago Silva
        'id_quarto' => 144, // Quarto Presidencial
        'data_reserva' => '2024-08-05',
        'data_encerrada' => '2024-08-15',
        'status_atual' => 'ENCERRADA'
    ],
    [
        'id_hos' => 17, // Eduardo Rocha
        'id_quarto' => 7, // Quarto Standard
        'data_reserva' => '2024-09-10',
        'data_encerrada' => '2024-09-17',
        'status_atual' => 'ENCERRADA'
    ],
    [
        'id_hos' => 19, // Roberto Nunes
        'id_quarto' => 12, // Quarto Standard
        'data_reserva' => '2024-10-22',
        'data_encerrada' => '2024-10-29',
        'status_atual' => 'ENCERRADA'
    ]
];

$stmt = $pdo->prepare("INSERT INTO reservas_quarto
                      (id_hos, id_quarto, data_reserva, data_encerrada, status) 
                      VALUES (?, ?, ?, ?, ?)");

foreach ($reservasEncerradas as $reserva) {
    $stmt->execute([
        $reserva['id_hos'],
        $reserva['id_quarto'],
        $reserva['data_reserva'],
        $reserva['data_encerrada'],
        $reserva['status_atual']
    ]);
    
    echo "Reserva encerrada para hóspede ID {$reserva['id_hos']} no quarto ID {$reserva['id_quarto']} adicionada com sucesso.<br>";
}
?>