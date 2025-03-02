<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/config.php";

if (empty($_SESSION['id_rp'])) {
	header('Location:/index.php');
	exit;
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/rp/rp.obj.php');
$dbrp = new rp($db, $_SESSION['id_rp']);
$entrada = $dbrp->devolveEntrada();
if ($entrada && $entrada["numero_cartao"]) {
?>

	<a href="#" class="back">
		<img src="/temas/rps/imagens/back.svg">
	</a>

	<div class="barcode">
		<?php
		if($entrada["pago"]) {
			?>
			<span class="pago">PAGO</span>
			<?php
		}
		?>
		<img src="/rp/barcode.php?f=svg&s=code-39&d=<?php echo $entrada["numero_cartao"]; ?>" />
	</div>
<?php
}
?>