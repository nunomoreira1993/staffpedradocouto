<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/config.php";
require_once($_SERVER['DOCUMENT_ROOT'] . "/plugins/dompdf/autoload.inc.php"); // DomPdf Library

require_once($_SERVER['DOCUMENT_ROOT'] . '/administrador/privados/privados.obj.php');
$dbprivados = new privados($db);
$entrada = $dbprivados->devolveEntradaPrivados($_GET['id']);
use Dompdf\Dompdf;
$dompdf = new Dompdf();

ob_start();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Pedra do Couto - Gestão de RP's &amp; Promotores</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0" />
    <style>
        @page { margin: 10px; }
        body { margin: 10px; }
        body, html, * {
            font-family: "Lucida Console", Monaco, monospace;
        }
        .topo .logotipo {
            display: block;
            text-align: center;
            margin-bottom: 15px;
        }
        .topo .logotipo img {
            max-width: 150px;
        }
        h1 {
            margin: 0;
        }
        hr {
            margin: 15px 0;

        }
        .reserva {
        }
        .reserva .bloco{
            font-size: 13px;
            margin: 10px 0;
        }
        .reserva .bloco .label{
            font-weight: 400;
            width: 100%;
            display: block;

        }
        .reserva .bloco .valor{
            font-weight: 700;
            width: 100%;
            display: block;
        }
        .rodape {
            display: block;
            text-align: center;
            font-style: italic;
        }
    </style>
</head>
<?php
$data = file_get_contents($_SERVER["DOCUMENT_ROOT"] . '/temas/administrador/imagens/logotipo.png');
$base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
?>
<body>
    <div class="topo">
        <span class="logotipo"><img src="<?php echo $base64; ?>" /> </span>
        <h1 style="text-align: center; width: 100%; font-size: 20px;"> ENTRADA DE PRIVADOS </h1>
    </div>
    <hr/>
    <div class="reserva">

        <div class="bloco">
            <span class="label">
                Data da entrada:
            </span>
            <span class="valor">
                <?php echo date('d-m-Y H:i:s', strtotime($entrada['data'])); ?>
            </span>
        </div>
        <div class="bloco">
            <span class="label">
               Entrada Número:
            </span>
            <span class="valor">
                <?php echo $entrada['id']; ?>
            </span>
        </div>
    </div>
    <hr>
    <div class="reserva">
        <div class="bloco">
            <span class="label">
                Nº Cartões:
            </span>
            <span class="valor">
                <?php echo $entrada['cartoes']; ?>
            </span>
        </div>

        <div class="bloco">
            <span class="label">
                Mesa:
            </span>
            <span class="valor">
                <?php echo $entrada['sala']; ?> - <?php echo strtoupper($entrada['mesa']); ?>
            </span>
        </div>
    </div>
    <hr>
    <span class="rodape">#ENTRADA DE PRIVADOS#</span>
</body>
<?php
$contents = ob_get_contents();
ob_end_clean();

$dompdf->loadHtml($contents);
// (Optional) Setup the paper size and orientation
$customPaper = array(0,0,227,280);
$dompdf->setPaper($customPaper);

// Render the HTML as PDF
$dompdf->render();

// Output the generated PDF to Browser
$dompdf->stream("entrada_privados_" . $_GET["id"] . ".pdf", array("Attachment" => false));

exit(0);
die;
