@extends('admin.layouts.app')
@section('panel')
    <div class="row g-3 g-lg-4 all-item-list">
        @forelse ($items as $item)
            @php
                $previewUnableItem = !$item->video || $item->status == Status::NO;
            @endphp
            <div class="col-xl-12 col-sm-6 list-column">
                <div class="video-item list-view">
                    <div class="video-item-wrapper">
                        <div class="video-item-thumb">
                            <img src="{{ getImage(getFilePath('item_portrait') . '/' . @$item->image->portrait) }}"
                                 alt="" class="poster-img">
                        </div>
                        <div class="video-item-content">
                            <h4 class="video-item-title">{{ __($item->title) }}</h4>
                            <div class="video-item-top">
                                <div class="video-item-info">
                                    <span class="category" data-bs-toggle="tooltip" title="@lang('Category')">
                                        <i class="lab la-buffer"></i>
                                        {{ __(@$item->category->name) }}
                                    </span>
                                    <span class="category" data-bs-toggle="tooltip" title="@lang('Subcategory')">
                                        <i class="las la-stream"></i> {{ __(@$item->sub_category->name) ?? 'N/A' }}
                                    </span>
                                    <span class="category" data-bs-toggle="tooltip" title="@lang('Version')">
                                        <i class="las la-tenge"></i>
                                        @if (@$item->version == Status::FREE_VERSION)
                                            @lang('Free')
                                        @elseif(@$item->version == Status::PAID_VERSION)
                                            @lang('Paid')
                                        @else
                                            @lang('Rent')
                                        @endif
                                    </span>
                                    <span class="category" data-bs-toggle="tooltip"
                                          title="{{ __(@$item->category->name) }} @lang('Type')">
                                        <i class="las la-columns"></i>
                                        @if ($item->versionName == 'Episode')
                                            <span class="text--primary">@lang('Episode Item')</span>
                                        @elseif(in_array($item->versionName, ['Free', 'Paid', 'Rent']))
                                            <span class="text--success">@lang('Single Item')</span>
                                        @else
                                            <span class="text--warning">@lang('Trailer')</span>
                                        @endif
                                    </span>
                                    <div class="category" data-bs-toggle="tooltip" title="@lang('IMDB Rating')">
                                        <i class="las la-star text--warning"></i>
                                        <span>{{ @$item->ratings }}/10</span>
                                    </div>
                                    <div class="category" data-bs-toggle="tooltip" title="@lang('Total Views')">
                                        <i class="fas fa-eye text-muted"></i>
                                        <span> @lang('Views') {{ @$item->view }}</span>
                                    </div>
                                </div>
                                <div class="item-item-badge">
                                    <span class="featured-badge">
                                        @if ($item->featured)
                                            <span class="featured-badge-one">@lang('Featured')</span>
                                        @else
                                            <span class="featured-badge-two">@lang('Non Featured')</span>
                                        @endif
                                    </span>
                                    <span class="status-badge">
                                        @if ($item->status == Status::ENABLE)
                                            <span class="status-badge-success">@lang('Enabled')</span>
                                        @else
                                            <span class="status-badge-disable">@lang('Disabled')</span>
                                        @endif
                                    </span>
                                </div>
                            </div>

                            <div class="video-item-list mb-xl-3">
                                <div class="info-wrapper">
                                    <span class="title">@lang('Preview Text')</span>
                                    <p class="text">@php echo strLimit($item->preview_text, 400); @endphp</p>
                                </div>
                                <div class="info-wrapper">
                                    <span class="title">@lang('Video Genres')</span>
                                    <span class="text">
                                        @foreach (explode(',', @$item->team->genres) as $genre)
                                            {{ __($genre) }}@if (!$loop->last)
                                                ,
                                            @endif
                                        @endforeach
                                    </span>
                                </div>
                                <div class="info-wrapper">
                                    <span class="title">@lang('Video Tags')</span>
                                    <span class="text">
                                        @foreach (explode(',', $item->tags) as $index => $tag)
                                            @if ($index < 15)
                                                {{ __($tag) }}
                                                @if (!$loop->last && $index < 14)
                                                    ,
                                                @endif
                                            @endif
                                        @endforeach
                                        @if ($index > 15)
                                            <span class="text--primary see-more-tags" data-bs-toggle="modal"
                                                  data-bs-target="#castModal"
                                                  data-tags="{{ implode(',', array_slice(explode(',', $item->tags), 15)) }}">
                                                [@lang('..See More')]
                                            </span>
                                        @endif
                                    </span>
                                </div>
                                <div class="info-wrapper">
                                    <span class="title">@lang('Video Cast')</span>
                                    <span class="text">
                                        @foreach (explode(',', $item->team->casts) as $index => $cast)
                                            @if ($index < 15)
                                                {{ __($cast) }}
                                                @if (!$loop->last && $index < 14)
                                                    ,
                                                @endif
                                            @endif
                                        @endforeach
                                        @if ($index > 15)
                                            <span class="text--primary see-more-casts" data-bs-toggle="modal"
                                                  data-bs-target="#castModal"
                                                  data-casts="{{ implode(',', array_slice(explode(',', $item->team->casts), 15)) }}">
                                                [@lang('..See More')]
                                            </span>
                                        @endif
                                    </span>
                                </div>
                                <div class="info-wrapper">
                                    <span class="title">@lang('Languages')</span>
                                    <span class="text">
                                        @foreach (explode(',', @$item->team->language) as $language)
                                            {{ __($language) }}@if (!$loop->last)
                                                ,
                                            @endif
                                        @endforeach
                                    </span>
                                </div>
                            </div>
                            <div class="see-more">@lang('Show More')</div>

                            <div class="actions-section-wrapper">
                                <div class="actions-section-btn">
                                    @lang('Actions') <i class="fas fa-ellipsis-h"></i>
                                </div>
                                <div class="actions-section">
                                    <a href="{{ route('admin.item.edit', $item->id) }}" class="btn btn--primary">
                                        <i class="la la-pencil"></i> @lang('Edit')
                                    </a>
                                    @if ($item->item_type == Status::EPISODE_ITEM)
                                        <a class="btn btn-outline--warning"
                                           href="{{ route('admin.item.episodes', $item->id) }}">
                                            <i class="las la-list"></i> @lang('Episodes')
                                        </a>
                                    @else
                                        @if ($item->video)
                                            <a class="btn btn-outline--success"
                                               href="{{ route('admin.item.updateVideo', $item->id) }}">
                                                <i class="las la-cloud-upload-alt"></i> @lang('Update Video')
                                            </a>
                                            <a class="btn btn-outline--warning"
                                               href="{{ route('admin.item.ads.duration', $item->id) }}">
                                                <i class="lab la-buysellads"></i> @lang('Update Ads')
                                            </a>
                                            <a class="btn btn-outline--danger"
                                               href="{{ route('admin.item.subtitle.list', [$item->id, '']) }}">
                                                <i class="las la-file-audio"></i> @lang('Config Subtitle')
                                            </a>
                                            <a class="btn btn-outline--primary"
                                               href="{{ route('admin.item.report', [$item->id, '']) }}">
                                                <i class="las la-chart-area"></i> @lang('View Report')
                                            </a>
                                        @else
                                            <a class="btn btn-outline--warning"
                                               href="{{ route('admin.item.uploadVideo', $item->id) }}">
                                                <i class="las la-cloud-upload-alt"></i> @lang('Upload Video')
                                            </a>
                                        @endif
                                    @endif

                                    <button type="button" class="btn btn-outline--info confirmationBtn"
                                            data-action="{{ route('admin.item.send.notification', $item->id) }}"
                                            data-question="@lang('Are you sure to send notifications to all users?')" href="javascript:void(0)"> <i
                                           class="las la-bell"></i>
                                        @lang('Send Notification')
                                    </button>
                                    <a href="{{ route('admin.item.seo', $item->id) }}"
                                       class="btn btn-outline--secondary">
                                        <i class="la la-cog"></i> @lang('SEO Setting')
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @empty
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-center py-5">
                            {{ __($emptyMessage) }}
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
        @if ($items->hasPages())
            <div class="py-4">
                @php echo paginateLinks($items) @endphp
            </div>
        @endif
        </div>

        <!--See More Modal -->
        <div class="modal fade" id="castModal" tabindex="-1" aria-labelledby="castModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="castModalLabel">@lang('Additional Data')</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="las la-times"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <ul id="additionalCastList" class="list-unstyled"></ul>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn--dark" data-bs-dismiss="modal">@lang('Close')</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel"
             aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="uploadModalLabel">@lang('Import Your Items')</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="las la-times"></i>
                        </button>
                    </div>
                    <form action="{{ route('admin.item.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <div id="dropZone">
                                    <p id="dropZoneText">@lang('Drag and drop a file here or click to select one')</p>
                                    <input type="file" class="form-control" name="file" id="fileInput"
                                           accept=".csv">
                                </div>
                            </div>
                            <code>
                                @lang('Please download the CSV template file below. This template contains the required columns and sample data to guide you in preparing your CSV file.')
                                <a href="{{ asset('assets/images/items.csv') }}" download>@lang('Click Here')</a>
                            </code>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn--dark" data-bs-dismiss="modal">@lang('Close')</button>
                            <button type="submit" class="btn btn--primary">@lang('Upload')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="offcanvas offcanvas-end" tabindex="-1" id="filterCanvas" aria-labelledby="filterCanvasLabel">
            <div class="offcanvas-header">
                <h5 id="filterCanvasLabel">@lang('Filter Here Your Video Items')</h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <form class="">
                    <div class="form-group">
                        <x-search-key-field />
                    </div>
                    <div class="form-group">
                        <select class="form-control select2" data-minimum-results-for-search="-1" name="category_id">
                            <option value="">@lang('All Category Videos')</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(request()->category_id == $category->id)>
                                    {{ __(@$category->name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <select class="form-control select2" data-minimum-results-for-search="-1" name="item_type">
                            <option value="">@lang('All Type Videos')</option>
                            <option value="1" @selected(request()->item_type == 1)> @lang('Single')</option>
                            <option value="2" @selected(request()->item_type == 2)> @lang('Episode')</option>
                            <option value="3" @selected(request()->item_type == 3)> @lang('Trailer')</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <select class="form-control select2" data-minimum-results-for-search="-1" name="version">
                            <option value="">@lang('All Version Videos')</option>
                            <option value="0" @selected(request()->version != null && request()->version == Status::FREE_VERSION)>@lang('Free')</option>
                            <option value="1" @selected(request()->version == Status::PAID_VERSION)>@lang('Paid')</option>
                            <option value="2" @selected(request()->version == Status::RENT_VERSION)>@lang('Rent')</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <select class="form-control select2" data-minimum-results-for-search="-1" name="featured">
                            <option value="">@lang('All Feature Video')</option>
                            <option value="1" @selected(request()->featured == Status::YES)>@lang('Featured')</option>
                            <option value="0" @selected(request()->featured != null && request()->featured == Status::NO)>@lang('Non Featured')</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <button class="btn btn--primary h-45 w-100" type="submit"><i
                               class="las la-lg la-filter"></i>@lang('Filter')</button>
                    </div>
                </form>
            </div>
        </div>

        <x-confirmation-modal />

    @endsection

    @push('breadcrumb-plugins')
        <button class="btn btn-outline-info listView active d-none d-xl-inline-block" data-view="list">
            <i class="las la-list"></i> @lang('List')
        </button>
        <button class="btn btn-outline-info gridView d-none d-xl-inline-block" data-view="grid">
            <i class="las la-th-large"></i> @lang('Grid')
        </button>
        <a class="btn btn-outline--primary" href="{{ route('admin.item.create') }}"><i
               class="la la-plus"></i>@lang('Add New')</a>
        <button class="btn btn-outline--dark" type="button" data-bs-toggle="modal" data-bs-target="#uploadModal"><i
               class="las la-lg la-file-csv"></i>@lang('Import')</button>

        <button class="btn btn-outline--danger" type="button" data-bs-toggle="offcanvas" data-bs-target="#filterCanvas"
                aria-controls="offcanvasRight"><i class="las la-lg la-filter"></i>@lang('Filter')</button>
    @endpush

    @push('style')
        <style>
            :root {
                --primary: 245 100% 60%;
                --success: 147 67% 47%;
                --warning: 43 96% 56%;
                --danger: 0 84% 60%;
                --white: 0 0% 100%;
                --black: 0 0% 0%;
                --th-w: 200px;
                --gap: 20px;
            }

            .gridView.active,
            .listView.active {
                color: #fff !important;
            }

            .see-more {
                cursor: pointer;
                color: #4634ff;
                font-size: 0.8rem;
                font-weight: 500;
                display: flex;
                align-items: center;
                gap: 4px;
                margin-block: 12px;
                display: none;
            }

            .video-item.grid-view .see-more {
                display: block;
            }

            .item-item-badge {
                display: flex;
                flex-wrap: wrap;
                gap: 4px;
            }

            .video-item {
                padding: 16px;
                background-color: hsl(var(--white));
                border-radius: 12px;
            }

            .video-item-wrapper {
                display: flex;
                justify-content: space-between;
            }

            .video-item-thumb {
                width: var(--th-w);
                border-radius: 8px;
                overflow: hidden;
            }

            .video-item-thumb img {
                height: 100%;
                width: 100%;
                object-fit: cover;
            }

            .video-item-content {
                width: calc(100% - var(--th-w) - var(--gap));
            }

            .video-item-title {
                margin-bottom: 12px;
                color: hsl(var(--black));
            }

            .info-wrapper {
                display: flex;
                flex-wrap: wrap;
                align-items: flex-start;
                font-size: 0.875rem;
            }

            .info-wrapper .title {
                width: 120px;
                font-weight: 600;
                color: hsl(var(--black) / .75);
            }

            .info-wrapper .text {
                flex: 1;
                color: #737373;
                padding-left: 20px;
                position: relative;
            }

            .info-wrapper .text::after {
                content: ":";
                position: absolute;
                top: 0;
                left: 0;
            }

            .info-wrapper:not(:last-child) {
                margin-bottom: 8px;
            }

            .rating-section {
                display: flex;
                align-items: center;
            }

            .video-item-bottom {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .video-item-info {
                display: flex;
                align-items: center;
                gap: 12px;
                flex-wrap: wrap;
            }

            .video-item-details {
                display: inline-flex;
                align-items: center;
                gap: 12px;
                padding: 12px 16px;
                border: 1px solid hsl(var(--black) / .1);
                border-radius: 8px;
                background-color: hsl(var(--black) / .02);
            }

            .video-item-info .category {
                display: flex;
                align-items: center;
                gap: 4px;
                font-size: 0.8rem;
                padding: 4px 10px;
                line-height: 1;
                border-radius: 16px;
                font-weight: 600;
                cursor: pointer;
                background-color: #f7f7f7;
            }

            .video-item-top {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                flex-wrap: wrap;
                border-bottom: 1px solid hsl(var(--black) / .1);
                margin-bottom: 12px;
                padding-bottom: 12px;
            }


            .video-item-badge {
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .item-item-badge .featured-badge .featured-badge-one,
            .item-item-badge .featured-badge .featured-badge-two,
            .item-item-badge .status-badge .status-badge-disable,
            .item-item-badge .status-badge .status-badge-success {
                display: inline-block;
                padding: 2px 8px;
                border-radius: 4px;
                font-size: 0.75rem;
                font-weight: 400;
            }

            .item-item-badge .status-badge .status-badge-disable {
                background-color: hsl(var(--danger));
                color: hsl(var(--white));
            }

            .item-item-badge .status-badge .status-badge-success {
                background-color: hsl(var(--success));
                color: hsl(var(--white));
            }

            .item-item-badge .featured-badge .featured-badge-one {
                background-color: hsl(var(--primary));
                color: hsl(var(--white));
            }

            .item-item-badge .featured-badge .featured-badge-two {
                background-color: hsl(var(--warning));
                color: hsl(var(--white));
            }

            .actions-section {
                display: flex;
                gap: 12px;
                flex-wrap: wrap;
            }

            .actions-section .btn {
                font-size: .8rem;
                padding: 4px 12px;
            }

            .see-more-casts,
            .see-more-tags {
                cursor: pointer;
            }

            .actions-section-btn {
                display: none;
            }

            .grid-view .video-item-wrapper {
                flex-direction: column;
            }

            .grid-view .video-item-thumb {
                max-width: 100%;
                width: 100%;
                height: 240px;
            }

            .grid-view .video-item-content {
                width: 100%;
            }

            .grid-view .info-wrapper {
                flex-direction: column;
                gap: 4px;
            }

            .grid-view .info-wrapper .text {
                padding-left: 0;
            }

            .grid-view .info-wrapper .text::after {
                display: none;
            }

            .grid-view .actions-section .btn {
                flex-grow: 1;
            }

            .grid-view .video-item-list {
                max-height: 96px;
                overflow: hidden;
                transition: max-height 0.3s ease;
                position: relative;
            }

            .grid-view .video-item-list::after {
                content: "";
                width: 100%;
                height: 24px;
                position: absolute;
                background: linear-gradient(180deg, transparent 0%, white 100%);
                bottom: 0;
                left: 0;
            }

            .grid-view .expanded.video-item-list::after {
                display: none;
            }

            .grid-view.video-item {
                position: relative;
                padding: 0;
            }

            .grid-view .video-item-content {
                padding: 16px;
            }

            .grid-view .item-item-badge {
                position: absolute;
                top: 12px;
                right: 12px;
            }

            .grid-view .actions-section-wrapper {
                position: relative;
            }

            .grid-view .actions-section-btn {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 8px;
                border-radius: 6px;
                font-size: 0.8rem;
                cursor: pointer;
                padding: 10px 12px;
                line-height: 1;
                border: 1px solid #e4e4e5;
                font-weight: 600;
            }

            .grid-view .actions-section {
                flex-direction: column;
                padding: 16px;
                background-color: #fff;
                border-radius: 6px;
                border: 1px solid rgb(0, 0, 0, 10%);
                position: absolute;
                top: -6px;
                width: 100%;
                transform: translateY(-100%);
                left: 0;
                z-index: 999;
                display: none;
            }

            .grid-view .actions-section .btn {
                width: 100%;
            }

            .grid-view .actions-section .btn:not(:last-child) {
                margin-bottom: 8px;
            }

            @media(max-width: 1399px) {
                .info-wrapper .title {
                    font-size: 0.8rem;
                }

                .info-wrapper .text {
                    font-size: 0.8rem;
                }

                .item-item-badge .featured-badge .featured-badge-one,
                .item-item-badge .featured-badge .featured-badge-two,
                .item-item-badge .status-badge .status-badge-disable,
                .item-item-badge .status-badge .status-badge-success {
                    padding: 4px 6px;
                    border-radius: 4px;
                    font-size: 0.75rem;
                }
            }

            @media(max-width: 1199px) {
                .video-item-wrapper {
                    flex-direction: column;
                }

                .see-more {
                    display: block;
                }

                :root {
                    --th-w: 100%;
                }

                .video-item-thumb {
                    max-width: var(--th-w);
                    width: 100%;
                    height: 240px;
                }

                .video-item-content {
                    width: 100%;
                }

                .info-wrapper {
                    flex-direction: column;
                    gap: 4px;
                }

                .info-wrapper .text {
                    padding-left: 0;
                }

                .info-wrapper .text::after {
                    display: none;
                }

                .actions-section .btn {
                    flex-grow: 1;
                }

                .video-item-list {
                    max-height: 96px;
                    overflow: hidden;
                    transition: max-height 0.3s ease;
                    position: relative;
                }

                .video-item-list::after {
                    content: "";
                    width: 100%;
                    height: 24px;
                    position: absolute;
                    background: linear-gradient(180deg, transparent 0%, white 100%);
                    bottom: 0;
                    left: 0;
                }

                .expanded.video-item-list::after {
                    display: none;
                }

                .video-item {
                    position: relative;
                    padding: 0;
                }

                .video-item-content {
                    padding: 16px;
                }

                .item-item-badge {
                    position: absolute;
                    top: 12px;
                    right: 12px;
                }

                .actions-section-wrapper {
                    position: relative;
                }

                .actions-section-btn {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    gap: 8px;
                    border-radius: 6px;
                    font-size: 0.8rem;
                    cursor: pointer;
                    padding: 10px 12px;
                    line-height: 1;
                    border: 1px solid #e4e4e5;
                    font-weight: 600;
                }

                .actions-section {
                    flex-direction: column;
                    padding: 16px;
                    background-color: #fff;
                    border-radius: 6px;
                    border: 1px solid rgb(0, 0, 0, 10%);
                    position: absolute;
                    top: -6px;
                    width: 100%;
                    transform: translateY(-100%);
                    left: 0;
                    z-index: 999;
                    display: none;
                }

                .actions-section .btn {
                    width: 100%;
                }

                .actions-section .btn:not(:last-child) {
                    margin-bottom: 8px;
                }
            }

            #dropZone {
                border: 2px dashed #4634ff;
                padding: 40px;
                text-align: center;
                cursor: pointer;
            }

            #fileInput {
                display: none;
            }
        </style>
    @endpush

    @push('script')
        <script>
            (function($) {
                "use strict";

                var dropZone = $('#dropZone');
                var fileInput = $('#fileInput');
                var dropZoneText = $('#dropZoneText');

                fileInput.on('click', function(e) {
                    e.stopPropagation();
                });

                dropZone.on('click', function() {
                    fileInput.click();
                });

                dropZone.on('dragover', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropZone.css('border-color', '#007bff');
                });

                dropZone.on('dragleave', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropZone.css('border-color', '#cccccc');
                });

                dropZone.on('drop', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropZone.css('border-color', '#cccccc');
                    var files = e.originalEvent.dataTransfer.files;
                    fileInput.prop('files', files);
                    dropZoneText.text(files[0].name);
                });

                fileInput.on('change', function() {
                    if (fileInput.prop('files').length > 0) {
                        dropZoneText.text(fileInput.prop('files')[0].name);
                    }
                });

                let listViewColumn = 'col-xl-12 col-sm-6';
                let gridViewColumn = 'col-xxl-3 col-xl-6 col-sm-6';
                let gridColumn = 'grid-column';
                let listColumn = 'list-column';
                let listView = 'list-view';
                let gridView = 'grid-view';
                let listViewEvent = $('.listView');
                let gridViewEvent = $('.gridView');
                let itemList = $('.all-item-list');
                let sessionData = null;

                listViewEvent.on('click', function(e) {
                    e.preventDefault();
                    setListView();
                    $('.video-item-list').css('max-height', 'none');
                    sessionData = listView;
                    localStorage.removeItem('sessionData');
                    localStorage.setItem('sessionData', sessionData);
                });

                gridViewEvent.on('click', function(e) {
                    e.preventDefault();
                    setGridView();
                    $('.video-item-list').css('max-height', '96px');
                    sessionData = gridView;
                    localStorage.removeItem('sessionData');
                    localStorage.setItem('sessionData', sessionData);
                });

                function setSessionData() {
                    sessionData = localStorage.getItem('sessionData');
                    if (sessionData && sessionData == 'grid-view') {
                        setGridView();
                    } else {
                        setListView();
                    }
                }

                function setListView() {
                    itemList.find(`.${gridColumn}`).removeClass(gridViewColumn).addClass(listViewColumn);
                    itemList.find('.video-item').removeClass(gridView).addClass(listView);
                    itemList.find(`.${gridColumn}`).removeClass(gridColumn).addClass(listColumn);
                    gridViewEvent.removeClass('active');
                    listViewEvent.addClass('active');
                    $('.actions-section').css('display', 'block');
                }

                function setGridView() {
                    itemList.find(`.${listColumn}`).removeClass(listViewColumn).addClass(gridViewColumn);
                    itemList.find('.video-item').removeClass(listView).addClass(gridView);
                    itemList.find(`.${listColumn}`).removeClass(listColumn).addClass(gridColumn);
                    gridViewEvent.addClass('active');
                    listViewEvent.removeClass('active');
                    $('.actions-section').css('display', 'none');
                }

                setSessionData();

                $('.actions-section-btn').on('click', function(e) {
                    e.preventDefault();
                    $(this).not('')
                    const currentSection = $(this).siblings('.actions-section');
                    $('.actions-section').not(currentSection).slideUp(300);
                    currentSection.slideToggle(300);
                });

                $('.see-more').on('click', function(e) {
                    e.preventDefault();
                    const videoList = $(this).closest('.video-item-content').find('.video-item-list');
                    const isExpanded = videoList.css('max-height') === 'none';
                    if (isExpanded) {
                        videoList.css('max-height', '96px');
                        $(this).text('@lang('Show More')');
                    } else {
                        videoList.css('max-height', 'none');
                        $(this).text('@lang('Show Less')');
                    }
                });


                $('.see-more-casts').on('click', function() {
                    const castData = $(this).data('casts');
                    const castArray = castData.split(',');
                    const additionalCastList = $('#additionalCastList');
                    additionalCastList.empty();
                    castArray.forEach(function(cast) {
                        additionalCastList.append('<li>' + cast.trim() + '</li>');
                    });
                });

                $('.see-more-tags').on('click', function() {
                    const tagData = $(this).data('tags');
                    const tagArray = tagData.split(',');
                    const additionalTagList = $('#additionalCastList');
                    additionalTagList.empty();
                    tagArray.forEach(function(tag) {
                        additionalTagList.append('<li>' + tag.trim() + '</li>');
                    });
                });

            })(jQuery)
        </script>
    @endpush
