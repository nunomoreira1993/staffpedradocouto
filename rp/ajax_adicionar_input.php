<?php
$incremento = $_GET['i'];
?>
<div class="bloco">
    <a href="#" class="remover">
        <img src="/temas/rps/imagens/remover.svg"/>
    </a>
    <div class="inputs">
        <div class="label">
            Nome do cliente
        </div>
        <div class="input">
            <input name="input[<?php echo $incremento; ?>][nome]" value="" type="text" required="required" />
        </div>
    </div>
    <?php
    if($_GET["sem_consumo"] == 1) {
        ?>
        <div class="inputs">
            <div class="label">
                Número de Bebidas
            </div>
            <div class="input">
                <input name="input[<?php echo $incremento; ?>][bebidas]" value="" type="number" min="0" max="4" />
            </div>
        </div>
        <?php
    }
    ?>
</div>