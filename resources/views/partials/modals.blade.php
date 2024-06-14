<div class="modal" tabindex="-1" role="dialog" id="expiredAtModal" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">기간 설정</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">기간</label>
                    <div class="form-control-wrap">
                        <div class="form-icon form-icon-left">
                            <em class="icon ni ni-calendar"></em>
                        </div>
                        <input type="text" id="expiredAt" class="form-control date-picker"
                            data-date-format="yyyy-mm-dd">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="expiredAtBtn">확인</button>
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">취소</button>
            </div>
        </div>
    </div>
</div>
<div class="modal modal-lg" role="dialog" id="viewProduct">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">도매윙 원상품 정보</h5>
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
