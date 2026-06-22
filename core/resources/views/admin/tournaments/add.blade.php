@extends('admin.layouts.app')
@section('panel')
    <div class="row mb-none-30">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.tournament.store', @$tournament->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-xxl-4 col-lg-6">
                                <div class="form-group">
                                    <label>@lang('Image')</label>
                                    <x-image-uploader image="{{ @$tournament->image }}" class="w-100" type="tournament" :required=false />
                                </div>
                            </div>
                            <div class="col-xxl-8 col-lg-6">
                                <div class="form-group ">
                                    <label>@lang('Name')</label>
                                    <input class="form-control" type="text" name="name" value="{{ old('name', @$tournament->name) }}" required>
                                </div>
                                <div class="form-group ">
                                    <label>@lang('Short Name')</label>
                                    <input class="form-control" type="text" name="short_name" value="{{ old('short_name', @$tournament->short_name) }}" required>
                                </div>
                                <div class="form-group ">
                                    <label>@lang('Season')</label>
                                    <input class="form-control" type="text" name="season" value="{{ old('season', @$tournament->season) }}" required>
                                </div>
                                <div class="form-group ">
                                    <label>@lang('Description')</label>
                                    <textarea name="description" class="form-control" rows="5" required>{{ old('description', @$tournament->description) }}</textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>@lang('Price')</label>
                                            <div class="input-group">
                                                <input type="number" step="any" name="price" class="form-control" value="{{ old('price', getAmount(@$tournament->price)) }}" required>
                                                <span class="input-group-text">{{ gs('cur_text') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>@lang('Version')</label>
                                            <input name="version" data-width="100%" data-size="large" data-onstyle="-success" data-offstyle="-danger" data-bs-toggle="toggle" data-height="50" data-on="@lang('Paid')" data-off="@lang('Free')" type="checkbox" @checked(@$tournament->version)>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn--primary h-45 w-100">@lang('Submit')</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.tournament.index') }}" />
@endpush
