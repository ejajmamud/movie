@extends('Template::layouts.master')
@section('content')
    @php
        $requestContent = getContent('item_request.content', true);
    @endphp
    <div class="card-area my-80">
        <div class="container">
            <div class="row g-4 justify-content-center">
                <div class="col-xxl-8 col-xl-10">
                    <div class="card custom--card endScroll">
                        <div class="card-body text-center">
                            <h4 class="card-title mb-xl-4 mb-xm-3 mb-2">{{ __(@$requestContent->data_values->heading) }}</h4>

                            <div class="input-group text-start mb-xl-4 mb-xm-3 mb-2">
                                <input type="search" class="form-control" id="movieSearch"
                                    placeholder="@lang('Search here')...">
                                <button class="btn btn--base fs-16 border-0" type="button" id="searchButton" disabled>
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
    <section class="movie-section section my-80" data-section="top">
        <div class="container">
            <div class="row">
                <div class="col-xl-12">
                    <div class="section-header">
                        <h3 class="section-title">@lang('Recently Added')</h3>
                    </div>
                </div>
            </div>
            <div class="loader-wrapper d-none py-5">
                <div class="loader-pre"></div>
            </div>
            <div class="row justify-content-center gy-4" id="movieCollection">
                @foreach ($recentItems as $latest)
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-xs-6">
                        <div class="movie-item">
                            <div class="movie-thumb">
                                <img class="lazy-loading-img"
                                    data-src="{{ getImage(getFilePath('item_portrait') . '/' . $latest->image->portrait) }}"
                                    src="{{ asset('assets/global/images/lazy.png') }}" alt="movie">
                                <span class="movie-badge">{{ $latest->versionName }}</span>
                                <div class="movie-thumb-overlay">
                                    <a class="video-icon" href="{{ route('watch', $latest->slug) }}"><i
                                            class="fas fa-play"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>



    <!-- Movie Request Modal -->
    <div class="modal alert-modal fade" id="requestModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content p-0">
                <div class="modal-header">
                    <h3 class="modal-title item-heading"></h3>
                </div>
                <div class="modal-body pt-3">
                    <form id="movieRequestForm" action="{{ route('user.request.item.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="track_id" id="itemId">
                        <input type="hidden" name="item" id="itemHeading">
                        <input type="hidden" name="image_path" id="imagePath">
                        <input type="hidden" name="overview" id="overview">
                        <div class="form-group">
                            <h6 class="my-2 text-start">@lang('Why do you recommend this item?') <small class="text--danger">*</small></h6>
                            <textarea name="recommend" class="form-control form--control" rows="5" required>{{ old('recommend') }}</textarea>
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
                        scrollTop: scrollPosition
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

@push('name')
    @push('style')
        <style>
            .loader-wrapper {
                position: unset;
                background: none;
            }
        </style>
    @endpush
@endpush
