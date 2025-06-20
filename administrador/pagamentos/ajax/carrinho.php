<?php
include_once $_SERVER['DOCUMENT_ROOT'] . "/lib/config.php";
if (empty($_SESSION['id_utilizador'])) {
    header('Location:/index.php');
    exit;
}
$id_rp = intval($_GET['id_rp']);
if (date('H') < 14) {
    $data = date('Y-m-d', strtotime('-1 day'));
} else {
    $data = date('Y-m-d');
}

if(empty($pagamento)){
	require_once($_SERVER['DOCUMENT_ROOT'] . '/administrador/pagamentos/pagamentos.obj.php');
	$dbpagamentos = new pagamentos($db);
	$pagamento = $dbpagamentos->devolvePagamento($id_rp);
}

if ($pagamento['divida']) {
    ?>

    <div class="linha debito">
        <span class="topo">
            <span class="nome">
                Dívida
            </span>
            <span class="valor">
                <?php echo euro($pagamento['divida']); ?>
            </span>
        </span>
    </div>
    <?php
}
if ($pagamento['guest']) {
    ?>
    <div class="linha credito">
        <span class="topo">
            <span class="nome">
                Comissão Guest
            </span>
            <span class="valor">
                <?php echo euro($pagamento["guest"]["comissao"]) ?>
            </span>
        </span>
        <span class="descricao">
            <?php echo $pagamento["guest"]["descricao"] ?>
        </span>
    </div>
    <?php
}

if ($pagamento['equipa']) {
    ?>
    <div class="linha credito">
        <span class="topo">
            <span class="nome">
                Equipa
            </span>
            <span class="valor">
                <?php echo euro($pagamento["equipa"]["comissao"]) ?>
            </span>
        </span>
        <span class="descricao">
            <?php echo $pagamento["equipa"]["descricao"] ?>
        </span>
    </div>
    <?php
}
if ($pagamento['privados']) {
    ?>
    <div class="linha credito">
        <span class="topo">
            <span class="nome">
                Privados
            </span>
            <span class="valor">
                <?php echo euro($pagamento["privados"]["comissao"]) ?>
            </span>
        </span>
        <span class="descricao">
            <?php echo $pagamento["privados"]["descricao"] ?>
        </span>
    </div>
    <?php
}
if ($pagamento['garrafas']) {
    ?>
    <div class="linha credito">
        <span class="topo">
            <span class="nome">
                Garrafas Bar
            </span>
            <span class="valor">
                <?php echo euro($pagamento["garrafas"]["comissao"]) ?>
            </span>
        </span>
        <span class="descricao">
            <?php echo $pagamento["garrafas"]["descricao"] ?>
        </span>
    </div>
    <?php
}
if ($pagamento['atrasos']) {
    ?>
    <div class="linha debito">
        <span class="topo">
            <span class="nome">
                Atraso
            </span>
            <span class="valor">
                <?php echo euro($pagamento['atrasos']['comissao']); ?>
            </span>
        </span>
        <span class="descricao">
            <?php echo ($pagamento['atrasos']['descricao']); ?>
        </span>
    </div>
    <?php
}
if ($pagamento['convites']) {
    ?>

    <div class="linha debito">
        <span class="topo">
            <span class="nome">
                Penalização convite
            </span>
            <span class="valor">
                <?php echo euro($pagamento['convites']['comissao']); ?>
            </span>
        </span>
        <span class="descricao">
            <?php echo ($pagamento['convites']['descricao']); ?>
        </span>
    </div>
    <?php
}
if ($pagamento['faltas']) {

    ?>

    <div class="linha debito">
        <span class="topo">
            <span class="nome">
                Penalização faltas
            </span>
            <span class="valor">
                <?php echo euro(array_sum($pagamento['faltas'])); ?>
            </span>
        </span>
        <span class="descricao">
			<?php
			foreach ($pagamento['faltas'] as $data_falta => $falta) {
				?>
				<b><?php echo $data_falta; ?></b> - <?php echo euro($falta); ?>
				<br/>
				<?php
			}
			?>

        </span>
    </div>
    <?php
}
if ($pagamento['extras']) {
    foreach ($pagamento['extras']['items'] as $items) {
        ?>
        <div class="linha <?php if ($items['valor'] > 0) { ?>credito  <?php } else { ?>debito <?php } ?>">
            <span class="topo">
                <span class="nome">
                    Extra
                </span>
                <span class="valor">
                    <?php echo euro(abs($items['valor'])); ?>
                </span>
            </span>
            <span class="descricao">
                <?php echo ($items['descricao']); ?>
            </span>
        </div>
        <?php
    }
}

?>
<div class="total">
    <span class="titulo">
        Total
    </span>
    <span class="valor">
        <?php echo euro($pagamento['total']); ?>
    </span>
</div>

<?php
if ($dbpagamentos->devolveDiferencaTotalCaixa($data) < $pagamento['total'] && $dbpagamentos->descontaValorCaixa() == 1) {
    ?>
    <span class="alerta_erro">
        <span class="titulo">Não é possivel efectuar o pagamento.</span>
        O total de pagamento é superior ao valor de caixa. Por favor adicione mais fundos na caixa para efectuar o
        pagamento.
    </span>
    <?php
} else {
    ?>
    <a href="/administrador/pagamentos/finalizar_pagamento.php?id_rp=<?php echo $id_rp; ?>" class="finalizar"> Finalizar
        Pagamento </a>
    <?php
}
?>