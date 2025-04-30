<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config.php';

// Verificar se um ID de usuário foi fornecido
if (!isset($_GET['user_id'])) {
    die("ID de usuário não especificado.");
}

$user_id = (int)$_GET['user_id'];

// Buscar dados do landing page
$stmt = $pdo->prepare("SELECT l.*, u.username, u.email, u.profile_picture 
                        FROM landing_pages l 
                        JOIN users u ON l.user_id = u.id 
                        WHERE l.user_id = ?");
$stmt->execute([$user_id]);
$landing = $stmt->fetch(PDO::FETCH_ASSOC);

// Verificar se o landing page existe e é público (a menos que o visitante seja o próprio dono ou admin)
if (!$landing) {
    die("Landing page não encontrado.");
}

if (!$landing['publico'] && (!isset($_SESSION['user_id']) || ($_SESSION['user_id'] != $user_id && $_SESSION['user_id'] != 8))) {
    die("Este landing page não é público.");
}

// Definir título da página
$page_title = $landing['titulo_principal'];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($landing['titulo_principal']) ?></title>
    <link rel="stylesheet" href="dark-mode.css">
    <style>
        /* Mantenha o CSS original aqui */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            transition: background-color 0.3s, color 0.3s;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        header {
            background-color: #007bff;
            color: white;
            text-align: center;
            padding: 40px 20px;
        }

        .profile-pic {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
            display: block;
            margin: 0 auto 20px;
        }

        h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        main {
            padding: 30px;
        }

        section {
            margin-bottom: 30px;
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
        }

        section:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        h2 {
            color: #007bff;
            margin-bottom: 15px;
            font-size: 1.5rem;
            border-left: 4px solid #007bff;
            padding-left: 10px;
        }

        .contact-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #007bff;
        }

        footer {
            background-color: #f0f0f0;
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 0.9rem;
        }

        footer a {
            color: #007bff;
            text-decoration: none;
        }

        footer a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .container {
                border-radius: 0;
            }

            header {
                padding: 30px 20px;
            }

            h1 {
                font-size: 2rem;
            }

            .profile-pic {
                width: 120px;
                height: 120px;
            }
        }
    </style>
    <script src="theme.js" defer></script>
</head>
<body>
<!-- Conteúdo original continua aqui -->
<div class="container">
    <header>
        <?php if (!empty($landing['profile_picture'])): ?>
            <img src="<?= htmlspecialchars($landing['profile_picture']) ?>" alt="Foto de <?= htmlspecialchars($landing['username']) ?>" class="profile-pic">
        <?php endif; ?>

        <h1><?= htmlspecialchars($landing['titulo_principal']) ?></h1>
        <?php if (!empty($landing['subtitulo_principal'])): ?>
            <div class="subtitle"><?= htmlspecialchars($landing['subtitulo_principal']) ?></div>
        <?php endif; ?>
    </header>

    <main>
        <?php if (!empty($landing['sobre'])): ?>
            <section>
                <h2>Sobre Mim</h2>
                <div><?= nl2br(htmlspecialchars($landing['sobre'])) ?></div>
            </section>
        <?php endif; ?>

        <?php if (!empty($landing['educacao'])): ?>
            <section>
                <h2>Educação</h2>
                <div><?= nl2br(htmlspecialchars($landing['educacao'])) ?></div>
            </section>
        <?php endif; ?>

        <?php if (!empty($landing['carreira'])): ?>
            <section>
                <h2>Carreira</h2>
                <div><?= nl2br(htmlspecialchars($landing['carreira'])) ?></div>
            </section>
        <?php endif; ?>

        <?php if (!empty($landing['contato'])): ?>
            <section>
                <h2>Contato</h2>
                <div class="contact-info"><?= nl2br(htmlspecialchars($landing['contato'])) ?></div>
            </section>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($landing['username']) ?> - Todos os direitos reservados</p>
        <?php if (isset($_SESSION['user_id'])): ?>
            <p><a href="user.php">Voltar ao painel</a></p>
        <?php endif; ?>
    </footer>
</div>
</body>
</html>