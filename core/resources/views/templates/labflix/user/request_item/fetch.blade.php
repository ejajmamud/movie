<div class="row gy-4">
    @forelse($items as $item)
        <li class="col-lg-6">
            <div class="wishlist-wishlist-card">
                <div class="movie__image">
                    <img class="w-100" src="https://image.tmdb.org/t/p/w200{{ $item->image_path }}" alt="Poster">
                </div>
                <div class="movie__content">
                    <h4 class="movie__content__title">
                        {{ __(strLimit($item->item, 60)) }}
                    </h4>
                    <p>{{ __($item->overview) }}</p>

                    <div class="flex-between mt-3 flex-grow-1 align-items-end">
                        <div class="movie__content__status">
                            @if (@$myItems)
                                @php echo $item->statusBadge; @endphp
                            @endif
                        </div>
                        <div class="vote-action">
                            <!-- Upvote Count & Button -->
                            <div class="vote-action-item">
                                <span class="vote-count pr-2"
                                    id="voteCount-{{ $item->id }}">{{ $item->upvotes }}</span>
                                <button class="requestUpVote" data-item-id="{{ $item->id }}" type="button"
                                    title="@lang('Up Vote')">
                                    <i
                                        class="{{ $item->user_vote == Status::VOTE_UP ? 'fas fa-thumbs-up' : 'far fa-thumbs-up' }}"></i>
                                </button>
                            </div>

                            <!-- Down vote Count & Button -->
                            <div class="vote-action-item">
                                <span class="vote-count"
                                    id="downVoteCount-{{ $item->id }}">{{ $item->downvotes }}</span>
                                <button class="requestDownVote" data-item-id="{{ $item->id }}" type="button"
                                    title="@lang('Down Vote')">
                                    <i
                                        class="{{ $item->user_vote == Status::VOTE_DOWN ? 'fas fa-thumbs-down' : 'far fa-thumbs-down' }}"></i>
                                </button>
                            </div>

                            <!-- Subscribe Button -->
                            <div class="vote-action-item">
                                <button class="subscribeMovie" id="subscribeItem-{{ $item->id }}"
                                    data-item-id="{{ $item->id }}" type="button" title="@lang('Subscribe for Notifications')">
                                    <span>
                                        <i class="{{ !$item->user_subscribe ? 'far fa-bell' : 'fas fa-bell shake-bell' }}"
                                            data-bs-toggle="tooltip" title="Subscribed"></i>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </li>
    @empty
        <li class="col-12">
            <div class="text-center">
                <i class="las text-muted la-4x la-clipboard-list"></i><br>
                <h4 class="mt-2 text-muted">@lang('No item requested yet!')</h4>
            </div>
        </li>
    @endforelse
</div>

@push('style')
    <style>
        .wishlist-wishlist-card {
            --thumb-w: 120px;
            --gap: 24px;
            display: flex;
            flex-wrap: wrap;
            gap: var(--gap);
            padding: 16px;
            border-radius: 6px;
            background: hsl(var(--white) / .03);
        }

        .movie__content__title {
            font-size: 1.125rem;
            margin-bottom: 6px;
        }

        .wishlist-wishlist-card:not(:last-child) {
            margin-bottom: 20px;
        }

        .movie__image {
            border-radius: 6px;
            overflow: hidden;
            width: var(--thumb-w);
            flex-shrink: 0;
            box-shadow: 0px 4px 12px hsl(var(--white) / .1);
        }

        .movie__content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        @media(max-width: 424px) {
            .wishlist-wishlist-card {
                flex-direction: column;
                --thumb-w: 100%;
            }
        }

        .vote-action {
            display: flex;
            align-items: center;
            padding-inline: 6px;
            background-color: rgb(255 255 255 / 8%);
            border-radius: 24px;
        }

        .vote-action-item {
            display: flex;
            font-size: 0.875rem;
            color: #fff;
            padding: 6px;
            align-items: center;
        }

        .vote-action-item:not(:last-child) {
            border-right: 1px solid rgb(255 255 255 / 8%);
        }

        .vote-action-item button {
            background-color: transparent;
            color: #fff;
            font-size: 1.125rem;
        }

        @media (max-width: 767px) {
            .vote-action-item {
                padding: 4px
            }

            .vote-action-item button {
                font-size: 1rem;
            }
        }
    </style>
@endpush
