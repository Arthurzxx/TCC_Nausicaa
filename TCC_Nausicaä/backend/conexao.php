<?php

// Dados de acesso ao MySQL (ajuste se o seu servidor for diferente).
// Se o MySQL usa outra porta, troque $host por "localhost;port=3307".
$host = "localhost";
$banco = "nausicaa";
$usuario = "root";
$senha = "";

try {

    $pdo = new PDO(
        "mysql:host=$host;dbname=$banco;charset=utf8mb4",
        $usuario,
        $senha,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

} catch (PDOException $e) {

    error_log("[nausicaa] Falha na conexão: " . $e->getMessage());

    // teste.php quer ver o motivo real
    if (defined("MODO_TESTE")) {
        throw $e;
    }

    // endpoints da API respondem em JSON
    if (defined("API_JSON")) {
        http_response_code(500);
        echo json_encode(["erro" => "Erro ao conectar com o banco de dados."], JSON_UNESCAPED_UNICODE);
        exit;
    }

    die("Erro ao conectar com o banco de dados.");
}