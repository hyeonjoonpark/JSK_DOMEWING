@extends('layouts.main')
@section('title')
    API윙
@endsection
@section('subtitle')
    <p>API윙을 통해 수집된 상품들을 가공합니다.</p>
@endsection
@section('content')
    <div class="row g-gs mb-5">
        <div class="col">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">원청사 선택</h6>
                    <p>가공 및 저장하고자 하는 원청사를 선택해주세요.</p>
                    <div class="form-group">
                        <label for="" class="form-label">원청사</label>
                        <div class="row">
                            @foreach ($sellers as $seller)
                                <div class="col-12 col-md-3 mb-3">
                                    <div class="custom-control custom-checkbox">
                                        <div class="custom-control custom-radio">
                                            <input type="radio" id="seller{{ $seller->vendor_id }}" name="sellers"
                                                value="{{ $seller->vendor_id }}" class="custom-control-input"
                                                {{ $seller->vendor_id == 16 ? 'checked' : '' }}>
                                            <label class="custom-control-label"
                                                for="seller{{ $seller->vendor_id }}">{{ $seller->name }}</label>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="d-flex justify-content-center">
                        <button class="btn btn-primary">API윙 가동</button>
                    </div>
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
    {{-- <div class="row g-gs">
        <div class="col">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">API윙 결과</h6>
                    <p>총 {{ number_format(count($products)) }}개의 상품이 검색되었습니다.</p>
                    <div class="d-flex justify-content-center">
                        <button class="btn btn-warning">가공 및 저장</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped text-nowrap align-middle">
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
    </div> --}}
@endsection
@section('scripts')
    <script></script>
@endsection
