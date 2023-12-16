<script src="{{ asset('assets/js/bundle.js') }}"></script>
<script src="{{ asset('assets/js/scripts.js') }}"></script>
<script src="https://kit.fontawesome.com/0a14a1d42d.js" crossorigin="anonymous"></script>
<script>
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
</script>
