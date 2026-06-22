@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5>@lang('Item Information')</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <span class="fw-bold">@lang('Category')</span>
                            <span>{{ __(@$item->category->name) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <span class="fw-bold">@lang('Total Views')</span>
                            <span class="total-view">{{ getAmount($totalViews) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <span class="fw-bold">@lang('Title')</span>
                            <span>{{ __($title) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <span class="fw-bold">@lang('Rating')</span>
                            <span>{{ getAmount($item->ratings) }}/10 @lang('Star')</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <div id="video-report"></div>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('breadcrumb-plugins')
    <div class="d-flex flex-wrap gap-2">
        <select class="form-control select2" name="region_name">
            <option value="">@lang('All Regions')</option>
            @foreach ($allRegions as $region)
                @if (is_string($region) && !empty($region))
                    <option value="{{ $region }}" @selected(request()->region_name == $region)>{{ __(keyToTitle($region)) }}</option>
                @endif
            @endforeach
        </select>
        <div>
            <input type="text" class="form-control bg-white text--black" id="videoViewDatePicker">
        </div>
    </div>
    <x-back :height="45" route="{{ route('admin.item.index') }}" />
@endpush

@push('script-lib')
    <script src="{{ asset('assets/admin/js/vendor/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/moment.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/daterangepicker.min.js') }}"></script>
@endpush

@push('style-lib')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/admin/css/daterangepicker.css') }}">
@endpush

@push('script')
    <script>
        $(document).ready(function() {
            "use strict";

            const start = moment().subtract(1, 'month').startOf('day');
            const end = moment();
            const dateRangeOptions = {
                startDate: start,
                endDate: end,
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 15 Days': [moment().subtract(14, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(30, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month')
                        .endOf('month')
                    ],
                    'Last 6 Months': [moment().subtract(6, 'months').startOf('month'), moment().endOf('month')],
                    'This Year': [moment().startOf('year'), moment().endOf('year')],
                },
                maxDate: moment()
            };

            const changeDatePickerText = (element, startDate, endDate) => {
                $(element).html(startDate.format('MMMM D, YYYY') + ' - ' + endDate.format('MMMM D, YYYY'));
            };

            var options = {
                series: [{
                    name: "Total Views",
                    data: [
                        @foreach ($reports as $report)
                            {{ getAmount($report) }},
                        @endforeach
                    ]
                }],
                chart: {
                    height: 450,
                    type: 'line',
                    zoom: {
                        enabled: false
                    },
                    toolbar: {
                        show: true,
                        tools: {
                            download: false
                        }
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'straight'
                },
                title: {
                    text: 'Video Report',
                    align: 'left'
                },
                grid: {
                    row: {
                        colors: ['#f3f3f3', 'transparent'],
                        opacity: 0.5
                    },
                },
                xaxis: {
                    categories: [
                        @foreach ($reports as $key => $report)
                            "{{ $key }}",
                        @endforeach
                    ],
                }
            };

            var VideoViewchart = new ApexCharts(document.querySelector("#video-report"), options);
            VideoViewchart.render();

            const videoViewChat = (startDate, endDate, regionName = '') => {

                const itemId = '{{ $item->id ?? 0 }}';
                const episodeId = '{{ $videoId ?? 0 }}';
                const data = {
                    start_date: startDate.format('YYYY-MM-DD'),
                    end_date: endDate.format('YYYY-MM-DD'),
                    item_id: itemId,
                    episode_id: episodeId,
                    search: regionName
                };

                const url = @json(route('admin.item.fetch.view-report.data'));

                $.get(url, data, function(response, status) {

                    if (status === 'success') {
                        VideoViewchart.updateSeries([{
                            name: 'Total Views',
                            data: response.data[0].data
                        }]);
                        VideoViewchart.updateOptions({
                            xaxis: {
                                categories: response.created_on,
                            }
                        });

                        $('.total-view').text(response.data.fetch_total_view);
                    }
                });
            };


            $('#videoViewDatePicker').daterangepicker(dateRangeOptions, (start, end) =>
                changeDatePickerText('#videoViewDatePicker span', start, end)
            );

            changeDatePickerText('#videoViewDatePicker span', start, end);
            videoViewChat(start, end);


            let picker;

            $('#videoViewDatePicker').on('apply.daterangepicker', (event, selectedPicker) => {
                picker = selectedPicker;
                videoViewChat(picker.startDate, picker.endDate, $('select[name="region_name"]').val());
            });

            $('select[name="region_name"]').change(function() {
                const regionName = $(this).val();
                if (picker && picker.startDate) {
                    videoViewChat(picker.startDate, picker.endDate, regionName);
                } else {
                    videoViewChat(start, end, regionName);
                }
            });
        });
    </script>
@endpush

@push('style')
    <style>
        .select2-container .select2-selection--single,
        .select2-container--default .select2-selection--single .select2-selection__rendered,
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            min-width: 300px !important;
        }

        .select2-container--default .select2-results>.select2-results__options {
            min-width: 300px !important;
            background-color: #fff;
        }

        .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
            min-width: 300px !important;
        }

        a.btn.h-45 {
            line-height: 27.5px;
            display: flex;
            align-items: center;
        }
    </style>
@endpush
