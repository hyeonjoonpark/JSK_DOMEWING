@extends('layouts.main')
@section('title')
    갓윙 환경설정
@endsection
@section('subtitle')
    <p>
        갓윙 업체들을 관리하는 페이지입니다.
    </p>
@endsection
@section('content')
    <div class="row g-gs">
        <div class="col-12 text-center">
            <div class="text-center">
                <button class="btn btn-primary" onclick="$('#updateGodwingModal').modal('show');">업데이트</button>
            </div>
        </div>
        <div class="col-12">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">갓윙 업체 리스트</h6>
                    <p>갓윙에 소속된 업체들의 상품은 GOD 클래스 미만의 파트너스와 도매윙에 노출되지 않습니다.</p>
                    <table class="table table-tranx">
                        <thead>
                            <tr class="tb-tnx-head">
                                <th class="tb-tnx-info">업체명</th>
                                <th class="tb-tnx-info">닉네임</th>
                                <th class="tb-tnx-info">생성일자</th>
                                <th class="tb-tnx-info">수정일자</th>
                                <th class="tb-tnx-info">작업</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($vendors as $v)
                                @if ($v->is_godwing)
                                    <tr class="tb-tnx-item">
                                        <td class="tb-tnx-info">
                                            <a href="{{ $v->vendor_href }}" target="_blank">
                                                {{ $v->name }}
                                            </a>
                                        </td>
                                        <td class="tb-tnx-info">
                                            {{ $v->nickname }}
                                        </td>
                                        <td class="tb-tnx-info">
                                            {{ $v->created_at }}
                                        </td>
                                        <td class="tb-tnx-info">
                                            {{ $v->updated_at }}
                                        </td>
                                        <td class="tb-tnx-info">
                                            <button class="btn btn-danger"
                                                onclick="initDestroy({{ $v->id }});">삭제</button>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="modal" tabindex="-1" role="dialog" id="updateGodwingModal" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">갓윙 업체 업데이트</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">업체 선택</label>
                        <div class="form-control-wrap">
                            <select class="form-select js-select2" id="vendorId" data-search="on">
                                @foreach ($vendors as $v)
                                    @if (!$v->is_godwing)
                                        <option value="{{ $v->id }}">{{ $v->name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" onclick="update();">확인</button>
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">취소</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        $(document).ready(function() {
            $("#vendorId").select2({
                dropdownParent: $("#updateGodwingModal")
            });
        });

        function update() {
            const vendorId = $("#vendorId").val();
            popupLoader(1, "해당 업체를 갓윙으로 업데이트 중입니다.");
            $.ajax({
                url: "/api/godwing/update",
                type: "POST",
                dataType: "JSON",
                data: {
                    rememberToken,
                    vendorId
                },
                success: ajaxSuccessHandling,
                error: AjaxErrorHandling
            });
        }

        function initDestroy(vendorId) {
            Swal.fire({
                icon: "warning",
                title: "갓윙 업체 삭제",
                text: "정말로 해당 업체를 갓윙으로부터 삭제하시겠습니까?",
                showCancelButton: true,
                cancelButtonText: "취소",
                confirmButtonText: "확인"
            }).then((result) => {
                if (result.isConfirmed) {
                    destroy(vendorId);
                }
            });
        }

        function destroy(vendorId) {
            popupLoader(1, "해당 업체를 갓윙 목록으로부터 제외하는 중입니다.");
            $.ajax({
                url: `/api/godwing/destroy/${vendorId}`,
                type: "DELETE",
                dataType: "JSON",
                data: {
                    rememberToken
                },
                success: ajaxSuccessHandling,
                error: AjaxErrorHandling
            });
        }
    </script>
@endsection
