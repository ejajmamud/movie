@extends('Template::layouts.frontend')

@section('content')
    <section class="mt-80 mb-80">
        <div class="container">
            <div class="row gy-4 justify-content-center">
                <div class="{{ gs('control_socket') ? 'col-lg-8 pe-lg-5' : 'col-12' }}">
                    <div class="tv-detials">
                        <div class="main-video">
                            <video playsinline class="video-player"
                                poster="{{ getImage(getFilePath('television') . '/' . $tv->image, getFileSize('television')) }}">
                                <source src="{{ $tv->url }}">
                            </video>
                        </div>

                        <div class="tv-details-wrapper">
                            <div class="tv-details__content">
                                <div class="tv-details-channel">
                                    <div class="tv-details-channel__thumb">
                                        <img src="{{ getImage(getFilePath('television') . '/' . $tv->image, getFileSize('television')) }}"
                                            alt="">
                                    </div>
                                    <div class="tv-details-channel__content">
                                        <h5 class="tv-details-channel__title">{{ __($tv->title) }}</h5>
                                    </div>
                                </div>
                                <div class="tv-details__social-share">
                                    <ul
                                        class="post-share d-flex align-items-center justify-content-sm-end justify-content-start flex-wrap">
                                        <li class="caption">@lang('Share') : </li>

                                        <li data-bs-toggle="tooltip" data-bs-placement="top" title="Facebook">
                                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
                                                target="_blank"><i class="lab la-facebook-f"></i></a>
                                        </li>
                                        <li data-bs-toggle="tooltip" data-bs-placement="top" title="Linkedin">
                                            <a href="http://www.linkedin.com/shareArticle?mini=true&amp;url={{ urlencode(url()->current()) }}&amp;title={{ __($tv->title) }}&amp;summary={{ __($tv->description) }}"
                                                target="_blank"><i class="fab fa-linkedin-in"></i></a>
                                        </li>
                                        <li data-bs-toggle="tooltip" data-bs-placement="top" title="Twitter">
                                            <a href="https://twitter.com/intent/tweet?text={{ __(@$tv->title) }}%0A{{ url()->current() }}"
                                                target="_blank"><i class="lab la-twitter"></i></a>
                                        </li>
                                        <li data-bs-toggle="tooltip" data-bs-placement="top" title="Pinterest">
                                            <a href="http://pinterest.com/pin/create/button/?url={{ urlencode(url()->current()) }}&description={{ __(@$tv->title) }}&media={{ getImage(getFilePath('television') . '/' . $tv->image, getFileSize('television')) }}"
                                                target="_blank"><i class="lab la-pinterest"></i></a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <p class="tv-details__desc mt-4">{{ __($tv->description) }}</p>
                        </div>
                    </div>
                </div>
                @if (gs('control_socket'))
                    <div class="col-lg-4">
                        <span class="show--chat d-none"> @lang('Show Chat')</span>
                        <div class="mb-4">
                            <div class="chat">
                                <div class="chat__header">
                                    <h3 class="mb-0">@lang('Live Chat')</h3>
                                    <span class="hide--chat"><i class="las la-times"></i></span>
                                </div>
                                <div class="chat__body">
                                    <div id="live-tv-comments-container" class="comments-container">
                                        <!-- Comments will appear here -->
                                    </div>
                                </div>
                                <div class="chat__footer">
                                    <form id="live-tv-comment-form" class="chat__box">
                                        <div class="chat__box-left">
                                            <input id="live-tv-comment-input" data-emojiable="true" type="text"
                                                class="chat__box-input" placeholder="@lang('Chat...')">
                                            <span class="emoji chat__box-icon" id="emoji-button" data-emojiable="true">
                                                <i class="las la-smile"></i>
                                            </span>
                                        </div>
                                        <button id="live-tv-comment-push-btn" type="submit" class="chat__box-btn">
                                            <i class="las la-paper-plane"></i>
                                        </button>
                                    </form>
                                    <small class="note-text-info">
                                        <i class="las la-info-circle"></i>
                                        @lang('All messages you send will appear publicly')
                                    </small>
                                </div>


                                <div class="connection-overlay">
                                    <div class="connection-status">
                                        <span class="icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                class="lucide lucide-wifi-off">
                                                <path d="M12 20h.01" />
                                                <path d="M8.5 16.429a5 5 0 0 1 7 0" />
                                                <path d="M5 12.859a10 10 0 0 1 5.17-2.69" />
                                                <path d="M19 12.859a10 10 0 0 0-2.007-1.523" />
                                                <path d="M2 8.82a15 15 0 0 1 4.177-2.643" />
                                                <path d="M22 8.82a15 15 0 0 0-11.288-3.764" />
                                                <path d="m2 2 20 20" />
                                            </svg>
                                        </span>
                                        <span class="text">@lang('Connection lost. Trying to reconnect')...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
                <div class="col-lg-12">
                    <div class="tv-details__sidebar">
                        <div class="tv-details__header">
                            <h3 class="tv-details__sidebar-title">@lang('Other Tv Channels')</h3>
                        </div>
                        <ul class="tv-sidebar-list">
                            @foreach ($otherTvs as $otherTv)
                                <li class="tv-sidebar-list__item">
                                    <a class="tv-sidebar-list__link" href="{{ route('watch.tv', $otherTv->id) }}">
                                        <div class="tv-details-channel__thumb">
                                            <img src="{{ getImage(getFilePath('television') . '/' . $otherTv->image, getFileSize('television')) }}"
                                                alt="">
                                        </div>
                                        <div class="tv-details-channel__content">
                                            <h5 class="tv-details-channel__title">{{ __($otherTv->title) }}</h5>
                                            <p class="tv-details-channel__text">
                                                {{ __(strLimit($otherTv->description, 50)) }}</p>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('style-lib')
    <link rel="stylesheet" href="{{ asset('assets/global/css/plyr.min.css') }}">
@endpush

@push('script-lib')
    <script src="{{ asset('assets/global/js/plyr.min.js') }}"></script>
    <script src="{{ asset('assets/global/js/hls.min.js') }}"></script>

    <script src="{{ asset('assets/global/js/emoji.min.js') }}"></script>
@endpush


@push('script')
    <script>
        (function($) {
            "use strict";
            $(document).find('.plyr__controls').addClass('d-none');
            $(document).find('.ad-video').find('.plyr__controls').addClass('d-none');

            // Show/Hide Chat Functionality
            const chatDiv = $('.chat');
            const showChatButton = $('.show--chat');
            const hideChatButton = $('.hide--chat');
            showChatButton.on('click', function() {
                chatDiv.removeClass('d-none');
                hideChatButton.removeClass('d-none');
                showChatButton.addClass('d-none');
            });
            hideChatButton.on('click', function() {
                chatDiv.addClass('d-none');
                showChatButton.removeClass('d-none');
                hideChatButton.addClass('d-none');
            });


            document.addEventListener('DOMContentLoaded', () => {
                const video = document.querySelector('video');
                const source = video.currentSrc;
                const controls = [
                    'play',
                    'play-large',
                ];
                const player = new Plyr(video, {
                    ratio: '16:9',
                    autoPlay: true,
                });

                player.on('play', () => $('.plyr__controls').removeClass('d-none'));

                if (!Hls.isSupported()) {
                    video.src = source;
                } else {
                    const hls = new Hls();
                    hls.loadSource(source);
                    hls.attachMedia(video);
                    window.hls = hls;
                }
                it
                window.player = player;
            });

        })(jQuery)
    </script>

    @if (!empty(gs('control_socket')))
        <script>
            window.liveTvConfig = {
                liveTvId: `{{ request()->id ?? '' }}`,
                commentStoreRoute: `{{ route('live-tv.comments.store') }}`,
                commentGetRoute: `{{ route('live-tv.comments.get', ['liveTvId' => request()->id ?? '']) }}`,
                csrfToken: `{{ csrf_token() }}`,
                socketUri: `{{ gs('socket_appuri') }}`,
            };
        </script>
        <script src="{{ asset('assets/global/js/ws.js') }}"></script>
    @endif
@endpush


@push('style')
    <style>
        .plyr__poster {
            background-size: cover;
        }

        .user__name {
            color: #fff;
        }

        .message-item__wrapper {
            display: flex;
        }

        .message-item__profile img {
            border-radius: 50%;
            width: 35px;
        }


        .comment-content {
            margin-left: 10px;
            cursor: pointer;
        }

        .show--chat {
            cursor: pointer;
            color: hsl(var(--white));
            border: 1px solid #363a43;
            border-radius: 24px;
            padding: 7px 10px;
            width: 100%;
            text-align: center;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .show--chat:hover {
            background-color: #363a43;
            color: hsl(var(--white));
            display: flex;
            flex-direction: column;
        }

        .chat {
            height: 550px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            overflow: hidden;
            position: relative;
        }

        .chat__header {
            display: flex;
            align-content: center;
            justify-content: space-between;
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
        }

        .chat__header .hide--chat {
            font-size: 1.5rem;
            color: hsl(var(--white));
            cursor: pointer;
        }

        .chat__body {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
            scroll-behavior: smooth;
        }

        .chat__body::-webkit-scrollbar {
            width: 3px;
            height: 3px;
        }

        .chat__body::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.322);
            border: 0px solid transparent;
            border-radius: 10px;
        }

        .message-item {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .message-item:not(:last-child) {
            margin-bottom: 12px;
        }

        .message-item__thumb {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            overflow: hidden;
            flex-shrink: 0
        }

        .message-item__thumb img {
            object-fit: cover;
            width: 100%;
            height: 100%;
        }

        .message-item__content {
            flex: 1;
        }

        .chat__footer {
            padding: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.15);
        }

        .user__name,
        .message-item__text {
            font-size: 0.875rem;
            color: hsl(var(--white));
            cursor: default;
        }

        .user__name {
            color: hsl(var(--white) / 0.75)
        }

        .chat__box {
            display: flex;
            gap: 12px;
        }

        .chat__box-left {
            flex: 1;
            display: flex;
            gap: 6px;
            background-color: #363a43;
            border-radius: 6px;
        }

        .chat__box-input {
            width: 100%;
            flex: 1;
            background-color: transparent;
            border: 0px;
            outline: none;
            color: hsl(var(--white));
            padding-inline: 12px;
        }

        .chat__box-input::placeholder {
            color: #bfbfbf;
            font-weight: 500;
            font-size: 14px;
        }

        .chat__box-icon {
            flex-shrink: 0;
            color: hsl(var(--white));
            width: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            cursor: pointer;
        }

        .chat__box-btn {
            flex-shrink: 0;
            color: hsl(var(--white));
            background-color: transparent;
            font-size: 1.5rem;
        }

        .note-text-info {
            display: flex;
            align-items: center;
            gap: 4px;
            color: hsl(var(--white) / 0.5);
            font-weight: 400;
            margin-top: 4px;
        }

        /* Emoji */
        .emoji-picker {
            background: #0d0d31 !important;
            border: 1px solid #3E3E60 !important;
        }

        .emoji-picker__search {
            border: 1px solid #3E3E60 !important;
            background: transparent;
            color: #ffffff;
        }

        .emoji-picker__search::placeholder {
            color: #ffffff;
        }

        .emoji-picker__search-icon {
            top: 3px !important;
        }

        .emoji-picker__category-button {
            color: #ffffff !important;
        }

        .emoji-picker__category-button:hover {
            color: hsl(var(--base)) !important;
        }

        .emoji-picker__category-button.active {
            color: hsl(var(--base)) !important;
            border-bottom: 2px solid hsl(var(--base)) !important;
        }

        .emoji-picker__emojis {
            overflow-x: hidden !important;
        }

        .emoji-picker__preview {
            display: none !important;
        }

        .emoji-picker__emoji {
            color: #ffffff;
            font-size: 18px !important;
            line-height: 1 !important;
        }

        .emoji-picker__emoji:focus,
        .emoji-picker__emoji:hover {
            background: #3E3E60 !important;
        }

        .emoji-picker__emojis .emoji-picker__category-name {
            color: #ffffff !important;
            text-transform: capitalize !important;
            font-weight: 500 !important;
        }

        /* Emoji Scrollbar */
        .emoji-picker__emojis::-webkit-scrollbar {
            width: 5px;
        }

        .emoji-picker__emojis::-webkit-scrollbar-thumb {
            background-color: #3E3E60;
            border-radius: 5px;
        }

        .emoji-picker__emojis::-webkit-scrollbar-track {
            background-color: transparent;
        }

        .connection-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgb(33 32 32 / 20%);
            backdrop-filter: blur(4px);
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 0;
            visibility: hidden;
        }

        .connection-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .connection-status {
            text-align: center;
        }

        .connection-status .icon svg {
            color: #958b8b;
            height: 32px;
            width: 32px;
        }

        .connection-status .text {
            margin-top: 10px;
            color: #ddd;
            display: block;
            font-size: 0.875rem;
        }

        .movie-item-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
        }

        .movie-item {
            width: 100%;
        }

        #live-tv-comment-input,
        .emoji-picker,
        .emoji {
            font-family: 'Segoe UI Emoji', 'Apple Color Emoji', 'Noto Color Emoji', sans-serif;
        }
    </style>
@endpush
