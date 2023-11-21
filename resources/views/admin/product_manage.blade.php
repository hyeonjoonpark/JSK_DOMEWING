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
            <table class="datatable-init nk-tb-list nk-tb-ulist" data-auto-responsive="true" data-order='[[3, "asc"]]'>
                <thead>
                    <tr class="nk-tb-item nk-tb-head">
                        <th class="nk-tb-col nk-tb-col-check">
                            <div class="custom-control custom-control-sm custom-checkbox notext">
                                <input type="checkbox" class="custom-control-input" id="products">
                                <label class="custom-control-label" for="products"></label>
                            </div>
                        </th>
                        <th class="nk-tb-col"><span class="sub-text">상품 정보</span></th>
                        <th class="nk-tb-col tb-col-mb"><span class="sub-text">상품 가격</span></th>
                        <th class="nk-tb-col tb-col-md"><span class="sub-text">업로드 일자</span></th>
                        <th class="nk-tb-col tb-col-lg"><span class="sub-text">수정 일자</span></th>
                        <th class="nk-tb-col nk-tb-col-tools text-end">ACTION
                        </th>
                    </tr>
                </thead>
                <tbody id="productList">
                    @foreach ($products as $product)
                        <tr class="nk-tb-item">
                            <td class="nk-tb-col nk-tb-col-check">
                                <div class="custom-control custom-control-sm custom-checkbox notext">
                                    <input type="checkbox" class="custom-control-input" id="product{{ $product->id }}">
                                    <label class="custom-control-label" for="product{{ $product->id }}"></label>
                                </div>
                            </td>
                            <td class="nk-tb-col col-4">
                                <a href="{{ $product->productHref }}" target="_blank" class="user-card">
                                    <div class="user-avatar bg-dim-primary d-none d-sm-flex">
                                        <img src="{{ $product->productImage }}" alt="상품 대표 이미지">
                                    </div>
                                    <div class="user-info">
                                        <span class="tb-lead">{{ $product->productName }} <span
                                                class="dot dot-success d-md-none ms-1"></span></span>
                                        <span>{{ $product->keywords }}</span>
                                    </div>
                                </a>
                            </td>
                            <td class="nk-tb-col" data-order="{{ $product->productPrice }}">
                                <span class="tb-amount">{{ number_format($product->productPrice, 0) }} <span
                                        class="currency">원</span></span>
                            </td>
                            <td class="nk-tb-col">
                                <span>{{ $product->createdAt }}</span>
                            </td>
                            <td class="nk-tb-col" data-order="Email Verified - Kyc Unverified">
                                <span>{{ $product->updatedAt }}</span>
                            </td>
                            <td class="nk-tb-col">
                                <button class="btn btn-success">수정</button>
                                <button class="btn btn-danger">삭제</button>
                            </td>
                        </tr><!-- .nk-tb-item  -->
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
