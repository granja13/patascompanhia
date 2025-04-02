<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
require_once(__DIR__ . '../../src/Database.php');


function send_mail($to, $mensagem, $title, $file = false, $replyto = false) {

    require 'setting.php';

    if (empty($smtp_mail) || !filter_var($smtp_mail, FILTER_VALIDATE_EMAIL)) {
        return "Erro: Endereço de e-mail do remetente inválido!";
    }
    

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = $smtp_host;
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtp_user;
        $mail->Password   = $smtp_pass;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $smtp_port;

        $mail->setFrom($smtp_mail, $smtp_name);
        $mail->addAddress($to);
        if ($replyto && filter_var($replyto, FILTER_VALIDATE_EMAIL)) {
            $mail->addReplyTo($replyto);
        }

        if ($file && isset($file['tmp_name']) && !empty($file['tmp_name'])) {
            $mail->addAttachment($file['tmp_name'], $file['name']);
        }

        $mail->isHTML(true);
        $mail->Subject = $title;
        $mail->Body    = $mensagem;
        $mail->AltBody = strip_tags($mensagem);

        $mail->send();
        return true;
    } catch (Exception $e) {
        return "Erro ao enviar email: {$mail->ErrorInfo}";
    }
}

function error_404() {
    //header('HTTP/1.0 404 Not Found');
    header("Location: /teste/www/app/mods/404/404.php");
    exit();
    
}

function var_dump_pretty($_param){
    print("<pre>".print_r($_param,true)."</pre>");
}

function enviarErroParaTelegram($mensagem) {
   /* $token = "TOKEN_DO_BOT"; // Substitua pelo seu token
    $chat_id = "-GRUPO_CHAT_ID"; // ID do grupo (com "-" se for grupo)
    
    $url = "https://api.telegram.org/bot$token/sendMessage";
    
    $dados = [
        "chat_id" => $chat_id,
        "text" => "🚨 *Erro no Site!* 🚨\n\n$mensagem",
        "parse_mode" => "Markdown"
    ];

    file_get_contents($url . "?" . http_build_query($dados));*/
}

// Captura todos os erros e envia para o Telegram
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    $mensagem = "Erro: *$errstr*\nArquivo: `$errfile`\nLinha: `$errline`";
    enviarErroParaTelegram($mensagem);
});

// Função para transferir o carrinho da sessão para o banco de dados
function transferirCarrinhoParaUsuario($conexao, $cliente_id)
{
    $data_atual = date('Y-m-d H:i:s');
    $novo_total = 0;
    $novo_peso = 0;
    $id_cart = null;

    if (isset($_SESSION['carrinho']) && !empty($_SESSION['carrinho'])) {
        // Verificar se o carrinho já existe para o cliente
        $queryCarrinhoExistente = $conexao->prepare("SELECT id, peso, total FROM carrinho WHERE id_cliente = :id_cliente");
        $queryCarrinhoExistente->bindParam(':id_cliente', $cliente_id);
        $queryCarrinhoExistente->execute();
        $carrinhoExistente = $queryCarrinhoExistente->fetch(PDO::FETCH_ASSOC);

        if ($carrinhoExistente) {
            $id_cart = $carrinhoExistente['id'];

            // Atualizar data_update do carrinho
            $updateCarrinho = $conexao->prepare("UPDATE carrinho SET data_update = :data_update WHERE id = :id_cart");
            $updateCarrinho->bindParam(':data_update', $data_atual);
            $updateCarrinho->bindParam(':id_cart', $id_cart);
            $updateCarrinho->execute();

            // Inicializar o total e peso do carrinho a partir do existente
            $novo_total = $carrinhoExistente['total']; 
            $novo_peso = $carrinhoExistente['peso'];
        } else {
            // Inserir novo carrinho
            $carrinho = $conexao->prepare("INSERT INTO carrinho (id_cliente, ip, data_criado, data_update, peso, total) VALUES (:id_cliente, :ip, :data_criado, :data_update, :peso, :total)");
            $carrinho->bindParam(':id_cliente', $cliente_id);
            $ip = $_SERVER['REMOTE_ADDR'];
            $carrinho->bindParam(':ip', $ip);
            $carrinho->bindParam(':data_criado', $data_atual);
            $carrinho->bindParam(':data_update', $data_atual);
            $peso = 0; 
            $carrinho->bindParam(':peso', $peso);
            $carrinho->bindParam(':total', $novo_total);

            if ($carrinho->execute()) {
                $id_cart = $conexao->lastInsertId();
            } else {
                throw new Exception("Erro ao inserir o carrinho.");
            }
        }

        foreach ($_SESSION['carrinho'] as $item) {
            // Verificar se o item já existe no banco
            $sql_verifica_item = $conexao->prepare("SELECT * FROM carrinho_items WHERE id_cliente = :id_cliente AND id_prod = :id_prod");
            $sql_verifica_item->bindParam(':id_cliente', $cliente_id, PDO::PARAM_INT);
            $sql_verifica_item->bindParam(':id_prod', $item['referencia'], PDO::PARAM_STR);
            $sql_verifica_item->execute();

            if ($sql_verifica_item->rowCount() > 0) {
                // Atualizar item no banco
                $sql_update = $conexao->prepare("UPDATE carrinho_items SET quantidade = quantidade + :quantidade, total = total + :total WHERE id_cliente = :id_cliente AND id_prod = :id_prod");
                $sql_update->bindParam(':quantidade', $item['quantidade'], PDO::PARAM_INT);
                $sql_update->bindParam(':total', $item['total'], PDO::PARAM_STR);
                $sql_update->bindParam(':id_cliente', $cliente_id, PDO::PARAM_INT);
                $sql_update->bindParam(':id_prod', $item['referencia'], PDO::PARAM_STR);
                $sql_update->execute();
            } else {
                // Inserir novo item no banco
                $sql_insert = $conexao->prepare("INSERT INTO carrinho_items (id_cliente, id_prod, preco, peso, quantidade, total, id_cart) VALUES (:id_cliente, :id_prod, :preco, :peso, :quantidade, :total, :id_cart)");
                $sql_insert->bindParam(':id_cliente', $cliente_id, PDO::PARAM_INT);
                $sql_insert->bindParam(':id_prod', $item['referencia'], PDO::PARAM_STR);
                $sql_insert->bindParam(':preco', $item['preco'], PDO::PARAM_STR);
                $sql_insert->bindParam(':peso', $item['peso'], PDO::PARAM_STR);
                $sql_insert->bindParam(':quantidade', $item['quantidade'], PDO::PARAM_INT);
                $sql_insert->bindParam(':total', $item['total'], PDO::PARAM_STR);
                $sql_insert->bindParam(':id_cart', $id_cart);
                $sql_insert->execute();
            }

            // Atualizar total e peso do carrinho
            $novo_total += $item['total'];
            $novo_peso += $item['peso'] * $item['quantidade'];
        }

        // Atualizar o carrinho no banco
        if ($id_cart) {
            $updateTotalCarrinho = $conexao->prepare("UPDATE carrinho SET peso = :peso, total = :total WHERE id = :id_cart");
            $updateTotalCarrinho->bindParam(':peso', $novo_peso);
            $updateTotalCarrinho->bindParam(':total', $novo_total);
            $updateTotalCarrinho->bindParam(':id_cart', $id_cart);
            $updateTotalCarrinho->execute();
        }

        unset($_SESSION['carrinho']);
    }
}

?>
