@extends('Template::layouts.master')
@section('content')
    @php
        $requestContent = getContent('item_request.content', true);
    @endphp
    <div class="card-area pt-80 mb-5">
        <div class="container">
            <div class="row g-4 justify-content-center">
                <!-- Search Section -->
                <div class="col-xxl-8 col-xl-10">
                    <div class="card custom--card endScroll">
                        <div class="card-body text-center">
                            <h4 class="card-title mb-xl-4 mb-xm-3 mb-2">{{ __(@$requestContent->data_values->heading) }}</h4>
                            <div class="input-group text-start mb-xl-4 mb-xm-3 mb-2">
                                <input type="search" class="form-control" id="movieSearch"
                                    placeholder="@lang('Search here')...">
                                <button class="btn btn--base fs-16" type="button" id="searchButton" disabled>
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                            <p class="card-text fs-14">{{ __(@$requestContent->data_values->content) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <section class="section mt-80 mb-80" data-section="top">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="section-header">
                        <h2 class="section-title">@lang('Recently Added Item')</h2>
                    </div>
                </div>
            </div>
            <div class="loader-wrapper d-none py-5">
                <div class="loader-pre"></div>
            </div>
            <div class="row mb-none-30 g-lg-4 g-3" id="movieCollection">
                @forelse($recentItems as $recent)
                    <div class="col-xxl-2 col-md-3 col-4 col-xs-6 mb-30">
                        <div class="movie-card" data-text="{{ $recent->versionName }}">
                            <div class="movie-card__thumb thumb__2">
                                <img class="lazy-loading-img"
                                    data-src="{{ getImage(getFilePath('item_portrait') . '/' . $recent->image->portrait) }}"
                                    src="{{ asset('assets/global/images/lazy.png') }}" alt="@lang('image')">
                                <a class="icon" href="{{ route('watch', $recent->slug) }}"><i class="fas fa-play"></i></a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center my-5">
                        <i class="las text-muted la-4x la-clipboard-list"></i><br>
                        <h4 class="mt-2 text-muted">@lang('Item not found!')</h4>
                    </div>
                @endforelse
            </div>
        </div>
    </section>


    <!-- Movie Request Modal -->
    <div class="modal alert-modal fade" id="requestModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content py-0">
                <div class="modal-header">
                    <h5 class="modal-title item-heading"></h5>
                </div>
                <div class="modal-body">
                    <form id="movieRequestForm" action="{{ route('user.request.item.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="track_id" id="itemId">
                        <input type="hidden" name="item" id="itemHeading">
                        <input type="hidden" name="image_path" id="imagePath">
                        <input type="hidden" name="overview" id="overview">
                        <div class="form-group my-3">
                            <label class="text-start">@lang('Why do you recommend this item?')</label>
                            <textarea name="recommend" class="form-control form--control" required>{{ old('recommend') }}</textarea>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn--dark" data-bs-dismiss="modal" type="button">@lang('No')</button>
                            <button type="submit" class="btn btn--base" id="submitRequest">@lang('Submit Request')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('style-lib')
    <link rel="stylesheet" href="{{ asset('assets/global/css/select2.min.css') }}">
@endpush

@push('script-lib')
    <script src="{{ asset('assets/global/js/select2.min.js') }}"></script>
@endpush

@push('style')
    <style>
        #searchButton {
            border: none;
        }

        .alert-modal .modal-dialog {
            text-align: left;
        }
    </style>
@endpush


@push('script')
    <script>
        $(document).ready(function() {
            const searchInput = $("#movieSearch");
            const searchButton = $("#searchButton");

            let typingTimer;
            const debounceTime = 1000;

            searchButton.prop('disabled', true);
            function performSearch() {
                let query = searchInput.val().trim();

                if (query.length < 3) {
                    searchButton.prop('disabled', true);
                    notify('warning', 'Please enter at least 3 characters to search.');
                    return;
                }

                $('.loader-wrapper').removeClass('d-none');
                $('#movieCollection').html('');
                const url = `{{ route('user.request.item.search') }}?query=${query}`;
                const sectionTitle = `{{ __('Searching item for') }} : ` + query;

                $.get(url, function(response) {
                    searchButton.prop('disabled', false);
                    $('.loader-wrapper').addClass('d-none');
                    if (response.html) {
                        $('#movieCollection').html(response.html);
                        scrollToElement('.endScroll', 630);
                        lazyLoadingImageElement();
                        $('.section-title').text(sectionTitle);
                    } else {
                        notify('error', 'No results found.');
                    }
                }).fail(function() {
                    searchButton.prop('disabled', false);
                    notify('error', 'Failed to fetch search results. Please try again.');
                });
            }

            function scrollToElement(selector, offsetFromBottom) {
                const targetElement = $(selector);
                if (targetElement.length) {
                    const targetPosition = targetElement.offset().top;
                    const elementHeight = targetElement.outerHeight();
                    const windowHeight = $(window).height();
                    const scrollPosition = targetPosition + elementHeight - windowHeight + offsetFromBottom;
                    $('html, body').animate({
                        scrollTop: scrollPosition,
                        easing: 'linear'
                    }, 1000);
                } else {
                    console.warn('Target element not found:', selector);
                }
            }

            searchInput.on('input', function() {
                clearTimeout(typingTimer);
                typingTimer = setTimeout(performSearch, debounceTime);
            });

            searchButton.on('click', performSearch);

            searchInput.on('keypress', function(e) {
                if (e.which == 13) {
                    searchButton.click();
                }
            });

            let modal = $('#requestModal');
            $('#movieCollection').on('click', '.requestToItem', function() {
                const itemId = $(this).data('item-id');
                const heading = $(this).data('heading');
                const imagePath = $(this).data('src');
                const overview = $(this).data('overview');

                $('#itemId').val(itemId);
                $('#itemHeading').val(heading);
                $('#imagePath').val(imagePath);
                $('#overview').val(overview);
                modal.find('.item-heading').text(heading);

                modal.modal('show');

                // Reset the form
                $('#movieRequestForm').trigger('reset');
                $('#recommend').val('');
            });
            lazyLoadingImageElement();

            function lazyLoadingImageElement() {
                let images = document.querySelectorAll('.lazy-loading-img');

                function preloadImage(image) {
                    const src = image.getAttribute('data-src');
                    image.src = src;
                }

                let imageOptions = {
                    threshold: 1,
                    border: "5px solid green",
                };

                const imageObserver = new IntersectionObserver((entries, imageObserver) => {
                    entries.forEach(entry => {
                        if (!entry.isIntersecting) {
                            return;
                        } else {
                            preloadImage(entry.target)
                            imageObserver.unobserve(entry.target)
                        }
                    })
                }, imageOptions)
                images.forEach(image => {
                    imageObserver.observe(image)
                });

            }

        });
    </script>
@endpush

@push('style')
    <style>
        .fs-14 {
            font-size: 14px;
        }

        .movie-title {
            display: -webkit-box !important;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .item-heading {
            color: hsl(var(--base));
        }

        #movieRequestForm .form-control {
            background-color: #0c0809;
        }

        .loader-wrapper {
            position: unset;
            background: none;
        }
    </style>
@endpush
