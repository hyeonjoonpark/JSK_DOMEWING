@extends('layouts.main')
@section('title')
    상품 데이터 마이닝
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
                                                {{ $loop->first ? 'checked' : '' }}>
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
    <script>
        function handleEnter(event, btnID) {
            if (event.key === 'Enter') {
                event.preventDefault(); // 엔터키의 기본 동작(새 줄 삽입)을 방지
                document.getElementById(btnID).click(); // 버튼 클릭 이벤트 실행
            }
        }

        function initExtract() {
            const listURL = $('#listURL').val();
            const sellerID = $('input[name="sellers"]:checked').val();
            requestExtract(sellerID, listURL);
        }

        function requestExtract(sellerID, listURL) {
            popupLoader(0);
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
                    if (response.status) {
                        updateDataTable(response.return);
                        $('#numResult').html(response.return.length);
                        closePopup();
                        Swal.fire({
                            icon: 'success',
                            title: '진행 성공',
                            text: '고르미가 상품셋을 성공적으로 가져왔습니다!'
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
                    console.log(response);
                }
            });
        }

        function popupLoader(index) {
            const loaders = ["{{ asset('assets/images/loading.gif') }}",
                "{{ asset('assets/images/search-loader.gif') }}"
            ];
            $('.btn').prop('disabled', true);
            let html = '<img src="' + loaders[index] + '" class="w-75" />'
            html += '<h2 class="swal2-title mt-5">고르미가 상품셋을 가지러 떠납니다.</h2>'
            Swal.fire({
                html: html,
                allowOutsideClick: false,
                showConfirmButton: false
            });
        }

        function closePopup() {
            Swal.close();
            $('.btn').prop('disabled', false);
        }

        function updateDataTable(products) {
            const dataTable = $('#productTable').DataTable();
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
                dataTable.row.add([
                    checkbox,
                    imageHtml,
                    nameHtml,
                    price,
                    platform
                ]).draw(false);
            }
            // 각 컬럼의 너비 조정
            dataTable.columns.adjust().draw();
        }
    </script>
@endsection
