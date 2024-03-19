@extends('layouts.main')
@section('title')
    상품 대량 관리
@endsection
@section('subtitle')
    <p>셀윙 상품 대량 관리 엑셀 양식으로 상품 정보 수정, 품절 상태 변경, 재입고 처리가 가능합니다.</p>
@endsection
@section('content')
    <div class="row g-gs">
        <div class="col-12">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">셀윙 상품 대량 관리 엑셀 양식 안내</h6>
                    <p>해당 양식을 다운로드하여 수정할 상품 정보를 입력한 후 아래의 업로드 과정을 진행해 주세요.</p>
                    <a href="{{ asset('assets/excel/sellwing_products_editor.xlsx') }}" target="_blank">양식
                        다운로드</a>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">상품 대량 관리 엑셀 업로드</h6>
                    <p>상품 기입이 완료된 셀윙 엑셀 양식을 업로드해주세요.</p>
                    <div class="d-flex text-nowrap">
                        <input type="file" class="form-control">
                        <button class="btn btn-primary">업로드</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script></script>
@endsection
