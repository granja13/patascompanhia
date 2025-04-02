<?php 

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
            'label' => 'Clientes'
        ],
    ],
    'ativar_menu' => [
        'item' => 'anuncios',
        'sub_item' => 'home',
    ],
];

echo $twig->render('anuncios/anuncios.twig', [
    'site_url' => $site_url,
    'site_env' => $site_env,
    'breadcrumbs' => $breadcrumbs,
    'titulo' => $breadcrumbs['itens'][0]['label'],
    'user_details_nome' => $_SESSION['cliente_nome'],
    'cliente_nome' => isset($_SESSION['cliente_nome']) ? $_SESSION['cliente_nome'] : null,
    'cliente_id' => isset($_SESSION['cliente_id']) ? $_SESSION['cliente_id'] : null
]);
