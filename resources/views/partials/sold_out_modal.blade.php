<div class="modal" tabindex="-1" role="dialog" id="selectB2bModal" data-bs-backdrop="static" data-bs-keyboard="false">
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
