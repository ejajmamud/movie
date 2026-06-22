@extends('admin.layouts.app')
@section('panel')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table table--light style--two">
                            <thead>
                                <tr>
                                    <th>@lang('Image')</th>
                                    <th>@lang('Tournament')</th>
                                    <th>@lang('Team')</th>
                                    <th>@lang('Price')</th>
                                    <th>@lang('Start Time')</th>
                                    <th>@lang('Version')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($games as $game)
                                    <tr>
                                        <td>
                                            <div class="user">
                                                <div class="thumb"><img
                                                        src="{{ getImage(getFilePath('game') . '/' . $game->image, getFileSize('game')) }}"
                                                        alt="{{ __($game->name) }}" class="plugin_bg"></div>
                                            </div>
                                        </td>
                                        <td>
                                            {{ __(@$game->tournament->name) }}
                                        </td>
                                        <td>
                                            <span class="name">{{ @$game->teamOne->name }}</span> -
                                            <span>{{ @$game->teamTwo->name }}</span>
                                        </td>
                                        <td>
                                            <span>{{ showAmount($game->price) }}</span>
                                        </td>
                                        <td>
                                            <span>{{ showDateTime($game->start_time) }}</span>
                                        </td>
                                        <td>
                                            @php
                                                echo $game->versionBadge;
                                            @endphp
                                        </td>
                                        <td>
                                            @php
                                                echo $game->statusBadge;
                                            @endphp
                                        </td>
                                        <td>
                                            <div class="btn--group">
                                                <div class="d-flex justify-content-end flex-wrap gap-1">
                                                    <button class="btn btn-outline--info btn-sm dropdown-toggle"
                                                        data-bs-toggle="dropdown">
                                                        <i class="las la-ellipsis-v"></i> @lang('More')
                                                    </button>
                                                    <ul class="dropdown-menu px-2">
                                                        <li>
                                                            <a href="{{ route('admin.game.add', $game->id) }}"
                                                                class="dropdown-item text--primary">
                                                                <i class="la la-pen"></i> @lang('Edit')
                                                            </a>
                                                        </li>
                                                        @if ($game->status == Status::ENABLE)
                                                            <li>
                                                                <button
                                                                    class="dropdown-item cursor-pointer text--warning confirmationBtn"
                                                                    data-question="@lang('Are you sure to disbale this game?')"
                                                                    data-action="{{ route('admin.game.status', $game->id) }}"><i
                                                                        class="la la-eye-slash"></i>
                                                                    @lang('Disable')</button>
                                                            </li>
                                                        @else
                                                            <li>
                                                                <button
                                                                    class="dropdown-item cursor-pointer text--success confirmationBtn"
                                                                    data-question="@lang('Are you sure to enable this game?')"
                                                                    data-action="{{ route('admin.game.status', $game->id) }}"><i
                                                                        class="la la-eye"></i> @lang('Enable')</button>
                                                            </li>
                                                        @endif
                                                        <li>
                                                            <a href="{{ route('admin.game.live.comment', $game->id) }}"
                                                                class="dropdown-item cursor-pointer text--success {{ $game->live_comments_count ? '' : 'disabled' }}">
                                                                <i class="lab la-rocketchat"></i> @lang('Live Comment')
                                                                <span
                                                                    class="live-comment-badge badge badge--success">{{ $game->live_comments_count }}</span>
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="{{ route('admin.game.seo', $game->id) }}"
                                                                class="dropdown-item cursor-pointer text--secondary"><i
                                                                    class="la la-cog"></i> @lang('Seo Setting')</a>
                                                        </li>
                                                    </ul>
                                                </div>
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
                @if ($games->hasPages())
                    <div class="card-footer py-4">
                        @php echo paginateLinks($games) @endphp
                    </div>
                @endif
            </div>
        </div>
    </div>

    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <x-search-form />
    <a href="{{ route('admin.game.add') }}" class="btn btn-outline--primary"><i class="las la-plus"></i>@lang('Add New')</a>
@endpush

@push('style')
    <style>
        .dropdown-item.text--success {
            position: relative;
            padding-right: 40px !important;
        }

        .dropdown-item .live-comment-badge {
            position: absolute;
            right: 10px;
            top: 0px;
            transform: translateY(-50%);
            font-size: 10px !important;
            font-weight: 400;
        }

        .dropdown-item.disabled .live-comment-badge {
            opacity: 0.6;
            border-color: #999;
            background: rgba(153, 153, 153, 0.1);
            color: #666;
        }

        .dropdown-item.active,
        .dropdown-item:active {
            background-color: unset !important;
        }
    </style>
@endpush
