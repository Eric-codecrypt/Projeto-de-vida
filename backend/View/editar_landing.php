<?php
require 'C:\Turma2\xampp\htdocs\Projeto-de-vida\config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$currentUser = $stmt->fetch();

if ($currentUser && $currentUser['username'] === 'Eric') {
    echo "<h2>Você não tem permissão para editar uma landing page.</h2>";
    exit;
}

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
    $publico = isset($_POST['publico']) ? 1 : 0;

    if ($landing) {
        $query = "UPDATE landing_pages SET titulo_principal=?, subtitulo_principal=?, sobre=?, educacao=?, carreira=?, contato=?, publico=? WHERE user_id=?";
        $pdo->prepare($query)->execute([$titulo_principal, $subtitulo_principal, $sobre, $educacao, $carreira, $contato, $publico, $user_id]);
    } else {
        $query = "INSERT INTO landing_pages (user_id, titulo_principal, subtitulo_principal, sobre, educacao, carreira, contato, publico) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $pdo->prepare($query)->execute([$user_id, $titulo_principal, $subtitulo_principal, $sobre, $educacao, $carreira, $contato, $publico]);
    }

    header("Location: landing.php?user_id=" . $user_id);
    exit;
}

// Recarrega os dados
$stmt = $pdo->prepare("SELECT * FROM landing_pages WHERE user_id = ?");
$stmt->execute([$user_id]);
$landing = $stmt->fetch();
?>

<form method="POST">
    <input type="text" name="titulo_principal" placeholder="Título" value="<?= htmlspecialchars($landing['titulo_principal'] ?? '') ?>"><br>
    <textarea name="subtitulo_principal" placeholder="Subtítulo"><?= htmlspecialchars($landing['subtitulo_principal'] ?? '') ?></textarea><br>
    <textarea name="sobre" placeholder="Sobre"><?= htmlspecialchars($landing['sobre'] ?? '') ?></textarea><br>
    <textarea name="educacao" placeholder="Educação"><?= htmlspecialchars($landing['educacao'] ?? '') ?></textarea><br>
    <textarea name="carreira" placeholder="Carreira"><?= htmlspecialchars($landing['carreira'] ?? '') ?></textarea><br>
    <textarea name="contato" placeholder="Contato"><?= htmlspecialchars($landing['contato'] ?? '') ?></textarea><br>
    <label><input type="checkbox" name="publico" <?= isset($landing['publico']) && $landing['publico'] ? 'checked' : '' ?>> Tornar público</label><br>
    <button type="submit">Salvar</button>
</form>
