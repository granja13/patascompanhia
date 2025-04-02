<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../../vendor/autoload.php';
require '../../../src/Database.php';
require './../setting.php';

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

// Verifica se o ID do produto foi passado
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Conectar ao banco de dados e buscar o produto
$db = Database::getConnection();
$query = $db->prepare("SELECT * FROM produtos WHERE id = ?");
$query->execute([$id]);
$produto = $query->fetch();

// Configurar o Twig
$loader = new FilesystemLoader('../../../templates/');
$twig = new Environment($loader);

// Renderizar o template
echo $twig->render('produto.twig', ['produto' => $produto]);
