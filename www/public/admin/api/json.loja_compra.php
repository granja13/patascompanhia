<?php 

header('Content-Type: application/json');

require '../../../app/vendor/autoload.php';
require '../../../src/Database.php';
require '../../../app/setting.php';

use Tracy\Debugger;

try {
    $db = Database::getConnection();
    
    $query = $db->query('SELECT e.*, c.nome, se.nome AS status_encomenda FROM `encomendas` e LEFT JOIN clientes c ON c.id = e.id_cliente LEFT JOIN status_encomenda se ON se.id_status = e.status');
    $result = $query->fetchAll(PDO::FETCH_ASSOC);
    
    ob_clean();

    echo json_encode(["data" => $result]);
} catch (PDOException $e) {
    echo json_encode(["error" => "Erro ao conectar ao banco de dados: " . $e->getMessage()]);
}

exit();
