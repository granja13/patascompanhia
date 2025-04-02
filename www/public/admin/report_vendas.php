<?php  
session_start();

require '../../app/vendor/autoload.php';
require '../../src/Database.php';
require '../../app/setting.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    try {
        $db = Database::getConnection();
        $mes = $_POST['novo_mes'] ?? '';

        if ($mes != '') {
            $data = $mes . "-01"; // YYYY-MM-01

            $stmt = $db->prepare("INSERT INTO vendas (data_venda, cliente, produto, quantidade, preco) VALUES (?, '---', '---', 0, 0)");
            $stmt->execute([$data]);

            echo json_encode([
                'status' => 'success',
                'message' => 'Mês adicionado com sucesso!',
                'redirect' => 'true'
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Mês inválido!']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Erro ao adicionar mês: ' . $e->getMessage()]);
    }
    exit;
}

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

$loader = new FilesystemLoader('../../templates/admin/');
$twig = new Environment($loader);

$breadcrumbs = [
    'itens' => [
        [
            'url' => null,
            'label' => 'Report - Vendas Mês'
        ],
    ],
    'ativar_menu' => [
        'item' => 'reports',
        'sub_item' => 'report_vendas',
    ],
];

echo $twig->render('reports/report_vendas.twig', [
    'breadcrumbs' => $breadcrumbs,
    'site_env' => $site_env,
    'titulo' => $breadcrumbs['itens'][0]['label'],
    'user_details_nome' => $_SESSION['cliente_nome'] ?? '',
    'success' => isset($_GET['success']) ? $_GET['success'] : null,
    'error' => isset($_GET['error']) ? $_GET['error'] : null
]);
?>
