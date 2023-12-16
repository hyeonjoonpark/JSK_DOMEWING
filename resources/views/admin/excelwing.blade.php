@extends('layouts.main')
@section('title')
    엑셀윙
@endsection
@section('subtitle')
    <p>
        엑셀윙은 B2B 업체별로 요구되는 대량 상품 등록을 위한 엑셀 양식에 맞추어 상품 데이터를 재구성하고 있습니다.
    </p>
@endsection
@section('content')
    <div class="row g-gs">
        <div class="col">
            <div class="card card-bordered mb-3">
                <div class="card-inner">
                    <h5 class="card-title">자동 엑셀 폼</h5>
                    <h6 class="card-subtitle mb-2">B2B 업체 및 등록을 위해 상품셋을 선택하신 후, 자동 엑셀 폼을 생성하기 위해 '엑셀 추출하기' 버튼을 클릭해 주세요.</h6>
                    <div class="form-group">
                        <label for="" class="form-label">B2B 업체</label>
                        <div class="row">
                            @foreach ($b2Bs as $b2B)
                                <div class="col-3 mb-3">
                                    <div class="custom-control custom-checkbox">
                                        <div class="custom-control custom-radio">
                                            <input type="radio" id="seller{{ $b2B->id }}" name="sellers"
                                                value="{{ $b2B->id }}" class="custom-control-input"
                                                {{ $loop->first ? 'checked' : '' }}>
                                            <label class="custom-control-label"
                                                for="seller{{ $b2B->id }}">{{ $b2B->name }}</label>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            <div class="d-flex justify-content-center">
                                <button class="btn btn-warning" onclick="initExcelwing();">엑셀 추출하기</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <p>총 <b class="font-weight-bold">{{ number_format(count($products), 0) }}</b>개의 상품이 준비됐습니다.</p>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col"><input type="checkbox" onclick="selectAll(this);"></th>
                        <th scope="col">상품 대표 이미지</th>
                        <th scope="col">상품명</th>
                        <th scope="col">가격</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                        <tr>
                            <td><input type="checkbox" name="selectedProducts" value="{{ $product->id }}"></td>
                            <td><a href="{{ $product->productHref }}" target="_blank"><img
                                        src="{{ $product->productImage }}" alt="상품 대표 이미지" width="100"
                                        height="100"></a>
                            </td>
                            <td><a href="{{ $product->productHref }}" target="_blank">{{ $product->productName }}</a></td>
                            <td>{{ number_format($product->productPrice, 0) }}원</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="modal fade" id="selectCategory" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">엑셀윙에 오신 것을 환영합니다.</h5>
                </div>
                <div class="modal-body">
                    <img src="{{ asset('media/Asset_Notif_Success.svg') }}" alt="엑셀윙 헤더 이미지" class="w-100">
                    <h6 class="mt-3">
                        상품 데이터를 불러오기 위해 원하는 카테고리를 선택해주세요.
                    </h6>
                    <div class="form-group">
                        <label for="categoryID">카테고리</label>
                        <div class="form-control-wrap">
                            <select class="form-select js-select2" data-search="on" id="categoryID">
                                @foreach ($ownerclanCategories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-center">
                    <button type="button" class="btn btn-primary"
                        onclick="selectCategory($('#categoryID').val());">선택하기</button>
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
        $(document).ready(function() {
            @if (!$selectedCategory)
                $('#selectCategory').modal('show');
            @endif
        });

        function selectCategory(categoryID) {
            window.location.replace("?categoryID=" + categoryID);
        }

        function initExcelwing() {
            // 'selectedProducts'라는 이름을 가진 체크된 모든 체크박스를 가져옵니다.
            const selectedProducts = document.querySelectorAll('input[name="selectedProducts"]:checked');
            // 선택된 상품의 ID를 배열로 추출합니다.
            const productIDs = Array.from(selectedProducts).map(product => product.value);
            const vendorID = $("input[name='sellers']:checked").val();
            requestExcelwing(productIDs, vendorID);
        }

        function requestExcelwing(productIDs, vendorID) {
            popupLoader(1, "선택하신 상품셋을 B2B 업체를 위한 대량 등록 양식에 맞추어 엑셀 파일로 작성 중입니다.");
            $.ajax({
                url: "/api/product/excelwing",
                type: "POST",
                dataType: "JSON",
                data: {
                    remember_token: "{{ Auth::user()->remember_token }}",
                    productIDs: productIDs,
                    vendorID: vendorID,
                    categoryID: categoryID
                },
                success: function(response) {
                    closePopup();
                    console.log(response);
                },
                error: function(response) {
                    closePopup();
                    console.log(response);
                }
            });
        }
    </script>
@endsection
