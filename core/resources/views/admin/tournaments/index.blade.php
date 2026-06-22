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
                                    <th>@lang('Image | Name')</th>
                                    <th>@lang('Short Name')</th>
                                    <th>@lang('Season')</th>
                                    <th>@lang('Price')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Version')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tournaments as $tournament)
                                    <tr>
                                        <td>
                                            <div class="user">
                                                <div class="thumb"><img
                                                        src="{{ getImage(getFilePath('tournament') . '/' . $tournament->image, getFileSize('tournament')) }}"
                                                        alt="{{ __($tournament->name) }}" class="plugin_bg"></div>
                                                <span class="name">{{ __($tournament->name) }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            {{ __($tournament->short_name) }}
                                        </td>
                                        <td>
                                            <span>{{ $tournament->season }}</span>
                                        </td>
                                        <td>
                                            <span>{{ showAmount($tournament->price) }}</span>
                                        </td>
                                        <td>
                                            @php
                                                echo $tournament->statusBadge;
                                            @endphp
                                        </td>
                                        <td>
                                            @php
                                                echo $tournament->versionBadge;
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
                                                            <a href="{{ route('admin.tournament.add', $tournament->id) }}"
                                                                class="dropdown-item text--primary">
                                                                <i class="la la-pen"></i> @lang('Edit')
                                                            </a>
                                                        </li>
                                                        @if ($tournament->status == Status::ENABLE)
                                                            <li>
                                                                <button
                                                                    class="dropdown-item cursor-pointer text--warning confirmationBtn"
                                                                    data-question="@lang('Are you sure to disbale this tournament?')"
                                                                    data-action="{{ route('admin.tournament.status', $tournament->id) }}"><i
                                                                        class="la la-eye-slash"></i>
                                                                    @lang('Disable')</button>
                                                            </li>
                                                        @else
                                                            <li>
                                                                <button
                                                                    class="dropdown-item cursor-pointer text--success confirmationBtn"
                                                                    data-question="@lang('Are you sure to enable this tournament?')"
                                                                    data-action="{{ route('admin.tournament.status', $tournament->id) }}"><i
                                                                        class="la la-eye"></i> @lang('Enable')</button>
                                                            </li>
                                                        @endif
                                                        <li>
                                                            <a href="{{ route('admin.tournament.seo', $tournament->id) }}"
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
                @if ($tournaments->hasPages())
                    <div class="card-footer py-4">
                        @php echo paginateLinks($tournaments) @endphp
                    </div>
                @endif
            </div>
        </div>
    </div>

    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <x-search-form placeholder='Search by Name' />
    <a href="{{ route('admin.tournament.add') }}" class="btn btn-outline--primary"><i
            class="las la-plus"></i>@lang('Add New')</a>
@endpush


@push('style')
    <style>
        .dropdown-item.active,
        .dropdown-item:active {
            background-color: unset !important;
        }
    </style>
@endpush
