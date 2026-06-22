<ul class="nav nav-tabs mb-4 topTap breadcrumb-nav" role="tablist">
    <button class="breadcrumb-nav-close"><i class="las la-times"></i></button>
    <li class="nav-item {{ menuActive(['admin.advertise.index']) }}" role="presentation">
        <a href="{{ route('admin.advertise.index') }}" class="nav-link text-dark" type="button">
            <i class="las la-photo-video"></i> @lang('Advertisements')
        </a>
    </li>
    <li class="nav-item {{ menuActive(['admin.video.advertise.index', 'admin.video.advertise.form', 'admin.video.advertise.edit']) }}" role="presentation">
        <a href="{{ route('admin.video.advertise.index') }}" class="nav-link text-dark" type="button">
            <i class="las la-video"></i> @lang('Video Advertisements')
        </a>
    </li>
</ul>
