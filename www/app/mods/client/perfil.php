<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require '../../vendor/autoload.php';
require '../../../src/Database.php';
require '../../setting.php';

$twig = require '../../site_init.php';

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

$id_cliente = $_SESSION['cliente_id'];

$db = Database::getConnection();
$query = $db->prepare("SELECT * FROM clientes WHERE id = ?");
$query->execute([$id_cliente]);
$dados = $query->fetch();


$twig->addGlobal('cliente_nome', isset($_SESSION['cliente_nome']) ? $_SESSION['cliente_nome'] : null);
$twig->addGlobal('cliente_id', isset($_SESSION['cliente_id']) ? $_SESSION['cliente_id'] : null);

echo $twig->render('perfil.twig', [
    'dados' => $dados,
    'site_url' => $site_url,
    'success' => isset($_GET['success']) ? $_GET['success'] : null,
    'error' => isset($_GET['error']) ? $_GET['error'] : null,
    'cliente_nome' => isset($_SESSION['cliente_nome']) ? $_SESSION['cliente_nome'] : null,
    'cliente_id' => isset($_SESSION['cliente_id']) ? $_SESSION['cliente_id'] : null,
    'cliente_perms' => isset($_SESSION['cliente_perms']) ? $_SESSION['cliente_perms'] : null,
    'categorias' => $categorias,
    'subcategorias_por_categoria' => $subCategoriasPorCategoria
]);
