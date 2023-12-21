@extends('layouts.main')
@section('title')
    상품 데이터 마인윙
@endsection
@section('subtitle')
    <p>
        더욱 강력해진 엔진 마인윙과 함께 상품 데이터셋을 수집합니다.
    </p>
@endsection
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
                                <div class="col-3 mb-3">
                                    <div class="custom-control custom-checkbox">
                                        <div class="custom-control custom-radio">
                                            <input type="radio" id="seller{{ $seller->vendor_id }}" name="sellers"
                                                value="{{ $seller->vendor_id }}" class="custom-control-input"
                                                {{ $seller->vendor_id == 14 ? 'checked' : '' }}>
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
                                <button class="btn btn-primary" id="searchBtn" onclick="initExtract();">상품 리스트 추출</button>
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
                        <button class="btn btn-warning" id="bulkCollectBtn" onclick="initProcess();">가공 및
                            수집하기</button>
                    </div>
                    <div id="collectResult" class="mt-5">
                        <table class="table table-striped">
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
                    <button type="button" class="btn btn-primary" onclick="requestSave();">저장하기</button>
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
                    <div class="row">
                        <div class="col">
                            <div class="w-100 d-flex jusitfy-content-center">
                                <img id="duProductImg" src="" alt="중복 상품명 이미지">
                            </div>
                            <h6>원상품명: <span id="productNameOri"></span></h6>
                            <input type="text" class="form-control" id="duProductName">
                        </div>
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
        var varProducts;
        var varProduct;

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
                    let html = "";
                    for (let i = 0; i < result.return.length; i++) {
                        html += "<option value='" + result.return[i].id + "'>" + result.return[i]
                            .name + "</option>";
                    }
                    $("#categoryId").html(html);
                    console.log(html);
                },
                error: function(response) {
                    $("#categorySearchBtn").html("검색");
                    $("#categorySearchBtn").prop("disabled", false);
                    console.log(response);
                }
            });
        }

        function initExtract() {
            const listURL = $('#listURL').val();
            const sellerID = $('input[name="sellers"]:checked').val();
            requestExtract(sellerID, listURL);
        }

        function handleEnter(event, btnID) {
            if (event.key === 'Enter') {
                event.preventDefault(); // 엔터키의 기본 동작(새 줄 삽입)을 방지
                document.getElementById(btnID).click(); // 버튼 클릭 이벤트 실행
            }
        }

        function requestExtract(sellerID, listURL) {
            popupLoader(0, '"구르미가 상품셋을 가지러 떠납니다."');
            $.ajax({
                url: '/api/product/mining',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    remember_token: '{{ Auth::user()->remember_token }}',
                    sellerID: sellerID,
                    listURL: listURL
                },
                success: function(response) {
                    console.log(response);
                    closePopup();
                    if (response.status) {
                        updateDataTable(response.return);
                        $('#numResult').html(response.return.length);
                        Swal.fire({
                            icon: 'success',
                            html: '<img class="w-100" src="{{ asset('media/Asset_Notif_Success.svg') }}"><h4 class="swal2-title mt-5">구르미가 무사히 돌아왔어요!</h4>'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            html: '<img class="w-100" src="{{ asset('media/Asset_Notif_Error.svg') }}"><h4 class="swal2-title mt-5">구르미를 올바른 주소로 보내주세요.</h4>'
                        });
                    }
                },
                error: function(response) {
                    console.log(response);
                    closePopup();
                    Swal.fire({
                        icon: 'error',
                        html: '<img class="w-100" src="{{ asset('media/Asset_Notif_Error.svg') }}"><h4 class="swal2-title mt-5">구르미를 올바른 주소로 보내주세요.</h4>'
                    });
                }
            });
        }
        // 전체 선택/해제 체크박스 이벤트 리스너
        $(document).on('click', '#selectAll', function() {
            const isChecked = $(this).is(':checked');
            $('input[name="selectedProducts"]').prop('checked', isChecked);
        });

        function initProcess() {
            const products = [];
            $('input[name="selectedProducts"]:checked').each(function() {
                products.push($(this).val());
            });
            requestUnique(products);
        }

        function requestUnique(products) {
            popupLoader(1, '중복 상품들을 검열 중이에요.');
            const productHrefs = [];
            for (product of products) {
                productHrefs.push($('#productHref' + product).attr('href'));
            }
            $.ajax({
                url: "/api/product/unique",
                type: 'POST',
                dataType: 'JSON',
                data: {
                    remember_token: '{{ Auth::user()->remember_token }}',
                    productHrefs: productHrefs
                },
                success: function(response) {
                    closePopup();
                    if (response.status) {
                        const uniqueProductHrefs = response.return.uniqueProductHrefs;
                        const returnMsg = response.return.message;
                        popupLoader(0, returnMsg);
                        requestProcess(uniqueProductHrefs);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            html: '<img class="w-100" src="{{ asset('media/Asset_Notif_Error.svg') }}"><h4 class="swal2-title mt-5">Oops! 에러가 발생했습니다. 다시 시도해주십시오.</h4>'
                        });
                    }
                },
                error: function(response) {
                    console.log(response);
                    closePopup();
                    Swal.fire({
                        icon: 'error',
                        html: '<img class="w-100" src="{{ asset('media/Asset_Notif_Error.svg') }}"><h4 class="swal2-title mt-5">Oops! 에러가 발생했습니다. 다시 시도해주십시오.</h4>'
                    });
                }
            });
        }

        function requestProcess(productHrefs) {
            const sellerID = $('input[name="sellers"]:checked').val();
            $.ajax({
                url: "/api/product/process",
                type: 'POST',
                dataType: 'JSON',
                data: {
                    remember_token: '{{ Auth::user()->remember_token }}',
                    productHrefs: productHrefs,
                    sellerID: sellerID
                },
                success: function(response) {
                    closePopup();
                    console.log(response.return);
                    if (response.status) {
                        const productDetails = response.return;
                        popupLoader(1, response.message);
                        productManufacture(productDetails, sellerID);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            html: '<img class="w-100" src="{{ asset('media/Asset_Notif_Error.svg') }}"><h4 class="swal2-title mt-5">' +
                                response.message + '</h4>'
                        });
                    }
                },
                error: function(response) {
                    closePopup();
                    console.log(response);
                }
            });
        }

        function productManufacture(productDetails, sellerID) {
            $.ajax({
                url: "/api/product/manufacture",
                type: 'POST',
                dataType: 'JSON',
                data: {
                    remember_token: '{{ Auth::user()->remember_token }}',
                    productDetails: productDetails,
                    sellerID: sellerID
                },
                success: function(response) {
                    closePopup();
                    console.log(response);
                    varProducts = response.return;
                    if (!response.status) {
                        varProduct = response.duplicates;
                        handleDuplicatedNames();
                    } else {
                        initSave(varProducts);
                    }
                },
                error: function(response) {
                    closePopup();
                    console.log(response);
                }
            });
        }

        function handleDuplicatedNames() {
            //handleDupNamesModal
            $('#duProductImg').attr('src', varProducts[varProduct.index].productImage);
            $('#duProductName').val(varProduct.productName);
            $("#productNameOri").html(varProduct.productNameOri);
            $('#handleDupNamesModal').modal('show');
        }

        function initHandleDupName() {
            const newProductName = $('#duProductName').val();
            validateProductNames(newProductName);
        }

        function validateProductNames(newProductName) {
            closePopup();
            popupLoader(1, '"새로운 상품명을 필터링하고, 중복 검사하는 중이에요."');
            $.ajax({
                url: '/api/product/validate-product-names',
                type: 'post',
                dataType: 'json',
                data: {
                    products: varProducts,
                    newProductName: newProductName,
                    index: varProduct.index
                },
                success: function(response) {
                    closePopup();
                    console.log(response);
                    varProducts = response.return;
                    if (!response.status) {
                        varProduct = response.duplicates;
                        handleDuplicatedNames();
                    } else {
                        initSave(varProducts);
                    }
                },
                error: function(response) {
                    closePopup();
                    console.log(response);
                }
            });
        }

        function updateDataTable(products) {
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
                html += "<td><a href='" + href + "' target='_blank'>" + name + "</a></td>";
                html += "<td><a href='" + href + "' target='_blank'>" + numberFormat(price, 0) + "원</a></td>";
                html += "</tr>";
            }
            $('#minewingResult').html(html);
        }

        function initSave() {
            $('#productSaveForm').modal('show');
        }

        function requestSave() {
            const categoryID = $('#categoryId').val();
            const productKeywords = $('#productKeywords').val();
            const sellerID = $('input[name="sellers"]:checked').val();
            console.log(sellerID);
            closePopup();
            popupLoader(0, '"가공된 상품셋을 저장소로 나르는 중이에요."');
            $.ajax({
                url: '/api/product/insert',
                type: 'post',
                dataType: 'json',
                data: {
                    products: varProducts,
                    remember_token: '{{ Auth::user()->remember_token }}',
                    categoryID: categoryID,
                    productKeywords: productKeywords
                },
                success: function(response) {
                    closePopup();
                    if (response.status === true) {
                        Swal.fire({
                            icon: 'success',
                            html: '<img class="w-100" src="{{ asset('media/Asset_Notif_Success.svg') }}"><h4 class="swal2-title mt-5">' +
                                response.return+'</h4>'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: '진행 실패',
                            text: response.return
                        });
                        initSave();
                    }
                },
                error: function(response) {
                    closePopup();
                    console.log(response);
                    Swal.fire({
                        icon: 'error',
                        html: '<img class="w-100" src="{{ asset('media/Asset_Notif_Error.svg') }}"><h4 class="swal2-title mt-5">Oops! 에러가 발생했습니다. 다시 시도해주십시오.</h4>'
                    });
                }
            });
        }
    </script>
@endsection
