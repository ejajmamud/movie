@forelse ($items as $item)
    <div class="col-xxl-2 col-xl-3 col-md-4 col-sm-6 col-xsm-6">
        <div class="card custom--card h-100">
            <img class="lazy-loading-img card-img-top movie-thumb"
                 data-src="https://image.tmdb.org/t/p/w500{{ $item['poster_path'] ?? '' }}"
                 src="{{ asset('assets/global/images/lazy.png') }}" alt="@lang('image')">
            <div class="card-body p-3">
                <h5 title="{{ @$item['title'] ?? @$item['name'] }}" class="card-title movie-title">
                    {{ @$item['title'] ?? @$item['name'] }}</h5>
                <p class="movie_overview">{{ strLimit($item['overview'], 50) }}</p>
                <button class="btn btn--base btn--sm requestToItem" data-item-id="{{ $item['id'] }}"
                        data-heading="{{ @$item['title'] ?? @$item['name'] }}" data-src="{{ $item['poster_path'] }}" data-overview="{{ $item['overview'] }}">
                    @lang('Request Item')
                </button>
            </div>
        </div>
    </div>
@empty
    <div class="text-center my-5">
        <i class="las text-muted la-4x la-clipboard-list"></i><br>
        <h4 class="mt-2 text-muted">@lang('Item not found!')</h4>
    </div>
@endforelse
