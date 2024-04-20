@extends('partner.layouts.main')
@section('title')
    상품 업로드관
@endsection
@section('subtitle')
    <p>연동된 각종 오픈 마켓으로 상품 테이블을 업로드하는 페이지입니다.</p>
@endsection
@section('content')
    <div class="row g-gs">
        <div class="col">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">오픈 마켓 리스트</h6>
                    <p>업로드할 상품 테이블과 오픈 마켓을 선택해주세요.</p>
                    <div class="row g-gs">
                        @foreach ($openMarkets as $openMarket)
                            <div class="col-12 col-md-3">
                                <div class="custom-control custom-checkbox">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="openMarket{{ $openMarket->id }}" name="openMakrets"
                                            value="{{ $openMarket->id }}" class="custom-control-input">
                                        <label class="custom-control-label"
                                            for="openMarket{{ $openMarket->id }}">{{ $openMarket->name }}</label>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <div class="col-12">
                            <select class="form-select js-select2" data-search="on" name="partnerTableToken"
                                id="partnerTableToken">
                                @foreach ($partnerTables as $partnerTable)
                                    <option value="{{ $partnerTable->token }}">{{ $partnerTable->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 text-center">
                            <button class="btn btn-primary" onclick="upload();">업로드하기</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        function upload() {
            popupLoader(0, "해당 상품 테이블을 스마트 스토어로 전송 중입니다.");
            const vendorId = $('input[name="openMakrets"]:checked').val();
            const partnerTableToken = $('#partnerTableToken').val();
            $.ajax({
                url: '/api/partner/product/upload',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    vendorId,
                    partnerTableToken,
                    apiToken
                },
                success: ajaxSuccessHandling,
                error: AjaxErrorHandling
            });
        }
    </script>
@endsection
