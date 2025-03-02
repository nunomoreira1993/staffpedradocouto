<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/config.php";
require_once($_SERVER['DOCUMENT_ROOT'] . '/administrador/rps/rps.obj.php');
$dbrps = new rps($db);

if (empty($_SESSION['id_utilizador'])) {
    header('Location:/index.php');
    exit;
}
$id_rp = intval($_GET['id_rp']);


if ($id_rp > 0  && $dbrps->verificaPresencaRP( $id_rp) == 1) {

    if (date('H') < 14) {
        $data = date('Y-m-d', strtotime('-1 day'));
    } else {
        $data = date('Y-m-d');
    }

    $campos['pago'] = 1;

    $id = $db->update('presencas', $campos, "id_rp = " . (int) $id_rp . " AND data_evento = '" . $data . "'");

	$db->Insert('logs', array('descricao' => "Pagou cartão do RP ".$campos['id_rp'], 'arr' => json_encode($campos), 'id_admin' => $_SESSION['id_utilizador'], 'tipo' => "Update", 'user_agent' => $_SERVER['HTTP_USER_AGENT'], 'ip' => $_SERVER['REMOTE_ADDR']));
	echo json_encode(array('sucesso' => 1,'erro' => 0));
}
else{
    echo json_encode(array('erro' => 1, 'sucesso' => 0));
}
