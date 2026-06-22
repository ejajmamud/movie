@extends('admin.layouts.app')
@section('panel')
    @push('topBar')
        @include('admin.advertise.top_bar')
    @endpush
    <div class="row">
        <div class="col-lg-12">
            <form action="{{ route('admin.video.advertise.store', @$advertise->id) }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-xl-9">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group ">
                                            <label>@lang('Type')</label>
                                            <select class="form-control select2" id="type" name="type" required>
                                                <option value="2" @selected(@$advertise->type == 2)>@lang('Video')
                                                </option>
                                                <option value="1" @selected(@$advertise->type == 1)>@lang('Link')
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 link d-none">
                                        <div class="form-group">
                                            <label>@lang('Link')<span class="text--danger">*</span></label>
                                            <input class="form-control" name="link" type="text"
                                                value="{{ old('link', @$advertise->content->link) }}"
                                                placeholder="@lang('Link')">
                                        </div>
                                    </div>

                                    <div class="col-md-6 file d-none">
                                        <div class="form-group">
                                            <label>@lang('File')<span class="text--danger">*</span></label>
                                            <input class="form-control" name="video" type="file" accept="video/*">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>@lang('Ad Format')</label>
                                            <select class="form-control select2" name="ad_format"
                                                data-minimum-results-for-search="-1" required>
                                                <option value="" disabled>@lang('Select One')</option>
                                                <option value="{{ Status::FORMAT_SKIPABLE }}" @selected(old('ad_format', @$advertise->ad_format) == Status::FORMAT_SKIPABLE)>
                                                    @lang('Skippable Ads')</option>
                                                <option value="{{ Status::FORMAT_NONSKIPABLE }}"
                                                    @selected(old('ad_format', @$advertise->ad_format) == Status::FORMAT_NONSKIPABLE)>
                                                    @lang('Non-Skippable Ads')</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <div
                                                class="d-flex flex-wrap gap-2 align-content-center justify-content-between mb-1">
                                                <label class="form-label">@lang('Frequency Cap (Max Views per User)') <i
                                                        class="las la-info-circle text--danger" data-bs-toggle="tooltip"
                                                        title="@lang('Login user, if a daily view limit is set for each date, that limit applies. Otherwise, the total view count is used. If the frequency cap is zero, there is no limit.')"></i></label>
                                                <div>
                                                    <input type="checkbox" name="is_daily" class="custom-control-input"
                                                        @if (old('is_daily', @$advertise->is_daily)) checked @endif id="is_daily">
                                                    <label class="custom-control-label text--primary is-daily"
                                                        for="is_daily">@lang('Daily Based')?</label>
                                                </div>
                                            </div>
                                            <input type="number" class="form-control"
                                                value="{{ old('frequency_cap', @$advertise->frequency_cap) }}"
                                                name="frequency_cap" placeholder="@lang('e.g., 3')">
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="col-xl-3">
                                <div class="form-group">
                                    <label class="form-label">@lang('Ad Scheduling From [Time]') <i class="las la-info-circle text--danger"
                                            data-bs-toggle="tooltip" title="@lang('If no picked any time, the ad will showing all times')"></i></label>
                                    <input type="text" class="form-control clockpicker" name="ad_schedule_from"
                                        value="{{ old('ad_schedule_from', @$advertise->ad_schedule_from) }}"
                                        placeholder="HH:mm:ss">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">@lang('Ad Scheduling To [Time]') <i class="las la-info-circle text--danger"
                                            data-bs-toggle="tooltip" title="@lang('If no picked any time, the ad will showing all times')"></i></label>
                                    <input type="text" class="form-control clockpicker" name="ad_schedule_to"
                                        value="{{ old('ad_schedule_to', @$advertise->ad_schedule_to) }}"
                                        placeholder="HH:mm:ss">
                                </div>
                            </div>

                            <div class="col-lg-12">
                                <div class="form-group">
                                    <div class="d-flex gap-2 align-content-center justify-content-between mb-1">
                                        <label class="form-label">@lang('Target Country')</label>
                                        <div>
                                            <input type="checkbox" name="is_global" class="custom-control-input"
                                                @if (old('is_global', @$advertise->is_global)) checked @endif id="is_global">
                                            <label class="custom-control-label text--primary"
                                                for="is_global">@lang('Target for Global')?</label>
                                        </div>
                                    </div>

                                    <select name="geo_targets[]" class="form-control select2" multiple>
                                        <option value="" disabled>@lang('Select One')</option>
                                        @foreach ($countriesJson as $key => $country)
                                            <option value="{{ $key }}"
                                                @if (in_array($key, old('geo_targets', (array) @$advertise->geo_targets))) selected @endif>
                                                {{ __($country->country) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn--primary h-45 w-100" type="submit">@lang('Submit')</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.video.advertise.index') }}" />
@endpush

@push('script-lib')
    <script src="{{ asset('assets/admin/js/clockpicker.min.js') }}"></script>
@endpush

@push('style-lib')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/clockpicker.min.css') }}">
@endpush

@push('style')
    <style>
        .form-check .btn {
            margin: 0;
        }

        .form-check .btn:hover {
            opacity: 0.9;
            color: inherit !important;
        }

        label.btn {
            border: 1px solid #ced4da !important;
        }

        .form-check:has(input:checked) .btn:hover {
            color: white !important;
        }

        .form-check {
            padding: 0;
        }

        .select2-container{
            width: auto !important;
        }

        .select2-container:has(.select2-selection--single, .select2-selection--multiple){
            width: 100% !important;
        }

    </style>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict"

            $('select[name="type"]').on('change', function() {
                adVideoType($(this).val())
            })
            adVideoType($('select[name="type"]').val());

            function adVideoType(adType) {
                if (adType == 1) {
                    $('.link').removeClass('d-none');
                    $('.file').addClass('d-none');
                } else {
                    $('.link').addClass('d-none');
                    $('.file').removeClass('d-none');
                }
            }


            let originalOptions = $('[name="geo_targets[]"]').html();

            function toggleGeoTargets() {
                if ($('#is_global').is(':checked')) {
                    $('[name="geo_targets[]"]').hide().prop('disabled', true).html(
                        '<option value="all" selected>All Countries</option>');
                } else {
                    $('[name="geo_targets[]"]').show().prop('disabled', false).html(originalOptions);
                    $('.select2').select2();
                }
            }

            // Run on page load to set initial state
            toggleGeoTargets();

            // On checkbox change
            $('#is_global').on('change', function() {
                toggleGeoTargets();
            });
            $('.custom-control-label').attr('for', 'is_global');
            $('.is-daily').attr('for', 'is_daily');


            $('.clockpicker').clockpicker({
                placement: 'bottom',
                align: 'left',
                donetext: 'Done',
                autoclose: true,
            }).on('change', function() {
                const input = $(this);
                const currentTime = input.val();
                const newTime = currentTime + ":00";
                if (newTime && /\b([01]\d|2[0-3]):[0-5]\d:[0-5]\d\b/.test(newTime)) {
                    input.val(newTime);
                } else {
                    input.val(currentTime + ":00");
                }
            });

        })(jQuery);
    </script>
@endpush
