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
                                    <th>@lang('S.N')</th>
                                    <th>@lang('Name')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($teams as $team)
                                    <tr>
                                        <td>{{ $teams->firstItem() + $loop->index }}</td>
                                        <td>{{ __($team->name) }}</td>
                                        <td>
                                            @php
                                                echo $team->statusBadge;
                                            @endphp
                                        </td>
                                        <td>
                                            <div class="button--group">
                                                <button class="btn btn--sm btn-outline--primary editBtn" data-team="{{ $team }}"><i class="la la-pencil"></i>@lang('Edit')</button>
                                                @if ($team->status == Status::ENABLE)
                                                    <button class="btn btn--sm btn-outline--danger confirmationBtn" data-question="@lang('Are you sure to disable this team?')" data-action="{{ route('admin.team.status', $team->id) }}"><i class="la la-eye-slash"></i>@lang('Disable')</button>
                                                @else
                                                    <button class="btn btn--sm btn-outline--success confirmationBtn" data-question="@lang('Are you sure to enable this team?')" data-action="{{ route('admin.team.status', $team->id) }}"><i class="la la-eye"></i>@lang('Enable')</button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if ($teams->hasPages())
                    <div class="card-footer py-4">
                        {{ paginateLinks($teams) }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="modal fade" id="teamModal" role="dialog" tabindex="-1">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="las la-times"></i>
                    </button>
                </div>
                <form method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label>@lang('Name')</label>
                            <input class="form-control" name="name" type="text" required>
                        </div>
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
    <button class="btn btn-outline--primary addBtn"><i class="las la-plus"></i>@lang('Add New')</button>
@endpush

@push('script')
    <script>
        (function($) {
            "use strict"
            var modal = $('#teamModal');
            $('.addBtn').on('click', function() {
                modal.find('.modal-title').text(`@lang('Add Team')`);
                modal.find('form').attr('action', `{{ route('admin.team.store') }}`);
                modal.modal('show');
            })
            $('.editBtn').on('click', function() {
                var team = $(this).data('team');
                modal.find('.modal-title').text(`@lang('Update Team')`);
                modal.find('input[name=name]').val(team.name);
                modal.find('form').attr('action', `{{ route('admin.team.store', '') }}/${team.id}`);
                modal.modal('show');
            });
            modal.on('hidden.bs.modal', function() {
                modal.find('form')[0].reset();
            });
        })(jQuery);
    </script>
@endpush
