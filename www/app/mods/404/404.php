<?php

require '../../vendor/autoload.php';

use Twig\Loader\FilesystemLoader;
use Twig\Environment;

// Configurar o Twig
$loader = new FilesystemLoader('../../../templates/');
$twig = new Environment($loader);

// Passar dados para o template Twig (como título ou mensagem de erro)
echo $twig->render('404.twig', ['error_message' => 'A página que você está procurando não foi encontrada.']);
