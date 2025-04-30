<?php
session_start();
require_once __DIR__ . '/../../config.php'; // Caminho correto para o config.php

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Buscar usuário no banco de dados
    $stmt = $pdo->prepare("SELECT id, password FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Login bem-sucedido - iniciar sessão
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $email;

        echo "<p>Login realizado com sucesso! Redirecionando...</p>";
        header("refresh:2; url=user.php"); // Redireciona após 2 segundos
        exit;
    } else {
        echo "<p style='color: red;'>E-mail ou senha incorretos.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="dark-mode.css">
    <link rel="icon" href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAOEAAADhCAMAAAAJbSJIAAAAclBMVEX///8hlvMAkPLF4Pvr9v6hzfklmfMAj/Itm/Tv+P6z1/ofl/MQkvMXlPP2+//i8P2p0voAi/LW6/2Bvvd6u/c2nvRjr/bz+v47ofS83PtMpPTZ7f11uPel0PnG4vzq8/2VyflQqvXQ5vxbrPVqsfaDvPcwwE3YAAAEeklEQVR4nO3dW3raMBAFYEsOFgENTjFQm0Awybr/Gdbu5aEPpR7KkWR952yg/WNJWLdxUTAMwzAMwzAMwzAMwzAMEzZ1v3hMzi+rVfe2vTx/3cQ2/Znyi31QvPfWOeer9rDrV9un2LLfKa15cMSIDNb18XR+j60b83jhb+jIlOtim63wl9O7qnmL2jPBwhEpbtlEbK944Yj0tn2J9cCDCH8im0vWwiHe7uq8hYNRduE/JoMKR2OfuXDoj1WZt3Awut�???" type="image/x-icon">
    <link rel="shortcut icon" href="data:image/png;base64,iVBORw0KGgoAAAANSUhCAAAEWa5wRJzGYAAAEeklEQVR4nO3dW3raMBAFYEsOFgENTjFQm0Awybr/Gdbu5aEPpR7KkWR952yg/WNJWLdxUTAMwzAMwzAMwzAMwzAMEzZ1v3hMzi+rVfe2vTx/3cQ2/Znyi31QvPfWOuer9rDrV9un2LLfKa15cMSIDNb18XR+j60b83jhb+jIlOtim63wl9O7qnmL2jPBwhEpbtlEbK944Yj0tn2J9cCDCH8im0vWwiHe7uq8hYNRduE/JoMKR2OfuXDoj1WZt..." type="image/x-icon">
    <title>Login</title>
    <script src="theme.js" defer></script>
    <style>
        /* Estilos originais */
        .features {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            flex-direction: column;
        }

        .feature-card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        h1 {
            margin-bottom: 30px;
            color: #333;
        }

        input {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }

        button, .btn {
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 12px 20px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
            transition: background-color 0.3s;
        }

        button:hover, .btn:hover {
            background-color: #0056b3;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .login_google {
            margin-top: 20px;
        }

        .google {
            display: flex;
            justify-content: center;
            margin-top: 10px;
        }

        p {
            margin: 15px 0;
        }

        .botao2 {
            margin-top: 15px;
        }
    </style>
</head>

<body class="features">
<div class="feature-card">
    <header>
        <h1>Login</h1>
    </header>

    <form method="POST">
        <input type="text" name="username" placeholder="Nome de usuário" required>
        <input type="password" name="password" placeholder="Senha" required>
        <input type="email" name="email" placeholder="seu@email.com" required>

        <?php if(isset($error_message)): ?>
            <p style="color: red;"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <button type="submit">Entrar</button>

        <div class="login_google">
            <h3>ou entre com o Google</h3>
            <div class="google">
                <script src="https://accounts.google.com/gsi/client" async></script>
                <div id="g_id_onload"></div>
            </div>
        </div>
    </form>

    <p>Não tem uma conta?</p>
    <div class="botao2">
        <button class="btn"><a href="register.php" style="color: white; text-decoration: none;">Cadastre-se</a></button>
    </div>

    <?php if (isset($_SESSION['user_id'])): ?>
        <p><a href="user.php">Voltar ao painel</a></p>
    <?php endif; ?>
</div>
</body>

</html>