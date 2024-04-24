@extends('partner.layouts.main')
@section('title')
    주문내역 확인
@endsection
@section('subtitle')
    <p>주문내역을 확인하는 페이지입니다.</p>
@endsection
@section('content')
    <div class="row g-gs">
        <div class="col">
            <div class="card card-bordered">
                <div class="card-inner">

                </div>
            </div>
        </div>
    </div>
    <div class="row g-gs">
        <div class="col">
            <div class="card card-bordered">
                <div class="card-inner">

                </div>
            </div>
        </div>
    </div>
    <div class="row g-gs">
        <div class="col">
            <div class="card card-bordered">
                <div class="card-inner">

                </div>
            </div>
        </div>
    </div>
    <button class="btn btn-primary">
        검색
    </button>
    <button class="btn btn-primary">
        검색초기화
    </button>
    <div class="row g-gs">
        <div class="col">
            <div class="card card-bordered">
                <div class="card-inner">
                    <div class="nk-tb-list">
                        <div class="nk-tb-item nk-tb-head">
                            <div class="nk-tb-col nk-tb-col-check">
                                <div class="custom-control custom-control-sm custom-checkbox notext">
                                    <input type="checkbox" class="custom-control-input" id="pid">
                                    <label class="custom-control-label" for="pid"></label>
                                </div>
                            </div>
                            <div class="nk-tb-col"><span>상품주문번호</span></div>
                            <div class="nk-tb-col"><span>주문번호</span></div>
                            <div class="nk-tb-col"><span>주문일시</span></div>
                            <div class="nk-tb-col"><span>주문상태</span></div>
                            <div class="nk-tb-col"><span>상품코드</span></div>
                            <div class="nk-tb-col"><span>상품명</span></div>
                            <div class="nk-tb-col"><span>수량</span></div>
                            <div class="nk-tb-col"><span>구매자명</span></div>
                            <div class="nk-tb-col"><span>구매자ID</span></div>
                            <div class="nk-tb-col"><span>수취인명</span></div>
                        </div><!-- .nk-tb-item -->
                        <div class="nk-tb-item">
                            <div class="nk-tb-col nk-tb-col-check">
                                <div class="custom-control custom-control-sm custom-checkbox notext">
                                    <input type="checkbox" class="custom-control-input" id="pid1">
                                    <label class="custom-control-label" for="pid1"></label>
                                </div>
                            </div>
                            <div class="nk-tb-col">
                                <span class="tb-sub">상품주문번호</span>
                            </div>
                            <div class="nk-tb-col">
                                <span class="tb-sub">고유주문번호</span>
                            </div>
                            <div class="nk-tb-col">
                                <span class="tb-sub">2024-04-24</span>
                            </div>
                            <div class="nk-tb-col">
                                <span class="tb-sub">입금안하냐?</span>
                            </div>
                            <div class="nk-tb-col tb-col-md">
                                <span class="tb-sub">우리상품코드</span>
                            </div>
                            <div class="nk-tb-col tb-col-md">
                                <span class="tb-sub">복분자</span>
                            </div>
                            <div class="nk-tb-col tb-col-md">
                                <span class="tb-sub">100</span>
                            </div>
                            <div class="nk-tb-col tb-col-md">
                                <span class="tb-sub">김상진</span>
                            </div>
                            <div class="nk-tb-col tb-col-md">
                                <span class="tb-sub">환타스틱</span>
                            </div>
                            <div class="nk-tb-col tb-col-md">
                                <span class="tb-sub">사무실</span>
                            </div>
                        </div><!-- .nk-tb-item -->

                        <div class="nk-tb-item">
                            <div class="nk-tb-col nk-tb-col-check">
                                <div class="custom-control custom-control-sm custom-checkbox notext">
                                    <input type="checkbox" class="custom-control-input" id="pid8">
                                    <label class="custom-control-label" for="pid8"></label>
                                </div>
                            </div>
                            <div class="nk-tb-col tb-col-sm">
                                <span class="tb-product">
                                    <img src="./images/product/h.png" alt="" class="thumb">
                                    <span class="title">Wireless Waterproof Speaker</span>
                                </span>
                            </div>
                            <div class="nk-tb-col">
                                <span class="tb-sub">UY3756</span>
                            </div>
                            <div class="nk-tb-col">
                                <span class="tb-lead">$ 59.00</span>
                            </div>
                            <div class="nk-tb-col">
                                <span class="tb-sub">37</span>
                            </div>
                            <div class="nk-tb-col tb-col-md">
                                <span class="tb-sub">Speaker, Gadgets</span>
                            </div>
                            <div class="nk-tb-col tb-col-md">
                                <div class="asterisk tb-asterisk">
                                    <a href="#"><em class="asterisk-off icon ni ni-star"></em><em
                                            class="asterisk-on icon ni ni-star-fill"></em></a>
                                </div>
                            </div>
                            <div class="nk-tb-col nk-tb-col-tools">
                                <ul class="nk-tb-actions gx-1 my-n1">
                                    <li class="me-n1">
                                        <div class="dropdown">
                                            <a href="#" class="dropdown-toggle btn btn-icon btn-trigger"
                                                data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <ul class="link-list-opt no-bdr">
                                                    <li><a href="#"><em class="icon ni ni-edit"></em><span>Edit
                                                                Product</span></a></li>
                                                    <li><a href="#"><em class="icon ni ni-eye"></em><span>View
                                                                Product</span></a></li>
                                                    <li><a href="#"><em
                                                                class="icon ni ni-activity-round"></em><span>Product
                                                                Orders</span></a></li>
                                                    <li><a href="#"><em class="icon ni ni-trash"></em><span>Remove
                                                                Product</span></a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div><!-- .nk-tb-item -->
                        <div class="nk-tb-item">
                            <div class="nk-tb-col nk-tb-col-check">
                                <div class="custom-control custom-control-sm custom-checkbox notext">
                                    <input type="checkbox" class="custom-control-input" id="pid9">
                                    <label class="custom-control-label" for="pid9"></label>
                                </div>
                            </div>
                            <div class="nk-tb-col tb-col-sm">
                                <span class="tb-product">
                                    <img src="./images/product/j.png" alt="" class="thumb">
                                    <span class="title">AliExpress Fitness Trackers</span>
                                </span>
                            </div>
                            <div class="nk-tb-col">
                                <span class="tb-sub">UY3758</span>
                            </div>
                            <div class="nk-tb-col">
                                <span class="tb-lead">$ 35.99</span>
                            </div>
                            <div class="nk-tb-col">
                                <span class="tb-sub">145</span>
                            </div>
                            <div class="nk-tb-col tb-col-md">
                                <span class="tb-sub">Fitbit, Tracker</span>
                            </div>
                            <div class="nk-tb-col tb-col-md">
                                <div class="asterisk tb-asterisk">
                                    <a href="#"><em class="asterisk-off icon ni ni-star"></em><em
                                            class="asterisk-on icon ni ni-star-fill"></em></a>
                                </div>
                            </div>
                            <div class="nk-tb-col nk-tb-col-tools">
                                <ul class="nk-tb-actions gx-1 my-n1">
                                    <li class="me-n1">
                                        <div class="dropdown">
                                            <a href="#" class="dropdown-toggle btn btn-icon btn-trigger"
                                                data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <ul class="link-list-opt no-bdr">
                                                    <li><a href="#"><em class="icon ni ni-edit"></em><span>Edit
                                                                Product</span></a></li>
                                                    <li><a href="#"><em class="icon ni ni-eye"></em><span>View
                                                                Product</span></a></li>
                                                    <li><a href="#"><em
                                                                class="icon ni ni-activity-round"></em><span>Product
                                                                Orders</span></a></li>
                                                    <li><a href="#"><em class="icon ni ni-trash"></em><span>Remove
                                                                Product</span></a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div><!-- .nk-tb-item -->
                        <div class="nk-tb-item">
                            <div class="nk-tb-col nk-tb-col-check">
                                <div class="custom-control custom-control-sm custom-checkbox notext">
                                    <input type="checkbox" class="custom-control-input" id="pid10">
                                    <label class="custom-control-label" for="pid10"></label>
                                </div>
                            </div>
                            <div class="nk-tb-col tb-col-sm">
                                <span class="tb-product">
                                    <img src="./images/product/i.png" alt="" class="thumb">
                                    <span class="title">Pool Party Drink Holder</span>
                                </span>
                            </div>
                            <div class="nk-tb-col">
                                <span class="tb-sub">UY3757</span>
                            </div>
                            <div class="nk-tb-col">
                                <span class="tb-lead">$ 9.49</span>
                            </div>
                            <div class="nk-tb-col">
                                <span class="tb-sub">73</span>
                            </div>
                            <div class="nk-tb-col tb-col-md">
                                <span class="tb-sub">Men, Holder</span>
                            </div>
                            <div class="nk-tb-col tb-col-md">
                                <div class="asterisk tb-asterisk">
                                    <a href="#"><em class="asterisk-off icon ni ni-star"></em><em
                                            class="asterisk-on icon ni ni-star-fill"></em></a>
                                </div>
                            </div>
                            <div class="nk-tb-col nk-tb-col-tools">
                                <ul class="nk-tb-actions gx-1 my-n1">
                                    <li class="me-n1">
                                        <div class="dropdown">
                                            <a href="#" class="dropdown-toggle btn btn-icon btn-trigger"
                                                data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <ul class="link-list-opt no-bdr">
                                                    <li><a href="#"><em class="icon ni ni-edit"></em><span>Edit
                                                                Product</span></a></li>
                                                    <li><a href="#"><em class="icon ni ni-eye"></em><span>View
                                                                Product</span></a></li>
                                                    <li><a href="#"><em
                                                                class="icon ni ni-activity-round"></em><span>Product
                                                                Orders</span></a></li>
                                                    <li><a href="#"><em class="icon ni ni-trash"></em><span>Remove
                                                                Product</span></a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div><!-- .nk-tb-item -->
                    </div><!-- .nk-tb-list -->
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script></script>
@endsection
