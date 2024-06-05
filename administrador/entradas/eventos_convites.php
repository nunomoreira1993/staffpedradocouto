<?php
if (empty($_SESSION['id_utilizador'])) {
    header('Location:/index.php');
    exit;
}
$id_evento = $_GET['id'];

if (empty($_GET['id'])) {
    header('Location: /administrador/index.php?pg=eventos');
    exit;
}

$quantidade = 20;
$pagina = intval($_GET['p']);
$limit = devolveLimit(array('pagina' => $pagina, 'numero' => $quantidade));

if ($_GET['nome_rp'] || $_GET['nome_cliente'] || $_GET['gerou_bilhete'] || $_GET['entrada']) {
    $filtro['nome_rp'] = $_GET['nome_rp'];
    $filtro['nome_cliente'] = $_GET['nome_cliente'];
    $filtro['gerou_bilhete'] = $_GET['gerou_bilhete'];
    $filtro['entrada'] = $_GET['entrada'];
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/administrador/eventos/evento.obj.php');
$dbevento = new evento($db);
$convites = $dbevento->listaConvites($id_evento, $filtro, $limit);
$conta = $dbevento->countConvites($id_evento, $filtro, $limit);
?>

<h1 class="titulo"> Convites </h1>
<div class="content" <?php echo escreveErroSucesso(); ?>>
    <form class="filtros" name="filtros" action="" method="GET">
        <input type="hidden" name="id" value="<?php echo $id_evento; ?>" />
        <input type="hidden" name="pg" value="<?php echo $pg; ?>" />
        <input type="hidden" name="p" value="<?php echo $p < 1 ? 1 : $p; ?>" />
        <div class="filtro">
            <span class="nome">Nome RP:</span>
            <span class="input"><input type="text" name="nome_rp" value="<?php echo $filtro['nome_rp']; ?>" /></span>
        </div>
        <div class="filtro">
            <span class="nome">Nome Cliente:</span>
            <span class="input"><input type="text" name="nome_cliente" value="<?php echo $filtro['nome_cliente']; ?>" /></span>
        </div>
        <div class="filtro">
            <span class="nome">Gerou bilhete?:</span>
            <span class="input">
                <select name="gerou_bilhete">
                    <option value=""></option>
                    <option value="1" <?php echo $filtro['gerou_bilhete'] == 1 ? "selected='selected'" : ""; ?>>Sim</option>
                    <option value="2" <?php echo $filtro['gerou_bilhete'] == 2 ? "selected='selected'" : ""; ?>>Não</option>
                </select>
            </span>
        </div>
        <div class="filtro">
            <span class="nome">Deu entrada?:</span>
            <span class="input">
                <select name="entrada">
                    <option value=""></option>
                    <option value="1" <?php echo $filtro['entrada'] == 1 ? "selected='selected'" : ""; ?>>Sim</option>
                    <option value="2" <?php echo $filtro['entrada'] == 2 ? "selected='selected'" : ""; ?>>Não</option>
                </select>
            </span>
        </div>
        <input type="submit" value="Filtrar" />
        <a href="/administrador/index.php?pg=<?php echo $_GET['pg']; ?>&p=<?php echo $_GET['p']; ?>" class="clean"> Limpar filtros </a>
    </form>

    <div style="margin-bottom:20px" class="filtros">
        <span class="registos"> Foram encontrados <b> <?php echo $conta; ?> </b> registos. </span>
    </div>

    <?php
    echo devolvePaginacao($pagina, $conta, $quantidade);
    ?>
    <div class="table-responsive">
        <table cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th>RP</th>
                    <th>Nome Cliente</th>
                    <th>Data de Convite</th>
                    <th>Método de convite</th>
                    <th>Estado do convite</th>
                    <th>Gerou bilhete?</th>
                    <th>Deu entrada?</th>
                    <th>Data entrada</th>
                    <th class="text-nowrap"></th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (empty($convites)) {
                ?>
                    <td colspan="7">
                        Sem convites gerados.
                    </td>
                <?php

                }
                foreach ($convites as $convite) {
                ?>
                    <tr>
                        <td><?php echo $convite['nome_rp']; ?></td>
                        <td><?php echo $convite['convite_nome']; ?></td>
                        <td><?php echo $convite['convite_data']; ?></td>
                        <td> <?php echo ($convite['convite_tipo'] == 1) ? "Telémovel" : (($convite['convite_tipo'] == 2) ? "Whatsapp" : ($convite['convite_tipo'] == 3 ? " E-mail" : "Automático") ); ?></td>
                        <td> <?php echo $convite["convite_status"] == "sucesso" ? "Enviado" : "Erro"; ?></td>
                        <td> <?php echo $convite["qrcode"] > 0 ? "Sim" : "Não"; ?></td>
                        <td> <?php echo $convite["qrcode_entrada"] > 0 ? "Sim" : "Não"; ?></td>
                        <td> <?php echo $convite["qrcode_entrada_data"]; ?></td>
                        <td class="text-nowrap">
                        </td>
                    </tr>
                <?php
                }
                ?>

            </tbody>
        </table>
    </div>
</div>