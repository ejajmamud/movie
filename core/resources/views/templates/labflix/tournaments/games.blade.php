@extends('Template::layouts.frontend')
@section('content')
    <section class="recent-match-section section--bg ptb-80">
        <div class="container">
            <div class="row gy-3">
                @include('Template::partials.games')
            </div>
        </div>
    </section>
@endsection
