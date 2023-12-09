@extends('layouts.main')
@section('title')
    상품 키워드 테스트
@endsection
@section('subtitle')
    <p>
        아래 입력창에 상품명을 기입해주세요.
    </p>
@endsection
@section('content')
    <div class="row g-gs">
        <div class="col">
            <div class="form-group">
                <label for="productName" class="form-label">상품명</label>
                <input type="text" class="form-control" id="productName">
                <button class="btn btn-primary" onclick="initProductKeywords($('#productName').val());">확인</button>
            </div>
            <div class="form-group">
                <label for="" class="form-label">결과</label>
                <input type="text" class="form-control" id="productKeywords">
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <link rel="stylesheet" href="{{ asset('assets/css/editors/summernote.css') }}">
    <script src="{{ asset('assets/js/editors.js') }}"></script>
    <script src="{{ asset('assets/js/libs/editors/summernote.js') }}"></script>
    <script>
        function initProductKeywords(productName) {
            $.ajax({
                url: "/api/product/keywords",
                type: "POST",
                dataType: "JSON",
                data: {
                    productName: productName,
                    remember_token: '{{ Auth::user()->remember_token }}'
                },
                success: function(response) {
                    console.log(response);
                    $('#productKeywords').val(response.return);
                },
                error: function(response) {
                    console.log(response);
                }
            });
        }
    </script>
@endsection
