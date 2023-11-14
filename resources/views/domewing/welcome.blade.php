@extends('domewing.layouts.main')
@section('content')
    <div class="row g-gs">
        <div class="col">
            <p>Welcome Page</p>
        </div>
    </div>

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
@endsection

@section('scripts')
@endsection
