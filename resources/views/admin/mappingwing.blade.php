@extends('layouts.main')
@section('title')
    매핑윙
@endsection
@section('subtitle')
    <p>
        매핑윙은 B2B 업체들 사이의 다양한 카테고리를 연결하고 매핑하는 엔진입니다.
    </p>
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
@endsection
@section('scripts')
    <link rel="stylesheet" href="{{ asset('assets/css/editors/summernote.css') }}">
    <script src="{{ asset('assets/js/editors.js') }}"></script>
    <script src="{{ asset('assets/js/libs/editors/summernote.js') }}"></script>
    <script></script>
@endsection
