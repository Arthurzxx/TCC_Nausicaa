
-- =====================================
-- BANCO DE DADOS NAUSICAA
-- =====================================

CREATE DATABASE IF NOT EXISTS nausicaa
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE nausicaa;

-- =====================================
-- TABELA DE USUARIOS
-- =====================================

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    tipo ENUM('admin', 'usuario') DEFAULT 'usuario',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================
-- TABELA DE GUERRAS
-- =====================================

CREATE TABLE IF NOT EXISTS guerras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    periodo VARCHAR(100),
    descricao TEXT,
    causas TEXT,
    consequencias TEXT,
    imagem VARCHAR(255)
);

-- =====================================
-- TABELA DE VIDEOS
-- =====================================

CREATE TABLE IF NOT EXISTS videos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    descricao TEXT,
    url VARCHAR(500) NOT NULL,
    id_guerra INT,
    FOREIGN KEY (id_guerra)
        REFERENCES guerras(id)
        ON DELETE SET NULL
);

-- =====================================
-- TABELA DE OBRAS
-- =====================================

CREATE TABLE IF NOT EXISTS obras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    autor VARCHAR(150),
    tipo VARCHAR(50),
    ano INT,
    descricao TEXT,
    imagem VARCHAR(255),
    link VARCHAR(500)
);

-- =====================================
-- TABELA DE QUIZZES
-- =====================================

CREATE TABLE IF NOT EXISTS quizzes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    descricao TEXT,
    id_guerra INT,
    FOREIGN KEY (id_guerra)
        REFERENCES guerras(id)
        ON DELETE SET NULL
);

-- =====================================
-- TABELA DE PERGUNTAS
-- =====================================

CREATE TABLE IF NOT EXISTS perguntas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_quiz INT NOT NULL,
    enunciado TEXT NOT NULL,
    FOREIGN KEY (id_quiz)
        REFERENCES quizzes(id)
        ON DELETE CASCADE
);

-- =====================================
-- TABELA DE ALTERNATIVAS
-- =====================================

CREATE TABLE IF NOT EXISTS alternativas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_pergunta INT NOT NULL,
    texto TEXT NOT NULL,
    correta BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (id_pergunta)
        REFERENCES perguntas(id)
        ON DELETE CASCADE
);

-- =====================================
-- TABELA DE RESULTADOS
-- =====================================

CREATE TABLE IF NOT EXISTS resultados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_quiz INT NOT NULL,
    pontuacao INT NOT NULL,
    total_perguntas INT NOT NULL,
    data_realizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id),
    FOREIGN KEY (id_quiz)
        REFERENCES quizzes(id)
);
