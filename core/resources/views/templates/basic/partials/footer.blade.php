@php
    $footerContent = getContent('basic_footer.content', true);
    $footerElement = getContent('basic_footer.element', orderById: true);
    $socialIcons = getContent('social_icon.element', orderById: true);
    $policyPages = getContent('policy_pages.element');
    $socialLinks = getContent('short_links.element', orderById: true);
@endphp
<footer class="footer-section footer bg-overlay-black bg_img @if (request()->routeIs('home') || request()->routeIs('category') || request()->routeIs('subCategory') || request()->routeIs('search')) d-none @endif pt-80" data-background="{{ getImage('assets/images/frontend/basic_footer/' . @$footerContent->data_values->background_image, '1920x789') }}">
    <div class="container">
        <div class="footer-top-area d-flex align-items-center justify-content-between flex-wrap">
            <div class="footer-logo">
                <a class="site-logo" href="{{ route('home') }}"><img src="{{ siteLogo() }}" alt="logo"></a>
            </div>
            <div class="social-area">
                <ul class="footer-social">
                    @foreach ($socialIcons as $item)
                        <li><a href="{{ @$item->data_values->url }}" target="_blank">@php echo @$item->data_values->social_icon @endphp</a></li>
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="footer-bottom-area">
            <div class="row justify-content-center mb-30-none">
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 mb-30">
                    <div class="footer-widget">
                        <h3 class="widget-title">@lang('About Us')</h3>
                        <p>{{ __(@$footerContent->data_values->about_us) }}</p>
                    </div>
                </div>
                <div class="col-xl-2 col-lg-2 col-md-6 col-sm-6 mb-30">
                    <div class="footer-widget">
                        <h3 class="widget-title">@lang('Categories')</h3>
                        <ul class="footer-links">
                            @foreach ($categories as $category)
                                <li><a href="{{ route('category', $category->id) }}">{{ __($category->name) }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="col-xl-2 col-lg-2 col-md-6 col-sm-4 mb-30">
                    <div class="footer-widget">
                        <h3 class="widget-title">@lang('Short Links')</h3>
                        <ul class="footer-links">

                            @forelse($socialLinks as $link)
                                <li><a href="{{ route('links', $link->slug) }}">{{ __(@$link->data_values->title) }}</a></li>
                            @empty
                            @endforelse
                        </ul>
                    </div>
                </div>
                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-8 mb-30">
                    <div class="footer-widget">
                        <h3 class="widget-title">@lang('Subscribe News Letter')</h3>
                        <p>{{ __(@$footerContent->data_values->subscribe_title) }}</p>
                        <form class="subscribe-form" method="post">
                            @csrf
                            <input name="email" type="email" placeholder="@lang('Email Address')" required>
                            <button type="submit"><i class="fas fa-paper-plane"></i></button>
                        </form>
                        <div class="download-links">
                            @foreach ($footerElement as $footer)
                                <a class="download-links__item" href="{{ @$footer->data_values->link }}" target="_blank">
                                    <img src="{{ getImage('assets/images/frontend/basic_footer/' . @$footer->data_values->store_image, '150x45') }}" alt="@lang('image')">
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="copyright-area">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-12 text-center">
                    <div class="copyright-wrapper d-flex align-items-center justify-content-between flex-wrap">
                        <div class="copyright">
                            <p>@lang('Copyright') &copy; {{ date('Y') }} @lang('All Rights Reserved By')
                                <a class="text--base" href="{{ route('home') }}">{{ gs('site_name') }}</a>
                            </p>
                        </div>
                        <div class="copyright-link-area">
                            <ul class="copyright-link">
                                @foreach ($policyPages as $policy)
                                    <li><a href="{{ route('policy.pages', $policy->slug) }}">{{ __(@$policy->data_values->title) }}</a></li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<div class="modal alert-modal" id="alertModal">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <span class="alert-icon"><i class="fas fa-question-circle"></i></span>
                <p class="modal-description">@lang('Subscription Alert!')</p>
                <p class="modal--text">@lang('Please subscribe a plan to view our paid items')</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn--dark btn--sm" data-bs-dismiss="modal" type="button">@lang('Cancel')</button>
                <a class="btn btn--base btn--sm" href="{{ route('subscription') }}">@lang('Subscribe Now')</a>
            </div>
        </div>
    </div>
</div>
