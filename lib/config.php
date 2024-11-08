<?php
if(session_id() == '') {
	session_start();
}
header("Access-Control-Allow-Origin: https://staffpedra.local:8001");
header("Access-Control-Allow-Origin: https://guest.pedradocouto.net");
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING ^E_DEPRECATED);
date_default_timezone_set( "Europe/Lisbon");

# MySQL Config #1
// $cfg_mysql[1]['db'] = 'staffpe1_guest';
// $cfg_mysql[1]['user'] = 'staffpe1_nuno';
// $cfg_mysql[1]['pass'] = '12qwaszx.-,';
// $cfg_mysql[1]['server'] = 'localhost';

# MySQL Config #1
$cfg_mysql[1]['db'] = 'personal_pedra';
$cfg_mysql[1]['user'] = 'root';
$cfg_mysql[1]['pass'] = 'p1wm6u';
$cfg_mysql[1]['server'] = 'localhost';



require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/db.obj.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/lib/funcoes.obj.php");
$db = new db();
$config = new config(1, $db, 'startup');


define('PEAR_PATH', $_SERVER['DOCUMENT_ROOT']. "/administrador/plugins/pear/");
set_include_path(get_include_path() . PATH_SEPARATOR . PEAR_PATH);

############################### Classe Config
#
class config {
	function __construct($i = 1, $db = 0, $mode = '') {
		global $cfg_mysql;
		$this->dbname = $cfg_mysql[$i]['db'];
		$this->login = $cfg_mysql[$i]['user'];
		$this->senha = $cfg_mysql[$i]['pass'];
		$this->odbc = "";
		$this->driver = "";
		$this->servidor = $cfg_mysql[$i]['server'];
		if ($mode == 'startup') {
			$this->db = $db;
			$this->db->open($this->dbname, $this->login, $this->senha, $this->odbc, $this->driver, $this->servidor);
		}
	}

}
?>