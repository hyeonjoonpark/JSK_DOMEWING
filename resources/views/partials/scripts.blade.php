<script src="{{ asset('assets/js/bundle.js') }}"></script>
<script src="{{ asset('assets/js/scripts.js') }}"></script>
<script src="https://kit.fontawesome.com/0a14a1d42d.js" crossorigin="anonymous"></script>
<script>
    var rememberToken = '{{ Auth::guard('user')->user()->remember_token }}';
    var audioError = new Audio('{{ asset('assets/audio/diring.mp3') }}');

    function popupLoader(index, text) {
        const loaders = ["{{ asset('assets/images/loading.gif') }}",
            "{{ asset('assets/images/search-loader.gif') }}"
        ];
        $('.btn').prop('disabled', true);
        let html = '<img src="' + loaders[index] + '" class="w-75" />'
        html += '<h2 class="swal2-title mt-5">' + text + '</h2>'
        Swal.fire({
            html: html,
            allowOutsideClick: false,
            showConfirmButton: false
        });
    }

    function closePopup() {
        Swal.close();
        $('.modal').modal('hide');
        $('.btn').prop('disabled', false);
    }

    function numberFormat(number, decimals = 0, decPoint = '.', thousandsSep = ',') {
        number = Number(number).toFixed(decimals);

        // 소수점 분리
        const numberParts = number.split('.');
        let integerPart = numberParts[0];
        const decimalPart = numberParts.length > 1 ? decPoint + numberParts[1] : '';

        // 정수부에 쉼표 추가
        const reg = /(\d+)(\d{3})/;
        while (reg.test(integerPart)) {
            integerPart = integerPart.replace(reg, '$1' + thousandsSep + '$2');
        }

        return integerPart + decimalPart;
    }

    function selectAll(selectAll) {
        const checkboxes = document.querySelectorAll('input[name="selectedProducts"]');
        checkboxes.forEach((checkbox) => {
            checkbox.checked = selectAll.checked
        });
    }

    function swalError(message) {
        audioError.play();
        Swal.fire({
            icon: 'error',
            html: '<img class="w-100" src="{{ asset('media/Asset_Notif_Error.svg') }}"><h4 class="swal2-title mt-5">' +
                message + '</h4>'
        });
    }

    function swalSuccess(message) {
        Swal.fire({
            icon: 'success',
            html: '<img class="w-100" src="{{ asset('media/Asset_Notif_Success.svg') }}"><h4 class="swal2-title mt-5">' +
                message + '</h4>'
        });
    }

    function swalWithReload(message, status) {
        // Determine the icon and image based on the status
        const icons = {
            'success': {
                icon: 'success',
                image: '{{ asset('media/Asset_Notif_Success.svg') }}'
            },
            'error': {
                icon: 'error',
                image: '{{ asset('media/Asset_Notif_Error.svg') }}'
            }
        };

        const {
            icon,
            image
        } = icons[status] || {};

        // Show the alert
        Swal.fire({
            icon,
            html: `<img class="w-100" src="${image}"><h4 class="swal2-title mt-5">${message}</h4>`
        }).then(result => {
            location.reload();
        });
    }

    function AjaxErrorHandling(error) {
        console.log(error);
        closePopup();
        audioError.play();
        swalError('"API 통신 요청 과정에서 에러가 발생했습니다."');
    }

    function initSoldOut(productCodes, type) {
        $('#runSoldOutBtn').off('click').on('click', function() {
            runSoldOut(productCodes, type);
        });
        $('#selectB2bModal').modal('show');
    }

    function runSoldOut(productCodes, type) {
        closePopup();
        popupLoader(0, '"선택된 업체들에게 업데이트 소식을 알리고 올게요."');
        const b2bs = $('input[name="b2bs"]:checked').map(function() {
            return $(this).val();
        }).get();
        console.log(b2bs);
        const isSellwingChecked = $('#sellwing').prop('checked');
        console.log(isSellwingChecked);
        $.ajax({
            url: '/api/product/sold-out',
            type: 'POST',
            dataType: 'JSON',
            data: {
                productCodes,
                rememberToken: rememberToken,
                b2bs,
                isSellwingChecked,
                type
            },
            success: soldOutSuccess,
            error: AjaxErrorHandling
        });
    }

    function soldOutSuccess(response) {
        console.log(response); // 응답 로그 출력
        closePopup(); // 팝업 닫기

        // 응답 상태에 따라 'success' 또는 'error'로 설정
        const statusType = response.status ? 'success' : 'error';

        // swalWithReload를 호출하여 사용자에게 결과 표시
        swalWithReload(response.return, statusType);
    }

    function numberFormatter(input, digitLength, decimalLength) {
        let value = $(input).val();
        if (value.includes('.') && decimalLength > 0) { // Decimal
            const splittedValue = value.split('.');
            const digit = integerFormatter(splittedValue[0], digitLength);
            const decimal = integerFormatter(splittedValue[1], decimalLength);
            if (decimal.length < 1 && digit.length < 1) {
                value = 0;
            } else {
                value = digit + '.' + decimal;
            }
        } else { // Integer
            value = integerFormatter(value, digitLength);
        }
        $(input).val(value);
    }

    function integerFormatter(value, length) {
        const filteredValue = value.replace(/\D/g, '').substring(0, length);
        return filteredValue ? parseInt(filteredValue) : '';
    }
</script>
