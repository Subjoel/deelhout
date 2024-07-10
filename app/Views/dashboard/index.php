<script src="<?= base_url('assets/admin/vendor/chart/chart.min.js'); ?>"></script>
<script src="<?= base_url('assets/admin/vendor/chart/utils.js'); ?>"></script>
<script src="<?= base_url('assets/admin/vendor/chart/analyser.js'); ?>"></script>
<div class="row m-b-30">
    <div class="col-sm-12">
        <div class="small-boxes-dashboard">

            <div<?= !$baseVars->isSaleActive ? ' class="classified-small-boxes"' : ''; ?>>
                <div class="col-lg-3 col-md-6 col-sm-12 p-0">
                    <div class="small-box-dashboard" <?= !$baseVars->isSaleActive ? 'style="border-radius: 4px 0 0 4px;"' : ''; ?>>
                        <h3 class="total"><?= $productsCount; ?></h3>
                        <span class="text-muted"><?= trans("products"); ?></span>
                        <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-basket" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M5.757 1.071a.5.5 0 0 1 .172.686L3.383 6h9.234L10.07 1.757a.5.5 0 1 1 .858-.514L13.783 6H15a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1v4.5a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 1 13.5V9a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h1.217L5.07 1.243a.5.5 0 0 1 .686-.172zM2 9v4.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V9H2zM1 7v1h14V7H1zm3 3a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3A.5.5 0 0 1 4 10zm2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3A.5.5 0 0 1 6 10zm2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3A.5.5 0 0 1 8 10zm2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3a.5.5 0 0 1 .5-.5zm2 0a.5.5 0 0 1 .5.5v3a.5.5 0 0 1-1 0v-3a.5.5 0 0 1 .5-.5z"/>
                        </svg>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12 p-0">
                    <div class="small-box-dashboard small-box-dashboard-last">
                        <h3 class="total"><?= !empty($totalPageviewsCount) ? $totalPageviewsCount : '0'; ?></h3>
                        <span class="text-muted"><?= trans("page_views"); ?></span>
                        <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-bar-chart" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M4 11H2v3h2v-3zm5-4H7v7h2V7zm5-5h-2v12h2V2zm-2-1a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1h-2zM6 7a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7zm-5 4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1v-3z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-6 col-sm-12 col-xs-12">
        <div class="box box-primary box-sm index-box">
            <div class="box-header with-border">
                <h3 class="box-title"><?= trans("most_viewed_products"); ?></h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                </div>
            </div>
            <div class="box-body index-table">
                <div class="table-responsive">
                    <table class="table no-margin">
                        <thead>
                        <tr>
                            <th><?= trans("id"); ?></th>
                            <th><?= trans("product"); ?></th>
                            <th><?= trans("page_views"); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($mostViewedProducts)):
                            foreach ($mostViewedProducts as $item): ?>
                                <tr>
                                    <td style="width: 10%"><?= esc($item->id); ?></td>
                                    <td><a href="<?= generateProductUrl($item); ?>" class="link-black" target="_blank"><?= getProductTitle($item); ?></a></td>
                                    <td><?= $item->pageviews; ?></td>
                                </tr>
                            <?php endforeach;
                        endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="box-footer clearfix text-right">
                <a href="<?= generateDashUrl("products"); ?>" class="btn btn-sm btn-default"><?= trans("view_all"); ?></a>
            </div>
        </div>
    </div>
</div>
<script>
    //monthly sales
    var months = ["<?= trans("january");?>", "<?= trans("february");?>", "<?= trans("march");?>", "<?= trans("april");?>", "<?= trans("may");?>", "<?= trans("june");?>", "<?= trans("july");?>", "<?= trans("august");?>", "<?= trans("september");?>", "<?= trans("october");?>", "<?= trans("november");?>", "<?= trans("december");?>"];
    var i;
    for (i = 0; i < months.length; i++) {
        months[i] = months[i].substr(0, 3);
    }
    var presets = window.chartColors;
    var utils = Samples.utils;
    var inputs = {
        min: 0,
        max: 100,
        count: 8,
        decimals: 2,
        continuity: 1
    };
    var options = {
        maintainAspectRatio: false,
        spanGaps: false,
        elements: {
            line: {
                tension: 0.000001
            }
        },
        plugins: {
            filler: {
                propagate: false
            }
        },
        scales: {
            x: {
                ticks: {
                    autoSkip: false,
                    maxRotation: 0
                }
            },
            yAxes: [
                {
                    ticks: {
                        beginAtZero: true,
                        callback: function (label, index, labels) {
                            return "<?= $defaultCurrency->symbol; ?>" + label;
                        }
                    }
                }
            ]
        },
        tooltips: {
            callbacks: {
                label: function (tooltipItem, data) {
                    return data['labels'][tooltipItem['index']] + ": <?= $defaultCurrency->symbol; ?>" + data['datasets'][0]['data'][tooltipItem['index']];
                }
            }
        }
    };
    [false, 'origin', 'start', 'end'].forEach(function () {
        utils.srand(8);
        new Chart('chart_montly_sales', {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    backgroundColor: utils.transparentize("#bfe8e6"),
                    borderColor: "#1BC5BD",
                    data: [<?php for ($i = 1; $i <= 12; $i++) {
                        echo $i > 1 ? ',' : '';
                        $total = 0;
                        if (!empty($salesSum)):
                            foreach ($salesSum as $sum):
                                if (isset($sum->month) && $sum->month == $i):
                                    $total = $sum->total_amount;
                                    break;
                                endif;
                            endforeach;
                        endif;
                        echo getPrice($total, 'decimal');
                    }?>],
                    label: "<?= trans("sales"); ?> (<?= date("Y") ?>)"
                }]
            },
            options: Chart.helpers.merge(options, {
                title: {
                    display: false
                },
                elements: {
                    line: {
                        tension: 0.4,
                        borderWidth: 2
                    }
                }
            })
        });
    });
</script>