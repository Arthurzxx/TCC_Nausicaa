
<?php
require_once "conexao.php";

header("Content-Type: application/json; charset=utf-8");

try {

    // =========================
    // READ - GET
    // =========================
    if ($_SERVER["REQUEST_METHOD"] === "GET") {

        if (isset($_GET["id"])) {

            $sql = $pdo->prepare(
                "SELECT * FROM guerra WHERE id = ?"
            );

            $sql->execute([(int) $_GET["id"]]);

            $guerra = $sql->fetch(PDO::FETCH_ASSOC);

            if (!$guerra) {
                http_response_code(404);
                echo json_encode([
                    "erro" => "Guerra não encontrada"
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }

            echo json_encode(
                $guerra,
                JSON_UNESCAPED_UNICODE
            );
            exit;
        }

        $sql = $pdo->query(
            "SELECT * FROM guerra ORDER BY id DESC"
        );

        echo json_encode(
            $sql->fetchAll(PDO::FETCH_ASSOC),
            JSON_UNESCAPED_UNICODE
        );
        exit;
    }


    // =========================
    // CREATE - POST
    // =========================
    if ($_SERVER["REQUEST_METHOD"] === "POST") {

        $dados = json_decode(
            file_get_contents("php://input"),
            true
        );

        $dados = is_array($dados) ? $dados : $_POST;

        $nome = trim($dados["nome"] ?? "");

        if ($nome === "") {
            http_response_code(400);
            echo json_encode([
                "erro" => "O nome da guerra é obrigatório"
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $periodo = $dados["periodo"] ?? null;
        $descricao = $dados["descricao"] ?? null;
        $causas = $dados["causas"] ?? null;
        $consequencias = $dados["consequencias"] ?? null;
        $imagem = $dados["imagem"] ?? null;

        $sql = $pdo->prepare(
            "INSERT INTO guerra
            (nome, periodo, descricao, causas, consequencias, imagem)
            VALUES (?, ?, ?, ?, ?, ?)"
        );

        $sql->execute([
            $nome,
            $periodo,
            $descricao,
            $causas,
            $consequencias,
            $imagem
        ]);

        echo json_encode([
            "sucesso" => true,
            "mensagem" => "Guerra cadastrada com sucesso!",
            "id" => $pdo->lastInsertId()
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }


    // =========================
    // UPDATE - PUT
    // =========================
    if ($_SERVER["REQUEST_METHOD"] === "PUT") {

        if (!isset($_GET["id"]) || (int) $_GET["id"] <= 0) {
            http_response_code(400);
            echo json_encode([
                "erro" => "Informe o ID da guerra para atualizar"
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $id = (int) $_GET["id"];

        $dados = json_decode(
            file_get_contents("php://input"),
            true
        );

        if (!is_array($dados)) {
            http_response_code(400);
            echo json_encode([
                "erro" => "Envie os dados em formato JSON"
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $nome = trim($dados["nome"] ?? "");

        if ($nome === "") {
            http_response_code(400);
            echo json_encode([
                "erro" => "O nome da guerra é obrigatório"
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $periodo = $dados["periodo"] ?? null;
        $descricao = $dados["descricao"] ?? null;
        $causas = $dados["causas"] ?? null;
        $consequencias = $dados["consequencias"] ?? null;
        $imagem = $dados["imagem"] ?? null;

        $sql = $pdo->prepare(
            "UPDATE guerra SET
                nome = ?,
                periodo = ?,
                descricao = ?,
                causas = ?,
                consequencias = ?,
                imagem = ?
            WHERE id = ?"
        );

        $sql->execute([
            $nome,
            $periodo,
            $descricao,
            $causas,
            $consequencias,
            $imagem,
            $id
        ]);

        if ($sql->rowCount() === 0) {
            $verificar = $pdo->prepare(
                "SELECT id FROM guerra WHERE id = ?"
            );
            $verificar->execute([$id]);

            if (!$verificar->fetch()) {
                http_response_code(404);
                echo json_encode([
                    "erro" => "Guerra não encontrada"
                ], JSON_UNESCAPED_UNICODE);
                exit;
            }
        }

        echo json_encode([
            "sucesso" => true,
            "mensagem" => "Guerra atualizada com sucesso!"
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }


    // =========================
    // DELETE - DELETE
    // =========================
    if ($_SERVER["REQUEST_METHOD"] === "DELETE") {

        if (!isset($_GET["id"]) || (int) $_GET["id"] <= 0) {
            http_response_code(400);
            echo json_encode([
                "erro" => "Informe o ID da guerra para excluir"
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $id = (int) $_GET["id"];

        $sql = $pdo->prepare(
            "DELETE FROM guerra WHERE id = ?"
        );

        $sql->execute([$id]);

        if ($sql->rowCount() === 0) {
            http_response_code(404);
            echo json_encode([
                "erro" => "Guerra não encontrada"
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        echo json_encode([
            "sucesso" => true,
            "mensagem" => "Guerra excluída com sucesso!"
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }


    // =========================
    // MÉTODO NÃO PERMITIDO
    // =========================
    http_response_code(405);

    echo json_encode([
        "erro" => "Método não permitido"
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        "erro" => "Erro ao processar guerras"
    ], JSON_UNESCAPED_UNICODE);
}
