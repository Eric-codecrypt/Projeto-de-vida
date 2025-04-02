<?php
session_start();
include_once 'C:/Turma2/xampp/htdocs/Projeto-de-vida/backend/Controller/UserController.php';
include_once 'C:/Turma2/xampp/htdocs/Projeto-de-vida/config.php';

$Controller = new UserController($pdo);

if (!empty($_POST)) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];

    $logged_in = $Controller->login($username, $email, $password);

    if (!empty($logged_in)) {
        $_SESSION['user_id'] = $logged_in['id'];
        $_SESSION['email'] = $logged_in['email'];

        header("Location: user.php");
        exit; // Garante que o script não continue rodando após o redirecionamento
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">

    <title>Pagina de login</title>
</head>
<style>
    .features {
        display: flex;
        justify-content: center;
        margin: 0 auto;
        width: 300px;
    }

    input {
        display: flex;
        flex-direction: column;
        justify-content: center;
        margin: 0 auto;
        width: 30vh;
        height: 5vh;
        font-size: 20px;
        padding: 10px;

    }

    header {
        display: flex;
        justify-content: center;
        margin-top: 40px
    }

    form {
        margin-top: -40px;
    }

  .login_google {
        display: flex;
       justify-content: center;
       flex-direction: column;
    }
    h3{
        display: flex;
        justify-content: center;
        margin-top:10px;
    }
    .google{
        display: flex;
        justify-content: center;
    }
    p{
        display: flex;
        justify-items: center;
        margin-top: 10px;
        margin: 0 auto;
    }
    .botao2{
        margin-top: 10px;
    }
</style>

<body class="features">
    <div class="feature-card">

        <header>

            <h1>Login</h1>
        </header>

        <section>
            <div>
                <form method="POST">
                    <input required type="text" name="username" placeholder="nome de usuário">
                    <br>
                    <input required type="password" name="password" placeholder="senha">
                    <br>
                    <input required="@gmail.com" type="text" name="email" placeholder="email">
                    <br>

                    <?php
                    if (isset($logged_in) && empty($logged_in)) {
                        echo "usuário ou senha estão errados, tente novamente!";
                    } else {
                    }
                    ?>
                    <div class="login_google">
                        <button class="btn btn-primary" type="submit">Login</button>
                        <h3>ou</h3>
                        <h3>logue com o Google</h3>
                        <html>

                        <body>
                          <div class="google">
                            <script src="https://accounts.google.com/gsi/client" async></script>
                            <div id="g_id_onload"
                                data-client_id="659345250941-hp6n6p2g45ogrgt0noqplur9tegi36vo.apps.googleusercontent.com"
                                data-login_uri="http://localhost/Projeto-de-vida/login-google/includes/login.php"
                                data-auto_prompt="false">
                            </div> 
                        </div>
                            <div class="g_id_signin"
                                data-type="standard"
                                data-size="large"
                                data-theme="outline"
                                data-text="sign_in_with"
                                data-shape="rectangular"
                                data-logo_alignment="left">
                           
                    </div>
</div>
                    <body>

</html>
</form>


    </div>
   <p> Não tem uma conta? registre uma</p>
   <div class="botao2">
<button class="btn btn-primary"><a href="register.php">Aqui!</a></button>
</div>



</section>
</div>

</html>