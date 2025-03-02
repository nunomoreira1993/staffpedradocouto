<?php
include $_SERVER["DOCUMENT_ROOT"] . "/plugins/phpbarcode/barcode.php";

$generator = new barcode_generator();
$s = $_GET["s"];
$d = $_GET["d"];
/* Generate SVG markup and write to standard output. */
header('Content-Type: image/svg+xml');
$svg = $generator->render_svg($s, $d, $options);
echo $svg;

