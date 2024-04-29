@extends('partner.layouts.main')
@section('title')
    주문내역 확인
@endsection
@section('subtitle')
    <p>주문내역을 확인하는 페이지입니다.</p>
@endsection
@section('content')
    <pre>
        {{ print_r($orderList) }}
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
    </script>
@endsection
