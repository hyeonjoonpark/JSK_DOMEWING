@extends('layouts.main')
@section('title')
    매핑윙
@endsection
@section('subtitle')
    <p>
        아직 매핑되지 않은 카테고리들을 업데이트해주세요.
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
                            <option value="-1">아직 매핑되지 않은 카테고리를 선택해주세요</option>
                            @foreach ($unmappedCategories as $category)
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
            popupLoader(1, '아직 매핑되지 않은 업체들을 추려내는 중입니다.');
            const ownerclanCategoryID = $(select).val();
            $.ajax({
                url: '/api/mappingwing/select-category',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    ownerclanCategoryID: ownerclanCategoryID
                },
                success: function(response) {
                    closePopup();
                    if (response.status) {
                        updateUnmappedB2B(response.return);
                    } else {
                        $('#selectCategoryResult').html("");
                        Swal.fire({
                            icon: 'error',
                            title: '진행 실패',
                            text: response.return
                        });
                    }
                },
                error: function(response) {
                    closePopup();
                    console.log(response);
                }
            });
        }

        function updateUnmappedB2B(unmappedB2Bs) {
            let html = '';
            const vendorIDs = [];
            for (b2B of unmappedB2Bs) {
                const b2BName = b2B.name;
                const b2BVendorId = b2B.vendorID;
                html += `
                    <div class="form-group">
                        <label for="" class="form-label">${b2BName}</label>
                        <div class="d-flex text-nowrap">
                            <input type="text" class="form-control" id="categoryID${b2BVendorId}" placeholder="검색 키워드를 기입해주세요." onkeydown="handleEnter(event, 'searchBtn${b2BVendorId}')">
                            <button class="btn btn-primary" id="searchBtn${b2BVendorId}">검색</button>
                        </div>
                        <select name="${b2BVendorId}" id="${b2BVendorId}" class="form-select js-select2"></select>
                    </div>
                `;
                vendorIDs.push(parseInt(b2BVendorId));
            }
            html += `
                <div class="d-flex justify-content-center">
                    <button class="btn btn-primary" onclick="initMapping(${vendorIDs});">저장하기</button>
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

        function initMapping(vendorIDs) {
            const mappedCategory = [];
            console.log(vendorIDs[1]);
        }
    </script>
@endsection
