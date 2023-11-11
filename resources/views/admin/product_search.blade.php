@extends('layouts.main')
@section('title')
    상품 대량 검색
@endsection
@section('subtitle')
    <p>상품 대량 검색 엔진을 가동합니다.</p>
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
                                <input type="text" class="form-control" id="productUrl" placeholder="상품 키워드 기입해주세요." />
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-primary" onclick="collectInit();">수집
                                    시작</button>
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
                    <h5 class="card-title">수집 결과</h5>
                    <h6 class="card-subtitle mb-2">해당 상품 정보에 대한 수집 결과입니다:</h6>
                    <p class="card-text">총 <span class="fw-bold" id="numResult"></span>건이 검색되었습니다</p>
                    <div id="collectResult">
                        <table id="productTable" class="datatable-init-export nowrap table" data-export-title="Export"
                            data-order='[[2, "asc"]]'>
                            <thead>
                                <tr>
                                    <th>이미지</th>
                                    <th>상품명</th>
                                    <th>가격</th>
                                    <th>플랫폼</th>
                                    <th>ACTION</th>
                                </tr>
                            </thead>
                            <tbody id="productList">
                                <tr class="odd">
                                    <td class="dtr-control" tabindex="0"><a
                                            href="http://domeggook.com//10732325?from=lstGen" target="_blank"><img
                                                src="https://cdn1.domeggook.com//upload/item/2020/09/04/15991991428E9D6BD09F80F34309382F/15991991428E9D6BD09F80F34309382F_img_330?hash=d4086f0619c10063fe1803851a89df3d"
                                                alt="Product" style="width:120px; height:120px;"></a></td>
                                    <td><a href="http://domeggook.com//10732325?from=lstGen" target="_blank"
                                            title="초경량고리형마스크스트랩끈스토퍼내장길이조절가능간편사용오염방지개별포장코로나필수품">초경량고리형마스크스트랩끈스토퍼내장길이조절가능간편사용오염...</a>
                                    </td>
                                    <td class="sorting_1">99</td>
                                    <td>도매매</td>
                                    <td class="dtr-hidden" style="display: none;"><button class="btn btn-primary"
                                            onclick="registerProduct('초경량고리형마스크스트랩끈스토퍼내장길이조절가능간편사용오염방지개별포장코로나필수품', '99', 'https://cdn1.domeggook.com//upload/item/2020/09/04/15991991428E9D6BD09F80F34309382F/15991991428E9D6BD09F80F34309382F_img_330?hash=d4086f0619c10063fe1803851a89df3d')">상품
                                            등록</button></td>
                                </tr>
                                <!-- 데이터는 JavaScript 코드로 동적으로 추가됩니다 -->
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
                    <form action="#" class="form-validate is-alter">
                        <div class="form-group">
                            <label class="form-label" for="category">상품 카테고리</label>
                            <div class="form-control-wrap d-flex text-nowrap mb-3">
                                <input type="text" class="form-control" placeholder="카테고리 검색 키워드를 입력해주세요."
                                    id="categoryKeyword">
                                <button class="btn btn-primary" onclick="categorySearch();"
                                    id="categorySearchBtn">검색</button>
                            </div>
                            <div class="form-control-wrap">
                                <select name="category" id="category" class="form-control js-select2"></select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="productName">상품명</label>
                            <div class="form-control-wrap">
                                <input type="text" class="form-control" id="productName" placeholder="상품명을 기입해주세요.">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="invoiceName">택배송장명</label>
                            <div class="form-control-wrap">
                                <input type="text" class="form-control" id="invoiceName"
                                    placeholder="택배송장명을 기입해주세요.">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="productKeywords">키워드</label>
                            <div class="form-control-wrap">
                                <input type="text" class="form-control" id="productKeywords"
                                    placeholder="상품 키워드를 , 단위로 구분하여 최소 5개를 기입해주세요.">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="productModel">모델명</label>
                            <div class="form-control-wrap">
                                <input type="text" class="form-control" id="productModel" placeholder="모델명을 기입해주세요.">
                            </div>
                        </div>
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
                                    <input type="text" class="form-control" id="shippingCost"
                                        placeholder="상품 가격을 기입해주세요.">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="productVendor">제조사/브랜드</label>
                            <div class="form-control-wrap">
                                <input type="text" class="form-control" id="productVendor"
                                    placeholder="상품의 제조사 혹은 브랜드를 기입해주세요.">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="productVendor">상품 대표 이미지</label>
                            <div class="w-100">
                                <img src="" alt="상품 대표 이미지" id="productImage">
                            </div>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-lg btn-primary">등록하기</button>
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-light">
                    <span class="sub-text">Powered by ChilledWatermelon</span>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
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

        function updateDataTable(products) {
            var dataTable = $('#productTable').DataTable();

            dataTable.clear().draw();

            for (let i = 0; i < products.length; i++) {
                const name = products[i].name;
                const price = products[i].price;
                const platform = products[i].platform;
                const image = products[i].image;
                const href = products[i].href;

                const imageHtml = '<a href="' + href + '" target="_blank"><img src="' + image +
                    '" alt="Product" style="width:120px; height:120px;"></a>';
                const nameHtml = '<a href="' + href + '" target="_blank" title="' + name + '">' + truncateText(name, 30) +
                    '</a>';
                const actionHtml =
                    `<button class="btn btn-primary" onclick="registerProduct('${name}', '${price}', '${image}')">상품 등록</button>`;
                dataTable.row.add([
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

        function truncateText(text, maxLength) {
            if (text.length > maxLength) {
                return text.slice(0, maxLength) + '...';
            }
            return text;
        }

        function registerProduct(name, price, image) {
            nameFormatter(name);
            $("#productPrice").val(price);
            $("#productImage").attr("src", image);
            $("#modalForm").modal("show");
        }

        function nameFormatter(name) {
            const MAX_LENGTH = 20;
            let newName = '';

            for (let i = 0; i < name.length && newName.length < MAX_LENGTH; i++) {
                const char = name[i];
                const asciiCode = char.charCodeAt(0);

                const isKorean = (asciiCode >= 44032 && asciiCode <= 55203);
                const isDigit = (asciiCode >= 48 && asciiCode <= 57);
                const isUpperCase = (asciiCode >= 65 && asciiCode <= 90);
                const isLowerCase = (asciiCode >= 97 && asciiCode <= 122);

                if (isKorean || isDigit || isUpperCase || isLowerCase) {
                    newName += char;
                }
            }

            $("#productName").val(name);
            $("#invoiceName").val(name);
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
                            html += "<option value='" + result.return[i].code + "'>" + result.return[i]
                                .wholeCategoryName + "</option>";
                        }
                        $("#category").html(html);
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
    </script>
@endsection
