<?php
require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__, null, false);
$dotenv->load();



/*$smtp_user = getenv('SMTP_USER');
$smtp_pass = getenv('SMTP_PASS');
$smtp_mail = getenv('SMTP_MAIL');
$smtp_name = getenv('SMTP_NAME');
$smtp_host = getenv('SMTP_HOST');
$smtp_port = getenv('SMTP_PORT');*/

$smtp_user = $_SERVER['SMTP_USER'];
$smtp_pass = $_SERVER['SMTP_PASS'];
$smtp_mail = $_SERVER['SMTP_MAIL'];
$smtp_name = $_SERVER['SMTP_NAME'];
$smtp_host = $_SERVER['SMTP_HOST'];
$smtp_port = $_SERVER['SMTP_PORT'];

$site_env = $_SERVER['SITE_ENV'];

/*$smtp_user = "rafaelgranja99@gmail.com";
$smtp_pass = "rwll hxpr fdyj cblu";
$smtp_mail = "rafaelgranja99@gmail.com";
$smtp_name = "Patas & Companhia";
$smtp_host = "smtp.gmail.com";
$smtp_port = "587";*/

if (!$smtp_mail) {
    die("Erro crítico: Variáveis de ambiente não carregadas corretamente!");
}


$site_url = $_SERVER['SITE_URL'];

?>
