<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/config.php";
if (empty($_SESSION['id_utilizador'])) {
    header('Location:/index.php');
    exit;
}

define('PEAR_PATH', $_SERVER['DOCUMENT_ROOT'].'/administrador/plugins/pear');
set_include_path($_SERVER['DOCUMENT_ROOT'].'/administrador/plugins/pear');

require_once PEAR_PATH . "/Spreadsheet/Excel/Writer.php";

$id = intval($_GET["id"]);
$res = $db->query("SELECT
                        rps.nome as rp_nome ,
                        eventos_convites.convite_nome ,
                        eventos_convites.convite_email,
                        eventos_convites.convite_telemovel,
                        eventos_convites.convite_data,
                        eventos_convites.convite_status,
                        eventos_convites.nome,
                        eventos_convites.data_nascimento,
                        eventos_convites.telemovel,
                        eventos_convites.email,
                        eventos_convites.termos_condicoes,
                        eventos_convites.marketing,
                        eventos_convites.qrcode,
                        eventos_convites.qrcode_data,
                        eventos_convites.qrcode_entrada,
                        eventos_convites.qrcode_entrada_data
                    FROM
                            eventos_convites
                        LEFT JOIN
                            rps
                        ON
                            rps.id = eventos_convites.id_rp
                    WHERE
                        eventos_convites.id_evento = " . $id
                    );

$array[] = array("RP Nome", "Convite - Nome", "Convite - E-mail", "Convite - Telémovel", "Convite - Data", "Convite - Estado", "Ticket - Nome", "Ticket - Data de Nascimento", "Ticket - Telémovel", "Ticket - E-mail", "Ticket - Termos e Condições", "Ticket - Marketing", "Gerou QR Code?", "Data QR Code", "QR Code deu entrada?", "QR Code Data de Entrada");

foreach ($res as $cliente) {
    $array[] = array(
        $cliente['rp_nome'],
        $cliente['convite_nome'],
        $cliente['convite_email'],
        $cliente['convite_telemovel'],
        $cliente['convite_data'],
        $cliente['convite_status'],
        $cliente['nome'],
        $cliente["data_nascimento"],
        $cliente["telemovel"],
        $cliente["email"],
        ($cliente["termos_condicoes"] == 1 ? "Sim" : "Não"),
        ($cliente["marketing"] == 1 ? "Sim" : "Não"),
        ($cliente["qrcode"] > 0 ? "Sim" : "Não"),
        $cliente["qrcode_data"],
        ($cliente["qrcode_entrada"] > 0 ? "Sim" : "Não"),
        $cliente["qrcode_entrada_data"]
    );
}

$nome_ficheiro =  "eventos_clientes";
$workbook = new Spreadsheet_Excel_Writer();
$workbook->setVersion(8);
$ws1 = &$workbook->addWorksheet(forceFilename(strtolower( $nome_ficheiro)));
$ws1->setInputEncoding('UTF-8');
$ws1->setRow(0, 0);

foreach($array as $linha => $data){
    foreach($data as $conta => $celula){
        $ws1->write($linha, $conta, $celula);
    }
}

$workbook->send(strtolower( $nome_ficheiro). ".xls");
$workbook->close();

exit();