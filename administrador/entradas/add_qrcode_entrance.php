<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/config.php";
$qrcode = $_REQUEST['qrcode'];
require_once($_SERVER['DOCUMENT_ROOT'] . '/administrador/eventos/evento.obj.php');
$dbevento = new evento($db);

if ($qrcode) {

    $pos = strpos($qrcode, "cartao_1_" );

    if ($pos !== false) {
        $id = str_replace("cartao_1_","", $qrcode);
        if($id) {
            $cartao = $dbevento->getSemConsumoByID($id);
            if($cartao) {
                $db->update('rps_cartoes_sem_consumo', array("entrou" => 1), array("id"=> $cartao["id"]));
                $db->Insert('logs', array('descricao' => "Entrada de cartão sem consumo com <b> " . $cartao["bebidas"] . " </b> via qrcode", 'arr' => json_encode($cartao), 'id_admin' => $_SESSION['id_utilizador'], 'tipo' => "Inserção", 'user_agent' => $_SERVER['HTTP_USER_AGENT'], 'ip' => $_SERVER['REMOTE_ADDR']));
                echo json_encode(array('status' => "success", "client_name" => $cartao["nome"], "type" => "Cartão Sem Consumo"));
                exit;
            }
        }
    } else {
        $pos = strpos($qrcode, "cartao_2_" );
        if ($pos !== false) {
            $id = str_replace("cartao_2_","", $qrcode);
            $cartao = $dbevento->getConsumoObrigatorioByID($id);
            if($cartao) {
                $db->update('rps_cartoes_consumo_obrigatorio', array("entrou" => 1), array("id"=> $cartao["id"]));
                $db->Insert('logs', array('descricao' => "Entrada de cartão  consumo obrigatório via qrcode", 'arr' => json_encode($cartao), 'id_admin' => $_SESSION['id_utilizador'], 'tipo' => "Inserção", 'user_agent' => $_SERVER['HTTP_USER_AGENT'], 'ip' => $_SERVER['REMOTE_ADDR']));
                echo json_encode(array('status' => "success", "client_name" => $cartao["nome"], "type" => "Cartão Consumo Obrigatório"));
                exit;
            }
        }
    }

    if (!preg_match('/^[0-9]+$/', $qrcode)) {
        $arr["status"] = "error";
        $arr["message"] = "QR Code inválido.";
        echo json_encode($arr);
        exit;
    }

    if (date('H') < 14) {
        $data = date('Y-m-d', strtotime('-1 day'));
    } else {
        $data = date('Y-m-d');
    }

    if (empty($_SESSION['id_utilizador'])) {
        header('Location:/index.php');
        exit;
    }

    $convite = $dbevento->getConviteByQRCode($qrcode);
    $evento = $dbevento->devolveEvento($convite["id_evento"]);

    if($evento["data"] != $data) {
        $arr["status"] = "error";
        $arr["message"] = "O QR Code não é para a data do evento de hoje mas para a data de " . date('d-m-Y', strtotime($evento["data"])) . ".";
        echo json_encode($arr);
        exit;
    }

    if($convite["estado"] == 2) {
        $arr["status"] = "error";
        $arr["message"] = "Este QR Code já deu entrada <br/> na data de " . date('d-m-Y H:i:s', strtotime($convite["qrcode_entrada_data"])) . ".";
        echo json_encode($arr);
        exit;
    }

    $quantidade = 1;
    $id_rp = intval($convite["id_rp"]);
    if ($id_rp > 0 && $quantidade > 0) {
        $campos['data'] = date('Y-m-d H:i:s');
        $campos['data_evento'] = $data;
        $campos['id_rp'] = $id_rp;
        $campos['id_ticket'] = $convite["id"];
        $campos['quantidade'] = $quantidade;
        $campos['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        $campos['ip'] = $_SERVER['REMOTE_ADDR'];
        $id = $db->Insert('rps_entradas', $campos);
        if ($id > 0) {
            $db->update('eventos_convites', array("estado" => 2, "qrcode_entrada" => 1, "qrcode_entrada_data" => date("Y-m-d H:i:s")), array("id"=> $convite["id"]));
            $db->Insert('logs', array('descricao' => "Inseriu uma entrada via qrcode", 'arr' => json_encode($campos), 'id_admin' => $_SESSION['id_utilizador'], 'tipo' => "Inserção", 'user_agent' => $_SERVER['HTTP_USER_AGENT'], 'ip' => $_SERVER['REMOTE_ADDR']));
            echo json_encode(array('status' => "success", "client_name" => $convite["nome"], "type" => "Bilhete"));
            exit;
        }
    } else {
        echo json_encode(array('status' => "error", "message" => "Ocorreu um problema a inserir a entrada (RP não encontrado)."));
        exit;
    }
}
else {

    echo json_encode(array('status' => "error", "message" => "Tentar novamente."));
    exit;
}
