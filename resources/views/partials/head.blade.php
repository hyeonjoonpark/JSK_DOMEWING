<meta charset="utf-8">
<meta name="author" content="Softnio">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="description"
    content="A powerful and conceptual apps base dashboard template that especially build for developers and programmers.">
<meta name="csrf-token" content="{{ csrf_token() }}">
<!-- Fav Icon  -->
<link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}">
<!-- Page Title  -->
<title>셀윙 | B2B 도매 통합 솔루션</title>
<!-- StyleSheets  -->
<link rel="stylesheet" href="{{ asset('assets/css/dashlite.css?ver=3.1.1') }}">
<link id="skin-default" rel="stylesheet" href="{{ asset('assets/css/theme.css?ver=3.1.1') }}">
<style>
    .product-list-image {
        border: 1px solid black;
        border-bottom: 2px solid black;
        border-right: 2px solid black;
        width: 100px;
        height: 100px;
    }

    /* Active state for pagination */
    .pagination.active {
        background-color: #007bff;
        border-color: #007bff;
        color: #fff;
    }

    /* Inactive state for pagination */
    .pagination {
        display: inline-block;
        padding: 8px 12px;
        margin: 4px;
        font-size: 14px;
        color: #007bff;
        border: 1px solid #007bff;
        text-decoration: none;
        cursor: pointer;
        border-radius: 4px;
    }

    .pagination:hover {
        background-color: #f8f9fa;
    }
</style>
