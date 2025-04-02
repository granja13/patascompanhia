<?php
require '../../app/vendor/autoload.php';
require '../../src/Database.php';

session_start();

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

$loader = new FilesystemLoader('../../templates/admin/');
$twig = new Environment($loader);

$db = Database::getConnection();

$id = isset($_GET['id']) ? $_GET['id'] : 0;

$dados_cliente = [];
$dados_cliente_morada = [];
$encomendas_detalhe = [];
$encomendas_id = [];

if (is_string($id) && !empty($id)) {
    $sql_encomendas = $db->prepare("
        SELECT c.*, cu.last_ip, cu.last_login, cu.perms, cu.logins 
        FROM clientes c 
        LEFT JOIN clientes_utilizadores cu ON cu.email = c.email 
        WHERE c.id = :id
    ");
    $sql_encomendas->bindValue(':id', $id, PDO::PARAM_STR);
    $sql_encomendas->execute();
    $dados_cliente = $sql_encomendas->fetch(PDO::FETCH_ASSOC);

    $sql_moradas = $db->prepare("
        SELECT * FROM clientes_moradas
        WHERE id_cliente = :id_cliente
    ");
    $sql_moradas->bindValue(':id_cliente', $id, PDO::PARAM_STR);
    $sql_moradas->execute();
    $dados_cliente_morada = $sql_moradas->fetchAll(PDO::FETCH_ASSOC);

    $sql_encomendas_detalhe = $db->prepare("
        SELECT e.id, ei.preco AS 'preco_enc', ei.qty, p.*
        FROM encomendas e 
        LEFT JOIN encomendas_itens ei ON ei.id_order = e.id 
        LEFT JOIN prods p ON p.referencia = ei.referencia 
        WHERE e.id = :id
    ");
    $sql_encomendas_detalhe->bindValue(':id', $id, PDO::PARAM_STR);
    $sql_encomendas_detalhe->execute();
    $encomendas_detalhe = $sql_encomendas_detalhe->fetchAll(PDO::FETCH_ASSOC);

    $sql_encomendas_id = $db->prepare("
        SELECT id FROM encomendas 
        WHERE id_cliente = :id
    ");
    $sql_encomendas_id->bindValue(':id', $id, PDO::PARAM_STR);
    $sql_encomendas_id->execute();
    $encomendas_id = $sql_encomendas_id->fetchAll(PDO::FETCH_ASSOC);
}

if ($_POST) {
    $telemovel = $_POST['telemovel'] ?? '';
    $nif = $_POST['nif'] ?? '';
    $termos = $_POST['termos'] ?? '';

    $password = $_POST['password'] ?? '';
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $perms = $_POST['perms'] ?? '';

    $stmt = $db->prepare("UPDATE `clientes` SET 
        `telefone` = :telefone, 
        `nif` = :nif,
        `data_modificacao` = NOW(), 
        `termos` = :termos
        WHERE id = :id
    ");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->bindValue(':telefone', $telemovel, PDO::PARAM_STR);
    $stmt->bindValue(':nif', $nif, PDO::PARAM_STR);
    $stmt->bindValue(':termos', $termos, PDO::PARAM_INT);
    $stmt->execute();

    $stmt_utilizadores = $db->prepare("UPDATE `clientes_utilizadores` SET 
        `password` = :password, 
        `perms` = :perms
        WHERE id_cliente = :id_cliente
    ");
    $stmt_utilizadores->bindValue(':id_cliente', $id, PDO::PARAM_INT);
    $stmt_utilizadores->bindValue(':password', $passwordHash, PDO::PARAM_STR);
    $stmt_utilizadores->bindValue(':perms', $perms, PDO::PARAM_STR);
    $stmt_utilizadores->execute();

    header("Location: ../../public/admin/clientes_edita.php?id=$id&success=1");
    exit();
}

$breadcrumbs = [
    'itens' => [
        [
            'url' => '../../public/admin/clientes.php',
            'label' => 'Clientes'
        ],
        [
            'url' => null,
            'label' => 'Editar Cliente'
        ],
    ],
    'ativar_menu' => [
        'item' => 'clientes',
        'sub_item' => 'lista',
    ],
];

echo $twig->render('cliente/clientes_edita.twig', [
    'site_url' => $site_url,
    'breadcrumbs' => $breadcrumbs,
    'titulo' => $breadcrumbs['itens'][1]['label'],
    'dados_cliente' => $dados_cliente,
    'dados_cliente_morada' => $dados_cliente_morada,
    'encomendas_detalhe' => $encomendas_detalhe,
    'encomendas_id' => $encomendas_id,
    'success' => isset($_GET['success']) ? $_GET['success'] : null,
    'error' => isset($_GET['error']) ? $_GET['error'] : null,
    'user_details_nome' => $_SESSION['cliente_nome'] ?? null,
    'cliente_nome' => $_SESSION['cliente_nome'] ?? null,
    'cliente_id' => $_SESSION['cliente_id'] ?? null
]);
?>
