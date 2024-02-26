<script>
    function initSoldOut(productCodes) {
        $('#runSoldOutBtn').off('click').on('click', function() {
            runSoldOut(productCodes);
        });
        $('#selectB2bModal').modal('show');
    }

    function runSoldOut(productCodes) {
        closePopup();
        popupLoader(0, '"선택된 업체들에게 품절 소식을 알리고 올게요."');
        const b2bs = $('input[name="b2bs"]:checked').map(function() {
            return $(this).val();
        }).get();
        const isSellwingChecked = $('#sellwing').prop('checked');
        console.log(isSellwingChecked);
        $.ajax({
            url: '/api/product/sold-out',
            type: 'POST',
            dataType: 'JSON',
            data: {
                productCodes,
                rememberToken,
                b2bs,
                isSellwingChecked
            },
            success: soldOutSuccess,
            error: AjaxErrorHandling
        });
    }

    function soldOutSuccess(response) {
        console.log(response);
        closePopup();
        const status = response.status;
        if (status === true) {
            swalSuccess(response.return);
        } else {
            swalError(response.return);
        }
    }
</script>
