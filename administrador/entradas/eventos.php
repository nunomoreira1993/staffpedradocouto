<?php
if (empty($_SESSION['id_utilizador'])) {
    header('Location:/index.php');
    exit;
}
require_once($_SERVER['DOCUMENT_ROOT'] . '/administrador/eventos/evento.obj.php');
$dbevento = new evento($db);

$quantidade = 20;
$pagina = intval($_GET['p']);
$limit = devolveLimit(array('pagina' => $pagina, 'numero' => $quantidade));

if ($_GET['nome'] || $_GET['telemovel']) {
    $filtro['nome'] = $_GET['nome'];
    $filtro['telemovel'] = $_GET['telemovel'];
}

$eventos = $dbevento->listaEventos($filtro, $limit);
$conta = $dbevento->countEventos($filtro);

if ($_GET['apagar'] == 1 && $_GET['id'] > 0) {

    $evento = $dbevento->devolveEvento($_GET['id']);
    if ($evento) {
        if ($evento['foto'] && file_exists($_SERVER['DOCUMENT_ROOT'] . "/fotos/eventos/" . $evento['foto'])) {
            var_dump(unlink($_SERVER['DOCUMENT_ROOT'] . "/fotos/eventos/" . $evento['foto']));
        }
        $query = 'DELETE FROM eventos WHERE id=' . $_GET['id'];
        $db->query($query);
        $db->Insert('logs', array('descricao' => "Apagou um evento", 'arr' => json_encode($evento), 'id_admin' => $_SESSION['id_utilizador'], 'tipo' => "apagar", 'user_agent' => $_SERVER['HTTP_USER_AGENT'], 'ip' => $_SERVER['REMOTE_ADDR']));
        $_SESSION['sucesso'] = "O evento foi apagado.";
        header('Location: /administrador/index.php?pg=eventos');
        exit;
    }
}
?>
<h1 class="titulo"> Eventos <a href="?pg=inserir_evento&id=0"> Criar novo </a> </h1>
<div class="content" <?php echo escreveErroSucesso(); ?>>
    <form class="filtros" name="filtros" action="" method="GET">
        <input type="hidden" name="pg" value="<?php echo $pg; ?>"/>
        <input type="hidden" name="p" value="<?php echo $p<1?1:$p; ?>"/>
        <div class="filtro">
            <span class="nome">Nome:</span>
            <span class="input"><input type="text" name="nome" value="<?php echo $filtro['nome']; ?>" /></span>
        </div>
        <input type="submit" value="Filtrar" />
        <a href="/administrador/index.php?pg=<?php echo $_GET['pg']; ?>&p=<?php echo $_GET['p']; ?>" class="clean"> Limpar filtros </a>
    </form>
    <a href="/administrador/exportar/exportar_clientes.php" class="exportar-excell"> Exportar dados de clientes </a>
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
                    <th>Nome</th>
                    <th>Data</th>
                    <th>Imagem</th>
                    <th>Nº de convites enviados</th>
                    <th>Nº de bilhetes gerados</th>
                    <th class="text-nowrap"></th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (empty($eventos)) {
                ?>
                    <td colspan="6">
                        Sem registos inseridos.
                    </td>
                <?php

                }
                foreach ($eventos as $evento) {
                    $contaConvites = $dbevento->contaConvites($evento["id"]);
                    $contaBilhetes = $dbevento->contaBilhetes($evento["id"]);
                ?>
                    <tr>
                        <td><?php echo $evento['nome']; ?></td>
                        <td><?php echo $evento['data']; ?></td>
                        <td>
                            <?php
                            if ($evento['imagem'] && file_exists($_SERVER['DOCUMENT_ROOT'] . "/fotos/eventos/" . $evento['imagem'])) {
                            ?>
                                <img src="/fotos/eventos/<?php echo $evento['imagem']; ?>" width="200px">
                            <?php

                            }
                            ?>
                        </td>
                        <td><?php echo $contaConvites; ?></td>
                        <td><?php echo $contaBilhetes; ?></td>
                        <td class="text-nowrap">
                            <div class="opcoes">
                                <a href="/administrador/exportar/exportar_evento_tickets.php?id=<?php echo $evento['id']; ?>" class="exportar-excell"> Exportar dados de clientes </a>
                                <a href="?pg=eventos_convites&id=<?php echo $evento['id']; ?>" class="entradas"> Ver convites / bilhetes</a>
                                <a href="?pg=inserir_evento&id=<?php echo $evento['id']; ?>" class="editar"> <i class="fa fa-pencil text-inverse m-r-10"></i> </a>
                                <a href="?pg=eventos&apagar=1&id=<?php echo $evento['id']; ?>" class="apagar"> <i class="fa fa-close text-danger"></i> </a>
                            </div>
                        </td>
                    </tr>
                <?php
                }
                ?>

            </tbody>
        </table>
    </div>
</div>