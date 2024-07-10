@extends('partner.layouts.main')
@section('title')
    대시보드
@endsection
@section('subtitle')
    <p>각종 판매 실적들을 파악하기 위한 차트 페이지입니다.</p>
@endsection
@section('content')
    <div class="row g-gs">
        <div class="col-12 text-center">
            <img class="w-50" src="{{ asset('assets/images/on_update.jpg') }}">
        </div>
        <div class="col-12 text-center">
            <h4 class="title">업데이트 중입니다</h4>
        </div>
    </div>
@endsection
@section('scripts')
    <script src="./assets/js/charts/gd-default.js?ver=3.1.1"></script>
    <script></script>
@endsection
