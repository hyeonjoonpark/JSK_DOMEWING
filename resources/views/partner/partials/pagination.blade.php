<div class="d-flex justify-content-center align-items-center flex-wrap">
    @if ($page > 1)
        <a class="pagination mx-1 my-1"
            href="{{ route('partner.products.collect', ['page' => $page - 1, 'searchKeyword' => $searchKeyword]) }}">
            &lt; 이전
        </a>
    @endif

    @php
        $isMobile = true;
        $range = $isMobile ? 3 : 5;
        $startPage = max(1, $page - $range);
        $endPage = min($numPages, $startPage + $range * 2);
    @endphp

    @for ($i = $startPage; $i <= $endPage; $i++)
        <a class="pagination mx-1 my-1 {{ $page == $i ? 'active' : '' }}"
            href="{{ route('partner.products.collect', ['page' => $i, 'searchKeyword' => $searchKeyword]) }}">
            {{ $i }}
        </a>
    @endfor

    @if ($page < $numPages)
        <a class="pagination mx-1 my-1"
            href="{{ route('partner.products.collect', ['page' => $page + 1, 'searchKeyword' => $searchKeyword]) }}">
            다음 &gt;
        </a>
    @endif
</div>
