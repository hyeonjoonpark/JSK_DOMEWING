@extends('layouts.main')
@section('title')
    대시보드
@endsection
@section('subtitle')
    <p>셀윙 파트너스 및 도매윙 판매 실적을 요약한 페이지입니다.</p>
@endsection
@section('content')
    <div class="row g-gs">
        <div class="col-lg-8">
            <div class="card card-bordered h-100">
                <div class="card-inner">
                    <div class="card-title-group align-start mb-3">
                        <div class="card-title">
                            <h6 class="title">주간 매출표</h6>
                            <p>최근 7일 이내의 일자 별 윙 매출액 및 충전액 차트입니다.</p>
                        </div>
                    </div><!-- .card-title-group -->
                    <div class="text-center" id="weeklySalesLoader">
                        <img src="{{ asset('assets/images/search-loader.gif') }}">
                        <h6>주간 매출표를 연산 중입니다.</h6>
                    </div>
                    <div class="nk-order-ovwg">
                        <div class="row g-4 align-end">
                            <div class="col-xxl-8">
                                <div class="nk-order-ovwg-ck">
                                    <canvas id="orderOverviewChart">
                                    </canvas>
                                </div>
                            </div><!-- .col -->
                            <div class="col-xxl-4">
                                <div class="row g-4" id="monthlySales">

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
                        <div class="text-center" id="actionCenterLoader">
                            <img src="{{ asset('assets/images/search-loader.gif') }}">
                            <h6>작업 센터를 연산 중입니다.</h6>
                        </div>
                    </div><!-- .card-inner -->
                    <div id="actionCenter"></div>
                </div><!-- .card-inner-group -->
            </div><!-- .card -->
        </div><!-- .col -->
        <div class="col-xl-7 col-xxl-8">
            <div class="card card-bordered card-full">
                <div class="card-inner">
                    <div class="card-title-group">
                        <div class="card-title">
                            <h6 class="title">도매윙 매출 TOP 6 멤버</h6>
                            <p>환불 및 교환액은 제외한 순수 매출액 기준입니다.</p>
                        </div>
                    </div>
                </div><!-- .card-inner -->
                <div class="card-inner p-0 border-top">
                    <div class="text-center" id="top6MemberSalesLoader">
                        <img src="{{ asset('assets/images/search-loader.gif') }}">
                        <h6>도매윙 매출 TOP 6 멤버를 연산 중입니다.</h6>
                    </div>
                    <div class="nk-tb-list nk-tb-orders" id="top6MemberSales">
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
                                    <h6 class="title">탑 5 원청사</h6>
                                    <p>이번 달 가장 높은 주문 수량/횟수를 가진 탑 5 원청사 리스트입니다.</p>
                                </div>
                            </div><!-- .card-title-group -->
                            <div class="text-center" id="topVendorsLoader">
                                <img src="{{ asset('assets/images/search-loader.gif') }}">
                            </div>
                            <div class="nk-coin-ovwg">
                                <div class="nk-coin-ovwg-ck">
                                    <canvas id="topVendors"></canvas>
                                </div>
                                <ul class="nk-coin-ovwg-legends" id="topVendorLegends">
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
        $(document).ready(function() {
            requestWeeklySales();
            requestActionCenter();
            top6MemberSales();
            requestTopVendors();
        });

        function top6MemberSales() {
            $.ajax({
                url: '/api/admin/dashboard/top-6-member-sales',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    rememberToken
                },
                success: function(top6MemberSales) {
                    const wingImagePath = "{{ asset('assets/images/wing.svg') }}";
                    let top6MemberSalesHtml = `
                        <div class="nk-tb-item nk-tb-head">
                            <div class="nk-tb-col nk-tb-orders-type"><span>전월 비교</span></div>
                            <div class="nk-tb-col"><span>멤버</span></div>
                            <div class="nk-tb-col"><span>매출액</span></div>
                        </div><!-- .nk-tb-item -->
                    `;

                    top6MemberSales.forEach(item => {
                        top6MemberSalesHtml += `
                            <div class="nk-tb-item">
                                <div class="nk-tb-col nk-tb-orders-type">
                                    <ul class="icon-overlap">
                                        <li>
                                            <div class="user-avatar">
                                                <img class="w-100 h-100" src="https://domewing.com/library/Profile_pictures/${item.member.profilePicture}" />
                                            </div>
                                        </li>
                                        <li>
                                            <em class="bg-success-dim icon-circle icon ni ni-arrow-${item.lastMonth > item.thisMonth ? 'down' : 'up'}-left"></em>
                                        </li>
                                    </ul>
                                </div>
                                <div class="nk-tb-col">
                                    <span class="tb-sub tb-amount">${item.member.name}</span>
                                    <span class="tb-sub">${item.member.phone}<br>${item.member.email}</span>
                                </div>
                                <div class="nk-tb-col">
                                    <span class="tb-sub tb-amount d-flex align-items-top">
                                        이번 달: ${numberFormat(item.thisMonth)}<img class="ms-1" src="${wingImagePath}" style="width: 1rem;">
                                    </span>
                                    <span class="tb-sub d-flex align-items-top">
                                        지난 달: ${numberFormat(item.lastMonth)}<img class="ms-1" src="${wingImagePath}" style="width: 1rem;">
                                    </span>
                                    <span class="tb-sub d-flex align-items-top">
                                        Total: ${numberFormat(item.total)}<img class="ms-1" src="${wingImagePath}" style="width: 1rem;">
                                    </span>
                                </div>
                            </div><!-- .nk-tb-item -->
                        `;
                    });
                    $("#top6MemberSalesLoader").hide();
                    $("#top6MemberSales").html(top6MemberSalesHtml);
                },
                error: function(response) {
                    console.log(response);
                }
            });
        }

        function requestActionCenter() {
            $.ajax({
                url: '/api/admin/dashboard/action-center',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    rememberToken
                },
                success: function(actionCenter) {
                    const titles = {
                        'numPendingOrders': '발주 대기 중인 주문',
                        'numContactUs': '접수된 문의',
                        'numPendingDeposits': '승인 대기 중인 입금'
                    };

                    const icons = {
                        'numPendingOrders': 'cc-alt-fill',
                        'numContactUs': 'help-fill',
                        'numPendingDeposits': 'wallet-fill'
                    };

                    const links = {
                        'numPendingOrders': '/admin/open-market',
                        'numContactUs': '/admin/contact-us',
                        'numPendingDeposits': 'https://domewing.com/admin/'
                    };

                    let actionCenterHtml = '';

                    for (const key in actionCenter) {
                        if (actionCenter.hasOwnProperty(key)) {
                            actionCenterHtml += `
                                <div class="card-inner">
                                    <div class="nk-wg-action">
                                        <div class="nk-wg-action-content">
                                            <em class="icon ni ni-${icons[key]}"></em>
                                            <div class="title">${titles[key]}</div>
                                            <p>총 <strong>${numberFormat(actionCenter[key])}</strong>개의 ${titles[key]}</p>
                                        </div>
                                        <a href="${links[key]}" class="btn btn-icon btn-trigger me-n2" target="${key === 'numPendingDeposits' ? '_blank' : '_self'}">
                                            <em class="icon ni ni-forward-ios"></em>
                                        </a>
                                    </div>
                                </div><!-- .card-inner -->
                            `;
                        }
                    }
                    $("#actionCenterLoader").hide();
                    $('#actionCenter').html(actionCenterHtml);
                },
                error: function(response) {
                    console.log(response);
                }
            });
        }

        function requestWeeklySales() {
            $.ajax({
                url: '/api/admin/dashboard/weeklySales',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    rememberToken
                },
                success: function(weeklySales) {
                    $('#weeklySalesLoader').hide();
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
                                    title: (tooltipItem, data) => data.datasets[tooltipItem[0]
                                        .datasetIndex].label,
                                    label: (tooltipItem, data) => {
                                        let value = data.datasets[tooltipItem.datasetIndex].data[
                                            tooltipItem
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
                                        stepSize: 1000000,
                                        callback: function(value) {
                                            return value.toLocaleString() +
                                                ' 윙'; // 천 단위 콤마 추가
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
                    const wingImagePath = "{{ asset('assets/images/wing.svg') }}";

                    const monthlySales = `
                        <div class="col-sm-6 col-xxl-12">
                            <div class="nk-order-ovwg-data buy">
                                <div class="amount d-flex align-items-top">
                                    ${numberFormat(weeklySales.thisMonthSaleTotal)}
                                    <img src="${wingImagePath}" alt="윙" class="ms-1" style="width: 1.5rem;">
                                </div>
                                <div class="info d-flex align-items-top">
                                    지난 달 <strong class="ms-1 me-1">${numberFormat(weeklySales.lastMonthSaleTotal)}</strong>
                                    <img src="${wingImagePath}" alt="윙" style="width: 1rem;">
                                </div>
                                <div class="title">
                                    <em class="icon ni ni-arrow-${parseInt(weeklySales.thisMonthSaleTotal) >= parseInt(weeklySales.lastMonthSaleTotal) ? 'up' : 'down'}-left"></em>
                                    총 매출액
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-xxl-12">
                            <div class="nk-order-ovwg-data sell">
                                <div class="amount d-flex align-items-top">
                                    ${numberFormat(weeklySales.thisMonthRechargeTotal)}
                                    <img src="${wingImagePath}" alt="윙" class="ms-1" style="width: 1.5rem;">
                                </div>
                                <div class="info d-flex align-items-top">
                                    지난 달 <strong class="ms-1 me-1">${numberFormat(weeklySales.lastMonthRechargeTotal)}</strong>
                                    <img src="${wingImagePath}" alt="윙" style="width: 1rem;">
                                </div>
                                <div class="title">
                                    <em class="icon ni ni-arrow-${parseInt(weeklySales.thisMonthRechargeTotal) >= parseInt(weeklySales.lastMonthRechargeTotal) ? 'up' : 'down'}-left"></em>
                                    총 충전액
                                </div>
                            </div>
                        </div>
                    `;
                    $('#monthlySales').html(monthlySales);
                },
                error: function(response) {
                    console.log(response);
                }
            });
        }

        function numberFormat(number) {
            return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }

        function requestTopVendors() {
            $.ajax({
                url: '/api/admin/dashboard/top-vendors',
                type: 'GET',
                dataType: 'JSON',
                data: {
                    rememberToken
                },
                success: function(response) {
                    const vendors = response;
                    const labels = [];
                    const countData = [];
                    const quantityData = [];
                    const colors = ['#f98c45', '#9cabff', '#8feac5', '#6b79c8', '#79f1dc'];
                    let topVendorLegends = '';
                    let i = 0;
                    for (const vendor of vendors) {
                        labels.push(vendor.name);
                        countData.push(vendor.count);
                        quantityData.push(vendor.quantity);
                        topVendorLegends += `
                             <li><span class="dot dot-lg sq" data-bg="${colors[i]}" style="background: ${colors[i]};"></span><span>${vendor.name}</span></li>
                        `;
                        i++;
                    }
                    $('#topVendorLegends').html(topVendorLegends);

                    const topVendors = {
                        labels,
                        stacked: true,
                        datasets: [{
                            label: "주문 횟수",
                            color: ["#f98c45", "#6baafe", "#8feac5", "#6b79c8", "#79f1dc"],
                            data: countData
                        }, {
                            label: "주문 수량",
                            color: [
                                NioApp.hexRGB('#f98c45', .2),
                                NioApp.hexRGB('#6baafe', .4),
                                NioApp.hexRGB('#8feac5', .4),
                                NioApp.hexRGB('#6b79c8', .4),
                                NioApp.hexRGB('#79f1dc', .4)
                            ],
                            data: quantityData
                        }]
                    };

                    renderChart('#topVendors', topVendors);
                    $('#topVendorsLoader').hide();
                },
                error: function(response) {
                    console.log(response);
                }
            });
        }

        function createChartData(dataset) {
            return {
                label: dataset.label,
                data: dataset.data,
                backgroundColor: dataset.color,
                borderWidth: 2,
                borderColor: 'transparent',
                hoverBorderColor: 'transparent',
                borderSkipped: 'bottom',
                barThickness: 8,
                categoryPercentage: 0.5,
                barPercentage: 1.0
            };
        }

        function createChartOptions(_get_data) {
            return {
                legend: {
                    display: _get_data.legend || false,
                    rtl: NioApp.State.isRTL,
                    labels: {
                        boxWidth: 30,
                        padding: 20,
                        fontColor: '#6783b8'
                    }
                },
                maintainAspectRatio: false,
                tooltips: {
                    enabled: true,
                    rtl: NioApp.State.isRTL,
                    callbacks: {
                        title: (tooltipItem, data) => data.labels[tooltipItem[0].index],
                        label: (tooltipItem, data) =>
                            `${data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index]} ${data.datasets[tooltipItem.datasetIndex].label}`
                    },
                    backgroundColor: '#eff6ff',
                    titleFontSize: 13,
                    titleFontColor: '#6783b8',
                    titleMarginBottom: 6,
                    bodyFontColor: '#9eaecf',
                    bodyFontSize: 12,
                    bodySpacing: 4,
                    yPadding: 10,
                    xPadding: 10,
                    footerMarginTop: 0,
                    displayColors: false
                },
                scales: {
                    yAxes: [{
                        display: false,
                        stacked: _get_data.stacked || false,
                        ticks: {
                            beginAtZero: true,
                            padding: 0
                        },
                        gridLines: {
                            color: NioApp.hexRGB("#526484", .2),
                            tickMarkLength: 0,
                            zeroLineColor: NioApp.hexRGB("#526484", .2)
                        }
                    }],
                    xAxes: [{
                        display: false,
                        stacked: _get_data.stacked || false,
                        ticks: {
                            fontSize: 9,
                            fontColor: '#9eaecf',
                            source: 'auto',
                            padding: 0,
                            reverse: NioApp.State.isRTL
                        },
                        gridLines: {
                            color: "transparent",
                            tickMarkLength: 0,
                            zeroLineColor: 'transparent'
                        }
                    }]
                }
            };
        }

        function renderChart(selector, chartData) {
            const $selector = selector ? $(selector) : $('#topVendors');
            $selector.each(function() {
                const $self = $(this);
                const _self_id = $self.attr('id');
                const selectCanvas = document.getElementById(_self_id).getContext("2d");

                const datasets = chartData.datasets.map(createChartData);
                const options = createChartOptions(chartData);

                new Chart(selectCanvas, {
                    type: 'horizontalBar',
                    data: {
                        labels: chartData.labels,
                        datasets
                    },
                    options
                });
            });
        }
    </script>
@endsection
