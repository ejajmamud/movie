@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="live-comments-container">
                        <div class="comments-list">
                            @forelse ($comments as $comment)
                                <div class="comment-item d-flex align-items-start p-3 border-bottom">
                                    <div class="flex-shrink-0">
                                        <img src="{{ getImage(getFilepath('userProfile') . '/' . $comment->user->image, getFileSize('userProfile'), true) }}"
                                            class="user-avatar rounded-circle" alt="{{ $comment->user->fullname }}">
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0 fw-bold">{{ $comment->user->fullname }}</h6>
                                                <small class="text-muted"> {{ showDateTime($comment->created_at) }}</small>
                                            </div>
                                        </div>
                                        <p class="mb-0 mt-2 comment-text">{{ @$comment->comment }}</p>
                                    </div>
                                    <span class="confirmationBtn cursor-pointer text--danger align-self-center"
                                        data-question="@lang('Are you sure to delete this live comment?')"
                                        data-action="{{ route('admin.television.channel.live.comment.delete', [$tvChannel->id, $comment->id]) }}">
                                        <i class="fas fa-trash-alt"></i>
                                    </span>
                                </div>
                            @empty
                                <div class="no-more-data text-center py-3"> {{ __($emptyMessage) }}</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            @if ($comments->hasPages())
                <div class=" py-4">
                    {{ paginateLinks($comments) }}
                </div>
            @endif
        </div>
    </div>

    <!-- Bulk Delete Modal -->
    <div class="modal fade" id="bulkDeleteModal" tabindex="-1" role="dialog" aria-labelledby="bulkDeleteModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bulkDeleteModalLabel">@lang('Bulk Delete Comments')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.television.channel.live.comment.bulk-delete', $tvChannel->id) }}"
                    method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group all-bulk-wrapper">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" name="delete_all">
                                <label class="form-check-label">
                                    @lang('Delete All Live Comments')
                                </label>
                            </div>
                        </div>

                        <div class="form-group date-range-field">
                            <label>@lang('Date Range')</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <div class="las la-calendar"></div>
                                </span>
                                <input name="date" type="search"
                                    class="datepicker-here form-control bg--white pe-2 date-range"
                                    placeholder="@lang('Start Date - End Date')" autocomplete="off" value="{{ request()->date }}">
                            </div>
                        </div>
                        <p class="text--danger mt-3">
                            <i class="las la-exclamation-triangle"></i>
                            <span class="warning-message">
                                @lang('All comments between selected dates will be permanently deleted!')
                            </span>
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--dark" data-bs-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="btn btn--danger">@lang('Confirm')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-confirmation-modal />
@endsection
@push('breadcrumb-plugins')
    <x-search-form />
    <button class="btn btn--sm btn-outline--primary" data-bs-toggle="modal" data-bs-target="#bulkDeleteModal"><i
            class="la la-trash-alt"></i>@lang('Bulk Delete')</button>
    <x-back route="{{ route('admin.television.channel.index') }}" />
@endpush
@push('script-lib')
    <script src="{{ asset('assets/admin/js/moment.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/daterangepicker.min.js') }}"></script>
@endpush

@push('style-lib')
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/admin/css/daterangepicker.css') }}">
@endpush

@push('style')
    <style>
        .comment-item:hover {
            background-color: #f8f9fa;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            object-fit: cover;
        }

        .comment-text {
            font-size: 0.9rem;
            line-height: 1.4;
        }

        /* Custom Scrollbar */
        .comments-list::-webkit-scrollbar {
            width: 8px;
        }

        .comments-list::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .comments-list::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .comments-list::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        .date-range-field {
            transition: all 0.3s ease;
        }

        .form-check {
            margin-bottom: 1rem;
        }

        .form-check-input {
            margin-top: 0.25rem;
        }

        .warning-message {
            font-weight: 500;
        }

        .all-bulk-wrapper {
            border: 1px solid #bfbfbf;
            border-radius: 6px;
            padding: 20px 10px 0px 9px;
        }

        .cursor-pointer {
            cursor: pointer;
        }
    </style>
@endpush

@push('script')
    <script>
        $(document).ready(function() {
            "use strict";

            // Initialize date range picker
            const datePicker = $('.date-range').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    cancelLabel: 'Clear'
                },
                showDropdowns: true,
                ranges: {
                    'Today': [moment(), moment()],
                    'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                    'Last 15 Days': [moment().subtract(14, 'days'), moment()],
                    'Last 30 Days': [moment().subtract(30, 'days'), moment()],
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                        'month').endOf('month')],
                    'Last 6 Months': [moment().subtract(6, 'months').startOf('month'), moment().endOf(
                        'month')],
                    'This Year': [moment().startOf('year'), moment().endOf('year')],
                },
                maxDate: moment()
            });

            // Update date range input text
            const changeDatePickerText = (event, startDate, endDate) => {
                $(event.target).val(startDate.format('MMMM DD, YYYY') + ' - ' + endDate.format(
                    'MMMM DD, YYYY'));
            };

            // Apply date range selection
            $('.date-range').on('apply.daterangepicker', (event, picker) => {
                changeDatePickerText(event, picker.startDate, picker.endDate);
            });

            // Set initial date range if value exists
            if ($('.date-range').val()) {
                let dateRange = $('.date-range').val().split(' - ');
                $('.date-range').data('daterangepicker').setStartDate(new Date(dateRange[0]));
                $('.date-range').data('daterangepicker').setEndDate(new Date(dateRange[1]));
            }

            // DOM elements
            const deleteAll = $('[name="delete_all"]');
            const dateFields = $('.date-range-field');
            const warningMessage = $('.warning-message');
            const dateRangeInput = $('.date-range');


            deleteAll.on('change', function() {
                if (this.checked) {
                    dateFields.hide();
                    warningMessage.text('@lang('All comments will be permanently deleted!')');
                    dateRangeInput.val(''); // Clear date range input
                } else {
                    dateFields.show();
                    warningMessage.text('@lang('All comments between selected dates will be permanently deleted!')');
                }
            });


            $('#bulkDeleteModal').on('hidden.bs.modal', function() {
                deleteAll.prop('checked', false);
                dateFields.show();
                dateRangeInput.val('');
                warningMessage.text('@lang('All comments between selected dates will be permanently deleted!')');
            });


            $('#bulkDeleteModal form').on('submit', function(e) {
                if (!deleteAll.is(':checked') && !dateRangeInput.val()) {
                    e.preventDefault();
                    notify('error', '@lang('Please select either "Delete All" or specify a date range')');
                }
            });

        });
    </script>
@endpush
