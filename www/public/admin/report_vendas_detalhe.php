<?php  
session_start();

require '../../app/vendor/autoload.php';
use Twig\Loader\FilesystemLoader;
use Twig\Environment;
require '../../src/Database.php';
require '../../app/setting.php';

$loader = new FilesystemLoader('../../templates/admin/');
$twig = new Environment($loader);

$mes = $_GET['mes'] ?? date('Y-m');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cliente = $_POST['cliente'];
    $produto = $_POST['produto'];
    $quantidade = $_POST['quantidade'];
    $preco = $_POST['preco'];
    $dia_atual = date('d'); 
    $data_venda = "$mes-$dia_atual";

    try {
        if (!empty($cliente) && !empty($produto) && !empty($quantidade) && !empty($preco)) {
            $db = Database::getConnection();
            
            $query = $db->prepare("INSERT INTO vendas (data_venda, cliente, produto, quantidade, preco) VALUES (:data_venda, :cliente, :produto, :quantidade, :preco)");
            $query->bindParam(':data_venda', $data_venda);
            $query->bindParam(':cliente', $cliente);
            $query->bindParam(':produto', $produto);
            $query->bindParam(':quantidade', $quantidade);
            $query->bindParam(':preco', $preco);
            
            $query->execute();

            header("Location: report_vendas_detalhe.php?mes=$mes&success=1");
            exit();
        } else {
            header("Location: report_vendas_detalhe.php?mes=$mes&error=1");
            exit();
        }
    } catch (PDOException $e) {
        header("Location: report_vendas_detalhe.php?mes=$mes&error=1");
        exit();
    }
}



$db = Database::getConnection();
$query = $db->prepare("SELECT * FROM vendas WHERE DATE_FORMAT(data_venda, '%Y-%m') = :mes ORDER BY data_venda");
$query->bindParam(':mes', $mes);
$query->execute();
$vendas = $query->fetchAll(PDO::FETCH_ASSOC);

$breadcrumbs = [
    'itens' => [
        [
            'url' => null,
            'label' => 'Report - Vendas Mês'
        ],
    ],
    'ativar_menu' => [
        'item' => 'reports',
        'sub_item' => 'report_order_mercado',
    ],
];

echo $twig->render('reports/report_vendas_detalhe.twig', [
    'breadcrumbs' => $breadcrumbs,
    'site_env' => $site_env,
    'titulo' => $breadcrumbs['itens'][0]['label'],
    'user_details_nome' => $_SESSION['cliente_nome'] ?? '',
    'vendas' => $vendas,
    'mes' => $mes,
    'success' => isset($_GET['success']) ? $_GET['success'] : null,
    'error' => isset($_GET['error']) ? $_GET['error'] : null
]);
