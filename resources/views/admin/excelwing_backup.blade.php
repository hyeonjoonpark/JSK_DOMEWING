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
                                            <label class="custom-control-label" for="seller{{ $b2B->id }}"
                                                id="b2BName{{ $b2B->id }}">{{ $b2B->name }}</label>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            <div class="d-flex justify-content-center">
                                <button class="btn btn-warning" onclick="initExcelwing();">엑셀 추출하기</button>
                            </div>
                        </div>
                    </div>
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
                                            <label class="custom-control-label" for="seller{{ $b2B->id }}"
                                                id="b2BName{{ $b2B->id }}">{{ $b2B->name }}</label>
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
    {{-- <div class="modal fade" id="selectCategory" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
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
    </div> --}}
    {{-- <div class="modal fade" id="categoryMapping" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">카테고리 매핑</h5>
                </div>
                <div class="modal-body">
                    <img src="{{ asset('media/Asset_Notif_Success.svg') }}" alt="엑셀윙 헤더 이미지" class="w-100">
                    <div class="form-group">
                        <label for="categoryID">선택된 카테고리</label>
                        <div class="form-control-wrap">
                            <select class="form-select js-select2" disabled>
                                @if (isset($selectedCategory))
                                    <option>{{ $selectedCategory->name }}</option>
                                @endif
                            </select>
                        </div>
                    </div>
                    <h6>
                        선택하신 B2B 업체의 카테고리를 선택해주세요.
                    </h6>
                    <div class="form-group">
                        <label class="form-label" for="categoryCode" id="selectedB2BName"></label>
                        <div class="form-control-wrap d-flex text-nowrap mb-3">
                            <input type="text" class="form-control" placeholder="카테고리 검색 키워드를 입력해주세요."
                                id="categoryKeyword" onclick="handleEnter(event, 'categorySearchBtn')">
                            <button class="btn btn-primary" onclick="categorySearch();" id="categorySearchBtn">검색</button>
                        </div>
                        <div class="form-control-wrap">
                            <select name="categoryCode" id="categoryCode" class="form-select"></select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-center">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">종료하기</button>
                    <button type="button" class="btn btn-success" onclick="requestExcelwing();">선택하기</button>
                </div>
            </div>
        </div>
    </div> --}}
@endsection
@section('scripts')
    <link rel="stylesheet" href="{{ asset('assets/css/editors/summernote.css') }}">
    <script src="{{ asset('assets/js/editors.js') }}"></script>
    <script src="{{ asset('assets/js/libs/editors/summernote.js') }}"></script>
    <script>
        var productIDs, vendorID;
        $(document).ready(function() {
            @if (!$selectedCategory)
                $('#selectCategory').modal('show');
            @endif
        });

        function handleEnter(event, btnID) {
            if (event.key === 'Enter') {
                event.preventDefault(); // 엔터키의 기본 동작(새 줄 삽입)을 방지
                document.getElementById(btnID).click(); // 버튼 클릭 이벤트 실행
            }
        }

        function categorySearch() {
            const keyword = $("#categoryKeyword").val();
            $("#categorySearchBtn").html("검색 중...");
            $("#categorySearchBtn").prop("disabled", true);
            $.ajax({
                url: '/api/product/category-mapping',
                type: 'post',
                dataType: 'json',
                data: {
                    vendorID: vendorID,
                    keyword: keyword
                },
                success: function(result) {
                    $("#categorySearchBtn").html("검색");
                    $("#categorySearchBtn").prop("disabled", false);
                    if (result.status == 1) {
                        let html = "";
                        for (let i = 0; i < result.return.length; i++) {
                            html += "<option value='" + result.return[i].code + "'>" + result.return[i]
                                .name + "</option>";
                        }
                        $("#categoryCode").html(html);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: '진행 실패',
                            text: result.return
                        });
                    }
                },
                error: function(response) {
                    $("#categorySearchBtn").html("검색");
                    $("#categorySearchBtn").prop("disabled", false);
                    console.log(response);
                }
            });
        }

        function selectCategory(categoryID) {
            window.location.replace("?categoryID=" + categoryID);
        }

        function initExcelwing() {
            // 'selectedProducts'라는 이름을 가진 체크된 모든 체크박스를 가져옵니다.
            const selectedProducts = document.querySelectorAll('input[name="selectedProducts"]:checked');
            // 선택된 상품의 ID를 배열로 추출합니다.
            productIDs = Array.from(selectedProducts).map(product => product.value);
            vendorID = $("input[name='sellers']:checked").val();
            getB2BName(vendorID);
        }

        function getB2BName(b2BID) {
            const b2BName = $("#b2BName" + b2BID).html();
            $("#selectedB2BName").html(b2BName + " 카테고리 검색");
            $("#categoryMapping").modal("show");
        }

        function requestExcelwing() {
            closePopup();
            popupLoader(1, "선택하신 상품셋을 B2B 업체를 위한 대량 등록 양식에 맞추어 엑셀 파일로 작성 중입니다.");
            const categoryCode = $("#categoryCode").val();
            $.ajax({
                url: "/api/product/excelwing",
                type: "POST",
                dataType: "JSON",
                data: {
                    remember_token: "{{ Auth::user()->remember_token }}",
                    productIDs: productIDs,
                    vendorID: vendorID,
                    categoryCode: categoryCode
                },
                success: function(response) {
                    console.log(response);
                    closePopup();
                    if (response.status) {
                        let html =
                            '<img class="w-100" src="{{ asset('media/Asset_Notif_Success.svg') }}"><h4 class="swal2-title mt-5">엑셀 파일을 성공적으로 추출했습니다.<br>아래 링크를 눌러 다운로드를 진행해주세요.</h4>';
                        html += "<a href='" + response.return+"' target='_blank' download>다운로드</a>";
                        Swal.fire({
                            html: html
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            html: '<img class="w-100" src="{{ asset('media/Asset_Notif_Error.svg') }}"><h4 class="swal2-title mt-5">' +
                                response.return+'</h4>'
                        });
                    }
                },
                error: function(response) {
                    closePopup();
                    console.log(response);
                }
            });
        }
    </script>
@endsection
