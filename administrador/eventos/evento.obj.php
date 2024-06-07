<?php
class evento {
	function __construct($db) {
		$this->db = $db;
	}
	function devolveEvento($id){
		$query ="SELECT * FROM eventos WHERE eventos.id = '$id'";
		$res = $this->db->query($query);
		return $res[0];
	}
	function listaEventos($filtros = array(), $limit = ""){
		$where = "";
		if($filtros){
			if($filtros['nome']){
				$where .= " AND eventos.nome like '%" . $filtros['nome'] . "%'";
			}
		}

		$query ="SELECT * FROM eventos WHERE 1=1 $where ORDER BY data DESC, nome $limit";
		$res = $this->db->query($query);
		return $res;
	}
	function countEventos($filtros = array()){
		$where = "";
		if($filtros){
			if($filtros['nome']){
				$where .= " AND eventos.nome like '%" . $filtros['nome'] . "%'";
			}
		}

		$query ="SELECT count(*) as conta FROM eventos WHERE 1=1 $where ORDER BY data DESC, nome";
		$res = $this->db->query($query);
		return $res[0]["conta"];
	}
	function contaConvites($id){
		$query ="SELECT count(eventos_convites.id) as conta FROM eventos_convites WHERE eventos_convites.id_evento = " . $id;
		$res = $this->db->query($query);

		return intval($res[0]['conta']);
	}
	function listaConvites($id_evento, $filtros = array(), $limit = ""){

		$where = "";
		if($filtros){
			if($filtros['nome_cliente']){
				$where .= " AND eventos_convites.convite_nome like '%" . $filtros['nome_cliente'] . "%'";
			}
			if($filtros['nome_rp']){
				$where .= " AND rps.nome like '%" . $filtros['nome_rp'] . "%'";
			}
			if($filtros['gerou_bilhete']){
				if($filtros['gerou_bilhete'] == 1){
					$where .= " AND eventos_convites.qrcode > 0";
				}
				else {
					$where .= " AND eventos_convites.qrcode = 0";
				}
			}
			if($filtros['entrada']){
				if($filtros['entrada'] == 1){
					$where .= " AND eventos_convites.qrcode_entrada = 1";
				}
				else {
					$where .= " AND eventos_convites.qrcode_entrada = 0";
				}
			}
		}
		$query ="SELECT rps.nome as nome_rp, eventos_convites.* FROM eventos_convites LEFT JOIN rps ON eventos_convites.id_rp = rps.id  WHERE eventos_convites.id_evento = " . (int) $id_evento . " $where ORDER BY qrcode_entrada_data DESC, convite_data DESC $limit";

		$res = $this->db->query($query);
		return $res;
	}
	function countConvites($id_evento, $filtros = array(), $limit = ""){

		$where = "";
		if($filtros){
			if($filtros['nome_cliente']){
				$where .= " AND eventos_convites.convite_nome like '%" . $filtros['nome_cliente'] . "%'";
			}
			if($filtros['nome_rp']){
				$where .= " AND rps.nome like '%" . $filtros['nome_rp'] . "%'";
			}
			if($filtros['gerou_bilhete']){
				if($filtros['gerou_bilhete'] == 1){
					$where .= " AND eventos_convites.qrcode > 0";
				}
				else {
					$where .= " AND eventos_convites.qrcode = 0";
				}
			}
			if($filtros['entrada']){
				if($filtros['entrada'] == 1){
					$where .= " AND eventos_convites.qrcode_entrada = 1";
				}
				else {
					$where .= " AND eventos_convites.qrcode_entrada = 0";
				}
			}
		}
		$query ="SELECT count(*) as conta FROM eventos_convites LEFT JOIN rps ON eventos_convites.id_rp = rps.id  WHERE eventos_convites.id_evento = " . (int) $id_evento . " $where ORDER BY qrcode_entrada_data DESC, convite_data DESC ";

		$res = $this->db->query($query);
		return $res[0]["conta"];
	}

	function contaBilhetes($id){
		$query ="SELECT count(id) as conta FROM eventos_convites WHERE id_evento = " . $id . " AND qrcode != ''";
		$res = $this->db->query($query);
		return intval($res[0]['conta']);
	}
	function contaEventos(){
		$query ="SELECT count(*) as conta FROM eventos";
		$res = $this->db->query($query);
		return $res[0]['conta'];
	}
	function devolveMensagemDefault() {
		return "Olá {NOME}!\nAcede a este endereço para entrares na minha Guest no próximo fim de semana.\n{ENDERECO}
		\nObrigado e até já!\nPEDRA DO COUTO";
	}
	function getConviteByQRCode($qrcode){
		$query = "SELECT * FROM eventos_convites WHERE qrcode = '" . $qrcode . "'";
		return $this->db->query($query)[0];
	}
	function getSemConsumoByID($id){
		$query = "SELECT * FROM rps_cartoes_sem_consumo WHERE id = '" . $id . "' AND entrou = 0";
		return $this->db->query($query)[0];
	}
	function getConsumoObrigatorioByID($id){
		$query = "SELECT * FROM rps_cartoes_consumo_obrigatorio WHERE id = '" . $id . "'  AND entrou = 0";
		return $this->db->query($query)[0];
	}
}
