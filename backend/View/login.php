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

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAOEAAADhCAMAAAAJbSJIAAAAclBMVEX///8hlvMAkPLF4Pvr9v6hzfklmfMAj/Itm/Tv+P6z1/ofl/MQkvMXlPP2+//i8P2p0voAi/LW6/2Bvvd6u/c2nvRjr/bz+v47ofS83PtMpPTZ7f11uPel0PnG4vzq8/2VyflQqvXQ5vxbrPVqsfaDvPcwwE3YAAAEeklEQVR4nO3dW3raMBAFYEsOFgENTjFQm0AwSbr/Ldbu5aEPpR7KkWR952yg/WNJWLdxUTAMwzAMwzAMwzAMwzAMEzZ1v3hMzi+rVfe2vTx/3cQ2/Znyi31QvPfWOuer9rDrV9un2LLfKa15cMSIDNb18XR+j60b83jhb+jIlOtim63wl9O7qnmL2jPBwhEpbtlEbK944Yj0tn2J9SCDCH8im0vWwiHe7uq8hYNRduF/JoMKR2OfuXDoj1WZt3AwutNz3sKhqVZd5sLhMTaZC42x12CDaiTh0FJDvZLHEg6vq4HG1GjCoTO+ZC40JgwxpjAMMarQ2FXuQrFvmQuNGPiEKrLQyD53ofGfuQuNBQ+o8YUi2K4YX2j8MXehcefchWKQU6kUhMbvEhHK7fwH0QInixrh8laqyljr73T6QwpCqV6fb+VSv3b9Z+X8PUiH27rRCKeMB5ttI3d0bfmYjXDMaq9/ju51TsKiWBgtUWDDKUZY1EdlU5U1aiEcJCyKk5LoUS/gMGHR6IhynZ2waLyKaEFTDKCwOKiIfgEBQoWbSjOiopopUli8O4XQuK8IIFZY7DTt1GJ2FbHCi+ZN3GM2FbFC3XjaPp5XwIW1aqyBdESwsPjQNFPIFAot7BRvNh6yT4MWbhQ/GJihBi0srtObqUDWMuDCfvpoKpClYbhQ0RFljxhM4cLL9I5457/wj8CFm7WiIyImUHhhOxmImSLChZrffIdY+sYLPxVCxEtNAOH0nwvIyQy88DT9GVrEUbe0hPN8hopWOtN+eMh+LD0qWuksfw81S4rzfKe5KN68K8TNKM4t/luY//xQ8eI9zzm+Yno403UaRSOd51rbZqlYL7VzXC990WwEe8hWPlaoeoSz3LdQ7a7Nce9ppdohhcwOscKt7miUxdzYBwq3Gh7uaBtOWCoPfqHO7MOEvVUKBXQUGiQsW91xIeAhWoiwbJ36+CVoJAUIn96aSu8zBnb/SXUK+ulG6vr1vVucjuv7jkFb0Jkv3Un29Y2MfwJ7/1F28Sig7r4F6C7CEN+nIYRFKlwFojSEyCvPSQgxS1ApCZGXgpIQgiaG6QjBl53jC8XnfksWXVchutCiyw3FFnrcpbU0hB51UyYVoRzxhc2iCqUNULktptAfQ1T8jCiE1/yILQxVlS6aMFClr2hCvw9WIzpS3cRTuKrCUWpfrvOufTk8QMxFw0SEYo+Bq7QHFro2ZAMNLhS3D1BjL5pQvL0GrpEcUjjwlg2stkd0oXgnn2W07yOgv/4g1smpi/n5B5xw/ISHLA/nSI0TKPzxFZahYbaHpoutG6MRjh/L+WvGj+m4IbZqD9/61faSyieDFML1eXUrXVe+b+ugBeUnRbPLnd7/fkrw9y1ih0IK0w+FFKYfCilMPxRSmH4opDD9UEhh+qGQwvRDIYXph0IK0w+FFKYfCilMPxRSmH4opDD9UEhh+qGQwvRDIYXph0IK0w+FFKYfCilMP+UXOzHezFNY94upOadyG41hGIZhGIZhGIZhGIZhmMflO7iScULsLU8KAAAAAElFTkSuQmCC" type="image/x-icon">
    <link rel="shortcut icon" href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAOEAAADhCAMAAAAJbSJIAAAAclBMVEX///8hlvMAkPLF4Pvr9v6hzfklmfMAj/Itm/Tv+P6z1/ofl/MQkvMXlPP2+//i8P2p0voAi/LW6/2Bvvd6u/c2nvRjr/bz+v47ofS83PtMpPTZ7f11uPel0PnG4vzq8/2VyflQqvXQ5vxbrPVqsfaDvPcwwE3YAAAEeklEQVR4nO3dW3raMBAFYEsOFgENTjFQm0AwSbr/Ldbu5aEPpR7KkWR952yg/WNJWLdxUTAMwzAMwzAMwzAMwzAMEzZ1v3hMzi+rVfe2vTx/3cQ2/Znyi31QvPfWOuer9rDrV9un2LLfKa15cMSIDNb18XR+j60b83jhb+jIlOtim63wl9O7qnmL2jPBwhEpbtlEbK944Yj0tn2J9SCDCH8im0vWwiHe7uq8hYNRduF/JoMKR2OfuXDoj1WZt3AwutNz3sKhqVZd5sLhMTaZC42x12CDaiTh0FJDvZLHEg6vq4HG1GjCoTO+ZC40JgwxpjAMMarQ2FXuQrFvmQuNGPiEKrLQyD53ofGfuQuNBQ+o8YUi2K4YX2j8MXehcefchWKQU6kUhMbvEhHK7fwH0QInixrh8laqyljr73T6QwpCqV6fb+VSv3b9Z+X8PUiH27rRCKeMB5ttI3d0bfmYjXDMaq9/ju51TsKiWBgtUWDDKUZY1EdlU5U1aiEcJCyKk5LoUS/gMGHR6IhynZ2waLyKaEFTDKCwOKiIfgEBQoWbSjOiopopUli8O4XQuK8IIFZY7DTt1GJ2FbHCi+ZN3GM2FbFC3XjaPp5XwIW1aqyBdESwsPjQNFPIFAot7BRvNh6yT4MWbhQ/GJihBi0srtObqUDWMuDCfvpoKpClYbhQ0RFljxhM4cLL9I5457/wj8CFm7WiIyImUHhhOxmImSLChZrffIdY+sYLPxVCxEtNAOH0nwvIyQy88DT9GVrEUbe0hPN8hopWOtN+eMh+LD0qWuksfw81S4rzfKe5KN68K8TNKM4t/luY//xQ8eI9zzm+Yno403UaRSOd51rbZqlYL7VzXC990WwEe8hWPlaoeoSz3LdQ7a7Nce9ppdohhcwOscKt7miUxdzYBwq3Gh7uaBtOWCoPfqHO7MOEvVUKBXQUGiQsW91xIeAhWoiwbJ36+CVoJAUIn96aSu8zBnb/SXUK+ulG6vr1vVucjuv7jkFb0Jkv3Un29Y2MfwJ7/1F28Sig7r4F6C7CEN+nIYRFKlwFojSEyCvPSQgxS1ApCZGXgpIQgiaG6QjBl53jC8XnfksWXVchutCiyw3FFnrcpbU0hB51UyYVoRzxhc2iCqUNULktptAfQ1T8jCiE1/yILQxVlS6aMFClr2hCvw9WIzpS3cRTuKrCUWpfrvOufTk8QMxFw0SEYo+Bq7QHFro2ZAMNLhS3D1BjL5pQvL0GrpEcUjjwlg2stkd0oXgnn2W07yOgv/4g1smpi/n5B5xw/ISHLA/nSI0TKPzxFZahYbaHpoutG6MRjh/L+WvGj+m4IbZqD9/61faSyieDFML1eXUrXVe+b+ugBeUnRbPLnd7/fkrw9y1ih0IK0w+FFKYfCilMPxRSmH4opDD9UEhh+qGQwvRDIYXph0IK0w+FFKYfCilMPxRSmH4opDD9UEhh+qGQwvRDIYXph0IK0w+FFKYfCilMP+UXOzHezFNY94upOadyG41hGIZhGIZhGIZhGIZhmMflO7iScULsLU8KAAAAAElFTkSuQmCC" type="image/x-icon">
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

    h3 {
        display: flex;
        justify-content: center;
        margin-top: 10px;
    }

    .google {
        display: flex;
        justify-content: center;
    }

    p {
        display: flex;
        justify-items: center;
        margin-top: 10px;
        margin: 0 auto;
    }

    .botao2 {
        margin-top: 10px;
    }

</style>
<body class="features">
        <div class="feature-card">
            <header>
                <h1>Login</h1>
            </header>
            <br>
            <br>

            <section>
                <div>
            <form method="POST" enctype="multipart/form-data">
                <input type="text" name="username" placeholder="Nome de usuário" required>
                <br>
                <input type="password" name="password" placeholder="Senha" required>
                <br>
                <input type="email" name="email" placeholder="seu@email.com" required>
                <br>
<br>
                <button  class="btn btn-primary" style="white" type="submit">Entrar</button>
            </form>
                    <br>
                    <br>

            <p>Não tem uma conta?</p>
            <div>
                <button  class="btn btn-primary">
                    <a style="color: white" href="register.php">Cadastre-se</a>
                </button>
            </div>

            <?php if (isset($_SESSION['user_id'])): ?>
                <p><a href="user.php">Voltar ao painel</a></p>
            <?php endif; ?>
        </div>


</body>
</html>
