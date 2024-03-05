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
<link rel="stylesheet" href="{{ asset('business_page.css') }}">

<link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.5.1/css/all.css">
<link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.5.1/css/sharp-thin.css">
<link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.5.1/css/sharp-solid.css">
<link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.5.1/css/sharp-regular.css">
<link rel="stylesheet" href="https://site-assets.fontawesome.com/releases/v6.5.1/css/sharp-light.css">
<style>
    .styled-table {
        width: 100%;
        border-collapse: collapse;
        margin: 25px 0;
        font-size: 1.2rem;
        font-family: sans-serif;
        text-align: center;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
    }

    .styled-table tfoot {
        color: #18A8F1;
        font-weight: bold;
    }

    .styled-table thead tr {
        background-color: #18A8F1;
        color: #ffffff;
    }

    .styled-table th,
    .styled-table td {
        padding: 12px 15px;
        border: 1px solid #dddddd;
        width: 25%;
        /* Adjust color as needed */
    }

    .styled-table tbody tr {
        border-bottom: 1px solid #dddddd;
    }

    .styled-table tbody tr:nth-of-type(even) {
        background-color: #f3f3f3;
    }

    .styled-table tbody tr:last-of-type {
        border-bottom: 2px solid #18A8F1;
    }

    .styled-table tbody tr.active-row {
        font-weight: bold;
        color: #18A8F1;
    }
</style>
