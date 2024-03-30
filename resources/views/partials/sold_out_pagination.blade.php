<!-- resources/views/admin/partials/pagination.blade.php -->

<div class="d-flex justify-content-center align-items-center">
    @if ($page > 1)
        <a class="pagination"
            href="{{ route('admin.product.sold-out', ['page' => max($page - 1, 1), 'searchKeyword' => $searchKeyword, 'productCodesStr' => $productCodesStr]) }}">
            &lt; 이전
        </a>
    @endif

    @php
        $startPage = max(1, $page - 5);
        $endPage = min($numPages, $startPage + 9);
    @endphp

    @for ($i = $startPage; $i <= $endPage; $i++)
        <a class="pagination {{ $page == $i ? 'active' : '' }}"
            href="{{ route('admin.product.sold-out', ['page' => $i, 'searchKeyword' => $searchKeyword, 'productCodesStr' => $productCodesStr]) }}">
            {{ $i }}
        </a>
    @endfor

    @if ($page < $numPages)
        <a class="pagination"
            href="{{ route('admin.product.sold-out', ['page' => $page + 1, 'searchKeyword' => $searchKeyword, 'productCodesStr' => $productCodesStr]) }}">
            다음 &gt;
        </a>
    @endif
</div>
