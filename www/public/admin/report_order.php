<?php
session_start();

require '../../app/vendor/autoload.php';
require '../../src/Database.php';
require '../../app/setting.php';

$db = Database::getConnection();

$date_start = !empty($_GET['date_start']) ? date('Y-m-d', strtotime($_GET['date_start'])): date('Y-m-d');
$date_end = !empty($_GET['date_end']) ? date('Y-m-d', strtotime($_GET['date_end'])): date('Y-m-d');

$start_date = date("Y-m-d 00:00:00", strtotime($date_start));
$end_date = date("Y-m-d 23:59:59", strtotime($date_end));

$value_date_start = htmlspecialchars($date_start);
$value_date_end = htmlspecialchars($date_end);

$sql_total_encomenda = $db->prepare("SELECT FORMAT(SUM(total), 2) AS total_geral, COUNT(*) AS total_encomendas FROM encomendas WHERE data BETWEEN ? AND ?");
$sql_total_encomenda->execute([$start_date, $end_date]);
$total_encomenda = $sql_total_encomenda->fetch(PDO::FETCH_ASSOC);

$sql_total_pagas = $db->prepare("SELECT FORMAT(SUM(total), 2) AS total_geral, COUNT(*) AS total_encomendas, envio_pagamento FROM encomendas WHERE pago = 1 AND data BETWEEN ? AND ?");
$sql_total_pagas->execute([$start_date, $end_date]);
$total_encomenda_paga = $sql_total_pagas->fetch(PDO::FETCH_ASSOC);

$sql_total_nao_pagas = $db->prepare("SELECT FORMAT(SUM(total), 2) AS total_geral, COUNT(*) AS total_encomendas, envio_pagamento FROM encomendas WHERE pago = 0 AND data BETWEEN ? AND ?");
$sql_total_nao_pagas->execute([$start_date, $end_date]);
$total_encomenda_nao_paga = $sql_total_nao_pagas->fetch(PDO::FETCH_ASSOC);

$sql_total_por_tipo = $db->prepare("SELECT envio_pagamento, FORMAT(SUM(total), 2) AS total_geral, COUNT(*) AS total_encomendas FROM encomendas WHERE pago = 1 AND data BETWEEN ? AND ? GROUP BY envio_pagamento");
$sql_total_por_tipo->execute([$start_date, $end_date]);
$total_encomenda_tipo_pago = $sql_total_por_tipo->fetchAll(PDO::FETCH_ASSOC);

$sql_total_por_tipo_nao_pago = $db->prepare("SELECT envio_pagamento, FORMAT(SUM(total), 2) AS total_geral, COUNT(*) AS total_encomendas FROM encomendas WHERE pago = 0 AND data BETWEEN ? AND ? GROUP BY envio_pagamento");
$sql_total_por_tipo_nao_pago->execute([$start_date, $end_date]);
$total_encomenda_tipo_nao_paga = $sql_total_por_tipo_nao_pago->fetchAll(PDO::FETCH_ASSOC);

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

$loader = new FilesystemLoader('../../templates/admin/');
$twig = new Environment($loader);

$breadcrumbs = [
    'itens' => [
        [
            'url' => null,
            'label' => 'Relatório de Encomendas'
        ],
    ],
    'ativar_menu' => [
        'item' => 'reports',
        'sub_item' => 'report_order',
    ],
];

echo $twig->render('reports/report_order.twig', [
    'breadcrumbs' => $breadcrumbs,
    'site_env' => $site_env,
    'titulo' => $breadcrumbs['itens'][0]['label'],
    'user_details_nome' => $_SESSION['cliente_nome'] ?? '',
    'success' => $_GET['success'] ?? null,
    'error' => $_GET['error'] ?? null,
    'value_date_start' => $value_date_start,
    'value_date_end' => $value_date_end,
    'total_encomenda' => $total_encomenda,
    'total_encomenda_paga' => $total_encomenda_paga,
    'total_encomenda_nao_paga' => $total_encomenda_nao_paga,
    'total_encomenda_tipo_pago' => $total_encomenda_tipo_pago,
    'total_encomenda_tipo_nao_paga' => $total_encomenda_tipo_nao_paga
]);
?>
