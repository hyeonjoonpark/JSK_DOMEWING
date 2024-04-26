@extends('partner.layouts.main')
@section('title')
    주문내역 확인
@endsection
@section('subtitle')
    <p>주문내역을 확인하는 페이지입니다.</p>
@endsection
@section('content')
    <div class="row g-gs">
        <div class="col-12">
            <div class="card card-bordered">
                <div class="card-inner">
                    <div class="form-group">
                        <label class="form-label">선택 조회</label>
                        <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-4 col-xl-3">
                                <div class="custom-controls">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" id="PAYMENT_WAITING" name="orderStatus"
                                            class="status-checkbox">
                                        <label for="PAYMENT_WAITING">결제 대기</label>
                                    </div>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" id="PAYED" name="orderStatus" class="status-checkbox">
                                        <label for="PAYED">결제 완료</label>
                                    </div>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" id="DELIVERING" name="orderStatus" class="status-checkbox">
                                        <label for="DELIVERING">배송 중</label>
                                    </div>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" id="DELIVERED" name="orderStatus" class="status-checkbox">
                                        <label for="DELIVERED">배송 완료</label>
                                    </div>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" id="EXCHANGED" name="orderStatus" class="status-checkbox">
                                        <label for="EXCHANGED">교환</label>
                                    </div>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" id="CANCELED" name="orderStatus" class="status-checkbox">
                                        <label for="CANCELED">취소</label>
                                    </div>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" id="RETURNED" name="orderStatus" class="status-checkbox">
                                        <label for="RETURNED">반품</label>
                                    </div>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" id="CANCELED_BY_NOPAYMENT" name="orderStatus"
                                            class="status-checkbox">
                                        <label for="CANCELED_BY_NOPAYMENT">미결제 취소</label>
                                    </div>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" id="PURCHASE_DECIDED" name="orderStatus"
                                            class="status-checkbox">
                                        <label for="PURCHASE_DECIDED">구매 확정</label>
                                    </div>
                                </div>

                            </div>
                            <p> 날짜 선택</p>
                            <div class="date-shortcuts">
                                <button data-range="today" class="btn btn-secondary">오늘</button>
                                <button data-range="week" class="btn btn-secondary">1주일</button>
                                <button data-range="month" class="btn btn-secondary">1개월</button>
                                <button data-range="3months" class="btn btn-secondary">3개월</button>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-4 col-xl-3">
                                <div class="form-group">
                                    <div class="form-control-wrap focused">
                                        <div class="form-icon form-icon-left">
                                            <em class="icon ni ni-calendar"></em>
                                        </div>
                                        <input type="text" id="startDate" class="form-control date-picker">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="form-control-wrap focused">
                                        <div class="form-icon form-icon-left">
                                            <em class="icon ni ni-calendar-alt"></em>
                                        </div>
                                        <input type="text" id="endDate" class="form-control date-picker">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="product-name">상품명</label>
                                    <div class="form-control-wrap">
                                        <input type="text" class="form-control" id="product-name">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label" for="product-code">상품코드</label>
                                    <div class="form-control-wrap">
                                        <input type="text" class="form-control" id="product-code">
                                    </div>
                                </div>
                                <button class="btn btn-primary">
                                    검색
                                </button>
                                <button class="btn btn-primary btn-reset">
                                    검색초기화
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card card-bordered card-preview">
            <div class="card-inner">
                <div class="table-responsive">
                    <table class="datatable-init table">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAll"></th>
                                <th>주문마켓</th>
                                <th>마켓주문번호</th>
                                <th>마켓상품주문번호</th>
                                <th>주문자</th>
                                <th>상품명</th>
                                <th>수량</th>
                                <th>마켓 판매가</th>
                                <th>총 판매가</th>
                                <th>마켓배송비</th>
                                <th>마켓주문상태</th>
                                <th>마켓주문일</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orderDetails['data']['data'] as $item)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="selected_orders[]"
                                            value="{{ $item['productOrder']['productOrderId'] ?? '' }}">
                                    </td>
                                    <td>{{ $item['order']['market'] ?? 'N/A' }}</td>
                                    <td>{{ $item['order']['orderId'] ?? 'N/A' }}</td>
                                    <td>{{ $item['productOrder']['productOrderId'] ?? 'N/A' }}</td>
                                    <td>{{ $item['order']['ordererName'] ?? 'N/A' }}</td>
                                    <td>{{ $item['productOrder']['productName'] ?? 'N/A' }}</td>
                                    <td>{{ $item['productOrder']['quantity'] ?? 'N/A' }}</td>
                                    <td>{{ $item['productOrder']['unitPrice'] ?? 'N/A' }}</td>
                                    <td>{{ $item['productOrder']['totalPaymentAmount'] ?? 'N/A' }}</td>
                                    <td>{{ $item['productOrder']['deliveryFeeAmount'] ?? 'N/A' }}</td>
                                    <td>{{ $item['productOrder']['productOrderStatus'] ?? 'N/A' }}</td>
                                    <td>{{ $item['order']['orderDate'] ?? 'N/A' }}</td>
                                </tr>
                            @endforeach


                        </tbody>
                    </table>
                </div>
            </div>
        </div><!-- .card-preview -->
    </div>
    </div>
    <pre>
        {{ print_r($orderList) }}
    </pre>
    <pre>
        {{ print_r($responseDetail) }}
    </pre>
    <pre>
        {{ print_r($orderDetails) }}
    </pre>
@endsection
@section('scripts')
    <script src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script>
        $(function() {
            // 날짜 선택기 초기화
            $('.date-picker').daterangepicker({
                singleDatePicker: true,
                showDropdowns: true,
                locale: {
                    format: 'YYYY-MM-DD'
                }
            });

            // 날짜 범위 설정 함수
            function setDateRange(startId, endId, start, end) {
                $(startId).data('daterangepicker').setStartDate(start);
                $(endId).data('daterangepicker').setEndDate(end);
            }

            // 버튼 이벤트 설정
            $('.date-shortcuts button').click(function() {
                let period = $(this).data('range');
                let start = moment();
                let end = moment();

                switch (period) {
                    case 'today':
                        start = end = moment();
                        break;
                    case 'week':
                        start = moment().subtract(6, 'days');
                        break;
                    case 'month':
                        start = moment().subtract(1, 'month');
                        break;
                    case '3months':
                        start = moment().subtract(3, 'months');
                        break;
                }

                setDateRange('#startDate', '#endDate', start, end);
            });
        });
        document.addEventListener('DOMContentLoaded', function() {
            // 전체 선택 체크박스
            var selectAllCheckbox = document.getElementById('selectAll');
            selectAllCheckbox.addEventListener('change', function() {
                // 모든 체크박스
                var checkboxes = document.querySelectorAll('.datatable-init tbody input[type="checkbox"]');
                for (var checkbox of checkboxes) {
                    checkbox.checked = this.checked;
                }
            });
        });



        document.addEventListener('DOMContentLoaded', function() {
            const orders = @json($orderDetails['data']['data']);

            document.querySelector('.btn-reset').addEventListener('click', function(e) {
                e.preventDefault();

                // 모든 입력 필드 초기화
                document.getElementById('startDate').value = '';
                document.getElementById('endDate').value = '';
                document.getElementById('product-name').value = '';
                document.getElementById('product-code').value = '';

                // 원래의 주문 목록을 다시 표시
                displayOrders(originalOrders);
            });

            function filterOrders() {
                const startDate = document.getElementById('startDate').value ? new Date(document.getElementById(
                    'startDate').value + 'T00:00:00Z') : null;
                const endDate = document.getElementById('endDate').value ? new Date(document.getElementById(
                    'endDate').value + 'T23:59:59Z') : null;
                const productName = document.getElementById('product-name').value.toLowerCase();
                const productCode = document.getElementById('product-code').value;
                const statusFilters = Array.from(document.querySelectorAll('.status-checkbox:checked')).map(el => el
                    .id);

                let filteredOrders = orders.filter(order => {
                    const orderDate = new Date(order.order.orderDate + 'Z');
                    const matchesDate = (!startDate || orderDate >= startDate) && (!endDate || orderDate <=
                        endDate);
                    const matchesName = !productName || order.productOrder.productName.toLowerCase()
                        .includes(productName);
                    const matchesCode = !productCode || order.productOrder.productCode === productCode;
                    const matchesStatus = !statusFilters.length || statusFilters.includes(order.productOrder
                        .productOrderStatus);

                    return matchesDate && matchesName && matchesCode && matchesStatus;
                });

                displayOrders(filteredOrders);
            }


            function displayOrders(filteredOrders) {
                const tbody = document.querySelector('.datatable-init tbody');
                tbody.innerHTML = ''; // Clear current rows
                filteredOrders.forEach(order => {
                    const row = `<tr>
                <td><input type="checkbox" name="selected_orders[]" value="${order.productOrder.productOrderId}"></td>
                <td>${order.order.market}</td>
                <td>${order.order.orderId}</td>
                <td>${order.productOrder.productOrderId}</td>
                <td>${order.order.ordererName}</td>
                <td>${order.productOrder.productName}</td>
                <td>${order.productOrder.quantity}</td>
                <td>${order.productOrder.unitPrice}</td>
                <td>${order.productOrder.totalPaymentAmount}</td>
                <td>${order.productOrder.deliveryFeeAmount}</td>
                <td>${order.productOrder.productOrderStatus}</td>
                <td>${order.order.orderDate}</td>
            </tr>`;
                    tbody.insertAdjacentHTML('beforeend', row);
                });
            }

            document.querySelector('.btn-primary').addEventListener('click', function(e) {
                e.preventDefault();
                filterOrders();
            });
        });
    </script>
@endsection
