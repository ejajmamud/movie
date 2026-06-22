@forelse ($games as $key => $gameData)
    <h4>{{ showDateTime($key, 'd M Y') }}</h4>
    @foreach ($gameData as $game)
        @php
            $version = $game->versionName;
            if ($game->tournament->version == Status::FREE_VERSION) {
                $version = 'Watch';
            }
            if (auth()->check()) {
                if ($game->tournament->version == Status::FREE_VERSION) {
                    $version = 'Watch';
                } else {
                    $subscribedTournaments = auth()->user()->subscribedTournamentId();
                    if (count($subscribedTournaments) && in_array($tournament->id, $subscribedTournaments)) {
                        $version = 'Watch';
                    } else {
                        $subscribedGames = auth()->user()->subscribedMatchId();
                        $version = in_array($game->id, $subscribedGames) ? 'Watch' : $version;
                    }
                }
            }
        @endphp
        <div class="col-lg-3 col-md-4 col-sm-6 col-xsm-6">
            <a href="{{ route('game.detail', [$game->id, $game->slug]) }}" class="recent-match">
                <span class="recent-match__tag">{{ __($version) }}</span>
                <img src="{{ getImage(getFilePath('game') . '/thumb_' . $game->image, getFileThumb('game')) }}" alt="">
            </a>
        </div>
    @endforeach
@empty
    <div class="col-md-12">
        <div class="text-center">
            <h4 class="mb-4">@lang('Match not found yet!')</h4>
        </div>
    </div>
@endforelse