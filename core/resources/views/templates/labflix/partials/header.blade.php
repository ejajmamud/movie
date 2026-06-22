<header class="header">
    <div class="header__bottom">
        <div class="container-fluid p-0">
            <nav class="navbar navbar-expand-xl align-items-center p-0">
                <a class="site-logo site-title" href="{{ route('home') }}"><img src="{{ siteLogo() }}"
                        alt="site-logo"><span class="logo-icon"><i class="flaticon-fire"></i></span></a>
                <button class="navbar-toggler ml-auto" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent" type="button" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="Toggle navigation">
                    <span class="menu-toggle"></span>
                </button>
                <div class="navbar-collapse collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav main-menu ms-xxl-5 mx-auto">
                        <li><a href="{{ route('home') }}">@lang('Home')</a></li>
                        @foreach ($categories as $category)
                            @if ($category->subcategories->where('status', 1)->count() > 0)
                                <li class="menu_has_children">
                                    <a href="{{ route('category', $category->id) }}">{{ __($category->name) }}</a>
                                    <span><i class="las la-caret-down"></i></span>
                                    <ul class="sub-menu">
                                        @foreach ($category->subcategories as $subcategory)
                                            <li><a
                                                    href="{{ route('subCategory', $subcategory->id) }}">{{ __($subcategory->name) }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </li>
                            @else
                                <li><a href="{{ route('category', $category->id) }}">{{ __($category->name) }}</a></li>
                            @endif
                        @endforeach
                        @if (gs('genre') && json_decode(gs('genres')))
                            <li><a href="{{ route('genre') }}">@lang('Genres')</a></li>
                        @endif
                        @if (gs('tournament'))
                            <li><a href="{{ route('live.tournaments') }}">@lang('Tournaments')</a></li>
                        @endif
                        @if (gs('live_tv'))
                            <li><a href="{{ route('live.tv') }}">@lang('Live TV')</a></li>
                        @endif
                        @guest
                            <li><a href="{{ route('contact') }}">@lang('Contact')</a></li>
                        @else
                            <li class="menu_has_children">
                                <a href="javascript:void(0)">@lang('More')</a>
                                <span><i class="las la-caret-down"></i></span>
                                <ul class="sub-menu">
                                    <li><a href="{{ route('user.deposit.history') }}">@lang('Payment History')</a></li>
                                    <li><a href="{{ route('user.wishlist.index') }}">@lang('My Wishlist')</a></li>
                                    <li><a href="{{ route('user.watch.history') }}">@lang('Watch History')</a></li>
                                    @if (gs('watch_party'))
                                        <li><a href="{{ route('user.watch.party.history') }}">@lang('Watch Party')</a></li>
                                    @endif
                                    <li><a href="{{ route('user.rented.item') }}">@lang('Rented Item')</a></li>
                                    <li><a href="{{ route('short.videos', [0, 'favorite']) }}">@lang('My Reel List')</a></li>
                                    @if (gs('request_item'))
                                        <li><a href="{{ route('user.request.item.index') }}">@lang('Request Items')</a></li>
                                    @endif
                                </ul>
                            </li>
                        @endguest
                    </ul>
                    <div class="nav-right d-flex ml-auto flex-wrap gap-2 gap-xxl-3">
                        <button class="nav-right__search-btn"><i class="fas fa-search"></i></button>
                        @guest
                            <a href="{{ route('user.login') }}"><i class="las la-sign-in-alt"></i> @lang('Login')</a>
                            @if (gs('registration'))
                                <a href="{{ route('user.register') }}"><i class="las la-user-plus"></i>
                                    @lang('Registration')</a>
                            @endif
                        @else
                            <div class="dropdown">
                                <button class="" data-bs-toggle="dropdown" data-display="static" type="button"
                                    aria-haspopup="true" aria-expanded="false">
                                    <i class="las la-user-plus"></i> {{ __(auth()->user()->username ?? 'Dashboard') }}
                                </button>
                                <div class="dropdown-menu dropdown-menu--sm box--shadow1 dropdown-menu-right border-0 p-0">
                                    <a class="dropdown-menu__item d-flex align-items-center px-3 py-2"
                                        href="{{ route('user.profile.setting') }}">
                                        <i class="dropdown-menu__icon las la-user-circle"></i>
                                        <span class="dropdown-menu__caption">@lang('Profile Setting')</span>
                                    </a>
                                    <a class="dropdown-menu__item d-flex align-items-center px-3 py-2"
                                        href="{{ route('ticket.index') }}">
                                        <i class="dropdown-menu__icon las la-list"></i>
                                        <span class="dropdown-menu__caption">@lang('My Support Ticket')</span>
                                    </a>
                                    <a class="dropdown-menu__item d-flex align-items-center px-3 py-2"
                                        href="{{ route('ticket.open') }}">
                                        <i class="dropdown-menu__icon las la-ticket-alt"></i>
                                        <span class="dropdown-menu__caption">@lang('Create Support Ticket')</span>
                                    </a>

                                    <a class="dropdown-menu__item d-flex align-items-center px-3 py-2"
                                        href="{{ route('user.change.password') }}">
                                        <i class="dropdown-menu__icon las la-key"></i>
                                        <span class="dropdown-menu__caption">@lang('Change Password')</span>
                                    </a>

                                    <a class="dropdown-menu__item d-flex align-items-center px-3 py-2"
                                        href="{{ route('user.logout') }}">
                                        <i class="dropdown-menu__icon las la-sign-out-alt"></i>
                                        <span class="dropdown-menu__caption">@lang('Logout')</span>
                                    </a>
                                </div>
                            </div>
                            @endif
                            @if (gs('multi_language'))
                                @php
                                    $languages = App\Models\Language::all();
                                    $language = $languages->where('code', '!=', session('lang'));
                                    $activeLanguage = $languages->where('code', session('lang'))->first();
                                @endphp
                                @if (!blank($language))
                                    <div class="language dropdown">
                                        <button class="language-wrapper" data-bs-toggle="dropdown" aria-expanded="false">
                                            <span class="language-content">
                                                <span class="language_flag">
                                                    <img src="{{ getImage(getFilePath('language') . '/' . @$activeLanguage->image, getFileSize('language')) }}"
                                                        alt="flag">
                                                </span>
                                                <span class="language_text_select">{{ __(@$activeLanguage->name) }}</span>
                                            </span>
                                            <span class="collapse-icon"><i class="las la-angle-down"></i></span>
                                        </button>
                                        <div class="dropdown-menu langList_dropdow py-2" style="">
                                            <ul class="langList">
                                                @foreach ($language as $item)
                                                    <li class="language-list langSel" data-lang_code="{{ $item->code }}">
                                                        <div class="language_flag">
                                                            <img src="{{ getImage(getFilePath('language') . '/' . @$item->image, getFileSize('language')) }}"
                                                                alt="flag">
                                                        </div>
                                                        <p class="language_text">{{ __(@$item->name) }}</p>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                @endif
                            @endif

                            <a class="btn btn--base btn--sm" href="{{ route('subscription') }}">
                                <span class="icon"><i class="fa-solid fa-crown"></i></span>
                                @lang('Subscribe')
                            </a>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
    </header>
    <div class="header-search-area">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <form class="header-search-form" action="{{ route('search') }}">
                        <input name="search" type="text" placeholder="@lang('Search here')....">
                        <button type="submit"><i class="fas fa-search"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
