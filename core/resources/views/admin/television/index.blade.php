@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card  ">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table--light style--two table">
                            <thead>
                                <tr>
                                    <th>@lang('Image')</th>
                                    <th>@lang('Category')</th>
                                    <th>@lang('Title')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($televisions as $television)
                                    <tr>
                                        <td>
                                            <div class="user">
                                                <div class="thumb"><img
                                                        src="{{ getImage(getFilePath('television') . '/' . $television->image, getFileSize('television')) }}"
                                                        alt="{{ __($television->name) }}" class="plugin_bg"></div>
                                            </div>
                                        </td>
                                        <td>{{ __(@$television->category->name) }}</td>
                                        <td>{{ __($television->title) }}</td>
                                        <td>
                                            @php
                                                echo $television->statusBadge;
                                            @endphp
                                        </td>
                                        <td>
                                            <div class="btn--group">
                                                <div class="d-flex justify-content-end flex-wrap gap-1">
                                                    <button class="btn btn-outline--info btn-sm dropdown-toggle"
                                                        data-bs-toggle="dropdown">
                                                        <i class="las la-ellipsis-v"></i> @lang('More')
                                                    </button>
                                                    <ul class="dropdown-menu px-2">
                                                        <li>
                                                            <button
                                                                class="dropdown-item cursor-pointer text--primary editBtn"
                                                                data-television="{{ $television }}"
                                                                data-image="{{ getImage(getFilePath('television') . '/' . $television->image, getFileSize('television')) }}"><i
                                                                    class="la la-pencil"></i> @lang('Edit')</button>
                                                        </li>
                                                        @if ($television->status == Status::ENABLE)
                                                            <li>
                                                                <button
                                                                    class="dropdown-item cursor-pointer text--warning confirmationBtn"
                                                                    data-question="@lang('Are you sure to disbale this television?')"
                                                                    data-action="{{ route('admin.television.channel.status', $television->id) }}"><i
                                                                        class="la la-eye-slash"></i>
                                                                    @lang('Disable')</button>
                                                            </li>
                                                        @else
                                                            <li>
                                                                <button
                                                                    class="dropdown-item cursor-pointer text--success confirmationBtn"
                                                                    data-question="@lang('Are you sure to enable this television?')"
                                                                    data-action="{{ route('admin.television.channel.status', $television->id) }}"><i
                                                                        class="la la-eye"></i> @lang('Enable')</button>
                                                            </li>
                                                        @endif
                                                        <li>
                                                            <button
                                                                class="dropdown-item cursor-pointer text--danger confirmationBtn"
                                                                data-question="@lang('Are you sure to delete this television?')"
                                                                data-action="{{ route('admin.television.channel.delete', $television->id) }}">
                                                                <i class="la la-trash"></i> @lang('Delete')</button>
                                                        </li>
                                                        <li>
                                                            <a href="{{ route('admin.television.channel.live.comment', $television->id) }}"
                                                                class="dropdown-item cursor-pointer text--success {{ $television->live_comments_count ? '' : 'disabled' }}">
                                                                <i class="lab la-rocketchat"></i> @lang('Live Comment')
                                                                <span
                                                                    class="live-comment-badge badge badge--success">{{ $television->live_comments_count }}</span>

                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="{{ route('admin.television.channel.seo', $television->id) }}"
                                                                class="dropdown-item cursor-pointer text--secondary"><i
                                                                    class="la la-cog"></i> @lang('Seo Setting')</a>
                                                        </li>
                                                    </ul>

                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if ($televisions->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($televisions) }}
                    </div>
                @endif
            </div><!-- card end -->
        </div>
    </div>

    <!-- Plan Modal -->
    <div class="modal fade" id="televisionModal" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        tabindex="-1">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>@lang('Thumbnail Image')</label>
                                    <x-image-uploader image="{{ getImage('/', getFileSize('television')) }}" class="w-100"
                                        type="ads" size="{{ getFileSize('television') }}" :required=false />
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>@lang('Categories')</label>
                                    <select name="channel_category_id" class="form-control select2">
                                        <option value="">@lang('Select One')</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ __($category->name) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>@lang('Title')</label>
                                    <input class="form-control" name="title" type="text" required>
                                </div>
                                <div class="form-group">
                                    <label>@lang('URL')</label>
                                    <input class="form-control" name="url" type="text" required>
                                </div>
                                <div class="form-group">
                                    <label>@lang('Description')</label>
                                    <textarea class="form-control" id="" name="description" rows="5" required></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn--primary w-100 h-45" type="submit">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <x-confirmation-modal />
@endsection

@push('breadcrumb-plugins')
    <x-search-form />
    <button class="btn btn--sm btn-outline--primary addBtn"><i class="la la-plus"></i>@lang('Add New')</button>
@endpush

@push('style')
    <style>
        .dropdown-item.text--success {
            position: relative;
            padding-right: 40px !important;
        }

        .dropdown-item .live-comment-badge {
            position: absolute;
            right: 10px;
            top: 0px;
            transform: translateY(-50%);
            font-size: 10px !important;
            font-weight: 400;
        }

        .dropdown-item.disabled .live-comment-badge {
            opacity: 0.6;
            border-color: #999;
            background: rgba(153, 153, 153, 0.1);
            color: #666;
        }

        .dropdown-item.active,
        .dropdown-item:active {
            background-color: unset !important;
        }
    </style>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict"
            var modal = $('#televisionModal');
            var defautlImage = `{{ getImage(getFilePath('television'), getFileSize('television')) }}`;

            $('.addBtn').on('click', function() {
                $('.modal-title').text(`@lang('Add New Television')`);
                modal.find('form').attr('action', `{{ route('admin.television.channel.store') }}`);
                modal.find('.image-upload-preview').attr('style', `background-image: url(${defautlImage})`);
                modal.find('select[name=channel_category_id]').val('').change();
                modal.find('.statusGroup').hide();
                modal.modal('show');
            });

            $('.editBtn').on('click', function() {
                var television = $(this).data('television');
                $('.modal-title').text(`@lang('Update Television')`);
                modal.find('input[name=title]').val(television.title);
                modal.find('input[name=url]').val(television.url);
                modal.find('[name=description]').val(television.description);
                modal.find('select[name=channel_category_id]').val(television.channel_category_id).change();
                modal.find('.image-upload-preview').attr('style',
                    `background-image: url(${$(this).data('image')})`);
                modal.find('form').attr('action',
                    `{{ route('admin.television.channel.store', '') }}/${television.id}`);
                modal.find('.statusGroup').show();
                modal.modal('show');
            });

            modal.on('hidden.bs.modal', function() {
                modal.find('form')[0].reset();
            });
        })(jQuery);
    </script>
@endpush
