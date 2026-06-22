@extends('admin.layouts.app')
@section('panel')
    <div class="row mb-none-30">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.game.store', @$game->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-xxl-4 col-lg-6">
                                <div class="form-group">
                                    <label>@lang('Image')</label>
                                    <x-image-uploader image="{{ @$game->image }}" class="w-100" type="game" :required=false />
                                </div>
                            </div>
                            <div class="col-xxl-8 col-lg-6">
                                <div class="form-group ">
                                    <label>@lang('Tournament')</label>
                                    <select class="form-control select2" name="tournament_id" required>
                                        <option value="" selected>@lang('Select One')</option>
                                        @foreach ($tournaments as $tournament)
                                            <option value="{{ $tournament->id }}" @selected(old('tournament_id', @$game->tournament_id) == $tournament->id)>{{ $tournament->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group ">
                                    <label>@lang('Team One')</label>
                                    <select class="form-control select2" name="team_one_id" required>
                                        <option value="{{ @$game->team_one_id }}" selected>{{ __(@$game->teamOne->name) }}</option>
                                    </select>
                                </div>
                                <div class="form-group ">
                                    <label>@lang('Team Two')</label>
                                    <select class="form-control select2" name="team_two_id" required>
                                        <option value="{{ @$game->team_two_id }}" selected>{{ __(@$game->teamTwo->name) }}</option>
                                    </select>
                                </div>
                                <div class="form-group ">
                                    <label>@lang('Start Time')</label>
                                    <input name="start_time" type="text" class="datepicker-here form-control" autocomplete="off" value="{{ old('start_time', @$game->start_time) }}" required>
                                </div>
                                <div class="form-group ">
                                    <label>@lang('Streaming Link')</label>
                                    <input type="text" name="link" class="form-control" value="{{ old('link', @$game->link) }}" required>
                                </div>
                                <div class="form-group ">
                                    <label>@lang('Details')</label>
                                    <textarea name="details" class="form-control" rows="5" required>{{ old('details', @$game->details) }}</textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>@lang('Price')</label>
                                            <div class="input-group">
                                                <input type="number" step="any" name="price" class="form-control" value="{{ old('price', getAmount(@$game->price)) }}" required>
                                                <span class="input-group-text">{{ gs('cur_text') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>@lang('Version')</label>
                                            <input name="version" data-width="100%" data-size="large" data-onstyle="-success" data-offstyle="-danger" data-bs-toggle="toggle" data-height="50" data-on="@lang('Paid')" data-off="@lang('Free')" type="checkbox" @checked(@$game->version)>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn--primary h-45 w-100">@lang('Submit')</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.game.index') }}" />
@endpush

@push('script-lib')
    <script src="{{ asset('assets/admin/js/moment.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/daterangepicker.min.js') }}"></script>
@endpush

@push('style-lib')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/admin/css/daterangepicker.css') }}">
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";
            $('[name="start_time"]').daterangepicker({
                singleDatePicker: true,
                timePicker: true,
                timePicker24Hour: true,
                showDropdowns: true,
                minYear: 1901,
                locale: {
                    format: 'YYYY-MM-DD H:mm'
                }
            });

            $("[name=team_one_id], [name=team_two_id]").select2({
                placeholder: 'Search Team...',
                allowClear: true,
                ajax: {
                    url: "{{ route('admin.game.team.search') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            term: params.term || '',
                            page: params.page || 1
                        }
                    },
                    cache: true
                }
            });
        })(jQuery)
    </script>
@endpush

@push('style')
    <style>
        .select2-container {
            width: auto !important;
        }

        .select2-container:has(.select2-selection--single) {
            width: 100% !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #444;
        }

        .select2-selection__clear {
            display: none;
        }
    </style>
@endpush
