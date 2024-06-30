@extends('layouts.main')
@section('title')
    날먹윙 상품 생성
@endsection
@section('subtitle')
    <p>
        엑셀 파일 업로드를 통해 날먹윙 상품을 생성하는 페이지입니다.
    </p>
@endsection
@section('content')
    <div class="row g-gs">
        <div class="col-12">
            <div class="card card-bordered">
                <div class="card-inner">
                    <div class="form-group">
                        <label class="form-label">B2B 업체</label>
                        <ul class="custom-control-group g-3 align-center">
                            @foreach ($vendors as $index => $v)
                                <li>
                                    <div class="custom-control custom-control-sm custom-radio">
                                        <input type="radio" class="custom-control-input" id="vendor{{ $index }}"
                                            name="vendorId" value="{{ $v->id }}">
                                        <label class="custom-control-label"
                                            for="vendor{{ $index }}">{{ $v->name }}</label>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="form-group">
                        <label class="form-label">엑셀 업로드</label>
                        <div class="form-control-wrap d-flex text-nowrap">
                            <input type="file" class="form-control" id="excel">
                            <button class="btn btn-primary" onclick="store();">업로드</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        async function store() {
            popupLoader(1, '업로드된 엑셀 파일로부터 상품들을 생성 중입니다.');
            const vendorId = $('input[name="vendorId"]:checked').val();
            const excel = $('#excel')[0].files[0];
            const data = new FormData();
            data.append('vendorId', vendorId);
            data.append('excel', excel);
            data.append('rememberToken', rememberToken);
            $.ajax({
                url: '/api/nalmeokwing/store',
                type: 'POST',
                dataType: 'JSON',
                processData: false,
                contentType: false,
                data,
                success: ajaxSuccessHandling,
                error: AjaxErrorHandling
            });
        }
    </script>
@endsection
