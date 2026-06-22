@extends('Template::layouts.frontend')
@section('content')
    <section class="match-details-banner-section bg_img"
        data-background="{{ getImage(getFilePath('game') . '/' . $game->image, getFileSize('game')) }}">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="movie-item">
                        <div class="match-details-banner-buttons">
                            @if ($watchEligible)
                                <a href="{{ route('watch.game', [$game->id, $game->slug]) }}" class="btn btn--base"><i
                                        class="far fa-play-circle"></i> @lang('Watch Now')</a>
                            @else
                                <button class="btn btn--base gamePurchaseBtn" type="button"><i class="fas fa-lock"></i>
                                    @lang('Subscribe Now')</button>
                            @endif
                            <button type="button" class="btn btn--white shareBtn"><i
                                    class="fas fa-share-alt text-dark"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="recent-match-section section--bg ptb-80">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <ul class="event--tab nav nav-pills" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="pills-details-tab" data-bs-toggle="pill"
                                data-bs-target="#pills-details" type="button" role="tab" aria-controls="pills-details"
                                aria-selected="true">@lang('Details')</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pills-match-tab" data-bs-toggle="pill"
                                data-bs-target="#pills-match" type="button" role="tab" aria-controls="pills-match"
                                aria-selected="false">@lang('Matches')</button>
                        </li>
                    </ul>
                    <div class="tab-content" id="pills-tabContent">
                        <div class="tab-pane fade show active" id="pills-details" role="tabpanel"
                            aria-labelledby="pills-details-tab" tabindex="0">
                            <div class="row">
                                <div class="col-xl-7 col-lg-9">
                                    <div class="match-details-content">
                                        <h2 class="match-details-content__title">{{ __(@$game->teamOne->name) }}
                                            @lang('VS') {{ __(@$game->teamTwo->name) }}</h2>
                                        <p class="match-details-content__desc">
                                            {{ __($game->details) }}
                                        </p>
                                        <ul class="match-details-content__list">
                                            <li>
                                                <span class="title">@lang('Release Date')</span>
                                                <span
                                                    class="desc">{{ showDateTime($game->start_time, 'd M Y h:i A') }}</span>
                                            </li>
                                            <li>
                                                <span class="title">@lang('Tournament')</span>
                                                <span class="desc">{{ __(@$game->tournament->name) }}</span>
                                            </li>
                                            <li>
                                                <span class="title">@lang('Season')</span>
                                                <span class="desc">{{ $game->tournament->season }}</span>
                                            </li>
                                            <li>
                                                <span class="title">@lang('Price')</span>
                                                <span class="desc">{{ showAmount($game->price) }}</span>
                                            </li>
                                        </ul>
                                        @php
                                            $policyPages = getContent('policy_pages.element', false, null, true);
                                        @endphp
                                        <span class="match-details-content__desc sm">@lang('By clicking "Watch Now" you acknowledge and accept our terms')
                                            @foreach ($policyPages as $policy)
                                                <a href="{{ route('policy.pages', $policy->slug) }}"
                                                    target="_blank">{{ __(@$policy->data_values->title) }}</a>
                                                {{ $loop->last ? '' : ',' }}
                                            @endforeach
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="pills-match" role="tabpanel" aria-labelledby="pills-match-tab"
                            tabindex="0">
                            <div class="row gy-3">
                                @foreach ($relatedGames as $relatedGame)
                                    <div class="col-lg-3 col-md-4 col-sm-6 col-xsm-6">
                                        <a href="{{ route('game.detail', [$relatedGame->id, $relatedGame->slug]) }}"
                                            class="recent-match">
                                            <span class="recent-match__tag">{{ __($relatedGame->versionName) }}</span>
                                            <img src="{{ getImage(getFilePath('game') . '/thumb_' . $relatedGame->image, getFileThumb('game')) }}"
                                                alt="">
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="modal alert-modal" id="gamePurchaseModal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form action="{{ route('user.subscribe.game', $game->id) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <span class="alert-icon"><i class="fas fa-question-circle"></i></span>
                        <p class="modal-description">@lang('Confirmation Alert!')</p>
                        <p class="modal--text">@lang('Are you sure to subscribe this game?')</p>
                        <p class="modal--text">@lang('The subscription price is ') {{ showAmount($game->price) }}</p>
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

    <div class="modal custom--modal-two" id="shareModal" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"> @lang('Share') </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span class="close-icon"> <i class="las la-times"></i> </span>
                    </button>
                </div>
                <div class="modal-body">
                    <ul class="social-list">
                        <li class="social-list__item"><a target="_blank"
                                href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
                                class="social-list__link facebook"><i class="fab fa-facebook-f"></i></a> </li>
                        <li class="social-list__item"><a target="_blank"
                                href="https://twitter.com/intent/tweet?text={{ __(@$game->slug) }}%0A{{ url()->current() }}"
                                class="social-list__link twitter"> <i class="fab fa-twitter"></i></a></li>
                        <li class="social-list__item"><a target="_blank"
                                href="http://www.linkedin.com/shareArticle?mini=true&amp;url={{ urlencode(url()->current()) }}&amp;title={{ __($game->slug) }}&amp;summary={{ __($game->details) }}"
                                class="social-list__link linkedin"> <i class="fab fa-linkedin-in"></i></a></li>
                        <li class="social-list__item"><a target="_blank"
                                href="http://pinterest.com/pin/create/button/?url={{ urlencode(url()->current()) }}&description={{ __(@$game->slug) }}&media={{ getImage(getFilePath('game') . '/' . $game->image, getFileSize('game')) }}"
                                class="social-list__link pinterest"> <i class="fab fa-pinterest-p"></i></a></li>
                    </ul>
                    <div class="bar">
                        <input type="text" class="share-link"
                            value="{{ route('game.detail', [$game->id, $game->slug]) }}">
                        <button class="btn btn--base btn--sm pill"> @lang('Copy') </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style')
    <style>
        .recent-match-section {
            padding-bottom: 80px !important;
        }
    </style>
@endpush
@push('script')
    <script>
        (function($) {
            "use strict";
            $('.gamePurchaseBtn').on('click', function(e) {
                var modal = $('#gamePurchaseModal');
                modal.modal('show');
            });
            $('.shareBtn').on('click', function(e) {
                var modal = $('#shareModal');
                modal.modal('show');
            });
            $('.pill').on('click', function() {
                var copyText = document.getElementsByClassName("share-link");
                copyText = copyText[0];
                copyText.select();
                copyText.setSelectionRange(0, 99999);
                document.execCommand("copy");
                navigator.clipboard.writeText(copyText.value);
            });
        })(jQuery)
    </script>
@endpush
