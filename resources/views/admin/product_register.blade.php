@extends('layouts.main')
@section('title')
    상품 등록 및 연동
@endsection
@section('subtitle')
    <p>타겟 쇼핑몰들에 해당 상품을 등록 및 연동합니다.</p>
@endsection
@section('content')
    <div class="row g-gs">
        <div class="col">
            <div class="card card-bordered preview">
                <div class="card-inner">
                    <h5 class="card-title">상품 등록</h5>
                    <h6 class="card-subtitle mb-2">상품명, 가격, 이미지, 그리고 설명을 기입하여 상품을 등록합니다.
                    </h6>
                    <div class="form-group">
                        <label class="form-label">등록할 업체</label>
                        <div class='row'>
                            @foreach ($vendors as $vendor)
                                <div class='col-3'>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" checked name="vendors"
                                            id="vendor{{ $vendor->vendor_id }}" value="{{ $vendor->vendor_id }}">
                                        <label class="custom-control-label" for="vendor{{ $vendor->vendor_id }}">
                                            {{ $vendor->name }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">카테고리</label>
                        <div class="row g-0">
                            <div class="col">
                                <input type="text" class="form-control" id="categoryInput"
                                    placeholder="카테고리 키워드를 기입해주세요." />
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-primary" id="searchBtn" onclick="categorySearch();">검색</button>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col">
                                <select id="categoryResult" class="form-select">
                                    <option value=""></option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4">
                            <div class="form-group">
                                <label class="form-label">상품명</label>
                                <input type="text" class="form-control" id="productName" placeholder="상품명을 기입해주세요."
                                    name="productName" onchange="productNameFormat(this);" value="{{ $name ?? '' }}"
                                    required>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-group">
                                <label class="form-label">택배송장명</label>
                                <input type="text" class="form-control" id="invoiceName" placeholder="택배송장명을 기입해주세요."
                                    name="invoiceName" onchange="productNameFormat(this);" value="{{ $name ?? '' }}"
                                    required>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-group">
                                <label class="form-label">모델명</label>
                                <input type="text" class="form-control" id="model" placeholder="모델명을 기입해주세요."
                                    name="model" onchange="productNameFormat(this);" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">키워드</label>
                        <input type="text" class="form-control" id="keywords"
                            placeholder="키워드를 ,로 구분하여 5개 이상 20개 이하로 기입해주세요. E.g. 싱크대,싱크,깨끗,클린,락스" name="keywords" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4">
                            <div class="form-group">
                                <label class="form-label">과세여부</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="taxability" id="taxability1"
                                        checked>
                                    <label class="form-check-label" for="taxability1">
                                        과세
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="taxability" id="taxability2">
                                    <label class="form-check-label" for="taxability2">
                                        면세
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-group">
                                <label class="form-label">미성년자 판매</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="saleToMinor" id="saleToMinor1"
                                        checked>
                                    <label class="form-check-label" for="saleToMinor1">
                                        가능
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="saleToMinor" id="saleToMinor2">
                                    <label class="form-check-label" for="saleToMinor2">
                                        불가
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-group">
                                <label class="form-label">원산지</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="origin" id="origin1"
                                        disabled>
                                    <label class="form-check-label" for="origin1">
                                        국내
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="origin" id="origin2"
                                        disabled>
                                    <label class="form-check-label" for="origin2">
                                        해외
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="origin" id="origin3"
                                        checked>
                                    <label class="form-check-label" for="origin3">
                                        기타
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4">
                            <div class="form-group">
                                <label class="form-label">의료기기 여부</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="madicalEquipment"
                                        id="madicalEquipment1" checked>
                                    <label class="form-check-label" for="madicalEquipment1">
                                        의료기기 아님
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="madicalEquipment"
                                        id="madicalEquipment2">
                                    <label class="form-check-label" for="madicalEquipment2">
                                        의료기기
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-group">
                                <label class="form-label">건강기능식품 여부</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="healthFunctional"
                                        id="healthFunctional1" checked>
                                    <label class="form-check-label" for="healthFunctional1">
                                        건강기능식품 아님
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="healthFunctional"
                                        id="healthFunctional2">
                                    <label class="form-check-label" for="healthFunctional2">
                                        건강기능식품
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="form-group">
                                <label class="form-label">배송정책</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="shipping" id="shipping1"
                                        checked>
                                    <label class="form-check-label" for="shipping1">
                                        선불
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="shipping" id="shipping2">
                                    <label class="form-check-label" for="shipping2">
                                        착불
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="shipping" id="shipping3">
                                    <label class="form-check-label" for="shipping3">
                                        무료
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <div class="form-group">
                                <label class="form-label">가격</label>
                                <input type="number" class="form-control" id="productPrice" placeholder="가격을 기입해주세요."
                                    name="productPrice" value="{{ $price ?? '' }}" required>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label class="form-label">제조사/브랜드</label>
                                <input type="text" class="form-control" id="vendor"
                                    placeholder="제조사/브랜드명을 기입해주세요." name="vendor" required>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label class="form-label">배송비</label>
                                <input type="number" class="form-control" id="shipCost" placeholder="배송비를 기입해주세요."
                                    name="shipCost" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">이미지</label>
                        <input type="file" class="form-control" id="productImage" name="productImage"
                            accept="image/*" />
                        @if (isset($image))
                            <a class="btn btn-outline-primary" id="downloadLink" href="{{ $image }}"
                                target="_blank" download>샘플
                                이미지
                                다운로드</a>
                            <img src="{{ $image }}" id="sampleImage" name="sampleImage" alt="Sample Image">
                        @endif
                    </div>
                    <div class="form-group">
                        <label class="form-label">상품 상세설명 이미지</label>
                        {{-- <div class="summernote-basic"></div> --}}
                        <input type="file" class="form-control" id="descImage" name="descImage" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label class="form-label">상품정보고시</label>
                        <select class="form-select js-select2" name="product_information" id="product_information">
                            @foreach ($productInformation as $i)
                                <option value="{{ $i->domesin_value }}">{{ $i->content }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="btn btn-primary" id="registerBtn" onclick="productRegister();">등록</button>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-gs mt-3">
        <div class="col">
            <div class="card card-bordered preview">
                <div class="card-inner">
                    <h5 class="card-title">상품 연동</h5>
                    <h6 class="card-subtitle mb-2">연동 키와 옵션을 통해 상품 연동을 설정합니다.
                    </h6>
                    <div class="form-group">
                        <label for="integrationKey">연동 키</label>
                        <input type="text" class="form-control" id="integrationKey" placeholder="연동 키를 기입해주세요."
                            name="integrationKey" required>
                    </div>
                    <div class="form-group">
                        <label for="integrationOptions">연동 옵션</label>
                        <select class="form-control js-select2" id="integrationOptions" name="integrationOptions"
                            data-search="on">
                            <option value="option1">옵션 1</option>
                            <option value="option2">옵션 2</option>
                            <option value="option3">옵션 3</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary" onclick="integrationInit();">연동</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <link rel="stylesheet" href="{{ asset('assets/css/editors/summernote.css') }}">
    {{-- <script src="{{ asset('assets/js/editors.js') }}"></script>
    <script src="{{ asset('assets/js/libs/editors/summernote.js') }}"></script> --}}
    <script>
        // var productDescImg;
        var apiUrl = "http:/127.0.0.1:8000/";

        // function insertImage(editor, welEditable, imageFile) {
        //     var reader = new FileReader();
        //     reader.onload = function(e) {
        //         editor.summernote('insertImage', e.target.result);
        //     };
        //     reader.readAsDataURL(imageFile);
        // }
        // $('.summernote-basic').summernote({
        //     height: ($(window).height() - 300),
        //     callbacks: {
        //         onImageUpload: function(files) {
        //             // 이미지 파일을 변수에 할당
        //             productDescImg = files[0];

        //             // 에디터에 이미지 삽입
        //             insertImage($('.summernote-basic'), $('.summernote-basic').summernote('core.editor'),
        //                 productDescImg);
        //         }
        //     }
        // });



        function categorySearch() {
            const keyword = $("#categoryInput").val();
            $("#searchBtn").html("검색 중...");
            $("#searchBtn").prop("disabled", true);
            $.ajax({
                url: '/api/product/category',
                type: 'post',
                dataType: 'json',
                data: {
                    keyword: keyword
                },
                success: function(result) {
                    console.log(result);
                    $("#searchBtn").html("검색");
                    $("#searchBtn").prop("disabled", false);
                    if (result.status == 1) {
                        let html = "";
                        for (let i = 0; i < result.return.length; i++) {
                            html += "<option value='" + result.return[i].code + "'>" + result.return[i]
                                .wholeCategoryName + "</option>";
                        }
                        $("#categoryResult").html(html);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: '진행 실패',
                            text: result.return
                        });
                    }
                },
                error: function(response) {
                    $("#searchBtn").html("검색");
                    $("#searchBtn").prop("disabled", false);
                    console.log(response);
                }
            });
        }

        function productRegister() {
            const formData = new FormData(); // FormData 객체 생성
            formData.append('remember_token', '{{ $remember_token }}');
            $("input[name='vendors']").each(function() {
                if ($(this).is(":checked") == true) {
                    var checkVal = $(this).val();
                    formData.append('vendors[]', checkVal);
                }
            });
            // const productDesc = $('.summernote-basic').summernote('code');
            const model = $('input[name="model"]').val();
            formData.append('model', model);
            const productDescImage = $('#descImage')[0].files[0];
            formData.append('productDescImage', productDescImage);
            // formData.append('productDesc', productDesc);
            formData.append('itemName', $("#productName").val());
            formData.append('invoiceName', $("#invoiceName").val());
            formData.append('category', $('#categoryResult option:selected').val());
            formData.append('keywords', $('#keywords').val());

            const taxability = $("input[name='taxability']:checked").next().text().trim();
            formData.append('taxability', taxability);

            const selectedFile = $('#productImage')[0].files[0];
            formData.append('productImage', selectedFile);

            const saleToMinor = $("input[name='saleToMinor']:checked").next().text().trim();
            formData.append('saleToMinor', saleToMinor);

            const origin = $("input[name='origin']:checked").next().text().trim();
            formData.append('origin', origin);

            const madicalEquipment = $("input[name='madicalEquipment']:checked").next().text().trim();
            formData.append('madicalEquipment', madicalEquipment);

            const healthFunctional = $("input[name='healthFunctional']:checked").next().text().trim();
            formData.append('healthFunctional', healthFunctional);

            const shipping = $("input[name='shipping']:checked").next().text().trim();
            formData.append('shipping', shipping);
            formData.append('shipCost', $('#shipCost').val());
            formData.append('price', $('#productPrice').val());
            formData.append('vendor', $('#vendor').val());
            const productInformation = $('#product_information').val();
            formData.append('product_information', productInformation);
            $('#registerBtn').prop('disabled', true);
            $("#registerBtn").html('로딩 중...');
            $.ajax({
                url: '/api/product/register',
                type: 'post',
                dataType: 'json',
                data: formData,
                processData: false, // FormData 처리 설정
                contentType: false, // Content-Type 설정
                success: function(response) {
                    $('#registerBtn').prop('disabled', false);
                    $("#registerBtn").html('등록');
                    console.log(response);
                    const status = parseInt(response.status);
                    const successVendors = response.success_vendors;
                    const errorVendors = response.error_vendors;
                    let icon, title;
                    if (status == 1) {
                        icon = 'success';
                        title = "진행 성공";
                    } else {
                        icon = "error";
                        title = "진행 실패";
                    }
                    Swal.fire({
                        icon: icon,
                        title: title,
                        text: response.return
                    });
                },
                error: function(response) {
                    $('#registerBtn').prop('disabled', false);
                    $("#registerBtn").html('등록');
                    console.log(response);
                }
            });
        }

        function productNameFormat(inputElement) {
            // 사용자가 입력한 값을 가져옴
            var inputValue = inputElement.value;

            // 허용할 문자의 아스키 코드 범위를 정의
            var allowedRanges = [
                [48, 57], // 숫자 (0-9)
                [65, 90], // 대문자 영어 (A-Z)
                [97, 122], // 소문자 영어 (a-z)
                [44032, 55203], // 한글 범위 (가-힣)
                [32, 32], // 공백
            ];

            // 입력값을 필터링하여 허용된 문자로 대체
            var filteredValue = "";
            for (var i = 0; i < inputValue.length; i++) {
                var charCode = inputValue.charCodeAt(i);
                var isAllowed = false;

                // 허용된 범위에 속하는지 확인
                for (var j = 0; j < allowedRanges.length; j++) {
                    var range = allowedRanges[j];
                    if (charCode >= range[0] && charCode <= range[1]) {
                        isAllowed = true;
                        break;
                    }
                }

                if (isAllowed) {
                    filteredValue += inputValue[i];
                }
            }

            // 입력 필드에 필터링된 값을 설정
            inputElement.value = filteredValue;
        }
    </script>
@endsection
