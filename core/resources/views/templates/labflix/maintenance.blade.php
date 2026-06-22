<!doctype html>
<html lang="en" itemscope itemtype="http://schema.org/WebPage">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ gs()->siteName(__($pageTitle)) }}</title>
    <link type="image/png" href="{{ siteFavicon() }}" rel="icon" sizes="16x16">
    <link href="{{ asset('assets/global/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/global/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset(activeTemplate(true) . 'css/main.css') }}" rel="stylesheet">
    <style>
        body {
            min-height: calc(100vh + 0px) !important;
        }

        .maintenance-page {
            display: grid;
            place-content: center;
            width: 100%;
            height: 100vh;
        }
    </style>
</head>

<body @stack('context')>
    <div class="maintenance-page">
        <div class="container">
            <div class="row d-flex justify-content-center">
                <div class="col-sm-6 col-8 col-lg-12">
                    <div class="mx-auto">
                        <img class="img-fluid mx-auto mb-5"
                            src="{{ getImage(getFilePath('maintenance') . '/' . $maintenance?->data_values?->image, getFileSize('maintenance')) }}"
                            alt="image">
                    </div>
                    <div class="text-center">
                        @php echo $maintenance->data_values->description @endphp
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
