create database sistema_hotel;
use sistema_hotel;

create table hospede(
	id_hos int primary key auto_increment,
    nome varchar(150),
    email varchar(200),
    cpf char (11),
    rg varchar(12),
    endereço varchar(250),
    telefone char(11),
    horario time,
    login varchar(50),
    senha varchar (100)
    
);

create table recepcionista (
	id_recep int primary key auto_increment,
    nome varchar(150),
    email varchar(200),
    cpf char (11),
    rg varchar(12),
    endereço varchar(250),
    telefone char(11),
    horario time,
    login varchar(50),
    senha varchar (100)
	
);

create table gerente (
	id_ger int primary key auto_increment,
    nome varchar(150),
    email varchar(200),
    cpf char (11),
    endereço varchar(250),
    telefone char(11),
    login varchar(50),
    senha varchar (100)

);

create table administrador(
	id_adm int primary key auto_increment,
    login varchar(50),
    senha varchar (100)
    
);









