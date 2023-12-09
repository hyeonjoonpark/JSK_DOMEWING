@extends('domewing.layouts.main')
@section('content')
    <div class="p-lg-5 p-2" style="background-color: var(--light-blue)">
        <div class="p-lg-5 p-2">
            <h4 class="text-white">Search for topics</h4>
            <div class="form-control-wrap">
                <div class="input-group input-group-lg">
                    <div class="input-group">
                        <input type="text" style="background-color: var(--white); border-right:none;" class="form-control"
                            placeholder="Need any assistance?" id="faq_keyword" name="faq_keyword">
                        <span class="input-group-text" style="background-color: var(--white);">
                            <a href="#">
                                <img class="icon-size" src={{ asset('media\Asset_Nav_Search.svg') }}>
                            </a>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="p-lg-5 py-5 px-2" style="background-color: var(--white);">
        <div class="row m-0">
            <div class="col-3 pe-lg-5 pe-3">
                @foreach ($FAQCategory as $key => $item)
                    <div class="pb-5">
                        <h4 class="d-inline-flex">
                            <a data-bs-toggle="collapse" href="#topic{{ $key }}" style="color: var(--light-blue);"
                                role="button" aria-expanded="false">
                                {{ $item }}
                            </a>
                        </h4>
                        <div class="pb-1" style="border-bottom: 2px solid var(--dark-blue)"></div>
                        <div class="collapse pt-3" id="topic{{ $key }}">
                            <h5><a href="#" style="color: var(--light-blue);">Domewing Account</a></h5>
                            <h5><a href="#" style="color: var(--light-blue);">Guidelines</a></h5>
                            <h5><a href="#" style="color: var(--light-blue);">Policies</a></h5>
                            <h5><a href="#" style="color: var(--light-blue);">Privacy</a></h5>
                            <h5><a href="#" style="color: var(--light-blue);">Additional Services</a></h5>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="col-9">
                <nav class="pb-4">
                    <ul class="breadcrumb breadcrumb-arrow">
                        <li class="breadcrumb-item"><a href="#">FAQ</a></li>
                        <li class="breadcrumb-item"><a href="#">General</a></li>
                        <li class="breadcrumb-item"><a href="#">Domewing Account</a></li>
                        <li class="breadcrumb-item active">Creating an Account</li>
                    </ul>
                </nav>

                <div class="p-4 rounded" style="background-color: var(--thin-blue)">
                    @foreach ($QnA as $key => $item)
                        <div class="pb-4">
                            <h4 class="d-inline-flex">
                                <a style="color: var(--dark-blue)" data-bs-toggle="collapse" href="#faq{{ $key }}"
                                    role="button" aria-expanded="false" aria-controls="faq{{ $key }}">
                                    {{ $item['question'] }}
                                </a>
                            </h4>
                            <div class="pb-1" style="border-bottom: 2px solid var(--dark-blue)"></div>
                            <div class="collapse pt-3" id="faq{{ $key }}">
                                <h5 class="ps-4" style="color: var(--dark-blue)">{{ $item['answer'] }}</h5>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
@endsection
