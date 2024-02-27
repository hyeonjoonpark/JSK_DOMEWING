{{-- layouts/main.blade.php를 상속받음 --}}
@extends('layouts.main')
{{-- 페이지 타이틀 섹션 --}}
@section('title', '네임윙')
{{-- 서브타이틀 섹션 --}}
@section('subtitle')
    <p>중복된 상품명들을 관리합니다. 엑셀윙 추출을 위해서는 반드시 네임윙이 선행되어야 합니다.</p>
@endsection
{{-- 메인 콘텐츠 섹션 --}}
@section('content')
    <div class="row g-gs">
        @forelse ($duplicatedProducts as $product)
            <div class="col-12 col-lg-6">
                <div class="card card-bordered preview">
                    <div class="card-inner text-center">
                        <img src="{{ $product->productImage }}" class="img-fluid col-12 col-lg-6 mx-auto d-block"><br>
                        <a class="btn btn-primary mt-2" href="{{ $product->productHref }}" target="_blank">상세보기</a>
                        <div class="form-group text-start mt-3">
                            <label for="{{ $product->productCode }}" class="form-label">상품명</label>
                            <input type="text" id="{{ $product->productCode }}" class="form-control"
                                value="{{ $product->productName }}">
                        </div>
                        <button class="btn btn-success"
                            onclick="initEditProductName('{{ $product->productCode }}');">수정완료</button>
                        <button class="btn btn-danger" onclick="initSoldOut(['{{ $product->productCode }}']);">품절처리</button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col">
                <p>중복된 상품명이 없습니다. 엑셀윙 진행 가능합니다.</p>
            </div>
        @endforelse
    </div>
    <div class="modal" tabindex="-1" role="dialog" id="selectB2bModal" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">B2B 업체 선택</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col">
                            <div class="form-group">
                                <label for="" class="form-label">B2B 업체 리스트</label>
                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <div class="custom-control custom-checkbox">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" id="sellwing" name="sellwing" value="0"
                                                    class="custom-control-input" checked>
                                                <label class="custom-control-label" for="sellwing">셀윙</label>
                                            </div>
                                        </div>
                                    </div>
                                    @foreach ($b2bs as $b2b)
                                        <div class="col-6 mb-3">
                                            <div class="custom-control custom-checkbox">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" id="b2b{{ $b2b->vendor_id }}" name="b2bs"
                                                        value="{{ $b2b->vendor_id }}" class="custom-control-input" checked>
                                                    <label class="custom-control-label"
                                                        for="b2b{{ $b2b->vendor_id }}">{{ $b2b->name }}</label>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" id="runSoldOutBtn">선택완료</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">종료하기</button>
                </div>
            </div>
        </div>
    </div>
@endsection
{{-- 추가 스크립트 섹션 --}}
@section('scripts')
    <script>
        var rememberToken = '{{ Auth::user()->remember_token }}';

        function initEditProductName(productCode) {
            popupLoader(1, '변경된 상품명을 DB에 적용 중입니다.');
            const newProductName = $('#' + productCode).val();
            $.ajax({
                url: '/api/product/edit-name',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    productCode,
                    newProductName,
                    rememberToken
                },
                success: function(response) {
                    closePopup();
                    const status = response.status;
                    let statusStr = 'error';
                    if (status === true) {
                        statusStr = 'success';
                    }
                    swalWithReload(response.return, statusStr);
                },
                error: function(response) {
                    closePopup();
                    console.log(response);
                    swalError('통신 상에 문제가 발생했습니다.');
                }
            });
        }
    </script>
@endsection
