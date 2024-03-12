@extends('layouts.main')
@section('title')
    환경설정
@endsection
@section('subtitle')
    <p>도매윙 엔진의 설정을 변경합니다.</p>
@endsection
@section('content')
    <div class="row g-gs">
        <div class="col-12 col-md-6 mb-3">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">마진율</h6>
                    <p>B2B 업체별로 각 상품의 원가에 적용될 마진율을 설정합니다.</p>
                    @foreach ($b2Bs as $b2B)
                        <div class="form-group">
                            <label for="" class="form-label">{{ $b2B->name }}</label>
                            <div class="d-flex text-nowrap">
                                <div class="form-control-wrap w-100">
                                    <div class="form-text-hint">
                                        <span class="overline-title">%</span>
                                    </div>
                                    <input type="number" class="form-control" id="marginRate{{ $b2B->vendor_id }}"
                                        value="{{ $b2B->margin_rate }}" placeholder="마진율(%)를 기입해주세요."
                                        onkeydown="handleEnter(event, 'marginBtn{{ $b2B->vendor_id }}')">
                                </div>
                                <button class="btn btn-primary" id="marginBtn{{ $b2B->vendor_id }}"
                                    onclick="changeMarginRate({{ $b2B->vendor_id }});">변경</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 mb-3">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">배송비</h6>
                    <p>원청사별로 각 상품의 배송비를 설정합니다.</p>
                    @foreach ($vendors as $vendor)
                        <div class="form-group">
                            <label for="" class="form-label">{{ $vendor->name }}</label>
                            <div class="d-flex text-nowrap">
                                <div class="form-control-wrap w-100">
                                    <div class="form-text-hint">
                                        <span class="overline-title">원</span>
                                    </div>
                                    <input type="number" class="form-control" id="shippingFee{{ $vendor->vendor_id }}"
                                        onkeydown="handleEnter(event, 'shippingFeeBtn{{ $vendor->vendor_id }}')"
                                        value="{{ $vendor->shipping_fee }}" placeholder="배송비를 기입해주세요.">
                                </div>
                                <button class="btn btn-primary" id="shippingFeeBtn{{ $vendor->vendor_id }}"
                                    onclick="editShippingFee({{ $vendor->vendor_id }});">변경</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        var csrfToken = document.head.querySelector('meta[name="csrf-token"]').content;

        function handleEnter(event, btnID) {
            if (event.key === 'Enter') {
                event.preventDefault(); // 엔터키의 기본 동작(새 줄 삽입)을 방지
                document.getElementById(btnID).click(); // 버튼 클릭 이벤트 실행
            }
        }

        function changeMarginRate(mrID) {
            $('.btn').prop('disabled', true);
            const marginRate = $('#marginRate' + mrID).val();
            $.ajax({
                url: "/api/account-setting/margin-rate",
                type: "POST",
                dataType: "JSON",
                data: {
                    marginRate: marginRate,
                    rememberToken: rememberToken,
                    mrID: mrID
                },
                success: function(response) {
                    $('.btn').prop('disabled', false);
                    if (response.status) {
                        Swal.fire({
                            icon: "success",
                            title: "진행 성공",
                            text: response.return
                        }).then((result) => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "진행 실패",
                            text: response.return
                        });
                    }
                },
                error: function(response) {
                    $('.btn').prop('disabled', false);
                    console.log(response);
                }
            });
        }

        function editShippingFee(vendorID) {
            popupLoader(0, "기입하신 정보를 저장 중입니다.");
            const shippingFee = $('#shippingFee' + vendorID).val();
            $.ajax({
                url: "/api/account-setting/shipping-fee",
                type: "POST",
                dataType: "JSON",
                data: {
                    rememberToken: rememberToken,
                    vendorID: vendorID,
                    shippingFee: shippingFee
                },
                success: function(response) {
                    closePopup();
                    console.log(response);
                    if (response.status) {
                        Swal.fire({
                            icon: 'success',
                            title: '진행 성공',
                            text: response.return
                        }).then((result) => {
                            location.reload();
                        });
                    } else {
                        swalError(response.return);
                    }
                },
                error: function(response) {
                    closePopup();
                    console.log(response);
                    swalError(response.return);
                }
            });
        }
    </script>
@endsection
