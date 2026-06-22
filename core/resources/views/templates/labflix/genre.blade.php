@extends('Template::layouts.frontend')
@section('content')
    @forelse ($genreItems as $key => $items)
        <section class="section mt-80 mb-80" data-section="single1">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="section-header">
                            <h2 class="section-title">{{ $key }}</h2>
                        </div>
                    </div>
                </div>
                <div class="movie-slider-one">
                    @foreach ($items as $item)
                        <div class="movie-card" data-text="{{ $item->versionName }}">
                            <div class="movie-card__thumb">
                                <img class="lazy-loading-img"
                                    data-src="{{ getImage(getFilePath('item_portrait') . '/' . @$item->image->portrait) }}"
                                    src="{{ asset('assets/global/images/lazy.png') }}" alt="@lang('image')">
                                <a class="icon" href="{{ route('watch', $item->slug) }}"><i
                                        class="lar la-play-circle"></i></a>
                            </div>
                            <div class="movie-card__content">
                                <h6><a href="{{ route('watch', $item->slug) }}">{{ __(short_string($item->title, 17)) }}</a>
                                </h6>
                                <ul class="movie-card__meta">
                                    <li><i class="far fa-eye color--primary"></i> <span>{{ numFormat($item->view) }}</span>
                                    </li>
                                    <li><i class="fas fa-star color--gold"></i> <span>({{ $item->ratings }})</span></li>
                                </ul>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @empty
        <div class="mt-80 mb-80">
            <div class="row mb-none-30">
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-xs-12 mb-30 mx-auto">
                    <img src="{{ asset(activeTemplate(true) . 'images/no-results.png') }}" alt="@lang('image')">
                </div>
            </div>
        </div>
    @endforelse
@endsection

@push('script')
    <script>
        let images = document.querySelectorAll('.lazy-loading-img');

        function preloadImage(image) {
            const src = image.getAttribute('data-src');
            image.src = src;
        }

        let imageOptions = {
            threshold: 1,
            border: "5px solid green",
        };

        const imageObserver = new IntersectionObserver((entries, imageObserver) => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) {
                    return;
                } else {
                    preloadImage(entry.target)
                    imageObserver.unobserve(entry.target)
                }
            })
        }, imageOptions)

        images.forEach(image => {
            imageObserver.observe(image)
        });
    </script>
@endpush
