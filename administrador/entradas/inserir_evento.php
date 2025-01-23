<?php
if (empty($_SESSION['id_utilizador'])) {
	header('Location:/index.php');
	exit;
}
if ($tipo != 1) {
	header('Location:/administrador/index.php');
	exit;
}
require_once($_SERVER['DOCUMENT_ROOT'] . '/administrador/eventos/evento.obj.php');
$dbevento = new evento($db);
if ($_GET['id'] > 0) {
	$evento = $dbevento->devolveEvento($_GET['id']);
}
if ($_POST) {
	$campos['nome'] = $_POST['nome'];
	$campos['data'] = $_POST['data'];
	$campos['descricao'] = $_POST['descricao'];
	$campos['mensagem'] = $_POST['mensagem'];
	$campos['cartoes_sem_consumo'] = $_POST['cartoes_sem_consumo'];

	if (empty($campos['nome']) && empty($_SESSION['erro'])) {
		$_SESSION['erro'] = "Preêncha o campo 'Nome'.";
	}
	if (empty($campos['data']) && empty($_SESSION['erro'])) {
		$_SESSION['erro'] = "Preêncha o campo 'Data'.";
	}
	if (empty($campos['descricao']) && empty($_SESSION['erro'])) {
		$_SESSION['erro'] = "Preêncha o campo 'Descrição'";
	}
	if (empty($campos['mensagem']) && empty($_SESSION['erro'])) {
		$_SESSION['erro'] = "Preêncha o campo 'Mensagem'";
	}
	if ($_FILES['imagem']['name'] && empty($_SESSION['erro'])) {
		$foto_importada = doUpload($_FILES['imagem'], "/eventos/originais/", "foto");

		$resize  = doResize("/fotos/eventos/originais/", $foto_importada, "/fotos/eventos/", $foto_importada, 4, "center", 1920, 1000);

		if ($resize['success'] == true) {
			$campos['imagem'] = $foto_importada;
		} else {
			$_SESSION['erro'] = $resize['errors']['user'][0];
		}

		if ($_GET['id'] && empty($_SESSION['erro'])) {
			if ($evento['imagem'] && file_exists($_SERVER['DOCUMENT_ROOT'] . "/fotos/eventos/" . $evento['imagem'])) {
				unlink($_SERVER['DOCUMENT_ROOT'] . "/fotos/eventos/" . $evento['foto']);
			}
		}
	}
	if (empty($_SESSION['erro'])) {
		if ($_GET['id'] == 0) {
			$db->Insert('eventos', $campos);
			$_SESSION['sucesso'] = "Inseriu o evento com sucesso";
			$db->Insert('logs', array('descricao' => "Inseriu um evento", 'arr' => json_encode($campos), 'id_admin' => $_SESSION['id_utilizador'], 'tipo' => "Inserção", 'user_agent' => $_SERVER['HTTP_USER_AGENT'], 'ip' => $_SERVER['REMOTE_ADDR']));
		} else {
			$db->Update('eventos', $campos, 'id=' . $_GET['id']);
			$_SESSION['sucesso'] = "Alterou o evento com sucesso";
			$db->Insert('logs', array('descricao' => "Alterou um evento", 'arr' => json_encode($campos), 'id_admin' => $_SESSION['id_utilizador'], 'tipo' => "Alteração", 'user_agent' => $_SERVER['HTTP_USER_AGENT'], 'ip' => $_SERVER['REMOTE_ADDR']));
		}
		header('Location:/administrador/index.php?pg=eventos');
		exit;
	}
}

if ($_GET['id'] == 0) {
?>
	<h1 class="titulo"> Inserir - Evento </h1>
<?php

} else {
?>
	<h1 class="titulo"> Editar - Evento </h1>
<?php
}
if ($campos) {
	$evento = $campos;
}
?>

<div class="content" <?php echo escreveErroSucesso(); ?>>
	<form action="" method="post" enctype="multipart/form-data">
		<div class="input-grupo">
			<label for="input-email">
				Titulo
			</label>
			<div class="input">
				<input type="text" value="<?php echo $evento['nome']; ?>" name="nome" id="input-nome" placeholder="Nome" />
			</div>
		</div>
		<div class="input-grupo">
			<label for="input-password">
				Data
			</label>
			<div class="input">
				<input type="date" value="<?php echo $evento['data']; ?>" name="data" id="input-data" placeholder="Data do evento" />
			</div>
		</div>
		<div class="input-grupo">
			<label for="input-foto">
				Imagem
			</label>
			<div class="input">
				<?php
				if (!empty($evento['imagem']) && file_exists($_SERVER['DOCUMENT_ROOT'] . "/fotos/eventos/" . $evento['imagem'])) {
				?>
					<div class="foto">
						<img src="/fotos/eventos/<?php echo $evento['imagem']; ?>" width="150px">
					</div>
				<?php
				}
				?>
				<input type="file" value="<?php echo $evento['imagem']; ?>" name="imagem" id="input-imagem" placeholder="Banner evento" />
			</div>
		</div>
		<div class="input-grupo">
			<label for="input-email">
				Cartões Sem Consumo
			</label>
			<div class="input">
				<input type="text" value="<?php echo $evento['cartoes_sem_consumo']; ?>" name="cartoes_sem_consumo" id="input-cartoes_sem_consumo" placeholder="Cartões Sem Consumo" />
			</div>
		</div>
		<div class="input-grupo">
			<label for="input-nome">
				Descrição
			</label>
			<div class="input">
				<textarea name="descricao" id="input-descricao" placeholder="Descrição"><?php echo $evento['descricao']; ?></textarea>
			</div>
		</div>
		<?php
		if (empty($evento['mensagem'])) {
			$evento['mensagem'] = $dbevento->devolveMensagemDefault();
		}
		?>
		<div class="input-grupo">
			<label for="input-nome">
				Mensagem
			</label>
			<div class="input">
				<textarea name="mensagem" id="input-mensagem" placeholder=""><?php echo $evento['mensagem']; ?></textarea>
			</div>
			<br />
			<small>Ao usar {NOME} ao ser enviado a mensagem vai ser substituido pelo nome do convidado; <br /> <br /> A tag {ENDERECO} é o sitio na mensagem onde vai ser enviado o URL do convite </small>
		</div>
		<div class="input-grupo">
			<div class="input">
				<input type="submit" value="Enviar" />
			</div>
		</div>
	</form>
</div>