
<?php
require_once "conexao.php";

header("Content-Type: application/json; charset=utf-8");

try {

    if ($_SERVER["REQUEST_METHOD"] === "GET") {

        if (isset($_GET["id"])) {
            $id = (int) $_GET["id"];

            $sql = $pdo->prepare(
                "SELECT * FROM quizzes WHERE id = ?"
            );
            $sql->execute([$id]);

            $quiz = $sql->fetch();

            if (!$quiz) {
                http_response_code(404);
                echo json_encode(["erro" => "Quiz não encontrado"]);
                exit;
            }

            $sql = $pdo->prepare(
                "SELECT * FROM perguntas WHERE quiz_id = ?"
            );
            $sql->execute([$id]);

            $quiz["perguntas"] = $sql->fetchAll();

            foreach ($quiz["perguntas"] as &$pergunta) {
                $sqlAlternativas = $pdo->prepare(
                    "SELECT id, texto, correta
                     FROM alternativas
                     WHERE pergunta_id = ?"
                );

                $sqlAlternativas->execute([$pergunta["id"]]);
                $pergunta["alternativas"] = $sqlAlternativas->fetchAll();
            }

            unset($pergunta);

            echo json_encode($quiz, JSON_UNESCAPED_UNICODE);
            exit;
        }

        $sql = $pdo->query(
            "SELECT * FROM quizzes ORDER BY id DESC"
        );

        echo json_encode(
            $sql->fetchAll(),
            JSON_UNESCAPED_UNICODE
        );
        exit;
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST") {

        $dados = json_decode(file_get_contents("php://input"), true);
        $dados = is_array($dados) ? $dados : $_POST;

        $titulo = trim($dados["titulo"] ?? "");
        $descricao = $dados["descricao"] ?? null;
        $perguntas = $dados["perguntas"] ?? [];

        if ($titulo === "") {
            http_response_code(400);
            echo json_encode(["erro" => "Informe o título do quiz"]);
            exit;
        }

        if (!is_array($perguntas)) {
            http_response_code(400);
            echo json_encode(["erro" => "Formato de perguntas inválido"]);
            exit;
        }

        $pdo->beginTransaction();

        $sql = $pdo->prepare(
            "INSERT INTO quizzes (titulo, descricao)
             VALUES (?, ?)"
        );

        $sql->execute([$titulo, $descricao]);

        $quizId = $pdo->lastInsertId();

        foreach ($perguntas as $pergunta) {

            $enunciado = trim($pergunta["enunciado"] ?? "");
            $alternativas = $pergunta["alternativas"] ?? [];

            if ($enunciado === "" || !is_array($alternativas)) {
                throw new Exception("Pergunta ou alternativas inválidas");
            }

            $sql = $pdo->prepare(
                "INSERT INTO perguntas (quiz_id, enunciado)
                 VALUES (?, ?)"
            );

            $sql->execute([$quizId, $enunciado]);

            $perguntaId = $pdo->lastInsertId();

            foreach ($alternativas as $alternativa) {

                $texto = trim($alternativa["texto"] ?? "");
                $correta = !empty($alternativa["correta"]) ? 1 : 0;

                if ($texto === "") {
                    throw new Exception("Alternativa sem texto");
                }

                $sql = $pdo->prepare(
                    "INSERT INTO alternativas
                    (pergunta_id, texto, correta)
                    VALUES (?, ?, ?)"
                );

                $sql->execute([
                    $perguntaId,
                    $texto,
                    $correta
                ]);
            }
        }

        $pdo->commit();

        echo json_encode([
            "sucesso" => true,
            "mensagem" => "Quiz cadastrado com sucesso!",
            "id" => $quizId
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    http_response_code(405);
    echo json_encode(["erro" => "Método não permitido"]);

} catch (Throwable $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);
    echo json_encode(["erro" => "Erro ao processar quiz"]);
}
