<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Iniciar a sessão para acessar as variáveis de sessão
session_start();

require '../vendor/autoload.php';
require '../../src/Database.php';
require '../setting.php';

// Usa a instância do Twig configurada no site_init.php
$twig = require '../site_init.php';

use Tracy\Debugger;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;

$db = Database::getConnection();

// Consultar produtos e marcas
$query = $db->query("SELECT prods.*, categorias.nome AS nome_categoria
    FROM prods
    LEFT JOIN categorias ON prods.categoria = categorias.id_categoria
    WHERE prods.ativo = 1
    GROUP BY prods.pai
    LIMIT 8");
$prods = $query->fetchAll();

$query_marcas = $db->query("SELECT * FROM anuncios WHERE tipo = 'marca'");
$marcas = $query_marcas->fetchAll();

// Renderizar o template com as variáveis de sessão
echo $twig->render('home.twig', [
    'produtos' => $prods,
    'anuncios_marca' => $marcas,
    'cliente_nome' => isset($_SESSION['cliente_nome']) ? $_SESSION['cliente_nome'] : null,
    'cliente_id' => isset($_SESSION['cliente_id']) ? $_SESSION['cliente_id'] : null,
    'cliente_perms' => isset($_SESSION['cliente_perms']) ? $_SESSION['cliente_perms'] : null,
    'site_url' => $site_url
]);
