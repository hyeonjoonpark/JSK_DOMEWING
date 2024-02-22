@extends('layouts.main')
@section('title')
    뉴 마인윙
@endsection
@section('subtitle')
    <p>
        더욱 강력해진 엔진 뉴 마인윙과 함께 상품 데이터셋을 수집합니다.
    </p>
@endsection
@php
    set_time_limit(0);
    ini_set('memory_limit', '-1');
@endphp
@section('content')
    <div class="row g-gs">
        <div class="col">
            <div class="card card-bordered preview">
                <div class="card-inner">
                    <h5 class="card-title">상품 리스트 추출</h5>
                    <h6 class="card-subtitle mb-2">원청사를 선택한 후, 상품 리스트 페이지 URL을 기입해주세요.</h6>
                    <div class="form-group">
                        <label class="form-label">원청사</label>
                        <div class="row">
                            @foreach ($sellers as $seller)
                                <div class="col-12 col-md-3 mb-3">
                                    <div class="custom-control custom-checkbox">
                                        <div class="custom-control custom-radio">
                                            <input type="radio" id="seller{{ $seller->vendor_id }}" name="sellers"
                                                value="{{ $seller->vendor_id }}" class="custom-control-input"
                                                @if ($loop->first) checked @endif>
                                            <label class="custom-control-label"
                                                for="seller{{ $seller->vendor_id }}">{{ $seller->name }}</label>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">상품 리스트 페이지 URL</label>
                        <div class="row g-0">
                            <div class="col">
                                <input type="text" class="form-control" id="listURL"
                                    placeholder="상품 리스트 페이지의 URL을 기입해주세요." onkeydown="handleEnter(event, 'searchBtn')" />
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-primary" id="searchBtn" onclick="initMinewing();">마인윙</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-gs mt-3">
        <div class="col">
            <div class="card card-bordered preview">
                <div class="card-inner">
                    <h5 class="card-title">상품 가공 및 수집</h5>
                    <h6 class="card-subtitle mb-2">검색 결과로부터 상품을 가공 및 수집합니다.</h6>
                    <p class="card-text">총 <span class="fw-bold" id="numResult"></span>건이 검색되었습니다</p>
                    <div class="w-100 d-flex justify-content-center">
                        <button class="btn btn-warning" id="bulkCollectBtn" onclick="initScrape();">가공 및
                            수집하기</button>
                    </div>
                    <div id="collectResult" class="table-responsive mt-5">
                        <table class="table table-striped text-nowrap align-middle">
                            <thead>
                                <tr>
                                    <th scope="col"><input type="checkbox" onclick="selectAll(this);"></th>
                                    <th scope="col">상품 대표 이미지</th>
                                    <th scope="col">상품명</th>
                                    <th scope="col">가격</th>
                                </tr>
                            </thead>
                            <tbody id="minewingResult">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal" tabindex="-1" role="dialog" id="productSaveForm" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">가공된 상품셋 저장</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label class="form-label" for="categoryId">상품 카테고리</label>
                                <div class="form-control-wrap d-flex text-nowrap mb-3">
                                    <input type="text" class="form-control" placeholder="카테고리 검색 키워드를 입력해주세요."
                                        onkeydown="handleEnter(event, 'categorySearchBtn')" id="categoryKeyword">
                                    <button class="btn btn-primary" onclick="categorySearch();"
                                        id="categorySearchBtn">검색</button>
                                </div>
                                <div class="form-control-wrap">
                                    <select name="categoryId" id="categoryId" class="form-select"></select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="productKeywords">상품 검색 키워드</label>
                                <div class="form-control-wrap">
                                    <input type="text" class="form-control" id="productKeywords"
                                        placeholder="상품 키워드를 , 단위로 구분하여 최소 5개를 기입해주세요.">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="initSave();">저장하기</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">종료하기</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal" tabindex="-1" role="dialog" id="handleDupNamesModal" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">중복 상품명 검출</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="duplicatedType">
                    <div class="row">
                        <div class="col-12 col-md-6 mb-3">
                            <h6 class="mb-3">수정할 상품</h6>
                            <img id="editProductImage" class="w-100 mb-3" src="" alt="중복 상품명 이미지">
                            <h6 class="mb-3">원상품명: <span id="editProductNameOri"></span></h6>
                            <a href="" id="editProductHref" target="_blank">상품 상세보기</a>
                        </div>
                        <div class="col-12 col-md-6 mb-3">
                            <h6 class="mb-3">중복된 상품</h6>
                            <img id="duplicatedProductImage" class="w-100 mb-3" src="" alt="중복 상품명 이미지">
                            <h6 class="mb-3">원상품명: <span id="duplicatedProductNameOri"></span></h6>
                            <a href="" id="duplicatedProductHref" target="_blank">상품 상세보기</a>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="">수정할 상품명</label>
                        <input type="text" class="form-control" id="editNewName">
                    </div>
                    <div class="form-group">
                        <label for="">중복된 상품명</label>
                        <input type="text" class="form-control" id="duplicatedNewName">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="initHandleDupName();">저장하기</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">종료하기</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        var rememberToken = '{{ Auth::user()->remember_token }}';
        var varIndex, varDupIndex, varProducts;
        var audioMining = new Audio('{{ asset('assets/audio/mining.mp3') }}');
        var audioCollect = new Audio('{{ asset('assets/audio/collect.mp3') }}');
        var audioSuccess = new Audio('{{ asset('assets/audio/success.mp3') }}');
        $(document).on('click', '#selectAll', function() {
            const isChecked = $(this).is(':checked');
            $('input[name="selectedProducts"]').prop('checked', isChecked);
        });

        function handleEnter(event, btnID) {
            if (event.key === 'Enter') {
                event.preventDefault(); // 엔터키의 기본 동작(새 줄 삽입)을 방지
                document.getElementById(btnID).click(); // 버튼 클릭 이벤트 실행
            }
        }

        function initMinewing() {
            popupLoader(0, "상품 데이터셋을 마이닝해올게요.");
            const listURL = $("#listURL").val();
            const vendorID = $('input[name="sellers"]:checked').val();
            runMinewing(listURL, vendorID, rememberToken);
        }

        function runMinewing(listURL, vendorID, rememberToken) {
            $.ajax({
                url: "/api/product/new-minewing",
                type: "POST",
                dataType: "JSON",
                data: {
                    rememberToken: rememberToken,
                    vendorID: vendorID,
                    listUrl: listURL
                },
                success: function(response) {
                    console.log(response);
                    if (response.status) {
                        const products = response.return;
                        updateMinewingResult(products);
                        closePopup();
                        audioMining.play();
                        swalSuccess('"상품 데이터셋을 성공적으로 가져왔어요!"');
                    } else {
                        closePopup();
                        swalError(response.return);
                    }
                },
                error: function(response) {
                    console.log(response);
                    closePopup();
                    swalError('예기치 못한 에러가 발생했어요.');
                }
            });
        }

        function updateMinewingResult(products) {
            $('#minewingResult').html("");
            let html = "";
            for (let i = 0; i < products.length; i++) {
                const name = products[i].name;
                const price = products[i].price;
                const image = products[i].image;
                const href = products[i].href;
                html += "<tr>";
                html += "<td><input type='checkbox' name='selectedProducts' value='" + i + "'></td>";
                html += "<td><a href='" + href + "' target='_blank' id='productHref" + i + "'><img src='" + image +
                    "' alt='상품 이미지' width='100' height='100'></a></td>";
                html += "<td><a href='" + href + "' target='_blank' id='duplicatedProductNameOri" + i + "'>" + name +
                    "</a></td>";
                html += "<td><a href='" + href + "' target='_blank'>" + numberFormat(price, 0) + "원</a></td>";
                html += "</tr>";
            }
            $('#numResult').html(numberFormat(products.length));
            $('#minewingResult').html(html);
        }

        function initScrape() {
            const selectedProducts = $('input[name="selectedProducts"]:checked');
            const productHrefs = [];
            selectedProducts.each(function() {
                const index = this.value;
                const productHref = $('#productHref' + index).attr('href');
                productHrefs.push(productHref);
            });
            popupLoader(1, '"각 상품의 고유 URL을 사용하여 중복 상품을 검사하고 있어요."');
            runUniqueProductHrefs(productHrefs);
        }

        function runUniqueProductHrefs(productHrefs) {
            $.ajax({
                url: "/api/minewing/unique-product-hrefs",
                type: "POST",
                dataType: "JSON",
                data: {
                    productHrefs: productHrefs
                },
                success: function(response) {
                    console.log(response);
                    closePopup();
                    const status = response.status;
                    if (status == true) {
                        const numDuplicated = response.return.numDuplicated;
                        const productHrefs = response.return.productHrefs;
                        popupLoader(0, '"총 ' + numDuplicated + '개의 중복 상품을 검열했어요.<br>각 상품의 상세 정보를 스크래핑해올게요."');
                        const vendorID = $('input[name="sellers"]:checked').val();
                        runScrape(productHrefs, vendorID);
                    } else {
                        const message = response.return.message;
                        swalError(message);
                    }
                },
                error: function(response) {
                    closePopup();
                    console.log(response);
                }
            });
        }

        function runScrape(productHrefs, vendorID) {
            $.ajax({
                url: '/api/product/process',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    rememberToken: rememberToken,
                    vendorID: vendorID,
                    productHrefs: productHrefs
                },
                success: function(response) {
                    console.log(response);
                    const status = response.status;
                    closePopup();
                    if (status) {
                        popupLoader(1, '"상품명을 가공 중이에요."');
                        runManufacture(response.return);
                    } else {
                        swalError(response.return);
                    }
                },
                error: function(error) {
                    console.log(error);
                    closePopup();
                    swalError('예기치 못한 에러가 발생했습니다. 기술자에게 문의해 주십시오.');
                }
            });
        }

        function runManufacture(products) {
            $.ajax({
                url: '/api/minewing/manufacture',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    products
                },
                success: handleManufactureSuccess,
                error: errorHandle
            });
        }

        function handleManufactureSuccess(response) {
            console.log(response);
            closePopup();

            if (response.status) {
                varProducts = response.return;
                audioCollect.play();
                $('#productSaveForm').modal('show');
            } else {
                // Handle failed response
                updateDuplicatedDetails(response);
            }
        }

        function errorHandle(response) {
            closePopup();
            console.log(response);
            swalError('예기치 못한 에러가 발생했어요.');
        }

        function updateDuplicatedDetails(response) {
            const {
                index,
                duplicatedProductName,
                products,
                type
            } = response.return;
            varProducts = products;
            varIndex = index;

            $('#duplicatedType').val(type);
            updateEditProductDetails(products, index, duplicatedProductName); // Update UI for the product to edit

            if (type == 'FROM_ARRAY') {
                handleFromArrayType(response.return);
            } else {
                handleOtherType(response.return);
            }
        }

        function updateEditProductDetails(products, index, duplicatedProductName) {
            $('#editProductImage').attr('src', products[index].productImage);
            $('#editProductNameOri').html(products[index].productName);
            $('#editProductHref').attr('href', products[index].productHref);
            $('#editNewName').val(duplicatedProductName);
        }

        function handleFromArrayType(details) {
            varDupIndex = details.duplicatedIndex;
            const duplicatedProductNameOri = $('#duplicatedProductNameOri' + varDupIndex).html();
            const products = varProducts;

            $('#duplicatedProductImage').attr('src', products[varDupIndex].productImage);
            $('#duplicatedProductNameOri').html(duplicatedProductNameOri);
            $('#duplicatedProductHref').attr('href', products[varDupIndex].productHref);
            $('#duplicatedNewName').val(products[varDupIndex].productName);
            $("#handleDupNamesModal").modal('show');
        }

        function handleOtherType(details) {
            const duplicatedProduct = details.duplicatedProduct;
            $('#duplicatedProductImage').attr('src', duplicatedProduct.productImage);
            $('#duplicatedProductNameOri').html(duplicatedProduct.productName);
            $('#duplicatedProductHref').attr('href', duplicatedProduct.productHref);
            $('#duplicatedNewName').val('상품 DB로부터 검출된 상품은 상품명 변경이 불가합니다.').attr('disabled', true);
            $("#handleDupNamesModal").modal('show');
        }

        function initHandleDupName() {
            const type = $('#duplicatedType').val();
            const editedProductName = $('#editNewName').val(); // 중복을 줄이기 위해 함수 시작 부분으로 이동

            // 'FROM_ARRAY' 타입일 때만 duplicatedProductName 업데이트
            if (type == 'FROM_ARRAY') {
                const duplicatedProductName = $('#duplicatedNewName').val();
                varProducts[varDupIndex].productName = duplicatedProductName.trim();
            }

            // editedProductName 업데이트는 두 경우 모두에서 수행됨
            varProducts[varIndex].productName = editedProductName.trim();

            // 수정된 varProducts로 다음 단계 실행
            closePopup();
            popupLoader(1, '"상품명을 가공 중이에요."');
            runManufacture(varProducts);
        }

        function categorySearch() {
            const keyword = $("#categoryKeyword").val();
            $("#categorySearchBtn").html("검색 중...");
            $("#categorySearchBtn").prop("disabled", true);
            $.ajax({
                url: '/api/product/category',
                type: 'post',
                dataType: 'json',
                data: {
                    keyword: keyword
                },
                success: function(result) {
                    console.log(result);
                    $("#categorySearchBtn").html("검색");
                    $("#categorySearchBtn").prop("disabled", false);
                    if (result.status == 1) {
                        let html = "";
                        for (let i = 0; i < result.return.length; i++) {
                            html += "<option value='" + result.return[i].id + "'>" + result.return[i]
                                .name + "</option>";
                        }
                        $("#categoryId").html(html);
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

        function initSave() {
            const categoryID = $('#categoryId').val();
            const productKeywords = $('#productKeywords').val();
            runSave(categoryID, productKeywords, varProducts, rememberToken);
            closePopup();
            popupLoader(1, '"데이터베이스에 상품 정보를 입력하고 있어요."');
        }

        function runSave(categoryID, productKeywords, products, rememberToken) {
            $.ajax({
                url: '/api/minewing/save-products',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    categoryID,
                    productKeywords,
                    products,
                    rememberToken
                },
                success: successHandle,
                error: errorHandle
            });
        }

        function successHandle(response) {
            closePopup();
            if (response.status) {
                audioSuccess.play();
                swalSuccess(response.return);
            } else {
                $('#productSaveForm').modal('show');
                swalError(response.return);
            }
        }
    </script>
@endsection
