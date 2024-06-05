<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/rp/rp.obj.php');
$dbrp = new rp($db, $_SESSION['id_rp']);

require_once($_SERVER['DOCUMENT_ROOT'] . '/rp/eventos/evento.obj.php');
$dbevento = new evento($db, $_SESSION['id_rp']);
if ($_GET['id_evento'] > 0) {
	$evento = $dbevento->devolveEvento($_GET['id_evento']);
} else {
	$_SESSION['erro'] = "Não é possivel aceder a essa página.";
	header('Location: /rp/');
	exit;
}

if (date('H') < 14) {
	$data_evento = date('Y-m-d', strtotime('-1 day'));
} else {
	$data_evento = date('Y-m-d');
}

if ($evento['data'] < $data_evento) {
	$_SESSION['erro'] = "Já não é possivel enviar convites para este evento.";
	header('Location: /rp/');
	exit;
}

$id = intval($_GET['id']);

if($id > 0) {
	$convite = $dbevento->devolveConvite($id);
}

if ($_POST) {
	$id_evento = (int) $_GET['id_evento'];
	$id_rp = $_SESSION['id_rp'];
	$nome = $_POST["nome"];
	$telemovel = $_POST["telemovel"];
	$email = $_POST["email"];
	$metodo = $_POST["metodo"];


	if (empty($nome)) {
		$_SESSION['erro'] = "Por favor introduza o nome do convidado.";
	}

	if (empty($metodo)) {
		$_SESSION['erro'] = "Por favor introduza o método de envio do convite.";
	}

	if ($metodo == 1 || $metodo == 2) {
		if (empty($telemovel)) {
			$_SESSION['erro'] = "Por favor introduza o telemovel.";
		}

		$numero = preg_replace('/\D/', '', $telemovel);
		if (!preg_match('/^9[0-9]{8}$/', $telemovel)) {
			$_SESSION['erro'] = "Número inválido, tente com outro número";
		}
	} else {
		if (empty($email)) {
			$_SESSION['erro'] = "Por favor introduza o e-mail";
		}
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$_SESSION['erro'] = "E-mail inválido, tente com outro e-mail";
		}
	}

	if (empty($_SESSION['erro'])) {
		$campos['convite_tipo'] = $metodo;
		$campos['convite_nome'] = $nome;
		$campos['convite_email'] = $email;
		$campos['convite_telemovel'] = $telemovel;

		if ($id > 0) {
			$db->Update('eventos_convites', $campos, 'id=' . intval($_GET['id']));
			$hash = $convite["hash"];
			$_SESSION['sucesso'] = "A convite foi alterado.";
		} else {
			$campos['id_evento'] = $id_evento;
			$campos['id_rp'] = $id_rp;
			$campos['convite_data'] = date('Y-m-d H:i:s');
			$id = $db->Insert('eventos_convites', $campos);
			$_SESSION['sucesso'] = "O convite foi inserido.";
			if ($id) {
				$hash = md5($campos['id_evento'] . strtotime($campos["convite_data"]) . $id);
				$db->Update('eventos_convites', array("hash" => $hash), 'id=' . intval($id));
			}
		}
		$evento["mensagem"] = str_replace("{NOME}", $nome, $evento["mensagem"]);
		$evento["mensagem"] = str_replace("{ENDERECO}", "https://guest.pedradocouto.net/index.php?hash=" . $hash, $evento["mensagem"]);

		require_once($_SERVER['DOCUMENT_ROOT'] . '/rp/MensagemHandler.obj.php');
		$MensagemHandler = new MensagemHandler();
		$response = $MensagemHandler->enviarMensagem($metodo, $nome, $email, $telemovel, $evento["mensagem"]);
		if($response["status"] == "erro") {

			$db->Update('eventos_convites', array("convite_status" => "erro", "convite_status_message" => $response["mensagem"], "convite_status_date" => date('Y-m-d H:i:s')), 'id=' . intval($id));

			$_SESSION['erro'] = "Tente novamente com outro método de envio, erro recebido: " . $response["mensagem"];
			header('Location: /rp/index.php?pg=eventos_convidar&id_evento=' . intval($_GET['id_evento']) . "&id=" . intval($id));
			exit;
		}
		else {
			$db->Update('eventos_convites', array("convite_status" => "sucesso", "convite_status_message" => "", "convite_status_date" => date('Y-m-d H:i:s')), 'id=' . intval($id));
			if($response["url"]) {
				header("Location: " . $response["url"]);
				exit;
			}
		}

		header('Location: /rp/index.php?pg=eventos');
		exit;
	}
}
if (empty($_POST)) {
	$campos = $convite;
}
?>


<div class="header">
	<h2>Convites </h2>
</div>

<div class="conteudo" <?php echo escreveErroSucesso(); ?>>
	<a href="/rp/index.php?pg=convites" class="voltar">
		<span class="icon"> <img src="/temas/rps/imagens/back.svg" /> </span>
		<span class="label"> Voltar </span>
	</a>

	<form name="formulario" class="eventos_convidar" action="" method="post" enctype="multipart/form-data">

		<div class="inputs">
			<div class="label">
				Evento
			</div>
			<div class="input">
				<?php echo date('d-m-Y', strtotime($evento["data"])) . " | " . $evento["nome"]; ?>
			</div>
		</div>
		<div class="inputs">
			<div class="label">
				Nome do Cliente
			</div>
			<div class="input">
				<input name="nome" value="<?php echo $campos['convite_nome']; ?>" type="text" required="required" />
			</div>
		</div>
		<div class="inputs">
			<div class="label">
				Método de Envio
			</div>
			<div class="input">
				<select name="metodo" required>
					<option value="">Escolha um método de envio</option>
					<?php
					if($_SESSION["id_rp"] == 192){
						?>
						<option value="1" <?php if ($campos["convite_tipo"] == 1) { ?> selected="selected" <?php } ?>>Telémovel</option>
						<?php
					}
					?>
					<option value="2" <?php if ($campos["convite_tipo"] == 2) { ?> selected="selected" <?php } ?>>Whatsapp</option>
					<option value="3" <?php if ($campos["convite_tipo"] == 3) { ?> selected="selected" <?php } ?>>E-mail</option>
				</select>
			</div>
		</div>
		<div class="inputs input-tel hidden">
			<div class="label">
				Número de Telémovel
			</div>
			<div class="input">
				<input name="telemovel" value="<?php echo $campos['convite_telemovel']; ?>" type="tel" />
			</div>
		</div>
		<div class="inputs  input-email hidden">
			<div class="label">
				E-mail
			</div>
			<div class="input">
				<input name="email" value="<?php echo $campos['convite_email']; ?>" type="email" />
			</div>
		</div>
		<div class="inputs">
			<input type="submit" value="Enviar" />
		</div>
	</form>
</div>