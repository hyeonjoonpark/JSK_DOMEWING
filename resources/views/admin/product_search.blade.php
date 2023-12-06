@extends('layouts.main')
@section('title')
    상품 가공
@endsection
@section('subtitle')
    <p>
        공급사들로부터 상품 정보들을 검색, 가공, 및 수집합니다.
    </p>
@endsection
@section('content')
    <div class="row g-gs">
        <div class="col">
            <div class="card card-bordered preview">
                <div class="card-inner">
                    <h5 class="card-title">상품 대량 수집</h5>
                    <h6 class="card-subtitle mb-2">검색 엔진에 활용할 업체들을 선택하고, 검색 키워드를 기입해주세요.</h6>
                    <div class="form-group">
                        <label class="form-label">업체</label>
                        <div class="row">
                            @foreach ($searchVendors as $vendor)
                                <div class="col-3 mb-3">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input"
                                            id="vendor{{ $vendor->vendor_id }}" name="searchVendor"
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
                                <input type="text" class="form-control" id="searchKeyword" placeholder="상품 키워드를 기입해주세요."
                                    onkeydown="handleEnter(event, 'searchBtn')" />
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-primary" id="searchBtn" onclick="initSearch();">상품 검색</button>
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
@endsection
@section('scripts')
    <link rel="stylesheet" href="{{ asset('assets/css/editors/summernote.css') }}">
    <script src="{{ asset('assets/js/editors.js') }}"></script>
    <script src="{{ asset('assets/js/libs/editors/summernote.js') }}"></script>
    <script>
        function initSearch() {
            $('.btn').prop('disabled', true);
            let loadingGifs = [
                "{{ asset('assets/images/search-loader.gif') }}",
                "{{ asset('assets/images/loading.gif') }}"
            ];
            loadingGifs = shuffleArray(loadingGifs);
            let html = '<img src="' + loadingGifs[0] + '" class="w-75" />'
            html += '<h2 class="swal2-title mt-5">상품 데이터를 수집 중입니다</h2>'
            Swal.fire({
                html: html,
                allowOutsideClick: false,
                showConfirmButton: false
            });
            const searchVendors = [];
            $('input[name="searchVendor"]:checked').each(function() {
                searchVendors.push($(this).val());
            });
            const searchKeyword = $('#searchKeyword').val();
            operSearch(searchVendors, searchKeyword);
        }

        function shuffleArray(array) {
            for (let i = array.length - 1; i > 0; i--) {
                // 랜덤 인덱스 선택 (0 이상 현재 인덱스 i 이하)
                const j = Math.floor(Math.random() * (i + 1));
                // 현재 요소와 랜덤 인덱스의 요소 교환
                [array[i], array[j]] = [array[j], array[i]];
            }
            return array;
        }

        function operSearch(searchVendors, searchKeyword) {
            $.ajax({
                url: '/api/product/search',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    remember_token: '{{ Auth::user()->remember_token }}',
                    searchVendors: searchVendors,
                    searchKeyword: searchKeyword
                },
                success: function(response) {
                    $('btn').prop('disabled', false);
                    Swal.close();
                    console.log(response);
                    if (response.status) {
                        const icon = 'success';
                        const title = '진행 성공';
                        updateDataTable(response.return);
                    } else {
                        const icon = 'error';
                        const title = '진행 실패';
                    }
                    Swal.fire({
                        icon: icon,
                        title: title,
                        text: response.message
                    });
                },
                error: function(response) {
                    $('btn').prop('disabled', false);
                    Swal.close();
                    console.log(response);
                }
            });
        }

        function updateDataTable(products) {
            const dataTable = $('#productTable').DataTable();
            dataTable.clear().draw();
            for (let i = 0; i < products.length; i++) {
                const name = products[i].name;
                const price = products[i].price;
                const platform = products[i].platform;
                const image = products[i].image;
                const href = products[i].href;
                const checkbox =
                    '<div class="custom-control custom-control-sm custom-checkbox notext"><input type="checkbox" class="custom-control-input" id="productIndex' +
                    i + '"><label class="custom-control-label" for="productIndex' + i + '"></label></div>';
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
            dataTable.columns.adjust().draw();
        }

        function handleEnter(event, btnID) {
            if (event.key === 'Enter') {
                event.preventDefault(); // 엔터키의 기본 동작(새 줄 삽입)을 방지
                document.getElementById(btnID).click(); // 버튼 클릭 이벤트 실행
            }
        }
    </script>
@endsection
