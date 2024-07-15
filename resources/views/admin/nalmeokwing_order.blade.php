@extends('layouts.main')
@section('title')
    주문서 추출
@endsection
@section('subtitle')
    <p>
        오더윙 업데이트를 통해 수집된 날먹윙 주문을 각 B2B 업체의 대량 주문 엑셀 양식에 맞춰 추출합니다.
    </p>
@endsection
@section('content')
    <div class="row g-gs">
        <div class="col-12">
            <div class="card card-bordered">
                <div class="card-inner">
                    <div class="form-group">
                        <label class="form-label">B2B 업체 선택</label>
                        <div class="form-control-wrap d-flex text-nowrap">
                            <select class="form-select js-select2" id="vendorId" data-search="on">
                                @foreach ($data['vendors'] as $v)
                                    <option value="{{ $v->id }}">{{ $v->name }}</option>
                                @endforeach
                            </select>
                            <button class="btn btn-primary" onclick="extract();">추출하기</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        function extract() {
            popupLoader(1, "주문 내역을 선택한 B2B 업체의 주문 엑셀 양식에 작성하는 중입니다.");
            const vendorId = $('#vendorId').val();
            $.ajax({
                url: '/api/nalmewokwings/extract',
                type: 'GET',
                dataType: 'JSON',
                data: {
                    rememberToken,
                    vendorId
                },
                success: function(response) {
                    console.log(response);
                },
                error: AjaxErrorHandling
            });
        }
    </script>
@endsection
