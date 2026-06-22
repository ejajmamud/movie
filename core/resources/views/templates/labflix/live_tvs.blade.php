@extends('Template::layouts.frontend')

@section('content')
    <section class="shorts-section mt-80 mb-80">
        <div class="container">
            <ul class="nav nav-pills event--tab mb-0" id="pills-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#pills-home"
                        type="button" role="tab" aria-controls="pills-home" aria-selected="true"> All Channels
                    </button>
                </li>
                @foreach ($channelCategories as $key => $category)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-{{ $key }}-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-{{ $key }}" type="button" role="tab"
                            aria-controls="pills-{{ $key }}" aria-selected="false"> {{ __($category->name) }}
                        </button>
                    </li>
                @endforeach
            </ul>
            <div class="tab-content" id="pills-tabContent">
                <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab"
                    tabindex="0">
                    @foreach ($channelCategories as $category)
                        @php
                            $eligable = false;
                            if (auth()->check()) {
                                $subscribedChannels = auth()->user()->subscribedChannelId();
                                $eligable = in_array($category->id, $subscribedChannels) ? true : false;
                            }
                        @endphp
                        <div class="tv-live">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                <div class="d-flex flex-wrap gap-2 gap-md-3">
                                    <h5 class="channel-title">{{ __($category->name) }} @lang('Channels')</h5>
                                    @if (!$eligable)
                                        <button class="btn btn--light btn--sm channelSubscribeBtn"
                                            data-id="{{ $category->id }}" data-price="{{ showAmount($category->price) }}">
                                            <span class="icon"><i class="fas fa-rocket fa-lg"></i></span>
                                            @lang('Subscribe')
                                        </button>
                                    @endif
                                </div>
                            </div>
                            <div class="tv-card-wrapper">
                                @foreach ($category->channels as $channel)
                                    <a href="{{ route('watch.tv', $channel->id) }}" class="tv-channel" alt="tv-channel">
                                        <div class="tv-channel__thumb">
                                            <span><img
                                                    src="{{ getImage(getFilePath('television') . '/' . $channel->image, getFileSize('television')) }}"
                                                    class="w-100"></span>
                                            <span class="play-btn-icon">
                                                <i class="las la-play"></i>
                                            </span>
                                        </div>
                                        <div class="tv-channel__content">
                                            <h6 class="tv-channel__title"> {{ __($channel->title) }} </h6>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
                @foreach ($channelCategories as $key => $category)
                    <div class="tab-pane fade" id="pills-{{ $key }}" role="tabpanel"
                        aria-labelledby="pills-{{ $key }}-tab" tabindex="0">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                            @php
                                $eligable = false;
                                if (auth()->check()) {
                                    $subscribedChannels = auth()->user()->subscribedChannelId();
                                    $eligable = in_array($category->id, $subscribedChannels) ? true : false;
                                }
                            @endphp
                            <div class="d-flex flex-wrap gap-2 gap-md-3">
                                <h4 class="channel-title">{{ __($category->name) }} @lang('Channels')</h4>
                                @if (!$eligable)
                                    <button class="btn btn--light btn--sm channelSubscribeBtn"
                                        data-id="{{ $category->id }}" data-price="{{ showAmount($category->price) }}">
                                        <span class="icon"><i class="fas fa-rocket fa-lg"></i></span>
                                        @lang('Subscribe')
                                    </button>
                                @endif
                            </div>
                        </div>
                        <div class="tv-card-wrapper">
                            @foreach ($category->channels as $channel)
                                <a href="{{ route('watch.tv', $channel->id) }}" class="tv-channel" alt="tv-channel">
                                    <div class="tv-channel__thumb">
                                        <span><img
                                                src="{{ getImage(getFilePath('television') . '/' . $channel->image, getFileSize('television')) }}"
                                                class="w-100"></span>
                                        <span class="play-btn-icon">
                                            <i class="las la-play"></i>
                                        </span>
                                    </div>
                                    <div class="tv-channel__content">
                                        <h6 class="tv-channel__title"> {{ __($channel->title) }} </h6>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    <div class="modal alert-modal" id="channelModal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form method="POST">
                    @csrf
                    <div class="modal-body">
                        <span class="alert-icon"><i class="fas fa-question-circle"></i></span>
                        <p class="modal-description">@lang('Confirmation Alert!')</p>
                        <p class="modal--text">@lang('Are you sure to subscribe to this channel group?')</p>
                        <p class="modal--text">@lang('Monthly subscription price is ') <span class="subscription-price"></span></p>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn--dark btn--sm" data-bs-dismiss="modal"
                            type="button">@lang('No')</button>
                        <button class="btn btn--base btn--sm" type="submit">@lang('Yes')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('style')
    <style>
        .tv-card__thumb {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            overflow: hidden;
        }

        @media (max-width: 1199px) {
            .tv-card__thumb {
                width: 106px;
                height: 106px;
            }
        }

        @media (max-width: 767px) {
            .tv-card__thumb {
                width: 93px;
                height: 93px;
            }
        }

        @media (max-width: 575px) {
            .tv-card__thumb {
                width: 85px;
                height: 85px;
            }
        }

        .tv-card-wrapper {
            display: flex;
            justify-content: flex-start;
            flex-wrap: wrap;
            gap: 12px;
        }

        .tv-card {
            display: flex;
            justify-content: center;
        }
    </style>
@endpush
@push('script')
    <script>
        (function($) {
            "use strict";
            $('.channelSubscribeBtn').on('click', function(e) {
                e.preventDefault();
                let modal = $("#channelModal");
                modal.find('.subscription-price').text($(this).data('price'));
                modal.find('form').attr('action',
                    `{{ route('user.subscribe.channel', '') }}/${$(this).data('id')}`)
                modal.modal('show');
            });
        })(jQuery)
    </script>
@endpush
