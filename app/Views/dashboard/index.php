<?php
/**
 * @var int $total
 * @var int $respondidas
 * @var int $pendentes
 * @var float|null $media_nps_mes_atual
 * @var float|null $media_notas_detratoras_mes_atual
 */
?>
<?php ob_start(); ?>

<div class="row">
    <div class="col-12">
        <h2>Dashboard</h2>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-3">
        <div class="card text-center nps-stat-card">
            <div class="card-body">
                <h5 class="card-title"><?= $total ?></h5>
                <p class="card-text">Total de Pesquisas do Mês</p>
                <small><?= date('m/Y') ?></small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center text-white bg-success nps-stat-card">
            <div class="card-body">
                <h5 class="card-title"><?= $respondidas ?></h5>
                <p class="card-text">Respondidas no Mês</p>
                <small><?= date('m/Y') ?></small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center text-white bg-warning nps-stat-card">
            <div class="card-body">
                <h5 class="card-title"><?= $pendentes ?></h5>
                <p class="card-text">E-mails Pendentes</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <?php
        $mediaNps = $media_nps_mes_atual;
        $mediaNpsClass = 'bg-secondary text-white';

        if ($mediaNps !== null) {
            $mediaNpsClass = $mediaNps >= 9 ? 'bg-success text-white' : ($mediaNps >= 7 ? 'bg-warning text-dark' : 'bg-danger text-white');
        }
        ?>
        <div class="card text-center nps-stat-card <?= $mediaNpsClass ?>">
            <div class="card-body">
                <h5 class="card-title">
                    <?= $mediaNps !== null ? number_format($mediaNps * 10, 1, ',', '.') : '-' ?>
                </h5>
                <p class="card-text mb-1">Média NPS do Mês</p>
                <small><?= date('m/Y') ?></small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center text-white bg-danger nps-stat-card">
            <div class="card-body">
                <h5 class="card-title">
                    <?= $media_notas_detratoras_mes_atual !== null ? number_format($media_notas_detratoras_mes_atual * 10, 1, ',', '.') : '-' ?>
                </h5>
                <p class="card-text mb-1">Média Notas Detratoras</p>
                <small><?= date('m/Y') ?></small>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Dashboard';
require __DIR__ . '/../layouts/admin.php';
?>
