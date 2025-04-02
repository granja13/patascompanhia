<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../../vendor/autoload.php';
require '../../../src/Database.php';
require '../../setting.php';
require_once  '../../site_functions.php';

// Usa a instância do Twig configurada no site_init.php
$twig = require '../../site_init.php';

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

$db = Database::getConnection();

//$loader = new FilesystemLoader('../../../templates/');
//$twig = new Environment($loader);

function registrarUsuario($db, $nome, $email, $password, $pais, $cidade, $cod_postal, $rua, $nr_porta, $token, $last_ip, $perms, $logins)
{
    $verificarEmail = $db->prepare("SELECT * FROM clientes WHERE email = :email");
    $verificarEmail->bindParam(":email", $email);
    $verificarEmail->execute();

    if ($verificarEmail->rowCount() > 0) {
        header("Location: ../../mods/client/autenticacao.php?error=1");
        exit();
    }

    $data_atual = date('Y-m-d H:i:s');
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $db->beginTransaction();

    try {
        $stmt = $db->prepare("INSERT INTO clientes (nome, email, data_criacao, data_modificacao) VALUES (:nome, :email, :data_criacao, :data_modificacao)");
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':data_criacao', $data_atual);
        $stmt->bindParam(':data_modificacao', $data_atual);

        if (!$stmt->execute()) {
            $db->rollBack();
            header("Location: ../../mods/client/autenticacao.php?error=1");
            exit();
        }

        $id_cliente = $db->lastInsertId();

        $stmtEndereco = $db->prepare("INSERT INTO clientes_moradas (id_cliente, nome, pais, cidade, cod_postal, rua, nr_porta) VALUES (:id_cliente, :nome, :pais, :cidade, :cod_postal, :rua, :nr_porta)");
        $stmtEndereco->bindParam(':id_cliente', $id_cliente);
        $stmtEndereco->bindParam(':nome', $nome);
        $stmtEndereco->bindParam(':pais', $pais);
        $stmtEndereco->bindParam(':cidade', $cidade);
        $stmtEndereco->bindParam(':cod_postal', $cod_postal);
        $stmtEndereco->bindParam(':rua', $rua);
        $stmtEndereco->bindParam(':nr_porta', $nr_porta);

        if (!$stmtEndereco->execute()) {
            $db->rollBack();
            header("Location: ../../mods/client/autenticacao.php?error=1");
            exit();
        }

        $stmtUtilizador = $db->prepare("INSERT INTO clientes_utilizadores (nome, email, password, token, id_cliente, last_ip, last_login, perms, logins) VALUES (:nome, :email, :password, :token, :id_cliente, :last_ip, :last_login, :perms, :logins)");
        $stmtUtilizador->bindParam(':nome', $nome);
        $stmtUtilizador->bindParam(':email', $email);
        $stmtUtilizador->bindParam(':password', $passwordHash);
        $stmtUtilizador->bindParam(':token', $token);
        $stmtUtilizador->bindParam(':id_cliente', $id_cliente);
        $stmtUtilizador->bindParam(':last_ip', $last_ip);
        $stmtUtilizador->bindParam(':last_login', $data_atual);
        $stmtUtilizador->bindParam(':perms', $perms);
        $stmtUtilizador->bindParam(':logins', $logins);

        if (!$stmtUtilizador->execute()) {
            $db->rollBack();
            header("Location: ../../mods/client/autenticacao.php?error=1");
            exit();
        }

        $db->commit();

        // Enviar email de boas-vindas
        $assunto = "Bem-vindo ao nosso serviço, $nome!";
        $mensagem = "Olá $nome,\n\nObrigado por se registrar em nosso serviço. Seu cadastro foi concluído com sucesso!\n\nSe precisar de qualquer ajuda, estamos à disposição.\n\nAtenciosamente,\nEquipe de Suporte";

        send_mail($email, $assunto, $mensagem);

        header("Location: ../../mods/client/autenticacao.php?success=1");
        exit();
    } catch (Exception $e) {
        $db->rollBack();
        header("Location: ../../mods/client/autenticacao.php?error=1");
        exit();
    }
}

function loginUsuario($db, $email, $password, $redirect)
{
    $stmt = $db->prepare("SELECT * FROM clientes_utilizadores WHERE email = :email");
    $stmt->bindParam(":email", $email);
    $stmt->execute();

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($password, $usuario['password'])) {
        $_SESSION['cliente_id'] = $usuario['id_cliente'];
        $_SESSION['cliente_nome'] = $usuario['nome'];
        $_SESSION['cliente_email'] = $usuario['email'];
        $_SESSION['cliente_perms'] = $usuario['perms'];

        if ($redirect === 'checkout') {
            header("Location: ../../public/checkout.php");
        } else {
            header("Location: ../../mods/client/index.php");
        }
        exit();
    } else {
        header("Location: ../../mods/client/autenticacao.php?error=1");
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['acao'])) {
    $acao = $_POST['acao'];

    if ($acao == "registrar") {
        $nome = $_POST['nome'];
        $email = $_POST['email'];
        $pais = $_POST['pais'];
        $cod_postal = $_POST['cod_postal'];
        $rua = $_POST['rua'];
        $nr_porta = $_POST['nr_porta'];
        $password = $_POST['password'];
        $cidade = $_POST['cidade'];
        $token = bin2hex(random_bytes(16));
        $last_ip = $_SERVER['REMOTE_ADDR'];
        $perms = '1';
        $logins = '1';

        registrarUsuario($db, $nome, $email, $password, $pais, $cidade, $cod_postal, $rua, $nr_porta, $token, $last_ip, $perms, $logins);
    } elseif ($acao == "login") {
        $email = $_POST['email'];
        $password = $_POST['password'];
        $redirect = isset($_POST['redirect']) ? $_POST['redirect'] : 'autenticacao';

        loginUsuario($db, $email, $password, $redirect);
    }
}

$twig->addGlobal('cliente_nome', isset($_SESSION['cliente_nome']) ? $_SESSION['cliente_nome'] : null);
$twig->addGlobal('cliente_id', isset($_SESSION['cliente_id']) ? $_SESSION['cliente_id'] : null);
$twig->addGlobal('cliente_perms', isset($_SESSION['cliente_perms']) ? $_SESSION['cliente_perms'] : null);

echo $twig->render('autenticacao.twig', [
    'site_url' => $site_url,
    'success' => isset($_GET['success']) ? $_GET['success'] : null,
    'error' => isset($_GET['error']) ? $_GET['error'] : null,
    'cliente_nome' => isset($_SESSION['cliente_nome']) ? $_SESSION['cliente_nome'] : '',
    'cliente_id' => isset($_SESSION['cliente_id']) ? $_SESSION['cliente_id'] : null,
    'cliente_perms' => isset($_SESSION['cliente_perms']) ? $_SESSION['cliente_perms'] : null,
    'categorias' => $categorias,
    'subcategorias_por_categoria' => $subCategoriasPorCategoria
]);

