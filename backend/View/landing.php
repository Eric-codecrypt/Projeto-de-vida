<?php
require 'C:\Turma2\xampp\htdocs\Projeto-de-vida\config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo "Acesso não autorizado.";
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM landing_pages WHERE user_id = ?");
$stmt->execute([$user_id]);
$landing = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo_principal = $_POST['titulo_principal'] ?? '';
    $subtitulo_principal = $_POST['subtitulo_principal'] ?? '';
    $sobre = $_POST['sobre'] ?? '';
    $educacao = $_POST['educacao'] ?? '';
    $carreira = $_POST['carreira'] ?? '';
    $contato = $_POST['contato'] ?? '';

    if ($landing) {
        $stmt = $pdo->prepare("UPDATE landing_pages SET titulo_principal = ?, subtitulo_principal = ?, sobre = ?, educacao = ?, carreira = ?, contato = ? WHERE user_id = ?");
        $stmt->execute([$titulo_principal, $subtitulo_principal, $sobre, $educacao, $carreira, $contato, $user_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO landing_pages (user_id, titulo_principal, subtitulo_principal, sobre, educacao, carreira, contato) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $titulo_principal, $subtitulo_principal, $sobre, $educacao, $carreira, $contato]);
    }

    header("Location: user.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Meu Currículo</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: auto; padding: 20px; background-color: #f8f8f8; color: #333; }
        h1, h2 { color: #2c3e50; }
        .section { margin-bottom: 30px; padding-bottom: 15px; border-bottom: 1px solid #ccc; }
        .subtitulo { font-style: italic; color: #555; }
        .contato { background-color: #ecf0f1; padding: 10px; border-radius: 5px; }
        .topo { text-align: center; margin-bottom: 40px; }
    </style>
</head>
<body>

<?php if ($landing): ?>
    <div class="topo">
        <h1><?= htmlspecialchars($landing['titulo_principal'] ?? 'Meu Currículo') ?></h1>
        <p class="subtitulo"><?= htmlspecialchars($landing['subtitulo_principal'] ?? '') ?></p>
    </div>

    <div class="section"><h2>Sobre Mim</h2><p><?= nl2br(htmlspecialchars($landing['sobre'] ?? '')) ?></p></div>
    <div class="section"><h2>Educação</h2><p><?= nl2br(htmlspecialchars($landing['educacao'] ?? '')) ?></p></div>
    <div class="section"><h2>Carreira</h2><p><?= nl2br(htmlspecialchars($landing['carreira'] ?? '')) ?></p></div>
    <div class="section"><h2>Contato</h2><div class="contato"><?= nl2br(htmlspecialchars($landing['contato'] ?? '')) ?></div></div>
<?php else: ?>
    <h2>Editar Minha Landing Page</h2>
    <form method="POST">
        <label>Título Principal</label><br>
        <input type="text" name="titulo_principal" value=""><br>

        <label>Subtítulo</label><br>
        <textarea name="subtitulo_principal" rows="2"></textarea><br>

        <label>Sobre</label><br>
        <textarea name="sobre" rows="4"></textarea><br>

        <label>Educação</label><br>
        <textarea name="educacao" rows="4"></textarea><br>

        <label>Carreira</label><br>
        <textarea name="carreira" rows="4"></textarea><br>

        <label>Contato</label><br>
        <textarea name="contato" rows="4"></textarea><br>

        <button type="submit">Salvar Landing Page</button>
    </form>
<?php endif ?>

<a href="user.php"><button>Voltar!</button></a>
</body>
</html>
