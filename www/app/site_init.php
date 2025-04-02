<?php
require 'vendor/autoload.php';

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

$db = Database::getConnection();

// Buscar categorias
$sql_categorias = $db->prepare("SELECT * FROM categorias");
$sql_categorias->execute();
$categorias = $sql_categorias->fetchAll(PDO::FETCH_ASSOC);

// Buscar subcategorias
$sql_sub_categoria = $db->prepare("SELECT categoria, nome, tipo FROM sub_categoria");
$sql_sub_categoria->execute();
$sub_categorias = $sql_sub_categoria->fetchAll(PDO::FETCH_ASSOC);

$subCategoriasPorCategoria = [];
foreach ($sub_categorias as $subCategoria) {
    $idCategoria = $subCategoria['categoria'];
    
    if (!isset($subCategoriasPorCategoria[$idCategoria])) {
        $subCategoriasPorCategoria[$idCategoria] = [];
    }

    $subCategoriasPorCategoria[$idCategoria][] = [
        'nome' => $subCategoria['nome'],
        'tipo' => $subCategoria['tipo']
    ];
}

$loader = new FilesystemLoader(__DIR__ . '/../templates/');
$twig = new Environment($loader, [
    'debug' => true,
    'cache' => false,
]);

$twig->addExtension(new \Twig\Extension\DebugExtension());

// Adiciona variáveis globais
$twig->addGlobal('categorias', $categorias);
$twig->addGlobal('subcategorias_por_categoria', $subCategoriasPorCategoria);

// Verificar se o usuário está logado e carregar o carrinho
if (isset($_SESSION['cliente_id'])) {
    // Usuário logado
    $id_cliente = $_SESSION['cliente_id'];


    transferirCarrinhoParaUsuario($db, $id_cliente);

    $sql_carrinho = $db->prepare("SELECT ci.*, p.img, p.nome FROM carrinho_items ci
    LEFT JOIN prods p ON p.referencia = ci.id_prod WHERE ci.id_cliente = :id_cliente");
    $sql_carrinho->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
    $sql_carrinho->execute();
    $listar_carrinho = $sql_carrinho->fetchAll(PDO::FETCH_ASSOC);

    $sql_total_carrinho = $db->prepare("SELECT c.*, ci.*, p.img, p.nome 
    FROM carrinho c
    LEFT JOIN carrinho_items ci ON ci.id_cart = c.id
    LEFT JOIN prods p ON p.referencia = ci.id_prod
    WHERE c.id_cliente = :id_cliente;");
    $sql_total_carrinho->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
    $sql_total_carrinho->execute();
    $sql_total = $sql_total_carrinho->fetchAll(PDO::FETCH_ASSOC);

} else {
    // Usuário não logado - usar a sessão
    if (!isset($_SESSION['carrinho'])) {
        $_SESSION['carrinho'] = [];
    }

    $listar_carrinho = $_SESSION['carrinho'];

    // Calcular o total do carrinho para usuários não logados
    $total_carrinho = array_reduce($listar_carrinho, function ($carry, $item) {
        return $carry + $item['total'];
    }, 0);

    $total_produtos_na_sessao = count($listar_carrinho);
    $sql_total_prods = [['total_prods' => $total_produtos_na_sessao]];
    $sql_total = [['total' => $total_carrinho]];
    $twig->addGlobal('sql_total_prods', $sql_total_prods);
}

// Calcular peso total
$peso_total = 0;
$subtotal = 0;
foreach ($listar_carrinho as $item) {
    $peso_produto = $item['peso'] * $item['quantidade'];
    $peso_total += $peso_produto;
    $subtotal += $item['total'];
}

// Cálculo de portes
$queryPortes = $db->prepare("SELECT preco FROM loja_portes_planos WHERE :peso_total BETWEEN peso_de AND peso_ate LIMIT 1");
$queryPortes->bindParam(':peso_total', $peso_total, PDO::PARAM_INT);
$queryPortes->execute();
$resultadoPortes = $queryPortes->fetch(PDO::FETCH_ASSOC);

$preco_portes = $resultadoPortes ? $resultadoPortes['preco'] : 0;

// Calcular o total final incluindo os portes
$total_final = $subtotal + $preco_portes;

$twig->addGlobal('total_final', $total_final);
$twig->addGlobal('peso_total', $peso_total);
$twig->addGlobal('subtotal', $subtotal);
$twig->addGlobal('preco_portes', $preco_portes);
$twig->addGlobal('listar_carrinho', $listar_carrinho);

return $twig;
