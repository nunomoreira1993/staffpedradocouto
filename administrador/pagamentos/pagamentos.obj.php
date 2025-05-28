<?php
class pagamentos
{
	protected $datas_rp = array();
	protected $faltas_rp = array();
	protected $faltas_euros = array();

    function __construct($db)
    {
        $this->db = $db;
    }
    function listaLogicaAtrasos()
    {

        $query = "SELECT * FROM logica_atrasos ORDER BY id ASC";
        $res = $this->db->query($query);
        return $res;
    }

    function devolveLogicaAtrasos($id)
    {
        $query = "SELECT * FROM logica_atrasos WHERE id = " . $id . "   ORDER BY id ASC";
        $res = $this->db->query($query);
        return $res[0];
    }

    function listaLogicaPagamentos()
    {

        $query = "SELECT * FROM logica_pagamentos ORDER BY id ASC";
        $res = $this->db->query($query);
        return $res;
    }

    function devolveLogicaPagamentos($id)
    {
        $query = "SELECT * FROM logica_pagamentos WHERE id = " . $id . "   ORDER BY id ASC";
        $res = $this->db->query($query);
        return $res[0];
    }
    function converteEntradasToEuro($entradas, $id_rp)
    {
        if ($id_rp) {

            $query = "SELECT rps_cargos.regra_entradas, rps.id, rps.nome FROM rps INNER JOIN rps_cargos ON rps.id_cargo = rps_cargos.id WHERE rps.id = '" . $id_rp . "' LIMIT 1";
            $resentradas = $this->db->query($query);
            if ($resentradas[0]['regra_entradas'] == 1) {

                $query = "SELECT * FROM logica_pagamentos   ORDER BY de ASC";
                $res = $this->db->query($query);
                $total = 0;
                $fim = 0;
                foreach ($res as $k => $rs) {
                    if ($fim == 0) {
                        $ate = $rs['ate'];
                        $de = $rs['de'];
                        $valor = $rs['valor'];
                        $numero_elementos = $ate - $de;
                        if ($k == 0) {
                            $sobra = $entradas - $numero_elementos;
                        } else {
                            $sobra = $sobra - $numero_elementos;
                        }
                        if ($sobra <= 0) {
                            $fim = 1;

                            $total += ($sobra + $numero_elementos) * $valor;
                        } else {
                            $total += $numero_elementos * $valor;
                        }
                    }
                }
            } else {
                $total = $entradas;
            }

            return $total;
        }
    }

    function listaEventosRP($id_rp, $data_evento)
    {
        $query = "SELECT sum(rps_entradas.quantidade) as quantidade FROM rps_entradas INNER JOIN rps ON rps.id = rps_entradas.id_rp AND rps.comissao_guest = 1  WHERE  rps_entradas.id_rp = " . $id_rp . " AND rps_entradas.data_evento = '" . $data_evento . "' GROUP BY rps_entradas.data_evento DESC";
        $eventoRP = $this->db->query($query);
        if ($eventoRP[0]['quantidade'] > 0) {
            $eventos_return['descricao'] = "<b>" . $data_evento . "</b>: " . intval($eventoRP[0]['quantidade']) . " entradas";
            $eventos_return['comissao'] = $this->converteEntradasToEuro($eventoRP[0]['quantidade'], $id_rp);

			if(in_array($data_evento,  $this->faltas_rp) && $eventos_return['comissao'] > 0) {
				$this->faltas_euros[$data_evento] += $eventos_return['comissao'];
			}

            return $eventos_return;
        }
    }
    function devolveDatasParaPagamento($id_rp)
    {
		if($this->datas_rp[$id_rp]){
			return $this->datas_rp[$id_rp];
		}
		else{
			$where = "";
			if (is_array($id_rp)) {
				$arrdatas = array();
				$res_entradas = array();
				$i = 0;
				foreach($id_rp as $id){

					$where = "";
					$query = "SELECT data_minima_pagamentos FROM rps WHERE id  = ".$id."";
					$min = $this->db->query($query);

					if ($min[0]['data_minima_pagamentos'] > "0000-00-00") {
						$where .= " AND data_evento >= '" . $min[0]['data_minima_pagamentos'] . "'";
					}

					$query = "SELECT data_evento FROM conta_corrente_eventos WHERE id_rp = ".$id." $where GROUP BY data_evento DESC";
					$datas_pagas = $this->db->query($query);
					if ($datas_pagas) {
						$datas_ja_pagas = array();
						foreach ($datas_pagas as $data) {
							$datas_ja_pagas[] = $data['data_evento'];
						}
						$where .= " AND data_evento NOT IN('" . implode("', '", $datas_ja_pagas) . "') ";
					}

					$query = "SELECT data_evento FROM rps_entradas WHERE 1=1 $where GROUP BY data_evento DESC";
					$res_meio = $this->db->query($query);
					if($res_meio){
						foreach($res_meio as $linha){
							if(!in_array($linha['data_evento'], $arrdatas)){
								$arrdatas[] = $linha['data_evento'];
								$res_entradas[$i]['data_evento'] = $linha['data_evento'];
								$i++;
							}
						}
					}
					$this->datas_rp[$id] = $res_entradas;
				}
			} else {
				$query = "SELECT data_minima_pagamentos FROM rps WHERE id = '" . $id_rp . "'";
				$min = $this->db->query($query);

				if ($min[0]['data_minima_pagamentos'] > "0000-00-00") {
					$where .= " AND data_evento >= '" . $min[0]['data_minima_pagamentos'] . "'";
				}

				$query = "SELECT data_evento FROM conta_corrente_eventos WHERE id_rp = '" . $id_rp . "' $where GROUP BY data_evento DESC";
				$datas_pagas = $this->db->query($query);
				if ($datas_pagas) {
					foreach ($datas_pagas as $data) {
						$datas_ja_pagas[] = $data['data_evento'];
					}
					$where .= " AND data_evento NOT IN('" . implode("', '", $datas_ja_pagas) . "') ";
				}
				$query = "SELECT data_evento FROM rps_entradas WHERE 1=1 $where GROUP BY data_evento DESC";
				$res_entradas = $this->db->query($query);

				$this->datas_rp[$id_rp] = $res_entradas;
			}
			return $res_entradas;
        }
    }

	function getPenalties($absences) {
		$penalties = array();

		for ($i = 1; $i < count($absences); $i++) {
			$current_date = new DateTime($absences[$i]);
			$previous_date = new DateTime($absences[$i-1]);

			$interval = $previous_date->diff($current_date)->days;

			if ($interval == 7) {
				$penalties[] = $absences[$i];
			}
		}

		return $penalties;
	}

	function devolveFaltas($id_rp, $data_minima)  {
		$query = "SELECT eventos.data FROM `eventos` LEFT JOIN presencas ON eventos.data = presencas.data_evento AND presencas.id_rp = " . $id_rp . " WHERE eventos.data >= '" . $data_minima . "' AND presencas.data_evento IS NULL ORDER BY eventos.data ASC";
		$res = $this->db->query($query);

		$res_columns = array();

		if(is_array($res) && count($res) > 0) {
			$res_columns = array_column($res, 'data');
			$res_columns = $this->getPenalties($res_columns);
		}

		$this->faltas_rp = $res_columns;

		return $res_columns;
	}

    function devolvePagamento($id_rp, $datas = "")
    {
        if ($id_rp > 0) {
            $divida = $this->devolveDividaRP($id_rp);

			if(empty($datas)) {
            $datas = $this->devolveDatasParaPagamento($id_rp);
			}
			if($datas) {
				$faltas = $this->devolveFaltas($id_rp, $datas[count($datas)-1]['data_evento']);
			}

			$garrafas = $this->devolveComissaoGarrafas($id_rp);

            $equipa_pagamentos = $this->devolveEquipa($id_rp);


			$return = array();

            if ($equipa_pagamentos['total'] > 0) {
                $return['equipa']['comissao'] = $equipa_pagamentos["total"];
                foreach($equipa_pagamentos['lista'] as $data => $equip_pag){
                    if($equip_pag['total']['total_pagar'] > 0) {
                        $return['equipa']["descricao"] .= "<b>".$data."</b> - " . euro($equip_pag['total']['total_pagar']) . "</br>";
                    }
                }
            }

            if ($datas) {

                foreach ($datas as $data) {
                    $data_evento = $data['data_evento'];

                    $guest = $this->listaEventosRP($id_rp, $data_evento);

                    $privados = $this->devolveComissaoPrivados($id_rp, $data_evento);
                    $atrasos = $this->devolveAtrasos($id_rp, $data_evento);
                    $convites = $this->devolveValidaConvite($id_rp, $data_evento);

                    if ($guest['comissao'] > 0) {
                        $return['guest']['comissao'] += $guest["comissao"];
                        $descricao_guest = $guest["descricao"] . " - " . euro($guest["comissao"]);
                        $return['guest']["descricao"] = $return['guest']['descricao'] ? $return['guest']['descricao'] . " </br> " . $descricao_guest : "" . $descricao_guest;
                    }
                    if ($privados['comissao'] > 0) {
                        $return['privados']['comissao'] += $privados["comissao"];
                        $descricao_privados = $privados["descricao"] . " - " . euro($privados["comissao"]);
                        $return['privados']["descricao"] = $return['privados']['descricao'] ? $return['privados']['descricao'] . " </br> " . $descricao_privados : "" . $descricao_privados;
                    }

                    if ($atrasos['comissao'] > 0) {
                        $return['atrasos']['comissao'] += $atrasos["comissao"];
                        $descricao_atrasos = $atrasos["descricao"] . " - " . euro($atrasos["comissao"]);
                        $return['atrasos']["descricao"] = $return['atrasos']['descricao'] ? $return['atrasos']['descricao'] . " </br> " . $descricao_atrasos : "" . $descricao_atrasos;
                    }
                    if ($convites['comissao'] > 0) {
                        $return['convites']['comissao'] += $convites["comissao"];
                        $descricao_convites = $convites["descricao"] . " - " . euro($convites["comissao"]);
                        $return['convites']["descricao"] = $return['convites']['descricao'] ? $return['convites']['descricao'] . " </br> " . $descricao_convites : "" . $descricao_convites;
                    }
                }
            }
        }
        if ($garrafas) {
            foreach ($garrafas as $garrafa) {
                $return['garrafas']['comissao'] += $garrafa["comissao"];
                $descricao_garrafas = $garrafa["descricao"] . " - " . euro($garrafa["comissao"]);
                $return['garrafas']["descricao"] = $return['garrafas']['descricao'] ? $return['garrafas']['descricao'] . " </br> " . $descricao_garrafas : "" . $descricao_garrafas;
            }
        }


        if ($divida < 0) {
            $return['divida'] = $divida;
        }

		$faltas_merged =  array_merge(array_fill_keys($this->faltas_rp, 0), $this->faltas_euros);

		$return["faltas"] = $faltas_merged;

        $return['extras'] = $this->devolveValoresExtras($id_rp);

        $return['total'] = $return['guest']['comissao'] + $return['equipa']['comissao'] + $return['privados']['comissao'] + $return['garrafas']['comissao'] - $return['convites']['comissao'] - $return['atrasos']['comissao'] + $return['extras']['total'] + ($return['divida']) - array_sum($this->faltas_euros);

        return $return;
    }
    function devolveValidaConvite($id_rp, $data_evento)
    {
        $query = "SELECT convites_pagamentos FROM rps INNER JOIN rps_cargos ON rps.id_cargo = rps_cargos.id  WHERE rps.id = " . $id_rp . " AND rps_cargos.convites_pagamentos = 1 AND rps.penaliza_convite = 1";
        $resultado_rp_cargos = $this->db->query($query);
        if ($resultado_rp_cargos[0]['convites_pagamentos'] == 1) {
            $query = "SELECT id, imagem, valido FROM convites WHERE convites.id_rp = " . $id_rp . " AND convites.data_evento = '" . $data_evento . "'   GROUP BY convites.data_evento DESC";
            $resultado = $this->db->query($query);

            if (empty($resultado)) {
                $eventos_return['comissao'] = 5;
                $eventos_return['descricao'] = "<b>" . $data_evento . "</b>: " . "Não foi feito o upload para este evento pelo staff.";
                return $eventos_return;
            } else {
                if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/fotos/convites/" . $resultado[0]['imagem']) && $resultado[0]['imagem']) {
                    if ($resultado[0]['valido'] == 2) {
                        $eventos_return['comissao'] = 5;
                        $eventos_return['descricao'] = "<b>" . $data_evento . "</b>: " . "O convite não foi aceite.";
                        return $eventos_return;
                    }
                } else {
                    $eventos_return['comissao'] = 5;
                    $eventos_return['descricao'] = "<b>" . $data_evento . "</b>: " . "Não foi feito o upload para este evento pelo staff.";
                    return $eventos_return;
                }
            }
        }
    }

    function devolveConvites($id_rp)
    {

        $where = "";
        $datas = $this->devolveDatasParaPagamento($id_rp);
        if ($datas) {
            foreach ($datas as $data) {
                $datas_a_pagar[] = $data['data_evento'];
            }
            $where .= " AND data_evento  IN('" . implode("', '", $datas_a_pagar) . "') ";

            $query = "SELECT id, imagem, valido, data, data_evento FROM convites WHERE convites.id_rp = " . $id_rp . "  $where";
            $resultado = $this->db->query($query);
            foreach ($resultado as $k => $res) {
                if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/fotos/convites/" . $res['imagem']) && $res['imagem']) {
                    $resultado[$k]['imagem'] = "/fotos/convites/" . $res['imagem'];
                }
            }
        }
        return $resultado;
    }
    function devolveEquipa($id_rp)
    {

        $query = "SELECT id, nome FROM rps WHERE rps.id_chefe_equipa = " . $id_rp;
        $rps = $this->db->query($query);

        if ($rps) {
            $ids_array = array_column($rps, 'id');
        }
        $where = "";
        $datas = $this->devolveDatasParaPagamento($id_rp);

        if ($datas) {
            $resultado['total'] = 0;
            $resultado['rps'] = $ids_array;
            foreach ($datas as $k => $data) {
                $resultado['lista'][$data['data_evento']]['total']['quantidade'] = 0;
                $resultado['lista'][$data['data_evento']]['total']['total_pagar'] = 0;
                $resultado['lista'][$data['data_evento']]['total']['total_privados'] = 0;
                $resultado['lista'][$data['data_evento']]['total']['total_pago_privados'] = 0;
                foreach ($rps as $i => $rp) {
                    $query = "SELECT sum(rps_entradas.quantidade) as quantidade FROM rps_entradas WHERE rps_entradas.id_rp = '" . $rp['id'] . "' AND rps_entradas.data_evento = '" . $data['data_evento'] . "'";
                    $entradas = $this->db->query($query);
                    if ($entradas[0]['quantidade']) {
                        $resultado['lista'][$data['data_evento']]['rps'][$i]['nome'] = $rp['nome'];
                        $resultado['lista'][$data['data_evento']]['rps'][$i]['quantidade'] = $entradas[0]['quantidade'];
                        if ($entradas[0]['quantidade'] < 5) {
                            $resultado['lista'][$data['data_evento']]['rps'][$i]['total_chefe'] = 0;
                        } else {
                            $resultado['lista'][$data['data_evento']]['total']['total_pagar'] += $resultado['lista'][$data['data_evento']]['rps'][$i]['total_chefe'] = ($entradas[0]['quantidade'] * 0.5);
                        }

						$resultado['lista'][$data['data_evento']]['total']['quantidade'] += $entradas[0]['quantidade'];
						// $resultado['lista'][$data['data_evento']]['total']['total_pagar'] += $resultado['lista'][$data['data_evento']]['rps'][$i]['total_rp'] = ($entradas[0]['quantidade'] * 1);
                    }
                    $privados = $this->devolveComissaoPrivados($rp['id'], $data['data_evento']);

                    if($privados['total'] > 0){
                        $resultado['lista'][$data['data_evento']]['total']['total_privados']  += $privados['total'];
                        $resultado['lista'][$data['data_evento']]['rps'][$i]['privados'] = $privados['descricao'];

                        #comissao
                        // $resultado['lista'][$data['data_evento']]['total']['total_pagar'] +=  $privados['comissao'];
                        $resultado['lista'][$data['data_evento']]['total']['total_pago_privados'] += $privados['comissao'];
                        $resultado['lista'][$data['data_evento']]['rps'][$i]['total_privados_rp'] +=  $privados['comissao'];

                        #cartoes
                        // $resultado['lista'][$data['data_evento']]['total']['total_pagar'] += $privados['total_cartoes'] * 0.5;
                        $resultado['lista'][$data['data_evento']]['total']['total_pagar'] += ($resultado['lista'][$data['data_evento']]['total']['total_privados_chefe'] += $privados['total_cartoes'] * 0.5);
                        $resultado['lista'][$data['data_evento']]['rps'][$i]['total_privados_chefe'] += $privados['total_cartoes'] * 0.5;

                    }

                    if($privados['total'] > 0  || $entradas[0]['quantidade']) {
                        $resultado['lista'][$data['data_evento']]['rps'][$i]['data'] = $data['data_evento'];
                        $resultado['lista'][$data['data_evento']]['rps'][$i]['nome'] = $rp['nome'];
                    }
                }
                if ($resultado['lista'][$data['data_evento']]['total']['quantidade'] == 100) {
                    $resultado['lista'][$data['data_evento']]['total']['total_pagar'] += $resultado['lista'][$data['data_evento']]['total']['bonus'] = 10;
                } else if ($resultado['lista'][$data['data_evento']]['total']['quantidade'] >= 101) {
                    $resultado['lista'][$data['data_evento']]['total']['total_pagar'] += $resultado['lista'][$data['data_evento']]['total']['bonus'] = 20;
                } else if ($resultado['lista'][$data['data_evento']]['total']['quantidade'] >= 151) {
                    $resultado['lista'][$data['data_evento']]['total']['total_pagar'] += $resultado['lista'][$data['data_evento']]['total']['bonus'] = 30;
                }
                $resultado['total'] += $resultado['lista'][$data['data_evento']]['total']['total_pagar'];

				if(in_array($data['data_evento'],  $this->faltas_rp) && $resultado['lista'][$data['data_evento']]['total']['total_pagar'] > 0) {
					$this->faltas_euros[$data['data_evento']] += $resultado['lista'][$data['data_evento']]['total']['total_pagar'];
				}
            }
        }

        return $resultado;
    }
    function devolveConviteFicheiro($ficheiro = false)
    {

        if ($ficheiro) {
            $query = "SELECT convites.id, convites.data_evento FROM convites WHERE convites.imagem= '" . $ficheiro . "'";
            $resultado = $this->db->query($query);

            if (!empty($resultado)) {
                return $resultado[0];
            }

        }
        die;

    }
    function devolveAtrasos($id_rp, $data_evento)
    {

        $query = "SELECT regras_atrasos_pagamentos FROM rps INNER JOIN rps_cargos ON rps.id_cargo = rps_cargos.id  WHERE rps.id = " . $id_rp . " AND rps_cargos.regras_atrasos_pagamentos = 1";
        $resultado_rp_cargos = $this->db->query($query);
        if ($resultado_rp_cargos[0]['regras_atrasos_pagamentos'] == 1) {
            $query = "SELECT data_evento, data_entrada FROM presencas WHERE presencas.id_rp = " . $id_rp . " AND presencas.data_evento = '" . $data_evento . "'  GROUP BY presencas.data_evento DESC";
            $resultado = $this->db->query($query);
            if (!empty($resultado)) {
                $hora_entrada = date('H:i:s', strtotime($resultado[0]['data_entrada']));
                if ($hora_entrada) {
                    $query = "SELECT valor FROM logica_atrasos WHERE (logica_atrasos.de <= '" . $hora_entrada . "' AND logica_atrasos.ate >= '" . $hora_entrada . "')  ";
                    $resultado = $this->db->query($query);

                    $eventos_return['descricao'] = "<b>" . $data_evento . "</b>: " . $hora_entrada;
                    $eventos_return['comissao'] = $resultado[0]['valor'];
                    return $eventos_return;
                }
            }
        }
    }

    function devolveComissaoPrivados($id_rp, $data_evento)
    {
        $query = "SELECT sum(venda_privados_garrafas.quantidade) as quantidade, SUM(venda_privados.total) as total FROM venda_privados INNER JOIN venda_privados_garrafas ON venda_privados_garrafas.id_compra = venda_privados.id  INNER JOIN garrafas ON venda_privados_garrafas.id_garrafa = garrafas.id AND garrafas.comissao = 1 WHERE venda_privados.id_rp = " . $id_rp . " AND venda_privados.data_evento = '" . $data_evento . "' AND venda_privados.total > 50 GROUP BY venda_privados.data_evento DESC";
        $resultado = $this->db->query($query);

        $query = "SELECT SUM(venda_privados.total) as total , SUM(venda_privados.numero_cartoes) as total_cartoes FROM venda_privados  WHERE venda_privados.id_rp = " . $id_rp . " AND venda_privados.data_evento = '" . $data_evento . "' AND venda_privados.total > 50 GROUP BY venda_privados.data_evento";
        $resultado2 = $this->db->query($query);


        if ($resultado) {
            $return['comissao'] = $resultado2[0]['total'] * 0.10;
            $return['descricao'] = "<b>" . $data_evento . "</b>: Total: " . euro($resultado2[0]['total']) . " - " . intval($resultado[0]['quantidade']) . " garrafas" . " - " . intval($resultado2[0]['total_cartoes']) . " cartões;";
            $return['total'] = ($resultado2[0]['total']);
            $return['total_cartoes'] = ($resultado2[0]['total_cartoes']);


			if(in_array($data_evento,  $this->faltas_rp) && $return['comissao'] > 0) {
				$this->faltas_euros[$data_evento] += $return['comissao'];
			}

            return $return;
        }
    }
    function devolveComissaoGarrafas($id_rp)
    {
        $query = "SELECT sum(venda_garrafas_bar_garrafas.quantidade) as quantidade, venda_garrafas_bar.data_evento FROM venda_garrafas_bar INNER JOIN venda_garrafas_bar_garrafas ON venda_garrafas_bar_garrafas.id_compra = venda_garrafas_bar.id  INNER JOIN garrafas ON venda_garrafas_bar_garrafas.id_garrafa = garrafas.id AND garrafas.comissao = 1 WHERE venda_garrafas_bar.id_rp = " . $id_rp . " AND venda_garrafas_bar.paga = '0' AND venda_garrafas_bar.total > 50 GROUP BY venda_garrafas_bar.data_evento DESC";
        $resultado = $this->db->query($query);
        if ($resultado) {
            foreach ($resultado as $k => $res) {

				if(in_array($res['data_evento'],  $this->faltas_rp) && $res['quantidade'] > 0) {
					$this->faltas_euros[$res['data_evento']] += $res['quantidade'] * 5;
				}

                $return[$k]['comissao'] = $res['quantidade'] * 5;
                $return[$k]['descricao'] .= "<b>" . $res['data_evento'] . "</b>: " . intval($res['quantidade']) . " garrafas";

            }
            return $return;
        }
    }
    function devolveNomeRP($id_rp)
    {
        $query = "SELECT nome FROM rps  WHERE  id = " . $id_rp . "";
        $eventoRP = $this->db->query($query);
        return $eventoRP[0]['nome'];
    }
    function devolveNomeAdministrador($id)
    {
        $query = "SELECT nome FROM administradores  WHERE  id = " . $id . "";
        $eventoRP = $this->db->query($query);
        return $eventoRP[0]['nome'];
    }
    function devolveSessaoRP($id_rp)
    {
        $query = "SELECT salario FROM rps  WHERE  id = " . $id_rp . "";
        $eventoRP = $this->db->query($query);
        return $eventoRP[0]['salario'];
    }
    function devolveExtras($id_rp, $sessao)
    {
        $query = "SELECT * FROM pagamentos_extras  WHERE  id_rp = '" . $id_rp . "' AND sessao =  '" . $sessao . "'";
        $eventoRP = $this->db->query($query);

        return $eventoRP;
    }
    function devolveValoresExtras($id_rp)
    {

        $query = "SELECT id, valor, descricao, nome, tipo FROM pagamentos_extras  WHERE  id_rp = '" . $id_rp . "'";
        $valores_extras = $this->db->query($query);

        if ($valores_extras) {
            $extras['items'] = $valores_extras;
            $extras['total'] = 0;
            foreach ($valores_extras as $ve) {
                $extras['total'] += $ve['valor'];
            }
        }


        return $extras;
    }
    function apagaExtras($id_rp)
    {
        $query = "DELETE FROM pagamentos_extras  WHERE  id_rp = '" . $id_rp . "'";
        return $this->db->query($query);
    }

    function devolveContaCorrente($id_rp)
    {

        $query = "SELECT id FROM conta_corrente  WHERE id_rp = '" . $id_rp . "'";
        $conta_corrente = $this->db->query($query);
        return $conta_corrente[0]['id'];
    }
    function devolveDividaRP($id_rp)
    {

        $query = "SELECT total FROM conta_corrente  WHERE id_rp = '" . $id_rp . "' ORDER BY data DESC";
        $divida = $this->db->query($query);

        if ($divida[0]['total'] < 0) {
            return $divida[0]['total'];
        }
    }

    function devolveContaCorrenteID($id)
    {
        $query = "SELECT * FROM conta_corrente  WHERE id = '" . $id . "'";
        $conta_corrente = $this->db->query($query);
        return $conta_corrente[0];
    }
    function devolveValoresCaixas($data)
    {
        $query = "SELECT * FROM conta_corrente_caixas  WHERE data = '" . $data . "' ORDER BY numero ASC";
        $res = $this->db->query($query);
        return $res;
    }
    function apagaValoresCaixas($data)
    {
        $query = "DELETE FROM conta_corrente_caixas  WHERE data = '" . $data . "'";
        $res = $this->db->query($query);
        return $res;
    }
    function listaPagamentosCaixaData($data = false)
    {
        $where = "";
        if ($data) {
            $where .= " WHERE data_evento = '" . $data . "' ";
        }

        $query = "SELECT data_evento as data FROM conta_corrente $where   GROUP BY data_evento DESC";
        $pagamentos = $this->db->query($query);
        if ($pagamentos) {
            foreach ($pagamentos as $k => $pagamento) {
                $pagamentos[$k]['total_caixa'] = $this->devolveTotalCaixa($pagamento['data']);
                $pagamentos[$k]['total_pagamentos'] = $this->devolveTotalPago($pagamento['data'], 1);
                $pagamentos[$k]['total_pagamentos_scaixa'] = $this->devolveTotalPago($pagamento['data'], 0);
                $pagamentos[$k]['total_recebido'] = $this->devolveTotalRecebido($pagamento['data']);
                $pagamentos[$k]['diferenca'] = $pagamentos[$k]['total_caixa'] - $pagamentos[$k]['total_pagamentos'];
            }
            return $pagamentos;
        }
    }

    function devolveDiferencaTotalCaixa($data)
    {
        $pagamentos['total_caixa'] = $this->devolveTotalCaixa($data);
        $pagamentos['total_pagamentos'] = $this->devolveTotalPago($data, 1);
        return $pagamentos['total_caixa'] - $pagamentos['total_pagamentos'];
    }

    function devolveTotalCaixa($data)
    {
        $query = "SELECT sum(valor) as total FROM conta_corrente_caixas WHERE data = '" . $data . "'  GROUP BY data";
        $res = $this->db->query($query);
        return $res[0]['total'];
    }
    function devolveTotalPago($data, $caixa = 1)
    {
        $where = "";
        if ($data) {
            $where .= " AND data_evento = '" . $data . "' ";
        }
        $query = "SELECT sum(total) as total FROM conta_corrente WHERE total > 0 $where AND pagamento_caixa = '" . $caixa . "' GROUP BY data_evento";
        $res = $this->db->query($query);
        return $res[0]['total'];
    }
    function devolveTotalRecebido($data)
    {
        $where = "";
        if ($data) {
            $where .= " AND data_evento = '" . $data . "' ";
        }
        $query = "SELECT sum(total) as total FROM conta_corrente WHERE total < 0 $where GROUP BY data_evento";
        $res = $this->db->query($query);
        return $res[0]['total'];
    }
    function listaCaixasData($data)
    {
        // conta_corrente_caixas

        $query = "SELECT numero, valor FROM conta_corrente_caixas WHERE data = '" . $data . "'";
        $res = $this->db->query($query);
        return $res;
    }
    function devolveFiltrosPagamentosCaixa($data, $filtro)
    {

        $query = "";
        $query_inner = "";
        if ($filtro) {
            if ($filtro['nome']) {
                $query_inner .= " LEFT JOIN rps ON rps.id = conta_corrente.id_rp ";
                $query .= " AND (rps.nome like '%" . $filtro['nome'] . "%' OR conta_corrente.nome like '%" . $filtro['nome'] . "%') ";
            }
            if (strlen($filtro['pagamento_caixa']) > 0) {
                $query .= " AND conta_corrente.pagamento_caixa = " . $filtro['pagamento_caixa'];
            }
            if ($filtro['id_administrador']) {
                $query .= " AND conta_corrente.id_administrador = " . $filtro['id_administrador'];
            }
        }

        $query_administradores = "SELECT administradores.id, administradores.nome FROM conta_corrente INNER JOIN administradores ON administradores.id = conta_corrente.id_administrador $query_inner WHERE conta_corrente.data_evento = '" . $data . "' $query GROUP by conta_corrente.id_administrador";
        $filtros['administradores'] = $this->db->query($query_administradores);

        $query_pagamento = "SELECT conta_corrente.pagamento_caixa, IF(conta_corrente.pagamento_caixa = 0, 'Não', 'Sim') as nome FROM conta_corrente  $query_inner WHERE conta_corrente.data_evento = '" . $data . "' $query GROUP BY conta_corrente.pagamento_caixa";
        $filtros['pagamento_caixa'] = $this->db->query($query_pagamento);
        return $filtros;

    }
    function listaExcellPagamentosLinhas($data)
    {

        $query = "SELECT IF(conta_corrente.nome = '', rps.nome, conta_corrente.nome) as nome_staff, administradores.nome as nome_administrador, IF(conta_corrente.pagamento_caixa = 0, 'Não', 'Sim') as pagamento_caixa,  conta_corrente_linhas.nome as titulo_pagamento, conta_corrente_linhas.descricao  as descricao_pagamento, conta_corrente_linhas.valor FROM conta_corrente_linhas INNER JOIN conta_corrente ON conta_corrente_linhas.id_conta_corrente = conta_corrente.id LEFT JOIN rps ON rps.id = conta_corrente.id_rp INNER JOIN administradores ON administradores.id = conta_corrente.id_administrador  WHERE conta_corrente.data_evento = '" . $data . "'  ORDER BY conta_corrente.data ASC";
        $res = $this->db->query($query);
        return $res;

    }
    function listaExcellPagamentosCaixa($data, $filtro = false)
    {
        $query = "";
        $query_inner = "";
        if ($filtro) {
            if ($filtro['nome']) {
                $query_inner .= " LEFT JOIN rps ON rps.id = conta_corrente.id_rp ";
                $query .= " AND (rps.nome like '%" . $filtro['nome'] . "%'  OR conta_corrente.nome like '%" . $filtro['nome'] . "%') ";
            }
            if (strlen($filtro['pagamento_caixa']) > 0) {
                $query .= " AND conta_corrente.pagamento_caixa = " . $filtro['pagamento_caixa'];
            }
            if ($filtro['id_administrador']) {
                $query .= " AND conta_corrente.id_administrador = " . $filtro['id_administrador'];

            }
        }

        $query = "SELECT conta_corrente.id, conta_corrente.id_administrador, conta_corrente.pagamento_caixa, conta_corrente.nome, conta_corrente.tipo, conta_corrente.id_rp, conta_corrente.data, conta_corrente.total FROM conta_corrente $query_inner WHERE conta_corrente.data_evento = '" . $data . "' $query ORDER BY conta_corrente.data ASC";
        $res = $this->db->query($query);
        foreach ($res as $k => $rs) {
            if ($rs['id_rp']) {
                $res[$k]['nome'] = $this->devolveNomeRP($rs['id_rp']);
            }
            if ($rs['id_administrador']) {
                $res[$k]['nome_administrador'] = $this->devolveNomeAdministrador($rs['id_administrador']);
            }
            if (empty($rs['tipo']) && $rs['id_rp']) {
                $res[$k]['tipo'] = "Pagamento Staff";
            }
        }
        return $res;
    }
    function listaPresencasEventos($filtros = false, $limit = false)
    {
        $query = "SELECT data_evento, count(*) as conta FROM presencas GROUP BY data_evento ORDER BY data_evento DESC $limit";
        $res = $this->db->query($query);
        return $res;
    }
    function contaPresencasEventos($filtros = false)
    {
        $query = "SELECT count(DISTINCT data_evento)  as conta FROM presencas  ORDER BY data_evento DESC";
        $res = $this->db->query($query);
        return $res[0]['conta'];
    }

    function listaPresencasEventosData($data, $filtro = false)
    {

        $query_search = "";
        $query_inner = "";
        if ($filtro) {
            if ($filtro['nome']) {
                $query_inner .= " LEFT JOIN rps ON rps.id = presencas.id_rp ";
                $query_search .= " AND ( rps.nome like '%" . $filtro['nome'] . "%' OR presencas.nome like '%" . $filtro['nome'] . "%' )";
            }
            if ($filtro['numero_cartao']) {
                $query_search .= " AND presencas.numero_cartao = " . $filtro['numero_cartao'];
            }
            if ($filtro['data_de']) {
                $query_search .= " AND presencas.data_entrada >= '" . $filtro['data_de'] . "'";
            }
            if ($filtro['data_ate']) {
                $query_search .= " AND presencas.data_entrada <= '" . $filtro['data_ate'] . "'";
            }
        }

        $query = "SELECT presencas.data_evento, presencas.nome, presencas.data_entrada, presencas.numero_cartao, presencas.id_rp, presencas.id FROM presencas  $query_inner WHERE presencas.data_evento = '" . $data . "'  $query_search ORDER BY presencas.data_entrada DESC";
        $res = $this->db->query($query);
        foreach ($res as $k => $rs) {
            if (empty($rs['nome'])) {
                $res[$k]['nome'] = $this->devolveNomeRP($rs['id_rp']);
            }
        }
        return $res;
    }
    function apagaContaCorrente($id)
    {
        if (intval($id) > 0) {

            $query = "DELETE FROM conta_corrente WHERE id = '" . $id . "'";
            $res = $this->db->query($query);

            $query = "DELETE FROM conta_corrente_eventos WHERE id_conta_corrente = '" . $id . "'";
            $res = $this->db->query($query);

            $query = "DELETE FROM conta_corrente_linhas WHERE id_conta_corrente = '" . $id . "'";
            $res = $this->db->query($query);
        }
        return $res;
    }
    function listaLinhasContaCorrente($id)
    {
        $query = "SELECT nome, descricao, valor FROM conta_corrente_linhas WHERE id_conta_corrente = '" . $id . "'  ORDER BY id ASC";
        $res = $this->db->query($query);
        return $res;
    }

    function listaConvitesEventos($filtros = false, $limit = false)
    {
        $query = "SELECT data_evento, count(*) as conta FROM convites GROUP BY data_evento ORDER BY data_evento DESC $limit";
        $res = $this->db->query($query);
        return $res;
    }

    function contaConvitesEventos($filtros = false)
    {
        $query = "SELECT  count(DISTINCT data_evento) as conta FROM convites ORDER BY data_evento DESC";
        $res = $this->db->query($query);
        return $res[0]['conta'];
    }

    function listaConvitesEventosData($data)
    {
        $query = "SELECT data_evento, data, imagem, id_rp, id, valido FROM convites WHERE data_evento = '" . $data . "'  ORDER BY data DESC";
        $res = $this->db->query($query);
        foreach ($res as $k => $rs) {
            $res[$k]['nome'] = $this->devolveNomeRP($rs['id_rp']);
        }

        return $res;
    }
    function devolveConvite($id)
    {
        $query = "SELECT data_evento, data, imagem, id_rp, id, valido FROM convites WHERE id = '" . $id . "' ";
        $res = $this->db->query($query);
        foreach ($res as $k => $rs) {
            $res[$k]['nome'] = $this->devolveNomeRP($rs['id_rp']);
        }
        return $res[0];
    }
    function verificaErro($id)
    {

        $query = "SELECT total FROM conta_corrente WHERE id = " . $id;
        $res = $this->db->query($query);
        $total = $res[0]['total'];


        $query = "SELECT sum(valor) as total FROM conta_corrente_linhas WHERE id_conta_corrente = '" . $id . "'  ORDER BY id ASC";
        $res = $this->db->query($query);
        $total_real = $res[0]['total'];
        if ($total_real != $total) {
            return 1;
        } else {
            return 0;
        }
    }
    function descontaValorCaixa()
    {
        $query = "SELECT pagamento_caixa FROM administradores WHERE id = '" . $_SESSION['id_utilizador'] . "' ";
        $res = $this->db->query($query);

        return $res[0]['pagamento_caixa'];
    }
}