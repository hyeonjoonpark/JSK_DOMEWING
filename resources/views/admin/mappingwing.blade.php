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
        <div class="col-12 col-md-6">
            <div class="card card-bordered">
                <div class="card-inner">
                    <div class="form-group">
                        <label for="" class="form-label">오너클랜 카테고리</label>
                        <select name="ownerclanCategoryID" id="ownerclanCategoryID" class="form-select js-select2"
                            data-search="on">
                            {{-- @foreach ($categoryMapping as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach --}}
                        </select>
                    </div>
                    {{ print_r($categoryMapping) }}
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="card card-bordered">
                <div class="card-inner">
                    {{-- @foreach ($b2Bs as $b2B)
                        <div class="form-group">
                            <label for="" class="form-label">{{ $b2B->name }}</label>
                            <div class="d-flex text-nowrap">
                                <input type="text" class="form-control" placeholder="검색 키워드를 기입해주세요.">
                                <button class="btn btn-primary">검색</button>
                            </div>
                            <select name="{{ $b2B->vendor_id }}" id="{{ $b2B->vendor_id }}"
                                class="form-select js-select2"></select>
                        </div>
                    @endforeach --}}
                    <div class="d-flex justify-content-center">
                        <button class="btn btn-primary">저장하기</button>
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
    <script></script>
@endsection
