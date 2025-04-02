<?php 
//exit('teste');
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../../app/vendor/autoload.php';
require '../../src/Database.php';

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

// Configurar o Twig
$loader = new FilesystemLoader('../../templates/admin/');
$twig = new Environment($loader);

$id_cart = (int)$_GET['id'];;
$tipo_cliente = 1;

require '../../app/mods/_libs.mods/cart.class.php';

$cart = new Cart();
$carrinho = $cart->get_cart($id_cart,$tipo_cliente);

$total_cart['preco']=$cart->total_preco;
$total_cart['peso']=$cart->total_peso;
$total_cart['items']=$cart->total_items;
$total_cart['prods']=$cart->total_prods;
$total_cart['promocoes']=$cart->promocoes;
$total_cart['id_cliente']=$cart->cliente;
$total_cart['oferta_portes']=$cart->oferta_portes;
$total_cart['tipo']=$tipo_cliente;
$total_cart['id_cart']=$id_cart;


$breadcrumbs = [
    'itens' => [
        [
            'url' => 'loja_carrinhos.php',
            'label' => 'Carrinhos'
        ],
        [
            'url' => null,
            'label' => 'Carrinho '.$id_cart,
        ],
    ],
    'ativar_menu' => [
        'item' => 'loja_online',
        'sub_item' => 'carrinhos',
    ],
];

// Renderizar o template passando os dados corretamente
echo $twig->render('loja/loja_carrinho.twig', [
    'produto' => 'teste',
    'breadcrumbs' => $breadcrumbs,
    'titulo' => $breadcrumbs['itens'][1]['label'],
    'user_details_nome' => $_SESSION['cliente_nome'],
    'carrinho' => $carrinho,
    'totais' => $total_cart
]);
