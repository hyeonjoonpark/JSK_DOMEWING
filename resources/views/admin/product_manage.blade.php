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
                                <button class="btn btn-success" onclick="initEditProduct({{ $product->id }});">수정</button>
                                <button class="btn btn-danger">삭제</button>
                            </td>
                        </tr><!-- .nk-tb-item  -->
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="modal fade" id="modalForm">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">상품 수정</h5>
                    <a href="#" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <em class="icon ni ni-cross"></em>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="form-validate is-alter">
                        <div class="form-group">
                            <label class="form-label" for="categoryId">상품 카테고리</label>
                            <div class="form-control-wrap d-flex text-nowrap mb-3">
                                <input type="text" class="form-control" placeholder="카테고리 검색 키워드를 입력해주세요."
                                    id="categoryKeyword">
                                <button class="btn btn-primary" onclick="categorySearch();"
                                    id="categorySearchBtn">검색</button>
                            </div>
                            <div class="form-control-wrap">
                                <select name="categoryId" id="categoryId" class="form-select"></select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="productName">상품명</label>
                            <div class="form-control-wrap">
                                <input type="text" class="form-control" id="productName" placeholder="상품명을 기입해주세요."
                                    onchange="$(this).val(nameFormatter($(this).val()));">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="invoiceName">택배송장명</label>
                            <div class="form-control-wrap">
                                <input type="text" class="form-control" id="invoiceName" placeholder="택배송장명을 기입해주세요."
                                    onchange="$(this).val(nameFormatter($(this).val()));">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="productKeywords">키워드</label>
                            <div class="form-control-wrap">
                                <input type="text" class="form-control" id="productKeywords"
                                    placeholder="상품 키워드를 , 단위로 구분하여 최소 5개를 기입해주세요.">
                            </div>
                        </div>
                        {{-- <div class="form-group">
                            <label class="form-label" for="productModel">모델명</label>
                            <div class="form-control-wrap">
                                <input type="text" class="form-control" id="productModel" placeholder="모델명을 기입해주세요.">
                            </div>
                        </div> --}}
                        <div class="form-group row">
                            <div class="col">
                                <label class="form-label" for="productPrice">상품 가격</label>
                                <div class="form-control-wrap">
                                    <input type="text" class="form-control" id="productPrice"
                                        placeholder="상품 가격을 기입해주세요.">
                                </div>
                            </div>
                            <div class="col">
                                <label class="form-label" for="shippingCost">배송비</label>
                                <div class="form-control-wrap">
                                    <input type="number" class="form-control" id="shippingCost"
                                        placeholder="상품 가격을 기입해주세요." value="3000" oninput="priceFormat(this);">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="productVendor">제조사/브랜드</label>
                            <input type="text" class="form-control" value="LADAM">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="productVendor">상품 대표 이미지</label>
                            <div class="w-100">
                                <img src="" alt="상품 대표 이미지" id="productImage">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">상품 상세설명 이미지</label>
                            <div class="summernote-basic" id="summernote"></div>
                            {{-- <input type="file" class="form-control" id="descImage" name="descImage" accept="image/*"> --}}
                        </div>
                        <div class="form-group">
                            <label class="form-label">상품정보고시</label>
                            <select class="form-select" name="product_information" id="product_information">
                                {{-- @foreach ($productInformation as $i)
                                    <option value="{{ $i->id }}">{{ $i->content }}</option>
                                @endforeach --}}
                            </select>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-lg btn-primary" onclick="productCollect();">가공
                                완료</button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <span class="sub-text">Powered by ColdWatermelon</span>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <link rel="stylesheet" href="{{ asset('assets/css/editors/summernote.css') }}">
    <script src="{{ asset('assets/js/editors.js') }}"></script>
    <script src="{{ asset('assets/js/libs/editors/summernote.js') }}"></script>
    <script>
        function productsUpload() {
            $('.btn').prop('disabled', true);
            $.ajax({
                url: '/api/product/upload',
                type: "POST",
                dataType: "JSON",
                data: {
                    rememberToken: '{{ Auth::user()->remember_token }}'
                },
                success: function(response) {
                    $('.btn').prop('disabled', false);
                    console.log(response);
                },
                error: function(response) {
                    console.log(response);
                }
            });
        }

        function initEditProduct(productId) {
            const products = {{ $products }};
            console.log(products);
        }
    </script>
@endsection
