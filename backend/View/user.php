<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$controllerPath = __DIR__ . '/../Controller/UserController.php';
if (file_exists($controllerPath)) {
    require_once $controllerPath;
} else {
    die("Erro: Arquivo UserController.php não encontrado em $controllerPath");
}

$configPath = __DIR__ . '/../../config.php';
if (file_exists($configPath)) {
    require_once $configPath;
} else {
    die("Erro: Arquivo config.php não encontrado em $configPath");
}

if (!class_exists('UserController')) {
    die("Erro: Classe UserController não encontrada.");
}

$Controller = new UserController($pdo);

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $Controller->getUserFromID($user_id)["username"];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Usuário não encontrado.");
}

$authType = $_SESSION['auth_type'] ?? 'normal';

// Buscar dados do formulário "Quem Sou Eu" (se existirem)
$stmt = $pdo->prepare("SELECT * FROM quem_sou_eu WHERE user_id = ?");
$stmt->execute([$user_id]);
$perfil = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['description'])) {
        $new_description = $_POST['description'];
        $stmt = $pdo->prepare("UPDATE users SET description = ? WHERE id = ?");
        $stmt->execute([$new_description, $user_id]);
        header("Location: user.php");
        exit();
    }

    if (isset($_POST['edit_usuario'])) {
        $new_name = trim($_POST['name']);
        $new_email = trim($_POST['email']);

        if (!empty($new_name) && !empty($new_email)) {
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            $stmt->execute([$new_name, $new_email, $user_id]);
            $username = $new_name;
            $user['email'] = $new_email;
        }
    }

    if (isset($_POST['salvar_perfil_completo'])) {
        $fale = $_POST['sobre_voce'] ?? '';
        $lembrancas = $_POST['lembrancas'] ?? '';
        $p_fortes = $_POST['pontos_fortes'] ?? '';
        $p_fracos = $_POST['pontos_fracos'] ?? '';
        $valores = $_POST['valores'] ?? '';
        $aptidoes = implode(', ', $_POST['aptidoes'] ?? []);
        $rel_familia = $_POST['familia'] ?? '';
        $rel_amigos = $_POST['amigos'] ?? '';
        $rel_escola = $_POST['escola'] ?? '';
        $rel_sociedade = $_POST['sociedade'] ?? '';
        $gosto = $_POST['gosto_fazer'] ?? '';
        $nao_gosto = $_POST['nao_gosto'] ?? '';
        $rotina = $_POST['rotina'] ?? '';
        $lazer = $_POST['lazer'] ?? '';
        $estudos = $_POST['estudos'] ?? '';
        $vida_escolar = $_POST['vida_escolar'] ?? '';
        $visao_fisica = $_POST['visao_fisica'] ?? '';
        $visao_intelectual = $_POST['visao_intelectual'] ?? '';
        $visao_emocional = $_POST['visao_emocional'] ?? '';
        $visao_amigos = $_POST['visao_amigos'] ?? '';
        $visao_familiares = $_POST['visao_familiares'] ?? '';
        $visao_professores = $_POST['visao_professores'] ?? '';
        $auto_total = (int) ($_POST['autovalorizacao'] ?? 0);

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM quem_sou_eu WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $existe = $stmt->fetchColumn();

        if ($existe) {
            $stmt = $pdo->prepare("UPDATE quem_sou_eu SET 
                fale_sobre_voce=?, minhas_lembrancas=?, pontos_fortes=?, pontos_fracos=?, meus_valores=?,
                principais_aptidoes=?, relacoes_familia=?, relacoes_amigos=?, relacoes_escola=?, relacoes_sociedade=?,
                gosto_fazer=?, nao_gosto_fazer=?, rotina=?, lazer=?, estudos=?, vida_escolar=?,
                visao_fisica=?, visao_intelectual=?, visao_emocional=?,
                visao_dos_amigos=?, visao_dos_familiares=?, visao_dos_professores=?, autovalorizacao_total=?
                WHERE user_id = ?");
            $stmt->execute([
                $fale,
                $lembrancas,
                $p_fortes,
                $p_fracos,
                $valores,
                $aptidoes,
                $rel_familia,
                $rel_amigos,
                $rel_escola,
                $rel_sociedade,
                $gosto,
                $nao_gosto,
                $rotina,
                $lazer,
                $estudos,
                $vida_escolar,
                $visao_fisica,
                $visao_intelectual,
                $visao_emocional,
                $visao_amigos,
                $visao_familiares,
                $visao_professores,
                $auto_total,
                $user_id
            ]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO quem_sou_eu (
                user_id, fale_sobre_voce, minhas_lembrancas, pontos_fortes, pontos_fracos, meus_valores,
                principais_aptidoes, relacoes_familia, relacoes_amigos, relacoes_escola, relacoes_sociedade,
                gosto_fazer, nao_gosto_fazer, rotina, lazer, estudos, vida_escolar,
                visao_fisica, visao_intelectual, visao_emocional,
                visao_dos_amigos, visao_dos_familiares, visao_dos_professores, autovalorizacao_total
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $user_id,
                $fale,
                $lembrancas,
                $p_fortes,
                $p_fracos,
                $valores,
                $aptidoes,
                $rel_familia,
                $rel_amigos,
                $rel_escola,
                $rel_sociedade,
                $gosto,
                $nao_gosto,
                $rotina,
                $lazer,
                $estudos,
                $vida_escolar,
                $visao_fisica,
                $visao_intelectual,
                $visao_emocional,
                $visao_amigos,
                $visao_familiares,
                $visao_professores,
                $auto_total
            ]);
        }

        header("Location: user.php");
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["profile_picture"])) {
    $file = $_FILES["profile_picture"];
    $uploadDir = __DIR__ . "/img/";

    $allowedTypes = ["image/jpeg", "image/png", "image/gif"];
    $fileType = mime_content_type($file["tmp_name"]);

    if ($file["error"] === 0 && in_array($fileType, $allowedTypes)) {
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = uniqid() . "_" . basename($file["name"]);
        $filePath = "img/" . $fileName;

        if (move_uploaded_file($file["tmp_name"], $uploadDir . $fileName)) {
            $stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
            $stmt->execute([$filePath, $user_id]);
            header("Location: user.php");
            exit();
        } else {
            echo "Erro ao mover o arquivo.";
        }
    } else {
        echo "Formato inválido. Use JPG, PNG ou GIF.";
    }

    
}

if (isset($_POST['teste_personalidade'])) {
    $extrovertido = (int) $_POST['extrovertido'] ?? 0;
    $intuitivo = (int) $_POST['intuitivo'] ?? 0;
    $racional = (int) $_POST['racional'] ?? 0;
    $julgador = (int) $_POST['julgador'] ?? 0;

    $stmt = $pdo->prepare("REPLACE INTO teste_personalidade (user_id, extrovertido, intuitivo, racional, julgador) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $extrovertido, $intuitivo, $racional, $julgador]);
    header("Location: user.php");
    exit;
}

$profilePicture = !empty($user['profile_picture']) ? $user['profile_picture'] : "img/default.png";

$stmt = $pdo->prepare("SELECT * FROM teste_personalidade WHERE user_id = ?");
$stmt->execute([$user_id]);
$personalidade = $stmt->fetch(PDO::FETCH_ASSOC);
?>



<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projeto de vida - Estudante de programação</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="styles.css">
</head>

<body>

    <header id="navbar">
        <div class="container">
            <a href="index.php" class="logo">Projeto de <span>Vida</span></a>
            <nav>
                <ul class="desktop-nav">
                    <li><a href="index.php">Início</a></li>
                    <li><a href="index.php">Sobre</a></li>
                    <li><a href="index.php">Educação</a></li>
                    <li><a href="index.php">Carreira</a></li>
                    <li><a href="index.php">Contato</a></li>
                    <li><a href="user.php">Perfil</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section class="profile-container">
        <div class="profile-pic-container">
            <img src="<?= htmlspecialchars($profilePicture) ?>" alt="Foto de Perfil">

            <div class="upload-overlay">
                <label for="profile_picture">Alterar Foto</label>
            </div>

            <form method="POST" action="user.php" enctype="multipart/form-data" id="uploadForm">
                <input type="file" id="profile_picture" name="profile_picture" required>
                <button type="submit" class="upload-btn">Enviar</button>
            </form>
        </div>

        <!-- Informações do usuário -->
        <div class="user-info">
            <?php if ($authType === 'normal'): ?>
                <?php
                if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['edit_usuario'])) {
                    $new_name = trim($_POST['name']);
                    $new_email = trim($_POST['email']);

                    if (!empty($new_name) && !empty($new_email)) {
                        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
                        if ($stmt->execute([$new_name, $new_email, $user['id']])) {
                            $username = $new_name;
                            $user['email'] = $new_email;
                            echo "<p style='color: green;'>Dados atualizados com sucesso.</p>";
                        } else {
                            echo "<p style='color: red;'>Erro ao atualizar dados.</p>";
                        }
                    }
                }
                ?>

                <form class="user-info" method="POST">
                    <h2>name de Usuário:</h2>
                    <input type="text" name="name" value="<?= htmlspecialchars($username) ?>">

                    <h2>Email:</h2>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>">

                    <button type="submit" name="edit_usuario" class="btn">Salvar Alterações</button>
                </form>

            <?php elseif ($authType === 'google' && isset($info)): ?>
                <h2>name de Usuário: <?= htmlspecialchars($info['name'] ?? 'name não disponível') ?></h2>
                <h2>Email: <?= htmlspecialchars($info['email'] ?? 'Email não disponível') ?></h2>
            <?php else: ?>
                <h2>name de Usuário: Não disponível</h2>
                <h2>Email: Não disponível</h2>
            <?php endif; ?>

            <a href="logout.php"><button class="logout-button">Sair da conta</button></a>
        </div>

        <!-- Descrição -->
        <h3>Descrição:</h3>
        <form method="POST">
            <textarea name="description" rows="5"><?= htmlspecialchars($user['description'] ?? '') ?></textarea>
            <button class="form-group" type="submit">Salvar</button>
        </form>
        <style>
            .form-grid {
                display: flex;
                flex-direction: column;
                align-items: center;
            }

            .question {
                width: 100%;
                max-width: 650px;
                display: none;
                animation: fadeIn 0.4s ease-in-out;
                margin-bottom: 20px;
                background: #f0f0f0;
                padding: 15px;
                border-radius: 8px;
                box-shadow: 0 0 8px rgba(0, 0, 0, 0.05);
            }

            .question.active {
                display: block;
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translateY(10px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }


            .resultado {
                background: #e7f3ff;
                border: 1px solid #b3d7ff;
                padding: 15px;
                border-radius: 8px;
                margin-top: 20px;
                display: none;
            }
        </style>
        <br>
        <br>
        <hr>
        <br>
        <br>
        <!-- Formulário "Quem Sou Eu?" -->
        <h3>Formulário "Quem Sou Eu"</h3>
        <?php if (!empty($perfil)): ?>
            <button class="btn" id="startForm">Refazer Formulário</button>
            <button class="btn" id="mostrarResultados">Exibir resultados do formulário</button>
        <?php else: ?>
            <button class="btn" id="startForm">Começar Formulário</button>
        <?php endif; ?>


        <form class="form-grid" method="post" id="formularioQuemSouEu">


            <?php
            $aptidoes = ['Liderança', 'Empatia', 'Organização', 'Criatividade', 'Comunicação'];
            foreach ($aptidoes as $apt) {
                $checked = (isset($user['aptidoes']) && in_array($apt, explode(',', $user['aptidoes']))) ? 'checked' : '';
                echo "<label><input type='checkbox' name='aptidoes[]' value='$apt' $checked> $apt</label><br>";
            }
            ?>

            <!-- Relacionamentos -->
            <label for="familia">Família:</label>
            <input type="text" name="familia" value="<?= htmlspecialchars($user['familia'] ?? '') ?>">

            <label for="amigos">Amigos:</label>
            <input type="text" name="amigos" value="<?= htmlspecialchars($user['amigos'] ?? '') ?>">

            <label for="escola">Escola:</label>
            <input type="text" name="escola" value="<?= htmlspecialchars($user['escola'] ?? '') ?>">

            <label for="sociedade">Sociedade:</label>
            <input type="text" name="sociedade" value="<?= htmlspecialchars($user['sociedade'] ?? '') ?>">

            <!-- Meu Dia a Dia -->
            <label for="gosto_fazer">O que gosto de fazer:</label>
            <input type="text" name="gosto_fazer" value="<?= htmlspecialchars($user['gosto_fazer'] ?? '') ?>">

            <label for="nao_gosto">O que não gosto:</label>
            <input type="text" name="nao_gosto" value="<?= htmlspecialchars($user['nao_gosto'] ?? '') ?>">

            <label for="rotina">Rotina:</label>
            <input type="text" name="rotina" value="<?= htmlspecialchars($user['rotina'] ?? '') ?>">

            <label for="lazer">Lazer:</label>
            <input type="text" name="lazer" value="<?= htmlspecialchars($user['lazer'] ?? '') ?>">

            <label for="estudos">Estudos:</label>
            <input type="text" name="estudos" value="<?= htmlspecialchars($user['estudos'] ?? '') ?>">

            <!-- Vida Escolar -->
            <label for="vida_escolar">Minha Vida Escolar:</label>
            <textarea name="vida_escolar" rows="3"><?= htmlspecialchars($user['vida_escolar'] ?? '') ?></textarea>

            <!-- Minha Visão Sobre Mim -->
            <label for="visao_fisica">Visão Física:</label>
            <input type="text" name="visao_fisica" value="<?= htmlspecialchars($user['visao_fisica'] ?? '') ?>">

            <label for="visao_intelectual">Visão Intelectual:</label>
            <input type="text" name="visao_intelectual" value="<?= htmlspecialchars($user['visao_intelectual'] ?? '') ?>">

            <label for="visao_emocional">Visão Emocional:</label>
            <input type="text" name="visao_emocional" value="<?= htmlspecialchars($user['visao_emocional'] ?? '') ?>">

            <!-- Visão das Pessoas Sobre Mim -->
            <label for="visao_amigos">O que meus amigos dizem:</label>
            <input type="text" name="visao_amigos" value="<?= htmlspecialchars($user['visao_amigos'] ?? '') ?>">

            <label for="visao_familiares">O que minha família diz:</label>
            <input type="text" name="visao_familiares" value="<?= htmlspecialchars($user['visao_familiares'] ?? '') ?>">

            <label for="visao_professores">O que meus professores dizem:</label>
            <input type="text" name="visao_professores" value="<?= htmlspecialchars($user['visao_professores'] ?? '') ?>">

            <!-- Autovalorização (exemplo simplificado) -->
            <label for="autovalorizacao">Como você se sente sobre você mesmo(a)?</label>
            <select name="autovalorizacao">
                <option value="1" <?= ($user['autovalorizacao'] ?? '') == '1' ? 'selected' : '' ?>>1 - Muito Ruim</option>
                <option value="2" <?= ($user['autovalorizacao'] ?? '') == '2' ? 'selected' : '' ?>>2</option>
                <option value="3" <?= ($user['autovalorizacao'] ?? '') == '3' ? 'selected' : '' ?>>3 - Regular</option>
                <option value="4" <?= ($user['autovalorizacao'] ?? '') == '4' ? 'selected' : '' ?>>4</option>
                <option value="5" <?= ($user['autovalorizacao'] ?? '') == '5' ? 'selected' : '' ?>>5 - Excelente</option>
            </select>

            <button type="submit">Salvar Formulário</button>
        </form>

        <div class="resultado" id="resultadoPerfil">
            <h3>Resultados do Formulário</h3>
            <?php
            if (!empty($perfil)) {
                foreach ($perfil as $chave => $valor) {
                    if ($chave === 'id' || $chave === 'user_id') {
                        if ($chave === 'user_id') {
                            echo "<p><strong>Nome do Usuário:</strong> " . htmlspecialchars($username) . "</p>";
                        }
                        continue;
                    }
                    echo "<p><strong>" . ucfirst(str_replace('_', ' ', $chave)) . ":</strong> " . nl2br(htmlspecialchars($valor)) . "</p>";
                }
            } else {
                echo "<p>Nenhum dado disponível.</p>";
            }
            ?>
        </div>
    </section>

    <hr>
    <br>
    <br>
    <br>
    <br>


    <hr>
    <br>
    <br>
    <br>
    <br>
    <!-- Quiz teste de personalidade-->
    <h3>(Quiz) - Teste de personalidade -</h3>
    <button class="btn" id="comecarQuiz">Começar Quiz!</button>
    <form id="quizForm" method="POST" style="display: none;">
        <input type="hidden" name="teste_personalidade" value="1">

        <div class="quiz-question">
            <label>Você se considera extrovertido? (0 a 100)</label>
            <input type="range" name="extrovertido" min="0" max="100">
            <button type="button" class="proximoQuiz">Próxima</button>
        </div>

        <div class="quiz-question" style="display:none;">
            <label>Você confia mais na intuição do que nos fatos? (0 a 100)</label>
            <input type="range" name="intuitivo" min="0" max="100">
            <button type="button" class="proximoQuiz">Próxima</button>
        </div>

        <div class="quiz-question" style="display:none;">
            <label>Toma decisões com base na lógica? (0 a 100)</label>
            <input type="range" name="racional" min="0" max="100">
            <button type="button" class="proximoQuiz">Próxima</button>
        </div>

        <div class="quiz-question" style="display:none;">
            <label>Você prefere organização e planejamento? (0 a 100)</label>
            <input type="range" name="julgador" min="0" max="100">
            <button type="submit">Salvar Teste</button>
        </div>
    </form>

    <?php if ($personalidade): ?>
        <h3>Resultado do Teste de Personalidade</h3>
        <canvas id="graficoPersonalidade" width="400" height="200"></canvas>
    <?php endif; ?>
    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
    <!-- script -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const perguntas = document.querySelectorAll('.question');
            const botaoIniciar = document.getElementById('startForm');
            const botaoMostrarResultados = document.getElementById('mostrarResultados');
            const resultado = document.getElementById('resultadoPerfil');

            let indice = 0;

            function mostrarPergunta(index) {
                perguntas.forEach((p, i) => {
                    p.classList.remove('active');
                    if (i === index) {
                        p.classList.add('active');
                    }
                });
            }

            function proximo() {
                if (indice < perguntas.length - 1) {
                    indice++;
                    mostrarPergunta(indice);
                } else {
                    mostrarPergunta(indice);
                }
            }

            if (botaoIniciar) {
                botaoIniciar.addEventListener('click', () => {
                    botaoIniciar.style.display = 'none';
                    mostrarPergunta(indice);
                });
            }

            if (botaoMostrarResultados) {
                botaoMostrarResultados.addEventListener('click', function() {
                    if (resultado.style.display === "none" || resultado.style.display === "") {
                        resultado.style.display = "block";
                        this.textContent = "Ocultar resultados do formulário";
                        window.scrollTo({
                            top: resultado.offsetTop,
                            behavior: 'smooth'
                        });
                    } else {
                        resultado.style.display = "none";
                        this.textContent = "Exibir resultados do formulário";
                    }
                });
            }

            perguntas.forEach((p) => {
                if (p.id !== 'final') {
                    const btnProximo = document.createElement('button');
                    btnProximo.textContent = 'Próxima';
                    btnProximo.type = 'button';
                    btnProximo.className = 'btn';
                    btnProximo.style.marginTop = '10px';
                    btnProximo.addEventListener('click', proximo);
                    p.appendChild(btnProximo);
                }

                const input = p.querySelector('textarea, input');
                if (input) {
                    input.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter' && !e.shiftKey) {
                            e.preventDefault();
                            proximo();
                        }
                    });
                }
            });
        });
    </script>

    <!--script quiz-->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const quizForm = document.getElementById('quizForm');
            const comecarQuiz = document.getElementById('comecarQuiz');
            const perguntasQuiz = quizForm.querySelectorAll('.quiz-question');
            let indiceQuiz = 0;

            comecarQuiz.addEventListener('click', () => {
                comecarQuiz.style.display = 'none';
                quizForm.style.display = 'block';
                perguntasQuiz[indiceQuiz].style.display = 'block';
            });

            const botoesProximo = quizForm.querySelectorAll('.proximoQuiz');
            botoesProximo.forEach((botao, i) => {
                botao.addEventListener('click', () => {
                    perguntasQuiz[i].style.display = 'none';
                    if (i + 1 < perguntasQuiz.length) {
                        perguntasQuiz[i + 1].style.display = 'block';
                    }
                });
            });

            <?php if ($personalidade): ?>
                const ctx = document.getElementById('graficoPersonalidade');
                new Chart(ctx, {
                    type: 'radar',
                    data: {
                        labels: ['Extrovertido', 'Intuitivo', 'Racional', 'Julgador'],
                        datasets: [{
                            label: 'Perfil de Personalidade',
                            data: [
                                <?= $personalidade['extrovertido'] ?>,
                                <?= $personalidade['intuitivo'] ?>,
                                <?= $personalidade['racional'] ?>,
                                <?= $personalidade['julgador'] ?>
                            ],
                            backgroundColor: 'rgba(54, 162, 235, 0.2)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        scales: {
                            r: {
                                min: 0,
                                max: 100
                            }
                        }
                    }
                });
            <?php endif; ?>
        });
    </script>



</body>

</html>