@props(['route' => '', 'height' => ''])

<a href="{{ $route }}"
    class="btn btn--sm btn-outline--dark @if($height) h-{{ $height }} @endif">
    <i class="la la-undo"></i> @lang('Back')
</a>
