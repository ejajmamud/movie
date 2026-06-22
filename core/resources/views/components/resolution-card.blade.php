@props(['qualityName' => '', 'resolution' => '', 'resolutionName' => '', 'update' => false, 'videoType' => '', 'video' => ''])
<div class="resolution-card">
    <div class="resolution-header">
        <span class="resolution-badge">{{ $resolution }}p</span>
        <span>{{ __($qualityName) }}</span>
    </div>
    <div class="upload-type-selector" data-resolution="{{ $resolution }}p">
        <div class="type-option file-type {{ $videoType == 1 ? 'active' : '' }}" data-target="file-panel-{{ $resolution }}p" data-value="1">
            <i class="fa-solid fa-file-video"></i>
            @lang('File Upload')
        </div>
        <div class="type-option link-type {{ $videoType == 1 ? '' : 'active' }}" data-target="link-panel-{{ $resolution }}p" data-value="0">
            <i class="fa-solid fa-link"></i>
            @lang('Video URL')
        </div>
    </div>
    <input type="hidden" name="video_type_{{ $resolutionName }}" value="{{ $videoType }}">
    <div class="content-panel {{ $videoType == 1 ? 'active' : '' }}" id="file-panel-{{ $resolution }}p">
        <div class="file-uploader" data-video-url="{{ isset($video) && $videoType == 1 ? $video : '' }}">
            <i class="fa-solid fa-cloud-arrow-up"></i>
            <p>@lang('Drag & drop your {{ $resolution }}p video file here')<br>@lang('or') <strong>@lang('click to browse')</strong></p>
            <div class="file-name"></div>
            <input type="file" name="{{ $resolutionName }}_video" accept=".mp4,.mkv,.3gp" style="display: none;" id="{{ $resolutionName }}_video">
            @if ($update && isset($video) && $video && $videoType == 1)
                <div class="watch-video-text">
                    <i class="fa-solid fa-play text--primary"></i>
                    <span>@lang('Click to watch video')</span>
                </div>
                <div class="upload-status upload-complete">
                    <svg class="icon" viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    @lang('Video uploaded')
                </div>
            @endif
        </div>
    </div>

    <div class="content-panel {{ $videoType == 1 ? '' : 'active' }}" id="link-panel-{{ $resolution }}p">
        <div class="link-input">
            <div class="input-group">
                <input type="url" name="{{ $resolutionName }}_link" placeholder="Enter {{ $resolution }}p video URL" value="{{ isset($video) && $videoType == 0 ? $video : '' }}" />
                <div class="input-icon">
                    <i class="fa-solid fa-link"></i>
                </div>
            </div>
        </div>
    </div>
</div>
