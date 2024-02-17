<?php
if (empty($_SESSION['id_utilizador'])) {
    header('Location:/index.php');
    exit;
}
$data = $_GET['data'];
require_once($_SERVER['DOCUMENT_ROOT'] . '/administrador/rps/rps.obj.php');
$dbrps = new rps($db);
$entradasRPData = $dbrps->listaEntradasRPSDia($data);
?>
<h1 class="titulo"> Lista de entradas por RP do evento do dia <?php echo $data; ?></h1>
<div class="content" <?php echo escreveErroSucesso(); ?>>
    <div class="table-responsive">
        <table cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th>Nome do STAFF</th>
                    <th>Nº de Entradas</th>
                    <th>Total S/Consumo</th>
                    <th>Total de C/Obrigatório</th>
                    <th>Total Entradas (€)</th>
                    <th>Total V/ Privados (€) </th>
                    <th>Total V/ Garrafas (€) </th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (empty($entradasRPData)) {
                    ?>
                    <td colspan="5">
                        Sem registos inseridos.
                    </td>
                <?php

            }
            foreach ($entradasRPData as $rpp) {
                ?>
                    <tr>
                        <td><?php echo $rpp['nome']; ?></td>
                        <td><?php echo $rpp['total']; ?></td>
                        <td><?php echo $rpp['total_sem_consumo']; ?></td>
                        <td><?php echo $rpp['total_cartoes_consumo_obrigatorio']; ?></td>
                        <td><?php echo euro($rpp['total_entradas']); ?></td>
                        <td><?php echo euro($rpp['total_privados']); ?></td>
                        <td><?php echo euro($rpp['total_garrafas']); ?></td>

                    </tr>
                <?php
            }
            ?>

            </tbody>
        </table>
    </div>
</div>