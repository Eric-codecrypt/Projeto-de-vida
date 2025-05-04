<?php
session_start();
include_once 'C:/xampp/htdocs/Projeto-de-vida/backend/Controller/UserController.php';
include_once 'C:/xampp/htdocs/Projeto-de-vida/config.php';




$Controller = new UserController($pdo);
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];
    $new_password = password_hash( $_POST['new_password'], PASSWORD_DEFAULT);

    // Verificar se o e-mail e senha atual estão corretos
    $stmt = $pdo->prepare("SELECT id, password FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $new_password != $user['password']) { 
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$new_password, $user['id']]);

        echo "<p>Senha alterada com sucesso.</p>";
        header("Location: login.php");
    } else {
        echo "<p>E-mail ou senha atual incorretos.</p>";
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alterar Senha</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAOEAAADhCAMAAAAJbSJIAAAAclBMVEX///8hlvMAkPLF4Pvr9v6hzfklmfMAj/Itm/Tv+P6z1/ofl/MQkvMXlPP2+//i8P2p0voAi/LW6/2Bvvd6u/c2nvRjr/bz+v47ofS83PtMpPTZ7f11uPel0PnG4vzq8/2VyflQqvXQ5vxbrPVqsfaDvPcwwE3YAAAEeklEQVR4nO3dW3raMBAFYEsOFgENTjFQm0AwSbr/Ldbu5aEPpR7KkWR952yg/WNJWLdxUTAMwzAMwzAMwzAMwzAMEzZ1v3hMzi+rVfe2vTx/3cQ2/Znyi31QvPfWOuer9rDrV9un2LLfKa15cMSIDNb18XR+j60b83jhb+jIlOtim63wl9O7qnmL2jPBwhEpbtlEbK944Yj0tn2J9SCDCH8im0vWwiHe7uq8hYNRduF/JoMKR2OfuXDoj1WZt3AwutNz3sKhqVZd5sLhMTaZC42x12CDaiTh0FJDvZLHEg6vq4HG1GjCoTO+ZC40JgwxpjAMMarQ2FXuQrFvmQuNGPiEKrLQyD53ofGfuQuNBQ+o8YUi2K4YX2j8MXehcefchWKQU6kUhMbvEhHK7fwH0QInixrh8laqyljr73T6QwpCqV6fb+VSv3b9Z+X8PUiH27rRCKeMB5ttI3d0bfmYjXDMaq9/ju51TsKiWBgtUWDDKUZY1EdlU5U1aiEcJCyKk5LoUS/gMGHR6IhynZ2waLyKaEFTDKCwOKiIfgEBQoWbSjOiopopUli8O4XQuK8IIFZY7DTt1GJ2FbHCi+ZN3GM2FbFC3XjaPp5XwIW1aqyBdESwsPjQNFPIFAot7BRvNh6yT4MWbhQ/GJihBi0srtObqUDWMuDCfvpoKpClYbhQ0RFljxhM4cLL9I5457/wj8CFm7WiIyImUHhhOxmImSLChZrffIdY+sYLPxVCxEtNAOH0nwvIyQy88DT9GVrEUbe0hPN8hopWOtN+eMh+LD0qWuksfw81S4rzfKe5KN68K8TNKM4t/luY//xQ8eI9zzm+Yno403UaRSOd51rbZqlYL7VzXC990WwEe8hWPlaoeoSz3LdQ7a7Nce9ppdohhcwOscKt7miUxdzYBwq3Gh7uaBtOWCoPfqHO7MOEvVUKBXQUGiQsW91xIeAhWoiwbJ36+CVoJAUIn96aSu8zBnb/SXUK+ulG6vr1vVucjuv7jkFb0Jkv3Un29Y2MfwJ7/1F28Sig7r4F6C7CEN+nIYRFKlwFojSEyCvPSQgxS1ApCZGXgpIQgiaG6QjBl53jC8XnfksWXVchutCiyw3FFnrcpbU0hB51UyYVoRzxhc2iCqUNULktptAfQ1T8jCiE1/yILQxVlS6aMFClr2hCvw9WIzpS3cRTuKrCUWpfrvOufTk8QMxFw0SEYo+Bq7QHFro2ZAMNLhS3D1BjL5pQvL0GrpEcUjjwlg2stkd0oXgnn2W07yOgv/4g1smpi/n5B5xw/ISHLA/nSI0TKPzxFZahYbaHpoutG6MRjh/L+WvGj+m4IbZqD9/61faSyieDFML1eXUrXVe+b+ugBeUnRbPLnd7/fkrw9y1ih0IK0w+FFKYfCilMPxRSmH4opDD9UEhh+qGQwvRDIYXph0IK0w+FFKYfCilMPxRSmH4opDD9UEhh+qGQwvRDIYXph0IK0w+FFKYfCilMP+UXOzHezFNY94upOadyG41hGIZhGIZhGIZhGIZhmMflO7iScULsLU8KAAAAAElFTkSuQmCC" type="image/x-icon">
    <link rel="shortcut icon" href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAOEAAADhCAMAAAAJbSJIAAAAclBMVEX///8hlvMAkPLF4Pvr9v6hzfklmfMAj/Itm/Tv+P6z1/ofl/MQkvMXlPP2+//i8P2p0voAi/LW6/2Bvvd6u/c2nvRjr/bz+v47ofS83PtMpPTZ7f11uPel0PnG4vzq8/2VyflQqvXQ5vxbrPVqsfaDvPcwwE3YAAAEeklEQVR4nO3dW3raMBAFYEsOFgENTjFQm0AwSbr/Ldbu5aEPpR7KkWR952yg/WNJWLdxUTAMwzAMwzAMwzAMwzAMEzZ1v3hMzi+rVfe2vTx/3cQ2/Znyi31QvPfWOuer9rDrV9un2LLfKa15cMSIDNb18XR+j60b83jhb+jIlOtim63wl9O7qnmL2jPBwhEpbtlEbK944Yj0tn2J9SCDCH8im0vWwiHe7uq8hYNRduF/JoMKR2OfuXDoj1WZt3AwutNz3sKhqVZd5sLhMTaZC42x12CDaiTh0FJDvZLHEg6vq4HG1GjCoTO+ZC40JgwxpjAMMarQ2FXuQrFvmQuNGPiEKrLQyD53ofGfuQuNBQ+o8YUi2K4YX2j8MXehcefchWKQU6kUhMbvEhHK7fwH0QInixrh8laqyljr73T6QwpCqV6fb+VSv3b9Z+X8PUiH27rRCKeMB5ttI3d0bfmYjXDMaq9/ju51TsKiWBgtUWDDKUZY1EdlU5U1aiEcJCyKk5LoUS/gMGHR6IhynZ2waLyKaEFTDKCwOKiIfgEBQoWbSjOiopopUli8O4XQuK8IIFZY7DTt1GJ2FbHCi+ZN3GM2FbFC3XjaPp5XwIW1aqyBdESwsPjQNFPIFAot7BRvNh6yT4MWbhQ/GJihBi0srtObqUDWMuDCfvpoKpClYbhQ0RFljxhM4cLL9I5457/wj8CFm7WiIyImUHhhOxmImSLChZrffIdY+sYLPxVCxEtNAOH0nwvIyQy88DT9GVrEUbe0hPN8hopWOtN+eMh+LD0qWuksfw81S4rzfKe5KN68K8TNKM4t/luY//xQ8eI9zzm+Yno403UaRSOd51rbZqlYL7VzXC990WwEe8hWPlaoeoSz3LdQ7a7Nce9ppdohhcwOscKt7miUxdzYBwq3Gh7uaBtOWCoPfqHO7MOEvVUKBXQUGiQsW91xIeAhWoiwbJ36+CVoJAUIn96aSu8zBnb/SXUK+ulG6vr1vVucjuv7jkFb0Jkv3Un29Y2MfwJ7/1F28Sig7r4F6C7CEN+nIYRFKlwFojSEyCvPSQgxS1ApCZGXgpIQgiaG6QjBl53jC8XnfksWXVchutCiyw3FFnrcpbU0hB51UyYVoRzxhc2iCqUNULktptAfQ1T8jCiE1/yILQxVlS6aMFClr2hCvw9WIzpS3cRTuKrCUWpfrvOufTk8QMxFw0SEYo+Bq7QHFro2ZAMNLhS3D1BjL5pQvL0GrpEcUjjwlg2stkd0oXgnn2W07yOgv/4g1smpi/n5B5xw/ISHLA/nSI0TKPzxFZahYbaHpoutG6MRjh/L+WvGj+m4IbZqD9/61faSyieDFML1eXUrXVe+b+ugBeUnRbPLnd7/fkrw9y1ih0IK0w+FFKYfCilMPxRSmH4opDD9UEhh+qGQwvRDIYXph0IK0w+FFKYfCilMPxRSmH4opDD9UEhh+qGQwvRDIYXph0IK0w+FFKYfCilMP+UXOzHezFNY94upOadyG41hGIZhGIZhGIZhGIZhmMflO7iScULsLU8KAAAAAElFTkSuQmCC" type="image/x-icon">
</head>

<body>
    <h2>Alterar Senha</h2>
    <form method="POST">
        <label for="email">E-mail cadastrado:</label>
        <input type="email" id="email" name="email" required>

        <label for="new_password">Nova Senha:</label>
        <input type="password" id="new_password" name="new_password" required>

        <button type="submit">Alterar Senha</button>
    </form>
</body>





</html>