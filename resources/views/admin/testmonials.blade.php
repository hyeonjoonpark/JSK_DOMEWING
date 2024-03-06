{{-- layouts/main.blade.php를 상속받음 --}}
@extends('layouts.main')
{{-- 페이지 타이틀 섹션 --}}
@section('title', '제품후기')
{{-- 서브타이틀 섹션 --}}
@section('subtitle')
    <p>제품후기 추가, 수정 및 삭제를 위한 관리 페이지입니다.</p>
@endsection
{{-- 메인 콘텐츠 섹션 --}}
@section('content')
    <div class="d-flex justify-content-center mb-3">
        <button class="btn btn-primary" onclick="$('#addTestmonialModal').modal('show')">추가하기</button>
    </div>
    <div class="row g-gs mb-3">
        @foreach ($testmonials as $testmonial)
            <div class="col-12 col-lg-6">
                <div class="card card-bordered">
                    <div class="card-inner">
                        <h5 class="card-title">제품후기</h5>
                        <div class="summernote-basic" id="message{{ $testmonial->id }}"></div>
                        <div class="form-group mt-3">
                            <div class="row">
                                <div class="col-12 col-lg-6">
                                    <label class="form-label">작성시간</label>
                                    <div class="form-control-wrap">
                                        <div class="form-icon form-icon-left">
                                            <em class="icon ni ni-calendar"></em>
                                        </div>
                                        <input type="text" class="form-control date-picker" data-date-format="yyyy-mm-dd"
                                            value="{{ $testmonial->created_at }}" id="createdAt{{ $testmonial->id }}">
                                    </div>
                                </div>
                                <div class="col-12 col-lg-6">
                                    <label class="form-label">작성자</label>
                                    <input type="text" class="form-control" value="{{ $testmonial->message_by }}"
                                        id="messageBy{{ $testmonial->id }}">
                                </div>
                            </div>
                        </div>
                        <div class="d-flex
                                        justify-content-center">
                            <button class="btn btn-success me-3"
                                onclick="edtTestmonial({{ $testmonial->id }})">수정하기</button>
                            <button class="btn btn-danger" onclick="initDelTestmonial({{ $testmonial->id }});">삭제하기</button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <div class="modal" tabindex="-1" role="dialog" id="addTestmonialModal" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">제품후기 추가하기</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col">
                            <div class="summernote-basic" id="message"></div>
                            <div class="form-group mt-3">
                                <div class="row">
                                    <div class="col-12 col-lg-6">
                                        <label class="form-label">작성시간</label>
                                        <div class="form-control-wrap">
                                            <div class="form-icon form-icon-left">
                                                <em class="icon ni ni-calendar"></em>
                                            </div>
                                            <input type="text" class="form-control date-picker"
                                                data-date-format="yyyy-mm-dd" placeholder="0000-00-00" id="createdAt">
                                        </div>
                                    </div>
                                    <div class="col-12 col-lg-6">
                                        <label class="form-label">작성자</label>
                                        <input type="text" class="form-control" id="messageBy"
                                            placeholder="작성자를 기입해주세요.">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="addTestmonial();">저장하기</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">종료하기</button>
                </div>
            </div>
        </div>
    </div>
@endsection
{{-- 추가 스크립트 섹션 --}}
@section('scripts')
    <link rel="stylesheet" href="{{ asset('assets/css/editors/summernote.css?ver=3.1.1') }}">
    <script src="{{ asset('assets/js/libs/editors/summernote.js?ver=3.1.1') }}"></script>
    <script src="{{ asset('assets/js/editors.js?ver=3.1.1') }}"></script>
    <script>
        $(document).ready(function() {
            const testmonials = @json($testmonials);
            const summernotes = $('.summernote-basic');
            for (let i = 0; i < testmonials.length; i++) {
                const summernote = $(summernotes[i]);
                const message = testmonials[i].message;
                summernote.summernote({
                    height: 400
                })
                summernote.summernote('code', message);
            }
        });

        function initDelTestmonial(testmonialId) {
            Swal.fire({
                icon: "warning",
                title: "제품후기 삭제",
                text: "정말로 해당 제품후기를 삭제하시겠습니까?",
                showCancelButton: true,
                confirmButtonText: "삭제하기",
                cancelButtonText: "취소하기",
            }).then((result) => {
                if (result.isConfirmed) {
                    delTestmonial(testmonialId);
                }
            });
        }

        function addTestmonial() {
            popupLoader(1, "제품후기를 추가하는 중입니다.");
            const message = $('#message').summernote('code');
            const createdAt = $('#createdAt').val();
            const messageBy = $('#messageBy').val();
            $.ajax({
                url: "/api/testmonials/add",
                type: "POST",
                dataType: "JSON",
                data: {
                    message,
                    messageBy,
                    createdAt,
                    rememberToken
                },
                success: function(response) {
                    console.log(response);
                    const responseStatus = response.status;
                    if (responseStatus) {
                        closePopup();
                        swalWithReload(response.return, "success");
                    } else {
                        let errorMsg = getErrorMessageFromResponse(response);
                        $('.btn').prop('disabled', false);
                        swalError(errorMsg);
                    }
                },
                error: AjaxErrorHandling
            });
        }

        function edtTestmonial(testmonialId) {
            popupLoader(1, "제품후기를 추가하는 중입니다.");
            const message = $('#message' + testmonialId).summernote('code');
            const createdAt = $('#createdAt' + testmonialId).val();
            const messageBy = $('#messageBy' + testmonialId).val();
            $.ajax({
                url: "/api/testmonials/edt",
                type: "POST",
                dataType: "JSON",
                data: {
                    message,
                    messageBy,
                    createdAt,
                    testmonialId,
                    rememberToken
                },
                success: function(response) {
                    console.log(response);
                    const responseStatus = response.status;
                    if (responseStatus) {
                        closePopup();
                        swalWithReload(response.return, "success");
                    } else {
                        let errorMsg = getErrorMessageFromResponse(response);
                        $('.btn').prop('disabled', false);
                        swalError(errorMsg);
                    }
                },
                error: AjaxErrorHandling
            });
        }

        function delTestmonial(testmonialId) {
            popupLoader(1, "제품후기를 삭제하는 중입니다.");
            $.ajax({
                url: "/api/testmonials/del",
                type: "POST",
                dataType: "JSON",
                data: {
                    testmonialId,
                    rememberToken
                },
                success: function(response) {
                    console.log(response);
                    const responseStatus = response.status;
                    if (responseStatus) {
                        closePopup();
                        swalWithReload(response.return, "success");
                    } else {
                        let errorMsg = getErrorMessageFromResponse(response);
                        $('.btn').prop('disabled', false);
                        swalWithReload(errorMsg, "error");
                    }
                },
                error: AjaxErrorHandling
            });
        }

        function getErrorMessageFromResponse(response) {
            const errors = response.return;
            let errorMsgParts = [];

            // 각 필드가 존재하는 경우에만 배열에 추가
            if (errors.message) errorMsgParts.push(errors.message);
            if (errors.messageBy) errorMsgParts.push(errors.messageBy);
            if (errors.rememberToken) errorMsgParts.push(errors.rememberToken);
            if (errors.createdAt) errorMsgParts.push(errors.createdAt);
            if (errors.testmonialId) errorMsgParts.push(errors.testmonialId);

            // 배열의 요소들을 공백이나 다른 구분자로 연결
            return errorMsgParts.join('<br><br>'); // 공백을 구분자로 사용
        }
    </script>
@endsection
