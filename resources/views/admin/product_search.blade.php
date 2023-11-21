@extends('layouts.main')
@section('title')
    상품 가공
@endsection
@section('subtitle')
    <p>
        각종 업체들로부터 상품 정보들을 검색하고 가공 및 수집합니다.
    </p>
@endsection
@section('content')
    <div class="row g-gs">
        <div class="col">
            <div class="card card-bordered preview">
                <div class="card-inner">
                    <h5 class="card-title">상품 정보 대량 수집</h5>
                    <h6 class="card-subtitle mb-2">검색 엔진에 활용할 업체를 고르고, 검색 키워드를 기입해주세요.</h6>
                    <div class="form-group">
                        <label class="form-label">업체</label>
                        <div class="row">
                            @foreach ($vendors as $vendor)
                                <div class="col-3 mb-3">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input"
                                            id="vendor{{ $vendor->vendor_id }}" name="vendors[]"
                                            value="{{ $vendor->vendor_id }}" checked>
                                        <label class="custom-control-label"
                                            for="vendor{{ $vendor->vendor_id }}">{{ $vendor->name }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">상품 키워드</label>
                        <div class="row g-0">
                            <div class="col">
                                <input type="text" class="form-control" id="productUrl"
                                    placeholder="상품 키워드 기입해주세요."onkeydown="handleEnter(event)" />
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-primary" id="searchBtn" onclick="collectInit();">상품 검색</button>
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
                        <button class="btn btn-warning" id="bulkCollectBtn" onclick="productBulkCollect();">상품 다중
                            수집</button>
                    </div>
                    <div id="collectResult">
                        <table id="productTable" class="datatable-init-export nowrap table" data-export-title="Export"
                            data-order='[[3, "asc"]]'>
                            <thead>
                                <tr>
                                    <th class="nk-tb-col nk-tb-col-check">
                                        <div class="custom-control custom-control-sm custom-checkbox notext">
                                            <input type="checkbox" class="custom-control-input" id="selectAll">
                                            <label class="custom-control-label" for="selectAll"></label>
                                        </div>
                                    </th>
                                    <th>이미지</th>
                                    <th>상품명</th>
                                    <th>가격</th>
                                    <th>플랫폼</th>
                                    <th>ACTION</th>
                                </tr>
                            </thead>
                            <tbody id="productList">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modalForm">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">상품 인스턴트 등록</h5>
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
                                @foreach ($productInformation as $i)
                                    <option value="{{ $i->id }}">{{ $i->content }}</option>
                                @endforeach
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
    <div class="modal fade" id="productBulkCollectWizard">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">상품 대량 수집 마법사</h5>
                    <a href="#" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <em class="icon ni ni-cross"></em>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="form-validate is-alter">
                        <div class="form-group">
                            <label class="form-label" for="pBCDCategoryId">상품 카테고리</label>
                            <div class="form-control-wrap d-flex text-nowrap mb-3">
                                <input type="text" class="form-control" placeholder="카테고리 검색 키워드를 입력해주세요."
                                    id="pBCDCategoryKeyword">
                                <button class="btn btn-primary" onclick="pBCDCategroySearch();"
                                    id="pBCDCategorySearchBtn">검색</button>
                            </div>
                            <div class="form-control-wrap">
                                <select name="pBCDCategoryId" id="pBCDCategoryId" class="form-select"></select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="pBCDKeywords">키워드</label>
                            <div class="form-control-wrap">
                                <input type="text" class="form-control" id="pBCDKeywords"
                                    placeholder="상품 키워드를 , 단위로 구분하여 최소 5개를 기입해주세요.">
                            </div>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-lg btn-primary" onclick="saveBulkProducts();">가공
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
        var productHref;
        $('#summernote').summernote({
            height: 300,
            callbacks: {
                onImageUpload: function(files) {
                    var csrfToken = $('meta[name="csrf-token"]').attr('content');
                    var $editor = $(this);
                    var data = new FormData();
                    data.append('file', files[0]);

                    $.ajax({
                        url: '/admin/upload-image',
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        data: data,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response.status === 1) {
                                // 이미지 업로드 성공 시
                                $editor.summernote('insertImage', response.return);
                            } else {
                                // 이미지 업로드 실패 시
                                console.error('Image upload failed');
                            }
                        },
                        error: function(response) {
                            console.error('Image upload error:', response);
                        }
                    });
                }
            }
        });
        const loadingGifSrc = '{{ asset('assets/images/loading.gif') }}';
        // 이미지를 미리 로딩
        const image = new Image();
        image.src = loadingGifSrc;

        function collectInit() {
            const loadingGifSrc = '{{ asset('assets/images/loading.gif') }}'
            let html = '<img src="' + image.src + '" class="w-75" />'
            html += '<h2 class="swal2-title mt-5">상품 데이터를 수집 중입니다</h2>'
            Swal.fire({
                html: html,
                allowOutsideClick: false,
                showConfirmButton: false
            });
            const keyword = $("#productUrl").val();
            let vendorIds = []; // 선택된 값들을 저장할 배열
            // 클래스가 'vendor-checkbox'인 체크박스들을 선택
            $('input[name="vendors[]"]:checked').each(function() {
                vendorIds.push($(this).val()); // 선택된 체크박스의 값(value)를 배열에 추가
            });
            $.ajax({
                url: '/api/product/search',
                type: 'post',
                dataType: 'json',
                data: {
                    keyword: keyword,
                    vendorIds: vendorIds
                },
                success: function(response) {
                    if (response.status == 1) {
                        Swal.fire({
                            icon: "success",
                            title: "진행 성공",
                            text: "데이터를 성공적으로 불러왔습니다."
                        });
                        console.log(response.return);
                        updateDataTable(response.return);
                        $('#numResult').html(response.return.length);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: '진행 실패',
                            text: response.return
                        });
                    }
                },
                error: function(response) {
                    $("#loadingImg").addClass("d-none");
                    Swal.fire({
                        icon: "error",
                        title: "진행 실패",
                        text: response.message
                    });
                    console.log(response);
                }
            });
        }
        $('#selectAll').on('change', function() {
            var dataTable = $('#productTable').DataTable();
            var rows = dataTable.rows().nodes();
            $('input[type="checkbox"]', rows).prop('checked', this.checked);
        });
        var collectedProducts;

        function updateDataTable(products) {
            var dataTable = $('#productTable').DataTable();

            dataTable.clear().draw();
            collectedProducts = [];
            for (let i = 0; i < products.length; i++) {
                const name = products[i].name;
                const price = products[i].price;
                const platform = products[i].platform;
                const image = products[i].image;
                const href = products[i].href;
                const tmpProduct = {
                    index: i,
                    name: name,
                    price: price,
                    platform: platform,
                    image: image,
                    href: href
                };
                collectedProducts.push(tmpProduct);

                const checkbox =
                    '<div class="custom-control custom-control-sm custom-checkbox notext"><input type="checkbox" class="custom-control-input" id="uid' +
                    i + '"><label class="custom-control-label" for="uid' + i + '"></label></div>';
                const imageHtml = '<a href="' + href + '" target="_blank"><img src="' + image +
                    '" alt="Product" style="width:120px; height:120px;"></a>';
                const nameHtml = '<a href="' + href + '" target="_blank" title="' + name + '">' + name +
                    '</a>';
                const actionHtml =
                    `<button class="btn btn-primary" onclick="registerProduct('${name}', '${price}', '${image}', '${platform}', '${href}')">상품 등록</button>`;
                dataTable.row.add([
                    checkbox,
                    imageHtml,
                    nameHtml,
                    price,
                    platform,
                    actionHtml
                ]).draw(false);
            }

            // 각 컬럼의 너비 조정
            dataTable.columns.adjust().draw();
        }

        function truncateString(inputString, maxLength) {
            if (inputString.length <= maxLength) {
                return inputString; // 문자열의 길이가 최대 길이보다 작으면 그대로 반환
            } else {
                return inputString.slice(0, maxLength); // 문자열의 길이가 최대 길이보다 크면 처음 maxLength까지만 반환
            }
        }

        function validateInput(input) {
            // 정규 표현식을 사용하여 유효한 문자만 허용
            var validatedValue = input.value.replace(/[^가-힣a-zA-Z0-9\s]/g, '');

            // 유효한 문자로만 값을 갱신
            input.value = validatedValue;
        }

        function priceFormat(input) {
            const price = $(input).val();
            const charArr = [];
            for (let i = 0; i < price.length; i++) {
                const char = price[i].charCodeAt(0);
                if (char >= 48 && char <= 57) {
                    charArr.push(char);
                }
            }
            const newPrice = parseInt(String.fromCharCode(...charArr));
            $(input).val(newPrice);
        }

        function registerProduct(name, price, image, platform, href) {
            elementEraser();
            loadProductDetail(platform, href);
            $('#productName').val(nameFormatter(name));
            $('#invoiceName').val(nameFormatter(name));
            $("#productPrice").val(Math.round(price * {{ $marginRate }}));
            $("#productImage").attr("src", image);
            productHref = href;
        }

        function nameFormatter(name) {
            const MAX_LENGTH = 20;

            const isCharacterValid = (char) => {
                const asciiCode = char.charCodeAt(0);
                return (asciiCode >= 44032 && asciiCode <= 55203) ||
                    (asciiCode >= 48 && asciiCode <= 57) ||
                    (asciiCode >= 65 && asciiCode <= 90) ||
                    (asciiCode >= 97 && asciiCode <= 122) ||
                    (asciiCode === 32);
            };

            const asciiArr = name
                .split('')
                .filter(isCharacterValid)
                .map(char => char.charCodeAt(0));

            const newName = String.fromCharCode(...asciiArr).substring(0, MAX_LENGTH);

            return newName;
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
                                .wholeCategoryName + "</option>";
                        }
                        $("#categoryId").html(html);
                        console.log(html);
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

        function pBCDCategroySearch() {
            const keyword = $("#pBCDCategoryKeyword").val();
            $(".btn").prop("disabled", true);
            $.ajax({
                url: '/api/product/category',
                type: 'post',
                dataType: 'json',
                data: {
                    keyword: keyword
                },
                success: function(result) {
                    console.log(result);
                    $(".btn").prop("disabled", false);
                    if (result.status == 1) {
                        let html = "";
                        for (let i = 0; i < result.return.length; i++) {
                            html += "<option value='" + result.return[i].id + "'>" + result.return[i]
                                .wholeCategoryName + "</option>";
                        }
                        $("#pBCDCategoryId").html(html);
                        console.log(html);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: '진행 실패',
                            text: result.return
                        });
                    }
                },
                error: function(response) {
                    $(".btn").prop("disabled", false);
                    console.log(response);
                }
            });
        }

        function productCollect() {
            const formData = new FormData(); // FormData 객체 생성
            formData.append('remember_token', '{{ Auth::user()->remember_token }}');
            const productDetail = $('.summernote-basic').summernote('code');
            formData.append('productDetail', productDetail);
            formData.append('productName', $("#productName").val());
            formData.append('categoryId', $('#categoryId').val());
            formData.append('keywords', $('#productKeywords').val());
            formData.append('taxability', 0);
            const productImage = $('#productImage').attr('src');
            formData.append('productImage', productImage);
            formData.append('saleToMinor', 0);
            formData.append('origin', 2);
            formData.append('isMedicalDevice', 0);
            formData.append('isMedicalFoods', 0);
            formData.append('shippingPolicy', 0);
            formData.append('shippingCost', $('#shippingCost').val());
            formData.append('productPrice', $('#productPrice').val());
            formData.append('productVendor', $('#productVendor').val());
            formData.append('productInformationId', $('#product_information').val());
            console.log($('#product_information option:selected').val());
            formData.append('productHref', productHref);
            $('.btn').prop('disabled', true);
            $.ajax({
                url: '/api/product/collect',
                type: 'post',
                dataType: 'json',
                data: formData,
                processData: false, // FormData 처리 설정
                contentType: false, // Content-Type 설정
                success: function(response) {
                    $('.btn').prop('disabled', false);
                    if (response.status == 1) {
                        $('.modal').modal('hide');
                        Swal.close();
                        Swal.fire({
                            icon: 'success',
                            title: '진행 성공',
                            text: response.return
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: '진행 실패',
                            text: response.return
                        });
                    }
                },
                error: function(response) {
                    $('.btn').prop('disabled', false);
                    console.log(response);
                }
            });
        }

        function loadProductDetail(platform, href) {
            $('.btn').prop('disabled', true);
            const loadingGifSrc = '{{ asset('assets/images/loading.gif') }}'
            let html = '<img src="' + image.src + '" class="w-75" />'
            html += '<h2 class="swal2-title mt-5">상품 정보를 추출 중입니다<br>잠시만 기다려주세요</h2>'
            Swal.fire({
                html: html,
                allowOutsideClick: false,
                showConfirmButton: false
            });
            $.ajax({
                url: '/api/product/load-product-detail',
                type: 'POST',
                dataType: "JSON",
                data: {
                    platform: platform,
                    href: href
                },
                success: function(response) {
                    $('.btn').prop('disabled', false);
                    Swal.close();
                    if (response.status == 1) {
                        console.log(response);
                        $('#summernote').summernote('code', response.return.productDetail);
                        $("#modalForm").modal("show");
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "진행 실패",
                            text: response.return
                        });
                    }
                },
                error: function(response) {
                    $('.btn').prop('disabled', false);
                    console.log(response);
                }
            });
        }

        function extractNumericPart(id) {
            // 정규 표현식을 사용하여 "uid" 다음의 숫자 부분을 추출
            const numericPart = id.replace(/^uid(\d+)$/, '$1');
            return numericPart;
        }
        var selectedProducts

        function productBulkCollect() {
            const selectedCheckboxes = [];
            $('#productTable tbody input[type="checkbox"]:checked').each(function() {
                const uid = extractNumericPart($(this).attr('id'));
                selectedCheckboxes.push(uid);
            });
            selectedProducts = [];
            // collectedProducts 와 selectedCheckboxes 를 활용해서 필터링하는 법.
            for (let i = 0; i < selectedCheckboxes.length; i++) {
                const tmpIndex = selectedCheckboxes[i];
                const tmpProduct = collectedProducts.find(function(product) {
                    return product.index == tmpIndex;
                });

                if (tmpProduct) {
                    selectedProducts.push(tmpProduct);
                }
            }
            console.log(collectedProducts);
            console.log(selectedProducts);
            $('#productBulkCollectWizard').modal('show');
        }

        function loadBulkDetails(products) {
            $('.btn').prop("disabled", true);
            $('.modal').modal("hide");
            Swal.close();
            let html = '<img src="' + image.src + '" class="w-75" />'
            html += '<h2 class="swal2-title mt-5">선택된 상품들의 정보들을 수집 및 가공 중입니다.</h2>'
            Swal.fire({
                html: html,
                allowOutsideClick: false,
                showConfirmButton: false
            });
            $.ajax({
                url: "/api/product/load-bulk-details",
                type: "POST",
                dataType: "JSON",
                data: {
                    products: products
                },
                success: function(response) {
                    $('.modal').modal("hide");
                    Swal.close();
                    $('.btn').prop('disabled', false);
                    console.log(response);
                    insertProductDB(response.return);
                },
                error: function(response) {
                    Swal.close();
                    $('.modal').modal('hide');
                    console.log(response);
                }
            });
        }

        function insertProductDB(products) {
            $('.modal').modal("hide");
            Swal.close();
            let html = '<img src="' + image.src + '" class="w-75" />'
            html += '<h2 class="swal2-title mt-5">가공된 상품들을 DB에 저장 중입니다.</h2>'
            Swal.fire({
                html: html,
                allowOutsideClick: false,
                showConfirmButton: false
            });
            const productKeywords = $('#pBCDKeywords').val();
            const categoryId = $('#pBCDCategoryId').val();
            $.ajax({
                url: "/api/product/insert-bulk-products",
                type: "POST",
                dataType: "JSON",
                data: {
                    remember_token: '{{ Auth::user()->remember_token }}',
                    products: products,
                    keywords: productKeywords,
                    categoryId: categoryId
                },
                success: function(response) {
                    Swal.close();
                    $('.modal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: '진행 성공',
                        text: response.return
                    });
                    console.log(response);
                },
                error: function(response) {
                    Swal.close();
                    $('.modal').modal('hide');
                    console.log(response);
                }
            });
        }

        function saveBulkProducts() {
            loadBulkDetails(selectedProducts);
        }

        function scrapeProductDetail(platform, href) {
            $.ajax({
                url: '/api/product/load-product-detail',
                type: 'POST',
                dataType: "JSON",
                data: {
                    platform: platform,
                    href: href
                },
                success: function(response) {
                    return response;
                },
                error: function(response) {
                    return {
                        status: -1,
                        return: '상품 정보 수집에 실패했습니다.'
                    };
                }
            });
        }


        function elementEraser() {
            // 각 입력 필드의 값을 초기화
            $('#categoryKeyword').val('');
            $('#categoryId').val('');
            $('#productName').val('');
            $('#invoiceName').val('');
            $('#productKeywords').val('');
            $('#productPrice').val('');
            $('#shippingCost').val('3000');
            $('#productVendor').val('LADAM');
            $('#productImage').attr('src', ''); // 이미지 초기화
            $('#summernote').summernote('code', ''); // Summernote 초기화
        }

        function handleEnter(event) {
            if (event.key === 'Enter') {
                event.preventDefault(); // 엔터키의 기본 동작(새 줄 삽입)을 방지
                document.getElementById('searchBtn').click(); // 버튼 클릭 이벤트 실행
            }
        }
    </script>
@endsection
