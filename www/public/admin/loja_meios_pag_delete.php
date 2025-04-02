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

$stmt = $db->prepare("DELETE FROM metodos_pagamento WHERE id = :id");
$stmt->bindParam(':id', $id);
$stmt->execute();


header("Location: ../../public/admin/loja_meios_pag.php?success=1");
exit();

?>