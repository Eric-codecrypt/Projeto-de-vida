<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Sistema de mensagens para o usuário
$errorMessage = $_SESSION['error_message'] ?? null;
$successMessage = $_SESSION['success_message'] ?? null;
$uploadError = $_SESSION['upload_error'] ?? null;

// Limpar mensagens da sessão após uso
unset($_SESSION['error_message'], $_SESSION['success_message'], $_SESSION['upload_error']);


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

// No início do arquivo, após buscar os dados do usuário
$themeColor = $user['theme_color'] ?? 'theme-base';

// No bloco de processamento POST
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['theme_color'])) {
    $newTheme = $_POST['theme_color'];
    $validThemes = [
        'theme-base', 'theme-red', 'theme-green', 'theme-blue',
        'theme-yellow', 'theme-purple', 'theme-pink', 'theme-teal',
        'theme-orange', 'theme-brown', 'theme-gray'
    ];



    if (isset($_POST['theme_color'])) {
        $newTheme = $_POST['theme_color'];
        // Validar o tema para segurança
        $validThemes = ['theme-base', 'theme-red', 'theme-green', 'theme-blue',
            'theme-yellow', 'theme-purple', 'theme-pink', 'theme-teal',
            'theme-orange', 'theme-brown', 'theme-gray'];

        if (in_array($newTheme, $validThemes)) {
            $stmt = $pdo->prepare("UPDATE users SET theme_color = ? WHERE id = ?");
            $stmt->execute([$newTheme, $user_id]);
            exit(); // Como estamos usando AJAX, podemos simplesmente sair
        }


        // Se for uma requisição AJAX, retorne um status
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            echo json_encode(['success' => true]);
            exit;
        }

        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}


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

        $errors = [];

        if (empty($new_name)) {
            $errors[] = "O nome de usuário não pode estar vazio.";
        }

        if (empty($new_email)) {
            $errors[] = "O email não pode estar vazio.";
        } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Por favor, forneça um email válido.";
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            $stmt->execute([$new_name, $new_email, $user_id]);
            $username = $new_name;
            $user['email'] = $new_email;
            $_SESSION['success_message'] = "Perfil atualizado com sucesso!";
        } else {
            $_SESSION['error_message'] = implode("<br>", $errors);
        }

        header("Location: user.php");
        exit();
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

    if ($file["error"] !== 0) {
        // Adicionar mensagem de erro específica baseada no código de erro
        $errorMessages = [
            1 => "O arquivo excede o tamanho máximo permitido pelo servidor.",
            2 => "O arquivo excede o tamanho máximo permitido pelo formulário.",
            3 => "O upload do arquivo foi feito parcialmente.",
            4 => "Nenhum arquivo foi enviado.",
            6 => "Pasta temporária ausente.",
            7 => "Falha ao escrever arquivo no disco.",
            8 => "Uma extensão PHP interrompeu o upload do arquivo."
        ];
        $_SESSION['upload_error'] = $errorMessages[$file["error"]] ?? "Erro desconhecido no upload.";
    } else {
        $allowedTypes = ["image/jpeg", "image/png", "image/gif", "image/webp"];
        $fileType = mime_content_type($file["tmp_name"]);

        if (in_array($fileType, $allowedTypes)) {
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileName = uniqid() . "_" . basename($file["name"]);
            $filePath = "img/" . $fileName;

            if (move_uploaded_file($file["tmp_name"], $uploadDir . $fileName)) {
                $stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
                $stmt->execute([$filePath, $user_id]);
                $_SESSION['success_message'] = "Foto de perfil atualizada com sucesso!";
                header("Location: user.php");
                exit();
            } else {
                $_SESSION['upload_error'] = "Erro ao mover o arquivo.";
            }
        } else {
            $_SESSION['upload_error'] = "Formato inválido. Use JPG, PNG ou GIF.";
        }
    }
    header("Location: user.php");
    exit();
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
    
    $errors = [];
    
    // Validação básica
    if (empty($titulo)) {
        $errors[] = "O título da meta é obrigatório.";
    }
    
    if (empty($prazo)) {
        $errors[] = "O prazo da meta é obrigatório.";
    } else {
        // Verificar se a data é válida e não está no passado
        $prazo_timestamp = strtotime($prazo);
        $hoje_timestamp = strtotime(date('Y-m-d'));
        
        if ($prazo_timestamp === false) {
            $errors[] = "Por favor, informe uma data válida.";
        } elseif ($prazo_timestamp < $hoje_timestamp) {
            $errors[] = "O prazo da meta não pode ser uma data passada.";
        }
    }
    
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO plano_acao (user_id, titulo, descricao, prazo) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $titulo, $descricao, $prazo]);
        $_SESSION['success_message'] = "Meta adicionada com sucesso!";
        header("Location: user.php");
        exit;
    } else {
        $_SESSION['error_message'] = implode("<br>", $errors);
        header("Location: user.php");
        exit;
    }
}

$proximasMetas = [];

// Buscar metas pendentes com prazo futuro
$stmt = $pdo->prepare("SELECT * FROM plano_acao WHERE user_id = ? AND concluida = 0 AND prazo >= CURDATE() ORDER BY prazo ASC");
$stmt->execute([$user_id]);
$proximasMetas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar metas vencidas (pendentes com prazo passado)
$stmt = $pdo->prepare("SELECT * FROM plano_acao WHERE user_id = ? AND concluida = 0 AND prazo < CURDATE() ORDER BY prazo DESC");
$stmt->execute([$user_id]);
$metasVencidas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Busca todas as metas do usuário (exceto as vencidas para o cálculo de progresso)
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM plano_acao WHERE user_id = ? AND (concluida = 1 OR prazo >= CURDATE())");
$stmt->execute([$user_id]);
$totalMetas = $stmt->fetchColumn();

// Busca metas concluídas
$stmt = $pdo->prepare("SELECT COUNT(*) as concluidas FROM plano_acao WHERE user_id = ? AND concluida = 1");
$stmt->execute([$user_id]);
$metasFeitas = $stmt->fetchColumn();

$porcentagemConcluida = $totalMetas > 0 ? round(($metasFeitas / $totalMetas) * 100) : 0;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['concluir_meta'])) {
    $metaId = (int) $_POST['concluir_meta'];
    $stmt = $pdo->prepare("UPDATE plano_acao SET concluida = 1 WHERE id = ? AND user_id = ?");
    $stmt->execute([$metaId, $user_id]);
    header("Location: user.php");
    exit;
}

// Adicione isso ao seu bloco de código PHP
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['excluir_meta'])) {
    $metaId = (int) $_POST['excluir_meta'];
    $stmt = $pdo->prepare("DELETE FROM plano_acao WHERE id = ? AND user_id = ?");
    $stmt->execute([$metaId, $user_id]);
    $_SESSION['success_message'] = "Meta excluída com sucesso!";
    header("Location: user.php");
    exit;
}


// Verificar se o formulário da landing page foi enviado
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['editar_landing'])) {
    $titulo_principal = trim($_POST['titulo_principal']);
    $subtitulo_principal = trim($_POST['subtitulo_principal']);
    $sobre = trim($_POST['sobre']);
    $educacao = trim($_POST['educacao']);
    $carreira = trim($_POST['carreira']);
    $contato = trim($_POST['contato']);
    $publico = isset($_POST['publico']) ? 1 : 0;
    
    // Validação básica
    if (empty($titulo_principal)) {
        $_SESSION['landing_error'] = "O título principal é obrigatório";
    } else {
        // Verificar se já existe um landing page para este usuário
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM landing_pages WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $existe = $stmt->fetchColumn();
        
        if ($existe) {
            // Atualizar landing page existente
            $stmt = $pdo->prepare("UPDATE landing_pages SET 
                titulo_principal = ?, 
                subtitulo_principal = ?, 
                sobre = ?, 
                educacao = ?, 
                carreira = ?, 
                contato = ?, 
                publico = ?,
                atualizado_em = NOW()
                WHERE user_id = ?");
            $stmt->execute([
                $titulo_principal, 
                $subtitulo_principal, 
                $sobre, 
                $educacao, 
                $carreira, 
                $contato, 
                $publico, 
                $user_id
            ]);
            $_SESSION['landing_success'] = "Seu landing page foi atualizado com sucesso!";
        } else {
            // Criar novo landing page
            $stmt = $pdo->prepare("INSERT INTO landing_pages 
                (user_id, titulo_principal, subtitulo_principal, sobre, educacao, carreira, contato, publico, criado_em, atualizado_em) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute([
                $user_id, 
                $titulo_principal, 
                $subtitulo_principal, 
                $sobre, 
                $educacao, 
                $carreira, 
                $contato, 
                $publico
            ]);
            $_SESSION['landing_success'] = "Seu landing page foi criado com sucesso!";
        }
    }
    
    header("Location: user.php#landing_pages");
    exit;
}
// Verificar se o usuário atual é o Eric (admin)
$is_admin = ($user_id == 8); // Eric tem ID 8

// Consulta para buscar os dados do landing page do próprio usuário
$stmt = $pdo->prepare("SELECT * FROM landing_pages WHERE user_id = ?");
$stmt->execute([$user_id]);
$landing = $stmt->fetch(PDO::FETCH_ASSOC);

// Listar todas as landing pages se for admin (Eric)
$all_landings = [];
if ($is_admin) {
    $stmt = $pdo->query("SELECT l.*, u.username 
                          FROM landing_pages l 
                          JOIN users u ON l.user_id = u.id 
                          ORDER BY l.titulo_principal ASC");
    $all_landings = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Se um ID específico for solicitado para visualização
$viewing_landing = null;
$viewing_user = null;
if (isset($_GET['view_landing'])) {
    $view_id = (int)$_GET['view_landing'];
    
    // Administrador pode ver qualquer landing page
    if ($is_admin) {
        $stmt = $pdo->prepare("SELECT l.*, u.username 
                              FROM landing_pages l 
                              JOIN users u ON l.user_id = u.id 
                              WHERE l.user_id = ?");
        $stmt->execute([$view_id]);
        $viewing_landing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($viewing_landing) {
            $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
            $stmt->execute([$view_id]);
            $viewing_user = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    } 
    // Usuários comuns só podem ver landing pages públicas
    else {
        $stmt = $pdo->prepare("SELECT l.*, u.username 
                              FROM landing_pages l 
                              JOIN users u ON l.user_id = u.id 
                              WHERE l.user_id = ? AND l.publico = 1");
        $stmt->execute([$view_id]);
        $viewing_landing = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($viewing_landing) {
            $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
            $stmt->execute([$view_id]);
            $viewing_user = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    }
}

// Adicione este código na seção onde você processa os formulários POST
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["background_image"])) {
    $file = $_FILES["background_image"];
    $uploadDir = __DIR__ . "/img/";

    // Dimensões desejadas
    $targetWidth = 1168;
    $targetHeight = 347.17;

    if ($file["error"] !== 0) {
        // Adicionar mensagem de erro específica baseada no código de erro
        $errorMessages = [
            1 => "O arquivo excede o tamanho máximo permitido pelo servidor.",
            2 => "O arquivo excede o tamanho máximo permitido pelo formulário.",
            3 => "O upload do arquivo foi feito parcialmente.",
            4 => "Nenhum arquivo foi enviado.",
            6 => "Pasta temporária ausente.",
            7 => "Falha ao escrever arquivo no disco.",
            8 => "Uma extensão PHP interrompeu o upload do arquivo."
        ];
        $_SESSION['upload_error'] = $errorMessages[$file["error"]] ?? "Erro desconhecido no upload do banner.";
    } else {
        $allowedTypes = ["image/jpeg", "image/png", "image/gif", "image/webp", "image/bmp", "image/x-icon", "image/vnd.microsoft.icon", "image/gif"];
        $fileType = mime_content_type($file["tmp_name"]);

        if (in_array($fileType, $allowedTypes)) {
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileName = "banner_" . uniqid() . ".jpg"; // Sempre salvar como JPG
            $filePath = "img/" . $fileName;
            $fullPath = $uploadDir . $fileName;

            // Redimensionar a imagem para as dimensões corretas
            if (function_exists('imagecreatefromstring')) {
                // Criar imagem a partir do arquivo enviado
                $sourceImage = imagecreatefromstring(file_get_contents($file["tmp_name"]));

                if ($sourceImage !== false) {
                    // Criar imagem de destino com as dimensões corretas
                    $targetImage = imagecreatetruecolor($targetWidth, round($targetHeight));

                    // Preservar transparência para PNG
                    if ($fileType == 'image/png') {
                        imagealphablending($targetImage, false);
                        imagesavealpha($targetImage, true);
                        $transparent = imagecolorallocatealpha($targetImage, 255, 255, 255, 127);
                        imagefilledrectangle($targetImage, 0, 0, $targetWidth, round($targetHeight), $transparent);
                    }

                    // Redimensionar
                    imagecopyresampled(
                        $targetImage,
                        $sourceImage,
                        0, 0, 0, 0,
                        $targetWidth,
                        round($targetHeight),
                        imagesx($sourceImage),
                        imagesy($sourceImage)
                    );

                    // Salvar imagem
                    switch ($fileType) {
                        case 'image/jpeg':
                            imagejpeg($targetImage, $fullPath, 90);
                            break;
                        case 'image/png':
                            imagepng($targetImage, $fullPath);
                            break;
                        case 'image/gif':
                            imagegif($targetImage, $fullPath);
                            break;
                        case 'image/webp':
                            imagewebp($targetImage, $fullPath, 90);
                            break;
                        default:
                            imagejpeg($targetImage, $fullPath, 90);
                    }

                    // Liberar memória
                    imagedestroy($sourceImage);
                    imagedestroy($targetImage);

                    // Atualizar banco de dados
                    $stmt = $pdo->prepare("UPDATE users SET background_image = ? WHERE id = ?");
                    $stmt->execute([$filePath, $user_id]);
                    $_SESSION['success_message'] = "Banner atualizado com sucesso!";
                } else {
                    $_SESSION['upload_error'] = "Não foi possível processar a imagem enviada.";
                }
            } else {
                // Se a GD não estiver disponível, apenas mova o arquivo
                if (move_uploaded_file($file["tmp_name"], $fullPath)) {
                    $stmt = $pdo->prepare("UPDATE users SET background_image = ? WHERE id = ?");
                    $stmt->execute([$filePath, $user_id]);
                    $_SESSION['success_message'] = "Banner atualizado com sucesso! (nota: a imagem não foi redimensionada)";
                } else {
                    $_SESSION['upload_error'] = "Falha ao mover o arquivo enviado.";
                }
            }
        } else {
            $_SESSION['upload_error'] = "Formato de arquivo não permitido. Por favor, envie uma imagem JPG, PNG, GIF ou WebP.";
        }
    }
    header("Location: user.php");
    exit();
}



?>

<!DOCTYPE html>
<html lang="pt-BR" class="<?php echo htmlspecialchars($themeColor); ?>" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil do Usuário</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="user-styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAOEAAADhCAMAAAAJbSJIAAAAclBMVEX///8hlvMAkPLF4Pvr9v6hzfklmfMAj/Itm/Tv+P6z1/ofl/MQkvMXlPP2+//i8P2p0voAi/LW6/2Bvvd6u/c2nvRjr/bz+v47ofS83PtMpPTZ7f11uPel0PnG4vzq8/2VyflQqvXQ5vxbrPVqsfaDvPcwwE3YAAAEeklEQVR4nO3dW3raMBAFYEsOFgENTjFQm0AwSbr/Ldbu5aEPpR7KkWR952yg/WNJWLdxUTAMwzAMwzAMwzAMwzAMEzZ1v3hMzi+rVfe2vTx/3cQ2/Znyi31QvPfWOuer9rDrV9un2LLfKa15cMSIDNb18XR+j60b83jhb+jIlOtim63wl9O7qnmL2jPBwhEpbtlEbK944Yj0tn2J9SCDCH8im0vWwiHe7uq8hYNRduF/JoMKR2OfuXDoj1WZt3AwutNz3sKhqVZd5sLhMTaZC42x12CDaiTh0FJDvZLHEg6vq4HG1GjCoTO+ZC40JgwxpjAMMarQ2FXuQrFvmQuNGPiEKrLQyD53ofGfuQuNBQ+o8YUi2K4YX2j8MXehcefchWKQU6kUhMbvEhHK7fwH0QInixrh8laqyljr73T6QwpCqV6fb+VSv3b9Z+X8PUiH27rRCKeMB5ttI3d0bfmYjXDMaq9/ju51TsKiWBgtUWDDKUZY1EdlU5U1aiEcJCyKk5LoUS/gMGHR6IhynZ2waLyKaEFTDKCwOKiIfgEBQoWbSjOiopopUli8O4XQuK8IIFZY7DTt1GJ2FbHCi+ZN3GM2FbFC3XjaPp5XwIW1aqyBdESwsPjQNFPIFAot7BRvNh6yT4MWbhQ/GJihBi0srtObqUDWMuDCfvpoKpClYbhQ0RFljxhM4cLL9I5457/wj8CFm7WiIyImUHhhOxmImSLChZrffIdY+sYLPxVCxEtNAOH0nwvIyQy88DT9GVrEUbe0hPN8hopWOtN+eMh+LD0qWuksfw81S4rzfKe5KN68K8TNKM4t/luY//xQ8eI9zzm+Yno403UaRSOd51rbZqlYL7VzXC990WwEe8hWPlaoeoSz3LdQ7a7Nce9ppdohhcwOscKt7miUxdzYBwq3Gh7uaBtOWCoPfqHO7MOEvVUKBXQUGiQsW91xIeAhWoiwbJ36+CVoJAUIn96aSu8zBnb/SXUK+ulG6vr1vVucjuv7jkFb0Jkv3Un29Y2MfwJ7/1F28Sig7r4F6C7CEN+nIYRFKlwFojSEyCvPSQgxS1ApCZGXgpIQgiaG6QjBl53jC8XnfksWXVchutCiyw3FFnrcpbU0hB51UyYVoRzxhc2iCqUNULktptAfQ1T8jCiE1/yILQxVlS6aMFClr2hCvw9WIzpS3cRTuKrCUWpfrvOufTk8QMxFw0SEYo+Bq7QHFro2ZAMNLhS3D1BjL5pQvL0GrpEcUjjwlg2stkd0oXgnn2W07yOgv/4g1smpi/n5B5xw/ISHLA/nSI0TKPzxFZahYbaHpoutG6MRjh/L+WvGj+m4IbZqD9/61faSyieDFML1eXUrXVe+b+ugBeUnRbPLnd7/fkrw9y1ih0IK0w+FFKYfCilMPxRSmH4opDD9UEhh+qGQwvRDIYXph0IK0w+FFKYfCilMPxRSmH4opDD9UEhh+qGQwvRDIYXph0IK0w+FFKYfCilMP+UXOzHezFNY94upOadyG41hGIZhGIZhGIZhGIZhmMflO7iScULsLU8KAAAAAElFTkSuQmCC" type="image/x-icon">
    <link rel="shortcut icon" href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAOEAAADhCAMAAAAJbSJIAAAAclBMVEX///8hlvMAkPLF4Pvr9v6hzfklmfMAj/Itm/Tv+P6z1/ofl/MQkvMXlPP2+//i8P2p0voAi/LW6/2Bvvd6u/c2nvRjr/bz+v47ofS83PtMpPTZ7f11uPel0PnG4vzq8/2VyflQqvXQ5vxbrPVqsfaDvPcwwE3YAAAEeklEQVR4nO3dW3raMBAFYEsOFgENTjFQm0AwSbr/Ldbu5aEPpR7KkWR952yg/WNJWLdxUTAMwzAMwzAMwzAMwzAMEzZ1v3hMzi+rVfe2vTx/3cQ2/Znyi31QvPfWOuer9rDrV9un2LLfKa15cMSIDNb18XR+j60b83jhb+jIlOtim63wl9O7qnmL2jPBwhEpbtlEbK944Yj0tn2J9SCDCH8im0vWwiHe7uq8hYNRduF/JoMKR2OfuXDoj1WZt3AwutNz3sKhqVZd5sLhMTaZC42x12CDaiTh0FJDvZLHEg6vq4HG1GjCoTO+ZC40JgwxpjAMMarQ2FXuQrFvmQuNGPiEKrLQyD53ofGfuQuNBQ+o8YUi2K4YX2j8MXehcefchWKQU6kUhMbvEhHK7fwH0QInixrh8laqyljr73T6QwpCqV6fb+VSv3b9Z+X8PUiH27rRCKeMB5ttI3d0bfmYjXDMaq9/ju51TsKiWBgtUWDDKUZY1EdlU5U1aiEcJCyKk5LoUS/gMGHR6IhynZ2waLyKaEFTDKCwOKiIfgEBQoWbSjOiopopUli8O4XQuK8IIFZY7DTt1GJ2FbHCi+ZN3GM2FbFC3XjaPp5XwIW1aqyBdESwsPjQNFPIFAot7BRvNh6yT4MWbhQ/GJihBi0srtObqUDWMuDCfvpoKpClYbhQ0RFljxhM4cLL9I5457/wj8CFm7WiIyImUHhhOxmImSLChZrffIdY+sYLPxVCxEtNAOH0nwvIyQy88DT9GVrEUbe0hPN8hopWOtN+eMh+LD0qWuksfw81S4rzfKe5KN68K8TNKM4t/luY//xQ8eI9zzm+Yno403UaRSOd51rbZqlYL7VzXC990WwEe8hWPlaoeoSz3LdQ7a7Nce9ppdohhcwOscKt7miUxdzYBwq3Gh7uaBtOWCoPfqHO7MOEvVUKBXQUGiQsW91xIeAhWoiwbJ36+CVoJAUIn96aSu8zBnb/SXUK+ulG6vr1vVucjuv7jkFb0Jkv3Un29Y2MfwJ7/1F28Sig7r4F6C7CEN+nIYRFKlwFojSEyCvPSQgxS1ApCZGXgpIQgiaG6QjBl53jC8XnfksWXVchutCiyw3FFnrcpbU0hB51UyYVoRzxhc2iCqUNULktptAfQ1T8jCiE1/yILQxVlS6aMFClr2hCvw9WIzpS3cRTuKrCUWpfrvOufTk8QMxFw0SEYo+Bq7QHFro2ZAMNLhS3D1BjL5pQvL0GrpEcUjjwlg2stkd0oXgnn2W07yOgv/4g1smpi/n5B5xw/ISHLA/nSI0TKPzxFZahYbaHpoutG6MRjh/L+WvGj+m4IbZqD9/61faSyieDFML1eXUrXVe+b+ugBeUnRbPLnd7/fkrw9y1ih0IK0w+FFKYfCilMPxRSmH4opDD9UEhh+qGQwvRDIYXph0IK0w+FFKYfCilMPxRSmH4opDD9UEhh+qGQwvRDIYXph0IK0w+FFKYfCilMP+UXOzHezFNY94upOadyG41hGIZhGIZhGIZhGIZhmMflO7iScULsLU8KAAAAAElFTkSuQmCC" type="image/x-icon">
    <link rel="stylesheet" href="dark-mode.css">

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const mobileMenuBtn = document.getElementById('mobile-menu-btn');
            const mobileMenu = document.getElementById('mobile-menu');

            // Verificar se ambos os elementos existem antes de manipular o DOM
            if (mobileMenuBtn && mobileMenu) {
                // Toggle menu mobile
                mobileMenuBtn.addEventListener('click', function () {
                    mobileMenu.classList.toggle('hidden');
                    // Alternar ícone e rótulo acessível
                    this.textContent = mobileMenu.classList.contains('hidden') ? '☰' : '✕';
                    this.setAttribute('aria-label', mobileMenu.classList.contains('hidden') ? 'Abrir menu' : 'Fechar menu');
                });

                // Fechar menu ao clicar em um link
                const mobileLinks = mobileMenu.getElementsByTagName('a');
                for (let i = 0; i < mobileLinks.length; i++) {
                    mobileLinks[i].addEventListener('click', function () {
                        mobileMenu.classList.add('hidden');
                        mobileMenuBtn.textContent = '☰';
                        mobileMenuBtn.setAttribute('aria-label', 'Abrir menu');
                    });
                }
            }

            // Controle do header fixo ao rolar a página
            let prevScrollPos = window.scrollY;
            window.onscroll = function () {
                const navbar = document.getElementById("navbar");
                let currentScrollPos = window.scrollY;

                if (navbar) {
                    // Adicionar classe de estilo quando o scroll é maior que 100px
                    if (currentScrollPos > 100) {
                        navbar.classList.add("navbar-scrolled");
                    } else {
                        navbar.classList.remove("navbar-scrolled");
                    }

                    // Ocultar/mostrar barra com base na direção do scroll
                    if (prevScrollPos > currentScrollPos) {
                        navbar.style.top = "0";
                    } else {
                        if (mobileMenu.classList.contains('hidden')) {
                            navbar.style.top = "-80px";
                        }
                    }
                }
                prevScrollPos = currentScrollPos;
            };
        });
    </script>

    <style>
        /* Estilos revisados para o menu */
        #navbar {
            position: fixed;
            width: 100%;
            top: 0;
            left: 0;
            background-color: rgba(255, 255, 255, 0.95);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease-in-out;
            z-index: 1000;
        }

        /* Transição suave ao rolar */
        .navbar-scrolled {
            background-color: black !important;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.15) !important;
        }

        /* Menu mobile */
        #mobile-menu {
            position: fixed;
            top: 70px;
            left: 0;
            width: 100%;
            background-color: white;
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
            z-index: 999;
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
            transition: transform 0.3s ease, opacity 0.3s ease;
        }

        #mobile-menu.hidden {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            transform: translateY(-100%);
        }

        /* Geral */
        body {
            padding-top: 80px;
        }

        @media (max-width: 992px) {
            #navbar .desktop-nav {
                display: none;
            }

            #mobile-menu-btn {
                display: block;
            }
        }
    </style>

    <header id="navbar">
        <div class="container">
            <a href="index.php" class="logo">Projeto de <span>Vida</span></a>
            <nav>
                <ul class="desktop-nav">
                    <li><a href="index.php?#Inicio">Início</a></li>
                    <li><a href="index.php?#Sobre">Sobre</a></li>
                    <li><a href="index.php?#Educacao">Educação</a></li>
                    <li><a href="index.php?#Carreira">Carreira</a></li>
                    <li><a href="index.php?#Contato">Contato</a></li>
                    <li><a href="user.php">Perfil</a></li>
                </ul>
                <button id="mobile-menu-btn" aria-label="Abrir menu">☰</button>
            </nav>
        </div>
        <div id="mobile-menu" class="hidden">
            <ul>
                <li><a href="index.php?#Inicio">Início</a></li>
                <li><a href="index.php?#Sobre">Sobre</a></li>
                <li><a href="index.php?#Educacao">Educação</a></li>
                <li><a href="index.php?#Carreira">Carreira</a></li>
                <li><a href="index.php?#Contato">Contato</a></li>
                <li><a href="user.php">Perfil</a></li>
            </ul>
        </div>
    </header>



    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Funções para as abas
            const tabs = document.querySelectorAll('.perfil-tab');
            const tabContents = document.querySelectorAll('.tab-content');

            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    // Remove a classe active de todas as abas
                    tabs.forEach(t => t.classList.remove('active'));
                    // Adiciona a classe active na aba clicada
                    tab.classList.add('active');

                    // Esconde todos os conteúdos
                    tabContents.forEach(content => content.classList.add('hidden'));
                    // Mostra o conteúdo da aba selecionada
                    const tabId = tab.getAttribute('data-tab');
                    document.getElementById(tabId).classList.remove('hidden');
                });
            });

            // Funções para modais
            const modalTriggers = document.querySelectorAll('[data-modal]');
            const modals = document.querySelectorAll('.modal');
            const closeButtons = document.querySelectorAll('.fechar-modal');

            modalTriggers.forEach(trigger => {
                trigger.addEventListener('click', () => {
                    const modalId = trigger.getAttribute('data-modal');
                    document.getElementById(modalId).classList.add('show');
                });
            });

            closeButtons.forEach(button => {
                button.addEventListener('click', () => {
                    button.closest('.modal').classList.remove('show');
                });
            });

            // Fechar modal ao clicar fora
            window.addEventListener('click', (e) => {
                modals.forEach(modal => {
                    if (e.target === modal) {
                        modal.classList.remove('show');
                    }
                });
            });
        });
    </script>
</head>
<body>
<main class="perfil-container animate-fadeIn">
    <section class="perfil-header" data-background="<?php echo $user['background_image'] ?? 'img/default-banner.jpg'; ?>">
        <div class="perfil-foto">
            <img src="<?php echo $profilePicture; ?>" alt="Foto de perfil">
            <label for="file-upload" class="editar-foto">
                <i class="fas fa-camera"></i>
            </label>
            <form method="POST" enctype="multipart/form-data" style="display: none;">
                <input id="file-upload" type="file" name="profile_picture" onchange="this.form.submit()">
            </form>
        </div>

        <div class="perfil-info">
            <h1 class="perfil-nome"><?php echo $username; ?></h1>
            <p class="perfil-email"><?php echo $user['email']; ?></p>

            <?php if (!empty($user['description'])): ?>
                <div class="perfil-bio">
                    <p><?php echo $user['description']; ?></p>
                </div>
            <?php endif; ?>

            <div class="perfil-acoes">
                <button class="btn btn-primario" data-modal="editar-perfil">
                    <i class="fas fa-user-edit"></i> Editar Perfil
                </button>
                <button class="btn btn-secundario" data-modal="editar-bio">
                    <i class="fas fa-edit"></i> Editar Bio
                </button>
                <button class="btn-terciario" data-modal="mudar-banner">
                    <i class="fas fa-image"></i> Mudar Banner
                </button>
            </div>
        </div>
    </section>


    <script>
        // Aplica a imagem de fundo ao perfil-header
        document.addEventListener('DOMContentLoaded', function() {
            const perfilHeader = document.querySelector('.perfil-header');
            if (perfilHeader) {
                const backgroundImage = perfilHeader.getAttribute('data-background');
                if (backgroundImage) {
                    perfilHeader.style.setProperty('--background-image', `url('${backgroundImage}')`);
                    perfilHeader.style.backgroundImage = `url('${backgroundImage}')`;
                }
            }
        });

    </script>

    <div class="perfil-tabs">
        <button class="perfil-tab active" data-tab="resumo">Resumo</button>
        <button class="perfil-tab" data-tab="quem-sou-eu">Quem Sou Eu</button>
        <button class="perfil-tab" data-tab="personalidade">Personalidade</button>
        <button class="perfil-tab" data-tab="metas">Minhas Metas</button>
        <button class="perfil-tab" data-tab="landing_pages">Landing page</button>
    </div>

    <!-- Aba de Landing Page -->
    <div class="tab-content hidden" id="landing_pages">
        <h2>Meu Portifolio</h2>

        <!-- Sistema de mensagens -->
        <?php if (isset($_SESSION['landing_success'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['landing_success'] ?>
                <?php unset($_SESSION['landing_success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['landing_error'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['landing_error'] ?>
                <?php unset($_SESSION['landing_error']); ?>
            </div>
        <?php endif; ?>

        <!-- Sub-navegação do landing page -->
        <div class="landing-tabs mb-4">
            <button class="landing-tab active" onclick="trocarAbaLanding('landing_preview')">Visualizar</button>
            <button class="landing-tab" onclick="trocarAbaLanding('landing_edit')">Editar</button>
            <?php if ($is_admin): ?>
                <button class="landing-tab" onclick="trocarAbaLanding('landing_admin')">Admin</button>
            <?php endif; ?>
        </div>

        <div class="landing-panes">
            <!-- Visualização do landing page -->
            <div id="landing_preview" class="landing-pane active">
                <?php if ($viewing_landing): ?>
                    <!-- Visualizando landing page de outro usuário -->
                    <div class="alert alert-info">
                        <p>Você está visualizando o landing page de: <strong><?= htmlspecialchars($viewing_user['username']) ?></strong></p>
                        <a href="user.php#landing_pages" class="btn btn-sm btn-outline-primary mt-2">Voltar ao meu landing page</a>
                    </div>

                    <div class="landing-preview">
                        <div class="topo">
                            <h1><?= htmlspecialchars($viewing_landing['titulo_principal']) ?></h1>
                            <p class="subtitulo"><?= htmlspecialchars($viewing_landing['subtitulo_principal'] ?? '') ?></p>
                        </div>

                        <div class="section">
                            <h3>Sobre Mim</h3>
                            <p><?= nl2br(htmlspecialchars($viewing_landing['sobre'] ?? '')) ?></p>
                        </div>

                        <div class="section">
                            <h3>Educação</h3>
                            <p><?= nl2br(htmlspecialchars($viewing_landing['educacao'] ?? '')) ?></p>
                        </div>

                        <div class="section">
                            <h3>Carreira</h3>
                            <p><?= nl2br(htmlspecialchars($viewing_landing['carreira'] ?? '')) ?></p>
                        </div>

                        <div class="section">
                            <h3>Contato</h3>
                            <div class="contato"><?= nl2br(htmlspecialchars($viewing_landing['contato'] ?? '')) ?></div>
                        </div>
                    </div>
                <?php elseif ($landing): ?>
                    <!-- Visualizando seu próprio landing page -->
                    <div class="landing-preview">
                        <div class="topo">
                            <h1><?= htmlspecialchars($landing['titulo_principal'] ?? 'Meu Currículo') ?></h1>
                            <p class="subtitulo"><?= htmlspecialchars($landing['subtitulo_principal'] ?? '') ?></p>
                        </div>

                        <div class="section">
                            <h3>Sobre Mim</h3>
                            <p><?= nl2br(htmlspecialchars($landing['sobre'] ?? '')) ?></p>
                        </div>

                        <div class="section">
                            <h3>Educação</h3>
                            <p><?= nl2br(htmlspecialchars($landing['educacao'] ?? '')) ?></p>
                        </div>

                        <div class="section">
                            <h3>Carreira</h3>
                            <p><?= nl2br(htmlspecialchars($landing['carreira'] ?? '')) ?></p>
                        </div>

                        <div class="section">
                            <h3>Contato</h3>
                            <div class="contato"><?= nl2br(htmlspecialchars($landing['contato'] ?? '')) ?></div>
                        </div>

                        <div class="mt-4">
                            <a href="landing.php?user_id=<?= $user_id ?>" target="_blank" class="btn btn-primary">Ver Página Completa</a>
                            <?php if (isset($landing['publico']) && $landing['publico']): ?>
                                <span class="badge badge-success ml-2">Público</span>
                            <?php else: ?>
                                <span class="badge badge-secondary ml-2">Privado</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <p>Você ainda não criou seu landing page. <a href="#" onclick="trocarAbaLanding('landing_edit'); return false;">Clique aqui para criar</a>.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Formulário de edição do landing page -->
            <div id="landing_edit" class="landing-pane">
                <form method="POST" action="" class="form-landing">
                    <div class="form-group">
                        <label for="titulo_principal">Título Principal</label>
                        <input type="text" id="titulo_principal" name="titulo_principal" class="form-control"
                               value="<?= htmlspecialchars($landing['titulo_principal'] ?? '') ?>"
                               placeholder="Ex: João Silva - Desenvolvedor Web">
                    </div>

                    <div class="form-group">
                        <label for="subtitulo_principal">Subtítulo</label>
                        <textarea id="subtitulo_principal" name="subtitulo_principal" class="form-control" rows="2"
                                  placeholder="Ex: Desenvolvedor Full Stack com 5 anos de experiência"><?= htmlspecialchars($landing['subtitulo_principal'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="sobre">Sobre Mim</label>
                        <textarea id="sobre" name="sobre" class="form-control" rows="4"
                                  placeholder="Descreva brevemente quem você é, suas habilidades e objetivos"><?= htmlspecialchars($landing['sobre'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="educacao">Educação</label>
                        <textarea id="educacao" name="educacao" class="form-control" rows="4"
                                  placeholder="Liste sua formação acadêmica, cursos e certificações"><?= htmlspecialchars($landing['educacao'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="carreira">Carreira</label>
                        <textarea id="carreira" name="carreira" class="form-control" rows="4"
                                  placeholder="Descreva sua experiência profissional"><?= htmlspecialchars($landing['carreira'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="contato">Contato</label>
                        <textarea id="contato" name="contato" class="form-control" rows="4"
                                  placeholder="Adicione suas informações de contato como email, telefone, LinkedIn, etc."><?= htmlspecialchars($landing['contato'] ?? '') ?></textarea>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" id="publico" name="publico" class="form-check-input"
                            <?= isset($landing['publico']) && $landing['publico'] ? 'checked' : '' ?>>
                        <label for="publico" class="form-check-label">Tornar meu Landing Page público</label>
                        <small class="form-text text-muted">Se marcado, qualquer pessoa poderá acessar seu landing page através do link.</small>
                    </div>

                    <div class="form-buttons">
                        <button type="submit" name="editar_landing" class="btn btn-primary">Salvar Landing Page</button>
                    </div>
                </form>
            </div>

            <!-- Seção de Admin (apenas para o Eric) -->
            <?php if ($is_admin): ?>
                <div id="landing_admin" class="landing-pane">
                    <h3 class="mb-4">Administração de Landing Pages</h3>

                    <?php if (empty($all_landings)): ?>
                        <div class="alert alert-info">
                            <p>Ainda não existem landing pages cadastrados no sistema.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="thead-dark">
                                <tr>
                                    <th>Usuário</th>
                                    <th>Título</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($all_landings as $l): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($l['username']) ?></td>
                                        <td><?= htmlspecialchars($l['titulo_principal']) ?></td>
                                        <td>
                                            <?php if ($l['publico']): ?>
                                                <span class="badge badge-success">Público</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Privado</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="user.php?view_landing=<?= $l['user_id'] ?>#landing_pages" class="btn btn-sm btn-info">Visualizar</a>
                                            <a href="landing.php?user_id=<?= $l['user_id'] ?>" target="_blank" class="btn btn-sm btn-primary">Ver Completo</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <style>
        /* Estilos para o sistema de abas do landing page */
        .landing-tabs {
            display: flex;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
        }

        .landing-tab {
            background: none;
            border: none;
            padding: 10px 15px;
            font-size: 16px;
            cursor: pointer;
            position: relative;
            color: #555;
            transition: color 0.3s;
        }

        .landing-tab:hover {
            color: var(--primary);
        }



        .landing-tab.active:after {
            content: '';
            position: absolute;
            height: 3px;
            background-color: var(--primary);
            width: 100%;
            bottom: -1px;
            left: 0;
        }

        /* Conteúdo das abas */
        .landing-pane {
            display: none;
        }

        .landing-pane.active {
            display: block;
        }

        /* Landing Preview */
        .landing-preview {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 25px;
            margin-bottom: 30px;
        }

        .landing-preview .topo {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .landing-preview h1 {
            font-size: 28px;
            color: #333;
            margin-bottom: 10px;
        }

        .landing-preview .subtitulo {
            font-size: 18px;
            color: #666;
            font-style: italic;
        }

        .landing-preview .section {
            margin-bottom: 25px;
        }

        .landing-preview .section h3 {
            font-size: 20px;
            color: #007bff;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 1px solid #eaeaea;
        }

        .landing-preview .contato {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #007bff;
        }

        /* Formulário */
        .form-landing {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 80px;
        }

        /* Estilos adicionais para a área admin */
        .table-responsive {
            display: block;
            width: 100%;
            overflow-x: auto;
        }

        .table {
            width: 100%;
            margin-bottom: 1rem;
            color: #212529;
            border-collapse: collapse;
        }

        .table-bordered {
            border: 1px solid #dee2e6;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.05);
        }

        .table th,
        .table td {
            padding: 0.75rem;
            vertical-align: middle;
            border-top: 1px solid #dee2e6;
        }

        .table-bordered th,
        .table-bordered td {
            border: 1px solid #dee2e6;
        }

        .thead-dark th {
            color: #fff;
            background-color: #343a40;
            border-color: #454d55;
        }

        .badge {
            display: inline-block;
            padding: 0.25em 0.5em;
            font-size: 75%;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.25rem;
            margin-left: 10px;
        }

        .badge-success {
            color: #fff;
            background-color: #28a745;
        }

        .badge-secondary {
            color: #fff;
            background-color: #6c757d;
        }

        .btn {
            display: inline-block;
            font-weight: 400;
            text-align: center;
            vertical-align: middle;
            cursor: pointer;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: 0.25rem;
            text-decoration: none;
        }

        .btn-primary {
            color: #fff;
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0069d9;
            border-color: #0062cc;
        }

        .btn-info {
            color: #fff;
            background-color: #17a2b8;
            border-color: #17a2b8;
        }

        .btn-info:hover {
            color: #fff;
            background-color: #138496;
            border-color: #117a8b;
        }

        .btn-outline-primary {
            color: #007bff;
            border-color: #007bff;
            background-color: transparent;
        }

        .btn-outline-primary:hover {
            color: #fff;
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            line-height: 1.5;
            border-radius: 0.2rem;
        }

        .alert {
            position: relative;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            border: 1px solid transparent;
            border-radius: 0.25rem;
        }

        .alert-info {
            color: #0c5460;
            background-color: #d1ecf1;
            border-color: #bee5eb;
        }

        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }

        .mt-2 {
            margin-top: 0.5rem !important;
        }

        .mb-4 {
            margin-bottom: 1.5rem !important;
        }

        .mt-4 {
            margin-top: 1.5rem !important;
        }

        .ml-2 {
            margin-left: 0.5rem !important;
        }
    </style>

    <script>
        // Função para alternar entre as abas do landing page
        function trocarAbaLanding(abaId) {
            // Esconde todas as abas
            document.querySelectorAll('.landing-pane').forEach(function(pane) {
                pane.classList.remove('active');
            });

            // Desativa todos os botões
            document.querySelectorAll('.landing-tab').forEach(function(tab) {
                tab.classList.remove('active');
            });

            // Mostra a aba selecionada
            document.getElementById(abaId).classList.add('active');

            // Ativa o botão correspondente
            document.querySelectorAll('.landing-tab').forEach(function(tab) {
                if (tab.getAttribute('onclick').includes(abaId)) {
                    tab.classList.add('active');
                }
            });
        }

        // Inicialização
        document.addEventListener('DOMContentLoaded', function() {
            // Verificar se há uma landing page específica sendo visualizada
            const viewingLanding = <?= isset($viewing_landing) ? 'true' : 'false' ?>;

            // Verificar se há hash na URL
            const hash = window.location.hash.substring(1);

            // Se há landing page para visualizar, mostrar a aba de preview
            if (viewingLanding) {
                trocarAbaLanding('landing_preview');
            }
            // Se for admin e estiver na seção admin, mostrar a aba admin
            else if (hash === 'landing_admin' && <?= $is_admin ? 'true' : 'false' ?>) {
                trocarAbaLanding('landing_admin');
            }
            // Caso contrário, decidir com base no landing do usuário
            else {
                <?php if (isset($landing) && $landing): ?>
                trocarAbaLanding('landing_preview');
                <?php else: ?>
                trocarAbaLanding('landing_edit');
                <?php endif; ?>
            }
        });
    </script>


    <div class="tab-content" id="resumo">
        <section class="perfil-conteudo">
            <h2 class="secao-titulo">Visão Geral</h2>

            <div class="card-grid">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-titulo">Progresso</h3>
                        <p class="card-subtitulo">Completou <?php echo $metasFeitas; ?> de <?php echo $totalMetas; ?> metas</p>
                    </div>
                    <div class="card-body">
                        <div class="progresso-container">
                            <div class="progresso-barra" style="width: <?php echo $porcentagemConcluida; ?>%"></div>
                        </div>
                        <p class="text-center"><?php echo $porcentagemConcluida; ?>% completo</p>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-titulo">Próximas Metas</h3>
                    </div>
                    <div class="card-body">
                        <?php if (count($proximasMetas) > 0): ?>
                            <div class="lista-metas">
                                <?php foreach(array_slice($proximasMetas, 0, 3) as $meta): ?>
                                    <div class="meta-item">
                                        <form method="POST">
                                            <input type="hidden" name="concluir_meta" value="<?php echo $meta['id']; ?>">
                                            <input type="checkbox" class="meta-checkbox" onchange="this.form.submit()">
                                        </form>
                                        <div class="meta-conteudo">
                                            <h4 class="meta-titulo"><?php echo $meta['titulo']; ?></h4>
                                            <p class="meta-descricao"><?php echo $meta['descricao']; ?></p>
                                            <p class="meta-prazo">
                                                <i class="fas fa-calendar-alt"></i>
                                                Prazo: <?php echo date('d/m/Y', strtotime($meta['prazo'])); ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-center">Você não tem metas pendentes.</p>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primario btn-pequeno" data-modal="adicionar-meta">
                            <i class="fas fa-plus"></i> Nova Meta
                        </button>
                    </div>
                </div>

                <?php if ($personalidade): ?>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-titulo">Personalidade</h3>
                            <p class="card-subtitulo">Meu traço dominante</p>
                        </div>
                        <div class="card-body">
                            <div class="traco-dominante text-center">
                                <?php echo $traço_dominante; ?>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="#personalidade" class="btn btn-secundario btn-pequeno" onclick="event.preventDefault(); document.querySelector('.perfil-tab[data-tab=\'personalidade\']').click();">
                                <i class="fas fa-chart-bar"></i> Ver Detalhes
                            </a>
                        </div>

                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <div class="tab-content hidden" id="quem-sou-eu">
        <h2 class="section-title">Quem Sou Eu</h2>

        <?php if (!$perfil): ?>
            <!-- Mensagem caso não existam dados -->
            <div class="missing-data">
                <p>Você ainda não preencheu seu perfil completo. Preencha o formulário abaixo para começar.</p>
            </div>
        <?php endif; ?>

        <form method="POST" action="user.php" class="form-quem-sou-eu">
            <!-- Seção 1: Autoconhecimento -->
            <div class="section">
                <h3>Autoconhecimento</h3>

                <div class="form-group">
                    <label for="sobre_voce">Fale sobre você</label>
                    <textarea id="sobre_voce" name="sobre_voce" class="input-field" rows="4"><?= htmlspecialchars($perfil['fale_sobre_voce'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="lembrancas">Minhas lembranças</label>
                    <textarea id="lembrancas" name="lembrancas" class="input-field" rows="4"><?= htmlspecialchars($perfil['minhas_lembrancas'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="pontos_fortes">Meus pontos fortes</label>
                    <textarea id="pontos_fortes" name="pontos_fortes" class="input-field" rows="4"><?= htmlspecialchars($perfil['pontos_fortes'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="pontos_fracos">Meus pontos fracos</label>
                    <textarea id="pontos_fracos" name="pontos_fracos" class="input-field" rows="4"><?= htmlspecialchars($perfil['pontos_fracos'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="valores">Meus valores</label>
                    <textarea id="valores" name="valores" class="input-field" rows="4"><?= htmlspecialchars($perfil['meus_valores'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label>Minhas principais aptidões</label>
                    <?php
                    // Transformar string em array
                    $aptidoesArray = explode(', ', $perfil['principais_aptidoes'] ?? '');
                    ?>
                    <div class="checkbox-grid">
                        <div class="checkbox-item">
                            <input type="checkbox" id="apt_comunicacao" name="aptidoes[]" value="Comunicação" <?= in_array('Comunicação', $aptidoesArray) ? 'checked' : '' ?>>
                            <label for="apt_comunicacao">Comunicação</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="apt_criatividade" name="aptidoes[]" value="Criatividade" <?= in_array('Criatividade', $aptidoesArray) ? 'checked' : '' ?>>
                            <label for="apt_criatividade">Criatividade</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="apt_lideranca" name="aptidoes[]" value="Liderança" <?= in_array('Liderança', $aptidoesArray) ? 'checked' : '' ?>>
                            <label for="apt_lideranca">Liderança</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="apt_organizacao" name="aptidoes[]" value="Organização" <?= in_array('Organização', $aptidoesArray) ? 'checked' : '' ?>>
                            <label for="apt_organizacao">Organização</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="apt_empatia" name="aptidoes[]" value="Empatia" <?= in_array('Empatia', $aptidoesArray) ? 'checked' : '' ?>>
                            <label for="apt_empatia">Empatia</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="apt_analise" name="aptidoes[]" value="Análise" <?= in_array('Análise', $aptidoesArray) ? 'checked' : '' ?>>
                            <label for="apt_analise">Análise</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="apt_resiliencia" name="aptidoes[]" value="Resiliência" <?= in_array('Resiliência', $aptidoesArray) ? 'checked' : '' ?>>
                            <label for="apt_resiliencia">Resiliência</label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="apt_adaptacao" name="aptidoes[]" value="Adaptação" <?= in_array('Adaptação', $aptidoesArray) ? 'checked' : '' ?>>
                            <label for="apt_adaptacao">Adaptação</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seção 2: Relações -->
            <div class="section">
                <h3>Minhas Relações</h3>

                <div class="form-group">
                    <label for="familia">Com a família</label>
                    <textarea id="familia" name="familia" class="input-field" rows="4"><?= htmlspecialchars($perfil['relacoes_familia'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="amigos">Com os amigos</label>
                    <textarea id="amigos" name="amigos" class="input-field" rows="4"><?= htmlspecialchars($perfil['relacoes_amigos'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="escola">Com a escola</label>
                    <textarea id="escola" name="escola" class="input-field" rows="4"><?= htmlspecialchars($perfil['relacoes_escola'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="sociedade">Com a sociedade</label>
                    <textarea id="sociedade" name="sociedade" class="input-field" rows="4"><?= htmlspecialchars($perfil['relacoes_sociedade'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Seção 3: Cotidiano -->
            <div class="section">
                <h3>Meu Cotidiano</h3>

                <div class="form-group">
                    <label for="gosto_fazer">O que gosto de fazer</label>
                    <textarea id="gosto_fazer" name="gosto_fazer" class="input-field" rows="4"><?= htmlspecialchars($perfil['gosto_fazer'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="nao_gosto">O que não gosto de fazer</label>
                    <textarea id="nao_gosto" name="nao_gosto" class="input-field" rows="4"><?= htmlspecialchars($perfil['nao_gosto_fazer'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="rotina">Minha rotina</label>
                    <textarea id="rotina" name="rotina" class="input-field" rows="4"><?= htmlspecialchars($perfil['rotina'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="lazer">Meu lazer</label>
                    <textarea id="lazer" name="lazer" class="input-field" rows="4"><?= htmlspecialchars($perfil['lazer'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Seção 4: Vida Escolar -->
            <div class="section">
                <h3>Vida Escolar</h3>

                <div class="form-group">
                    <label for="estudos">Meus estudos</label>
                    <textarea id="estudos" name="estudos" class="input-field" rows="4"><?= htmlspecialchars($perfil['estudos'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="vida_escolar">Minha vida escolar</label>
                    <textarea id="vida_escolar" name="vida_escolar" class="input-field" rows="4"><?= htmlspecialchars($perfil['vida_escolar'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Seção 5: Como Me Vejo -->
            <div class="section">
                <h3>Como Me Vejo</h3>

                <div class="form-group">
                    <label for="visao_fisica">Fisicamente</label>
                    <textarea id="visao_fisica" name="visao_fisica" class="input-field" rows="4"><?= htmlspecialchars($perfil['visao_fisica'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="visao_intelectual">Intelectualmente</label>
                    <textarea id="visao_intelectual" name="visao_intelectual" class="input-field" rows="4"><?= htmlspecialchars($perfil['visao_intelectual'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="visao_emocional">Emocionalmente</label>
                    <textarea id="visao_emocional" name="visao_emocional" class="input-field" rows="4"><?= htmlspecialchars($perfil['visao_emocional'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Seção 6: Como Os Outros Me Veem -->
            <div class="section">
                <h3>Como Os Outros Me Veem</h3>

                <div class="form-group">
                    <label for="visao_amigos">Na visão dos amigos</label>
                    <textarea id="visao_amigos" name="visao_amigos" class="input-field" rows="4"><?= htmlspecialchars($perfil['visao_dos_amigos'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="visao_familiares">Na visão dos familiares</label>
                    <textarea id="visao_familiares" name="visao_familiares" class="input-field" rows="4"><?= htmlspecialchars($perfil['visao_dos_familiares'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="visao_professores">Na visão dos professores</label>
                    <textarea id="visao_professores" name="visao_professores" class="input-field" rows="4"><?= htmlspecialchars($perfil['visao_dos_professores'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Seção 7: Autovalorização -->
            <div class="section">
                <h3>Autovalorização</h3>

                <div class="form-group">
                    <label for="autovalorizacao">Em uma escala de 1 a 10, quanto você se valoriza?</label>
                    <input type="range" id="autovalorizacao" name="autovalorizacao" min="0" max="10" class="range-slider" value="<?= (int)($perfil['autovalorizacao_total'] ?? 5) ?>">
                    <div class="range-value" id="autovalorizacao-value"><?= (int)($perfil['autovalorizacao_total'] ?? 5) ?></div>
                </div>
            </div>

            <div class="form-buttons">
                <button type="submit" name="salvar_perfil_completo" class="btn btn-primary">Salvar Perfil</button>
            </div>
        </form>
    </div>

    <style>
        /* Estilo geral da seção "Quem Sou Eu" */
        #quem-sou-eu {
            padding: 20px;
        }

        #quem-sou-eu .section {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 25px;
        }

        #quem-sou-eu .section h3 {
            color: #007bff;
            font-size: 20px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        /* Estilo para o formulário */
        .form-quem-sou-eu .form-group {
            margin-bottom: 20px;
        }

        .form-quem-sou-eu label {
            display: block;
            font-weight: 500;
            margin-bottom: 8px;
            color: #333;
        }

        .form-quem-sou-eu .input-field {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .form-quem-sou-eu .input-field:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
        }

        /* Estilo para o grid de checkboxes */
        .checkbox-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }

        .checkbox-item input[type="checkbox"] {
            margin-right: 10px;
            width: 18px;
            height: 18px;
        }

        /* Estilo para o slider */
        .range-slider {
            width: 100%;
            margin-top: 10px;
        }

        .range-value {
            text-align: center;
            font-weight: bold;
            margin-top: 10px;
            font-size: 24px;
            color: #007bff;
        }

        /* Mensagem de dados faltantes */
        .missing-data {
            background-color: #f8f9fa;
            border-left: 4px solid #17a2b8;
            padding: 15px;
            color: #555;
            margin-bottom: 20px;
            border-radius: 4px;
        }

        /* Botões do formulário */
        .form-buttons {
            margin-top: 30px;
            text-align: center;
        }
    </style>

    <script>
        // Script para mostrar valor do slider
        document.addEventListener('DOMContentLoaded', function() {
            const slider = document.getElementById('autovalorizacao');
            const output = document.getElementById('autovalorizacao-value');

            if (slider && output) {
                // Atualizar o valor ao carregar a página
                output.textContent = slider.value;

                // Atualizar o valor quando o slider é movido
                slider.oninput = function() {
                    output.textContent = this.value;
                };
            }
        });
    </script>


    <div class="tab-content hidden" id="personalidade">
        <h2 class="section-title">Minha Personalidade</h2>

        <div class="personality-sections">
            <!-- Seção do gráfico de pizza -->
            <div class="personality-section">
                <h3>Distribuição de Traços</h3>

                <div class="chart-container">
                    <canvas id="personalityChart"></canvas>

                    <?php if ($personalidade): ?>
                        <div class="dominant-trait">
                            <span>Traço dominante:</span>
                            <strong><?= htmlspecialchars($traço_dominante) ?></strong>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Seção do formulário de avaliação -->
            <div class="personality-section">
                <h3>Teste de Personalidade</h3>
                <p class="section-description">Avalie cada dimensão da sua personalidade numa escala de 0 a 10:</p>

                <form method="POST" action="user.php" class="personality-form">
                    <div class="slider-group">
                        <div class="slider-labels">
                            <span>Introvertido</span>
                            <span>Extrovertido</span>
                        </div>
                        <div class="slider-container">
                            <input type="range" id="extrovertido" name="extrovertido" min="0" max="10" value="<?= (int) ($personalidade['extrovertido'] ?? 5) ?>">
                            <div class="slider-value" id="extrovertido-value"><?= (int) ($personalidade['extrovertido'] ?? 5) ?></div>
                        </div>
                    </div>

                    <div class="slider-group">
                        <div class="slider-labels">
                            <span>Sensorial</span>
                            <span>Intuitivo</span>
                        </div>
                        <div class="slider-container">
                            <input type="range" id="intuitivo" name="intuitivo" min="0" max="10" value="<?= (int) ($personalidade['intuitivo'] ?? 5) ?>">
                            <div class="slider-value" id="intuitivo-value"><?= (int) ($personalidade['intuitivo'] ?? 5) ?></div>
                        </div>
                    </div>

                    <div class="slider-group">
                        <div class="slider-labels">
                            <span>Emocional</span>
                            <span>Racional</span>
                        </div>
                        <div class="slider-container">
                            <input type="range" id="racional" name="racional" min="0" max="10" value="<?= (int) ($personalidade['racional'] ?? 5) ?>">
                            <div class="slider-value" id="racional-value"><?= (int) ($personalidade['racional'] ?? 5) ?></div>
                        </div>
                    </div>

                    <div class="slider-group">
                        <div class="slider-labels">
                            <span>Perceptivo</span>
                            <span>Julgador</span>
                        </div>
                        <div class="slider-container">
                            <input type="range" id="julgador" name="julgador" min="0" max="10" value="<?= (int) ($personalidade['julgador'] ?? 5) ?>">
                            <div class="slider-value" id="julgador-value"><?= (int) ($personalidade['julgador'] ?? 5) ?></div>
                        </div>
                    </div>

                    <div class="form-buttons">
                        <button type="submit" name="teste_personalidade" class="btn btn-primary">Salvar Resultados</button>
                    </div>
                </form>
            </div>

            <!-- Seção de interpretação dos resultados -->
            <div class="personality-section results-section">
                <h3>Interpretação dos Resultados</h3>

                <?php if ($personalidade): ?>
                    <div class="personality-traits">
                        <div class="trait">
                            <h4>Introversão vs. Extroversão <span>(<?= (int) ($personalidade['extrovertido']) ?>/10)</span></h4>
                            <div class="trait-bar">
                                <div class="trait-fill" style="width: <?= (int) ($personalidade['extrovertido'] * 10) ?>%"></div>
                            </div>
                            <p class="trait-description">
                                <?php if ($personalidade['extrovertido'] > 5): ?>
                                    Você tende a ser mais <strong>extrovertido</strong>, preferindo interação social e ambientes estimulantes.
                                <?php else: ?>
                                    Você tende a ser mais <strong>introvertido</strong>, preferindo reflexão e ambientes mais calmos.
                                <?php endif; ?>
                            </p>
                        </div>

                        <div class="trait">
                            <h4>Sensorial vs. Intuitivo <span>(<?= (int) ($personalidade['intuitivo']) ?>/10)</span></h4>
                            <div class="trait-bar">
                                <div class="trait-fill" style="width: <?= (int) ($personalidade['intuitivo'] * 10) ?>%"></div>
                            </div>
                            <p class="trait-description">
                                <?php if ($personalidade['intuitivo'] > 5): ?>
                                    Você tende a ser mais <strong>intuitivo</strong>, focando em possibilidades futuras e significados abstratos.
                                <?php else: ?>
                                    Você tende a ser mais <strong>sensorial</strong>, focando em experiências concretas e detalhes práticos.
                                <?php endif; ?>
                            </p>
                        </div>

                        <div class="trait">
                            <h4>Emocional vs. Racional <span>(<?= (int) ($personalidade['racional']) ?>/10)</span></h4>
                            <div class="trait-bar">
                                <div class="trait-fill" style="width: <?= (int) ($personalidade['racional'] * 10) ?>%"></div>
                            </div>
                            <p class="trait-description">
                                <?php if ($personalidade['racional'] > 5): ?>
                                    Você tende a ser mais <strong>racional</strong>, priorizando a lógica e objetividade nas decisões.
                                <?php else: ?>
                                    Você tende a ser mais <strong>emocional</strong>, priorizando valores pessoais e harmonia nas decisões.
                                <?php endif; ?>
                            </p>
                        </div>

                        <div class="trait">
                            <h4>Perceptivo vs. Julgador <span>(<?= (int) ($personalidade['julgador']) ?>/10)</span></h4>
                            <div class="trait-bar">
                                <div class="trait-fill" style="width: <?= (int) ($personalidade['julgador'] * 10) ?>%"></div>
                            </div>
                            <p class="trait-description">
                                <?php if ($personalidade['julgador'] > 5): ?>
                                    Você tende a ser mais <strong>julgador</strong>, preferindo estrutura, planejamento e decisões definidas.
                                <?php else: ?>
                                    Você tende a ser mais <strong>perceptivo</strong>, preferindo flexibilidade, adaptabilidade e manter opções abertas.
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="no-results">
                        <p>Você ainda não realizou o teste de personalidade. Complete o formulário ao lado para ver seus resultados.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <style>
        /* Estilos para a seção de personalidade */
        #personalidade {
            padding: 20px;
        }

        .personality-sections {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        @media (max-width: 992px) {
            .personality-sections {
                grid-template-columns: 1fr;
            }
        }

        .personality-section {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 25px;
        }

        .results-section {
            grid-column: span 2;
        }

        @media (max-width: 992px) {
            .results-section {
                grid-column: span 1;
            }
        }

        .personality-section h3 {
            color: #007bff;
            font-size: 20px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .section-description {
            color: #666;
            margin-bottom: 20px;
        }

        /* Estilos para o gráfico */
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
        }

        .dominant-trait {
            text-align: center;
            margin-top: 20px;
            font-size: 18px;
        }

        .dominant-trait strong {
            color: #007bff;
        }

        /* Estilos para o formulário */
        .slider-group {
            margin-bottom: 25px;
        }

        .slider-labels {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }

        .slider-container {
            position: relative;
        }

        .slider-container input[type="range"] {
            width: 100%;
            height: 8px;
            appearance: none;
            background: #e9ecef;
            border-radius: 5px;
            outline: none;
        }

        .slider-container input[type="range"]::-webkit-slider-thumb {
            appearance: none;
            width: 20px;
            height: 20px;
            background: var(--primary);
            border-radius: 50%;
            cursor: pointer;
        }

        .slider-value {
            position: absolute;
            top: -30px;
            left: 50%;
            transform: translateX(-50%);
            background: #007bff;
            color: white;
            padding: 2px 10px;
            border-radius: 4px;
            font-weight: bold;
        }

        /* Estilos para a interpretação dos resultados */
        .personality-traits {
            display: grid;
            gap: 25px;
        }

        .trait h4 {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            color: #333;
        }

        .trait-bar {
            height: 10px;
            background-color: #e9ecef;
            border-radius: 5px;
            overflow: hidden;
            margin-bottom: 10px;
        }

        .trait-fill {
            height: 100%;
            background-color: #007bff;
            border-radius: 5px;
            transition: width 0.5s ease;
        }

        .trait-description {
            color: #555;
            font-size: 15px;
        }

        .no-results {
            background-color: #f8f9fa;
            border-left: 4px solid #17a2b8;
            padding: 15px;
            color: #555;
            border-radius: 4px;
        }

        /* Botões do formulário */
        .form-buttons {
            margin-top: 30px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: background-color 0.3s;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background-color: #0069d9;
        }
    </style>

    <!-- Incluir Chart.js antes do script -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Configuração dos sliders
            const sliders = ['extrovertido', 'intuitivo', 'racional', 'julgador'];

            sliders.forEach(slider => {
                const input = document.getElementById(slider);
                const output = document.getElementById(`${slider}-value`);

                if (input && output) {
                    // Atualizar o valor ao carregar a página
                    output.textContent = input.value;
                    updateSliderPosition(input, output);

                    // Atualizar o valor quando o slider é movido
                    input.oninput = function() {
                        output.textContent = this.value;
                        updateSliderPosition(this, output);
                    };
                }
            });

            // Função para atualizar a posição do indicador de valor
            function updateSliderPosition(slider, output) {
                const percent = (slider.value - slider.min) / (slider.max - slider.min);
                const position = percent * (slider.offsetWidth - 20) + 10; // Ajustar para o tamanho do thumb
                output.style.left = `${position}px`;
            }

            // Criar o gráfico de pizza se existir dados
            <?php if ($personalidade): ?>
            const ctx = document.getElementById('personalityChart').getContext('2d');

            // Dados dos traços de personalidade
            const data = {
                labels: ['Extrovertido', 'Intuitivo', 'Racional', 'Julgador'],
                datasets: [{
                    data: [
                        <?= (int) ($personalidade['extrovertido'] ?? 0) ?>,
                        <?= (int) ($personalidade['intuitivo'] ?? 0) ?>,
                        <?= (int) ($personalidade['racional'] ?? 0) ?>,
                        <?= (int) ($personalidade['julgador'] ?? 0) ?>
                    ],
                    backgroundColor: [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                        '#4BC0C0'
                    ],
                    borderWidth: 1
                }]
            };

            // Opções do gráfico
            const options = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                size: 14
                            },
                            padding: 20
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                return `${label}: ${value}/10`;
                            }
                        }
                    }
                }
            };

            // Criar gráfico
            new Chart(ctx, {
                type: 'pie',
                data: data,
                options: options
            });
            <?php endif; ?>
        });
    </script>


    <div class="tab-content hidden" id="metas">
        <section class="perfil-conteudo">
            <h2 class="secao-titulo">
                Minhas Metas
                <button class="btn btn-primario btn-pequeno" data-modal="adicionar-meta">
                    <i class="fas fa-plus"></i> Nova Meta
                </button>
            </h2>

            <div class="progresso-container">
                <div class="progresso-barra" style="width: <?php echo $porcentagemConcluida; ?>%"></div>
            </div>
            <p class="text-center mb-4"><?php echo $porcentagemConcluida; ?>% completo (<?php echo $metasFeitas; ?> de <?php echo $totalMetas; ?>)</p>

            <?php if (count($proximasMetas) > 0): ?>
                <div class="lista-metas">
                    <?php foreach($proximasMetas as $meta): ?>
                        <div class="meta-item">
                            <form method="POST">
                                <input type="hidden" name="concluir_meta" value="<?php echo $meta['id']; ?>">
                                <input type="checkbox" class="meta-checkbox" onchange="this.form.submit()">
                            </form>
                            <div class="meta-conteudo">
                                <h4 class="meta-titulo"><?php echo $meta['titulo']; ?></h4>
                                <p class="meta-descricao"><?php echo $meta['descricao']; ?></p>
                                <p class="meta-prazo">
                                    <i class="fas fa-calendar-alt"></i>
                                    Prazo: <?php echo date('d/m/Y', strtotime($meta['prazo'])); ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center mt-4">
                    <p>Você não tem metas pendentes</p>
                    <button class="btn btn-primario mt-3" data-modal="adicionar-meta">
                        Adicionar Meta
                    </button>
                </div>
            <?php endif; ?>
        </section>
    </div>


    <!-- Metas Vencidas -->
    <?php if (!empty($metasVencidas)): ?>
        <div class="card mb-4">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0">Metas Vencidas</h5>
            </div>
            <div class="card-body">
                <ul class="list-group lista-metas">
                    <?php foreach ($metasVencidas as $meta): ?>
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-1"><?= htmlspecialchars($meta['titulo']) ?></h5>
                                    <?php if (!empty($meta['descricao'])): ?>
                                        <p class="mb-1 text-muted"><?= htmlspecialchars($meta['descricao']) ?></p>
                                    <?php endif; ?>
                                    <small class="text-danger">
                                        Prazo vencido: <?= date('d/m/Y', strtotime($meta['prazo'])) ?>
                                    </small>
                                </div>
                                <div class="d-flex">
                                    <form method="post" action="" class="me-2">
                                        <input type="hidden" name="concluir_meta" value="<?= $meta['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-success">Concluir</button>
                                    </form>
                                    <form method="post" action="">
                                        <input type="hidden" name="excluir_meta" value="<?= $meta['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                                    </form>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

</main>

<!-- Modais -->
<div class="modal" id="editar-perfil">
    <div class="modal-conteudo">
        <div class="modal-cabecalho">
            <h3>Editar Perfil</h3>
            <button class="fechar-modal">&times;</button>
        </div>
        <div class="modal-corpo">
            <form method="POST" class="perfil-form">
                <div class="form-grupo">
                    <label class="form-label" for="name">Nome</label>
                    <input type="text" id="name" name="name" class="form-input" value="<?php echo $username; ?>" required>
                </div>

                <div class="form-grupo">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-input" value="<?php echo $user['email']; ?>" required>
                </div>

                <div class="form-acoes">
                    <button type="button" class="btn btn-secundario fechar-modal">Cancelar</button>
                    <button type="submit" name="edit_usuario" class="btn btn-primario">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal" id="editar-bio">
    <div class="modal-conteudo">
        <div class="modal-cabecalho">
            <h3>Editar Bio</h3>
            <button class="fechar-modal">&times;</button>
        </div>
        <div class="modal-corpo">
            <form method="POST" class="perfil-form">
                <div class="form-grupo">
                    <label class="form-label" for="description">Biografia</label>
                    <textarea id="description" name="description" class="form-input textarea" rows="5"><?php echo $user['description']; ?></textarea>
                </div>

                <div class="form-acoes">
                    <button type="button" class="btn btn-secundario fechar-modal">Cancelar</button>
                    <button type="submit" class="btn btn-primario">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="mudar-banner-modal" class="modal">
    <div class="modal-content">
        <span class="close-button">&times;</span>
        <h2>Mudar Imagem de Fundo</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-grupo">
                <label for="background_image" class="form-label">Selecione uma nova imagem de fundo</label>
                <input type="file" id="background_image" name="background_image" class="form-input" accept="image/*">
                <p class="text-small mt-2">Dimensões recomendadas: 1168 x 347.17 pixels</p>
            </div>
            <div class="flex justify-end gap-2 mt-4">
                <button type="button" class="btn btn-secundario close-modal">Cancelar</button>
                <button type="submit" class="btn btn-primario">Salvar</button>
            </div>
        </form>
    </div>
</div>


<script>
    // Funções para manipulação de modais
    document.addEventListener('DOMContentLoaded', function() {
        // Seleciona todos os botões que abrem modais
        const modalButtons = document.querySelectorAll('[data-modal]');
        const closeButtons = document.querySelectorAll('.close-button, .close-modal');

        // Adiciona listener para abrir o modal correspondente
        modalButtons.forEach(button => {
            button.addEventListener('click', () => {
                const modalId = button.getAttribute('data-modal');
                const modal = document.getElementById(`${modalId}-modal`);
                if (modal) {
                    modal.style.display = 'block';
                }
            });
        });

        // Adiciona listener para fechar modais
        closeButtons.forEach(button => {
            button.addEventListener('click', () => {
                const modal = button.closest('.modal');
                if (modal) {
                    modal.style.display = 'none';
                }
            });
        });

        // Fecha o modal quando clicar fora dele
        window.addEventListener('click', (event) => {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        });
    });

</script>


<div class="modal" id="adicionar-meta">
    <div class="modal-conteudo">
        <div class="modal-cabecalho">
            <h3>Adicionar Nova Meta</h3>
            <button class="fechar-modal">&times;</button>
        </div>
        <div class="modal-corpo">
            <form method="POST" class="perfil-form">
                <div class="form-grupo">
                    <label class="form-label" for="titulo">Título da Meta</label>
                    <input type="text" id="titulo" name="titulo" class="form-input" required>
                </div>

                <div class="form-grupo">
                    <label class="form-label" for="descricao">Descrição</label>
                    <textarea id="descricao" name="descricao" class="form-input textarea"></textarea>
                </div>

                <div class="form-grupo">
                    <label class="form-label" for="prazo">Prazo</label>
                    <input type="date" id="prazo" name="prazo" class="form-input" required>
                </div>

                <div class="form-acoes">
                    <button type="button" class="btn btn-secundario fechar-modal">Cancelar</button>
                    <button type="submit" name="adicionar_meta" class="btn btn-primario">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal" id="editar-quem-sou">
    <div class="modal-conteudo">
        <div class="modal-cabecalho">
            <h3>Quem Sou Eu</h3>
            <button class="fechar-modal">&times;</button>
        </div>
        <div class="modal-corpo">
            <form method="POST" class="perfil-form">
                <div class="form-grupo">
                    <label class="form-label" for="sobre_voce">Fale Sobre Você</label>
                    <textarea id="sobre_voce" name="sobre_voce" class="form-input textarea"><?php echo $perfil['fale_sobre_voce'] ?? ''; ?></textarea>
                </div>

                <div class="form-grupo">
                    <label class="form-label" for="lembrancas">Minhas Lembranças</label>
                    <textarea id="lembrancas" name="lembrancas" class="form-input textarea"><?php echo $perfil['minhas_lembrancas'] ?? ''; ?></textarea>
                </div>

                <div class="form-grupo">
                    <label class="form-label" for="pontos_fortes">Pontos Fortes</label>
                    <textarea id="pontos_fortes" name="pontos_fortes" class="form-input textarea"><?php echo $perfil['pontos_fortes'] ?? ''; ?></textarea>
                </div>

                <div class="form-grupo">
                    <label class="form-label" for="pontos_fracos">Pontos Fracos</label>
                    <textarea id="pontos_fracos" name="pontos_fracos" class="form-input textarea"><?php echo $perfil['pontos_fracos'] ?? ''; ?></textarea>
                </div>

                <div class="form-grupo">
                    <label class="form-label" for="valores">Meus Valores</label>
                    <textarea id="valores" name="valores" class="form-input textarea"><?php echo $perfil['meus_valores'] ?? ''; ?></textarea>
                </div>

                <?php
                $aptidoesArray = [];
                if (isset($perfil['principais_aptidoes'])) {
                    $aptidoesArray = explode(', ', $perfil['principais_aptidoes']);
                }

                $aptidoesPossiveis = [
                    'comunicação', 'liderança', 'organização', 'criatividade',
                    'resolução de problemas', 'trabalho em equipe', 'adaptabilidade',
                    'pensamento crítico', 'empatia', 'gestão de tempo'
                ];
                ?>

                <div class="form-grupo">
                    <label class="form-label">Principais Aptidões</label>
                    <div class="checkbox-group">
                        <?php foreach($aptidoesPossiveis as $aptidao): ?>
                            <div class="checkbox-item">
                                <input type="checkbox"
                                       id="apt_<?php echo str_replace(' ', '_', $aptidao); ?>"
                                       name="aptidoes[]"
                                       value="<?php echo $aptidao; ?>"
                                    <?php echo in_array($aptidao, $aptidoesArray) ? 'checked' : ''; ?>>
                                <label for="apt_<?php echo str_replace(' ', '_', $aptidao); ?>"><?php echo ucfirst($aptidao); ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-grupo">
                    <label class="form-label" for="familia">Relações com a Família</label>
                    <textarea id="familia" name="familia" class="form-input textarea"><?php echo $perfil['relacoes_familia'] ?? ''; ?></textarea>
                </div>

                <div class="form-grupo">
                    <label class="form-label" for="amigos">Relações com Amigos</label>
                    <textarea id="amigos" name="amigos" class="form-input textarea"><?php echo $perfil['relacoes_amigos'] ?? ''; ?></textarea>
                </div>

                <div class="form-grupo">
                    <label class="form-label" for="escola">Relações na Escola</label>
                    <textarea id="escola" name="escola" class="form-input textarea"><?php echo $perfil['relacoes_escola'] ?? ''; ?></textarea>
                </div>

                <div class="form-grupo">
                    <label class="form-label" for="sociedade">Relações com a Sociedade</label>
                    <textarea id="sociedade" name="sociedade" class="form-input textarea"><?php echo $perfil['relacoes_sociedade'] ?? ''; ?></textarea>
                </div>

                <div class="form-grupo">
                    <label class="form-label" for="gosto_fazer">O que gosto de fazer</label>
                    <textarea id="gosto_fazer" name="gosto_fazer" class="form-input textarea"><?php echo $perfil['gosto_fazer'] ?? ''; ?></textarea>
                </div>

                <div class="form-grupo">
                    <label class="form-label" for="nao_gosto">O que não gosto de fazer</label>
                    <textarea id="nao_gosto" name="nao_gosto" class="form-input textarea"><?php echo $perfil['nao_gosto_fazer'] ?? ''; ?></textarea>
                </div>

                <div class="form-grupo">
                    <label class="form-label" for="rotina">Minha Rotina</label>
                    <textarea id="rotina" name="rotina" class="form-input textarea"><?php echo $perfil['rotina'] ?? ''; ?></textarea>
                </div>

                <div class="form-grupo">
                    <label class="form-label" for="lazer">Meu Lazer</label>
                    <textarea id="lazer" name="lazer" class="form-input textarea"><?php echo $perfil['lazer'] ?? ''; ?></textarea>
                </div>

                <div class="form-grupo">
                    <label class="form-label" for="estudos">Meus Estudos</label>
                    <textarea id="estudos" name="estudos" class="form-input textarea"><?php echo $perfil['estudos'] ?? ''; ?></textarea>
                </div>

                <div class="form-grupo">
                    <label class="form-label" for="vida_escolar">Minha Vida Escolar</label>
                    <textarea id="vida_escolar" name="vida_escolar" class="form-input textarea"><?php echo $perfil['vida_escolar'] ?? ''; ?></textarea>
                </div>

                <h3 class="section-subtitle mt-4">Como me vejo</h3>

                <div class="form-grupo">
                    <label class="form-label" for="visao_fisica">Fisicamente</label>
                    <textarea id="visao_fisica" name="visao_fisica" class="form-input textarea"><?php echo $perfil['visao_fisica'] ?? ''; ?></textarea>
                </div>

                <div class="form-grupo">
                    <label class="form-label" for="visao_intelectual">Intelectualmente</label>
                    <textarea id="visao_intelectual" name="visao_intelectual" class="form-input textarea"><?php echo $perfil['visao_intelectual'] ?? ''; ?></textarea>
                </div>

                <div class="form-grupo">
                    <label class="form-label" for="visao_emocional">Emocionalmente</label>
                    <textarea id="visao_emocional" name="visao_emocional" class="form-input textarea"><?php echo $perfil['visao_emocional'] ?? ''; ?></textarea>
                </div>

                <h3 class="section-subtitle mt-4">Como os outros me veem</h3>

                <div class="form-grupo">
                    <label class="form-label" for="visao_amigos">Na visão dos amigos</label>
                    <textarea id="visao_amigos" name="visao_amigos" class="form-input textarea"><?php echo $perfil['visao_dos_amigos'] ?? ''; ?></textarea>
                </div>

                <div class="form-grupo">
                    <label class="form-label" for="visao_familiares">Na visão dos familiares</label>
                    <textarea id="visao_familiares" name="visao_familiares" class="form-input textarea"><?php echo $perfil['visao_dos_familiares'] ?? ''; ?></textarea>
                </div>

                <div class="form-grupo">
                    <label class="form-label" for="visao_professores">Na visão dos professores</label>
                    <textarea id="visao_professores" name="visao_professores" class="form-input textarea"><?php echo $perfil['visao_dos_professores'] ?? ''; ?></textarea>
                </div>

                <div class="form-grupo">
                    <label class="form-label" for="autovalorizacao">Auto-Valorização (1-10)</label>
                    <input type="range" id="autovalorizacao" name="autovalorizacao" min="1" max="10" value="<?php echo $perfil['autovalorizacao_total'] ?? 5; ?>" class="form-range">
                    <div class="range-value" id="range-value"><?php echo $perfil['autovalorizacao_total'] ?? 5; ?></div>
                </div>

                <div class="form-acoes">
                    <button type="button" class="btn btn-secundario fechar-modal">Cancelar</button>
                    <button type="submit" name="salvar_perfil_completo" class="btn btn-primario">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal" id="teste-personalidade">
    <div class="modal-conteudo">
        <div class="modal-cabecalho">
            <h3>Teste de Personalidade</h3>
            <button class="fechar-modal">&times;</button>
        </div>
        <div class="modal-corpo">
            <form method="POST" class="perfil-form">
                <div class="form-grupo">
                    <label class="form-label">Extroversão vs. Introversão</label>
                    <p class="form-info">Defina quanto você se considera extrovertido(a)</p>
                    <input type="range" name="extrovertido" min="0" max="100" value="<?php echo $personalidade['extrovertido'] ?? 50; ?>" class="form-range">
                    <div class="range-labels">
                        <span>Introvertido</span>
                        <span class="range-value"><?php echo $personalidade['extrovertido'] ?? 50; ?>%</span>
                        <span>Extrovertido</span>
                    </div>
                </div>

                <div class="form-grupo">
                    <label class="form-label">Intuição vs. Sensação</label>
                    <p class="form-info">Defina quanto você se baseia na intuição</p>
                    <input type="range" name="intuitivo" min="0" max="100" value="<?php echo $personalidade['intuitivo'] ?? 50; ?>" class="form-range">
                    <div class="range-labels">
                        <span>Sensação</span>
                        <span class="range-value"><?php echo $personalidade['intuitivo'] ?? 50; ?>%</span>
                        <span>Intuição</span>
                    </div>
                </div>

                <div class="form-grupo">
                    <label class="form-label">Racionalidade vs. Emocionalidade</label>
                    <p class="form-info">Defina quanto você toma decisões baseadas na razão</p>
                    <input type="range" name="racional" min="0" max="100" value="<?php echo $personalidade['racional'] ?? 50; ?>" class="form-range">
                    <div class="range-labels">
                        <span>Emocional</span>
                        <span class="range-value"><?php echo $personalidade['racional'] ?? 50; ?>%</span>
                        <span>Racional</span>
                    </div>
                </div>

                <div class="form-grupo">
                    <label class="form-label">Julgamento vs. Percepção</label>
                    <p class="form-info">Defina quanto você prefere situações estruturadas</p>
                    <input type="range" name="julgador" min="0" max="100" value="<?php echo $personalidade['julgador'] ?? 50; ?>" class="form-range">
                    <div class="range-labels">
                        <span>Percepção</span>
                        <span class="range-value"><?php echo $personalidade['julgador'] ?? 50; ?>%</span>
                        <span>Julgamento</span>
                    </div>
                </div>

                <div class="form-acoes">
                    <button type="button" class="btn btn-secundario fechar-modal">Cancelar</button>
                    <button type="submit" name="teste_personalidade" class="btn btn-primario">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* Estilos adicionais para modais que não estão no CSS externo */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        overflow-y: auto;
        padding: 2rem 1rem;
    }

    .modal.show {
        display: flex;
        align-items: flex-start;
        justify-content: center;
    }

    .modal-conteudo {
        background-color: var(--white);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        width: 100%;
        max-width: 800px;
        margin: 2rem auto;
        overflow: hidden;
        animation: modalFadeIn 0.3s ease;
    }

    @keyframes modalFadeIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .modal-cabecalho {
        padding: 1.5rem;
        border-bottom: 1px solid var(--gray-200);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .modal-cabecalho h3 {
        margin: 0;
        font-weight: 600;
        color: var(--gray-800);
    }

    .fechar-modal {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: var(--gray-600);
        transition: color 0.2s ease;
    }

    .fechar-modal:hover {
        color: var(--danger);
    }

    .modal-corpo {
        padding: 1.5rem;
        max-height: 70vh;
        overflow-y: auto;
    }

    /* Estilos para inputs tipo range */
    .form-range {
        width: 100%;
        height: 1rem;
        padding: 0;
        background-color: transparent;
        -webkit-appearance: none;
        appearance: none;
    }

    .form-range:focus {
        outline: none;
    }

    .form-range::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: var(--primary);
        cursor: pointer;
        margin-top: -8px;
    }

    .form-range::-moz-range-thumb {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: var(--primary);
        cursor: pointer;
    }

    .form-range::-webkit-slider-runnable-track {
        width: 100%;
        height: 4px;
        cursor: pointer;
        background: var(--gray-300);
        border-radius: 999px;
    }

    .form-range::-moz-range-track {
        width: 100%;
        height: 4px;
        cursor: pointer;
        background: var(--gray-300);
        border-radius: 999px;
    }

    .range-labels {
        display: flex;
        justify-content: space-between;
        margin-top: 0.5rem;
        font-size: 0.875rem;
        color: var(--gray-600);
    }

    .range-value {
        font-weight: 600;
        color: var(--primary);
    }

    .section-subtitle {
        font-size: 1.125rem;
        font-weight: 600;
        color: var(--gray-700);
        margin-top: 2rem;
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid var(--gray-200);
    }

    .checkbox-group {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 0.75rem;
    }

    .checkbox-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-info {
        font-size: 0.875rem;
        color: var(--gray-600);
        margin-bottom: 0.75rem;
    }

    .form-acoes {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        margin-top: 2rem;
    }
</style>

<script>
    // Script adicional para controlar os inputs de range
    document.addEventListener('DOMContentLoaded', function() {
        const rangeInputs = document.querySelectorAll('input[type="range"]');

        rangeInputs.forEach(input => {
            const valueDisplay = input.nextElementSibling?.querySelector('.range-value');

            if (valueDisplay) {
                // Atualizar valor ao carregar
                valueDisplay.textContent = input.value + '%';

                // Atualizar valor ao mover o slider
                input.addEventListener('input', function() {
                    valueDisplay.textContent = this.value + '%';
                });
            }

            // Para o range da autoavaliação que tem display separado
            if (input.id === 'autovalorizacao') {
                const rangeValue = document.getElementById('range-value');
                if (rangeValue) {
                    // Atualizar valor ao carregar
                    rangeValue.textContent = input.value;

                    // Atualizar valor ao mover o slider
                    input.addEventListener('input', function() {
                        rangeValue.textContent = this.value;
                    });
                }
            }
        });
    });
</script>


<div class="style-switcher">
    <!-- Botão de alternância do painel -->
    <div class="style-switcher-toggler s-icon">
        <i class="fas fa-palette fa-spin"></i>
    </div>

    <!-- Botão claro/escuro -->
    <div class="day-night s-icon" id="theme-toggle">
        <i class="fas fa-moon icon moon"></i>
        <i class="fas fa-sun icon sun"></i>
    </div>

    <!-- Painel de cores -->
    <div class="theme-panel">
        <h4>Temas</h4>

      <?php  echo htmlspecialchars($themeColor); ?>
        <hr>
        <br>
        <div class="colors">
            <!-- Temas originais -->
            <button class="theme-btn" data-theme="theme-base" style="background-color: #0064fa;" aria-label="Azul padrão"></button>
            <button class="theme-btn" data-theme="theme-red" style="background-color: #ff0000;" aria-label="Vermelho"></button>
            <button class="theme-btn" data-theme="theme-green" style="background-color: #00ff00;" aria-label="Verde"></button>
            <button class="theme-btn" data-theme="theme-blue" style="background-color: #0000ff;" aria-label="Azul"></button>
            <button class="theme-btn" data-theme="theme-yellow" style="background-color: #ffff00;" aria-label="Amarelo"></button>
            <button class="theme-btn" data-theme="theme-purple" style="background-color: #800080;" aria-label="Roxo"></button>
            <button class="theme-btn" data-theme="theme-pink" style="background-color: #ff69b4;" aria-label="Rosa"></button>
            <button class="theme-btn" data-theme="theme-teal" style="background-color: #008080;" aria-label="Teal"></button>
            <button class="theme-btn" data-theme="theme-orange" style="background-color: #ffa500;" aria-label="Laranja"></button>
            <button class="theme-btn" data-theme="theme-brown" style="background-color: #8b4513;" aria-label="Marrom"></button>
            <button class="theme-btn" data-theme="theme-gray" style="background-color: #808080;" aria-label="Cinza"></button>

            <!-- Metálicos -->
            <button class="theme-btn" data-theme="theme-gold" style="background-color: #ffd700;" aria-label="Dourado"></button>
            <button class="theme-btn" data-theme="theme-silver" style="background-color: #c0c0c0;" aria-label="Prata"></button>
            <button class="theme-btn" data-theme="theme-bronze" style="background-color: #cd7f32;" aria-label="Bronze"></button>
            <button class="theme-btn" data-theme="theme-copper" style="background-color: #b87333;" aria-label="Cobre"></button>
            <button class="theme-btn" data-theme="theme-platinum" style="background-color: #e5e4e2;" aria-label="Platina"></button>
            <button class="theme-btn" data-theme="theme-titanium" style="background-color: #878681;" aria-label="Titânio"></button>

            <!-- Cores vibrantes -->
            <button class="theme-btn" data-theme="theme-neon-green" style="background-color: #39ff14;" aria-label="Verde Neon"></button>
            <button class="theme-btn" data-theme="theme-hot-pink" style="background-color: #ff1493;" aria-label="Rosa Vibrante"></button>
            <button class="theme-btn" data-theme="theme-electric-purple" style="background-color: #bf00ff;" aria-label="Roxo Elétrico"></button>
            <button class="theme-btn" data-theme="theme-neon-blue" style="background-color: #1e90ff;" aria-label="Azul Neon"></button>
            <button class="theme-btn" data-theme="theme-magenta" style="background-color: #ff00ff;" aria-label="Magenta"></button>
            <button class="theme-btn" data-theme="theme-cyan" style="background-color: #00ffff;" aria-label="Ciano"></button>

            <!-- Cores pastéis -->
            <button class="theme-btn" data-theme="theme-pastel-blue" style="background-color: #a7c7e7;" aria-label="Azul Pastel"></button>
            <button class="theme-btn" data-theme="theme-pastel-pink" style="background-color: #f8c8dc;" aria-label="Rosa Pastel"></button>
            <button class="theme-btn" data-theme="theme-pastel-green" style="background-color: #b5e7a0;" aria-label="Verde Pastel"></button>
            <button class="theme-btn" data-theme="theme-pastel-yellow" style="background-color: #fdfd96;" aria-label="Amarelo Pastel"></button>
            <button class="theme-btn" data-theme="theme-pastel-lavender" style="background-color: #d8b5e7;" aria-label="Lavanda Pastel"></button>

            <!-- Cores terrosas e naturais -->
            <button class="theme-btn" data-theme="theme-terra-cotta" style="background-color: #e2725b;" aria-label="Terracota"></button>
            <button class="theme-btn" data-theme="theme-sage" style="background-color: #9caf88;" aria-label="Sálvia"></button>
            <button class="theme-btn" data-theme="theme-moss" style="background-color: #8a9a5b;" aria-label="Musgo"></button>
            <button class="theme-btn" data-theme="theme-sand" style="background-color: #d2b48c;" aria-label="Areia"></button>
            <button class="theme-btn" data-theme="theme-slate" style="background-color: #708090;" aria-label="Ardósia"></button>

            <!-- Temas de marcas -->
            <button class="theme-btn" data-theme="theme-social-blue" style="background-color: #1877f2;" aria-label="Azul Social"></button>
            <button class="theme-btn" data-theme="theme-social-red" style="background-color: #ff0000;" aria-label="Vermelho Social"></button>
            <button class="theme-btn" data-theme="theme-eco-green" style="background-color: #00d084;" aria-label="Verde Ecológico"></button>
            <button class="theme-btn" data-theme="theme-tech-dark" style="background-color: #333333;" aria-label="Preto Tech"></button>
            <button class="theme-btn" data-theme="theme-streaming-red" style="background-color: #e50914;" aria-label="Vermelho Streaming"></button>

            <!-- Temas gradientes (representados pela cor principal) -->
            <button class="theme-btn" data-theme="theme-sunset" style="background-color: #ff7e5f;" aria-label="Pôr do Sol"></button>
            <button class="theme-btn" data-theme="theme-ocean" style="background-color: #2193b0;" aria-label="Oceano"></button>
            <button class="theme-btn" data-theme="theme-forest" style="background-color: #134e5e;" aria-label="Floresta"></button>
            <button class="theme-btn" data-theme="theme-berry" style="background-color: #6f0000;" aria-label="Frutas Vermelhas"></button>
            <button class="theme-btn" data-theme="theme-aurora" style="background-color: #00c9ff;" aria-label="Aurora"></button>

            <!-- Temas conceituais -->
            <button class="theme-btn" data-theme="theme-vintage" style="background-color: #d1b280;" aria-label="Vintage"></button>
            <button class="theme-btn" data-theme="theme-cyberpunk" style="background-color: #f706cf;" aria-label="Cyberpunk"></button>
            <button class="theme-btn" data-theme="theme-space" style="background-color: #3e1f47;" aria-label="Espaço"></button>
            <button class="theme-btn" data-theme="theme-coffee" style="background-color: #65433c;" aria-label="Café"></button>
            <button class="theme-btn" data-theme="theme-mint" style="background-color: #3eb489;" aria-label="Menta"></button>

            <!-- Pedras preciosas -->
            <button class="theme-btn" data-theme="theme-ruby" style="background-color: #e0115f;" aria-label="Rubi"></button>
            <button class="theme-btn" data-theme="theme-emerald" style="background-color: #046307;" aria-label="Esmeralda"></button>
            <button class="theme-btn" data-theme="theme-sapphire" style="background-color: #0f52ba;" aria-label="Safira"></button>
            <button class="theme-btn" data-theme="theme-amethyst" style="background-color: #9966cc;" aria-label="Ametista"></button>
            <button class="theme-btn" data-theme="theme-topaz" style="background-color: #ffc87c;" aria-label="Topázio"></button>
        </div>
    </div>

</div>

<style>
    /* Style Switcher */
    .style-switcher {
        position: fixed;
        right: 0;
        top: 60px;
        padding: 15px;
        width: 200px;
        background: var(--bg-black-100, #fff);
        z-index: 101;
        border-radius: 5px;
        transform: translateX(100%);
        border: 1px solid var(--primary, #0064fa);
        transition: all 0.3s ease;
    }

    .style-switcher.open {
        transform: translateX(0%);
        box-shadow: -3px 0 15px rgba(0,0,0,0.1);
    }

    .style-switcher .s-icon {
        position: absolute;
        height: 40px;
        width: 40px;
        text-align: center;
        background: var(--bg-black-100, #fff);
        color: var(--primary, #0064fa);
        right: 100%;
        border: 1px solid var(--primary, #0064fa);
        margin-right: 25px;
        cursor: pointer;
        border-radius: 50%;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .style-switcher .s-icon i {
        line-height: 40px;
        font-size: 20px;
    }

    .style-switcher .s-icon:hover {
        background: var(--primary, #0064fa);
        color: #fff;
    }

    .style-switcher .style-switcher-toggler {
        top: 0;
    }

    .style-switcher .day-night {
        top: 55px;
    }

    .style-switcher h4 {
        margin: 0 0 10px;
        color: var(--text-black-700, #333);
        font-size: 16px;
        font-weight: 600;
        text-transform: capitalize;
    }

    .style-switcher .colors {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        padding: 5px;
    }

    .style-switcher .theme-btn {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        border: 2px solid #fff;
        cursor: pointer;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        transition: all 0.3s ease;
    }

    .style-switcher .theme-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 0 8px rgba(0,0,0,0.3);
    }

    /* Animação para o ícone de configuração */
    @keyframes fa-spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .fa-spin {
        animation: fa-spin 2s infinite linear;
    }

    /* Animação para o botão claro/escuro */
    [data-theme='dark'] .day-night .sun {
        display: none;
    }

    [data-theme='dark'] .day-night .moon {
        display: block;
    }

    [data-theme='light'] .day-night .sun {
        display: block;
    }

    [data-theme='light'] .day-night .moon {
        display: none;
    }

    /* Temas escuro/claro */
    [data-theme='dark'] {
        --bg-black-100: #222;
        --text-black-700: #fff;
    }

    [data-theme='light'] {
        --bg-black-100: #fff;
        --text-black-700: #333;
    }





    /* Temas originais */
    .theme-cyan {
        --primary: #00ffff;
        --primary-light: #80ffff;
        --primary-dark: #008080;
        --bg-black-100: #000;
        --text-black-700: #fff;
        --secondary: #e0ffff;
    }

    .theme-black {
        --primary: #000000;
        --primary-light: #333333;
        --primary-dark: #000000;
        --secondary: #4d4d4d;
    }

    .theme-gold {
        --primary: #ffd700;
        --primary-light: #ffe54d;
        --primary-dark: #d7bf00;
        --secondary: #fff5b3;
    }

    .theme-silver {
        --primary: #c0c0c0;
        --primary-light: #d9d9d9;
        --primary-dark: #8c8c8c;
        --secondary: #f2f2f2;
    }

    .theme-red {
        --primary: #ff4d4d;
        --primary-light: #ff9999;
        --primary-dark: #cc0000;
        --secondary: #ffdddd;
    }

    .theme-green {
        --primary: #4dff4d;
        --primary-light: #99ff99;
        --primary-dark: #008000;
        --secondary: #ddffdd;
    }

    .theme-blue {
        --primary: #4d4dff;
        --primary-light: #9999ff;
        --primary-dark: #000080;
        --secondary: #ddddff;
    }

    .theme-yellow {
        --primary: #ffff4d;
        --primary-light: #ffff99;
        --primary-dark: #ffcc00;
        --secondary: #ffffcc;
    }

    .theme-purple {
        --primary: #8000ff;
        --primary-light: #b366ff;
        --primary-dark: #5900cc;
        --secondary: #e6ccff;
    }

    .theme-pink {
        --primary: #fd55ac;
        --primary-light: #ffc0ff;
        --primary-dark: #ff69b4;
        --secondary: #ffd6e9;
    }

    .theme-teal {
        --primary: #008080;
        --primary-light: #4cb3b3;
        --primary-dark: #004d4d;
        --secondary: #b3e6e6;
    }

    .theme-orange {
        --primary: #ffa500;
        --primary-light: #ffcc80;
        --primary-dark: #ff7700;
        --secondary: #ffedcc;
    }

    .theme-brown {
        --primary: #8b4513;
        --primary-light: #a36741;
        --primary-dark: #59270e;
        --secondary: #d2b89b;
    }

    .theme-gray {
        --primary: #808080;
        --primary-light: #b3b3b3;
        --primary-dark: #4d4d4d;
        --secondary: #e6e6e6;
    }

    /* Novas cores vibrantes */
    .theme-neon-green {
        --primary: #39ff14;
        --primary-light: #85ff70;
        --primary-dark: #00cc00;
        --secondary: #d4ffcf;
    }

    .theme-hot-pink {
        --primary: #ff1493;
        --primary-light: #ff69b4;
        --primary-dark: #c71585;
        --secondary: #ffcce6;
    }

    .theme-electric-purple {
        --primary: #bf00ff;
        --primary-light: #d580ff;
        --primary-dark: #8a00b8;
        --secondary: #f0ccff;
    }

    .theme-neon-blue {
        --primary: #1e90ff;
        --primary-light: #6cb8ff;
        --primary-dark: #0066cc;
        --secondary: #d1e8ff;
    }

    .theme-magenta {
        --primary: #ff00ff;
        --primary-light: #ff80ff;
        --primary-dark: #cc00cc;
        --secondary: #ffccff;
    }

    /* Cores pastéis */
    .theme-pastel-blue {
        --primary: #a7c7e7;
        --primary-light: #d6e6f5;
        --primary-dark: #6a9ac7;
        --secondary: #e9f2fa;
    }

    .theme-pastel-pink {
        --primary: #f8c8dc;
        --primary-light: #fce6ee;
        --primary-dark: #e293b5;
        --secondary: #fdf2f6;
    }

    .theme-pastel-green {
        --primary: #b5e7a0;
        --primary-light: #d8f2ce;
        --primary-dark: #86cd6e;
        --secondary: #ebf8e5;
    }

    .theme-pastel-yellow {
        --primary: #fdfd96;
        --primary-light: #fefecb;
        --primary-dark: #f9f959;
        --secondary: #fefefd;
    }

    .theme-pastel-lavender {
        --primary: #d8b5e7;
        --primary-light: #ecdcf5;
        --primary-dark: #b586cd;
        --secondary: #f5ebfa;
    }

    /* Cores terrosas e naturais */
    .theme-terra-cotta {
        --primary: #e2725b;
        --primary-light: #eea08d;
        --primary-dark: #c45240;
        --secondary: #f7ddd5;
    }

    .theme-sage {
        --primary: #9caf88;
        --primary-light: #c1cfb5;
        --primary-dark: #778d63;
        --secondary: #e6ebe0;
    }

    .theme-moss {
        --primary: #8a9a5b;
        --primary-light: #b0bd8e;
        --primary-dark: #637346;
        --secondary: #e5ead8;
    }

    .theme-sand {
        --primary: #d2b48c;
        --primary-light: #e5d3b3;
        --primary-dark: #b39067;
        --secondary: #f4ede1;
    }

    .theme-slate {
        --primary: #708090;
        --primary-light: #a4b0bc;
        --primary-dark: #4c5866;
        --secondary: #dce0e5;
    }

    /* Temas de marcas famosas */
    .theme-social-blue {
        --primary: #1877f2;
        --primary-light: #619ef5;
        --primary-dark: #0e5bbc;
        --secondary: #e2ebfa;
    }

    .theme-social-red {
        --primary: #ff0000;
        --primary-light: #ff6666;
        --primary-dark: #cc0000;
        --secondary: #ffcccc;
    }

    .theme-eco-green {
        --primary: #00d084;
        --primary-light: #66e3b7;
        --primary-dark: #00a368;
        --secondary: #ccf5e6;
    }

    .theme-tech-dark {
        --primary: #333333;
        --primary-light: #666666;
        --primary-dark: #000000;
        --secondary: #bbbbbb;
    }

    .theme-streaming-red {
        --primary: #e50914;
        --primary-light: #f45762;
        --primary-dark: #b20710;
        --secondary: #fad3d6;
    }

    /* Temas gradientes */
    .theme-sunset {
        --primary: #ff7e5f;
        --primary-light: #ffaf9f;
        --primary-dark: #e35d4d;
        --secondary: #feb692;
    }

    .theme-ocean {
        --primary: #2193b0;
        --primary-light: #6bc6d9;
        --primary-dark: #186d84;
        --secondary: #6dd5ed;
    }

    .theme-forest {
        --primary: #134e5e;
        --primary-light: #5c8d9a;
        --primary-dark: #0c3945;
        --secondary: #71b280;
    }

    .theme-berry {
        --primary: #6f0000;
        --primary-light: #a64d4d;
        --primary-dark: #490000;
        --secondary: #200122;
    }

    .theme-aurora {
        --primary: #00c9ff;
        --primary-light: #66deff;
        --primary-dark: #0098c0;
        --secondary: #92fe9d;
    }

    /* Temas conceituais */
    .theme-vintage {
        --primary: #d1b280;
        --primary-light: #e2cdaa;
        --primary-dark: #a89066;
        --secondary: #f0e6d5;
    }

    .theme-cyberpunk {
        --primary: #f706cf;
        --primary-light: #fa62dd;
        --primary-dark: #c405a5;
        --secondary: #04e1fc;
    }

    .theme-space {
        --primary: #3e1f47;
        --primary-light: #7c5d87;
        --primary-dark: #29142f;
        --secondary: #4b257c;
    }

    .theme-coffee {
        --primary: #65433c;
        --primary-light: #926c64;
        --primary-dark: #462e29;
        --secondary: #c0a392;
    }

    .theme-mint {
        --primary: #3eb489;
        --primary-light: #7dcab0;
        --primary-dark: #2a8c69;
        --secondary: #c3e8d7;
    }

    /* Cores metálicas */
    .theme-bronze {
        --primary: #cd7f32;
        --primary-light: #dba570;
        --primary-dark: #9c5e26;
        --secondary: #f0d4bc;
    }

    .theme-copper {
        --primary: #b87333;
        --primary-light: #d49e70;
        --primary-dark: #9a5526;
        --secondary: #f1d9c5;
    }

    .theme-platinum {
        --primary: #e5e4e2;
        --primary-light: #f0efee;
        --primary-dark: #b8b8b5;
        --secondary: #f8f8f7;
    }

    .theme-titanium {
        --primary: #878681;
        --primary-light: #b0afab;
        --primary-dark: #5e5d5a;
        --secondary: #dbdad8;
    }

    /* Cores de pedras preciosas */
    .theme-ruby {
        --primary: #e0115f;
        --primary-light: #eb6097;
        --primary-dark: #b00d4a;
        --secondary: #f7d1e0;
    }

    .theme-emerald {
        --primary: #046307;
        --primary-light: #3e8e41;
        --primary-dark: #023d04;
        --secondary: #a7e8a9;
    }

    .theme-sapphire {
        --primary: #0f52ba;
        --primary-light: #5382d2;
        --primary-dark: #093b85;
        --secondary: #d1ddf1;
    }

    .theme-amethyst {
        --primary: #9966cc;
        --primary-light: #b799dd;
        --primary-dark: #7744aa;
        --secondary: #e6d9f2;
    }

    .theme-topaz {
        --primary: #ffc87c;
        --primary-light: #ffdba9;
        --primary-dark: #ffb74d;
        --secondary: #fff0db;
    }




    /* Aplicação das cores às classes */
    #navbar .logo span {
        color: var(--primary);
    }

    .btn {
        background-color: var(--primary);
        color: white;
    }

    .btn:hover,
    .btn-primary:hover {
        background-color: var(--primary-light);
    }

    .perfil-foto .editar-foto {
        border: 2px solid var(--primary);
    }

    .landing-preview .section h3 {
        color: var(--primary);
    }

    .landing-preview .contato {
        border-left:4px solid var(--primary);
    }

    .traco-dominante {
        background-color: var(--primary-light);
        color: var(--primary);
    }

    .progresso-barra {
        background-color: var(--primary-light);
    }

    #quem-sou-eu .section h3 {
        color: var(--primary);
    }

    .range-slider {
        background: var(--primary-light);
    }

    .personality-section h3 {
        color: var(--primary);
    }

    .dominant-trait strong {
        background-color: var(--primary);
        color: white;
    }

    .slider-value {
        color: var(--primary);
        background-color: var(--primary-light);
    }



    .trait-fill {
        background-color: var(--primary);
    }



    .landing-tab.active {
        border-bottom-color: var(--primary);

    }

</style>

<script>



    document.addEventListener('DOMContentLoaded', function() {
        const styleSwitcher = document.querySelector('.style-switcher');
        const styleSwitcherToggler = document.querySelector('.style-switcher-toggler');
        const themeToggle = document.getElementById('theme-toggle');
        const themeButtons = document.querySelectorAll('.theme-btn');

        // Toggle do painel
        styleSwitcherToggler.addEventListener('click', () => {
            styleSwitcher.classList.toggle('open');
        });

        // Fechar ao clicar fora
        document.addEventListener('click', (e) => {
            if (!styleSwitcher.contains(e.target) && !styleSwitcherToggler.contains(e.target)) {
                styleSwitcher.classList.remove('open');
            }
        });




        // Toggle do tema claro/escuro
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);

        themeToggle.addEventListener('click', function() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        });

        // Seleção de cores
        themeButtons.forEach(button => {
            button.addEventListener('click', () => {
                const selectedTheme = button.getAttribute('data-theme');
                const formData = new FormData();
                formData.append('theme_color', selectedTheme);

                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                    .then(response => {
                        if (response.ok) {
                            document.documentElement.className = selectedTheme;
                            styleSwitcher.classList.remove('open');
                        }
                    })
                    .catch(error => console.error('Erro ao salvar o tema:', error));
            });
        });

        // Verificar preferência do sistema
        if (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.documentElement.setAttribute('data-theme', 'dark');
            localStorage.setItem('theme', 'dark');
        }
    });
</script>



</body>
</html>