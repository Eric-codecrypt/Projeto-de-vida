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
    $extrovertido = (int) ($_POST['extrovertido'] ?? 0);
    $intuitivo = (int) ($_POST['intuitivo'] ?? 0);
    $racional = (int) ($_POST['racional'] ?? 0);
    $julgador = (int) ($_POST['julgador'] ?? 0);

    // Salva os dados no banco
    $stmt = $pdo->prepare("REPLACE INTO teste_personalidade (user_id, extrovertido, intuitivo, racional, julgador) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $extrovertido, $intuitivo, $racional, $julgador]);

    // Redireciona após o envio
    header("Location: user.php");
    exit;
}

// Busca os dados do banco
$stmt = $pdo->prepare("SELECT * FROM teste_personalidade WHERE user_id = ?");
$stmt->execute([$user_id]);
$personalidade = $stmt->fetch(PDO::FETCH_ASSOC);

// Define traço dominante, se houver dados
$traço_dominante = '';
if ($personalidade) {
    $dados = [
        'Extrovertido' => (int) $personalidade['extrovertido'],
        'Intuitivo' => (int) $personalidade['intuitivo'],
        'Racional' => (int) $personalidade['racional'],
        'Julgador' => (int) $personalidade['julgador']
    ];
    arsort($dados);
    $traço_dominante = array_key_first($dados);
}

$profilePicture = !empty($user['profile_picture']) ? $user['profile_picture'] : "img/default.png";

//progresso
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['adicionar_meta'])) {
    $titulo = trim($_POST['titulo']);
    $descricao = trim($_POST['descricao']);
    $prazo = $_POST['prazo'];

    if (!empty($titulo) && !empty($prazo)) {
        $stmt = $pdo->prepare("INSERT INTO plano_acao (user_id, titulo, descricao, prazo) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $titulo, $descricao, $prazo]);
        header("Location: user.php"); // atualiza a página
        exit;
    }
}

$proximasMetas = []; // valor padrão

// Buscar metas pendentes com prazo futuro
$stmt = $pdo->prepare("SELECT * FROM plano_acao WHERE user_id = ? AND concluida = 0 AND prazo >= CURDATE() ORDER BY prazo ASC");
$stmt->execute([$user_id]);
$proximasMetas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalMetas = $dadosPlano['total'] ?? 0;
$metasFeitas = $dadosPlano['feitas'] ?? 0;

$porcentagemConcluida = $totalMetas > 0 ? round(($metasFeitas / $totalMetas) * 100) : 0;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['concluir_meta'])) {
    $metaId = (int) $_POST['concluir_meta'];
    $stmt = $pdo->prepare("UPDATE plano_acao SET concluida = 1 WHERE id = ? AND user_id = ?");
    $stmt->execute([$metaId, $user_id]);
    header("Location: user.php");
    exit;
}

// Busca todas as metas do usuário
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM plano_acao WHERE user_id = ?");
$stmt->execute([$user_id]);
$totalMetas = $stmt->fetchColumn();

// Busca metas concluídas
$stmt = $pdo->prepare("SELECT COUNT(*) as concluidas FROM plano_acao WHERE user_id = ? AND concluida = 1");
$stmt->execute([$user_id]);
$concluidas = $stmt->fetchColumn();

// Evita divisão por zero
$percentualConcluido = ($totalMetas > 0) ? round(($concluidas / $totalMetas) * 100) : 0;


// Salvando a landing personalizada
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salvar_landing'])) {
    $titulo = $_POST['titulo_principal'];
    $subtitulo = $_POST['subtitulo'];
    $sobre = $_POST['sobre'];
    $educacao = $_POST['educacao'];
    $carreira = $_POST['carreira'];
    $contato = $_POST['contato'];

    $stmt = $pdo->prepare("SELECT id FROM landing_pages WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $exists = $stmt->fetch();

    if ($exists) {
        $stmt = $pdo->prepare("UPDATE landing_pages SET titulo_principal = ?, subtitulo = ?, sobre = ?, educacao = ?, carreira = ?, contato = ? WHERE user_id = ?");
        $stmt->execute([$titulo, $subtitulo, $sobre, $educacao, $carreira, $contato, $user['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO landing_pages (user_id, titulo_principal, subtitulo, sobre, educacao, carreira, contato) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user['id'], $titulo, $subtitulo, $sobre, $educacao, $carreira, $contato]);
    }

    echo "<p style='color:green;'>Landing page atualizada com sucesso!</p>";
}


//landings privadas para um usuario 
if (!$user_id) {
    echo "Acesso não autorizado.";
    exit;
}

// Verifica se o usuário é 'Eric'
$isEric = strtolower($username) === 'eric';

// Verifica se já existe uma landing page
$stmt = $pdo->prepare("SELECT id FROM landing_pages WHERE user_id = ?");
$stmt->execute([$user_id]);
$landing = $stmt->fetch();
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
    <!--perfil css-->
    <style>
        .profile-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            max-width: 600px;
            margin: 40px auto;
            padding: 30px;
            border-radius: 16px;
            background-color: #f4f6fa;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .profile-pic-container {
            position: relative;
            width: 160px;
            height: 160px;
            margin-bottom: 20px;
        }

        .profile-pic-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid #4a90e2;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .upload-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.6);
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
            font-size: 14px;
            text-align: center;
            cursor: pointer;
        }

        .profile-pic-container:hover .upload-overlay {
            opacity: 1;
        }

        .upload-overlay label {
            cursor: pointer;
        }

        #profile_picture {
            display: none;
        }

        .upload-btn {
            margin-top: 10px;
            background-color: #4a90e2;
            color: #fff;
            padding: 8px 14px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
        }

        .upload-btn:hover {
            background-color: #357abd;
        }

        .user-info {
            width: 100%;
            margin-top: 10px;
        }

        .user-info h2 {
            margin: 12px 0 6px;
            font-size: 18px;
            color: #333;
        }

        .user-info input {
            width: 100%;
            padding: 10px;
            margin-bottom: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 15px;
        }

        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #3e8e41;
        }

        .logout-button {
            margin-top: 20px;
            background-color: #e74c3c;
            color: white;
            padding: 10px 18px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        .logout-button:hover {
            background-color: #c0392b;
        }

        h3 {
            margin-top: 40px;
            font-size: 20px;
            color: #333;
            align-self: flex-start;
        }

        textarea {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 15px;
            resize: vertical;
            margin-bottom: 12px;
        }

        .form-group {
            background-color: #4a90e2;
            color: white;
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        .form-group:hover {
            background-color: #357abd;
        }

        @media (max-width: 600px) {
            .profile-container {
                padding: 20px;
            }

            .profile-pic-container {
                width: 120px;
                height: 120px;
            }
        }
    </style>

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
                    <h2>Nome de Usuário:</h2>
                    <input type="text" name="name" value="<?= htmlspecialchars($username) ?>">

                    <h2>Email:</h2>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>">

                    <button type="submit" name="edit_usuario" class="btn">Salvar Alterações</button>
                </form>

            <?php elseif ($authType === 'google' && isset($info)): ?>
                <h2>Nome de Usuário: <?= htmlspecialchars($info['name'] ?? 'name não disponível') ?></h2>
                <h2>Email: <?= htmlspecialchars($info['email'] ?? 'Email não disponível') ?></h2>
            <?php else: ?>
                <h2>Nome de Usuário: Não disponível</h2>
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
            <br>
            <button class="btn" id="startForm">Refazer Formulário</button>
            <br>
            <button class="btn" id="mostrarResultados">Exibir resultados do formulário</button>
        <?php else: ?>
            <br>
            <button class="btn" id="startForm">Começar Formulário</button>
        <?php endif; ?>


        <form class="form-grid" method="post" id="formularioQuemSouEu">


            <?php
            $perguntas = [
                'sobre_voce' => 'Fale sobre você:',
                'lembrancas' => 'Minhas lembranças:',
                'pontos_fortes' => 'Pontos fortes:',
                'pontos_fracos' => 'Pontos fracos:',
                'valores' => 'Meus valores:',
                'aptidoes' => 'Principais aptidões (separe com vírgulas):',
                'familia' => 'Relação com a família:',
                'amigos' => 'Relação com amigos:',
                'escola' => 'Relação com a escola:',
                'sociedade' => 'Relação com a sociedade:',
                'gosto_fazer' => 'O que gosto de fazer:',
                'nao_gosto' => 'O que não gosto de fazer:',
                'rotina' => 'Minha rotina:',
                'lazer' => 'Meu lazer:',
                'estudos' => 'Meus estudos:',
                'vida_escolar' => 'Minha vida escolar:',
                'visao_fisica' => 'Visão física:',
                'visao_intelectual' => 'Visão intelectual:',
                'visao_emocional' => 'Visão emocional:',
                'visao_amigos' => 'O que meus amigos dizem sobre mim:',
                'visao_familiares' => 'O que meus familiares dizem sobre mim:',
                'visao_professores' => 'O que meus professores dizem sobre mim:'
            ];
            $index = 0;
            foreach ($perguntas as $campo => $texto) {
                echo "<div class='question' id='q$index'>";
                echo "<label for='$campo'>$texto</label>";
                if ($campo === 'aptidoes') {
                    echo "<input type='text' name='aptidoes[]' value='" . htmlspecialchars($perfil['principais_aptidoes'] ?? '') . "'>";
                } else {
                    echo "<textarea name='$campo'>" . htmlspecialchars($perfil[$campo] ?? '') . "</textarea>";
                }
                echo "</div>";
                $index++;
            }
            ?>
            <div class="question" id="final">
                <label for="autovalorizacao">Autovalorização total(0-100):</label>
                <input type="number" name="autovalorizacao" min="0" max="100" value="<?= htmlspecialchars($perfil['autovalorizacao_total'] ?? 0) ?>">
                <button type="submit" name="salvar_perfil_completo" class="btn">Salvar Perfil</button>
            </div>
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


    <br>
    <br>
    <br>
    <br>
    <hr>
    <!--css progresso-->
    <style>
        .proximas-metas {
            list-style: none;
            padding-left: 0;
            margin-top: 10px;
        }

voltar-ao-ponto
        .proximas-metas li {
            background: #f9f9f9;
            border-left: 4px solid #4caf50;
            padding: 10px 12px;
            margin-bottom: 10px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
        }

        .prazo {
            color: #777;
            font-size: 0.9em;
        }
    </style>

    <h3>Progresso das Metas/Tarefas</h3>
    <canvas id="graficoProgresso" width="300" height="300"></canvas>
    <h3>Nova Tarefa ou Meta</h3>
    <form method="POST" action="">
        <input type="text" name="titulo" placeholder="Título da Meta" required><br>
        <textarea name="descricao" placeholder="Descrição (opcional)"></textarea><br>
        <label>Prazo:</label>
        <input type="date" name="prazo" required><br>
        <button type="submit" name="adicionar_meta">Adicionar</button>
    </form>
    <h3>Próximas Tarefas/Metas</h3>
    <ul class="proximas-metas">
        <?php if (!empty($proximasMetas)): ?>
            <?php foreach ($proximasMetas as $meta): ?>
                <li style="<?= $meta['concluida'] ? 'text-decoration: line-through; color: gray;' : '' ?>">
                    <strong><?= htmlspecialchars($meta['titulo']) ?></strong>
                    <span class="prazo">→ até <?= date('d/m/Y', strtotime($meta['prazo'])) ?></span>

                    <?php if (!$meta['concluida']): ?>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="concluir_meta" value="<?= $meta['id'] ?>">
                            <button type="submit">Concluir</button>
                        </form>
                    <?php else: ?>
                        <span>✅ Concluída</span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li>Nenhuma tarefa pendente encontrada.</li>
        <?php endif; ?>
    </ul>

    <hr>

    <br>
    <br>
    <br>
    <br>
    <style>
        /* Estilo geral do quiz */
        #quizForm,
        .quiz-question {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        h3 {
            text-align: center;
            color: #333;
            font-weight: 600;
        }

        label {
            display: block;
            margin-bottom: 15px;
            font-size: 16px;
            color: #444;
        }

        input[type="range"] {
            width: 100%;
            margin-bottom: 20px;
        }

        .btn,
        .proximoQuiz,
        form button[type="submit"] {
            background-color: #4a90e2;
            color: #fff;
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }

        .btn:hover,
        .proximoQuiz:hover,
        form button[type="submit"]:hover {
            background-color: #357abd;
        }

        canvas {
            display: block;
            margin: 40px auto;
            max-width: 100%;
        }

        .quiz-question {
            display: none;
            animation: fadeIn 0.5s ease-in-out;
        }

        .quiz-question:first-of-type {
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

        .highlight-result {
            text-align: center;
            font-size: 18px;
            color: #333;
            margin-top: 30px;
            background: #e8f0fe;
            padding: 15px;
            border-left: 5px solid #4a90e2;
            border-radius: 10px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
    </style>

    <!-- Quiz teste de personalidade-->
    <h3>(Quiz) - Teste de personalidade -</h3>
    <br>
    <br>
    <br>
    <br>
    <br>

    <button class="btn btn-primary" style="justify-content: center;" id="comecarQuiz">Começar Quiz!</button>

    <form id="quizForm" method="POST" style="display: none;">
        <input type="hidden" name="teste_personalidade" value="1">

        <div class="quiz-question">
            <label>Você se considera extrovertido? (0 a 100)</label>
            <input type="range" name="extrovertido" min="0" max="100">
            <button type="button" class="proximoQuiz">Próxima</button>
        </div>

        <div class="quiz-question">
            <label>Você confia mais na intuição do que nos fatos? (0 a 100)</label>
            <input type="range" name="intuitivo" min="0" max="100">
            <button type="button" class="proximoQuiz">Próxima</button>
        </div>

        <div class="quiz-question">
            <label>Toma decisões com base na lógica? (0 a 100)</label>
            <input type="range" name="racional" min="0" max="100">
            <button type="button" class="proximoQuiz">Próxima</button>
        </div>

        <div class="quiz-question">
            <label>Você prefere organização e planejamento? (0 a 100)</label>
            <input type="range" name="julgador" min="0" max="100">
            <button type="submit">Salvar Teste</button>
        </div>
    </form>

    <?php if ($personalidade): ?>
        <?php if ($traço_dominante): ?>
            <div class="highlight-result">
                <?php if ($traço_dominante === "Racional"): ?>
                    <strong>Ótimo!</strong> Você é <strong>Racional</strong>: lógico e analítico!
                <?php elseif ($traço_dominante === "Extrovertido"): ?>
                    <strong>Ótimo!</strong> Você é <strong>Extrovertido</strong>: comunicativo e sociável!
                <?php elseif ($traço_dominante === "Intuitivo"): ?>
                    <strong>Ótimo!</strong> Você é <strong>Intuitivo</strong>: guiado pela sua intuição!
                <?php elseif ($traço_dominante === "Julgador"): ?>
                    <strong>Ótimo!</strong> Você é <strong>Julgador</strong>: organizado e planejador!
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <h3>Resultado do Teste de Personalidade</h3>
        <canvas id="graficoPersonalidade" width="600" height="400"></canvas>
    <?php endif; ?>










    <hr>







    <!-- Botão para editar a própria landing page -->


    <!-- Lista todas as landing pages - somente visível para Eric -->
    <?php if ($isEric): ?>
        <h3>Landing Pages Criadas por Todos os Usuários</h3>
        <ul>
            <?php
            $stmt = $pdo->query("SELECT lp.*, u.username FROM landing_pages lp JOIN users u ON lp.user_id = u.id");
            while ($row = $stmt->fetch()):
            ?>
                <li>
                    <strong><?= htmlspecialchars($row['username']) ?>:</strong>
                    <a href="landing.php?usuario=<?= urlencode($row['username']) ?>">Ver Landing Page</a>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <?php if($landing): ?>
        <h3>Minha landing page</h3>
        <a href="landing.php">
            <button>Minha landing page</button>
        </a>
        <?php else: ?>
            <a href="landing.php">
            <button>Criar Minha Landing Page</button>
        </a>
        <?php endif ?>
        <a href="editar_landing.php">
            <button>Editar Minha Landing Page</button>
        </a>
    <?php endif ?>

















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


    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('graficoProgresso').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Concluídas', 'Pendentes'],
                datasets: [{
                    data: [<?= $concluidas ?>, <?= $totalMetas - $concluidas ?>],
                    backgroundColor: ['#0064fa', '#64748b'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>

</html>