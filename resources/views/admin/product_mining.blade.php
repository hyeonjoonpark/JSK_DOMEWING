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
                                <div class="col-12 col-md-3 mb-3">
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
        var varIndex, varDupIndex;
        var varProducts = [{
                "productName": "우리 LED 엣지 원형 센서등 8인치 15W 주광색",
                "productPrice": "5600",
                "productImage": "http://ds1008.hgodo.com/gd5replace/zlem2432/data/goods/23/10/42/1000032307/1000032307_detail_079.jpg",
                "productDetail": [
                    "http://ds1008.hgodo.com/gd5replace/zlem2432/data/goods/editor/231018/c_145722.jpg"
                ],
                "hasOption": "false",
                "productHref": "https://www.ds1008.com/goods/goods_view.php?goodsNo=1000032307",
                "sellerID": "14"
            },
            {
                "productName": "우리 LED 엣지 원형 직부등 8인치 15W 주광색",
                "productPrice": "4400",
                "productImage": "http://ds1008.hgodo.com/gd5replace/zlem2432/data/goods/23/10/42/1000032306/1000032306_detail_010.jpg",
                "productDetail": [
                    "http://ds1008.hgodo.com/gd5replace/zlem2432/data/goods/editor/231018/c_145524.jpg"
                ],
                "hasOption": "false",
                "productHref": "https://www.ds1008.com/goods/goods_view.php?goodsNo=1000032306",
                "sellerID": "14"
            },
            {
                "productName": "히포 LED터널등기구30W 욕실용 03233",
                "productPrice": "16500",
                "productImage": "http://ds1008.hgodo.com/gd5replace/zlem2432/data/goods/16/03/21/6716/6716_detail_06.jpg",
                "productDetail": [
                    "http://ds1008.hgodo.com/gd5replace/zlem2432/data/goods/editor/211122/32_led_light_142653.jpg"
                ],
                "hasOption": "true",
                "productOptions": [{
                        "optionName": "낱개",
                        "optionPrice": "0"
                    },
                    {
                        "optionName": "10개묶음(개당15,500원)",
                        "optionPrice": "138500"
                    }
                ],
                "productHref": "https://www.ds1008.com/goods/goods_view.php?goodsNo=6716",
                "sellerID": "14"
            },
            {
                "productName": "히포 LED터널등기구20W 욕실용 03232",
                "productPrice": "13000",
                "productImage": "http://ds1008.hgodo.com/gd5replace/zlem2432/data/goods/16/03/22/6717/6717_detail_088.jpg",
                "productDetail": [
                    "http://ds1008.hgodo.com/gd5replace/zlem2432/data/goods/editor/211122/31_led_light_142539.jpg"
                ],
                "hasOption": "true",
                "productOptions": [{
                        "optionName": "낱개",
                        "optionPrice": "0"
                    },
                    {
                        "optionName": "10개묶음(개당 10,500원)",
                        "optionPrice": "92000"
                    }
                ],
                "productHref": "https://www.ds1008.com/goods/goods_view.php?goodsNo=6717",
                "sellerID": "14"
            },
            {
                "productName": "히포 LED터널등기구15W 욕실용 03231",
                "productPrice": "11000",
                "productImage": "http://ds1008.hgodo.com/gd5replace/zlem2432/data/goods/17/06/28/8435/8435_detail_08.jpg",
                "productDetail": [
                    "http://ds1008.hgodo.com/gd5replace/zlem2432/data/goods/editor/211122/30_led_light_142425.jpg"
                ],
                "hasOption": "true",
                "productOptions": [{
                        "optionName": "낱개",
                        "optionPrice": "0"
                    },
                    {
                        "optionName": "5개묶음시(개당9300원)",
                        "optionPrice": "35500"
                    }
                ],
                "productHref": "https://www.ds1008.com/goods/goods_view.php?goodsNo=8435",
                "sellerID": "14"
            },
            {
                "productName": "히포 DAA030 LED등기구30W DLFL238 불투명 주",
                "productPrice": "7200",
                "productImage": "https://cdn-pro-web-250-123.cdn-nhncommerce.com/zlem2432_godomall_com/data/goods/16/03/22/6721/1502069543646m0.jpg",
                "productDetail": [
                    "http://ds1008.hgodo.com/gd5replace/zlem2432/data/editor/goods/191017/281770615111089c4e577ae7f12173b2_153336.jpg",
                    "http://ds1008.hgodo.com/gd5replace/zlem2432/data/editor/goods/1466487615.jpg"
                ],
                "hasOption": "true",
                "productOptions": [{
                        "optionName": "주광 낱개",
                        "optionPrice": "0"
                    },
                    {
                        "optionName": "주광20개묶음(개당6,200원)",
                        "optionPrice": "120800"
                    }
                ],
                "productHref": "https://www.ds1008.com/goods/goods_view.php?goodsNo=6721",
                "sellerID": "14"
            },
            {
                "productName": "히포 DAC030 LED등기구30W DLFL238C 크리스탈",
                "productPrice": "7150",
                "productImage": "https://cdn-pro-web-250-123.cdn-nhncommerce.com/zlem2432_godomall_com/data/goods/16/03/22/6723/1502069515282m0.jpg",
                "productDetail": [
                    "http://ds1008.hgodo.com/gd5replace/zlem2432/data/editor/goods/1466487586.jpg"
                ],
                "hasOption": "true",
                "productOptions": [{
                        "optionName": "주광색 낱개",
                        "optionPrice": "0"
                    },
                    {
                        "optionName": "주광색 20개묶음(개당6,600원)",
                        "optionPrice": "124850"
                    }
                ],
                "productHref": "https://www.ds1008.com/goods/goods_view.php?goodsNo=6723",
                "sellerID": "14"
            },
            {
                "productName": "히포 DAD055 LED등기구55W DLFL259C 크리스탈",
                "productPrice": "13700",
                "productImage": "https://cdn-pro-web-250-123.cdn-nhncommerce.com/zlem2432_godomall_com/data/goods/16/03/22/6718/6718_detail_065.jpg",
                "productDetail": [
                    "http://ds1008.hgodo.com/gd5replace/zlem2432/data/editor/goods/221012/01_161055.jpg"
                ],
                "hasOption": "true",
                "productOptions": [{
                        "optionName": "낱개",
                        "optionPrice": "0"
                    },
                    {
                        "optionName": "10개묶음(개당 12.900원)",
                        "optionPrice": "115300"
                    }
                ],
                "productHref": "https://www.ds1008.com/goods/goods_view.php?goodsNo=6718",
                "sellerID": "14"
            },
            {
                "productName": "LED평판 직하무타공W2 1285320 50W 주광 65K",
                "productPrice": "120000",
                "productImage": "https://cdn-pro-web-250-123.cdn-nhncommerce.com/zlem2432_godomall_com/data/goods/23/07/29/1000032286/1000032285_detail_09.jpg",
                "productDetail": [
                    "http://ds1008.hgodo.com/gd5replace/zlem2432/data/editor/goods/230914/ilsin_130444.jpg",
                    "https://vitsonimg.co.kr/images/productsNew/374/374579%20(10).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/374/374579%20(2)%20(1).png",
                    "https://vitsonimg.co.kr/images/productsNew/374/374579%20(1).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/374/374579%20(2)%20(1).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/374/374579%20(3).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/374/374579%20(3).png",
                    "https://vitsonimg.co.kr/images/productsNew/374/374579%20(4).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/374/374579%20(5).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/374/374579%20(6).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/374/ch3755xx%2050w%201285%20320%20%EC%9D%B8%EC%A6%9D%EC%84%9C%20(1).png"
                ],
                "hasOption": "false",
                "productHref": "https://www.ds1008.com/goods/goods_view.php?goodsNo=1000032286",
                "sellerID": "14"
            },
            {
                "productName": "LED평판 직하(무타공)_W2/1285*320 50W 주광 6.5K/IN-374579",
                "productPrice": "27000",
                "productImage": "https://cdn-pro-web-250-123.cdn-nhncommerce.com/zlem2432_godomall_com/data/goods/23/07/29/1000032285/1000032285_detail_09.jpg",
                "productDetail": [
                    "http://ds1008.hgodo.com/gd5replace/zlem2432/data/editor/goods/230914/ilsin_130444.jpg",
                    "https://vitsonimg.co.kr/images/productsNew/374/374579%20(10).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/374/374579%20(2)%20(1).png",
                    "https://vitsonimg.co.kr/images/productsNew/374/374579%20(1).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/374/374579%20(2)%20(1).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/374/374579%20(3).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/374/374579%20(3).png",
                    "https://vitsonimg.co.kr/images/productsNew/374/374579%20(4).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/374/374579%20(5).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/374/374579%20(6).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/374/ch3755xx%2050w%201285%20320%20%EC%9D%B8%EC%A6%9D%EC%84%9C%20(1).png"
                ],
                "hasOption": "false",
                "productHref": "https://www.ds1008.com/goods/goods_view.php?goodsNo=1000032285",
                "sellerID": "14"
            },
            {
                "productName": "코콤 60W/LED십자등 루미플러스(주광색)/49236",
                "productPrice": "18040",
                "productImage": "https://cdn-pro-web-250-123.cdn-nhncommerce.com/zlem2432_godomall_com/data/goods/19/03/20/13919/13919_detail_04.jpg",
                "productDetail": [
                    "http://ds1008.hgodo.com/gd5replace/zlem2432/data/editor/goods/191017/281770615111089c4e577ae7f12173b2_153336.jpg",
                    "http://ds1008.hgodo.com/gd5replace/zlem2432/data/editor/goods/1553055764.jpg",
                    "http://ds1008.hgodo.com/gd5replace/zlem2432/data/editor/goods/1553055771.jpg",
                    "http://ds1008.hgodo.com/gd5replace/zlem2432/data/editor/goods/1553055777.jpg",
                    "http://ds1008.hgodo.com/gd5replace/zlem2432/data/editor/goods/1553055783.jpg",
                    "http://ds1008.hgodo.com/gd5replace/zlem2432/data/editor/goods/1553055795.jpg"
                ],
                "hasOption": "true",
                "productOptions": [{
                        "optionName": "낱개",
                        "optionPrice": "0"
                    },
                    {
                        "optionName": "10개묶음(개당 16,390원)",
                        "optionPrice": "145860"
                    }
                ],
                "productHref": "https://www.ds1008.com/goods/goods_view.php?goodsNo=13919",
                "sellerID": "14"
            },
            {
                "productName": "LED평판 엣지형 더 스타일/640*640 50W★주광★KS/43004",
                "productPrice": "38000",
                "productImage": "https://vitsonimg.co.kr/images/products/43004.jpg",
                "productDetail": [
                    "https://vitsonimg.co.kr/images/products/43004.jpg",
                    "https://vitsonimg.co.kr/images/products/43004%20(16).JPG",
                    "https://vitsonimg.co.kr/images/products/43004%20(17).JPG",
                    "https://vitsonimg.co.kr/images/products/43004%20(11)%20(1).JPG",
                    "https://vitsonimg.co.kr/images/products/43004%20(12)%20(1).JPG",
                    "https://vitsonimg.co.kr/images/products/43004%20(10)%20(2).JPG",
                    "https://vitsonimg.co.kr/images/productsNew/43/43004.jpg",
                    "https://vitsonimg.co.kr/images/productsNew/83/000.png",
                    "https://vitsonimg.co.kr/images/certificates/37172.jpg"
                ],
                "hasOption": "false",
                "productHref": "https://www.ds1008.com/goods/goods_view.php?goodsNo=14433",
                "sellerID": "14"
            },
            {
                "productName": "PL 등기구 LED 30W 일자 (전구색) KC IN-43329",
                "productPrice": "4800",
                "productImage": "https://vitsonimg.co.kr/images/products/43329%20(2).JPG",
                "productDetail": [
                    "http://ds1008.hgodo.com/gd5replace/zlem2432/data/editor/goods/230914/ilsin_130444.jpg",
                    "http://ds1008.hgodo.com/gd5replace/zlem2432/data/editor/goods/191017/281770615111089c4e577ae7f12173b2_153336.jpg",
                    "https://vitsonimg.co.kr/images/products/433291.jpg",
                    "https://vitsonimg.co.kr/images/products/43329%20(2).JPG",
                    "https://vitsonimg.co.kr/images/products/43329%20(4).JPG",
                    "https://vitsonimg.co.kr/images/products/43329%20(6).JPG",
                    "https://vitsonimg.co.kr/images/products/43329%20(7).JPG",
                    "https://vitsonimg.co.kr/images/products/43329%20(9).JPG",
                    "https://vitsonimg.co.kr/images/products/43329%20(10).JPG",
                    "https://vitsonimg.co.kr/images/products/43329%20(11).JPG",
                    "https://vitsonimg.co.kr/images/productsNew/43/43329.jpg",
                    "https://vitsonimg.co.kr/images/productsNew/43/43327_43329%20(2).png"
                ],
                "hasOption": "true",
                "productOptions": [{
                        "optionName": "낱개",
                        "optionPrice": "0"
                    },
                    {
                        "optionName": "20개묶음 (개당4,300원)",
                        "optionPrice": "81200"
                    }
                ],
                "productHref": "https://www.ds1008.com/goods/goods_view.php?goodsNo=1000029567",
                "sellerID": "14"
            },
            {
                "productName": "코콤 30W/LED일자등 루미플러스(주광색)/49235",
                "productPrice": "8250",
                "productImage": "https://cdn-pro-web-250-123.cdn-nhncommerce.com/zlem2432_godomall_com/data/goods/19/03/20/13918/13918_detail_026.jpg",
                "productDetail": [
                    "http://ds1008.hgodo.com/gd5replace/zlem2432/data/editor/goods/191017/281770615111089c4e577ae7f12173b2_153336.jpg",
                    "http://ds1008.hgodo.com/gd5replace/zlem2432/data/editor/goods/1553055420.jpg",
                    "http://ds1008.hgodo.com/gd5replace/zlem2432/data/editor/goods/1553055434.jpg",
                    "http://ds1008.hgodo.com/gd5replace/zlem2432/data/editor/goods/1553055472.jpg"
                ],
                "hasOption": "true",
                "productOptions": [{
                        "optionName": "낱개",
                        "optionPrice": "0"
                    },
                    {
                        "optionName": "20개묶음(개당7590원)",
                        "optionPrice": "143550"
                    }
                ],
                "productHref": "https://www.ds1008.com/goods/goods_view.php?goodsNo=13918",
                "sellerID": "14"
            },
            {
                "productName": "다운라이트 LED 슬림 5인치 15W 주광색/IN-374563",
                "productPrice": "2900",
                "productImage": "https://vitsonimg.co.kr/images/productsNew/374/374563%20(1).jpg",
                "productDetail": [
                    "http://ds1008.hgodo.com/gd5replace/zlem2432/data/editor/goods/230914/ilsin_130444.jpg",
                    "https://vitsonimg.co.kr/images/productsNew/374/374563%20(1).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/374/374563.jpg",
                    "https://vitsonimg.co.kr/images/productsNew/374/374563%20(2).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/374/374563%20(3).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/374/374563%20(4).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/374/374563%20(5).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/374/5%EC%9D%B8%EC%B9%98.jpg",
                    "https://vitsonimg.co.kr/images/productsNew/374/%EC%95%88%EC%A0%84%EC%9D%B8%EC%A6%9D%EC%84%9C%20(9).png"
                ],
                "hasOption": "false",
                "productHref": "https://www.ds1008.com/goods/goods_view.php?goodsNo=1000032320",
                "sellerID": "14"
            },
            {
                "productName": "다운라이트 LED 슬림 3인치 6W 주광색/IN-374554",
                "productPrice": "1800",
                "productImage": "https://vitsonimg.co.kr/images/productsNew/374/374554%20(1).jpg",
                "productDetail": [
                    "http://ds1008.hgodo.com/gd5replace/zlem2432/data/editor/goods/230914/ilsin_130444.jpg",
                    "https://vitsonimg.co.kr/images/productsNew/374/374554%20(1).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/374/374554.jpg",
                    "https://vitsonimg.co.kr/images/productsNew/374/374554%20(2).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/374/374554%20(3).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/374/374554%20(4).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/374/374554%20(5).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/374/3%EC%9D%B8%EC%B9%98.jpg",
                    "https://vitsonimg.co.kr/images/productsNew/374/%EC%95%88%EC%A0%84%EC%9D%B8%EC%A6%9D%EC%84%9C.png"
                ],
                "hasOption": "false",
                "productHref": "https://www.ds1008.com/goods/goods_view.php?goodsNo=1000032319",
                "sellerID": "14"
            },
            {
                "productName": "다운라이트 LED 슬림 6인치20w ☆주광☆/IN-374569",
                "productPrice": "4100",
                "productImage": "http://ds1008.hgodo.com/gd5replace/zlem2432/data/goods/23/02/07/1000032065/1000032065_detail_06.png",
                "productDetail": [
                    "http://ds1008.hgodo.com/gd5replace/zlem2432/data/editor/goods/230914/ilsin_130444.jpg",
                    "https://vitsonimg.co.kr/images/productsNew/374/374569%20(1).png",
                    "https://vitsonimg.co.kr/images/productsNew/374/374569.jpg",
                    "https://vitsonimg.co.kr/images/productsNew/374/374569%20(2).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/374/374569%20(3).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/374/374569%20(4).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/374/374569%20(5).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/374/374569%20(5).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/374/374569%20(6).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/374/%EC%95%88%EC%A0%84%EC%9D%B8%EC%A6%9D%EC%84%9C%20(15).png"
                ],
                "hasOption": "false",
                "productHref": "https://www.ds1008.com/goods/goods_view.php?goodsNo=1000032065",
                "sellerID": "14"
            },
            {
                "productName": "새장직부/69244",
                "productPrice": "28000",
                "productImage": "https://cdn-pro-web-250-123.cdn-nhncommerce.com/zlem2432_godomall_com/data/goods/15/06/30/6328/1435665326123m0.jpg",
                "productDetail": [
                    "https://cdn-pro-web-250-123.cdn-nhncommerce.com/zlem2432_godomall_com/data/editor/goods/1435665321.jpg",
                    "https://cdn-pro-web-250-123.cdn-nhncommerce.com/zlem2432_godomall_com/data/editor/goods/1436349767.jpg"
                ],
                "hasOption": "true",
                "productOptions": [{
                        "optionName": "낱개",
                        "optionPrice": "0"
                    },
                    {
                        "optionName": "2개묶음(개당25,500원)",
                        "optionPrice": "23000"
                    },
                    {
                        "optionName": "4개묶음(개당23,500원)",
                        "optionPrice": "66000"
                    }
                ],
                "productHref": "https://www.ds1008.com/goods/goods_view.php?goodsNo=6328",
                "sellerID": "14"
            },
            {
                "productName": "히포/DAB055/LED등기구-55W/DLFL-259/불투명/03218",
                "productPrice": "13700",
                "productImage": "https://cdn-pro-web-250-123.cdn-nhncommerce.com/zlem2432_godomall_com/data/goods/18/06/27/11092/11092_detail_051.jpg",
                "productDetail": [
                    "http://ds1008.hgodo.com/gd5replace/zlem2432/data/editor/goods/221012/01_162436.jpg"
                ],
                "hasOption": "true",
                "productOptions": [{
                        "optionName": "낱개",
                        "optionPrice": "0"
                    },
                    {
                        "optionName": "10개묶음(개당 12,900원)",
                        "optionPrice": "115300"
                    }
                ],
                "productHref": "https://www.ds1008.com/goods/goods_view.php?goodsNo=11092",
                "sellerID": "14"
            },
            {
                "productName": "다운라이트 LED(MR16 일체형)/ 3인치 5W전구(75파이)/IN-48151",
                "productPrice": "3000",
                "productImage": "https://vitsonimg.co.kr/images/products/48151.JPG",
                "productDetail": [
                    "http://ds1008.hgodo.com/gd5replace/zlem2432/data/editor/goods/230914/ilsin_130444.jpg",
                    "http://ds1008.hgodo.com/gd5replace/zlem2432/data/editor/goods/191017/281770615111089c4e577ae7f12173b2_153336.jpg",
                    "https://vitsonimg.co.kr/images/products/48151.JPG",
                    "https://vitsonimg.co.kr/images/products/48151%20(2).JPG",
                    "https://vitsonimg.co.kr/images/products/48151%20(3).JPG",
                    "https://vitsonimg.co.kr/images/products/48151%20(4).JPG",
                    "https://vitsonimg.co.kr/images/products/48151%20(5).JPG",
                    "https://vitsonimg.co.kr/images/products/48151%20(6).JPG",
                    "https://vitsonimg.co.kr/images/products/48151%20(7).JPG",
                    "https://vitsonimg.co.kr/images/products/48151%20(8).JPG",
                    "https://vitsonimg.co.kr/images/products/48151%20(11).JPG"
                ],
                "hasOption": "false",
                "productHref": "https://www.ds1008.com/goods/goods_view.php?goodsNo=1000007866",
                "sellerID": "14"
            },
            {
                "productName": "미란다/LED원형 센서등/주광색/안정기내장형/15w/국산/할인행사품목/69242",
                "productPrice": "5700",
                "productImage": "http://ds1008.hgodo.com/gd5replace/zlem2432/data/goods/19/11/47/1000010395/1000010395_detail_04.jpg",
                "productDetail": [
                    "http://ds1008.hgodo.com/gd5replace/zlem2432/data/editor/goods/191017/281770615111089c4e577ae7f12173b2_153336.jpg",
                    "http://ds1008.hgodo.com/gd5replace/zlem2432/data/goods/editor/230201/01_151851.jpg"
                ],
                "hasOption": "true",
                "productOptions": [{
                        "optionName": "낱개",
                        "optionPrice": "0"
                    },
                    {
                        "optionName": "20개묶음(개당4,900원)",
                        "optionPrice": "92300"
                    }
                ],
                "productHref": "https://www.ds1008.com/goods/goods_view.php?goodsNo=1000010395",
                "sellerID": "14"
            },
            {
                "productName": "미란다/LED원형 직부등 주광색 15W 국산/할인행사품목/60264",
                "productPrice": "4600",
                "productImage": "http://ds1008.hgodo.com/gd5replace/zlem2432/data/goods/19/11/47/1000010394/1000010394_detail_072.jpg",
                "productDetail": [
                    "http://ds1008.hgodo.com/gd5replace/zlem2432/data/goods/editor/230201/01_151756.jpg"
                ],
                "hasOption": "true",
                "productOptions": [{
                        "optionName": "낱개",
                        "optionPrice": "0"
                    },
                    {
                        "optionName": "20개묶음(개당4,100원)",
                        "optionPrice": "77400"
                    }
                ],
                "productHref": "https://www.ds1008.com/goods/goods_view.php?goodsNo=1000010394",
                "sellerID": "14"
            },
            {
                "productName": "사각LED센서등/초슬림엣지/20W/주광색/in-50071",
                "productPrice": "8700",
                "productImage": "https://cdn-pro-web-250-123.cdn-nhncommerce.com/zlem2432_godomall_com/data/goods/20/03/12/1000011983/1000011983_detail_028.jpg",
                "productDetail": [
                    "http://ds1008.hgodo.com/gd5replace/zlem2432/data/editor/goods/230914/ilsin_130444.jpg",
                    "http://ds1008.hgodo.com/gd5replace/zlem2432/data/editor/goods/191017/281770615111089c4e577ae7f12173b2_153336.jpg",
                    "https://vitsonimg.co.kr/images/products/500711.jpg",
                    "https://vitsonimg.co.kr/images/products/50071%20(2).JPG",
                    "https://vitsonimg.co.kr/images/products/50071%20(3).JPG",
                    "https://vitsonimg.co.kr/images/products/50071%20(4).JPG",
                    "https://vitsonimg.co.kr/images/products/50071%20(5).JPG",
                    "https://vitsonimg.co.kr/images/products/50071%20(6).JPG",
                    "https://vitsonimg.co.kr/images/products/50071%20(7).JPG",
                    "https://vitsonimg.co.kr/images/products/50071%20(8).JPG",
                    "https://vitsonimg.co.kr/images/products/50071%20(9).JPG",
                    "https://vitsonimg.co.kr/images/productsNew/50/50071%20(10).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/50/50071%20(11).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/50/50071.jpg",
                    "https://vitsonimg.co.kr/images/productsNew/7/%EC%84%BC%EC%84%9C.jpg"
                ],
                "hasOption": "false",
                "productHref": "https://www.ds1008.com/goods/goods_view.php?goodsNo=1000011983",
                "sellerID": "14"
            },
            {
                "productName": "사각LED투광등 S-B-7 (신형) 40W 화이트 ★주광★ AC IN-44658",
                "productPrice": "29900",
                "productImage": "https://vitsonimg.co.kr/images/products/44658.JPG",
                "productDetail": [
                    "http://ds1008.hgodo.com/gd5replace/zlem2432/data/editor/goods/230914/ilsin_130444.jpg",
                    "https://vitsonimg.co.kr/images/products/44658.JPG",
                    "https://vitsonimg.co.kr/images/products/44658%20(3).JPG",
                    "https://vitsonimg.co.kr/images/products/44658%20(4).JPG",
                    "https://vitsonimg.co.kr/images/products/44658%20(5).JPG",
                    "https://vitsonimg.co.kr/images/products/44658%20(6).JPG",
                    "https://vitsonimg.co.kr/images/products/44658%20(9).JPG",
                    "https://vitsonimg.co.kr/images/productsNew/44/44658.jpg"
                ],
                "hasOption": "false",
                "productHref": "https://www.ds1008.com/goods/goods_view.php?goodsNo=1000029720",
                "sellerID": "14"
            },
            {
                "productName": "사각LED투광기 화이트 미니 30W 노출 전구색 KC IN-243082",
                "productPrice": "11000",
                "productImage": "https://vitsonimg.co.kr/images/productsNew/243/243082%20(2).jpg",
                "productDetail": [
                    "http://ds1008.hgodo.com/gd5replace/zlem2432/data/editor/goods/230914/ilsin_130444.jpg",
                    "https://vitsonimg.co.kr/images/productsNew/243/243082%20(2).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/243/243082.png",
                    "https://vitsonimg.co.kr/images/productsNew/243/243082%20(4).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/243/243082%20(5).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/243/243082%20(6).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/243/243082%20(7).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/243/243082%20(9).jpg",
                    "http://ds1008.hgodo.com/gd5replace/zlem2432/data/editor/goods/220223/58950944057940fff33d8ebefa44a390_155849.jpg",
                    "https://vitsonimg.co.kr/images/productsNew/236/30w.png"
                ],
                "hasOption": "false",
                "productHref": "https://www.ds1008.com/goods/goods_view.php?goodsNo=1000029719",
                "sellerID": "14"
            },
            {
                "productName": "사각LED투광기 화이트 미니 30W 노출 주광색 KC IN-236344",
                "productPrice": "11000",
                "productImage": "https://vitsonimg.co.kr/images/productsNew/236/236344%20(2).jpg",
                "productDetail": [
                    "http://ds1008.hgodo.com/gd5replace/zlem2432/data/editor/goods/230914/ilsin_130444.jpg",
                    "https://vitsonimg.co.kr/images/productsNew/236/236344%20(2).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/236/236344.png",
                    "https://vitsonimg.co.kr/images/productsNew/236/236344%20(4).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/236/236344%20(5).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/236/236344%20(6).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/236/236344%20(7).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/236/236344%20(9).jpg",
                    "http://ds1008.hgodo.com/gd5replace/zlem2432/data/editor/goods/220223/58950944057940fff33d8ebefa44a390_155757.jpg",
                    "https://vitsonimg.co.kr/images/productsNew/236/30w.png"
                ],
                "hasOption": "false",
                "productHref": "https://www.ds1008.com/goods/goods_view.php?goodsNo=1000029718",
                "sellerID": "14"
            },
            {
                "productName": "미란다/LED등기구/안정기내장형/60W/주광색/십자/국산/69241",
                "productPrice": "10500",
                "productImage": "http://ds1008.hgodo.com/gd5replace/zlem2432/data/goods/20/04/18/1000012742/1000012742_detail_048.jpg",
                "productDetail": [
                    "http://ds1008.hgodo.com/gd5replace/zlem2432/data/editor/goods/191017/281770615111089c4e577ae7f12173b2_153336.jpg",
                    "http://ds1008.hgodo.com/gd5replace/zlem2432/data/goods/editor/230201/c2_143125.jpg"
                ],
                "hasOption": "true",
                "productOptions": [{
                        "optionName": "낱개",
                        "optionPrice": "0"
                    },
                    {
                        "optionName": "10개묶음(개당9,900원)",
                        "optionPrice": "88500"
                    }
                ],
                "productHref": "https://www.ds1008.com/goods/goods_view.php?goodsNo=1000012742",
                "sellerID": "14"
            },
            {
                "productName": "미란다/LED등기구/안정기내장형/30W TYPE/주광색/일자/국산/60262",
                "productPrice": "4500",
                "productImage": "http://ds1008.hgodo.com/gd5replace/zlem2432/data/goods/19/11/47/1000010386/1000010386_detail_093.jpg",
                "productDetail": [
                    "http://ds1008.hgodo.com/gd5replace/zlem2432/data/goods/editor/230329/c2_114052.jpg"
                ],
                "hasOption": "true",
                "productOptions": [{
                        "optionName": "낱개",
                        "optionPrice": "0"
                    },
                    {
                        "optionName": "30개묶음(개당 3,900원)",
                        "optionPrice": "112500"
                    }
                ],
                "productHref": "https://www.ds1008.com/goods/goods_view.php?goodsNo=1000010386",
                "sellerID": "14"
            },
            {
                "productName": "다운라이트 LED/6인치 20W★주광★/40154",
                "productPrice": "5800",
                "productImage": "https://vitsonimg.co.kr/images/products/401543.jpg",
                "productDetail": [
                    "http://ds1008.hgodo.com/gd5replace/zlem2432/data/editor/goods/191017/281770615111089c4e577ae7f12173b2_153336.jpg",
                    "https://vitsonimg.co.kr/images/products/401543.jpg",
                    "https://vitsonimg.co.kr/images/products/40154%20(2)2.JPG",
                    "https://vitsonimg.co.kr/images/products/40154%20(3)2.JPG",
                    "https://vitsonimg.co.kr/images/products/40154%20(4)2.JPG",
                    "https://vitsonimg.co.kr/images/products/40154%20(5)2.JPG",
                    "https://vitsonimg.co.kr/images/products/40154%20(6)2.JPG",
                    "https://vitsonimg.co.kr/images/products/40154%20(7)2.JPG",
                    "https://vitsonimg.co.kr/images/products/40154%20(12).jpg"
                ],
                "hasOption": "false",
                "productHref": "https://www.ds1008.com/goods/goods_view.php?goodsNo=14532",
                "sellerID": "14"
            },
            {
                "productName": "다운라이트 LED/6인치 20W 전구/IN-40155",
                "productPrice": "6000",
                "productImage": "https://vitsonimg.co.kr/images/products/401552.jpg",
                "productDetail": [
                    "http://ds1008.hgodo.com/gd5replace/zlem2432/data/editor/goods/230914/ilsin_130444.jpg",
                    "http://ds1008.hgodo.com/gd5replace/zlem2432/data/editor/goods/191017/281770615111089c4e577ae7f12173b2_153336.jpg",
                    "https://vitsonimg.co.kr/images/products/401552.jpg",
                    "https://vitsonimg.co.kr/images/products/40155%20(2)1.JPG",
                    "https://vitsonimg.co.kr/images/products/40155%20(3)1.JPG",
                    "https://vitsonimg.co.kr/images/products/40155%20(4)1.JPG",
                    "https://vitsonimg.co.kr/images/products/40155%20(5)1.JPG",
                    "https://vitsonimg.co.kr/images/products/40155%20(6)1.JPG",
                    "https://vitsonimg.co.kr/images/products/40155%20(11).jpg"
                ],
                "hasOption": "false",
                "productHref": "https://www.ds1008.com/goods/goods_view.php?goodsNo=14533",
                "sellerID": "14"
            },
            {
                "productName": "팬던트/ 1등 펌킨 P/D ▶램프별도◀/IN-33927",
                "productPrice": "10400",
                "productImage": "https://vitsonimg.co.kr/images/productsNew/33/33927%20(9).jpg",
                "productDetail": [
                    "http://ds1008.hgodo.com/gd5replace/zlem2432/data/editor/goods/230914/ilsin_130444.jpg",
                    "https://vitsonimg.co.kr/images/productsNew/33/33927.jpg",
                    "https://vitsonimg.co.kr/images/productsNew/33/33927%20(3).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/33/33927%20(4).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/33/33927%20(5).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/33/33927%20(7).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/33/33927%20(10).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/33/33927%20(12).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/33/33927%20(13).jpg",
                    "https://vitsonimg.co.kr/images/productsNew/33/33927%20(14).jpg",
                    "https://vitsonimg.co.kr/images/products/33927_01.jpg",
                    "https://vitsonimg.co.kr/images/productsNew/33/1-2등%20인증서%20(1).jpg"
                ],
                "hasOption": "false",
                "productHref": "https://www.ds1008.com/goods/goods_view.php?goodsNo=1000013053",
                "sellerID": "14"
            },
            {
                "productName": "건식/유백LED 심플 욕실등/20W/60414",
                "productPrice": "6000",
                "productImage": "https://cdn-pro-web-250-123.cdn-nhncommerce.com/zlem2432_godomall_com/data/goods/19/12/50/1000011126/1000011126_detail_070.jpg",
                "productDetail": [
                    "http://ds1008.hgodo.com/gd5replace/zlem2432/data/editor/goods/210910/da2_142644.jpg"
                ],
                "hasOption": "false",
                "productHref": "https://www.ds1008.com/goods/goods_view.php?goodsNo=1000011126",
                "sellerID": "14"
            }
        ];
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
                url: "/api/product/mining",
                type: "POST",
                dataType: "JSON",
                data: {
                    rememberToken: rememberToken,
                    vendorID: vendorID,
                    listURL: listURL
                },
                success: function(response) {
                    if (response.status) {
                        const products = response.return;
                        updateMinewingResult(products);
                        closePopup();
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
                    closePopup();
                    const numDups = productHrefs.length - response.length;
                    popupLoader(0, '"총 ' + numDups +
                        '개의 중복 상품을 검열했어요.<br>각 상품의 상세 정보를 스크래핑해올게요."');
                    const vendorID = $('input[name="sellers"]:checked').val();
                    runScrape(response, vendorID);
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
                    closePopup();
                    if (response.status) {
                        const products = response.return;
                        popupLoader(1, '"상품명을 가공 중이에요."');
                        runManufacture(products);
                    } else {
                        swalError(response.return);
                    }
                },
                error: function(response) {
                    console.log(response);
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
                swalSuccess(response.return);
            } else {
                swalError(response.return);
            }
        }
    </script>
@endsection
