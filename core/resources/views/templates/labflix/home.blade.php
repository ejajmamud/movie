@extends('Template::layouts.frontend')
@section('content')
    @php
        $banner_content = getContent('banner.content', true);
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

    <section class="hero">
        <div class="hero__slider">
            @foreach ($sliders as $slider)
                @if ($slider->caption_show != 1)
                    <div class="single-slide" id="slide-{{ $slider->id }}"
                        aria-hidden="{{ $slider->isActive ? 'false' : 'true' }}">
                        <a href="{{ route('watch', $slider->item->slug) }}">
                            <img src="{{ getImage(getFilePath('slider') . '/' . $slider->image) }}" alt="hero-image">
                        </a>
                    </div>
                @else
                    <div class="movie-slide bg_img"
                        data-background="{{ getImage(getFilePath('slider') . '/' . $slider->image) }}">
                        <div class="movie-slide__content">
                            <h6 class="movie-name" data-animation="fadeInUp" data-delay=".2s">{{ __($slider->item->title) }}
                            </h6>
                            <ul class="movie-meta justify-content-lg-start justify-content-center" data-animation="fadeInUp"
                                data-delay=".4s">
                                <li><i class="fas fa-star color--gold"></i> <span>({{ __($slider->item->ratings) }})</span>
                                </li>
                                <li><span>{{ __($slider->item->category->name) }}</span></li>
                            </ul>
                            <p data-animation="fadeInUp" data-delay=".7s">{{ __($slider->item->preview_text) }}</p>
                            <div class="btn-area justify-content-lg-start justify-content-center align-items-center mt-lg-5 mt-sm-3 mt-2"
                                data-animation="fadeInLeft" data-delay="1s">
                                @if (@$slider->item->is_trailer == Status::TRAILER && @$slider->item->item_type == Status::SINGLE_ITEM)
                                    <a class="video-btn justify-content-lg-start justify-content-center"
                                        href="{{ route('watch', $slider->item->slug) }}">
                                        <div class="icon"><i class="lar la-play-circle"></i></div>
                                        <span>@lang('Watch Trailer')</span>
                                    </a>
                                @else
                                    <a class="video-btn justify-content-lg-start justify-content-center"
                                        href="{{ route('watch', $slider->item->slug) }}">
                                        <div class="icon">
                                            <i class="lar la-play-circle"></i>
                                        </div>
                                        <span>@lang('Watch Now')</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </section>

    @include('Template::partials.short_reels')

    @include('Template::partials.today_games')

    <section class="section mt-80 mb-80" data-section="single1">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-header">
                        <h2 class="section-title">@lang('Featured Items')</h2>
                    </div>
                </div>
            </div><!-- row end -->
            <div class="movie-slider-one">
                @foreach ($featuredMovies as $featured)
                    <div class="movie-card" data-text="{{ $featured->versionName }}">
                        <div class="movie-card__thumb">
                            <img class="lazy-loading-img"
                                data-src="{{ getImage(getFilePath('item_portrait') . '/' . @$featured->image->portrait) }}"
                                src="{{ asset('assets/global/images/lazy.png') }}" alt="@lang('image')">
                            <a class="icon" href="{{ route('watch', $featured->slug) }}"><i
                                    class="lar la-play-circle"></i></a>
                        </div>
                        <div class="movie-card__content">
                            <h6><a
                                    href="{{ route('watch', $featured->slug) }}">{{ __(short_string($featured->title, 17)) }}</a>
                            </h6>
                            <ul class="movie-card__meta">
                                <li><i class="far fa-eye color--primary"></i> <span>{{ numFormat($featured->view) }}</span>
                                </li>
                                <li><i class="fas fa-star color--gold"></i> <span>({{ $featured->ratings }})</span></li>
                            </ul>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <div class="sections"></div>
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
                    $('.loading').removeClass('loader');
                    return false;
                }

                $('.loading').addClass('loader');
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
                                $('.loading').removeClass('loader');
                                $('.footer').removeClass('d-none');
                                return false;
                            }
                            $('.loading').removeClass('loader');
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
    </script>
@endpush

@push('style-lib')
    <link rel="stylesheet" href="{{ asset('assets/global/css/plyr.min.css') }}">
@endpush

@push('script-lib')
    <script src="{{ asset('assets/global/js/plyr.min.js') }}"></script>
    <script src="https://cdn.plyr.io/3.6.8/plyr.polyfilled.js"></script>
@endpush

@push('script')
    <script>
        "use strict";
        const controls = [
            'play-large',
        ];

        let players = Plyr.setup('.video-player', {
            controls,
            autoplay: false,
            ratio: '9:16'
        });

        if (players.length > 0) {
            players.forEach((player, index) => {
                player.on('mouseenter', () => {
                    players.forEach((p, i) => {
                        if (i !== index) {
                            p.pause();
                        }
                    });
                    player.muted = true;
                    player.play().catch(error => {
                        console.log('Playback prevented by the browser.', error);
                    });
                });

                player.on('mouseleave', () => {
                    player.pause();
                });
            });
        }
    </script>
@endpush
