<?php
class methods {
    private $host = "https://www.staffpedradocouto.com/ajax/methods/";
    private $hash;
    private $hash_evento;
    private $hash_rp;
    private $cartao;
    private $tipo_cartao;
    function __construct($hash = "", $evento = "", $rp = "", $cartao = "", $tipo_cartao = ""){
        $this->hash = $hash;
        $this->hash_evento = $evento;
        $this->hash_rp = $rp;
        $this->cartao = $cartao;
        $this->tipo_cartao = $tipo_cartao;
    }
    public function sendForm($fields) {
        return $this->post("sendForm.php", $fields);
    }
    public function getCartaoByHash() {
        return $this->get("cartao.php");
    }
    public function getConviteByHash() {
        return $this->get("convite.php");
    }
    public function getEventoByID($id_evento) {
        return $this->get("eventoById.php", array("id_evento"=> $id_evento));
    }
    public function getEventoByMD5() {
        return $this->get("eventoByMD5.php");
    }
    public function getNomeRP($id_rp) {
        return $this->get("rpNome.php", array("id_rp"=> $id_rp));
    }

    public function getRPByMD5() {
        return $this->get("getRPByMD5.php");
    }

    public function get($url, $params = array()) {
        return $this->executeRequest($url, 'GET', $params);
    }

    public function post($url, $params = array()) {
        return $this->executeRequest($url, 'POST', $params);
    }

    private function executeRequest($url, $method, $params) {
        // Inicializa a sessão cURL
        $curl = curl_init();

        // Constrói a URL completa para a requisição
        $fullUrl = $this->host.$url;

        $params["hash"] = $this->hash;
        $params["evento"] = $this->hash_evento;
        $params["rp"] = $this->hash_rp;
        $params["tipo_cartao"] = $this->tipo_cartao;
        $params["cartao"] = $this->cartao;

        if ($method === 'GET' && !empty($params)) {
            $fullUrl .= '?' . http_build_query($params);
        }

        // Configura as opções da requisição cURL
        curl_setopt($curl, CURLOPT_URL, $fullUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        if ($method === 'POST') {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
        }

        // Desabilita a verificação do certificado SSL
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        // Desabilita a verificação do nome do host SSL
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        // Executa a requisição cURL
        $response = curl_exec($curl);

        // Verifica por erros na requisição cURL
        if(curl_errno($curl)) {
            $error_msg = curl_error($curl);
            curl_close($curl);
            throw new Exception("Erro na requisição cURL: $error_msg");
        }

        // Fecha a sessão cURL
        curl_close($curl);
        return json_decode($response, true);
    }
    function formatarDataPortugues($data) {
        // Array associativo para traduzir os nomes dos dias da semana
        $dias_semana = array(
            'Sunday' => 'Domingo',
            'Monday' => 'Segunda-feira',
            'Tuesday' => 'Terça-feira',
            'Wednesday' => 'Quarta-feira',
            'Thursday' => 'Quinta-feira',
            'Friday' => 'Sexta-feira',
            'Saturday' => 'Sábado'
        );

        // Array associativo para traduzir os nomes dos meses
        $meses = array(
            'January' => 'Janeiro',
            'February' => 'Fevereiro',
            'March' => 'Março',
            'April' => 'Abril',
            'May' => 'Maio',
            'June' => 'Junho',
            'July' => 'Julho',
            'August' => 'Agosto',
            'September' => 'Setembro',
            'October' => 'Outubro',
            'November' => 'Novembro',
            'December' => 'Dezembro'
        );

        // Converter a data para o formato desejado
        $dataFormatada = date('l, d \d\e F \d\e Y', strtotime($data));
        $dataFormatada = strtr($dataFormatada, $dias_semana);
        $dataFormatada = strtr($dataFormatada, $meses);

        return $dataFormatada;
    }
}
?>