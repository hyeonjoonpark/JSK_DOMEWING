@extends('layouts.main')
@section('title')
    파트너스 매칭
@endsection
@section('subtitle')
    <p>
        날먹윙 및 갓윙 상품들을 셀윙 파트너들과 매칭하는 페이지입니다.
    </p>
@endsection
@section('content')
    <div class="row g-gs">
        <div class="col-12">
            <div class="card card-bordered">
                <div class="card-inner">
                    <h6 class="title">매칭</h6>
                    <ul class="list">
                        <li>아래 매칭 버튼을 눌러 소유주가 없는 상품들을 갓윙 클래스 파트너들에게 무작위로 분배합니다.</li>
                        <li>{생성일시 갓윙 인덱스} 형식으로 갓윙 클래스 파트너들의 상품 관리 메뉴에 테이블 형식으로 생성됩니다.</li>
                        <li>각 테이블은 최대 500개의 상품을 수용합니다.</li>
                        <li>한 번 매칭된 상품은 수정하거나 삭제할 수 없습니다.</li>
                        <li>현재 총 <b>{{ number_format($data['numGodwingProducts']) }}</b>개의 날먹윙 또는 갓윙 상품이 있습니다.</li>
                        <li>현재 총 <b>{{ number_format($data['numUnmatchedProducts']) }}</b>개의 매칭되지 않은 상품이 있습니다.</li>
                    </ul>
                    <button class="btn btn-primary" onclick="initCombine();">매칭</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        function initCombine() {
            const html = `
                <h6 class="title">날먹윙|갓윙 상품 매칭</h6>
                <p>
                    정말로 날먹윙|갓윙 상품 매칭을 시작하시겠습니까?<br>
                    이 작업은 되돌릴 수 없습니다.
                </p>
            `;
            Swal.fire({
                icon: "info",
                html,
                showCancelButton: true,
                cancelButtonText: "취소",
                confirmButtonText: "확인"
            }).then((result) => {
                if (result.isConfirmed) {
                    combine();
                }
            });
        }

        function combine() {
            popupLoader(1, '날먹윙 및 갓윙 상품들을 갓 클래스 파트너들과 무작위로 매칭 중입니다.');
            $.ajax({
                url: '/api/nalmeokwing/combine',
                type: 'PUT',
                dataType: 'JSON',
                data: {
                    rememberToken
                },
                success: ajaxSuccessHandling,
                error: AjaxErrorHandling
            });
        }
    </script>
@endsection
