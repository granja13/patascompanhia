<?php 
ob_start(); // Limpa qualquer output anterior
header('Content-Type: application/json');

require '../../../app/vendor/autoload.php';
require '../../../src/Database.php';
require '../../../app/setting.php';

use Tracy\Debugger;

try {
    $db = Database::getConnection();
    
    $query = $db->query("SELECT DATE_FORMAT(data_venda, '%Y-%m') AS mes, SUM(quantidade * preco) AS total 
        FROM vendas GROUP BY mes ORDER BY mes DESC");
    $result = $query->fetchAll(PDO::FETCH_ASSOC);
    
    // Formatar os dados (adicionando coluna "acao")
    $data = [];
    foreach ($result as $row) {
        $data[] = [
            'mes' => $row['mes'],
            'total' => number_format($row['total'], 2, ',', '.') . " €",
            'acao' => "<a href='report_vendas_detalhe.php?mes={$row['mes']}'><button class='btn btn-primary btn-sm'><i class='fas fa-eye'></i></button></a>"
        ];
    }

    ob_clean(); // Limpa qualquer saída anterior

    echo json_encode(["data" => $data]);
} catch (PDOException $e) {
    ob_clean();
    echo json_encode(["error" => "Erro ao conectar ao banco de dados: " . $e->getMessage()]);
}

exit();
