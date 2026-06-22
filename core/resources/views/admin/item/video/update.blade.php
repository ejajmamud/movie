@extends('admin.layouts.app')
@section('panel')
    <form id="uploadForm" action="{{ $route }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="resolutions-grid">
            <x-resolution-card resolutionName="three_sixty" resolution="360" qualityName="SD Quality" :update=true :videoType="$video->video_type_three_sixty ?? 1" :video="@$videoFile['three_sixty']" />
            <x-resolution-card resolutionName="four_eighty" resolution="480" qualityName="SD Quality" :update=true :videoType="$video->video_type_four_eighty ?? 1" :video="@$videoFile['four_eighty']" />
            <x-resolution-card resolutionName="seven_twenty" resolution="720" qualityName="HD Quality" :update=true :videoType="$video->video_type_seven_twenty ?? 1" :video="@$videoFile['seven_twenty']" />
            <x-resolution-card resolutionName="thousand_eighty" resolution="1080" qualityName="Full HD Quality" :update=true :videoType="$video->video_type_thousand_eighty ?? 1" :video="@$videoFile['thousand_eighty']" />
        </div>
        <button class="btn btn--primary h-45 w-100 mt-3" type="submit">@lang('Submit')</button>
    </form>

    <div class="modal fade" id="uploadProgressModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="uploadProgressModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <h5 class="modal-body__title">@lang('Uploading')...</h5>
                    <p class="modal-body__desc">
                        @lang('Just give us a moment to upload your file.')
                    </p>
                    <div class="progress" role="progressbar" aria-label="Animated striped example" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: 100%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="ffmpegModal" role="dialog" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Video Upload with FFMPEG')</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form enctype="multipart/form-data" id="ffmpegUpload">
                    @csrf
                    <div class="modal-body">
                        <div class="file-uploader">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            <p>@lang('Drag & drop your video file here')<br>@lang('or') <strong>@lang('click to browse')</strong></p>
                            <div class="file-name"></div>
                            <input type="file" name="ffmpeg_video" accept=".mp4,.mkv,.3gp" style="display: none;">
                        </div>
                        <button class="btn btn--primary w-100 h-45 mt-3" type="submit">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="chunkProgressModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="chunkProcessModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body">
                    <h4 class="modal-body__title">@lang('Video Upload with FFMPEG')</h4>
                    <div class="video__progress-thumb">
                        <div class="percent">
                            <svg>
                                <circle cx="60" cy="60" r="55"></circle>
                                <circle cx="60" cy="60" r="55" style="--percent: 0"></circle>
                            </svg>
                        </div>

                        <div class="thumb">
                            <div class="percentag">
                                0%
                            </div>
                        </div>
                    </div>
                    <div class="pt-3">
                        <h6>@lang('Converting videos in multiple resolutions')</h6>
                        <div class="multiple-video-box pt-2 d-flex flex-column gap-2">
                            <div class="video-quality-list">
                                <p class="text-center py-5">@lang('Loading...')</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    @if (gs('ffmpeg'))
        <button class="btn btn-outline--primary btn--sm ffmpegBtn" type="button"><i class="fa-solid fa-crop"></i> @lang('Upload with FFMPEG')</button>
    @endif
    <x-back route="{{ $prevUrl }}" />
@endpush

@push('style-lib')
    <link href="{{ asset('assets/global/css/plyr.min.css') }}" rel="stylesheet">
@endpush

@push('script-lib')
    <script src="{{ asset('assets/global/js/plyr.min.js') }}"></script>
@endpush


@push('script')
    <script>
        (function($) {
            "use strict"
            @if (gs('ffmpeg'))
                $('.ffmpegBtn').on('click', function(e) {
                    e.preventDefault();
                    let ffmpegModal = $("#ffmpegModal");
                    ffmpegModal.modal('show');
                });

                $('#ffmpegUpload').on('submit', function(e) {
                    e.preventDefault();
                    let ffmpegModal = $("#ffmpegModal");
                    let chunkModal = $("#chunkProgressModal");
                    let fileInput = $(this).find('[name=ffmpeg_video]')[0];
                    let file = fileInput.files[0];

                    if (!file) {
                        notify('error', "Please select a file");
                        return false;
                    }

                    const chunkSize = 5 * 1024 * 1024; // 5MB
                    const totalChunks = Math.ceil(file.size / chunkSize);
                    const fileName = file.name;
                    const uploadId = Date.now();
                    let currentChunk = 0;

                    function uploadChunk() {
                        const start = currentChunk * chunkSize;
                        const end = Math.min(start + chunkSize, file.size);
                        const chunk = file.slice(start, end);
                        const formData = new FormData();
                        formData.append('chunk', chunk);
                        formData.append('chunk_index', currentChunk);
                        formData.append('total_chunks', totalChunks);
                        formData.append('file_name', fileName);
                        formData.append('upload_id', uploadId);
                        formData.append('_token', '{{ csrf_token() }}');

                        $.ajax({
                            url: "{{ route('admin.item.video.upload.chunk') }}",
                            type: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(response) {
                                if (response.error) {
                                    notify('error', response.error);
                                    return false;
                                }

                                ffmpegModal.modal('hide');
                                chunkModal.modal('show');

                                currentChunk++;
                                let progress = Math.round((currentChunk / totalChunks) * 100);

                                $('.video__progress-thumb .percent svg circle:nth-child(2)').css('--percent', progress);
                                $('.video__progress-thumb .thumb .percentag').text(progress + '%');

                                if (currentChunk < totalChunks) {
                                    uploadChunk();
                                } else {
                                    $.ajax({
                                        url: "{{ route('admin.item.chunk.video') }}",
                                        type: 'POST',
                                        data: {
                                            _token: '{{ csrf_token() }}',
                                            file_name: fileName,
                                            upload_id: uploadId
                                        },
                                        success: function(response) {
                                            let videoPath = response.video_path;
                                            availableVideoResolutions(videoPath)
                                        },
                                    });
                                }
                            }
                        });
                    }
                    uploadChunk();
                });

                function availableVideoResolutions(videoPath) {
                    $.ajax({
                        url: "{{ route('admin.item.get.resolutions') }}",
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            video_path: videoPath,
                        },
                        success: function(response) {
                            if (response.error) {
                                notify('error', response.error);
                                $('#chunkProgressModal').modal('hide');
                                return false;
                            }

                            let resolutions = response.resolutions;
                            let multipleVideoBox = $('.multiple-video-box .video-quality-list');
                            multipleVideoBox.empty();
                            for (const [label, size] of Object.entries(resolutions)) {
                                let html = `<div class="video-quality-list__item ${label}">
                                                <div class="spinner-border spinner-border-sm me-1" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                                <span>${label}</span>
                                            </div>`;
                                multipleVideoBox.append(html);
                            }
                            multiResolution(resolutions, videoPath);
                        }
                    });
                }

                function multiResolution(resolutions, videoPath) {
                    let resolutionKeys = Object.keys(resolutions);
                    const lastKey = resolutionKeys.slice().reverse().find(res => res.includes("p"));
                    let index = 0;
                    let allResolution;

                    function sendNextRequest() {
                        let itemId = Number("{{ $item->id }}");
                        let episodeId = Number("{{ $episodeId }}");
                        let resKey = resolutionKeys[index];
                        let resolution = resolutions[resKey];
                        allResolution = resolutionKeys;
                        $.ajax({
                            url: "{{ route('admin.item.multi.resolution') }}",
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                resolution: resolution,
                                key: resKey,
                                video_path: videoPath,
                                item_id: itemId,
                                episode_id: episodeId,
                                last_key: lastKey,
                                all_resolution: allResolution
                            },
                            success: function(response) {
                                if (response.error) {
                                    notify('error', response.error);
                                    $('#chunkProgressModal').modal('hide');
                                    return false;
                                }
                                $('.multiple-video-box').find('.video-quality-list__item.' + resKey + ' ' + '.spinner-border').replaceWith(`<i class="las la-check-double me-1 text-success"></i>`);

                                if (lastKey == resKey) {
                                    $('#chunkProgressModal').modal('hide');
                                    notify('success', 'Successfully uploaded the video with FFmpeg')
                                    setTimeout(function() {
                                        window.location.href = response.redirect;
                                    }, 2000);
                                    return true;
                                }
                                index++;
                                sendNextRequest();
                            },
                        });
                    }
                    sendNextRequest();
                }
            @endif

            let resolution = {
                "1080p": 'thousand_eighty',
                "720p": 'seven_twenty',
                "480p": "four_eighty",
                "360p": "three_sixty"
            };

            $('.type-option').on('click', function() {
                const $selectorContainer = $(this).closest('.upload-type-selector');
                $selectorContainer.find('.type-option').removeClass('active');
                $(this).addClass('active');
                const $resolutionCard = $(this).closest('.resolution-card');
                $resolutionCard.find('.content-panel').removeClass('active');
                const targetPanelId = $(this).data('target');
                $('#' + targetPanelId).addClass('active');

                $(`[name=video_type_${resolution[$(this).parent().data('resolution')]}]`).val($(this).data('value'))
            });

            // Form submission handling
            $('#uploadForm').on('submit', function(e) {
                e.preventDefault();
                var modal = $('#uploadProgressModal');
                modal.modal('show');
                var formData = new FormData($(this)[0]);
                $.ajax({
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content'),
                    },
                    url: $(this).attr('action') || window.location.href,
                    method: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.error) {
                            modal.modal('hide');
                            notify('error', response.error);
                        } else {
                            setTimeout(function() {
                                modal.modal('hide');
                                window.location.href = response.redirect || "{{ route('admin.item.index') }}";
                                notify('success', response.success);
                            }, 3000);
                        }
                    },
                    error: function(xhr, status, error) {
                        let errorMessage = 'An error occurred during the upload.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMessage = xhr.responseJSON.error;
                        }

                        notify('error', errorMessage);
                        modal.modal('hide');
                    }
                });
            });


            $('.watch-video-text').on('click', function(e) {
                e.stopPropagation();
                var videoURL = $(this).closest('.file-uploader').data('video-url');
                showVideoModal(videoURL, 'Preview Video');
            });

        })(jQuery);

        // Vanilla JavaScript for file upload handling and UI
        document.addEventListener('DOMContentLoaded', function() {
            // Get all file uploader elements
            const uploaders = document.querySelectorAll('.file-uploader');

            // Function to handle the drag over event
            function handleDragOver(e) {
                e.preventDefault();
                e.stopPropagation();
                this.classList.add('drag-over');
            }

            // Function to handle the drag leave event
            function handleDragLeave(e) {
                e.preventDefault();
                e.stopPropagation();
                this.classList.remove('drag-over');
            }

            // Function to handle the drop event
            function handleDrop(e) {
                e.preventDefault();
                e.stopPropagation();
                this.classList.remove('drag-over');

                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    handleFiles(files, this);
                }
            }

            // Function to handle file selection via input
            function handleFileSelect(e) {
                const files = e.target.files;
                if (files.length > 0) {
                    handleFiles(files, this.parentElement);
                }
            }

            // Function to handle the selected files
            function handleFiles(files, uploader) {
                const file = files[0]; // Take only the first file

                // Find the actual file input and set its value
                const fileInput = uploader.querySelector('input[type="file"]');
                if (fileInput) {
                    // Create a new FileList with our file
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    fileInput.files = dataTransfer.files;
                }

                // Check if the file is a video
                if (!file.type.match('video.*')) {
                    showError(uploader, 'Please select a valid video file');
                    return;
                }

                // Display file information
                const fileNameElement = uploader.querySelector('.file-name');
                fileNameElement.textContent = file.name;
                fileNameElement.title = file.name;

                // Create progress bar if it doesn't exist
                let progressContainer = uploader.querySelector('.progress');
                let progressBar = uploader.querySelector('.progress-bar');

                if (!progressContainer) {
                    progressContainer = document.createElement('div');
                    progressContainer.className = 'progress';
                    progressBar = document.createElement('div');
                    progressBar.className = 'progress-bar';
                    progressContainer.appendChild(progressBar);
                    uploader.appendChild(progressContainer);
                }

                // Reset progress bar
                progressBar.style.width = '0%';

                // Remove any previous status messages
                const previousStatus = uploader.querySelector('.upload-status');
                if (previousStatus) {
                    previousStatus.remove();
                }

                // Simulate upload with progress
                simulateUpload(file, progressBar, uploader);
            }

            // Function to simulate file upload with progress
            function simulateUpload(file, progressBar, uploader) {
                let width = 0;
                const duration = 3000; // 3 seconds for simulation
                const interval = 50; // Update every 50ms
                const increment = (interval / duration) * 100;

                const simulateProgress = setInterval(() => {
                    width += increment;
                    if (width >= 100) {
                        width = 100;
                        clearInterval(simulateProgress);
                        uploadComplete(uploader, file);
                    }
                    progressBar.style.width = width + '%';
                }, interval);
            }

            // Function to show upload completion with clickable text to watch video
            function uploadComplete(uploader, file) {
                // Remove the progress bar
                const progressContainer = uploader.querySelector('.progress');
                if (progressContainer) {
                    progressContainer.remove();
                }

                // Remove the upload instructions and icons
                const uploadIcons = uploader.querySelectorAll('i, p:not(.file-name)');
                uploadIcons.forEach(element => {
                    element.style.display = 'none';
                });

                // Create URL for the video file
                const videoURL = URL.createObjectURL(file);

                // Store the video URL as a data attribute on the uploader
                uploader.dataset.videoUrl = videoURL;

                // Clear previous content
                const previousWatchText = uploader.querySelector('.watch-video-text');
                if (previousWatchText) {
                    previousWatchText.remove();
                }

                // Create clickable text to watch video
                const watchVideoText = document.createElement('div');
                watchVideoText.className = 'watch-video-text';
                watchVideoText.innerHTML = `
           <i class="fa-solid fa-play text--primary"></i>
            <span>Click to watch video</span>
        `;

                // Add click event to watch the video
                watchVideoText.addEventListener('click', function(e) {
                    e.stopPropagation(); // Prevent event from bubbling
                    showVideoModal(videoURL, 'Preview Video');
                });

                // Add the clickable text
                uploader.appendChild(watchVideoText);

                // Create status message
                const statusElement = document.createElement('div');
                statusElement.className = 'upload-status upload-complete';

                statusElement.innerHTML = `
            <svg class="icon" viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            Video uploaded successfully
        `;

                // Remove any previous status messages
                const previousStatus = uploader.querySelector('.upload-status');
                if (previousStatus) {
                    previousStatus.remove();
                }

                uploader.appendChild(statusElement);

                // Add "Change Video" button
                const changeButton = document.createElement('button');
                changeButton.className = 'change-video-btn';
                changeButton.innerHTML = '<i class="las la-pen"></i> Change Video';
                uploader.appendChild(changeButton);

                // Remove drag and drop functionality
                uploader.removeEventListener('dragover', handleDragOver);
                uploader.removeEventListener('dragleave', handleDragLeave);
                uploader.removeEventListener('drop', handleDrop);
                uploader.removeEventListener('click', uploader.clickHandler);

                // Add click event for "Change Video" button
                changeButton.addEventListener('click', function(e) {
                    e.stopPropagation(); // Prevent event from bubbling
                    resetUploader(uploader);

                    // Re-enable file selection through the existing input
                    const fileInput = uploader.querySelector('input[type="file"]');
                    if (fileInput) {
                        fileInput.click();
                    }
                });
            }

            // Function to reset uploader to initial state
            function resetUploader(uploader) {
                if (uploader.dataset.videoUrl) {
                    URL.revokeObjectURL(uploader.dataset.videoUrl);
                    delete uploader.dataset.videoUrl;
                }

                const watchVideoText = uploader.querySelector('.watch-video-text');
                if (watchVideoText) {
                    watchVideoText.remove();
                }

                const statusElement = uploader.querySelector('.upload-status');
                if (statusElement) {
                    statusElement.remove();
                }

                const changeButton = uploader.querySelector('.change-video-btn');
                if (changeButton) {
                    changeButton.remove();
                }

                // Show the upload instructions and icons
                const uploadIcons = uploader.querySelectorAll('i, p:not(.file-name)');
                uploadIcons.forEach(element => {
                    element.style.display = '';
                });

                // Clear file name
                const fileNameElement = uploader.querySelector('.file-name');
                if (fileNameElement) {
                    fileNameElement.textContent = '';
                    fileNameElement.title = '';
                }

                // Reset file input value
                const fileInput = uploader.querySelector('input[type="file"]');
                if (fileInput) {
                    fileInput.value = '';
                }

                // Re-add event listeners
                uploader.addEventListener('dragover', handleDragOver);
                uploader.addEventListener('dragleave', handleDragLeave);
                uploader.addEventListener('drop', handleDrop);
                uploader.addEventListener('click', uploader.clickHandler);
            }

            // Function to show a modal with the video player


            // Function to show error messages
            function showError(uploader, message) {
                const statusElement = document.createElement('div');
                statusElement.className = 'upload-status upload-error';

                statusElement.innerHTML = `
            <svg class="icon" viewBox="0 0 24 24" width="16" height="16" stroke="#e53e3e" stroke-width="2" fill="none">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="15" y1="9" x2="9" y2="15"></line>
                <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>
            <span>${message}</span>
        `;

                const previousStatus = uploader.querySelector('.upload-status');
                if (previousStatus) {
                    previousStatus.remove();
                }

                uploader.appendChild(statusElement);

                setTimeout(() => {
                    statusElement.remove();
                }, 3000);
            }

            // Setup each uploader with event listeners
            uploaders.forEach(uploader => {
                // Get existing file input or create one if needed
                let fileInput = uploader.querySelector('input[type="file"]');
                if (!fileInput) {
                    fileInput = document.createElement('input');
                    fileInput.type = 'file';
                    fileInput.accept = 'video/*';
                    fileInput.style.display = 'none';
                    uploader.appendChild(fileInput);
                }

                fileInput.addEventListener('change', handleFileSelect);

                // Add drop zone event listeners
                uploader.addEventListener('dragover', handleDragOver);
                uploader.addEventListener('dragleave', handleDragLeave);
                uploader.addEventListener('drop', handleDrop);

                uploader.clickHandler = function() {
                    fileInput.click();
                };

                uploader.addEventListener('click', uploader.clickHandler);
            });

            // Add necessary styles
            const style = document.createElement('style');
            style.textContent = `
        .file-uploader.drag-over {
            border-color: #4634ff;
            background-color: #ebf8ff;
        }
        
        .upload-status {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 15px;
            font-size: 14px;
        }
        
        .upload-complete {
            color: #38a169;
        }
        
        .upload-error {
            color: #e53e3e;
        }
        
        .progress {
            height: 4px;
            width: 100%;
            background-color: #edf2f7;
            border-radius: 2px;
            overflow: hidden;
            margin-top: 15px;
        }
        
        .progress-bar {
            height: 100%;
            background-color: #4634ff;
            width: 0%;
            transition: width 0.2s ease;
        }
        
        .watch-video-text {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 15px;
            padding: 10px;
            background-color: #f7fafc;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .watch-video-text:hover {
            background-color: #edf2f7;
        }
        
        .watch-video-text .video-icon {
            color: #e53e3e;
        }
        
        .watch-video-text span {
            font-size: 16px;
            color: #4a5568;
        }
        
        .change-video-btn {
            margin-top: 10px;
            padding: 6px 12px;
            background: transparent;
            color: #4634ff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            border: 1px solid #4634ff;
        }
        
        .change-video-btn:hover {
            background-color: #ebf8ff;
        }
       
        /* Modal styles */
        .video-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        
        .video-modal .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            max-width: 800px;
            position: relative;
        }
        
        .close-btn {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 24px;
            cursor: pointer;
            color: #4a5568;
        }
        
        .video-title {
            margin-top: 0;
            margin-bottom: 15px;
            color: #2d3748;
        }
        
        .modal-video-player {
            width: 100%;
            max-height: 70vh;
            border-radius: 4px;
        }
    `;
            document.head.appendChild(style);
        });

        // Utility function to show notifications (this should be defined elsewhere)
        function notify(type, message) {
            // This is just a placeholder - your actual notification system may be different
            if (typeof iziToast !== 'undefined') {
                // Using iziToast if available
                iziToast[type]({
                    message: message,
                    position: 'topRight'
                });
            } else if (typeof toastr !== 'undefined') {
                // Using toastr if available
                toastr[type](message);
            } else {
                // Fallback to alert
                if (type === 'error') {
                    alert('Error: ' + message);
                } else {
                    alert(message);
                }
            }
        }




        function showVideoModal(videoURL, fileName) {
            let modal = document.getElementById('video-modal');
            if (modal) {
                document.body.removeChild(modal);
            }

            modal = document.createElement('div');
            modal.id = 'video-modal';
            modal.className = 'video-modal';

            const modalContent = document.createElement('div');
            modalContent.className = 'modal-content';

            const closeBtn = document.createElement('span');
            closeBtn.className = 'close-btn';
            closeBtn.innerHTML = '&times;';
            closeBtn.addEventListener('click', function() {
                document.body.removeChild(modal);
            });

            const title = document.createElement('h5');
            title.textContent = fileName;
            title.className = 'video-title';

            const videoContainer = document.createElement('div');
            videoContainer.className = 'plyr-container';

            const videoElement = document.createElement('video');
            videoElement.className = 'modal-video-player';
            videoElement.src = videoURL;
            videoElement.controls = true;
            videoElement.autoplay = true;

            // Add Plyr-specific attributes
            videoElement.setAttribute('data-plyr-config', '{"autoplay": true}');
            videoElement.setAttribute('playsinline', '');

            videoContainer.appendChild(videoElement);
            modalContent.appendChild(closeBtn);
            modalContent.appendChild(title);
            modalContent.appendChild(videoContainer);
            modal.appendChild(modalContent);

            document.body.appendChild(modal);

            // Initialize Plyr player
            const player = new Plyr(videoElement, {
                autoplay: true,
                controls: ['play-large', 'play', 'progress', 'current-time', 'volume', 'captions', 'settings', 'pip', 'airplay', 'fullscreen']
            });

            // Close modal when clicking outside content
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    document.body.removeChild(modal);
                }
            });
        }
    </script>
@endpush


@push('style')
    <style type="text/css">
        .upload-section {
            background-color: #f8fafc;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            border: 1px solid #e2e8f0;
        }

        .section-title {
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 20px;
            color: #2d3748;
        }

        .resolutions-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .resolution-card {
            background-color: #fff;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            padding: 20px;
            transition: all 0.2s ease;
        }

        .resolution-card:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        }

        .resolution-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .resolution-badge {
            background-color: #4634ff;
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }

        .upload-type-selector {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }

        .type-option {
            flex: 1;
            padding: 10px;
            background-color: #fff;
            border: 2px solid #e2e8f0;
            border-radius: 6px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 14px;
        }

        .type-option.active {
            border-color: #4634ff;
            background-color: #ebf8ff;
        }

        .file-uploader {
            border: 2px dashed #cbd5e0;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 10px;
        }

        .file-uploader:hover {
            border-color: #4634ff;
            background-color: #f0f9ff;
        }

        .link-input {
            margin-top: 10px;
        }

        input[type="text"],
        input[type="url"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #e2e8f0;
            border-radius: 6px !important;
            font-size: 16px;
            transition: border 0.2s ease;
        }

        input[type="text"]:focus,
        input[type="url"]:focus {
            border-color: #4634ff;
            outline: none;
        }

        .input-group {
            position: relative;
        }

        .input-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #718096;
        }

        .buttons {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
        }


        .btn-primary {
            background-color: #4634ff;
            color: white;
        }

        .btn-primary:hover {
            background-color: #3182ce;
        }

        .btn-outline {
            background-color: white;
            border: 1px solid #e2e8f0;
            color: #4a5568;
        }

        .btn-outline:hover {
            background-color: #f7fafc;
        }

        .file-name {
            margin-top: 10px;
            font-size: 14px;
            color: #4a5568;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .progress {
            height: 4px;
            width: 100%;
            background-color: #edf2f7;
            border-radius: 2px;
            overflow: hidden;
            margin-top: 15px;
        }

        .progress-bar {
            height: 100%;
            background-color: #4634ff;
            width: 0%;
            transition: width 0.3s ease;
        }

        .upload-complete {
            color: #38a169;
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 15px;
            font-size: 15px;
        }

        .icon {
            display: inline-block;
            width: 18px;
            height: 18px;
            stroke-width: 0;
            stroke: currentColor;
            fill: currentColor;
        }

        .content-panel {
            display: none;
        }

        .content-panel.active {
            display: block;
        }

        @media (max-width: 768px) {
            .resolutions-grid {
                grid-template-columns: 1fr;
            }
        }


        .upload {
            margin-right: auto;
            margin-left: auto;
            width: 100%;
            height: 200px;
            margin-top: 20px;
            border: 3px dashed #929292;
            line-height: 200px;
            font-size: 18px;
            line-height: unset !important;
            display: table;
            text-align: center;
            margin-bottom: 20px;
            color: #929292;
        }

        .upload:hover {
            border: 3px dashed #04abf2;
            cursor: pointer;
            color: #04abf2;
        }

        .upload.hover {
            border: 3px dashed #04abf2;
            cursor: pointer;
            color: #04abf2;
        }

        .upload>div {
            display: table-cell;
            vertical-align: middle;
        }

        .upload>div h4 {
            padding: 0;
            margin: 0;
            font-size: 25px;
            font-weight: 700;
            font-family: Lato, sans-serif;
        }

        .upload>div p {
            padding: 0;
            margin: 0;
            font-family: Lato, sans-serif;
        }

        .upload-video-file {
            opacity: 0;
            position: fixed;
        }

        .video-quality .nav-link {
            border: 1px solid #0d6efd;
        }

        .video-quality {
            gap: 10px !important;
        }

        .plyr__control--overlaid,
        .plyr--video .plyr__control:focus-visible,
        .plyr--video .plyr__control:hover,
        .plyr--video .plyr__control[aria-expanded=true] {
            background: #4634ff;
        }

        .plyr--full-ui input[type=range] {
            color: #4634ff
        }

        .plyr__menu__container .plyr__control[role=menuitemradio][aria-checked=true]:before {
            background: #4634ff;
        }

        .watch-video-text i {
            color: #{{ gs('base_color') }};
        }


        .progress.chunk-progress {
            margin-top: 5px !important;
        }

        .spinner-border.convert-snipper {
            height: 25px;
            width: 25px;
        }

        .multiple-video-box .la-check-double {
            color: #28c76f !important;
        }

        #chunkProgressModal .progress {
            height: 18px;
        }
    </style>
@endpush
