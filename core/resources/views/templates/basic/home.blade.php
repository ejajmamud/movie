@extends('Template::layouts.frontend')
@section('content')
    @php
        $bannerContent = getContent('banner.content', true);
        $games = App\Models\Game::active()->whereDate('start_time', today())->limit(10)->get();
        $reels = App\Models\Reel::orderBy('id', 'desc')->limit(10)->get();
    @endphp

    @if ($advertise && !auth()->id())
        <div class="modal" id="adModal">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-body position-relative p-0">
                        <div class="ads-close-btn position-absolute">
                            <button class="btn-close btn-close-white" data-bs-dismiss="modal" type="button"
                                aria-label="Close"><i class="las la-times"></i></button>
                        </div>
                        <a href="{{ $advertise->content->link }}" target="_blank">
                            <img src="{{ getImage(getFilePath('ads') . '/' . @$advertise->content->image) }}"
                                alt="@lang('image')">
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif


    <section class="banner-section bg-overlay-black bg_img"
        data-background="{{ getImage('assets/images/frontend/banner/' . @$bannerContent->data_values->background_image, '1778x755') }}">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-xl-6 col-lg-6">
                    <div class="banner-content">
                        <span class="sub-title">{{ __(@$bannerContent->data_values->heading) }}</span>
                        <h1 class="title text-white">{{ __(@$bannerContent->data_values->sub_heading) }}</h1>
                        @guest
                            <div class="banner-btn">
                                <a class="btn btn--base"
                                    href="{{ @$bannerContent->data_values->button_1_link }}">{{ __(@$bannerContent->data_values->button_1) }}</a>
                                <a class="btn btn-outline--base" href="{{ @$bannerContent->data_values->button_2_link }}"><i
                                        class="las la-plus"></i> {{ __(@$bannerContent->data_values->button_2) }}</a>
                            </div>
                        @endguest
                    </div>
                </div>
                <div class="col-xl-6 col-lg-6">
                    <div class="banner-slider">
                        <div class="swiper-wrapper">
                            @foreach ($sliders as $slider)
                                <div class="swiper-slide">
                                    <div class="movie-item">
                                        <div class="movie-thumb">
                                            <img class="lazy-loading-img"
                                                data-src="{{ getImage(getFilePath('item_portrait') . '/' . @$slider->item->image->portrait) }}"
                                                src="{{ getImage(getFilePath('item_portrait') . '/' . @$slider->item->image->portrait) }}"
                                                alt="movie">
                                            <div class="movie-thumb-overlay">
                                                <a class="video-icon" href="{{ route('watch', @$slider->item->slug) }}"><i
                                                        class="fas fa-play"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="swiper-pagination"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('Template::partials.short_reels')

    @include('Template::partials.today_games')

    <section class="movie-section my-80 section" data-section="single1">
        <div class="container">
            <div class="row justify-content-center align-items-center mb-30-none">
                <div class="col-xl-3 col-lg-4 col-md-12 col-sm-12 mb-30">
                    <div class="movie-section-header-wrapper">
                        <div class="movie-section-header">
                            <h2 class="title">@lang('Featured Movies to Watch Now')</h2>
                            <p>@lang('Most watched movies by days')</p>
                        </div>
                        <div class="movie-slider-arrow">
                            <div class="slider-prev">
                                <i class="fas fa-angle-left"></i>
                            </div>
                            <div class="slider-next">
                                <i class="fas fa-angle-right"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-9 col-lg-8 col-md-12 col-sm-12 mb-30">
                    <div class="movie-slider">
                        <div class="swiper-wrapper">
                            @foreach ($featuredMovies as $featured)
                                <div class="swiper-slide">
                                    <div class="movie-item">
                                        <div class="movie-thumb">
                                            <img class="lazy-loading-img"
                                                data-src="{{ getImage(getFilePath('item_portrait') . $featured->image->portrait) }}"
                                                src="{{ getImage(getFilePath('item_portrait') . $featured->image->portrait) }}"
                                                alt="movie">
                                            <span class="movie-badge">{{ __($featured->versionName) }}</span>
                                            <div class="movie-thumb-overlay">
                                                <a class="video-icon" href="{{ route('watch', $featured->slug) }}"><i
                                                        class="fas fa-play"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="sections"></div>
    <div class="custom_loading"></div>
@endsection


@push('script')
    <script>
        "use strict";

        $(document).ready(function() {
            setTimeout(() => {
                $("#adModal").modal('show');
            }, 2000);
        });

        var send = 0;
        $(window).scroll(function() {
            if ($(window).scrollTop() + $(window).height() > $(document).height() - 60) {
                if ($('.section').hasClass('last-item')) {
                    $('.custom_loading').removeClass('loader-area');
                    return false;
                }

                $('.custom_loading').addClass('loader-area');
                setTimeout(function() {
                    if (send == 0) {
                        send = 1;
                        var sec = $('.section').last().data('section');
                        var url = "{{ route('get.section') }}";
                        var data = {
                            sectionName: sec
                        };
                        $.get(url, data, function(response) {
                            if (response == 'end') {
                                $('.section').last().addClass('last-item');
                                $('.custom_loading').removeClass('loader-area');
                                $('.footer').removeClass('d-none');
                                return false;
                            }
                            $('.custom_loading').removeClass('loader-area');
                            $('.sections').append(response);
                            send = 0;
                        });

                    }
                }, 1000)
            }
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
        });



        // Swiper JS
        var swiper = new Swiper(".mySwiper", {
            slidesPerView: 4,
            spaceBetween: 24,
            pagination: false,
            loop: true,
            autoplay: {
                delay: 5000,
            },
            breakpoints: {
                1399: {
                    slidesPerView: 4,
                    spaceBetween: 24,
                },
                1199: {
                    slidesPerView: 4,
                    spaceBetween: 20,
                },
                991: {
                    slidesPerView: 3,
                    spaceBetween: 20,
                },
                767: {
                    slidesPerView: 3,
                    spaceBetween: 16,
                },
                575: {
                    slidesPerView: 2,
                    spaceBetween: 16,
                },
                400: {
                    slidesPerView: 1,
                    spaceBetween: 12,
                },
            },
        });
    </script>
@endpush
