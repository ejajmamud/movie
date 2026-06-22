@extends('admin.layouts.app')
@section('panel')
    @push('topBar')
        @include('admin.advertise.top_bar')
    @endpush
    <div class="row">
        <div class="col-lg-12">
            <div class="card  ">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table--light style--two table">
                            <thead>
                                <tr>
                                    <th>@lang('Ads Type')</th>
                                    <th>@lang('Ads Format')</th>
                                    <th>@lang('URL')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ads as $ad)
                                    <tr>
                                        <td>
                                            @if ($ad->type == 1)
                                                <span>@lang('Link')</span>
                                            @else
                                                <span>@lang('Video')</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($ad->ad_format == Status::FORMAT_SKIPABLE)
                                                <span>@lang('Skipable')</span>
                                            @else
                                                <span>@lang('Non-Skipable')</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($ad->type == 1)
                                                <a href="{{ $ad->content->link }}"
                                                   target="_blank">{{ $ad->content->link }}</a>
                                            @else
                                                <a href="{{ $ad->videoAds }}"
                                                   target="_blank">{{ strLimit($ad->videoAds, 100) }}</a>
                                            @endif
                                        </td>
                                        <td data-action="@lang('Action')">
                                            <div class="button--group">
                                                <a href="{{ route('admin.video.advertise.edit', $ad->id) }}" class="btn btn--sm btn-outline--primary">
                                                    <i class="la la-pencil"></i>@lang('Edit')
                                                </a>
                                                <button class="btn btn--sm btn-outline--info previewVideo" data-type="{{ $ad->type }}" data-adsurl="{{ $ad->type == 2 ? $ad->videoAds : $ad->content->link }}">
                                                    <i class="las la-play-circle"></i> @lang('Preview')
                                                </button>
                                                <button class="btn btn--sm btn-outline--danger confirmationBtn" data-id="{{ $ad->id }}" data-question="@lang('Are you sure to remove this advertise?')" data-action="{{ route('admin.video.advertise.remove', $ad->id) }}">
                                                    <i class="la la-trash"></i>@lang('Delete')
                                                </button>
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
                @if ($ads->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($ads) }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Video Preview Modal -->
    <div class="modal fade" id="videoPreviewModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Video Preview')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <video id="player" class="video-player">
                    </video>
                </div>
            </div>
        </div>
    </div>

    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <a href="{{ route('admin.video.advertise.form') }}" class="btn btn--sm btn-outline--primary"><i class="la la-plus"></i>
        @lang('Add New')</a>
@endpush

@push('style-lib')
    <link rel="stylesheet" href="{{ asset('assets/global/css/plyr.min.css') }}">
@endpush

@push('script-lib')
    <script src="{{ asset('assets/global/js/plyr.min.js') }}"></script>
    <script src="{{ asset('assets/global/js/hls.min.js') }}"></script>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";

            var modal = $('#advertiseModal');
            $('.addBtn').on('click', function() {
                modal.find('.modal-title').text(`@lang('Add Video Advertise')`);
                modal.find('form').attr('action', `{{ route('admin.video.advertise.store') }}`);
                var type = modal.find('select[name=type]').val('').change();
                if (type == 1) {
                    $('.link').removeClass('d-none');
                    $('.file').addClass('d-none');
                } else {
                    $('.link').addClass('d-none');
                    $('.file').removeClass('d-none');
                }
                modal.modal('show');
            });

            $('.editBtn').on('click', function() {
                var data = $(this).data();
                modal.find('.modal-title').text(`@lang('Update Video Advertise')`);
                modal.find('form').attr('action', `{{ route('admin.video.advertise.store', '') }}/${data.id}`);
                modal.find('select[name=type]').val(data.type).change();
                if (data.type == 1) {
                    $('.link').removeClass('d-none');
                    $('.file').addClass('d-none');
                } else {
                    $('.link').addClass('d-none');
                    $('.file').removeClass('d-none');
                }
                modal.modal('show');
            });

            $('#type').on('change', function() {
                if ($(this).val() == 1) {
                    $('.link').removeClass('d-none');
                    $('.file').addClass('d-none');
                } else {
                    $('.link').addClass('d-none');
                    $('.file').removeClass('d-none');
                }
            }).change();


            modal.on('hidden.bs.modal', function() {
                $('#advertiseModal form')[0].reset();
            });


            let player = new Plyr('#player', {
                controls: ['play', 'progress', 'current-time', 'mute', 'volume', 'settings', 'fullscreen'],
                ratio: '16:9',
            });
            $('.previewVideo').on('click', function(e) {
                e.preventDefault();
                let modal = $('#videoPreviewModal');
                let url = $(this).data('adsurl');
                let videoType = $(this).data('type');

                player.source = {
                    type: 'video',
                    sources: [{
                        src: url,
                        type: 'video/mp4',
                    }, ],
                };
                const video = document.querySelector('video');
                if (videoType == 1) {
                    if (!video.hasAttribute('crossorigin')) {
                        video.setAttribute('crossorigin', 'anonymous');
                        player.media.load();
                    }
                } else if (video.hasAttribute('crossorigin')) {
                    video.removeAttribute('crossorigin');
                }
                player.play();
                modal.modal('show');
            });

            modal.on('hidden.bs.modal', function() {
                player.pause();
                player.source = {
                    type: 'video',
                    sources: []
                };
            });
            // Advertise Video Preview
        })(jQuery);
    </script>
@endpush


@push('style')
    <style>
        .video-player {
            width: 100%;
            height: 100%;
        }

        .plyr--full-ui input[type="range"] {
            color: #4634ff;
        }

        .plyr--video .plyr__control:hover {
            background: #4634ff;
        }
    </style>
@endpush
