@extends('layouts.main')
@section('title')
    매핑윙
@endsection
@section('subtitle')
    <p>
        매핑된 카테고리 데이터셋을 관리합니다.
    </p>
@endsection
@section('content')
    <div class="row g-gs">
        <div class="col-12 col-md-6">
            <div class="card card-bordered">
                <div class="card-inner">
                    <div class="form-group">
                        <label for="" class="form-label">오너클랜 카테고리</label>
                        <select name="ownerclanCategoryID" id="ownerclanCategoryID" class="form-select js-select2"
                            onchange="selectCategory(this);" data-search="on">
                            <option value="-1">조회하고자 하는 카테고리를 선택해주세요</option>
                            @foreach ($categoryMapping as $category)
                                <option value="{{ $category->ownerclan }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="card card-bordered">
                <div class="card-inner">
                    <div id="selectCategoryResult"></div>
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
        function selectCategory(select) {
            popupLoader(1, '카테고리 매핑 정보를 불러오는 중이에요.');
            const ownerclanCategoryID = $(select).val();
            $.ajax({
                url: '/api/mappingwing/get-mapped',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    ownerclanCategoryID: ownerclanCategoryID
                },
                success: function(response) {
                    closePopup();
                    console.log(response);
                    if (response.status) {
                        updateMappedB2B(response.return, ownerclanCategoryID);
                    } else {
                        swalError(response.return);
                    }
                },
                error: function(response) {
                    closePopup();
                    console.log(response);
                }
            });
        }

        function updateMappedB2B(unmappedB2Bs, ownerclanCategoryID) {
            let html = '';
            const vendorIDs = [];
            for (b2B of unmappedB2Bs) {
                const b2BID = b2B.b2BID;
                const b2BName = b2B.b2BName;
                const categoryID = b2B.categoryID;
                const categoryName = b2B.categoryName;

                html += `
                    <div class="form-group">
                        <label for="" class="form-label">${b2BName}</label>
                        <div class="d-flex text-nowrap">
                            <input type="text" class="form-control" id="searchKeyword${b2BID}" placeholder="검색 키워드를 기입해주세요." onkeydown="handleEnter(event, 'searchBtn${b2BID}')">
                            <button class="btn btn-primary" id="searchBtn${b2BID}" onclick="categorySearch(${b2BID});">검색</button>
                        </div>
                        <select name="categoryID${b2BID}" id="categoryID${b2BID}" class="form-select js-select2">
                            <option value="${categoryID}">${categoryName}</option>
                        </select>
                    </div>
                `;
                vendorIDs.push(b2BID);
            }
            html += `
                <div class="d-flex justify-content-center">
                    <button class="btn btn-primary" onclick="initMapping(${JSON.stringify(vendorIDs)}, ${ownerclanCategoryID});">저장하기</button>
                </div>
            `;
            $('#selectCategoryResult').html(html);
        }

        function handleEnter(event, btnID) {
            if (event.key === 'Enter') {
                event.preventDefault(); // 엔터키의 기본 동작(새 줄 삽입)을 방지
                document.getElementById(btnID).click(); // 버튼 클릭 이벤트 실행
            }
        }

        function initMapping(vendorIDs, ownerclanCategoryID) {
            const mappedCategory = [];
            for (vendorID of vendorIDs) {
                const tmpData = {
                    vendorID: vendorID,
                    categoryID: $("#categoryID" + vendorID).val()
                };
                mappedCategory.push(tmpData);
            }
            requestMapping(ownerclanCategoryID, mappedCategory);
        }

        function requestMapping(ownerclanCategoryID, mappedCategory) {
            console.log(mappedCategory);
            popupLoader(0, "매핑된 카테고리를 저장소로 옮기는 중이에요.");
            $.ajax({
                url: "/api/mappingwing/request-mapping",
                type: "POST",
                dataType: "JSON",
                data: {
                    ownerclanCategoryID: ownerclanCategoryID,
                    mappedCategory: mappedCategory
                },
                success: function(response) {
                    closePopup();
                    if (response.status) {
                        Swal.fire({
                            icon: 'success',
                            html: '<img class="w-100" src="{{ asset('media/Asset_Notif_Success.svg') }}"><h4 class="swal2-title mt-5">' +
                                response.return+'</h4>'
                        }).then((result) => {
                            location.reload();
                        });
                    }
                },
                error: function(response) {
                    closePopup();
                    console.log(response);
                }
            });
        }

        function categorySearch(vendorID) {
            $('.btn').prop("disabled", true);
            const keyword = $("#searchKeyword" + vendorID).val();
            $.ajax({
                url: "/api/mappingwing/category-search",
                type: "POST",
                dataType: "JSON",
                data: {
                    vendorID: vendorID,
                    keyword: keyword
                },
                success: function(response) {
                    let options = '';
                    for (category of response) {
                        options += '<option value="' + category.id + '">' + category.name + '</option>';
                    }
                    $("#categoryID" + vendorID).html(options);
                    $('.btn').prop("disabled", false);
                },
                error: function(response) {
                    $('.btn').prop("disabled", false);
                    console.log(response);
                }
            });
        }
    </script>
@endsection
