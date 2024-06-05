<?php
if (isset($_GET['pg']))
	$pg = $_GET['pg'];
else
	$pg = "";

switch ($pg) {

	case 'alterar_password':
		include "alterar_password.php";
		break;

	case 'alterar_foto':
		include "alterar_foto.php";
		break;

	case 'eventos':
		include "eventos.php";
		break;

	case 'eventos_convidar':
		include "eventos_convidar.php";
		break;

	case 'eventos_convites':
		include "eventos_convites.php";
		break;

	case 'cartoes_sem_consumo':
		include "cartoes_sem_consumo.php";
		break;

	case 'adicionar_cartoes_sem_consumo':
		include "adicionar_cartoes_sem_consumo.php";
		break;

	case 'disponibilidade_de_mesas':
		include $_SERVER['DOCUMENT_ROOT'] . "/rp/privados/disponibilidade_de_mesas.php";
		break;

	case 'lista_espera_mesas':
		include $_SERVER['DOCUMENT_ROOT'] . "/rp/privados/lista_espera_mesas.php";
		break;

	case 'adicionar_lista_espera_mesa':
		include $_SERVER['DOCUMENT_ROOT'] . "/rp/privados/adicionar_lista_espera_mesa.php";
		break;

	case 'inserir_reserva':
		include $_SERVER['DOCUMENT_ROOT'] . "/rp/privados/inserir_reserva.php";
		break;

	case 'pagamento_adiantado':
		include $_SERVER['DOCUMENT_ROOT'] . "/rp/privados/pagamento_adiantado.php";
		break;

	case 'reserva_garrafas':
		include $_SERVER['DOCUMENT_ROOT'] . "/rp/privados/reserva_garrafas.php";
		break;

	case 'adicionar_reserva_garrafa':
		include $_SERVER['DOCUMENT_ROOT'] . "/rp/privados/adicionar_reserva_garrafa.php";
		break;

	case 'cartoes_consumo_obrigatorio':
		include "cartoes_consumo_obrigatorio.php";
		break;

	case 'adicionar_cartoes_consumo_obrigatorio':
		include "adicionar_cartoes_consumo_obrigatorio.php";
		break;

	case 'convites':
		include "convites.php";
		break;

	case 'adicionar_convites':
		include "adicionar_convites.php";
		break;

	case 'historico_pagamentos':
		include "historico_pagamentos.php";
		break;

	case 'homepage':
		include "homepage.php";
		break;


	default:
		if ($_SESSION["id_rp"] == 192) {
			include  "eventos.php";
		} else {
			include  "homepage.php";
		}
		break;
}
