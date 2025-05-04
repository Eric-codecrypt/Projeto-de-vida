<?php
session_start();
require_once __DIR__ . '/../../config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Buscar usuário no banco de dados
    $stmt = $pdo->prepare("SELECT id, password, theme_color FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $email;
        $_SESSION['theme_color'] = $user['theme_color'];

        header("Location: user.php");
        exit;
    } else {
        $error_message = "E-mail ou senha incorretos.";
    }
}


?>

    <div class="features">
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
                <button class="btn">
                    <a href="register.php" style="color: white; text-decoration: none;">Cadastre-se</a>
                </button>
            </div>

            <?php if (isset($_SESSION['user_id'])): ?>
                <p><a href="user.php">Voltar ao painel</a></p>
            <?php endif; ?>
        </div>
    </div>