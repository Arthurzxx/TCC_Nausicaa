
<?php
require_once "conexao.php";

header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["erro" => "Método não permitido"]);
    exit;
}

$dados = json_decode(file_get_contents("php://input"), true);
$dados = is_array($dados) ? $dados : $_POST;

$nome = trim($dados["nome"] ?? "");
$email = trim($dados["email"] ?? "");
$senha = $dados["senha"] ?? "";

if ($nome === "" || $email === "" || $senha === "") {
    http_response_code(400);
    echo json_encode(["erro" => "Preencha todos os campos"]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["erro" => "E-mail inválido"]);
    exit;
}

if (strlen($senha) < 8) {
    http_response_code(400);
    echo json_encode(["erro" => "A senha deve ter pelo menos 8 caracteres"]);
    exit;
}

try {
    $verificar = $pdo->prepare(
        "SELECT id FROM usuarios WHERE email = ?"
    );
    $verificar->execute([$email]);

    if ($verificar->fetch()) {
        http_response_code(409);
        echo json_encode(["erro" => "Este e-mail já está cadastrado"]);
        exit;
    }

    $hash = password_hash($senha, PASSWORD_DEFAULT);

    $sql = $pdo->prepare(
        "INSERT INTO usuarios (nome, email, senha)
         VALUES (?, ?, ?)"
    );

    $sql->execute([$nome, $email, $hash]);

    echo json_encode([
        "sucesso" => true,
        "mensagem" => "Usuário cadastrado com sucesso!"
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["erro" => "Erro ao cadastrar usuário"]);
}