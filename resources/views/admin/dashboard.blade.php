@extends('layouts.main')
@section('title')
    대시보드
@endsection
@section('subtitle')
    <p>셀윙 파트너스 및 도매윙 판매 실적을 요약한 페이지입니다.</p>
@endsection
@section('content')
    <div class="row g-gs">
        <?php
        $labels = $weeklySales['labels'];
        $sales = $weeklySales['sales'];
        $recharges = $weeklySales['recharges'];
        $max = $weeklySales['max'];
        $thisMonthSaleTotal = $weeklySales['thisMonthSaleTotal'];
        $lastMonthSaleTotal = $weeklySales['lastMonthSaleTotal'];
        $thisMonthRechargeTotal = $weeklySales['thisMonthRechargeTotal'];
        $lastMonthRechargeTotal = $weeklySales['lastMonthRechargeTotal'];
        ?>
        <div class="col-lg-8">
            <div class="card card-bordered h-100">
                <div class="card-inner">
                    <div class="card-title-group align-start mb-3">
                        <div class="card-title">
                            <h6 class="title">주간 매출표</h6>
                            <p>
                                최근 7일 이내의 일자 별 윙 매출액 및 충전액 차트입니다.
                            </p>
                        </div>
                    </div><!-- .card-title-group -->
                    <div class="nk-order-ovwg">
                        <div class="row g-4 align-end">
                            <div class="col-xxl-8">
                                <div class="nk-order-ovwg-ck">
                                    <canvas id="orderOverviewChart"></canvas>
                                </div>
                            </div><!-- .col -->
                            <div class="col-xxl-4">
                                <div class="row g-4">
                                    <div class="col-sm-6 col-xxl-12">
                                        <div class="nk-order-ovwg-data buy">
                                            <div class="amount d-flex align-items-top">
                                                {{ number_format($thisMonthSaleTotal) }}
                                                <img src="{{ asset('assets/images/wing.svg') }}" alt="윙"
                                                    class="ms-1" style="width: 1.5rem;">
                                            </div>
                                            <div class="info d-flex align-items-top">
                                                지난 달 <strong
                                                    class="ms-1 me-1">{{ number_format($lastMonthSaleTotal) }}</strong><img
                                                    src="{{ asset('assets/images/wing.svg') }}" alt="윙"
                                                    style="width: 1rem;">
                                            </div>
                                            <div class="title"><em
                                                    class="icon ni ni-arrow-{{ $thisMonthSaleTotal >= $lastMonthSaleTotal ? 'up' : 'down' }}-left"></em>
                                                총 매출액
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-xxl-12">
                                        <div class="nk-order-ovwg-data sell">
                                            <div class="amount d-flex align-items-top">
                                                {{ number_format($thisMonthRechargeTotal) }}
                                                <img src="{{ asset('assets/images/wing.svg') }}" alt="윙"
                                                    class="ms-1" style="width: 1.5rem;">
                                            </div>
                                            <div class="info d-flex align-items-top">
                                                지난 달 <strong
                                                    class="ms-1 me-1">{{ number_format($lastMonthRechargeTotal) }}</strong><img
                                                    src="{{ asset('assets/images/wing.svg') }}" alt="윙"
                                                    style="width: 1rem;">
                                            </div>
                                            <div class="title"><em
                                                    class="icon ni ni-arrow-{{ $thisMonthRechargeTotal >= $lastMonthRechargeTotal ? 'up' : 'down' }}-left"></em>
                                                총 충전액</div>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- .col -->
                        </div>
                    </div><!-- .nk-order-ovwg -->
                </div><!-- .card-inner -->
            </div><!-- .card -->
        </div><!-- .col -->
        <div class="col-lg-4">
            <div class="card card-bordered">
                <div class="card-inner-group">
                    <div class="card-inner card-inner-md">
                        <div class="card-title-group">
                            <div class="card-title">
                                <h6 class="title">작업 센터</h6>
                            </div>
                        </div>
                    </div><!-- .card-inner -->
                    <div class="card-inner">
                        <div class="nk-wg-action">
                            <div class="nk-wg-action-content">
                                <em class="icon ni ni-cc-alt-fill"></em>
                                <div class="title">발주 대기 중인 주문</div>
                                <p>
                                    총 <strong>{{ number_format($actionCenter['numPendingOrders']) }}</strong>개의 주문이 발주 대기
                                    중입니다.
                                </p>
                            </div>
                            <a href="/admin/open-market" class="btn btn-icon btn-trigger me-n2"><em
                                    class="icon ni ni-forward-ios"></em></a>
                        </div>
                    </div><!-- .card-inner -->
                    <div class="card-inner">
                        <div class="nk-wg-action">
                            <div class="nk-wg-action-content">
                                <em class="icon ni ni-help-fill"></em>
                                <div class="title">접수된 문의</div>
                                <p>
                                    총 <strong>{{ number_format($actionCenter['numContactUs']) }}</strong>개의 문의가 답변 대기 중입니다.
                                </p>
                            </div>
                            <a href="/admin/contact-us" class="btn btn-icon btn-trigger me-n2"><em
                                    class="icon ni ni-forward-ios"></em></a>
                        </div>
                    </div><!-- .card-inner -->
                    <div class="card-inner">
                        <div class="nk-wg-action">
                            <div class="nk-wg-action-content">
                                <em class="icon ni ni-wallet-fill"></em>
                                <div class="title">승인 대기 중인 입금</div>
                                <p>
                                    총 <strong>{{ number_format($actionCenter['numPendingDeposits']) }}</strong>개의 입금이 승인 대기
                                    중입니다.
                                </p>
                            </div>
                            <a href="https://domewing.com/admin/" class="btn btn-icon btn-trigger me-n2" target="_blank"><em
                                    class="icon ni ni-forward-ios"></em></a>
                        </div>
                    </div><!-- .card-inner -->
                </div><!-- .card-inner-group -->
            </div><!-- .card -->
        </div><!-- .col -->
        <div class="col-xl-7 col-xxl-8">
            <div class="card card-bordered card-full">
                <div class="card-inner">
                    <div class="card-title-group">
                        <div class="card-title">
                            <h6 class="title"><span class="me-2">Orders Activities</span> <a href="#"
                                    class="link d-none d-sm-inline">See History</a></h6>
                        </div>
                        <div class="card-tools">
                            <ul class="card-tools-nav">
                                <li><a href="#"><span>Buy</span></a></li>
                                <li><a href="#"><span>Sell</span></a></li>
                                <li class="active"><a href="#"><span>All</span></a></li>
                            </ul>
                        </div>
                    </div>
                </div><!-- .card-inner -->
                <div class="card-inner p-0 border-top">
                    <div class="nk-tb-list nk-tb-orders">
                        <div class="nk-tb-item nk-tb-head">
                            <div class="nk-tb-col nk-tb-orders-type"><span>Type</span></div>
                            <div class="nk-tb-col"><span>Desc</span></div>
                            <div class="nk-tb-col tb-col-sm"><span>Date</span></div>
                            <div class="nk-tb-col tb-col-xxl"><span>Time</span></div>
                            <div class="nk-tb-col tb-col-xxl"><span>Ref</span></div>
                            <div class="nk-tb-col tb-col-sm text-end"><span>USD Amount</span></div>
                            <div class="nk-tb-col text-end"><span>Amount</span></div>
                        </div><!-- .nk-tb-item -->
                        <div class="nk-tb-item">
                            <div class="nk-tb-col nk-tb-orders-type">
                                <ul class="icon-overlap">
                                    <li><em class="bg-btc-dim icon-circle icon ni ni-sign-btc"></em></li>
                                    <li><em class="bg-success-dim icon-circle icon ni ni-arrow-down-left"></em></li>
                                </ul>
                            </div>
                            <div class="nk-tb-col">
                                <span class="tb-lead">Buy Bitcoin</span>
                            </div>
                            <div class="nk-tb-col tb-col-sm">
                                <span class="tb-sub">02/10/2020</span>
                            </div>
                            <div class="nk-tb-col tb-col-xxl">
                                <span class="tb-sub">11:37 PM</span>
                            </div>
                            <div class="nk-tb-col tb-col-xxl">
                                <span class="tb-sub text-primary">RE2309232</span>
                            </div>
                            <div class="nk-tb-col tb-col-sm text-end">
                                <span class="tb-sub tb-amount">4,565.75 <span>USD</span></span>
                            </div>
                            <div class="nk-tb-col text-end">
                                <span class="tb-sub tb-amount ">+ 0.2040 <span>BTC</span></span>
                            </div>
                        </div><!-- .nk-tb-item -->
                        <div class="nk-tb-item">
                            <div class="nk-tb-col nk-tb-orders-type">
                                <ul class="icon-overlap">
                                    <li><em class="bg-eth-dim icon-circle icon ni ni-sign-eth"></em></li>
                                    <li><em class="bg-success-dim icon-circle icon ni ni-arrow-down-left"></em></li>
                                </ul>
                            </div>
                            <div class="nk-tb-col">
                                <span class="tb-lead">Buy Ethereum</span>
                            </div>
                            <div class="nk-tb-col tb-col-sm">
                                <span class="tb-sub">02/10/2020</span>
                            </div>
                            <div class="nk-tb-col tb-col-xxl">
                                <span class="tb-sub">10:37 PM</span>
                            </div>
                            <div class="nk-tb-col tb-col-xxl">
                                <span class="tb-sub text-primary">RE2309232</span>
                            </div>
                            <div class="nk-tb-col tb-col-sm text-end">
                                <span class="tb-sub tb-amount">2,039.39 <span>USD</span></span>
                            </div>
                            <div class="nk-tb-col text-end">
                                <span class="tb-sub tb-amount ">+ 0.12600 <span>BTC</span></span>
                            </div>
                        </div><!-- .nk-tb-item -->
                        <div class="nk-tb-item">
                            <div class="nk-tb-col nk-tb-orders-type">
                                <ul class="icon-overlap">
                                    <li><em class="bg-btc-dim icon-circle icon ni ni-sign-btc"></em></li>
                                    <li><em class="bg-purple-dim icon-circle icon ni ni-arrow-up-right"></em></li>
                                </ul>
                            </div>
                            <div class="nk-tb-col">
                                <span class="tb-lead">Sell Bitcoin</span>
                            </div>
                            <div class="nk-tb-col tb-col-sm">
                                <span class="tb-sub">02/10/2020</span>
                            </div>
                            <div class="nk-tb-col tb-col-xxl">
                                <span class="tb-sub">10:45 PM</span>
                            </div>
                            <div class="nk-tb-col tb-col-xxl">
                                <span class="tb-sub text-primary">RE2309232</span>
                            </div>
                            <div class="nk-tb-col tb-col-sm text-end">
                                <span class="tb-sub tb-amount">9,285.71 <span>USD</span></span>
                            </div>
                            <div class="nk-tb-col text-end">
                                <span class="tb-sub tb-amount ">+ 0.94750 <span>BTC</span></span>
                            </div>
                        </div><!-- .nk-tb-item -->
                        <div class="nk-tb-item">
                            <div class="nk-tb-col nk-tb-orders-type">
                                <ul class="icon-overlap">
                                    <li><em class="bg-eth-dim icon-circle icon ni ni-sign-eth"></em></li>
                                    <li><em class="bg-purple-dim icon-circle icon ni ni-arrow-up-right"></em></li>
                                </ul>
                            </div>
                            <div class="nk-tb-col">
                                <span class="tb-lead">Sell Etherum</span>
                            </div>
                            <div class="nk-tb-col tb-col-sm">
                                <span class="tb-sub">02/11/2020</span>
                            </div>
                            <div class="nk-tb-col tb-col-xxl">
                                <span class="tb-sub">10:25 PM</span>
                            </div>
                            <div class="nk-tb-col tb-col-xxl">
                                <span class="tb-sub text-primary">RE2309232</span>
                            </div>
                            <div class="nk-tb-col tb-col-sm text-end">
                                <span class="tb-sub tb-amount">12,596.75 <span>USD</span></span>
                            </div>
                            <div class="nk-tb-col text-end">
                                <span class="tb-sub tb-amount ">+ 1.02050 <span>BTC</span></span>
                            </div>
                        </div><!-- .nk-tb-item -->
                        <div class="nk-tb-item">
                            <div class="nk-tb-col nk-tb-orders-type">
                                <ul class="icon-overlap">
                                    <li><em class="bg-btc-dim icon-circle icon ni ni-sign-btc"></em></li>
                                    <li><em class="bg-success-dim icon-circle icon ni ni-arrow-down-left"></em></li>
                                </ul>
                            </div>
                            <div class="nk-tb-col">
                                <span class="tb-lead">Buy Bitcoin</span>
                            </div>
                            <div class="nk-tb-col tb-col-sm">
                                <span class="tb-sub">02/10/2020</span>
                            </div>
                            <div class="nk-tb-col tb-col-xxl">
                                <span class="tb-sub">10:12 PM</span>
                            </div>
                            <div class="nk-tb-col tb-col-xxl">
                                <span class="tb-sub text-primary">RE2309232</span>
                            </div>
                            <div class="nk-tb-col tb-col-sm text-end">
                                <span class="tb-sub tb-amount">400.00 <span>USD</span></span>
                            </div>
                            <div class="nk-tb-col text-end">
                                <span class="tb-sub tb-amount ">+ 0.00056 <span>BTC</span></span>
                            </div>
                        </div><!-- .nk-tb-item -->
                        <div class="nk-tb-item">
                            <div class="nk-tb-col nk-tb-orders-type">
                                <ul class="icon-overlap">
                                    <li><em class="bg-eth-dim icon-circle icon ni ni-sign-eth"></em></li>
                                    <li><em class="bg-purple-dim icon-circle icon ni ni-arrow-up-right"></em></li>
                                </ul>
                            </div>
                            <div class="nk-tb-col">
                                <span class="tb-lead">Sell Etherum</span>
                            </div>
                            <div class="nk-tb-col tb-col-sm">
                                <span class="tb-sub">02/09/2020</span>
                            </div>
                            <div class="nk-tb-col tb-col-xxl">
                                <span class="tb-sub">05:15 PM</span>
                            </div>
                            <div class="nk-tb-col tb-col-xxl">
                                <span class="tb-sub text-primary">RE2309232</span>
                            </div>
                            <div class="nk-tb-col tb-col-sm text-end">
                                <span class="tb-sub tb-amount">6,246.50 <span>USD</span></span>
                            </div>
                            <div class="nk-tb-col text-end">
                                <span class="tb-sub tb-amount ">+ 0.02575 <span>BTC</span></span>
                            </div>
                        </div><!-- .nk-tb-item -->
                    </div>
                </div><!-- .card-inner -->
                <div class="card-inner-sm border-top text-center d-sm-none">
                    <a href="#" class="btn btn-link btn-block">See History</a>
                </div><!-- .card-inner -->
            </div><!-- .card -->
        </div><!-- .col -->
        <div class="col-xl-5 col-xxl-4">
            <div class="row g-gs">
                <div class="col-md-6 col-lg-12">
                    <div class="card card-bordered card-full">
                        <div class="card-inner">
                            <div class="card-title-group align-start mb-2">
                                <div class="card-title">
                                    <h6 class="title">Top Coin in Orders</h6>
                                    <p>In last 15 days buy and sells overview.</p>
                                </div>
                                <div class="card-tools mt-n1 me-n1">
                                    <div class="drodown">
                                        <a href="#" class="dropdown-toggle btn btn-icon btn-trigger"
                                            data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                                        <div class="dropdown-menu dropdown-menu-sm dropdown-menu-end">
                                            <ul class="link-list-opt no-bdr">
                                                <li><a href="#" class="active"><span>15 Days</span></a></li>
                                                <li><a href="#"><span>30 Days</span></a></li>
                                                <li><a href="#"><span>3 Months</span></a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- .card-title-group -->
                            <div class="nk-coin-ovwg">
                                <div class="nk-coin-ovwg-ck">
                                    <canvas class="coin-overview-chart" id="coinOverview"></canvas>
                                </div>
                                <ul class="nk-coin-ovwg-legends">
                                    <li><span class="dot dot-lg sq" data-bg="#f98c45"></span><span>Bitcoin</span></li>
                                    <li><span class="dot dot-lg sq" data-bg="#9cabff"></span><span>Ethereum</span></li>
                                    <li><span class="dot dot-lg sq" data-bg="#8feac5"></span><span>NioCoin</span></li>
                                    <li><span class="dot dot-lg sq" data-bg="#6b79c8"></span><span>Litecoin</span></li>
                                    <li><span class="dot dot-lg sq" data-bg="#79f1dc"></span><span>Bitcoin Cash</span>
                                    </li>
                                </ul>
                            </div><!-- .nk-coin-ovwg -->
                        </div><!-- .card-inner -->
                    </div><!-- .card -->
                </div><!-- .col -->
                <div class="col-md-6 col-lg-12">
                    <div class="card card-bordered card-full">
                        <div class="card-inner">
                            <div class="card-title-group align-start mb-3">
                                <div class="card-title">
                                    <h6 class="title">User Activities</h6>
                                    <p>In last 30 days <em class="icon ni ni-info" data-bs-toggle="tooltip"
                                            data-bs-placement="right" title="Referral Informations"></em></p>
                                </div>
                                <div class="card-tools mt-n1 me-n1">
                                    <div class="drodown">
                                        <a href="#" class="dropdown-toggle btn btn-icon btn-trigger"
                                            data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                                        <div class="dropdown-menu dropdown-menu-sm dropdown-menu-end">
                                            <ul class="link-list-opt no-bdr">
                                                <li><a href="#"><span>15 Days</span></a></li>
                                                <li><a href="#" class="active"><span>30 Days</span></a></li>
                                                <li><a href="#"><span>3 Months</span></a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="user-activity-group g-4">
                                <div class="user-activity">
                                    <em class="icon ni ni-users"></em>
                                    <div class="info">
                                        <span class="amount">345</span>
                                        <span class="title">Direct Join</span>
                                    </div>
                                </div>
                                <div class="user-activity">
                                    <em class="icon ni ni-users"></em>
                                    <div class="info">
                                        <span class="amount">49</span>
                                        <span class="title">Referral Join</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="user-activity-ck">
                            <canvas class="usera-activity-chart" id="userActivity"></canvas>
                        </div>
                    </div><!-- .card -->
                </div><!-- .col -->
            </div><!-- .row -->
        </div><!-- .col -->
    </div><!-- .row -->
@endsection
@section('scripts')
    <script src="{{ asset('assets/js/charts/gd-default.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const weeklySales = @json($weeklySales);
            const labels = weeklySales.labels;
            const sales = weeklySales.sales;
            const recharges = weeklySales.recharges;
            const max = weeklySales.max;
            const orderOverviewData = {
                labels,
                dataUnit: '윙',
                datasets: [{
                        label: "매출액",
                        color: "#8feac5",
                        data: sales
                    },
                    {
                        label: "충전액",
                        color: "#6baafe",
                        data: recharges
                    }
                ]
            };
            const ctx = document.getElementById('orderOverviewChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: orderOverviewData.labels,
                    datasets: orderOverviewData.datasets.map(dataset => ({
                        label: dataset.label,
                        data: dataset.data,
                        backgroundColor: dataset.color,
                        borderWidth: 2,
                        borderColor: 'transparent',
                        hoverBorderColor: 'transparent',
                        borderSkipped: 'bottom',
                        barPercentage: 0.8,
                        categoryPercentage: 0.6
                    }))
                },
                options: {
                    legend: {
                        display: false
                    },
                    maintainAspectRatio: false,
                    tooltips: {
                        callbacks: {
                            title: (tooltipItem, data) => data.datasets[tooltipItem[0].datasetIndex].label,
                            label: (tooltipItem, data) => {
                                let value = data.datasets[tooltipItem.datasetIndex].data[tooltipItem
                                    .index];
                                value = value.toString();
                                value = value.split(/(?=(?:...)*$)/);
                                value = value.join(',');
                                return value + '윙';
                            }
                        },
                        backgroundColor: '#eff6ff',
                        titleFontSize: 13,
                        titleFontColor: '#6783b8',
                        bodyFontColor: '#9eaecf',
                        bodyFontSize: 12,
                        bodySpacing: 4,
                        yPadding: 10,
                        xPadding: 10,
                        displayColors: false
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: true,
                                min: 0,
                                max: max,
                                stepSize: 500000,
                                callback: function(value) {
                                    return value.toLocaleString() + ' 윙'; // 천 단위 콤마 추가
                                }
                            },
                            gridLines: {
                                color: 'rgba(82, 100, 132, 0.2)',
                                zeroLineColor: 'rgba(82, 100, 132, 0.2)'
                            }
                        }],
                        xAxes: [{
                            ticks: {
                                fontSize: 9,
                                fontColor: '#9eaecf',
                                padding: 10
                            },
                            gridLines: {
                                color: "transparent"
                            }
                        }]
                    }
                }
            });
        });
    </script>
@endsection
