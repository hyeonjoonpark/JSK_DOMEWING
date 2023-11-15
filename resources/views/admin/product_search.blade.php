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
                                <input type="text" class="form-control" id="productUrl" placeholder="상품 키워드 기입해주세요." />
                            </div>
                            <div class="col-auto">
                                <button class="btn btn-primary" onclick="collectInit();">상품 검색</button>
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
                        <button class="btn btn-warning" onclick="productBulkCollect();">상품 다중 수집</button>
                    </div>
                    <div id="collectResult">
                        <table id="productTable" class="datatable-init-export nowrap table" data-export-title="Export"
                            data-order='[[2, "asc"]]'>
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

                                <!-- 데이터는 JavaScript 코드로 동적으로 추가됩니다 -->




































































































                                <tr class="odd">
                                    <td class="dtr-control" tabindex="0">
                                        <div class="custom-control custom-control-sm custom-checkbox notext"><input
                                                type="checkbox" class="custom-control-input" id="uid116"
                                                value="1216빨아쓰는슬림규조토발매트욕실주방싱크대매트러그논슬립발닦개물기흡수, 6500, 도매매, https://cdn1.domeggook.com//upload/item/2022/03/31/1648698231E69623E78F80B724175699/1648698231E69623E78F80B724175699_img_330?hash=031566ac3401f48465dd6bb7bad9d17f, http://domeggook.com//21146576?from=lstGen"><label
                                                class="custom-control-label" for="uid116"></label></div>
                                    </td>
                                    <td><a href="http://domeggook.com//21146576?from=lstGen" target="_blank"><img
                                                src="https://cdn1.domeggook.com//upload/item/2022/03/31/1648698231E69623E78F80B724175699/1648698231E69623E78F80B724175699_img_330?hash=031566ac3401f48465dd6bb7bad9d17f"
                                                alt="Product" style="width:120px; height:120px;"></a></td>
                                    <td class="sorting_1"><a href="http://domeggook.com//21146576?from=lstGen"
                                            target="_blank"
                                            title="1216빨아쓰는슬림규조토발매트욕실주방싱크대매트러그논슬립발닦개물기흡수">1216빨아쓰는슬림규조토발매트욕실주방싱크대매트러그논슬립...</a>
                                    </td>
                                    <td>6500</td>
                                    <td>도매매</td>
                                    <td><button class="btn btn-primary"
                                            onclick="registerProduct('1216빨아쓰는슬림규조토발매트욕실주방싱크대매트러그논슬립발닦개물기흡수', '6500', 'https://cdn1.domeggook.com//upload/item/2022/03/31/1648698231E69623E78F80B724175699/1648698231E69623E78F80B724175699_img_330?hash=031566ac3401f48465dd6bb7bad9d17f', '도매매', 'http://domeggook.com//21146576?from=lstGen')">상품
                                            등록</button></td>
                                </tr>
                                <tr class="even">
                                    <td class="dtr-control" tabindex="0">
                                        <div class="custom-control custom-control-sm custom-checkbox notext"><input
                                                type="checkbox" class="custom-control-input" id="uid204"
                                                value="2P다용도팬더싱크대문걸이고리주방수납걸이, 890, 도매매, https://cdn1.domeggook.com//upload/item/2022/04/21/16505271660B6E95B3DF11DEC4ED7CB1/16505271660B6E95B3DF11DEC4ED7CB1_stt_330.png?hash=0529da27e9a62fdfd4f9e4afb7657a0f, http://domeggook.com//22152716?from=lstGen"><label
                                                class="custom-control-label" for="uid204"></label></div>
                                    </td>
                                    <td><a href="http://domeggook.com//22152716?from=lstGen" target="_blank"><img
                                                src="https://cdn1.domeggook.com//upload/item/2022/04/21/16505271660B6E95B3DF11DEC4ED7CB1/16505271660B6E95B3DF11DEC4ED7CB1_stt_330.png?hash=0529da27e9a62fdfd4f9e4afb7657a0f"
                                                alt="Product" style="width:120px; height:120px;"></a></td>
                                    <td class="sorting_1"><a href="http://domeggook.com//22152716?from=lstGen"
                                            target="_blank" title="2P다용도팬더싱크대문걸이고리주방수납걸이">2P다용도팬더싱크대문걸이고리주방수납걸이</a></td>
                                    <td>890</td>
                                    <td>도매매</td>
                                    <td><button class="btn btn-primary"
                                            onclick="registerProduct('2P다용도팬더싱크대문걸이고리주방수납걸이', '890', 'https://cdn1.domeggook.com//upload/item/2022/04/21/16505271660B6E95B3DF11DEC4ED7CB1/16505271660B6E95B3DF11DEC4ED7CB1_stt_330.png?hash=0529da27e9a62fdfd4f9e4afb7657a0f', '도매매', 'http://domeggook.com//22152716?from=lstGen')">상품
                                            등록</button></td>
                                </tr>
                                <tr class="odd">
                                    <td class="dtr-control" tabindex="0">
                                        <div class="custom-control custom-control-sm custom-checkbox notext"><input
                                                type="checkbox" class="custom-control-input" id="uid42"
                                                value="2p싱크대개수통홀컵, 880, 도매토피아, https://dmtusr.vipweb.kr/goods_img/1/2008/10/1855/1855_0_d912e463b1adefe3b06b3cc9e6182d3e5994list1.jpg, https://dometopia.com/goods/view?no=1855&amp;code="><label
                                                class="custom-control-label" for="uid42"></label></div>
                                    </td>
                                    <td><a href="https://dometopia.com/goods/view?no=1855&amp;code=" target="_blank"><img
                                                src="https://dmtusr.vipweb.kr/goods_img/1/2008/10/1855/1855_0_d912e463b1adefe3b06b3cc9e6182d3e5994list1.jpg"
                                                alt="Product" style="width:120px; height:120px;"></a></td>
                                    <td class="sorting_1"><a href="https://dometopia.com/goods/view?no=1855&amp;code="
                                            target="_blank" title="2p싱크대개수통홀컵">2p싱크대개수통홀컵</a></td>
                                    <td>880</td>
                                    <td>도매토피아</td>
                                    <td><button class="btn btn-primary"
                                            onclick="registerProduct('2p싱크대개수통홀컵', '880', 'https://dmtusr.vipweb.kr/goods_img/1/2008/10/1855/1855_0_d912e463b1adefe3b06b3cc9e6182d3e5994list1.jpg', '도매토피아', 'https://dometopia.com/goods/view?no=1855&amp;code=')">상품
                                            등록</button></td>
                                </tr>
                                <tr class="even">
                                    <td class="dtr-control" tabindex="0">
                                        <div class="custom-control custom-control-sm custom-checkbox notext"><input
                                                type="checkbox" class="custom-control-input" id="uid170"
                                                value="2단소형철제주방싱크대수세미보관부착식선반BD1750, 1450, 도매매, https://cdn1.domeggook.com//upload/item/2022/10/27/166684711573418A1406154AD271305B/166684711573418A1406154AD271305B_stt_330.png?hash=3e24ed7fc5636483d5d8a534c1e552f7, http://domeggook.com//29813705?from=lstGen"><label
                                                class="custom-control-label" for="uid170"></label></div>
                                    </td>
                                    <td><a href="http://domeggook.com//29813705?from=lstGen" target="_blank"><img
                                                src="https://cdn1.domeggook.com//upload/item/2022/10/27/166684711573418A1406154AD271305B/166684711573418A1406154AD271305B_stt_330.png?hash=3e24ed7fc5636483d5d8a534c1e552f7"
                                                alt="Product" style="width:120px; height:120px;"></a></td>
                                    <td class="sorting_1"><a href="http://domeggook.com//29813705?from=lstGen"
                                            target="_blank"
                                            title="2단소형철제주방싱크대수세미보관부착식선반BD1750">2단소형철제주방싱크대수세미보관부착식선반BD1750</a></td>
                                    <td>1450</td>
                                    <td>도매매</td>
                                    <td><button class="btn btn-primary"
                                            onclick="registerProduct('2단소형철제주방싱크대수세미보관부착식선반BD1750', '1450', 'https://cdn1.domeggook.com//upload/item/2022/10/27/166684711573418A1406154AD271305B/166684711573418A1406154AD271305B_stt_330.png?hash=3e24ed7fc5636483d5d8a534c1e552f7', '도매매', 'http://domeggook.com//29813705?from=lstGen')">상품
                                            등록</button></td>
                                </tr>
                                <tr class="odd">
                                    <td class="dtr-control" tabindex="0">
                                        <div class="custom-control custom-control-sm custom-checkbox notext"><input
                                                type="checkbox" class="custom-control-input" id="uid126"
                                                value="2단싱크대하부선반/싱크인선반/싱크대정리, 7500, 도매매, https://cdn1.domeggook.com//upload/item/2020/06/22/159278516545735993DD00F275C49B3F/159278516545735993DD00F275C49B3F_stt_330.png?hash=76c45030f691c07f46fc266f3a14aa6c, http://domeggook.com//10366815?from=lstGen"><label
                                                class="custom-control-label" for="uid126"></label></div>
                                    </td>
                                    <td><a href="http://domeggook.com//10366815?from=lstGen" target="_blank"><img
                                                src="https://cdn1.domeggook.com//upload/item/2020/06/22/159278516545735993DD00F275C49B3F/159278516545735993DD00F275C49B3F_stt_330.png?hash=76c45030f691c07f46fc266f3a14aa6c"
                                                alt="Product" style="width:120px; height:120px;"></a></td>
                                    <td class="sorting_1"><a href="http://domeggook.com//10366815?from=lstGen"
                                            target="_blank" title="2단싱크대하부선반/싱크인선반/싱크대정리">2단싱크대하부선반/싱크인선반/싱크대정리</a></td>
                                    <td>7500</td>
                                    <td>도매매</td>
                                    <td><button class="btn btn-primary"
                                            onclick="registerProduct('2단싱크대하부선반/싱크인선반/싱크대정리', '7500', 'https://cdn1.domeggook.com//upload/item/2020/06/22/159278516545735993DD00F275C49B3F/159278516545735993DD00F275C49B3F_stt_330.png?hash=76c45030f691c07f46fc266f3a14aa6c', '도매매', 'http://domeggook.com//10366815?from=lstGen')">상품
                                            등록</button></td>
                                </tr>
                                <tr class="even">
                                    <td class="dtr-control" tabindex="0">
                                        <div class="custom-control custom-control-sm custom-checkbox notext"><input
                                                type="checkbox" class="custom-control-input" id="uid87"
                                                value="304스텐와이어주방세척솔(25.5cm), 9240, 도매토피아, https://dmtusr.vipweb.kr/goods_img/1/2023/05/175062/O1CN0_581list1.jpg, https://dometopia.com/goods/view?no=175062&amp;code="><label
                                                class="custom-control-label" for="uid87"></label></div>
                                    </td>
                                    <td><a href="https://dometopia.com/goods/view?no=175062&amp;code="
                                            target="_blank"><img
                                                src="https://dmtusr.vipweb.kr/goods_img/1/2023/05/175062/O1CN0_581list1.jpg"
                                                alt="Product" style="width:120px; height:120px;"></a></td>
                                    <td class="sorting_1"><a href="https://dometopia.com/goods/view?no=175062&amp;code="
                                            target="_blank" title="304스텐와이어주방세척솔(25.5cm)">304스텐와이어주방세척솔(25.5cm)</a></td>
                                    <td>9240</td>
                                    <td>도매토피아</td>
                                    <td><button class="btn btn-primary"
                                            onclick="registerProduct('304스텐와이어주방세척솔(25.5cm)', '9240', 'https://dmtusr.vipweb.kr/goods_img/1/2023/05/175062/O1CN0_581list1.jpg', '도매토피아', 'https://dometopia.com/goods/view?no=175062&amp;code=')">상품
                                            등록</button></td>
                                </tr>
                                <tr class="odd">
                                    <td class="dtr-control" tabindex="0">
                                        <div class="custom-control custom-control-sm custom-checkbox notext"><input
                                                type="checkbox" class="custom-control-input" id="uid88"
                                                value="304스텐와이어주방세척솔(26cm), 11220, 도매토피아, https://dmtusr.vipweb.kr/goods_img/1/2023/05/175060/O1CN0_884list1.jpg, https://dometopia.com/goods/view?no=175060&amp;code="><label
                                                class="custom-control-label" for="uid88"></label></div>
                                    </td>
                                    <td><a href="https://dometopia.com/goods/view?no=175060&amp;code="
                                            target="_blank"><img
                                                src="https://dmtusr.vipweb.kr/goods_img/1/2023/05/175060/O1CN0_884list1.jpg"
                                                alt="Product" style="width:120px; height:120px;"></a></td>
                                    <td class="sorting_1"><a href="https://dometopia.com/goods/view?no=175060&amp;code="
                                            target="_blank" title="304스텐와이어주방세척솔(26cm)">304스텐와이어주방세척솔(26cm)</a></td>
                                    <td>11220</td>
                                    <td>도매토피아</td>
                                    <td><button class="btn btn-primary"
                                            onclick="registerProduct('304스텐와이어주방세척솔(26cm)', '11220', 'https://dmtusr.vipweb.kr/goods_img/1/2023/05/175060/O1CN0_884list1.jpg', '도매토피아', 'https://dometopia.com/goods/view?no=175060&amp;code=')">상품
                                            등록</button></td>
                                </tr>
                                <tr class="even">
                                    <td class="dtr-control" tabindex="0">
                                        <div class="custom-control custom-control-sm custom-checkbox notext"><input
                                                type="checkbox" class="custom-control-input" id="uid182"
                                                value="[30매]물빠짐음식물쓰레기봉투/타공/싱크대/봉지, 1800, 도매매, https://cdn1.domeggook.com//upload/item/2019/08/05/15649697637D0EF630AD6FB0AFEF3405/15649697637D0EF630AD6FB0AFEF3405_stt_330.png?hash=37377fa2b531187b093db55ffe8325c4, http://domeggook.com//9001372?from=lstGen"><label
                                                class="custom-control-label" for="uid182"></label></div>
                                    </td>
                                    <td><a href="http://domeggook.com//9001372?from=lstGen" target="_blank"><img
                                                src="https://cdn1.domeggook.com//upload/item/2019/08/05/15649697637D0EF630AD6FB0AFEF3405/15649697637D0EF630AD6FB0AFEF3405_stt_330.png?hash=37377fa2b531187b093db55ffe8325c4"
                                                alt="Product" style="width:120px; height:120px;"></a></td>
                                    <td class="sorting_1"><a href="http://domeggook.com//9001372?from=lstGen"
                                            target="_blank"
                                            title="[30매]물빠짐음식물쓰레기봉투/타공/싱크대/봉지">[30매]물빠짐음식물쓰레기봉투/타공/싱크대/봉지</a></td>
                                    <td>1800</td>
                                    <td>도매매</td>
                                    <td><button class="btn btn-primary"
                                            onclick="registerProduct('[30매]물빠짐음식물쓰레기봉투/타공/싱크대/봉지', '1800', 'https://cdn1.domeggook.com//upload/item/2019/08/05/15649697637D0EF630AD6FB0AFEF3405/15649697637D0EF630AD6FB0AFEF3405_stt_330.png?hash=37377fa2b531187b093db55ffe8325c4', '도매매', 'http://domeggook.com//9001372?from=lstGen')">상품
                                            등록</button></td>
                                </tr>
                                <tr class="odd">
                                    <td class="dtr-control" tabindex="0">
                                        <div class="custom-control custom-control-sm custom-checkbox notext"><input
                                                type="checkbox" class="custom-control-input" id="uid17"
                                                value="[LVT] 매직커버/ 싱크대 배수구 커버/싱크대 배수구 마개/배수구 마개/속마개/속뚜껑/음식물처리 , 4500, 바보나라, http://babonara.co.kr/shop/data/goods/1437666861617s0.jpg, http://babonara.co.kr/shop/goods/goods_view.php?goodsno=43&amp;category=002002"><label
                                                class="custom-control-label" for="uid17"></label></div>
                                    </td>
                                    <td><a href="http://babonara.co.kr/shop/goods/goods_view.php?goodsno=43&amp;category=002002"
                                            target="_blank"><img
                                                src="http://babonara.co.kr/shop/data/goods/1437666861617s0.jpg"
                                                alt="Product" style="width:120px; height:120px;"></a></td>
                                    <td class="sorting_1"><a
                                            href="http://babonara.co.kr/shop/goods/goods_view.php?goodsno=43&amp;category=002002"
                                            target="_blank"
                                            title="[LVT] 매직커버/ 싱크대 배수구 커버/싱크대 배수구 마개/배수구 마개/속마개/속뚜껑/음식물처리 ">[LVT] 매직커버/ 싱크대
                                            배수구 커버/싱크대 배수구...</a></td>
                                    <td>4500</td>
                                    <td>바보나라</td>
                                    <td><button class="btn btn-primary"
                                            onclick="registerProduct('[LVT] 매직커버/ 싱크대 배수구 커버/싱크대 배수구 마개/배수구 마개/속마개/속뚜껑/음식물처리 ', '4500', 'http://babonara.co.kr/shop/data/goods/1437666861617s0.jpg', '바보나라', 'http://babonara.co.kr/shop/goods/goods_view.php?goodsno=43&amp;category=002002')">상품
                                            등록</button></td>
                                </tr>
                                <tr class="even">
                                    <td class="dtr-control" tabindex="0">
                                        <div class="custom-control custom-control-sm custom-checkbox notext"><input
                                                type="checkbox" class="custom-control-input" id="uid7"
                                                value="[LVT] 싱크대 배수구 겉뚜껑/악취방지용/대형배수구/배수구덮개/배수구캡/배수트랩/싱크배수구/주방배수구/배수구속뚜껑/싱크대속뚜껑, 2500, 바보나라, http://babonara.co.kr/shop/data/goods/1439274513315s0.jpg, http://babonara.co.kr/shop/goods/goods_view.php?goodsno=63&amp;category=002002"><label
                                                class="custom-control-label" for="uid7"></label></div>
                                    </td>
                                    <td><a href="http://babonara.co.kr/shop/goods/goods_view.php?goodsno=63&amp;category=002002"
                                            target="_blank"><img
                                                src="http://babonara.co.kr/shop/data/goods/1439274513315s0.jpg"
                                                alt="Product" style="width:120px; height:120px;"></a></td>
                                    <td class="sorting_1"><a
                                            href="http://babonara.co.kr/shop/goods/goods_view.php?goodsno=63&amp;category=002002"
                                            target="_blank"
                                            title="[LVT] 싱크대 배수구 겉뚜껑/악취방지용/대형배수구/배수구덮개/배수구캡/배수트랩/싱크배수구/주방배수구/배수구속뚜껑/싱크대속뚜껑">[LVT]
                                            싱크대 배수구 겉뚜껑/악취방지용/대형배수구/...</a></td>
                                    <td>2500</td>
                                    <td>바보나라</td>
                                    <td><button class="btn btn-primary"
                                            onclick="registerProduct('[LVT] 싱크대 배수구 겉뚜껑/악취방지용/대형배수구/배수구덮개/배수구캡/배수트랩/싱크배수구/주방배수구/배수구속뚜껑/싱크대속뚜껑', '2500', 'http://babonara.co.kr/shop/data/goods/1439274513315s0.jpg', '바보나라', 'http://babonara.co.kr/shop/goods/goods_view.php?goodsno=63&amp;category=002002')">상품
                                            등록</button></td>
                                </tr>
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
                            <button type="submit" class="btn btn-lg btn-primary" onclick="productBulkCollect();">가공
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
            var rows = dataTable.rows({
                page: 'current'
            }).nodes();
            $('input[type="checkbox"]', rows).prop('checked', this.checked);
        });

        function updateDataTable(products) {
            var dataTable = $('#productTable').DataTable();

            dataTable.clear().draw();

            for (let i = 0; i < products.length; i++) {
                const name = products[i].name;
                const price = products[i].price;
                const platform = products[i].platform;
                const image = products[i].image;
                const href = products[i].href;

                const checkbox =
                    '<div class="custom-control custom-control-sm custom-checkbox notext"><input type="checkbox" class="custom-control-input" id="uid' +
                    i + '" value="' + name + ', ' + price + ', ' + platform + ', ' + image + ', ' + href +
                    '"><label class="custom-control-label" for="uid' + i + '"></label></div>';
                const imageHtml = '<a href="' + href + '" target="_blank"><img src="' + image +
                    '" alt="Product" style="width:120px; height:120px;"></a>';
                const nameHtml = '<a href="' + href + '" target="_blank" title="' + name + '">' + truncateText(name, 30) +
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

        function truncateText(text, maxLength) {
            if (text.length > maxLength) {
                return text.slice(0, maxLength) + '...';
            }
            return text;
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

        function productBulkCollect() {
            // 선택된 체크박스 요소를 담을 배열 초기화
            var selectedCheckboxes = [];

            // DataTables 테이블의 모든 체크박스 요소 선택
            const products = $('#productTable tbody input[type="checkbox"]:checked').each(function() {
                // 선택된 체크박스의 값을 가져와 배열에 추가
                const productValue = $(this).val();
                selectedCheckboxes.push(productValue.defaultValue);
            });
            const productKeywords = $('#pBCDKeywords').val();
            const categoryId = $('#pBCDCategoryId').val();

            console.log(products);

            $('#productBulkCollectWizard').modal('show');
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
    </script>
@endsection
