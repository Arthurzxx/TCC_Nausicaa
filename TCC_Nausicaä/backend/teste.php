<?php

// Página de diagnóstico: abra http://localhost/.../backend/teste.php
// Mostra se o PHP, o MySQL e as tabelas estão certos.
// Apague ou proteja este arquivo antes de publicar o site.

define("MODO_TESTE", true);

$erroConexao = null;

try {
    require_once "conexao.php";
} catch (PDOException $e) {
    $erroConexao = $e;
}

// tabela => colunas que o back-end espera
$esperado = [
    "usuarios" => ["id", "nome", "email", "senha", "tipo"],
    "guerras" => ["id", "codigo", "nome", "titulo", "nucleo", "secs", "periodo", "descricao", "causas", "consequencias", "corpo", "fontes", "materiais", "imagem"],
    "videos" => ["id", "titulo", "descricao", "url", "id_guerra"],
    "obras" => ["id", "codigo", "categoria", "titulo", "autor", "tipo", "ano", "descricao", "imagem", "link"],
    "quizzes" => ["id", "titulo", "descricao", "id_guerra"],
    "perguntas" => ["id", "id_quiz", "enunciado", "explicacao", "tema"],
    "alternativas" => ["id", "id_pergunta", "texto", "correta"],
    "resultados" => ["id", "id_usuario", "id_quiz", "pontuacao", "total_perguntas", "data_realizacao"],
];

function h($s)
{
    return htmlspecialchars((string) $s, ENT_QUOTES, "UTF-8");
}

$local = in_array($_SERVER["REMOTE_ADDR"] ?? "", ["127.0.0.1", "::1"], true);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Teste · Nausicaä</title>
<style>
    body { font-family: system-ui, sans-serif; max-width: 760px; margin: 32px auto; padding: 0 16px; line-height: 1.5; }
    .ok { color: #0a7a33; } .falha { color: #b00020; } .aviso { color: #9a6700; }
    li { margin: 2px 0; } code { background: #eee; padding: 1px 5px; border-radius: 4px; }
</style>
</head>
<body>

<h1>NAUSICAA</h1>

<ul>
    <li class="<?= version_compare(PHP_VERSION, "7.3.0", ">=") ? "ok" : "falha" ?>">
        PHP <?= h(PHP_VERSION) ?> <?= version_compare(PHP_VERSION, "7.3.0", ">=") ? "" : "(precisa ser 7.3 ou superior)" ?>
    </li>
    <li class="<?= in_array("mysql", PDO::getAvailableDrivers(), true) ? "ok" : "falha" ?>">
        Extensão pdo_mysql: <?= in_array("mysql", PDO::getAvailableDrivers(), true) ? "ativa" : "NÃO ativa (habilite extension=pdo_mysql no php.ini)" ?>
    </li>
</ul>

<?php if ($erroConexao): ?>

    <p class="falha"><strong>Não foi possível conectar ao banco de dados.</strong></p>

    <?php
    $codigo = (int) $erroConexao->getCode();
    $msg = $erroConexao->getMessage();
    if ($codigo === 1049 || stripos($msg, "Unknown database") !== false) {
        $dica = "O banco \"nausicaa\" não existe. Importe sql/database.sql (instalação nova).";
    } elseif ($codigo === 1045 || stripos($msg, "Access denied") !== false) {
        $dica = "Usuário ou senha do MySQL incorretos. Confira \$usuario e \$senha em conexao.php.";
    } elseif ($codigo === 2002 || stripos($msg, "refused") !== false || stripos($msg, "No such file") !== false) {
        $dica = "O MySQL não está respondendo. Ligue o MySQL no XAMPP/WAMP e confira a porta (3306 ou 3307).";
    } elseif (stripos($msg, "could not find driver") !== false) {
        $dica = "Habilite a extensão pdo_mysql no php.ini e reinicie o Apache.";
    } else {
        $dica = "Veja a mensagem abaixo.";
    }
    ?>
    <p class="aviso"><?= h($dica) ?></p>

    <?php if ($local): ?>
        <p>Mensagem do servidor: <code><?= h($msg) ?></code></p>
    <?php else: ?>
        <p>Os detalhes aparecem só quando a página é aberta no próprio computador do servidor.</p>
    <?php endif; ?>

<?php else: ?>

    <p class="ok">Conexão com o banco de dados realizada com sucesso!</p>

    <h2>Tabelas</h2>
    <ul>
    <?php
    $precisaMigrar = false;
    $semTabela = false;

    foreach ($esperado as $tabela => $colunas) {

        $st = $pdo->prepare(
            "SELECT COLUMN_NAME FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?"
        );
        $st->execute([$tabela]);
        $existentes = array_column($st->fetchAll(), "COLUMN_NAME");

        if (!$existentes) {
            $semTabela = true;
            echo '<li class="falha">' . h($tabela) . ": tabela não existe</li>";
            continue;
        }

        $faltam = array_diff($colunas, $existentes);

        if ($faltam) {
            $precisaMigrar = true;
            echo '<li class="falha">' . h($tabela) . ": faltam colunas → " . h(implode(", ", $faltam)) . "</li>";
            continue;
        }

        $total = (int) $pdo->query("SELECT COUNT(*) FROM `$tabela`")->fetchColumn();
        echo '<li class="ok">' . h($tabela) . ": ok (" . $total . " registro(s))</li>";
    }
    ?>
    </ul>

    <?php if ($semTabela): ?>
        <p class="aviso">Faltam tabelas: importe <code>sql/database.sql</code>.</p>
    <?php endif; ?>
    <?php if ($precisaMigrar): ?>
        <p class="aviso">O banco é de uma versão antiga: importe <code>sql/migracao.sql</code> (não apaga nada).</p>
    <?php endif; ?>
    <?php if (!$semTabela && !$precisaMigrar): ?>
        <p class="ok">Estrutura correta. Se "guerras" estiver com 0 registros, importe <code>sql/dados_iniciais.sql</code>.</p>
        <p>Teste a API: <a href="dados.php">dados.php</a> · <a href="guerras.php">guerras.php</a> · <a href="obras.php">obras.php</a> · <a href="quizzes.php">quizzes.php</a></p>
    <?php endif; ?>

<?php endif; ?>

</body>
</html>