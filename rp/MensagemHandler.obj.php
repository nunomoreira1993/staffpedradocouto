<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once $_SERVER['DOCUMENT_ROOT'] . '/plugins/phpmailer/src/Exception.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/plugins/phpmailer/src/PHPMailer.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/plugins/phpmailer/src/SMTP.php';

class MensagemHandler {
    private $whatsappBaseUrl;

    public function __construct() {
        $this->whatsappBaseUrl = "https://wa.me/";
    }

    public function enviarMensagem($metodo, $nome, $email, $telemovel, $mensagem) {
        switch ($metodo) {
            case 1:
                return $this->enviarSms($telemovel, $mensagem);
            case 2:
                return $this->enviarWhatsapp($telemovel, $mensagem);
            case 3:
                return $this->enviarEmail($nome, $email, $mensagem);
            default:
                return array("status" => "erro", "mensagem" => "Método inválido.");
        }
    }

    private function enviarSms($destino, $mensagem) {
        // Lógica para enviar SMS
        // Substitua esta lógica pela implementação real do envio de SMS
		$destino = 351 . $destino;
        $post['to'] = array($destino);
        $post['text'] = $mensagem;
        $post['from'] = "PEDRADCOUTO";
        $post['coding'] = "gsm-pt";
        $post['parts'] = 4;
        $user ="infopedradocout";
        $password = "NUgm17?%";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://dashboard.wausms.com/Api/rest/message");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Accept: application/json",
        "Authorization: Basic ".base64_encode($user.":".$password)));
        $result = curl_exec ($ch);
        curl_close($ch);


        if($result){
            $result = json_decode($result, true);
            if($result["error"]) {
                return array("status" => "erro", "mensagem" => $result["error"]["description"]);
            }
            return array("status" => "sucesso");
        } else {
            return array("status" => "erro", "mensagem" => "Erro ao enviar SMS, tente novamente com outro método de envio.");
        }
    }

    private function enviarEmail($nome, $destino, $mensagem) {


        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->SMTPDebug = 0;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = 'mail.staffpedradocouto.com';                     //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = 'noreply@staffpedradocouto.com';                     //SMTP username
            $mail->Password   = '12qwaszx.-,';                               //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom('noreply@staffpedradocouto.com', 'Pedra do Couto');
            $mail->addAddress($destino, $nome);     //Add a recipient


            //Content
            $mail->CharSet = "UTF-8";
            $mail->Encoding = 'base64';
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = 'Convite Guest List - Pedra do Couto';
            $mail->Body    =  nl2br($mensagem);
            $mail->AltBody = nl2br($mensagem);

            $mail->send();
            $envioEmail = true;
        } catch (Exception $e) {
            array("status" => "erro", "mensagem" =>  "Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }

        if ($envioEmail) {
            return array("status" => "sucesso", "mensagem" => "E-mail enviado para $destino.");
        } else {
            return array("status" => "erro", "mensagem" => "Erro ao enviar e-mail para $destino.");
        }
    }

    private function enviarWhatsapp($destino, $mensagem) {
        $url = $this->whatsappBaseUrl . $destino . "?text=" . urlencode($mensagem);

        if ($url) {
            return array("status" => "sucesso", "mensagem" => "A redirecionar para o whatsapp", "url"=> $url);
        } else {
            return array("status" => "erro", "mensagem" => "Erro ao enviar mensagem via WhatsApp para $destino.");
        }
    }
}