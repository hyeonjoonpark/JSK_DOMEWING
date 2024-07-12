@extends('layouts.main')
@section('title')
    날먹윙
@endsection
@section('subtitle')
    <p>
        B2B 업체들의 상품들을 가공 및 수집하는 페이지입니다.
    </p>
@endsection
@section('content')
    <div class="row g-gs">
        <div class="col-12">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6>엑셀 파일 업로드</h6>
                    <p>B2B 업체를 선택한 후, 해당 업체의 상품 엑셀 양식을 업로드해주세요.</p>
                    <div class="row g-gs">
                        <div class="col">
                            <div class="form-group">
                                <label class="form-label">B2B 업체 선택</label>
                                <div class="form-control-wrap">
                                    <select class="form-select js-select2" id="vendorId" data-search="on">
                                        @foreach ($data['vendors'] as $v)
                                            <option value="{{ $v->id }}">{{ $v->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group">
                                <label class="form-label">엑셀 파일 업로드</label>
                                <div class="form-control-wrap">
                                    <input type="file" class="form-control" id="file" />
                                </div>
                            </div>
                        </div>
                        <div class="col-12 text-center">
                            <button class="btn btn-primary" onclick="store();">가공 및 수집</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">업로드 결과</h6>
                    <p>오류가 발생한 상품의 열 번호와 원인이 표시됩니다.</p>
                    <p id="uploadResult"></p>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        function store() {
            popupLoader(1, "엑셀 파일로부터 상품들을 가공 및 수집하는 중입니다.");
            const vendorId = $('#vendorId').val();
            const file = $('#file')[0].files[0];
            const formData = new FormData();
            formData.append('rememberToken', rememberToken);
            formData.append('vendorId', vendorId);
            formData.append('file', file);
            $.ajax({
                url: "/api/nalmeokwing/store",
                type: "POST",
                dataType: "JSON",
                processData: false,
                contentType: false,
                data: formData,
                success: function(response) {
                    console.log(response);
                    Swal.close();
                    $('.modal').modal("hide");
                    $('.btn').prop('disabled', false);
                    const status = response.status;
                    const message = response.message;
                    const error = response.error;
                    if (!status) {
                        Swal.fire({
                            icon: "warning",
                            title: "진행 실패",
                            text: message
                        });
                    } else {
                        let html = '';
                        for (const e of error) {
                            html += `
                                열 ${e.index} 오류: ${e.message}<br>
                            `;
                        }
                        $('#uploadResult').html(html);
                        Swal.fire({
                            icon: "success",
                            title: "진행 성공",
                            text: message
                        });
                    }
                },
                error: AjaxErrorHandling
            });
        }
    </script>
@endsection
