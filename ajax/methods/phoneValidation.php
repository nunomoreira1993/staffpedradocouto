<?php
$skip_session = true;
require_once $_SERVER['DOCUMENT_ROOT'] . "/lib/config.php";
require_once($_SERVER['DOCUMENT_ROOT'] . '/rp/eventos/evento.obj.php');
$dbevento = new evento($db, $_SESSION['id_rp']);

$_GET["hash"] = $_POST["hash"]?:"";
$_GET["evento"] = $_POST["evento"] ?: "";
$_GET["rp"] = $_POST["rp"] ?: "";
if (preg_match('/^[a-f0-9]{32}$/i', $_GET["hash"]) || (preg_match('/^[a-f0-9]{32}$/i', $_GET["evento"]) && preg_match('/^[a-f0-9]{32}$/i', $_GET["rp"]))) {
    if($_GET["hash"]) {
        $convite = $dbevento->devolveEventoByHash($_GET["hash"]);
        if ((int) $convite["id_evento"] > 0) {
            $evento = $dbevento->devolveEvento($convite["id_evento"]);
        }
    }
    else if ($_GET["rp"] && $_GET["evento"]) {
        $evento = $dbevento->devolveEventoByMD5($_GET["evento"]);
        $rp = $dbevento->devolveRPByMD5($_GET["rp"]);
    }
    if($evento) {
        if($_POST["phone"]) {
            if($dbevento->verificaConviteTelemovel($_POST["phone"], $evento["id"])){
                echo json_encode(array("status" => "error", "message" => "Já foi gerado um QR Code para este número."));
            }
            else {
                $convite = $dbevento->getInfoConviteTelemovel($_POST["phone"]);
                if($convite["data_nascimento"]) {
                    $data_nascimento = DateTime::createFromFormat('Y-m-d', $convite["data_nascimento"]);
                    $convite["data_nascimento"] = $data_nascimento->format('d-m-Y');
                }
                echo json_encode(array("status"=> "success", "convite"=> $convite));
            }
        }
        else {
            echo json_encode(array("status"=> "error",  "message"=> "Phone is required"));
        }
    }
    else {
        echo json_encode(array("status" => "error", "message" => "Event not found"));
    }
} else {
    echo json_encode(array("status" => "error", "message" => "Problem with URL"));
}
