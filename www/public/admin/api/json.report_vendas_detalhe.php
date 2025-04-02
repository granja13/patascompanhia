<?php 
header('Content-Type: application/json; charset=UTF-8'); 

require '../../../app/vendor/autoload.php';
require '../../../src/Database.php';
require '../../../app/setting.php';

use Tracy\Debugger;

try {
    if (empty($_GET['mes'])) {
        echo json_encode(["error" => "Parâmetro 'mes' não fornecido. Use ?mes=YYYY-MM"]);
        exit();
    }
    
    $mes = $_GET['mes'];

    
    $db = Database::getConnection();
    
    $query = $db->prepare("SELECT * FROM vendas WHERE DATE_FORMAT(data_venda, '%Y-%m') = :mes ORDER BY data_venda");
    $query->bindParam(':mes', $mes);
    $query->execute();
    
    $result = $query->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["data" => $result]);

} catch (PDOException $e) {
    echo json_encode(["error" => "Erro ao conectar ao banco de dados: " . $e->getMessage()]);
}

exit();
