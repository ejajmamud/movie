@extends('Template::layouts.frontend')
@section('content')
    <section class="movie-section my-80">
        <div class="container">
            <div class="row justify-content-center mb-30-none">
                <div class="col-md-9">
                    @php echo $cookie->data_values->description @endphp
                </div>

            </div>
        </div>
    </section>
@endsection
