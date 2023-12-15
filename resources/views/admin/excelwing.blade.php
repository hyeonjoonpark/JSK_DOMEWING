@extends('layouts.main')
@section('title')
    엑셀윙
@endsection
@section('subtitle')
    <p>
        엑셀윙은 B2B 업체별로 요구되는 대량 상품 등록을 위한 엑셀 양식에 맞추어 상품 데이터를 재구성하고 있습니다.
    </p>
@endsection
@section('content')
    <div class="row g-gs">
        <div class="col">
            <p>총 <b class="font-weight-bold">{{ number_format(count($products), 0) }}</b>개의 상품이 준비됐습니다.</p>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col"><input type="checkbox"></th>
                        <th scope="col">상품 대표 이미지</th>
                        <th scope="col">상품명</th>
                        <th scope="col">가격</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $product)
                        <tr>
                            <td><input type="checkbox"></td>
                            <td><a href="{{ $product->productHref }}" target="_blank"><img
                                        src="{{ $product->productImage }}" alt="상품 대표 이미지" width="100"
                                        height="100"></a>
                            </td>
                            <td><a href="{{ $product->productHref }}" target="_blank">{{ $product->productName }}</a></td>
                            <td>{{ number_format($product->productPrice, 0) }}원</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="modal fade" id="selectCategory" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">엑셀윙에 오신 것을 환영합니다.</h5>
                </div>
                <div class="modal-body">
                    <img src="{{ asset('media/Asset_Notif_Success.svg') }}" alt="엑셀윙 헤더 이미지" class="w-100">
                    <h6 class="mt-3">
                        상품 데이터를 불러오기 위해 원하는 카테고리를 선택해주세요.
                    </h6>
                    <div class="form-group">
                        <label for="categoryID">카테고리</label>
                        <div class="form-control-wrap">
                            <select class="form-select js-select2" data-search="on" id="categoryID">
                                @foreach ($ownerclanCategories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-center">
                    <button type="button" class="btn btn-primary"
                        onclick="selectCategory($('#categoryID').val());">선택하기</button>
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
        $(document).ready(function() {
            @if (!isset($_GET['categoryID']))
                $('#selectCategory').modal('show');
            @endif
        });

        function selectCategory(categoryID) {
            window.location.replace("?categoryID=" + categoryID);
        }
    </script>
@endsection
