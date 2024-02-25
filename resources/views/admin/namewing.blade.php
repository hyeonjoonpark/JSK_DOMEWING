{{-- layouts/main.blade.php를 상속받음 --}}
@extends('layouts.main')

{{-- 페이지 타이틀 섹션 --}}
@section('title', '네임윙')

{{-- 서브타이틀 섹션 --}}
@section('subtitle')
    <p>중복된 상품명들을 관리합니다.</p>
@endsection

{{-- 메인 콘텐츠 섹션 --}}
@section('content')
    <div class="row g-gs">
        @forelse ($duplicatedProducts as $product)
            <div class="col-6">
                <div class="card card-bordered preview">
                    <div class="card-inner text-center">
                        <img src="{{ $product->productImage }}" class="img-fluid col-12 col-lg-6 mx-auto d-block"><br>
                        <a class="btn btn-primary mt-2" href="{{ $product->productHref }}" target="_blank">상세보기</a>
                        <div class="form-group text-start mt-3">
                            <label for="{{ $product->productCode }}" class="form-label">상품명</label>
                            <input type="text" id="{{ $product->productCode }}" class="form-control"
                                value="{{ $product->productName }}">
                        </div>
                        <button class="btn btn-success">수정완료</button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col">
                <p>중복된 상품명이 없습니다. 엑셀윙 진행 가능합니다.</p>
            </div>
        @endforelse
    </div>
@endsection

{{-- 추가 스크립트 섹션 --}}
@section('scripts')
    <script>
        // 여기에 필요한 자바스크립트 코드를 추가할 수 있습니다.
    </script>
@endsection
