@extends('partner.layouts.main')
@section('title')
    셀윙 파트너 계정 설정
@endsection
@section('subtitle')
    <p>셀윙 파트너 계정 정보 및 클래스 업그레이드를 관리합니다.</p>
@endsection
@section('content')
    <div class="row g-gs">
        <div class="col-12 col-lg-6">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">계정 정보</h6>
                    <p>기본 계정 정보를 관리합니다.</p>
                    <div class="form-group">
                        <label class="form-label">성명</label>
                        <input type="text" class="form-control" id="name" value="{{ $partner->name }}"
                            placeholder="실명을 입력해주세요.">
                    </div>
                    <div class="form-group">
                        <label class="form-label">이메일(이메일 정보는 수정이 불가합니다.)</label>
                        <input type="text" class="form-control" value="{{ $partner->email }}" disabled>
                    </div>
                    <div class="form-group">
                        <label class="form-label">휴대 전화</label>
                        <input type="text" class="form-control" id="phone" value="{{ $partner->phone }}"
                            placeholder="휴대 전화번호 11자리('-' 제외)를 입력해주세요.">
                    </div>
                    <div class="form-group">
                        <label class="form-label">비밀번호</label>
                        <input type="password" class="form-control" id="password" placeholder="비밀번호 8자리 이상을 입력해주세요.">
                    </div>
                    <div class="form-group">
                        <label class="form-label">비밀번호 확인</label>
                        <input type="password" class="form-control" id="password_confirmation"
                            placeholder="비밀번호와 똑같이 입력해주세요.">
                    </div>
                    <div class="text-center">
                        <button class="btn btn-primary" onclick="updatePartner();">저장하기</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">사업자 정보</h6>
                    <p>사업자 정보를 관리합니다.</p>
                    <div class="form-group">
                        <label class="form-label">사업자 번호</label>
                        <input type="text" class="form-control" value="{{ $partner->business_number }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">상호(법인명)</label>
                        <input type="text" class="form-control" value="{{ $partner->business_name }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">사업장 소재지</label>
                        <input type="text" class="form-control" value="{{ $partner->business_address }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">사업자 등록증</label>
                        <div class="text-center">
                            <img class="w-100 w-lg-50"
                                src="{{ asset('images/business-license/' . $partner->business_image) }}" alt="사업자 등록증">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">사업자 등록증 업로드</label>
                        <input type="file" class="form-control">
                    </div>
                    <div class="text-center">
                        <button class="btn btn-primary">업데이트 예정입니다</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        async function updatePartner() {
            popupLoader(1, '수정된 정보를 반영 중입니다.');
            const fields = ["name", "phone", "password", "password_confirmation"];
            const formData = new FormData();
            fields.forEach(field => {
                formData.append(field, $(`#${field}`).val());
            });
            formData.append('apiToken', apiToken);
            try {
                const response = await $.ajax({
                    url: "/api/partner/account-setting/partner",
                    type: "POST",
                    dataType: "JSON",
                    data: formData,
                    contentType: false, // FormData와 함께 사용할 때 필요
                    processData: false, // FormData의 데이터를 직접 처리하도록 설정
                });
                closePopup();
                const status = response.status;
                if (status === true) {
                    Swal.fire({
                        icon: 'success',
                        text: response.message
                    }).then((result) => {
                        window.location.replace('/partner/auth/logout');
                    });
                } else {
                    swalError(response.message);
                }
            } catch (error) {
                AjaxErrorHandling(error);
            }
        }
    </script>
@endsection
