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
                                    <th>@lang('S.N.')</th>
                                    <th>@lang('Title')</th>
                                    <th>@lang('Video')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reels as $reel)
                                    <tr>
                                        <td>{{ $reels->firstItem() + $loop->index }}</td>
                                        <td>{{ __($reel->title) }}</td>
                                        <td>
                                            <a href="{{ $reel->reelVideo }}" target="_blank">
                                                {{ $reel->reelVideo }}
                                            </a>
                                        </td>
                                        <td data-action="@lang('Action')">
                                            <div class="button--group">
                                                <button class="btn btn--sm btn-outline--primary editBtn"
                                                    data-reel="{{ $reel }}"><i
                                                        class="la la-pencil"></i>@lang('Edit')</button>
                                                <button class="btn btn--sm btn-outline--info previewReel"
                                                    data-reelurl="{{ $reel->reelVideo }}">
                                                    <i class="las la-play-circle"></i> @lang('Preview')
                                                </button>
                                                <button class="btn btn--sm btn-outline--danger confirmationBtn"
                                                    data-id="{{ $reel->id }}" data-question="@lang('Are you sure to remove this reel?')"
                                                    data-action="{{ route('admin.item.reel.remove', $reel->id) }}"><i
                                                        class="la la-trash"></i>@lang('Delete')</button>
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
                @if ($reels->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($reels) }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="modal fade" id="reelsModal" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel"></h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>@lang('Title')</label>
                            <input class="form-control" name="title" type="text" required>
                        </div>
                        <div class="form-group">
                            <label>@lang('File')<span class="text--danger">*</span></label>
                            <input class="form-control" name="video" type="file" accept="video/*">
                        </div>
                        <div class="form-group">
                            <label>@lang('Short Description')</label>
                            <textarea name="description" class="form-control" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn--primary w-100 h-45" type="submit">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Video Preview Modal -->
    <div class="modal fade" id="reelPreviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Reel Preview')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <video id="videoPlayer" class="video-player" crossorigin="anonymous" controls>
                        <source id="videoSource">
                    </video>
                </div>
            </div>
        </div>
    </div>


    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <x-search-form />
    <button class="btn btn--sm btn-outline--primary addBtn"><i class="la la-plus"></i> @lang('Add New')</button>
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
            var modal = $('#reelsModal');
            $('.addBtn').on('click', function() {
                modal.find('.modal-title').text(`@lang('Add New Reel')`);
                modal.find('form').attr('action', `{{ route('admin.item.reel.store') }}`);
                modal.modal('show');
            });

            $('.editBtn').on('click', function() {
                var data = $(this).data('reel');
                modal.find('.modal-title').text(`@lang('Update Reel Video')`);
                modal.find('form').attr('action', `{{ route('admin.item.reel.store', '') }}/${data.id}`);
                modal.find('[name="title"]').val(data.title);
                modal.find('[name="description"]').val(data.description);
                modal.modal('show');
            });

            modal.on('hidden.bs.modal', function() {
                $('#reelsModal form')[0].reset();
            });


            // Reels Preview
            $(document).on('click', '.previewReel', function() {
                const videoUrl = $(this).data('reelurl');


                if (!videoUrl) {
                    notify('error', 'No reel URL provided.');
                    return;
                }

                const videoPlayer = document.getElementById('videoPlayer');
                const videoSource = document.getElementById('videoSource');


                if (window.player) {
                    window.player.destroy();
                    window.player = null;
                }


                if (window.hls) {
                    window.hls.destroy();
                    window.hls = null;
                }

                const controls = [
                    'play',
                    'play-large',
                    'progress',
                    'current-time',
                    'duration',
                    'mute',
                    'volume',
                    'fullscreen'
                ];

                if (isM3U8(videoUrl) && Hls.isSupported()) {
                    const hls = new Hls();
                    hls.loadSource(videoUrl);
                    hls.attachMedia(videoPlayer);
                    window.hls = hls;

                    hls.on(Hls.Events.MANIFEST_PARSED, function() {
                        window.player = new Plyr(videoPlayer, {
                            controls,
                            ratio: '16:9'
                        });
                    });
                } else {
                    videoPlayer.setAttribute("crossorigin", "anonymous");
                    videoSource.src = videoUrl;
                    videoPlayer.load();

                    window.player = new Plyr(videoPlayer, {
                        controls,
                        ratio: '16:9'
                    });
                }

                $('#reelPreviewModal').modal('show');
                window.player.play();

                $('#reelPreviewModal').on('shown.bs.modal', function() {
                    if (window.player) {
                        window.player.once('canplay', () => {
                            setTimeout(() => {
                                window.player.play().catch(error => console.error(
                                    'Playback error:', error));
                            }, 500);
                        });
                    }
                });

                $('#reelPreviewModal').on('hidden.bs.modal', function() {
                    if (window.player) {
                        window.player.stop();
                        window.player.destroy();
                        window.player = null;
                    }
                });
            });

            function isM3U8(url) {
                return typeof url === 'string' && url.includes('.m3u8');
            }


        })(jQuery);
    </script>
@endpush

@push('style')
    <style>
        .video-player {
            width: 100%;
            height: 100%;
        }

        .plyr__control--overlaid {
            background: #4634ff;
        }

        .plyr--video .plyr__control:hover {
            background: #2f1cf8;
        }

        .plyr--full-ui input[type="range"] {
            color: #4634ff;
        }
    </style>
@endpush
