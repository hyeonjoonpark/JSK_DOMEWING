@extends('layouts.main')
@section('title')
    수집된 상품 데이터
@endsection
@section('subtitle')
    <p>
        수집된 상품 데이터는 매일 오전 00시 정각에 업로드가 진행됩니다.
    </p>
@endsection
@section('content')
    <div class="row g-gs">
        <div class="col">
            <button class="btn btn-warning mb-5" onclick="productsUpload();">업로드 테스트</button>
            <table id="productTable" class="datatable-init-export nowrap table" data-export-title="Export"
                data-order='[[3, "asc"]]'>
                <thead>
                    <tr>
                        <th>이미지</th>
                        <th>상품명</th>
                        <th>가격</th>
                        <th>ACTION</th>
                    </tr>
                </thead>
                <tbody id="productList">
                    @foreach ($products as $product)
                        <tr>
                            <td><img src="{{ $product->productImage }}" width="300" alt="상품 이미지"></td>
                            <td>{{ $product->productName }}</td>
                            <td>{{ $product->productPrice }}원</td>
                            <td>
                                <button class="btn btn-success">수정</button>
                                <button class="btn btn-danger">삭제</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
@section('scripts')
    <link rel="stylesheet" href="{{ asset('assets/css/editors/summernote.css') }}">
    <script src="{{ asset('assets/js/editors.js') }}"></script>
    <script src="{{ asset('assets/js/libs/editors/summernote.js') }}"></script>
    <script>
        function productsUpload() {
            $.ajax({
                url: '/api/product/upload',
                type: "POST",
                dataType: "JSON",
                data: {
                    remember_token: '{{ Auth::user()->remember_token }}'
                },
                success: function(response) {
                    console.log(response);
                },
                error: function(response) {
                    console.log(response);
                }
            });
        }
    </script>
@endsection
