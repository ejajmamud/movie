@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive--sm table-responsive">
                        <table class="table table--light style--two custom-data-table">
                            <thead>
                                <tr>
                                    <th>@lang('Label')</th>
                                    <th>@lang('Width')</th>
                                    <th>@lang('Height')</th>
                                    <th>@lang('Actions')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($resolutions as $resolution)
                                    <tr>
                                        <td>{{ __($resolution->resolution_label) }}</td>
                                        <td>{{ $resolution->width }}</td>
                                        <td>{{ $resolution->height }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline--primary editBtn" data-action="{{ route('admin.resolutions.save', $resolution->id) }}" data-resolution="{{ $resolution }}"><i class="las la-pencil-alt"></i>@lang('Edit')</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table><!-- table end -->
                    </div>
                </div>
            </div><!-- card end -->
        </div>
    </div>


    {{-- NEW MODAL --}}
    <div class="modal fade" id="createModal" role="dialog" aria-labelledby="createModalLabel" aria-hidden="true" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="createModalLabel"> @lang('Add New')</h4>
                    <button class="close" data-bs-dismiss="modal" type="button" aria-label="Close"><i class="las la-times"></i></button>
                </div>
                <form class="form-horizontal" method="post" action="">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">

                            <label class="form-label">@lang('Resolution')</label>

                            <input class="form-control" name="resolution_label" type="text" readonly>
                        </div>

                        <div class="row justify-content-center">
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="form-label">@lang('Width')</label>
                                    <input class="form-control" name="width" type="number" value="{{ old('width') }}" required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="form-label">@lang('Height')</label>
                                    <input class="form-control" name="height" type="number" value="{{ old('height') }}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn--primary w-100 h-45" id="btn-save" type="submit" value="add">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <x-confirmation-modal />
@endsection

@push('style')
    <style>
        .key-added {
            pointer-events: unset !important;
        }
    </style>
@endpush



@push('script')
    <script>
        (function($) {
            "use strict";
            $('.editBtn').on('click', function() {
                var modal = $('#createModal');
                var resolution = $(this).data('resolution');
                var url = $(this).data('action');
                modal.find('form').attr('action', url);
                modal.find('.modal-title').text("@lang('Edit Resulotions')");
                modal.find('[name="resolution_label"]').val(resolution.resolution_label);
                modal.find('[name="width"]').val(resolution.width);
                modal.find('[name="height"]').val(resolution.height);
                modal.modal('show');
            });
        })(jQuery);
    </script>
@endpush
