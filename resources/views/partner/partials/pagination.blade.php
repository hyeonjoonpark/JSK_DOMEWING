<div class="d-flex justify-content-center align-items-center flex-wrap">
    @if ($page > 1)
        <a class="pagination"
            href="{{ route('partner.products.collect', ['page' => $page - 1, 'searchKeyword' => $searchKeyword, 'productCodes' => $productCodesStr]) }}">
            &lt; 이전
        </a>
    @endif

    @php
        $startPage = max(1, $page - 5);
        $endPage = min($numPages, $startPage + 9);
    @endphp

    @for ($i = $startPage; $i <= $endPage; $i++)
        <a class="pagination {{ $page == $i ? 'active' : '' }}"
            href="{{ route('partner.products.collect', ['page' => $i, 'searchKeyword' => $searchKeyword, 'productCodes' => $productCodesStr]) }}">
            {{ $i }}
        </a>
    @endfor

    @if ($page < $numPages)
        <a class="pagination"
            href="{{ route('partner.products.collect', ['page' => $page + 1, 'searchKeyword' => $searchKeyword, 'productCodes' => $productCodesStr]) }}">
            다음 &gt;
        </a>
    @endif
</div>
