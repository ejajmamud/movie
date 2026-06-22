@extends('admin.layouts.app')

@section('panel')
    <div class="row">
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="mb-4">
                        <div class="d-flex align-items-center mb-3">
                            <div>
                                <h4 class="mb-1">{{ $item->item }}</h4>
                                <p class="text-muted mb-1"><strong>@lang('Track ID'):</strong> {{ $item->track_id }}</p>
                                <p class="text-muted mb-0"><strong>@lang('Overview'):</strong> {{ $item->overview }}</p>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3">@lang('User Recommendations')</h5>
                    @if ($items->isNotEmpty())
                        <div class="table-responsive--sm table-responsive">
                            <table class="table--light style--two table">
                                <thead>
                                    <tr>
                                        <th>@lang('User')</th>
                                        <th>@lang('Email')</th>
                                        <th>@lang('Recommendation')</th>
                                        <th>@lang('Date')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($items as $userRequest)
                                        <tr>
                                            <td>
                                                <span class="fw-bold">{{ $userRequest->user->fullname }}</span>
                                                <br>
                                                <span class="small">
                                                    <a
                                                        href="{{ route('admin.users.detail', $userRequest->user->id) }}"><span>@</span>{{ $userRequest->user->username }}</a>
                                                </span>
                                            </td>
                                            <td>{{ $userRequest->user->email ?? 'N/A' }}</td>
                                            <td>{{ $userRequest->recommend }}</td>
                                            <td>{{ showDateTime($userRequest->created_at, 'd M Y, h:i A') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">@lang('No recommendations found for this item.')</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('breadcrumb-plugins')
    <x-back route="{{ route('admin.request.item.index') }}" />
@endpush
