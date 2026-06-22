@extends('Template::layouts.master')

@section('content')
    <div class="card-area my-80">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-10">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>@lang('Subject')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Priority')</th>
                                <th>@lang('Last Reply')</th>
                                <th>@lang('Action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($supports as $key => $support)
                                <tr>
                                    <td> <a class="font-weight-bold" href="{{ route('ticket.view', $support->ticket) }}">
                                            [@lang('Ticket')#{{ $support->ticket }}] {{ __($support->subject) }} </a></td>
                                    <td>
                                        @php echo $support->statusBadge; @endphp
                                    </td>
                                    <td>
                                        @php echo $item->priorityBadge; @endphp
                                    </td>
                                    <td>{{ diffForHumans($support->last_reply) }} </td>

                                    <td>
                                        <a class="btn btn--base btn--sm"
                                            href="{{ route('ticket.view', $support->ticket) }}">
                                            <i class="las la-desktop"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="data-not-found" colspan="100">
                                        <div class="data-not-found__text text-center">
                                            <h6 class="empty-table__text mt-1">{{ __($emptyMessage) }} </h6>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{ paginateLinks($supports) }}
                </div>
            </div>
        </div>
    </div>
@endsection
