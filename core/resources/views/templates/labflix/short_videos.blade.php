@extends('Template::layouts.app')
@section('app')
    <a class="back-btn" href="{{ route('home') }}" class="text--danger">
        <i class="las la-times"></i>
    </a>

    <div class="reel-slider">
        <div class="reel-slider-arrow">
            <div class="slider-prev">
                <i class="fas fa-angle-up"></i>
            </div>
            <div class="slider-next">
                <i class="fas fa-angle-down"></i>
            </div>
        </div>

        <div class="swiper-wrapper">
            @include('Template::partials.reels')
        </div>
    </div>
@endsection


@push('style')
    <style>
        .short-reel-wrapper {
            height: 100vh;
            display: flex;
            align-items: center;
            gap: 24px;
            justify-content: center;
        }

        .short-reel {
            height: calc(100vh - 100px);
            max-width: 490px;
            width: 100%;
            position: relative;
        }

        .reel-content {
            position: absolute;
            width: 100%;
            left: 0;
            bottom: 50px;
            padding: 0 16px;
            background: -webkit-linear-gradient(90deg, hsl(var(--black) / .05) 0%, hsl(var(--white) / .0) 50%);
        }

        .reel-content__title {
            font-weight: 500;
            margin-bottom: 6px;
        }

        .reel-content__description {
            color: #fff;
            font-size: 14px;
            line-height: 1.4;
        }

        .short-reel .plyr--video {
            height: 100%;
        }

        .reel-action {
            flex-shrink: 0;
        }

        .reel-action-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
            justify-content: flex-end;
            height: 100%;
        }

        .reel-action-item {
            text-align: center;
        }

        .reel-action-icon {
            height: 48px;
            width: 48px;
            display: grid;
            place-content: center;
            font-size: 24px;
            background-color: hsl(var(--white) / .1);
            border-radius: 50%;
            margin: 0 auto 12px;
            cursor: pointer;
        }

        .reel-action-icon.active {
            background-color: hsl(var(--white));
            color: hsl(var(--black))
        }

        .reel-action-title {
            font-weight: 600;
            display: block;
            line-height: 1;
            color: hsl(var(--white));
        }

        .reel-slider {
            height: 100vh;
            overflow: hidden;
        }

        .reel-slider-arrow {
            position: fixed;
            z-index: 999;
            top: 50%;
            transform: translateY(-50%);
            right: 50px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;

        }

        .reel-slider-arrow .slider-next {
            left: 0 !important;
        }

        .back-btn {
            position: fixed;
            top: 32px;
            right: 32px;
            display: grid;
            place-content: center;
            background-color: hsl(var(--white) / .1);
            color: hsl(var(--white));
            height: 48px;
            width: 48px;
            border-radius: 50%;
            z-index: 999;
            font-size: 20px;
            transition: all 0.3s linear;
        }

        .back-btn:hover {
            background-color: hsl(var(--white) / .3);
        }

        @media (max-width: 575px) {
            .back-btn {
                display: none;
            }
        }

        @media (max-width: 991px) {
            .short-reel {
                max-width: 420px;
            }

            .reel-action-icon {
                height: 40px;
                width: 40px;
                font-size: 22px;
            }

            .reel-action-title {
                font-size: 14px;
                font-weight: 500;
            }

            .reel-slider-arrow {
                top: auto;
                transform: unset;
                bottom: 50px;
            }
        }

        @media (max-width: 767px) {
            .short-reel {
                max-width: 380px;
            }
        }

        @media (max-width: 575px) {
            .reel-slider-arrow {
                display: none;
            }

            .short-reel {
                max-width: 320px;
            }

            .short-reel-wrapper {
                padding-inline: 16px;
                gap: 16px;
            }
        }

        @media (max-width: 425px) {
            .short-reel {
                max-width: 100%;
            }

            .reel-action {
                position: absolute;
                top: 50%;
                transform: translateY(-50%);
                right: 24px;
            }

            .reel-action-icon {
                height: 36px;
                width: 36px;
                font-size: 32px;
                background-color: rgba(0, 0, 0, 0.4);
                font-size: 24px;
            }

            .reel-action-icon.active {
                background-color: transparent;
                color: hsl(var(--base));
            }

            .reel-action-title {
                font-weight: 600;
            }

            .reel-action-list {
                gap: 32px;
            }
        }

        body {
            min-height: 100vh !important;
        }
    </style>
@endpush

@push('style-lib')
    <link rel="stylesheet" href="{{ asset('assets/global/css/plyr.min.css') }}">
@endpush

@push('script-lib')
    <script src="{{ asset('assets/global/js/plyr.min.js') }}"></script>
@endpush
@push('script')
    <script>
        "use strict";

        let userLikesId = @json($userLikesId);
        let userUnLikesId = @json($userUnLikesId);
        let userListId = @json($userListId);
        let lastId = Number("{{ @$lastId }}");

        function plyr() {
            const controls = [
                'play-large',
                'play',
                'progress',
                'volume',
            ];
            var player = Plyr.setup('.video-player', {
                controls,
                muted: false,
                autoplay: true,
                ratio: '9:16'
            });
            player.map((p) => p.volume = 0.5)
        }

        plyr();

        var swiper = new Swiper(".reel-slider", {
            slidesPerView: 1,
            direction: 'vertical',
            speed: 1000,
            mousewheel: true,
            allowTouchMove: true,
            navigation: {
                nextEl: ".slider-next",
                prevEl: ".slider-prev",
            },
            on: {
                init: function() {
                    setupVideoEndListener();
                },
                slideChange: function() {
                    let currentSlide = swiper.slides[swiper.activeIndex];
                    let id = Number(currentSlide.getAttribute('data-id'));
                    document.querySelectorAll('.video-player').forEach(player => {
                        player.plyr.pause();
                    });
                    let videoPlayer = currentSlide.querySelector('.video-player');
                    if (videoPlayer) {
                        videoPlayer.plyr.play();
                    }
                    videoPlayer.addEventListener('ended', setupVideoEndListener(videoPlayer));

                    if (lastId == id) {
                        loadMoreSlides(id);
                    }
                }
            }
        });

        function setupVideoEndListener(videoPlayer = null) {
            let videoElement = document.querySelector('.plyr-video');
            if (videoPlayer) {
                videoElement = videoPlayer;
            }
            if (videoElement) {
                videoElement.removeEventListener('ended', slideNextOnVideoEnd);
                videoElement.addEventListener('ended', slideNextOnVideoEnd);
            }

            function slideNextOnVideoEnd() {
                swiper.slideNext();
            }
        }

        function loadMoreSlides(id) {
            $.ajax({
                type: "GET",
                url: "{{ route('short.videos') }}",
                data: {
                    lastId: lastId
                },
                success: function(response) {
                    if (response.error) {
                        return;
                    }
                    $('.swiper-wrapper').append(response.data);
                    plyr();
                    swiper.update();
                    userLikesId = response.userLikesId;
                    userUnLikesId = response.userUnLikesId;
                    userListId = response.userListId;
                    lastId = response.lastId;
                }
            });
        }


        let AUTH_USER = Number("{{ auth()->id() }}");
        $(document).on('click', '.likeBtn', function(e) {
            e.preventDefault();
            if (!AUTH_USER) {
                notify('error', 'Authentication is required');
                return;
            }
            insertReaction($(this), 'likes');
        });

        $(document).on('click', '.unlikeBtn', function(e) {
            e.preventDefault();
            if (!AUTH_USER) {
                notify('error', 'Authentication is required');
                return;
            }
            insertReaction($(this), 'unlikes');
        });

        function insertReaction(element, type) {
            let btn = element;
            let id = btn.data('id');
            let reelId = id;
            $.ajax({
                type: "POST",
                url: "{{ route('user.reel.like') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: id,
                    type: type
                },
                success: function(response) {
                    if (response.error) {
                        notify('error', response.error);
                        return;
                    }

                    btn.addClass('active');
                    if (type == 'likes') {
                        btn.parent().parent().find('.unlikeBtn').removeClass('active');
                        userUnLikesId = userUnLikesId.filter(element => element !== reelId);
                        userLikesId.push(reelId);
                    } else {
                        userLikesId = userLikesId.filter(element => element !== reelId);
                        userUnLikesId.push(reelId);
                        btn.parent().parent().find('.likeBtn').removeClass('active');
                    }
                }
            });
        }

        $(document).on('click', '.myListBtn', function(e) {
            e.preventDefault();
            if (!AUTH_USER) {
                notify('error', 'Authentication is required');
                return;
            }
            let listBtn = $(this);
            let listBtnId = listBtn.data('id');

            $.ajax({
                type: "POST",
                url: "{{ route('user.reel.list') }}",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: listBtnId,
                },
                success: function(response) {
                    if (response.error) {
                        notify('error', response.error);
                        return;
                    }
                    if (response.type == 'add') {
                        listBtn.addClass('active');
                        userListId.push(listBtnId);
                    } else {
                        listBtn.removeClass('active');
                        userListId = userListId.filter(element => element !== listBtnId);
                    }
                    notify('success', response.success);
                }
            });
        });
    </script>
@endpush
