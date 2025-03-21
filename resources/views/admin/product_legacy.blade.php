@extends('layouts.main')
@section('title')
    상품윙
@endsection
@section('subtitle')
    <p>
        마인윙으로부터 수집된 상품들을 관리합니다.
    </p>
@endsection
@section('content')
    <div class="row g-gs mb-5">
        <div class="col">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">통합 검색</h6>
                    <p>테이블의 모든 컬럼 중 원하는 키워드를 검색하세요.</p>
                    <div class="form-group">
                        <label for="" class="form-label">상품 검색</label>
                        <form class="d-flex text-nowrap" method="POST">
                            @csrf
                            <input type="text" class="form-control" placeholder="검색 키워드를 기입해주세요" name="searchKeyword"
                                value="{{ $searchKeyword }}">
                            <button type="submit" class="btn btn-primary">검색</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-gs">
        <div class="col">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">상품윙 테이블</h6>
                    <p>검색된 상품이 총 {{ number_format(count($products), 0) }}건입니다. 상품 검색 결과는 최대 1,000건으로 제한됩니다.</p>
                    <div class="table-responsive">
                        <table class="table text-nowrap align-middle">
                            <thead>
                                <tr>
                                    <th scope="col"><input type="checkbox" onclick="selectAll(this);"></th>
                                    <th scope="col">대표 이미지</th>
                                    <th scope="col">상품명</th>
                                    <th scope="col">코드</th>
                                    <th scope="col">가격</th>
                                    <th scope="col">원청사</th>
                                    <th scope="col">수집일자</th>
                                    <th scope="col">ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($products as $product)
                                    <tr>
                                        <td scope="row"><input type="checkbox" name="selectedProducts"
                                                value="{{ $product->productCode }}"></td>
                                        <td><a href="{{ $product->productHref }}" target="_blank"><img
                                                    src="{{ $product->productImage }}" alt="상품 대표 이미지" width=100
                                                    height=100></a></td>
                                        <td><a href="{{ $product->productHref }}"
                                                target="_blank">{{ $product->productName }}</a></td>
                                        <td><a href="{{ $product->productHref }}"
                                                target="_blank">{{ $product->productCode }}</a></td>
                                        <td><a href="{{ $product->productHref }}"
                                                target="_blank">{{ number_format($product->productPrice, 0) }}원</a>
                                        </td>
                                        <td><a href="{{ $product->productHref }}" target="_blank">{{ $product->name }}</a>
                                        </td>
                                        <td>{{ date('Y-m-d', strtotime($product->createdAt)) }}</td>
                                        <td>레거시 상품</td>
                                    </tr>
                                @endforeach
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
        var rememberToken = '{{ Auth::guard('user')->user()->remember_token }}';
        $(document).on('click', '#selectAll', function() {
            const isChecked = $(this).is(':checked');
            $('input[name="selectedProducts"]').prop('checked', isChecked);
        });
    </script>
@endsection
