@extends('front.layouts.app')
@section('content')
    @if (Session::has('Success'))
        <script>
            swal("Message", "{{ Session::get('Success') }}", 'success', {
                button: true,
                button: "Ok",
                timer: 3500
            });
        </script>
    @endif
    @if (Session::has('Fail'))
        <div class = "col-md-12">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {!! Session::get('Fail') !!}
                <button type="button" class="btn-close" data-bs-dismiss="alert" area-label = "Close"></button>
            </div>
        </div>
    @endif
    <section class="section-5 pt-3 pb-3 mb-3 bg-white">
        <div class="container">
            <div class="light-font">
                <ol class="breadcrumb primary-color mb-0">
                    <li class="breadcrumb-item"><a class="white-text" href="#">Home</a></li>
                    <li class="breadcrumb-item">Login</li>
                </ol>
            </div>
        </div>
    </section>

    <section class=" section-10">
        <div class="container">
            <div class="login-form">
                <form action="{{ route('front.forgotPassword') }}" method="post">
                    @csrf
                    <h4 class="modal-title">Forgot Password</h4>
                    <div class="form-group">
                        <input type="text" class="form-control @error('email') is-invalid @enderror" placeholder="Email"
                            name="email" value="{{ old('email') }}">
                        @error('email')
                            <p class="invalid-feedback">{{ $message }}</p>
                        @enderror
                    </div>
                    <input type="submit" class="btn btn-dark btn-block btn-lg" value="Submit">
                </form>
                <div class="text-center small"><a href="{{ route('user_account.login') }}">Login</a>
                </div>
            </div>
        </div>
    </section>
@endsection
