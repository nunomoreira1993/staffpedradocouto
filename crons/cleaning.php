<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/config.php";


#eventos convites
$query = "SELECT eventos_convites.* FROM eventos_convites LEFT JOIN eventos ON eventos.id = eventos_convites.id_evento WHERE eventos.data <= '". date ("Y-m-d H:i:s", strtotime("-1 month"))."' AND eventos_convites.marketing = 1 ORDER BY eventos_convites.id ASC";
$result = $db->query($query);

if($result) {
	foreach($result as $rw) {
		$cliente["id_rp"] = $rw["id_rp"];
		$cliente["nome"] = $rw["nome"];
		$cliente["data_nascimento"] = $rw["data_nascimento"];
		$cliente["telemovel"] = $rw["telemovel"];
		$cliente["email"] = $rw["email"];
		$cliente["estado"] = $rw["estado"];
		$cliente["termos_condicoes"] = $rw["termos_condicoes"];
		$cliente["marketing"] = $rw["marketing"];
		$cliente["qrcode_data"] = $rw["qrcode_data"];
		$cliente["qrcode_ip"] = $rw["qrcode_ip"];
		$cliente["qrcode_user_agent"] = $rw["qrcode_user_agent"];

		$query = "SELECT * FROM clientes WHERE telemovel = '" . $cliente["telemovel"] . "'";
		$result = $db->query($query);

		if($result[0]["id"]) {
			$db->update("clientes", $cliente, "id = " . $result[0]["id"] );
		}
		else {
			$db->insert("clientes", $cliente);
		}
	}
}

$query = "DELETE eventos_convites FROM eventos_convites LEFT JOIN eventos ON eventos.id = eventos_convites.id_evento WHERE eventos.data <= '". date ("Y-m-d H:i:s", strtotime("-2 month"))."' OR eventos.id IS NULL ";
$result = $db->query($query);

$query = "OPTIMIZE TABLE eventos_convites; ";
$result = $db->query($query);

#delete logs
$query = "DELETE FROM logs  WHERE logs.data <= '". date ("Y-m-d H:i:s", strtotime("-2 months"))."'";
$result = $db->query($query);

$query = "OPTIMIZE TABLE logs ";
$result = $db->query($query);

#presencas
$query = "DELETE FROM presencas WHERE presencas.data_entrada <= '". date ("Y-m-d H:i:s", strtotime("-2 months"))."'";
$result = $db->query($query);

$query = "OPTIMIZE TABLE presencas ";
$result = $db->query($query);


#delete logs rps
$query = "DELETE FROM logs_rp  WHERE logs_rp.data <= '". date ("Y-m-d H:i:s", strtotime("-2 months"))."'";
$result = $db->query($query);

$query = "OPTIMIZE TABLE logs_rp";
$result = $db->query($query);
