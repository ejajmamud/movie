@extends('Template::layouts.frontend')
@section('content')
    <section class="current-live-match-section my-80">
        <div class="container">
            <div class="row justify-content-center gy-3">
                @forelse ($tournaments as $tournament)
                    @php
                        $version = $tournament->versionName;
                        if (auth()->check()) {
                            $subscribedTournaments = auth()->user()->subscribedTournamentId() ?? [];
                            $version = in_array($tournament->id, $subscribedTournaments) ? 'Watch' : $version;
                        }
                    @endphp
                    <div class="col-xxl-3 col-lg-4 col-sm-6">
                        <a href="{{ route('tournament.detail', [$tournament->id, slug($tournament->name)]) }}"
                            class="current-live-match">
                            <span class="current-live-match__tag">{{ __($version) }}</span>
                            <img src="{{ getImage(getFilePath('tournament') . '/thumb_' . $tournament->image, getFileThumb('tournament')) }}"
                                alt="">
                        </a>
                    </div>
                @empty
                    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                        <img src="{{ asset(activeTemplate(true) . 'images/no-results.png') }}" alt="">
                    </div>
                @endforelse
                {{ paginateLinks($tournaments) }}
            </div>
        </div>
    </section>
@endsection
