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
    $sql_metodos_pagamento = $db->prepare("SELECT * FROM metodos_pagamento WHERE id = :id");
    $sql_metodos_pagamento->bindValue(':id', $id, PDO::PARAM_STR);
    $sql_metodos_pagamento->execute();
    $metodos_pagamento = $sql_metodos_pagamento->fetch(PDO::FETCH_ASSOC);

}

if ($_POST) {
    $nome = $_POST['nome'] ?? '';
    $modulo = $_POST['modulo'] ?? '';
    $logotipo = $metodos_pagamento['logotipo'] ?? null;

    if (isset($_FILES['logotipo']) && $_FILES['logotipo']['error'] == 0) {
        $uploadDir = '../../public/assets/imagens/pagamentos/';
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
        $stmt = $db->prepare("INSERT INTO metodos_pagamento (nome, modulo, logotipo) VALUES (:nome, :modulo, :logotipo)");
        $stmt->bindValue(':nome', $nome);
        $stmt->bindValue(':modulo', $modulo);
        $stmt->bindValue(':logotipo', $logotipo);
        $stmt->execute();
        header("Location: ../../public/admin/loja_meios_pag.php?success=1");
        exit();
    } else {
        $query = "UPDATE metodos_pagamento SET nome = :nome, modulo = :modulo";
        if ($logotipo) {
            $query .= ", logotipo = :logotipo";
        }
        $query .= " WHERE id = :id";

        $stmt = $db->prepare($query);
        $stmt->bindValue(':nome', $nome);
        $stmt->bindValue(':modulo', $modulo);
        if ($logotipo) {
            $stmt->bindValue(':logotipo', $logotipo);
        }
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        header("Location: ../../public/admin/loja_meios_pag.php?updated=1");
        exit();
    }
}

$breadcrumbs = [
    'itens' => [
        [
            'url' => '../../public/admin/loja_meios_pag.php',
            'label' => 'Métodos de Pagamento'
        ],
        [
            'url' => null,
            'label' => 'Editar Método Pagamento'
        ],
    ],
    'ativar_menu' => [
        'item' => 'loja_online',
        'sub_item' => 'meios_pag',
    ],
];

echo $twig->render('loja/loja_meios_pag_edita.twig', [
    'site_url' => $site_url,
    'breadcrumbs' => $breadcrumbs,
    'titulo' => $breadcrumbs['itens'][1]['label'],
    'metodos_pagamento' => $metodos_pagamento,
    'success' => $_GET['success'] ?? null,
    'error' => $_GET['error'] ?? null,
    'user_details_nome' => $_SESSION['cliente_nome'] ?? null,
    'cliente_nome' => $_SESSION['cliente_nome'] ?? null,
    'cliente_id' => $_SESSION['cliente_id'] ?? null
]);
?>