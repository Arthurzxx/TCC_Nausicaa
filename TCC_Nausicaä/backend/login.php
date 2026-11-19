
<?php
session_start();

require_once "conexao.php";

header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["erro" => "Método não permitido"]);
    exit;
}

$dados = json_decode(file_get_contents("php://input"), true);
$dados = is_array($dados) ? $dados : $_POST;

$email = trim($dados["email"] ?? "");
$senha = $dados["senha"] ?? "";

if ($email === "" || $senha === "") {
    http_response_code(400);
    echo json_encode(["erro" => "Informe e-mail e senha"]);
    exit;
}

try {
    $sql = $pdo->prepare(
        "SELECT id, nome, email, senha
         FROM usuarios
         WHERE email = ?"
    );

    $sql->execute([$email]);
    $usuario = $sql->fetch();

    if (!$usuario || !password_verify($senha, $usuario["senha"])) {
        http_response_code(401);
        echo json_encode(["erro" => "E-mail ou senha incorretos"]);
        exit;
    }

    session_regenerate_id(true);

    $_SESSION["usuario_id"] = $usuario["id"];
    $_SESSION["usuario_nome"] = $usuario["nome"];

    echo json_encode([
        "sucesso" => true,
        "mensagem" => "Login realizado com sucesso!",
        "usuario" => [
            "id" => $usuario["id"],
            "nome" => $usuario["nome"],
            "email" => $usuario["email"]
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["erro" => "Erro ao realizar login"]);
}
