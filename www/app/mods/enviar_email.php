<?php
require '../site_functions.php';

$destinatario = 'patasecompanhia23@gmail.com';
$assunto = 'Teste de E-mail com PHPMailer';
$mensagem = '<h1>Olá!</h1><p>Este é um teste de envio de e-mail com PHPMailer.</p>';

$resultado = send_mail($destinatario, $mensagem, $assunto);

if ($resultado === true) {
    echo 'Email enviado com sucesso!';
} else {
    echo $resultado;
}
?>
