@php
    $contactContent = getContent('contact.content', true);
    $socialIcons = getContent('social_icon.element', orderById: true);
@endphp
@extends('Template::layouts.frontend')
@section('content')
    <section class="my-80">
        <div class="container">
            <div class="row justify-content-between gy-4">
                <div class="col-lg-8">
                    <div class="card custom--card">
                        <div class="card-body">
                            <div class="mb-4">
                                <h2>{{ __($contactContent?->data_values?->heading ?? '') }}</h2>
                                <p class="fs-16">
                                    {{ __($contactContent?->data_values?->subheading ?? '') }}
                                </p>
                            </div>
                            <form class="verify-gcaptcha contact-form" method="post">
                                @csrf
                                <div class="form-group">
                                    <label class="form-label">@lang('Name')</label>
                                    <input class="form-control form--control" name="name" type="text"
                                        value="{{ old('name', $user?->fullname) }}"
                                        @if ($user && $user->profile_complete) readonly @endif required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">@lang('Email')</label>
                                    <input class="form-control form--control" name="email" type="email"
                                        value="{{ old('email', $user?->email) }}"
                                        @if ($user) readonly @endif required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">@lang('Subject')</label>
                                    <input class="form-control form--control" name="subject" type="text"
                                        value="{{ old('subject') }}" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">@lang('Message')</label>
                                    <textarea class="form-control form--control" name="message" wrap="off" required>{{ old('message') }}</textarea>
                                </div>
                                <x-captcha />
                                <div class="form-group">
                                    <button class="btn btn--base w-100" type="submit">@lang('Submit')</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="border-bottom pb-2 mb-3 pb-md-4 mb-md-4">
                        <h5 class="mb-2 fs-18">@lang('Address'):</h5>
                        <p class="fs-14">{{ __($contactContent?->data_values?->address_title ?? '') }}</p>
                        <p class="fs-14">
                            {{ __($contactContent?->data_values?->address ?? '') }}
                        </p>
                    </div>

                    <div class="border-bottom pb-2 mb-3 pb-md-4 mb-md-4 pt-2">
                        <h5 class="mb-2 fs-18">@lang('Phone'):</h5>
                        <p class="fs-14">
                            @php
                                $phone = $contactContent?->data_values?->mobile ?? '';
                            @endphp
                            @if ($phone)
                                <a href="tel:{{ $phone }}">{{ $phone }}</a>
                            @endif
                        </p>

                        <h5 class="mb-2 fs-18">@lang('Email'):</h5>
                        <p class="fs-14">
                            @php
                                $email = $contactContent?->data_values?->email ?? '';
                            @endphp
                            @if ($email)
                                <a href="mailto:{{ $email }}">{{ $email }}</a>
                            @endif
                        </p>
                    </div>
                    <div class="pt-2" bis_skin_checked="1">
                        <h5 class="mb-3 fs-18">@lang('Follow Us')</h5>
                        <ul class="social-list justify-content-start">
                            @foreach ($socialIcons as $item)
                                <li>
                                    <a class="social-list__link" href="{{ @$item->data_values->url }}" target="_blank">
                                        @php echo @$item->data_values->social_icon @endphp
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
