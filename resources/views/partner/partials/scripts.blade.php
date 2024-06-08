<script src="{{ asset('assets/js/bundle.js') }}"></script>
<script src="{{ asset('assets/js/scripts.js') }}"></script>
<script src="https://kit.fontawesome.com/0a14a1d42d.js" crossorigin="anonymous"></script>
<script>
    window.addEventListener('DOMContentLoaded', function() {
        const pageLoader = document.getElementById('pageLoader');
        pageLoader.style.opacity = '0';
        setTimeout(function() {
            pageLoader.style.display = 'none';
        }, 500);
    });
    var audioError = new Audio('{{ asset('assets/audio/diring.mp3') }}');
    var apiToken = "{{ Auth::guard('partner')->user()->api_token }}";

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
            popupLoader(0, '페이지를 새로고침 중입니다.');
            location.reload();
        });
    }

    function numberFormat(number, decimals = 0, decPoint = '.', thousandsSep = ',') {
        // Ensure number is a float.
        number = parseFloat(number);

        // Check if number is NaN and return 0 in such case
        if (isNaN(number)) {
            return '0';
        }

        // Fix the number to specified decimal places and convert to string.
        number = number.toFixed(decimals);

        // Split the number by the decimal point.
        const parts = number.split('.');

        // Replace instances of thousand separator.
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousandsSep);

        // Reassemble the number and return it.
        return parts.join(decPoint);
    }

    function AjaxErrorHandling(error) {
        console.log(error);
        closePopup();
        audioError.play();
        swalError('"API 요청 과정에서 에러가 발생했습니다."');
    }

    function ajaxSuccessHandling(response) {
        console.log(response);
        const status = response.status;
        const message = response.message;
        closePopup();
        if (status === false) {
            swalError(message);
        } else {
            swalWithReload(message, 'success');
        }
    }

    function createProductTable() {
        popupLoader(1, "신규 상품 테이블을 생성 중입니다.");
        const productTableName = $('#productTableName').val();
        $.ajax({
            url: "/api/partner/product/create-table",
            type: "POST",
            dataType: "JSON",
            data: {
                productTableName,
                apiToken
            },
            success: ajaxSuccessHandling,
            error: AjaxErrorHandling
        });
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
        return filteredValue ? filteredValue : '';
    }

    function readAll() {
        $.ajax({
            url: '/api/partner/notifications/read-all',
            type: 'POST',
            dataType: 'JSON',
            data: {
                apiToken
            },
            success: ajaxSuccessHandling,
            error: AjaxErrorHandling
        });
    }
</script>
