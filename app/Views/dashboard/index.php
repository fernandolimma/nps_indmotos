<?php
/**
 * @var int $total
 * @var int $respondidas
 * @var float|null $media_nps_mes_atual
 * @var float|null $media_notas_detratoras_mes_atual
 * @var array{
 *     media_nps: ?float,
 *     total_respostas: int,
 *     meses: list<array{mes: string, media_nps: ?float, total_respostas: int}>
 * } $historico_nps
 */
$mediaNps = $media_nps_mes_atual;
$notaDoIndice = $mediaNps !== null ? max(0, min(100, $mediaNps * 10)) : 0;
$statusIndice = $mediaNps === null
    ? 'Sem avaliações'
    : ($mediaNps >= 9 ? 'Excelente' : ($mediaNps >= 7 ? 'Bom' : 'Atenção'));
$mediaNpsSeisMeses = $historico_nps['media_nps'];
$inicioPeriodo = $historico_nps['meses'][0]['mes'] ?? date('Y-m');
$fimPeriodo = $historico_nps['meses'][5]['mes'] ?? date('Y-m');
?>
<?php ob_start(); ?>

<style>
    .nps-dashboard .card {
        height: 100%;
        border: 0;
        border-radius: 1.25rem;
        box-shadow: 0 0.35rem 1.25rem rgba(32, 54, 74, 0.07);
    }

    .nps-dashboard .nps-card-label {
        color: #78838d;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .nps-dashboard .nps-balance-value {
        color: #203648;
        font-size: clamp(1.75rem, 3vw, 2.4rem);
        font-weight: 700;
        letter-spacing: -0.04em;
        line-height: 1.1;
    }

    .nps-history-chart {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        align-items: end;
        gap: clamp(0.45rem, 2vw, 1.1rem);
        height: 12.5rem;
        margin-top: 1.5rem;
    }

    .nps-chart-column {
        display: flex;
        height: 100%;
        min-width: 0;
        flex-direction: column;
        justify-content: flex-end;
        text-align: center;
    }

    .nps-chart-value {
        min-height: 1.4rem;
        color: #52616b;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .nps-chart-track {
        position: relative;
        height: 9rem;
        overflow: hidden;
        border-radius: 0.65rem;
        background: #f0f4f3;
    }

    .nps-chart-bar {
        position: absolute;
        right: 0;
        bottom: 0;
        left: 0;
        border-radius: 0.65rem 0.65rem 0 0;
        background: linear-gradient(180deg, #a9ddd2 0%, #ccebe4 100%);
    }

    .nps-chart-bar.is-empty {
        background: #dfe6e5;
    }

    .nps-chart-month {
        margin-top: 0.55rem;
        color: #78838d;
        font-size: 0.7rem;
        white-space: nowrap;
    }

    .nps-performance-card {
        background: linear-gradient(155deg, #ffffff 15%, #f5f8fc 100%);
    }

    .nps-gauge {
        display: block;
        width: min(100%, 19rem);
        margin: 0 auto;
    }

    .nps-gauge-score {
        fill: #17212b;
        font-size: 2rem;
        font-weight: 700;
        text-anchor: middle;
    }

    .nps-gauge-status {
        color: #203648;
        font-size: 1.5rem;
        font-weight: 700;
    }

    .nps-asset-card {
        min-height: 9.5rem;
    }

    .nps-asset-card .nps-asset-value {
        color: #203648;
        font-size: 1.8rem;
        font-weight: 700;
    }

    .nps-asset-total {
        background: #fff1c9;
    }

    .nps-asset-answered {
        background: #d7eafb;
    }

    .nps-asset-detractors {
        background: #d4eee8;
    }

    @media (max-width: 575.98px) {
        .nps-history-chart {
            gap: 0.35rem;
        }

        .nps-chart-track {
            height: 7rem;
        }

        .nps-chart-month,
        .nps-chart-value {
            font-size: 0.65rem;
        }
    }
</style>

<div class="nps-dashboard">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-4">
        <div>
            <p class="nps-card-label mb-1">Indmotos</p>
            <h2 class="mb-0">Dashboard</h2>
        </div>
        <span class="text-muted"><?= date('m/Y') ?></span>
    </div>

    <div class="row g-4 align-items-stretch">
        <div class="col-lg-8">
            <section class="card">
                <div class="card-body p-4 p-xl-5">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-start gap-3">
                        <div>
                            <p class="nps-card-label mb-2">Balance</p>
                            <h3 class="h5 mb-1">Média NPS dos últimos 6 meses</h3>
                            <p class="small text-muted mb-0">
                                <?= date('m/Y', strtotime($inicioPeriodo . '-01')) ?>
                                – <?= date('m/Y', strtotime($fimPeriodo . '-01')) ?>
                            </p>
                        </div>
                        <div class="text-sm-end">
                            <div class="nps-balance-value">
                                <?= $mediaNpsSeisMeses !== null ? number_format($mediaNpsSeisMeses, 1, ',', '.') : '—' ?>
                                <span class="fs-6 fw-normal text-muted">/ 10</span>
                            </div>
                            <small class="text-muted">
                                <?= number_format($historico_nps['total_respostas'], 0, ',', '.') ?> respostas
                            </small>
                        </div>
                    </div>

                    <div class="nps-history-chart" role="img" aria-label="Média NPS mensal nos últimos seis meses">
                        <?php foreach ($historico_nps['meses'] as $mes): ?>
                            <?php
                            $notaMensal = $mes['media_nps'];
                            $alturaBarra = $notaMensal !== null ? max(3, min(100, $notaMensal * 10)) : 0;
                            ?>
                            <div class="nps-chart-column" title="<?= App\Core\View::escape(date('m/Y', strtotime($mes['mes'] . '-01'))) ?>">
                                <span class="nps-chart-value">
                                    <?= $notaMensal !== null ? number_format($notaMensal, 1, ',', '.') : '—' ?>
                                </span>
                                <div class="nps-chart-track">
                                    <div
                                        class="nps-chart-bar <?= $notaMensal === null ? 'is-empty' : '' ?>"
                                        style="height: <?= $alturaBarra ?>%;"
                                    ></div>
                                </div>
                                <span class="nps-chart-month"><?= App\Core\View::escape(date('m/y', strtotime($mes['mes'] . '-01'))) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        </div>

        <div class="col-lg-4">
            <section class="card nps-performance-card">
                <div class="card-body p-4 d-flex flex-column justify-content-between text-center">
                    <div class="d-flex justify-content-between align-items-center text-start">
                        <p class="nps-card-label mb-0">Performance Index</p>
                        <span class="text-muted" title="Média NPS do mês atual" aria-label="Média NPS do mês atual">ⓘ</span>
                    </div>

                    <div>
                        <svg class="nps-gauge" viewBox="0 0 200 120" role="img" aria-label="Média NPS do mês: <?= $mediaNps !== null ? number_format($notaDoIndice, 1, ',', '.') : 'sem avaliações' ?> de 100">
                            <defs>
                                <linearGradient id="nps-gauge-gradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                    <stop offset="0%" stop-color="#83c5e8"></stop>
                                    <stop offset="55%" stop-color="#9bd9d0"></stop>
                                    <stop offset="100%" stop-color="#c5a6e8"></stop>
                                </linearGradient>
                            </defs>
                            <path d="M 20 100 A 80 80 0 0 1 180 100" fill="none" stroke="#e9eef2" stroke-width="14" stroke-linecap="round"></path>
                            <?php if ($mediaNps !== null): ?>
                                <path
                                    d="M 20 100 A 80 80 0 0 1 180 100"
                                    fill="none"
                                    stroke="url(#nps-gauge-gradient)"
                                    stroke-width="14"
                                    stroke-linecap="round"
                                    pathLength="100"
                                    stroke-dasharray="<?= number_format($notaDoIndice, 2, '.', '') ?> 100"
                                ></path>
                            <?php endif; ?>
                            <text class="nps-gauge-score" x="100" y="88">
                                <?= $mediaNps !== null ? number_format($notaDoIndice, 1, ',', '.') : '—' ?>
                            </text>
                        </svg>
                        <p class="nps-gauge-status mb-1"><?= $statusIndice ?></p>
                        <p class="small text-muted mb-0">
                            Média NPS do mês · <?= date('m/Y') ?>
                        </p>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <section class="mt-4">
        <div class="d-flex justify-content-between align-items-end mb-3">
            <div>
                <p class="nps-card-label mb-1">Your Assets</p>
                <h3 class="h5 mb-0">Indicadores do mês</h3>
            </div>
            <small class="text-muted"><?= date('m/Y') ?></small>
        </div>

        <div class="row g-3">
            <div class="col-sm-6 col-xl-4">
                <article class="card nps-asset-card nps-asset-total">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <span class="nps-card-label">Total de pesquisas</span>
                        <span class="nps-asset-value"><?= number_format($total, 0, ',', '.') ?></span>
                    </div>
                </article>
            </div>
            <div class="col-sm-6 col-xl-4">
                <article class="card nps-asset-card nps-asset-answered">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <span class="nps-card-label">Pesquisas respondidas</span>
                        <span class="nps-asset-value"><?= number_format($respondidas, 0, ',', '.') ?></span>
                    </div>
                </article>
            </div>
            <div class="col-sm-6 col-xl-4">
                <article class="card nps-asset-card nps-asset-detractors">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <span class="nps-card-label">Média das notas detratoras</span>
                        <span class="nps-asset-value">
                            <?= $media_notas_detratoras_mes_atual !== null ? number_format($media_notas_detratoras_mes_atual * 10, 1, ',', '.') : '—' ?>
                            <small class="fs-6 fw-normal text-muted">/ 100</small>
                        </span>
                    </div>
                </article>
            </div>
        </div>
    </section>
</div>

<?php
$content = ob_get_clean();
$title = 'Dashboard';
require __DIR__ . '/../layouts/admin.php';
?>
