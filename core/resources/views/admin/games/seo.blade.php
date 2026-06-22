@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form class="disableSubmission" method="POST" action="{{ route('admin.game.seo.store', $data->id) }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-xl-4">
                                <div class="form-group">
                                    <label>@lang('SEO Image')</label>
                                    <x-image-uploader class="w-100" :imagePath="getImage(getFilePath('gameSeo').'/'.@$data->seo_content->image, getFileSize('seo'))" :size="getFileSize('seo')" :required="false" />
                                </div>
                            </div>
                            <div class="col-xl-8 mt-xl-0 mt-4">
                                <div class="form-group select2-parent position-relative">
                                    <label>@lang('Meta Keywords')</label>
                                    <small class="ms-2 mt-2  ">@lang('Separate multiple keywords by') <code>,</code>(@lang('comma')) @lang('or') <code>@lang('enter')</code> @lang('key')</small>
                                    <select class="form-control select2-auto-tokenize" name="keywords[]" multiple="multiple">
                                        @if (@$data->seo_content->keywords)
                                            @foreach (@$data->seo_content->keywords as $option)
                                                <option value="{{ $option }}" selected>{{ __($option) }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>@lang('Meta Description')</label>
                                    <textarea class="form-control" name="description" rows="3">{{ @$data->seo_content->description }}</textarea>
                                </div>
                                <div class="form-group">
                                    <label>@lang('Social Title')</label>
                                    <input class="form-control" name="social_title" type="text" value="{{ @$data->seo_content->social_title }}" />
                                </div>
                                <div class="form-group">
                                    <label>@lang('Social Description')</label>
                                    <textarea class="form-control" name="social_description" rows="3">{{ @$data->seo_content->social_description }}</textarea>
                                </div>
                                <div class="form-group">
                                    <button class="btn cmn-btn w-100" type="submit">@lang('Submit')</button>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

@endsection
@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.game.index') }}" />
@endpush

@push('script')
    <script>
        (function($) {
            "use strict";

            function proPicURL(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        var preview = $(input).parents('.profile-thumb').find('.profilePicPreview');
                        $(preview).css('background-image', 'url(' + e.target.result + ')');
                        $(preview).addClass('has-image');
                        $(preview).hide();
                        $(preview).fadeIn(650);
                    }
                    reader.readAsDataURL(input.files[0]);
                }
            }
            $(".profilePicUpload").on('change', function() {
                proPicURL(this);
            });

            $(".remove-image").on('click', function() {
                $(".profilePicPreview").css('background-image', 'none');
                $(".profilePicPreview").removeClass('has-image');
            })
        })(jQuery);
    </script>
@endpush
