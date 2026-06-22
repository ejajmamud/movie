@if (!blank($reels))
    <section class="shorts-section my-80">
        <div class="container-fluid">
            <div class="seciton-title">
                <div class="seciton-title__left">
                    <h3 class="seciton-title__heading mb-0">@lang('Shorts')</h3>
                    <a href="{{ route('short.videos') }}" class="seciton-title__link">
                        @lang('View All') <span class="fs-16"> <i class="las la-arrow-right"></i> </span>
                    </a>
                </div>
                <div class="seciton-title__right">
                    <div class="Short-slider-arrow">
                        <div class="slider-prev">
                            <i class="fas fa-angle-left"></i>
                        </div>
                        <div class="slider-next">
                            <i class="fas fa-angle-right"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="shorts-slider overflow-hidden">
                <div class="swiper-wrapper">
                    @foreach ($reels as $reel)
                        <div class="swiper-slide">
                            <a href="{{ route('short.videos', $reel->id) }}" class="shorts-items">
                                <div class="video-container">
                                    <video class="video-player plyr-video" playsinline controls>
                                        <source src="{{ $reel->reelVideo }}" type="video/mp4">
                                    </video>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endif

@push('style')
    <style>
        .plyr__control--overlaid,
        .plyr--video .plyr__control:focus-visible,
        .plyr--video .plyr__control:hover,
        .plyr--video .plyr__control[aria-expanded="true"] {
            background: transparent;
            color: hsl(var(--base));
        }
    </style>
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
