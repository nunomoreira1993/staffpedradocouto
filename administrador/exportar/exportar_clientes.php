<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/config.php";
if (empty($_SESSION['id_utilizador'])) {
    header('Location:/index.php');
    exit;
}

define('PEAR_PATH', $_SERVER['DOCUMENT_ROOT'].'/administrador/plugins/pear');
set_include_path($_SERVER['DOCUMENT_ROOT'].'/administrador/plugins/pear');

require_once PEAR_PATH . "/Spreadsheet/Excel/Writer.php";

$res = $db->query("SELECT nome, data_nascimento, telemovel, email, marketing FROM eventos_convites WHERE qrcode > 0");

$array[] = array("Nome", "Data de Nascimento", "Telémovel", "E-mail", "Aceitou termos de marketing?");

foreach ($res as $cliente) {

    $array[] = array($cliente['nome'], $cliente["data_nascimento"], $cliente["telemovel"], $cliente["email"], ($cliente["marketing"] == 1 ? "Sim" : "Não"));
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