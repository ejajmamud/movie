@extends('admin.layouts.app')
@section('panel')
    <div class="row mb-none-30">
        <div class="col-lg-12 col-md-12 mb-30">
            <div class="card">
                <div class="card-body">
                    <div class="documentation">
                        <div id="installation2">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="documentation-body__top">
                                        <h2 class="documentation-body__top-title">@lang('WebSocket Application Setup for Live Chatting')</h2>
                                        <p class="documentation-body__top-desc">
                                            @lang('Please follow these documentation to setup step your WebSocket application for live chatting.')
                                        </p>
                                    </div>
                                    <div class="changelog-container">
                                        <div class="changelog-left">
                                            <div class="changelog-list-style">
                                                <div class="changelog-circle"></div>
                                            </div>
                                            <div class="changelog-border">
                                                <div class="changelog-border-circle"></div>
                                            </div>
                                        </div>
                                        <div class="changelog-right">
                                            <div class="changelog-right-content">
                                                <ul class="documentation-body__list">
                                                    <li class="documentation-body__list-item">
                                                        <strong>@lang('Step 1') : </strong>@lang('Go to your panel and search for')<strong> "@lang('Setup Node.js App')" </strong>. @lang('Click to open it.')
                                                    </li>
                                                    <li class="documentation-body__list-item">
                                                        <strong class="mb-3">@lang('Image'):</strong> <br />
                                                        <img class="mt-3"
                                                             src="{{ asset('assets/admin/images/socket/panel.png') }} "
                                                             alt="" />
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="changelog-container">
                                        <div class="changelog-left">
                                            <div class="changelog-list-style">
                                                <div class="changelog-circle"></div>
                                            </div>
                                            <div class="changelog-border">
                                                <div class="changelog-border-circle"></div>
                                            </div>
                                        </div>
                                        <div class="changelog-right">
                                            <div class="changelog-right-content">
                                                <ul class="documentation-body__list">
                                                    <li class="documentation-body__list-item">
                                                        <strong> @lang('Step 2') : </strong> @lang('Click on') <strong>“@lang('Create Application')”</strong> @lang('to go to the create page.')

                                                    </li>
                                                    <li class="documentation-body__list-item">
                                                        <strong>Image:</strong> <br />
                                                        <img class="mt-3"
                                                             src="{{ asset('assets/admin/images/socket/app_list.png') }}"
                                                             alt="" />
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="changelog-container">
                                        <div class="changelog-left">
                                            <div class="changelog-list-style">
                                                <div class="changelog-circle"></div>
                                            </div>
                                            <div class="changelog-border">
                                                <div class="changelog-border-circle"></div>
                                            </div>
                                        </div>
                                        <div class="changelog-right">
                                            <div class="changelog-right-content">
                                                <ul class="documentation-body__list">
                                                    <li class="documentation-body__list-item">
                                                        <strong> @lang('Step 3') : </strong> @lang('Now configure your web application page')
                                                        <strong>“@lang('Create Application')”</strong>. </strong>
                                                        <ul class="text-list">
                                                            <li>@lang('Select your desired Node.js version (recommended).')</li>
                                                            <li>@lang('Set') <strong>@lang('Application Mode')</strong> @lang('to')
                                                                <code>@lang('production')</code>.
                                                            </li>
                                                            <li>@lang('Give an') <strong>@lang('Application Root')</strong> @lang('name as you like')
                                                                (@lang('e.g.'), <code>@lang('playlab_socket')</code>).</li>
                                                            <li>@lang('Set') <strong>@lang('Application URL')</strong> @lang('same as Application Root.')</li>
                                                            <li>@lang('Enter Application Startup File Name as')
                                                                <code>@lang('server.js')</code> (@lang('this is inside of root file, e.g.') <code>@lang('websocket.zip')</code>).
                                                            </li>
                                                            <li><i class="las la-mouse"></i> @lang('Click') <strong>@lang('CREATE')</strong>.
                                                            </li>
                                                        </ul>
                                                    </li>
                                                    <li class="documentation-body__list-item">
                                                        <strong>@lang('Image'):</strong> <br />
                                                        <img class="mt-3" src="{{ asset('assets/admin/images/socket/create.png') }}" alt="" />
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="changelog-container">
                                        <div class="changelog-left">
                                            <div class="changelog-list-style">
                                                <div class="changelog-circle"></div>
                                            </div>
                                            <div class="changelog-border">
                                                <div class="changelog-border-circle"></div>
                                            </div>
                                        </div>
                                        <div class="changelog-right">
                                            <div class="changelog-right-content">
                                                <ul class="documentation-body__list">
                                                    <li class="documentation-body__list-item">
                                                        <strong> @lang('Step 4') : </strong> @lang('Already Created a folder in root : “playlab_socket” check it out your panel root.')
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="changelog-container">
                                        <div class="changelog-left">
                                            <div class="changelog-list-style">
                                                <div class="changelog-circle"></div>
                                            </div>
                                            <div class="changelog-border">
                                                <div class="changelog-border-circle"></div>
                                            </div>
                                        </div>
                                        <div class="changelog-right">
                                            <div class="changelog-right-content">
                                                <ul class="documentation-body__list">
                                                    <li class="documentation-body__list-item">
                                                        <strong> @lang('Step 5') : @lang('Upload Websocket Files, ')</strong> @lang('On your local PC, locate') <code>@lang('websocket.zip')</code> @lang('Files > websocket > websocket.zip')
                                                        <ul class="text-list">
                                                            <li>@lang('Upload') <code>@lang('websocket.zip')</code> @lang('into the')
                                                                <code>@lang('playlab_socket')</code> @lang('folder.')
                                                            </li>
                                                            <li>@lang('Delete the existing') <code>@lang('server.js')</code></li>
                                                            <li>@lang('Extract the uploaded') <code>@lang('websocket.zip')</code> @lang('file.')</li>
                                                        </ul>
                                                    </li>
                                                    <li class="documentation-body__list-item">
                                                        <strong>@lang('Image 1'):</strong> <br />
                                                        <img class="mt-3"
                                                             src="{{ asset('assets/admin/images/socket/pc_locate.png') }}"
                                                             alt="" />
                                                    </li>
                                                    <li class="documentation-body__list-item">
                                                        <strong>@lang('Image 2'):</strong> <br />
                                                        <img class="mt-3"
                                                             src="{{ asset('assets/admin/images/socket/root_file.png') }}"
                                                             alt="" />
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="changelog-container">
                                        <div class="changelog-left">
                                            <div class="changelog-list-style">
                                                <div class="changelog-circle"></div>
                                            </div>
                                            <div class="changelog-border">
                                                <div class="changelog-border-circle"></div>
                                            </div>
                                        </div>
                                        <div class="changelog-right">
                                            <div class="changelog-right-content">
                                                <ul class="documentation-body__list">
                                                    <li class="documentation-body__list-item">
                                                        <strong> @lang('Step 6') : @lang('Install Dependencies') </strong>
                                                        <ul class="text-list">
                                                            <li>@lang('After extraction, hard refresh the') <code>"@lang('CREATE APPLICATION')"</code> <strong>@lang('page')</strong> @lang('Where you was created. And refresh until you see the')
                                                                <strong>“@lang('Run NPM Install')”</strong> @lang('button.')
                                                            </li>
                                                            <li><i class="las la-mouse"></i> <strong>@lang('Click') “@lang('Run NPM Install')”</strong> @lang('to install dependencies inside') <code>@lang('playlab_socket')</code>.</li>
                                                            <li>@lang('Install dependencies') “@lang('node modules')” @lang('inside') <code> @lang('e.g. playlab_socket')</code> @lang('folder.')</li>
                                                        </ul>
                                                    </li>

                                                    <li class="documentation-body__list-item">
                                                        <strong>@lang('Image 1'):</strong> <br />
                                                        <img class="mt-3"
                                                             src="{{ asset('assets/admin/images/socket/create_before.png') }}"
                                                             alt="" />
                                                    </li>
                                                    <li class="documentation-body__list-item">
                                                        <strong>@lang('Image 2'):</strong> <br />
                                                        <img class="mt-3"
                                                             src="{{ asset('assets/admin/images/socket/create_after.png') }}"
                                                             alt="" />
                                                    </li>
                                                    <li class="documentation-body__list-item">
                                                        <strong>@lang('Image 3'):</strong> <br />
                                                        <img class="mt-3"
                                                             src="{{ asset('assets/admin/images/socket/root_folder.png') }}"
                                                             alt="" />
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="changelog-container">
                                        <div class="changelog-left">
                                            <div class="changelog-list-style">
                                                <div class="changelog-circle"></div>
                                            </div>
                                            <div class="changelog-border">
                                                <div class="changelog-border-circle"></div>
                                            </div>
                                        </div>
                                        <div class="changelog-right">
                                            <div class="changelog-right-content">
                                                <ul class="documentation-body__list">
                                                    <li class="documentation-body__list-item">
                                                        <strong> @lang('Step 7') : @lang('Configure WebSocket in Admin Panel')</strong>
                                                        <ul class="text-list">
                                                            <li>@lang('Go to “Web Applications” and copy your created application URI (App URI).')</li>
                                                            <li>@lang('Open your script’s admin panel, go to') <strong>@lang('General Settings')</strong> > <strong>@lang('Socket Configuration')</strong>.</li>
                                                            <li>@lang('Replace the URI with your App URI:')
                                                                <br />
                                                                <code>@lang('wss://example.com/playlab_socket')</code>
                                                                <br />
                                                                @lang('or')
                                                                <br />
                                                                <code>@lang('ws://example.com/playlab_socket')</code>
                                                            </li>
                                                            <li> <i class="las la-mouse"></i> @lang('Click') <strong>@lang('Submit')</strong>.</li>
                                                        </ul>
                                                    </li>
                                                    <li class="documentation-body__list-item">
                                                        <strong>@lang('Image 1'):</strong> <br />
                                                        <img class="mt-3"
                                                             src="{{ asset('assets/admin/images/socket/app_list_uri.png') }}"
                                                             alt="" />
                                                    </li>
                                                    <li class="documentation-body__list-item">
                                                        <strong>@lang('Image 2'):</strong> <br />
                                                        <img class="mt-3"
                                                             src="{{ asset('assets/admin/images/socket/update_app_uri.png') }}"
                                                             alt="" />
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.setting.general') }}" />
@endpush


@push('style')
    <style>
        code {
            font-size: 16px !important;
            font-weight: 600 !important;
        }

        .changelog-container {
            align-content: flex-start;
            align-items: flex-start;
            display: flex;
            flex: none;
            flex-direction: row;
            flex-wrap: nowrap;
            gap: 24px;
            height: min-content;
            justify-content: flex-start;
            padding: 0;
            position: relative;
            width: 100%;
        }

        .changelog-left {
            align-content: flex-start;
            align-items: flex-start;
            align-self: stretch;
            display: flex;
            flex: none;
            flex-direction: column;
            flex-wrap: nowrap;
            gap: 0px;
            height: auto;
            justify-content: flex-start;
            overflow: hidden;
            padding: 0;
            position: relative;
            width: min-content;
            min-height: auto;
        }

        .changelog-list-style {
            align-content: center;
            align-items: center;
            display: flex;
            flex: none;
            flex-direction: row;
            flex-wrap: nowrap;
            gap: 0px;
            height: min-content;
            justify-content: center;
            overflow: hidden;
            padding: 9px;
            position: relative;
            width: min-content;
        }

        .changelog-border {
            align-content: center;
            align-items: center;
            align-self: stretch;
            display: flex;
            flex: 1 0 0px;
            flex-direction: row;
            flex-wrap: nowrap;
            gap: 10px;
            height: 1px;
            justify-content: center;
            overflow: hidden;
            padding: 0;
            position: relative;
            width: auto;
        }

        .changelog-border-circle {
            background: linear-gradient(180deg, rgba(29, 30, 32, 0.1) 0%, rgba(29, 30, 32, 0.24) 46.53%, rgba(29, 30, 32, 0.1) 100%);
            border-radius: 1px;
            flex: none;
            height: 100%;
            overflow: hidden;
            position: relative;
            width: 1px;
            will-change: transform;
        }

        .changelog-circle {
            aspect-ratio: 1 / 1;
            background-color: #5d50e6;
            border-radius: 30px;
            box-shadow: 0 0 0 3px #4634ff3b;
            flex: none;
            height: 7px;
            overflow: hidden;
            position: relative;
            width: 7px;
            will-change: transform;
        }

        .documentation .documentation-body__top {
            padding-bottom: 50px;
        }

        @media screen and (max-width: 1199px) {
            .documentation .documentation-body__top {
                padding-bottom: 25px;
                margin-bottom: 25px;
            }
        }

        @media screen and (max-width: 767px) {
            .documentation .documentation-body__top {
                padding-bottom: 20px;
                margin-bottom: 20px;
            }
        }

        .documentation .documentation-body__top-title {
            margin-bottom: 10px;
            font-size: 2rem;
            font-weight: 500;
        }

        @media screen and (max-width: 1199px) {
            .documentation .documentation-body__top-title {
                font-size: 2.5rem;
            }
        }

        @media screen and (max-width: 991px) {
            .documentation .documentation-body__top-title {
                font-size: 2.251rem;
            }
        }


        @media screen and (max-width: 767px) {
            .documentation .documentation-body__top-title {
                margin-bottom: 8px;
                font-size: 1.5rem;
            }
        }

        .documentation .documentation-body__top-desc {
            font-size: 1.125rem;
            line-height: 1.5;
            /* color: hsl(var(--body-color)); */
        }

        @media screen and (max-width: 1199px) {
            .documentation .documentation-body__top-desc {
                font-size: 1.0625rem;
            }
        }

        @media screen and (max-width: 991px) {
            .documentation .documentation-body__top-desc {
                font-size: 1rem;
            }
        }

        @media screen and (max-width: 767px) {
            .documentation .documentation-body__top-desc {
                font-size: 0.9375rem;
            }
        }

        .documentation .documentation-body__content-item {
            margin-bottom: 45px;
        }

        .documentation .documentation-body__content-title {
            margin-bottom: 10px;
        }

        .documentation .documentation-body__content-desc {
            margin-bottom: 20px;
        }

        @media screen and (max-width: 1199px) {
            .documentation .documentation-body__content-desc {
                margin-bottom: 15px;
            }
        }

        @media screen and (max-width: 767px) {
            .documentation .documentation-body__content-desc {
                margin-bottom: 12px;
            }
        }

        @media screen and (max-width: 575px) {
            .documentation .documentation-body__content-desc {
                margin-bottom: 10px;
            }
        }


        .documentation-body__inner {
            margin: 0 auto;
        }


        .documentation .documentation-body__content-desc .link {
            font-weight: 600;
        }

        .documentation .documentation-body__content-desc .highlight {
            color: hsl(var(--heading-color));
            font-weight: 600;
        }

        .documentation .documentation-body__content code:not(.language-php) {
            font-size: 0.8125rem;
            font-weight: 600;
            color: hsl(var(--heading-color));
            background: #f8fafc;
            border: 1px solid #4634ff;
            border-radius: 4px;
            padding: 2px 4px;
        }

        .documentation .documentation-body__list {
            margin-bottom: 15px;
        }

        .documentation .documentation-body__list-item {
            position: relative;
            font-size: 1rem;
            padding-left: 25px;
            margin-bottom: 16px;
            list-style: none !important;
        }

        @media screen and (max-width: 1199px) {
            .documentation .documentation-body__list-item {
                font-size: 0.9375rem;
            }
        }

        @media screen and (max-width: 767px) {
            .documentation .documentation-body__list-item {
                font-size: 0.875rem;
                margin-bottom: 10px;
            }
        }

        .documentation .documentation-body__list-item:last-child {
            margin-bottom: 0;
        }

        .documentation .documentation-body__list-item::before {
            position: absolute;
            content: "\f138";
            font-family: "Line Awesome Free";
            font-weight: 900;
            border-radius: 50%;
            left: -1px;
            top: 0px;
        }

        .documentation .documentation-body__list-item .highlight {
            color: hsl(var(--heading-color));
            font-weight: 600;
        }

        .documentation .documentation-body__quote {
            gap: 8px;
            position: relative;
            border-left: 4px solid #4634ff;
            padding: 8px 0 8px 15px;
        }

        .documentation .documentation-body__screenshot {
            border-radius: 8px;
            overflow: hidden;
        }

        .text-list li {
            margin-bottom: 2px;
            padding-left: 12px;
            position: relative;
        }

        .text-list li::before {
            position: absolute;
            top: 8px;
            content: '';
            left: 0;
            width: 5px;
            height: 5px;
            background: #5b6e88;
            border-radius: 50%;
        }

        .text-list {
            margin-top: 10px;
        }

        .documentation-body__list-item img {
            background: #fff;
            padding: 20px;
            box-shadow: rgba(0, 0, 0, 0.1) 0px 1px 3px 0px, rgba(0, 0, 0, 0.06) 0px 1px 2px 0px;
            border-radius: 8px;
        }

        @media screen and (max-width: 575px) {
            .documentation-body__list-item img {
                padding: 10px;
            }

            .changelog-container {
                gap: 12px;
            }
        }

        @media screen and (max-width: 424px) {
            .documentation-body__list-item img {
                padding: 5px;
            }

            .changelog-container {
                gap: 10px;
            }

            .changelog-list-style {
                padding: 0;
                margin-top: 5px;
            }

            .documentation .documentation-body__list-item {
                padding-left: 18px;
            }
        }
    </style>
@endpush
