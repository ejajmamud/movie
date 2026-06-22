@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card  ">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table--light style--two table">
                            <thead>
                                <tr>
                                    <th>@lang('Item Name')</th>
                                    <th>@lang('Request')</th>
                                    <th>@lang('Track Id')</th>
                                    <th>@lang('Vote')</th>
                                    <th>@lang('Subscribe')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Is Published')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $item)
                                    <tr>
                                        <td>{{ __($item->item) }}</td>
                                        <td>{{ $item->users->count() }}</td>
                                        <td>
                                            <div class="flex-align gap-1">
                                                <span class="text--primary">{{ $item->track_id }} </span>
                                                <span class="view-btn" data-track_id="{{ $item->track_id }}"><i
                                                        class="fas fa-eye"></i></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <span data-bs-toggle="tooltip" data-bs-title="@lang('Up Vote')"
                                                    class="text--success"> <i class="las la-thumbs-up"></i>
                                                    {{ $item->upvotes }}</span> <br>
                                                <span data-bs-toggle="tooltip" data-bs-placement="bottom"
                                                    data-bs-title="@lang('Down Vote')" class="text--danger"><i
                                                        class="las la-thumbs-down"></i>
                                                    {{ $item->downvotes }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span data-bs-toggle="tooltip" data-bs-placement="bottom"
                                                data-bs-title="@lang('Interested users for upload notify')" class="text--primary"><i
                                                    class="las la-bell"></i>
                                                {{ $item->item_subscribes_count }}</span>
                                        </td>
                                        <td>
                                            @if ($item->status == Status::REQUEST_ITEM_ACCEPTED)
                                                <span class="badge badge--primary">@lang('Accepted')</span>
                                            @elseif($item->status == Status::REQUEST_ITEM_REJECTED)
                                                <span class="badge badge--danger">@lang('Rejected')</span>
                                            @else
                                                <span class="badge badge--warning">@lang('Pending')</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($item->is_publish)
                                                <span class="badge badge--info">@lang('Yes')</span>
                                            @else
                                                <span class="badge badge--dark">@lang('No')</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="button--group">
                                                @if ($item->users->count() > 0)
                                                    <a href="{{ route('admin.request.item.details', $item->id) }}"
                                                        class="btn btn--sm btn-outline--info">
                                                        <i class="la la-desktop"></i>@lang('Details')</a>
                                                @else
                                                    <a href="#" class="btn btn--sm btn-outline--info disabled">
                                                        <i class="la la-desktop"></i>@lang('Details')</a>
                                                @endif
                                                </button>
                                                @if ($item->status == Status::REQUEST_ITEM_PENDING)
                                                    <button class="btn btn--sm btn-outline--primary confirmationBtn"
                                                        data-question="@lang('Are you sure to accept this requested item?')"
                                                        data-action="{{ route('admin.request.item.status', [$item->id, Status::REQUEST_ITEM_ACCEPTED]) }}">
                                                        <i class="la la-check"></i>@lang('Accept')
                                                    </button>

                                                    <button class="btn btn--sm btn-outline--danger confirmationBtn"
                                                        data-question="@lang('Are you sure to reject this requested item?')"
                                                        data-action="{{ route('admin.request.item.status', [$item->id, Status::REQUEST_ITEM_REJECTED]) }}">
                                                        <i class="la la-close"></i>@lang('Reject')
                                                    </button>
                                                @endif
                                                @if ($item->status == Status::REQUEST_ITEM_ACCEPTED)
                                                    <button class="btn btn--sm btn-outline--primary disabled">
                                                        <i class="la la-check"></i>@lang('Accept')
                                                    </button>
                                                    <button
                                                        class="btn btn--sm btn-outline--danger @if ($item->is_publish) disabled @else confirmationBtn @endif "
                                                        data-question="@lang('Are you sure to reject this requested item?')"
                                                        data-action="{{ route('admin.request.item.status', [$item->id, Status::REQUEST_ITEM_REJECTED]) }}">
                                                        <i class="la la-close"></i>@lang('Reject')
                                                    </button>
                                                @endif
                                                @if ($item->status == Status::REQUEST_ITEM_REJECTED)
                                                    <button class="btn btn--sm btn-outline--primary confirmationBtn"
                                                        data-question="@lang('Are you sure to accept this requested item?')"
                                                        data-action="{{ route('admin.request.item.status', [$item->id, Status::REQUEST_ITEM_ACCEPTED]) }}">
                                                        <i class="la la-check"></i>@lang('Accept')
                                                    </button>
                                                    <button class="btn btn--sm btn-outline--danger disabled">
                                                        <i class="la la-close"></i>@lang('Reject')
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if ($items->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($items) }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="modal fade" id="itemPublishModal" role="dialog" aria-hidden="true" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"></h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form method="post" action="{{ route('admin.request.item.publish') }}">
                    @csrf
                    <div class="modal-body">
                        <h6 class="text--info mb-3">@lang('This will notify the requesting user and subscribed users.')</h6>
                        <div class="form-group">
                            <label>@lang('Track Id')</label>
                            <input class="form-control" name="item_track_id" type="text" required>
                            <small class="text--warning">@lang('This is the track ID of the movie or series you have already published or uploaded.')</small>
                        </div>
                        <div class="form-group">
                            <label>@lang('Are you sure to notify?')</label>
                            <input type="checkbox" data-width="100%" data-size="large" data-onstyle="-success"
                                data-offstyle="-danger" data-bs-toggle="toggle" data-height="35"
                                data-on="@lang('Yes')" data-off="@lang('No')" name="is_notify">
                        </div>
                        <div class="form-group link-wrapper d-none">
                            <label>@lang('Link') <small class="text--danger">*</small>
                                <i class="fas fa-info-circle text--info ms-1" data-bs-toggle="tooltip"
                                    data-bs-placement="right"
                                    title="This is the link of the movie or series you have already published or uploaded.">
                                </i>
                            </label>
                            <input class="form-control" name="item_link" type="text">
                            <small class="text--warning">@lang('Subscribed users can quickly access this item using this link.')</small>
                        </div>
                        <button class="btn btn--primary w-100 h-45" type="submit">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="confirmationModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Confirmation Alert!')</h5>
                    <button type="button" class="close btn-close-white" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form method="POST">
                    @csrf
                    <div class="modal-body">
                        <p class="question"></p>
                    </div>
                    <input type="hidden" name="status">
                    <div class="modal-footer">
                        <button class="btn btn--dark" data-bs-dismiss="modal" type="button">@lang('No')</button>
                        <button class="btn btn--primary" type="submit">@lang('Yes')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="details-view-modal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">@lang('Move Details View')</h6>
                    <button type="button" class="close btn-close-white" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="view-short-details">
                            <div class="view-short-details-banner">
                                <img src="" alt="banner-image">
                            </div>
                            <div class="view-short-details-wrapper">
                                <div class="view-short-details-thumb">
                                    <img src="" alt="thumb-image">
                                </div>
                                <div class="view-short-details-content">
                                    <p class="view-short-details-name"></p>
                                    <div class="view-short-details-info">
                                        <p class="view-short-details-info-item">
                                            <span class="icon">
                                                <i class="fa-regular fa-calendar"></i>
                                            </span>
                                            <span class="value release_date"></span>
                                        </p>
                                        <p class="view-short-details-info-item">
                                            <span class="icon">
                                                <i class="fa-regular fa-clock"></i>
                                            </span>
                                            <span class="value runtime"></span>
                                        </p>
                                        <p class="view-short-details-info-item">
                                            <span class="icon">
                                                <i class="fa-solid fa-ticket"></i>
                                            </span>
                                            <span class="value action"></span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="view-short-details-desc">
                                <p class="title">Overview</p>
                                <p class="text">
                                </p>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <x-search-form />
    <button class="btn btn-outline--info publishBtn"><i class="las la-check"></i>@lang('Publish & Notify')</button>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict"

            let modal = $('#itemPublishModal');

            $('.publishBtn').on('click', function() {
                modal.find('.modal-title').text(`@lang('Item Notify & Publish')`);
                modal.modal('show');
            });

            modal.find('[name="is_notify"]').on('change', function() {
                modal.find('.link-wrapper').toggleClass('d-none', !$(this).prop('checked'));
            });

            $('.recommendBtn').on('click', function() {
                let modal = $('#recommendModal');
                let data = $(this).data();
                modal.find('.item-heading').text(`${data.item_heading}`);
                modal.find('.recommend-text').text(`${data.recommend}`);
                modal.modal('show');

            });

            $('.confirmationBtn').on('click', function() {
                var modal = $('#confirmationModal');
                let data = $(this).data();
                modal.find('.question').text(`${data.question}`);
                modal.find('form').attr('action', `${data.action}`);
                modal.modal('show');
            });


            $('.view-btn').on('click', async function(e) {
                e.preventDefault();
                let detailModal = $('#details-view-modal');
                try {
                    let trackId = $(this).data('track_id');
                    const url =
                        `https://api.themoviedb.org/3/movie/${trackId}?api_key={{ gs('tmdb_api') }}`;
                    const response = await fetch(url);
                    const data = await response.json();

                    let bannerImage = `https://image.tmdb.org/t/p/w200${data.backdrop_path}`;
                    let posterImage = `https://image.tmdb.org/t/p/w300${data.poster_path}`;

                    detailModal.find('.view-short-details-banner img').attr('src', bannerImage);
                    detailModal.find('.view-short-details-thumb img').attr('src', posterImage);
                    detailModal.find('.view-short-details-name').text(data.title);
                    detailModal.find('.release_date').text(data.release_date);
                    detailModal.find('.runtime').text(`${data.runtime} minutes`);
                    detailModal.find('.action').text(data.genres[0].name);
                    detailModal.find('.view-short-details-desc .text').text(data.overview);
                    detailModal.modal('show');

                } catch (error) {
                    console.error(error.message);
                }
            });

        })(jQuery);
    </script>
@endpush

@push('style')
    <style>
        .view-btn {
            height: 22px;
            width: 24px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;

            i {

                font-size: 0.7rem;
            }
        }

        .view-short-details {
            --banner-h: 120px;
            --thumb-h: 120px;
            --thumb-w: 80px;
        }

        .view-short-details-banner {
            height: var(--banner-h);
            position: relative;
            border-radius: 6px;
            overflow: hidden;

            img {
                height: 100%;
                width: 100%;
                object-fit: cover;
            }
        }

        .view-short-details-banner::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(180deg, rgba(87, 199, 133, 0) 31%, rgba(0, 0, 0, 0.8) 69%);
        }

        .view-short-details-wrapper {
            display: flex;
            align-items: flex-end;
            gap: 12px;
            padding-inline: 12px;
            position: relative;
            z-index: 2;
        }

        .view-short-details-thumb {
            height: var(--thumb-h);
            width: var(--thumb-w);
            border-radius: 4px;
            overflow: hidden;
            margin-top: calc(-1 * var(--thumb-h) / 2);

            img {
                height: 100%;
                width: 100%;
                object-fit: cover;
            }
        }

        .view-short-details-content {
            flex: 1;
        }

        .view-short-details-name {
            font-size: 1.125rem;
            font-weight: 600;
            color: #000;
        }

        .view-short-details-info {
            display: flex;
            align-items: center;
            gap: 0 8px;
            flex-wrap: wrap;
        }

        .view-short-details-info-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.75rem;
        }

        .view-short-details-desc {
            padding: 12px;
        }

        .view-short-details-desc .title {
            font-weight: 600;
            color: #000;
        }

        .view-short-details-desc .text {
            font-size: 0.75rem;
        }
    </style>
@endpush
