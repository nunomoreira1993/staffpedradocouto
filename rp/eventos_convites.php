<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/rp/rp.obj.php');
$dbrp = new rp($db, $_SESSION['id_rp']);


$id_evento = (int) $_GET["id_evento"];

require_once($_SERVER['DOCUMENT_ROOT'] . '/rp/eventos/evento.obj.php');
$dbevento = new evento($db, $_SESSION['id_rp']);
$convites = $dbevento->listaConvites($id_evento);
?>
<div class="header">
	<h2>Eventos </h2>
</div>

<div class="conteudo" <?php echo escreveErroSucesso(); ?>>
	<?php
	if(empty($convites)){
		?>
		<span class="sem_registos">
			Não foram encontrados registos.
		</span>
		<?php
	}
	else{

		foreach($convites as $convite){
		?>
			<div class="tabela eventos">
				<div class="item">
					<div class="topo">
						<div class="coluna">
							<div class="titulo">
								Nome
							</div>
							<div class="valor">
								<?php echo $convite['convite_nome']; ?>
							</div>
						</div>
						<div class="coluna">
							<div class="titulo">
								Método de convite
							</div>
							<div class="valor">
								<?php echo ($convite['convite_tipo'] == 1) ? "Telémovel" : (($convite['convite_tipo'] == 2) ? "Whatsapp" : ($convite['convite_tipo'] == 3 ? " E-mail" : "Automático") ); ?>
							</div>
						</div>
						<div class="coluna">
							<div class="titulo">
								Estado do convite
							</div>
							<div class="valor">
								<?php echo $convite["convite_status"] == "sucesso" ? "Enviado" : "Erro" ; ?>
							</div>
						</div>
						<div class="coluna">
							<div class="titulo">
								Gerou bilhete?
							</div>
							<div class="valor">
								<?php echo $convite["qrcode"] > 0 ? "Sim" : "Não"; ?>
							</div>
						</div>
					</div>
					<?php
					if($convite["qrcode"] == 0) {
						?>
						<div class="rodape">
							<a href="/rp/index.php?pg=eventos_convidar&id_evento=<?php echo $convite['id_evento']; ?>&id=<?php echo $convite["id"]; ?>" class="convidar_novamente"> Convidar Novamente </a>
						</div>
						<?php
					}
					?>
				</div>
			</div>
		<?php
		}
	}
?>
</div>