@extends('layouts.main')
@section('title')
    상품 데이터 관리
@endsection
@section('subtitle')
    <p>
        상품 업로드를 위한 상품 데이터셋을 관리합니다.
    </p>
@endsection
@section('content')
    <div class="row g-gs">
        <div class="col">
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
    <script></script>
@endsection
