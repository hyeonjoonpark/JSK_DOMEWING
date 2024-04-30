@extends('partner.layouts.main')
@section('title')
    스마트스토어 주문내역 확인
@endsection
@section('subtitle')
    <p>스마트 스토어 주문내역을 확인하는 페이지입니다.</p>
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
                            @foreach ($orderDetails as $item)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="selected_orders[]"
                                            value="{{ $item['productOrderId'] ?? '' }}">
                                    </td>
                                    <td>{{ $item['market'] ?? 'N/A' }}</td>
                                    <td>{{ $item['orderId'] ?? 'N/A' }}</td>
                                    <td>{{ $item['productOrderId'] ?? 'N/A' }}</td>
                                    <td>{{ $item['ordererName'] ?? 'N/A' }}</td>
                                    <!-- 상품명 클릭 시 모달을 표시하는 링크 -->
                                    <td><a href="#" class="product-name" data-toggle="modal"
                                            data-target="#exampleModal"
                                            data-product-name="{{ $item['productName'] ?? 'N/A' }}"
                                            data-quantity="{{ $item['quantity'] ?? 'N/A' }}"
                                            data-price="{{ $item['unitPrice'] ?? '0' }}">{{ $item['productName'] ?? 'N/A' }}</a>
                                    </td>
                                    <td>{{ $item['quantity'] ?? 'N/A' }}</td>
                                    <td>{{ $item['unitPrice'] ?? 'N/A' }}</td>
                                    <td>{{ $item['totalPaymentAmount'] ?? 'N/A' }}</td>
                                    <td>{{ $item['deliveryFeeAmount'] ?? 'N/A' }}</td>
                                    <td>{{ $item['productOrderStatus'] ?? 'N/A' }}</td>
                                    <td>{{ $item['orderDate'] ?? 'N/A' }}</td>
                                </tr>
                            @endforeach


                        </tbody>
                    </table>
                </div>
            </div>
        </div><!-- .card-preview -->
    </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">상품 상세 정보</h5>
                    <!-- 모달 닫기 버튼 (상단의 X 버튼) -->
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- 여기에 콘텐츠를 넣습니다 -->
                    <p id="modalProductContent">상품 정보가 여기에 표시됩니다.</p>
                </div>
                <div class="modal-footer">
                    <!-- 모달 닫기 버튼 (하단의 닫기 버튼) -->
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">닫기</button>
                </div>
            </div>
        </div>
    </div>



    <pre>
        {{ print_r($orderDetails) }}
    </pre>
@endsection
@section('scripts')
    <!-- jQuery 라이브러리 -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <!-- Bootstrap Bundle JS (Popper.js 포함) -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
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
            $('.date-shortcuts button').click(function() {
                var period = $(this).data('range');
                var start = moment();
                var end = moment();

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

                $('#startDate').data('daterangepicker').setStartDate(start);
                $('#endDate').data('daterangepicker').setEndDate(end);
            });

            // 전체 선택 체크박스
            $('#selectAll').change(function() {
                $('.datatable-init tbody input[type="checkbox"]').prop('checked', this.checked);
            });


            // 상품명 클릭 이벤트
            $('.product-name').click(function(event) {
                event.preventDefault();
                var productName = $(this).data('productName');
                var price = $(this).data('price');
                var quantity = $(this).data('quantity');
                var modalContentHtml = `
        <p><strong>상품명:</strong> ${productName}</p>
        <p><strong>가격:</strong> ₩${price}</p>
        <p><strong>수량:</strong> ${quantity}</p>
    `;

                $('#modalProductContent').html(modalContentHtml);
                $('#exampleModal').modal('show');
            });

            // 모달 닫기 이벤트
            $('#exampleModal').on('hidden.bs.modal', function() {
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();
            });

        });
    </script>
@endsection
