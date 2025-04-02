<?php
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

require '../../app/vendor/autoload.php';
require '../../src/Database.php';
$twig = require '../../app/site_init.php';

session_start();

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

// Configurar Twig para admin
$loader = new FilesystemLoader('../../templates/admin/');
$twig = new Environment($loader);

// Conexão DB
$db = Database::getConnection();

// Obter ID da encomenda
$id = isset($_GET['id']) ? $_GET['id'] : 0;

// Inicializar variáveis vazias
$encomendas = [];
$encomendas_detalhe = [];
$detalhe_status = [];

if (is_string($id) && !empty($id)) {
    // Buscar dados da encomenda
    $sql_encomendas = $db->prepare("
        SELECT e.*, cm.nome, cm.pais, cm.cidade, cm.cod_postal, cm.rua, cm.nr_porta
        FROM encomendas e 
        LEFT JOIN clientes_moradas cm ON cm.id = e.envio_morada 
        WHERE e.id = :id
    ");
    $sql_encomendas->bindValue(':id', $id, PDO::PARAM_STR);
    $sql_encomendas->execute();
    $encomendas = $sql_encomendas->fetch(PDO::FETCH_ASSOC);

    // Buscar detalhes dos produtos na encomenda
    $sql_encomendas_detalhe = $db->prepare("
        SELECT e.id, ei.preco AS preco_enc, ei.qty, p.*
        FROM encomendas e 
        LEFT JOIN encomendas_itens ei ON ei.id_order = e.id 
        LEFT JOIN prods p ON p.referencia = ei.referencia 
        WHERE e.id = :id
    ");
    $sql_encomendas_detalhe->bindValue(':id', $id, PDO::PARAM_STR);
    $sql_encomendas_detalhe->execute();
    $encomendas_detalhe = $sql_encomendas_detalhe->fetchAll(PDO::FETCH_ASSOC);
 

    // Buscar status possíveis
    $sql_status = $db->prepare("SELECT * FROM status_encomenda");
    $sql_status->execute();
    $detalhe_status = $sql_status->fetchAll(PDO::FETCH_ASSOC);
}

// Atualização via POST (exemplo: atualizando status da encomenda)
if ($_POST) {
    $status = $_POST['status'] ?? '';

    $stmt = $db->prepare("UPDATE `encomendas` SET 
        `status` = :status
        WHERE id = :id
    ");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->bindValue(':status', $status, PDO::PARAM_STR);
    $stmt->execute();

    header("Location: ../../public/admin/loja_compra_edita.php?id=$id&success=1");
    exit();
}

// Breadcrumbs
$breadcrumbs = [
    'itens' => [
        [
            'url' => '../../public/admin/loja_compra.php',
            'label' => 'Encomendas'
        ],
        [
            'url' => null,
            'label' => 'Editar Encomenda'
        ],
    ],
    'ativar_menu' => [
        'item' => 'loja_online',
        'sub_item' => 'encomendas',
    ],
];

// Renderização
echo $twig->render('loja/loja_compra_edita.twig', [
    'site_url' => $site_url,
    'breadcrumbs' => $breadcrumbs,
    'titulo' => $breadcrumbs['itens'][1]['label'],
    'encomendas' => $encomendas,
    'encomendas_detalhe' => $encomendas_detalhe,
    'detalhe_status' => $detalhe_status,
    'success' => isset($_GET['success']) ? $_GET['success'] : null,
    'error' => isset($_GET['error']) ? $_GET['error'] : null,
    'user_details_nome' => $_SESSION['cliente_nome'] ?? null,
    'cliente_nome' => $_SESSION['cliente_nome'] ?? null,
    'cliente_id' => $_SESSION['cliente_id'] ?? null
]);
?>
