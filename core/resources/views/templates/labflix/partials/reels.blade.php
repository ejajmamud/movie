@foreach ($reels as $reel)
    <div class="swiper-slide" data-id="{{ $reel->id }}">
        <div class="short-reel-wrapper">
            <div class="short-reel">
                <video class="video-player plyr-video" playsinline controls data-poster="">
                    <source src="{{ @$reel->reelVideo }}" type="video/mp4" >
                </video>

                <div class="reel-content">
                    <h3 class="reel-content__title"> {{ __($reel->title) }}</h3>
                    <p class="reel-content__description">
                        {{ __($reel->description) }}
                    </p>
                </div>
            </div>
            <div class="reel-action">
                <ul class="reel-action-list">
                    <li class="reel-action-item">
                        <span class="reel-action-icon likeBtn {{ in_array(@$reel->id, $userLikesId) ? 'active' : '' }}" data-id="{{ @$reel->id }}">
                            <i class="lar la-thumbs-up"></i>
                        </span>
                        <span class="reel-action-title">@lang('Like')</span>
                    </li>
                    <li class="reel-action-item">
                        <span class="reel-action-icon unlikeBtn {{ in_array(@$reel->id, $userUnLikesId) ? 'active' : '' }}" data-id="{{ @$reel->id }}">
                            <i class="lar la-thumbs-down"></i>
                        </span>
                        <span class="reel-action-title">@lang('Dislike')</span>
                    </li>
                    <li class="reel-action-item">
                        <span class="reel-action-icon myListBtn {{ in_array(@$reel->id, $userListId) ? 'active' : '' }}" data-id="{{ @$reel->id }}">
                            <i class="las la-plus"></i>
                        </span>
                        <span class="reel-action-title">@lang('My List')</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
@endforeach
