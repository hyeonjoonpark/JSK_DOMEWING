@extends('partner.layouts.main')
@section('title')
    주문내역 확인
@endsection
@section('subtitle')
    <p>주문내역을 확인하는 페이지입니다.</p>
@endsection
@section('content')
    <div class="row g-gs">
        <div class="col">
            <div class="card card-bordered">
                <div class="card-inner">
                    <div class="form-group">
                        <label class="form-label">선택 조회</label>
                        <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-6 col-lg-4 col-xl-3">
                                <div class="custom-controls">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" id="allOrders" name="orderStatus" value="allOrders">
                                        <label for="allOrders">전체 주문</label>
                                    </div>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" id="pendingPayment" name="orderStatus"
                                            value="pendingPayment">
                                        <label for="pendingPayment">입금 예정</label>
                                    </div>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" id="paymentComplete" name="orderStatus"
                                            value="paymentComplete">
                                        <label for="paymentComplete">입금 완료</label>
                                    </div>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" id="paymentConfirmed" name="orderStatus"
                                            value="paymentConfirmed">
                                        <label for="paymentConfirmed">결제 완료</label>
                                    </div>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" id="shipping" name="orderStatus" value="shipping">
                                        <label for="shipping">배송 중</label>
                                    </div>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" id="delivered" name="orderStatus" value="delivered">
                                        <label for="delivered">배송 완료</label>
                                    </div>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" id="purchaseConfirmed" name="orderStatus"
                                            value="purchaseConfirmed">
                                        <label for="purchaseConfirmed">구매 확정</label>
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
                                <button class="btn btn-primary">
                                    검색초기화
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>

    <div class="row g-gs">
        <div class="col">
            <div class="card card-bordered card-preview">
                <div class="card-inner">
                    <table class="datatable-init table">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAll"></th>
                                <th>Name</th>
                                <th>Position</th>
                                <th>Office</th>
                                <th>Age</th>
                                <th>Start date</th>
                                <th>Salary</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><input type="checkbox"></td>
                                <td>Tiger Nixon</td>
                                <td>System Architect</td>
                                <td>Edinburgh</td>
                                <td>61</td>
                                <td>2011/04/25</td>
                                <td>$320,800</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox"></td>
                                <td>Shou Itou</td>
                                <td>Regional Marketing</td>
                                <td>Tokyo</td>
                                <td>20</td>
                                <td>2011/08/14</td>
                                <td>$163,000</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox"></td>
                                <td>Michelle House</td>
                                <td>Integration Specialist</td>
                                <td>Sydney</td>
                                <td>37</td>
                                <td>2011/06/02</td>
                                <td>$95,400</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox"></td>
                                <td>Suki Burks</td>
                                <td>Developer</td>
                                <td>London</td>
                                <td>53</td>
                                <td>2009/10/22</td>
                                <td>$114,500</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox"></td>
                                <td>Prescott Bartlett</td>
                                <td>Technical Author</td>
                                <td>London</td>
                                <td>27</td>
                                <td>2011/05/07</td>
                                <td>$145,000</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox"></td>
                                <td>Gavin Cortez</td>
                                <td>Team Leader</td>
                                <td>San Francisco</td>
                                <td>22</td>
                                <td>2008/10/26</td>
                                <td>$235,500</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox"></td>
                                <td>Martena Mccray</td>
                                <td>Post-Sales support</td>
                                <td>Edinburgh</td>
                                <td>46</td>
                                <td>2011/03/09</td>
                                <td>$324,050</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox"></td>
                                <td>Unity Butler</td>
                                <td>Marketing Designer</td>
                                <td>San Francisco</td>
                                <td>47</td>
                                <td>2009/12/09</td>
                                <td>$85,675</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div><!-- .card-preview -->
        </div>
    </div>
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
    </script>
@endsection
