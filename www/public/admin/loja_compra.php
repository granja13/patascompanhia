<?php 
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

require '../../app/vendor/autoload.php';
require '../../src/Database.php';
$twig = require '../../app/site_init.php';

session_start();

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

$loader = new FilesystemLoader('../../templates/admin/');
$twig = new Environment($loader);

$breadcrumbs = [
    'itens' => [
        [
            'url' => null,
            'label' => 'Encomendas'
        ],
    ],
    'ativar_menu' => [
        'item' => 'loja_online',
        'sub_item' => 'encomendas',
    ],
];

echo $twig->render('loja/loja_compra.twig', [
    'produto' => 'teste',
    'site_url' => $site_url,
    'breadcrumbs' => $breadcrumbs,
    'titulo' => $breadcrumbs['itens'][0]['label'],
    'user_details_nome' => $_SESSION['cliente_nome'],
    'cliente_nome' => isset($_SESSION['cliente_nome']) ? $_SESSION['cliente_nome'] : null,
    'cliente_id' => isset($_SESSION['cliente_id']) ? $_SESSION['cliente_id'] : null
]);
