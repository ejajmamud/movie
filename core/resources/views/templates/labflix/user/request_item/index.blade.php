@extends('Template::layouts.master')
@section('content')
    @php
        $myItems = request()->routeIs('user.request.item.mine');
    @endphp
    <div class="card-area mt-80 mb-80">
        <div class="container">
            <div class="position-relative">
                <div class="col-12">
                    <div class="card custom--card">
                        <div class="card-body">
                            <div class="request-items-wrapper d-flex justify-content-between align-items-center">
                                <form class="flex-grow-1 me-3 request-items-search">
                                    <div class="input-group">
                                        <input type="search" name="search" class="form-control"
                                            value="{{ request()->search }}" placeholder="@lang('Search here by movies')...">
                                        <span class="input-group-text"><i class="las la-search"></i> </span>
                                    </div>
                                </form>
                                <div class="request-items-buttons d-flex gap-2">
                                    @if ($myItems)
                                        <a href="{{ route('user.request.item.index') }}" class="btn btn-outline--light">
                                            @lang('Back')</a>
                                    @else
                                        <a href="{{ route('user.request.item.mine') }}" class="btn btn-outline--light">
                                            @lang('My Items')</a>
                                    @endif
                                    <a href="{{ route('user.request.item.create') }}" class="btn btn--base">
                                        @lang('Make Request')</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="loader-wrapper">
                    <div class="loader-pre"></div>
                </div>
                <div>
                    <ul class="wishlist-card-list py-5">
                        @include('Template::user.request_item.fetch')
                    </ul>
                </div>
            </div>
            <div class="load-more-button d-flex justify-content-center pb-80 {{ $items->hasMorePages() ? '' : 'd-none' }}">
                <button class="btn btn--base" id="load-more-btn" data-last_id="{{ @$lastId }}"
                    type="buttton">@lang('Load More')</button>
            </div>
        </div>
    </div>

    <div class="modal alert-modal" id="joinWatchParty" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form method="POST" id="partyJoinForm">
                    <div class="modal-body">
                        <h5 class="party-modal-title">@lang('Do you want to join the party?')</h5>
                        <div class="form-group mb-0">
                            <input type="text" placeholder="Enter Party Code" name="party_code" class="form--control">
                        </div>
                    </div>
                    <div class="modal-footer flex-nowrap">
                        <button class="btn btn--danger btn--sm" data-bs-dismiss="modal"
                            type="button">@lang('No')</button>
                        <button class="btn btn--base btn--sm" type="submit">@lang('Yes')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-basic-confirmation-modal />
@endsection

@push('script')
    <script>
        (function($) {
            "use strict";
            let pageNumber = 2;
            let myItem = `{{ request()->routeIs('user.request.item.mine') }}` ? 'true' : 'false';
            let searchValue = '';

            $('.loader-wrapper').addClass('d-none');
            $('#load-more-btn').on('click', function(e) {
                let page = pageNumber;
                let search = searchValue;
                $(this).attr('disabled', true);
                $('.loader-wrapper').removeClass('d-none');

                if (myItem == 'true') {
                    var url =
                        `{{ route('user.request.item.mine') }}?page=${page}&search=${search}&load=${true}&myItem=${myItem}`;
                } else {
                    var url =
                        `{{ route('user.request.item.index') }}?page=${page}&search=${search}&load=${true}&myItem=${myItem}`;
                }

                $.ajax({
                    type: "GET",
                    url: url,
                    search: search,
                    success: function(response) {
                        if (response.error) {
                            notify('error', response.error);
                            $('.load-more-button').addClass('d-none')
                            return;
                        }

                        pageNumber = pageNumber + 1;
                        $('.wishlist-card-list').append(response.data)
                        if (!response.hasMore) {
                            $('.load-more-button').addClass('d-none')
                        }

                    }
                }).done(function() {

                    $('.loader-wrapper').addClass('d-none')
                    $('#load-more-btn').removeAttr('disabled', true);
                });
            });

            $(document).on('click', '.requestUpVote, .requestDownVote', function() {
                const itemId = $(this).data('item-id');
                const voteType = $(this).hasClass('requestUpVote') ? 'upvote' : 'downvote';
                handleVote(itemId, voteType);
            });

            function handleVote(itemId, voteType) {
                const url = `{{ route('user.request.item.vote') }}`;

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        track_id: itemId,
                        vote_type: voteType,
                        _token: '{{ csrf_token() }}',
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            updateVoteUI(itemId, response);
                        } else {
                            notify('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        const errors = xhr.responseJSON?.errors;
                        alert(errors ? `Error: ${Object.values(errors).join('\n')}` :
                            'An unexpected error occurred.');
                    },
                });
            }

            function updateVoteUI(itemId, response) {
                const upVoteButton = $(`.requestUpVote[data-item-id="${itemId}"]`);
                const downVoteButton = $(`.requestDownVote[data-item-id="${itemId}"]`);
                const upVoteCount = $(`#voteCount-${itemId}`);
                const downVoteCount = $(`#downVoteCount-${itemId}`);


                upVoteButton.find("i").removeClass("fas far fa-thumbs-up").addClass("far fa-thumbs-up");
                downVoteButton.find("i").removeClass("fas far fa-thumbs-down").addClass("far fa-thumbs-down");


                if (response.voteType === "upvote" && response.voteDelete !== "upDelete") {
                    upVoteButton.find("i").removeClass("far fa-thumbs-up").addClass("fas fa-thumbs-up");
                } else if (response.voteType === "downvote" && response.voteDelete !== "downDelete") {
                    downVoteButton.find("i").removeClass("far fa-thumbs-down").addClass("fas fa-thumbs-down");
                }

                // Update vote counts
                upVoteCount.text(response.upVotes);
                downVoteCount.text(response.downVotes);
            }


            //subscribe
            $(document).on("click", ".subscribeMovie", function() {
                const itemId = $(this).data("item-id");
                handleSubscription(itemId);
            });

            function handleSubscription(itemId) {
                const url = `{{ route('user.request.item.subscribe') }}`;

                $.post(url, {
                        item_id: itemId,
                        _token: "{{ csrf_token() }}"
                    })
                    .done(function(response) {
                        const subscribeItem = $(`#subscribeItem-${itemId}`);
                        const icon = subscribeItem.find("i");

                        if (response.status === "success" && response.enable) {
                            icon.removeClass("far fa-bell").addClass("fas fa-bell");
                            notify("success", response.message);
                        } else if (response.status === "success") {
                            icon.removeClass("fas fa-bell").addClass("far fa-bell");
                            notify("warning", response.message);
                        } else {
                            notify("error", response.message);
                        }
                    })
                    .fail(function() {
                        notify("error", "Unable to process your subscription at this time.");
                    });
            }


            //item live search//
            let typingTimer; // global timer variable
            const debounceTime = 1000; // 2 seconds
            $('[name="search"]').on('keyup', function(event) {
                event.preventDefault();
                searchValue = $(this).val();

                clearTimeout(typingTimer); // reset the timer
                typingTimer = setTimeout(function() {
                    liveSearch(searchValue);
                }, debounceTime);
            });

            function liveSearch(keyword) {
                $('.loader-wrapper').removeClass('d-none');
                pageNumber = 2;
                $.ajax({
                    url: "{{ route('user.request.item.live.search') }}",
                    type: "GET",
                    data: {
                        search: keyword,
                        myItem: myItem,
                    },

                    success: function(response) {
                        if (response.error || !response.data) {
                            $('.wishlist-card-list').html(`<li class="text-center my-5">
                                                                    <i class="las text-muted la-4x la-clipboard-list"></i><br>
                                                                    <h4 class="mt-2 text-muted">@lang('Search item not found!')</h4>
                                                                </li>`);
                            return;
                        }


                        $('.wishlist-card-list').html(response.data);
                        if (response.hasMore) {
                            $('.load-more-button').removeClass('d-none')
                        } else {
                            $('.load-more-button').addClass('d-none')
                        }
                    }
                }).always(function() {
                    $('.loader-wrapper').addClass('d-none');
                });
            }

        })(jQuery)
    </script>
@endpush



@push('style')
    <style>
        .wishlist-card-list p {
            display: -webkit-box;
            -webkit-line-clamp: 4;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            color: hsl(var(--white) / .65);
            font-size: 0.9125rem;
        }

        .wishlist-card-wrapper__icon button {
            margin: 0 5px;
        }

        .vote-count {
            font-weight: bold;
            margin: 0 5px;
        }

        .wishlist-card-list__item {
            border-bottom: 1px solid hsl(var(--white) / .10);
        }

        .wishlist-card-list__item:last-child {
            border-bottom: none;
        }

        .wishlist-card__title__icon {
            margin-right: 10px;
        }

        .wishlist-card-wrapper div:nth-child(1) {
            width: 45%;
        }

        .wishlist-card-wrapper div:nth-child(2) {
            text-align: right width: 20%;
        }

        .wishlist-card-wrapper div:nth-child(3) {
            width: 35%;
        }

        @media (max-width: 767px) {
            .wishlist-card-wrapper div:nth-child(1) {
                width: 100%;
                margin-bottom: 12px;
            }

            .wishlist-card-wrapper div:nth-child(2) {
                width: 30%;
                text-align: left
            }

            .wishlist-card-wrapper div:nth-child(3) {
                width: 70%;
            }

            .request-items-wrapper {
                display: block !important;
            }

            .request-items-search {
                margin: 0 0 10px !important;
            }

            .request-items-buttons {
                justify-content: end;
            }
        }

        @media (max-width: 374px) {
            .wishlist-card-wrapper div:nth-child(3) .btn {
                padding: 5px 8px !important;
            }

            .wishlist-card-wrapper div:nth-child(3) .down-vote-count {
                padding: 0 5px !important;
            }

            .wishlist-card-wrapper div:nth-child(3) .vote-count {
                margin: 0 !important;
                font-weight: 400 !important;
            }
        }
    </style>
@endpush
