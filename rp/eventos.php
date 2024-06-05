<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/rp/rp.obj.php');
$dbrp = new rp($db, $_SESSION['id_rp']);

require_once($_SERVER['DOCUMENT_ROOT'] . '/rp/eventos/evento.obj.php');
$dbevento = new evento($db, $_SESSION['id_rp']);
$eventos = $dbevento->listaEventos();
?>
<div class="header">
	<h2>Eventos </h2>
</div>

<div class="conteudo" <?php echo escreveErroSucesso(); ?>>
	<?php
	if(empty($eventos)){
		?>
		<span class="sem_registos">
			Não foram encontrados registos.
		</span>
		<?php
	}
	else{

		foreach($eventos as $evento){
			$contaConvites = $dbevento->contaConvites($evento["id"]);
			$contaBilhetes = $dbevento->contaBilhetes($evento["id"]);
		?>
			<div class="tabela eventos">
				<div class="item">
					<div class="topo">
						<div class="coluna">
							<div class="titulo">
								Nome
							</div>
							<div class="valor">
								<?php echo $evento['nome']; ?>
							</div>
						</div>
						<div class="coluna">
							<div class="titulo">
								Data Evento
							</div>
							<div class="valor">
								<?php echo $evento['data']; ?>
							</div>
						</div>
						<div class="coluna">
							<div class="titulo">
								Nº de convites enviados
							</div>
							<div class="valor">
								<?php echo $contaConvites; ?>
							</div>
						</div>
						<div class="coluna">
							<div class="titulo">
								Nº de bilhetes gerados
							</div>
							<div class="valor">
								<?php echo $contaBilhetes; ?>
							</div>
						</div>
						<div class="url_evento"><input type="text" class="form-control input-monospace" id="input-url-<?php echo $evento["id"]; ?>" data-autoselect="true" readonly="" value="https://guest.pedradocouto.net/index.php?evento=<?php echo md5($evento["id"]); ?>&rp=<?php echo md5($_SESSION["id_rp"]); ?>" tabindex="0">
							<button data-component="IconButton" type="button" aria-label="Copy url to clipboard" tabindex="-1" aria-describedby="tooltip-:rg:" data-no-visuals="true" onclick="copytoclipboard('input-url-<?php echo $evento["id"]; ?>');" class="button_copy">
							<div data-direction="nw" aria-label="Copiar URL" alt="Copiar URl" title="Copiar URL" role="tooltip" id="tooltip-:rg:" class="Tooltip__StyledTooltip-sc-5xkifj-0 cKmcbz" style="">Copiar URL</div>
						</button></div>
					</div>
						<div class="rodape">
							<a href="/rp/index.php?pg=eventos_convidar&id_evento=<?php echo $evento['id']; ?>" class="editar"> Convidar </a>
							<a href="/rp/index.php?pg=eventos_convites&id_evento=<?php echo $evento['id']; ?>" class="editar"> Ver convites </a>
						</div>
				</div>
			</div>
		<?php
		}
	}
?>
</div>