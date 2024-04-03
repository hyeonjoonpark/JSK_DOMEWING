<div class="modal" tabindex="-1" role="dialog" id="viewProfileModal" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">내 프로필</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">성명</label>
                    <input type="text" class="form-control" value="{{ $partner->name }}" disabled />
                </div>
                <div class="form-group">
                    <label class="form-label">클래스 / 만료일</label>
                    <div class="d-flex">
                        <input type="text" class="form-control" value="{{ $partner->partnerClass->name }}"
                            disabled />
                        <input type="text" class="form-control" value="{{ $partner->expired_at }} 까지" disabled />
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">연락처</label>
                    <input type="text" class="form-control" value="{{ $partner->phone }}" disabled />
                </div>
                <div class="form-group">
                    <label class="form-label">이메일</label>
                    <input type="text" class="form-control" value="{{ $partner->email }}" disabled />
                </div>
                <div class="form-group">
                    <label class="form-label">사업자 번호</label>
                    <input type="text" class="form-control" value="{{ $partner->business_number }}" disabled />
                </div>
                <div class="form-group">
                    <label class="form-label">사업자명</label>
                    <input type="text" class="form-control" value="{{ $partner->business_name }}" disabled />
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">확인</button>
            </div>
        </div>
    </div>
</div>
<div class="modal modal-lg" role="dialog" id="viewProduct">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">상품 상세보기</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row g-gs">
                    <div class="col-12 col-lg-6">
                        <img class="w-100" src="" alt="상품 이미지" id="viewProductImage">
                    </div>
                    <div class="col-12 col-lg-6">
                        <p class="title" id="viewCategory"></p>
                        <div class="form-group">
                            <label class="form-label">상품명</label>
                            <h6 class="title" id="viewProductName"></h6>
                        </div>
                        <div class="form-group">
                            <label class="form-label">상품 코드</label>
                            <h6 class="title" id="viewProductCode"></h6>
                        </div>
                        <div class="form-group">
                            <label class="form-label">상품 가격</label>
                            <h6 class="title wing-font" id="viewProductPrice"></h6>
                        </div>
                        <div class="form-group">
                            <label class="form-label">배송비</label>
                            <h6 class="title wing-font" id="viewShippingFee"></h6>
                        </div>
                    </div>
                    <div class="col-12" id="viewProductDetail">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">닫기</button>
            </div>
        </div>
    </div>
</div>
