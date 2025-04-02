<?php 

header('Content-Type: application/json');

require '../../../app/vendor/autoload.php';
require '../../../src/Database.php';
require '../../../app/setting.php';

use Tracy\Debugger;

try {
    $db = Database::getConnection();
    
    $query = $db->query('SELECT c.*, cu.nome, cu.last_login FROM `carrinho` c
LEFT JOIN clientes_utilizadores cu ON cu.id_cliente = c.id_cliente;');
    $result = $query->fetchAll(PDO::FETCH_ASSOC);
    
    ob_clean();

    echo json_encode(["data" => $result]);
} catch (PDOException $e) {
    echo json_encode(["error" => "Erro ao conectar ao banco de dados: " . $e->getMessage()]);
}

exit();
