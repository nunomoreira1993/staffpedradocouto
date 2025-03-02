<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/config.php";
if (empty($_SESSION['id_utilizador'])) {
	header('Location:/index.php');
	exit;
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/administrador/rps/rps.obj.php');
$dbrps = new rps($db);
$letra = $_GET['letra'];
$cargos = $dbrps->listaCargosAtrasos();
$rps = $dbrps->listaRPs($letra, 1, false, $cargos);
foreach ($rps as $rp) {
?>
	<a href="javascript:;" data-id="<?php echo $rp['id']; ?>">
		<span class="bebidas">
			<span class="numero"><?php echo (int) $rp['bebidas_cartao']; ?></span>
			<span class="mini"><?php echo (int) $rp['bebidas_cartao'] == 1 ? "bebida" : "bebidas"; ?></span>
		</span>
		<?php
		if ($rp['foto'] && file_exists($_SERVER['DOCUMENT_ROOT'] . "/fotos/rps/" . $rp['foto'])) {
		?>
			<span class="foto">
				<img src="/fotos/rps/<?php echo $rp['foto']; ?>" />
			</span>
		<?php
		}
		?>
		<span class="nome">
			<?php
			echo $rp['nome'];
			?>
			<span class="cargo">
				<?php
				echo $rp['cargo'];
				?>
			</span>
		</span>
		<?php
		$presenca = $dbrps->devolvePresencaRP($rp['id']);
		if ($presenca) {
			if ($presenca["pago"] == 1) {
			?>
				<span class="presenca_paga"> PAGO </span>
			<?php
			} else {
			?>
				<span class="presenca_confirmada"> Entrada confirmada </span>
			<?php
			}
			?>
		<?php
		} else {
		?>
			<span class="confirmar_presenca"> Confirmar entrada </span>
		<?php

		}
		?>
	</a>
<?php
}
?>