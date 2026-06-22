@if (!blank($games))
    <section class="recent-match-section">
        <div class="container-fluid">
            <div class="swiper mySwiper overflow-hidden">
                <div class="swiper-wrapper">
                    @foreach ($games as $game)
                        <div class="swiper-slide">
                            <a href="{{ route('game.detail', [$game->id, $game->slug]) }}" class="recent-match">
                                <span class="recent-match__tag">{{ __($game->versionName) }}</span>
                                <img src="{{ getImage(getFilePath('game') . '/thumb_' . $game->image, getFileThumb('game')) }}" alt="">
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endif
