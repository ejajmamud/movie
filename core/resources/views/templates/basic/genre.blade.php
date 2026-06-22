@extends('Template::layouts.frontend')
@section('content')
    <section class="my-80">
        <div class="container">
            @forelse ($genreItems as $key => $items)
                <div class="{{ $loop->index ? 'my-80' : '' }}">
                    <div class="row>
                        <div class="col-xl-12">
                        <div class="section-header">
                            <h2 class="section-title">{{ __($key) }}</h2>
                        </div>
                    </div>
                    <div class="row justify-content-center mb-30-none">
                        @foreach ($items as $item)
                            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-xs-6 mb-30">
                                <div class="movie-item">
                                    <div class="movie-thumb">
                                        <img class="lazy-loading-img"
                                            data-src="{{ getImage(getFilePath('item_portrait') . '/' . $item->image->portrait) }}"
                                            src="{{ asset('assets/global/images/lazy.png') }}" alt="movie">
                                        <span class="movie-badge">{{ $item->versionName }}</span>
                                        <div class="movie-thumb-overlay">
                                            <a class="video-icon" href="{{ route('watch', $item->slug) }}"><i
                                                    class="fas fa-play"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="row justify-content-center mb-30-none">
                    <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12 col-xs-12 mb-30">
                        <img src="{{ asset(activeTemplate(true) . 'images/no-results.png') }}" alt="">
                    </div>
                </div>
            @endforelse
        </div>
    </section>
@endsection


@push('script')
    <script>
        (function($) {
            "use strict";
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
        })(jQuery);
    </script>
@endpush
