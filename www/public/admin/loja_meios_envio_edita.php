<?php
require '../../app/vendor/autoload.php';
require '../../src/Database.php';
$twig = require '../../app/site_init.php';

session_start();

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

$loader = new FilesystemLoader('../../templates/admin/');
$twig = new Environment($loader);

$db = Database::getConnection();

$id = isset($_GET['id']) ? $_GET['id'] : 0;

$metodos_pagamento = [];
if (!empty($id)) {
    $sql_metodos_envio = $db->prepare("SELECT * FROM metodos_envio WHERE id = :id");
    $sql_metodos_envio->bindValue(':id', $id, PDO::PARAM_STR);
    $sql_metodos_envio->execute();
    $metodos_envio = $sql_metodos_envio->fetch(PDO::FETCH_ASSOC);

}

if ($_POST) {
    $id = isset($_POST['id']) ? $_POST['id'] : 0; 
    $nome = $_POST['nome'] ?? '';
    $logotipo = $metodos_pagamento['logotipo'] ?? null;

    if (isset($_FILES['logotipo']) && $_FILES['logotipo']['error'] == 0) {
        $uploadDir = '../../public/assets/imagens/icones/';
        $fileName = basename($_FILES['logotipo']['name']);
        $filePath = $uploadDir . $fileName;

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (move_uploaded_file($_FILES['logotipo']['tmp_name'], $filePath)) {
            $logotipo = $fileName;
        } else {
            die("Erro ao fazer upload do arquivo.");
        }
    }

    if ($id == 0) {
        $stmt = $db->prepare("INSERT INTO metodos_envio (nome, logotipo) VALUES (:nome, :logotipo)");
        $stmt->bindValue(':nome', $nome);
        $stmt->bindValue(':logotipo', $logotipo);
        $stmt->execute();
        header("Location: ../../public/admin/loja_meios_envio.php?success=1");
        exit();
    } else {
        $query = "UPDATE metodos_envio SET nome = :nome";
        if ($logotipo) {
            $query .= ", logotipo = :logotipo";
        }
        $query .= " WHERE id = :id";

        $stmt = $db->prepare($query);
        $stmt->bindValue(':nome', $nome);
        if ($logotipo) {
            $stmt->bindValue(':logotipo', $logotipo);
        }
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        header("Location: ../../public/admin/loja_meios_envio.php?updated=1");
        exit();
    }
}

$breadcrumbs = [
    'itens' => [
        [
            'url' => '../../public/admin/loja_meios_envio.php',
            'label' => 'Métodos de Pagamento'
        ],
        [
            'url' => null,
            'label' => 'Editar Método Envio'
        ],
    ],
    'ativar_menu' => [
        'item' => 'loja_online',
        'sub_item' => 'meios_envio',
    ],
];

echo $twig->render('loja/loja_meios_envio_edita.twig', [
    'site_url' => $site_url,
    'breadcrumbs' => $breadcrumbs,
    'titulo' => $breadcrumbs['itens'][1]['label'],
    'metodos_envio' => $metodos_envio,
    'success' => $_GET['success'] ?? null,
    'error' => $_GET['error'] ?? null,
    'user_details_nome' => $_SESSION['cliente_nome'] ?? null,
    'cliente_nome' => $_SESSION['cliente_nome'] ?? null,
    'cliente_id' => $_SESSION['cliente_id'] ?? null
]);
?>